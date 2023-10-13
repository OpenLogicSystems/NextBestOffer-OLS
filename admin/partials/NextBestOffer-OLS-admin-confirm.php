<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Provide a submenue confirmation page
 *
 *
 * @link       https://wordpress.org/plugins/nextbestoffer-ols/
 * @since      1.0.0
 *
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->


<?php
// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    return;
}
?>

<div class="wrap">
    <h2><?php esc_html_e('Start Model Training?', 'nextbestoffer-ols'); ?></h2>
    <h3><?php esc_html_e('Starting the training will create a new model. If a previous model exists, it will be overwritten.', 'nextbestoffer-ols'); ?></h3>
    <h3><?php esc_html_e('After confirmation, please wait until the page has finished loading and the options page is displayed again.', 'nextbestoffer-ols'); ?></h3>
    <form method="post">
        <?php wp_nonce_field('start_training_action', 'start_training_nonce'); ?>
        <input type="submit" name="start_training" class="button button-primary" value="<?php esc_attr_e('Yes', 'nextbestoffer-ols'); ?>">
        <a href="<?php echo esc_url(admin_url('options-general.php?page=NextBestOffer_OLS_options')); ?>" class="button"><?php esc_html_e('No', 'nextbestoffer-ols'); ?></a>
    </form>
</div>