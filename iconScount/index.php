<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require "functions.cfg.php";
$secret_key = 'ILn2@qtoyZrunQK!Ys#alxzjSce4^fLb';

if(isset($_GET['access_id']) && !empty($_GET['access_id'])){
	
	require "access-check-by-id.php";
	
}else{
	
	require "access-check-by-cookie.php";

	// pa(check_user_limits($decryptedData['product'],$decryptedData['user_email']));
	// exit;
$prefix 		= 'IconScout_1';
$cookies 		= get_cookies_from_api_without_verify($prefix);
$host 			= $_SERVER['HTTP_HOST'];
$requestUrl 	= $_SERVER['REQUEST_URI'];
$requestType 	= $_SERVER['REQUEST_METHOD'];
$toolDomain 	= "iconscout.com";
$url            = "https://".$toolDomain.$requestUrl;
$staticExts     = ['css', 'js', 'woff2', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp'];
$path           = parse_url($url, PHP_URL_PATH);
$extension      = pathinfo($path, PATHINFO_EXTENSION);
$cacheFile      = '/tmp/homepage_cache.html';
$isCache        = false;
$cacheDirectory = __DIR__ . '/cache';
$cacheFile      = $cacheDirectory . '/homepage_cache.html';

// Check if cache folder exists, create it if not
if (!is_dir($cacheDirectory)) {
    mkdir($cacheDirectory, 0777, true); // Create the folder with write permissions
}

if ($isCache == true && isHomepage($url) && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
    // If cache exists and is less than 1 hour old, use the cached content
    echo file_get_contents($cacheFile);
    exit();
}

if (str_contains($requestUrl, "logout")) {
    echo "Not Allowed";
    exit;
}

if ($extension && in_array(strtolower($extension), $staticExts)) {
    downloadFiles($url, $cookies, $toolDomain, $host);
    exit();
}

$headersArray = [
    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
    'accept-language: en-US,en;q=0.9',
   'cache-control: max-age=0',
   'origin: https://'.$toolDomain ,
   'referer: https://'.$toolDomain ,
   'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="102"',
   'sec-ch-ua-mobile: ?0',
    'sec-ch-ua-platform: "Windows"',
    'sec-fetch-dest: document',
    'sec-fetch-mode: navigate',
    'sec-fetch-site: same-site',
    'sec-fetch-user: ?1',
    'upgrade-insecure-requests: 1',
   'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.5621.0 Safari/537.36',
    'accept-encoding: gzip',
];

$startTime = microtime(true);

$multiCurl = [];
$mh = curl_multi_init();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headersArray);
curl_setopt($ch, CURLOPT_COOKIE, $cookies);
curl_setopt($ch, CURLOPT_ENCODING , "gzip");

if (str_contains($requestType, "POST")) {
    $postdata = file_get_contents("php://input");

    // If the content type is multipart/form-data, we need to forward the files properly
    if (isset($_FILES) && !empty($_FILES)) {
        $postfields = [];

        foreach ($_FILES as $key => $file) {
            $postfields[$key] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        }

        // If there are additional POST parameters
        parse_str($postdata, $params);
        foreach ($params as $key => $value) {
            $postfields[$key] = $value;
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    }
}

if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
    $headersArray[] = 'authorization: '.$_SERVER["HTTP_AUTHORIZATION"];
}
if (isset($_SERVER["CONTENT_TYPE"])) {
    $headersArray[] = 'content-type: '.$_SERVER["CONTENT_TYPE"];
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headersArray);

// Add the request to the multi-curl handle
curl_multi_add_handle($mh, $ch);

// Execute the multi-curl requests
do {
    $status = curl_multi_exec($mh, $active);
    if ($active) {
        curl_multi_select($mh);
    }
} while ($active && $status == CURLM_OK);



// Handle response
if (str_contains($requestUrl, ".json")) {
    // Convert to JSON
    $response = json_decode($response);
    header('Content-Type: application/json; charset=utf-8');
    $response = json_encode($response, JSON_PRETTY_PRINT);
}
// Handle response
if (str_contains($requestUrl, "/download")) {
   
   check_user_limits($decryptedData['product'],$decryptedData['user_email']);  
   
}else{
	$response = curl_multi_getcontent($ch);
}

if (str_contains($response, "download_url")) {
	   update_user_limits($decryptedData['product'],$decryptedData['user_email']);
	}

// Cache the homepage response if it's not cached already
if ($isCache == true && isHomepage($url)) {
    file_put_contents($cacheFile, $response);
}
//$response = str_replace("</body>", "<script>alert()</script></body>")
echo $response;

// Close the multi-curl handle and the single curl handle
curl_multi_remove_handle($mh, $ch);
curl_close($ch);
curl_multi_close($mh);

// $endTime = microtime(true);
// $executionTime = $endTime - $startTime;

// echo "<br>Script 2 Execution Time: $executionTime seconds";
}
?>
