<?php
add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_styles');

function my_child_theme_enqueue_styles() {
    wp_enqueue_style('parent-theme-style', get_template_directory_uri() . '/var/www/html/wordpress/wp-content/themes/twentytwentyfour/style.css');
}

/**
 * Establish MySQL database connection.
 */
$host = 'localhost';
$username = 'root';
$password = 'testpassword';
$database = 'Flora';

$db = new mysqli($host, $username, $password, $database);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

/**
 * Retrieve plant types based on the user-selected location. --------------- version 2.0
 *
 * Steps:
 * 1. Present a dropdown list in the frontend for users to select a location.
 * 2. Based on the selected location, retrieve the corresponding longitude and latitude from the database.
 * 3. Use the retrieved longitude and latitude to call an API for getting climate information specific to the location.
 * 4. Utilize the climate information obtained from the API to fetch suitable plant types for that climate.
 * 5. Loop through the returned plant types to query the database for their botanical names...
 * 6. For each botanical name, call an API to retrieve image URLs of the plants...
 * 7. Display the plant images on the frontend, potentially using a shortcode for easy integration...
 */

function get_lon_lat($postcode) {
    global $db;

    $stmt = $db->prepare("SELECT longitude, latitude FROM postcodes_geo WHERE postcode = ? LIMIT 1");
    $stmt->bind_param('s', $postcode);
    $stmt->execute();

    $stmt->bind_result($longitude, $latitude);
    $stmt->fetch();
    $stmt->close();

    if ($longitude && $latitude) {
        return array('lon' => $longitude, 'lat' => $latitude);
    } else {
        return false;
    }
}

function get_climate_info($lon_lat_data) {
    $lon_lat_data['lon'] = floatval($lon_lat_data['lon']);
    $lon_lat_data['lat'] = floatval($lon_lat_data['lat']);

    $api_url = 'http://3.107.46.246:8001/climateinfo';

    $headers = array(
        'Content-Type: application/json'
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($lon_lat_data),
        CURLOPT_HTTPHEADER => $headers,
    ));

    $climate_info = curl_exec($curl);

    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        return "Error: " . $error_msg;
    }

    curl_close($curl);

    return $climate_info;
}

function get_plant_type($climate_info) {
    $api_url = 'http://3.107.46.246:8000/predict';

    $headers = array(
        'Content-Type: application/json'
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $climate_info,
        CURLOPT_HTTPHEADER => $headers,
    ));

    $plant_type_prediction = curl_exec($curl);

    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        return "Error: " . $error_msg;
    }

    curl_close($curl);

    $plant_type_prediction = json_decode($plant_type_prediction, true);

    if (isset($plant_type_prediction['prediction'])) {
        $plant_types = $plant_type_prediction['prediction'][0];
    } else {
        return array();
    }

    return $plant_types;
}

