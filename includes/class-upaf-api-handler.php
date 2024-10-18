<?php
if (!defined('ABSPATH')) {
    // Exit if accessed directly.
    exit; 
}
class UPAF_API_Handler {

    private $api_url = 'https://httpbin.org/post';

    public function push_data($preferences) {
        $response = wp_remote_post($this->api_url, array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode(array('preferences' => $preferences)),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', __('Invalid JSON response', 'upaf'));
        }

        return $data;
    }

    public function fetch_data($preferences) {
        // For demonstration purposes, we'll just return the response of the push_data function
        return $this->push_data($preferences);
    }
}
