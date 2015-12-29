<?php

return
array(

	// MySQL Database
	"Database" =>
	array(
		// Database Name
		"Database_Name" => "better",
				
		// Database Host Name
		"Host_Name" => getenv('host');,
				
		// Database Username
		"Username" => getenv('username');,
				
		// MySQL Database Password
		"Password" => getenv('password'),
				
		// MySQL Table Prefix
		"Table_Prefix" => ""
	),
	
	// API Keys
	// TODO - open-source version should set this independently and strip these ones.
	"API_Keys" => 
	array (
		"Lokku" => "911903378c845ddd76e5eeafe3532b07",
		"Twilio" => array(
				"Account_SID" => "ACfafb21565ac1c37bf9bc0b3cc85a2bc1",
				"Auth_Token" => "61b52654d5cc6f1fa80c634852754c80",
				"From" => "+13072238837"
			),
		"Stripe" => array(	
				"Mode" => "Test", 
				"Test" => array(
						"Client_ID" => "ca_6h83T8QQ42Yoz3VOJh6f5cXZpGCuM1FC",
						"Public_Key" => "pk_test_Xs5W6vfDHOlw6jEsgq2fXpG9",
						"Secret_Key" => "sk_test_L5qNqKOawpb0bL2IenbKj571"
					),
				"Live" => array(
						"Client_ID" => "ca_6h83bGx6hJvCFZpfZnAEFUdKdHpK2MWi",
						"Public_Key" => "pk_live_CQziZxuxxLPOtg4oAbc3ba9m",
						"Secret_Key" => "sk_live_oHNjDwo7Z5LdrItcHy1f1HTL"
					)
			)
	),	
	
	// Data Folder Path
	//	"Data_Folder_Path" => "~/Documents/Jelly Data",
	
	// URL Prefix
	"URL_Prefix" => "",
	
	// Admin
	"Admin" => true,
	
	// Allow Reset
	// TODO: Remove
	"Allow_Reset" => true,
	
	// Allow tracking
	"Allow_Tracking" => true,
	
	// Soft URL
	"Soft_URL" => true,
	
	// Compiled Javascript
	"Compiled_Javascript" => false,
	
	// Compiled LESS -> CSS
	"Compiled_Styles" => false
);

?>