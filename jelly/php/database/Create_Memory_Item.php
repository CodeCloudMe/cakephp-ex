<?php

// Create Memory Item

// Creates a new item, and optionally saves it to database.

// TODO @core_language pass in Command?
// TODO array memory items

function &Create_Memory_Item(&$Database, &$Cached_Type)// , &$Command)
{
	// Create item
	$Item = &Create_Item($Database, $Cached_Type);
	
	// Setup memory Item
// 	$Item['Item_Source'] = 'Memory';
// 	$Item['Original Command'] = &$Command; // TODO: or copy?
	
	// Set specific type to cached type for memory items
	$Item['Cached_Specific_Type'] = &$Cached_Type;
	
	// Setup Data
	$Item['Data'] = array();
	
	// Mark that item has not been saved
	$Item['Saved'] = false;

	// Populate data with default values for each property
	foreach ($Cached_Type['All_Cached_Properties'] as &$Cached_Property)
	{	
		$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
		$Cached_Property_Value_Type_Alias = &$Cached_Property_Value_Type['Alias'];
		$Cached_Property_Relation = &$Cached_Property['Relation'];
		
		if (Is_Simple_Type($Cached_Property_Value_Type) || strtolower($Cached_Property_Relation) == MANY_TO_ONE)
		{	
			$Cached_Property_Alias = &$Cached_Property['Alias'];
					 
			if (array_key_exists('Default_Value', $Cached_Property))
			{
				// Set default value explicitly, if supplied
				$Cached_Property_Default_Value = &$Cached_Property['Default_Value'];
				Set_Value($Item, $Cached_Property_Alias, $Cached_Property_Default_Value);
			}

			// If property does not have a default value, special case some value types with automatic values
			else
			{
				// Set automatic values for some value types, if no default value is supplied
				switch (strtolower($Cached_Property_Value_Type_Alias))
				{
					case 'date':
					case 'date_time':
					case 'time':
						// Set date/time property value to current time
						Set_Simple($Item, $Cached_Property_Alias, time());
						break;
				}	
			}
		}		
	}
	
	// Populate default simple value for simple types
	if (Is_Simple_Type($Cached_Type))
	{
		$Cached_Type_Alias = &$Cached_Type['Alias'];
		switch (strtolower($Cached_Type_Alias))
		{
			case 'date':
			case 'date_time':
			case 'time':
				// Set time value to current time
				Set_Simple($Item, 'Simple_Value', time());
				break;
			default:
				break;
		}
	}
	
	// Populate data with override values
	// TODO @feature-language Specific Values hasn't been implemented yet in Createe_Item, so no reason to use it below until it has
// 	foreach ($Item['Override_Values'] as $Override_Property_Alias => &$Override_Value)
// 	{
// 		// TODO: should we be doing this with Set_Value?
// 		Set_Value($Item, $Override_Property_Alias, $Override_Value);
// 	}
	
	// TODO: Any special type handling stuff (e.g. , additional values for date/time/datetime)
	
	// Set status	
	$Item['End_Of_Results'] = false;
	$Item['Index'] = 0;
	
	// Return new item as reference
	return $Item;
}

?>