<?php

// Set Value

// TODO @feature-language: instead of a Value, maybe it should take in a command or clause (i.e. Page.Name, 13, 'hej') so it gets more meta-data (like the expected type)

function Set_Value(&$Item, $Property_Lookup, &$Value)
{		
	// Value verification
	if (is_array($Value) && ! (Is_Item($Value) || Is_Not_Set($Value)))
		throw new Exception ('Malformed Value in Set for Property_Lookup:' . ' ' . $Property_Lookup);
		
	// Get database
	$Database = &$Item['Database'];
	
	// Get value's type information...
	if (Is_Item($Value))
	{
		$Value_Type = &$Value['Cached_Specific_Type'];
		$Value_Type_Alias = &$Value_Type['Alias'];
	}
	
	// If value is not an array, it's a simple value (or a key to a complex value)
	else
	{
		// TODO @feature-language: simple types could be IDs or Aliases of complex types, so maybe pass in a Value_Type like we used to? Or figure out another way to do this
		
		// Default to text...
		
		// TODO @feature-language: use PHP's type getting to see if a, etc. (but not is_numeric since that might truncate leading 0s etc)
		// Set the new property's value type to text
		$Value_Type_Alias = &New_String('Text');
		$Value_Type = &Get_Cached_Type($Database, $Value_Type_Alias);
	}
		
	// Check if property already exists
	if (Has_Property($Item, $Property_Lookup))
	{	
		// Get the property
		$Cached_Property = &Get_Property($Item, $Property_Lookup);
		
		// Get the property alias & data name
		$Cached_Property_Alias = &$Cached_Property['Alias'];
		$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];
			
		// If this is a property of the type, then get the value type from the property.
		if (array_key_exists(strtolower($Cached_Property_Alias), $Item['Cached_Specific_Type']['Cached_Property_Lookup']))
		{
			$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
			$Cached_Property_Value_Type_Alias = &$Cached_Property_Value_Type['Alias'];
		}
	
		// If this an inline item property, set the value type from the item, and update the property's value type.
		else
		{
			$Cached_Property_Value_Type = &$Value_Type;
			$Cached_Property_Value_Type_Alias = &$Value_Type_Alias;
			// Update the value type of the cached property to the value's type
			$Cached_Property['Cached_Value_Type'] = &$Cached_Property_Value_Type;			
		}
	}

	// If property does not already exist, create a new item-specific property...
	else
	{	
		// Set new property name to the property lookup
		$Cached_Property_Name = &$Property_Lookup;
		
		// Generate property alias and use as the data name
		$Cached_Property_Alias = &Alias_From_Name($Cached_Property_Name);
		$Cached_Property_Data_Name = &$Cached_Property_Alias;
		
		// Set up new property
		$Cached_Property = &New_Array();
		$Cached_Property['Name'] = &$Cached_Property_Name;
		$Cached_Property['Alias'] = &$Cached_Property_Alias;
		$Cached_Property['Data_Name'] = &$Cached_Property_Data_Name;
		$Cached_Property['Cached_Type'] = &$Item['Cached_Base_Type'];
		
		// Mark that property is temporary
		// TODO @feature-database: Do we actually need to store this in the property?
		// TODO @feature-database: or a different helper array
		$Cached_Property['Specific'] = &New_Boolean(true);
		
		// Set value type to value's type
		$Cached_Property_Value_Type_Alias = &$Value_Type_Alias;
		$Cached_Property_Value_Type = &$Value_Type;
		$Cached_Property['Cached_Value_Type'] = &$Cached_Property_Value_Type;
		
		// Set relation to many-to-one for complex value types, blank for simple value types.
		if (!Is_Simple_Type($Value_Type))
			$Cached_Property_Relation = &New_String('Many-To-One');
		else
			$Cached_Property_Relation = &New_String('');
		$Cached_Property['Relation'] = &$Cached_Property_Relation;
		
		// Save new property in item-specific properties and the property lookup
		$Item['Cached_Specific_Properties'][strtolower($Cached_Property_Alias)] = &$Cached_Property;
		$Item['Cached_Specific_Property_Lookup'][strtolower($Cached_Property_Alias)] = &$Cached_Property;
	}
	
	// Standardize values...
	
	// TODO - maybe need better matching here.
	// TODO - Not_Set for date values here is incompatible with Save_Item behavior, which will then throw an exception.
	// If value is not set or null, store it as null or not set depending on value type
	if (Is_Not_Set($Value) || is_null($Value))
	{
		global $Simple_Types;
		global $Date_Types;
		if (in_array(strtolower($Cached_Property_Value_Type_Alias), $Simple_Types) && !in_array(strtolower($Cached_Property_Value_Type_Alias), $Date_Types))
			$Standardized_Value = &New_Null();
		else
			$Standardized_Value = &New_Not_Set();
	}
	
	// If value is an item, store pointer to item.
	else if (Is_Item($Value))
	{
		// TODO - If property value type is simple, then throw error.
		$Standardized_Value = &$Value;	
	}
		
	// If value is a simple value, transform to standard values for the property's value type
	else
	{
		switch (strtolower($Cached_Property_Value_Type_Alias))
		{
			case 'simp	le_item':
				throw new Exception ('whats all this then');
				break;

			// Standardize value for a boolean property to boolean.
			case "boolean":
				if (!is_bool($Value))
				{
					switch (strtolower($Value))
					{
						case 'true':
						case '1':
							$Standardized_Value = &New_Boolean(true);
							break;
						case '':
						case 'false':
						case '0':
							$Standardized_Value = &New_Boolean(false);
							break;
						default:
							// TODO - decide Set_Value's type mismatch behavior
							throw new Exception ('property value type type/ value type mis-match for boolean');
							break;
					}
				}
				else
					$Standardized_Value = &$Value;
				break;

			// Standardize value for a date/time property to a memory item.
			case "date":
			case "date_time":
			case "time":
				// TODO - this is significantly memory expensive, and we might want to revert to scalar standardization.
//				$Standardized_Value = &$Value;
				$Standardized_Value = &Create_Item($Database, $Cached_Property_Value_Type);
				Set_Value($Standardized_Value, 'Simple_Value', $Value);
				break;
				
			default:
				// TODO - maybe in_array this just to be consistent with above.
				if (!Is_Simple_Type($Cached_Property_Value_Type))
				{
					// If the database is available, get the item from the database 
					// TODO - this is somewhat insane, but only maybe.
					if ($Database['Ready'])
					{
						$Cached_Property_Key = &$Cached_Property['Key'];
						$Cached_Key_Property = &$Cached_Property_Value_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Key)];
						$Cached_Key_Property_Alias = &$Cached_Key_Property['Alias'];
									
						$Complex_Value_Command_String =
							$Cached_Property_Value_Type_Alias .
								' ' . 'from' . ' ' . 
							'Database' . 
								' ' . 'where' . ' ' .
							'(' . 
								$Cached_Key_Property_Alias . 
									' ' . '=' . ' ' . 
								('"' . $Value . '"') . 
							')' . 
								' ' . 'as' . ' ' .
							'Reference';

						$Complex_Value_Processed_Command = &Process_Command_String($Database, $Complex_Value_Command_String);
						$Complex_Value_Item  = &$Complex_Value_Processed_Command['Chunks'][0]['Item'];
						$Standardized_Value = &$Complex_Value_Item;
					}

					// If the database is not available, keep the simple value.
					else
					{
						$Standardized_Value = &$Value;
					}
				}
				else
				{
					// If this is a simple value for a memory item, standardize the format.
					if (strtolower($Property_Lookup) == 'simple_value')
					{
						$Cached_Item_Type = &$Item['Cached_Specific_Type'];
						$Cached_Item_Type_Alias = $Cached_Item_Type['Alias'];
						switch ($Cached_Item_Type_Alias)
						{
							case "date":
							case "date_time":
							case "time":
								// Convert strings to time, or null.
								if (!is_numeric($Value))
								{
									if (strtotime($Value) !== false)
										$Standardized_Value = &New_Number(strtotime($Value));
					
									// TODO - decide Set_Value's type mismatch behavior
									else
										throw new Exception ('property value type type/ value type mis-match for '. $Cached_Item_Type_Alias);
								}
								else
									$Standardized_Value = &$Value;
								break;
								
							default:
								$Standardized_Value = &$Value;
								break;
						}						
					}


					// Geocode Lat / Longitude on changed addresses
					// TODO - temporary hack for a demo
					// TODO - abstract this behavior to change to "location" simple value type
					// TODO - in which case we need another level of abstraction for "refreshing" items...		
					
					/*				
					if (strtolower($Property_Lookup) == 'address')
					{
						$Cached_Item_Type = &$Item['Cached_Specific_Type'];
						$Cached_Item_Type_Alias = $Cached_Item_Type['Alias'];
						if (strtolower($Cached_Item_Type_Alias) == 'map_marker')
						{						
							$Geocoded_Address = &Geocode($Value);
							if ($Geocoded_Address === false)
							{
								// TODO - handle error if applicable.
							}
							else
							{
								Set_Value($Item, 'Latitude', $Geocoded_Address['lat']);
								Set_Value($Item, 'Longitude', $Geocoded_Address['lng']);
							}
						}
					}
					*/
					
					// TODO - this can't belong here... does it?					
					$Standardized_Value = &$Value;

				}
		}
	}
		
	// If this is an item, prevent it from iterating in this context.
	if (Is_Item($Standardized_Value))
		$Standardized_Value['Item_Was_Set_As_A_Value'] = &New_Boolean(true); 

		
	// For items from the database, store the previous value
	if (array_key_exists('Saved', $Item) && $Item['Saved'])
	{
		// Only remember simple and single properties
		if (Is_Simple_Type($Cached_Property_Value_Type) || (strtolower($Cached_Property['Relation']) == MANY_TO_ONE))
		{
			// Only remember the original value if it hasn't already changed before
			if (!array_key_exists('Original_Values', $Item) || (array_key_exists('Original_Values', $Item) && !array_key_exists($Cached_Property_Data_Name, $Item['Original_Values'])))
			{	
				// Check if new value is actually different from existing value
				// TODO @feature-database: for complex values, use what you got (i.e. if new item is array, compare its key, or if keys are changing, dereference items, etc)
				if (
					!array_key_exists($Cached_Property_Data_Name, $Item['Data'])
					||
						(
							// TODO : figure out correct comparison operation -  e.g. changing from false to null, and references to the same value
							array_key_exists($Cached_Property_Data_Name, $Item['Data']) && $Standardized_Value != $Item['Data'][$Cached_Property_Data_Name])
						)
				{
					// Remember original value
					$Item['Original_Values'][$Cached_Property_Data_Name] = &$Item['Data'][$Cached_Property_Data_Name];
				}
			}
		}
	}
	
	$Item['Data'][$Cached_Property_Data_Name] = &$Standardized_Value;	

}


/*				
	// TODO - Don't know what this is, was upstairs
	switch (strtolower($Cached_Property_Value_Type_Alias))
	{
		// For a non_item non_null simple value set to a property with an item value type, change the value type according to the item.
		case 'simple_item':
			// Update the value type of the cached property to the value's type
			$Cached_Property_Value_Type_Alias = &$Value_Type_Alias;
			$Cached_Property_Value_Type = &$Value_Type;
			$Cached_Property['Cached_Value_Type'] = &$Cached_Property_Value_Type;
			break;
	}
*/

?>