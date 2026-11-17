<?php
/**
 * Contact form. [bcc_contact] prints the form; messages are emailed to the address in Settings > Beer City.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bcc_contact_topics() {
	return array( 'General', 'Merch or an order', 'Events, vendors and sponsors', 'Rides' );
}

function bcc_contact_handle() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['bcc_contact_nonce'] ) ) {
		return;
	}
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/contact/' );
	$back = remove_query_arg( 'bcc_contact', $back );
	$fail = function ( $code ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'bcc_contact', $code, $back ) . '#contact-form' );
		exit;
	};
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bcc_contact_nonce'] ) ), 'bcc_contact' ) ) {
		$fail( 'expired' );
	}
	// Honeypot: real people never see or fill this field.
	if ( ! empty( $_POST['bcc_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'bcc_contact', 'sent', $back ) . '#contact-form' );
		exit;
	}
	$name    = sanitize_text_field( wp_unslash( $_POST['bcc_name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['bcc_from'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['bcc_phone'] ?? '' ) );
	$topic   = sanitize_text_field( wp_unslash( $_POST['bcc_topic'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['bcc_message'] ?? '' ) );
	if ( ! in_array( $topic, bcc_contact_topics(), true ) ) {
		$topic = 'General';
	}
	if ( ! $name || ! is_email( $email ) || strlen( $message ) < 2 ) {
		$fail( 'missing' );
	}
	$body  = "New message from the Beer City Cycles website\n\n";
	$body .= "Name: $name\nEmail: $email\n" . ( $phone ? "Phone: $phone\n" : '' ) . "About: $topic\n\n$message\n";
	$sent  = wp_mail(
		bcc_email(),
		'[Beer City Cycles] ' . $topic . ' from ' . $name,
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);
	wp_safe_redirect( add_query_arg( 'bcc_contact', $sent ? 'sent' : 'failed', $back ) . '#contact-form' );
	exit;
}
add_action( 'template_redirect', 'bcc_contact_handle' );

function bcc_contact_shortcode() {
	$state = isset( $_GET['bcc_contact'] ) ? sanitize_key( wp_unslash( $_GET['bcc_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$out   = '<div class="bcc-contact" id="contact-form">';
	if ( 'sent' === $state ) {
		$out .= '<div class="bcc-notice is-ok" role="status"><strong>Message sent.</strong> We will get back to you soon.' . ( bcc_is_demo() ? ' <em>Demo preview: the email was captured in Dashboard &gt; Demo Outbox instead of being sent.</em>' : '' ) . '</div>';
	} elseif ( 'missing' === $state ) {
		$out .= '<div class="bcc-notice is-bad" role="alert">Fill in your name, a working email and a message, then send it again.</div>';
	} elseif ( 'expired' === $state || 'failed' === $state ) {
		$out .= '<div class="bcc-notice is-bad" role="alert">That did not go through. Try again, or message us on Instagram.</div>';
	}
	$out .= '<form class="bcc-form" method="post" action="' . esc_url( get_permalink() ) . '">';
	$out .= wp_nonce_field( 'bcc_contact', 'bcc_contact_nonce', true, false );
	$out .= '<p class="bcc-field"><label for="bcc_name">Name</label><input type="text" id="bcc_name" name="bcc_name" autocomplete="name" required></p>';
	$out .= '<p class="bcc-field"><label for="bcc_from">Email</label><input type="email" id="bcc_from" name="bcc_from" autocomplete="email" required></p>';
	$out .= '<p class="bcc-field"><label for="bcc_phone">Phone <span>(optional)</span></label><input type="tel" id="bcc_phone" name="bcc_phone" autocomplete="tel"></p>';
	$out .= '<p class="bcc-field"><label for="bcc_topic">What is it about?</label><select id="bcc_topic" name="bcc_topic">';
	foreach ( bcc_contact_topics() as $t ) {
		$out .= '<option>' . esc_html( $t ) . '</option>';
	}
	$out .= '</select></p>';
	$out .= '<p class="bcc-field bcc-field-wide"><label for="bcc_message">Message</label><textarea id="bcc_message" name="bcc_message" rows="6" required></textarea></p>';
	$out .= '<p class="bcc-hp" aria-hidden="true"><label>Leave this empty<input type="text" name="bcc_website" tabindex="-1" autocomplete="off"></label></p>';
	$out .= '<p class="bcc-field bcc-field-wide"><button type="submit" class="wp-element-button">Send it</button></p>';
	return $out . '</form></div>';
}
add_shortcode( 'bcc_contact', 'bcc_contact_shortcode' );
