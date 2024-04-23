<?php
// Add logs page to WordPress admin dashboard
function github_sync_logs_page() {
  add_menu_page('GitHub Sync Logs', 'GitHub Sync Logs', 'manage_options', 'github-sync-logs', 'github_sync_logs');
}

// Logs page content
function github_sync_logs() {
  $log_file = plugin_dir_path(__FILE__) . 'github-sync.log';
  $log_content = file_get_contents($log_file);

  // Clear logs if older than 2 days
  github_sync_clear_old_logs();
?>
<div class="wrap github-sync-logs">
  <h1>GitHub Sync Logs</h1>
  <div class="log-container">
    <textarea rows="20" cols="100" readonly><?php echo $log_content; ?></textarea>
  </div>
  <div class="log-actions">
    <button class="button button-primary" onclick="github_sync_clear_logs('<?php echo plugin_dir_path(__FILE__); ?>')">Clear Logs</button>
  </div>
</div>
<?php
}

// Log response
function github_sync_log_response($fileName, $response) {
  $log_file = plugin_dir_path(__FILE__) . 'github-sync.log';
  $log_entry = sprintf(
    "[%s] File: %s, Event: Synced (%s)\n",
    date('Y-m-d H:i:s'),
    $fileName,
    truncate_string($response, 100) // Truncate long responses
  );
  file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Clear logs
function github_sync_clear_logs($plugin_dir_path) {
  $log_file = $plugin_dir_path . 'github-sync.log';
  file_put_contents($log_file, '');
}

// Clear logs if older than 2 days
function github_sync_clear_old_logs() {
  $log_file = plugin_dir_path(__FILE__) . 'github-sync.log';
  $last_modified = filemtime($log_file);
  $current_time = time();
  $days_since_last_modified = ($current_time - $last_modified) / (24 * 60 * 60);

  if ($days_since_last_modified >= 2) {
    github_sync_clear_logs(plugin_dir_path(__FILE__));
  }
}

// Truncate long strings
function truncate_string($string, $length) {
  return substr($string, 0, $length) . (strlen($string) > $length ? '...' : '');
}