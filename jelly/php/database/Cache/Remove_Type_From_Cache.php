<?php

function Remove_Type_From_Cache(&$Database, &$Cached_Type)
{
	// Get Cached Type Alias
	$Cached_Type_Alias = &$Cached_Type['Alias'];
	
	// Remove from global cache
	unset($Database['All_Cached_Types'][strtolower($Cached_Type_Alias)]);
	unset($Database['Cached_Type_Lookup'][strtolower($Cached_Type_Alias)]);
	if (isset($Cached_Type['Plural_Alias']))
	{
		$Cached_Type_Plural_Alias = &$Cached_Type['Plural_Alias'];
		unset($Database['Cached_Type_Lookup'][strtolower($Cached_Type_Plural_Alias)]);
	}
	
	// Remove from specific child types of immediate parent type
	if (isset($Cached_Type['Cached_Parent_Type']))
	{
		$Cached_Type_Parent_Type = &$Cached_Type['Cached_Parent_Type'];
		unset($Cached_Type_Parent_Type['Cached_Specific_Child_Types'][strtolower($Cached_Type_Alias)]);
	}
	
	// Remove from child types of type and all parent types
	$Recursive_Cached_Type_Parent_Type = &$Cached_Type;
	while (isset($Recursive_Cached_Type_Parent_Type))
	{
		unset($Cached_Type_Parent_Type['All_Cached_Child_Types'][strtolower($Cached_Type_Alias)]);
		
		// Move up to parent type
		if (isset($Recursive_Cached_Type_Parent_Type['Cached_Parent_Type']))
			$Recursive_Cached_Type_Parent_Type = &$Recursive_Cached_Type_Parent_Type['Cached_Parent_Type'];
		else
			unset($Recursive_Cached_Type_Parent_Type);
	}
}

?>