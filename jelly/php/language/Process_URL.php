<?php

//  Processed URL is an Item, an Original URL, a Kind, a Template, and Raw flag
function &Process_URL(&$Database, &$URL, &$Memory_Stack_Reference)
{	
	$Debug = false;

	// Load globals
	$Cached_Type_Lookup = &$Database['Cached_Type_Lookup'];

	// Setup result
	$Processed_URL = &New_Array();
	$Processed_URL['Kind'] = &New_String('Processed_URL');
	$Processed_URL['Original_URL'] = &$URL;
	$Processed_URL['No_Wrap'] = &New_Boolean(false);
	$Processed_URL['Raw'] = &New_Boolean(false);
	$Processed_URL['Preserve_Namespace'] = &New_Boolean(false);

	// Prepare URL
	// TODO: Trim, etc.	
	// TODO: Verify form of Path
	
	// Split into item identifier and variables string
	// Notice: URL_Parts not by reference
	$URL_Parts = explode(':', $URL, 2);
	$Item_Identifier_String = &$URL_Parts[0];
	
	// Handle variables
	if (isset($URL_Parts[1]))
	{
		$Variables_String = &$URL_Parts[1];	
		
		// Process variables string into item.
		$Variables_Item = &Process_Variables_String_Into_Item($Database, $Variables_String, $Memory_Stack_Reference);
		
		// Store variables item in Processed URL.
		$Processed_URL['Variables'] = &$Variables_Item;
	}
		
	// Break identifier list into an array
	// Note: Identifier_Parts not by reference
	$Identifier_Parts = explode('/', $Item_Identifier_String);
	
	$Current_Path_Item = &New_Null();
	
	if (!count($Identifier_Parts) > 0 || !$Identifier_Parts[0])
	{ 
		if ($Debug)
			echo "Process_URL: no identifier\n<br/>";
			
		// Get site item
		$Site_Item = &Get_Site_Item($Database, $Memory_Stack_Reference);
		
		// Get Default Item
		// TODO - hange to Default_Item now that we have child types?
		$Default_Item_Command_String = &New_String("Default_Item");
		$Default_Item_Command = &Parse_String_Into_Command($Default_Item_Command_String);
		$Default_Item = &Get_Value($Site_Item, $Default_Item_Command);
		
		$Default_Item_Specific_Item_Command_String = &New_String("Specific_Item");
		$Default_Item_Specific_Item_Command = &Parse_String_Into_Command($Default_Item_Specific_Item_Command_String);
		$Current_Path_Item = &Get_Value($Default_Item, $Default_Item_Specific_Item_Command);
	}

	// Resolve identifier parts into item
	else
	{		
		// Store path.
		$Processed_URL['Original_Item_Identifier'] = &$Item_Identifier_String;
		
		// Isolate and handle raw.
		if (strtolower($Identifier_Parts[count($Identifier_Parts) - 1]) == 'raw')
		{
			array_pop($Identifier_Parts);
			$Processed_URL['Raw'] = true;
		}
		
		// Try various roots...
		$Root_Identifier = &$Identifier_Parts[0];
		
		// Try root identifier as type alias...
		{
			if ($Debug)  echo "Process_URL: root as type by alias...";
			$Match_Type_Command_String = &New_String('Type where Alias = ' . '"' .  $Root_Identifier . '" from Database as Reference');
			$Match_Type_Processed_Command = &Process_Command_String($Database, $Match_Type_Command_String, $Memory_Stack_Reference);
			$Match_Type_Item = &$Match_Type_Processed_Command['Chunks'][0]['Item'];
			if (!$Match_Type_Item['End_Of_Results'])
			{
				$Current_Path_Item = &$Match_Type_Item;

				// Get children
				for ($Identifier_Part_Index = 1; $Identifier_Part_Index < count($Identifier_Parts); $Identifier_Part_Index++)
				{
					$Current_Identifier = &$Identifier_Parts[$Identifier_Part_Index];

					$Next_Path_Item = &Get_Next_Path_Item($Database, $Current_Path_Item, $Current_Identifier, $Memory_Stack_Reference);
					
					switch ($Next_Path_Item['Kind'])
					{
						case 'Item':
							$Current_Path_Item = &$Next_Path_Item['Item'];
							break;

						case 'Not_Set':
							$Current_Path_Item = &New_Null();
							break 2;
							break;
							
						case 'Template':
						case 'Property':
							if ($Identifier_Part_Index == count($Identifier_Parts) - 1)
								$Processed_URL[$Next_Path_Item['Kind']] = &$Next_Path_Item[$Next_Path_Item['Kind']];
							else
								break 2;
							break;
					}
				}
			}
		}
				
		// Try root identifier as child path identifier of site...
		if (!$Current_Path_Item)
		{
			if ($Debug)  echo "Process_URL: root as site...";
			
			$Current_Path_Item = &Get_Site_Item($Database, $Memory_Stack_Reference);
			
			if (strtolower($Identifier_Parts[0]) == 'site')
				$Identifier_Part_Index = 1;
			else
				$Identifier_Part_Index = 0;

			// Get children
			for (; $Identifier_Part_Index < count($Identifier_Parts); $Identifier_Part_Index++)
			{
				$Current_Identifier = &$Identifier_Parts[$Identifier_Part_Index];
				
				$Next_Path_Item = &Get_Next_Path_Item($Database, $Current_Path_Item, $Current_Identifier, $Memory_Stack_Reference);
				
				switch ($Next_Path_Item['Kind'])
				{
					case 'Item':
						$Current_Path_Item = &$Next_Path_Item['Item'];
						break;

					case 'Not_Set':
						$Current_Path_Item = &New_Null();
						break 2;
						break;
						
					case 'Template':
					case 'Property':
						if ($Identifier_Part_Index == count($Identifier_Parts) - 1)
							$Processed_URL[$Next_Path_Item['Kind']] = &$Next_Path_Item[$Next_Path_Item['Kind']];
						else
							break 2;
						break;
				}				
			}
		}
		
		// Try root identifer as item alias...
		if (!$Current_Path_Item)
		{
			// Match items by alias
			// TODO: @core-language - Do where/which clauses trigger from database
			// TODO: @core-language - casting?!? transform.
			// TODO: @core-language - finish 'from'
			if ($Debug)  echo "Process_URL: root as item by alias...";

			$Match_Item_Command_String = &New_String('Item where Alias = ' . '"' .  $Root_Identifier . '" from Database as Reference');
			$Match_Item_Processed_Command = &Process_Command_String($Database, $Match_Item_Command_String, $Memory_Stack_Reference);
			$Match_Item = &$Match_Item_Processed_Command['Chunks'][0]['Item'];
			if (!$Match_Item['End_Of_Results'])
			{	
				// Specifize Matched Item
				// TODO - do we want to do specific item queries for every match (see below)?
				if ($Match_Item['Cached_Specific_Type']['Alias'] != $Match_Item['Cached_Base_Type']['Alias'])
				{
					$Match_Item_Specific_Type_Alias = &$Match_Item['Cached_Specific_Type']['Alias'];
					$Match_Item_ID = &$Match_Item['Data']['ID'];
					
					$Specific_Item_Command_String = &New_String($Match_Item_Specific_Type_Alias . ' where ID = ' . '"' .  $Match_Item_ID . '" from Database as Reference');
					$Specific_Item_Processed_Command = &Process_Command_String($Database, $Specific_Item_Command_String, $Memory_Stack_Reference);
					$Specific_Item = &$Specific_Item_Processed_Command['Chunks'][0]['Item'];
					
					$Match_Item = &$Specific_Item;
				}
				
				// Advance current path item.
				$Current_Path_Item = &$Match_Item;

				// Get children
				for ($Identifier_Part_Index = 1; $Identifier_Part_Index < count($Identifier_Parts); $Identifier_Part_Index++)
				{
					$Current_Identifier = &$Identifier_Parts[$Identifier_Part_Index];

					$Next_Path_Item = &Get_Next_Path_Item($Database, $Current_Path_Item, $Current_Identifier, $Memory_Stack_Reference);
					
					switch ($Next_Path_Item['Kind'])
					{
						case 'Item':
							$Current_Path_Item = &$Next_Path_Item['Item'];
							break;

						case 'Not_Set':
							$Current_Path_Item = &New_Null();
							break 2;
							break;
							
						case 'Template':
						case 'Property':
							if ($Identifier_Part_Index == count($Identifier_Parts) - 1)
								$Processed_URL[$Next_Path_Item['Kind']] = &$Next_Path_Item[$Next_Path_Item['Kind']];
							else
								break 2;
							break;
					}
				}
			}
		}
	}
		
	// If nothing has been found, get and return the not found path item.
	if (!$Current_Path_Item)
	{	
		if ($Debug)
			echo "Process_URL: not found...\n<br/>";

		$Current_Path_Item = &Get_Not_Found_Item($Database, $Memory_Stack_Reference);
		unset($Processed_URL['Template']);
		unset($Processed_URL['Property']);
	}
	
	// Set item
	$Processed_URL['Item'] = &$Current_Path_Item;
	
	// If neither property nor template is set, set default template for items
	if (!isset($Processed_URL['Property']) && !isset($Processed_URL['Template']))
	{
		$Processed_Item_Type_Alias = &$Processed_URL['Item']['Cached_Specific_Type']['Alias'];
		$Processed_URL['Template'] = &$Cached_Type_Lookup[strtolower($Processed_Item_Type_Alias)]['Cached_Template_Lookup'][strtolower('default')];
	}
	
	//Return processed URL
	return $Processed_URL;
}
?>