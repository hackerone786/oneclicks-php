<?php

// Check if the secret key is provided in the request
if (isset($_POST['secret']) && $_POST['secret'] === 'an33zmdx5') {
    $cache_path = 'cache/.cache';

    // Check if the cache file exists
    if (file_exists($cache_path)) {
        // Delete the cache file
        unlink($cache_path);
        echo 'Cache file deleted successfully.';
    } else {
        echo 'Cache file does not exist.';
    }
} else {
    // If the secret key doesn't match, deny access
    echo 'Unauthorized access. Invalid secret key.';
}