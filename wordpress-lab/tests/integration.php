<?php
/** Real WordPress + MySQL functional checks; no mocked WordPress API. */
declare(strict_types=1);

$root = rtrim( (string) getenv( 'WP_ROOT' ), '/' );
if ( ! is_file( $root . '/wp-load.php' ) ) {
	throw new RuntimeException( 'WP_ROOT must point to an actual installed WordPress directory.' );
}
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require $root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! preg_match( '/^wordpress_lab(?:_[a-z0-9]+)?$/', DB_NAME ) || 'Reference Cards synthetic lab' !== get_bloginfo( 'name' ) ) {
	throw new RuntimeException( 'Refusing to create fixtures outside the isolated test installation.' );
}
$artifacts = getenv( 'WP_LAB_ARTIFACTS' ) ?: dirname( __DIR__ ) . '/artifacts';
if ( ! is_dir( $artifacts ) && ! mkdir( $artifacts, 0755, true ) ) {
	throw new RuntimeException( 'Unable to create the artifacts directory.' );
}
$checks = array();
function expect( bool $condition, string $message = 'Expectation failed.' ): void {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}
function check( string $name, callable $test ): void {
	global $checks;
	try {
		$test();
		$checks[] = array( 'name' => $name, 'status' => 'passed' );
		echo 'PASS ' . $name . "\n";
	} catch ( Throwable $error ) {
		$checks[] = array( 'name' => $name, 'status' => 'failed', 'message' => $error->getMessage() );
		fwrite( STDERR, 'FAIL ' . $name . ': ' . $error->getMessage() . "\n" );
	}
}
function document( string $html ): DOMDocument {
	$dom = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$dom->loadHTML( '<!doctype html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>' );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
	return $dom;
}
function card_count( string $html ): int {
	return ( new DOMXPath( document( $html ) ) )->query( '//article' )->length;
}
function fixture( string $title, string $status, array $extra = array() ): int {
	$id = wp_insert_post( array_merge( array(
		'post_title' => $title,
		'post_status' => $status,
		'post_type' => 'post',
		'post_content' => 'Synthetic article body for the WordPress portfolio lab.',
		'post_excerpt' => 'A short synthetic article summary. No client data is used.',
		'post_date' => gmdate( 'Y-m-d H:i:s', time() - 86400 ),
		'post_date_gmt' => gmdate( 'Y-m-d H:i:s', time() - 86400 ),
	), $extra ), true );
	if ( is_wp_error( $id ) || ! $id ) {
		throw new RuntimeException( 'Fixture creation failed.' );
	}
	return (int) $id;
}

wp_set_current_user( (int) username_exists( 'lab_admin' ) );
// This is a guarded disposable database. Remove the WordPress sample content.
foreach ( get_posts( array( 'post_type' => array( 'post', 'page' ), 'post_status' => 'any', 'numberposts' => -1 ) ) as $sample ) {
	wp_delete_post( $sample->ID, true );
}
update_option( 'permalink_structure', '' );
update_option( 'timezone_string', 'UTC' );
$guides_term = wp_insert_term( 'Guides', 'category', array( 'slug' => 'guides' ) );
$news_term = wp_insert_term( 'News', 'category', array( 'slug' => 'news' ) );
if ( is_wp_error( $guides_term ) || is_wp_error( $news_term ) ) {
	throw new RuntimeException( 'Fixture categories require a fresh test database.' );
}
$guides_id = (int) $guides_term['term_id'];
$news_id = (int) $news_term['term_id'];
$guide_posts = array();
for ( $i = 1; $i <= 15; ++$i ) {
	$date = gmdate( 'Y-m-d H:i:s', time() - ( 21 - $i ) * 86400 );
	$guide_posts[] = fixture( sprintf( 'Guide %02d', $i ), 'publish', array(
		'post_category' => array( $guides_id ),
		'post_date' => $date,
		'post_date_gmt' => $date,
	) );
}
$news_post = fixture( 'Other category article', 'publish', array( 'post_category' => array( $news_id ) ) );
$draft = fixture( 'Excluded draft article', 'draft', array( 'post_category' => array( $guides_id ) ) );
$private = fixture( 'Excluded private article', 'private', array( 'post_category' => array( $guides_id ) ) );
$protected = fixture( 'Excluded password article', 'publish', array( 'post_password' => 'synthetic', 'post_category' => array( $guides_id ) ) );
$future = fixture( 'Excluded scheduled article', 'future', array(
	'post_category' => array( $guides_id ),
	'post_date' => gmdate( 'Y-m-d H:i:s', time() + 7 * 86400 ),
	'post_date_gmt' => gmdate( 'Y-m-d H:i:s', time() + 7 * 86400 ),
) );
$page = fixture( 'Excluded page article', 'publish', array( 'post_type' => 'page' ) );
wp_set_current_user( 0 );

