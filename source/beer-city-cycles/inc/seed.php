<?php
/**
 * Starter content: pages, the 2027 events, sample merch and store settings.
 *
 * Runs once from the demo blueprint, or from Settings > Beer City > "Load starter content" on a
 * fresh install. Safe to run again: it updates what it made before instead of duplicating it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Copy a theme image into the Media Library once and return its attachment ID. */
function bcc_seed_image( $file, $title ) {
	$found = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_bcc_seed_src',
			'meta_value'  => $file,
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $found ) {
		return (int) $found[0];
	}
	$src = get_theme_file_path( 'assets/images/' . $file );
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	$up = wp_upload_dir();
	wp_mkdir_p( $up['path'] );
	$name = wp_unique_filename( $up['path'], 'beer-city-cycles-' . $file );
	$dest = trailingslashit( $up['path'] ) . $name;
	if ( ! copy( $src, $dest ) ) {
		return 0;
	}
	$type = wp_check_filetype( $name );
	$id   = wp_insert_attachment(
		array(
			'post_mime_type' => $type['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$dest
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	$size = getimagesize( $dest );
	wp_update_attachment_metadata(
		$id,
		array(
			'width'  => $size ? $size[0] : 0,
			'height' => $size ? $size[1] : 0,
			'file'   => ltrim( str_replace( $up['basedir'], '', $dest ), '/' ),
			'sizes'  => array(),
		)
	);
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	update_post_meta( $id, '_bcc_seed_src', $file );
	return (int) $id;
}

function bcc_seed_page( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug );
	$data     = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $content,
	);
	if ( $existing ) {
		$data['ID'] = $existing->ID;
		return (int) wp_update_post( $data );
	}
	return (int) wp_insert_post( $data );
}

function bcc_seed_events() {
	$events = array(
		array(
			'slug'    => 'unofficial-mama-tried-after-party-2027',
			'title'   => 'Unofficial Mama Tried After Party',
			'excerpt' => 'Presented by Beer City Cycles and Not Bad Foods. Free keg, food from Not Bad, tattoos by Jan Michel, DJs and Beer City merch. Free admission.',
			'content' => '<!-- wp:paragraph --><p>Presented by Beer City Cycles and Not Bad Foods. When the show lets out, the party moves to Bad Moon Saloon.</p><!-- /wp:paragraph --><!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Free keg</li><!-- /wp:list-item --><!-- wp:list-item --><li>Food from Not Bad</li><!-- /wp:list-item --><!-- wp:list-item --><li>Tattoos by Jan Michel</li><!-- /wp:list-item --><!-- wp:list-item --><li>DJs</li><!-- /wp:list-item --><!-- wp:list-item --><li>Beer City merch</li><!-- /wp:list-item --><!-- wp:list-item --><li>Free admission</li><!-- /wp:list-item --></ul><!-- /wp:list -->',
			'meta'    => array( 'bcc_date' => '2027-02-20', 'bcc_start' => '19:00', 'bcc_end' => '', 'bcc_venue' => 'Bad Moon Saloon', 'bcc_address' => '4035 S Clement Ave, Milwaukee, WI 53207', 'bcc_type' => 'event', 'bcc_cost' => 'Free admission', 'bcc_link' => '' ),
			'image'   => 'poster-after-party.jpg',
		),
		array(
			'slug'    => 'bike-show-and-bbq-2027',
			'title'   => 'Beer City Cycles Bike Show & BBQ',
			'excerpt' => 'The annual ride in bike show and BBQ at Puddlers Hall. Choppers, vintage iron and performance Dynas and FXRs.',
			'content' => '<!-- wp:paragraph --><p>The annual Beer City Cycles ride in bike show and BBQ at Puddlers Hall in Bay View. Choppers, vintage iron and performance Dynas and FXRs. Ride it in, park it and grab a plate.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Want a vendor spot or to sponsor the show? Send us a message through the contact page.</p><!-- /wp:paragraph -->',
			'meta'    => array( 'bcc_date' => '2027-06-12', 'bcc_start' => '11:00', 'bcc_end' => '16:00', 'bcc_venue' => 'Puddlers Hall', 'bcc_address' => '2461 S St Clair St, Milwaukee, WI 53207', 'bcc_type' => 'event', 'bcc_cost' => '', 'bcc_link' => '' ),
			'image'   => 'poster-bike-show.jpg',
		),
	);
	foreach ( $events as $e ) {
		$existing = get_page_by_path( $e['slug'], OBJECT, 'bcc_event' );
		$data     = array(
			'post_type'    => 'bcc_event',
			'post_status'  => 'publish',
			'post_name'    => $e['slug'],
			'post_title'   => $e['title'],
			'post_excerpt' => $e['excerpt'],
			'post_content' => $e['content'],
		);
		if ( $existing ) {
			$data['ID'] = $existing->ID;
		}
		$id = wp_insert_post( $data );
		foreach ( $e['meta'] as $k => $v ) {
			update_post_meta( $id, $k, $v );
		}
		$img = bcc_seed_image( $e['image'], $e['title'] . ' poster' );
		if ( $img ) {
			set_post_thumbnail( $id, $img );
		}
	}
}

