<?php 

// TODO - lots needs to be cleaned up here, but it works.

function Export_Local_Data_as_XML($Database)
{
	header('HTTP/1.1 200 OK');
	header('Status: 200 OK');
	header('Content-type:' . ' ' . 'text/xml' . ';' . ' ' . 'charset=utf-8');	

	// Get file name
	// TODO generate from site name
	$XML_File_Name = "Export " . date('Y-m-d H\hi')  . ".xml";
	
	// Start XML String
	$XML_String = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	$XML_String .='<Jelly>' . "\n";
	
	// TODO - take out jelly language	
	$Explicit_Types_Block_String = '';
	$Types_Command_String = &New_String('Types from Database by ID as Reference where (Parent_Type exists and Parent_Type is not "")');
	$Types_Processed_Command = &Process_Command_String($Database, $Types_Command_String, $Memory_Stack_Reference);
	$Types_Item = &$Types_Processed_Command['Chunks'][0][Item];						

	$Type_Alias_Array = &New_Array();						
	while(!array_key_exists('End_Of_Results', $Types_Item) || !$Types_Item['End_Of_Results'])
	{
		$Type_Alias_Array[] = &$Types_Item['Data']['Alias'];
		Move_Next($Types_Item);	
	}
	unset($Types_Item);
	unset($Types_Processed_Command);
	
	// TODO - take out Jelly Language
	$Type_Alias_Count = count ($Type_Alias_Array);
	for ($Type_Alias_Index = 0; $Type_Alias_Index < $Type_Alias_Count; $Type_Alias_Index++)
	{
		$Type_Alias = $Type_Alias_Array[$Type_Alias_Index];
	
		$Type_Items_Command_String = &New_String($Type_Alias . ' ' . 'from Database where (Item.Package does not exist or Item.Package = "Local") No_Child_Types as Reference by ID');
		$Types_Items_Processed_Command = &Process_Command_String($Database, $Type_Items_Command_String, $Memory_Stack_Reference);
		$Type_Items_Item = &$Types_Items_Processed_Command['Chunks'][0][Item];
	
		$Type_Item_ID_Array = &New_Array();
		while(!array_key_exists('End_Of_Results', $Type_Items_Item) || !$Type_Items_Item['End_Of_Results'])
		{
			$Type_Item_ID_Array[] = &$Type_Items_Item['Data']['ID'];
			Move_Next($Type_Items_Item);
		}
		unset($Type_Items_Item);
		unset($Types_Items_Processed_Command);
	
		$Item_Count = 0;
	
		$Type_Item_ID_Count = count($Type_Item_ID_Array);
		for ($Type_Item_ID_Index = 0; $Type_Item_ID_Index < $Type_Item_ID_Count; $Type_Item_ID_Index++)
		{
			$Type_Item_ID = $Type_Item_ID_Array[$Type_Item_ID_Index];
			$XML_String .= Generate_XML_For_Item($Database, $Type_Alias, $Type_Item_ID);
			$XML_String .= "\n\n";
		}	
	}
	$XML_String .= "</Jelly>";
	print $XML_String;
}

