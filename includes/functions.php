<?php
defined('ABSPATH') || exit;

add_action('init', function () {
	if (current_user_can('administrator') && isset($_GET['force_atflt_cleanup'])) {
		atflt_cleanup_old_records();
		wp_die('Cleanup executed.');
	}
});