<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_db' );

/** Database username */
define( 'DB_USER', 'wp_demouser' );

/** Database password */
define( 'DB_PASSWORD', 'testpassword' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'qQ]qxj3o0^;{A[D;~){=CMt[4|MOlL>+;B1x&+:_g/;I&<RQ|&vpl]hkgi;so_VP' );
define( 'SECURE_AUTH_KEY',  '5P$O{Jy|,0kG|&9aL=|[VE`?cb9?n[ToHBU=~>MutNz@Q[KVGsv/603/))hyjWfC' );
define( 'LOGGED_IN_KEY',    ']4-TM.HJTV$|)1Dk>l%.1Cm|0;/,x>AgvtGee5Z8u)u3LF>>{vD:(7<hl&p]!/SD' );
define( 'NONCE_KEY',        'p. QIV&ISd)6=8Dzf^f@Xk{VfQzf;DST99ru5uCrSD3ZnS!na0??{FjOxz}WuX[j' );
define( 'AUTH_SALT',        'j,e5HU79HF<Fj}5 QFR<~*$.)DEmM(8mvdf FJDA%>QkG@rH7eGRDr8hu<=moh8D' );
define( 'SECURE_AUTH_SALT', '^X?j&T4K-Jt2(1XF:J U=d4JP{.8dYC8wGW2k/PkvC]77~wK_>s_etw{[Sz/(w>W' );
define( 'LOGGED_IN_SALT',   'tWioO`eSVMwBN<%6L`f6}tGVVRuV) 1L[4 4~0-9W6az=fCfHlOk9cvDd>L}]q88' );
define( 'NONCE_SALT',       'vVR8O+#J.o>%vG6Id9B2(XFpyA:xQ^RLK(X%w-[{fjujv5I f|R*(X`J0@ NQ[:k' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
define( 'FS_METHOD', 'direct');
