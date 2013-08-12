<?php
	/**
	 * This file contains the StormAPI Class for working with Liquid Web's Storm Platorm API
	 * 
	 * @package StormAPI
	 * @license http://opensource.org/licenses/EPL-1.0 Eclipse Public License
	 * @author Jason Gillman Jr <jgillman@liquidweb.com>
	 * 
	 */

	/**
	 * This class allows for making calls to the Storm Platform API.
	 * 
	 * The class will perform automatic encoding and decoding of JSON for passing and retrieving information from the Storm Platform API
	 * The returned output is an array
	 * View the Storm Platform API documentation for a listing of methods: http://www.liquidweb.com/StormServers/api/docs/v1/
	 * 
	 * @author Jason Gillman Jr <jgillman@liquidweb.com>
	 * @package StormAPI
	 *
	 */
	class StormAPI
	{
		// Let's define attributes
		private $api_user, $api_pass, $base_url, $api_format, $api_full_uri, $api_request;
		private $api_request_body, $api_method, $api_params, $api_return, $api_version, $api_port; 
		
		/**
		 * 
		 * @param string $api_user The Storm API User
		 * @param string $api_pass The API User's Password
		 * @param string $api_method The Storm API Method being called. Example: "server/list"
		 * @param int $api_version The API version to use. Defaults to v1
		 * 
		 */
		function __construct($api_user, $api_pass, $api_method, $api_version = "v1")
		{	
			//$this->api_user = $api_user;
			//$this->api_pass = $api_pass;
			//$this->api_method = $api_method;
			//$this->version = $api_version;
			$this->base_url = 'https://api.stormondemand.com/';
			$this->api_format = 'json';
			$this->api_port = 443;
			define("API_VERSION", $api_version);
			
			$this->api_full_uri = $this->base_url . $api_version . "/" . $api_method . "." . $this->api_format;
			$this->api_request = curl_init($this->api_full_uri); // Instantiate
			curl_setopt($this->api_request, CURLOPT_RETURNTRANSFER, TRUE); // Don't dump directly to output
			curl_setopt($this->api_request, CURLOPT_PORT, $this->api_port); // The port to call to.
			curl_setopt($this->api_request, CURLOPT_SSL_VERIFYPEER, TRUE); // It does look like verification works now.
			curl_setopt($this->api_request, CURLOPT_USERPWD, "$api_user:$api_pass"); // Pass the creds
		}
		
		/**
		 * 
		 * @param string $parameter The parameter for the Storm API Method
		 * @param string $value The value of the parameter
		 * @return array Appends the parameter and value to the array being used for storing the API method's parameters
		 * 
		 */
		function add_param($parameter, $value)
		{
			$this->api_request_body['params'][$parameter] = $value;
		}
		
		/**
		 * 
		 * @return null Clears the array being used for storing the API method's parameters
		 * 
		 */
		function clear_params()
		{
			unset($this->api_request_body);
			curl_setopt($this->api_request, CURLOPT_HTTPGET, TRUE); //If the request was previously run with params, this cleans those out. Otherwise they go back with the request
		}
		
		/**
		 * 
		 * @param string $api_method The new Storm API method you would like to use
		 * @param boolean $clearparams Defaults TRUE - clears the array being used for storing the API method's parameters
		 * @return null
		 * 
		 */
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
		
		/**
		 * 
		 * This method will return the server, port, method, and parameters set
		 * 
		 * @return string Returns a string containing the server, port, method, and parameters currently set
		 */
		function debug_info()
		{
			$this->debug_vars = "Full URI: " . $this->api_full_uri . "\n";
			$this->debug_vars .= "Port: " . $this->api_port . "\n";
			$this->debug_vars .= "Parameters as follows: \n";
			
			if(isset($this->api_request_body))
			{
				foreach($this->api_request_body['params'] as $par_key => $par_value)
				{
					if($is_array($par_value))
					{
						$this->debug_vars .= $par_key . "=>" . print_r($par_value, TRUE);
					}
					else
					{
						$this->debug_vars .= $par_key . " => " . $par_value . "\n";
					}
				}
			}
			else
			{
				$this->debug_vars .= "No Parameters\n";
			}
			
			$this->debug_vars .= "=== End Params ===\n";
			
			return $this->debug_vars;
		}
		
		/**
		 *
		 * This method will return a list of available API methods
		 *
		 * @param string $docVersion The version of the API being used. Defaults to the version of the API specified upon construction
		 * @return array Returns an array of the available API methods based on the version supplied
		 */
		function listMethods($docVersion = API_VERSION)
		{
			$this->api_docs = file_get_contents("http://www.liquidweb.com/StormServers/api/docs/" . $docVersion . "/docs.json");
			$this->api_docs = json_decode($this->api_docs, TRUE);
		
			foreach($this->api_docs as $groupName => $group)
			{
				foreach($group['__methods'] as $methodName => $methodSpecs)
				{
					$this->methodList[$groupName][] = $methodName;
				}
			}
			return $this->methodList;
		}
		
		/**
		 * 
		 * This method makes the call to the Storm Platform API and retrieves the information
		 * 
		 * @return array Returns a JSON decoded array of returned information from the API call
		 * 
		 */
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