<?php
function syncWithGitHub($filePath, $githubRepo, $githubDirectory, $githubToken) {
    $fileName = basename($filePath);
    $content = file_get_contents($filePath);
    
    // Retrieve the SHA of the existing file, if it exists
    $existingFileSHA = getExistingFileSHA($githubRepo, $githubDirectory, $fileName, $githubToken);
    
    // Prepare data for GitHub API
    $data = [
        'message' => 'Sync file ' . $fileName,
        'content' => base64_encode($content),
        'sha' => $existingFileSHA // Use the existing file SHA if it exists
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
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $dataJson,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    
    // Execute cURL session
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        github_sync_log_error($fileName, curl_error($ch));
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Log the response
    github_sync_log_response($fileName, $response);
}

// Function to retrieve the SHA of an existing file in GitHub
function getExistingFileSHA($githubRepo, $githubDirectory, $fileName, $githubToken) {
    // GitHub API endpoint URL
    $url = 'https://api.github.com/repos/' . $githubRepo . '/contents/' . $githubDirectory . '/' . $fileName;
    
    // Headers for authentication
    $headers = [
        'Authorization: token ' . $githubToken,
        'User-Agent: GitHub Sync Plugin' // Replace with your plugin name or identifier
    ];
    
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    
    // Execute cURL session
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        github_sync_log_error('Error getting file SHA', curl_error($ch));
        return null;
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Decode the response
    $response = json_decode($response, true);
    
    // Return the SHA of the file, if it exists
    if (isset($response['sha'])) {
        return $response['sha'];
    } else {
        return null;
    }
}