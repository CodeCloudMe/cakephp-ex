<?php

// Evaluate Clause Tree

function &Evaluate_Clause_Tree(&$Database, &$Tree, &$Memory_Stack_Reference)
{
	// TODO: [If false or false or false] => [If false or true] => [If true] (still active bug)
	// TODO: Seek more possibilities like the above and fix them too.  Might is Not_Set items clouding evaluation.   
	// Globals
	global $Language_Operators;
	
	// Setup Result
	$Result = &New_Array();
	
	// Process various tree types (i.e. operator, text, number, variable)
	switch (strtolower($Tree['Kind']))
	{
		// Variables or Operators
		case 'variable':
		case 'operator':
		{
			if (strtolower($Tree['Kind']) == 'variable' || (strtolower($Tree['Kind']) == 'operator' && $Tree['Value'] == '.'))
			{
				// Set up new command to fetch variable value from context
				$Tree_Value_Command = &New_Command();
				
				// Use current tree as the name clause for the new command
				$Tree_Value_Command['Clauses']['name'] = &New_Clause();
				$Tree_Value_Command['Clauses']['name']['Name'] = &New_String('Name');
				$Tree_Value_Command['Clauses']['name']['Tree'] = &$Tree;
				
				// Get result as reference
				$Tree_Value_Command['Clauses']['as'] = &New_Clause();
				$Tree_Value_Command['Clauses']['as']['Name'] = &New_String('As');
				$Tree_Value_Command['Clauses']['as']['Tree'] = &New_Tree();
				$Tree_Value_Command['Clauses']['as']['Tree']['Kind'] = &New_String('Variable');
				$Tree_Value_Command['Clauses']['as']['Tree']['Value'] = &New_String('Reference');
				
// 				traverse($Tree_Value_Command);
				// Process the new command
				// TODO @core-language: how to deal with this database problem (i.e. shouldn't use database in this case)
// 				traverse($Tree_Value_Command);
				$Processed_Command = &Process_Command($Database, $Tree_Value_Command, $Memory_Stack_Reference);
				
// 				traverse($Processed_Command);
				// Check if processed result is an item or a simple value
				// TODO: should By Reference set something other than 'Content' for simple values?
				if (isset($Processed_Command['Chunks'][0]['Content']))
					$Value = &$Processed_Command['Chunks'][0]['Content'];
				else
					$Value = &$Processed_Command['Chunks'][0]['Item'];
				
// 				traverse($Value);
				// Store value in result
				$Result['Kind'] = &New_String('Evaluated_Dot_Operator_Or_Variable');
				$Result['Value'] = &$Value;
			}
			else
			{
				// Non-Dot Operator...
				$Operator_Lookup = &$Tree['Value'];
				
				// Get operator data
				$Operator_Data = &$Language_Operators[strtolower($Operator_Lookup)];
		
				// Process operators depending on term count (i.e. unary, binary, ternary, etc)
				// TODO @core-language finish term count
				switch ($Operator_Data['Term_Count'])
				{
					// Binary Operator: 2 terms
					case 2:
					{
						// For binary operators, evaluate left and right operands
						$Left_Operand = &$Tree['Terms'][1];
						$Left_Evaluated_Operand = &Evaluate_Clause_Tree($Database, $Left_Operand, $Memory_Stack_Reference);
						$Left_Value = &$Left_Evaluated_Operand['Value'];
						$Right_Operand = &$Tree['Terms'][0];
						$Right_Evaluated_Operand = &Evaluate_Clause_Tree($Database, $Right_Operand, $Memory_Stack_Reference);
						$Right_Value = &$Right_Evaluated_Operand['Value'];
				
						// Execute operator
						// TODO @core-language: finish operators
						switch (strtolower($Operator_Data['Mapped_Operator']))
						{
							// Addition: +
							case '+':
								// TODO @feature-language: all string concatenations (all options: string + string, string + number, number + string, number + number) and dates, etc.
								$Result['Kind'] = &New_String('Evaluated_Number_Operator');
								if (!is_numeric($Left_Value) && !is_numeric($Right_Value))
									$Result['Value'] = &New_String($Left_Value . $Right_Value);
								else
									$Result['Value'] = &New_Number($Left_Value + $Right_Value);
								break;
						
							// Subtraction: -
							case '-':
								$Result['Kind'] = &New_String('Evaluated_Number_Operator');
								$Result['Value'] = &New_Number($Left_Value - $Right_Value);
								break;
						
							// Multipliation: *
							case '*':
								$Result['Kind'] = &New_String('Evaluated_Number_Operator');
								$Result['Value'] = &New_Number($Left_Value * $Right_Value);
								break;
						
							// Division: /
							case '/':
								$Result['Kind'] = &New_String('Evaluated_Number_Operator');
								$Result['Value'] = &New_Number($Left_Value / $Right_Value);
								break;
				
							// Comparison 
							case 'is hash of':
								// TODO - I guess we could verify that Right_Value converts to an empty string from null as needed, but it may never happen -- can't tell.
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								$Result['Value'] = &New_Boolean(password_verify($Right_Value, $Left_Value));
								break;

							case '==':
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								$Result['Value'] = &New_Boolean($Left_Value == $Right_Value);
								break;
								
							case '!=':
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								$Result['Value'] = &New_Boolean($Left_Value != $Right_Value);
								break;
						
							// Less Than: <
							case '<':
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								$Result['Value'] = &New_Boolean($Left_Value < $Right_Value);
								break;
						
							// Greater Than: >
							case '>':
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								$Result['Value'] = &New_Boolean($Left_Value > $Right_Value);
								break;
								
							// Logical Or: ||								
							case '||':
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								$Result['Value'] = &New_Boolean($Left_Value || $Right_Value);
								break;

							// Logical And: ||
							case '&&':
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								$Result['Value'] = &New_Boolean($Left_Value && $Right_Value);
								break;
							
							case 'is parent type of':
							case 'is child type of':
								//Verify
								if ($Left_Value['End_Of_Results'] || $Right_Value['End_Of_Results'])
									throw new Exception('No values specified in \'is parent type\' operator .');
									
								if (!(strtolower($Left_Value['Cached_Base_Type']['Alias']) == 'type') && (strtolower($Right_Value['Cached_Base_Type']['Alias']) == 'type'))
									throw new Exception('Invalid operands for \'is parent type of\' operator.');
									
								// Set parent and child direction
								if (strtolower($Operator_Data['Mapped_Operator']) == 'is parent type of')
								{
									$Parent_Value = &$Left_Value;
									$Child_Value = &$Right_Value;
								}
								else
								{
									$Parent_Value = &$Right_Value;
									$Child_Value = &$Left_Value;
								}
									
								$Parent_Value_Alias = &$Left_Value['Data']['Alias'];
								$Child_Value_Alias = &$Right_Value['Data']['Alias'];
								$Parent_Value_Type = &Get_Cached_Type($Database, $Parent_Value_Alias);
								$Child_Value_Type = &Get_Cached_Type($Database, $Child_Value_Alias);
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');							
								$Result['Value'] = &Is_Child_Type_Of($Child_Value_Type, $Parent_Value_Type);
								break;
							
							// Unknown
							default:
								traverse($Tree);
								throw new Exception('Unimplemented operator: ' . $Operator_Data['Mapped_Operator']);
								break;
						}
				
						break;
					}
					
					case 1:
					{
						// Get and evaluate single operand
						$Operand = &$Tree['Terms'][0];
						$Evaluated_Operand = &Evaluate_Clause_Tree($Database, $Operand, $Memory_Stack_Reference);
						$Value = &$Evaluated_Operand['Value'];
						
						// Handle various operators
						switch (strtolower($Operator_Data['Mapped_Operator']))
						{
							// Exists
							// TODO finish operators.
							// TODO: Is_Set and Is_Not_Set are not implemented properly. They should check against Not_Set returned values!!!!!!
							case 'exists':
							{
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								if (Is_Not_Set($Value))
								{
									$Result['Value'] =  &New_Boolean(false);
								}
								else if (Is_Item($Value))
								{
									if (array_key_exists('End_Of_Results', $Value) && $Value['End_Of_Results'])
										$Result['Value'] = &New_Boolean(false);
									else
										$Result['Value'] = &New_Boolean(true);
								}
								else
								{
									if ($Value === null)
										$Result['Value'] = &New_Boolean(false);
									else
										$Result['Value'] = &New_Boolean(true);
								}
								break;
							}
							case 'does not exist':
							{
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								if (Is_Not_Set($Value))
								{
									$Result['Value'] =  &New_Boolean(true);
								}
								else if (Is_Item($Value))
								{
									if (array_key_exists('End_Of_Results', $Value) && $Value['End_Of_Results'])
										$Result['Value'] = &New_Boolean(true);
									else
										$Result['Value'] = &New_Boolean(false);
								}
								else
								{
									if ($Value === null)
										$Result['Value'] = &New_Boolean(true);
									else
										$Result['Value'] = &New_Boolean(false);
								}
								break;
							}
							case 'twas set':
							{
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								if (Is_Not_Set($Value))
								{
									$Result['Value'] =  &New_Boolean(false);
								}
								else
									$Result['Value'] =  &New_Boolean(true);
								break;
							}	
							case 'twas not set':
							{
								// TODO: how to handle non-simple values
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								if (Is_Not_Set($Value))
								{
									$Result['Value'] =  &New_Boolean(true);
								}
								else
									$Result['Value'] = &New_Boolean(false);
								break;
							}
							// TODO - not sure how much I like the ! operator.  What is it:  if it doesn't exist, or it does exist and is either null, 0, "", or false, return true? I mean, I guess that works... but
							case '!':
							{
								$Result['Kind'] = &New_String('Evaluated_Boolean_Operator');
								if (!$Value || Is_Not_Set($Value))
									$Result['Value'] = &New_Boolean(true);								
								else
									$Result['Value'] = &New_Boolean(false);
								break;
							}	
							// TODO - I guess we can use is_simple_type, but it's more "work".
							case 'is complex type':
							{
								global $Simple_Types;
								if (Is_Item($Value) && $Value['Cached_Base_Type']['Alias'] == 'Type' && !in_array(strtolower($Value['Data']['Alias']), $Simple_Types))
								 	 $Result['Value'] = &New_Boolean(true);
								else
								 	 $Result['Value'] = &New_Boolean(false);
								break;
							}
							// TODO - I guess we can use is_simple_type, but it's more "work".								
							case 'is simple type':
							{
								global $Simple_Types;
								if (Is_Item($Value) && $Value['Cached_Base_Type']['Alias'] == 'Type' && in_array(strtolower($Value['Data']['Alias']), $Simple_Types))
								 	 $Result['Value'] = &New_Boolean(true);
								else
								 	 $Result['Value'] = &New_Boolean(false);
								break;
							}
							// TODO - I guess we can use is_simple_type, but it's more "work".								
							case 'is not simple type':
							{
								global $Simple_Types;
								if (Is_Item($Value) && $Value['Cached_Base_Type']['Alias'] == 'Type' && in_array(strtolower($Value['Data']['Alias']), $Simple_Types))
								 	 $Result['Value'] = &New_Boolean(false);
								else
								 	 $Result['Value'] = &New_Boolean(true);
								break;
							}
							// TODO does is_item belong here, instead of elsewhere?

							// Unknown
							default:
							{
								throw new Exception('Unimplemented operator: ' . $Operator_Data['Mapped_Operator']);
								break;
							}
						}
						break;
					}
			
					// Unknown operator term count
					default:
					{
						throw new Exception('Unimplemented operator term count: ' . $Operator_Data['Term_Count'] . ' for operator "' . strtolower($Operator_Lookup) . '"');
						break;
					}
				}
				break;
			}
			
			break;
		}
		
		// Text node
		case 'text':
		{
			$Result['Kind'] = &New_String('Evaluated_Text');
			$Result['Value'] = &$Tree['Value'];
			break;
		}
		
		// Null node
		case 'null':
		{
			$Result['Kind'] = &New_String('Evaluated_Null');
			$Result['Value'] = &$Tree['Value'];
			break;
		}
		
		// Number node
		case 'number':
		{
			$Result['Kind'] = &New_String('Evaluated_Number');
			$Result['Value'] = &$Tree['Value'];
			break;
		}
		
		// Boolean node
		case 'boolean':
		{
			$Result['Kind'] = &New_String('Evaluated_Boolean');
			$Result['Value'] = &$Tree['Value'];
			break;
		}
		
		// Date/Time nodes
		case 'date':
		{	
			$Result['Kind'] = &New_String('Evaluated_Date');
			
			$Cached_Date_Type = &Get_Cached_Type($Database, 'Date');
			$Date_Item = &Create_Item($Database, $Cached_Date_Type);
			Set_Value($Date_Item, 'Simple_Value', $Tree['Value']);
			$Result['Value'] = &$Date_Item;
			break;
		}
		case 'time':
		{
			$Result['Kind'] = &New_String('Evaluated_Time');

			$Cached_Time_Type = &Get_Cached_Type($Database, 'Time');
			$Time_Item = &Create_Item($Database, $Cached_Time_Type);
			Set_Value($Time_Item, 'Simple_Value', $Tree['Value']);
			$Result['Value'] = &$Time_Item;
			break;
		}
		case 'date_time':
		{
			$Result['Kind'] = &New_String('Evaluated_Date_Time');
			
			$Cached_Date_Time_Type = &Get_Cached_Type($Database, 'Date_Time');
			$Date_Time_Item = &Create_Item($Database, $Cached_Date_Time_Type);
			Set_Value($Date_Time_Item, 'Simple_Value', $Tree['Value']);
			$Result['Value'] = &$Date_Time_Item;
			break;
		}

		
		// Unhandled
		default:
		{
			throw new Exception('Unknown tree type: ' . $Tree['Kind']);
			break;
		}
	}
	
	// Return result
	return $Result;
}

?>