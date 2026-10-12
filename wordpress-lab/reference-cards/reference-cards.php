<?php
/**
 * Plugin Name: Reference Cards
 * Description: A small portfolio shortcode for published article cards, with a bounded query and escaped output.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Daniel Albadawi
 * Author URI: https://albadawidev.github.io/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: reference-cards
 */

namespace Albadawi\ReferenceCards;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VERSION = '1.0.0';
const MAX_CARDS = 12;
const STYLE_HANDLE = 'albadawi-reference-cards';

/** Register through the WordPress lifecycle, including activation on a later request. */
function register_shortcode(): void {
	add_shortcode( 'reference_cards', __NAMESPACE__ . '\\render_shortcode' );
}
add_action( 'init', __NAMESPACE__ . '\\register_shortcode' );

/** One small, namespaced stylesheet; no JavaScript or external requests. */
function enqueue_assets(): void {
	wp_enqueue_style(
		STYLE_HANDLE,
		plugins_url( 'assets/reference-cards.css', __FILE__ ),
		array(),
		VERSION
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );

/** Bound both positive and negative integer input. Non-integer input uses the default. */
function card_limit( $value ): int {
	$integer = filter_var( $value, FILTER_VALIDATE_INT );
	return false === $integer ? 3 : max( 1, min( MAX_CARDS, $integer ) );
}

function status_markup( string $message ): string {
	return '<section class="reference-cards" aria-label="' . esc_attr__( 'Reference articles', 'reference-cards' ) . '">'
		. '<p class="reference-cards__status" role="status">' . esc_html( $message ) . '</p></section>';
}

/**
 * Render only public, unprotected posts. Explicit post arguments preserve the
 * surrounding main or nested Loop, so the shortcode never calls the_post().
 *
 * @param array|string $attributes Shortcode attributes from WordPress.
 */
function render_shortcode( $attributes = array() ): string {
	$attributes = shortcode_atts(
		array( 'count' => 3, 'category' => '' ),
		$attributes,
		'reference_cards'
	);
	$category_input = is_scalar( $attributes['category'] ) ? trim( (string) $attributes['category'] ) : '';
	$category = sanitize_title( $category_input );
	if ( '' !== $category_input && '' === $category ) {
		return status_markup( __( 'No published articles match this selection.', 'reference-cards' ) );
	}

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'has_password'        => false,
		'posts_per_page'      => card_limit( $attributes['count'] ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'orderby'             => array( 'date' => 'DESC', 'ID' => 'DESC' ),
	);
	if ( '' !== $category ) {
		$args['category_name'] = $category;
	}

	try {
		$query = new \WP_Query( $args );
		if ( empty( $query->posts ) ) {
			return status_markup( __( 'No published articles match this selection.', 'reference-cards' ) );
		}

		$html = '<section class="reference-cards" aria-label="' . esc_attr__( 'Reference articles', 'reference-cards' ) . '">';
		$html .= '<ul class="reference-cards__grid">';
		foreach ( $query->posts as $article ) {
			if ( ! $article instanceof \WP_Post ) {
				throw new \UnexpectedValueException( 'Expected WordPress post objects.' );
			}
			$title = get_the_title( $article );
			if ( '' === trim( $title ) ) {
				$title = __( 'Untitled article', 'reference-cards' );
			}
			$excerpt = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $article ) ), 28, '…' );
			$html .= '<li class="reference-cards__item"><article class="reference-cards__card">';
			$html .= '<h3 class="reference-cards__title"><a href="' . esc_url( get_permalink( $article ) ) . '">' . esc_html( $title ) . '</a></h3>';
			$html .= '<time class="reference-cards__date" datetime="' . esc_attr( get_the_date( 'c', $article ) ) . '">' . esc_html( get_the_date( '', $article ) ) . '</time>';
			if ( '' !== $excerpt ) {
				$html .= '<p class="reference-cards__excerpt">' . esc_html( $excerpt ) . '</p>';
			}
			$html .= '</article></li>';
		}
		return $html . '</ul></section>';
	} catch ( \Throwable $error ) {
		// No exception messages, SQL, paths, or user data are exposed to visitors.
		return status_markup( __( 'Articles are temporarily unavailable. Please try again later.', 'reference-cards' ) );
	}
}
