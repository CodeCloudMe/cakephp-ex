<?php

// Add Value

function &Add_Value(&$Item, $Property_Lookup, &$Value)
{
	// Make sure property exists
	if (!Has_Property($Item, $Property_Lookup))
		throw new Exception('Tried to add value but no property exists.');
		
	// Make sure value is saved
	if (!$Item['Saved'])
		throw new Exception('Tried to add to an unsaved item.');
		
	// Make sure value is saved
	if (Is_Item($Value))
	{	
		if (!$Value['Saved'])
			throw new Exception('Tried to add an unsaved value.');
	}
	
	// Get property
	$Cached_Property = &Get_Property($Item, $Property_Lookup);
	
	// Add values depending on relation
	$Cached_Property_Relation = &$Cached_Property['Relation'];
	switch (strtolower($Cached_Property_Relation))
	{
		case COMMUTATIVE:	
			$Cached_Property_Attachment_Type = &$Cached_Property['Cached_Attachment_Type'];
			
			// Get attachment type property alias
			// TODO - this is a little <em>too</em> formal
			$Cached_Attachment_Type_Property_Lookup = &$Cached_Property['Alias'];
			$Cached_Attachment_Type_Property = &$Cached_Property_Attachment_Type['Cached_Property_Lookup'][strtolower($Cached_Attachment_Type_Property_Lookup)];
			$Cached_Attachment_Type_Property_Alias = $Cached_Attachment_Type_Property['Alias'];
			
			// Get attachment type other property alias
			$Cached_Attachment_Type_Other_Property_Lookup = &New_String('Other' . '_' . $Cached_Property['Alias']);
			$Cached_Attachment_Type_Other_Property = &$Cached_Property_Attachment_Type['Cached_Property_Lookup'][strtolower($Cached_Attachment_Type_Other_Property_Lookup)];
			$Cached_Attachment_Type_Other_Property_Alias = $Cached_Attachment_Type_Other_Property['Alias'];
			
			// Get package from item	
			$Package_Command_String = &New_String('Package');
			$Package_Command = &Parse_String_Into_Command($Package_Command_String);
			$Package_Item = &Get_Value($Item, $Package_Command);
			$Cached_Property_Type_Package_Alias = $Package_Item['Data']['Alias'];

			// Create attachment and set forward/reverse values
			$Database = &$Item['Database'];
			$Attachment_Item = &Create_Memory_Item($Database, $Cached_Property_Attachment_Type);
			Set_Value($Attachment_Item, $Cached_Attachment_Type_Property_Alias, $Value);
			Set_Value($Attachment_Item, $Cached_Attachment_Type_Other_Property_Alias, $Item);
			Set_Value($Attachment_Item, 'Package', $Cached_Property_Type_Package_Alias);
			
			// Save attachment
			Save_Item($Attachment_Item);
			
			return $Attachment_Item;
			
			break;
		case MANY_TO_MANY:
			$Cached_Property_Attachment_Type = &$Cached_Property['Cached_Attachment_Type'];
			$Cached_Property_Alias = &$Cached_Property['Alias'];
			$Cached_Property_Reverse_Alias = &$Cached_Property['Reverse_Alias'];
			
			// Get package from item	
			$Package_Command_String = &New_String('Package');
			$Package_Command = &Parse_String_Into_Command($Package_Command_String);
			$Package_Item = &Get_Value($Item, $Package_Command);
			$Cached_Property_Type_Package_Alias = $Package_Item['Data']['Alias'];

			// Create attachment and set forward/reverse values
			$Database = &$Item['Database'];
			$Attachment_Item = &Create_Memory_Item($Database, $Cached_Property_Attachment_Type);
			Set_Value($Attachment_Item, $Cached_Property_Alias, $Value);
			Set_Value($Attachment_Item, $Cached_Property_Reverse_Alias, $Item);
			Set_Value($Attachment_Item, 'Package', $Cached_Property_Type_Package_Alias);
			
			// Save attachment
			Save_Item($Attachment_Item);
			
			return $Attachment_Item;
			
			break;
			
		case ONE_TO_MANY:
			// Get property's reverse alias
			$Cached_Property_Reverse_Alias = &$Cached_Property['Reverse_Alias'];
			
			// Set value's one-to-many reverse property to item.
			Set_Value($Value, $Cached_Property_Reverse_Alias, $Item);
			
			// Save value.
			Save_Item($Value);
			
			return $Value;			
			break;
		
		default:
			throw new Exception('Tried to add value to non-multiple property.');
			break;
	}
}

?>