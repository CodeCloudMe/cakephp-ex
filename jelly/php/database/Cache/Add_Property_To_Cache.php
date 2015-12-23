<?php

function Add_Property_To_Cache(&$Database, &$Property)
{
	// TODO @core-database: other exceptions below for various relations etc
// 	if (!array_key_exists('ID', $Property)) throw new Exception('Property ID required');
	if (!array_key_exists('Name', $Property)) throw new Exception('Property Name required');
	if (!array_key_exists('Alias', $Property)) throw new Exception('Property Alias required');
	if (!array_key_exists('Data_Name', $Property)) throw new Exception('Property Data_Name required');
	if (!array_key_exists('Type', $Property)) throw new Exception('Property Type required');
	if (!array_key_exists('Value_Type', $Property)) throw new Exception('Property Value_Type required');
	
	// Store references to type and value type
	$Property_Type_Alias = &$Property['Type'];
	
	if (strtolower($Property['Relation']) == COMMUTATIVE)
		$Property_Value_Type_Alias = &$Property['Type'];
	else
		$Property_Value_Type_Alias = &$Property['Value_Type'];

	$Cached_Property_Type = &Get_Cached_Type($Database, strtolower($Property_Type_Alias));
	$Cached_Property_Value_Type = &Get_Cached_Type($Database, strtolower($Property_Value_Type_Alias));
	
	// Generate: Cached_Forward_Property, Cached_Reverse_Property, Cached_Attachment_Type_Forward_Property, Cached_Attachment_Type_Reverse_Property, Cached_Attachment_Forward_Property, Cached_Attachment_Reverse_Property
	
	// Create Cached Forward Property
	$Cached_Forward_Property = &New_Property();
	$Cached_Forward_Property['Name'] = &$Property['Name'];
	$Cached_Forward_Property['Alias'] = &$Property['Alias'];
	$Cached_Forward_Property['ID'] = &$Property['ID'];
	$Cached_Forward_Property['Cached_Type'] = &$Cached_Property_Type;
	$Cached_Forward_Property['Cached_Value_Type'] = &$Cached_Property_Value_Type;
	
	// Check if property is Complex or Simple
	if (!Is_Simple_Type($Cached_Property_Value_Type))
	{
		// Additional Forward Properties
		$Cached_Forward_Property['Relation'] = &$Property['Relation'];
		
		if (strtolower($Property['Relation']) != COMMUTATIVE)
		{
			// Get reverse alias or generate if not set
			if ($Property['Reverse_Alias'])
				$Property_Reverse_Alias = &$Property['Reverse_Alias'];
			else
				$Property_Reverse_Alias = &New_String($Property['Alias'] . '_Reverse');
		
			// Create Cached Reverse Property
			global $Relation_Inverses;
			$Cached_Reverse_Property = &New_Property();
			$Cached_Reverse_Property['Name'] = &$Property['Reverse_Name'];
			$Cached_Reverse_Property['Alias'] = &$Property_Reverse_Alias;
			$Cached_Reverse_Property['ID'] = &$Property['ID'];
			$Cached_Reverse_Property['Cached_Type'] = &$Cached_Property_Value_Type;
			$Cached_Reverse_Property['Cached_Value_Type'] = &$Cached_Property_Type;
			$Cached_Reverse_Property['Relation'] = &$Relation_Inverses[strtolower($Property['Relation'])];
	
			// Set reverse aliases in Forward and Reverse Properties
			// TODO @feature-database: dereference Reverse Alias
			$Cached_Forward_Property['Reverse_Alias'] = &$Cached_Reverse_Property['Alias'];
			$Cached_Reverse_Property['Reverse_Alias'] = &$Cached_Forward_Property['Alias'];
		}
		
		
		switch (strtolower($Property['Relation']))
		{
			case COMMUTATIVE:
				$Cached_Forward_Property['Plural_Alias'] = &$Property['Plural_Alias'];
				break;

			case MANY_TO_MANY:
				$Cached_Forward_Property['Plural_Alias'] = &$Property['Plural_Alias'];
				$Cached_Reverse_Property['Plural_Alias'] = &$Property['Reverse_Plural_Alias'];
				
				$Cached_Forward_Property['Reverse_Plural_Alias'] = &$Cached_Reverse_Property['Plural_Alias'];
				$Cached_Reverse_Property['Reverse_Plural_Alias'] = &$Cached_Forward_Property['Plural_Alias'];
				
				// TODO: think about Default_Value for Many-To-Many
				
				break;
			case ONE_TO_MANY:
				$Cached_Forward_Property['Plural_Alias'] = &$Property['Plural_Alias'];
				
				$Cached_Reverse_Property['Reverse_Plural_Alias'] = &$Cached_Forward_Property['Plural_Alias'];
				
				// TODO: think about Default_Value for One-To-Many
				
				break;
			case MANY_TO_ONE:
				$Cached_Forward_Property['Reverse_Plural_Alias'] = &$Cached_Reverse_Property['Plural_Alias'];
				
				$Cached_Reverse_Property['Plural_Alias'] = &$Property['Reverse_Plural_Alias'];

				// TODO - etc.
				// TODO - this translation provided a little flexibility throughout the code in Create_Memory_Item / Save_Item
				if (array_key_exists('Default_Value', $Property) &&  !is_null($Property['Default_Value']))
				{
					$Cached_Forward_Property['Default_Value'] = &$Property['Default_Value'];
					$Cached_Reverse_Property['Default_Value'] = &$Property['Default_Value'];
				}				
				break;
		}

		// Create commutative Attachment cached properties		
		switch (strtolower($Property['Relation']))
		{
			case COMMUTATIVE:
			{
				// Add key to cached forward property
				$Cached_Property_Key = &$Property['Key'];
				$Cached_Forward_Property['Key'] = &$Cached_Property_Key;

				// Get attachment type
				$Property_Attachment_Type_Alias = &$Property['Attachment_Type'];
				$Cached_Property_Attachment_Type = &Get_Cached_Type($Database, strtolower($Property_Attachment_Type_Alias));
			
				// Add attachment type to cached forward property
				$Cached_Forward_Property['Cached_Attachment_Type'] = &$Cached_Property_Attachment_Type;
							
				// Created Cached Attachment Type Forward Property
				$Cached_Attachment_Type_Property = &New_Property();
				$Cached_Attachment_Type_Property['Name'] = &$Property['Name'];
				$Cached_Attachment_Type_Property['Alias'] = &$Property['Alias'];
				$Cached_Attachment_Type_Property['ID'] = &$Property['ID'];
				$Cached_Attachment_Type_Property['Cached_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Attachment_Type_Property['Cached_Value_Type'] = &$Cached_Property_Value_Type;
				$Cached_Attachment_Type_Property['Relation'] = MANY_TO_ONE;
				$Cached_Attachment_Type_Property['Data_Name'] = &$Property['Data_Name'];
				$Cached_Attachment_Type_Property['Key'] = &$Property['Key'];
				$Cached_Attachment_Type_Property['Attachment'] = true; // TODO: necessary?
			
				// Created Cached Attachment Type Reverse Property
				$Cached_Attachment_Type_Other_Property = &New_Property();
				$Cached_Attachment_Type_Other_Property['Name'] = &New_String('Other' . ' ' . $Property['Name']);
				$Cached_Attachment_Type_Other_Property['Alias'] = &New_String('Other' . '_' . $Property['Alias']);
				$Cached_Attachment_Type_Other_Property['ID'] = &$Property['ID'];
				$Cached_Attachment_Type_Other_Property['Cached_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Attachment_Type_Other_Property['Cached_Value_Type'] = &$Cached_Property_Value_Type;
				$Cached_Attachment_Type_Other_Property['Relation'] = MANY_TO_ONE;
				$Cached_Attachment_Type_Other_Property['Data_Name'] = &$Property['Reverse_Data_Name'];
				$Cached_Attachment_Type_Other_Property['Key'] = &$Property['Key'];
				$Cached_Attachment_Type_Other_Property['Attachment'] = true;
				
				// Create Cached Attachment Forward Property
				$Cached_Attachment_Property = &New_Property();
				$Cached_Attachment_Property['Name'] = &New_String($Property['Name'] . ' ' . 'Attachment');
				$Cached_Attachment_Property['Alias'] = &New_String($Property['Name'] . '_' . 'Attachment');
				$Cached_Attachment_Property['ID'] = &$Property['ID'];
				$Cached_Attachment_Property['Cached_Type'] = &$Cached_Property_Type;
				$Cached_Attachment_Property['Cached_Value_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Attachment_Property['Relation'] = ONE_TO_MANY;
				$Cached_Attachment_Property['Data_Name'] = &$Property['Reverse_Data_Name'];
				$Cached_Attachment_Property['Key'] = &$Property['Key'];
			
				// Create Cached Attachment Reverse Property
				$Cached_Attachment_Other_Property = &New_Property();
				$Cached_Attachment_Other_Property['Name'] = &New_String('Other' . ' ' . $Property['Name'] . ' ' . 'Attachment');
				$Cached_Attachment_Other_Property['Alias'] = &New_String('Other' . '_' . $Property['Name'] . '_' . 'Attachment');
				$Cached_Attachment_Other_Property['ID'] = &$Property['ID'];
				$Cached_Attachment_Other_Property['Cached_Type'] = &$Cached_Property_Value_Type;
				$Cached_Attachment_Other_Property['Cached_Value_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Attachment_Other_Property['Relation'] = ONE_TO_MANY;
				$Cached_Attachment_Other_Property['Data_Name'] = &$Property['Data_Name'];
				$Cached_Attachment_Other_Property['Key'] = &$Property['Key'];
				break;
			}
			case MANY_TO_MANY:
			{
				// Create Attachment Cached Properties...
				// Get attachment type
				$Property_Attachment_Type_Alias = &$Property['Attachment_Type'];
				$Cached_Property_Attachment_Type = &Get_Cached_Type($Database, strtolower($Property_Attachment_Type_Alias));
			
				// Add Attachment Type to Cached Forward and Reverse Properties
				$Cached_Forward_Property['Cached_Attachment_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Reverse_Property['Cached_Attachment_Type'] = &$Cached_Property_Attachment_Type;
			
				// Created Cached Attachment Forward Property
				$Cached_Attachment_Type_Forward_Property = &New_Property();
				$Cached_Attachment_Type_Forward_Property['Name'] = &$Property['Name'];
				$Cached_Attachment_Type_Forward_Property['Alias'] = &$Property['Alias'];
				$Cached_Attachment_Type_Forward_Property['ID'] = &$Property['ID'];
				$Cached_Attachment_Type_Forward_Property['Reverse_Alias'] = &$Property_Reverse_Alias;
				$Cached_Attachment_Type_Forward_Property['Reverse_Plural_Alias'] = &$Property_Reverse_Plural_Alias;
				$Cached_Attachment_Type_Forward_Property['Cached_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Attachment_Type_Forward_Property['Cached_Value_Type'] = &$Cached_Property_Value_Type;
				$Cached_Attachment_Type_Forward_Property['Relation'] = MANY_TO_ONE;
				$Cached_Attachment_Type_Forward_Property['Data_Name'] = &$Property['Data_Name'];
				$Cached_Attachment_Type_Forward_Property['Key'] = &$Property['Key'];
				$Cached_Attachment_Type_Forward_Property['Attachment'] = true; // TODO: necessary?
			
				// Created Cached Attachment Reverse Property
				$Cached_Attachment_Type_Reverse_Property = &New_Property();
				$Cached_Attachment_Type_Reverse_Property['Name'] = &$Property['Reverse_Name'];
				$Cached_Attachment_Type_Reverse_Property['Alias'] = &$Property_Reverse_Alias;
				$Cached_Attachment_Type_Reverse_Property['ID'] = &$Property['ID'];
				$Cached_Attachment_Type_Reverse_Property['Reverse_Alias'] = &$Property['Alias'];
				$Cached_Attachment_Type_Reverse_Property['Reverse_Plural_Alias'] = &$Property['Plural_Alias'];
				$Cached_Attachment_Type_Reverse_Property['Cached_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Attachment_Type_Reverse_Property['Cached_Value_Type'] = &$Cached_Property_Type;
				$Cached_Attachment_Type_Reverse_Property['Relation'] = MANY_TO_ONE;
				$Cached_Attachment_Type_Reverse_Property['Data_Name'] = &$Property['Reverse_Data_Name'];
				$Cached_Attachment_Type_Reverse_Property['Key'] = &$Property['Reverse_Key'];
				$Cached_Attachment_Type_Reverse_Property['Attachment'] = true; // TODO: necessary?
			
				// Store Attachment Type Forward and Reverse properties in forward and reverse properties
				$Cached_Forward_Property['Cached_Attachment_Type_Forward_Property'] = &$Cached_Attachment_Type_Forward_Property;
				$Cached_Forward_Property['Cached_Attachment_Type_Reverse_Property'] = &$Cached_Attachment_Type_Reverse_Property;
				$Cached_Reverse_Property['Cached_Attachment_Type_Forward_Property'] = &$Cached_Attachment_Type_Reverse_Property;
				$Cached_Reverse_Property['Cached_Attachment_Type_Reverse_Property'] = &$Cached_Attachment_Type_Forward_Property;			
			
				// Create Cached Attachment Forward Property
				$Cached_Attachment_Forward_Property = &New_Property();
				$Cached_Attachment_Forward_Property_Name = &New_String($Property['Name'] . ' Attachment');
				$Cached_Attachment_Forward_Property['Name'] = &$Cached_Attachment_Forward_Property_Name;
				$Cached_Attachment_Forward_Property_Alias = &New_String($Property['Alias'] . '_Attachment');
				$Cached_Attachment_Forward_Property['Alias'] = &$Cached_Attachment_Forward_Property_Alias;
				$Cached_Attachment_Forward_Property['ID'] = &$Property['ID'];
				$Cached_Attachment_Forward_Property['Reverse_Alias'] = &$Property_Reverse_Alias;
				$Cached_Attachment_Forward_Property['Cached_Type'] = &$Cached_Property_Type;
				$Cached_Attachment_Forward_Property['Cached_Value_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Attachment_Forward_Property['Relation'] = ONE_TO_MANY;
				// TODO - are these correct, below?
				$Cached_Attachment_Forward_Property['Data_Name'] = &$Property['Reverse_Data_Name'];
				$Cached_Attachment_Forward_Property['Key'] = &$Property['Reverse_Key'];
			
				// Create Cached Attachment Reverse Property
				$Cached_Attachment_Reverse_Property = &New_Property();
				$Cached_Attachment_Reverse_Property_Name = &New_String($Property['Reverse_Name'] . ' Attachment');
				$Cached_Attachment_Reverse_Property['Name'] = &$Cached_Attachment_Reverse_Property_Name;
				$Cached_Attachment_Reverse_Property_Alias = &New_String($Property_Reverse_Alias . '_Attachment');
				$Cached_Attachment_Reverse_Property['Alias'] = &$Cached_Attachment_Reverse_Property_Alias;
				$Cached_Attachment_Reverse_Property['ID'] = &$Property['ID'];
				$Cached_Attachment_Reverse_Property['Reverse_Alias'] = &$Property['Alias'];
				$Cached_Attachment_Reverse_Property['Cached_Type'] = &$Cached_Property_Value_Type;
				$Cached_Attachment_Reverse_Property['Cached_Value_Type'] = &$Cached_Property_Attachment_Type;
				$Cached_Attachment_Reverse_Property['Relation'] = ONE_TO_MANY;
				// TODO - are these correct, below?
				$Cached_Attachment_Reverse_Property['Data_Name'] = &$Property['Data_Name'];
				$Cached_Attachment_Reverse_Property['Key'] = &$Property['Key'];

				// Store cached attachment forward and reverse properties in cached type and value type
				// TODO - what?
				break;
			}
			
			default:
			{
				// Additional Properties for Non-Attachment Properties
			
				// Additional Forward Properties
				// TODO @feature-database: Resolve keys LATER since not all properties have been set up and therefore cannot be dereferenced
	// 				$Cached_Property_Key = &$Cached_Property_Value_Type['Cached_Property_Lookup'][strtolower($Property['Key'])];
				$Cached_Forward_Property['Data_Name'] = &$Property['Data_Name'];
				$Cached_Property_Key = &$Property['Key'];
				$Cached_Forward_Property['Key'] = &$Cached_Property_Key;
			
				// Additional Reverse Properties
	// 					$Cached_Reverse_Property_Key = &$Cached_Property_Type['Cached_Property_Lookup'][strtolower($Property['Reverse_Key'])];
				$Cached_Reverse_Property['Data_Name'] = &$Property['Data_Name'];
				$Cached_Reverse_Property_Key = &$Property['Key'];
				$Cached_Reverse_Property['Key'] = &$Cached_Reverse_Property_Key;
				
				break;
			}
		}
	}
	else
	{
		// Additional Properties for Simple Properties
		$Cached_Forward_Property['Data_Name'] = &$Property['Data_Name'];

		// TODO - etc.
		// TODO - this translation provided a little flexibility throughout the code in Create_Memory_Item / Save_Item
		if (array_key_exists('Default_Value', $Property) &&  !is_null($Property['Default_Value']))
			$Cached_Forward_Property['Default_Value'] = &$Property['Default_Value'];
	}
	
	// Cache type properties...
	// TODO: add by Plural Aliases
	{
		$Type_Properties_To_Store = &New_Array();

		$Type_Properties_To_Store[] = &$Cached_Forward_Property;

		if (isset($Cached_Attachment_Forward_Property))
			$Type_Properties_To_Store[] = &$Cached_Attachment_Forward_Property;

		if (isset($Cached_Attachment_Property))
			$Type_Properties_To_Store[] = &$Cached_Attachment_Property;

		if (isset($Cached_Attachment_Other_Property))
			$Type_Properties_To_Store[] = &$Cached_Attachment_Other_Property;
			
		foreach ($Type_Properties_To_Store as &$Type_Property_To_Store)
		{
			// Store in specific properties for property type 
			$Cached_Property_Type['Cached_Specific_Properties'][strtolower($Type_Property_To_Store['Alias'])] = &$Type_Property_To_Store;
			
			// Store in all properties, property lookups for property type and child types
			foreach ($Cached_Property_Type['All_Cached_Child_Types'] as &$Cached_Property_Type_Child_Type)
			{
				$Cached_Property_Type_Child_Type['All_Cached_Properties'][strtolower($Type_Property_To_Store['Alias'])] = &$Type_Property_To_Store;
				$Cached_Property_Type_Child_Type['Cached_Property_Lookup'][strtolower($Type_Property_To_Store['Alias'])] = &$Type_Property_To_Store;
			}
		}
	}

	// Cache value type properties...
	// TODO is_simple checking?
	{	
		$Value_Type_Properties_To_Store = &New_Array();		

		if (isset($Cached_Reverse_Property))
			$Value_Type_Properties_To_Store[] = &$Cached_Reverse_Property;

		if (isset($Cached_Attachment_Reverse_Property))
			$Value_Type_Properties_To_Store[] = &$Cached_Attachment_Reverse_Property;
			
		foreach ($Value_Type_Properties_To_Store as &$Value_Type_Property_To_Store)
		{
			// Store in specific properties for property type 
			$Cached_Property_Value_Type['Cached_Specific_Properties'][strtolower($Value_Type_Property_To_Store['Alias'])] = &$Value_Type_Property_To_Store;
			
			// Store in all properties, property lookups for property type and child types
			foreach ($Cached_Property_Value_Type['All_Cached_Child_Types'] as &$Cached_Property_Value_Type_Child_Type)
			{
				$Cached_Property_Value_Type_Child_Type['All_Cached_Properties'][strtolower($Value_Type_Property_To_Store['Alias'])] = &$Value_Type_Property_To_Store;
				$Cached_Property_Value_Type_Child_Type['Cached_Property_Lookup'][strtolower($Value_Type_Property_To_Store['Alias'])] = &$Value_Type_Property_To_Store;
			}
		}
	}
	
	// Cache attachment type properties...
	if (isset($Cached_Property_Attachment_Type))
	{
		$Attachment_Type_Properties_To_Store = &New_Array();

		if (isset($Cached_Attachment_Type_Forward_Property))
			$Attachment_Type_Properties_To_Store[] = &$Cached_Attachment_Type_Forward_Property;

		if (isset($Cached_Attachment_Type_Reverse_Property))
			$Attachment_Type_Properties_To_Store[] = &$Cached_Attachment_Type_Reverse_Property;
			
		if (isset($Cached_Attachment_Type_Property))
			$Attachment_Type_Properties_To_Store[] = &$Cached_Attachment_Type_Property;
			
		if (isset($Cached_Attachment_Type_Other_Property))
			$Attachment_Type_Properties_To_Store[] = &$Cached_Attachment_Type_Other_Property;
			
		foreach ($Attachment_Type_Properties_To_Store as &$Attachment_Type_Property_To_Store)
		{
			// Store in specific properties for property type 
			$Cached_Property_Attachment_Type['Cached_Specific_Properties'][strtolower($Attachment_Type_Property_To_Store['Alias'])] = &$Attachment_Type_Property_To_Store;
			
			// Store in all properties, property lookups for property type and child types
			foreach ($Cached_Property_Attachment_Type['All_Cached_Child_Types'] as &$Cached_Property_Attachment_Type_Child_Type)
			{	$Cached_Property_Attachment_Type_Child_Type['All_Cached_Properties'][strtolower($Attachment_Type_Property_To_Store['Alias'])] = &$Attachment_Type_Property_To_Store;
			$Cached_Property_Attachment_Type_Child_Type['Cached_Property_Lookup'][strtolower($Attachment_Type_Property_To_Store['Alias'])] = &$Attachment_Type_Property_To_Store;
			}
		}
	}
}

?>