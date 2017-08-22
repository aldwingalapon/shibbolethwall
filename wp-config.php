<?php
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
define('DB_NAME', 'upvangu2_sw');

/** MySQL database username */
define('DB_USER', 'upvangu2_swuser');

/** MySQL database password */
define('DB_PASSWORD', ';[PIgm+lnhbgm?++tb');

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
define('AUTH_KEY',         '0nThF+*T.)G uwQYcv*W%W `hq,Q=FH-yrZnBQ=C>mZ7Z:C9w)]Mxwt_G3y^y&.)');
define('SECURE_AUTH_KEY',  'g&EA;9L%#fg+=A{z=4.l[&4aeu+<&M5)8k_u?Jg}%}#xJXay unxgeDTD+~iuO<W');
define('LOGGED_IN_KEY',    'hHKL_H2{kTg4Q{OBEIu{,@}Q-*ZD*#rOSR} SLlu>;HXy:J(4/=zMZ<m/ W+BAy5');
define('NONCE_KEY',        'UCdT!IQUE9O!se#+0Ee?3*/u1+z#BIJ3B8oXw5A;rCa5=FOV=Yz*30+}CC :^&j5');
define('AUTH_SALT',        '#FqOyyl89<;6o)D}!ClGjBWBqiVU3_R)(+S}EHfebxEA>/vXBX.)G7RAO:_w9ox,');
define('SECURE_AUTH_SALT', 'tzF#WWnOaI{GLTN@>b*[[3.>Mv&{``n/u0w7Pbg,VC9r))VKd-WuI M+(y>j*./q');
define('LOGGED_IN_SALT',   '5~.{7#,QgU.SDhz&+P-96j.J(Qy$!:7j-6;bLYgd?q&oaa*q,a9VvM5}^[$h>}N:');
define('NONCE_SALT',       'G)<y=6;Dz_{1^t>~^GrTI|vm5iq|NM6FCrT_Jt-eM{}7@Xd(k)`H_e^NW!$(h:T?');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_sw_';

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