check( 'Actual WordPress core and database are loaded', static function () {
	global $wpdb;
	expect( defined( 'ABSPATH' ) && $wpdb instanceof wpdb );
	expect( '' !== (string) get_bloginfo( 'version' ) && '' !== $wpdb->db_version() );
} );
check( 'Plugin activation survives a fresh PHP bootstrap', static function () {
	expect( is_plugin_active( 'reference-cards/reference-cards.php' ) );
	expect( function_exists( 'Albadawi\\ReferenceCards\\render_shortcode' ) );
} );
check( 'Shortcode registers on the init lifecycle', static function () {
	expect( did_action( 'init' ) > 0 );
	expect( shortcode_exists( 'reference_cards' ) );
	expect( false !== has_action( 'init', 'Albadawi\\ReferenceCards\\register_shortcode' ) );
} );
check( 'Stylesheet is enqueued through the frontend lifecycle', static function () {
	expect( false !== has_action( 'wp_enqueue_scripts', 'Albadawi\\ReferenceCards\\enqueue_assets' ) );
	do_action( 'wp_enqueue_scripts' );
	expect( wp_style_is( 'albadawi-reference-cards', 'enqueued' ) );
	$style = wp_styles()->registered['albadawi-reference-cards'];
	expect( str_ends_with( $style->src, '/reference-cards/assets/reference-cards.css' ) );
	expect( array() === $style->deps );
} );
check( 'Default count returns three latest published posts', static function () use ( $news_post, $guide_posts ) {
	$html = do_shortcode( '[reference_cards]' );
	expect( 3 === card_count( $html ) );
	$links = ( new DOMXPath( document( $html ) ) )->query( '//article/h3/a' );
	expect( get_permalink( $news_post ) === $links->item( 0 )->getAttribute( 'href' ) );
	expect( get_permalink( $guide_posts[14] ) === $links->item( 1 )->getAttribute( 'href' ) );
} );
foreach ( array( '1' => 1, '2' => 2, '0' => 1, '-99' => 1, '999999' => 12, 'abc' => 3, '2.5' => 3 ) as $count => $expected ) {
	check( 'Bounded count input ' . $count . ' renders ' . $expected . ' cards', static function () use ( $count, $expected ) {
		expect( $expected === card_count( do_shortcode( '[reference_cards count="' . $count . '"]' ) ) );
	} );
}
check( 'Unknown attributes do not alter the selection', static function () {
	expect( do_shortcode( '[reference_cards unknown="9999"]' ) === do_shortcode( '[reference_cards]' ) );
} );
check( 'Category slug selects the expected real database rows', static function () use ( $guide_posts ) {
	$html = do_shortcode( '[reference_cards count="12" category="guides"]' );
	expect( 12 === card_count( $html ) );
	$links = ( new DOMXPath( document( $html ) ) )->query( '//article/h3/a' );
	foreach ( $links as $i => $link ) {
		expect( get_permalink( $guide_posts[14 - $i] ) === $link->getAttribute( 'href' ) );
	}
} );
check( 'A second category excludes the first category', static function () {
	$html = do_shortcode( '[reference_cards count="12" category="news"]' );
	expect( 1 === card_count( $html ) && str_contains( $html, 'Other category article' ) );
	expect( ! str_contains( $html, 'Guide 15' ) );
} );
check( 'Missing category has a readable empty state', static function () {
	$html = do_shortcode( '[reference_cards category="no-such-category"]' );
	expect( 0 === card_count( $html ) && str_contains( $html, 'No published articles match this selection.' ) );
	expect( str_contains( $html, 'role="status"' ) );
} );
check( 'An invalid nonempty category fails closed', static function () {
	expect( 0 === card_count( do_shortcode( '[reference_cards category="@@@"]' ) ) );
} );
check( 'Draft, private, protected, scheduled and page content are excluded', static function () {
	$html = do_shortcode( '[reference_cards count="12"]' );
	expect( ! str_contains( $html, 'Excluded ' ) );
} );
check( 'Public selection remains public for a logged-in administrator', static function () {
	wp_set_current_user( (int) username_exists( 'lab_admin' ) );
	try {
		expect( ! str_contains( do_shortcode( '[reference_cards count="12"]' ), 'Excluded ' ) );
	} finally {
		wp_set_current_user( 0 );
	}
} );
check( 'Query has a hard limit and skips pagination totals', static function () {
	$seen = null;
	$capture = static function ( WP_Query $query ) use ( &$seen ) {
		if ( 'post' === $query->get( 'post_type' ) && true === $query->get( 'no_found_rows' ) ) {
			$seen = $query->query_vars;
		}
	};
	add_action( 'pre_get_posts', $capture );
	try {
		do_shortcode( '[reference_cards count="999999" category="guides"]' );
	} finally {
		remove_action( 'pre_get_posts', $capture );
	}
	expect( is_array( $seen ), 'The actual WP_Query did not run.' );
	expect( 12 === $seen['posts_per_page'] && true === $seen['no_found_rows'] );
	expect( 'publish' === $seen['post_status'] && false === $seen['has_password'] );
} );
check( 'Sticky posts do not override newest-first order', static function () use ( $guide_posts ) {
	stick_post( $guide_posts[0] );
	try {
		$links = ( new DOMXPath( document( do_shortcode( '[reference_cards category="guides"]' ) ) ) )->query( '//article/h3/a' );
		expect( get_permalink( $guide_posts[14] ) === $links->item( 0 )->getAttribute( 'href' ) );
	} finally {
		unstick_post( $guide_posts[0] );
	}
} );
check( 'Shortcode returns its markup without echoing', static function () {
	ob_start();
	$html = do_shortcode( '[reference_cards]' );
	$echoed = ob_get_clean();
	expect( '' === $echoed && 3 === card_count( $html ) );
} );

