<?php
/**
 * Plugin Name: User Preferences API Fetch
 * Description: A plugin to manage user preferences and fetch data from an API.
 * Version: 1.0
 * Author: Qasid Saleh
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Flush rewrite rules on activation and deactivation
register_activation_hook(__FILE__, 'upaf_activate_plugin');
register_deactivation_hook(__FILE__, 'upaf_deactivate_plugin');

function upaf_activate_plugin() {
    flush_rewrite_rules();
}

function upaf_deactivate_plugin() {
    flush_rewrite_rules();
}

// Include the main plugin classes
include_once plugin_dir_path(__FILE__) . 'includes/class-upaf.php';
include_once plugin_dir_path(__FILE__) . 'includes/class-upaf-widget.php';

// Initialize the plugin
function upaf_initialize_plugin() {
    $plugin = new UPAF_User_Preferences_API_Fetch();
    add_action('widgets_init', function() {
        register_widget('UPAF_Widget');
    });
}
add_action('plugins_loaded', 'upaf_initialize_plugin');

// Enqueue styles
function upaf_enqueue_styles() {
    wp_enqueue_style('upaf-styles', plugin_dir_url(__FILE__) . 'assets/css/styles.css');
}
add_action('wp_enqueue_scripts', 'upaf_enqueue_styles');