function Generate_XML_For_Item($Database, $Type_Alias, $Type_Item_ID)
{
	// Setup
	$host = getenv("OPENSHIFT_MYSQL_DB_HOST");
	$user = getenv('OPENSHIFT_MYSQL_DB_USERNAME');
	$pass = getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
	$name = getenv("OPENSHIFT_APP_NAME");

	$mysqli = new mysqli($host,$user,$pass,$name); 
	$mysqli->query("SET NAMES 'utf8'");
	$Type_Alias = $Type_Alias;

	// TODO - update
	$Table_Name = $Type_Alias;
	$Type_Name = $Type_Alias;
	$Item_ID = $Type_Item_ID;
	$Item_Query_String = 'SELECT' . ' ' . '*' . ' ' . 'FROM' . ' ' . $Table_Name . ' ' . 'WHERE' . ' ' . 'ID' . ' = ' . $Item_ID;
	$Item_Query = $mysqli->query($Item_Query_String);
	$Item_Row = $Item_Query->fetch_assoc();

	$Cached_Item_Type = &Get_Cached_Type($Database, strtolower($Type_Alias));

	$Resolved_Item_Row = array();
	foreach($Cached_Item_Type["All_Cached_Properties"] as $Cached_Property)
	{
		$Cached_Property_Alias = $Cached_Property['Alias'];
		$Cached_Property_Data_Name = $Cached_Property['Data_Name'];
		$Resolved_Value = null;
	
		// Forward non-attachment properties only
		if ( (!array_key_exists('Is_Reverse_Property', $Cached_Property) || !$Cached_Property['Is_Reverse_Property']) && (!array_key_exists('Is_Attachment_Property', $Cached_Property) || !$Cached_Property['Is_Attachment_Property']) && (!array_key_exists('Attachment', $Cached_Property) || !$Cached_Property['Attachment']) )
		{
			// Check if not many-to-many and (Type is not Type or relation is not one to many)
			if ($Cached_Property['Relation'] != 'Many-To-Many' && ($Cached_Property['Relation'] != 'One-To-Many' || $Type_Alias != 'Type'))
			{
				if ($Cached_Property['Relation'] == "")
				{
					switch ($Cached_Property['Cached_Value_Type']['Alias'])
					{
						case 'Date':
							if ($Item_Row[$Cached_Property_Data_Name])
								$Resolved_Value = date('Y-m-d', strtotime($Item_Row[$Cached_Property_Data_Name]));
							break;
						case 'Date_Time':
							if ($Item_Row[$Cached_Property_Data_Name])
								$Resolved_Value = date('Y-m-d g:iA', strtotime($Item_Row[$Cached_Property_Data_Name]));
							break;
						case 'Time':
							if ($Item_Row[$Cached_Property_Data_Name])
								$Resolved_Value = date('g:iA', strtotime($Item_Row[$Cached_Property_Data_Name]));
							break;
						default:
							// TODO - format as XML Data
							$Resolved_Value = $Item_Row[$Cached_Property_Data_Name];
							break;
					}
				}
				else
				{
					if (!is_null($Item_Row[$Cached_Property_Data_Name]))
					{
						// For items of with value types that are parent types of meta-data like types, see if it resolves into a meta-data like type, and if so, change behavior to specific type
						// TODO - Just using Item, could be more programmatic later.
						$Search_Type_Alias = $Cached_Property['Cached_Value_Type']['Alias'];
						if ($Search_Type_Alias== 'Item')
						{
							$Search_Connection = new mysqli($host,$user,$pass,$name); 
							$Search_Connection->query("SET NAMES 'utf8'");					
							$Search_Key = $Cached_Property['Key'];
							$Search_Value = $Item_Row[$Cached_Property_Data_Name];
							$Search_Query_String_Parts = array();
							foreach(array('Type', 'Template', 'Type_Action', 'Property') as $Search_Query_String_Part_Data_Name)
							{
								$Search_Query_String_Parts[] = "SELECT `Item`.`ID`, '" . $Search_Query_String_Part_Data_Name . "' AS `Specific_Type` FROM `" . $Search_Query_String_Part_Data_Name . "` AS `Item`";
							}
					
							$Search_Query_String = "SELECT `Item`.`Specific_Type` FROM \n(" . implode("\n UNION \n", $Search_Query_String_Parts) . ")\n AS `Item` WHERE (`Item`.`ID` = ";
							if ($Search_Key == 'Alias')
								$Search_Query_String .= "\"" . $Search_Value. "\"";
							else
								$Search_Query_String .= $Search_Value;
							$Search_Query_String .= ")";

							if (!$Search_Query = $Search_Connection->query($Search_Query_String))
								die($Search_Connection->error);
							$Search_Result = $Search_Query->fetch_row();
							if ($Search_Result !== null)
								$Search_Type_Alias = $Search_Result[0];
						}
						switch ($Search_Type_Alias)
						{	
							// For properties with of metadata-like types, or properties of the parent types of those metadata-like types
							// TODO - 'item' and 'action' are hard coded because they're parent types of metadata-like types, but it could be more programmatic.
							case 'Action':
							case 'Template':
							case 'Type':
							case 'Type_Action':
							case 'Property':						
								$Search_Connection = new mysqli($host,$user,$pass,$name); 
								$Search_Connection->query("SET NAMES 'utf8'");
								$Search_Key = $Cached_Property['Key'];
								$Search_Value = $Item_Row[$Cached_Property_Data_Name];

								if ($Search_Type_Alias == 'Action')
									$Search_Type_Data_Name = 'Type_Action';
								else
									$Search_Type_Data_Name = $Search_Type_Alias;
							
								$Search_Query_String = 'SELECT' . ' ' . '*' . ' ' . 'FROM' . ' ' . '`' . $Search_Type_Data_Name . '`' . ' ' . 'WHERE' . ' ' . $Search_Key. ' = ';
								if ($Search_Key == 'Alias')
									$Search_Query_String .= "\"" . $Search_Value. "\"";
								else
									$Search_Query_String .= $Search_Value;
							
								$Search_Query = $Search_Connection->query($Search_Query_String);
								$Search_Result = $Search_Query->fetch_assoc();
			
								if ($Search_Result !== null)
								{								
									// Create value array
									$Resolved_Value = Array(
										'ID' => $Search_Result['ID'],
										'Alias' => $Search_Result['Alias'],
										'Meta_Type' => $Search_Type_Data_Name,
									);
									if (array_key_exists('Type', $Search_Result))
										$Resolved_Value['Type'] = $Search_Result['Type'];
								}
						
								// For actions, just export the key
								// Should mimic the default case behavior below.
								else if ($Search_Type_Alias == 'Action')
									$Resolved_Value = $Item_Row[$Cached_Property_Data_Name];
						
								// For unresolved values, export the key
								// TODO - or nothing? 
								else
									$Resolved_Value = $Item_Row[$Cached_Property_Data_Name];						

								// Free resources			
								$Search_Query->free();
								$Search_Connection->close();
								break;
					
							// For other types, just export the key.
							default:
								$Resolved_Value = $Item_Row[$Cached_Property_Data_Name];
								break;
						}

					}
					else 
						continue;
				}
			}
		}
	
		// Reverse properties		
		else if ((!array_key_exists('Is_Attachment_Property', $Cached_Property) || !$Cached_Property['Is_Attachment_Property']) && (!array_key_exists('Attachment', $Cached_Property) || !$Cached_Property['Attachment']) )
		{
			if ($Cached_Property['Cached_Value_Type']['Alias'] == 'Type' && strtolower($Cached_Property['Relation']) == 'many-to-one') 
			{ 
				$Value_Cached_Type = &Get_Cached_Type($Database, strtolower($Item_Row[$Cached_Property_Data_Name]));
				$Resolved_Value = Array(
					'ID' => $Value_Cached_Type['ID'],
					'Alias' => $Value_Cached_Type['Alias'],
					'Meta_Type' => 'Type'
				);
			}
			else
				continue;
		}
		
		// Attachment properties
		else if ((!array_key_exists('Is_Attachment_Property', $Cached_Property) || !$Cached_Property['Is_Attachment_Property']))
		{
			if (!is_null($Item_Row[$Cached_Property_Data_Name]))
			{				
				// For items of with value types that are parent types of meta-data like types, see if it resolves into a meta-data like type, and if so, change behavior to specific type
				// TODO - Just using Item, could be more programmatic later.
				$Search_Type_Alias = $Cached_Property['Cached_Value_Type']['Alias'];
				if ($Search_Type_Alias== 'Item')
				{
					$Search_Connection = new mysqli($host,$user,$pass,$name); 
					$Search_Connection->query("SET NAMES 'utf8'");					
					$Search_Key = $Cached_Property['Key'];
					$Search_Value = $Item_Row[$Cached_Property_Data_Name];
					$Search_Query_String_Parts = array();
					foreach(array('Type', 'Template', 'Type_Action', 'Property') as $Search_Query_String_Part_Data_Name)
					{
						$Search_Query_String_Parts[] = "SELECT `Item`.`ID`, '" . $Search_Query_String_Part_Data_Name . "' AS `Specific_Type` FROM `" . $Search_Query_String_Part_Data_Name . "` AS `Item`";
					}
					
					$Search_Query_String = "SELECT `Item`.`Specific_Type` FROM \n(" . implode("\n UNION \n", $Search_Query_String_Parts) . ")\n AS `Item` WHERE (`Item`.`ID` = ";
					if ($Search_Key == 'Alias')
						$Search_Query_String .= "\"" . $Search_Value. "\"";
					else
						$Search_Query_String .= $Search_Value;
					$Search_Query_String .= ")";

					if (!$Search_Query = $Search_Connection->query($Search_Query_String))
						die($Search_Connection->error);
					$Search_Result = $Search_Query->fetch_row();
					if ($Search_Result !== null)
						$Search_Type_Alias = $Search_Result[0];
				}
												
				switch ($Search_Type_Alias)
				{	
					// For properties with of metadata-like types, or properties of the parent types of those metadata-like types
					// TODO - 'item' and 'action' are hard coded because they're parent types of metadata-like types, but it could be more programmatic.
					case 'Action':
					case 'Template':
					case 'Type':
					case 'Type_Action':
					case 'Property':						
						$Search_Connection = new mysqli($host,$user,$pass,$name); 
						$Search_Connection->query("SET NAMES 'utf8'");
						$Search_Key = $Cached_Property['Key'];
						$Search_Value = $Item_Row[$Cached_Property_Data_Name];

						if ($Search_Type_Alias == 'Action')
							$Search_Type_Data_Name = 'Type_Action';
						else
							$Search_Type_Data_Name = $Search_Type_Alias;
							
						$Search_Query_String = 'SELECT' . ' ' . '*' . ' ' . 'FROM' . ' ' . '`' . $Search_Type_Data_Name . '`' . ' ' . 'WHERE' . ' ' . $Search_Key. ' = ';
						if ($Search_Key == 'Alias')
							$Search_Query_String .= "\"" . $Search_Value. "\"";
						else
							$Search_Query_String .= $Search_Value;
							
						$Search_Query = $Search_Connection->query($Search_Query_String);
						$Search_Result = $Search_Query->fetch_assoc();
			
						if ($Search_Result !== null)
						{								
							// Create value array
							$Resolved_Value = Array(
								'ID' => $Search_Result['ID'],
								'Alias' => $Search_Result['Alias'],
								'Meta_Type' => $Search_Type_Data_Name,
							);
							if (array_key_exists('Type', $Search_Result))
								$Resolved_Value['Type'] = $Search_Result['Type'];
						}
						
						// For actions, just export the key
						// Should mimic the default case behavior below.
						else if ($Search_Type_Alias == 'Action')
							$Resolved_Value = $Item_Row[$Cached_Property_Data_Name];
						
						// For unresolved values, export the key
						// TODO - or nothing? 
						else
							$Resolved_Value = $Item_Row[$Cached_Property_Data_Name];						

						// Free resources			
						$Search_Query->free();
						$Search_Connection->close();
						break;
					
					// For other types, just export the key.
					default:
						$Resolved_Value = $Item_Row[$Cached_Property_Data_Name];
						break;
				}
			}
			else 
				continue;
		}
		else
			continue;
		
		// Store value
		$Resolved_Item_Row[$Cached_Property_Alias] = $Resolved_Value;
	}
	
	$XML_String = "";
	$XML_String .=  '<!-- ' . 
		htmlspecialchars($Type_Name  . ' ' . '"' . $Resolved_Item_Row['Name'] .'"' . ' (ID: ' . $Resolved_Item_Row['ID']. ')' . ' ' , ENT_XML1, 'UTF-8') . 
		'-->' . "\n";
	
	$XML_String.= '<' .
			htmlspecialchars($Type_Alias, ENT_XML1, 'UTF-8') .
			'>' . "\n";
	foreach ($Resolved_Item_Row as $Resolved_Item_Key => $Resolved_Item_Value)
	{
		$XML_String.= "\t" . '<' .
			htmlspecialchars($Resolved_Item_Key, ENT_XML1, 'UTF-8') .
			'>';

		if (is_array($Resolved_Item_Value))
		{
			$XML_String .= "\n";
			foreach ($Resolved_Item_Value as $Resolved_Item_Value_Key => $Resolved_Item_Value_Value)
			{
				$XML_String.= "\t" . "\t" . '<' .
					htmlspecialchars($Resolved_Item_Value_Key, ENT_XML1, 'UTF-8') .
					'>';
				$XML_String .= htmlspecialchars($Resolved_Item_Value_Value, ENT_XML1, 'UTF-8');
				$XML_String .= '</' .
					htmlspecialchars($Resolved_Item_Value_Key, ENT_XML1, 'UTF-8') .
					'>' . "\n";
			}
			$XML_String .= "\t";
		}
		else
			$XML_String .= htmlspecialchars($Resolved_Item_Value, ENT_XML1, 'UTF-8');
				
		$XML_String .= '</' .
			htmlspecialchars($Resolved_Item_Key, ENT_XML1, 'UTF-8') .
			'>' . "\n";
	}
	$XML_String.= '</' .
			htmlspecialchars($Type_Alias, ENT_XML1, 'UTF-8') .
			'>';
	return $XML_String;
	$mysqli->close();
}

?>