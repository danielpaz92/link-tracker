<?php
defined('ABSPATH') || exit;

/**
 * Adds the admin menu item under "Tools".
 */
add_action('admin_menu', function () {
	add_management_page(
		__('Above the Fold Tracker', 'atflt'),
		__('Above the Fold Tracker', 'atflt'),
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

	// Filters
	$screen_filter = isset($_GET['screen_width']) ? intval($_GET['screen_width']) : null;
	$url_filter = isset($_GET['link_contains']) ? sanitize_text_field($_GET['link_contains']) : null;
	$order_by = isset($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'timestamp';
	$order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

	// Base query
	$query = "SELECT * FROM $table WHERE timestamp >= NOW() - INTERVAL 7 DAY";

	if ($screen_filter) {
		$query .= $wpdb->prepare(" AND screen_width = %d", $screen_filter);
	}

	if ($url_filter) {
		$query .= " AND links LIKE '%" . esc_sql($url_filter) . "%'";
	}

	$valid_orderby = ['timestamp', 'screen_width', 'screen_height'];
	if (!in_array($order_by, $valid_orderby, true)) {
		$order_by = 'timestamp';
	}
	$query .= " ORDER BY $order_by $order";

	$results = $wpdb->get_results($query);

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__('Above the Fold Link Tracker', 'atflt') . '</h1>';

	// Filter form
	echo '<form method="get">';
	echo '<input type="hidden" name="page" value="atflt-dashboard" />';
	echo '<input type="text" name="screen_width" placeholder="' . esc_attr__('Filter by screen width', 'atflt') . '" value="' . esc_attr($screen_filter) . '" />';
	echo '&nbsp;';
	echo '<input type="text" name="link_contains" placeholder="' . esc_attr__('Filter by URL keyword', 'atflt') . '" value="' . esc_attr($url_filter) . '" />';
	echo '&nbsp;';
	submit_button(__('Filter', 'atflt'), '', '', false);
	echo '</form><br>';

	if (empty($results)) {
		echo '<p>' . esc_html__('No data available for the selected filters.', 'atflt') . '</p>';
	} else {
		$columns = [
			'timestamp' => __('Date/Time', 'atflt'),
			'screen_width' => __('Width', 'atflt'),
			'screen_height' => __('Height', 'atflt'),
		];
		$current_url = admin_url('tools.php?page=atflt-dashboard');
		echo '<table class="widefat fixed striped">';
		echo '<thead><tr>';
		foreach ($columns as $key => $label) {
			$new_order = ($order_by === $key && $order === 'ASC') ? 'desc' : 'asc';
			$url = add_query_arg([
				'orderby' => $key,
				'order' => $new_order,
				'screen_width' => $screen_filter,
				'link_contains' => $url_filter,
			], $current_url);
			echo '<th><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></th>';
		}
		echo '<th>' . esc_html__('Links Above the Fold', 'atflt') . '</th>';
		echo '</tr></thead><tbody>';

		foreach ($results as $row) {
			$links = maybe_unserialize($row->links);
			$linkList = is_array($links)
				? '<ul>' . implode('', array_map(fn($l) => '<li><a href="' . esc_url($l) . '" target="_blank">' . esc_html($l) . '</a></li>', $links)) . '</ul>'
				: esc_html($links);

			printf(
				'<tr>
					<td>%s</td>
					<td>%d</td>
					<td>%d</td>
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
