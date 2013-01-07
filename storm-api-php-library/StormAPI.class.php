<?php
	/*
	 * Author: Jason Gillman Jr.
	 * Email: jgillman@liquidweb.com
	 * Description: This is my attempt at writing a PHP wrapper that will ease Storm API calls with PHP
	 * 				It will be designed to use the JSON format for talking with the API server.
	 * 				$api_method is as described in docs (Case doesn't matter)
	 * 				request() method returns an array generated from the API return
	 */
	class StormAPI
	{
		// Let's define attributes
		private $api_user, $api_pass, $base_url, $api_format, $api_full_uri, $api_request;
		private $api_request_body, $api_method, $api_params, $api_return, $api_version; 
		
		function __construct($api_user, $api_pass, $api_method, $api_version = "1")
		{	
			//$this->api_user = $api_user;
			//$this->api_pass = $api_pass;
			//$this->api_method = $api_method;
			//$this->version = $api_version;
			$this->base_url = 'https://api.stormondemand.com/';
			$this->api_format = 'json';
			
			$this->api_full_uri = $this->base_url . "v" . $api_version . "/" . $api_method . "." . $this->api_format;
			$this->api_request = curl_init($this->api_full_uri); // Instantiate
			curl_setopt($this->api_request, CURLOPT_RETURNTRANSFER, TRUE); // Don't dump directly to output
			curl_setopt($this->api_request, CURLOPT_SSL_VERIFYPEER, TRUE); // It does look like verification works now.
			curl_setopt($this->api_request, CURLOPT_USERPWD, "$api_user:$api_pass"); // Pass the creds
		}
		
		function add_param($parameter, $value)
		{
			$this->api_request_body['params'][$parameter] = $value;
		}
		
		function clear_params()
		{
			unset($this->api_request_body);
			curl_setopt($this->api_request, CURLOPT_HTTPGET, TRUE); //If the request was previously run with params, this cleans those out. Otherwise they go back with the request
		}
		
		function new_method($api_method, $clearparams = TRUE) // Clears out parameters by default, since they may not apply now
		{
			if($clearparams == TRUE)
			{
				unset($this->api_request_body);
				curl_setopt($this->api_request, CURLOPT_HTTPGET, TRUE); //If the request was previously run with params, this cleans those out. Otherwise they go back with the request
			}
			
			$this->api_method = $api_method; // New method, coming right up!
			$this->api_full_uri = $this->base_url . $this->api_method . "." .$this->api_format; // New URI since method change
			curl_setopt($this->api_request, CURLOPT_URL, $this->api_full_uri);
			
		}
		
		function request()
		{
			if(is_array($this->api_request_body)) // We have params
			{
				curl_setopt($this->api_request, CURLOPT_POST, TRUE); //POST method since we'll be feeding params
				curl_setopt($this->api_request, CURLOPT_HTTPHEADER, Array('Content-type: application/json')); // Since we'll be using JSON
				curl_setopt($this->api_request, CURLOPT_POSTFIELDS, json_encode($this->api_request_body)); // Insert the parameters
			}
			
			// Now send the request and get the return on investment
			try
			{
				return json_decode(curl_exec($this->api_request), TRUE); // Pull the trigger and get nice pretty arrays of returned data
			}
			catch (Exception $e)
			{
				echo 'Error: ' . $e->getMessage();
			}
		}
	}
?>