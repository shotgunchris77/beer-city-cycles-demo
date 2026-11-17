<?php
/**
 * USPS Ground Advantage shipping, calculated from Milwaukee.
 *
 * No USPS account or API key is needed. The rate is looked up the same way the post office
 * counter does it:
 *   1. Destination ZIP -> USPS zone from Milwaukee (origin ZIP prefix 532), from data/usps-zones-532.json.
 *   2. Package weight + zone -> USPS Ground Advantage retail price, from data/usps-rates.json.
 *
 * usps-rates.json can hold several price tables with an "effective" date. The newest table whose
 * date has arrived is used, so a future price change can be loaded ahead of time.
 * Source for both files: https://pe.usps.com/pricechange/index and https://postcalc.usps.com/DomesticZoneChart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bcc_usps_data( $file ) {
	static $cache = array();
	if ( ! isset( $cache[ $file ] ) ) {
		$raw            = file_get_contents( get_theme_file_path( 'data/' . $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$cache[ $file ] = $raw ? json_decode( $raw, true ) : array();
	}
	return $cache[ $file ];
}

/** USPS zone (1-9) from Milwaukee for a destination ZIP. 0 when the ZIP is not a valid U.S. ZIP. */
function bcc_usps_zone( $zip, $oz = 0 ) {
	$zip = preg_replace( '/\D/', '', (string) $zip );
	if ( strlen( $zip ) < 5 ) {
		return 0;
	}
	$zip5 = (int) substr( $zip, 0, 5 );
	$zip3 = (int) substr( $zip, 0, 3 );
	$data = bcc_usps_data( 'usps-zones-532.json' );
	foreach ( (array) ( $data['zip5'] ?? array() ) as $r ) {
		// Fourth value 1 = exception only applies to packages under 16 oz (military mail).
		if ( $zip5 >= $r[0] && $zip5 <= $r[1] && ( empty( $r[3] ) || $oz < 16 ) ) {
			return (int) $r[2];
		}
	}
	foreach ( (array) ( $data['zip3'] ?? array() ) as $r ) {
		if ( $zip3 >= $r[0] && $zip3 <= $r[1] ) {
			return (int) $r[2];
		}
	}
	return 0;
}

/** The price table in force on a date (Y-m-d). */
function bcc_usps_table( $date = '' ) {
	$date = $date ? $date : wp_date( 'Y-m-d' );
	$data = bcc_usps_data( 'usps-rates.json' );
	$pick = null;
	foreach ( (array) ( $data['tables'] ?? array() ) as $t ) {
		if ( $t['effective'] <= $date && ( ! $pick || $t['effective'] > $pick['effective'] ) ) {
			$pick = $t;
		}
	}
	return $pick;
}

/** Retail price for a package of $oz ounces to a zone. Null when it cannot be priced. */
function bcc_usps_rate( $oz, $zone, $date = '' ) {
	$table = bcc_usps_table( $date );
	$zone  = (int) $zone;
	if ( ! $table || $zone < 1 || $zone > 9 || $oz <= 0 ) {
		return null;
	}
	if ( $oz < 16 ) {
		foreach ( array( '4', '8', '12', '15.999' ) as $tier ) {
			if ( $oz <= (float) $tier && isset( $table['oz'][ $tier ] ) ) {
				return (float) $table['oz'][ $tier ][ $zone - 1 ];
			}
		}
	}
	$lb = (string) max( 1, (int) ceil( $oz / 16 ) );
	return isset( $table['lb'][ $lb ] ) ? (float) $table['lb'][ $lb ][ $zone - 1 ] : null;
}

function bcc_shipping_init() {
	if ( class_exists( 'BCC_USPS_Ground' ) || ! class_exists( 'WC_Shipping_Method' ) ) {
		return;
	}

	class BCC_USPS_Ground extends WC_Shipping_Method {

		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'bcc_usps_ground';
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = 'USPS Ground Advantage (from Milwaukee)';
			$this->method_description = 'Charges the USPS Ground Advantage retail price for the cart weight and the customer\'s ZIP code, measured from Milwaukee (ZIP 532). Prices come from the rate tables in the theme\'s data folder.';
			$this->supports           = array( 'shipping-zones', 'instance-settings' );
			$this->init();
		}

		public function init() {
			$this->instance_form_fields = array(
				'title'        => array(
					'title'   => 'Name shown at checkout',
					'type'    => 'text',
					'default' => 'USPS Ground Advantage',
				),
				'packaging_oz' => array(
					'title'       => 'Packaging weight (oz)',
					'type'        => 'number',
					'description' => 'Added once per order for the mailer or box.',
					'default'     => '1',
					'custom_attributes' => array( 'step' => '0.1', 'min' => '0' ),
				),
				'default_oz'   => array(
					'title'       => 'Weight for items with no weight set (oz)',
					'type'        => 'number',
					'description' => 'Used when a product or size has no weight entered.',
					'default'     => '7',
					'custom_attributes' => array( 'step' => '0.1', 'min' => '0' ),
				),
				'handling'     => array(
					'title'       => 'Handling fee ($)',
					'type'        => 'number',
					'description' => 'Optional flat amount added on top of the USPS price.',
					'default'     => '0',
					'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ),
				),
			);
			$this->title = $this->get_option( 'title', 'USPS Ground Advantage' );
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function package_ounces( $package ) {
			$default = (float) $this->get_option( 'default_oz', 7 );
			$oz      = 0.0;
			foreach ( $package['contents'] as $item ) {
				if ( empty( $item['data'] ) || ! $item['data']->needs_shipping() ) {
					continue;
				}
				$w   = (float) $item['data']->get_weight();
				$oz += ( $w > 0 ? (float) wc_get_weight( $w, 'oz' ) : $default ) * (int) $item['quantity'];
			}
			return $oz > 0 ? $oz + (float) $this->get_option( 'packaging_oz', 1 ) : 0.0;
		}

		public function calculate_shipping( $package = array() ) {
			$country = $package['destination']['country'] ?? '';
			$zip     = $package['destination']['postcode'] ?? '';
			if ( 'US' !== $country || ! $zip ) {
				return;
			}
			$oz   = $this->package_ounces( $package );
			$zone = bcc_usps_zone( $zip, $oz );
			$rate = bcc_usps_rate( $oz, $zone );
			if ( null === $rate ) {
				return;
			}
			$this->add_rate(
				array(
					'id'        => $this->get_rate_id(),
					'label'     => $this->title,
					'cost'      => round( $rate + (float) $this->get_option( 'handling', 0 ), 2 ),
					'package'   => $package,
					'meta_data' => array(
						'USPS zone'      => $zone,
						'Package weight' => round( $oz, 1 ) . ' oz',
					),
				)
			);
		}
	}
}
add_action( 'woocommerce_shipping_init', 'bcc_shipping_init' );

function bcc_shipping_methods( $methods ) {
	$methods['bcc_usps_ground'] = 'BCC_USPS_Ground';
	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'bcc_shipping_methods' );
