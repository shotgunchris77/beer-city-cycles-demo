<?php
/**
 * Merch store glue for WooCommerce.
 *
 * Shortcodes:
 *   [bcc_merch]            product grid with a per-size availability strip
 *   [bcc_merch count="4"]  limit how many show
 *   [bcc_product]          the single product layout (used by templates/single-product.html)
 *   [bcc_cart_link]        cart link with item count for the header
 *
 * Inventory is WooCommerce's own: each size is a variation with "Manage stock" on, so a size
 * that reaches zero cannot be added to the cart or checked out.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bcc_wc_active() {
	return class_exists( 'WooCommerce' );
}

/* One address for every store notification. */
foreach ( array( 'new_order', 'cancelled_order', 'failed_order', 'low_stock', 'no_stock', 'backorder' ) as $bcc_mail_id ) {
	add_filter(
		'woocommerce_email_recipient_' . $bcc_mail_id,
		function () {
			return bcc_email();
		}
	);
}
unset( $bcc_mail_id );

/* Leaner checkout: no company line. Phone stays so a problem with an order can be sorted out fast. */
function bcc_checkout_fields( $fields ) {
	unset( $fields['billing']['billing_company'], $fields['shipping']['shipping_company'] );
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'bcc_checkout_fields' );

/* Sizes for a product: array of [ label, in_stock, qty ] in the order set on the product. */
function bcc_product_sizes( $product ) {
	if ( ! $product || ! $product->is_type( 'variable' ) ) {
		return array();
	}
	$order = array();
	foreach ( $product->get_attributes() as $attr ) {
		if ( $attr->get_variation() ) {
			$order = array_merge( $order, $attr->get_options() );
		}
	}
	$sizes = array();
	foreach ( $product->get_children() as $vid ) {
		$v = wc_get_product( $vid );
		if ( ! $v || 'publish' !== $v->get_status() ) {
			continue;
		}
		$label   = implode( ' / ', array_filter( $v->get_attributes() ) );
		$sizes[] = array(
			'label' => $label,
			'in'    => $v->is_in_stock() && $v->is_purchasable(),
			'qty'   => $v->get_stock_quantity(),
			'pos'   => false === array_search( $label, $order, true ) ? 999 : array_search( $label, $order, true ),
		);
	}
	usort(
		$sizes,
		function ( $a, $b ) {
			return $a['pos'] <=> $b['pos'];
		}
	);
	return $sizes;
}

function bcc_size_strip( $product ) {
	$sizes = bcc_product_sizes( $product );
	if ( ! $sizes ) {
		return '';
	}
	$out = '<ul class="bcc-sizes" aria-label="Sizes">';
	foreach ( $sizes as $s ) {
		$out .= '<li class="' . ( $s['in'] ? '' : 'is-out' ) . '"' . ( $s['in'] ? '' : ' title="Sold out"' ) . '>' . esc_html( $s['label'] ) . ( $s['in'] ? '' : '<span class="screen-reader-text"> sold out</span>' ) . '</li>';
	}
	return $out . '</ul>';
}

function bcc_merch_shortcode( $atts ) {
	if ( ! bcc_wc_active() ) {
		return '<p class="bcc-empty">The merch store needs the WooCommerce plugin. Install and activate it under Plugins.</p>';
	}
	$atts     = shortcode_atts( array( 'count' => -1 ), $atts );
	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => (int) $atts['count'],
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		)
	);
	if ( ! $products ) {
		return '<p class="bcc-empty">New merch is on the way. Watch <a href="' . esc_url( bcc_instagram_url() ) . '" target="_blank" rel="noopener">' . esc_html( bcc_instagram_handle() ) . '</a> for drops.</p>';
	}
	$out = '<div class="bcc-merch">';
	foreach ( $products as $p ) {
		$url  = $p->get_permalink();
		$out .= '<article class="bcc-card' . ( $p->is_in_stock() ? '' : ' is-soldout' ) . '">';
		$out .= '<a class="bcc-card-img" href="' . esc_url( $url ) . '">' . $p->get_image( 'large', array( 'loading' => 'lazy', 'alt' => $p->get_name() ) ) . ( $p->is_in_stock() ? '' : '<span class="bcc-badge">Sold out</span>' ) . '</a>';
		$out .= '<div class="bcc-card-body"><h3 class="bcc-card-title"><a href="' . esc_url( $url ) . '">' . esc_html( $p->get_name() ) . '</a></h3>';
		$out .= '<p class="bcc-card-price">' . wp_kses_post( $p->get_price_html() ) . '</p>';
		$out .= bcc_size_strip( $p );
		$out .= '</div></article>';
	}
	return $out . '</div>';
}
add_shortcode( 'bcc_merch', 'bcc_merch_shortcode' );

