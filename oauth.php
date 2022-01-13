<?php

$authorize_url = "https://pretix.eu/api/v1/oauth/authorize";
$token_url = "https://pretix.eu/api/v1/oauth/token";

//	callback URL specified when the application was defined--has to match what the application says
$callback_uri = "https://" . $_SERVER['SERVER_NAME'] . "/xrevent-creator/";

$test_endpoint = "me";
$api_url = "https://pretix.eu/api/v1/";

//	client (application) credentials
$client_id = "1jMmI9bqw6MsUCzeIw3QhBuSFucnvsKJIe8eW8By";
$client_secret = "3TciTvJycQxRCtEgzdlp4UZjTyL9fdhzLR5PRojPm8sdbkEVekSeCg5yO4dlo7zjZNeWIU1Lw7HFfdAF9j1Qg4JFIn3B5xWZpnD4oJc27VjFanmJwiryu2BU0EteKWYM";



if ($_POST["authorization_code"]) {
	//	what to do if there's an authorization code
	$access_token = getAccessToken($_POST["authorization_code"]);
	$resource = getResource($access_token);
	echo $resource;
} elseif ($_GET["code"]) {
	$access_token = getAccessToken($_GET["code"]);
	//$resource = getResource($access_token);
	echo $access_token;
} elseif (isset($_GET["access_token"], $_GET["endpoint"], $_GET["prop"], $_GET["key"], $_GET["value"])){	
	$resource = patchRequest($_GET["access_token"], $_GET["endpoint"], $_GET["prop"], $_GET["key"], $_GET["value"]);
	echo $resource;
} elseif (isset($_GET["access_token"], $_GET["endpoint"], $_GET["content"])){	
	// $content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri";
	$resource = postRequest($_GET["access_token"], $_GET["endpoint"], $_GET["content"]);
	echo $resource;
} elseif (isset($_GET["access_token"], $_GET["endpoint"], $_GET["action"])){
	$resource = deleteRequest($_GET["access_token"], $_GET["endpoint"], $_GET["action"]);
	echo $resource;
} elseif (isset($_GET["access_token"], $_GET["endpoint"])){
	$resource = getResource($_GET["access_token"], $_GET["endpoint"]);
	echo $resource;
} elseif (isset($_GET["access_token"])){
	$resource = getResource($_GET["access_token"], $test_endpoint);
	echo $resource;
} else {
	//	what to do if there's no authorization code
	getAuthorizationCode();
}



//	step A - simulate a request from a browser on the authorize_url
//		will return an authorization code after the user is prompted for credentials
function getAuthorizationCode() {
	global $authorize_url, $client_id, $callback_uri;

	$authorization_redirect_url = $authorize_url . "?response_type=code&client_id=" . $client_id . "&redirect_uri=" . $callback_uri . "&scope=read+write";

	header("Location: " . $authorization_redirect_url);

	//	if you don't want to redirect
	// echo "Go <a href='$authorization_redirect_url'>here</a>, copy the code, and paste it into the box below.<br /><form action=" . $_SERVER["PHP_SELF"] . " method = 'post'><input type='text' name='authorization_code' /><br /><input type='submit'></form>";
}

//	step I, J - turn the authorization code into an access token, etc.
function getAccessToken($authorization_code) {
	global $token_url, $client_id, $client_secret, $callback_uri;

	$authorization = base64_encode("$client_id:$client_secret");
	$header = array("Accept: application/json, text/javascript", "Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
	$content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri";

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $token_url,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $content
	));
	$response = curl_exec($curl);
	curl_close($curl);

	if ($response === false) {
		echo "Failed";
		echo curl_error($curl);
		echo "Failed";
	} elseif (json_decode($response)->error) {
		echo "Error:<br />";
		echo $authorization_code;
		echo $response;
	}

	//header("Location: " . $callback_uri . "?access_token=" . json_decode($response)->access_token);
	return json_decode($response)->access_token;
}

//	we can now use the access_token as much as we want to access protected resources
function getResource($access_token, $endpoint) {
	//global $test_api_url;
	global $api_url;

	$header = array("Authorization: Bearer {$access_token}");

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $api_url . $endpoint,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($curl);
	curl_close($curl);
	
	return $response;
	//return json_decode($response, true);
}

//  Used to modify properties on the server
function postRequest($access_token, $endpoint, $content) {
	
	global $api_url;

	$header = array("Accept: application/json, text/javascript","Authorization: Bearer {$access_token}","Content-Type: application/x-www-form-urlencoded");
	//$content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri";

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $content,
		CURLOPT_URL => $api_url . $endpoint,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($curl);
	curl_close($curl);
	
	return $response;
	//return json_decode($response, true);
}

//  Used to patch properties on the server
function patchRequest($access_token, $endpoint, $key, $keyA, $valueA) {
	$json_array = array("item_meta_properties" => array ("$keyA" => "$valueA") );
	$data = json_encode($json_array);

	global $api_url;

	$header = array("Accept: application/json, text/javascript","Authorization: Bearer {$access_token}","Content-Type: application/json");
	//$content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri";

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_CUSTOMREQUEST => 'PATCH',
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_URL => $api_url . $endpoint,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($curl);
	curl_close($curl);
	
	return $response;
	//return json_decode($response, true);
}

//  Used to delete e.g. teams on the server
function deleteRequest($access_token, $endpoint, $action) {
		
	global $api_url;

	$header = array("Accept: application/json, text/javascript","Authorization: Bearer {$access_token}","Content-Type: application/json");
	//$content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri";

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_CUSTOMREQUEST => 'DELETE',
		CURLOPT_URL => $api_url . $endpoint,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($curl);
	curl_close($curl);
	
	return $response;
	//return json_decode($response, true);
}



?>