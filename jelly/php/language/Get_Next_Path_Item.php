<?php

//  Processed URL is an Item, an Original URL, a Kind, a Template, and Raw flag
function &Get_Next_Path_Item(&$Database, &$Current_Path_Item, &$Current_Identifier, &$Memory_Stack_Reference)
{	
	$Debug = false;
	
	// Load globals
	$Cached_Type_Lookup = &$Database['Cached_Type_Lookup'];
	
	$Next_Path_Item = &New_Array();
	
	// Match various path children of current path item, and continue if match found.
	if ($Debug)
	{
		echo "Process_URL: found...";
		echo "Process_URL: finding a child element...\n (matching $Current_Identifier)<br/>";
	}
	
	// Get current path item type alias
	$Current_Path_Item_Type_Alias = &$Current_Path_Item['Cached_Specific_Type']['Alias'];	
	
	// Match type action of current path item by alias
	// TODO check if well-formed alias?
	// TODO: maybe include /Action/ to specify actions
	// TODO - May merge with this Template & Property handling, meaning they can lead into each other in various directions, and targets might be set as needed. 
	// This sets the target of the type action item automatically.
	{
		if ($Debug)
			echo "Process_URL: checking actions...\n<br/>";
		
		$Match_Type_Action_Command_String = &New_String('Action' . ' ' . $Current_Identifier);
		$Match_Type_Action_Command = &Parse_String_Into_Command($Match_Type_Action_Command_String);
		$Match_Type_Action_Item = &Get_Value($Current_Path_Item, $Match_Type_Action_Command);
		
		if (!$Match_Type_Action_Item['End_Of_Results'])
		{	
			$Next_Path_Item['Kind'] = &New_String('Item');
			$Next_Path_Item['Item'] = &$Match_Type_Action_Item;
			return $Next_Path_Item;
		}
	}
	
	// Match template
	// Match templates of current path item and complete search.
	if ($Debug)
			echo "Process_URL: checking templates...\n<br/>";
	if (isset($Cached_Type_Lookup[strtolower($Current_Path_Item_Type_Alias)]['Cached_Template_Lookup'][strtolower($Current_Identifier)]))
	{
		// TODO - this kind does not look right.
		$Next_Path_Item['Kind'] = &New_String('Template');
		$Next_Path_Item['Template'] = &$Cached_Type_Lookup[strtolower($Current_Path_Item_Type_Alias)]['Cached_Template_Lookup'][strtolower($Current_Identifier)];
		return $Next_Path_Item;
	}
		
	// If current path item is a type, search for alias's or ids that are items of that type.	
	if (strtolower($Current_Path_Item_Type_Alias) == 'type')
	{		
		if ($Debug)
			echo "Process_URL: checking items of this type by alias or id...\n<br/>";
			
		// Get current type alias					
		$Cached_Type_Alias = &$Current_Path_Item['Data']['Alias'];
	
		// Match items of current type by alias.
		// TODO: 'Print'
		$Match_Item_Of_Type_Command_String = &New_String($Cached_Type_Alias . ' where Alias = ' . '"' .  $Current_Identifier . '" from Database as Reference');
		$Match_Item_Of_Type_Processed_Command = &Process_Command_String($Database, $Match_Item_Of_Type_Command_String, $Memory_Stack_Item);
		$Match_Item_Of_Type_Item = &$Match_Item_Of_Type_Processed_Command['Chunks'][0]['Item'];
			
		if (!$Match_Item_Of_Type_Item['End_Of_Results'])
		{	
			$Next_Path_Item['Kind'] = &New_String('Item');
			$Next_Path_Item['Item'] = &$Match_Item_Of_Type_Item;
			return $Next_Path_Item;
		}
		
		// Match items of current type by ID.
		// TODO: combine with above?
		if (is_numeric($Current_Identifier))
		{
			$Match_Item_Of_Type_Command_String = &New_String($Cached_Type_Alias . ' where ID = ' . '"' .  $Current_Identifier . '" from Database as Reference');
			$Match_Item_Of_Type_Processed_Command = &Process_Command_String($Database, $Match_Item_Of_Type_Command_String, $Memory_Stack_Item);
			$Match_Item_Of_Type_Item = &$Match_Item_Of_Type_Processed_Command['Chunks'][0]['Item'];

			if (!$Match_Item_Of_Type_Item['End_Of_Results'])
			{	
				$Next_Path_Item['Kind'] = &New_String('Item');
				$Next_Path_Item['Item'] = &$Match_Item_Of_Type_Item;
				return $Next_Path_Item;
			}
		}
	}
	
	// Match children of properties of current path item by property alias
	// TODO should this require Admin?
	// TODO once a Property is hit (or Template for that matter) then URL matching should stop or return error if there is more left
	{
		if ($Debug)
			echo "Process_URL: checking children of properties of this item by property alias...\n<br/>";

		if (Has_Property($Current_Path_Item, $Current_Identifier))
		{
			$Match_Property_Lookup = &$Current_Identifier;
			$Match_Property = &Get_Property($Current_Path_Item, $Match_Property_Lookup);
			$Match_Property_Value_Type = &$Match_Property['Cached_Value_Type'];
			
			if (Is_Simple_Type($Match_Property_Value_Type))
			{
				$Next_Path_Item['Kind'] = &New_String('Property');
				$Next_Path_Item['Property'] = &$Match_Property;
				return $Next_Path_Item;
			}
			else
			{
				$Match_Property_Alias_Command_String = &New_String($Current_Identifier);
				$Match_Property_Alias_Command = &Parse_String_Into_Command($Match_Property_Alias_Command_String);
				$Match_Property_Alias_Item = &Get_Value($Current_Path_Item, $Match_Property_Alias_Command);
				
				if (!$Match_Property_Alias_Item['End_Of_Results'])
				{	
					$Next_Path_Item['Kind'] = &New_String('Item');
					$Next_Path_Item['Item'] = &$Match_Property_Alias_Item;
					return $Next_Path_Item;
				}
			}
		}
	}
	
	// Match children of properties of current path item by item alias
	{
		if ($Debug)
			echo "Process_URL: checking children of properties of this item by item alias...\n<br/>";
			
		// For each property of current path item
		$Current_Path_Item_Cached_Properties = &$Cached_Type_Lookup[strtolower($Current_Path_Item_Type_Alias)]['All_Cached_Properties'];
		foreach ($Current_Path_Item_Cached_Properties as &$Current_Path_Item_Cached_Property)
		{
			$Current_Path_Item_Cached_Property_Relation = &$Current_Path_Item_Cached_Property['Relation'];
			$Current_Path_Item_Cached_Property_Value_Type = &$Current_Path_Item_Cached_Property['Cached_Value_Type'];
			
			// If the property is a multiple, complex property, search for a child value that matches this alias 
			if (strtolower($Current_Path_Item_Cached_Property_Relation) != MANY_TO_ONE && !Is_Simple_Type($Current_Path_Item_Cached_Property_Value_Type))
			{
				$Current_Path_Item_Cached_Property_Alias = &$Current_Path_Item_Cached_Property['Alias'];
				// TODO - Maybe we can write Get_Value to be able to handle these normally, but we escape them right now.
				if (!in_array($Current_Path_Item_Cached_Property_Alias, ['Action','Property', 'Template']))
					{
					// TODO - i think i'm supposed to use quotes, but unsure.  
					$Match_Child_Alias_Command_String = &New_String($Current_Path_Item_Cached_Property_Alias . ' ' . 'Where' . ' ' . 'Alias' . ' ' . '=' . ' ' . '"' . $Current_Identifier . '"');
					$Match_Child_Alias_Command = &Parse_String_Into_Command($Match_Child_Alias_Command_String);
					$Match_Child_Alias_Item = &Get_Value($Current_Path_Item, $Match_Child_Alias_Command);
					if (!$Match_Child_Alias_Item['End_Of_Results'])
					{	
						$Next_Path_Item['Kind'] = &New_String('Item');
						$Next_Path_Item['Item'] = &$Match_Child_Alias_Item;
						return $Next_Path_Item;
					}
				}
			}
		}
	}
	
	// Match as "not set"
	$Next_Path_Item = &New_Not_Set();
	return $Next_Path_Item;
}

?>