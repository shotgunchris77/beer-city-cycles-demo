<?php
/**
 * Events & Rides.
 *
 * Post type bcc_event with a details box (date, time, venue, address, type, cost, link).
 * Shortcodes:
 *   [bcc_events]                 upcoming events and rides, soonest first
 *   [bcc_events type="ride"]     only rides (or type="event")
 *   [bcc_events count="2"]       limit how many show
 *   [bcc_events past="1"]        past ones, newest first
 *   [bcc_event_single]           the full layout on a single event page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bcc_event_fields() {
	return array( 'bcc_date', 'bcc_start', 'bcc_end', 'bcc_venue', 'bcc_address', 'bcc_type', 'bcc_cost', 'bcc_link' );
}

function bcc_register_events() {
	register_post_type(
		'bcc_event',
		array(
			'labels'        => array(
				'name'          => 'Events & Rides',
				'singular_name' => 'Event',
				'add_new_item'  => 'Add New Event or Ride',
				'edit_item'     => 'Edit Event',
				'all_items'     => 'All Events & Rides',
				'menu_name'     => 'Events & Rides',
			),
			'public'        => true,
			'has_archive'   => false,
			'rewrite'       => array( 'slug' => 'event' ),
			'menu_icon'     => 'dashicons-calendar-alt',
			'menu_position' => 5,
			'show_in_rest'  => true,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		)
	);
	foreach ( bcc_event_fields() as $key ) {
		register_post_meta(
			'bcc_event',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'bcc_register_events' );

function bcc_event_metabox() {
	add_meta_box( 'bcc_event_details', 'Event details', 'bcc_event_metabox_html', 'bcc_event', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'bcc_event_metabox' );

function bcc_event_metabox_html( $post ) {
	wp_nonce_field( 'bcc_event_save', 'bcc_event_nonce' );
	$v = function ( $k ) use ( $post ) {
		return esc_attr( get_post_meta( $post->ID, $k, true ) );
	};
	$type = get_post_meta( $post->ID, 'bcc_type', true );
	echo '<p><label><strong>Type</strong><br><select name="bcc_type" style="width:100%"><option value="event"' . selected( $type, 'event', false ) . '>Event</option><option value="ride"' . selected( $type, 'ride', false ) . '>Ride</option></select></label></p>';
	echo '<p><label><strong>Date</strong><br><input type="date" name="bcc_date" value="' . $v( 'bcc_date' ) . '" style="width:100%"></label></p>';
	echo '<p><label><strong>Start time</strong> (rides: kickstands up)<br><input type="time" name="bcc_start" value="' . $v( 'bcc_start' ) . '" style="width:100%"></label></p>';
	echo '<p><label><strong>End time</strong> (optional)<br><input type="time" name="bcc_end" value="' . $v( 'bcc_end' ) . '" style="width:100%"></label></p>';
	echo '<p><label><strong>Venue or meet up spot</strong><br><input type="text" name="bcc_venue" value="' . $v( 'bcc_venue' ) . '" style="width:100%"></label></p>';
	echo '<p><label><strong>Address</strong><br><input type="text" name="bcc_address" value="' . $v( 'bcc_address' ) . '" style="width:100%"></label></p>';
	echo '<p><label><strong>Cost</strong> (optional, e.g. Free admission)<br><input type="text" name="bcc_cost" value="' . $v( 'bcc_cost' ) . '" style="width:100%"></label></p>';
	echo '<p><label><strong>Link</strong> (optional, tickets or more info)<br><input type="url" name="bcc_link" value="' . $v( 'bcc_link' ) . '" style="width:100%"></label></p>';
	echo '<p class="description">Set the poster as the Featured image. The short summary goes in Excerpt.</p>';
}

function bcc_event_save( $post_id ) {
	if ( ! isset( $_POST['bcc_event_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bcc_event_nonce'] ) ), 'bcc_event_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( bcc_event_fields() as $k ) {
		if ( ! isset( $_POST[ $k ] ) ) {
			continue;
		}
		$val = wp_unslash( $_POST[ $k ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized below.
		update_post_meta( $post_id, $k, 'bcc_link' === $k ? esc_url_raw( $val ) : sanitize_text_field( $val ) );
	}
}
add_action( 'save_post_bcc_event', 'bcc_event_save' );

add_filter(
	'manage_bcc_event_posts_columns',
	function ( $cols ) {
		$cols['bcc_date'] = 'Event date';
		$cols['bcc_type'] = 'Type';
		return $cols;
	}
);
add_action(
	'manage_bcc_event_posts_custom_column',
	function ( $col, $id ) {
		if ( 'bcc_date' === $col ) {
			echo esc_html( bcc_fmt_date( get_post_meta( $id, 'bcc_date', true ) ) );
		}
		if ( 'bcc_type' === $col ) {
			echo 'ride' === get_post_meta( $id, 'bcc_type', true ) ? 'Ride' : 'Event';
		}
	},
	10,
	2
);

function bcc_fmt_time( $t ) {
	if ( ! $t ) {
		return '';
	}
	$ts = strtotime( '2000-01-01 ' . $t );
	return $ts ? str_replace( ':00', '', gmdate( 'g:i A', $ts ) ) : $t;
}

function bcc_fmt_date( $d, $format = 'l, F j, Y' ) {
	$ts = $d ? strtotime( $d . ' 12:00:00' ) : false;
	return $ts ? gmdate( $format, $ts ) : '';
}

function bcc_time_range( $id ) {
	$s = bcc_fmt_time( get_post_meta( $id, 'bcc_start', true ) );
	$e = bcc_fmt_time( get_post_meta( $id, 'bcc_end', true ) );
	if ( ! $s ) {
		return '';
	}
	return $e ? $s . ' to ' . $e : $s;
}

function bcc_maps_url( $id ) {
	$q = trim( get_post_meta( $id, 'bcc_venue', true ) . ' ' . get_post_meta( $id, 'bcc_address', true ) );
	return $q ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $q ) : '';
}

function bcc_get_events( $args = array() ) {
	$args  = wp_parse_args( $args, array( 'count' => -1, 'type' => '', 'past' => false ) );
	$today = wp_date( 'Y-m-d' );
	$meta  = array(
		array(
			'key'     => 'bcc_date',
			'value'   => $today,
			'compare' => $args['past'] ? '<' : '>=',
			'type'    => 'DATE',
		),
	);
	if ( $args['type'] ) {
		$meta[] = array( 'key' => 'bcc_type', 'value' => $args['type'] );
	}
	return get_posts(
		array(
			'post_type'      => 'bcc_event',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $args['count'],
			'meta_key'       => 'bcc_date',
			'orderby'        => 'meta_value',
			'order'          => $args['past'] ? 'DESC' : 'ASC',
			'meta_query'     => $meta,
		)
	);
}

function bcc_event_datebox( $id ) {
	$d = get_post_meta( $id, 'bcc_date', true );
	return '<span class="bcc-datebox"><span class="m">' . esc_html( bcc_fmt_date( $d, 'M' ) ) . '</span><span class="d">' . esc_html( bcc_fmt_date( $d, 'j' ) ) . '</span><span class="y">' . esc_html( bcc_fmt_date( $d, 'Y' ) ) . '</span></span>';
}

function bcc_event_facts( $id ) {
	$out   = '<dl class="bcc-facts">';
	$date  = bcc_fmt_date( get_post_meta( $id, 'bcc_date', true ) );
	$time  = bcc_time_range( $id );
	$venue = get_post_meta( $id, 'bcc_venue', true );
	$addr  = get_post_meta( $id, 'bcc_address', true );
	$cost  = get_post_meta( $id, 'bcc_cost', true );
	if ( $date ) {
		$out .= '<div><dt>When</dt><dd>' . esc_html( $date ) . ( $time ? '<br>' . esc_html( $time ) : '' ) . '</dd></div>';
	}
	if ( $venue || $addr ) {
		$out .= '<div><dt>Where</dt><dd>' . esc_html( $venue ) . ( $addr ? '<br><a href="' . esc_url( bcc_maps_url( $id ) ) . '" target="_blank" rel="noopener">' . esc_html( $addr ) . '</a>' : '' ) . '</dd></div>';
	}
	if ( $cost ) {
		$out .= '<div><dt>Cost</dt><dd>' . esc_html( $cost ) . '</dd></div>';
	}
	return $out . '</dl>';
}

function bcc_event_card( $ev ) {
	$id   = $ev->ID;
	$type = 'ride' === get_post_meta( $id, 'bcc_type', true ) ? 'Ride' : 'Event';
	$url  = get_permalink( $ev );
	$out  = '<article class="bcc-event">';
	if ( has_post_thumbnail( $ev ) ) {
		$out .= '<a class="bcc-event-poster" href="' . esc_url( $url ) . '">' . get_the_post_thumbnail( $ev, 'large', array( 'loading' => 'lazy', 'alt' => get_the_title( $ev ) . ' poster' ) ) . '</a>';
	}
	$out .= '<div class="bcc-event-body"><div class="bcc-event-head">' . bcc_event_datebox( $id ) . '<div><span class="bcc-tag">' . esc_html( $type ) . '</span><h3 class="bcc-event-title"><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $ev ) ) . '</a></h3></div></div>';
	if ( has_excerpt( $ev ) ) {
		$out .= '<p class="bcc-event-excerpt">' . esc_html( get_the_excerpt( $ev ) ) . '</p>';
	}
	$out .= bcc_event_facts( $id );
	$out .= '<div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $url ) . '">Details</a></div>';
	if ( bcc_maps_url( $id ) ) {
		$out .= '<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . esc_url( bcc_maps_url( $id ) ) . '" target="_blank" rel="noopener">Directions</a></div>';
	}
	return $out . '</div></div></article>';
}

function bcc_events_shortcode( $atts ) {
	$atts   = shortcode_atts( array( 'count' => -1, 'type' => '', 'past' => '', 'empty' => '' ), $atts );
	$events = bcc_get_events( array( 'count' => $atts['count'], 'type' => $atts['type'], 'past' => (bool) $atts['past'] ) );
	if ( ! $events ) {
		if ( $atts['past'] ) {
			return '';
		}
		$msg = $atts['empty'] ? $atts['empty'] : 'Nothing on the books right now. New dates get posted here and on Instagram first.';
		return '<p class="bcc-empty">' . esc_html( $msg ) . ' <a href="' . esc_url( bcc_instagram_url() ) . '" target="_blank" rel="noopener">' . esc_html( bcc_instagram_handle() ) . '</a></p>';
	}
	$out = '<div class="bcc-events">';
	foreach ( $events as $ev ) {
		$out .= bcc_event_card( $ev );
	}
	return $out . '</div>';
}
add_shortcode( 'bcc_events', 'bcc_events_shortcode' );

/**
 * "On The Bill" box for the home page: the next few dated events as a show bill.
 */
