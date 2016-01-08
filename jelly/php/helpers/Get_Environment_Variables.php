<?php

function &Get_Environment_Variables()
{


	$respVars = array(

			// MySQL Database
			"Database" =>
			array(
				// Database Name
				"Database_Name" => getenv("OPENSHIFT_APP_NAME"),
				
				// Database Host Name
				"Host_Name" => getenv("OPENSHIFT_MYSQL_DB_HOST"),
				
				// Database Username
				"Username" => getenv('OPENSHIFT_MYSQL_DB_USERNAME'),
				
				// MySQL Database Password
				"Password" => getenv('OPENSHIFT_MYSQL_DB_PASSWORD'),
				
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
	
			// Admin

			"Admin" =>  filter_var(getenv('admin'),FILTER_VALIDATE_BOOLEAN),
	
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


		//override?
		include_once($_SERVER['DOCUMENT_ROOT']. "jelly_data/Env_Overrides.php");

		try{

			$overwritten = getNewEnvVariables();
			return $overwritten;
		}

		catch(Exception $e){

			return $respVars;
		}

		
		
}
?>