/** Sample merch. Prices, sizes, weights and stock counts are placeholders to be replaced. */
function bcc_seed_products() {
	if ( ! bcc_wc_active() ) {
		return;
	}
	$note     = 'Sample listing. Price, sizes and stock counts are placeholders until the real numbers are entered.';
	$sizes    = array( 'S', 'M', 'L', 'XL', '2XL', '3XL' );
	$weights  = array( 'S' => 5, 'M' => 6, 'L' => 6, 'XL' => 7, '2XL' => 8, '3XL' => 9 ); // ounces
	$products = array(
		array( 'premium-choppers-tee', 'Premium Choppers Tee', 'The Beer City Cycles logo. Pin up, chopper and a cold one. Milwaukee, WI.', array( 'premium-choppers.jpg' ), array( 6, 10, 10, 8, 4, 2 ) ),
		array( 'eagle-skyline-tee', 'Eagle Skyline Tee', 'Eagle over the Milwaukee skyline with the American Made shield.', array( 'eagle-skyline.jpg' ), array( 5, 8, 8, 6, 3, 0 ) ),
		array( 'chrome-reaper-tee', 'Chrome Reaper Tee', 'Chrome lettering, the reaper and a long chopper in the lightning.', array( 'chrome-reaper.jpg' ), array( 4, 8, 8, 6, 2, 2 ) ),
		array( 'demon-chopper-tee', 'Demon Chopper Tee', 'Flame tank chopper with the horned one watching over it.', array( 'demon-chopper.jpg' ), array( 0, 6, 8, 6, 3, 1 ) ),
		array( 'ready-to-hunt-tee', 'Ready To Hunt Tee', 'Blaze orange hunting season design. Camo lettering, Shovelhead and antlers, with the skull and shotguns pocket print.', array( 'ready-to-hunt.jpg', 'ready-to-hunt-pocket.jpg' ), array( 3, 6, 6, 5, 2, 0 ) ),
		array( 'gone-fishing-tee', 'Gone Fishing Tee', 'Largemouth, crossed rods and an Evo. Gone fishing.', array( 'gone-fishing.jpg' ), array( 4, 6, 6, 6, 3, 2 ) ),
	);
	foreach ( $products as $i => $row ) {
		list( $slug, $name, $desc, $images, $stock ) = $row;
		$pid = wc_get_product_id_by_sku( 'BCC-' . strtoupper( $slug ) );
		$p   = $pid ? wc_get_product( $pid ) : new WC_Product_Variable();
		if ( ! $p || ! $p->is_type( 'variable' ) ) {
			continue;
		}
		$attr = new WC_Product_Attribute();
		$attr->set_name( 'Size' );
		$attr->set_options( $sizes );
		$attr->set_position( 0 );
		$attr->set_visible( true );
		$attr->set_variation( true );

		$img_ids = array();
		foreach ( $images as $file ) {
			$img_ids[] = bcc_seed_image( $file, $name );
		}
		$img_ids = array_values( array_filter( $img_ids ) );

		$p->set_name( $name );
		$p->set_slug( $slug );
		$p->set_status( 'publish' );
		$p->set_sku( 'BCC-' . strtoupper( $slug ) );
		$p->set_short_description( $desc );
		$p->set_description( $note );
		$p->set_menu_order( $i );
		$p->set_attributes( array( $attr ) );
		if ( $img_ids ) {
			$p->set_image_id( $img_ids[0] );
			$p->set_gallery_image_ids( array_slice( $img_ids, 1 ) );
		}
		$pid = $p->save();

		if ( ! $p->get_children() ) {
			foreach ( $sizes as $n => $size ) {
				$v = new WC_Product_Variation();
				$v->set_parent_id( $pid );
				$v->set_attributes( array( 'size' => $size ) );
				$v->set_sku( 'BCC-' . strtoupper( $slug ) . '-' . $size );
				$v->set_regular_price( '25' );
				$v->set_weight( $weights[ $size ] );
				$v->set_manage_stock( true );
				$v->set_stock_quantity( $stock[ $n ] );
				$v->set_backorders( 'no' );
				$v->set_status( 'publish' );
				$v->save();
			}
		}
		WC_Product_Variable::sync( $pid );
	}
}

