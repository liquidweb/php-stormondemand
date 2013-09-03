php-stormondemand
============

php-stormondemand provides the PHP programmer a library to interact with
Liquid Web's Storm API via the **_StormAPI_** class. 

### Platforms Supported ###
Regardless of whether you are using Liquid Web Storm Servers
or Storm on Demand, the API, and subsequently this library, will work.

### API Versions Supported ###
The php-stormondemand library will work with any current or future version of the Storm API.
Currently, the two versions are:
* v1
* bleed

### Requirements ###
The php-stormondemand library requires that cURL support be enabled in PHP
(see http://www.php.net/manual/en/book.curl.php for more information).


### Basic Usage ###
The following is an example script (located in the examples/ directory) that
shows basic use:

```php
<?php
	require_once('../StormAPI.class.php');
	
	$apiUser = "api_user";
	$apiPass = "api_pass";
	$apiMethod = "storm/server/list";
	$paramsArray = FALSE;
	$apiVersion = "v1";
	
	$storm = new StormAPI($apiUser, $apiPass, $apiMethod, $paramsArray, $apiVersion);
	
	$storm->addParam("page_size", 999);
	$results = $storm->request();
	
	foreach($results['items'] as $item)
	{
		echo $item['domain']  . " || " . $item['uniq_id'] . "\n"; 
	}	
?>
```

Alternatively, the parameter(s) can be passed upon instantiation as so:


```php
<?php
	require_once('../StormAPI.class.php');
	
	$apiUser = "api_user";
	$apiPass = "api_pass";
	$apiMethod = "storm/server/list";
	$paramsArray = array('page_size' => 999);
	$apiVersion = "v1";
	
	$storm = new StormAPI($apiUser, $apiPass, $apiMethod, $paramsArray, $apiVersion);
	
	$results = $storm->request();
	
	foreach($results['items'] as $item)
	{
		echo $item['domain']  . " || " . $item['uniq_id'] . "\n"; 
	}	
?>
```

PHPDoc blocks are utilized so that code hinting can be used if your IDE supports it.

### Library Method Summary ###
The following is just a quick summary of methods that the library provides,
their functions, and their parameters.

**__construct($apiUser, $apiPass, $apiMethod, $paramsArray = FALSE, $apiVersion = "v1")**
The magic constructor method that is called upon instantiation of a **_StormAPI_** object.
If you want to pass along a specific version of the API, but don't want to (or can't) pass
along parameters upon construction, specify a boolean (I prefer _FALSE_) for _$paramsArray_.

**bulkParams($paramsArray)**
Allows the passing of multiple parameters via an associative array.

**addParam($parameter, $value)**
Adds specifies the parameter and its value to be passed along with the API request.

**removeParam($parameter)**
Removes the specified parameter from being passed along with the API request.

**listParams()**
Lists any parameters that are currently set.

**listMethodParams()**
Parses the API documentation and provides parameters associated with the method
that is currently being used. Additionally, it shows the optionality of the parameter.

**clearParams()**
Clears out all of the parameters that may be currently set.

**newMethod($apiMethod, $clearparams = TRUE)**
Changes the API method to call. Also clears out the parameters by default
unless overridden.

**listMethods()**
Returns a listing of all the API methods available for the version of the 
API that is being used.

**debugInfo()**
More of a convenience method that outputs some information that might be useful
for debugging purposes.

**request()**
This is the method that makes it all happen. Returns an array.
