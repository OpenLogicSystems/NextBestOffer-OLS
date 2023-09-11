<?php

/**
 * Provide a submenue confirmation page
 *
 *
 * @link       https://wordpress.org/plugins/NextBestOffer-OLS
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
    <h2><?php esc_html_e('Start Model Training?', 'NextBestOffer-OLS'); ?></h2>
    <h3><?php esc_html_e('Starting the training will create a new model. If a previous model exists, it will be overwritten.', 'NextBestOffer-OLS'); ?></h3>
    <h3><?php esc_html_e('After confirmation, please wait until the page has finished loading and the options page is displayed again.', 'NextBestOffer-OLS'); ?></h3>
    <form method="post">
        <input type="submit" name="start_training" class="button button-primary" value="<?php esc_attr_e('Yes', 'NextBestOffer-OLS'); ?>">
        <a href="<?php echo esc_url(admin_url('options-general.php?page=NextBestOffer_OLS_options')); ?>" class="button"><?php esc_html_e('No', 'NextBestOffer-OLS'); ?></a>
    </form>
</div>