<?php
/**
 * Plugin Name: Above the Fold Link Tracker
 * Description: Tracks hyperlinks visible above the fold on homepage visits.
 * Version: 1.0.1
 * Author: Daniel Paz
 */

defined('ABSPATH') || exit;

// Load internal components
require_once plugin_dir_path(__FILE__) . 'includes/db.php';
require_once plugin_dir_path(__FILE__) . 'includes/rest-api.php';

// Create the DB table when the plugin is activated
register_activation_hook(__FILE__, 'atflt_activate_plugin');

function atflt_activate_plugin() {
	atflt_create_table();
}

// Enqueue the JS only on the homepage
add_action('wp_enqueue_scripts', function () {
	if (is_front_page()) {
		wp_enqueue_script(
			'atflt-script',
			plugin_dir_url(__FILE__) . 'js/track-links.js',
			[],
			'1.0',
			true
		);
	}
});
