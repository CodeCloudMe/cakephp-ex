<?php

// Delete Item
function Delete_Item(&$Item)
{
	// Error-Check: Make sure item is saved
	if (!$Item['Saved'])
		throw new Exception('Cannot delete un-saved items.');
	
	// Error-Check: Make sure item is not at the end of its results
	if ($Item['End_Of_Results'])
		throw new Exception('Cannot delete end-of-results items.');
	
	// Get item's database and prefix
	$Database = &$Item['Database'];
	$Table_Prefix = &$Database['Table_Prefix'];
	
	// Get item's ID
	$Item_ID = &$Item['Data']['ID'];
	
	// Get item's type
	$Item_Cached_Type = &$Item['Cached_Specific_Type'];
	$Item_Cached_Type_Data_Name = &$Item_Cached_Type['Data_Name'];
	$Item_Cached_Type_Table = &New_String($Table_Prefix . $Item_Cached_Type_Data_Name);
	
	// Get item's order
	$Item_Order = &$Item['Data']['Order'];
	
	// Delete item from database
	$Delete_Item_Query = &New_String(
		'DELETE FROM' . ' ' . ('`' . mysqli_real_escape_string($Database['Link'], $Item_Cached_Type_Table) . '`') . ' ' .
		'WHERE' . ' ' . ('`ID`' . ' ' . '=' . ' ' . ('\'' . mysqli_real_escape_string($Database['Link'], $Item_ID) . '\'')));
	Query($Database, $Delete_Item_Query);
	
	// Update order for subsequent items
	$Update_Order_Query = &New_String(
		'UPDATE' . ' ' . ('`' . mysqli_real_escape_string($Database['Link'], $Item_Cached_Type_Table) . '`') . ' ' .
		'SET' . ' ' . ('`Order` = `Order` - 1') . ' ' .
		'WHERE' . ' ' . ('`Order`' . ' ' . '>' . ' ' . mysqli_real_escape_string($Database['Link'], $Item_Order)));
	Query($Database, $Update_Order_Query);
	
	// Check if item type requires follow-up actions
	$Item_Cached_Type_Alias = &$Item_Cached_Type['Alias'];
	switch (strtolower($Item_Cached_Type_Alias))
	{
		// Deleting Type
		case 'type':
		{
			$Type = &$Item['Data'];
			$Type_Alias = &$Type['Alias'];
			$Cached_Type = &Get_Cached_Type($Database, $Type_Alias);
			$Cached_Type_Alias = &$Cached_Type['Alias'];
			
			// Delete type's specific Properties until there are none left
			while (count($Cached_Type['Cached_Specific_Properties']))
			{
				$Cached_Type_Specific_Property = &$Cached_Type['Cached_Specific_Properties'][array_keys($Cached_Type['Cached_Specific_Properties'])[0]];
				
				// TODO @feature-database: maybe get properties with a better Get_Item() query
				
				// Get property item from database
				$Cached_Type_Specific_Property_ID = &$Cached_Type_Specific_Property['ID'];
				$Property_Command_String = &New_String('1 Property Where ID = ' . $Cached_Type_Specific_Property_ID);
				$Property_Command = &Parse_String_Into_Command($Property_Command_String);
				$Property_Item = &Get_Database_Item($Database, $Property_Command);
				
				// Delete property item
				Delete_Item($Property_Item);
			}
			
			// Delete type's Child Types until there are none left
			while (count($Cached_Type['Cached_Specific_Child_Types']))
			{
				$Cached_Specific_Child_Type = &$Cached_Type['Cached_Specific_Child_Types'][array_keys($Cached_Type['Cached_Specific_Child_Types'])[0]];
				
				// TODO @feature-database: maybe get child types with a better Get_Item() query
				
				// Get child type from database
				$Cached_Specific_Child_Type_ID = &$Cached_Specific_Child_Type['ID'];
				$Child_Type_Command_String = &New_String('1 Type Where ID = ' . $Cached_Specific_Child_Type_ID);
				$Child_Type_Command = &Parse_String_Into_Command($Child_Type_Command_String);
				// TODO - lol spelling errors
				$Chlid_Type_Item = &Get_Database_Item($Database, $Child_Type_Command);
				
				// Delete child type item
				Delete_Item($Chlid_Type_Item);
			}
			
			// Drop database table
			$Cached_Type_Data_Name = &$Cached_Type['Data_Name'];
			$Cached_Type_Table = &New_String($Table_Prefix . $Cached_Type_Data_Name);
			$Delete_Table_Query = &New_String(
				'DROP TABLE' . ' ' .
				('`' . mysqli_real_escape_string($Database['Link'], $Cached_Type_Table) . '`'));
			Query($Database, $Delete_Table_Query);
			
			// Remove Type From Cache
			Remove_Type_From_Cache($Database, $Cached_Type);
			
			break;
		}
		
		// Deleting Property
		case 'property':
		{
			$Property = &$Item['Data'];
			
			// Get Cached Property Type
			$Property_Type_Alias = &$Property['Type'];
			$Cached_Property_Type = &Get_Cached_Type($Database, $Property_Type_Alias);
			
			// Get Cached Property
			$Property_Alias = &$Property['Alias'];
			$Cached_Property = &$Cached_Property_Type['Cached_Property_Lookup'][strtolower($Property_Alias)];
			$Cached_Property_Alias = &$Cached_Property['Alias'];
			
			// Get Property Value Type
			$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
			
			// Get Relation for Complex Properties
			if (!Is_Simple_Type($Cached_Property_Value_Type))
				$Cached_Property_Relation = &$Cached_Property['Relation'];
			
			// Remove Property From Cache
			Remove_Property_From_Cache($Database, $Cached_Property);
			
			// Check if property is simple or complex/many-to-one
			$Delete_Target = &New_Array();
			$Delete_Target['Columns'] = &New_Array();
			if (Is_Simple_Type($Cached_Property_Value_Type) || strtolower($Cached_Property_Relation) == MANY_TO_ONE)
			{
				// Set delete target to property's type
				$Delete_Target['Cached_Type'] = &$Cached_Property_Type;
				
				// Make sure table always has one column remaining
				if ($Property['Alias'] != 'ID')
				{
					// Add property to delete columns
					$Delete_Target['Columns'][0] = &New_Array();
					$Delete_Target['Columns'][0]['Cached_Property'] = &$Cached_Property;
				}
			}
			// Otherwise, check if property is one-to-many
			elseif (strtolower($Cached_Property_Relation) == ONE_TO_MANY)
			{
				// Set delete target to property's value type
				$Delete_Target['Cached_Type'] = &$Cached_Property_Value_Type;
				
				// Add property to delete columns
				$Delete_Target['Columns'][0] = &New_Array();
				$Delete_Target['Columns'][0]['Cached_Property'] = &$Cached_Property;
			}
			// Otherwise, check if property is many-to-many
			elseif (strtolower($Cached_Property_Relation) == MANY_TO_MANY)
			{
				// Set delete target to property's attachment type
				$Cached_Property_Attachment_Type = &$Cached_Property['Cached_Attachment_Type'];
				$Delete_Target['Cached_Type'] = &$Cached_Property_Attachment_Type;
				
				// Add attachment type's forward property to delete columns
				$Cached_Attachment_Type_Forward_Property = &$Cached_Property['Cached_Attachment_Type_Forward_Property'];
				$Delete_Target['Columns'][0] = &New_Array();
				$Delete_Target['Columns'][0]['Cached_Property'] = &$Cached_Attachment_Type_Forward_Property;
				
				// Add attachment type's reverse property to delete columns
				$Cached_Attachment_Type_Reverse_Property = &$Cached_Property['Cached_Attachment_Type_Reverse_Property'];
				$Delete_Target['Columns'][1] = &New_Array();
				$Delete_Target['Columns'][1]['Cached_Property'] = &$Cached_Attachment_Type_Reverse_Property;
			}
			
			// Delete columns from database
			$Delete_Target_Cached_Type = &$Delete_Target['Cached_Type'];
			foreach ($Delete_Target_Cached_Type['All_Cached_Child_Types'] as &$Delete_Target_Cached_Type_Child_Type)
			{
				$Delete_Target_Cached_Type_Child_Type_Data_Name = &$Delete_Target_Cached_Type_Child_Type['Data_Name'];
				$Delete_Target_Cached_Type_Child_Type_Table_Name = &New_String($Table_Prefix . $Delete_Target_Cached_Type_Child_Type_Data_Name);
				foreach ($Delete_Target['Columns'] as &$Delete_Target_Column)
				{
					// Get property's data name
					$Delete_Target_Column_Cached_Property = &$Delete_Target_Column['Cached_Property'];
					$Delete_Target_Column_Cached_Property_Data_Name = &$Delete_Target_Column_Cached_Property['Data_Name'];
					$Delete_Target_Column_Cached_Property_Column_Name = &$Delete_Target_Column_Cached_Property_Data_Name;
				
					// Generate drop column query
					$Drop_Column_Query = &New_String(
						'ALTER TABLE' . ' ' . ('`' . mysqli_real_escape_string($Database['Link'], $Delete_Target_Cached_Type_Child_Type_Table_Name) . '`') . ' ' .
						'DROP COLUMN' . ' ' . ('`' . mysqli_real_escape_string($Database['Link'], $Delete_Target_Column_Cached_Property_Column_Name) . '`'));
				
					// Execute drop column query
					Query($Database, $Drop_Column_Query);
				}
			}
			
			// Delete Attachment Type for Many-To-Many properties
			if (!Is_Simple_Type($Cached_Property_Value_Type) && strtolower($Cached_Property_Relation) == MANY_TO_MANY)
			{
// 				case COMMUTATIVE: TODO
				// Get attachment type item
				$Cached_Property_Attachment_Type_ID = &$Cached_Property_Attachment_Type['ID'];
				$Attachment_Type_Command_String = &New_String('1 Type Where ID = ' . $Cached_Property_Attachment_Type_ID);
				$Attachment_Type_Command = &Parse_String_Into_Command($Attachment_Type_Command_String);
				$Attachment_Type_Item = &Get_Database_Item($Database, $Attachment_Type_Command);
				
				// Delete attachment type item
				Delete_Item($Attachment_Type_Item);
			}
			
			break;
		}
	}
	
	// Nullify References (i.e. when deleting a Page, nullify all values of other items that pointed to that Page, either by MySQL query or saving items)
	// TODO @feature-database
}

?>