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
<div class="recommendations-container">
    <h3><?php echo "Customers also bought: " ?></h3>
    <div class="recommendation-items">
        <?php $count = count($recommendations); ?>
        <?php foreach ($recommendations as $product_id) : ?>
            <?php $product = wc_get_product($product_id); ?>
            <?php if ($product) : ?>
                <div class="recommendation-item <?php echo ($count > 3) ? 'more-than-three' : ''; ?>">
                    <h5><?php echo $product->get_name(); ?></h5>
                    <img src="<?php echo wp_get_attachment_image_url($product->get_image_id(), 'medium'); ?>" alt="<?php echo $product->get_name(); ?>" />
                    <p><?php echo $product->get_short_description(); ?></p>
                    <a href="<?php echo esc_url($product->get_permalink()); ?>"><?php _e('Learn more', 'NextBestOffer-OLS'); ?></a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php if ($count > 3) : ?>
        <div class="read-more">
            <button class="btn-read-more"><?php _e('Read more', 'NextBestOffer-OLS'); ?></button>
        </div>
    <?php endif; ?>
</div>
