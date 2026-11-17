<?php
/**
 * Settings > Beer City: the one notification email and the Instagram link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Where order emails, low stock alerts and contact form messages go. */
function bcc_email() {
	$e = sanitize_email( (string) get_option( 'bcc_email', '' ) );
	return $e ? $e : get_option( 'admin_email' );
}

function bcc_instagram_url() {
	$u = (string) get_option( 'bcc_instagram', '' );
	return $u ? $u : 'https://www.instagram.com/beercitycycles/';
}

function bcc_instagram_handle() {
	$path = trim( (string) wp_parse_url( bcc_instagram_url(), PHP_URL_PATH ), '/' );
	return '@' . ( $path ? $path : 'beercitycycles' );
}

function bcc_register_settings() {
	register_setting( 'bcc_settings', 'bcc_email', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_email' ) );
	register_setting( 'bcc_settings', 'bcc_instagram', array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ) );
}
add_action( 'admin_init', 'bcc_register_settings' );

function bcc_settings_menu() {
	add_options_page( 'Beer City', 'Beer City', 'manage_options', 'bcc-settings', 'bcc_settings_page' );
}
add_action( 'admin_menu', 'bcc_settings_menu' );

function bcc_settings_page() {
	?>
	<div class="wrap">
		<h1>Beer City Cycles settings</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'bcc_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="bcc_email">Notification email</label></th>
					<td><input type="email" class="regular-text" id="bcc_email" name="bcc_email" value="<?php echo esc_attr( get_option( 'bcc_email', '' ) ); ?>">
					<p class="description">Every merch order, low stock alert and contact form message is sent here.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="bcc_instagram">Instagram link</label></th>
					<td><input type="url" class="regular-text" id="bcc_instagram" name="bcc_instagram" value="<?php echo esc_attr( bcc_instagram_url() ); ?>"></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
	do_action( 'bcc_settings_after' );
}

/** [bcc_instagram] link, used in page text so the handle only lives in one place. */
function bcc_instagram_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'class' => '', 'label' => '' ), $atts );
	$label = $atts['label'] ? $atts['label'] : bcc_instagram_handle();
	return '<a class="' . esc_attr( $atts['class'] ) . '" href="' . esc_url( bcc_instagram_url() ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a>';
}
add_shortcode( 'bcc_instagram', 'bcc_instagram_shortcode' );
