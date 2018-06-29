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
define('DB_NAME', 'wp_start');

/** MySQL database username */
define('DB_USER', 'wp_fresh_user');

/** MySQL database password */
define('DB_PASSWORD', 'TYXojivERTLchf6P');

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
define('AUTH_KEY',         ';ud*Yo;3$b{Lux_f%N*o6[b([i/mFw$&TEfj[GFx&q+5:fLvoA=~v^G/|R+DTq#@');
define('SECURE_AUTH_KEY',  'IFXwb*hF<H ODZ-+VLG!@aY3KA][$tuuD!`+HGsvF7&%9:z$[weZ%>rUhBCuDuh`');
define('LOGGED_IN_KEY',    'q1Jh!94VAKa?E^J2-3LvOY_Erf)@c7?S`{/MR<gF`Ro]X`,bM4}0ND2CtNZ;.1,+');
define('NONCE_KEY',        'P=IA26=X5-8O=t=YC&a,CZ2Z;(HPmNayKjS~<TPVf~6$Usxw[yN>>)6b=X:}tB=0');
define('AUTH_SALT',        '9HeP*:t.K:WKuKkBXg:nAVadnSsH:RKvPvS7,ZX-4+gV^.YN8sP>3Be;lyvix;{l');
define('SECURE_AUTH_SALT', ',$jF#]@HO}WLJeyL[%zv<&G1] /]Z7DFm.9Bbc$9Iq7j15V,SiS8e&8Gw9#+UI0W');
define('LOGGED_IN_SALT',   'Kgg{mx+O8:Ef/d3/>p-ug.Xc9`glr98AwW/B%&<i9hPd9HGapaqrK5Q,I#> r=O,');
define('NONCE_SALT',       'tIR?F=3xCZt:H}:KnoZeaxLg(~n,W)=|qmz/.G0QlM:e-Nzkx-RtT+|i;_?~@He!');

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
define('WP_DEBUG', true);
define('RELOCATE',true);
define('WP_HOME','http://localhost:8888');
define('WP_SITEURL','http://localhost:8888');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
