<?php
/**
 * Title: Header Content
 * Slug: beer-city-cycles/header
 * Categories: beer-city-cycles
 * Description: Logo, menu, Instagram and cart.
 * Inserter: no
 */
?>
<!-- wp:group {"align":"full","className":"bcc-header","layout":{"type":"constrained","contentSize":"1240px","wideSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-header"><!-- wp:group {"align":"wide","className":"bcc-header-row","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"nowrap"}} -->
<div class="wp-block-group alignwide bcc-header-row"><!-- wp:group {"className":"bcc-brand","style":{"spacing":{"blockGap":"0.75rem"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group bcc-brand"><!-- wp:image {"width":"62px","linkDestination":"custom","className":"bcc-header-logo"} -->
<figure class="wp-block-image is-resized bcc-header-logo"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( bcc_img( 'logo-400.jpg' ) ); ?>" alt="Beer City Cycles logo" style="width:62px"/></a></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"bcc-wordmark"} -->
<p class="bcc-wordmark"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Beer City Cycles</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"bcc-header-right","style":{"spacing":{"blockGap":"1.1rem"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group bcc-header-right"><!-- wp:navigation {"overlayBackgroundColor":"black","overlayTextColor":"cream","style":{"spacing":{"blockGap":"1.6rem"}},"layout":{"type":"flex","justifyContent":"right"}} -->
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
