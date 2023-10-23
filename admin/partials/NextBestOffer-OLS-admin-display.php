<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
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

// Get the active tab from the $_GET param
$default_tab = null;
$allowed_tabs = ['partial_selection', 'settings', 'logs', 'reporting'];
$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : $default_tab;
if (!in_array($tab, $allowed_tabs, true)) {
    $tab = $default_tab;
}
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <nav class="nav-tab-wrapper">
        <a href="?page=NextBestOffer_OLS_options" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Customer ID & API Key', 'nextbestoffer-ols' ); ?></a>
        <a href="?page=NextBestOffer_OLS_options&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Settings', 'nextbestoffer-ols' ); ?></a>
        <a href="?page=NextBestOffer_OLS_options&tab=logs" class="nav-tab <?php if($tab==='logs'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Logs', 'nextbestoffer-ols' ); ?></a>
        <a href="?page=NextBestOffer_OLS_options&tab=partial_selection" class="nav-tab <?php if($tab==='partial_selection'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Design', 'nextbestoffer-ols' ); ?></a>
        <a href="?page=NextBestOffer_OLS_options&tab=reporting" class="nav-tab <?php if($tab==='reporting'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Bug Report', 'nextbestoffer-ols' ); ?></a>
    </nav>

    <div class="tab-content">
        <?php switch($tab) :
            case 'partial_selection':
                ?>
                <form method="post" action="options.php">
                    <?php settings_fields( 'NextBestOffer_OLS_partial_selection' ); ?>
                    <?php do_settings_sections( 'NextBestOffer_OLS' ); ?>
                    <?php wp_nonce_field('partial_selection_action', 'partial_selection_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Choose Design', 'nextbestoffer-ols' ); ?></th>
                            <td>
                                <select name="NextBestOffer_OLS_selected_partial">
                                    <option value="none" <?php selected(get_option('NextBestOffer_OLS_selected_partial'), 'none'); ?>><?php echo esc_html__( 'None', 'nextbestoffer-ols' ); ?></option>
                                    <option value="partial-1" <?php selected(get_option('NextBestOffer_OLS_selected_partial'), 'partial-1'); ?>><?php echo esc_html__( 'Partial 1', 'nextbestoffer-ols' ); ?></option>
                                    <option value="partial-2" <?php selected(get_option('NextBestOffer_OLS_selected_partial'), 'partial-2'); ?>><?php echo esc_html__( 'Partial 2', 'nextbestoffer-ols' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
                <?php
            break;
            case 'settings':
                ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'NextBestOffer_OLS_model_settings' ); ?>
                <?php do_settings_sections( 'NextBestOffer_OLS' ); ?>
                <?php wp_nonce_field('settings_action', 'settings_nonce'); ?>
                <?php 
                    printf( 
                        /* translators: 1: Link to the Log Tab */
                        esc_html__( 'Note: The current values and the number of rules found can be viewed in the %1$s. After changes, the training must be restarted.', 'nextbestoffer-ols' ),
                        '<a href="?page=NextBestOffer_OLS_options&amp;tab=logs">' . esc_html__( 'Log Tab', 'nextbestoffer-ols' ) . '</a>'
                    );
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Max. rule length', 'nextbestoffer-ols' ); ?></th>
                        <td>
                            <input type="number" name="NextBestOffer_OLS_max_rule_length" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_max_rule_length' ) ); ?>" min="1" max="10" step="1">
                            <p class="description"><?php echo esc_html__( 'This setting determines how many different products can appear together in a recommendation at most. A larger number means more diverse recommendations, but possibly also more rules. (Default: 5)', 'nextbestoffer-ols' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Min. Support', 'nextbestoffer-ols' ); ?></th>
                        <td>
                            <input type="number" name="NextBestOffer_OLS_min_support" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_min_support' ) ); ?>" min="0" max="1" step="0.05">
                            <p class="description"><?php echo esc_html__( 'This value determines how often a product combination must occur in the orders for it to be displayed as a recommendation. A higher value only shows combinations that are purchased more frequently. (Default: 0.5)', 'nextbestoffer-ols' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Min. Confidence', 'nextbestoffer-ols' ); ?></th>
                        <td>
                            <input type="number" name="NextBestOffer_OLS_min_confidence" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_min_confidence' ) ); ?>" min="0" max="1" step="0.05">
                            <p class="description"><?php echo esc_html__( 'Here you determine how confident the plugin must be that the recommendation is relevant for the customers. A higher value means more accurate recommendations, but possibly less choice. (Default: 0.8)', 'nextbestoffer-ols' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Training Mode', 'nextbestoffer-ols' ); ?></th>
                        <td>
                            <select name="NextBestOffer_OLS_training_mode">
                                <option value="transaction_related" <?php selected(get_option('NextBestOffer_OLS_training_mode'), 'transaction_related'); ?>><?php echo esc_html__( 'Transaction related', 'nextbestoffer-ols' ); ?></option>
                                <option value="customer_related" <?php selected(get_option('NextBestOffer_OLS_training_mode'), 'customer_related'); ?>><?php echo esc_html__( 'Customer related', 'nextbestoffer-ols' ); ?></option>
                            </select>
                            <p class="description"><?php echo esc_html__( 'Choose the mode for the association analysis: "Transaction related" (default) or "Customer related". In "Transaction related" mode, the analysis is conducted on all transactions, generating recommendations based on collective purchasing behavior. In "Customer related" mode, individual customer preferences guide the recommendations, enhancing personal relevance. Select the mode that best suits your business needs.', 'nextbestoffer-ols' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Batch Size', 'nextbestoffer-ols' ); ?></th>
                        <td>
                            <input type="number" name="NextBestOffer_OLS_batch_size" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_batch_size' ) ); ?>" min="500" max="4000" step="100">
                            <p class="description"><?php echo esc_html__( 'This setting controls the batch size for sending orders to the recommendation service. Higher values send more orders at once but may overload the WordPress PHP script whereas lower values make the transfer slower (min: 500; max: 4000).', 'nextbestoffer-ols' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Email Recommendations', 'nextbestoffer-ols' ); ?></th>
                        <td>
                            <select name="NextBestOffer_OLS_email_recommendations">
                                <option value="enabled" <?php selected(get_option('NextBestOffer_OLS_email_recommendations'), 'enabled'); ?>><?php echo esc_html__( 'Enabled', 'nextbestoffer-ols' ); ?></option>
                                <option value="disabled" <?php selected(get_option('NextBestOffer_OLS_email_recommendations'), 'disabled'); ?>><?php echo esc_html__( 'Disabled', 'nextbestoffer-ols' ); ?></option>
                            </select>
                            <p class="description"><?php echo esc_html__( 'If you enable this feature customers get also personalized recommendations in their order confirmation email.', 'nextbestoffer-ols' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <?php
            break;
            case 'logs':
                ?>
                <form method="post">
                    <?php wp_nonce_field('logs_action', 'logs_nonce'); ?>
                    <div id="logs">
                        <div class="scrollable-window">
                            <div class="scrollable-content">
                                <pre><?php echo esc_html( get_option( 'NextBestOffer_OLS_logs', '' ) ); ?></pre>
                            </div>
                        </div>
                    </div>
                    <input type="submit" name="get_logs" class="button button-primary" value="<?php esc_attr_e( 'Refresh', 'nextbestoffer-ols' ); ?>" />
                </form>
                <?php
                break;
            case 'reporting':
                ?>
                <form method="post">
                    <?php wp_nonce_field('reporting_action', 'reporting_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'EMail', 'nextbestoffer-ols' ); ?></th>
                            <td><input type="email" name="NextBestOffer_OLS_bug_report_email" placeholder="someone@example.com" required/></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Message', 'nextbestoffer-ols' ); ?></th>
                            <td><textarea name="NextBestOffer_OLS_bug_report_text" placeholder="Please explain your issue here" required></textarea></td>
                        </tr>
                    </table>
                    <input type="submit" name="report_bug" class="button button-primary" />
                </form>
                <?php
                break;
            default:
                ?>
                <form method="post" action="options.php">
                    <?php settings_fields( 'NextBestOffer_OLS_credentials' ); ?>
                    <?php do_settings_sections( 'NextBestOffer_OLS' ); ?>
                    <?php wp_nonce_field('default_action', 'default_nonce'); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Customer ID', 'nextbestoffer-ols' ); ?></th>
                            <td><input type="text" name="NextBestOffer_OLS_use_case" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_use_case' ) ); ?>" placeholder="Customer ID from Email"/></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'API Key', 'nextbestoffer-ols' ); ?></th>
                            <td><input type="text" name="NextBestOffer_OLS_api_key" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_api_key' ) ); ?>" placeholder="API Key from Email" /></td>
                        </tr>
                        <tr>
                            <td><a href="https://open-ls.de/impressum/" target="_blank"><?php esc_html_e( 'Kontakt', 'nextbestoffer-ols' ); ?></a></td>
                            <td><a href="https://open-ls.de/produkt/nextbestoffer-ols-plugin-api-key/" target="_blank"><?php esc_html_e( 'Buy Plugin', 'nextbestoffer-ols' ); ?></a></td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                    <input type="submit" name="confirm_start_training" class="button button-primary" value="<?php esc_attr_e( 'Start Training', 'nextbestoffer-ols' ); ?>" />
                </form>
                <?php
                break;
        endswitch; ?>
    </div>
</div>