$main_query = new WP_Query( array( 'p' => $news_post ) );
$GLOBALS['wp_query'] = $main_query;
$GLOBALS['wp_the_query'] = $main_query;
$main_query->the_post();
check( 'The surrounding main Loop is preserved', static function () use ( $main_query, $news_post ) {
	$before_post = $GLOBALS['post'];
	do_shortcode( '[reference_cards category="guides"]' );
	expect( $main_query === $GLOBALS['wp_query'] && $before_post === $GLOBALS['post'] );
	expect( $news_post === get_the_ID() && 'Other category article' === get_the_title() );
} );
check( 'A nested Loop is preserved and WordPress can reset to the main Loop', static function () use ( $news_post, $guide_posts ) {
	$secondary = new WP_Query( array( 'p' => $guide_posts[1] ) );
	$secondary->the_post();
	$before_post = $GLOBALS['post'];
	do_shortcode( '[reference_cards]' );
	expect( $before_post === $GLOBALS['post'] && $guide_posts[1] === get_the_ID() );
	wp_reset_postdata();
	expect( $news_post === get_the_ID() );
} );

$error_markup = '';
check( 'Query exceptions render a safe error state and preserve the Loop', static function () use ( &$error_markup, $news_post ) {
	$failure = static function ( $posts, WP_Query $query ) {
		if ( true === $query->get( 'no_found_rows' ) && 'post' === $query->get( 'post_type' ) ) {
			throw new RuntimeException( 'synthetic private SQL error detail' );
		}
		return $posts;
	};
	add_filter( 'posts_pre_query', $failure, 10, 2 );
	try {
		$error_markup = do_shortcode( '[reference_cards category="guides" count="4"]' );
	} finally {
		remove_filter( 'posts_pre_query', $failure, 10 );
	}
	expect( str_contains( $error_markup, 'Articles are temporarily unavailable.' ) );
	expect( ! str_contains( $error_markup, 'SQL error detail' ) && $news_post === get_the_ID() );
} );
check( 'Untrusted title and excerpt output cannot introduce active HTML', static function () use ( $guide_posts ) {
	$title = static function ( $text, $id ) use ( $guide_posts ) {
		return $guide_posts[14] === (int) $id ? '<script>alert("synthetic")</script> & "quoted"' : $text;
	};
	$excerpt = static function ( $text, $post ) use ( $guide_posts ) {
		return $guide_posts[14] === (int) $post->ID ? '<img src=x onerror=alert(1)>Excerpt & "quoted"' : $text;
	};
	add_filter( 'the_title', $title, 99, 2 );
	add_filter( 'get_the_excerpt', $excerpt, 99, 2 );
	try {
		$html = do_shortcode( '[reference_cards category="guides" count="1"]' );
	} finally {
		remove_filter( 'the_title', $title, 99 );
		remove_filter( 'get_the_excerpt', $excerpt, 99 );
	}
	expect( str_contains( $html, '&lt;script&gt;' ) && str_contains( $html, '&amp;' ) && str_contains( $html, '&quot;' ) );
	$xpath = new DOMXPath( document( $html ) );
	expect( 0 === $xpath->query( '//script | //img | //*[@onerror]' )->length );
} );
check( 'Filtered javascript URLs are rejected by WordPress URL escaping', static function () {
	$url = static function () { return 'javascript:alert("synthetic")'; };
	add_filter( 'post_link', $url );
	try {
		$html = do_shortcode( '[reference_cards count="1"]' );
	} finally {
		remove_filter( 'post_link', $url );
	}
	expect( ! str_contains( strtolower( $html ), 'javascript:' ) );
} );
check( 'Empty titles receive a descriptive accessible link label', static function () {
	$title = static function () { return ''; };
	add_filter( 'the_title', $title, 99 );
	try {
		$html = do_shortcode( '[reference_cards count="1"]' );
	} finally {
		remove_filter( 'the_title', $title, 99 );
	}
	expect( str_contains( $html, '>Untitled article</a>' ) );
} );
check( 'Cards use named links and machine-readable dates', static function () {
	$xpath = new DOMXPath( document( do_shortcode( '[reference_cards]' ) ) );
	$links = $xpath->query( '//article/h3/a' );
	$dates = $xpath->query( '//article/time[@datetime]' );
	expect( 3 === $links->length && 3 === $dates->length );
	foreach ( $links as $link ) {
		expect( '' !== trim( $link->textContent ) && str_starts_with( $link->getAttribute( 'href' ), 'http://localhost/' ) );
	}
	foreach ( $dates as $date ) {
		expect( false !== strtotime( $date->getAttribute( 'datetime' ) ) );
	}
} );

