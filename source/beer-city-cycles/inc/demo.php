<?php
/**
 * Demo preview helpers. Only active when the bcc_demo_mode option is on (the demo blueprint sets it).
 * On a live site this file does nothing.
 *
 * - Emails are captured into Dashboard > Demo Outbox instead of being sent (the browser preview cannot send mail).
 * - The front-end admin bar is hidden and a slim strip links to the dashboard screens that matter.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bcc_demo_capture_mail( $short, $atts ) {
	if ( ! bcc_is_demo() ) {
		return $short;
	}
	$box = get_option( 'bcc_demo_outbox', array() );
	if ( ! is_array( $box ) ) {
		$box = array();
	}
	$headers = $atts['headers'] ?? array();
	$headers = is_array( $headers ) ? implode( "\n", $headers ) : (string) $headers;
	array_unshift(
		$box,
		array(
			'time'    => wp_date( 'M j, Y g:i:s A' ),
			'to'      => is_array( $atts['to'] ) ? implode( ', ', $atts['to'] ) : (string) $atts['to'],
			'subject' => (string) $atts['subject'],
			'body'    => (string) $atts['message'],
			'html'    => false !== stripos( $headers, 'text/html' ) || false !== stripos( (string) $atts['message'], '<html' ) || false !== stripos( (string) $atts['message'], '<table' ),
		)
	);
	update_option( 'bcc_demo_outbox', array_slice( $box, 0, 40 ), false );
	return true;
}
add_filter( 'pre_wp_mail', 'bcc_demo_capture_mail', 10, 2 );

function bcc_demo_menu() {
	if ( bcc_is_demo() ) {
		add_menu_page( 'Demo Outbox', 'Demo Outbox', 'manage_options', 'bcc-demo-outbox', 'bcc_demo_outbox_page', 'dashicons-email-alt', 3 );
	}
}
add_action( 'admin_menu', 'bcc_demo_menu' );

function bcc_demo_outbox_page() {
	$box = get_option( 'bcc_demo_outbox', array() );
	echo '<div class="wrap"><h1>Demo Outbox</h1><p>This preview cannot send real email, so every email the site would send is kept here, newest first. On the live site these go out as normal: order emails, low stock alerts and contact form messages go to <strong>' . esc_html( bcc_email() ) . '</strong> (change it under Settings &gt; Beer City), and customers get their own order confirmation.</p>';
	if ( ! $box ) {
		echo '<p><em>Nothing yet. Place a test order or send the contact form, then come back.</em></p></div>';
		return;
	}
	foreach ( $box as $m ) {
		echo '<div style="background:#fff;border:1px solid #c3c4c7;margin:16px 0;padding:14px 18px;max-width:900px">';
		echo '<p style="margin:0 0 8px"><strong>To:</strong> ' . esc_html( $m['to'] ) . ' &nbsp; <strong>Sent:</strong> ' . esc_html( $m['time'] ) . '<br><strong>Subject:</strong> ' . esc_html( $m['subject'] ) . '</p>';
		if ( ! empty( $m['html'] ) ) {
			echo '<iframe sandbox="" style="width:100%;height:520px;border:1px solid #dcdcde" srcdoc="' . esc_attr( $m['body'] ) . '"></iframe>';
		} else {
			echo '<pre style="white-space:pre-wrap;background:#f6f7f7;padding:12px;margin:0">' . esc_html( $m['body'] ) . '</pre>';
		}
		echo '</div>';
	}
	echo '</div>';
}

add_filter(
	'show_admin_bar',
	function ( $show ) {
		return ( bcc_is_demo() && ! is_admin() ) ? false : $show;
	}
);

function bcc_demo_strip() {
	if ( ! bcc_is_demo() ) {
		return;
	}
	$links = array(
		'Orders'           => admin_url( 'edit.php?post_type=shop_order' ),
		'Merch and stock'  => admin_url( 'edit.php?post_type=product' ),
		'Events and rides' => admin_url( 'edit.php?post_type=bcc_event' ),
		'Demo outbox'      => admin_url( 'admin.php?page=bcc-demo-outbox' ),
		'Settings'         => admin_url( 'options-general.php?page=bcc-settings' ),
	);
	if ( bcc_wc_active() ) {
		$links['Orders'] = admin_url( 'admin.php?page=wc-orders' );
	}
	echo '<div class="bcc-demo-strip"><span><strong>Demo preview.</strong> No cards are charged and emails are captured, not sent. Owner dashboard:</span> ';
	foreach ( $links as $label => $url ) {
		echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a> ';
	}
	echo '</div>';
}
add_action( 'wp_footer', 'bcc_demo_strip' );

/* Keep the WooCommerce setup wizard out of the way in the preview. */
add_filter(
	'woocommerce_enable_setup_wizard',
	function ( $on ) {
		return bcc_is_demo() ? false : $on;
	}
);
add_filter(
	'woocommerce_prevent_automatic_wizard_redirect',
	function ( $prevent ) {
		return bcc_is_demo() ? true : $prevent;
	}
);
