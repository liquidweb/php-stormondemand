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
		private $apiUser, $apiPass, $baseUrl, $apiFormat, $apiFullUri, $apiRequest;
		private $apiRequestBody, $apiMethod, $apiParams, $apiVersion, $apiPort; 
		
		/**
		 * 
		 * @param string $apiUser The Storm API User
		 * @param string $apiPass The API User's Password
		 * @param string $apiMethod The Storm API Method being called. Example: "server/list"
		 * @param string $apiVersion The API version to use. Defaults to v1
		 * 
		 */
		function __construct($apiUser, $apiPass, $apiMethod, $apiVersion = "v1")
		{	
			//$this->apiUser = $apiUser;
			//$this->apiPass = $apiPass;
			//$this->apiMethod = $apiMethod;
			//$this->version = $apiVersion;
			$this->baseUrl = 'https://api.stormondemand.com/';
			$this->apiFormat = 'json';
			$this->apiPort = 443;
			$this->apiVersion = $apiVersion;
			$this->apiMethod = $apiMethod;
			
			$this->apiFullUri = $this->baseUrl . $this->apiVersion . "/" . $apiMethod . "." . $this->apiFormat;
			$this->apiRequest = curl_init($this->apiFullUri); // Instantiate
			curl_setopt($this->apiRequest, CURLOPT_RETURNTRANSFER, TRUE); // Don't dump directly to output
			curl_setopt($this->apiRequest, CURLOPT_PORT, $this->apiPort); // The port to call to.
			curl_setopt($this->apiRequest, CURLOPT_SSL_VERIFYPEER, TRUE); // It does look like verification works now.
			curl_setopt($this->apiRequest, CURLOPT_USERPWD, "$apiUser:$apiPass"); // Pass the creds
		}
		
		/**
		 * 
		 * @param string $parameter The parameter for the Storm API Method
		 * @param string $value The value of the parameter
		 * @return array Appends the parameter and value to the array being used for storing the API method's parameters, or over writes if already set
		 * 
		 */
		function addParam($parameter, $value)
		{
			$this->apiRequestBody['params'][$parameter] = $value;
		}
		
		/**
		 * 
		 * @param string $parameter The Storm API Method's parameter that you want to remove
		 * @return bool Will return TRUE if successful, FALSE if not (such as the parameter didn't actually exist) 
		 * 
		 */
		function removeParam($parameter)
		{
			if(isset($this->apiRequestBody['params'][$parameter]))
			{
				unset($this->apiRequestBody['params'][$parameter]);
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		
		/**
		 * 
		 * @return array|bool Returns an array of the currently set parameters, FALSE if none are set
		 * 
		 */
		function listParams()
		{
			if(count($this->apiRequestBody['params']) > 0)
			{
				return $this->apiRequestBody['params'];
			}
			else
			{
				return FALSE;
			}
		}
		
		/**
		 * 
		 * @return array|bool Returns an array of possible parameters and their optionality for the current method, FALSE if no parameters
		 * 
		 */
		 function listMethodParams()
		 {
			$this->apiDocs = file_get_contents("http://www.liquidweb.com/StormServers/api/docs/" . $this->apiVersion . "/docs.json");
			$this->apiDocs = json_decode($this->apiDocs, TRUE);
			
			$apiDocsLocal = array_change_key_case($this->apiDocs); // Lowercase the groupings
			
			// Split up the group and method so we can properly find it in the JSON file
			$methodSplitter = explode("/", $this->apiMethod);
			$splitterCount = count($methodSplitter) - 2; // This will determine the index of the $methodSplitter array that has the last group element
			$groupElement = ""; // Empty init to prevent complaints
			$i = 0;
			while($i <= $splitterCount) // Generate the group element
			{
				if($i < $splitterCount)
				{
					$groupElement .= strtolower($methodSplitter[$i]) . "/";
				}
				else // Last element of the group element
				{
					$groupElement .= strtolower($methodSplitter[$i]);
				}
				$i++;
			}
			$methodElement = strtolower($methodSplitter[$i]);
			if(isset($apiDocsLocal[$groupElement])) // First line of defense - make sure the grouping exists
			{
				$apiDocsLocal[$groupElement]['__methods'] = array_change_key_case($apiDocsLocal[$groupElement]['__methods']); // Lowercase the methods as well
			}
			else // Kill it now - not a valid method since we don't have a valid group
			{
				return FALSE;
			}

			// Now on to the heart of the matter - and yes, this might get a bit crazy
			if(count($apiDocsLocal[$groupElement]['__methods'][$methodElement]['__input']) != 0) // Check for either a valid method, or the existence of parameters
			{
				foreach($apiDocsLocal[$groupElement]['__methods'][$methodElement]['__input'] as $tempKey => $tempValue)
				{
					if(isset($tempValue['optional']))
					{
						$methodParams[$tempKey] = "Optional";
					}
					elseif(isset($tempValue['required_if']))
					{
						foreach($tempValue['required_if'] as $requireName => $requireValue)
						{
							if($requireValue == NULL)
							{
								$required[] = $requireName . " = NULL";
							}
							else
							{
								$required[] = $requireName . " = " . $requireValue;
							}
						}
						$methodParams[$tempKey] = "Required if: [" . implode(" , ", $required) . "]";
						unset($required);
					}
					else // Required parameter
					{
						$methodParams[$tempKey] = "Required";
					}
				}
				return $methodParams;
			}
			else
			{
				return FALSE;
			}
		 }
		
		/**
		 * 
		 * @return null Clears the array being used for storing the API method's parameters
		 * 
		 */
		function clearParams()
		{
			unset($this->apiRequestBody);
			$this->apiRequestBody = TRUE; // Initialize blank, so that a warning doesn't get thrown about the array being undefined
			curl_setopt($this->apiRequest, CURLOPT_HTTPGET, TRUE); //If the request was previously run with params, this cleans those out. Otherwise they go back with the request
		}
		
		/**
		 * 
		 * @param string $apiMethod The new Storm API method you would like to use
		 * @param boolean $clearparams Defaults TRUE - clears the array being used for storing the API method's parameters
		 * @return null
		 * 
		 */
		function newMethod($apiMethod, $clearparams = TRUE) // Clears out parameters by default, since they may not apply now
		{
			if($clearparams == TRUE)
			{
				unset($this->apiRequestBody);
				$this->apiRequestBody = TRUE; // Initialize blank, so that a warning doesn't get thrown about the array being undefined
				curl_setopt($this->apiRequest, CURLOPT_HTTPGET, TRUE); //If the request was previously run with params, this cleans those out. Otherwise they go back with the request
			}
			
			$this->apiMethod = $apiMethod; // New method, coming right up!
			$this->apiFullUri = $this->baseUrl . $this->apiMethod . "." .$this->apiFormat; // New URI since method change
			curl_setopt($this->apiRequest, CURLOPT_URL, $this->apiFullUri);
		}
		
		/**
		 * 
		 * This method will return the server, port, method, and parameters set - general debugging stuff
		 * 
		 * @return string Returns a string containing the server, port, method, and parameters currently set
		 * 
		 */
		function debugInfo()
		{
			$this->debugVars = "Full URI: " . $this->apiFullUri . "\n";
			$this->debugVars .= "Port: " . $this->apiPort . "\n";
			$this->debugVars .= "Parameters as follows: \n";
			
			if(isset($this->apiRequestBody))
			{
				foreach($this->apiRequestBody['params'] as $par_key => $par_value)
				{
					if(is_array($par_value))
					{
						$this->debugVars .= $par_key . "=>" . print_r($par_value, TRUE);
					}
					else
					{
						$this->debugVars .= $par_key . " => " . $par_value . "\n";
					}
				}
			}
			else
			{
				$this->debugVars .= "No Parameters\n";
			}
			
			$this->debugVars .= "=== End Params ===\n";
			
			return $this->debugVars;
		}
		
		/**
		 *
		 * This method will return a list of available API methods
		 *
		 * @return array Returns an array of the available API methods based on the version supplied
		 */
		function listMethods()
		{
			$this->apiDocs = file_get_contents("http://www.liquidweb.com/StormServers/api/docs/" . $this->apiVersion . "/docs.json");
			$this->apiDocs = json_decode($this->apiDocs, TRUE);
		
			foreach($this->apiDocs as $groupName => $group)
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
			if(is_array($this->apiRequestBody)) // We have params
			{
				curl_setopt($this->apiRequest, CURLOPT_POST, TRUE); //POST method since we'll be feeding params
				curl_setopt($this->apiRequest, CURLOPT_HTTPHEADER, Array('Content-type: application/json')); // Since we'll be using JSON
				curl_setopt($this->apiRequest, CURLOPT_POSTFIELDS, json_encode($this->apiRequestBody)); // Insert the parameters
			}
			
			// Now send the request and get the return on investment
			try
			{
				return json_decode(curl_exec($this->apiRequest), TRUE); // Pull the trigger and get nice pretty arrays of returned data
			}
			catch (Exception $e)
			{
				echo 'Error: ' . $e->getMessage();
			}
		}
	}
?>
