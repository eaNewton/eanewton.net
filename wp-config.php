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
define('DB_NAME', 'vjnewton_eanewton');

/** MySQL database username */
define('DB_USER', 'vjnewton_eaadmin');

/** MySQL database password */
define('DB_PASSWORD', 'patri0ts!');

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
define('AUTH_KEY',         '7VwR!myONJNo_qQlhm}+:^HPG&ZZ!ou|9=X&X;j-@Lm(qd([X<6.AS.AY1PZ]$I5');
define('SECURE_AUTH_KEY',  '/Wa`b`gd|lUPA>FqaNd.Fi^W`%]#Nxn9[^;Yg=~`tA/THrw?YBPaiM@=(s]X,k[M');
define('LOGGED_IN_KEY',    '%poEv/rTG)ObZ1~DQtrtNE4a$4^P+*Jtbe9~b W?cCvYCLDnwwFf=f5&V$,*n$:t');
define('NONCE_KEY',        'aSSEH-|F9*hyuH`>Jb&yt;LN$^%qO$YF0a8v8c1(xa&1~w!-FJ5[-]dES_VLM,|Z');
define('AUTH_SALT',        '~xR_[Z83K);m1l:#j:y:[t+t=g9A5Rl%h(,|XA>9= @6@P`Fn>M?-hL^Ky}rfY#P');
define('SECURE_AUTH_SALT', 'Ta!H<6+qy3J_sa7w3yRtErwf<BW_,r?(Tg$EPH!,zfyE(9%!2y4(C_M!L?d :gjx');
define('LOGGED_IN_SALT',   'rU1).Z0&CdK`E1xQxf_G%*[5LQZN=ZeDA&#Cd]]],2+~@L_-M<al]Z0kmr=J)oP!');
define('NONCE_SALT',       '^5<D_zgt!s}Z}{:NNA7rB8B@v(]q&+Q<Nt|.&+|Pu%=S4f0+lDFiB+~5!gJ+C3bL');

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
