<?php
/**
 * Title: Events Page
 * Slug: beer-city-cycles/events
 * Categories: beer-city-cycles
 * Description: Upcoming events and rides.
 */
?>
<!-- wp:group {"align":"full","className":"bcc-pagehead","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-pagehead"><!-- wp:paragraph {"className":"bcc-script"} -->
<p class="bcc-script">Mark the calendar</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Events &amp; Rides</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bcc-lede"} -->
<p class="bcc-lede">Bike shows, parties and pop up rides. Everything Beer City has on the books.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-section"><!-- wp:heading -->
<h2 class="wp-block-heading">Events</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[bcc_events type="event"]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section bcc-section-alt","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-section bcc-section-alt"><!-- wp:heading -->
<h2 class="wp-block-heading">Rides</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bcc-lede"} -->
<p class="bcc-lede">Pop up rides get called when the weather is right. Ride dates are posted here and on Instagram first, so follow along and keep the tank full.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[bcc_events type="ride" empty="No rides on the books right now. Follow along for the next one:"]
<!-- /wp:shortcode -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( bcc_instagram_url() ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html( bcc_instagram_handle() ); ?> on Instagram</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section bcc-cta","layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull bcc-section bcc-cta"><!-- wp:heading -->
<h2 class="wp-block-heading">Vendors and Sponsors</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bcc-lede"} -->
<p class="bcc-lede">Want a vendor spot at the bike show or to sponsor an event? Send us a message and we will get you the details.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get in touch</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
