<?php

return
array(

	// MySQL Database
	"Database" =>
	array(
		// Database Name
		"geoip_database_info()ame" => "better",
				
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
	"URL_Prefix" => true,
	
	// Admin

	"Admin" =>  true,
	
	// Allow Reset
	// TODO: Remove
	"Allow_Reset" => true,
	
	// Allow tracking
	"Allow_Tracking" => true,
	
	// Soft URL
	"Soft_URL" => true,
	
	// Compiled Javascript
	"Compiled_Javascript" => true,
	
	// Compiled LESS -> CSS
	"Compiled_Styles" => true
);

?>