<?php
	$access_data = getAccess_data($_GET['access_id']);
	
	if(isset($access_data['access_token']) && isTokenExpired($access_data['access_token'])==false){
		
			
		$encryptedData = encryptData($access_data, $secret_key);
		// Store the encrypted string in a cookie
		
		setcookie('atad', $encryptedData, time() + 86400 , '/', '', true, true);
		
		header("Location: ".'https://'.$_SERVER['HTTP_HOST'].'/');
		
	}else{
		header("Location: ".'https://'.$_SERVER['HTTP_HOST'].'/expired/');
	}
	
	exit;
?>