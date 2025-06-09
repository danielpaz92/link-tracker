<?php
/**
 * Plugin Name: Above the Fold Link Tracker
 * Description: Tracks hyperlinks visible above the fold on homepage visits.
 * Version: 1.0.1
 * Author: Daniel Paz
 */

defined('ABSPATH') || exit;

// Load internal components

// Load database setup and cleanup logic
require_once plugin_dir_path(__FILE__) . 'includes/db.php';

// Register the REST API endpoint
require_once plugin_dir_path(__FILE__) . 'includes/rest-api.php';

// Load admin dashboard UI
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';

// Load custom utilities and manual test hooks
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

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

// Load plugin text domain for translations
add_action('plugins_loaded', function () {
	load_plugin_textdomain('atflt', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

