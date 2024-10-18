<?php
if (!defined('ABSPATH')) {
    // Exit if accessed directly.
    exit; 
}
// Include API Handler
include_once plugin_dir_path(__FILE__) . 'class-upaf-api-handler.php';
class UPAF_User_Preferences_API_Fetch {
    private $api_handler;
    public function __construct() {
        $this->api_handler = new UPAF_API_Handler();
        add_action('init', array($this, 'add_rewrite_endpoint'));
        add_action('woocommerce_account_upaf_preferences_endpoint', array($this, 'upaf_preferences_page'));
        add_action('template_redirect', array($this, 'handle_form_submission'));
        add_action('wp_ajax_upaf_save_preferences', array($this, 'save_preferences'));
        add_action('wp_ajax_nopriv_upaf_save_preferences', array($this, 'save_preferences'));
        add_action('woocommerce_account_menu_items', array($this, 'add_my_account_link'), 40);
        add_filter('woocommerce_get_endpoint_url', array($this, 'add_my_account_link'), 10, 2);
    }
    public function add_rewrite_endpoint() {
        add_rewrite_endpoint('upaf_preferences', EP_ROOT | EP_PAGES);
    }
    // Display user preferences in WooCommerce My Account
    public function upaf_preferences_page() {
        if (!is_user_logged_in()) {
            return;
        }
    
        $user_id = get_current_user_id();
        $preferences = get_user_meta($user_id, '_upaf_preferences', true);
        $preference_values = is_array($preferences) ? implode(',', $preferences) : '';
    
        ?>
        <div class="upaf-preferences-container">
            <h3><?php _e('User Preferences', 'upaf'); ?></h3>
            <form method="post" action="">
                <?php wp_nonce_field('upaf_save_preferences'); ?>
                <label for="preferences"><?php _e('Enter your preferences (comma-separated)', 'upaf'); ?></label>
                <input type="text" name="preferences" value="<?php echo esc_attr($preference_values); ?>" />
                <input type="submit" name="upaf_save_preferences" value="<?php _e('Save Preferences', 'upaf'); ?>" />
            </form>
        </div>
        <?php
    }
    // Handle form submission
    public function handle_form_submission() {
        if (isset($_POST['upaf_save_preferences']) && is_user_logged_in()) {
            $this->save_preferences();
        }
    }
    // Save preferences functionality
    public function save_preferences() {
        if (!isset($_POST['preferences']) || !wp_verify_nonce($_POST['_wpnonce'], 'upaf_save_preferences')) {
            wp_die(__('Invalid nonce', 'upaf'));
        }
    
        $user_id = get_current_user_id();
        $preferences = sanitize_text_field($_POST['preferences']);
        $preferences_array = array_filter(array_map('sanitize_text_field', array_map('trim', explode(',', $preferences))));
    
        if (empty($preferences_array)) {
            wp_die(__('No valid preferences provided', 'upaf'));
        }
    
        // Save preferences to user meta
        update_user_meta(get_current_user_id(), '_upaf_preferences', $preferences_array);

        // Push data to API and fetch response
        $api_data = $this->api_handler->push_data($preferences_array);

         // Handle API response
         if (is_wp_error($api_data)) {
            echo '<p>' . __('Failed to push data to API: ', 'upaf') . esc_html($api_data->get_error_message()) . '</p>';
        }
    
        // Redirect to avoid form resubmission
        wp_redirect(wc_get_account_endpoint_url('upaf_preferences'));
        exit;
    }
    // Show user preferences in WooCommerce My Account
    public function add_my_account_link($items) {
        if (is_array($items)) {
            $items['upaf_preferences'] = __('User Preferences', 'upaf');
        }
        return $items;
    }
}
