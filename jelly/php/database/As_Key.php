<?php

function &As_Key(&$Value, &$Key_Property_Alias = null)
{
	// If this is an item, resolve it to a key value.
	if (Is_Item($Value))
	{	
		if (is_null($Key_Property_Alias))
		{		
			// Get value type
			$Value_Type = &$Value['Cached_Specific_Type'];
		
			// Get value type default key
			$Value_Type_Default_Key_Property_Alias =  &$Value_Type['Default_Key'];
			
			// Get key property alias.
			$Key_Property_Alias = &$Value_Type_Default_Key_Property_Alias;
		}
		
		// If key value exists, resolve the return value to this key value.
		if (!$Value['End_Of_Results'] && array_key_exists($Key_Property_Alias, $Value['Data']))
		{	
			$Value_Key = &$Value['Data'][$Key_Property_Alias];
		}

		// Otherwise, resolve the return value to null.
		// TODO - throw exception? 
		else
			$Value_Key = &New_Null();
	}

	// If this is a not set, resoluve it to null.
	else if (Is_Not_Set($Value))
	{
		$Value_Key = &New_Null();	
	}
	
	// Otherwise return value as is.
	else
	{
		// TODO - verify format, if provided.
		// Get key value
		$Value_Key = &$Value;
	}
	
	return $Value_Key;
}

?>