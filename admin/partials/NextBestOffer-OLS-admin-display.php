<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
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

// Get the active tab from the $_GET param
$default_tab = null;
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

?>
<div class="wrap">
    <?php settings_errors( 'NextBestOffer_OLS' ); ?>
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <nav class="nav-tab-wrapper">
        <a href="?page=NextBestOffer_OLS_options" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Customer ID & API Key', 'NextBestOffer-OLS' ); ?></a>
        <a href="?page=NextBestOffer_OLS_options&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Settings', 'NextBestOffer-OLS' ); ?></a>
        <a href="?page=NextBestOffer_OLS_options&tab=logs" class="nav-tab <?php if($tab==='logs'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Logs', 'NextBestOffer-OLS' ); ?></a>
        <a href="?page=NextBestOffer_OLS_options&tab=partial_selection" class="nav-tab <?php if($tab==='partial_selection'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Design', 'NextBestOffer-OLS' ); ?></a>
    </nav>

    <div class="tab-content">
        <?php switch($tab) :
            case 'partial_selection':
                ?>
                <form method="post" action="options.php">
                    <?php settings_fields( 'NextBestOffer_OLS_partial_selection' ); ?>
                    <?php do_settings_sections( 'NextBestOffer_OLS' ); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Choose Design', 'NextBestOffer-OLS' ); ?></th>
                            <td>
                                <select name="NextBestOffer_OLS_selected_partial">
                                    <option value="partial-1" <?php selected(get_option('NextBestOffer_OLS_selected_partial'), 'partial-1'); ?>>Partial 1</option>
                                    <option value="partial-2" <?php selected(get_option('NextBestOffer_OLS_selected_partial'), 'partial-2'); ?>>Partial 2</option>
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
                <p><strong>Note:</strong> The current values and the number of rules found can be viewed in the <a href="?page=NextBestOffer_OLS_options&amp;tab=logs">Log Tab</a>. After changes, the training must be restarted.</p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Max. Regellänge', 'NextBestOffer-OLS' ); ?></th>
                        <td>
                            <input type="number" name="NextBestOffer_OLS_max_rule_length" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_max_rule_length' ) ); ?>" min="1" max="10" step="1">
                            <p class="description">This setting determines how many different products can appear together in a recommendation at most. A larger number means more diverse recommendations, but possibly also more rules. (Default: 5)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Min. Support', 'NextBestOffer-OLS' ); ?></th>
                        <td>
                            <input type="number" name="NextBestOffer_OLS_min_support" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_min_support' ) ); ?>" min="0" max="1" step="0.05">
                            <p class="description">This value determines how often a product combination must occur in the orders for it to be displayed as a recommendation. A higher value only shows combinations that are purchased more frequently. (Default: 0.5)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Min. Konfidenz', 'NextBestOffer-OLS' ); ?></th>
                        <td>
                            <input type="number" name="NextBestOffer_OLS_min_confidence" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_min_confidence' ) ); ?>" min="0" max="1" step="0.05">
                            <p class="description">Here you determine how confident the plugin must be that the recommendation is relevant for the customers. A higher value means more accurate recommendations, but possibly less choice. (Default: 0.8)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Training Mode', 'NextBestOffer-OLS' ); ?></th>
                        <td>
                            <select name="NextBestOffer_OLS_training_mode">
                                <option value="transaction_related" <?php selected(get_option('NextBestOffer_OLS_training_mode'), 'transaction_related'); ?>>Transaction related</option>
                                <option value="customer_related" <?php selected(get_option('NextBestOffer_OLS_training_mode'), 'customer_related'); ?>>Customer related</option>
                            </select>
                            <p class="description">Choose the mode for the association analysis: "Transaction related" (default) or "Customer related". In "Transaction related" mode, the analysis is conducted on all transactions, generating recommendations based on collective purchasing behavior. In "Customer related" mode, individual customer preferences guide the recommendations, enhancing personal relevance. Select the mode that best suits your business needs.</p>
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
                    <div id="logs">
                        <pre><?php echo esc_html( get_option( 'NextBestOffer_OLS_logs', '' ) ); ?></pre>
                    </div>
                    <input type="submit" name="get_logs" class="button button-primary" value="<?php esc_attr_e( 'Refresh', 'NextBestOffer-OLS' ); ?>" />
                </form>
                <?php
                break;
            default:
                ?>
                <form method="post" action="options.php">
                    <?php settings_fields( 'NextBestOffer_OLS_credentials' ); ?>
                    <?php do_settings_sections( 'NextBestOffer_OLS' ); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Customer ID', 'NextBestOffer-OLS' ); ?></th>
                            <td><input type="text" name="NextBestOffer_OLS_use_case" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_use_case' ) ); ?>" placeholder="Customer ID from Email"/></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'API Key', 'NextBestOffer-OLS' ); ?></th>
                            <td><input type="text" name="NextBestOffer_OLS_api_key" value="<?php echo esc_attr( get_option( 'NextBestOffer_OLS_api_key' ) ); ?>" placeholder="API Key from Email" /></td>
                        </tr>
                        <tr>
                            <th scope="row"></th>
                            <td><a href="https://open-ls.de/produkt/nextbestoffer-ols-plugin-api-key/" target="_blank"><?php esc_html_e( 'Buy Plugin', 'NextBestOffer-OLS' ); ?></a></td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                    <input type="submit" name="start_training" class="button button-primary" value="<?php esc_attr_e( 'Start Training', 'NextBestOffer-OLS' ); ?>" />
                </form>
                <?php
                break;
        endswitch; ?>
    </div>
</div>