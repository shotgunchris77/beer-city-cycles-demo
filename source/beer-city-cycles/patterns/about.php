<?php
/**
 * Title: About Page
 * Slug: beer-city-cycles/about
 * Categories: beer-city-cycles
 * Description: Who Beer City Cycles is.
 */
?>
<!-- wp:group {"align":"full","className":"bcc-pagehead","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-pagehead"><!-- wp:paragraph {"className":"bcc-script"} -->
<p class="bcc-script">About</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Beer City Cycles</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bcc-lede"} -->
<p class="bcc-lede">Choppers and Dynas. Milwaukee, Wisconsin.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-section"><!-- wp:columns {"verticalAlignment":"center","className":"bcc-split"} -->
<div class="wp-block-columns are-vertically-aligned-center bcc-split"><!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Not a club. Just friends who ride.</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Beer City Cycles is a chopper and Dyna brand out of Milwaukee, Wisconsin. We are not a club. We are a group of friends who ride motorcycles, drink beer and put down miles.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>It started with a couple of friends and some pop up rides on the south side of Milwaukee. Then came the shirts, the parties and a bike show. Now we throw the Beer City Cycles Bike Show &amp; BBQ at Puddlers Hall every June and the Unofficial Mama Tried After Party every February, and we print merch for the people who show up.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>If it has two wheels and you ride it, you are welcome. Come party with us.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%"><!-- wp:image {"sizeSlug":"full","className":"bcc-art bcc-art-logo"} -->
<figure class="wp-block-image size-full bcc-art bcc-art-logo"><img src="<?php echo esc_url( bcc_img( 'logo.jpg' ) ); ?>" alt="Beer City Cycles logo: Premium Choppers, Milwaukee, WI"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section bcc-section-alt","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-section bcc-section-alt"><!-- wp:columns {"className":"bcc-pillars"} -->
<div class="wp-block-columns bcc-pillars"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Party</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A bike show and BBQ every June. The Unofficial Mama Tried After Party every February. Free kegs, good food and a lot of bikes out front.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Drink Beer</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>This is Milwaukee. The name is not an accident, and neither is the keg.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Put Down Miles</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Pop up rides on choppers, Dynas, FXRs and whatever else runs. Ride dates go up on Instagram first.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section bcc-cta","layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull bcc-section bcc-cta"><!-- wp:heading -->
<h2 class="wp-block-heading">See you out there</h2>
<!-- /wp:heading -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/events/' ) ); ?>">Events and rides</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/merch/' ) ); ?>">Shop merch</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( bcc_instagram_url() ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html( bcc_instagram_handle() ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