$css = file_get_contents( dirname( __DIR__ ) . '/reference-cards/assets/reference-cards.css' );
$version = esc_html( get_bloginfo( 'version' ) );
$snapshot = '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
	. '<title>Reference Cards: actual WordPress render</title><style>body{font-family:system-ui,sans-serif;background:#f7f9fc;margin:0;color:#18283b}main{max-width:1080px;margin:auto;padding:2rem 1rem}h1{font-size:2rem}p{line-height:1.6}'
	. $css . '</style></head><body><main><h1>Reference Cards</h1><p>Rendered by actual WordPress ' . $version . '. Synthetic fixtures. Links target the disposable localhost installation.</p><h2>Latest guides</h2>'
	. do_shortcode( '[reference_cards count="3" category="guides"]' ) . '<h2>Empty selection</h2>'
	. do_shortcode( '[reference_cards category="no-such-category"]' ) . '<h2>Controlled query exception</h2>'
	. $error_markup . '<p>Assisted personal portfolio lab. No client code or client data.</p></main></body></html>';
file_put_contents( $artifacts . '/reference-cards-render.html', $snapshot );
$failed = count( array_filter( $checks, static function ( $check ) { return 'failed' === $check['status']; } ) );
$report = array(
	'generated_at_utc' => gmdate( 'c' ),
	'wordpress_version' => get_bloginfo( 'version' ),
	'php_version' => PHP_VERSION,
	'mysql_version' => $wpdb->db_version(),
	'wordpress_download_sha256' => getenv( 'WP_LAB_DOWNLOAD_SHA256' ) ?: null,
	'fixture_type' => 'synthetic',
	'checks_total' => count( $checks ),
	'checks_passed' => count( $checks ) - $failed,
	'checks_failed' => $failed,
	'checks' => $checks,
);
file_put_contents( $artifacts . '/integration-results.json', json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n" );
echo 'Actual tested versions: WordPress ' . $report['wordpress_version'] . ' / PHP ' . PHP_VERSION . ' / MySQL ' . $report['mysql_version'] . "\n";
echo ( count( $checks ) - $failed ) . '/' . count( $checks ) . " functional checks passed.\n";
exit( 0 === $failed ? 0 : 1 );