function bcc_seed_store_settings() {
	if ( ! bcc_wc_active() ) {
		return;
	}
	$options = array(
		// Store basics: Milwaukee, U.S. dollars, ounces, U.S. shipping only.
		'woocommerce_store_city'                      => 'Milwaukee',
		'woocommerce_store_postcode'                  => '53207',
		'woocommerce_default_country'                 => 'US:WI',
		'woocommerce_currency'                        => 'USD',
		'woocommerce_weight_unit'                     => 'oz',
		'woocommerce_dimension_unit'                  => 'in',
		'woocommerce_allowed_countries'               => 'specific',
		'woocommerce_specific_allowed_countries'      => array( 'US' ),
		'woocommerce_ship_to_countries'               => '',
		'woocommerce_default_customer_address'        => 'base',
		'woocommerce_calc_taxes'                      => 'no',
		'woocommerce_enable_coupons'                  => 'no',
		// Inventory: track stock, stop selling at zero, show "Only N left" when a size is nearly gone.
		'woocommerce_manage_stock'                    => 'yes',
		'woocommerce_notify_low_stock'                => 'yes',
		'woocommerce_notify_no_stock'                 => 'yes',
		'woocommerce_notify_low_stock_amount'         => 2,
		'woocommerce_notify_no_stock_amount'          => 0,
		'woocommerce_hide_out_of_stock_items'         => 'no',
		'woocommerce_stock_format'                    => 'low_amount',
		'woocommerce_hold_stock_minutes'              => 60,
		// Checkout: guests welcome, no accounts needed.
		'woocommerce_enable_guest_checkout'           => 'yes',
		'woocommerce_enable_signup_and_login_from_checkout' => 'no',
		'woocommerce_enable_checkout_login_reminder'  => 'no',
		'woocommerce_cart_redirect_after_add'         => 'no',
		'woocommerce_enable_shipping_calc'            => 'yes',
		'woocommerce_shipping_cost_requires_address'  => 'yes',
		'woocommerce_ship_to_destination'             => 'billing',
		'woocommerce_checkout_privacy_policy_text'    => 'Your details are only used to fill and ship this order.',
		// Emails.
		'woocommerce_email_from_name'                 => 'Beer City Cycles',
		'woocommerce_email_base_color'                => '#B87A22',
		'woocommerce_email_footer_text'               => 'Beer City Cycles. Milwaukee, WI. Party, drink beer, put down miles.',
		'woocommerce_coming_soon'                     => 'no',
		'woocommerce_store_pages_only'                => 'no',
		'woocommerce_allow_tracking'                  => 'no',
		'woocommerce_show_marketplace_suggestions'    => 'no',
		'woocommerce_task_list_hidden'                => 'yes',
		'woocommerce_extended_task_list_hidden'       => 'yes',
		'woocommerce_onboarding_profile'              => array( 'skipped' => true ),
	);
	foreach ( $options as $k => $v ) {
		update_option( $k, $v );
	}

	// Stand-in payment method until a card gateway (Stripe, Square or PayPal) is added.
	update_option(
		'woocommerce_cheque_settings',
		array(
			'enabled'      => 'yes',
			'title'        => 'Order now, pay by payment request',
			'description'  => 'No card is charged on this site yet. Place the order and Beer City Cycles will send a payment request to your email. Your sizes are held as soon as the order is placed.',
			'instructions' => 'We got your order and your items are held. Watch your email for a payment request from Beer City Cycles. The order ships once it is paid.',
		)
	);
	foreach ( array( 'bacs', 'cod' ) as $gw ) {
		$s = get_option( 'woocommerce_' . $gw . '_settings', array() );
		if ( is_array( $s ) ) {
			$s['enabled'] = 'no';
			update_option( 'woocommerce_' . $gw . '_settings', $s );
		}
	}

	// Shipping zone: United States -> USPS Ground Advantage from Milwaukee.
	if ( class_exists( 'WC_Shipping_Zones' ) && class_exists( 'WC_Shipping_Zone' ) ) {
		$has = false;
		foreach ( WC_Shipping_Zones::get_zones() as $z ) {
			foreach ( $z['shipping_methods'] as $m ) {
				if ( 'bcc_usps_ground' === $m->id ) {
					$has = true;
				}
			}
		}
		if ( ! $has ) {
			$zone = new WC_Shipping_Zone();
			$zone->set_zone_name( 'United States' );
			$zone->add_location( 'US', 'country' );
			$zone->save();
			$zone->add_shipping_method( 'bcc_usps_ground' );
		}
	}
}

