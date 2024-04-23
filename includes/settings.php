<?php
// Add settings page to WordPress admin dashboard
function github_sync_settings_page() {
    add_menu_page('GitHub Sync', 'GitHub Sync', 'manage_options', 'github-sync', 'github_sync_settings');
}

// Settings page content
function github_sync_settings() {
    // Check if settings are saved
    if (isset($_POST['github_sync_settings'])) {
        github_sync_save_settings();
    }

    // Retrieve settings from the database
    $settings = github_sync_get_settings();
    ?>
    <div class="wrap github-sync-settings">
    <h1>GitHub Sync Settings</h1>
    <form method="post" action="">
        <div class="form-group">
            <label for="directory">Directory to monitor:</label>
            <input type="text" id="directory" name="directory" value="<?php echo esc_attr($settings['directory']); ?>" />
            <p class="description">Enter the path to the directory you want to monitor for file changes.</p>
        </div>

        <div class="form-group">
            <label for="github_repo">GitHub repository:</label>
            <input type="text" id="github_repo" name="github_repo" value="<?php echo esc_attr($settings['github_repo']); ?>" />
            <p class="description">Enter the GitHub repository where you want to sync your files (e.g., username/repo-name).</p>
        </div>

        <div class="form-group">
            <label for="github_directory">GitHub directory:</label>
            <input type="text" id="github_directory" name="github_directory" value="<?php echo esc_attr($settings['github_directory']); ?>" />
            <p class="description">Enter the directory within the GitHub repository where you want to sync your files.</p>
        </div>

        <div class="form-group">
            <label for="github_token">GitHub personal access token:</label>
            <input type="text" id="github_token" name="github_token" value="<?php echo esc_attr($settings['github_token']); ?>" />
            <p class="description">Enter your GitHub personal access token with appropriate permissions.</p>
        </div>

        <div class="form-group">
            <label for="schedule_interval">Schedule Interval (minutes):</label>
            <input type="number" id="schedule_interval" min="1" name="schedule_interval" value="<?php echo esc_attr($settings['schedule_interval']); ?>" />
            <p class="description">Enter the interval (in minutes) at which you want to sync your files with GitHub.</p>
        </div>

        <p class="submit"><input type="submit" name="github_sync_settings" class="button button-primary" value="Save Settings" /></p>
    </form>

    <div class="instructions">
        <h2>Instructions</h2>
        <ol>
            <li>Enter the path to the directory you want to monitor for file changes.</li>
            <li>Enter your GitHub repository details (username/repo-name and the directory within the repo).</li>
            <li>Generate a GitHub personal access token with appropriate permissions and enter it in the designated field.</li>
            <li>Set the schedule interval (in minutes) at which you want to sync your files with GitHub.</li>
            <li>Click the "Save Settings" button to save your settings and start syncing files.</li>
        </ol>
    </div>
</div>
    <?php
}

// Save settings
function github_sync_save_settings() {
    // Sanitize and store settings in WordPress database
    $directory = sanitize_text_field($_POST['directory']);
    $github_repo = sanitize_text_field($_POST['github_repo']);
    $github_directory = sanitize_text_field($_POST['github_directory']);
    $github_token = sanitize_text_field($_POST['github_token']);
    $schedule_interval = absint($_POST['schedule_interval']);

    update_option('github_sync_directory', $directory);
    update_option('github_sync_github_repo', $github_repo);
    update_option('github_sync_github_directory', $github_directory);
    update_option('github_sync_github_token', $github_token);
    update_option('github_sync_schedule_interval', $schedule_interval);

    // Start monitoring the directory
    github_sync_monitor_directory();

    // Schedule the cron job
    if (!wp_next_scheduled('github_sync_cron_hook')) {
        wp_schedule_event(time(), 'github_sync_interval', 'github_sync_cron_hook');
    }
}

// Get settings from the database
function github_sync_get_settings() {
    $directory = get_option('github_sync_directory', '');
    $github_repo = get_option('github_sync_github_repo', '');
    $github_directory = get_option('github_sync_github_directory', '');
    $github_token = get_option('github_sync_github_token', '');
    $schedule_interval = get_option('github_sync_schedule_interval', 60);

    return array(
        'directory' => $directory,
        'github_repo' => $github_repo,
        'github_directory' => $github_directory,
        'github_token' => $github_token,
        'schedule_interval' => $schedule_interval,
    );
}