<?php
	require_once('../StormAPI.class.php');
	
	$api_user = "api_user";
	$api_pass = "api_pass";
	$api_method = "server/list";
	$api_version = "1";
	
	$storm = new StormAPI($api_user, $api_pass, $api_method, $api_version);
	
	$storm->add_param("page_size", "999");
	$results = $storm->request();
	
	foreach($results['items'] as $item)
	{
		echo $item['domain']  . " || " . $item['uniq_id'] . "\n"; 
	}
	
?>