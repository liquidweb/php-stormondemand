<?php
	require_once('../StormAPI.class.php');
	
	$apiUser = "api_user";
	$apiPass = "api_pass";
	$apiMethod = "storm/server/list";
	$paramsArray = FALSE;	
	$apiVersion = "v1";
	
	$storm = new \LiquidWeb\StormAPI($apiUser, $apiPass, $apiMethod, $paramsArray, $apiVersion);
	
	$storm->addParam("page_size", 999);
	$results = $storm->request();
	
	foreach($results['items'] as $item)
	{
		echo $item['domain']  . " || " . $item['uniq_id'] . "\n"; 
	}	
?>