function bcc_bill_shortcode( $atts ) {
	$atts   = shortcode_atts( array( 'count' => 2 ), $atts );
	$events = bcc_get_events( array( 'count' => (int) $atts['count'] ) );
	$out    = '<div class="bcc-bill"><div class="bcc-bill-in"><p class="bcc-bill-title">On The Bill</p>';
	foreach ( $events as $ev ) {
		$d     = get_post_meta( $ev->ID, 'bcc_date', true );
		$venue = get_post_meta( $ev->ID, 'bcc_venue', true );
		$start = bcc_fmt_time( get_post_meta( $ev->ID, 'bcc_start', true ) );
		$where = trim( $venue . ( $venue && $start ? ', ' : '' ) . $start );
		$out  .= '<a class="bcc-bill-row" href="' . esc_url( get_permalink( $ev ) ) . '"><span class="d">' . esc_html( bcc_fmt_date( $d, 'M j' ) ) . '</span><span class="n">' . esc_html( get_the_title( $ev ) ) . '</span>' . ( $where ? '<span class="w">' . esc_html( $where ) . '</span>' : '' ) . '</a>';
	}
	$out .= '<a class="bcc-bill-row" href="' . esc_url( bcc_instagram_url() ) . '" target="_blank" rel="noopener"><span class="n">Pop up rides</span><span class="w">Announced on Instagram ' . esc_html( bcc_instagram_handle() ) . '</span></a>';
	return $out . '</div></div>';
}
add_shortcode( 'bcc_bill', 'bcc_bill_shortcode' );

