<?php

// Render SQL Name Expression Tree

function Render_SQL_Name_Expression_Tree(&$Database, &$Command, &$Tree)
{
	// Check if current tree node is a dot-relation or a simple variable
	switch (strtolower($Tree['Kind']))
	{
		// Operator Nodes
		case 'operator':
		{
			switch (strtolower($Tree['Value']))
			{
				case '.':
				{
					// Dot operators represent relations between types and properties...
				
					// Render left operand (the types leading up to (above) the current dot-relation
					$Left_Operand = &$Tree['Terms'][1];
					Render_SQL_Name_Expression_Tree($Database, $Command, $Left_Operand);
					
					// TODO - this isn't exactly the right place to store this, but if I write 'browser_controls.variables.<name>, i get this below exception. doesn't seem like the appropriate flow, might uncover a bug.
					if (isset($Left_Operand['Is_Metadata_Property']))
						throw new Exception("Getting properties of metadata properties of database items not supported.");
					
					if (isset($Left_Operand['Cached_Type']))
					{
						$Left_Cached_Type = &$Left_Operand['Cached_Type'];
						$Left_Namespace = &$Left_Operand['Namespace'];
					}
					else
					{
						$Left_Right_Operand = &$Left_Operand['Terms'][0];
						$Left_Cached_Type = &$Left_Right_Operand['Cached_Type'];
						$Left_Namespace = &$Left_Right_Operand['Namespace'];
					}
					
				
					// Get the right operand (always a simple variable node representing the current property)
					$Right_Operand = &$Tree['Terms'][0];
	
					// Look up property in current type and store in the dot-relation operator node
					$Property_Lookup = &$Right_Operand['Value'];
					global $Metadata_Properties;
					if ((!isset($Command['Clauses']['from']) || strtolower($Command['Clauses']['from']['Tree']['Value']) != 'database') && in_array(strtolower($Property_Lookup), $Metadata_Properties))
					{
						$Tree['Is_Metadata_Property'] = &New_Boolean(true);
					}
					else
					{
						if (!isset($Left_Cached_Type["Cached_Property_Lookup"][strtolower($Property_Lookup)]))
							throw new Exception('No property set: ' . $Property_Lookup);
						$Cached_Property = &$Left_Cached_Type["Cached_Property_Lookup"][strtolower($Property_Lookup)];
					
						// Check if property is simple or complex
						$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
						if (Is_Simple_Type($Cached_Property_Value_Type))
						{
							// Store resulting property value type in right operand
							$Right_Operand['Cached_Type'] = &$Cached_Property_Value_Type;
						
							// Store current property in the tree
							$Tree['Cached_Property'] = &$Cached_Property;
						}
						else
						{
							// Property is complex...
						
							// Check if property is many-to-many or commutative
							// TODO - switch
							if (strtolower($Cached_Property['Relation']) == COMMUTATIVE)
							{
								// Property is Commutative
							
								// For commutative, insert a new operator for the attachment type
								// TODO - better comment
								$Cached_Property_Attachment_Type = &$Cached_Property['Cached_Attachment_Type'];
								$Cached_Property_Type = &$Cached_Property['Cached_Type'];
								
								$New_Tree = &New_Tree();
								$New_Tree['Kind'] = &New_String('Operator');
								$New_Tree['Value'] = &New_String('.');

								// Pass current left operand up to the new tree
								$New_Tree['Terms'][1] = &$Left_Operand;
								
								// Get attachment type property alias
								$Attachment_Type_Property_Lookup = &$Cached_Property['Alias'];
								$Attachment_Type_Property = &$Cached_Property_Attachment_Type['Cached_Property_Lookup'][strtolower($Attachment_Type_Property_Lookup)];
								$Attachment_Type_Property_Alias = &$Attachment_Type_Property['Alias'];
								
								// Get attachment type other property alias
								$Attachment_Type_Other_Property_Lookup = &New_String('Other' . '_' . $Cached_Property['Alias']);
								$Attachment_Type_Other_Property = &$Cached_Property_Attachment_Type['Cached_Property_Lookup'][strtolower($Attachment_Type_Other_Property_Lookup)];
								$Attachment_Type_Other_Property_Alias = &$Attachment_Type_Other_Property['Alias'];
								
								// Get attachment property alias
								$Attachment_Property_Lookup = &New_String($Cached_Property['Alias'] . '_' . 'Attachment');
								$Attachment_Property = &$Cached_Property_Type['Cached_Property_Lookup'][strtolower($Attachment_Property_Lookup)];
								$Attachment_Property_Alias = &$Attachment_Property['Alias'];
								
								// Get attachment other property alias
								$Attachment_Other_Property_Lookup = &New_String('Other' . '_' . $Cached_Property['Alias'] . '_' . 'Attachment');
								$Attachment_Other_Property = &$Cached_Property_Type['Cached_Property_Lookup'][strtolower($Attachment_Other_Property_Lookup)];
								$Attachment_Other_Property_Alias = &$Attachment_Other_Property['Alias'];
								
								//  TODO - comment
								$Left_Operand['Namespace'] = &New_String($Left_Namespace . '_' . $Attachment_Type_Other_Property_Alias . '_Value');

								// Make a new right tree representing the attachment
								$New_Right_Tree = &New_Tree();
								$New_Right_Tree['Kind'] = &New_String('Attachment_Type');
								$New_Right_Tree['Cached_Type'] = &$Cached_Property_Attachment_Type;
								
								// Set attachment property's namespace.
								$Right_Namespace = &New_String($Left_Namespace . '_' . $Attachment_Type_Property_Alias . '_' . 'Attachment');
								$New_Right_Tree['Namespace'] = &$Right_Namespace;
								
								//Attachment property becomes the new right operand.
								$New_Tree['Terms'][0] = &$New_Right_Tree;
								//TODO - doubt this is needed.
								unset($New_Right_Tree);
						
								// Set the new tree's cached property and cached other property to the attachment properties
								$New_Tree['Cached_Property'] = &$Attachment_Property;
								$New_Tree['Cached_Other_Property'] = &$Attachment_Other_Property;
						
								// Set the current tree's cached property to the attachment type's properties
								$Tree['Cached_Property'] = &$Attachment_Type_Property;
								$Tree['Cached_Other_Property'] = &$Attachment_Type_Other_Property;
						
								// Replace the left operand of the current tree with the new tree
								$Tree['Terms'][1] = &$New_Tree;
								
								$Right_Operand['Cached_Type'] = &$Cached_Property_Value_Type;
								$Right_Operand['Namespace'] = &New_String($Left_Namespace . '_' . $Attachment_Type_Property_Alias . '_Value');
								$Right_Operand['Other_Namespace'] = &New_String($Left_Namespace . '_' . $Attachment_Type_Other_Property_Alias . '_Value');

							}
							else if (strtolower($Cached_Property['Relation']) == MANY_TO_MANY)
							{
								// Property is Many-To-Many...
							
								// For properties that use attachment types (complex many-to-many), insert a new operator for the attachment type
								$Cached_Property_Attachment_Type = &$Cached_Property['Cached_Attachment_Type'];
							
								$New_Tree = &New_Tree();
								$New_Tree['Kind'] = &New_String('Operator');
								$New_Tree['Value'] = &New_String('.');
						
								// Pass current left operand up to the new tree
								$New_Tree['Terms'][1] = &$Left_Operand;
						
							
								// Make a new right tree representing the attachment
								$New_Right_Tree = &New_Tree();
								$New_Right_Tree['Kind'] = &New_String('Attachment_Type');
								$New_Right_Tree['Cached_Type'] = &$Cached_Property_Attachment_Type;

								// Set attachment property's namespace.
								$Right_Namespace = &New_String($Left_Namespace . "_" . $Cached_Property['Alias'] . '_Attachment');
								$New_Right_Tree['Namespace'] = &$Right_Namespace;
						
								//Attachment property becomes the new right operand.
								$New_Tree['Terms'][0] = &$New_Right_Tree;
								unset($New_Right_Tree);
						
								// Get attachment properties
								$Cached_Property_Alias = &$Cached_Property['Alias'];
								$Cached_Property_Type = &$Cached_Property['Cached_Type'];
		//						traverse($Cached_Property_Type['Cached_Property_Lookup']);
								$Cached_Attachment_Type_One_To_Many_Property = &$Cached_Property_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Alias . '_Attachment')];
						
								$Cached_Attachment_Type_Many_To_One_Property = &$Cached_Property_Attachment_Type['Cached_Property_Lookup'][strtolower($Cached_Property_Alias)];
						
								// Set the new tree's cached property to the one to many property of the attachment
								$New_Tree['Cached_Property'] = &$Cached_Attachment_Type_One_To_Many_Property;
		//						$New_Tree['Cached_Type'] = &$Cached_Property_Attachment_Type;

						
								// Set the current tree's cached property to the many to one property of the attachment
								$Tree['Cached_Property'] = &$Cached_Attachment_Type_Many_To_One_Property;
						

		//						$Tree['Cached_Type'] = &$Cached_Property_Value_Type;
						
								// Replace the left operand of the current tree with the new tree
								$Tree['Terms'][1] = &$New_Tree;
						
								$Right_Operand['Cached_Type'] = &$Cached_Property_Value_Type;
								$Right_Operand['Namespace'] = &New_String($Left_Namespace . '_' . $Cached_Property['Alias'] . '_Value');
						
		//						traverse($Tree);
							}
							else
							{
								// Property is Many-To-One or One-To-Many...
							
								// For properties that don't use attachment types, use the operator as-is...
						
								// Store resulting property value type in right operand
								$Right_Operand['Cached_Type'] = &$Cached_Property_Value_Type;
							
								// Store new namespace in the right operand
								$Right_Operand['Namespace'] = &New_String($Left_Namespace . '_' . $Cached_Property['Alias']);
						
								// Store current property in the tree
								$Tree['Cached_Property'] = &$Cached_Property;
							}
						}
					}
				
					break;
				}
				
				// Error checking
				default:
					throw new Exception('Unsupported tree operator: ' . $Tree['Value']);
					break;
			}
			break;
		}
		
		// Variable Nodes
		case 'variable':
		{
			// If node is a variable, it represents the left-most "top" type...
			
			// TODO - This used to populate the Cached Type/Namespace no matter what, overwriting the value that may have been generated from a previous pass.  I made it only get the cached_type if it wasn't already there.  Not sure if it's destructive.
		
			if(!array_key_exists('Cached_Type', $Tree))
			{
				$Type_Lookup = &$Tree['Value'];				

				// Store type in current tree node
				$Tree_Cached_Type = &Get_Cached_Type($Database, $Type_Lookup);
				$Tree['Cached_Type'] = &$Tree_Cached_Type;	
			}

			if(!array_key_exists('Namespace', $Tree))
			{
				// Store namespace in current tree node.
				$Tree_Namespace = &$Tree_Cached_Type['Alias'];
				$Tree['Namespace'] = &$Tree_Namespace;
			}
			break;
		}
	
		// Error Checking
		default:
			throw new Exception('Unsupported tree type: ' . $Tree['Kind']);
			break;
	}
// 	traverse($Tree);
	
}

?>