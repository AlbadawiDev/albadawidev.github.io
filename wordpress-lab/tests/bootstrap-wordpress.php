<?php
/** Install and activate the lab in an empty, explicitly named test database. */
declare(strict_types=1);

if ( '1' !== getenv( 'WP_LAB_ALLOW_INSTALL' ) ) {
	fwrite( STDERR, "Set WP_LAB_ALLOW_INSTALL=1 for a disposable test installation.\n" );
	exit( 1 );
}
$root = rtrim( (string) getenv( 'WP_ROOT' ), '/' );
$database = (string) getenv( 'WP_LAB_DB_NAME' );
if ( ! is_file( $root . '/wp-load.php' ) || ! preg_match( '/^wordpress_lab(?:_[a-z0-9]+)?$/', $database ) ) {
	fwrite( STDERR, "A real WordPress directory and a wordpress_lab test database are required.\n" );
	exit( 1 );
}
if ( is_file( $root . '/wp-config.php' ) ) {
	fwrite( STDERR, "Refusing to replace an existing wp-config.php. Use a fresh temporary WordPress directory.\n" );
	exit( 1 );
}

$config = "<?php\n";
foreach ( array(
	'DB_NAME' => $database,
	'DB_USER' => getenv( 'WP_LAB_DB_USER' ) ?: 'root',
	'DB_PASSWORD' => (string) getenv( 'WP_LAB_DB_PASSWORD' ),
	'DB_HOST' => getenv( 'WP_LAB_DB_HOST' ) ?: '127.0.0.1:3306',
	'DB_CHARSET' => 'utf8mb4',
	'DB_COLLATE' => '',
	'WP_HOME' => 'http://localhost',
	'WP_SITEURL' => 'http://localhost',
	'WP_ENVIRONMENT_TYPE' => 'local',
	'DISABLE_WP_CRON' => true,
	'AUTOMATIC_UPDATER_DISABLED' => true,
	'WP_DEBUG' => true,
	'WP_DEBUG_DISPLAY' => false,
) as $key => $value ) {
	$config .= 'define( ' . var_export( $key, true ) . ', ' . var_export( $value, true ) . " );\n";
}
foreach ( array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ) as $key ) {
	$config .= 'define( ' . var_export( $key, true ) . ', ' . var_export( bin2hex( random_bytes( 32 ) ), true ) . " );\n";
}
$config .= '$table_prefix = ' . var_export( 'wp_lab_', true ) . ";\n";
$config .= "if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }\nrequire_once ABSPATH . 'wp-settings.php';\n";
if ( false === file_put_contents( $root . '/wp-config.php', $config ) ) {
	throw new RuntimeException( 'Unable to write the temporary WordPress configuration.' );
}
chmod( $root . '/wp-config.php', 0600 );
define( 'WP_INSTALLING', true );
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require $root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( is_blog_installed() ) {
	throw new RuntimeException( 'Refusing to install into an existing WordPress database.' );
}
add_filter( 'pre_wp_mail', static function () { return true; } );
$installed = wp_install( 'Reference Cards synthetic lab', 'lab_admin', 'lab@example.invalid', false, '', bin2hex( random_bytes( 16 ) ) );
if ( empty( $installed['user_id'] ) || ! is_blog_installed() ) {
	throw new RuntimeException( 'WordPress installation failed.' );
}
$activation = activate_plugin( 'reference-cards/reference-cards.php', '', false, true );
if ( is_wp_error( $activation ) ) {
	throw new RuntimeException( $activation->get_error_message() );
}
echo 'Installed WordPress ' . get_bloginfo( 'version' ) . ' on PHP ' . PHP_VERSION . ' / MySQL ' . $wpdb->db_version() . "\n";
