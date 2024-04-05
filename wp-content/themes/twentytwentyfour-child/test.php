<?php
// Load WordPress
require_once('/var/www/html/wordpress/wp-load.php');

// Include necessary function definitions
require_once('/var/www/html/wordpress/wp-content/themes/twentytwentyfour-child/functions.php');

$time_availablity = "moderate";
$flexibility = "high";
$garden_purpose = array("Hobby Leisure", "Fresh Produce");
$plant_types = array("Medium Tree", "Groundcover", "Perennial", "Grass", "Shrub");

//$lifestyle_filter = generate_lifestyle_filter($time_availablity, $flexibility, $garden_purpose, $plant_types);
//$lifestyle_botanical_names = generate_lifestyle_botanical_names($lifestyle_filter);
//$lifestyle_plant_info = get_plant_info($lifestyle_botanical_names);
//$response = json_encode($lifestyle_plant_info);
//echo $response;
print_r($plant_types);
//print_r($lifestyle_botanical_names);
$plant_types = get_all_plant_types();
print_r($plant_types);
?>
