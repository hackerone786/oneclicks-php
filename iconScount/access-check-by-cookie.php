<?php

if (isset($_COOKIE['atad'])) {
    // Decrypt the data
    $decryptedData = decryptData($_COOKIE['atad'], $secret_key);
    
    // Validate user IP and user agent
    if (get_client_ip() == $decryptedData['user_ip'] && $_SERVER['HTTP_USER_AGENT'] == $decryptedData['user_agent']) {
        // Validate JWT Token
        
        if ($_ENV['MODE'] != 'development' && isTokenExpired($decryptedData['access_token'])==true) {			
           //Invalid or expired JWT token!
            header("Location: ".'https://'.$_SERVER['HTTP_HOST'].'/expired/');
			exit;
        }
    } else {
        //User IP or User Agent mismatch
		header("Location: ".'https://'.$_SERVER['HTTP_HOST'].'/expired/');
		exit;
    }
} else {
	//No cookie or invalid cookie
    header("Location: ".'https://'.$_SERVER['HTTP_HOST'].'/expired/');
	exit;
}
?>
