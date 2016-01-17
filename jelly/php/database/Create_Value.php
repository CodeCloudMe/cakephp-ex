<?php

// Create Value

function &Create_Value(&$Item, $Property_Lookup)
{
	// Require a type property
	if (!Has_Property($Item, $Property_Lookup))
		throw new Exception('Tried to create value but no property exists.');
	
	// Get property
	$Cached_Property = &Get_Property($Item, $Property_Lookup);
			
	// Get property's value type
	$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
	
	// Create value item
	$Database = &$Item['Database'];
	$Value = &Create_Memory_Item($Database, $Cached_Property_Value_Type);
	
	// Save value item
	Save_Item($Value);
	
	// Add value item to parent
	Add_Value($Item, $Property_Lookup, $Value);
	
	// Return new value
	return $Value;
}

?>