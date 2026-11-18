<?php
/**
 * Title: Home Page
 * Slug: beer-city-cycles/home
 * Categories: beer-city-cycles
 * Description: Hero, upcoming events, merch, about and Instagram.
 */
?>
<!-- wp:group {"align":"full","className":"bcc-hero","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-hero"><!-- wp:columns {"verticalAlignment":"center","className":"bcc-hero-cols"} -->
<div class="wp-block-columns are-vertically-aligned-center bcc-hero-cols"><!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%"><!-- wp:heading {"level":1,"className":"bcc-hero-title"} -->
<h1 class="wp-block-heading bcc-hero-title">Party.<br><mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-blaze-color">Drink Beer.</mark><br>Put Down Miles.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bcc-lede"} -->
<p class="bcc-lede">Choppers and Dynas out of Milwaukee. Bike shows, parties, pop up rides and the merch to wear to all of it.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/merch/' ) ); ?>">Shop merch</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/events/' ) ); ?>">Events and rides</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"33%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:33%"><!-- wp:image {"sizeSlug":"full","className":"bcc-hero-logo"} -->
<figure class="wp-block-image size-full bcc-hero-logo"><img src="<?php echo esc_url( bcc_img( 'logo.jpg' ) ); ?>" alt="Beer City Cycles logo: Premium Choppers, Milwaukee, WI"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"27%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:27%"><!-- wp:shortcode -->
[bcc_bill count="2"]
<!-- /wp:shortcode --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-strip","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-strip"><!-- wp:paragraph {"className":"bcc-strip-text"} -->
<p class="bcc-strip-text"><b>Ride it in.</b> <span>★</span> <b>Park it.</b> <span>★</span> <b>Grab a beer.</b> <span>★</span> <b>Stay for one more.</b></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-section"><!-- wp:paragraph {"className":"bcc-script"} -->
<p class="bcc-script">Mark the calendar</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Coming Up</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[bcc_events count="2"]
<!-- /wp:shortcode -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/events/' ) ); ?>">All events and rides</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section bcc-section-alt","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-section bcc-section-alt"><!-- wp:paragraph {"className":"bcc-script"} -->
<p class="bcc-script">Wear it out</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Merch</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bcc-lede"} -->
<p class="bcc-lede">Shirts printed with Beer City art. Every listing shows which sizes are left, and orders ship from Milwaukee by USPS.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[bcc_merch count="3"]
<!-- /wp:shortcode -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/merch/' ) ); ?>">Shop all merch</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-section"><!-- wp:columns {"verticalAlignment":"center","className":"bcc-split"} -->
<div class="wp-block-columns are-vertically-aligned-center bcc-split"><!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%"><!-- wp:image {"sizeSlug":"full","className":"bcc-art"} -->
<figure class="wp-block-image size-full bcc-art"><img src="<?php echo esc_url( bcc_img( 'eagle-skyline.jpg' ) ); ?>" alt="Beer City Cycles eagle over the Milwaukee skyline artwork"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%"><!-- wp:paragraph {"className":"bcc-script"} -->
<p class="bcc-script">Who we are</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Not a club. Just friends who ride.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Beer City Cycles is a chopper and Dyna brand out of Milwaukee. We ride motorcycles, drink beer and put down miles, and we throw a few parties a year for everybody else who does the same.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About Beer City</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section bcc-cta","layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull bcc-section bcc-cta"><!-- wp:paragraph {"className":"bcc-script"} -->
<p class="bcc-script">Stay in the loop</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Follow the Party</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bcc-lede"} -->
<p class="bcc-lede">Ride dates, merch drops and show news hit Instagram first.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( bcc_instagram_url() ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html( bcc_instagram_handle() ); ?> on Instagram</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
