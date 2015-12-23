<?php

// Get Item

// TODO: Permissions

function &Get_Database_Item(&$Database, &$Command, $Memory_Stack_Reference = null)
{					
	// Localize Variables
	$Table_Prefix = &$Database['Table_Prefix'];
	
	// Calculate parameters
	$No_Child_Types = &New_Boolean(isset($Command['Clauses']['no_child_types']));
	if (isset($Command['Clauses']['restrict_type']))
		$Restrict_Type = &$Command['Clauses']['restrict_type']['Tree']['Value'];
	$As_Attachment = &New_Boolean(isset($Command['Clauses']['as_attachment']));
	
	// Render the name clause tree to get the types and relations we will get from the database
	// TODO this is a bit of a hack
	$Copied_Original_Name_Clause_Tree = &Clone_Dot_Tree($Command['Clauses']['name']['Tree']);
	Render_SQL_Name_Expression_Tree($Database, $Command, $Copied_Original_Name_Clause_Tree);
			
	// Check if restricting results to a type
	if (isset($Restrict_Type))
	{
		// Overwrite cached type for dot operator or base variable
		if ($Copied_Original_Name_Clause_Tree['Kind'] == 'Operator')
			$Copied_Original_Name_Clause_Tree['Terms'][0]['Cached_Type'] = &Get_Cached_Type($Database, $Restrict_Type);
		else
			$Copied_Original_Name_Clause_Tree['Cached_Type'] = &Get_Cached_Type($Database, $Restrict_Type);
	}
	
	// Get base type for attachments...

	// TODO - I think this is just a simple check, because you can't drill into simple values for As_Attachment --- but check with tristan
	if ($As_Attachment)
	{
		if (strtolower($Copied_Original_Name_Clause_Tree['Kind']) == 'operator' && $Copied_Original_Name_Clause_Tree['Value'] == '.')
		{
			$Name_Clause_Tree_Cached_Property = &$Copied_Original_Name_Clause_Tree['Cached_Property'];
			$Cached_Base_Type = &$Name_Clause_Tree_Cached_Property['Cached_Type'];
		}
		else
			throw new Exception('Cannot get attachment item without context.');
	}
	
	///Get base type for non-attachments....
	
	else
	{
	
		// Get the base type from the first term (or right-most term if a dot-operator) from the name tree
		// Check if tree node represents a base type or dot-relation between types
		$Recursive_Name_Tree = &$Copied_Original_Name_Clause_Tree;
		while (isset($Recursive_Name_Tree))
		{	
			if (strtolower($Recursive_Name_Tree['Kind']) == 'operator' && $Recursive_Name_Tree['Value'] == '.')
				$Relevant_Term = &$Recursive_Name_Tree['Terms'][0];
			else
				$Relevant_Term = &$Recursive_Name_Tree;
			
			// If the relevant term is a non simple type, this is the base type.
			if (isset($Relevant_Term['Cached_Type']) && !Is_Simple_Type($Relevant_Term['Cached_Type']))
			{
				$Cached_Base_Type = &$Relevant_Term['Cached_Type'];
			
				// Chop off base name tree
				$Base_Name_Tree = &$Relevant_Term;			
				// Break once we've found a cached type
				break;
			}
			// Otherwise...
			else
			{
				// TODO - comment....
				if (isset($Parallel_Tree))
				{
					$New_Left_Term = &New_Tree();
					$New_Left_Term['Kind'] = &$Relevant_Term['Kind'];
					$New_Left_Term['Value'] = &$Relevant_Term['Value'];
				
					$New_Right_Term = &New_Tree();
					$New_Right_Term['Kind'] = &$Current_Parallel_Tree_Node['Kind'];
					$New_Right_Term['Value'] = &$Current_Parallel_Tree_Node['Value'];
				
					$Current_Parallel_Tree_Node['Kind'] = &New_String('Operator');
					$Current_Parallel_Tree_Node['Value'] = &New_String('.');
					$Current_Parallel_Tree_Node['Terms'][0] = &$New_Right_Term;
					$Current_Parallel_Tree_Node['Terms'][1] = &$New_Left_Term;
				}
				else
				{
					$New_Term = &New_Tree();
					$New_Term['Kind'] = &$Relevant_Term['Kind'];
					$New_Term['Value'] = &$Relevant_Term['Value'];
					$Parallel_Tree = &$New_Term;
					$Current_Parallel_Tree_Node = &$Parallel_Tree;
				}
			}
		
			// Move up left branch
			if (strtolower($Recursive_Name_Tree['Kind']) == 'operator' && $Recursive_Name_Tree['Value'] == '.')
				$Recursive_Name_Tree = &$Recursive_Name_Tree['Terms'][1];
			else
				unset($Recursive_Name_Tree);
		}
				
		// Check if we have advanced past any tree terms
		if (isset($Parallel_Tree))
		{
			//TODO this used to die on WHERE, but I just moved the where into the regressive command, which follows other logic in Jelly
	// 		if (isset($Command['Clauses']['where']))
	// 		{
	// 			throw new Exception('"Where" clauses not supported for meta properties of database queries.');
	// 		}
		
			// Generate a new command excluding the final simple property
			$Regressive_Command = &Copy_Command($Command);
		
			// Set command's name to exclude the final simple property
			$Regressive_Command['Clauses']['name']['Tree'] = &$Base_Name_Tree;
		
			// Store and then unset the which clause on the regressive command
			$Progressive_Which_Clause = &$Regressive_Command['Clauses']['which'];
			unset($Regressive_Command['Clauses']['which']);
		
			// TODO: think about how Where clauses get propogated?
			// TODO: I decided where clauses are just applied to the base. Am i wrong?
			$Regressive_Command['Clauses']['where'] = &Copy_Clause($Command['Clauses']['where']);
		
			// Get the complex item from the database
			$Regressive_Item = &Get_Database_Item($Database, $Regressive_Command);
		
			// Generate a new command for the remaining properties
			$Progressive_Command = &New_Command();
			$Progressive_Command['Clauses']['name'] = &New_Clause();
			$Progressive_Command['Clauses']['name']['Name'] = &New_String('Name');
			$Progressive_Command['Clauses']['name']['Tree'] = &$Parallel_Tree;
			$Progressive_Command['Clauses']['which'] = &$Progressive_Which_Clause;
		
			// Get the simple value from the above complex value
			$Value = &Get_Value($Regressive_Item, $Progressive_Command);
		
			return $Value;
		}		
	}

	// Create new item of the base type	
	if (!isset($Cached_Base_Type))
		throw new Exception("No base type determined on Get_Database_Item command");
	$Cached_Base_Type_Alias = &$Cached_Base_Type['Alias'];
	$Item = &Create_Item($Database, $Cached_Base_Type);
		
	// Generate query..
	
	// TODO - this is all a hack for commutative. is there a better way? 
	$Item_Queries = &New_Array();
	$Expression_Tree_Interpretations = &New_Array();
	
	$Is_Commutative_Property = &New_Boolean(false);
	if (strtolower($Copied_Original_Name_Clause_Tree['Kind']) == 'operator' && $Copied_Original_Name_Clause_Tree['Value'] == '.')
	{
		if (array_key_exists('Other_Namespace', $Copied_Original_Name_Clause_Tree['Terms'][0]))
			$Is_Commutative_Property = &New_Boolean(true);
	}
	else
		if (array_key_exists('Other_Namespace', $Copied_Original_Name_Clause_Tree))
			$Is_Commutative_Property = &New_Boolean(true);
			
	if ($Is_Commutative_Property)
		$Expression_Tree_Interpretations = [
					[
						'Use_Other_Namespace_As_Base_Namepsace' => false, 
						'Use_Other_SQL_As_Comparison_SQL' => true
					],
					[
						'Use_Other_Namespace_As_Base_Namepsace' => true, 
						'Use_Other_SQL_As_Comparison_SQL' => false
					]
			];
	else
		$Expression_Tree_Interpretations = [
				[
					'Use_Other_Namespace_As_Base_Namepsace' => false, 
					'Use_Other_SQL_As_Comparison_SQL' => false
				],
			];
	
	// Make a query statement for each interpretation.
	$Recursive_Namespaces = array();
	foreach ($Expression_Tree_Interpretations as &$Expression_Tree_Interpretation)
	{	
		// Get the tables
	
		// Cycle up through Name tree to build From clauses (to left join each type/attachment type (with relevant On clauses))
		// (i.e. In Event.Performer.Member, Event is the left-most 'top' name, and Member is the right-most 'bottom' name)
		// (i.e. In Event.Performer.Member, the tree is structured ((Event).Performer).Member, and the dot preceeding Member (the bottom dot) is parsed first)
		$Recursive_Name_Clause_Tree = &$Copied_Original_Name_Clause_Tree;
		$Tree_Index = 0;
		$From_Clauses = array();
		$Target_Dependent_From_Columns = array();
		while (isset($Recursive_Name_Clause_Tree))
		{
			// Check if tree node represents a base type or dot-relation between types
			if (strtolower($Recursive_Name_Clause_Tree['Kind']) == 'operator' && $Recursive_Name_Clause_Tree['Value'] == '.')
			{
				// If node is a dot, load type from its right child (always a type because dot operator is left-to-right)
				$Recursive_Name_Clause_Tree_Right_Term = &$Recursive_Name_Clause_Tree['Terms'][0];
				$Recursive_Base_Cached_Type = &$Recursive_Name_Clause_Tree_Right_Term['Cached_Type'];
				$Recursive_Kind = &$Recursive_Name_Clause_Tree_Right_Term['Kind'];
				$Recursive_Cached_Property = &$Recursive_Name_Clause_Tree['Cached_Property'];

				// Get namespace
				$Recursive_Namespace = &$Recursive_Name_Clause_Tree_Right_Term['Namespace'];

				// Handle commutative query...
				if (array_key_exists('Other_Namespace', $Recursive_Name_Clause_Tree_Right_Term))
					$Recursive_Other_Namespace = &$Recursive_Name_Clause_Tree_Right_Term['Other_Namespace'];
			}
			else
			{
				// If node is a base type, we are at the left-most (the top) type, so there is a Type but no Property
				$Recursive_Base_Cached_Type = &$Recursive_Name_Clause_Tree['Cached_Type'];
				$Recursive_Kind = &$Recursive_Name_Clause_Tree['Kind'];
				$Recursive_Namespace = &$Recursive_Name_Clause_Tree['Namespace'];

				// Handle commutative query...
				if (array_key_exists('Other_Namespace', $Recursive_Name_Clause_Tree))
					$Recursive_Other_Namespace = &$Recursive_Name_Clause_Tree['Other_Namespace'];

				// TODO - i doubt this is necessary
				unset($Recursive_Cached_Property);
			}
			$Recursive_Namespaces[] = &$Recursive_Namespace;
			
			// Handle commutative query for base namespace
			$Recursive_Base_Namespace = &$Recursive_Namespace;
			if ($Expression_Tree_Interpretation['Use_Other_Namespace_As_Base_Namepsace'])
			{
				if ($As_Attachment)
				{					
					if ($Tree_Index == 1)
						$Recursive_Base_Namespace = &$Recursive_Other_Namespace;
				}
				else
				{
					if ($Tree_Index == 0)		
						$Recursive_Base_Namespace = &$Recursive_Other_Namespace;
				}
			}
		
			// Get all columns for current base type
			$Recursive_Base_Cached_Type_Data_Name = &$Recursive_Base_Cached_Type['Data_Name'];
			$Type_Table_Name = &New_String($Table_Prefix . $Recursive_Base_Cached_Type_Data_Name);
			$Cached_Base_Type_Column_Statements = array();
		
			if ($As_Attachment)
			{
				if ($Tree_Index == 1)
					$Target_Table_Name = &New_String($Table_Prefix . $Recursive_Namespace);
			}

			else
			{
				if ($Tree_Index == 0)
				{
					$Target_Table_Name = &New_String($Table_Prefix . $Recursive_Base_Namespace);
				}
				
				if ($Tree_Index == 1 && $Recursive_Kind == 'Attachment_Type')			
				{
					$Target_Dependent_From_Columns[] = '`' . mysqli_real_escape_string($Database['Link'], $Recursive_Namespace) . '`.`' . 'ID' . '`' . ' AS ' . '`' . 'Attachment_ID' . '`';
					$Target_Dependent_From_Columns[] = '`' . mysqli_real_escape_string($Database['Link'], $Recursive_Namespace) . '`.`' . 'Specific_Type' . '`' . ' AS ' . '`' . 'Attachment_Type' . '`';
				}
			}
		
			foreach($Recursive_Base_Cached_Type['All_Cached_Properties'] as &$Cached_Property)
			{
				// Only fetch simple and single columns
				$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
				$Cached_Property_Value_Type_Alias = &$Cached_Property_Value_Type['Alias'];
				$Cached_Property_Relation = &$Cached_Property['Relation'];
				if (Is_Simple_Type($Cached_Property_Value_Type) || (strtolower($Cached_Property_Relation) == MANY_TO_ONE))
				{
					// Generate column statements depending on value type
					switch (strtolower($Cached_Property_Value_Type_Alias))
					{
						// Can do date/time preconverting here, but instead we may want to keep date functions within PHP in case SQL is setup different
						default:
							// Get property data name
							$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];
					
							// Generate select statement from type table and property data name
							$Cached_Base_Type_Column_Statements[] = '`' . mysqli_real_escape_string($Database['Link'], $Type_Table_Name) . '`.`' . $Cached_Property_Data_Name . '`';
						
							// If the current node is the type we are actually getting, store the dependent columns for our main Select clause
							// TODO @core-language: how to use this to allow different levels (i.e. Attachment or 'Event where Selected_Performer...')
							if ($As_Attachment)
							{
								if ($Tree_Index == 1)
									$Target_Dependent_From_Columns[] = '`' . mysqli_real_escape_string($Database['Link'], $Recursive_Namespace) . '`.`' . $Cached_Property_Data_Name . '`';
							}
							else
							{
								if ($Tree_Index == 0)
									$Target_Dependent_From_Columns[] = '`' . mysqli_real_escape_string($Database['Link'], $Recursive_Base_Namespace) . '`.`' . $Cached_Property_Data_Name . '`';
							}
						
							break;
					}
				}
			}
		
			// Generate column select for each child type of base type
			$From_Statements = array();			
			
			// Create child types array based on No_Child_Types
			if ($No_Child_Types)
			{
				$Query_Types = &New_Array();
				$Query_Types[] = &$Recursive_Base_Cached_Type;
			}
			else
			{
				$Query_Types = &$Recursive_Base_Cached_Type['All_Cached_Child_Types'];
			}
			
			foreach ($Query_Types as &$Query_Type)
			{
				// Get child type alias
				$Query_Type_Alias = &$Query_Type['Alias'];
				$Query_Type_Data_Name = &$Query_Type['Data_Name'];
				$Query_Type_Table_Name = &New_String($Table_Prefix . $Query_Type['Data_Name']);
		
				// Add specific-type to columns
				$Specific_Type_Select = '\'' . mysqli_real_escape_string($Database['Link'], $Query_Type_Alias) . '\' AS `Specific_Type`';
		
				// Add current child type data name to inner From statement
				$From_Statements[] =
					'SELECT ' . implode(', ', $Cached_Base_Type_Column_Statements). '' . ', ' . $Specific_Type_Select .
					' FROM ' . '`' . mysqli_real_escape_string($Database['Link'], $Query_Type_Table_Name) .
					'` AS `' . mysqli_real_escape_string($Database['Link'], $Recursive_Base_Cached_Type_Data_Name) . '`';
			}
		
			// Generate the final MySQL inner From clause unioning the required columns and naming the result with the base type data name
			$From_Clause = '(' . implode(' UNION ', $From_Statements) . ')' . ' ' .
				'`' . mysqli_real_escape_string($Database['Link'], $Recursive_Namespace) . '`';
				
			// Check if a property is set (we are at a dot in the tree instead of the final level)
			if (isset($Recursive_Cached_Property))
			{
				$Recursive_Cached_Property_Relation = &$Recursive_Cached_Property['Relation'];
				$Recursive_Cached_Property_Value_Type = &$Recursive_Cached_Property['Cached_Value_Type'];
			
				// Check if the property is complex
				if (!Is_Simple_Type($Recursive_Cached_Property_Value_Type))
				{
					// For complex types, check if the left term (the next level up) is a dot-relation
					$Recursive_Name_Clause_Tree_Left_Tree = &$Recursive_Name_Clause_Tree['Terms'][1];
					if (strtolower($Recursive_Name_Clause_Tree_Left_Tree['Kind']) == 'operator' && $Recursive_Name_Clause_Tree_Left_Tree['Value'] == '.')
					{
						$Cached_Next_Type = &$Recursive_Name_Clause_Tree_Left_Tree['Terms'][0]['Cached_Type'];
						$Next_Namespace =  &$Recursive_Name_Clause_Tree_Left_Tree['Terms'][0]['Namespace'];
					}
					else
					{
						$Cached_Next_Type = &$Recursive_Name_Clause_Tree['Terms'][1]['Cached_Type'];
						$Next_Namespace = &$Recursive_Name_Clause_Tree['Terms'][1]['Namespace'];
					}
				
					switch (strtolower($Recursive_Cached_Property_Relation))
					{
						case ONE_TO_MANY:
						{
							// For one-to-many properties, the left join is done directly on the child table, matching the key column in the parent table with the key column in the child table...
						
							// Get property's data name
							$Recursive_Cached_Property_Data_Name = &$Recursive_Cached_Property['Data_Name'];
						
							// Get property's key in current type, and data name for the key
							$Recursive_Cached_Property_Key_Alias = &$Recursive_Cached_Property['Key'];
							$Recursive_Cached_Property_Key = &$Cached_Next_Type['Cached_Property_Lookup'][strtolower($Recursive_Cached_Property_Key_Alias)];
							$Recursive_Cached_Property_Key_Data_Name = &$Recursive_Cached_Property_Key['Data_Name'];
						
							// Generate MySQL 'on' clause to join the tables
							$From_Clause .= ' ON ' . '(' .
								'`' . mysqli_real_escape_string($Database['Link'], $Next_Namespace) . '`' .
									'.' . '`' . mysqli_real_escape_string($Database['Link'], $Recursive_Cached_Property_Key_Data_Name) . '`'  .
								' = ' .
								'`' . mysqli_real_escape_string($Database['Link'], $Recursive_Namespace) . '`' . 
									'.' . '`' . mysqli_real_escape_string($Database['Link'], $Recursive_Cached_Property_Data_Name) . '`' . ')';
							break;
						}
						case MANY_TO_ONE:
						{
							// For many-to-one properties, the left join is done directly on the child table, matching the property column in the parent table with the key column in the child table...
						
							// Get property's data name
							$Recursive_Cached_Property_Data_Name = &$Recursive_Cached_Property['Data_Name'];
						
							// Get property's key in current type, and data name for the key
							$Recursive_Cached_Property_Key_Alias = &$Recursive_Cached_Property['Key'];
							$Recursive_Cached_Property_Key = &$Recursive_Base_Cached_Type['Cached_Property_Lookup'][strtolower($Recursive_Cached_Property_Key_Alias)];
							$Recursive_Cached_Property_Key_Data_Name = &$Recursive_Cached_Property_Key['Data_Name'];
						
							// Generate MySQL 'on' clause to join the tables
							$From_Clause .= ' ON ' . '(' .
								'`' . mysqli_real_escape_string($Database['Link'], $Next_Namespace) . '`' .
									'.' . '`' . mysqli_real_escape_string($Database['Link'], $Recursive_Cached_Property_Data_Name) . '`'  .
								' = ' .
								'`' . mysqli_real_escape_string($Database['Link'], $Recursive_Namespace) . '`' . 
									'.' . '`' . mysqli_real_escape_string($Database['Link'], $Recursive_Cached_Property_Key_Data_Name) . '`' . ')';
							break;
						}
					}
				}
			}
		
			// Add from clause to from clauses that will be left joined together
			$From_Clauses[] = &$From_Clause;
			unset($From_Clause);
		
			// Move to next tree item
			if (strtolower($Recursive_Name_Clause_Tree['Kind']) == 'operator' && $Recursive_Name_Clause_Tree['Value'] == '.')
			{
				$Recursive_Name_Clause_Tree = &$Recursive_Name_Clause_Tree['Terms'][1];
				$Tree_Index++;
			}
			else
				unset($Recursive_Name_Clause_Tree);
		}
		
		// Render which/where clause into SQL 
		if (isset($Command['Clauses']['which']))
		{
			$Which_Tree_Base = &$Command['Clauses']['which']['Tree'];
			switch (strtolower($Which_Tree_Base['Kind']))
			{
				case 'variable':
					$Which_Key = &New_String('Alias');
					$Which_Value = '"' . $Which_Tree_Base['Value'] . '"';
					break;
				case 'text':
					$Which_Key = &New_String('Name');
					$Which_Value = '"' . $Which_Tree_Base['Value'] . '"';
					break;
				case 'number':
					$Which_Key = &New_String('ID');
					$Which_Value = $Which_Tree_Base['Value'];
					break;
				default:
					throw new Exception('Unknown which clause type: ' . $Which_Tree_Base['Kind']);
			}
		
			// Generate a new command for the which clause
			$Which_Command_String = &New_String($Cached_Base_Type_Alias . ' Where ' . $Which_Key . ' Is ' . $Which_Value);
		
			// Parse the new command and get its Where tree
			$Which_Command = &Parse_String_Into_Command($Which_Command_String);
			$Which_Clause_Tree = &$Which_Command['Clauses']['where']['Tree'];
		}
	
		// Render Final Where Clause (combining Where and Which clauses)
		if (isset($Command['Clauses']['where']))
		{
			$Where_Clause_Tree = &$Command['Clauses']['where']['Tree'];
			// TODO weird if
			if (isset($Which_Clause_Tree))
			{
				$Where_Tree = &New_Tree();
				$Where_Tree['Kind'] = &New_String('Operator');
				$Where_Tree['Value'] = &New_String('&&');
				$Where_Tree['Operands'] = &New_Array();
				$Where_Tree['Operands'][1] = &$Which_Clause_Tree;
				$Where_Tree['Operands'][0] = &$Where_Clause_Tree;
			}
			else
				$Where_Tree = &$Where_Clause_Tree;
		}
		elseif (isset($Which_Clause_Tree))
			$Where_Tree = &$Which_Clause_Tree;
	
	
	
		// Render where tree into SQL
		if (isset($Where_Tree))
		{
			Render_SQL_Expression_Tree($Command, $Where_Tree, $Database, $Memory_Stack_Reference);

			// TODO - decide how to handle these.
			if (isset($Where_Tree['Not_Found']) && $Where_Tree['Not_Found'])
				throw new Exception('Error resolving where expression');

			// Commutative...
			if ($Expression_Tree_Interpretation['Use_Other_SQL_As_Comparison_SQL'])
				$SQL_Clauses['Where'] = &$Where_Tree['Other_SQL'];
			else
				$SQL_Clauses['Where'] = &$Where_Tree['SQL'];
		}
	
		// Reverse from clauses (so the types go from top to bottom) and build top-level joins
		$From_Clauses = array_reverse($From_Clauses);
		$Join_Command = 'INNER JOIN';
		$SQL_Clauses['From'] = implode(' ' . $Join_Command . ' ', $From_Clauses);
	
		// Distinct
		// TODO: why?)
	//	if (isset($Parameters['Distinct']) && $Parameters['Distinct'])
	//		$Select = 'SELECT DISTINCT ' . implode(', ', $Select_Columns);
	//	else
			$SQL_Clauses['Select'] = 'SELECT ' . implode(', ', $Target_Dependent_From_Columns) . ', `' . mysqli_real_escape_string($Database['Link'], $Target_Table_Name) . '`.`Specific_Type`';
	
		// Check if ordered randomly
		if (isset($Command['Clauses']['random']))
		{
			// TODO @feature-database: either clause tree should be empty (i.e. '1 Page Random') or should evaulate to true (i.e. not '1 Page Random False')
			$SQL_Clauses['Order'] = 'RAND()';
		}
		else
		{
			// If not ordered randomly, load sort order
		
			// Sort Order
			// TODO @feature-database: Order might affect tables/columns fetched, etc.
			// TODO @feature-database: Multiple 'by' values (comma-separated, i.e. 'By Name, Ranking')
			if (isset($Command['Clauses']['by']))
			{
				$By_Tree = &$Command['Clauses']['by']['Tree'];
				Render_SQL_Expression_Tree($Command, $By_Tree, $Database);
				$SQL_Clauses['Order'] = &$By_Tree['SQL'];
			}
			else
			{
				// TODO Merge with By above somehow?
				$Reversed_Recursive_Namespaces = array_reverse($Recursive_Namespaces);
				$Order_Parts = array();
				foreach ($Reversed_Recursive_Namespaces as &$Reversed_Recursive_Namespace)
					$Order_Parts[] = '`' . $Reversed_Recursive_Namespace . '`.`Order`';
				$SQL_Clauses['Order'] = implode(', ', $Order_Parts);
			}
		
			// Direction
			if (isset($Command['Clauses']['ascending']) || isset($Command['Clauses']['descending']))
			{
				if (isset($Command['Clauses']['ascending']))
					$SQL_Clauses['Order'] .= ' ASC';
				elseif (isset($Command['Clauses']['descending']))
					$SQL_Clauses['Order'] .= ' DESC';
			}
	// 		traverse($SQL_Clauses);
		}
			
		if (isset($Command['Clauses']['segment_by']))
		{
			$Segment_By_Tree = &$Command['Clauses']['segment_by']['Tree'];
			Render_SQL_Expression_Tree($Command, $Segment_By_Tree, $Database);
			$SQL_Clauses['Segment_By'] = &$Segment_By_Tree['SQL'];
			$SQL_Clauses['Select'] .= ', ' . 'COUNT(*) as Segment_Count';
			switch (strtolower($Segment_By_Tree['Value']))
			{
				case 'hour':
					$SQL_Clauses['Select'] .= ', ' . 'HOUR(Created) as Segment_By_Value';
					break;
				case 'minute':
					$SQL_Clauses['Select'] .= ', ' . 'MINUTE(Created) as Segment_By_Value';
					break;
			}
		}
	
		// Offset
		if (isset($Command['Clauses']['at']) && $Command['Clauses']['at'])
		{
			// Use maximum MySQL value if offset is specified but not the limit
			$MySQL_Max_Integer = '18446744073709551615';
			if (!isset($SQL_Clauses['Limit']))
				$SQL_Clauses['Limit'] = $MySQL_Max_Integer;
		
			// Set the clause offset
			// TODO @feature-database treat At as an expression
			$SQL_Clauses['Offset'] = mysqli_real_escape_string($Database['Link'], $Command['Clauses']['at']['Tree']['Value']);
		}
	
		// Generate SQL Query
		$Item_Query = &New_String(
				$SQL_Clauses['Select'] .
					' ' . 'FROM' . ' ' .
				$SQL_Clauses['From']
			);

		if (array_key_exists('Where', $SQL_Clauses))
			$Item_Query = &New_String(
					$Item_Query . 
						' ' . 'WHERE' . ' ' .
					$SQL_Clauses['Where']
				);
		
		if (isset($SQL_Clauses['Segment_By']))
			// TODO which iteration to put this in?
			$Item_Query .= ' GROUP BY ' . $SQL_Clauses['Segment_By'];
			
		$Item_Queries[] = &New_String(
				'(' . 
					' ' . $Item_Query . ' ' . 
				')'
			);
	}
	
		// Parse Count
		// TODO @feature-database: should Count be a clause (so treat it as an expression) or a simple value in the Command as it is now?
		if (isset($Command['Count']))
		{
			$SQL_Clauses['Limit'] = mysqli_real_escape_string($Database['Link'], $Command['Count']);
// 			traverse($Command);
		}
	
	$Item_Query = join($Item_Queries, ' ' . 'UNION' . ' ' );
	
	if (isset($SQL_Clauses['Order']))
		$Item_Query .= ' ORDER BY ' . $SQL_Clauses['Order'];
	if (isset($SQL_Clauses['Limit']))
		$Item_Query .= ' LIMIT ' . $SQL_Clauses['Limit'];
	if (isset($SQL_Clauses['Offset']))
		$Item_Query .= ' OFFSET ' . $SQL_Clauses['Offset'];
	
	// Store query
	$Item['Original_Query'] = &$Item_Query;
	
	// Reset item to run query
	Reset_Item($Item);
	
	// Return item
	return $Item;
}

?>