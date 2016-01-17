<?php

function &Get_Environment_Variables()
{
	// Load defaults
	$respVars = array(

			// MySQL Database
			"Database" =>
			array(
				// Database Name
				"Database_Name" => getenv("DATABASE_NAME"),
				
				// Database Host Name
				"Host_Name" => "localhost",
				
				// Database Username
				"Username" => getenv('DATABASE_PASSWORD'),
				
				// MySQL Database Password
				"Password" => getenv('DATABASE_USER'),
				
				// MySQL Table Prefix
				"Table_Prefix" => ""
			),
	
			// API Keys
			// TODO - open-source version should set this independently and strip these ones.
			"API_Keys" => 
			array (
				"Lokku" =>  getenv('lokkuKey'),
				"Twilio" => array(
						"Account_SID" =>  getenv('twilioSID'),
						"Auth_Token" =>  getenv('twilioToken'),
						"From" =>  getenv('twilioFrom')
					),
				"Stripe" => array(	
						"Mode" =>  getenv('stripeMode'), 
						"Test" => array(
								"Client_ID" =>  getenv('stripeTestId'),
								"Public_Key" =>  getenv('stripeTestPKey'),
								"Secret_Key" =>  getenv('stripeTestSKey')
							),
						"Live" => array(
								"Client_ID" => getenv('stripeLiveId'),
								"Public_Key" => getenv('stripeLivePKey'),
								"Secret_Key" => getenv('stripeLiveSKey')
							)
					)
			),	
	
			// Data Folder Path
			//	"Data_Folder_Path" => "~/Documents/Jelly Data",
	
			// URL Prefix
			"URL_Prefix" => "",
	
			// Superuser

			"Superuser" =>  filter_var(getenv('superuser'),FILTER_VALIDATE_BOOLEAN),
	
			// Allow Reset
			// TODO: Remove
			"Allow_Reset" => filter_var(getenv('reset'),FILTER_VALIDATE_BOOLEAN),
	
			// Allow tracking
			"Allow_Tracking" => filter_var(getenv('tracking'),FILTER_VALIDATE_BOOLEAN),
	
			// Soft URL
			"Soft_URL" => filter_var(getenv('softUrl'),FILTER_VALIDATE_BOOLEAN),
	
			// Compiled Javascript
			"Compiled_Javascript" => filter_var(getenv('compiledJavascript'),FILTER_VALIDATE_BOOLEAN),
	
			// Compiled LESS -> CSS
			"Compiled_Styles" => filter_var(getenv('compiledStyles'),FILTER_VALIDATE_BOOLEAN)
		);
		
		// Override Variables.		
		// Sample Overrides File
		// Save as "/jelly_data/Overrides.php"
		
/*
 		function Overrides()
 		{
 			return array(
 				'Admin' => false
 			);
 		}
*/

		$Override_Path = $_SERVER['DOCUMENT_ROOT']. "/jelly_data/Overrides.php";
		if (file_exists($Override_Path))
		{
			@include_once($_SERVER['DOCUMENT_ROOT']. "/jelly_data/Overrides.php");
			$Override_Variables = Overrides();
			foreach ($Override_Variables as $Override_Key => $Override_Value)
			{
				$respVars[$Override_Key] = $Override_Value;
			}
		}
	
		
		return $respVars;		
}
?>