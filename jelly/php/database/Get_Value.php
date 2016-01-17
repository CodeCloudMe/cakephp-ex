<?php

// Get Value

// TODO @feature-database: implement Query clause (security risk?)
// TODO: implement Commutative
// TODO: implement Attachment

// TODO @core-language: substitute values

function &Get_Value(&$Item, &$Command, $Memory_Stack_Reference = null)
{
	// Verify item
	if (!Is_Item($Item))
		throw new Exception('Get_Value called without Item');
	
	// Localize Variables
	$Database = &$Item['Database'];
	
	// Get 'Name' tree
	$Name_Tree = &$Command['Clauses']['name']['Tree'];
	
	// Move up through Name tree to get the base name
	$Recursive_Name_Tree = &$Name_Tree;
	while (strtolower($Recursive_Name_Tree['Kind']) == 'operator')
	{
		$Last_Name_Tree = &$Recursive_Name_Tree;
		
		// Move up through left term
		$Recursive_Name_Tree = &$Recursive_Name_Tree['Terms'][1];
	}
	$Name_Tree_Base = &$Recursive_Name_Tree;
	$Name_Tree_Base_Value = &$Name_Tree_Base['Value'];
	
	// Get property
	$Property_Lookup = &$Name_Tree_Base_Value;

	global $Metadata_Properties;
	
	// Check definition properties
	// TODO - decide which ones get priority --- but I almost think that actual properties should get priority, and then these should be invoked via .metadata?
	if (!isset($Command['Clauses']['from']) || strtolower($Command['Clauses']['from']['Tree']['Value']) != 'database')
	{
		switch (strtolower($Name_Tree_Base_Value))
		{
			case 'namespace':
				if (!count($Item['References'])) { traverse($Item);throw new Exception ('you found me!');}
				$Item_Reference = &$Item['References'][count($Item['References']) - 1];
				$Value = &$Item_Reference['Namespace'];
				break;
				
			case 'values':
				$Value = &$Item;
				break;
			
			case 'variables':
				if (!count($Item['References'])) { traverse($Item);throw new Exception ('Tried to access variables of an item that does not have them, i.e. was not in context.'); }
				$Item_Reference = &$Item['References'][count($Item['References']) - 1];

				// TODO: references should always have Variables
				if (!isset($Item_Reference['Variables']))
					throw new Exception('// TODO: references should always have Variables'); 

				$Value = &$Item_Reference['Variables'];
				break;
			
			case 'specific_type':
			case 'type':	
				// TODO - So... if you try to get ___.Variables.Type, this will always catch it.  You can escape to values.type, but you can't set a variables type.
				$Cached_Type = &$Item['Cached_Specific_Type'];
				$Type_Command_String = &New_String('Type ' . $Cached_Type['Alias'] . ' from Database as Reference');
				$Type_Processed_Command = &Process_Command_String($Database, $Type_Command_String);
				$Type_Item = &$Type_Processed_Command['Chunks'][0]['Item'];
				$Value = &$Type_Item;
				break;
				
			case 'base_type':
				// TODO: this is totally wrong
				throw new exception("this is totally wrong");
				$Cached_Type = &$Item['Cached_Base_Type'];
				$Type_Command_String = &New_String('Type ' . $Cached_Type['Alias'] . ' as Reference');
				$Type_Command = &Parse_String_Into_Command($Type_Command_String);
				$Type_Item = &$Type_Command['Chunks'][0]['Item'];
				$Value = &$Type_Item;
				break;
			
			case 'specific_item':
				$Cached_Type = &$Item['Cached_Specific_Type'];
				$Item_Command_String = &New_String($Cached_Type['Alias'] . ' ' . $Item['Data']['ID'] . ' from Database as Reference');
				$Item_Command_Command = &Process_Command_String($Database, $Item_Command_String);
				$Specific_Item = &$Item_Command_Command['Chunks'][0]['Item'];
				$Value = &$Specific_Item;
				
				break;
			
			case 'is_attachment_value':
				// TODO - of arguable worth.
				if (isset($Item['Data']['Attachment_ID']))
					$Value = &New_Boolean(true);
				else
					$Value = &New_Boolean(false);
				
				break;
			
			case 'attachment_type':
				// TODO - this busts trying to get attachment types of a property.
				if (!isset($Item['Data']['Attachment_ID']))
					throw new exception("Trying to get attachment ID of a non-attachment value.");
				$Attachment_Type_Lookup = &$Item['Data']['Attachment_Type'];
				$Cached_Attachment_Type = &Get_Cached_Type($Database, $Attachment_Type_Lookup);
				$Attachment_Type_Command_String = &New_String('Type ' . $Cached_Attachment_Type['Alias'] . ' from Database as Reference');
				$Attachment_Type_Processed_Command = &Process_Command_String($Database, $Attachment_Type_Command_String);
				$Attachment_Type_Item = &$Attachment_Type_Processed_Command['Chunks'][0]['Item'];
				$Value = &$Attachment_Type_Item;
				
				break;
			
			case 'attachment_id':
				if (!isset($Item['Data']['Attachment_ID']))
					throw new exception("Trying to get attachment ID of a non-attachment value.");
				$Value = &$Item['Data']['Attachment_ID'];
				
				break;
				
			case 'attachment':
				if (!isset($Item['Data']['Attachment_ID']))
					throw new exception("Trying to get attachment ID of a non-attachment value.");
				$Attachment_Type_Lookup = &$Item['Data']['Attachment_Type'];
				$Cached_Attachment_Type = &Get_Cached_Type($Database, $Attachment_Type_Lookup);
				
				$Attachment_ID = &$Item['Data']['Attachment_ID'];
				
				$Attachment_Item_Command_String = &New_String($Cached_Attachment_Type['Alias'] . ' ' . $Attachment_ID . ' from Database as Reference');
				$Attachment_Item_Processed_Command_String = &Process_Command_String($Database, $Attachment_Item_Command_String);
				$Attachment_Item = &$Attachment_Item_Processed_Command_String['Chunks'][0]['Item'];
				
				$Value = &$Attachment_Item;
				
				break;
				
			case 'action':
			{
				// If no which argument is supplied, treat as normal property.
				if (!isset($Command['Clauses']['which']))
					break;

				// Generate list of parent type query				
				$Type_Action_Command_String_Type_Query = &New_String('');
				$Recursive_Item_Parent_Type = &$Item['Cached_Specific_Type'];
				while(isset($Recursive_Item_Parent_Type))
				{	
					$Recursive_Item_Parent_Type_Alias = &$Recursive_Item_Parent_Type['Alias'];
					
					// Add conjunction as necessary
					if ($Type_Action_Command_String_Type_Query)
						$Type_Action_Command_String_Type_Query = &New_String($Type_Action_Command_String_Type_Query . ' ' . 'or' . ' ');

					// Add parent type alias check
					$Type_Action_Command_String_Type_Query =  &New_String($Type_Action_Command_String_Type_Query . 'Type.Alias' . ' ' . '=' . ' ' . '"' .  $Recursive_Item_Parent_Type_Alias . '"');
					
					$Recursive_Item_Parent_Type = &$Recursive_Item_Parent_Type['Cached_Parent_Type'];
				}
				
				// Get which clause
				$Command_Which_Clause_Tree = &$Command['Clauses']['which']['Tree'];				

				// Get command string match property based on clause kind
				$Command_Which_Clause_Tree_Kind = &$Command_Which_Clause_Tree['Kind'];
				$Command_Which_Clause_Tree_Value = &$Command_Which_Clause_Tree['Value'];
				$Command_Which_Match_Value = &$Command_Which_Clause_Tree_Value;

				switch($Command_Which_Clause_Tree_Kind)
				{	
					case 'Variable':
					case 'Number':
						$Command_Which_Match_Property_Alias = 	&New_String('Alias');
						break;
					case 'Text':
						$Command_Which_Match_Property_Alias = 	&New_String('Name');					
						break;
					case 'Operator':
						throw new Exception('Complex which clauses not supported');
						break;
					default:
						traverse($Command_Which_Clause_Tree);
						throw new Exception('Unknown kind of which clause');
						exit;
						break;
				}

				// Generate command string to match type action of item according to which.
				$Type_Action_Command_String = &New_String('Type.Action' . ' ' .  
					'where' . ' ' .
						(('Type_Action' . '.' . $Command_Which_Match_Property_Alias) . ' ' . '=' . ' ' . '"' . $Command_Which_Match_Value . '"') . ' ' .
						'and' . ' ' .
						('(' . $Type_Action_Command_String_Type_Query . ')') . ' ' .
					'from' . ' ' . 'Database' . ' ' . 'as' . ' ' . 'Reference');
			
				// Process command string
				$Type_Action_Processed_Command = &Process_Command_String($Database, $Type_Action_Command_String, $Memory_Stack_Item);
			
				// Get processed item
				$Action_Item = &$Type_Action_Processed_Command['Chunks'][0]['Item'];
				
				// Store target metadata
				// TODO: clean up runtime-properties?
				// TODO - this was hard to find during a big cleanup idea. worried this isn't the right way to do it, exactly.
				Set_Value($Action_Item, 'Target', $Item);
				
				$Value = &$Action_Item;
			
				break;
			}
			
			case 'index':
				$Value = &$Item['Index'];
				break;
			
			case 'segment_count':
				if (!isset($Item['Data']['Segment_Count']))
					throw new exception("Trying to get Segment_Count when not in query result.");
				$Value = &$Item['Data']['Segment_Count'];
				
				break;
				
			case 'segment_by_value':
				if (!isset($Item['Data']['Segment_By_Value']))
					throw new exception("Trying to get Segment_By_Value when not in query result.");
				$Value = &$Item['Data']['Segment_By_Value'];
				
				break;
				
			case 'template':
			{
				// Require which....
				if (!isset($Command['Clauses']['which']['Tree']['Value']))
				{	
					traverse($Command);
					throw new Exception('Cannot currently get template without which');
				}

				// Get which				
				$Template_Which = &$Command['Clauses']['which']['Tree']['Value'];
				
				// Get cached template
				// TODO - Copy code from action to support different kinds o fwhich's
				// TODO - this totally dies if you have an incorrect alias.
				$Item_Cached_Specific_Type = &$Item['Cached_Specific_Type'];				
				$Cached_Item_Specific_Type_Template = &$Item_Cached_Specific_Type['Cached_Template_Lookup'][strtolower($Template_Which)];
				
				// Create a command string to retrieve the template from databae
				$Cached_Item_Specific_Type_Template_ID = &$Cached_Item_Specific_Type_Template['ID'];
				$Template_Command_String = 'Template' . ' ' . 'where' . ' ' . 'ID' . ' ' . '=' . ' ' . $Cached_Item_Specific_Type_Template_ID . ' ' . 'from' . ' ' . 'Database' . ' ' . 'as' . ' ' . 'Reference';
			
				// Process the command string		
				$Template_Processed_Command = &Process_Command_String($Database, $Template_Command_String, $Memory_Stack_Item);
			
				// Get processed item
				$Template_Item = &$Template_Processed_Command['Chunks'][0]['Item'];
				$Value = &$Template_Item;
				break;
			}
			
			case 'forward_property':
			case 'forward_properties':
			case 'property':
			case 'properties':
			case 'reverse_property':
			case 'reverse_properties':
			case 'attachment_property':
			case 'attachment_properties':
			case 'all_property':
			case 'all_properties':
			case 'item_forward_property':
			case 'item_forward_properties':
			case 'item_property':
			case 'item_properties':
			case 'item_reverse_property':
			case 'item_reverse_properties':
			case 'item_attachment_property':
			case 'item_attachment_properties':
			case 'item_all_property':
			case 'item_all_properties':
			{
				// Check if we are getting the current item's properties, or the properties of items of the current type
				if (strpos(strtolower($Name_Tree_Base_Value), 'item_') === 0)
				{
					$Property_Select = &New_String(substr($Name_Tree_Base_Value, strlen('item_')));
					$Item_Properties = true;
				}
				else
				{
					$Property_Select = &New_String($Name_Tree_Base_Value);
					$Item_Properties = false;
				}
				
				// Get which
				if (isset($Command['Clauses']['which']['Tree']['Value']))
					$Property_Which = &$Command['Clauses']['which']['Tree']['Value'];
					
				// Get order
				if (isset($Command['Clauses']['by']['Tree']['Value']))
					$Property_Order = &$Command['Clauses']['by']['Tree']['Value'];
								
				// Generate list of parent type query
				if ($Item_Properties)
					$Recursive_Item_Parent_Type = &Get_Cached_Type($Database, $Item['Data']['Alias']);
				else
					$Recursive_Item_Parent_Type = &$Item['Cached_Specific_Type'];
				$Recursive_Item_Parent_Type_Command_Parts = &New_Array();
				$Recursive_Item_Parent_Value_Type_Command_Parts = &New_Array();
				
				while(isset($Recursive_Item_Parent_Type))
				{	
					$Recursive_Item_Parent_Type_Alias = &$Recursive_Item_Parent_Type['Alias'];
					
					// TODO these used to be 'Type.Alias' but I don't think our technology supports that, can't remember.
					$Recursive_Item_Parent_Type_Command_Parts[] =  &New_String('Type' . ' ' . '=' . ' ' . '"' .  $Recursive_Item_Parent_Type_Alias . '"');
					$Recursive_Item_Parent_Value_Type_Command_Parts[] =  &New_String('Value_Type' . ' ' . '=' . ' ' . '"' .  $Recursive_Item_Parent_Type_Alias . '"');
					$Recursive_Item_Parent_Attachment_Type_Command_Parts[] =  &New_String('Attachment_Type' . ' ' . '=' . ' ' . '"' .  $Recursive_Item_Parent_Type_Alias . '"');

					// Get recursive parent type					
					$Recursive_Item_Parent_Type = &$Recursive_Item_Parent_Type['Cached_Parent_Type'];
				}

				// Generate type & value type checks.
				$Recursive_Item_Parent_Type_Command_Query = &New_String(implode(' ' . 'or' . ' ', $Recursive_Item_Parent_Type_Command_Parts));
				$Recursive_Item_Parent_Value_Type_Command_Query = &New_String(implode(' ' . 'or' . ' ', $Recursive_Item_Parent_Value_Type_Command_Parts));
				$Recursive_Item_Parent_Attachment_Type_Command_Query = &New_String(implode(' ' . 'or' . ' ', $Recursive_Item_Parent_Attachment_Type_Command_Parts));
				
				// Forward, All
				if (isset($Property_Which))
				{				
					$Property_Forward_Command_String = &New_String(
							'(' . 
								'(' . $Recursive_Item_Parent_Type_Command_Query . ')' . 
									' ' . 'and' . ' ' . 
								'(' . ('Property' . '.' . 'Alias') . ' ' . '=' . ' ' . ('"' . $Property_Which . '"') . ')' .
							')'
						);
				}
				else
				{
					$Property_Forward_Command_String = &New_String(
							'(' . $Recursive_Item_Parent_Type_Command_Query . ')'
						);
				}
				
				// Reverse, All				
				if (isset($Property_Which))
				{				
					$Property_Reverse_Command_String = &New_String(
							'(' . 
								'(' . $Recursive_Item_Parent_Value_Type_Command_Query . ')' . 
									' ' . 'and' . ' ' . 
								'(' . ('Property' . '.' . 'Reverse_Alias') . ' ' . '=' . ' ' . ('"' . $Property_Which . '"') . ')' .
							')'
						);
				}
				else
				{
					$Property_Reverse_Command_String = &New_String(
							'(' . 
								'(' . $Recursive_Item_Parent_Value_Type_Command_Query . ')' . 
									' ' . 'and' . ' ' . 
								'(' . ('Property' . '.' . 'Reverse_Alias') . ' ' . 'exists' . ')' . 
							')'
						);
				}				
				
				// Attachment, All 
				// TODO - if needed, split into forward and backwards, this is all-encompassing.
				if (isset($Property_Which))
				{
					$Property_Attachment_Command_String = &New_String(
							'(' .
								'(' . $Recursive_Item_Parent_Attachment_Type_Command_Query . ')' . 
									' ' . 'and' . ' ' . 
								'(' . 
									'(' . ('Property' . '.' . 'Alias') . ' ' . '=' . ' ' . ('"' . $Property_Which .  '"') . ')' . 
										' ' . 'or' . ' ' .
									'(' . ('Property' . '.' . 'Reverse_Alias') . ' ' . '=' . ' ' . ('"' . $Property_Which .  '"') . ')' . 
								')' .
							')'
						);
							
				}
				else
				{
					$Property_Attachment_Command_String = &New_String(
						'(' . $Recursive_Item_Parent_Attachment_Type_Command_Query . ')'
					);
				}
				
				// TODO: Support "Where" clauses			
				switch (strtolower($Property_Select))
				{
					// TODO - see if we need attachment in forward, see above todo about directional attachment queriess
					case 'forward_property':
					case 'forward_properties':
						$Property_Command_String = &New_String(
								'Property' . 
									' ' . 'where' . ' ' . 
										'(' . $Property_Forward_Command_String . ')'
							);
						break;
					case 'reverse_property':
					case 'reverse_properties':
						$Property_Command_String = &New_String(
								'Property' . 
									' ' . 'where' . ' ' . 
										'(' . $Property_Reverse_Command_String . ')'
							);
						break;
					case 'attachment_property':
					case 'attachment_properties':
						$Property_Command_String = &New_String(
								'Property' . 
									' ' . 'where' . ' ' . 
										'(' . $Property_Attachment_Command_String . ')'
							);
						break;
					case 'property':
					case 'properties':
					case 'all_property':
					case 'all_properties':
						$Property_Command_String = &New_String(
								'Property' . 
									' ' . 'where' . ' ' . 
										'(' . 
											'(' . $Property_Forward_Command_String . ')' . 
												' ' . 'or' . ' ' .
											'(' . $Property_Reverse_Command_String . ')' . 
												' ' . 'or' . ' ' . 
											'(' . $Property_Attachment_Command_String . ')' . 
										')'
							);
						break;
				}

				$Property_Command_String = &New_String($Property_Command_String . 
						' ' . 'from' . ' ' .
							'Database' . 
						' ' . 'as' . ' ' . 
							'Reference'
					);
				
				if (isset($Property_Order))
					$Property_Command_String = &New_String($Property_Command_String . 
							' ' . 'by' . ' ' .
								$Property_Order
						);
					
				// Parse properrty command string
				$Property_Command = &Parse_String_Into_Command($Property_Command_String);
				
				// If a where clause exists for the original command, place it or merge it into the property command.
				if (array_key_exists('where', $Command['Clauses']))
				{	
					$Property_Where_Clause = &$Command['Clauses']['where'];
					
					// If the property command has a where clause, merge the original where clause into it.
					if (array_key_exists('where', $Property_Command['Clauses']))
					{
						// Get property command where clause tree
						$Property_Command_Where_Clause_Tree = &$Property_Command['Clauses']['where']['Tree'];
						
						// Get original command where clause tree
						$Property_Where_Clause_Tree = &$Property_Where_Clause['Tree'];
						
						// Create merged where clause tree
						$Merged_Where_Clause_Tree = &New_Tree();
						$Merged_Where_Clause_Tree['Kind'] = &New_String('Operator');
						$Merged_Where_Clause_Tree['Value'] = &New_String('and');
						$Merged_Where_Clause_Tree['Terms'][] = &$Property_Command_Where_Clause_Tree;
						$Merged_Where_Clause_Tree['Terms'][] = &$Property_Where_Clause_Tree;
						
						// Set the merged where clause tree into the property command.
						/// TODO - I guess this should be a clause made from scratch, not just the tree being replaced.
						$Property_Command['Clauses']['where']['Tree'] = &$Merged_Where_Clause_Tree;
					}
					// Otherwise set the original where clause into this property command.
					else
						$Property_Command['where'] = &$Property_Where_Clause;
				}
				
				// Process the property command
				$Property_Processed_Command = &Process_Command($Database, $Property_Command, $Memory_Stack_Item);
				
				// Get processed item
				$Property_Item = &$Property_Processed_Command['Chunks'][0]['Item'];				
				$Value = &$Property_Item;
				break;
			}
				
			case 'item':
				// TODO
				break;
		}
	}
	
	
	if (!isset($Value))
	{
		// Check if item has the property
		if (!Has_Property($Item, $Property_Lookup) && !in_array(strtolower($Property_Lookup), $Metadata_Properties))
		{
			global $Date_Types;
			global $All_Date_Format_Strings;
	
			// If this item is a date/date_time/time type, and the command is date format string, return a formatted date value.
			if ((in_array(strtolower($Item['Cached_Base_Type']['Alias']), $Date_Types) && in_array(strtolower($Property_Lookup), $All_Date_Format_Strings)))
				$Value = &Jelly_Date_Format($Item['Data']['Simple_Value'], $Property_Lookup);	
				
			// TODO - quick merge of values and variables searching, may not be a good idea.
			// TODO - might be but is incompatible with our current implementation.  reconsider and uncomment in time.
	// 		else if (count($Item['References']) && Has_Property($Item['References'][count($Item['References']) - 1]['Variables'], $Property_Lookup))
	// 			$Value = &$Item['References'][count($Item['References']) - 1]['Variables']['Data'][$Property_Lookup];
			
			// Otherwise, return not set value.
			else
				$Value = &New_Not_Set();
				
			return $Value;		
			
			// TODO - older code here. Anything to salvage?
			/*
			// TODO: temp hack - Gracefully degrade for just 'items' (uh, usually variables)
			if ($Item['Cached_Base_Type']['Alias'] == 'Item')
				return New_Null();
			else
			{
				traverse($Command);
				traverse($Property_Lookup);
				throw new exception('Get_Value: Property does not exist: ' . $Property_Lookup);
			}
			*/
		}
	}
	
	// Check if we already got a value above
	if (isset($Value))
	{
		// Check if we are getting more properties
		if (strtolower($Name_Tree['Kind']) == 'operator')
		{
			// Copy Command to Progressive Command
			$Progressive_Command = &Copy_Command($Command);
			
			// Create progressive command
			$Progressive_Name_Tree = &Clone_Dot_Tree_Without_Base($Name_Tree);
			$Progressive_Command['Clauses']['name'] = &New_Clause();
			$Progressive_Command['Clauses']['name']['Name'] = &New_String("Name");
			$Progressive_Command['Clauses']['name']['Tree'] = &$Progressive_Name_Tree;
			
			// For 'Values', force "From Database"
			if (strtolower($Name_Tree_Base_Value) == 'values')
			{
				$Progressive_Command['Clauses']['from'] = &New_Clause();
				$Progressive_Command['Clauses']['from']['Name'] = &New_String("From");
				$Progressive_Command['Clauses']['from']['Tree'] = &New_Tree();
				$Progressive_Command['Clauses']['from']['Tree']['Kind'] = &New_String("Variable");
				$Progressive_Command['Clauses']['from']['Tree']['Value'] = &New_String("Database");
			}
			
// 				echo $Last_Name_Tree['Terms'][0]['Value'] . ",";
// 			$Find_Node = &$Progressive_Name_Tree;	
// 			while ($Find_Node['Kind'] == 'Operator')
// 			{
// 				echo $Find_Node['Terms'][0]['Value'] . ".";
// 				if ($Find_Node['Terms'][0]['Value'] == 'Is_Item')
// 				{
// 					echo "IS ITEMMMM";
// 					traverse($Item);
// 					traverse($Value);
// 				}
// 				$Find_Node = &$Find_Node['Terms'][1];
// 			}
// 			echo ($Find_Node['Value']) . ", ";
			
			// TODO @core-language: Substitute values here? (this was taken from below)
			$Value = &Get_Value($Value, $Progressive_Command, $Memory_Stack_Reference);
		}
		
		// Return the final value
		return $Value;
	}
	else
	{
		// Check if item has the requested property
		// TODO - I think this should happen here instead of in Process_Command, but I'm not sure.
		if (!Has_Property($Item, $Property_Lookup))
		{
			$Value = &New_Not_Set();
			return $Value;
		}

		$Cached_Property = &Get_Property($Item, $Property_Lookup);
		$Cached_Property_Type = &$Cached_Property['Cached_Type'];
		$Cached_Property_Type_Alias = &$Cached_Property_Type['Alias'];
		$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
		$Cached_Property_Value_Type_Alias = &$Cached_Property_Value_Type['Alias'];
		
		// Check if property is simple/complex
		if (Is_Simple_Type($Cached_Property_Value_Type))
		{
			// Simple Properties...

			// Get simple value
			$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];
			$Value = &$Item['Data'][$Cached_Property_Data_Name];
			
			// TODO - this might be more like something that should never happen.
			if (Is_Item($Value))
			{
				if (array_key_exists('Simple_Value', $Value['Data']))
				{
					$Value = &$Value['Data']['Simple_Value'];
				}
				else
					throw new Exception('Language Error - non simple value item set for a simple value type');
			}
			
			// Check if we are getting more properties
			$Create_Value_Item = &New_Boolean(true);
			$Treat_As_Simple_Value = &New_Boolean(true);
			if (strtolower($Name_Tree['Kind']) == 'operator')
			{
				// Copy Command to Progressive Command
				$Progressive_Command = &Copy_Command($Command);
			
				// Create progressive name tree
				$Progressive_Name_Tree = &Clone_Dot_Tree_Without_Base($Name_Tree);
					
				// Get simple property value
				$Progressive_Name_Tree_Value = &$Progressive_Name_Tree['Value'];
				
				switch (strtolower($Progressive_Name_Tree_Value))
				{
					// For meta-data property is_item, return true for automatic item types, or false
					// TODO - this code be wrapped up neater.
					case 'is_item':
						switch(strtolower($Cached_Property_Value_Type_Alias))
						{
							case 'date':
							case 'date_time':
							case 'time':
								$Value = &New_Boolean(true);
								break;
							default:
								$Value = &New_Boolean(false);
								break;
						}	
						break;

					default:
						// Return formatted date values, if this is a date format string
						global $Date_Types;
						global $All_Date_Format_Strings;						

						if (in_array(strtolower($Cached_Property_Value_Type_Alias), $Date_Types) && in_array(strtolower($Progressive_Name_Tree_Value), $All_Date_Format_Strings))
						{
							if (Is_Item($Value))
								$Simple_Value = &$Value['Data']['Simple_Value'];
							else $Simple_Value = &$Value;
							
							$Value = &Jelly_Date_Format($Simple_Value, $Progressive_Name_Tree_Value);
						}
							
						// TODO - how to handle this? 
						else
							throw new Exception('No support for this property of a simple value:' . ' ' . $Progressive_Name_Tree_Value);
						break;		
				}
			}
			else
			{	
				// Certain simple types, as well as simple types with specified templates, return items
				switch (strtolower($Cached_Property_Value_Type_Alias))
				{
					case 'date_time':
					case 'date':
					case 'time':
						$Create_Value_Item = &New_Boolean(true);
						if ($Value !== null)
							$Treat_As_Simple_Value = &New_Boolean(false);
						break;
					default:
						// TODO - hack, see process_command, can be improved.
						// TODO - commented out currently.
// 						if (isset($Command['Clauses']['as']))
// 							$Create_Value_Item = &New_Boolean(true);
						break;
				}
			}

			if ($Create_Value_Item)
			{					
				// Copy simple value
				$Copied_Simple_Value = $Value;
				
				// Create memory item of this value type
				$Value = &Create_Item($Database, $Cached_Property_Value_Type);
				
				// Copy in Treat_As_Simple_Value
				// TODO better way to flag this?
				$Value['Treat_As_Simple_Value'] = &$Treat_As_Simple_Value;
				
				// Set simple value to memory item
				Set_Value($Value, 'Simple_Value', $Copied_Simple_Value);
				
				// Cleanup simple value
				unset($Copied_Simple_Value);
				
				// Set data to know where the value came from
				$Value['Parent_Item'] = &$Item;
				$Value['Parent_Cached_Property'] = &$Cached_Property;
			}

			// Return value
			return $Value;
		}
		else
		{
			// Complex Properties...
		
		
// 			echo $Cached_Property['Alias'];
// 			traverse($Cached_Property);
// 			if ($Cached_Property['Alias'] == "Edited_Creator")
// 				traverse($Item);
			
			// Fetch property values
			$Cached_Property_Name = &$Cached_Property['Name'];
			$Cached_Property_Alias = &$Cached_Property['Alias'];
		
			$Cached_Property_Value_Type_Data_Name = &$Cached_Property_Value_Type['Data_Name'];
		
			$Cached_Property_Relation = &$Cached_Property['Relation'];
			$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];
		
			// Check if target key value is already an item (i.e. an array)
			// TODO we have a potential conflict between in-memory one-to-many values stored in items that might use the same data-name as regular forward properties
			// TODO - handle not set
			if (isset($Item['Data'][$Cached_Property_Data_Name]) && is_array($Item['Data'][$Cached_Property_Data_Name]))
			{
				// Get values using in-memory value
				// TODO: note this doesn't apply Where clauses, etc.
				$Value = &$Item['Data'][$Cached_Property_Data_Name];
				
				// TODO: bring this back sometime?
// 				// Store value's direct parent
// 				$Value['Parent'] = &$Item;
			
				// Check if we are getting more properties
				if (strtolower($Name_Tree['Kind']) == 'operator')
				{
					// Copy Command to Progressive Command
					$Progressive_Command = &Copy_Command($Command);
				
					// Create progressive command
					$Progressive_Name_Tree = &Clone_Dot_Tree_Without_Base($Name_Tree);
					
					// Check for constant properties
					$Progressive_Name_Tree_Value = &$Progressive_Name_Tree['Value'];
					switch (strtolower($Progressive_Name_Tree_Value))
					{
						// TODO - clean up things like this, Is_Item, etc.
						case 'is_item':
							$Value = &New_Boolean(true);
							break;
							
						default:
							$Progressive_Command['Clauses']['name'] = &New_Clause();
							$Progressive_Command['Clauses']['name']['Name'] = &New_String("Name");
							$Progressive_Command['Clauses']['name']['Tree'] = &$Progressive_Name_Tree;
			
							// TODO @core-language: Substitute values here?					
							$Value = &Get_Value($Value, $Progressive_Command, $Memory_Stack_Reference);
					}
				}
				else
				{
					if (is_array($Value))
					{
						// Set data to know where the value came from
						$Value['Parent_Item'] = &$Item;
						$Value['Parent_Cached_Property'] = &$Cached_Property;
					}
				}
				
				// Return the final value
				return $Value;
			}
			else
			{
				// TODO @core-language: Substitute unknowns in Where clause for values of type
		
				// TODO @core-language: 'Which' clause should get merged and passed through
				// TODO - if the item value lookup is "NULL", the progressive lookup doesn't trigger any exceptions, which could be ok, but is worth thinking about, in case we think of it differently from "Not Found"
			
				switch (strtolower($Cached_Property_Relation))
				{
					case COMMUTATIVE: 
					{	
						// Get key
						$Cached_Property_Key_Lookup = &$Cached_Property['Key'];
						
						// Get key property
						$Cached_Property_Type_Key_Cached_Property = &$Cached_Property_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Key_Lookup)];

						// Get key property alias
						$Cached_Property_Type_Key_Alias = &$Cached_Property_Type_Key_Cached_Property['Alias'];
						
						// Get key property data name
						$Cached_Property_Type_Key_Data_Name = &$Cached_Property_Type_Key_Cached_Property['Data_Name'];

						// Get item key value
						$Item_Key_Value = &$Item['Data'][$Cached_Property_Type_Key_Data_Name];
						
						
						// Generate command for where clause...
						$Value_Command_String = &New_String(
								($Cached_Property_Type_Alias . '.' . $Cached_Property_Alias) . 
									' ' . 'Where' . ' ' .
								'(' . 
									($Cached_Property_Type_Alias . '.' . $Cached_Property_Type_Key_Alias) .
										' ' . '='  . ' ' .
									('"' . $Item_Key_Value . '"') . 
								')'
							);
						break;
					}
					case MANY_TO_MANY:
					{
						$Cached_Property_Reverse_Key_Lookup = &$Cached_Property['Cached_Attachment_Type_Reverse_Property']['Key'];
					
						$Cached_Property_Type_Key_Cached_Property = &$Cached_Property_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Reverse_Key_Lookup)];
						$Cached_Property_Type_Key_Cached_Property_Alias = &$Cached_Property_Type_Key_Cached_Property['Alias'];
						$Cached_Property_Type_Key_Data_Name = &$Cached_Property_Type_Key_Cached_Property['Data_Name'];
						
						// Set match to left side
						// TODO: this is a little hacky and only works on single-attachment queries
						$Left_Or_Right_Side_Item = 'Left_Side_Item';
					
						// Get item's key value
						$Item_Key_Value = &$Item['Data'][$Cached_Property_Type_Key_Data_Name];
					
						// Get attachment type