function bcc_seed( $demo = false ) {
	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	kses_remove_filters();

	if ( ! get_option( 'bcc_email' ) ) {
		update_option( 'bcc_email', 'beercitycycycles@gmail.com' );
	}
	if ( ! get_option( 'bcc_instagram' ) ) {
		update_option( 'bcc_instagram', 'https://www.instagram.com/beercitycycles/' );
	}
	if ( $demo ) {
		update_option( 'bcc_demo_mode', 1 );
		update_option( 'admin_email', get_option( 'bcc_email' ) );
	}

	// Pages built from the theme's patterns.
	$reg = WP_Block_Patterns_Registry::get_instance();
	$ids = array();
	foreach ( array( 'home' => 'Home', 'about' => 'About', 'events' => 'Events & Rides', 'contact' => 'Contact' ) as $slug => $title ) {
		$p            = $reg->get_registered( 'beer-city-cycles/' . $slug );
		$ids[ $slug ] = bcc_seed_page( $slug, $title, $p ? $p['content'] : '' );
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] );

	// Store pages. /merch/ is the shop page; cart and checkout use the classic WooCommerce forms.
	$ids['merch'] = bcc_seed_page( 'merch', 'Merch', '' );
	if ( bcc_wc_active() ) {
		$old_shop = (int) get_option( 'woocommerce_shop_page_id' );
		update_option( 'woocommerce_shop_page_id', $ids['merch'] );
		if ( $old_shop && $old_shop !== $ids['merch'] && 'shop' === get_post_field( 'post_name', $old_shop ) ) {
			wp_delete_post( $old_shop, true );
		}
		foreach ( array( 'cart' => array( 'Cart', '[woocommerce_cart]' ), 'checkout' => array( 'Checkout', '[woocommerce_checkout]' ) ) as $slug => $def ) {
			$content = '<!-- wp:shortcode -->' . $def[1] . '<!-- /wp:shortcode -->';
			$pid     = (int) get_option( 'woocommerce_' . $slug . '_page_id' );
			if ( $pid && get_post( $pid ) ) {
				wp_update_post( array( 'ID' => $pid, 'post_content' => $content, 'post_title' => $def[0], 'post_status' => 'publish' ) );
			} else {
				update_option( 'woocommerce_' . $slug . '_page_id', bcc_seed_page( $slug, $def[0], $content ) );
			}
		}
	}

	// Clear out the stock WordPress samples.
	foreach ( get_posts( array( 'post_type' => 'post', 'name' => 'hello-world', 'post_status' => 'any' ) ) as $p ) {
		wp_delete_post( $p->ID, true );
	}
	$sample = get_page_by_path( 'sample-page' );
	if ( $sample ) {
		wp_delete_post( $sample->ID, true );
	}

	bcc_seed_events();
	bcc_seed_store_settings();
	bcc_seed_products();

	update_option( 'bcc_seeded', 1 );
	delete_option( 'bcc_demo_outbox' ); // Setup notices are not part of the demo.
	flush_rewrite_rules();
}

/* Settings > Beer City: a button to load the starter content on a fresh install. */
function bcc_seed_button() {
	if ( get_option( 'bcc_seeded' ) ) {
		return;
	}
	echo '<div class="wrap"><h2>Starter content</h2><p>Creates the Home, About, Events &amp; Rides, Merch and Contact pages, the 2027 events and sample merch listings.' . ( bcc_wc_active() ? '' : ' <strong>Install and activate WooCommerce first so the store is set up too.</strong>' ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="bcc_seed">';
	wp_nonce_field( 'bcc_seed' );
	submit_button( 'Load starter content', 'secondary' );
	echo '</form></div>';
}
add_action( 'bcc_settings_after', 'bcc_seed_button' );

function bcc_seed_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'bcc_seed' );
	bcc_seed( false );
	wp_safe_redirect( admin_url( 'options-general.php?page=bcc-settings' ) );
	exit;
}
add_action( 'admin_post_bcc_seed', 'bcc_seed_action' );
