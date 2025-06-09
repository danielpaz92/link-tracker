<?php
defined('ABSPATH') || exit;

/**
 * Registers the REST endpoint used by the frontend JS.
 */
add_action('rest_api_init', function () {
	register_rest_route('atflt/v1', '/track', [
		'methods'  => 'POST',
		'callback' => 'atflt_handle_tracking',
		'permission_callback' => '__return_true',
	]);
});

/**
 * Handles the incoming POST request and stores the tracking data.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function atflt_handle_tracking(WP_REST_Request $request) {
	global $wpdb;

	$data = $request->get_json_params();

	if (empty($data['links']) || empty($data['screen'])) {
		return new WP_REST_Response(['error' => 'Invalid payload'], 400);
	}

	$table     = $wpdb->prefix . 'atflt_visits';
	$timestamp = current_time('mysql');
	$width     = intval($data['screen']['width']);
	$height    = intval($data['screen']['height']);
	$links     = maybe_serialize($data['links']);

	$wpdb->insert($table, [
		'timestamp'     => $timestamp,
		'screen_width'  => $width,
		'screen_height' => $height,
		'links'         => $links,
	]);

	return new WP_REST_Response(['success' => true], 200);
}
