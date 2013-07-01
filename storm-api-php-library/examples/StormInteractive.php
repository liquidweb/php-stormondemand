<?php
	/*
	 * Author: Jason Gillman Jr.
	 * Description: My attempt at writing a simple interactive CLI script for dumping raw data from Storm API returns.
	 * 				All you are going to get is print_r() of the returned array.
	 * 				Hope it's useful!
	 */

	require_once('StormAPI.class.php');
	
	// Initial information
	// echo "\nAPI Username: "; $api_user = trim(fgets(STDIN));
	// echo "Password: "; $api_pass = trim(fgets(STDIN));
	$api_user = 'user';
	$api_pass = 'pass';
	echo "Initial Method: "; $api_method = trim(fgets(STDIN));
	
	$storm = new StormAPI($api_user, $api_pass, $api_method);
	
	// Menu
	while(!isset($stop))
	{
		echo "\n\nPick your poison... \n";
		echo "1. Change method (will clear params) \n";
		echo "2. Add parameter \n";
		echo "3. Clear parameters \n";
		echo "4. Execute request and display \n";
		echo "5. Get me out of here \n";
		echo "Enter a number: "; fscanf(STDIN, "%d\n", $choice); // Get the choice
		
		switch($choice)
		{
			case 1:
				echo "\nEnter your new method: "; $api_method = trim(fgets(STDIN));
				$storm->new_method($api_method);
				break;
			case 2:
				echo "\nEnter the parameter: "; $parameter = trim(fgets(STDIN));
				echo "\nEnter the value: "; $value = trim(fgets(STDIN));
				$storm->add_param($parameter, $value);
				unset($parameter, $value);
				break;
			case 3:
				$storm->clear_params();
				break;
			case 4:
				cleanArrayDisp($storm->request());
				break;
			case 5:
				echo "\n\n";
				$stop = TRUE;
				break;
			default:
				echo "Really? How about you enter a valid value?";
				break;
		}
	}
	
	function cleanArrayDisp($array)
	{
		global $path; // For when things get... recursive
		
		static $path_idx = 0; // Keepin it real - for all of it
		
		foreach($array as $key => $value)
		{
			if(!is_array($value))
			{
				if($path_idx > 0) // Let's show where this value falls in the scheme of things
				{
					echo '[';
					echo implode("][", $path);
					echo ']';
				}
				echo '[' . $key . ']' . " => " . $value . "\n";
			}
			elseif(is_array($value)) // Recursion time!
			{
				$path_idx++; // Increment the path index
				$path[$path_idx] = $key;
				
				cleanArrayDisp($value);
				
				unset($path[$path_idx]); // Cleanup
				$path_idx--;
				
				echo "\n"; // Space = happy
			}
		}
	}
?>