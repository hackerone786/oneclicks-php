<?php
require "env.php";
function downloadFiles($url, $cookies, $toolDomain, $host, $is_replace=False, $find="", $replace=""){

    global $cookies;
    global $toolDomain;
    global $host;

    $parsedUrl = parse_url($url);  
    
    // Assuming your root path is the script's directory.
    $basePath = $_SERVER['DOCUMENT_ROOT'];

    // Extract the path and filename from the URL.
    $filePath = $basePath . $parsedUrl['path'];

    // Check if directory exists, if not, create it.
    $directory = dirname($filePath);
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    // Check if the file exists.
    if (!file_exists($filePath)) {
        // File doesn't exist, download it.
		$multiCurl = [];
		$mh = curl_multi_init();
		
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language: en-US,en;q=0.9',
            'cache-control: max-age=0',
            'sec-ch-ua: "Not=A?Brand";v="99", "Chromium";v="118"',
            'sec-ch-ua-arch: "x86"',
            'sec-ch-ua-bitness: "64"',
            'sec-ch-ua-full-version: "118.0.5993.159"',
            'sec-ch-ua-full-version-list: "Not=A?Brand";v="99.0.0.0", "Chromium";v="118.0.5993.159"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-model: ""',
            'sec-ch-ua-platform: "Windows"',
            'sec-ch-ua-platform-version: "7.0.0"',
            'sec-fetch-dest: document',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: same-origin',
            'sec-fetch-user: ?1',
            'upgrade-insecure-requests: 1',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36',
        ]);
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);

        $fileData  = curl_exec($ch);
        // if (!str_contains($fileData, "302 Found")){
            $fileData = str_replace($toolDomain, $host , $fileData);
            if ($is_replace){
                $fileData = str_replace($find, $replace, $fileData);  
            }

            file_put_contents($filePath, $fileData);
            echo $fileData;
        // }
        if (curl_errno($ch)) {
            die('Error: ' . curl_error($ch));
        }

        curl_close($ch);

        // Save the file.
        
    } else {
        echo file_get_contents($filePath);
    }

}
//===============================================
function isHomepage($url) {
    // Homepage is the root or URL without any slugs or file extensions
    $path = parse_url($url, PHP_URL_PATH);
    
    // Check if the path is '/' (homepage) or empty (root URL)
    return ($path === '/' || empty($path));
}

//===============================================

function get_cookies_from_api_without_verify($prefix) {
    // Set API URL and API Key from environment variables
    $api_url_full = $_ENV['API_URL'].'/oneclick/access_without_verify/' . $prefix;

    
    $cache_path = __DIR__ . '/cache/.cache';

    // Check if cache exists and is not expired (1 hour old)
    if (file_exists($cache_path)) {
        $cache_time = filemtime($cache_path);
        $current_time = time();
        
        // If cache is less than 1 hour old, use cached value
        if (($current_time - $cache_time) < 3600) {
            return file_get_contents($cache_path); // Return immediately if cache is valid
        }
    }

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url_full);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $_ENV['API_KEY']
    ]);

    // Execute cURL request and get response
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        echo 'cURL Error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    // Close cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    if (isset($data['access_configuration_preferences'][0]['accounts'][0]) && !empty($data['access_configuration_preferences'][0]['accounts'][0])) {
        $jsonCookies = $data['access_configuration_preferences'][0]['accounts'][0];

        $cookies = json_decode($jsonCookies, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
            exit;
        }

        $cookiePairs = [];
        foreach ($cookies as $cookie) {
            if (isset($cookie['name']) && isset($cookie['value'])) {
                $cookiePairs[] = $cookie['name'] . '=' . $cookie['value'];
            }
        }

        if (empty($cookiePairs)) {
            return null;
        }

        // Convert cookies to a string and save to cache
        $cookieString = implode('; ', $cookiePairs);

        // Ensure cache directory exists with proper permissions
        $cacheDir = __DIR__ . '/cache';
        if (!is_dir($cacheDir)) {
            if (!mkdir($cacheDir, 0777, true)) {
                error_log("Failed to create cache directory: $cacheDir");
                return $cookieString; // Return without caching
            }
        }
        
        // Ensure cache directory is writable
        if (!is_writable($cacheDir)) {
            error_log("Cache directory not writable: $cacheDir");
            return $cookieString; // Return without caching
        }

        // Save the cookie string to cache file with absolute path
        $absoluteCachePath = $cacheDir . '/.cache';
        if (file_put_contents($absoluteCachePath, $cookieString) === false) {
            error_log("Failed to write cache file: $absoluteCachePath");
        }

        return $cookieString;

    } else {
        return null;
    }
}

