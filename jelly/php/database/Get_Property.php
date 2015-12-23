<?php

// Get Property

// TODO: Get_Cached_Property?
function &Get_Property(&$Item, $Property_Lookup)
{
	// Check if it is a temporary property
	// TODO
// 	global $Temporary_Properties;
// 	if (isset($Temporary_Properties[strtolower($Property_Lookup)]))
// 		return $Temporary_Properties[strtolower($Property_Lookup)];
		
	// Check if property exists in item
	if (isset($Item['Cached_Specific_Property_Lookup'][strtolower($Property_Lookup)]))
		return $Item['Cached_Specific_Property_Lookup'][strtolower($Property_Lookup)];
	
	// Check if property exists in type
	if (isset($Item['Cached_Specific_Type']['Cached_Property_Lookup'][strtolower($Property_Lookup)]))
		return $Item['Cached_Specific_Type']['Cached_Property_Lookup'][strtolower($Property_Lookup)];
		
	traverse($Item);
	traverse($Item['References']);
	traverse($Property_Lookup);
	throw new exception("Should have returned a property by now.");
}

?>