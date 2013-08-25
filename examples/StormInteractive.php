<?php
	/*
	 * 	Author: Jason Gillman Jr.
	 *	Description: My attempt at writing a simple interactive CLI script for dumping raw data from Storm API returns.
	 * 	I've adjusted this script so that instead of just a print_r() dump, it "breadcrumbs" the returned location so you don't get lost
	 * 	Hope it's useful!
	 */

	require_once('../StormAPI.class.php'); // Edit location as needed
	
	// Initial information
	// echo "\nAPI Username: "; $apiUser = trim(fgets(STDIN));
	// echo "Password: "; $apiPass = trim(fgets(STDIN));
	$apiUser = 'user';
	$apiPass = 'pass';
	echo "Initial Method: "; $api_method = trim(fgets(STDIN));
	
	$storm = new StormAPI($apiUser, $apiPass, $api_method);
	
	// Menu
	while(!isset($stop))
	{
		// Get logging status
		if(isset($logging))
		{
			$logStatus = "(Currently Active " . $logging['filename'] . ")\n";
		}
		else
		{
			$logStatus = "(Currently Inactive)\n";
		}
		
		echo "\n\nPick your poison... \n";
		echo "1. Change method (will clear all params) \n";
		echo "2. Add parameter \n";
		echo "3. List currently set parameters \n";
		echo "4. Clear ALL parameters \n";
		echo "5. Execute request and display \n";
		echo "6. Toggle logging " . $logStatus;
		echo "7. List available methods\n";
		echo "8. List parameters for current method\n";
		echo "9. Remove a specific parameter\n";
		echo "10. Get me out of here \n";
		echo "Enter a number: "; fscanf(STDIN, "%d\n", $choice); // Get the choice
		
		switch($choice)
		{
			case 1:
				echo "\nEnter your new method: "; $api_method = trim(fgets(STDIN));
				$storm->newMethod($api_method);
				break;
			case 2:
				echo "\nEnter the parameter: "; $parameter = trim(fgets(STDIN));
				echo "\nEnter the value: "; $value = trim(fgets(STDIN));
				$storm->addParam($parameter, $value);
				unset($parameter, $value);
				break;
			case 3:
				echo "\nCurrently set parameters: \n";
				if($storm->listParams())
				{
					cleanArrayDisp($storm->listParams());
				}
				else
				{
					echo "No parameters are currently set\n";
				}
				break;
			case 4:
				$storm->clearParams();
				break;
			case 5:
				if(isset($logging))
				{
					fwrite($logging['handle'], $storm->debugInfo()); // Head up the output with the debug information
					fwrite($logging['handle'], "\n"); // Whitespace makes people happy
					cleanArrayDisp($storm->request(), $logging); // Done this way so the file handler gets passed into the function
					fwrite($logging['handle'], "\n\n"); // More whitespace happiness
				}
				else
				{
					cleanArrayDisp($storm->request());
				}
				break;
			case 6:
				if(!isset($logging))
				{
					$logging['filename'] = date('His - dMy') . ".log";
					$logging['handle'] = fopen($logging['filename'], 'w+');
				}
				else
				{
					// Clean up
					fclose($logging['handle']);
					unset($logging);
				}
				break;
			case 7:
				echo "Available methods:\n";
				cleanArrayDisp($storm->listMethods());
				break;
			case 8:
				cleanArrayDisp($storm->listMethodParams());
				break;
			case 9:
				if($storm->listParams())
				{
					echo "\nCurrently set parameters: \n";
					$i = 0;
					foreach($storm->listParams() as $paramName => $paramValue)
					{
						$paramIndex[$i] = $paramName;
						echo $i . ". " . $paramName . " => " . $paramValue . "\n";
						$i++;
					}
					unset($i);
					echo "\nEnter the number of the parameter you would like to clear (enter anything else to cancel): "; fscanf(STDIN, "%d\n", $choice);
					if(isset($paramIndex[$choice])) // Valid choice
					{
						$storm->removeParam($paramIndex[$choice]);
					}
					else
					{
						echo "Not a valid choice. Canceling the operation.\n";
					}
					unset($paramIndex);
				}
				else
				{
					echo "No parameters are currently set\n";
				}
				break;
			case 10:
				echo "\n";
				$stop = TRUE;
				break;
			default:
				echo "Really? How about you enter a valid value?";
				break;
		}
	}
	
	function cleanArrayDisp($array, $log_array = FALSE)
	{
		global $path; // For when things get... recursive
		
		static $path_idx = 0; // Keepin it real - for all of it
		
		foreach($array as $key => $value)
		{
			if(!is_array($value))
			{
				if($path_idx > 0) // Let's show where this value falls in the scheme of things
				{
					if($log_array != FALSE) // If logging is enabled
					{
						$line = '[' . implode("][", $path) . ']';
						fwrite($log_array['handle'], $line);
						echo $line;
						unset($line);
					}
					else
					{
						echo '[' . implode("][", $path) . ']';
					}
				}
				if($log_array != FALSE) // If logging is enabled
				{
					$line = '[' . $key . ']' . " => " . $value . "\n";
					fwrite($log_array['handle'], $line);
					echo $line;
					unset($line);
				}
				else
				{
					echo '[' . $key . ']' . " => " . $value . "\n";
				}
			}
			elseif(is_array($value)) // Recursion time!
			{
				$path_idx++; // Increment the path index
				$path[$path_idx] = $key;
				
				if($log_array != FALSE)
				{
					cleanArrayDisp($value, $log_array);
				}
				else
				{
					cleanArrayDisp($value);
				}
				
				unset($path[$path_idx]); // Cleanup
				$path_idx--;
				
				// Uncomment the following line to have some breaks in the output
				//echo "\n"; // Space = happy
			}
		}
	}
?>
