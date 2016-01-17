<?php

// Get Cached Type

function &Get_Cached_Type(&$Database, $Type_Lookup)
{
	// Require type lookup or throw exception
	if (!$Type_Lookup)
		throw new Exception('Get Cached Type: no alias set');
	
	// Verify that type exists or throw exception.
	$Cached_Type_Lookup = &$Database['Cached_Type_Lookup'];
	if (!isset($Cached_Type_Lookup[strtolower($Type_Lookup)]))
		throw new Exception('No type found: ' . $Type_Lookup);
	
	// Return cached type from cached types table.
	$Result = &$Cached_Type_Lookup[strtolower($Type_Lookup)];	
	return $Result;
}

?>