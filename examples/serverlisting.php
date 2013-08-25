<?php
	require_once('../StormAPI.class.php');
	
	$apiUser = "api_user";
	$apiPass = "api_pass";
	$apiMethod = "storm/server/list";
	$apiVersion = "v1";
	
	$storm = new StormAPI($apiUser, $apiPass, $apiMethod, $apiVersion);
	
	$storm->addParam("page_size", "999");
	$results = $storm->request();
	
	foreach($results['items'] as $item)
	{
		echo $item['domain']  . " || " . $item['uniq_id'] . "\n"; 
	}	
?>