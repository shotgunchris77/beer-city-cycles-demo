<?php
/**
 * Title: Contact Page
 * Slug: beer-city-cycles/contact
 * Categories: beer-city-cycles
 * Description: Contact form and Instagram.
 */
?>
<!-- wp:group {"align":"full","className":"bcc-pagehead","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-pagehead"><!-- wp:paragraph {"className":"bcc-script"} -->
<p class="bcc-script">Say hello</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Get In Touch</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"bcc-lede"} -->
<p class="bcc-lede">Questions on merch, an order, a vendor spot or a ride. Send it over.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"bcc-section","layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-section"><!-- wp:columns {"className":"bcc-split bcc-split-top"} -->
<div class="wp-block-columns bcc-split bcc-split-top"><!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Other ways to reach us</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The fastest way to see what we are up to is Instagram.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( bcc_instagram_url() ); ?>" target="_blank" rel="noreferrer noopener"><?php echo esc_html( bcc_instagram_handle() ); ?> on Instagram</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Based in</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Milwaukee, Wisconsin</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"60%"} -->
<div class="wp-block-column" style="flex-basis:60%"><!-- wp:shortcode -->
[bcc_contact]
<!-- /wp:shortcode --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
