<?php
defined('ABSPATH') || exit;

/**
 * Creates the custom table to store visits.
 */
function atflt_create_table() {
	global $wpdb;

	$table = $wpdb->prefix . 'atflt_visits';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		timestamp DATETIME NOT NULL,
		screen_width INT NOT NULL,
		screen_height INT NOT NULL,
		links TEXT NOT NULL,
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);
}

/**
 * Deletes records older than 7 days from the database.
 */
function atflt_cleanup_old_records() {
	global $wpdb;
	$table = $wpdb->prefix . 'atflt_visits';

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $table WHERE timestamp < NOW() - INTERVAL 7 DAY"
		)
	);
}