// 						$Cached_Reverse_Property_Alias = &$Cached_Property['Reverse_Alias'];
	// 					$Cached_Property_Attachment_Type = &$Cached_Property['Cached_Attachment_Type'];
	// 					$Cached_Property_Attachment_Type_Data_Name = &$Cached_Property_Attachment_Type['Data_Name'];
					
						// Generate command for where clause
						// TODO - I don't think this can work for property with the same value type as the type?
						// TODO @core-language: numbers and aliases maybe shouldn't get wrapped in quotes, but Names and other strings do
						$Value_Command_String = &New_String(
							($Cached_Property_Type_Alias . '.' . $Cached_Property_Alias) .
							' ' . 'Where' . ' ' .
// 							('Attachment' . '.' . $Cached_Reverse_Property_Alias) .
							($Left_Or_Right_Side_Item . '.' . $Cached_Property_Type_Key_Cached_Property_Alias) .
// 							($Cached_Property_Type_Alias . '.' . $Cached_Property_Type_Key_Cached_Property_Alias) .
							' ' . '=' . ' ' .
							('"' . $Item_Key_Value . '"'));
							
						break;
					}
				
					case MANY_TO_ONE:
					{
						$Cached_Property_Key_Lookup = &$Cached_Property['Key'];
			
						// Get value type's key property
						$Cached_Property_Value_Type_Key_Cached_Property = &$Cached_Property_Value_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Key_Lookup)];
						$Cached_Property_Value_Type_Key_Cached_Property_Alias = &$Cached_Property_Value_Type_Key_Cached_Property['Alias'];
					
						// Get target item's key value
						$Target_Key_Value = &$Item['Data'][$Cached_Property_Data_Name];
					
						// Generate command for where clause
						// TODO @core-language: numbers and aliases maybe shouldn't get wrapped in quotes, but Names and other strings do
						$Value_Command_String = &New_String($Cached_Property_Value_Type_Alias .
							' ' . 'Where' . ' ' .
								($Cached_Property_Value_Type_Alias . '.' . $Cached_Property_Value_Type_Key_Cached_Property_Alias) .
							' = ' . '"' . $Target_Key_Value . '"');
						break;
					}
			
					case ONE_TO_MANY:
					{
						$Cached_Property_Key_Lookup = &$Cached_Property['Key'];
					
						// Get type's key property
						$Cached_Property_Type_Key_Cached_Property = &$Cached_Property_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Key_Lookup)];
						$Cached_Property_Type_Key_Data_Name = &$Cached_Property_Type_Key_Cached_Property['Data_Name'];
					
						$Cached_Property_Reverse_Alias = &$Cached_Property['Reverse_Alias'];
			
						// Get item's key value
						$Item_Key_Value = &$Item['Data'][$Cached_Property_Type_Key_Data_Name];
					
						// Generate command for where clause
						// TODO: actually get key property and proper data_name
						// TODO @core-language: numbers and aliases maybe shouldn't get wrapped in quotes, but Names and other strings do
						$Value_Command_String = &New_String($Cached_Property_Value_Type_Alias .
							' Where' . ' ' .
								($Cached_Property_Value_Type_Alias . '.' . $Cached_Property_Reverse_Alias) .
							' = ' . '"' . $Item_Key_Value . '"');
				
						break;
					}
				}
			
				// TODO: should build new command from scratch instead of replacing values in original command
			
				// Generate where clause
				// TODO: Build Where Clause By Hand?
				$Value_Command = &Parse_String_Into_Command($Value_Command_String);
				$Value_Where_Clause_Tree = &$Value_Command['Clauses']['where']['Tree'];
				$Value_Name_Clause_Tree = &$Value_Command['Clauses']['name']['Tree'];
			
				if (isset($Last_Name_Tree))
				{
					// Replace left term of last name tree with new name tree
					$Last_Name_Tree['Terms'][1] = &$Value_Name_Clause_Tree;
				}
				else
				{
					$Command['Clauses']['name']['Tree'] = &$Value_Name_Clause_Tree;
				}
			
				// Replace name tree base with generic type alias
				$Name_Tree_Base['Value'] = &$Cached_Property_Value_Type_Alias;
			
				// Merge new 'Where' tree into original command
				if (isset($Command['Clauses']['where']))
				{
					$Merged_And_Operator = &New_Tree();
					$Merged_And_Operator['Kind'] = &New_String('Operator');
					$Merged_And_Operator['Value'] = &New_String('And');
					$Merged_And_Operator['Terms'][] = &$Value_Where_Clause_Tree;
					$Merged_And_Operator['Terms'][] = &$Command['Clauses']['where']['Tree'];
					$Command['Clauses']['where']['Tree'] = &$Merged_And_Operator;
				}
				else
				{
					$Command['Clauses']['where']['Tree'] = &$Value_Where_Clause_Tree;
				}
				
				$Value = &Get_Database_Item($Database, $Command, $Memory_Stack_Reference);
				
				// Set data to know where the value came from
				$Value['Parent_Item'] = &$Item;
				$Value['Parent_Cached_Property'] = &$Cached_Property;
				
				return $Value;
			}
		}
	}
}

?>