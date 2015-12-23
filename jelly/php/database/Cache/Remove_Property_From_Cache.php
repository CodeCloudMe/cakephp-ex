<?php

// Remove Property From Cache

function Remove_Property_From_Cache(&$Database, &$Cached_Property)
{
	$Cached_Property_Alias = &$Cached_Property['Alias'];
	$Cached_Property_Reverse_Alias = &$Cached_Property['Reverse_Alias'];
	
	// Remove Property From Type's Cache
	$Cached_Property_Type = &$Cached_Property['Cached_Type'];
	foreach ($Cached_Property_Type['All_Cached_Child_Types'] as &$Cached_Property_Type_Child_Type)
	{
		// Remove Property from Type's Cached Properties by Alias
		unset($Cached_Property_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Alias)]);
		unset($Cached_Property_Type_Child_Type['All_Cached_Properties'][strtolower($Cached_Property_Alias)]);
		unset($Cached_Property_Type_Child_Type['Cached_Specific_Properties'][strtolower($Cached_Property_Alias)]);
		
		// Remove Attachment Property from Type's Cached Properties
		if (isset($Cached_Property['Cached_Attachment_Type']))
		{
			unset($Cached_Property_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Alias . '_Attachment')]);
			unset($Cached_Property_Type_Child_Type['All_Cached_Properties'][strtolower($Cached_Property_Alias . '_Attachment')]);
			unset($Cached_Property_Type_Child_Type['Cached_Specific_Properties'][strtolower($Cached_Property_Alias . '_Attachment')]);
		}
		
		// Check if Property has a Plural Alias
		if (isset($Cached_Property['Plural_Alias']) && $Cached_Property['Plural_Alias'])
		{
			$Cached_Property_Plural_Alias = &$Cached_Property['Plural_Alias'];
			
			// Remove Property and Attachment Property from Type's Cached Properties by Plural Alias
			unset($Cached_Property_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Plural_Alias)]);
			if (isset($Cached_Property['Attachment']))
				unset($Cached_Property_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Plural_Alias . '_Attachment')]);
		}
	}
	
	// Remove from Value Type's cache
	$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
	foreach ($Cached_Property_Value_Type['All_Cached_Child_Types'] as &$Cached_Property_Value_Type_Child_Type)
	{
		// Remove From Type's Cached Property Lookup by Reverse Alias
		unset($Cached_Property_Value_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Reverse_Alias)]);
		unset($Cached_Property_Value_Type_Child_Type['All_Cached_Properties'][strtolower($Cached_Property_Reverse_Alias)]);
		unset($Cached_Property_Value_Type_Child_Type['Cached_Specific_Properties'][strtolower($Cached_Property_Reverse_Alias)]);
		
		// Remove Attachment Property from Value Type's Cached Properties
		if (isset($Cached_Property['Cached_Attachment_Type']))
		{
			unset($Cached_Property_Value_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Reverse_Alias . '_Attachment')]);
			unset($Cached_Property_Value_Type_Child_Type['All_Cached_Properties'][strtolower($Cached_Property_Reverse_Alias . '_Attachment')]);
			unset($Cached_Property_Value_Type_Child_Type['Cached_Specific_Properties'][strtolower($Cached_Property_Reverse_Alias . '_Attachment')]);
		}
		
		// Check if Property has a Reverse Plural Alias
		if (isset($Cached_Property['Reverse_Plural_Alias']) && $Cached_Property['Reverse_Plural_Alias'])
		{
			$Cached_Property_Reverse_Plural_Alias = &$Cached_Property['Reverse_Plural_Alias'];
			
			// Remove Property and Attachment Property from Value Type's Cached Properties by Reverse Plural Alias
			unset($Cached_Property_Value_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Reverse_Plural_Alias)]);
			if (isset($Cached_Property['Attachment']))
				unset($Cached_Property_Value_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Reverse_Plural_Alias . '_Attachment')]);
		}
	}
	
	// If Many-To-Many, remove from Attachment Type's cache
	if (isset($Cached_Property['Cached_Attachment_Type']))
	{
		$Cached_Property_Attachment_Type = &$Cached_Property['Cached_Attachment_Type'];
		
		foreach ($Cached_Property_Attachment_Type['All_Cached_Child_Types'] as &$Cached_Property_Attachment_Type_Child_Type)
		{
			// Remove From Attachment Type's Cached Property Lookup by Alias
			unset($Cached_Property_Attachment_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Alias)]);
			unset($Cached_Property_Attachment_Type_Child_Type['All_Cached_Properties'][strtolower($Cached_Property_Alias)]);
			unset($Cached_Property_Attachment_Type_Child_Type['Cached_Specific_Properties'][strtolower($Cached_Property_Alias)]);
			
			// Remove From Attachment Type's Cached Property Lookup by Reverse Alias
			unset($Cached_Property_Attachment_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Reverse_Alias)]);
			unset($Cached_Property_Attachment_Type_Child_Type['All_Cached_Properties'][strtolower($Cached_Property_Reverse_Alias)]);
			unset($Cached_Property_Attachment_Type_Child_Type['Cached_Specific_Properties'][strtolower($Cached_Property_Reverse_Alias)]);
			
			// Check if Property has a Plural Alias
			if (isset($Cached_Property['Plural_Alias']) && $Cached_Property['Plural_Alias'])
			{
				$Cached_Property_Plural_Alias = &$Cached_Property['Plural_Alias'];
				
				// Remove Property from Attachment Type's Cached Properties by Plural Alias
				unset($Cached_Property_Attachment_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Plural_Alias)]);
			}
			
			// Check if Property has a Reverse Plural Alias
			if (isset($Cached_Property['Reverse_Plural_Alias']) && $Cached_Property['Reverse_Plural_Alias'])
			{
				$Cached_Property_Reverse_Plural_Alias = &$Cached_Property['Reverse_Plural_Alias'];
				
				// Remove Property from Attachment Type's Cached Properties by Reverse Plural Alias
				unset($Cached_Property_Attachment_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Reverse_Plural_Alias)]);
			}
		}
	}
}

?>