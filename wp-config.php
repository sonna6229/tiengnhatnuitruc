<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'nhtrupgg_wp2015');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ';wW_s@p JuV/$^pi )luB|kP/M~NA=cVJ/d>Zu#Bgc2,a8|,0R+%?9QI31PAkp,2');
define('SECURE_AUTH_KEY',  '0DMRdYTohBagp3@Y<?)~QtMtiaA;c?s,UJm!JDQmJ:xZQ )W3X2<9Y%x=}v8&A:z');
define('LOGGED_IN_KEY',    '#71)(9%TRV(l7vt?D>VveH2D1^Jgm^tHa`y8W=Gg6m[*Uj 9@V_Ng$U?uRzm%qXJ');
define('NONCE_KEY',        ' GHKGv=@ emydwY^Z2-53=`7(67iOJ@5qFPDWRF5BA@/>YX(NO&$x6TTuY=#S~.)');
define('AUTH_SALT',        '?>k9!vgBT~N#GDa/s,[?eSN&Jz8s&|=`@]D})]L|jNg^|@i?O=W-KUn$lLQ|cw@F');
define('SECURE_AUTH_SALT', '(.P6hC]fUg{uH[kFDJL)Wm5W7J6QcM,4/I#/DZXxN?5YrR[eAH&Uvu$w!#=#NC!N');
define('LOGGED_IN_SALT',   'DBtHKha#js,)8?#X@&Jet<^@C~^(26)P-m2hT*Oy=Kz2(QmzK4;fR{.0X~R)#T0B');
define('NONCE_SALT',       'a-xVTzn.$Z7^ZT&CP5/@RmV?O*7yrQ1W@__k}#Q$g,!khF83LEU[kzmZ|nd,,~ue');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wptttn_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