function bcc_event_single_shortcode() {
	$id = get_queried_object_id();
	if ( ! $id || 'bcc_event' !== get_post_type( $id ) ) {
		return '';
	}
	$ev   = get_post( $id );
	$type = 'ride' === get_post_meta( $id, 'bcc_type', true ) ? 'Ride' : 'Event';
	$out  = '<div class="bcc-event-single">';
	if ( has_post_thumbnail( $ev ) ) {
		$out .= '<div class="bcc-event-single-poster">' . get_the_post_thumbnail( $ev, 'full', array( 'alt' => get_the_title( $ev ) . ' poster' ) ) . '</div>';
	}
	$out .= '<div class="bcc-event-single-body"><p class="bcc-back"><a href="' . esc_url( home_url( '/events/' ) ) . '">&larr; All events and rides</a></p>';
	$out .= '<span class="bcc-tag">' . esc_html( $type ) . '</span><h1 class="bcc-event-single-title">' . esc_html( get_the_title( $ev ) ) . '</h1>';
	$out .= bcc_event_facts( $id );
	$out .= '<div class="bcc-event-content">' . apply_filters( 'the_content', $ev->post_content ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	$out .= '<div class="wp-block-buttons">';
	$link = get_post_meta( $id, 'bcc_link', true );
	if ( $link ) {
		$out .= '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $link ) . '" target="_blank" rel="noopener">More info</a></div>';
	}
	if ( bcc_maps_url( $id ) ) {
		$out .= '<div class="wp-block-button' . ( $link ? ' is-style-outline' : '' ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( bcc_maps_url( $id ) ) . '" target="_blank" rel="noopener">Directions</a></div>';
	}
	$out .= '<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . esc_url( home_url( '/contact/' ) ) . '">Vendor or sponsor? Get in touch</a></div>';
	return $out . '</div></div></div>';
}
add_shortcode( 'bcc_event_single', 'bcc_event_single_shortcode' );
