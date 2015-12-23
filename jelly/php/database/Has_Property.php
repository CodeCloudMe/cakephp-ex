<?php

// Has Property

function Has_Property(&$Item, $Property_Lookup)
{
	// Check if property exists in item
	if (isset($Item['Cached_Specific_Property_Lookup'][strtolower($Property_Lookup)]))
		return true;
	
	// Check if property exists in type
	if (isset($Item['Cached_Specific_Type']['Cached_Property_Lookup'][strtolower($Property_Lookup)]))
		return true;
	
	return false;
}

?>