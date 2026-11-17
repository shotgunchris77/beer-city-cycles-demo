<?php
/**
 * Beer City Cycles theme.
 *
 * inc/settings.php  Settings > Beer City (notification email, Instagram link)
 * inc/events.php    "Events & Rides" post type and its shortcodes
 * inc/store.php     WooCommerce glue: merch grid, product page, cart link, order email routing
 * inc/shipping.php  USPS Ground Advantage shipping from Milwaukee (zone + weight tables)
 * inc/contact.php   Contact form
 * inc/demo.php      Demo preview helpers (only active when the bcc_demo_mode option is on)
 * inc/seed.php      One-time starter content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BCC_VERSION', '1.1.0' );

function bcc_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'bcc_setup' );

function bcc_assets() {
	wp_enqueue_style( 'bcc-style', get_stylesheet_uri(), array(), BCC_VERSION );
	wp_enqueue_script( 'bcc-site', get_theme_file_uri( 'assets/js/site.js' ), array(), BCC_VERSION, true );
	wp_localize_script( 'bcc-site', 'bccSite', array( 'demo' => bcc_is_demo() ) );
}
add_action( 'wp_enqueue_scripts', 'bcc_assets', 20 );

function bcc_pattern_category() {
	register_block_pattern_category( 'beer-city-cycles', array( 'label' => 'Beer City Cycles' ) );
}
add_action( 'init', 'bcc_pattern_category' );

function bcc_is_demo() {
	return (bool) get_option( 'bcc_demo_mode' );
}

function bcc_img( $file ) {
	return get_theme_file_uri( 'assets/images/' . $file );
}

function bcc_favicon() {
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="' . esc_url( bcc_img( 'icon-512.png' ) ) . '" sizes="512x512">' . "\n";
	}
}
add_action( 'wp_head', 'bcc_favicon' );

/*
 * In block templates WordPress expands shortcodes first and then runs the Shortcode block, which
 * adds paragraph and line break tags to the finished markup. Hand back the markup untouched.
 */
function bcc_shortcode_block_raw( $content, $block ) {
	$raw = isset( $block['innerHTML'] ) ? trim( $block['innerHTML'] ) : '';
	if ( '' !== $raw && '[' !== $raw[0] && false !== strpos( $raw, 'bcc-' ) ) {
		return $raw;
	}
	return $content;
}
add_filter( 'render_block_core/shortcode', 'bcc_shortcode_block_raw', 10, 2 );

require get_theme_file_path( 'inc/settings.php' );
require get_theme_file_path( 'inc/events.php' );
require get_theme_file_path( 'inc/store.php' );
require get_theme_file_path( 'inc/shipping.php' );
require get_theme_file_path( 'inc/contact.php' );
require get_theme_file_path( 'inc/demo.php' );
require get_theme_file_path( 'inc/seed.php' );
