<?php

// TODO @core-language: split by spaces so 3+5 works (right now only 3 + 5 works)
// TODO @core-language: handle lists of operands (i.e. With Name 'Home')
// TODO @core-language: a space found within Name clause should start the First_Value clauses

function &Parse_String_Into_Command(&$Command_String)
{
	// Check if command already parsed in cache
// 	global $Command_Cache;
// 	if (isset($Command_Cache[strtolower($Command_String)]))
// 	{
// 		$Command = $Command_Cache[strtolower($Command_String)];
// 		return $Command;
// 	}
	
	// Interpret command...
	
	// Load language terms
	global $Language_Terms;
	global $Language_Operators;
	
	// Initialize parsed command
	$Command = &New_Command();
	
	// Store original command string
	$Command['Original_String'] = &$Command_String;
	
	$Is_New_Command = &New_Boolean(true);
	$In_Operator = &New_Boolean(false);
	$Parenthesis_Count = 0;
	
	// Split spaces, colon, quotes, parentheses, commas, operators (= < >)
	// TODO @feature-language: split by operators too and make easier to understand
	// Warning: $Command_Parts not by reference
	global $Language_Regex;
	$Command_Parts = preg_split(
		$Language_Regex,
		$Command_String,
		null,
		PREG_SPLIT_NO_EMPTY |  PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE
		);
	
	// Iterate over command parts
	// Warning: $Command_Part_Index not by reference
	for ($Command_Part_Index = 0; $Command_Part_Index  < count($Command_Parts); $Command_Part_Index++)
	{
		$Command_Part_Word = &$Command_Parts[$Command_Part_Index][0];
		
		switch ($Command_Part_Word)
		{
			// Space/return/tab: ignore
			case ' ':
				// Spaces break the Name clause
				// TODO this is sorta a hack to see if the name clause is set up yet
				if (isset($Clause) && $Clause['Name'] == 'Name' && count($Clause['Items']))
					unset($Clause);
				break;
				
			case "\n":
			case "\r":
			case "\t":
				break;
			
			// Word
			default:
				
				// If this is new selector, for example, the first part in a string, or the first part following a ':'
				if ($Is_New_Command)
				{
					$Is_New_Command = &New_Boolean(false);
					
					// Create the Name clause
					$Clause = &New_Clause();
					if (isset($Language_Terms[strtolower($Command_Part_Word)]))
					{
						$Clause['Name'] = &New_String('Name');
						
						// Initialize clause items array
						$Clause['Items'] = &New_Array();
						$Clause_Item = &New_Array();
						$Clause_Item['Kind'] = &New_String('Variable'); // TODO @core-language: correct type?
						$Clause_Item['Value'] = &$Command_Part_Word;
						$Clause['Items'][] = &$Clause_Item;
						unset($Clause_Item);
						
						// Add clause to this selector
						$Command['Clauses'][strtolower($Clause['Name'])] = &$Clause;
						
						unset($Clause);
						
						// Load selector terms for new selector
						$Command_Terms = &$Language_Terms[strtolower($Command_Part_Word)];
					}
					else
					{
						$Clause['Name'] = &New_String('Name');
					
						// Initialize clause items array
						$Clause['Items'] = &New_Array();
					
						// Add clause to this selector
						$Command['Clauses'][strtolower($Clause['Name'])] = &$Clause;
						
						// Check if command starts with a number
						if (is_numeric($Command_Part_Word))
							$Command['Count'] = &$Command_Part_Word;
					
						// Otherwise, check if command starts with 'new'
						elseif (strtolower($Command_Part_Word) == 'new')
							$Command['New'] = &New_Boolean(true);
						
						else
							$Command_Part_Index--;
					
						// Load selector terms for new selector
						$Command_Terms = &$Language_Terms['default'];
					}
				}
				else
				{
					// Initialize new clause item
					$Clause_Item = &New_Array();
					
					// Check what kind of part we are reading
					switch ($Command_Part_Word)
					{
						case '"':
							// Quote: start-quote
							
							// Advance past quote
							$Command_Part_Index++;
							
							// Initialize new string
							$Quoted_String = &New_String('');
							
							// Advance until we find a close quote
							while ($Command_Part_Index < count($Command_Parts))
							{
								// Load current command part
								$Command_Part_Word = &$Command_Parts[$Command_Part_Index][0];
								
								// If current command part is a quote, break out of advancing loop, otherwise append to string
								if ($Command_Part_Word == '"')
									break;
								else
									$Quoted_String = &New_String($Quoted_String . $Command_Part_Word);
								
								$Command_Part_Index++;
							}
							
							// Set clause item's value to unescaped string
							$Clause_Item['Value'] = &New_String(str_replace('\\"', '"', $Quoted_String));
							
							// Set the type of the clause item to a literal string
							$Clause_Item['Kind'] = &New_String('Text');
							
							$In_Operator = &New_Boolean(false);
							
							break;
							
						case '(':
							// Parenthesis: open parentheses
							
							// Set clause item's value
							$Clause_Item['Value'] = &New_String('(');
							
							// Clause item is a parenthesis
							$Clause_Item['Kind'] = &New_String('Left_Parenthesis');
							
							$Parenthesis_Count++;
							
							break;
							
						case ')':
							// Parenthesis: close parentheses
							
							// Set clause item's value
							$Clause_Item['Value'] = &New_String(')');
							
							// Clause item is a parenthesis
							$Clause_Item['Kind'] = &New_String('Right_Parenthesis');
							
							$Parenthesis_Count--;
							
							if ($Parenthesis_Count == 0)
								$In_Operator = &New_Boolean(false);
							
							break;
							
						default:
							// Word (either a new clause, or a literal string...)
							
							// Store clause item's value
							$Clause_Item['Value'] = &$Command_Part_Word;
							
							// TODO - changed the order of operations here, allowing for selector terms to override all other behavior. dangerous? This used to follow the operator check.
							// Otherwise, check if current part is in selector's selector terms, unless within the 'with' clause, which ignores term names
							if (!$In_Operator && in_array(strtolower($Command_Part_Word), $Command_Terms) && (!isset($Clause) || ($Clause['Name'] != 'with')))
							{
								// A selector term indicates a new clause
								unset($Clause);
								
								// Initialize new clause
								$Clause = &New_Clause();
								
								// Set clause name to this command part
								$Clause['Name'] = &$Command_Part_Word;
								// TODO @feature-language: Name should be the expression up to the first clause (instead of just a single word)
								
								// Initialize clause items array
								$Clause['Items'] = &New_Array();
								
								// Add clause to this selector
								$Command['Clauses'][strtolower($Clause['Name'])] = &$Clause;
								
								// Since current part has now been transformed to a new clause name, continue to next command part
								continue 2;
							}
							
							// Load language term lists.
							global $Language_Constants;
							global $Language_Booleans;

							// Check if the current word is a language constant
							if (array_key_exists(strtolower($Command_Part_Word), $Language_Constants))
							{	
								// Get constant
								$Language_Constant = &$Language_Constants[strtolower($Command_Part_Word)];
								
								// Set kind and value.
								$Clause_Item['Kind'] = &$Language_Constant['Kind'];
								$Clause_Item['Value'] = &$Language_Constant['Value'];

								$In_Operator = &New_Boolean(false);
							}							
							
							// Check if current part is a boolean value
							elseif (in_array(strtolower($Command_Part_Word), $Language_Booleans))
							{
								$Clause_Item['Kind'] = &New_String('Boolean');
								
								if (strtolower($Command_Part_Word) == 'true')
									$Clause_Item['Value'] = &New_Boolean(true);
								else
									$Clause_Item['Value'] = &New_Boolean(false);
									
								$In_Operator = &New_Boolean(false);
							}
							
							// Check if current part is a number
							elseif (is_numeric($Command_Part_Word))
							{
								$Clause_Item['Kind'] = &New_String('Number');				
									
								// If there are two more command parts, consolidate them, test if numeric, and consolidate value and command parts if so.
								if (count($Command_Parts) > $Command_Part_Index + 2)
								{
									$Consolidated_Command_Part_Word = &New_String('');
									for ($Consolidated_Command_Part_Index = 0; $Consolidated_Command_Part_Index < 3; $Consolidated_Command_Part_Index++)
									{	
										// Warning: $Consolidated_Command_Part_Word not by reference
										$Consolidated_Command_Part_Word .= $Command_Parts[$Command_Part_Index + $Consolidated_Command_Part_Index][0];
									}

									if (is_numeric($Consolidated_Command_Part_Word))
									{
										$Clause_Item['Value'] = &$Consolidated_Command_Part_Word;
										$Command_Part_Index+= 2;
									}
								}
								
								$In_Operator = &New_Boolean(false);
							}
							
							// Check if current part is null
							elseif (strtolower($Command_Part_Word) == 'null')
							{
								$Clause_Item['Kind'] = &New_String('Null');
								$Clause_Item['Value'] = &New_Null();
								
								$In_Operator = &New_Boolean(false);
							}							
							
							// Otherwise, check if current part is an operator.
							elseif (isset($Language_Operators[strtolower($Command_Part_Word)]))
							{	
								// Set default operator values
								// Clause item is an operator
								$Clause_Item['Kind'] = &New_String('Operator');								
								$In_Operator = &New_Boolean(true);

								// For '.', test if decimal number or operator, and override default behavior if it is a number.
								if ($Command_Part_Word == '.')
								{
									// TODO - this doesn't take into account the character before the operator.
										
									// If there is one more command, consolidate it, test if numeric, and consolidate value and command parts if so.
									if (count($Command_Parts) > $Command_Part_Index + 1)
									{
										$Consolidated_Command_Part_Word = &New_String('');
										for ($Consolidated_Command_Part_Index = 0; $Consolidated_Command_Part_Index < 2; $Consolidated_Command_Part_Index++)
										{	
											// Warning: $Consolidated_Command_Part_Word not by reference
											$Consolidated_Command_Part_Word .= $Command_Parts[$Command_Part_Index + $Consolidated_Command_Part_Index][0];
										}

										if (is_numeric($Consolidated_Command_Part_Word))
										{
											$Clause_Item['Value'] = &$Consolidated_Command_Part_Word;
											$Clause_Item['Kind'] = &New_String('Number');
											$In_Operator = &New_Boolean(false);
											$Command_Part_Index+=1;
										}
									}
								}

							}
														
							// Otherwise, current part is a variable
							else
							{
								$Clause_Item['Kind'] = &New_String('Variable');
								
								$In_Operator = &New_Boolean(false);
							}
							
							break;
					}
					
					// If this is the first clause in a selector and it hasn't been instantiated, set up the 'first value' clause
					if (!isset($Clause))
					{
						// Initialize new clause
						$Clause = &New_Clause();
						
						// Set new clause name to the first selector term, if it has any defined selector terms, or to a default selector term
						if (count($Command_Terms))
							$Clause['Name'] = &$Command_Terms[0];
						else
							$Clause['Name'] = &New_String('First_Value');
						
						// Initialize clause items array
						$Clause['Items'] = &New_Array();
						
						// Add clause to selector
						$Command['Clauses'][strtolower($Clause['Name'])] = &$Clause;
					}
					
					// Add item to clause
					$Clause['Items'][] = &$Clause_Item;
					
					// Unset clause item
					unset($Clause_Item);
				}
				
				break;
		}
	}
	
	// Parse all clause items into trees
	foreach ($Command['Clauses'] as $Clause_Lookup => &$Clause)
	{
		// If clause has any items, parse them into an expression tree
		if (count($Clause['Items']))
		{
			// Save clause tree
			// TODO - do we need this original string in the clause for any debugging? maybe, i guess, but I'm still going to comment it out
			// $Clause['Original_String'] = &New_String($Command_String);
			$Clause['Tree'] = &Parse_Clause_Into_Tree($Clause);
		}
		
		// Remove clause items
		unset($Command['Clauses'][$Clause_Lookup]['Items']);
	}
	
	// Save parsed command in cache
	$Command_Cache[strtolower($Command_String)] = &$Command;
	
	// Return command
	return $Command;
}

?>