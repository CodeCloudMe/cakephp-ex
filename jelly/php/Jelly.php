<?php

// Start Timer
$Start_Time = microtime(true);

//Pre-Requires
{
	// TODO: cleanup -- or just put the environment at the top.
	date_default_timezone_set('America/New_York');
}

// Requires
{
	require_once('database/Database.php');
	require_once('helpers/Helpers.php');
	require_once('compiler/Compiler.php');
	require_once('language/Language.php');
	require_once('runtime/Runtime.php');
	require_once('datatypes/Datatypes.php');
	require_once('setup/Setup.php');
}

// Libraries
// TODO - for php < 5.5
{
	require_once('libraries/password/password.php');
}

// Settings
{		
	// Get install path
	$GLOBALS['Install_Path'] = &New_String('jelly');

	// Get data directory path	
	$Data_Directory_Name = &New_String('jelly_data');
	$Data_Directory_Path = &New_String($Data_Directory_Name);
	$GLOBALS["Data_Directory_Path"] = &$Data_Directory_Path;
	
	// Get environment configuration variables 
	// Warning: $Configuration not by reference
	$Configuration = &Get_Environment_Variables();
	
	// Load Files Directory Path
	if (isset($Configuration['Files_Directory_Path']))
		$Files_Directory_Path = $Configuration['Files_Directory_Path'];
	else
	{
		$Files_Directory_Name = &New_String('Files');
		$Files_Directory_Path = &New_String($Data_Directory_Path . '/' . $Files_Directory_Name);
	}
	
	// Globalize Configuration Variables
	$GLOBALS['URL_Prefix'] = &New_String(trim($Configuration['URL_Prefix']));
	$GLOBALS['Admin'] = &$Configuration['Admin'];
	$GLOBALS['Compiled_Javascript'] = &$Configuration['Compiled_Javascript'];
	$GLOBALS['Compiled_Styles'] = &$Configuration['Compiled_Styles'];
	$GLOBALS['Allow_Tracking'] = &$Configuration['Allow_Tracking'];
	global $URL_Prefix;
	
	// Check if using Soft URLs
	if (!isset($Configuration['Soft_URL']))
		$Configuration['Soft_URL'] = true;
	if ($Configuration['Soft_URL'])
	{
		// Check if htaccess file exists
		$htaccess_File_Name = '.htaccess';
		$htaccess_File_Path = $htaccess_File_Name;
		if (!file_exists($htaccess_File_Path))
		{
			if (!($htaccess_File_Handle = @fopen($htaccess_File_Path, 'w')))
				throw new Exception('Cannot create htaccess file');
			
			// Write htaccess File Data
			$htaccess_File_Data = &htaccess_File_Data();
			fwrite($htaccess_File_Handle, $htaccess_File_Data);
			fclose($htaccess_File_Handle);
			
			// Redirect to root
			header('Location: ' . $URL_Prefix . '/');
			
			exit();
		}
	}
}

// Set up php environment
{	
	// TODO: check PHP version (see old Jelly)
	
	// TODO: check for soft URLs (see old Jelly)
	
	// Set 'ini' values for MySQL
	// TODO: Necessary?
	ini_set('mysql.connect_timeout', 300);
	ini_set('mysql.wait_timeout', 300);
	ini_set('default_socket_timeout', 300);
	
	// TODO refactor code
	if (ini_get('xdebug.max_nesting_level'))
	{
		ini_set('xdebug.max_nesting_level', 500);
	}
	
	// Error reporting environment
}


