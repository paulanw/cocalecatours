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
error_reporting(E_ALL);

ini_set('display_errors', '1');
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'lizegama_wp1');

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
define('AUTH_KEY',         '4k2Yb11xeyt8XmIlhKOQEsqtEI8FxCxA9P0W2bSevwDG6FGsSnFDqwYBxFsvlema');
define('SECURE_AUTH_KEY',  'kCxQAU5KDHKGnOI55OeY5lPhYFFFrgf2BW4guNUMuBy0vBq5uA1diwhzn7zTPooR');
define('LOGGED_IN_KEY',    'YAWndFcD29jwrATsMj7oHbxGIYcdSz1DHMT3mHi32hsYf2j5REcTMjNTL6719gmE');
define('NONCE_KEY',        'oJDArtAeQQyHAUE8EnVazEwfrUrDbxAePKfyQedz0qxxraWcjH6Qto3XwJcfdiAd');
define('AUTH_SALT',        'afDNE93QEUhv3ZoW3OMtASDVuzO1itAbcwj6myXXu0mYZfHoWJg1wUe56vQdO0Su');
define('SECURE_AUTH_SALT', 'ZFfgrtYnMA8ptl0Dfh3RNHkfFalADaCv7Ts8Pmln12UtoM7C2VVwoHitbk5dGcYd');
define('LOGGED_IN_SALT',   'XvtIGyYEFO2m88vsfRsWY4POIU2arIVaT9AdKrbucVt1EenHH64vNcvhurQRNx8I');
define('NONCE_SALT',       'i20Efv3P5VJymWbCVV1ncZtaLQjjoHVYtnzZdIAvR7JWUWeuGZEneLHF52A1WXMu');

/**
 * Other customizations.
 */
define('FS_METHOD','direct');define('FS_CHMOD_DIR',0755);define('FS_CHMOD_FILE',0644);
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');

/**
 * Turn off automatic updates since these are managed upstream.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);


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
