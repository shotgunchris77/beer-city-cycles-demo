<?php
/**
 * Title: Header Content
 * Slug: beer-city-cycles/header
 * Categories: beer-city-cycles
 * Description: Logo, menu, Instagram and cart.
 * Inserter: no
 */
?>
<!-- wp:group {"align":"full","className":"bcc-ticker","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull bcc-ticker"><!-- wp:paragraph -->
<p>Milwaukee, Wis. <span>★</span> Choppers <span>★</span> Dynas <span>★</span> Cold Beer <span>★</span> Long Miles <span>★</span> Not a club, just friends who ride</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-header","layout":{"type":"constrained","contentSize":"1240px","wideSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-header"><!-- wp:group {"align":"wide","className":"bcc-mast-top","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
<div class="wp-block-group alignwide bcc-mast-top"><!-- wp:paragraph -->
<p>Est. on the south side</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Premium Choppers</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Milwaukee, Wisconsin</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"align":"wide","className":"bcc-wordmark"} -->
<p class="alignwide bcc-wordmark"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beer City <mark>Cycles</mark></a></p>
<!-- /wp:paragraph -->

<!-- wp:group {"align":"wide","className":"bcc-header-row","layout":{"type":"flex","justifyContent":"center","flexWrap":"nowrap"}} -->
<div class="wp-block-group alignwide bcc-header-row"><!-- wp:group {"className":"bcc-header-right","style":{"spacing":{"blockGap":"1.1rem"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group bcc-header-right"><!-- wp:navigation {"overlayBackgroundColor":"black","overlayTextColor":"cream","style":{"spacing":{"blockGap":"1.6rem"}},"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:navigation-link {"label":"About","url":"<?php echo esc_url( home_url( '/about/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Events \u0026 Rides","url":"<?php echo esc_url( home_url( '/events/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Merch","url":"<?php echo esc_url( home_url( '/merch/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Contact","url":"<?php echo esc_url( home_url( '/contact/' ) ); ?>","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"Instagram","url":"<?php echo esc_url( bcc_instagram_url() ); ?>","kind":"custom","opensInNewTab":true,"isTopLevelLink":true,"className":"bcc-nav-ig"} /-->
<!-- /wp:navigation -->

<!-- wp:html -->
<span class="bcc-cart-slot"></span>
<!-- /wp:html --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
