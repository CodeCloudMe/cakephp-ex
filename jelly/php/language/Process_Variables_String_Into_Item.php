<?php

function &Process_Variables_String_Into_Item(&$Database, &$Variables_String, &$Memory_Stack_Reference)
{
	// TODO - this doesn't handle values like True/False/Null. Not sure if these should be treated as strings or have a way to escape them?

	// Transform string into initial array.
	$Variables_Initial_Array = &New_Array();
	
	// TODO store original-text-case of the variable names
	
	// Notice: $Variables_Parts not by reference
	$Variables_Parts = explode(",", $Variables_String);

	foreach($Variables_Parts as &$Variables_Part)
	{
		// Check if variable part is a name/value pair
		if (strpos($Variables_Part, '=') !== false)
		{
			// Notice: $Variable_Name, $Variable_Value not by reference
			list($Variable_Name, $Variable_Value) = explode('=', $Variables_Part);
		
			// Notice: $Variables_Initial_Array not by reference
			$Variables_Initial_Array[strtolower($Variable_Name)] = $Variable_Value;
		}
		else
		{
			// Make sure variable part is not empty
			if (strlen($Variables_Part) != 0)
			{
				$Variables_Initial_Array[strtolower($Variables_Part)] = true;
			}
		}
	}
	
	// Create variables item 
	$Simple_Item_Type = &Get_Cached_Type($Database, 'Simple_Item');
	$Variables_Item = &Create_Memory_Item($Database, $Simple_Item_Type);

	// Resolve types and set values of variable item.
	foreach($Variables_Initial_Array as $Variable_Initial_Name => &$Variable_Initial_Value)
	{
		// If variable is a type, ignore it.
		if (strtolower(substr($Variable_Initial_Name, -strlen('_type'))) == '_type')
		{
			if (array_key_exists(substr($Variable_Initial_Name, 0, strlen($Variable_Initial_Name) - strlen('_type')), $Variables_Initial_Array))
				continue;
		}
	
		// If variable has a type, resolve the variable into an item and set it
		if (array_key_exists($Variable_Initial_Name . '_' . 'type', $Variables_Initial_Array))
		{
			$Variable_Type_Alias = &$Variables_Initial_Array[$Variable_Initial_Name . '_' . 'type'];
			$Variable_Type = &Get_Cached_Type($Database, $Variable_Type_Alias);
			$Variable_Type_Key_Alias = &$Variable_Type['Default_Key'];

			// TODO: hej, overwritten for easier testing.
			// TODO: redo as by alias, then id if numeric? 
// 			$Variable_Type_Key_Alias = 'alias';
			
			// TODO this key stuff isn't implemented well
			
			$Variable_Key = &$Variable_Initial_Value;
			if (is_numeric($Variable_Key))
				$Variable_Type_Key_Alias = &New_String('ID');
			
			$Variable_Command_String = &New_String($Variable_Type_Alias . ' ' . 'from database' . ' ' . 'where' . ' ' . $Variable_Type_Key_Alias . ' ' . '=' . ' ' . '"' . $Variable_Key . '"' . ' ' . 'as Reference');
			$Variable_Value_Processed_Command = &Process_Command_String($Database, $Variable_Command_String, $Memory_Stack_Reference);
		
			// TODO: Kunal added this code to get it the item from the chunk, seemed unfinished before - but is this correct?
			$Variable_Value_Item = &$Variable_Value_Processed_Command['Chunks'][0]['Item'];
			
			Set_Value($Variables_Item, $Variable_Initial_Name, $Variable_Value_Item);
		}
	
		// If variable is simple, set it.
		else
			Set_Value($Variables_Item, $Variable_Initial_Name, $Variable_Initial_Value);
	}
	
	return $Variables_Item;

}

?>