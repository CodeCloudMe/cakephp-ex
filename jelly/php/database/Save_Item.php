<?php

// Save Item
// TODO: use order/limit when truncating multiple values to single
// TODO: think about is_set, array_key_exists, null in most of these checks  --- most of the time it's the intended behavior, but the isset code still is ambigous feeling.

function Save_Item(&$Item, $Parameters = array())
{	
	// Verify Item
	if (!$Item)
		throw new Exception('Save_Item: Item required.');
	
	// Localize variables
	$Database = &$Item['Database'];
	$Table_Prefix = &$Database['Table_Prefix'];
	global $Types_To_SQL_Types;
	global $Simple_Types;
	
	// Load item's type
	$Cached_Item_Type = &$Item['Cached_Specific_Type'];
	$Cached_Item_Type_Alias = &$Cached_Item_Type['Alias'];
	
	// Load item values
	$Item_Values = &$Item['Data'];

	// Load logic values
	$Is_Changed = &New_Boolean(array_key_exists('Original_Values', $Item));
	$Is_New_Item = &New_Boolean(!$Item['Saved']);
	
	// TODO - might have to standardize values here, but hopefully not. hm...
	// TODO - this may overlap with the below verification stage.
	
	// Auto-set properties for all items
	{
		// TODO - this should be deletable (Package has a default value, and Reset handles it otherwise)
		/* 
			Autoset 'Package'
		if (!array_key_exists('Package', $Item_Values) || is_null($Item_Values['Package']))
		{					
			// Get package of item type.
			if (array_key_exists('Current_Package', $GLOBALS))
				$Item_Package = &$GLOBALS['Current_Package'];
			else
				$Item_Package = &New_String('Local');
			Set_Value($Item, 'Package', $Item_Package);
		}
		*/
		
		// Autoset 'Status'
		if (!array_key_exists('Status', $Item_Values) || is_null($Item_Values['Status']))
		{	
			$Item_Status = &New_String('Published');
			Set_Value($Item, 'Status', $Item_Status);
		}
		
		// Autoset 'Alias', if alias is not hard set.
		if (!array_key_exists('Alias', $Item_Values) || ($Is_Changed && !array_key_exists('Alias', $Item['Original_Values'])))
		{
			if (array_key_exists('Name', $Item_Values) && !is_null($Item_Values['Name']))
			{
				$Item_Name = &$Item_Values['Name'];
				$Item_Alias = &Jelly_Format($Item_Name, 'Alias');
				Set_Value($Item, 'Alias', $Item_Alias);
			}
		}
	}
	
	// Auto-set properties for specific types...
	switch (strtolower($Cached_Item_Type_Alias))
	{
		case 'type':
		{	
			// Autoset 'Plural Alias', if Plural Alias is not hard set
			if (!array_key_exists('Plural_Alias', $Item_Values) || ($Is_Changed && !array_key_exists('Plural_Alias', $Item['Original_Values'])))
			{
				if (array_key_exists('Plural_Name', $Item_Values) && !is_null($Item_Values['Plural_Name']))
				{
					$Item_Plural_Name = &$Item_Values['Plural_Name'];
					$Item_Plural_Alias = &Jelly_Format($Item_Plural_Name, 'Alias');
					Set_Value($Item, 'Plural_Alias', $Item_Plural_Alias);
				}
			}
						
			// Autoset 'Data Name', if Data Name is not hard set.
			if (!array_key_exists('Data_Name', $Item_Values) || ($Is_Changed && !array_key_exists('Data_Name', $Item['Original_Values'])))
			{
				if (array_key_exists('Alias', $Item_Values) && !is_null($Item_Values['Alias']))
				{
					$Item_Alias =  &$Item_Values['Alias'];
					$Item_Data_Name = &$Item_Alias;
					Set_Value($Item, 'Data_Name', $Item_Data_Name);
				}
			}
						
			// Auto-set properties for complex types other than item and basic item
			$Type_Alias = &$Item_Values['Alias'];
			if (!in_array(strtolower($Type_Alias), $Simple_Types) && !in_array(strtolower($Type_Alias), array('item', 'simple_item')))
			{
				// Autoset 'Default Key'
				if (!array_key_exists('Default_Key', $Item_Values) || is_null($Item_Values['Default_Key']))
					Set_Simple($Item, 'Default_Key', 'ID');
					
				// Autoset 'Parent Type'
				if (!array_key_exists('Parent_Type', $Item_Values) || Is_Not_Set($Item_Values['Parent_Type']))
					$Item['Data']['Parent_Type'] = 'Item';
// 					Set_Simple($Item, 'Parent_Type', 'Item');
			}
			break;
		}

		case 'property':
		{	
			// TODO auto-set Type/Value_Type?
			
			// TODO - Reset databases passes in non-item values sometimes, but maybe there's a better solution than this.

			// Get cached type. 
			$Property_Type_Alias = &As_Key($Item_Values['Type']);
			
			// TODO - this is for debugging only
			try
			{
				$Cached_Property_Type = &Get_Cached_Type($Database, $Property_Type_Alias);
			}
			catch (Exception $Exception)
			{
				traverse($Item);
				throw $Exception;
			}
			
			// Get cached value type.
			if (array_key_exists('Relation', $Item_Values) && (strtolower($Item_Values['Relation']) == COMMUTATIVE))
				$Property_Value_Type_Alias = &$Property_Type_Alias;
			else
				$Property_Value_Type_Alias = &As_Key($Item_Values['Value_Type']);
			$Cached_Property_Value_Type = &Get_Cached_Type($Database, $Property_Value_Type_Alias);

			// Auto-set 'Name'
			if (!array_key_exists('Name', $Item_Values) || is_null($Item_Values['Name']))
			{
				$Item_Name = &$Cached_Property_Value_Type['Name'];
				Set_Value($Item, 'Name', $Item_Name);

				// Trickle auto-set 'Alias', if Alias is not hard-set
				if (!array_key_exists('Alias', $Item_Values) || ($Is_Changed && !array_key_exists('Alias', $Item['Original_Values'])))	
				{
					$Item_Alias = &Jelly_Format($Item_Name, 'Alias');
					Set_Value($Item, 'Alias', $Item_Alias);
				}
			}
			
			// Autoset 'Reverse Alias', if Reverse Alias is not hard set
			if (!array_key_exists('Reverse_Alias', $Item_Values) || ($Is_Changed && !array_key_exists('Reverse_Alias', $Item['Original_Values'])))
			{
				if (array_key_exists('Reverse_Name', $Item_Values) && !is_null($Item_Values['Reverse_Name']))
				{
					$Item_Reverse_Name = &$Item_Values['Reverse_Name'];
					$Item_Reverse_Alias = &Jelly_Format($Item_Reverse_Name, 'Alias');
					Set_Value($Item, 'Reverse_Alias', $Item_Reverse_Alias);
				}
			}
			
			// Autoset 'Plural Alias', if Plural Alias is not hard set
			if (!array_key_exists('Plural_Alias', $Item_Values) || ($Is_Changed && !array_key_exists('Plural_Alias', $Item['Original_Values'])))
			{
				if (array_key_exists('Plural_Name', $Item_Values) && !is_null($Item_Values['Plural_Name']))
				{
					$Item_Plural_Name = &$Item_Values['Plural_Name'];
					$Item_Plural_Alias = &Jelly_Format($Item_Plural_Name, 'Alias');
					Set_Value($Item, 'Plural_Alias', $Item_Plural_Alias);
				}
			}
			
			// Autoset 'Reverse Plural Alias', if Reverse Plural Alias is not hard set
			if (!array_key_exists('Reverse_Plural_Alias', $Item_Values) || ($Is_Changed && !array_key_exists('Reverse_Plural_Alias', $Item['Original_Values'])))
			{
				if (array_key_exists('Reverse_Plural_Name', $Item_Values) && !is_null($Item_Values['Reverse_Plural_Name']))
				{
					$Item_Reverse_Plural_Name = &$Item_Values['Reverse_Plural_Name'];
					$Item_Reverse_Plural_Alias = &Jelly_Format($Item_Reverse_Plural_Name, 'Alias');
					Set_Value($Item, 'Reverse_Plural_Alias', $Item_Reverse_Plural_Alias);
				}
			}
			
			// Autoset values for simple value types
			if (Is_Simple_Type($Cached_Property_Value_Type))
			{	
				// Autoset 'Relation'
				{
					$Property_Relation = &New_Null();
					Set_Value($Item, 'Relation', $Property_Relation);
				}
				
				// Autoset 'Data Name', if Data Name is not hard set
				if (!array_key_exists('Data_Name', $Item_Values) || ($Is_Changed && !array_key_exists('Data_Name', $Item['Original_Values'])))
				{
					if (array_key_exists('Alias', $Item_Values) && !is_null($Item_Values['Alias']))
					{
						$Item_Alias =  &$Item_Values['Alias'];
						$Item_Data_Name = &$Item_Alias;
						Set_Value($Item, 'Data_Name', $Item_Data_Name);
					}
				}				
			}

			// Autoset values for complex value types
			else
			{
				// Autoset 'Relation'
				if (!array_key_exists('Relation', $Item_Values) || is_null($Item_Values['Relation']))
				{
					$Property_Relation = &New_String('Many-To-One');
					Set_Value($Item, 'Relation', $Property_Relation);
				}
	
				$Property_Relation =  $Item_Values['Relation'];
				switch (strtolower($Property_Relation))
				{
					case MANY_TO_ONE:
					{						
						// Autoset 'Data Name', if Data Name is not hard set
						if (!array_key_exists('Data_Name', $Item_Values) || ($Is_Changed && !array_key_exists('Data_Name', $Item['Original_Values'])))
						{
							if (array_key_exists('Alias', $Item_Values) && !is_null($Item_Values['Alias']))
							{
								$Item_Alias =  &$Item_Values['Alias'];
								$Item_Data_Name = &$Item_Alias;
								Set_Value($Item, 'Data_Name', $Item_Data_Name);
							}
						}
						
						// Autoset 'Key' if it doesn't exist, or if the value type has changed and the key is not hard set
						if (!array_key_exists('Key', $Item_Values) || ($Is_Changed && !array_key_exists('Key', $Item['Original_Values']) && array_key_exists('Value_Type', $Item['Original_Values'])))
						{
							$Item_Key = &$Cached_Property_Value_Type['Default_Key'];
							Set_Value($Item, 'Key', $Item_Key);
						}
						
						break;
					}

					case ONE_TO_MANY:
					{
						// Autoset 'Reverse Name'
						if (!array_key_exists('Reverse_Name', $Item_Values) || is_null($Item_Values['Reverse_Name']))
						{
							$Item_Reverse_Name = &$Cached_Property_Type['Name'];
							Set_Value($Item, 'Reverse_Name', $Item_Reverse_Name);
							
							// Trickle auto-set 'Reverse Alias', if Reverse Alias is not hard set
							if (!array_key_exists('Reverse_Alias', $Item_Values) || ($Is_Changed && !array_key_exists('Reverse_Alias', $Item['Original_Values'])))
							{
								$Item_Reverse_Alias = &Jelly_Format($Item_Reverse_Name, 'Alias');
								Set_Value($Item, 'Reverse_Alias', $Item_Reverse_Alias);
							}
						}
						
						// Auto-set 'Data Name', if Data Name is not hard set
						if (!array_key_exists('Data_Name', $Item_Values) || ($Is_Changed && !array_key_exists('Data_Name', $Item['Original_Values'])))
						{
							if (array_key_exists('Reverse_Alias', $Item_Values) && !is_null($Item_Values['Reverse_Alias']))
							{
								$Item_Reverse_Alias =  &$Item_Values['Reverse_Alias'];
								$Item_Data_Name = &$Item_Reverse_Alias;
								Set_Value($Item, 'Data_Name', $Item_Data_Name);
							}
						}

						// Auto-set 'Key', if Key is not hard set
						if (!array_key_exists('Key', $Item_Values) || ($Is_Changed && !array_key_exists('Key', $Item['Original_Values'])))
						{
							$Item_Key = &$Cached_Property_Type['Default_Key'];
							Set_Value($Item, 'Key', $Item_Key);
						}
						
						break;
					}
					
					case COMMUTATIVE:
						// Autoset 'Data Name', if Data Name is not hard set
						if (!array_key_exists('Data_Name', $Item_Values) || ($Is_Changed && !array_key_exists('Data_Name', $Item['Original_Values'])))
						{
							if (array_key_exists('Alias', $Item_Values) && !is_null($Item_Values['Alias']))
							{
								$Item_Alias =  &$Item_Values['Alias'];
								$Item_Data_Name = &$Item_Alias;
								Set_Value($Item, 'Data_Name', $Item_Data_Name);
							}
						}
					
						// Autoset 'Reverse Data Name', if Reverse Data Name is not hard set
						if (!array_key_exists('Reverse_Data_Name', $Item_Values) || ($Is_Changed && !array_key_exists('Reverse_Data_Name', $Item['Original_Values'])))
						{
							if (array_key_exists('Alias', $Item_Values) && !is_null($Item_Values['Alias']))
							{
								$Item_Other_Alias =  &New_String('Other' . '_' . $Item_Values['Alias']);
								$Item_Reverse_Data_Name = &$Item_Other_Alias;
								Set_Value($Item, 'Reverse_Data_Name', $Item_Reverse_Data_Name);
							}
						}
						
						// Autoset 'Key', if Key is not hard set
						if (!array_key_exists('Key', $Item_Values) || ($Is_Changed && !array_key_exists('Key', $Item['Original_Values'])))
						{
							$Item_Key = &$Cached_Property_Value_Type['Default_Key'];
							Set_Value($Item, 'Key', $Item_Key);
						}
						break;

					
					case MANY_TO_MANY:
					{
						// Autoset 'Reverse Name'
						if (!array_key_exists('Reverse_Name', $Item_Values) || is_null($Item_Values['Reverse_Name']))
						{
							$Item_Reverse_Name = &$Cached_Property_Type['Name'];
							Set_Value($Item, 'Reverse_Name', $Item_Reverse_Name);
							
							// Trickle auto-set 'Reverse Alias', if Reverse Alias is not hard set
							if (!array_key_exists('Reverse_Alias', $Item_Values) || ($Is_Changed && !array_key_exists('Reverse_Alias', $Item['Original_Values'])))
							{
								$Item_Reverse_Alias = &Jelly_Format($Item_Reverse_Name, 'Alias');
								Set_Value($Item, 'Reverse_Alias', $Item_Reverse_Alias);
							}
						}
						
						// Autoset 'Data Name', if Data Name is not hard set
						if (!array_key_exists('Data_Name', $Item_Values) || ($Is_Changed && !array_key_exists('Data_Name', $Item['Original_Values'])))
						{
							if (array_key_exists('Alias', $Item_Values) && !is_null($Item_Values['Alias']))
							{
								$Item_Alias =  &$Item_Values['Alias'];
								$Item_Data_Name = &$Item_Alias;
								Set_Value($Item, 'Data_Name', $Item_Data_Name);
							}
						}
						
						// Autoset 'Reverse Data Name', if Reverse Data Name is not hard set
						if (!array_key_exists('Reverse_Data_Name', $Item_Values) || ($Is_Changed && !array_key_exists('Reverse_Data_Name', $Item['Original_Values'])))
						{
							if (array_key_exists('Reverse_Alias', $Item_Values) && !is_null($Item_Values['Reverse_Alias']))
							{
								$Item_Reverse_Alias =  &$Item_Values['Reverse_Alias'];
								$Item_Reverse_Data_Name = &$Item_Reverse_Alias;
								Set_Value($Item, 'Reverse_Data_Name', $Item_Reverse_Data_Name);
							}
						}
						
						// Autoset 'Key', if Key is not hard set
						if (!array_key_exists('Key', $Item_Values) || ($Is_Changed && !array_key_exists('Key', $Item['Original_Values'])))
						{
							$Item_Key = &$Cached_Property_Value_Type['Default_Key'];
							Set_Value($Item, 'Key', $Item_Key);
						}
						
						// Autoset 'Reverse Key', if Reverse Key is not hard set
						if (!array_key_exists('Reverse_Key', $Item_Values) || ($Is_Changed && !array_key_exists('Reverse_Key', $Item['Original_Values'])))
						{
							$Item_Reverse_Key = &$Cached_Property_Type['Default_Key'];
							Set_Value($Item, 'Reverse_Key', $Item_Reverse_Key);
						}
						break;
					}
				}
			}
			break;
		}
	}
	
	// Update database structure for types and properties
	if (!isset($Parameters['No_Structure']) || !$Parameters['No_Structure'])
	{
		// Handle saving of special types
		// TODO - might want a unique name for types/type_action's within scope, and other items in general, as a nice convenience.
		$Cached_Item_Type_Alias = &$Cached_Item_Type['Alias'];
		switch (strtolower($Cached_Item_Type_Alias))
		{
			// Types
			case 'type':
			{
				// Require values...
				
				// Require non-blank data name 
				if (!isset($Item['Data']['Data_Name']) || !$Item['Data']['Data_Name'])
					throw new Exception('Type\'s Data Name required.');					

				// Require non-blank alias
				if (!isset($Item['Data']['Alias']) || !$Item['Data']['Alias'])
					throw new Exception('Type\'s Alias required.');				

				// Verify values... 
				
				// If new item, or name has changed, verify unique name - and trickle any changes to alias
				if ($Is_New_Item || ($Is_Changed && array_key_exists('Name', $Item['Original_Values'])))
				{
					$Item_Name = &$Item_Values['Name'];
					$Item_Unique_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Name', $Item_Name, ' ');	
					if ($Item_Unique_Name != $Item_Name)
					{
						Set_Value($Item, 'Name', $Item_Unique_Name);					

						$Item_Alias = &New_String(Jelly_Format($Item_Unique_Name, 'Alias'));
						Set_Value($Item, 'Alias', $Item_Alias);
					}
				}
				
				// If new item, or alias has changed, verify unique alias - and trickle any changes to data_name
				if ($Is_New_Item || ($Is_Changed && array_key_exists('Alias', $Item['Original_Values'])))
				{
					$Item_Alias = &$Item_Values['Alias'];
					$Item_Unique_Alias = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Alias', $Item_Alias);
					
					if ($Item_Unique_Alias != $Item_Alias)
					{
						Set_Value($Item, 'Alias', $Item_Unique_Alias);
						
						$Item_Data_Name = &$Item_Alias;
						Set_Value($Item, 'Data_Name', $Item_Data_Name);
					}
				}
				
				// If new item, or data_name has changed, verify unique data_name
				if ($Is_New_Item || ($Is_Changed && array_key_exists('Data_Name', $Item['Original_Values'])))
				{
					$Item_Data_Name = &$Item_Values['Data_Name'];
					$Item_Unique_Data_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Data_Name', $Item_Data_Name);						

					if ($Item_Unique_Data_Name != $Item_Data_Name)
						Set_Value($Item, 'Data_Name', $Item_Unique_Data_Name);
				}
								
				// Localize type data
				// TODO - this is ... completely fucking nuts? 
				$Type = &New_Array();
				$Type['ID'] = &$Item['Data']['ID'];
				$Type['Name'] = &$Item['Data']['Name'];
				$Type['Plural_Name'] = &$Item['Data']['Plural_Name'];
				$Type['Default_Key'] = &$Item['Data']['Default_Key'];
				$Type['Plural_Alias'] = &$Item['Data']['Plural_Alias'];
				$Type['Data_Name'] = &$Item['Data']['Data_Name'];
				$Type['Alias'] = &$Item['Data']['Alias'];

				// Check if type is simple or complex
				$Type_Alias = &$Type['Alias'];
				if (!in_array(strtolower($Type_Alias), $Simple_Types))
				{
					// Complex Type...
					
					// If this isn't 'Item' or 'Simple_Item', resolve parent type
					if (!in_array(strtolower($Type['Alias']), array('item', 'simple_item')))
					{
						if(!array_key_exists('Parent_Type', $Item['Data']) || Is_Not_Set($Item['Data']['Parent_Type']))
							throw new Exception('Type\'s Parent Type must be set');
							
						// TODO - remove in production.
						if (is_null($Item['Data']['Parent_Type']))
							throw new Exception('Language glitch, parent type should not be null...');
													
						$Type['Parent_Type'] = &As_Key($Item['Data']['Parent_Type']);
					}
				
					// Check if type is already in the cache
					// TODO @feature-database: OK for simple types
					$Cached_Type_Lookup = &$Database['Cached_Type_Lookup'];
					if (!isset($Cached_Type_Lookup[strtolower($Type_Alias)]))
					{
						if (!$Type['Parent_Type'])
							throw new Exception('Type Parent Type required');
						
						// Get Parent Type
						// TODO @feature-database: resolve Parent Type above
						$Type_Parent_Type_Alias = &$Type['Parent_Type'];
						$Cached_Parent_Type = &Get_Cached_Type($Database, $Type_Parent_Type_Alias);
					}
					else
					{
						// TODO: this is a bit of a hack to create columns for Item
						// TODO: $Cached_Parent_Type is ill-named; should be Copy_Type or something
						$Cached_Parent_Type = &Get_Cached_Type($Database, $Type_Alias);
					}
				
					$Type_Data_Name = &$Type['Data_Name'];

					// Handle newly created type...
					if (!$Item['Saved'])
					{
						// Type is being created...
				
						// Generate Property Columns
						$Table_Columns = array();
						foreach ($Cached_Parent_Type['All_Cached_Properties'] as &$Cached_Parent_Type_Property)
						{
							$Cached_Parent_Type_Property_Alias = &$Cached_Parent_Type_Property['Alias'];
					
							switch (strtolower($Cached_Parent_Type_Property_Alias))
							{
								case 'id':
									$Table_Columns[] = '`ID` INT PRIMARY KEY NOT NULL';
									break;
								case 'order':
									$Table_Columns[] = '`Order` INT';
									break;
								case 'modified':
									$Table_Columns[] = '`Modified` TIMESTAMP';
									break;
								default:
									// Only many-to-one and simple columns are created at this point
									$Cached_Parent_Type_Property_Value_Type = &$Cached_Parent_Type_Property['Cached_Value_Type'];
									$Cached_Parent_Type_Property_Value_Type_Alias = &$Cached_Parent_Type_Property_Value_Type['Alias'];
									$Cached_Parent_Type_Property_Relation = &$Cached_Parent_Type_Property['Relation'];
									
									if (Is_Simple_Type($Cached_Parent_Type_Property_Value_Type) || strtolower($Cached_Parent_Type_Property_Relation) == MANY_TO_ONE)
									{
										// Check if property is simple or complex
										if (Is_Simple_Type($Cached_Parent_Type_Property_Value_Type))
										{
											// For simple properties, look up the SQL column for its value type
											$Cached_Parent_Type_Property_SQL_Type = &$Types_To_SQL_Types[strtolower($Cached_Parent_Type_Property_Value_Type_Alias)];
										}
										else
										{
											// For complex properties, look up the SQL column for its key
											$Cached_Parent_Type_Property_Key_Alias = &$Cached_Parent_Type_Property['Key'];
											
											$Cached_Parent_Type_Property_Key = &$Cached_Parent_Type_Property_Value_Type['Cached_Property_Lookup'][strtolower($Cached_Parent_Type_Property_Key_Alias)];
											$Cached_Parent_Type_Property_Key_Value_Type = &$Cached_Parent_Type_Property_Key['Cached_Value_Type'];
											$Cached_Parent_Type_Property_Key_Value_Type_Alias = &$Cached_Parent_Type_Property_Key_Value_Type['Alias'];
											$Cached_Parent_Type_Property_SQL_Type = &$Types_To_SQL_Types[strtolower($Cached_Parent_Type_Property_Key_Value_Type_Alias)];
										}
								
										// Add SQL column definition
										$Cached_Parent_Type_Property_Data_Name = &$Cached_Parent_Type_Property['Data_Name'];
										$Table_Columns[] =
											'`' . mysqli_real_escape_string($Database['Link'], $Cached_Parent_Type_Property_Data_Name) . '`' . ' ' .
											mysqli_real_escape_string($Database['Link'], $Cached_Parent_Type_Property_SQL_Type);
									}
									break;
							}
						}
				
						// Add index
						// TODO this is a bit of a hack to prevent indices on Simple_Item
// 						if (strtolower($Type_Data_Name) != 'simple_item')
// 							$Table_Columns[] = 'INDEX (`Alias`, `Order`)';
				
						// TODO @error-database: Check if table exists
				
						// Generate MySQL create table statement
						$Table_Columns[] = "DUMMY_COLUMN int";
						$Table_Columns_String = implode(', ', $Table_Columns);
						$Create_Table_Command =
							'CREATE TABLE `' . mysqli_real_escape_string($Database['Link'], $Type_Data_Name) . '`' . ' ' . 
							'(' . $Table_Columns_String . ')' . ' ' .
							'CHARACTER SET utf8';
						Query($Database, $Create_Table_Command);
					}

					else
					// Handle altered type...
					{							
						$Alter_Table_Query_Parts = array();
						
						// Get the current table name...
						
						// If the data name has changed, get the table name from the previous data name
						if (array_key_exists('Original_Values', $Item) && array_key_exists('Data_Name', $Item['Original_Values']))
						{	
							$Cached_Current_Table_Type_Data_Name = &$Item['Original_Values']['Data_Name'];
						}
						
						// Otherwise, if the alias changed, get it from ... hey this makes no sense.
						// TODO - do we need this? 
						else if ( array_key_exists('Original_Values', $Item) && array_key_exists('Alias', $Item['Original_Values']))
						{
							$Current_Table_Type_Alias = &$Item['Original_Values']['Alias'];
							$Cached_Current_Table_Type = &Get_Cached_Type($Database, $Current_Table_Type_Alias);
							$Cached_Current_Table_Type_Data_Name = &$Cached_Current_Table_Type['Data_Name'];
							
						}
						// Otherwise, get the table name from the type's data name value.
						else
							$Cached_Current_Table_Type_Data_Name = &$Type_Data_Name;
										
						// Check if type's Parent Type is changing
						if (array_key_exists('Original_Values', $Item) && array_key_exists('Parent_Type', $Item['Original_Values']))
						{
							// Type's Parent Type is changing...
						
							// Get Parent Type
							$Type_Parent_Type_Alias = &$Type['Parent_Type'];
							// TODO dereference above
							$Cached_Parent_Type = &Get_Cached_Type($Database, $Type_Parent_Type_Alias);
						
							// Get Original Parent Type
							$Type_Original_Parent_Type_Alias = &$Item['Original_Values']['Parent_Type'];
							// TODO dereference above
							$Type_Original_Parent_Type = &Get_Cached_Type($Database, $Type_Original_Parent_Type_Alias);
						
							// Determine list of properties unique to the old parent type (to delete)
							$Unique_Original_Parent_Type_Properties = &New_Array();
							$Type_Original_Parent_Type_Properties = &$Type_Original_Parent_Type['All_Cached_Properties'];
							foreach($Type_Original_Parent_Type_Properties as &$Type_Original_Parent_Type_Property)
							{
								$Type_Original_Parent_Type_Property_Alias = &$Type_Original_Parent_Type_Property['Alias'];
								if (!isset($Cached_Parent_Type['Cached_Property_Lookup'][strtolower($Type_Original_Parent_Type_Property_Alias)]))
								{
									$Unique_Original_Parent_Type_Properties[] = &$Type_Original_Parent_Type_Property;
								}
							}
						
							// Check if we found any unique properties to the original type (to delete)
							if (count($Unique_Original_Parent_Type_Properties))
							{
								// TODO @feature-database: do this for each Child_Type and check against overloaded properties that might already exist or have different definitions
							
								// Delete Unique Original Parent Type Properties...
								$Drop_Columns_Parts = array();
								foreach($Unique_Original_Parent_Type_Properties as &$Unique_Original_Parent_Type_Property)
								{
									$Unique_Original_Parent_Type_Property_Data_Name = &$Unique_Original_Parent_Type_Property['Data_Name'];
									$Drop_Column_String = 'DROP COLUMN' . ' ' . '`' . mysqli_real_escape_string($Database['Link'], $Unique_Original_Parent_Type_Property_Data_Name) . '`';
									$Drop_Columns_Parts[] = &$Drop_Column_String;
								}	
								$Drop_Columns_String = implode(' ', $Drop_Columns_Parts);
								$Alter_Table_Query_Parts[] = &$Drop_Columns_String;
							}
						
							// Determine list of properties unique to the new parent type (to add)
							$Unique_Cached_Parent_Type_Properties = &New_Array();
							$Cached_Parent_Type_Properties = &$Cached_Parent_Type['All_Cached_Properties'];
							foreach($Cached_Parent_Type_Properties as &$Cached_Parent_Type_Property)
							{
								$Cached_Parent_Type_Property_Alias = &$Cached_Parent_Type_Property['Alias'];
								if (!isset($Type_Original_Parent_Type['Cached_Property_Lookup'][strtolower($Cached_Parent_Type_Property_Alias)]))
								{
									$Unique_Cached_Parent_Type_Properties[] = &$Cached_Parent_Type_Property;
								}
							}
						
							// Check if we found any unique properties to the new parent type (to add)
							if (count($Unique_Cached_Parent_Type_Properties))
							{
								// TODO @feature-language: do this for each Child_Type and check against overloaded properties that might already exist or have different definitions
							
								// Add Unique Type Properties
								$Add_Columns_Parts = array();
								foreach($Unique_Cached_Parent_Type_Properties as &$Unique_Cached_Parent_Type_Property)
								{
									$Unique_Cached_Parent_Type_Property_Data_Name = &$Unique_Cached_Parent_Type_Property['Data_Name'];
									// TODO lots of duplicate code follows... better ADD COLUMN command generation (combine with other times columns are added?)
									// $Add_Column_String = 'ADD COLUMN' . ' ' . '`' . mysqli_real_escape_string($Database['Link'], $Unique_Cached_Parent_Type_Property_Data_Name) . '`';
							
									// Only many-to-one and simple columns are created at this point
									$Unique_Cached_Parent_Type_Property_Value_Type = &$Unique_Cached_Parent_Type_Property['Cached_Value_Type'];
									$Unique_Cached_Parent_Type_Property_Value_Type_Alias = &$Unique_Cached_Parent_Type_Property_Value_Type['Alias'];
									$Unique_Cached_Parent_Type_Property_Relation = &$Unique_Cached_Parent_Type_Property['Relation'];
									if (in_array(strtolower($Unique_Cached_Parent_Type_Property_Value_Type_Alias), $Simple_Types) || strtolower($Unique_Cached_Parent_Type_Property_Relation) == MANY_TO_ONE)
									{
										// Check if property is simple or complex
										if (in_array(strtolower($Unique_Cached_Parent_Type_Property_Value_Type_Alias), $Simple_Types))
										{
											// For simple properties, look up the SQL column for its value type
											$Unique_Cached_Parent_Type_Property_SQL_Type = &$Types_To_SQL_Types[strtolower($Unique_Cached_Parent_Type_Property_Value_Type_Alias)];
										}
										else
										{
											// For complex properties, look up the SQL column for its key
											$Unique_Cached_Parent_Type_Property_Key_Alias = &$Unique_Cached_Parent_Type_Property['Key'];
											$Unique_Cached_Parent_Type_Property_Key = &$Unique_Cached_Parent_Type_Property_Value_Type['Cached_Property_Lookup'][strtolower($Unique_Cached_Parent_Type_Property_Key_Alias)];
											$Unique_Cached_Parent_Type_Property_Key_Value_Type = &$Unique_Cached_Parent_Type_Property_Key['Cached_Value_Type'];
											$Unique_Cached_Parent_Type_Property_Key_Value_Type_Alias = &$Unique_Cached_Parent_Type_Property_Key_Value_Type['Alias'];
											$Unique_Cached_Parent_Type_Property_SQL_Type = &$Types_To_SQL_Types[strtolower($Unique_Cached_Parent_Type_Property_Key_Value_Type_Alias)];
										}
							
										// Add SQL column definition
										$Unique_Cached_Parent_Type_Property_Data_Name = &$Unique_Cached_Parent_Type_Property['Data_Name'];
										$Add_Column_String = 'ADD COLUMN' . ' ' .
											'`' . mysqli_real_escape_string($Database['Link'], $Unique_Cached_Parent_Type_Property_Data_Name) . '`' . ' ' .
											mysqli_real_escape_string($Database['Link'], $Unique_Cached_Parent_Type_Property_SQL_Type);
							
										$Add_Columns_Parts[] = &$Add_Column_String;
										unset($Add_Column_String);
									}
								}
								$Add_Columns_String = implode(', ', $Add_Columns_Parts);
								$Alter_Table_Query_Parts[] = &$Add_Columns_String;
							}
						}
					
						// Check if type's Data Name is changing
						if (array_key_exists('Original_Values', $Item) && array_key_exists('Data_Name', $Item['Original_Values']))
						{
							// Type's Data Name is changing...
						
							// Generate rename table query
							$Rename_String = 'RENAME TO ' . ' ' . '`' . mysqli_real_escape_string($Database['Link'], $Type_Data_Name) . '`';
						
							$Alter_Table_Query_Parts[] = &$Rename_String;
						}

						$Alter_Table_Command = "ALTER TABLE " . '`' . mysqli_real_escape_string($Database['Link'], $Cached_Current_Table_Type_Data_Name) . '`';
// 						$Alter_Table_Query_Parts[] = &$Alter_Table_Command;
						
						// Combine query parts into Alter Table query
						$Alter_Table_Query_String = $Alter_Table_Command . implode(', ', $Alter_Table_Query_Parts);
					
						// Execute query
						Query($Database, $Alter_Table_Query_String);
					}
				}
				
				break;
			}

			// Property
			case 'property':
			{
				// Verify values... 
				
				$Is_New_Item = &New_Boolean(!$Item['Saved']);
				$Is_Changed = &New_Boolean(array_key_exists('Original_Values', $Item));

				// Only update database structure if property is new or if it has changed values
				if ($Is_New_Item || $Is_Changed)
				{
					// Set constants
					$Staging_Suffix = '_Temporary';
					
					// Get new and original value arrays.
					{
						// TODO @feature-database : get from cache
						// TODO - test reverse version						
						
						// Localize new property type, new cached property value type, new relation...						
						$New_Property_Relation = &$Item_Values['Relation'];
					
						// TODO - this is a little bit of a rehash of a section above, should consolidate.						
						// TODO - Reset databases passes in non-item values sometimes, but maybe there's a better solution than this.
						
						// Get cached new property type
						$Cached_New_Property_Type_Alias = &As_Key($Item_Values['Type']);
						$Cached_New_Property_Type = &Get_Cached_Type($Database, $Cached_New_Property_Type_Alias);
						$Cached_New_Property_Type_Query = &New_String('`' . 'Type' . '`' . ' ' . '=' . ' ' . '\'' . mysqli_real_escape_string($Database['Link'], $Cached_New_Property_Type_Alias) . '\'');

						// Get cached new property value type			
						if (strtolower($New_Property_Relation) == COMMUTATIVE)
							$Cached_New_Property_Value_Type_Alias =  &$Cached_New_Property_Type_Alias;
						else
							$Cached_New_Property_Value_Type_Alias = &As_Key($Item_Values['Value_Type']);
						$Cached_New_Property_Value_Type = &Get_Cached_Type($Database, $Cached_New_Property_Value_Type_Alias);
						$Cached_New_Property_Value_Type_Query = &New_String('`' . 'Value_Type' . '`' . ' ' . '=' . ' ' . '\'' . mysqli_real_escape_string($Database['Link'], $Cached_New_Property_Value_Type_Alias) . '\'');
							
						// Generate conditional queries
						$Cached_New_Property_One_To_Many_Query = &New_String('`' . 'Relation' . '`' . ' ' . '=' .' ' . '\'' . 'One-To-Many' . '\'');
						$Cached_New_Property_Not_One_To_Many_Query = &New_String(
								'(' . 
									('(' . '`' . 'Relation' . '`' . ' ' . '!=' .' ' . '\'' . 'One-To-Many' . '\'' . ')') . 
										' ' . 'OR' . ' ' . 
									('(' . '`' . 'Relation' . '`'. ' ' . 'IS' . ' ' . 'NULL' . ')') . 
								')'
							);

						// Verify new property values... 
						
						// Create property type query condition

						// If new item, or name has changed, verify unique name - and trickle any changes to alias
						if ($Is_New_Item || ($Is_Changed && array_key_exists('Name', $Item['Original_Values'])))
						{
							$Item_Name = &$Item_Values['Name'];

							$Item_Unique_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Name', $Item_Name, ' ', $Cached_New_Property_Type_Query);

							if ($Item_Unique_Name != $Item_Name)
							{
								Set_Value($Item, 'Name', $Item_Unique_Name);					

								$Item_Alias = &New_String(Jelly_Format($Item_Unique_Name, 'Alias'));
								Set_Value($Item, 'Alias', $Item_Alias);
							}
						}
						
						// If new item, or reverse_name has changed, verify unique reverse_name - and trickle any changes to alias
						if (strtolower($New_Property_Relation) != COMMUTATIVE)
						{
							if ($Is_New_Item || ($Is_Changed && array_key_exists('Reverse_Name', $Item['Original_Values'])))
							{
								$Item_Reverse_Name = &$Item_Values['Reverse_Name'];
							
								$Item_Unique_Reverse_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Reverse_Name', $Item_Reverse_Name, ' ', $Cached_New_Property_Value_Type_Query);

								if ($Item_Unique_Reverse_Name != $Item_Reverse_Name)
								{
									Set_Value($Item, 'Reverse_Name', $Item_Unique_Reverse_Name);					

									$Reverse_Item_Alias = &New_String(Jelly_Format($Item_Unique_Reverse_Name, 'Alias'));
									Set_Value($Item, 'Reverse_Alias', $Reverse_Item_Alias);
								}
							}
						}

						// If new item, or alias has changed, verify unique alias - and trickle changes to data_name if needed
						if ($Is_New_Item || ($Is_Changed && array_key_exists('Alias', $Item['Original_Values'])))
						{
							$Item_Alias= &$Item_Values['Alias'];

							$Item_Unique_Alias = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Alias', $Item_Alias, '_', $Cached_New_Property_Type_Query);

							if ($Item_Unique_Alias != $Item_Alias)
							{
								Set_Value($Item, 'Alias', $Item_Unique_Alias);

								if (strtolower($New_Property_Relation) == MANY_TO_ONE || !$New_Property_Relation)
								{
									$Item_Data_Name = &$Item_Alias;
									Set_Value($Item, 'Data_Name', $Item_Data_Name);
								}
							}
						}
						
						// If new item, or reverse_alias has changed, verify unique reverse alias - and trickle changes to data_name or reverse_data_name if needed
						if (strtolower($New_Property_Relation) != COMMUTATIVE)
						{
							if ($Is_New_Item || ($Is_Changed && array_key_exists('Reverse_Alias', $Item['Original_Values'])))
							{
								$Item_Reverse_Alias = &$Item_Values['Reverse_Alias'];
							
								$Item_Unique_Reverse_Alias = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Reverse_Alias', $Item_Reverse_Alias, '_', $Cached_New_Property_Value_Type_Query);

								if ($Item_Unique_Reverse_Alias != $Item_Reverse_Alias)
								{
									Set_Value($Item, 'Reverse_Alias', $Item_Unique_Reverse_Alias);
								
									if (strtolower($New_Property_Relation) != ONE_TO_MANY)
									{
										$Item_Reverse_Data_Name = &$Item_Reverse_Alias;
										Set_Value($Item, 'Reverse_Data_Name', $Item_Reverse_Data_Name);
									}
									else
									{
										$Item_Data_Name = &$Item_Reverse_Alias;
										Set_Value($Item, 'Data_Name', $Item_Data_Name);
									}
								}
							}
						}
						
						// If new item, or data_name has changed, verify unique data_name
						// TODO - Data_Name should check column names, instead of the below code, for reliability reasons discovered via testing  --- or more robust heuristics checks that check for additional reserved/existing key word conflicts.  Actually probably the latter, and apply it to above alias/reverse_alias checking as well, etc.
						// TODO - Check if this needs to be scoped by relation.
						if ($Is_New_Item || ($Is_Changed && array_key_exists('Data_Name', $Item['Original_Values'])))
						{
							$Item_Data_Name= &$Item_Values['Data_Name'];
							
							if (strtolower($New_Property_Relation) != ONE_TO_MANY)
							{
								$Item_Unique_Data_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Data_Name', $Item_Data_Name, '_', '(' . $Cached_New_Property_Type_Query. ')' . ' ' . 'AND' . ' ' . '(' . $Cached_New_Property_Not_One_To_Many_Query. ')');
							}
							else
							{		
								// TODO - i messed with these, hoping that they're more correct now, but did not test.
								$Item_Unique_Data_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Data_Name', $Item_Data_Name, '_', '(' . $Cached_New_Property_Type_Query. ')' . ' ' . 'AND' . ' ' . '(' . $Cached_New_Property_One_To_Many_Query. ')');
	
								$Item_Unique_Data_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Reverse_Data_Name', $Item_Unique_Data_Name, '_', $Cached_New_Property_Value_Type_Query);
							}

							if ($Item_Unique_Data_Name != $Item_Data_Name)
								Set_Value($Item, 'Data_Name', $Item_Unique_Data_Name);
						}
						
						// If new item, or reverse_data_name has changed, verify unique reverse_data_name
						// TODO - Data_Name should check column names, instead of the below code, for reliability reasons discovered via testing  --- or more robust heuristics checks that check for additional reserved/existing key word conflicts.  Actually probably the latter, and apply it to above alias/reverse_alias checking as well, etc.
						// TODO - check if this needs to be scoped by relation.
						if ($Is_New_Item || ($Is_Changed && array_key_exists('Reverse_Data_Name', $Item['Original_Values'])))
						{
							$Item_Reverse_Data_Name= &$Item_Values['Reverse_Data_Name'];
							
							$Item_Unique_Reverse_Data_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Data_Name', $Item_Reverse_Data_Name, '_', '(' . $Cached_New_Property_Value_Type_Query. ')' . ' ' . 'AND' . ' ' . '(' . $Cached_New_Property_One_To_Many_Query. ')');
							
							$Item_Unique_Reverse_Data_Name = &Generate_Unique_Value($Database, $Cached_Item_Type, 'Reverse_Data_Name', $Item_Unique_Reverse_Data_Name, '_', $Cached_New_Property_Value_Type_Query);

							if ($Item_Unique_Reverse_Data_Name != $Item_Reverse_Data_Name)
								Set_Value($Item, 'Reverse_Data_Name', $Item_Unique_Reverse_Data_Name);
						}
						
						// Localize remaining new values....
						$New_Property_Values = &$Item['Data'];
						$New_Property_Name = &$New_Property_Values['Name'];
						$New_Property_Alias = &$New_Property_Values['Alias'];						
						$New_Property_Data_Name = &$New_Property_Values['Data_Name'];

						// TODO - etc.
						if (array_key_exists('Key', $New_Property_Values))
							$New_Property_Key = &$New_Property_Values['Key'];
						$New_Property_Reverse_Name = &$New_Property_Values['Reverse_Name'];	
						$New_Property_Reverse_Alias = &$New_Property_Values['Reverse_Alias'];	
						$New_Property_Reverse_Data_Name = &$New_Property_Values['Reverse_Data_Name'];
						$New_Property_Reverse_Key = &$New_Property_Values['Reverse_Key'];						
						if (array_key_exists('Attachment_Type', $New_Property_Values) && $New_Property_Values['Attachment_Type'])
						{
							$Cached_New_Property_Attachment_Type_Alias = &As_Key($New_Property_Values['Attachment_Type']);
							$Cached_New_Property_Attachment_Type = &Get_Cached_Type($Database, $Cached_New_Property_Attachment_Type_Alias);
						}
						$New_Property_Is_Simple = in_array(strtolower($Cached_New_Property_Value_Type['Alias']), $Simple_Types);
													
						// Localize original values...
						
						// Localize original values array.
						if ($Item['Saved'] && isset($Item['Original_Values']))
							$Original_Property_Values = &$Item['Original_Values'];

						// Localize original property type.
						if ($Item['Saved'] && array_key_exists('Type', $Original_Property_Values))
						{
							$Cached_Original_Property_Type_Alias = &As_Key($Original_Property_Values['Type']);
							$Cached_Original_Property_Type = &Get_Cached_Type($Database, $Cached_Original_Property_Type_Alias);
						}
						else
							$Cached_Original_Property_Type = &$Cached_New_Property_Type;
							
						// Localize original property value type.
						if (strtolower($Item_Values['Relation']) == COMMUTATIVE)
						{
							if ($Item['Saved'] && array_key_exists('Type', $Original_Property_Values))
							{
								$Cached_Original_Property_Value_Type_Alias = &As_Key($Original_Property_Values['Type']);
								$Cached_Original_Property_Value_Type = &Get_Cached_Type($Database, $Cached_Original_Property_Value_Type_Alias);
							}
							else
								$Cached_Original_Property_Value_Type = &$Cached_New_Property_Type;
						}
						else
						{
							if ($Item['Saved'] && array_key_exists('Value_Type', $Original_Property_Values))
							{
								$Cached_Original_Property_Value_Type_Alias = &As_Key($Original_Property_Values['Value_Type']);
								$Cached_Original_Property_Value_Type = &Get_Cached_Type($Database, $Cached_Original_Property_Value_Type_Alias);
							}
							else
								$Cached_Original_Property_Value_Type = &$Cached_New_Property_Value_Type;
						}
						
						// Localize original property alias
						if ($Item['Saved'] && array_key_exists('Alias', $Original_Property_Values))
							$Original_Property_Alias = &$Original_Property_Values['Alias'];
						else
							$Original_Property_Alias = &$New_Property_Values['Alias'];
							
						// Localize original property reverse alias
						if ($Item['Saved'] && array_key_exists('Reverse_Alias', $Original_Property_Values))
							$Original_Property_Reverse_Alias = &$Original_Property_Values['Reverse_Alias'];
						else
							$Original_Property_Reverse_Alias = &$New_Property_Values['Reverse_Alias'];
							
						// Localize original property data name
						if ($Item['Saved'] && array_key_exists('Data_Name', $Original_Property_Values))
							$Original_Property_Data_Name = &$Original_Property_Values['Data_Name'];
						else
							$Original_Property_Data_Name = &$New_Property_Values['Data_Name'];
							
						// Localize original property reverse data name
						if ($Item['Saved'] && array_key_exists('Reverse_Data_Name', $Original_Property_Values))
							$Original_Property_Reverse_Data_Name = &$Original_Property_Values['Reverse_Data_Name'];
						else
							$Original_Property_Reverse_Data_Name = &$New_Property_Values['Reverse_Data_Name'];
						
						// Localize original property relation
						if ($Item['Saved'] && array_key_exists('Relation', $Original_Property_Values))
							$Original_Property_Relation = &$Original_Property_Values['Relation'];
						else
							$Original_Property_Relation = &$New_Property_Values['Relation'];
				
						// Localize original property key
						if ($Item['Saved'] && array_key_exists('Key', $Original_Property_Values))
							$Original_Property_Key = &$Original_Property_Values['Key'];
						// TODO - etc.
						else if (array_key_exists('Key', $New_Property_Values))
							$Original_Property_Key = &$New_Property_Values['Key'];
				
						// Localize original property reverse key
						if ($Item['Saved'] && array_key_exists('Reverse_Key', $Original_Property_Values))
							$Original_Property_Reverse_Key = &$Original_Property_Values['Reverse_Key'];
						else
							$Original_Property_Reverse_Key = &$New_Property_Values['Reverse_Key'];

						// Localize original property attachment type.							
						if ($Item['Saved'] && array_key_exists('Attachment_Type', $Original_Property_Values))
						{
							$Cached_Original_Property_Attachment_Type = &As_Key($Original_Property_Values['Attachment_Type']);
							$Cached_Original_Property_Attachment_Type = &Get_Cached_Type($Database, $Cached_Original_Property_Attachment_Type);
						}
						else if (isset($Cached_New_Property_Attachment_Type))
							$Cached_Original_Property_Attachment_Type = &$Cached_New_Property_Attachment_Type;
								
						// Localize original property is simple
						$Original_Property_Is_Simple = &New_Boolean(in_array(strtolower($Cached_Original_Property_Value_Type['Alias']), $Simple_Types));
					}
					
					// Create attachment type...
					{
						// TODO @error-database: error if changing to attachment type where value type is simple
						// If it's a new many-to-many/commutative property, or if the property's relation is changing to many-to-many/commutative from something other than those two, make an attachment type
						if ( 
								!$Parameters['No_Attachment_Structure']
									&&
								(
									(strtolower($New_Property_Relation) == MANY_TO_MANY) 
										|| 
									(strtolower($New_Property_Relation) == COMMUTATIVE) 
								)
									 &&
								(
									(!$Item['Saved'])
										||
									(
										(strtolower($Original_Property_Relation) != MANY_TO_MANY)
											&& 
										(strtolower($Original_Property_Relation) != COMMUTATIVE)
									)
								)
							)
						{
							// Create attachment type item
							$Type_Cached_Type = &Get_Cached_Type($Database, 'Type');
							$Attachment_Type_Item = &Create_Memory_Item($Database, $Type_Cached_Type);
					
							// Generate name and alias
							$Attachment_Type_Parent_Type_Alias = 'Item';
							$Attachment_Type_Name = &New_String($Cached_New_Property_Type['Alias'] .  ' '  . $New_Property_Values['Name']);
							$Attachment_Type_Alias = &New_String($Cached_New_Property_Type['Alias'] .  '_'  . $New_Property_Values['Alias']);
							$Attachment_Type_Data_Name = &$Attachment_Type_Alias;
							
							// Generate package
							$Attachment_Type_Package_Alias = &$Cached_New_Property_Type['Package'];
						
							// Set attachment type values
							Set_Value($Attachment_Type_Item, 'Parent_Type', $Attachment_Type_Parent_Type_Alias);
							Set_Value($Attachment_Type_Item, 'Name', $Attachment_Type_Name);
							Set_Value($Attachment_Type_Item, 'Alias', $Attachment_Type_Alias);
							Set_Value($Attachment_Type_Item, 'Data_Name', $Attachment_Type_Data_Name);
							Set_Value($Attachment_Type_Item, 'Package', $Attachment_Type_Package_Alias);
							
							// Save the attachment type item
							// TODO Somehow use same parameters so attachments aren't all added first during reset
							Save_Item($Attachment_Type_Item);
					
							// TODO: Maybe this should be hard coded.
							Set_Value($Item, 'Attachment_Type', $Attachment_Type_Alias);
						
							// Get attachment type for later code
							$Cached_New_Property_Attachment_Type = &Get_Cached_Type($Database, $Attachment_Type_Alias);
						}
						// TODO: Consider renaming attachment type alias when needed?
					}
					
					// Create staging columns...
					{
						$Target = &New_Array();
						$Target['Columns'] = &New_Array();
						switch (strtolower($New_Property_Relation))
						{
							// COMMUTATIVE
							case COMMUTATIVE:
								// Set target's type to attachment type
								$Target['Type'] = &$Cached_New_Property_Attachment_Type;
							
								// Create target column 1
								$Target['Columns'][0] = &New_Array();
							
								// Set target 1 staging column's value type to property's value type
								$Target['Columns'][0]['Value_Type'] = &$Cached_New_Property_Type;
								$Target['Columns'][0]['Key_Alias'] = &$New_Property_Values['Key'];
					
								// Set target 1 staging column name to property data name with staging suffix
								$Target['Columns'][0]['Staging_Name'] = &New_String($New_Property_Values['Data_Name'] . $Staging_Suffix);
								$Target['Columns'][0]['Name'] = &New_String($New_Property_Values['Data_Name']);
							
								// Create target column 2
								$Target['Columns'][1] = &New_Array();
							
								// Set target 2 staging column's value type to property's type
								$Target['Columns'][1]['Value_Type'] = &$Cached_New_Property_Type;
								$Target['Columns'][1]['Key_Alias'] = &$New_Property_Values['Key'];
					
								// Set target 2 staging column name to property reverse data name with staging suffix
								$Target['Columns'][1]['Staging_Name'] = &New_String($New_Property_Values['Reverse_Data_Name'] . $Staging_Suffix);
								$Target['Columns'][1]['Name'] = &New_String($New_Property_Values['Reverse_Data_Name']);
								break;

							// Many-To-Many
							case MANY_TO_MANY:
								// Set target's type to attachment type
								$Target['Type'] = &$Cached_New_Property_Attachment_Type;
							
								// Create target column 1
								$Target['Columns'][0] = &New_Array();
							
								// Set target 1 staging column's value type to property's value type
								$Target['Columns'][0]['Value_Type'] = &$Cached_New_Property_Value_Type;
								$Target['Columns'][0]['Key_Alias'] = &$New_Property_Values['Key'];
					
								// Set target 1 staging column name to property data name with staging suffix
								$Target['Columns'][0]['Staging_Name'] = &New_String($New_Property_Values['Data_Name'] . $Staging_Suffix);
								$Target['Columns'][0]['Name'] = &New_String($New_Property_Values['Data_Name']);
							
							
								// Create target column 2
								$Target['Columns'][1] = &New_Array();
							
								// Set target 2 staging column's value type to property's type
								$Target['Columns'][1]['Value_Type'] = &$Cached_New_Property_Type;
								$Target['Columns'][1]['Key_Alias'] = &$New_Property_Values['Reverse_Key'];
					
								// Set target 2 staging column name to property reverse data name with staging suffix
								$Target['Columns'][1]['Staging_Name'] = &New_String($New_Property_Values['Reverse_Data_Name'] . $Staging_Suffix);
								$Target['Columns'][1]['Name'] = &New_String($New_Property_Values['Reverse_Data_Name']);
							
								break;
						
							// One-To-Many
							case ONE_TO_MANY:
								// Set target's type to property's value type
								$Target['Type'] = &$Cached_New_Property_Value_Type;
							
								$Target['Columns'][0] = &New_Array();
							
								// Set target 2 staging column's value type to property's type
								$Target['Columns'][0]['Value_Type'] = &$Cached_New_Property_Type;
								$Target['Columns'][0]['Key_Alias'] = &$New_Property_Values['Key'];
					
								// Set target 1 staging column name to property data name with staging suffix
								$Target['Columns'][0]['Staging_Name'] = &New_String($New_Property_Values['Data_Name'] . $Staging_Suffix);
								$Target['Columns'][0]['Name'] = &New_String($New_Property_Values['Data_Name']);
						
								break;
							
							// Many-To-One
							case MANY_TO_ONE:
								// Set target 1's type to property's type
								$Target['Type'] = &$Cached_New_Property_Type;
							
							
								$Target['Columns'][0] = &New_Array();
							
								// Set target 1 staging column's value type to property's value type
								$Target['Columns'][0]['Value_Type'] = &$Cached_New_Property_Value_Type;
								$Target['Columns'][0]['Key_Alias'] = &$New_Property_Values['Key'];
					
								// Set target 1 staging column name to property data name with staging suffix
								$Target['Columns'][0]['Staging_Name'] = &New_String($New_Property_Values['Data_Name'] . $Staging_Suffix);
								$Target['Columns'][0]['Name'] = &New_String($New_Property_Values['Data_Name']);
								
								break;
						
							// Simple
							default:
								// TODO this should not happen for complex types, catch it somewhere.
								// Set target's type to property's type
								$Target['Type'] = &$Cached_New_Property_Type;
							
							
								$Target['Columns'][0] = &New_Array();
							
								// Set target 1 staging column's value type to property's value type
								$Target['Columns'][0]['Value_Type'] = &$Cached_New_Property_Value_Type;
					
								// Set target 1 staging column name to property data name with staging suffix
								$Target['Columns'][0]['Staging_Name'] = &New_String($New_Property_Values['Data_Name'] . $Staging_Suffix);
								$Target['Columns'][0]['Name'] = &New_String($New_Property_Values['Data_Name']);
							
								break;
						}
					
						// Create Staging Columns for each target
						foreach ($Target['Columns'] as &$Column)
						{
							// Get SQL type for staging column value type
							$Column['Value_Type_Alias'] = &$Column['Value_Type']['Alias'];
							if (in_array(strtolower($Column['Value_Type_Alias']), $Simple_Types))
							{
								$Column['Key_Value_Type'] = &$Column['Value_Type'];
							}
							else
							{
								// Get staging column's SQL type from value type's key
								$Column['Key'] = &$Column['Value_Type']['Cached_Property_Lookup'][strtolower($Column['Key_Alias'])];
								$Column['Key_Value_Type'] = &$Column['Key']['Cached_Value_Type'];
								$Column['Key_Data_Name'] = &$Column['Key']['Data_Name'];
								$Column['Key_Column'] = &$Column['Key_Data_Name'];
							}
					
							// Get SQL type for staging column
							$Column['Key_Value_Type_Alias'] = &$Column['Key_Value_Type']['Alias'];
							$Column['SQL_Type'] = &$Types_To_SQL_Types[strtolower($Column['Key_Value_Type_Alias'])];
					
							foreach ($Target['Type']['All_Cached_Child_Types'] as &$Target_Type_Child_Type)
							{
								// Generate table name
								$Target_Type_Child_Type_Data_Name = &$Target_Type_Child_Type['Data_Name'];
								$Target_Type_Child_Type_Table = &New_String($Table_Prefix . $Target_Type_Child_Type_Data_Name);
							
								// Generate query
								$Create_Staging_Column_Query =
									'ALTER TABLE'. ' ' . ('`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Child_Type_Table) . '`') . ' ' . 
									'ADD COLUMN' . ' ' .
										(('`' . mysqli_real_escape_string($Database['Link'], $Column['Staging_Name'])  . '`') . ' ' .
											(mysqli_real_escape_string($Database['Link'], $Column['SQL_Type'])));
							
								// Execute query
								Query($Database, $Create_Staging_Column_Query);
							}
						}
					}
					
					// For Saved Properties, copy data and delete original columns
					if ($Item['Saved'])
					{
						// Setup Sources...
						{
							// Setup sources for each relation
							$Source = &New_Array();
							$Source['Columns'] = &New_Array();
							switch (strtolower($Original_Property_Relation))
							{
								// Commutative 
								case COMMUTATIVE:
									// Set source 1's type to attachment type
									$Source['Type'] = &$Cached_Original_Property_Attachment_Type;
								
									// Create source column 1
									$Source['Columns'][0] = &New_Array();
							
									// Set source 1 column's value type to property's value type
									$Source['Columns'][0]['Value_Type'] = &$Cached_Original_Property_Type;
									$Source['Columns'][0]['Key_Alias'] = &$Original_Property_Key;
					
									// Set source 1 column name to property data name
									$Source['Columns'][0]['Name'] = &New_String($Original_Property_Data_Name);
							
									// Create source column 2
									$Source['Columns'][1] = &New_Array();
							
									// Set source 2 column's value type to property's type
									$Source['Columns'][1]['Value_Type'] = &$Cached_Original_Property_Type;
									$Source['Columns'][1]['Key_Alias'] = &$Original_Property_Key;
					
									// Set source 2 column name to property reverse data name
									$Source['Columns'][1]['Name'] = &New_String($Original_Property_Reverse_Data_Name);
							
									break;
								
								// Many-To-Many
								case MANY_TO_MANY:
									// Set source 1's type to attachment type
									$Source['Type'] = &$Cached_Original_Property_Attachment_Type;
								
									// Create source column 1
									$Source['Columns'][0] = &New_Array();
							
									// Set source 1 column's value type to property's value type
									$Source['Columns'][0]['Value_Type'] = &$Cached_Original_Property_Value_Type;
									$Source['Columns'][0]['Key_Alias'] = &$Original_Property_Key;
					
									// Set source 1 column name to property data name
									$Source['Columns'][0]['Name'] = &New_String($Original_Property_Data_Name);
							
							
									// Create source column 2
									$Source['Columns'][1] = &New_Array();
							
									// Set source 2 column's value type to property's type
									$Source['Columns'][1]['Value_Type'] = &$Cached_Original_Property_Type;
									$Source['Columns'][1]['Key_Alias'] = &$Original_Property_Reverse_Key;
					
									// Set source 2 column name to property reverse data name
									$Source['Columns'][1]['Name'] = &New_String($Original_Property_Reverse_Data_Name);
							
									break;
						
								// One-To-Many
								case ONE_TO_MANY:
							
									// Set source 2's type to property's value type
									$Source['Type'] = &$Cached_Original_Property_Value_Type;
								
									$Source['Columns'][0] = &New_Array();
							
									// Set source 2 column's value type to property's type
									$Source['Columns'][0]['Value_Type'] = &$Cached_Original_Property_Type;
									$Source['Columns'][0]['Key_Alias'] = &$Original_Property_Key;
					
									// Set source 1 column name to property data name
									$Source['Columns'][0]['Name'] = &New_String($Original_Property_Data_Name);
						
									break;
							
								// Many-To-One
								case MANY_TO_ONE:
							
									// Set source 1's type to property's type
									$Source['Type'] = &$Cached_Original_Property_Type;
								
									$Source['Columns'][0] = &New_Array();
							
									// Set source 1 column's value type to property's value type
									$Source['Columns'][0]['Value_Type'] = &$Cached_Original_Property_Value_Type;
									$Source['Columns'][0]['Key_Alias'] = &$Original_Property_Key;
					
									// Set source 1 column name to property data name
									$Source['Columns'][0]['Name'] = &New_String($Original_Property_Data_Name);
							
									break;
						
								// Simple
								default:
							
									// Set source 1's type to property's type
									$Source['Type'] = &$Cached_Original_Property_Type;
								
									$Source['Columns'][0] = &New_Array();
							
									// Set source 1 column's value type to property's value type
									$Source['Columns'][0]['Value_Type'] = &$Cached_Original_Property_Value_Type;
					
									// Set source 1 column name to property data name with suffix
									$Source['Columns'][0]['Name'] = &New_String($Original_Property_Data_Name);
							
									break;
							}
						
							// Setup data for each source
							foreach ($Source['Columns'] as &$Column)
							{
								// Get SQL type for column value type
								$Column['Value_Type_Alias'] = &$Column['Value_Type']['Alias'];
								if (in_array(strtolower($Column['Value_Type_Alias']), $Simple_Types))
								{
									$Column['Key_Value_Type'] = &$Column['Value_Type'];
								}
								else
								{
									// Get column's SQL type from value type's key
									$Column['Key'] = &$Column['Value_Type']['Cached_Property_Lookup'][strtolower($Column['Key_Alias'])];
									$Column['Key_Value_Type'] = &$Column['Key']['Cached_Value_Type'];
									$Column['Key_Data_Name'] = &$Column['Key']['Data_Name'];
									$Column['Key_Column'] = &$Column['Key_Data_Name'];
								}
							
								// Get SQL type for column
								$Column['Key_Value_Type_Alias'] = &$Column['Key_Value_Type']['Alias'];
								$Column['SQL_Type'] = &$Types_To_SQL_Types[strtolower($Column['Key_Value_Type_Alias'])];
							}
						}
					
						// Copy valid data to staging columns...
						{
							$Target_Type_Staging_Column_Name = &$Target['Columns'][0]['Staging_Name'];
							if ($New_Property_Relation)
								$Target_Value_Type_Key_Column_Name = &$Target['Columns'][0]['Key_Column'];
							switch (strtolower($New_Property_Relation))
							{
								case COMMUTATIVE:
									// TODO - test
									$Attachment_Table_Name = &New_String($Table_Prefix . $Cached_New_Property_Attachment_Type['Data_Name']);
									$Target_Type_Table_Name = &New_String('Attachment');
									$Target_Value_Type_Table_Name = &New_String('Type');
									$Target_Value_Type_2_Table_Name = &New_String('Type');
									$Target_Type_Staging_Column_2_Name = &$Target['Columns'][1]['Staging_Name'];
									$Target_Value_Type_2_Key_Column_Name = &$Target['Columns'][1]['Key_Column'];
									break;
								case MANY_TO_MANY:
									$Attachment_Table_Name = &New_String($Table_Prefix . $Cached_New_Property_Attachment_Type['Data_Name']);
									$Target_Type_Table_Name = &New_String('Attachment');
									$Target_Value_Type_Table_Name = &New_String('Value_Type');
									$Target_Value_Type_2_Table_Name = &New_String('Type');
									$Target_Type_Staging_Column_2_Name = &$Target['Columns'][1]['Staging_Name'];
									$Target_Value_Type_2_Key_Column_Name = &$Target['Columns'][1]['Key_Column'];
									break;
								case ONE_TO_MANY:
									$Target_Type_Table_Name = &New_String('Value_Type');
									$Target_Value_Type_Table_Name = &New_String('Type');
									break;
								case MANY_TO_ONE:
									$Target_Type_Table_Name = &New_String('Type');
									$Target_Value_Type_Table_Name = &New_String('Value_Type');
									break;
								default:
									$Target_Type_Table_Name = &New_String('Type');
									$Target_Value_Type_Table_Name = &New_String('Type');
									break;
							}
						
							$Source_Type_Column_Name = &$Source['Columns'][0]['Name'];
							if ($Original_Property_Relation)
								$Source_Value_Type_Key_Column_Name = &$Source['Columns'][0]['Key_Column'];
							else
								$Source_Value_Type_Key_Column_Name = &$Source['Columns'][0]['Name'];
							switch (strtolower($Original_Property_Relation))
							{
								case COMMUTATIVE:
									// TODO - test
									$Attachment_Table_Name = &New_String($Table_Prefix . $Cached_Original_Property_Attachment_Type['Data_Name']);
									$Source_Type_Table_Name = &New_String('Attachment');
									$Source_Value_Type_Table_Name = &New_String('Type');
									$Source_Value_Type_2_Table_Name = &New_String('Type');
									$Source_Type_Column_Name_2 = &$Source['Columns'][1]['Name'];
									$Source_Value_Type_2_Key_Column_Name = &$Source['Columns'][1]['Key_Column'];
									break;
								case MANY_TO_MANY:
									$Attachment_Table_Name = &New_String($Table_Prefix . $Cached_Original_Property_Attachment_Type['Data_Name']);
									$Source_Type_Table_Name = &New_String('Attachment');
									$Source_Value_Type_Table_Name = &New_String('Value_Type');
									$Source_Value_Type_2_Table_Name = &New_String('Type');
									$Source_Type_Column_Name_2 = &$Source['Columns'][1]['Name'];
									$Source_Value_Type_2_Key_Column_Name = &$Source['Columns'][1]['Key_Column'];
									break;
								case ONE_TO_MANY:
									$Source_Type_Table_Name = &New_String('Value_Type');
									$Source_Value_Type_Table_Name = &New_String('Type');
									break;
								case MANY_TO_ONE:
									$Source_Type_Table_Name = &New_String('Type');
									$Source_Value_Type_Table_Name = &New_String('Value_Type');
									break;
								default:
									$Source_Type_Table_Name = &New_String('Type');
									break;
							}
						
							$Attachment_Type_Item_Order = 0;
						
							$Min_Type = &Min_Parent_Type($Cached_Original_Property_Type, $Cached_New_Property_Type);
						
							// Get Min Value Type
							if (!$New_Property_Relation && !$Original_Property_Relation)
								$Min_Value_Type = &$Cached_New_Property_Value_Type;
							else
								$Min_Value_Type = &Min_Parent_Type($Cached_Original_Property_Value_Type, $Cached_New_Property_Value_Type);
						
							if (!is_null($Min_Value_Type))
							{
								foreach ($Min_Value_Type['All_Cached_Child_Types'] as &$Min_Value_Type_Child_Type)
								{
									$Min_Value_Type_Child_Type_Data_Name = &$Min_Value_Type_Child_Type['Data_Name'];
									$Min_Value_Type_Child_Type_Table = &New_String($Table_Prefix . $Min_Value_Type_Child_Type_Data_Name);
								
									$Value_Type_Table_Name = &$Min_Value_Type_Child_Type_Table;
								
									if (!is_null($Min_Type))
									{
										foreach ($Min_Type['All_Cached_Child_Types'] as &$Min_Type_Child_Type)
										{
											$Min_Type_Child_Type_Data_Name = &$Min_Type_Child_Type['Data_Name'];
											$Min_Type_Child_Type_Table = &New_String($Table_Prefix . $Min_Type_Child_Type_Data_Name);
										
											$Type_Table_Name = &$Min_Type_Child_Type_Table;
										
											// Check if changing to many-to-many or commutative
											if ( 
													(
														(strtolower($New_Property_Relation) == MANY_TO_MANY) 
															|| 
														(strtolower($New_Property_Relation) == COMMUTATIVE) 
													)
														 &&
													(
														(strtolower($Original_Property_Relation) != MANY_TO_MANY)
															&& 
														(strtolower($Original_Property_Relation) != COMMUTATIVE)
													)
												)
											{
												// Only create attachment items for non-simple original values
												if ($Original_Property_Relation)
												{
													$Type_Table_Query =
														'SELECT' . ' ' .
															'`'  . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_2_Table_Name) . '`' . '.' .
																'`'  . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_2_Key_Column_Name) . '`' . ' ' .
																'AS' . ' ' . '`' . 'Type_Item_Key_Value' . '`' . ',' . ' ' .
															'`'  . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_Table_Name) . '`' . '.' .
																'`'  . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_Key_Column_Name) . '`' . ' ' .
																'AS' . ' ' . '`' . 'Value_Type_Item_Key_Value' . '`' . ' ' .
														'FROM' . ' ' .
															('`'  . mysqli_real_escape_string($Database['Link'], $Min_Type_Child_Type_Table) . '`') . ' ' . 'AS' . ' ' . ('`' . 'Type' . '`') . ',' . ' ' .
															('`'  . mysqli_real_escape_string($Database['Link'], $Min_Value_Type_Child_Type_Table) . '`') . ' ' . 'AS' . ' ' . ('`' . 'Value_Type' . '`') . ' ' .
														'WHERE' . ' ' .
															'`'  . mysqli_real_escape_string($Database['Link'], $Source_Value_Type_Table_Name) . '`' . '.' .
																'`'  . mysqli_real_escape_string($Database['Link'], $Source_Value_Type_Key_Column_Name) . '`' . ' ' .
																'=' . ' ' .
															'`'  . mysqli_real_escape_string($Database['Link'], $Source_Type_Table_Name) . '`' . '.' .
																'`'  . mysqli_real_escape_string($Database['Link'], $Source_Type_Column_Name) . '`';
										
													$Type_Table_Result = &Query($Database, $Type_Table_Query);
													while ($Type_Table_Row = mysqli_fetch_assoc($Type_Table_Result))
													{
														$Type_Item_Key_Value = $Type_Table_Row['Type_Item_Key_Value'];
														$Value_Type_Item_Key_Value = $Type_Table_Row['Value_Type_Item_Key_Value'];
											
														$Attachment_Type_Item_Order++;
									
														$Attachment_Type_Item_ID = &Generate_ID($Database);
											
														// Fetch creation date
														// TODO @feature-database Consider using Create_Item()/Save_Item() to create a new attachment item, but figure out how to handle properties that aren't in cache yet (maybe create+save and then do a raw UPDATE)
														$Current_Unix_Time = &New_Number(time());
														$Current_SQL_Time = date('Y-m-d H:i:s', $Current_Unix_Time);
													
														$Cached_New_Property_Attachment_Type_Data_Name = &$Cached_New_Property_Attachment_Type['Data_Name'];
														$Cached_New_Property_Attachment_Type_Table_Name = &New_String($Table_Prefix . $Cached_New_Property_Attachment_Type_Data_Name);
													
														$Insert_Query =
															'INSERT INTO' . ' ' . '`' .
																mysqli_real_escape_string($Database['Link'], $Cached_New_Property_Attachment_Type_Table_Name) . '`' . ' ' .
															'SET' . ' ' .
																'`' . 'ID' . '`' . ' ' . '=' . ' ' . '\'' . mysqli_real_escape_string($Database['Link'], $Attachment_Type_Item_ID) . '\'' . ',' . ' ' .
																'`' . 'Modified' . '`' . ' ' . '=' . ' ' . '\'' . mysqli_real_escape_string($Database['Link'], $Current_SQL_Time) . '\'' . ',' . ' ' .
																'`' . 'Created' . '`' . ' ' . '=' . ' ' . '\'' . mysqli_real_escape_string($Database['Link'], $Current_SQL_Time) . '\'' . ',' . ' ' .
																'`' . 'Order' . '`' . ' ' . '=' . ' ' . '\'' . mysqli_real_escape_string($Database['Link'], $Attachment_Type_Item_Order) . '\'' . ',' . ' ' .
																'`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Staging_Column_2_Name) . '`' . ' ' . '=' . ' ' . '\'' . mysqli_real_escape_string($Database['Link'], $Type_Item_Key_Value) . '\'' . ',' . ' ' .
																'`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Staging_Column_Name) . '`' . ' ' . '=' . ' ' . '\'' . mysqli_real_escape_string($Database['Link'], $Value_Type_Item_Key_Value) . '\'';
														Query($Database, $Insert_Query);
													}
													mysqli_free_result($Type_Table_Result);
												}
											}
											else
											{
												// Generate Copy Query
										
												// Update
												{
													$Update_Tables = &New_Array();
													$Update_Tables[] = (('`' . mysqli_real_escape_string($Database['Link'], $Type_Table_Name) . '`') . ' ' . 'AS' . ' ' . ('`' . 'Type' . '`'));
													if ($New_Property_Relation)
														$Update_Tables[] = (('`' . mysqli_real_escape_string($Database['Link'], $Value_Type_Table_Name) . '`') . ' ' . 'AS' . ' ' . ('`' . 'Value_Type' . '`'));
													if (
															(strtolower($Original_Property_Relation) == MANY_TO_MANY) 
																|| 
															(strtolower($Original_Property_Relation) == COMMUTATIVE) 
														)
														$Update_Tables[] = (('`' . mysqli_real_escape_string($Database['Link'], $Attachment_Table_Name) . '`') . ' ' . 'AS' . ' ' . ('`' . 'Attachment' . '`'));
													$Copy_Values_Query = 'UPDATE' . ' ' . implode(', ', $Update_Tables);
												}
										
												// Set
												{
													$Set_Parts = &New_Array();
													if ($New_Property_Relation)
														$Set_Parts[] =
															(('`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Table_Name) . '`') . '.' .
															('`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Staging_Column_Name) . '`') .' ' .
																'=' . ' ' .
															('`' . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_Table_Name) . '`') . '.' .
															('`' . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_Key_Column_Name) . '`'));
													else
														$Set_Parts[] =
															(('`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Table_Name) . '`') . '.' .
															('`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Staging_Column_Name) . '`') .' ' .
																'=' . ' ' .
															('`' . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_Table_Name) . '`') . '.' .
															('`' . mysqli_real_escape_string($Database['Link'], $Source_Value_Type_Key_Column_Name) . '`'));
													if (
															(strtolower($New_Property_Relation) == MANY_TO_MANY) 
																|| 
															(strtolower($New_Property_Relation) == COMMUTATIVE) 
														)
														$Set_Parts[] =
															(('`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Table_Name) . '`') . '.' .
															('`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Staging_Column_2_Name) . '`') .' ' .
																'=' . ' ' .
															('`' . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_2_Table_Name) . '`') . '.' .
															('`' . mysqli_real_escape_string($Database['Link'], $Target_Value_Type_2_Key_Column_Name) . '`'));
													$Copy_Values_Query .= ' SET' . ' ' . implode(', ', $Set_Parts);
												}
										
												// Where
												{
													// Only apply 'where' clauses to complex->complex property changes
													if ($Original_Property_Relation && $New_Property_Relation)
													{
														$Where_Parts = &New_Array();
														$Where_Parts[] = 
															(('`' . mysqli_real_escape_string($Database['Link'], $Source_Type_Table_Name) . '`') . '.' .
															('`' . mysqli_real_escape_string($Database['Link'], $Source_Type_Column_Name) . '`') . ' ' .
																'=' . ' ' .
															('`' . mysqli_real_escape_string($Database['Link'], $Source_Value_Type_Table_Name) . '`') . '.' .
															('`' . mysqli_real_escape_string($Database['Link'], $Source_Value_Type_Key_Column_Name) . '`'));
														if (
																(strtolower($Original_Property_Relation) == MANY_TO_MANY) 
																	|| 
																(strtolower($Original_Property_Relation) == COMMUTATIVE) 
															)
															$Where_Parts[] = 
																(('`' . mysqli_real_escape_string($Database['Link'], $Source_Type_Table_Name) . '`') . '.' .
																('`' . mysqli_real_escape_string($Database['Link'], $Source_Type_Column_Name_2) . '`') . ' ' .
																	'=' . ' ' .
																('`' . mysqli_real_escape_string($Database['Link'], $Source_Value_Type_2_Table_Name) . '`') . '.' .
																('`' . mysqli_real_escape_string($Database['Link'], $Source_Value_Type_2_Key_Column_Name) . '`'));
														$Copy_Values_Query .= ' WHERE' . ' ' . implode(' AND ', $Where_Parts);
													}
												}
										
												// Execute Query
												Query($Database, $Copy_Values_Query);
											}
										
										}
									}
								}
							}
						}
					
						// Delete original columns...
						{
							// Delete Original Columns for each source
							foreach ($Source['Columns'] as &$Column)
							{
								foreach ($Source['Type']['All_Cached_Child_Types'] as &$Source_Type_Child_Type)
								{
									// Generate table name for child type
									$Source_Type_Child_Type_Data_Name = &$Source_Type_Child_Type['Data_Name'];
									$Source_Type_Child_Type_Table = &New_String($Table_Prefix . $Source_Type_Child_Type_Data_Name);
						
									// Generate query
									$Delete_Column_Query =
										'ALTER TABLE'. ' ' . ('`' . mysqli_real_escape_string($Database['Link'], $Source_Type_Child_Type_Table) . '`') . ' ' . 
										'DROP COLUMN' . ' ' .
											('`' . mysqli_real_escape_string($Database['Link'], $Column['Name'])  . '`');
									
									// Execute query
									Query($Database, $Delete_Column_Query);
								}
							}
						}
				
						// Delete attachment type...
						{
							// If the property was a many-to-many, and isn't any longer, delete the attachment type.
							if ( 
									(
										(strtolower($Original_Property_Relation) == MANY_TO_MANY) 
											|| 
										(strtolower($Original_Property_Relation) == COMMUTATIVE) 
									)
										 &&
									(
										(strtolower($New_Property_Relation) != MANY_TO_MANY)
											&& 
										(strtolower($New_Property_Relation) != COMMUTATIVE)
									)
								)
							{
								// Remove property from attachment type's cached properties
								// TODO move this to its own function?
								unset($Cached_Original_Property_Attachment_Type['Cached_Property_Lookup'][strtolower($Original_Property_Alias)]);
								unset($Cached_Original_Property_Attachment_Type['Cached_Property_Lookup'][strtolower($Original_Property_Reverse_Alias)]);
								unset($Cached_Original_Property_Attachment_Type['All_Cached_Properties'][strtolower($Original_Property_Alias)]);
								unset($Cached_Original_Property_Attachment_Type['All_Cached_Properties'][strtolower($Original_Property_Reverse_Alias)]);
								unset($Cached_Original_Property_Attachment_Type['Cached_Specific_Properties'][strtolower($Original_Property_Alias)]);
								unset($Cached_Original_Property_Attachment_Type['Cached_Specific_Properties'][strtolower($Original_Property_Reverse_Alias)]);
								
								// delete attachment type
								$Cached_Original_Property_Attachment_Type_Alias = &$Cached_Original_Property_Attachment_Type['Alias'];
					
								$Attachment_Type_Command_String = '1 Type from Database where Alias = "' . mysqli_real_escape_string($Database['Link'], $Cached_Original_Property_Attachment_Type_Alias) . '"';
								$Attachment_Type_Command = &Parse_String_Into_Command($Attachment_Type_Command_String);
								$Attachment_Type_Item = &Get_Database_Item($Database, $Attachment_Type_Command);
					
								Delete_Item($Attachment_Type_Item);
							}
						}
					}
					
					// Finalize staging columns...
					{
						// Finalize Staging Columns for each target
						foreach ($Target['Columns'] as &$Column)
						{
							foreach ($Target['Type']['All_Cached_Child_Types'] as &$Target_Type_Child_Type)
							{
								// Generate table name for child type
								$Target_Type_Child_Type_Data_Name = &$Target_Type_Child_Type['Data_Name'];
								$Target_Type_Child_Type_Table = &New_String($Table_Prefix . $Target_Type_Child_Type_Data_Name);
							
								// Generate query
								$Rename_Column_Query =
									'ALTER TABLE' . ' ' . ('`' . mysqli_real_escape_string($Database['Link'], $Target_Type_Child_Type_Table) . '`') . ' ' . 
									'CHANGE COLUMN' . ' ' .
										(('`' . mysqli_real_escape_string($Database['Link'], $Column['Staging_Name'])  . '`') . ' ' .
										('`' . mysqli_real_escape_string($Database['Link'], $Column['Name'])  . '`') . ' ' .
											(mysqli_real_escape_string($Database['Link'], $Column['SQL_Type'])));
							
								// Execute query
								Query($Database, $Rename_Column_Query);
							}
						}
					}
				}
				break;
			}
		}
	}
	
	// Hacky way to unset attachment_type after structure stuff
	if (strtolower($Cached_Item_Type_Alias) == 'property')
	{
		if ($Item_Values['Relation'] == 'Many-To-One')
		{
			Set_Simple($Item, "Attachment_Type", null);
			unset($Item_Values['Attachment_Type']);
		}
	}

	// Save to database...
	
	if (!isset($Parameters['No_Row']) || !$Parameters['No_Row'])
	{
		// Fetch new ID from database if necessary
		// TODO @feature-database: should this be below within the No_Row check?
		if (!isset($Item['Data']['ID']))
			Set_Simple($Item, 'ID', Generate_ID($Database));
		$Item_ID = &$Item['Data']['ID'];
	
		$Cached_Item_Type_Data_Name = &$Cached_Item_Type['Data_Name'];
	
		// Set Modified Time
		$Current_Unix_Time = &New_Number(time());
		Set_Value($Item, 'Modified', $Current_Unix_Time);
		
		// Set Modified By
		if ($GLOBALS['Current_User_Item'])
			Set_Value($Item, 'Modified_By', $GLOBALS['Current_User_Item']);
	
		// Build query action for both new and saved items	
		if ($Item['Saved'])
		{
			// Generate query action to update existing row
			$Query_Action = 'UPDATE `' . $Cached_Item_Type_Data_Name . '`';
			$Query_Where = 'WHERE `ID` = \'' . mysqli_real_escape_string($Database['Link'], $Item_ID) . '\'';
		}
		else
		{		
			// Fetch creation date
			Set_Value($Item, 'Created', $Current_Unix_Time);
			
			// Set Created By
			if ($GLOBALS['Current_User_Item'])
				Set_Value($Item, 'Created_By', $GLOBALS['Current_User_Item']);
		
			// Get next order index
			// TODO @feature-database: Is this something we should use MySQL to do on the column level? 
			$Order_Query = 'SELECT MAX(`Order`) AS Max_Order FROM `' . mysqli_real_escape_string($Database['Link'], $Cached_Item_Type_Data_Name) . '`';
			$Order_Result = Query($Database, $Order_Query);
			$Order_Row = mysqli_fetch_assoc($Order_Result);
			mysqli_free_result($Order_Result);
			$Item_Order = $Order_Row['Max_Order'] + 1;
			$Item['Data']['Order'] = &$Item_Order;
			
			// Generate query action to insert a new row
			$Query_Action = 'INSERT INTO `' . mysqli_real_escape_string($Database['Link'], $Cached_Item_Type_Data_Name) . '`';
		}
	
		// Commit properties...
		$Set_Parts = array();
		foreach ($Cached_Item_Type['All_Cached_Properties'] as &$Cached_Property)
		{
			$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
			$Cached_Property_Value_Type_Alias = &$Cached_Property_Value_Type['Alias'];
			
			if (array_key_exists('Relation', $Cached_Property))
				$Cached_Property_Relation = &$Cached_Property['Relation'];
			else 
				$Cached_Property_Relation = &New_String('');

			// Build set statements for simple values
			if (Is_Simple_Type($Cached_Property_Value_Type))
			{
				$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];
			
				// If the item has not yet been saved, or if this value has changed, set the item value in the database
				if (!$Item['Saved'] || (isset($Item['Original_Values']) && array_key_exists($Cached_Property_Data_Name, $Item['Original_Values'])))
				{
					// Get item value
					$Memory_Value = &$Item['Data'][$Cached_Property_Data_Name];
					
					// TODO @error-database: If value is wrong kind of data, throw an error
					// TODO @feature-database: convert keys if needed (i.e. Alias to ID)
					// TODO @feature-database: validate references against database (i.e. if key doesn't map to an item)
					
					// Dereference array values, or catch mistyped values...
					
					// Throw exception for not set. 
					// TODO - remove from production
					if (Is_Not_Set($Memory_Value))
					{
						traverse($Item);
						throw new Exception('Language Error - shouldnt be any Not_Set_Items in simple value item data --- ' . $Cached_Property_Data_Name);
						exit();
					}
						
					// Dereference dates or throw exceptions for other kinds.
					if (Is_Item($Memory_Value))
					{	
						$Memory_Value_Cached_Item_Type = $Memory_Value['Cached_Specific_Type'];
						$Memory_Value_Cached_Item_Type_Alias = $Memory_Value_Cached_Item_Type['Alias'];
						switch(strtolower($Memory_Value_Cached_Item_Type_Alias))
						{
							case 'date':
							case 'date_time':
							case 'time':
								if ($Memory_Value['Data']['Simple_Value'] !== false)
									$Memory_Value = &$Memory_Value['Data']['Simple_Value'];
								else
									$Memory_Value = &New_Null();
								break;
							default:
								traverse($Memory_Value);
								throw new Exception('Language Error - ' . $Memory_Value_Cached_Item_Type_Alias . ' is wrong kind of memory item for simple value data');
								break;
						}
					}
					
					// If this is a password value type, store a hashed value to SQL (including null values)
					if (strtolower($Cached_Property_Value_Type_Alias) == 'password')
					{
						if (is_null($Memory_Value))
							$Password_Value = &New_String('');
						else
							$Password_Value = &$Memory_Value;
							
						if (!isset($Parameters['No_Hash']) || !$Parameters['No_Hash'])
							$SQL_Value = &New_String(password_hash($Password_Value, PASSWORD_DEFAULT));
						else
							$SQL_Value = &$Password_Value;
					}
					
					// Otherwise, format non-null values to SQL value
					else if (!is_null($Memory_Value))
					{
						// Translate memory value to a SQL value for the set query (by copying it)
						switch (strtolower($Cached_Property_Value_Type_Alias))
						{
							// Time
							case 'time':
								$SQL_Value = &New_String(date('H:i:s', $Memory_Value));
								break;
						
							// Date
							case 'date':
								$SQL_Value = &New_String(date('Y-m-d', $Memory_Value));
								break;
						
							// Date Time
							case 'date_time':
								$SQL_Value = &New_String(date('Y-m-d H:i:s', $Memory_Value));
								break;
						
							// Boolean
							case 'boolean':
								if ($Memory_Value)
									$SQL_Value = &New_Number(1);
								else
									$SQL_Value = &New_Number(0);
								break;
						
							// Other (i.e. Text, Number, Item)
							default:
								$SQL_Value = &$Memory_Value;
								break;
						}
					}
					
					// Keep null value as null.
					else
						$SQL_Value = &$Memory_Value;
				
					// Generate SQL set statement
					if (is_null($SQL_Value))
						$Set_Part = ('`' . mysqli_real_escape_string($Database['Link'], $Cached_Property_Data_Name) . '`') . ' = ' . 'NULL';
					else
						$Set_Part = ('`' . mysqli_real_escape_string($Database['Link'], $Cached_Property_Data_Name) . '`') . ' = ' . ('\'' . mysqli_real_escape_string($Database['Link'], $SQL_Value) . '\'');
					
					// Store set part
					$Set_Parts[] = &$Set_Part;
					unset($Set_Part);
				}
			}
		
			// Build SQL statements for complex, many-to-one values.
			elseif (strtolower($Cached_Property_Relation) == MANY_TO_ONE)
			{				
// 				global $Debug_Values;
				$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];
			
				// If the item has not yet been saved, or if this value has changed, set the item value in the database
				if (!$Item['Saved'] || (array_key_exists('Original_Values', $Item) && array_key_exists($Cached_Property_Data_Name, $Item['Original_Values'])))
				{
					// If the value exists in the item data, translate it to a sql value.
					if (array_key_exists($Cached_Property_Data_Name, $Item['Data']))
					{
// 						$Debug_Values['test']++;
						// Get item value
						$Memory_Value = &$Item['Data'][$Cached_Property_Data_Name];
					
						// If is item, dereference to key value.
						// TODO - throw exception for simple values that are as items.
						 if (Is_Item($Memory_Value))
						{	
							// Get key property of this item properties value type
							$Memory_Value_Key_Property_Alias = &$Cached_Property['Key'];
							$Memory_Value_Key_Property = &$Cached_Property_Value_Type['Cached_Property_Lookup'][strtolower($Memory_Value_Key_Property_Alias)];
							// Get data name of key property
							$Memory_Value_Key_Property_Data_Name = &$Memory_Value_Key_Property['Data_Name'];
							// Get key value of item memory value
							$Memory_Value_Key_Value = &$Memory_Value['Data'][$Memory_Value_Key_Property_Data_Name];
							// Store key value
							$SQL_Value = &$Memory_Value_Key_Value;
						}

						// Convert not-set to null.
						else if (Is_Not_Set($Memory_Value))
							$SQL_Value = &New_Null();
							
						else if (!$Memory_Value)
						{	
							// TODO this is should throw an error because it's an indication the code is awry, and it happens, so the code is awry, but I let it go for today.
							$SQL_Value = &New_Null();
						}

						// Throw exception for arrays that are neither items nor not-sets.
						// TODO - remove from production, or rewrite for public
						else if (is_array($Memory_Value))
							throw new Exception('Language Error - shouldnt be any cached_types/not_sets/etc in Item data');	
						
						// Throw exception for null.
						// TODO - remove from production, or rewrite for public
						else if (is_null($Memory_Value))
						{
							traverse($Cached_Property);
							traverse($Item);
							throw new Exception('Language Error - shouldnt be any null items for complex value data ');
						}
				
						// TODO @error-database: Verify key
						else
							$SQL_Value = &$Memory_Value;
					}

					// If the value is not set in the item data, set the sql value to null.
					// TODO - is this right for non-existing data array keys? or should this process do nothing and use the sql default?			
					else
						$SQL_Value = &New_Null();
				
					// Generate SQL set statement
					if (is_null($SQL_Value))
					{
						$Set_Part = ('`' . mysqli_real_escape_string($Database['Link'], $Cached_Property_Data_Name) . '`') .
							' = ' .
							'NULL';
					}
					else
					{
						$Set_Part = ('`' . mysqli_real_escape_string($Database['Link'], $Cached_Property_Data_Name) . '`') .
							' = ' .
							('\'' . mysqli_real_escape_string($Database['Link'], $SQL_Value) . '\'');
					}
				
					// Store set part.					
					$Set_Parts[] = &$Set_Part;
					unset($Set_Part);
				}
			}
		}	
		// If any set parts have been created, merge them into a string
		if (count($Set_Parts))
			$Set_String = 'SET ' . implode(', ', $Set_Parts);
	
		// Generate save query for unsaved items or saved items where values have changed
		// TODO @feature-database these checks could be cleaner here, above and below
		if (!$Item['Saved'] || ($Item['Saved'] && count($Set_Parts)))
		{
			// Generate save query
			$Save_Query = $Query_Action;
			if (isset($Set_String))
				$Save_Query .= ' ' . $Set_String;
			if (isset($Query_Where))
				$Save_Query .= ' ' . $Query_Where;
			
			// Execute save query
			Query($Database, $Save_Query);
		}
		
		// Mark item as saved
		$Item['Saved'] = &New_Boolean(true);
	}

	// Update cache...
	// TODO update cache inline above?
	if (!isset($Parameters['No_Cache']) || !$Parameters['No_Cache'])
	{
		$Cached_Item_Type_Alias = &$Cached_Item_Type['Alias'];
		switch (strtolower($Cached_Item_Type_Alias))
		{	
			// TODO: cleanup, maybe just parameter to excuse core types....
			// TODO - clean expired cache data from çache 
			// Type
			case 'type':
			{
				// TODO - remove expired types from cache.
				
				// Localize type data
				$Type = &Copy_Array($Item['Data']);
				if (array_key_exists('Parent_Type', $Type))
					$Type['Parent_Type'] = &As_Key($Type['Parent_Type']);
				if (array_key_exists('Status', $Type))
					$Type['Status'] = &As_Key($Type['Status']);
				
				// If the type is not in the database cache, add it
				if (!isset($Database['Cached_Type_Lookup'][strtolower($Type['Alias'])]))
					$Cached_Type = &Add_Type_To_Cache($Database, $Type);
				
				break;
			}
			
			// Property
			case 'property':
			{
				// Localize property data
				$Property_Values = &Copy_Array($Item['Data']);
				if (array_key_exists('Type', $Property_Values))
					$Property_Values['Type'] = &As_Key($Property_Values['Type']);
				if (array_key_exists('Value_Type', $Property_Values))
					$Property_Values['Value_Type'] = &As_Key($Property_Values['Value_Type']);
				if (array_key_exists('Attachment_Type', $Property_Values))
					$Property_Values['Attachment_Type'] = &As_Key($Property_Values['Attachment_Type']);
				if (array_key_exists('Status', $Property_Values))
					$Property_Values['Status'] = &As_Key($Property_Values['Status']);
				
				// If alias, type, or value_type, or attachment_type has changed, remove the original property from the cache.
				// TODO - should it always remove the property from cache? 
				if (array_key_exists('Original_Values', $Item) && 
						(
							array_key_exists('Alias', $Item['Original_Values']) 
								|| 
							array_key_exists('Type', $Item['Original_Values']) 
								|| 
							array_key_exists('Value_Type', $Item['Original_Values'])
								|| 
							array_key_exists('Attachment_Type', $Item['Original_Values'])
						)
					)
				{
					// Localize database type lookup 
					$Cached_Type_Lookup = &$Database['Cached_Type_Lookup'];
					
					// Get original type lookup
					// TODO - I think this is already orchestrated above.
					if (array_key_exists('Type', $Item['Original_Values']))
						$Original_Property_Type_Lookup = &New_String(strtolower(As_Key($Item['Original_Values']['Type'])));
					else
						$Original_Property_Type_Lookup = &New_String(strtolower(As_Key($Property_Values['Type'])));
						
					// Get original property lookup
					if (array_key_exists('Alias', $Item['Original_Values']))
						$Original_Property_Property_Lookup = &New_String(strtolower($Item['Original_Values']['Alias']));
					else
						$Original_Property_Property_Lookup = &New_String(strtolower($Property_Values['Alias']));					
						
					// Remove original property from cache, if it is in the cache.
					if (
						array_key_exists($Original_Property_Type_Lookup, $Cached_Type_Lookup) 
							&& 
						array_key_exists($Original_Property_Property_Lookup, $Cached_Type_Lookup[$Original_Property_Type_Lookup]['Cached_Property_Lookup'])
						)
					{
						$Cached_Original_Property = $Cached_Type_Lookup[$Original_Property_Type_Lookup]['Cached_Property_Lookup'][$Original_Property_Property_Lookup];
						Remove_Property_From_Cache($Database, $Cached_Original_Property);						
					}
				}

				// Add property to cache
				Add_Property_To_Cache($Database, $Property_Values);
				
				break;				
			}
		}
	}
	
	// After specific changes, refactor data as necessary.
	{
		// TODO - Are there any cases for when the ID has changed for which we would refactor? 
		
		switch (strtolower($Cached_Item_Type_Alias))
		{
			// Handles changes in types.
			case 'type':
				// If the alias has changed, update the alias-based references to this type
				if (array_key_exists('Original_Values', $Item) &&array_key_exists('Alias', $Item['Original_Values']) )
				{	
					// Get original alias
					$Item_Original_Alias = &$Item['Original_Values']['Alias'];
						
					// Search for properties that are matching this type by an alias key. 
					// TODO - split one-to-many into one-to-many or commutative
					$Affected_Property_Command_String = &New_String(
							'Property' . ' ' . 'where'  . ' ' . 
								'(' .
									'Relation' . ' ' . '=' . ' ' . ('"' . 'Many-To-One' . '"') . ' ' . 
										'and' . ' ' .									
									'Value_Type' . ' ' . '=' . ' ' . ('"' . 'Type' . '"') . ' ' . 
										'and' . ' ' .
									'Key' . ' ' .  '=' . ' ' .  ('"' . 'Alias' . '"')  . 
								')' . ' ' .  
									'or' . ' ' . 
								'(' .
									'(' . 
										'Relation' . ' ' . '=' . ' ' . ('"' . 'One-To-Many' . '"') . ' ' . 
												'or' . ' ' .
										'Relation' . ' ' . '=' . ' ' . ('"' . 'Commutative' . '"') . 
									')' . ' ' .
										'and' . ' ' .
									'Type' . ' ' . '=' . ' ' . ('"' . 'Type' . '"') . ' ' . 
										'and' . ' ' .
									'Key' . ' ' .  '=' . ' ' .  ('"' . 'Alias' . '"')  . 
								')' . ' ' .  
									'or' . ' ' . 
								'(' . 
									'Relation' . ' ' . '=' . ' ' . ('"' . 'Many-To-Many' . '"') . ' ' . 
										'and' . ' ' . 
									'Type' . ' ' . '=' . ' ' . ('"' . 'Type' . '"') . ' ' . 
										'and' . ' ' .
									 'Reverse_Key' . ' ' . '=' . ' ' . ('"' . 'Alias' . '"') . 
								')'
						);
					$Affected_Property_Command =  &Parse_String_Into_Command($Affected_Property_Command_String);
					$Affected_Property_Item = &Get_Database_Item($Database, $Affected_Property_Command);
					
					// For each of these properties, refactor any affected items to the new alias
					while (!array_key_exists('End_Of_Results', $Affected_Property_Item) || !$Affected_Property_Item['End_Of_Results'])
					{	
						// Perform refactoring for forward direction matches...
						if (
								$Affected_Property_Item['Data']['Value_Type'] == 'Type' 
									&& 						
								($Affected_Property_Item['Data']['Relation'] == 'Many-To-One' || $Affected_Property_Item['Data']['Relation'] == 'Many-To-Many') 
									 && 
								$Affected_Property_Item['Data']['Key'] == 'Alias'
							)
						{
							// Get the item type alias.
							$Affected_Item_Type_Alias = &$Affected_Property_Item['Data']['Type'];

							// Get affected property alias.
							$Affected_Property_Alias = &$Affected_Property_Item['Data']['Alias'];

							// Get affected items. 
							// TODO: should the alias be escaped?
							$Affected_Item_Command_String = &New_String(
									$Affected_Item_Type_Alias . ' ' .  'where' . ' ' . $Affected_Property_Alias . ' ' . '=' . ' ' .  ('"' . mysqli_real_escape_string($Database['Link'], $Item_Original_Alias) . '"')
								);
							$Affected_Item_Command = &Parse_String_Into_Command($Affected_Item_Command_String);
							$Affected_Item = &Get_Database_Item($Database, $Affected_Item_Command);
						
							// For each matched item, set the affected property to the new type alias.
							while (!array_key_exists('End_Of_Results', $Affected_Item) || !$Affected_Item['End_Of_Results'])
							{
								Set_Value($Affected_Item, $Affected_Property_Alias, $Item_Alias);
								Save_Item($Affected_Item, Array('No_Structure'=> true));
								Move_Next($Affected_Item);
							}
						}
						
						// Perform refactoring for reverse direction matches...
						if (
								$Affected_Property_Item['Data']['Type'] == 'Type'
									&&
								(
									(
										($Affected_Property_Item['Data']['Relation'] == 'One-To-Many' || $Affected_Property_Item['Data']['Relation'] == 'Commutative'))
											 && 
										$Affected_Property_Item['Data']['Key'] == 'Alias'
									)
										||
									(
										$Affected_Property_Item['Data']['Relation'] == 'Many-To-Many'
											&&
										$Affected_Property_Item['Data']['Reverse_Key'] == 'Alias'
									)
								)
						{
							// Get the item type alias.
							if ($Affected_Property_Item['Data']['Relation'] == 'Commutative')
								$Affected_Item_Type_Alias = &$Affected_Property_Item['Data']['Type'];
							else
								$Affected_Item_Type_Alias = &$Affected_Property_Item['Data']['Value_Type'];							

							// Get affected property alias.
							// TODO - handle commutative - i forget how they go with aliases.
							$Affected_Property_Alias = &$Affected_Property_Item['Data']['Reverse_Alias'];
							
							// Get affected items. 
							$Affected_Item_Command_String = &New_String(
									$Affected_Item_Type_Alias . ' ' .  'where' . ' ' . $Affected_Property_Alias . ' ' . '=' . ' ' .  ('"' . mysqli_real_escape_string($Database['Link'], $Item_Original_Alias) . '"')
								);
							$Affected_Item_Command = &Parse_String_Into_Command($Affected_Item_Command_String);
							$Affected_Item = &Get_Database_Item($Database, $Affected_Item_Command);
						
							// For each matched item, set the affected property to the new type alias.
							while (!array_key_exists('End_Of_Results', $Affected_Item) || !$Affected_Item['End_Of_Results'])
							{
								Set_Value($Affected_Item, $Affected_Property_Alias, $Item_Alias);
								Save_Item($Affected_Item, Array('No_Structure'=> true));
								Move_Next($Affected_Item);
							}
						}
						
						// Move to the next property.
						Move_Next($Affected_Property_Item);
					}				
				}
		}
	}
	
	// Clean up item
	if ($Item['Saved'] && !isset($Parameters['No_Cleanup']) || !$Parameters['No_Cleanup'])
		unset($Item['Original_Values']);

}

?>