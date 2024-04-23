<?php
// Set up WordPress filesystem API to monitor directory
function github_sync_monitor_directory() {
    // Retrieve settings
    $settings = github_sync_get_settings();

    // Set up filesystem API to monitor the directory
    $directory = $settings['directory'];
    $files = scandir($directory);

    // Remove '.' and '..' from the list
    $files = array_diff($files, ['.', '..']);

    // Check for new or updated files
    foreach ($files as $file) {
        // Check if the file is a markdown file
        if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
            $filePath = $directory . '/' . $file;
            syncWithGitHub($filePath, $settings['github_repo'], $settings['github_directory'], $settings['github_token']);
        }
    }
}

// Sync file with GitHub
function syncWithGitHub($filePath, $githubRepo, $githubDirectory, $githubToken) {
    $fileName = basename($filePath);
    $content = file_get_contents($filePath);

    // Prepare data for GitHub API
    $data = [
        'message' => 'Sync file ' . $fileName,
        'content' => base64_encode($content)
    ];

    // Encode data as JSON
    $dataJson = json_encode($data);

    // GitHub API endpoint URL
    $url = 'https://api.github.com/repos/' . $githubRepo . '/contents/' . $githubDirectory . '/' . $fileName;

    // Headers for authentication and content type
    $headers = [
        'Authorization: token ' . $githubToken,
        'Content-Type: application/json',
        'User-Agent: GitHub Sync Plugin' // Replace with your plugin name or identifier
    ];

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute cURL session
    $response = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    // Log the response
    github_sync_log_response($fileName, $response);
}