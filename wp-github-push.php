<?php
/*
 * Plugin Name: WP Github Push
 * Plugin URI: https://wrygo.com/wp-github-push
 * Description: Automate syncing chosen file types from a WordPress directory with a GitHub repository or its subdirectory.
 * Version: 1.1
 * Author: Abdur Rob Badhon
 * Author URI: https://wrygo.com/wp-github-push
 * Text Domain: wp-github-push
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Include necessary files
include 'includes/settings.php';
include 'includes/sync.php';
include 'includes/logs.php';

// Register settings page
add_action('admin_menu', 'github_sync_settings_page');

// Register logs page
add_action('admin_menu', 'github_sync_logs_page');

// Handle cron job
function github_sync_cron_job() {
    github_sync_monitor_directory();
}
add_action('github_sync_cron_hook', 'github_sync_cron_job');

// Unschedule cron job on plugin deactivation
function github_sync_deactivation() {
    $timestamp = wp_next_scheduled('github_sync_cron_hook');
    wp_unschedule_event($timestamp, 'github_sync_cron_hook');
}
register_deactivation_hook(__FILE__, 'github_sync_deactivation');

// Register cron interval
function github_sync_cron_intervals($schedules) {
    $schedules['github_sync_interval'] = array(
        'interval' => get_option('github_sync_schedule_interval') * 60,
        'display' => 'GitHub Sync Interval'
    );
    return $schedules;
}
add_filter('cron_schedules', 'github_sync_cron_intervals');

// Enqueue CSS and JavaScript files
function github_sync_enqueue_assets() {
    wp_enqueue_style('github-sync-styles', plugins_url('includes/assets/app.css', __FILE__));
    wp_enqueue_script('github-sync-scripts', plugins_url('includes/assets/app.js', __FILE__), array(), false, true);
}
add_action('admin_enqueue_scripts', 'github_sync_enqueue_assets');

// Handle AJAX request to clear logs
add_action('wp_ajax_github_sync_clear_logs', 'github_sync_clear_logs_ajax');
function github_sync_clear_logs_ajax() {
    $plugin_dir_path = sanitize_text_field($_POST['plugin_dir_path']);
    github_sync_clear_logs($plugin_dir_path);
    wp_die();
}