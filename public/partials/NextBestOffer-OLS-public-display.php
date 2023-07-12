<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wordpress.org/plugins/NextBestOffer-OLS
 * @since      1.0.0
 *
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<section class="related products">
    <h2><?php echo "Customers also bought: " ?></h2>
    <ul class="products columns-4">
        <?php $counter = 0; ?>
        <?php foreach ($recommendations as $product_id) : ?>
            <?php if ($counter >= 3) break; ?>
            <?php $product = wc_get_product($product_id); ?>
            <?php if ($product) : ?>
                <li class="product type-product">
                    <a href="<?php echo esc_url($product->get_permalink()); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                        <img src="<?php echo wp_get_attachment_image_url($product->get_image_id(), 'medium'); ?>" alt="<?php echo $product->get_name(); ?>" />
                        <h2 class="woocommerce-loop-product__title"><?php echo $product->get_name(); ?></h2>
                        <span class="price"><?php echo $product->get_price_html(); ?></span>
                    </a>
                    <a href="?add-to-cart=<?php echo $product_id; ?>" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="<?php echo $product_id; ?>" data-product_sku="" aria-label="<?php echo sprintf(__('"%s" add to your shopping cart', 'NextBestOffer-OLS'), $product->get_name()); ?>" rel="nofollow"><?php _e('Add to cart', 'NextBestOffer-OLS'); ?></a>
                </li>
                <?php $counter++; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</section>