// Set up path
{
	// Warning: $Path not by reference
	$Path = getenv('REQUEST_URI');

	// Trim leading & trailing slash
	$Path = trim($Path, '/');
	
	// Trim leading query indicator
	$Path = ltrim($Path, '?');
	
	// Trim leading slash after query string indicator, if exists.
	$Path = ltrim($Path, '/');

	// Trim configuration URL prefix
	// TODO: this doesn't exactly look like it would work in all slash scenarios.
	if ($URL_Prefix)
	{
		if (strpos($Path, $URL_Prefix) === 0)
		{
			$Path = substr($Path, strlen($URL_Prefix));
			$Path = trim($Path, '/');
		}
	}
	

	// Special case requests
	// robots
	// favico
	// reset
	// logout
	// phpinfo (lol)
	
	switch (strtolower($Path))
	{
		case 'logout':
			// TODO - break out to a new function?Remove_Session or something?

			// Unset the cookies locally
			unset($_COOKIE['Session_ID']);
			unset($_COOKIE['Session_Code']);
			
			// Reset session cookies in the browser
			setcookie('Session_ID', '', time() - 3600, '/');
			setcookie('Session_Code', '', time() - 3600, '/');
			
			// TODO - this area is just a hack for the demonstration.
			exit();
			break;
			
		case 'compile':
			// Compile Javascript.
			Compile_Javascript();
			Compile_Styles();
			echo "Done.";
			exit();
			break;	

		case 'reset':
			//if (isset($Configuration['Allow_Reset']) && $Configuration['Allow_Reset'])
			if (1)
			{
				// Reset Database.
				$Database_Settings = &$Configuration['Database'];
				$Database = &Connect_Database($Database_Settings);
				Reset_Database($Database);
				
				// Compile Javascript.
				Compile_Javascript();
				Compile_Styles();
			
				// TODO: remove on dev
// 			exit();

				echo "Done.";
				exit();
				
				// Redirect page
				header('Location: ' . $URL_Prefix . '/');
				exit();
			}
			else
				die('Resetting not allowed.');
			break;
	}
}

// Set up database
{
	$Database_Settings = &$Configuration['Database'];
	$Database = &Connect_Database($Database_Settings);

	// Load database
	Generate_Database_Cache($Database);
}