function get_botanical_names($plant_types) {
    global $db;

    $botanical_names = array();

    foreach ($plant_types as $plant_type) {
        $stmt = $db->prepare("
            SELECT Botanical_name
            FROM water_plant
            WHERE Plant_type = ?
                AND Native = 'Yes'
                AND IF_image = 'Yes'
                AND Image_location LIKE '%.jpg'
            LIMIT 1
        ");

        $stmt->bind_param('s', $plant_type);
        $stmt->execute();
        $stmt->bind_result($botanical_name);
        while ($stmt->fetch()) {
            $botanical_names[] = $botanical_name;
        }
        $stmt->close();
    }

    return $botanical_names;
}

function get_plant_info($botanical_names) {
    global $db;

    $plant_info = array();

    foreach ($botanical_names as $botanical_name) {
        $stmt = $db->prepare("SELECT * FROM water_plant WHERE Botanical_name = ? AND IF_image = 'Yes' AND Image_location LIKE '%.jpg'");
        $stmt->bind_param('s', $botanical_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $plant_data = $result->fetch_assoc();

        $plant_info[] = $plant_data;

        $stmt->close();
    }

    return $plant_info;
}

function handle_get_plant_types() {
    $postcode = isset($_POST['input']) ? sanitize_text_field($_POST['input']) : '';
    $lon_lat_data = get_lon_lat($postcode);
    $climate_info = get_climate_info($lon_lat_data);
    $plant_types = get_plant_type($climate_info);
    $botanical_names = get_botanical_names($plant_types);
    $plant_info = get_plant_info($botanical_names);
    $response = json_encode($plant_info);

    echo $response;
    wp_die();
}

add_action('wp_ajax_handle_get_plant_types', 'handle_get_plant_types');
add_action('wp_ajax_nopriv_handle_get_plant_types', 'handle_get_plant_types');

/**
 * Lifestyle.
 */
function generate_lifestyle_filter($time_availability, $flexibility, $garden_purpose, $plant_types) {
    $maintenance_mark = 0;

    switch (strtolower($time_availability)) {
        case "limited":
            $maintenance_mark += 1;
            break;
        case "moderate":
            $maintenance_mark += 2;
            break;
        case "abundant":
            $maintenance_mark += 3;
            break;
    }

    switch (strtolower($flexibility)) {
        case "low":
            $maintenance_mark += 1;
            break;
        case "moderate":
            $maintenance_mark += 2;
            break;
        case "high":
            $maintenance_mark += 3;
            break;
    }

    foreach ($garden_purpose as $purpose) {
        switch ($purpose) {
            case "Fresh Produce":
                array_unshift($plantTypes, "Vegetable");
                $maintenance_mark += 1;
                break;
            case "Hobby Leisure":
            case "Education Opportunities":
                $maintenance_mark += 1;
                break;
            case "Basic Garden":
                $maintenance_mark -= 2;
                break;
        }
    }

    if ($maintenance_mark < 3) {
        $maintenance = "Low";
    } elseif ($maintenance_mark >= 3 && $maintenance_mark <= 4) {
        $maintenance = "Medium";
    } else {
        $maintenance = "High";
    }

    return array($maintenance, $plant_types);
}

function generate_lifestyle_botanical_names($lifestyle_filter) {
    global $db;

    $maintenance = $lifestyle_filter[0];
    $plant_types = $lifestyle_filter[1];

    $lifestyle_botanical_names = array();

    foreach ($plant_types as $plant_type) {
        $stmt = $db->prepare("SELECT Botanical_name FROM water_plant WHERE Plant_type = ? AND Maintenance = ? AND IF_image = 'Yes' LIMIT 100");
        $stmt->bind_param("ss", $plant_type, $maintenance);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $lifestyle_botanical_names[] = $row['Botanical_name'];
        }

        $stmt->close();
    }

    return $lifestyle_botanical_names;
}

function handle_get_plant_recommendation_lifestyle() {
    if (isset($_POST['time_availablity']) && isset($_POST['flexibility']) && isset($_POST['garden_purpose']) && isset($_POST['plant_types'])) {
        $time_availablity = sanitize_text_field($_POST['time_availablity']);
        $flexibility = sanitize_text_field($_POST['flexibility']);
        $garden_purpose = sanitize_text_field($_POST['garden_purpose']);
        $plant_types = $_POST['plant_types'];

        $lifestyle_filter = generate_lifestyle_filter($time_availablity, $flexibility, $garden_purpose, $plant_types);
        $lifestyle_botanical_names = generate_lifestyle_botanical_names($lifestyle_filter);
        $lifestyle_plant_info = get_plant_info($lifestyle_botanical_names);
	$response = json_encode($lifestyle_plant_info);
        echo $response;

    } else {
        wp_send_json_error('Missing parameters');
    }

    wp_die();
}

add_action('wp_ajax_get_plant_recommendation_lifestyle', 'handle_get_plant_recommendation_lifestyle');
add_action('wp_ajax_nopriv_get_plant_recommendation_lifestyle', 'handle_get_plant_recommendation_lifestyle');

/**
 * All plant type.
 */
function get_all_plant_types() {
    global $db;

    $plant_types = array();

    $stmt = $db->prepare("SELECT DISTINCT Plant_type FROM water_plant WHERE Plant_type IS NOT NULL AND Plant_type <> ''");
    $stmt->execute();
    $stmt->bind_result($plant_type);

    while ($stmt->fetch()) {
        $plant_types[] = $plant_type;
    }

    $stmt->close();

    return $plant_types;
}

function handle_get_all_plant_types() {
    $plant_types = get_all_plant_types();
    $botanical_names = get_botanical_names($plant_types);
    $plant_info = get_plant_info($botanical_names);
    $response = json_encode($plant_info);

    echo $response;
    wp_die();
}

add_action('wp_ajax_handle_get_all_plant_types', 'handle_get_all_plant_types');
add_action('wp_ajax_nopriv_handle_get_all_plant_types', 'handle_get_all_plant_types');
