<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wordpress.org/plugins/nextbestoffer-ols/
 * @since      1.0.0
 *
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<section class="NextBestOffer-OLS-carousel">
    <h2><?php esc_html_e('Customers also bought:', 'nextbestoffer-ols'); ?></h2>
    <div class="carousel-container">
        <ul class="carousel-list">
            <?php foreach ($recommendations as $product_id) : ?>
                <?php $product = wc_get_product($product_id); ?>
                <?php if ($product) : ?>
                    <li class="carousel-item">
                        <a href="<?php echo esc_url($product->get_permalink()); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
                        <img src="<?php echo esc_url(wp_get_attachment_image_url($product->get_image_id(), 'medium')); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" />
                            <h4 class="woocommerce-loop-product__title"><?php echo esc_html($product->get_name()); ?></h4>
                            <span class="price"><?php echo wp_kses_post($product->get_price_html()); ?></span>
                        </a>
                        <a href="?add-to-cart=<?php echo esc_attr( $product_id ); ?>" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="<?php echo esc_attr( $product_id ); ?>" data-product_sku="" 
                        aria-label="
                        <?php 
                        /* translators: 1: product name */
                        echo esc_attr(sprintf(_x('%1$s add to your shopping cart', '1: product name', 'nextbestoffer-ols'), $product->get_name())); 
                        ?>" rel="nofollow"><?php esc_html_e('Add to cart', 'nextbestoffer-ols'); ?></a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</section>