//=========================================
function getAccess_data($accessId) {
    $api_url_full = $_ENV['API_URL'].'/oneclick/access/'.$accessId;


    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url_full);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $_ENV['API_KEY']
    ]);

    // Execute cURL request and get the response
    $response = curl_exec($ch);

    // Check if there were any errors
    if(curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);
	
	if(!empty($data) && is_array($data)){
		$data['user_ip'] 	= get_client_ip();
		$data['user_agent']	= $_SERVER['HTTP_USER_AGENT'];
	}

    return $data;
}

//=========================================
//=========================================
function check_user_limits($product_id,$user_email) {
    $api_url_full = $_ENV['API_URL'].'/oneclick/product_usage/'.$product_id.'?user_email='.$user_email;


    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url_full);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $_ENV['API_KEY']
    ]);

    // Execute cURL request and get the response
    $response = curl_exec($ch);
	$resp_data = curl_getinfo($ch);

    // Check if there were any errors
    if(curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);
	if(isset($data['product_limit']) && isset($data['user_usage']) && $data['user_usage']<$data['product_limit']){
		
		return true;
		
	}elseif(isset($data['product_limit'])){		
	
	
		echo json_encode([
    "status" => "success",
    "response" => [
        "download" => [
            "url" => "https://".$_SERVER['HTTP_HOST'].'/limit-reached/?limit='.$data['product_limit'],
            "name" => "Beer Bar",
            "download_url" => "https://".$_SERVER['HTTP_HOST'].'/limit-reached/?limit='.$data['product_limit'],
            "download_license_uuid" => "xxx",
            "job_ids" => []
        ]
    ],
    "meta" => [
        "total_time" => 48.37,
        "db_query_time" => 0,
        "db_query_count" => 0
    ],
    "elements" => null,
    "message" => "You have downloaded Beer Bar icon successfully."
]);
		
		




		
		exit;
	}
	
	
}
//=========================================
function update_user_limits($product_id,$user_email) {
    $api_url_full = $_ENV['API_URL'].'/oneclick/product_usage/'.$product_id.'/increment?user_email='.$user_email.'&increment_by=1';


    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $api_url_full);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: ' . $_ENV['API_KEY']
    ]);

    // Execute cURL request and get the response
    $response = curl_exec($ch);
	$resp_data = curl_getinfo($ch);
// pa($response);
// pa($resp_data);
// exit;
    // Check if there were any errors
    if(curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);
	
	
	
}
//==========================================


//=========================================
function pa($data){
	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

//=========================================
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = '';
    return $ipaddress;
}
//========================================
function decodeJWT($jwt) {
    // Split the JWT into its three parts: header, payload, signature
    $jwtParts = explode('.', $jwt);

    if (count($jwtParts) !== 3) {
        return null; // Invalid JWT format
    }

    // Base64 decode the payload part (second part of JWT)
    $payload = base64_decode(strtr($jwtParts[1], '-_', '+/'));

    // Convert the decoded payload from JSON to an associative array
    $decodedPayload = json_decode($payload, true);

    return $decodedPayload;
}

//====================================
function isTokenExpired($jwt) {
    // Decode the JWT and get the payload
    $decoded = decodeJWT($jwt);
    
    if ($decoded === null || !isset($decoded['exp'])) {
        return true; // Invalid JWT or no exp field, consider the token expired
    }

    // Get the expiration timestamp from the payload
    $expirationTime = $decoded['exp'];

    // Get the current time as a Unix timestamp
    $currentTime = time();

    // Compare current time with expiration time
    return $currentTime > $expirationTime; // Returns true if expired, false if not
}
//====================================
function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(16); // Generate a random initialization vector
    $encrypted = openssl_encrypt(serialize($data), 'AES-256-CBC', $key, 0, $iv);
    // Return both the encrypted data and the iv (used for decryption)
    return base64_encode($iv . $encrypted);
}

//====================================
function decryptData($encryptedData, $key) {
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, 16); // Extract the IV
    $encrypted = substr($data, 16); // Extract the encrypted data
    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    return unserialize($decrypted); // Unserialize to get the original array
}
?>