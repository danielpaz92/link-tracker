<?php
/**
 * Plugin Name: Above the Fold Link Tracker
 * Description: Tracks hyperlinks visible above the fold on homepage visits.
 * Version: 1.0.0
 * Author: Daniel Paz
 */

defined( 'ABSPATH' ) || exit;

// Enfileira o JavaScript na homepage
add_action( 'wp_enqueue_scripts', function () {
	if ( is_front_page() ) {
		wp_enqueue_script(
			'atflt-script',
			plugin_dir_url( __FILE__ ) . 'js/track-links.js',
			[],
			'1.0',
			true
		);
	}
});
