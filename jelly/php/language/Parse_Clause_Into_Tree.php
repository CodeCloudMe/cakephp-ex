<?php

// Parse Clause Items

// TODO @core-language: don't use operators by reference since it messes up the original Clause['Items'] list

function &Parse_Clause_Into_Tree(&$Clause)
{
	// Load language terms
	global $Language_Terms;
	global $Language_Operators;
	
	// Error-Check: clause must contain items
	if (count($Clause['Items']) == 0)
		throw new Exception('Clause does not contain any items to parse.');
	
	// Use Shunting Yard Algorithm
	
	// Setup parse stacks
	$Operator_Stack = array();
	$Operator_Stack_Length = 0;
	$Operand_Stack = array();
	$Operand_Stack_Length = 0;
	
	foreach ($Clause['Items'] as &$Clause_Item)
	{
		$Clause_Item_Value = &$Clause_Item['Value'];
		
		switch (strtolower($Clause_Item['Kind']))
		{
			case 'left_parenthesis':
				$Operator_Stack[$Operator_Stack_Length++] = &$Clause_Item;
				break;
			case 'right_parenthesis':
				// TODO @core-language: check for unmatched
				// https://en.wikipedia.org/wiki/Shunting-yard_algorithm#The_algorithm_in_detail
				while ($Operator_Stack_Length > 0 && $Operator_Stack[$Operator_Stack_Length - 1]['Value'] != '(')
				{
					$Top_Operator = &$Operator_Stack[--$Operator_Stack_Length];
					unset($Operator_Stack[$Operator_Stack_Length]);
					
					$Top_Operator['Terms'] = array();
					for ($Term_Index = 0; $Term_Index < $Language_Operators[strtolower($Top_Operator['Value'])]['Term_Count']; $Term_Index++)
					{
						$Top_Operator['Terms'][] = &$Operand_Stack[--$Operand_Stack_Length];
						unset($Operand_Stack[$Operand_Stack_Length]);
					}
					
					$Operand_Stack[$Operand_Stack_Length++] = &$Top_Operator;
				}
				
				--$Operator_Stack_Length;
				unset($Operator_Stack[$Operator_Stack_Length]);
				
				break;
			case 'boolean':
			case 'number':
			case 'null':
			case 'text':
			case 'date':
			case 'date_time':
			case 'time':
			case 'variable':
				$Operand_Stack[$Operand_Stack_Length++] = &$Clause_Item;
				break;
			case 'operator':
				$Operator = &$Language_Operators[strtolower($Clause_Item_Value)];
				$Operator_Precedence = &$Operator['Precedence'];
				$Operator_Associativity = &$Operator['Associativity'];
				
				while ($Operator_Stack_Length > 0 &&
					(
						($Operator_Associativity == 'Left-To-Right' && $Operator_Precedence == $Language_Operators[strtolower($Operator_Stack[$Operator_Stack_Length-1]['Value'])]['Precedence'])
						||
						($Operator_Precedence < $Language_Operators[strtolower($Operator_Stack[$Operator_Stack_Length-1]['Value'])]['Precedence'])
					)
				)
				{
					$Top_Operator = &$Operator_Stack[--$Operator_Stack_Length];
					unset($Operator_Stack[$Operator_Stack_Length]);
					
					$Top_Operator['Terms'] = array();
					for ($Term_Index = 0; $Term_Index < $Language_Operators[strtolower($Top_Operator['Value'])]['Term_Count']; $Term_Index++)
					{
						$Top_Operator['Terms'][] = &$Operand_Stack[--$Operand_Stack_Length];
						unset($Operand_Stack[$Operand_Stack_Length]);
					}
					
					$Operand_Stack[$Operand_Stack_Length++] = &$Top_Operator;
				}
				
				$Operator_Stack[$Operator_Stack_Length++] = &$Clause_Item;
				
				break;
		}
	}
	
	while ($Operator_Stack_Length > 0)
	{
		$Top_Operator = &$Operator_Stack[--$Operator_Stack_Length];
		unset($Operator_Stack[$Operator_Stack_Length]);
		
		$Top_Operator['Terms'] = array();
		for ($Term_Index = 0; $Term_Index < $Language_Operators[strtolower($Top_Operator['Value'])]['Term_Count']; $Term_Index++)
		{
			$Top_Operator['Terms'][] = &$Operand_Stack[--$Operand_Stack_Length];
			unset($Operand_Stack[$Operand_Stack_Length]);
		}
		
		$Operand_Stack[$Operand_Stack_Length++] = &$Top_Operator;
	}
	
	// Check for unmatched parentheses
	
	$Clause_Tree = &$Operand_Stack[--$Operand_Stack_Length];
	unset($Operand_Stack[$Operand_Stack_Length]);
	
	if ($Operand_Stack_Length)
	{
		traverse($Clause);
		throw new Exception('Still operators on stack: ' . $Clause['Original_String']);
	}
	
	return $Clause_Tree;
}

?>