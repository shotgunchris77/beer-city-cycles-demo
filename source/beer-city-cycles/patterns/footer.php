<?php
/**
 * Title: Footer Content
 * Slug: beer-city-cycles/footer
 * Categories: beer-city-cycles
 * Description: Logo, links and Instagram.
 * Inserter: no
 */
?>
<!-- wp:group {"align":"full","className":"bcc-footer","layout":{"type":"constrained","contentSize":"1240px","wideSize":"1240px"}} -->
<div class="wp-block-group alignfull bcc-footer"><!-- wp:columns {"align":"wide","className":"bcc-footer-cols"} -->
<div class="wp-block-columns alignwide bcc-footer-cols"><!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%"><!-- wp:image {"width":"150px","className":"bcc-footer-logo"} -->
<figure class="wp-block-image is-resized bcc-footer-logo"><img src="<?php echo esc_url( bcc_img( 'logo-400.jpg' ) ); ?>" alt="Beer City Cycles, Premium Choppers, Milwaukee, WI" style="width:150px"/></figure>
<!-- /wp:image -->

<!-- wp:paragraph {"className":"bcc-footer-tag"} -->
<p class="bcc-footer-tag">Party. Drink beer. Put down miles.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"30%"} -->
<div class="wp-block-column" style="flex-basis:30%"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Get around</h4>
<!-- /wp:heading -->

<!-- wp:list {"className":"bcc-footer-links"} -->
<ul class="wp-block-list bcc-footer-links"><!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/events/' ) ); ?>">Events &amp; Rides</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/merch/' ) ); ?>">Merch</a></li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"30%"} -->
<div class="wp-block-column" style="flex-basis:30%"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Find us</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Milwaukee, Wisconsin</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Instagram: <a href="<?php echo esc_url( bcc_instagram_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( bcc_instagram_handle() ); ?></a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Send us a message</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:paragraph {"align":"wide","className":"bcc-footer-fine"} -->
<p class="alignwide bcc-footer-fine">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Beer City Cycles. Milwaukee, WI.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
