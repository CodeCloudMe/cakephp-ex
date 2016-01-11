<?php

// Generate Database Cache

function Generate_Database_Cache(&$Database)
{
	// Load local variables
	$Table_Prefix = &$Database['Table_Prefix'];
	
	// Get Database Link
	$Database_Link = &$Database['Link'];
	
	// If Data table does not exist, reset the database
	// TODO - Parameters? Depends on Reset Database.
	if (isset($Parameters['Reset']) && $Parameters['Reset'])
	{
		// If we're not resetting, simply exit (i.e. requesting favicon from blank database)
		if (isset($GLOBALS['No_Reset']) && $GLOBALS['No_Reset'])
			exit;
		
		Restart_Database($Database);
		
		Load_Defaults();
	}
	
	// Read database version
	// TODO: check database version on Connect?
	$Data_Table_Name = &New_String($Table_Prefix . 'Data');
	$Data_Query = &New_String('(SELECT * FROM `' . $Data_Table_Name . '`)');
	try
	{
		$Data_Result = &Query($Database, $Data_Query);
	}
	catch (Exception $e)
	{
		echo 'should reset?'; return;
	}
	
	
	if (!($Data_Row = mysqli_fetch_assoc($Data_Result)))
	{
		// Otherwise, reset the database
//		Reset_Database();
		echo 'should reset?';
		
		// Try getting data table a second time after resetting
		$Data_Result = Query($Database, $Data_Query);
		if (!($Data_Row = mysqli_fetch_assoc($Data_Result)))
			throw new Exception('Cannot get database version for sure');
	}
	$Database_Version = &$Data_Row['Database_Version'];
	mysqli_free_result($Data_Result);
	
	// Upgrade tables
	switch ($Database_Version)
	{
		case 1:
			break;
		default:
			throw new Exception('Unknown database version: ' . $Database_Version);
			break;
	}
	
	
	// Setup type cache arrays
	$All_Cached_Types = &New_Array();
	$Cached_Type_Lookup = &New_Array();
	
	// Store references globally
	$Database['All_Cached_Types'] = &$All_Cached_Types;
	$Database['Cached_Type_Lookup'] = &$Cached_Type_Lookup;
	
	// Load Types
	// TODO error-database: check for types that have unset important values
	// TODO: Might not need all columns listed.
	$Type_Table_Name = &New_String($Table_Prefix . 'Type');
	$Type_Query = &New_String('(SELECT `ID`, `Name`, `Alias`, `Plural_Name`, `Parent_Type`, `Default_Key`, `Data_Name`, `Plural_Alias` FROM `' . mysqli_real_escape_string($Database['Link'], $Type_Table_Name) . '`) ORDER BY `Name`');
	
	$Type_Result = Query($Database, $Type_Query);
	
	// Loop through type results
	while ($Type_Row = mysqli_fetch_assoc($Type_Result))
	{
		// Create cached type
		$Cached_Type = &New_Type();
		
		// Copy database row to cached type
		$Cached_Type['ID'] = &$Type_Row['ID'];
		$Cached_Type['Name'] = &$Type_Row['Name'];
		$Cached_Type['Alias'] = &$Type_Row['Alias'];
		$Cached_Type['Plural_Name'] = &$Type_Row['Plural_Name'];
		$Cached_Type['Parent_Type'] = &$Type_Row['Parent_Type'];
		$Cached_Type['Default_Key'] = &$Type_Row['Default_Key'];
		$Cached_Type['Data_Name'] = &$Type_Row['Data_Name'];
		$Cached_Type['Plural_Alias'] = &$Type_Row['Plural_Alias'];
		
		// Setup empty properties array
		$Cached_Type['All_Cached_Properties'] = &New_Array();
		$Cached_Type['Cached_Specific_Properties'] = &New_Array();
		$Cached_Type['Cached_Property_Lookup'] = &New_Array();
		
		// Setup empty templates arrays
		$Cached_Type['All_Cached_Templates'] = &New_Array();
		$Cached_Type['Cached_Specific_Templates'] = &New_Array();
		$Cached_Type['Cached_Template_Lookup'] = &New_Array();
		
		// Initialize child type array
		$Cached_Type['Cached_Specific_Child_Types'] = &New_Array();
		$Cached_Type['All_Cached_Child_Types'] = &New_Array();
		
		// Store item in local type caches
		$Cached_Type_Alias = &$Cached_Type['Alias'];
		$All_Cached_Types[strtolower($Cached_Type_Alias)] = &$Cached_Type;
		$Cached_Type_Lookup[strtolower($Cached_Type_Alias)] = &$Cached_Type;
		if ($Cached_Type['Plural_Alias'])
			$Cached_Type_Lookup[strtolower($Cached_Type['Plural_Alias'])] = &$Cached_Type;
	}
	mysqli_free_result($Type_Result);
	
	// Resolve parent types
	foreach ($All_Cached_Types as &$Cached_Type)
	{
		if ($Cached_Type['Parent_Type'])
		{
			// Resolve parent type
			$Cached_Parent_Type = &$Cached_Type_Lookup[strtolower($Cached_Type['Parent_Type'])];
			$Cached_Type['Cached_Parent_Type'] = &$Cached_Parent_Type;
			unset($Cached_Type['Parent_Type']);

			// Add type to parent's specific child types
			$Cached_Parent_Type['Cached_Specific_Child_Types'][strtolower($Cached_Type['Alias'])] = &$Cached_Type;			
		}
	}
	
	// Add types to all of parent type's child types
	foreach ($All_Cached_Types as &$Cached_Type)
	{
		// Loop upwards through parent types
		$Recursive_Cached_Parent_Type = &$Cached_Type;

		while (isset($Recursive_Cached_Parent_Type))
		{
			// Add type to all of parent's all child types
			$Recursive_Cached_Parent_Type['All_Cached_Child_Types'][strtolower($Cached_Type['Alias'])] = &$Cached_Type;
			
			// Move up to next parent type
			if (isset($Recursive_Cached_Parent_Type['Cached_Parent_Type']))
				$Recursive_Cached_Parent_Type = &$Recursive_Cached_Parent_Type['Cached_Parent_Type'];
			else
				unset($Recursive_Cached_Parent_Type);
		}
	}
	
	// Load Properties
	// TODO error-database: check for properties that have unset important values
	// TODO - not sure yet if we will end up needing 'Status" in cached properties
	$Property_Table_Name = &New_String($Table_Prefix . 'Property');
	$Property_Query = &New_String('(SELECT `ID`, `Name`, `Reverse_Name`, `Plural_Name`, `Reverse_Plural_Name`, `Alias`,  `Reverse_Alias`, `Plural_Alias`, `Reverse_Plural_Alias`, `Type`, `Value_Type`, `Relation`, `Key`, `Reverse_Key`, `Data_Name`, `Reverse_Data_Name`, `Attachment_Type`, `Default_Value` FROM `' . mysqli_real_escape_string($Database['Link'], $Property_Table_Name) . '`) ORDER BY `Type`, `Alias`');
	$Property_Result = Query($Database, $Property_Query);
	while ($Property_Row = mysqli_fetch_assoc($Property_Result))
	{
		Add_Property_To_Cache($Database, $Property_Row);
	}
	mysqli_free_result($Property_Result);
	
	
	// Load Templates
	// TODO error-database: check for templates that have unset important values
	$Template_Table_Name = &New_String($Table_Prefix . 'Template');
	$Template_Query = &New_String('SELECT `ID`, `Name`, `Alias`, `Type`, `Content`, `Content_Type` FROM `' . mysqli_real_escape_string($Database['Link'], $Template_Table_Name) . '` WHERE (`Status` = "Published") ORDER BY `Order`');
	$Template_Result = Query($Database, $Template_Query);
	while ($Template_Row = mysqli_fetch_assoc($Template_Result))
	{
		// Create Cached Template (copy, not by reference)
		$Cached_Template = &New_Template();
		
		// Copy database row to cached template
		$Cached_Template['ID'] = &$Template_Row['ID'];
		$Cached_Template['Name'] = &$Template_Row['Name'];
		$Cached_Template['Alias'] = &$Template_Row['Alias'];
		$Cached_Template['Type'] = &$Template_Row['Type'];
		$Cached_Template['Content'] = &$Template_Row['Content'];
		$Cached_Template['Content_Type'] = &$Template_Row['Content_Type'];
		
		// Store item by reference
		$Cached_Template_Type = &$Cached_Type_Lookup[strtolower($Template_Row['Type'])];
		$Cached_Template_Type['Cached_Specific_Templates'][] = &$Cached_Template;
		$Cached_Template_Type['All_Cached_Templates'][] = &$Cached_Template;
		$Template_Alias = &$Template_Row['Alias'];
		$Cached_Template_Type['Cached_Template_Lookup'][strtolower($Template_Alias)] = &$Cached_Template;
		$Cached_Template['Cached_Type'] = &$Cached_Template_Type;
		unset($Template_Row['Type']);
		
		// Reset reference
		unset($Cached_Template);
	}
	mysqli_free_result($Template_Result);
	
	// Resolve templates
	foreach ($All_Cached_Types as &$Cached_Type)
	{
		// Loop upwards through parent types
		$Recursive_Cached_Parent_Type = &$Cached_Type;

		while (isset($Recursive_Cached_Parent_Type))
		{
			// Add all of parent type's templates to type
			foreach ($Recursive_Cached_Parent_Type['Cached_Specific_Templates'] as &$Cached_Template)
			{
				$Cached_Template_Alias = &$Cached_Template['Alias'];
				
				// If a template is not already in the cache by this template alias, add template to type by this template alias.
				if (!isset($Cached_Type['Cached_Template_Lookup'][strtolower($Cached_Template_Alias)]))
				{
					$Cached_Type['Cached_Template_Lookup'][strtolower($Cached_Template_Alias)] = &$Cached_Template;
					$Cached_Type['All_Cached_Templates'][] = &$Cached_Template;
				}
			}
			
			// Move up to next parent type
			if (isset($Recursive_Cached_Parent_Type['Cached_Parent_Type']))
				$Recursive_Cached_Parent_Type = &$Recursive_Cached_Parent_Type['Cached_Parent_Type'];
			else
				unset($Recursive_Cached_Parent_Type);
		}
	}
}

?>