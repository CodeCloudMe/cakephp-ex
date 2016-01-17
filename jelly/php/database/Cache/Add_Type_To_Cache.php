<?php

function &Add_Type_To_Cache(&$Database, &$Type)
{
 	// TODO check this
// 	if (!array_key_exists('ID', $Type)) throw new Exception('Type ID required');
	if (!array_key_exists('Name', $Type)) throw new Exception('Type Name required');
	if (!array_key_exists('Alias', $Type)) throw new Exception('Type Alias required');
	if (!array_key_exists('Plural_Name', $Type)) throw new Exception('Type Plural_Name required');
	if (!array_key_exists('Plural_Alias', $Type)) throw new Exception('Type Plural_Alias required');
	if (!array_key_exists('Data_Name', $Type)) throw new Exception('Type Data_Name required');
	if (!array_key_exists('Default_Key', $Type)) throw new Exception('Type Default_Key required');
	if (!array_key_exists('Parent_Type', $Type))
		if (!(in_array(strtolower($Type['Alias']), array('item', 'simple_item')) || in_array(strtolower($Type['Alias']), $GLOBALS['Simple_Types'])))
			throw new Exception('Type Parent_Type required');
	
	// Create new cached type
	$Cached_Type = &New_Type();
	
	// Setup empty properties array
	$Cached_Type['All_Cached_Properties'] = array();
	$Cached_Type['Cached_Specific_Properties'] = array();
	$Cached_Type['Cached_Property_Lookup'] = array();
	
	// Setup empty templates arrays
	$Cached_Type['All_Cached_Templates'] = array();
	$Cached_Type['Cached_Specific_Templates'] = array();
	$Cached_Type['Cached_Template_Lookup'] = array();
	
	// Initialize child type array
	$Cached_Type['Cached_Specific_Child_Types'] = array();
	$Cached_Type['All_Cached_Child_Types'] = array();
	
	// Copy over relevant values
	$Cached_Type['ID'] = &$Type['ID'];
	$Cached_Type['Name'] = &$Type['Name'];
	$Cached_Type['Alias'] = &$Type['Alias'];
	$Cached_Type['Plural_Name'] = &$Type['Plural_Name'];
	$Cached_Type['Plural_Alias'] = &$Type['Plural_Alias'];
	$Cached_Type['Data_Name'] = &$Type['Data_Name'];
	$Cached_Type['Default_Key'] = &$Type['Default_Key'];
	$Cached_Type['Package'] = &$Type['Package'];
	
	// TODO: is_array()?
	if ($Type['Parent_Type'])
		$Cached_Type['Cached_Parent_Type'] = &Get_Cached_Type($Database, $Type['Parent_Type']);
	
	// Get Cached Type Alias
	$Cached_Type_Alias = &$Cached_Type['Alias'];
	
	// Add new type to All_Cached_Types and Cached_Type_Lookup
	$Database['All_Cached_Types'][strtolower($Cached_Type_Alias)] = &$Cached_Type;
	$Database['Cached_Type_Lookup'][strtolower($Cached_Type_Alias)] = &$Cached_Type;
	if ($Cached_Type['Plural_Alias'])
	{
		$Cached_Type_Plural_Alias = &$Cached_Type['Plural_Alias'];
		$Database['Cached_Type_Lookup'][strtolower($Cached_Type_Plural_Alias)] = &$Cached_Type;
	}
	
	// Add new type to Immediate Parent's Cached Specific Child Types
	if (isset($Cached_Type['Cached_Parent_Type']))
	{	
		$Cached_Type_Parent_Type = &$Cached_Type['Cached_Parent_Type'];
		$Cached_Type_Parent_Type['Cached_Specific_Child_Types'][strtolower($Cached_Type_Alias)] = &$Cached_Type;
	}
		
	// Add new type to All Cached Child Types of Type and all Parent Types
	$Recursive_Cached_Type_Parent_Type = &$Cached_Type;
	while (isset($Recursive_Cached_Type_Parent_Type))
	{
		// Add type to all cached child types of current parent type
		$Recursive_Cached_Type_Parent_Type['All_Cached_Child_Types'][strtolower($Cached_Type_Alias)] = &$Cached_Type;
	
		// Move up to parent type
		if (isset($Recursive_Cached_Type_Parent_Type['Cached_Parent_Type']))
			$Recursive_Cached_Type_Parent_Type = &$Recursive_Cached_Type_Parent_Type['Cached_Parent_Type'];
		else
			unset($Recursive_Cached_Type_Parent_Type);
	}
	
	// Add parent type properties to this type
	if (isset($Cached_Type['Cached_Parent_Type']))
	{
		$Cached_Type_Parent_Type = &$Cached_Type['Cached_Parent_Type'];
		foreach ($Cached_Type_Parent_Type['All_Cached_Properties'] as &$Cached_Type_Parent_Type_Property)
		{
			$Cached_Type['All_Cached_Properties'][strtolower($Cached_Type_Parent_Type_Property['Alias'])] = &$Cached_Type_Parent_Type_Property;
			$Cached_Type['Cached_Property_Lookup'][strtolower($Cached_Type_Parent_Type_Property['Alias'])] = &$Cached_Type_Parent_Type_Property;
		}
	}
	
	// Add parent type templates to this type
	if (isset($Cached_Type['Cached_Parent_Type']))
	{
		$Cached_Type_Parent_Type = &$Cached_Type['Cached_Parent_Type'];
		foreach ($Cached_Type_Parent_Type['All_Cached_Templates'] as &$Cached_Type_Parent_Type_Template)
		{
			$Cached_Type['All_Cached_Templates'][strtolower($Cached_Type_Parent_Type_Template['Alias'])] = &$Cached_Type_Parent_Type_Template;
			$Cached_Type['Cached_Template_Lookup'][strtolower($Cached_Type_Parent_Type_Template['Alias'])] = &$Cached_Type_Parent_Type_Template;
		}
	}
	
	// TODO reload specific type's properties and templates (for when we are re-saving a type that was already loaded; currently it loses its specific data)
	
	// Return the new cached type
	return $Cached_Type;
}

?>