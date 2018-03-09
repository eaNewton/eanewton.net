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
define('DB_NAME', 'eanewton_dev');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'w j^(N/ -W5ne}$Mjn4|-?q|KT:x0.QNE4t!r_UftJ3fS{)~JF+WmtFA=D4NP_,1');
define('SECURE_AUTH_KEY',  ':cA89<Z~f^bwypj|yB?Ahitw+(Xu%;0R(&)H~w-osq:}.k+aT+L[-p=+}{-|_LP&');
define('LOGGED_IN_KEY',    'Fc/./6cVYS@9vR8ICWVj7uAq1@+ZI|WF>1u~zIf>O~#&gb9fFGck+zh!J9c^tAR3');
define('NONCE_KEY',        'Gd+^6HLe[4Rxrx(be}:vWZt|L,JN-}rn~u`d)kt^n]6A+mruF(p?inTjpV]vrzjh');
define('AUTH_SALT',        'K[%n1~4T7jvzfP+rePCu$m[zeecz|U|0;t;c^}m3KR|.PZS|Tif@<Kqk6$=8cm^7');
define('SECURE_AUTH_SALT', 'u,,~Kp@<H5xHF}E-!qoS}e[E)t,i?8inyaG>d~W9^ L/+siy{3&+[*=+aqvuhDUs');
define('LOGGED_IN_SALT',   '+f*3l2xTB:|$ ZV3|+)|P|_3~v_]:E;KgDv*;Vox0#|6{Tebm`IRqBdPbyj04zI?');
define('NONCE_SALT',       '0)NDnaj4rZ8~x}M+z1+=x{g!Xc)^0WQ*-y,|}0Mg8e!PpK:aue{ p?g0{h+U|iJ*');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