// Set up variables
{
	// Set up Memory Stack and global variables
	{
		// Set up Globals item
		$Item_Type = &Get_Cached_Type($Database, 'Item');
		$Globals_Item = &Create_Memory_Item($Database, $Item_Type);
		Set_Simple($Globals_Item, 'Name', 'Globals');
		Set_Simple($Globals_Item, 'Alias', 'Globals');
		Set_Value($Globals_Item, 'Install_Path', $GLOBALS['Install_Path']);
		Set_Value($Globals_Item, 'URL_Prefix', $URL_Prefix);
		Set_Value($Globals_Item, 'Superuser', $GLOBALS['Admin']);
		Set_Value($Globals_Item, 'Compiled_Javascript', $GLOBALS['Compiled_Javascript']);
		Set_Value($Globals_Item, 'Compiled_Styles', $GLOBALS['Compiled_Styles']);
		Set_Value($Globals_Item, 'Allow_Tracking', $GLOBALS['Allow_Tracking']);
		
		// Store Globals Item in PHP Globals
		// TODO is this clean? So far only used in setting up sessions
		$GLOBALS['Globals_Item'] = &$Globals_Item;
	
		// Set up memory stack reference to Globals item
		$Globals_Reference = &New_Reference($Database);
		$Globals_Reference['Item'] = &$Globals_Item;
		$Globals_Item['References'][] = &$Globals_Reference;

		// Point memory stack root to Globals reference
		$Memory_Stack_Reference = &$Globals_Reference;
		
		// Set up an array of this request's used namespaces
		// TODO: better way to do this?
		$GLOBALS['Used_Namespaces'] = array();
	}
	
	// Set up post variables
	{
		$Form_Item = &Extract_Post_Variables($Database);
		Set_Value($Globals_Item, 'Form', $Form_Item);
// 		traverse($Form_Item);
	}
	
	// Set Up Session
	{
		// Check if a session was passed in via cookies
		if (isset($_COOKIE['Session_ID']))
		{
			// Get session variables from cookies
			$Session_ID = &$_COOKIE['Session_ID'];
			$Session_Code = &$_COOKIE['Session_Code'];
			
			// Get session from database
			// TODO security: check escaping
			$Session_Command_String = &New_String('1 Session from Database Where ID is ' . $Session_ID . ' and Code Is ' . $Session_Code);
			$Session_Command = &Parse_String_Into_Command($Session_Command_String);
			$Session_Item = &Get_Database_Item($Database, $Session_Command);
			
			// Check if the session was found in the database
			if ($Session_Item['End_Of_Results'])
			{
				// Unset the cookies locally
				unset($_COOKIE['Session_ID']);
				unset($_COOKIE['Session_Code']);
				
				// Reset session cookies in the browser
				setcookie('Session_ID', '', time() - 3600, '/');
				setcookie('Session_Code', '', time() - 3600, '/');
			}
			else
			{
				// Store session item in PHP Globals
				$GLOBALS['Current_Session_Item'] = &$Session_Item;
				
				// Add session item to globals item
				Set_Value($Globals_Item, 'Current_Session', $Session_Item);
				
				// Get user from session
				// TODO disabled since it can just be gotten from Current_Session.User later (and that supports changing it mid-execution)
				// TODO Re-enabled since we use it for logs
				$Session_User_Command_String = &New_String('User');
				$Session_User_Command = &Parse_String_Into_Command($Session_User_Command_String);
				$Current_User_Item = &Get_Value($Session_Item, $Session_User_Command);
				if (!$Current_User_Item['End_Of_Results'])
				{
					// Store user in PHP Globals
					$GLOBALS['Current_User_Item'] = &$Current_User_Item;
					
					// Add user item to globals item
					Set_Value($Globals_Item, 'Current_User', $Session_Item);
				}
			}
		}
	}
	
	// TODO hacky implementation	
	if ($_POST['Username'] && !$Path)
	{
		$Login_Action_Command_String = &New_String('[Action "Login" from Database as Execute /]');
		$Login_Action_Result = &Process_Block_String($Database, $Login_Action_Command_String, $Memory_Stack_Reference);
		echo "<html><script>window.location = window.location.href;;</script></html";
		exit();
	}
	
	
	// Get path item
	$Processed_URL = &Process_URL($Database, $Path, $Memory_Stack_Reference);
	
	if (isset($Processed_URL['Template']))
	{
		$Path_Template = &$Processed_URL['Template'];
		$Path_Template_Alias = &$Path_Template['Alias'];
		
		// Set response content type to template's content type
		$Response_Content_Type = &$Path_Template['Content_Type'];
		
		// Store template alias in globals
		// TODO is this used?
		Set_Value($Globals_Item, 'Path_Template_Alias', $Path_Template_Alias);
	}
	elseif (isset($Processed_URL['Property']))
	{
		$Path_Property = &$Processed_URL['Property'];
		$Path_Property_Alias = &$Path_Property['Alias'];	
		// Set response content type to plain text
		$Response_Content_Type = &New_String('text/plain');
	}
	else
		throw new Exception("URL should have resolved to template or property.");
	
	// TODO: This is a hack before we do post var stuff
	if (isset($Form_Item['Data']['No_Wrap']) && $Form_Item['Data']['No_Wrap'])
		$Processed_URL['No_Wrap'] = &New_Boolean(true);		
	if (isset($Form_Item['Data']['Raw']) && $Form_Item['Data']['Raw'])
		$Processed_URL['Raw'] = &New_Boolean(true);
	if (isset($Form_Item['Data']['Preserve_Namespace']) && $Form_Item['Data']['Preserve_Namespace'])
		$Processed_URL['Preserve_Namespace'] = &New_Boolean(true);
		
	if (isset($Form_Item['Data']['From_Container']) && $Form_Item['Data']['From_Container'])
		$Processed_URL['From_Container'] = &New_Boolean(true);
	
	if (isset($Form_Item['Data']['Never_Wrap']) && $Form_Item['Data']['Never_Wrap'])
		$Processed_URL['Never_Wrap'] = &New_Boolean(true);
	
	if ($Response_Content_Type != 'text/html')
	{
		$Processed_URL['No_Wrap'] = &New_Boolean(true);
		$Processed_URL['Raw'] = &New_Boolean(true);
	}

	// Store path item in global memory item values
	// TODO: Do we need to store path variables here? 
	$Site_Command_String = &New_String('1 Site from database');
	$Site_Command = &Parse_String_Into_Command($Site_Command_String);
	$Site_Item = &Get_Database_Item($Database, $Site_Command);
	Set_Value($Globals_Item, 'Current_Site', $Site_Item);
	Set_Value($Globals_Item, 'Path_Item', $Processed_URL['Item']);

	// TODO - do we need this, if we are passing it into the path item reference?
	// TODO - or vice versa, maybe, considering we do use this a lot --- maybe duplicating it is bad, however.
	if (isset($Processed_URL['Variables']))
		Set_Value($Globals_Item, 'Path_Variables', $Processed_URL['Variables']);
	
	// Store item reference in memory stack reference
	$Path_Item = &$Processed_URL['Item'];
	$Path_Item_Reference = &New_Reference($Database);
	$Path_Item_Reference['Item'] = &$Path_Item;
	if (isset($Processed_URL['Variables']))
		$Path_Item_Reference['Variables'] = &$Processed_URL['Variables'];
	if (isset($Form_Item['Data']['Metadata_Namespace']) && $Form_Item['Data']['Metadata_Namespace'])
		$Path_Item_Reference['Namespace'] = &$Form_Item['Data']['Metadata_Namespace'];
	else
		$Path_Item_Reference['Namespace'] = &New_String('Jelly');
	$Path_Item_Reference['Previous_Memory_Stack_Reference'] = &$Memory_Stack_Reference;
	$Path_Item['References'][] = &$Path_Item_Reference;
	$Memory_Stack_Reference = &$Path_Item_Reference;

	$Render_Flags = array();
	
// 	traverse($Processed_URL);
	
	// If not requesting Raw, render the default site
	if (isset($Processed_URL['Raw']) && $Processed_URL['Raw'])
	{
		// TODO: this is for the autosetting of action.target entirely. Maybe there's a better way.
		// TODO: need to wrap sometimes, i.e. direct loading of raw content for fills
		// TODO: Tristan randomly tried removing Preserve_Variables from second option below...
		// TODO: Might be related to action targets.
	
		// TODO - move flags upstairs.
		$Command_String_Flags = array();
		$Command_String_Flags[] = 'Preserve_Variables';
		
		if (isset($Processed_URL['From_Container']) && $Processed_URL['From_Container'])
			$Command_String_Flags[] = 'From_Container';

		if ($Processed_URL['No_Wrap'])
			$Command_String_Flags[] = 'No_Wrap';
	
		if ($Processed_URL['Preserve_Namespace'])
		{
			$Command_String_Flags[] = 'Preserve_Namespace';
			$Command_String_Flags[] = 'No_Scripts';
		}
	
		// TODO - maybe this property thing can be an item template.
		if (isset($Processed_URL['Property']))
			$Path_Block_String  = '[This.' . $Path_Property_Alias . ' Process_Once/]';
		elseif (isset($Processed_URL['Template']))
			$Path_Block_String  = '[This as ' . $Path_Template_Alias . '  ' . implode(' ', $Command_String_Flags) . ' /]';
// 		traverse($Path_Block_String);
	}
	// Otherwise, render the path item from memory
	else
	{
		// TODO -  Move Command_String_Flags to flags. 
		if (isset($Form_Item['Data']['Design_Only']) && $Form_Item['Data']['Design_Only'])
		{	
			// TODO - Do we ever want this wrapped?
			// TODO - added no_Wrap, Preserve_Namespace but check w/ Tristan...
			$Path_Block_String = '[Site.Design No_Wrap Preserve_Namespace/]';
		}
		else
		{
// 			$Path_Block_String = '[1 Site from Database][Site.Created][Month_Name/][/][/1 Site]';
			// TODO - don't know, but it might make sense to tag most things as from_request?
			$Path_Block_String = '[1 Site from Database No_Wrap From_Request/]';
		}
	}
	
	
	// Add global no_wrap for non-HTML
	// TODO clean up like above
	// TODO string not by reference
	// TODO is this affected by application/unknown? Those templates should set their own content-type that might be HTML
	if ($Response_Content_Type != 'text/html')
		$Path_Block_String  = "[No_Wrap]" . $Path_Block_String . "[/No_Wrap]";
		
	// Never_Wrap handles text/html content that should not wrap
	// TODO - not sure if no_Scripts is implied. -- update,  I'm pretty sure it is not implied.
	// TODO - maybe call it never_wrap in general, both in the tag and the post value
	else if (isset($Processed_URL['Never_Wrap']) && $Processed_URL['Never_Wrap'])
		$Path_Block_String  = "[No_Scripts][No_Wrap]" . $Path_Block_String . "[/No_Wrap][/No_Scripts]";
	
	// If the client is a robot, don't bother wrapping or including scripts
	// TODO soooooooooooooooo needs cleanup!!
	if (Is_Robot())
	{
		$Render_Flags['No_Wrap'] = true;
		$Render_Flags['No_Scripts'] = true;
	}
	
	// Note: Result Chunk is printed to browser (this is the only time a rendered chunk is printed to the browser)
	$Rendered_Path_Item = &Process_Block_String($Database, $Path_Block_String, $Memory_Stack_Reference);
	
	// Render the processed chunk
	Render_Processed_Block($Rendered_Path_Item, $Render_Flags);
	
// 		traverse($Rendered_Path);
		
	// Set up response headers.
	{
		// Setup encodings
		mb_language('uni');
		mb_internal_encoding('UTF-8');
		mb_http_output('UTF-8');

		// HTTP Headers
		if (!headers_sent())
		{
			$Status_Header_Sent = false;
			foreach ($Rendered_Path_Item['Headers'] as &$Rendered_Path_Item_Header_Values)
			{
				if ($Rendered_Path_Item_Header_Values['Header'] == 'Status')
				{
					$Rendered_Path_Item_Header = &New_String('HTTP/1.1' . ' ' . $Rendered_Path_Item_Header_Values['Value']);
					header($Rendered_Path_Item_Header);
					
					$Rendered_Path_Item_Header = &New_String($Rendered_Path_Item_Header_Values['Header'] . ':' . ' ' . $Rendered_Path_Item_Header_Values['Value']);
					header($Rendered_Path_Item_Header);
					
					$Status_Header_Sent = true;
				}
			}
			
			if (!$Status_Header_Sent)
			{
				header('HTTP/1.1 200 OK');
				header('Status: 200 OK');
			}
		
			// TODO: restructure
			// TODO: or rename
			// If the content type is specified, set the HTTP content-type header
		
			if (strtolower($Response_Content_Type) != 'application/unknown')
				header('Content-type:' . ' ' . $Response_Content_Type . ';' . ' ' . 'charset=utf-8');
		
			// Print rendered headers
	// 			traverse($Rendered_Path_Item['Headers']);
			foreach ($Rendered_Path_Item['Headers'] as &$Rendered_Path_Item_Header_Values)
			{	
				if ($Rendered_Path_Item_Header_Values['Header'] != 'Status')
				{
					$Rendered_Path_Item_Header = &New_String($Rendered_Path_Item_Header_Values['Header'] . ':' . ' ' . $Rendered_Path_Item_Header_Values['Value']);
					header($Rendered_Path_Item_Header);
				}
			}
		}
	}
	
	// Create response for rendered path item according to content-type
	switch ($Response_Content_Type)
	{
		// HTML
		case 'text/html':
			// Format content
			$Response_Content = &$Rendered_Path_Item['Content'];
			
			// Format script
			$Response_Script = &New_String('<script>head.ready(function() {' . "\n" . $Rendered_Path_Item['Script'] . "\n" . '});</script>');

			// Format response
			$Response_Data = &New_String($Response_Content . "\n\n" . $Response_Script);
			
// 			$dom = new DOMDocument();
// 			$dom->preserveWhiteSpace = false;
// 			libxml_use_internal_errors(true);
// 			$dom->loadHTML($Response_Data);
// 			libxml_use_internal_errors(false);
// 			$dom->formatOutput = true;
// 			$Response_Data = $dom->saveHTML();
			
			break;

		// Images
		
		// Not HTML
		default:
			// Format response
			$Response_Data = &$Rendered_Path_Item['Content'];
			break;
	}

	// TODO: Add elapsed time	

	// Print processed path item
	print $Response_Data;
// 	print memory_get_usage() . "<br/>\n";
// 	print memory_get_peak_usage() . "<br/>\n";

	// TODO - behavior here is dirty and quick 
	if ($GLOBALS['Stream_Content_Path'])
		readfile($GLOBALS['Stream_Content_Path']);

	// Add server request to log
	$End_Time = microtime(true);
	$Elapsed_Time = $End_Time - $Start_Time;
	$Server_Request_Cached_Type = &Get_Cached_Type($Database, 'Server_Request');
	$Server_Request_Item = &Create_Item($Database, $Server_Request_Cached_Type);
	Set_Value($Server_Request_Item, 'Requested URL', $Path);
	Set_Value($Server_Request_Item, 'Requested Item', $Processed_URL['Item']);
	Set_Value($Server_Request_Item, 'IP Address', $_SERVER['REMOTE_ADDR']);
	Set_Value($Server_Request_Item, 'Elapsed Time', $Elapsed_Time);
	Set_Value($Server_Request_Item, 'Requested Item Name', $Processed_URL['Item']['Data']['Name']);
	Set_Value($Server_Request_Item, 'Requested Item Type Name', $Processed_URL['Item']['Cached_Specific_Type']['Name']);
	if (isset($Path_Template))
		Set_Value($Server_Request_Item, 'Template Name', $Path_Template["Name"]);
	if (isset($Session_Item))
		Set_Value($Server_Request_Item, 'Session', $Session_Item);
	if (isset($Current_User_Item))
		Set_Value($Server_Request_Item, 'User', $Current_User_Item);
	// TODO Re-enable
//	Save_Item($Server_Request_Item);
}
	
?>