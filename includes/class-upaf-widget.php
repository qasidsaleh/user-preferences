<?php

if (!defined('ABSPATH')) {
    // Exit if accessed directly.
    exit; 
}

class UPAF_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'upaf_widget',
            __('User Data Widget', 'upaf'),
            array('description' => __('Displays data based on user preferences.', 'upaf'))
        );
    }

    public function widget($args, $instance) {
        if (!is_user_logged_in()) {
            echo '<p>' . __('Please log in to view this widget.', 'upaf') . '</p>';
            return;
        }

        $user_id = get_current_user_id();
        $preferences = get_user_meta($user_id, '_upaf_preferences', true);

        if (!is_array($preferences) || empty($preferences)) {
            echo '<p>' . __('No preferences set.', 'upaf') . '</p>';
            return;
        }

        $api_handler = new UPAF_API_Handler();
        $api_data = $api_handler->fetch_data($preferences);

        if (is_wp_error($api_data)) {
            echo '<p>' . __('Failed to fetch data from API: ', 'upaf') . esc_html($api_data->get_error_message()) . '</p>';
        } else {
            echo $args['before_widget'];
            echo $args['before_title'] . __('User Preferences', 'upaf') . $args['after_title'];
            
            foreach ($api_data['json']['preferences'] as $data_item) {
                // Normalize input to lower case
                $data_item = strtolower($data_item);
                if ($data_item === 'username') {
                    $display_data = __('Username: ', 'upaf') . esc_html(get_userdata($user_id)->user_login);
                } elseif ($data_item === 'email') {
                    $display_data = __('Email: ', 'upaf') . esc_html(get_userdata($user_id)->user_email);
                } elseif ($data_item === 'nickname') {
                    $display_data = __('Nickname: ', 'upaf') . esc_html(get_userdata($user_id)->nickname);
                } else {
                    $display_data = __('Preference not recognized: ', 'upaf') . esc_html($data_item);
                }
                // Display the processed data
                echo '<p>' . $display_data . '</p>';
            }
            echo $args['after_widget'];
        }
    }

    public function form($instance) {
        echo '<p>' . __('This widget does not have any options.', 'upaf') . '</p>';
    }

    public function update($new_instance, $old_instance) {
        return $old_instance;
    }
}