function bcc_product_shortcode() {
	if ( ! bcc_wc_active() ) {
		return '';
	}
	$p = wc_get_product( get_queried_object_id() );
	if ( ! $p ) {
		return '';
	}
	global $product;
	$prev    = $product;
	$product = $p; // The WooCommerce add-to-cart templates read this global.

	$ids = array_filter( array_merge( array( $p->get_image_id() ), $p->get_gallery_image_ids() ) );

	ob_start();
	echo '<div class="bcc-product">';
	ob_start();
	woocommerce_output_all_notices();
	$notices = trim( ob_get_clean() );
	if ( '' !== trim( wp_strip_all_tags( $notices ) ) ) {
		echo '<div class="bcc-product-notices">' . $notices . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '<div class="bcc-product-media">';
	if ( $ids ) {
		echo '<div class="bcc-product-main">' . wp_get_attachment_image( $ids[0], 'full', false, array( 'id' => 'bcc-product-img', 'alt' => $p->get_name() ) ) . '</div>';
		if ( count( $ids ) > 1 ) {
			echo '<div class="bcc-product-thumbs">';
			foreach ( array_values( $ids ) as $i => $aid ) {
				$full = wp_get_attachment_image_url( $aid, 'full' );
				echo '<button type="button" class="bcc-thumb' . ( 0 === $i ? ' is-on' : '' ) . '" data-full="' . esc_url( $full ) . '" aria-label="Show image ' . esc_attr( $i + 1 ) . '">' . wp_get_attachment_image( $aid, 'medium', false, array( 'alt' => '' ) ) . '</button>';
			}
			echo '</div>';
		}
	}
	echo '</div>';
	echo '<div class="bcc-product-info">';
	echo '<p class="bcc-back"><a href="' . esc_url( home_url( '/merch/' ) ) . '">&larr; All merch</a></p>';
	echo '<h1 class="bcc-product-title">' . esc_html( $p->get_name() ) . '</h1>';
	echo '<p class="bcc-product-price">' . wp_kses_post( $p->get_price_html() ) . '</p>';
	if ( $p->get_short_description() ) {
		echo '<div class="bcc-product-desc">' . wp_kses_post( wpautop( $p->get_short_description() ) ) . '</div>';
	}
	if ( $p->is_in_stock() ) {
		woocommerce_template_single_add_to_cart();
	} else {
		echo '<p class="bcc-soldout-note">Sold out. Watch <a href="' . esc_url( bcc_instagram_url() ) . '" target="_blank" rel="noopener">' . esc_html( bcc_instagram_handle() ) . '</a> for the next run.</p>';
	}
	echo '<ul class="bcc-product-points"><li>Ships from Milwaukee by USPS Ground Advantage. Shipping is worked out at checkout from your ZIP code.</li><li>U.S. orders only for now.</li><li>Questions on sizing or an order? <a href="' . esc_url( home_url( '/contact/' ) ) . '">Get in touch</a>.</li></ul>';
	if ( $p->get_description() ) {
		echo '<div class="bcc-product-long">' . wp_kses_post( wpautop( $p->get_description() ) ) . '</div>';
	}
	echo '</div></div>';

	$product = $prev;
	return ob_get_clean();
}
add_shortcode( 'bcc_product', 'bcc_product_shortcode' );

function bcc_cart_count() {
	return ( bcc_wc_active() && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
}

function bcc_cart_link_shortcode() {
	if ( ! bcc_wc_active() ) {
		return '';
	}
	$n = bcc_cart_count();
	return '<a class="bcc-cart-link" href="' . esc_url( wc_get_cart_url() ) . '" aria-label="Cart, ' . esc_attr( $n ) . ' items"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1.2 13H4.8L6 7z"/><path d="M9 7V5.5a3 3 0 0 1 6 0V7"/></svg><span class="bcc-cart-count' . ( $n ? ' has-items' : '' ) . '">' . esc_html( $n ) . '</span></a>';
}
add_shortcode( 'bcc_cart_link', 'bcc_cart_link_shortcode' );

/* The header pattern holds an empty slot; fill it with the live cart link when the page renders. */
function bcc_cart_slot( $html ) {
	if ( false === strpos( $html, 'bcc-cart-slot' ) ) {
		return $html;
	}
	return str_replace( '<span class="bcc-cart-slot"></span>', bcc_cart_link_shortcode(), $html );
}
add_filter( 'render_block_core/html', 'bcc_cart_slot' );

/* Keep the header count right when WooCommerce refreshes cart fragments. */
function bcc_cart_fragments( $fragments ) {
	$n = bcc_cart_count();
	$fragments['span.bcc-cart-count'] = '<span class="bcc-cart-count' . ( $n ? ' has-items' : '' ) . '">' . esc_html( $n ) . '</span>';
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'bcc_cart_fragments' );

/* After adding to the cart, the notice offers the cart and keeps the shopper on the product. */
add_filter( 'woocommerce_continue_shopping_redirect', function () {
	return home_url( '/merch/' );
} );
