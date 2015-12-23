<?php

// Render SQL Expression Tree
// TODO - handle not_set?
// TODO - test null

function Render_SQL_Expression_Tree(&$Command, &$Expression_Tree, &$Database, $Memory_Stack_Reference = null)
{
	$Name_Tree = &$Command['Clauses']['name']['Tree'];
	
	global $Language_Operators;

	// Get value.
	$Expression_Value = &$Expression_Tree['Value'];
	
	// Handle different types of tree nodes (i.e. Operator, Text, Number, Variable, etc)
	switch (strtolower($Expression_Tree['Kind']))
	{
		// Operator (i.e. dot, +, exists, etc)
		case 'operator':
		{
			// Get operator info from constants
			$Operator_Info = &$Language_Operators[strtolower($Expression_Value)];
			$Mapped_Operator = &$Operator_Info['Mapped_Operator'];
			
			// Check if it's a special operator
			switch ($Mapped_Operator)
			{
				// Dot Operator
				case '.':
				{
					// Render a tree of dot operators into a single table_name and column name that corresponds with the name tree.

					// Render left operand
					$Left_Operand = &$Expression_Tree['Terms'][1];
					$Left_Operand['Not_Dot_Root'] = &New_Boolean(true);
					Render_SQL_Expression_Tree($Command, $Left_Operand, $Database, $Memory_Stack_Reference);
					
					if (isset($Left_Operand['Not_Found']))
					{
						$Expression_Tree['Not_Found'] = &New_Boolean(true);
						
						// Check if we are at the root of a dot tree
						if (!isset($Expression_Tree['Not_Dot_Root']))
						{
							// Make sure we have a memory stack reference for searching the memory
							// TODO is there a better way to indicate whether it should search memory and pass in the reference?
							if (!$Memory_Stack_Reference)
								throw new exception("No memory stack reference for non-database SQL expression dot tree.");
							
							// We are at the root of a dot tree
							
							// Fetch from memory...
							
							// Build a new command to get the target
							$Dot_Tree_Command = &New_Command();
							
							// Set new commands name to the progressive tree
							$Dot_Tree_Command['Clauses']['name'] = &New_Clause();
							$Dot_Tree_Command['Clauses']['name']['Name'] = &New_String('Name');
							$Dot_Tree_Command['Clauses']['name']['Tree'] = &$Expression_Tree;
							
							// Add "As Reference" to new command
							$Dot_Tree_Command['Clauses']['as'] = &New_Clause();
							$Dot_Tree_Command['Clauses']['as']['Name'] = &New_String('As');
							$Dot_Tree_Command['Clauses']['as']['Tree'] = &New_Tree();
							$Dot_Tree_Command['Clauses']['as']['Tree']['Kind'] = &New_String('Variable');
							$Dot_Tree_Command['Clauses']['as']['Tree']['Value'] = &New_String('Reference');
							
							// Add "From Memory" to new command
							$Dot_Tree_Command['Clauses']['from'] = &New_Clause();
							$Dot_Tree_Command['Clauses']['from']['Name'] = &New_String('From');
							$Dot_Tree_Command['Clauses']['from']['Tree'] = &New_Tree();
							$Dot_Tree_Command['Clauses']['from']['Tree']['Kind'] = &New_String('Variable');
							$Dot_Tree_Command['Clauses']['from']['Tree']['Value'] = &New_String('Memory');
							
							// Process the command
							$Processed_Dot_Tree_Command = &Process_Command($Database, $Dot_Tree_Command, $Memory_Stack_Reference);
							
							$Dot_Tree_Result = &$Processed_Dot_Tree_Command['Chunks'][0];
							
							switch (strtolower($Dot_Tree_Result['Kind']))
							{
								case 'text_chunk':
									// Convert null to SQL null
									if (is_null($Dot_Tree_Result['Content']))
										$Expression_Tree['SQL'] = &New_String('NULL');

									// Wrap other text values in single quotes
									else
										$Expression_Tree['SQL'] = &New_String('\'' . mysqli_real_escape_string($Database['Link'], $Dot_Tree_Result['Content']) . '\'');
									break;
									
								// TODO - proper not set behavior? not sure. 
								case 'not_set_item_chunk':
									$Expression_Tree['SQL'] = &New_String('NULL');
									break;
									
								case 'item_chunk':
									//TODO - can totally dereference if for some reason this happened.  (maybe via henceforth it could.)
									throw new Exception("Item_Chunks are not supported");
									break;
									
								default:
									// TODO - removed some cases that I thought were mistakes, might be wrong.s
									throw new Exception("Unsupported chunk type in Render_SQL");
									break;
							}
						}
					}
					else
					{
						// Get left cached type
						$Left_Cached_Type = &$Left_Operand['Cached_Type'];
					
						// Get left namespace
						$Left_Namespace = &$Left_Operand['Namespace'];
						
						// TODO - hack for commutative
						if (array_key_exists('Other_Namespace', $Left_Operand))
							$Left_Other_Namespace = &$Left_Operand['Other_Namespace'];

						// Check if right operand matches a property of left operand's cached type.
						$Right_Operand = &$Expression_Tree['Terms'][0];
						if (isset($Left_Cached_Type['Cached_Property_Lookup'][strtolower($Right_Operand['Value'])]))
						{
							// Get matching property 
							$Cached_Property = &$Left_Cached_Type['Cached_Property_Lookup'][strtolower($Right_Operand['Value'])];
		
							// Check matching property relation
							switch(strtolower($Cached_Property['Relation']))
							{
								case COMMUTATIVE:
									$Expression_Tree['Cached_Type'] = &$Cached_Property['Cached_Value_Type'];
									$Expression_Tree['Namespace'] = &New_String($Left_Namespace . '_' . $Cached_Property['Alias'] . '_' . 'Value');

									// TODO - commutative hack, possibly unnecessary
									$Cached_Property_Other_Alias = &$Cached_Property['Cached_Attachment_Type_Reverse_Property']['Alias'];
									$Expression_Tree['Other_Namespace'] = &New_String($Left_Namespace . '_' . $Cached_Property_Other_Alias . '_' . 'Value');
									break;
								case MANY_TO_MANY:
									// If the property is an attachment type property, store the value type and the namespace in the operator tree and continue.
									$Expression_Tree['Cached_Type'] = &$Cached_Property['Cached_Value_Type'];
									$Expression_Tree['Namespace'] = &New_String($Left_Namespace . '_' . $Cached_Property['Alias'] . '_' . 'Value');									
									break;
								default:
									// If the property is not an attachment type property, store a SQL string in the operator tree referencing the left operand's data name and the right operand's data name
									// TODO: Is there a way to link reference a namespace here from the Name Clause?
									// TODO @core-language: Attachment's break here, since the namespace generation doesn't match @Kunal
									$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];
									$Expression_Tree['Cached_Type'] = &$Cached_Property['Cached_Value_Type'];
									$Expression_Tree['Namespace'] = &New_String($Left_Namespace . '_' . $Cached_Property['Alias']);									
									$Expression_Tree['SQL'] = &New_String(('`' . mysqli_real_escape_string($Database['Link'], $Left_Namespace) . '`') . '.'. ('`' . mysqli_real_escape_string($Database['Link'], $Cached_Property_Data_Name) . '`'));

									// TODO - hack for commutative 
									if (isset($Left_Other_Namespace))
									{
										$Expression_Tree['Other_Namespace'] = &New_String($Left_Other_Namespace . '_' . $Cached_Property['Alias']);							
										$Expression_Tree['Other_SQL'] = &New_String(('`' . mysqli_real_escape_string($Database['Link'], $Left_Other_Namespace) . '`') . '.'. ('`' . mysqli_real_escape_string($Database['Link'], $Cached_Property_Data_Name) . '`'));
									}

									break;
							}
						}
					
						// If right operand doesn't match the cached type of the left operand, throw an exception
						else
						{
							traverse($Left_Operand);
							traverse($Right_Operand);
							throw new Exception('Render_SQL_Expression_Tree: Unmatched right operand type: ' . $Right_Operand['Value']);
						}
					}
					break;
				}
				
				// Normal Operators (i.e. +/-/=/!)
				default:
				{
					
					// Implement operators grouped by term count
					$Term_Count = &$Operator_Info['Term_Count'];
					switch ($Term_Count)
					{
						// One term (i.e. 'Page Exists')
						case 1:
						{
							// Render single operand.
							$Operand = &$Expression_Tree['Terms'][0];
							Render_SQL_Expression_Tree($Command, $Operand, $Database, $Memory_Stack_Reference);
							
							// TODO - decide how to handle this.
							if (isset($Operand['Not_Found']) && $Operand['Not_Found'])
								throw new Exception('Error resolving operand of where expression');
							
							switch ($Mapped_Operator)
							{
								case 'exists':
									$Expression_Tree['SQL'] = &New_String('(' . $Operand['SQL'] . ' ' . 'IS NOT NULL' . ')');
									break;
								case 'does not exist':
									$Expression_Tree['SQL'] = &New_String('(' . $Operand['SQL'] . ' ' . 'IS NULL' . ')');
									break;
								case '!':
									$Expression_Tree['SQL'] = &New_String(
											'(' . 
												'(' . 'NOT' . ' ' . $Operand['SQL'] . ')' .
													' ' . 'OR' . ' ' . 
												'(' . $Operand['SQL'] . ' ' . 'IS NULL' . ')' .
											')'
										);
									break;
								default:
									throw new exception('Unhandled operator: ' . $Mapped_Operator);
							}
							break;
						}
						
						// Two terms (i.e. '3 + 5')
						case 2:
						{
							// Render left operand
							{
								$Left_Operand = &$Expression_Tree['Terms'][1];
								Render_SQL_Expression_Tree($Command, $Left_Operand, $Database, $Memory_Stack_Reference);

								// TODO - Decide how to handle this. 
								if (isset($Left_Operand['Not_Found']) && $Left_Operand['Not_Found'])
									throw new Exception('Error resolving Left Operand of where expression');
							}	
								
							// Render right operand
							{
								$Right_Operand = &$Expression_Tree['Terms'][0];
								Render_SQL_Expression_Tree($Command, $Right_Operand, $Database, $Memory_Stack_Reference);																
								if (isset($Right_Operand['Not_Found']) && $Right_Operand['Not_Found'])
								{
									// TODO - this was a quick bugfix, not thought through.
									if ($Right_Operand['Kind'] != 'Operator')
									{
										// Resolve right operand from memory.
										// TODO - Move below, but be careful to respect other not_found behavior.
										$Right_Operand_Command_String = &New_String($Right_Operand['Value'] . ' ' . 'from' . ' ' . 'Memory' . ' ' . 'as' . ' ' . 'Reference');
										$Processed_Right_Operand_Command = &Process_Command_String($Database, $Right_Operand_Command_String, $Memory_Stack_Reference);

										$Processed_Right_Operand_Command_Result = &$Processed_Right_Operand_Command['Chunks'][0];

										switch (strtolower($Processed_Right_Operand_Command_Result['Kind']))
										{
											case 'text_chunk':
												// Convert null to SQL null
												if (is_null($Dot_Tree_Result['Content']))
													$Right_Operand['SQL'] = &New_String('NULL');

												// Wrap other text values in single quotes
												else
												{
													$Right_Operand['SQL'] = &New_String('\'' . mysqli_real_escape_string($Database['Link'], $Dot_Tree_Result['Content']) . '\'');
												}
												break;
									
											// TODO - proper not set behavior? not sure. 
											case 'not_set_item_chunk':
												$Right_Operand['SQL'] = &New_String('NULL');
												break;
									
											case 'item_chunk':
												$Processed_Right_Operand_Command_Result_Item = &$Processed_Right_Operand_Command_Result['Item'];
												
												// TODO don't know if there's other behavior that makes sense
												if ($Processed_Right_Operand_Command_Result_Item['End_of_Results'])
													throw new Exception("Item chunks that are EOF are not supported yet in queries");
												
												// Deference to ID value
												else
												{
													// TODO there might be more nuance here
													if ($Processed_Right_Operand_Command_Result_Item['Data'] && $Processed_Right_Operand_Command_Result_Item['Data']['ID'])
														$Right_Operand['SQL'] = &$Processed_Right_Operand_Command_Result_Item['Data']['ID'];
													else	
														throw new Exception("Item chunks without IDs are not yet supported in queries");
												}
												
												break;
									
											default:
												// TODO - removed some cases that I thought were mistakes, might be wrong.s
												throw new Exception("Unsupported chunk type in Render_SQL");
												break;
										}
									}
								}
							}
							
							switch ($Mapped_Operator)
							{
								// Build new SQL statement depending on operator
								case '+':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '+' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '-':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '-' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '*':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '*' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '/':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '/' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '%':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '%' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case 'pow':
									$Expression_Tree['SQL'] = &New_String('(' . ('pow(' . $Left_Operand['SQL'] . ' ' . ', ' . ' ' . $Right_Operand['SQL'] . ')') . ')');
									break;
								case '==':
									// Convert to SQL operator based on comparison value
									if ($Right_Operand['Kind'] == 'Null')
										$SQL_Operator = &New_String('is');
									else
										$SQL_Operator = &New_String('=');
									
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . $SQL_Operator . ' ' . $Right_Operand['SQL'] . ')');

									// TODO - hack for commutative
									if (array_key_exists('Other_SQL', $Left_Operand))
										$Expression_Tree['Other_SQL'] = &New_String('(' . $Left_Operand['Other_SQL'] . ' ' . $SQL_Operator . ' ' . $Right_Operand['SQL'] . ')');
										
									break;
								case 'contains':
								case 'does_not_contain':
								case 'starts_with':
								case 'does_not_start_with':
								case 'ends_with':
								case 'does_not_end_with':
									// Check if negative match
									$Negative_SQL = &New_String('');
									switch ($Mapped_Operator)
									{
										case 'does_not_contain':
										case 'does_not_start_with':
										case 'does_not_end_with':
											$Negative_SQL = &New_String('NOT');
									}
									
									// Check for starting wildcards
									$Starting_Wildcard_SQL = &New_String('');
									switch ($Mapped_Operator)
									{
										case 'contains':
										case 'does_not_contain':
										case 'ends_with':
										case 'does_not_end_with':
											$Starting_Wildcard_SQL = &New_String('%');
									}
									
									// Check for ending wildcards
									$Ending_Wildcard_SQL = &New_String('');
									switch ($Mapped_Operator)
									{
										case 'contains':
										case 'does_not_contain':
										case 'starts_with':
										case 'does_not_start_with':
											$Ending_Wildcard_SQL = &New_String('%');
									}
									
									// Convert to SQL operator based on comparison value
									$SQL_Operator = &New_String('LIKE');
									
									// TODO handle non-strings better (and strings for that matter
									// Trim quotes from right operand and wrap with wildcard
									$Modified_Right_Operand = &New_String(
										'\'' .
										$Starting_Wildcard_SQL .
										mysqli_real_escape_string($Database['Link'], substr($Right_Operand['SQL'], 1, -1)) .
										$Ending_Wildcard_SQL . '\'');
									
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . $Negative_SQL . ' ' . $SQL_Operator . ' ' . $Modified_Right_Operand . ')');
									break;
								case '!=':
									// If the right operand is null, convert to sql check for is not null.
									if ($Right_Operand['Kind'] == 'Null')
										$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . 'IS NOT' . ' ' . 'NULL' . ')');
								
									// Otherwise, convert to sql check for is not value, or is null.
									else
									{
										$Expression_Tree['SQL'] = &New_String(
											'(' . 
												('(' . $Left_Operand['SQL'] . ' ' . 'IS' . ' ' . 'NULL' . ')') . 
													' ' . 'OR' . ' ' .
												('(' . $Left_Operand['SQL'] . ' ' . '!=' . ' ' . $Right_Operand['SQL'] . ')') .
											')'
										);		
									}
									break;
								case '<':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '<' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '>':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '>' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '<=':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '<=' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '>=':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . '>=' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '||':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . 'OR' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								case '&&':
									$Expression_Tree['SQL'] = &New_String('(' . $Left_Operand['SQL'] . ' ' . 'AND' . ' ' . $Right_Operand['SQL'] . ')');
									break;
								default:
									throw new exception('Unhandled operator: ' . $Mapped_Operator);
							}
							break;
						}
						default:
							throw new exception('Term count not handled in render sql expression tree: ' . $Term_Count);
					}
					break;
				}
			}
			break;
		}
		
		// Text nodes
		case 'text':
			// Wrap escaped text in single quotes
			$Expression_Tree['SQL'] = &New_String('\'' . mysqli_real_escape_string($Database['Link'], $Expression_Value) . '\'');
			break;
			
		// Date/Time nodes
		case 'date':
			$Expression_Tree['SQL'] = &New_String('\'' . date('Y-m-d', $Expression_Value) . '\'');
			break;
		case 'time':
			$Expression_Tree['SQL'] = &New_String('\'' . date('H:i:s', $Expression_Value) . '\'');
			break;
		case 'date_time':
			$Expression_Tree['SQL'] = &New_String('\'' . date('Y-m-d H:i:s', $Expression_Value) . '\'');
			break;
		
		// Number nodes
		case 'number':
			// Escape number
			$Expression_Tree['SQL'] = &New_String(mysqli_real_escape_string($Database['Link'], $Expression_Value));
			break;
			
		// Boolean nodes
		case 'boolean':
			// Escape boolean
			if ($Expression_Value)
				$Expression_Tree['SQL'] = &New_String('b' . '\'' . '1' . '\'');
			else
				$Expression_Tree['SQL'] = &New_String('b' . '\'' . '0' . '\'');
			break;
			
		// Null nodes
		case 'null':
			$Expression_Tree['SQL'] = &New_String('NULL');
			break;
		
		// Variable nodes
		case 'variable':
		{
			// Navigate the passed in name clause tree, from right to left, matching against types or properties.
			
			// Check key words.
			switch (strtolower($Expression_Value))
			{
				case 'left_side_item':
				{
					// Get type and namespace from left-side item of original command
					$Expression_Tree['Cached_Type'] = &$Name_Tree['Terms'][1]['Cached_Type'];
					$Expression_Tree['Namespace'] = &$Name_Tree['Terms'][1]['Namespace'];
					break;
				}
				// Check 'attachment', resolve to first term of type 'attachment_type.'
				case 'attachment':
				{
					$Recursive_Name_Tree = &$Name_Tree;
					while (isset($Recursive_Name_Tree))
					{
						// Check if tree node represents a base type or dot-relation between types
						// Then Load term type (attachment_type or not), cached type, and namespace.
						if (strtolower($Recursive_Name_Tree['Kind']) == 'operator' && $Recursive_Name_Tree['Value'] == '.')
						{
							// If node is a dot, load type from its right child (always a type because dot operator is left-to-right)
							$Recursive_Name_Tree_Right_Term = &$Recursive_Name_Tree['Terms'][0];
							$Recursive_Term_Type = &$Recursive_Name_Tree_Right_Term['Kind'];
							$Recursive_Base_Cached_Type = &$Recursive_Name_Tree_Right_Term['Cached_Type'];
							$Recursive_Namespace = &$Recursive_Name_Tree_Right_Term['Namespace'];
							if (array_key_exists('Other_Namespace', $Recursive_Name_Tree_Right_Term))
								$Recursive_Other_Namespace = &$Recursive_Name_Tree_Right_Term['Other_Namespace'];
						}
						else
						{
							// If node is a base type, we are at the left-most (the top) type.
							$Recursive_Term_Type = &$Recursive_Name_Tree['Kind'];							
							$Recursive_Base_Cached_Type = &$Recursive_Name_Tree['Cached_Type'];
							$Recursive_Namespace = &$Recursive_Name_Tree['Namespace'];
							// TODO - hack for commutative
							if (array_key_exists('Other_Namespace', $Recursive_Name_Tree))
								$Recursive_Other_Namespace = &$Recursive_Name_Tree['Other_Namespace'];
						}
						
						// Check if the current item is an attachment type
						if (strtolower($Recursive_Term_Type) == 'attachment_type')
						{
							$Expression_Tree['Cached_Type'] = &$Recursive_Base_Cached_Type;
							$Expression_Tree['Namespace'] = &$Recursive_Namespace;
							// TODO - hack for commutative
							if (isset($Recursive_Other_Namespace))
								$Expression_Tree['Other_Namespace'] = &$Recursive_Other_Namespace;
							break 3;
						}
				
						// Move to the next tree item while it exists.
						if (strtolower($Recursive_Name_Tree['Kind']) == 'operator' && $Recursive_Name_Tree['Value'] == '.')
							$Recursive_Name_Tree = &$Recursive_Name_Tree['Terms'][1];
						else
							unset($Recursive_Name_Tree);
					}
					
					throw new Exception('No attachment type found.');
					break;
				}
				
				case 'hour':
				case 'minute':
				{
					$Expression_Tree['SQL'] = &New_String('Segment_By_Value');
					break;
				}

				default:
				{
					// Check if the variable corresponds to a expression value in the original name clause (i.e. henceforth, check if the where query matches any part of the name query literally.
					$Recursive_Name_Tree = &$Name_Tree;
			
					while (isset($Recursive_Name_Tree))
					{
						// Check if tree node represents a base type or dot-relation between types
						// Load value, cached type, namespace
						if (strtolower($Recursive_Name_Tree['Kind']) == 'operator' && $Recursive_Name_Tree['Value'] == '.')
						{
							// If node is a dot, load type from its right child (always a type because dot operator is left-to-right)
							$Recursive_Name_Tree_Right_Term = &$Recursive_Name_Tree['Terms'][0];
							$Recursive_Value = &$Recursive_Name_Tree_Right_Term['Value'];
							$Recursive_Base_Cached_Type = &$Recursive_Name_Tree_Right_Term['Cached_Type'];
							$Recursive_Namespace = &$Recursive_Name_Tree_Right_Term['Namespace'];
							// TODO - hack for commutative - not sure if it's needed
							if (array_key_exists('Other_Namespace', $Recursive_Name_Tree_Right_Term))
								$Recursive_Other_Namespace = &$Recursive_Name_Tree_Right_Term['Other_Namespace'];
						}
						else
						{
							// If node is a base type, we are at the left-most (the top) type.
							$Recursive_Value = &$Recursive_Name_Tree['Value'];
							$Recursive_Base_Cached_Type = &$Recursive_Name_Tree['Cached_Type'];
							$Recursive_Namespace = &$Recursive_Name_Tree['Namespace'];
							// TODO - hack for commutative - not sure if it's needed
							if (array_key_exists('Other_Namespace', $Recursive_Name_Tree))
								$Recursive_Other_Namespace = &$Recursive_Name_Tree['Other_Namespace'];
						}

						// Check if the variable corresponds to this type, or any of its parent types
						$Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type = &$Recursive_Base_Cached_Type;
						while (isset($Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type))
						{
						
							// TODO @core-database: dunno if this is right, do i check this by alias? 
							$Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type_Alias = &$Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type['Alias'];
							if (strtolower($Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type_Alias) == strtolower($Expression_Value))
							{
								// Store the cached type and namespace into this variable tree and return.
								// TODO @core-database: Do I use the base cached type or the parent type?
								// I think I use the base one but I am not sure.
								$Expression_Tree['Cached_Type'] = &$Recursive_Base_Cached_Type;
								$Expression_Tree['Namespace'] = &$Recursive_Namespace;
								// TODO - hack for commutative, not sure if needed.
								if (isset($Recursive_Other_Namespace))
									$Expression_Tree['Other_Namespace'] = &$Recursive_Other_Namespace;
								break 4;
							}
							
							// Move up to next parent type while it exists.
							if (isset($Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type['Cached_Parent_Type']))
								$Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type = &$Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type['Cached_Parent_Type'];
							else
								unset($Recursive_Base_Cached_Type_Recursive_Cached_Parent_Type);							
						}
				
						// Move to the next tree item while it exists.
						if (strtolower($Recursive_Name_Tree['Kind']) == 'operator' && $Recursive_Name_Tree['Value'] == '.')
							$Recursive_Name_Tree = &$Recursive_Name_Tree['Terms'][1];
						else
							unset($Recursive_Name_Tree);
					}

					// Check if the variable corresponds to a property of a type in name tree.
					$Recursive_Name_Tree = &$Name_Tree;
					while (isset($Recursive_Name_Tree))
					{
						// Check if tree node represents a base type or dot-relation between types
						//load cached type, namespace
						if (strtolower($Recursive_Name_Tree['Kind']) == 'operator' && $Recursive_Name_Tree['Value'] == '.')
						{
							// If node is a dot, load type from its right child (always a type because dot operator is left-to-right)
							$Recursive_Name_Tree_Right_Term = &$Recursive_Name_Tree['Terms'][0];
							$Recursive_Base_Cached_Type = &$Recursive_Name_Tree_Right_Term['Cached_Type'];
							$Recursive_Namespace = &$Recursive_Name_Tree_Right_Term['Namespace'];
							// TODO - hack for commutative - not sure if needed
							if (array_key_exists('Other_Namespace', $Recursive_Name_Tree_Right_Term))
								$Recursive_Other_Namespace = &$Recursive_Name_Tree_Right_Term['Other_Namespace'];
						}
						else
						{
							// If node is a base type, we are at the left-most (the top) type.
							$Recursive_Base_Cached_Type = &$Recursive_Name_Tree['Cached_Type'];
							$Recursive_Namespace = &$Recursive_Name_Tree['Namespace'];
							// TODO - hack for commutative - not sure if needed
							if (array_key_exists('Other_Namespace', $Recursive_Name_Tree))
								$Recursive_Other_Namespace = &$Recursive_Name_Tree['Other_Namespace'];
						}
				
						// Check if the variable corresponds to a property of this type
						if (isset($Recursive_Base_Cached_Type['Cached_Property_Lookup'][strtolower($Expression_Value)]))
						{	
							// Get the cached property
							$Cached_Property = &$Recursive_Base_Cached_Type['Cached_Property_Lookup'][strtolower($Expression_Value)];
							$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];

							// Store the SQL expression from this property and type, and return.
							$Expression_Tree['Namespace'] = &$Recursive_Namespace;
							$Expression_Tree['SQL'] = &New_String('`' . mysqli_real_escape_string($Database['Link'], $Recursive_Namespace) . '`' . '.'. '`' . mysqli_real_escape_string($Database['Link'], $Cached_Property_Data_Name) . '`');
		
							// TODO - hack for commutative
							if (isset($Recursive_Other_Namespace))
							{
								$Expression_Tree['Other_Namespace'] = &$Recursive_Other_Namespace;
								$Expression_Tree['Other_SQL'] = &New_String('`' . mysqli_real_escape_string($Database['Link'], $Recursive_Other_Namespace) . '`' . '.'. '`' . mysqli_real_escape_string($Database['Link'], $Cached_Property_Data_Name) . '`');
							}
							break 3;
						}

						// Move to next tree item
						if (strtolower($Recursive_Name_Tree['Kind']) == 'operator' && $Recursive_Name_Tree['Value'] == '.')
							$Recursive_Name_Tree = &$Recursive_Name_Tree['Terms'][1];
						else
							unset($Recursive_Name_Tree);
					}
					
					// If nothing is returned, set not found on the tree
					$Expression_Tree['Not_Found'] = &New_Boolean(true);
					break;
				}
			}
			break;
		}
		
		// Error checking
		default:
			throw new Exception ('Unexpected Expression tree kind: ' . $Expression_Tree['Kind']);
	}
// 	traverse($Expression_Tree);
}

?>