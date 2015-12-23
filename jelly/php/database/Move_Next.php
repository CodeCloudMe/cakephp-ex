<?php

// Move Next

function Move_Next(&$Item)
{
	// Localize variables
	$Database = &$Item['Database'];
	
	// Make sure item is not already at its end of results
	if ($Item['End_Of_Results'])
		throw new exception('Tried to move next in an item already at the end of results.');
	
	// Increment item index
	$Item['Index']++;
	
	// Reset original values (get rid of unsaved changes)
	unset($Item['Original_Values']);
	
	// Load next database row
	if ($Database_Row = mysqli_fetch_assoc($Item['Result']))
	{
		// Store database row in item
		$Item['Data'] = &$Database_Row;
		
		// Mark item as saved
		$Item['Saved'] = &New_Boolean(true);
		
		// Set Cached Specific Type
		$Cached_Item_Specific_Type = &Get_Cached_Type($Database, $Item['Data']['Specific_Type']);
		$Item['Cached_Specific_Type'] = &$Cached_Item_Specific_Type;
	
		// Convert SQL values to PHP values
		foreach ($Cached_Item_Specific_Type['All_Cached_Properties'] as &$Cached_Property)
		{
			// Only convert properties that are set and are not null (because null is a valid state)
			$Property_Data_Name = &$Cached_Property['Data_Name'];
			if (isset($Item['Data'][$Property_Data_Name]) && !is_null($Item['Data'][$Property_Data_Name]))
			{
				// Handle value types differently
				// TODO @feature-database: do this in the query instead?
				$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
				$Property_Value_Type_Alias = &$Cached_Property_Value_Type['Alias'];

				switch (strtolower($Property_Value_Type_Alias))
				{
					// Date/Time
					case 'date':
					case 'time':
					case 'date_time':
						$Item['Data'][$Property_Data_Name] = strtotime($Item['Data'][$Property_Data_Name]);
						break;
				
					// Boolean
					case 'boolean':
						$Item['Data'][$Property_Data_Name] = (bool) $Item['Data'][$Property_Data_Name];
						break;
				}
			}
		}
	}
	
	// Otherwise, no result was returned
	else
	{
		// Clear item's data
		unset($Item['Data']);
		
		// Set end-of-file to true
		$Item['End_Of_Results'] = &New_Boolean(true);
		
		// Mark item as not saved
		$Item['Saved'] = &New_Boolean(false);
		
		// Use item's base type for its specific type (for valid handling of empty results, etc)
		$Cached_Item_Base_Type = &$Item['Cached_Base_Type'];
		$Item['Cached_Specific_Type'] = &$Cached_Item_Base_Type;
		
		// TODO - not sure if necessary -- but the manual said these are freed "end of script" rather than at object destruction. so they're in here in case.
		mysqli_free_result($Item['Result']);
	}
}

?>