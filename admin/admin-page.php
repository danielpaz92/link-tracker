<?php
defined('ABSPATH') || exit;

/**
 * Adds the admin menu item under "Tools".
 */
add_action('admin_menu', function () {
	add_management_page(
		'Above the Fold Tracker',
		'Above the Fold Tracker',
		'manage_options',
		'atflt-dashboard',
		'atflt_render_dashboard'
	);
});

/**
 * Renders the dashboard page.
 */
function atflt_render_dashboard() {
	global $wpdb;

	$table = $wpdb->prefix . 'atflt_visits';

	// Get entries from the last 7 days
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $table WHERE timestamp >= NOW() - INTERVAL 7 DAY ORDER BY timestamp DESC"
		)
	);

	echo '<div class="wrap">';
	echo '<h1>Above the Fold Link Tracker</h1>';

	if (empty($results)) {
		echo '<p>No data available for the past 7 days.</p>';
	} else {
		echo '<table class="widefat fixed striped">';
		echo '<thead>
				<tr>
					<th>Date/Time</th>
					<th>Screen Size</th>
					<th>Links Above the Fold</th>
				</tr>
			  </thead>';
		echo '<tbody>';

		foreach ($results as $row) {
			$links = maybe_unserialize($row->links);
			if (is_array($links)) {
				$linkList = '<ul>';
				foreach ($links as $link) {
					$linkList .= '<li><a href="' . esc_url($link) . '" target="_blank">' . esc_html($link) . '</a></li>';
				}
				$linkList .= '</ul>';
			} else {
				$linkList = esc_html($links);
			}

			printf(
				'<tr>
					<td>%s</td>
					<td>%dx%d</td>
					<td>%s</td>
				</tr>',
				esc_html($row->timestamp),
				(int) $row->screen_width,
				(int) $row->screen_height,
				$linkList
			);
		}

		echo '</tbody></table>';
	}

	echo '</div>';
}