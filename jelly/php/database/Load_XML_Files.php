<?php

// TODO: This supports matching existing types and properties and templates, but does not support matching other existing data.
// TODO: Actually verify types, properties, templates by item rather than saving by string.
// TODO: check in on default values (i.e. Template.Content_Type)
function Load_XML_Files(&$Database, &$XML_File_Paths, $Parameters = array())
{	
	global $Simple_Types;
	$String_Match_Types =  array('property', 'template', 'type');	
	// Warning: $Core_Types not by reference
	$Core_Types = array('simple_item', 'item', 'type', 'property');
	$Core_Properties = array(
		'item' => array('id', 'created', 'order', 'alias', 'name'),
		'type' => array('parent_type'),
		'property' => array(),
		);
		
	// Set default package value (Core for reset, Local for other cases)
	// TODO - might be a better way to do this.  e.g. You shouldn't need Local.
	if ($Parameters['First_Time'])
		$Default_Package_Value = &New_String('Core');
	else
		$Default_Package_Value = &New_String('Local');
		
	$Default_Viewable_By_Value = &New_String('Admin');
	$Property_Default_Viewable_By_Value = &New_String('Public');
	
	// Load XML Items from each file
	$Loaded_XML_Items_By_Type = &New_Array();
	// Warning: XML_File_Path not by reference
	foreach ($XML_File_Paths as &$XML_File_Path)
	{
		// Read XML File
		// Warning: $File_Data not by reference
		$File_Data = file_get_contents($XML_File_Path);
		
		// Replace brackets
		// TODO - maybe more explicit format setting can help here :) 
		if (substr($File_Data, 0, 1) == '{')
		{
			$File_Data = str_replace('&', '&amp;', $File_Data);
			$File_Data = str_replace('<', '&lt;', $File_Data);
			$File_Data = str_replace('>', '&gt;', $File_Data);
			$File_Data = str_replace('\{', 'OPENBRACKET', $File_Data);
			$File_Data = str_replace('\}', 'CLOSEBRACKET', $File_Data);
			$File_Data = str_replace('{', '<', $File_Data);
			$File_Data = str_replace('}', '>', $File_Data);
			$File_Data = str_replace('OPENBRACKET', '{', $File_Data);
			$File_Data = str_replace('CLOSEBRACKET', '}', $File_Data);
		}
	
		// Load XML
// 			Traverse($File_Data);
		$Defaults = new SimpleXMLElement($File_Data);
		
		if ($Defaults === false)
		{
			Traverse("XML Error");
			Traverse($File_Data);
			exit();
		}
		
		// Populate items array
		// Warning: XML_Node not by reference
		foreach ($Defaults->children() as $XML_Node)
		{
			// Cycle through Item, loading its properties
			
			// Get item type alias
			$Item_Type_Alias = &New_String((string) $XML_Node->getName());
			
			// Create new values array and add to XML items
			$Loaded_XML_Item = &New_Array();
			$Loaded_XML_Items_By_Type[strtolower($Item_Type_Alias)][] = &$Loaded_XML_Item;
			
			// Cycle through item's children values
			// Warning: $Item_Property_Alias not by reference
			// Warning: $Item_Property_Value not by reference
			foreach ($XML_Node->children() as $Item_Property_Alias => $Item_Property_Value)
			{
				$Item_Property_XML_Values = &New_Array();
				
				// Get value as string
				// TODO: better way?
				// TODO: better way?
				if ($Item_Property_Value->ID)
				{
					foreach($Item_Property_Value as $Item_Property_Value_Key => $Item_Property_Value_Value)
					{
						$Item_Property_XML_Values[$Item_Property_Value_Key] =  &New_String((string) $Item_Property_Value_Value);
					}
				}
				else
					$Item_Property_XML_Values['Value'] = &New_String((string) $Item_Property_Value);
				
				// Get attributes
// 				$Attributes = &$Item_Property_Value->attributes();
				
				// TODO get from attributes
				// Base 64
				// Remove indentation and leading white space
				$XML_Value = &New_Null();
				foreach($Item_Property_XML_Values as $Item_Property_XML_Value_Key => $Item_Property_XML_Value_Value)
				{
					$New_Value = &$Item_Property_XML_Value_Value;
					
					$Return_Count = 0;
					// TODO : Does this make sense?
// 					if (!$New_Value)
// 						continue;
					
					while ($New_Value[$Return_Count] == "\n")
						$Return_Count++;
					$New_Value = &New_String(substr($New_Value, $Return_Count));
					
					// Count and move past tabs
					$Tab_Count = 0;
					while ($Tab_Count < strlen($New_Value) && $New_Value[$Tab_Count] == "\t")
						$Tab_Count++;
				
					// Get tabs string to be removed later
					// Warning: $Tabs not by reference
					$Tabs = substr($New_Value, 0, $Tab_Count);
				
					// Get value
					$New_Value = &New_String(substr($New_Value, $Tab_Count));
				
					// Remove tabs from value
					$New_Value = &New_String(str_replace("\n" . $Tabs, "\n", $New_Value));
				
					// TODO What is this
					// Warning: $Tab_Count not by reference
					$Tab_Count = 0;
					while ((strlen($New_Value) - $Tab_Count - 1) >= 0 && (strlen($New_Value) - $Tab_Count - 1) < strlen($New_Value) && ($New_Value[strlen($New_Value) - $Tab_Count - 1] == "\t" || $New_Value[strlen($New_Value) - $Tab_Count - 1] == "\n"))
						$Tab_Count++;
					$New_Value = &New_String(substr($New_Value, 0, strlen($New_Value) - $Tab_Count));
				
					if ($Item_Property_XML_Value_Key == 'Value')
						$XML_Value = &$New_Value;
					else
						$XML_Value[$Item_Property_XML_Value_Key] = &$New_Value;
				}
				
				// Store value or add/create array
				if (!isset($Loaded_XML_Item[$Item_Property_Alias]))
					$Loaded_XML_Item[$Item_Property_Alias] = &New_Array();
				$Loaded_XML_Item[$Item_Property_Alias][] = &$XML_Value;
			}
		}
	}

	//	traverse($Loaded_XML_Items_By_Type);

	// Upgrade tweaks per version?
	// TODO - set this somehow
	$Upgrade_Mode = &New_Boolean(false);
	if (!$Parameters['First_Time'])
		$Upgrade_Mode = &New_Boolean(true);
	if ($Upgrade_Mode)
	{
		// TODO - check version

		// Upgrade folder child items...

		// Check the current folder items.
		// TODO - don't actualy need id... 
		$Folder_Child_Type_DB_Query = "SELECT Type.ID, Type.Alias FROM Folder_Child_Item, Type where Folder_Child_Item.Child_Item = Type.ID";
		$Folder_Child_Type_Result = &Query($Database, $Folder_Child_Type_DB_Query);
		$Folder_Child_Types_To_Make = array();
		while ($Folder_Child_Type_Row = mysqli_fetch_assoc($Folder_Child_Type_Result))
		{
			$Folder_Child_Types_To_Make[$Folder_Child_Type_Row['Alias']] = $Folder_Child_Type_Row['ID'];
		}
		mysqli_free_result($Folder_Child_Type_Result);
		
		if (count($Folder_Child_Types_To_Make) > 0)
		{
			// For each valid folder child item, if it's an array, with an alias, remove it
			// Happens to delete broken links too
			foreach ($Loaded_XML_Items_By_Type['folder_child_item'] as $XML_Folder_Child_Item_Index => &$XML_Folder_Child_Item)
			{	
				if (
						array_key_exists('Parent_Folder', $XML_Folder_Child_Item) && $XML_Folder_Child_Item['Parent_Folder'][0] && 
						array_key_exists('Child_Item', $XML_Folder_Child_Item) && $XML_Folder_Child_Item['Child_Item'][0]
					)
				{
					if (is_array($XML_Folder_Child_Item['Child_Item'][0]) && $XML_Folder_Child_Item['Child_Item'][0]['Meta_Type'] == 'Type')
						unset($Folder_Child_Types_To_Make[$XML_Folder_Child_Item['Child_Item'][0]['Alias']]);
				}
				else
					unset($Loaded_XML_Items_By_Type['folder_child_item'][$XML_Folder_Child_Item_Index]);
			}
		}
		
		// Add the rest
		foreach($Folder_Child_Types_To_Make as $Child_Type_Alias => &$Child_Type_ID)
		{

			// Get current parent folder id
			$Folder_ID = &$Loaded_XML_Items_By_Type['folder'][0]['ID'][0];
			$Folder_Child_Type_Item_XML = &$_;
			$_ = array(
				'Parent_Folder' => array(&$Folder_ID),
				'Child_Item' => array($Child_Type_Alias)
			);
			unset($_);
			$Loaded_XML_Items_By_Type['folder_child_item'][] = &$Folder_Child_Type_Item_XML;
		}

		// Replace action get involved content & code with new version.
		foreach ($Loaded_XML_Items_By_Type['action'] as &$XML_Action)
		{	
			if ($XML_Action['Alias'][0] == 'Get_Involved')
			{
				$Action_Get_Involved_Command_String = &New_String('Action "Get Involved" from Database as Reference');
				$Processed_Action_Get_Involved_Command = &Process_Command_String($Database, $Action_Get_Involved_Command_String);
				$Action_Get_Involved_Item = &$Processed_Action_Get_Involved_Command['Chunks'][0]['Item'];				
				$XML_Action['Content'][0] = &$Action_Get_Involved_Item['Data']['Content'];
				$XML_Action['Code'][0] = &$Action_Get_Involved_Item['Data']['Code'];
			}
		}
		
		// Replace scripting module content & code with new version.
		if (count($Loaded_XML_Items_By_Type['scripting_module']) == 1)
		{
			$XML_Scripting_Module = &$Loaded_XML_Items_By_Type['scripting_module'][0];
			$Teams_Scripting_Module_Command_String = &New_String('1 Scripting_Module from Database as Reference');
			$Processed_Teams_Scripting_Module_Command = &Process_Command_String($Database, $Teams_Scripting_Module_Command_String);
			$Teams_Scripting_Module_Item = &$Processed_Teams_Scripting_Module_Command['Chunks'][0]['Item'];
			$XML_Scripting_Module['Script'][0] = &$Teams_Scripting_Module_Item['Data']['Script'];
		}
	}
	
	// Apply filters
	foreach ($Loaded_XML_Items_By_Type as $XML_Type_Lookup => &$Loaded_XML_Items)
	{
		foreach ($Loaded_XML_Items as &$Loaded_XML_Item)
		{
			// Generate lookup from alias, name, or none.
			if (array_key_exists('Alias', $Loaded_XML_Item))
				$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'] = &$Loaded_XML_Item['Alias'][0];
			else if (array_key_exists('Name', $Loaded_XML_Item))
				$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'] = &New_String(Alias_From_Name($Loaded_XML_Item['Name'][0]));
			else
				$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'] = &New_Null();
			
			switch (strtolower($XML_Type_Lookup))
			{
				case 'type':
					// Set identification method
					$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Query_Method'] = &New_String('Lookup');

					// Get type alias
					$Type_Lookup = &$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'];
										
					// Set flags
					if (in_array(strtolower($Type_Lookup), $Simple_Types) || in_array(strtolower($Type_Lookup), $Core_Types))
					{
						// If the type is a core type, mark it as a core type
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Is_Core'] = &New_Boolean(true);					

						// If this is a reset database call, then create all core types
						if ($Parameters['First_Time'])
							$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Create');
							
						// If this is an import xml call, then ignore all the core types
						else
							$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Ignore');
					}
				
					else
					{
						// For non-core types, mark as not a core type
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Is_Core'] = &New_Boolean(false);
						
						// For non-core types, update the current record with modified values.
						// TODO - or replace? 
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Update');
					}					
					break;
					
				case 'property':
					// Set identification method
					$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Query_Method'] = &New_String('Type_And_Lookup');
					
					// Get property alias
					$Property_Lookup = &$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'];
					
					// Get property type alias
					if (is_array($Loaded_XML_Item['Type'][0]))
						$Property_Type_Alias = &$Loaded_XML_Item['Type'][0]['Alias'];
					else
						$Property_Type_Alias = &$Loaded_XML_Item['Type'][0];
					
					// Set flags					
					// TODO - not sure where this should be caught, but this will let you overload core properties.  Not really about import so I ignored it.
					if (
							array_key_exists(strtolower($Property_Type_Alias), $Core_Properties) 
								&&
							in_array(strtolower($Property_Lookup), $Core_Properties[strtolower($Property_Type_Alias)])
						)
					{
						// If the type is a core property, mark it as a core property
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Is_Core'] = &New_Boolean(true);
						
						// If this is a reset database call, then create all core properties
						if ($Parameters['First_Time'])
							$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Create');
							
						// If this is an import xml call, then ignore all the core properties
						else
							$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Ignore');

					}
					
					else
					{
						// For non-core properties, mark as not a core property
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Is_Core'] = &New_Boolean(false);
						
						// For non-core properties, update the current record with modified values.
						// TODO - or replace? 
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Update');
					}
					break;

				// TODO - this had some vision of "cleaning" -- dumping all things associated in the package and reinstalling.
// 				case 'package':
// 					break;
				case 'site':
					// Set the identification method
					$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Query_Method'] = &New_String('All');
					
					//	Set the behavior to replace all records, unless otherwise specified
					if ($Parameters['Behavior'] == 'Append')
						// TODO - lots of bugs in the append version, not done yet	
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Create');
					else	
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Update'); 
					break;
					
				// TODO - not sure if necessary
				case 'status':
					// Set the identification method
					$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Query_Method'] = &New_String('Lookup');	
					
					// For statuses, update the current record with modified values.
					$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Update');
					break;
									
				default:
					// Set the identification method
					switch (strtolower($XML_Type_Lookup))
					{
						case 'type_action':
						case 'template':
							$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Query_Method'] = &New_String('Type_And_Lookup');
							break;
						default:
							$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Query_Method'] = &New_String('Lookup');
							break;
					}
					
					//	Set the behavior to replace the current record, unless otherwise specified
					if ($Parameters['Behavior'] == 'Append')
						// TODO - lots of bugs in the append version, not done yet	
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Create');
					else	
						$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] = &New_String('Replace');
					break;
			}
		}
	}
		
	// Populate ID and Alias lookups
	$ID_Lookup = &New_Array();
	$Alias_Lookup_By_Type = &New_Array();
	
	// Warning: $Type_Alias not by reference
	foreach ($Loaded_XML_Items_By_Type as $XML_Type_Lookup => &$Loaded_XML_Items)
	{
		foreach ($Loaded_XML_Items as &$Loaded_XML_Item)
		{			
			if ($Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Behavior'] != 'Ignore')
			{
				// If item has an ID, store it in the ID lookup and clear the old ID
				if (isset($Loaded_XML_Item['ID']))
				{
					$Loaded_XML_Item_ID = &$Loaded_XML_Item['ID'][0];
					$ID_Lookup[$Loaded_XML_Item_ID] = &$Loaded_XML_Item;
					unset($Loaded_XML_Item['ID']);

					// TODO: Rewrote the below but unsure. - Kunal
				
					/*
					$Loaded_XML_Item_ID = &$Loaded_XML_Item['ID'][0];
					if (!isset($ID_Lookup_By_Type[strtolower($XML_Type_Alias)]))
						$ID_Lookup_By_Type[strtolower($XML_Type_Alias)] = &New_Array();
					$ID_Lookup_By_Type[strtolower($XML_Type_Alias)][$Loaded_XML_Item_ID] = &$Loaded_XML_Item;
					unset($Loaded_XML_Item['ID']);
					*/
				}
			
				// If item has a unique alias, store it in the unique alias lookup
				// TODO - not sure why string match types excludes types, but added it back in for this one case anyway.
				if ((!in_array(strtolower($XML_Type_Lookup), $String_Match_Types) || strtolower($XML_Type_Lookup) == 'type') && (isset($Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'])))
				{
					$Loaded_XML_Item_Lookup = &$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'];
					if (!isset($Alias_Lookup_By_Type[strtolower($XML_Type_Lookup)]))
						$Alias_Lookup_By_Type[strtolower($XML_Type_Lookup)] = &New_Array();
					$Alias_Lookup_By_Type[strtolower($XML_Type_Lookup)][strtolower($Loaded_XML_Item_Lookup)] = &$Loaded_XML_Item;
				}
			}
		}
	}
		
	// Load simple/core types into cache.
	$All_Cached_Types = &$Database['All_Cached_Types'];
	$Cached_Type_Lookup = &$Database['Cached_Type_Lookup'];
	if (isset($Loaded_XML_Items_By_Type["type"]))
	{
		$Loaded_XML_Type_Items = &$Loaded_XML_Items_By_Type["type"];
		foreach ($Loaded_XML_Type_Items as &$Loaded_XML_Type_Item)
		{
			if ($Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Is_Core'] && $Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Behavior'] != 'Ignore')
			{	
				$Type = &New_Array();
	
				// Copy xml item to cached type
				if (isset($Loaded_XML_Type_Item['ID']))
					$Type['ID'] = &$Loaded_XML_Type_Item['ID'][0];
				$Type['Name'] = &$Loaded_XML_Type_Item['Name'][0];
				$Type['Alias'] = &$Loaded_XML_Type_Item['Alias'][0];
				if (isset($Loaded_XML_Type_Item['Plural_Name']))
					$Type['Plural_Name'] = &$Loaded_XML_Type_Item['Plural_Name'][0];
				else
					$Type['Plural_Name'] = &New_Null();
				if (isset($Loaded_XML_Type_Item['Parent_Type']))
				{		
					if (is_array($Loaded_XML_Type_Item['Parent_Type'][0]))
						$Loaded_XML_Type_Item_Parent_Type_Alias = &$Loaded_XML_Type_Item['Parent_Type'][0]['Alias'];
					else
						$Loaded_XML_Type_Item_Parent_Type_Alias = &$Loaded_XML_Type_Item['Parent_Type'][0];
						
					$Type['Parent_Type'] = &$Loaded_XML_Type_Item_Parent_Type_Alias;
				}
				else
					$Type['Parent_Type'] = &New_Null();
				if (isset($Loaded_XML_Type_Item['Default_Key']))
					$Type['Default_Key'] = &$Loaded_XML_Type_Item['Default_Key'][0];
				else
					$Type['Default_Key'] = &New_Null();
				if (isset($Loaded_XML_Type_Item['Data_Name']))
					$Type['Data_Name'] = &$Loaded_XML_Type_Item['Data_Name'][0];
				else
					$Type['Data_Name'] = &New_Null();
				if (isset($Loaded_XML_Type_Item['Plural_Alias']))
					$Type['Plural_Alias'] = &$Loaded_XML_Type_Item['Plural_Alias'][0];
				else
					$Type['Plural_Alias'] = &New_Null();					
				if (isset($Loaded_XML_Type_Item['Package']))
					$Type['Package'] = &$Loaded_XML_Type_Item['Package'][0];
				else
					$Type['Package'] = &$Default_Package_Value;
	
				$Cached_Type = &Add_Type_To_Cache($Database, $Type);
	
// 				$Cached_Type_Alias = &$Cached_Type['Alias'];
// 				$All_Cached_Types[strtolower($Cached_Type_Alias)] = &$Cached_Type;
// 				$Cached_Type_Lookup[strtolower($Cached_Type_Alias)] = &$Cached_Type;
// 				if ($Cached_Type['Plural_Alias'])
// 					$Cached_Type_Lookup[strtolower($Cached_Type['Plural_Alias'])] = &$Cached_Type;
			}
		}
	}
	
	//Load core properties into cache
	if (isset($Loaded_XML_Items_By_Type["property"]))
	{
		$Loaded_XML_Property_Items = &$Loaded_XML_Items_By_Type["property"];
		foreach ($Loaded_XML_Property_Items as &$Loaded_XML_Property_Item)
		{		
			if ($Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']['Is_Core'] && $Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']['Behavior'] != 'Ignore')
			{	
				if (is_array($Loaded_XML_Property_Item['Type'][0]))
					$Loaded_XML_Property_Item_Type_Alias = &$Loaded_XML_Property_Item['Type'][0]['Alias'];
				else
					$Loaded_XML_Property_Item_Type_Alias = &$Loaded_XML_Property_Item['Type'][0];
				$Cached_Property_Type = &Get_Cached_Type($Database, $Loaded_XML_Property_Item_Type_Alias);

				if (is_array($Loaded_XML_Property_Item['Value_Type'][0]))
					$Loaded_XML_Property_Item_Value_Type_Alias = &$Loaded_XML_Property_Item['Value_Type'][0]['Alias'];
				else
					$Loaded_XML_Property_Item_Value_Type_Alias = &$Loaded_XML_Property_Item['Value_Type'][0];
				$Cached_Property_Value_Type = &Get_Cached_Type($Database, $Loaded_XML_Property_Item_Value_Type_Alias);
			
				// TODO add other required property properties and use isset()
				$Cached_Forward_Property = &New_Array();
				$Cached_Forward_Property['Name'] = &$Loaded_XML_Property_Item['Name'][0];
				$Cached_Forward_Property['Alias'] = &$Loaded_XML_Property_Item['Alias'][0];
				if (isset($Loaded_XML_Property_Item['ID']))
					$Cached_Forward_Property['ID'] = &$Loaded_XML_Property_Item['ID'][0];
				$Cached_Forward_Property['Cached_Type'] = &$Cached_Property_Type;
				$Cached_Forward_Property['Cached_Value_Type'] = &$Cached_Property_Value_Type;
				$Cached_Forward_Property['Data_Name'] = &$Loaded_XML_Property_Item['Data_Name'][0];
				// TODO - etc
				if (array_key_exists('Key', $Loaded_XML_Property_Item))
					$Cached_Forward_Property['Key'] = &$Loaded_XML_Property_Item['Key'][0];
				if (array_key_exists('Relation', $Loaded_XML_Property_Item))
					$Cached_Forward_Property['Relation'] = &$Loaded_XML_Property_Item['Relation'][0];

				$Cached_Property_Type['Cached_Specific_Properties'][strtolower($Cached_Forward_Property['Alias'])] = &$Cached_Forward_Property;
				foreach ($Cached_Property_Type['All_Cached_Child_Types'] as &$Cached_Property_Type_Child_Type)
				{
					$Cached_Property_Type_Child_Type['All_Cached_Properties'][strtolower($Cached_Forward_Property['Alias'])] = &$Cached_Forward_Property;
					$Cached_Property_Type_Child_Type['Cached_Property_Lookup'][strtolower($Cached_Forward_Property['Alias'])] = &$Cached_Forward_Property;
				}
			}
		}
	}
		
	// Create and save core type items.
	// echo "CREATE AND PRE-SAVE CORE TYPES\n<br/>";	
	if (isset($Loaded_XML_Items_By_Type["type"]))
	{
		$Loaded_XML_Type_Items = &$Loaded_XML_Items_By_Type["type"];
		foreach ($Loaded_XML_Type_Items as &$Loaded_XML_Type_Item)
		{
			if ($Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Is_Core'] && $Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Behavior'] == 'Create')
			{
				$Type_Cached_Type = &Get_Cached_Type($Database, 'Type');
		
				$Type_Item = &Create_Memory_Item($Database, $Type_Cached_Type);
		
				if (isset($Loaded_XML_Type_Item['ID']))
					Set_Value($Type_Item, 'ID', $Loaded_XML_Type_Item['ID'][0]);
				Set_Value($Type_Item, 'Name', $Loaded_XML_Type_Item['Name'][0]);
				Set_Value($Type_Item, 'Alias', $Loaded_XML_Type_Item['Alias'][0]);
				if (isset($Loaded_XML_Type_Item['Plural_Name']))
					Set_Value($Type_Item, 'Plural_Name', $Loaded_XML_Type_Item['Plural_Name'][0]);
				if (isset($Loaded_XML_Type_Item['Parent_Type']))
				{		
					if (is_array($Loaded_XML_Type_Item['Parent_Type'][0]))
						$Loaded_XML_Type_Item_Parent_Type_Alias = &$Loaded_XML_Type_Item['Parent_Type'][0]['Alias'];
					else
						$Loaded_XML_Type_Item_Parent_Type_Alias = &$Loaded_XML_Type_Item['Parent_Type'][0];
					Set_Value($Type_Item, 'Parent_Type', $Loaded_XML_Type_Item_Parent_Type_Alias);
				}

					
				if (isset($Loaded_XML_Type_Item['Default_Key']))
					Set_Value($Type_Item, 'Default_Key', $Loaded_XML_Type_Item['Default_Key'][0]);
				if (isset($Loaded_XML_Type_Item['Data_Name']))
					Set_Value($Type_Item, 'Data_Name', $Loaded_XML_Type_Item['Data_Name'][0]);
				if (isset($Loaded_XML_Type_Item['Plural_Alias']))
					Set_Value($Type_Item, 'Plural_Alias', $Loaded_XML_Type_Item['Plural_Alias'][0]);
				if (isset($Loaded_XML_Type_Item['Package']))
					Set_Value($Type_Item, 'Package', $Loaded_XML_Type_Item['Package'][0]);
				else
					Set_Value($Type_Item, 'Package', $Default_Package_Value);					
				if (isset($Loaded_XML_Type_Item['Viewable_By']))
					Set_Value($Type_Item, 'Viewable_By', $Loaded_XML_Type_Item['Viewable_By'][0]);
				else
					Set_Value($Type_Item, 'Viewable_By', $Default_Viewable_By_Value);

				// Set parameters for Save_Item
				$Save_Core_Type_Parameters = &New_Array();
				$Save_Core_Type_Parameters['No_Row'] = &New_Boolean(true);
				$Save_Core_Type_Parameters['No_Cache'] = &New_Boolean(true);
				$Save_Core_Type_Parameters['No_Cleanup'] = &New_Boolean(true);

				// Prevent save Item from building a table structure for the simple_item type
				if (strtolower($Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Lookup']) == 'simple_item')
					$Save_Core_Type_Parameters['No_Structure'] = &New_Boolean(true);
			
				// Save core type 
				Save_Item($Type_Item, $Save_Core_Type_Parameters);
		
				$Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']["Database_Item"] = &$Type_Item;
			}
		}
	}
	
	// Create but do not save core property items.
	// echo "CREATE CORE PROPERTIES\n<br/>";	
	{
		if (isset($Loaded_XML_Items_By_Type["property"]))
		{
			$Loaded_XML_Property_Items = &$Loaded_XML_Items_By_Type["property"];
					
			// Save properties in order of simple to complex
			// TODO: this may or may not be hacky
			// TODO: maybe the most robust approach is from core simple to wild complex	
			// TODO - not sure if this needs to follow the simple -> complex order of operations, but just copied it for the time being.
			foreach (array('', 'Many-To-One', 'One-To-Many', 'Many-To-Many', 'Commutative') as $Property_Relation)
			{
				foreach ($Loaded_XML_Property_Items as &$Loaded_XML_Property_Item)
				{
					if (isset($Loaded_XML_Property_Item['Relation']))
						$Comparable_Property_Relation = &$Loaded_XML_Property_Item['Relation'][0];
					else
						$Comparable_Property_Relation = &New_String('');
					
					if (strtolower($Comparable_Property_Relation) == strtolower($Property_Relation))
					{					
						if ($Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']['Is_Core'] && $Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']['Behavior'] == 'Create')
						{
							// Create property item.
							$Property_Cached_Type = &Get_Cached_Type($Database, 'Property');
							$Property_Item = &Create_Memory_Item($Database, $Property_Cached_Type);
						
							// Set values from XML on property item
							// TODO: should Alias/Data_Name be specifically checked for (FYI they are auto-set above)
							if (is_array($Loaded_XML_Property_Item['Type'][0]))
								$Loaded_XML_Property_Item_Type_Alias = &$Loaded_XML_Property_Item['Type'][0]['Alias'];
							else
								$Loaded_XML_Property_Item_Type_Alias = &$Loaded_XML_Property_Item['Type'][0];
								
							if (is_array($Loaded_XML_Property_Item['Value_Type'][0]))
								$Loaded_XML_Property_Item_Value_Type_Alias = &$Loaded_XML_Property_Item['Value_Type'][0]['Alias'];
							else
								$Loaded_XML_Property_Item_Value_Type_Alias = &$Loaded_XML_Property_Item['Value_Type'][0];
							
							Set_Value($Property_Item, 'Type', $Loaded_XML_Property_Item_Type_Alias);
							Set_Value($Property_Item, 'Value_Type', $Loaded_XML_Property_Item_Value_Type_Alias);

							Set_Value($Property_Item, 'Name', $Loaded_XML_Property_Item['Name'][0]);
							if (isset($Loaded_XML_Property_Item['Label']))
								Set_Value($Property_Item, 'Label', $Loaded_XML_Property_Item['Label'][0]);
							else
								Set_Value($Property_Item, 'Label', $Loaded_XML_Property_Item['Name'][0]);
							if (isset($Loaded_XML_Property_Item['Alias']))
								Set_Value($Property_Item, 'Alias', $Loaded_XML_Property_Item['Alias'][0]);

							if (isset($Loaded_XML_Property_Item['Reverse_Name']))
								Set_Value($Property_Item, 'Reverse_Name', $Loaded_XML_Property_Item['Reverse_Name'][0]);

							if (isset($Loaded_XML_Property_Item['Reverse_Label']))
								Set_Value($Property_Item, 'Reverse_Label', $Loaded_XML_Property_Item['Reverse_Label'][0]);
							else if (isset($Loaded_XML_Property_Item['Reverse_Name']))
									Set_Value($Property_Item, 'Reverse_Label', $Loaded_XML_Property_Item['Reverse_Name'][0]);
									
							if (isset($Loaded_XML_Property_Item['Reverse_Alias']))
								Set_Value($Property_Item, 'Reverse_Alias', $Loaded_XML_Property_Item['Reverse_Alias'][0]);
							if (isset($Loaded_XML_Property_Item['Data_Name']))
								Set_Value($Property_Item, 'Data_Name', $Loaded_XML_Property_Item['Data_Name'][0]);
							if (isset($Loaded_XML_Property_Item['Key']))
								Set_Value($Property_Item, 'Key', $Loaded_XML_Property_Item['Key'][0]);
							if (isset($Loaded_XML_Property_Item['Relation']))
								Set_Value($Property_Item, 'Relation', $Loaded_XML_Property_Item['Relation'][0]);
							if (isset($Loaded_XML_Property_Item['Default_Value']))
								Set_Value($Property_Item, 'Default_Value', $Loaded_XML_Property_Item['Default_Value'][0]);
							if (isset($Loaded_XML_Property_Item['Viewable_By']))
								Set_Value($Property_Item, 'Viewable_By', $Loaded_XML_Property_Item['Viewable_By'][0]);
							else
								Set_Value($Property_Item, 'Viewable_By', $Property_Default_Viewable_By_Value);
							
							// Don't save property structure - handled by low level type creation structure directly in save_item
							// TODO - clean up and isolate somehow? 
							// TODO - verify? 
						
							// Store property item in XML
							$Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']["Database_Item"] = &$Property_Item;
						}
					}
				}
			}
		}
	}

	
	// Create or retrieve database items for other types marked to overwrite their database values.
	if (!$Parameters['First_Time'])
	{
		// echo "POPULATE ITEMS FROM DATABASE\n<br/>";
		foreach ($Loaded_XML_Items_By_Type as $XML_Type_Lookup => &$Loaded_XML_Items)
		{
			if (array_key_exists(strtolower($XML_Type_Lookup), $All_Cached_Types))
			{
				foreach ($Loaded_XML_Items as &$Loaded_XML_Item)
				{	
					if (!array_key_exists('Database_Item', $Loaded_XML_Item['Jelly_Import_XML_Metadata']))
					{
						if (in_array($Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'],['Replace', 'Update']))
						{							
							// Create query 
							switch($Loaded_XML_Item['Jelly_Import_XML_Metadata']['Query_Method'])
							{
								case 'All':
									$Existing_Item_Command_String = &New_String(
											$XML_Type_Lookup
										);								
									break;
									
								case 'Lookup':
									// Get lookup
									$Loaded_XML_Item_Lookup = &$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'];

									$Existing_Item_Command_String = &New_String(
											'1' . ' ' . $XML_Type_Lookup . ' ' . 
												'where' . ' ' . 
													'Alias'  . ' ' . '=' . ' ' . ('"' . $Loaded_XML_Item_Lookup . '"')
										);
									break;
				
								case 'Type_And_Lookup':								
									// Get lookup
									$Loaded_XML_Item_Lookup = &$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Lookup'];

									// Get type alias
									if (is_array($Loaded_XML_Property_Item['Type'][0]))
										$Loaded_XML_Item_Type_Alias = &$Loaded_XML_Item['Type'][0]['Alias'];
									else
										$Loaded_XML_Item_Type_Alias = &$Loaded_XML_Item['Type'][0];
									
									$Existing_Item_Command_String = &New_String(
											'1' . ' ' . $XML_Type_Lookup . ' ' . 
												'where' . ' ' . 
													'Alias'  . ' ' . '=' . ' ' . ('"' . $Loaded_XML_Item_Lookup . '"') .  ' ' . 
														'and' .  ' ' . 
													'Type' . ' ' . '=' . ' ' . ('"' . $Loaded_XML_Item_Type_Alias . '"')
										);
									break;							
							}
							
							// Attempt to retrieve a database item with this query. 
							$Existing_Item_Command = &Parse_String_Into_Command($Existing_Item_Command_String);
							$Existing_Item = &Get_Database_Item($Database, $Existing_Item_Command);													

							if (!array_key_exists('End_Of_Results', $Existing_Item) || !$Existing_Item['End_Of_Results'])
							{
								// If the behavior for this item is to replace it, then delete the current record.
								// TODO - while the Site identification says all, this code doesn't obey that, so it's inconistent.
								if ($Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] == 'Replace')
								{
									// If there's a site included, consider this an upgrade, and do extra cleanup
									if (array_key_exists('site', $Loaded_XML_Items_By_Type) && !empty($Loaded_XML_Items_By_Type['site']))
									{
										// Manual one-to-many style cleanup for type, page, action, folder item, navigation item, i
										// TODO - shouldn't they just be one to many? 
										switch($Existing_Item['Cached_Specific_Type']['Alias'])
										{
											case 'Folder':	
												// Delete attachments
												$Attachment_String = &New_String('Folder_Child_Item where Parent_Folder = ' . $Existing_Item['Data']['ID']);
												$Attachment_Command = &Parse_String_Into_Command($Attachment_String);
												$Attachment_Item = &Get_Database_Item($Database, $Attachment_Command);
												while (!array_key_exists('End_Of_Results', $Attachment_Item) || !$Attachment_Item['End_Of_Results'])
												{
													Delete_Item($Attachment_Item);
													Move_Next($Attachment_Item);
												}
												break;
											
											case 'Navigation':
												// Delete attachments
												$Attachment_String = &New_String('Navigation_Navigation_Item where Parent_Navigation = ' . $Existing_Item['Data']['ID']);
												$Attachment_Command = &Parse_String_Into_Command($Attachment_String);
												$Attachment_Item = &Get_Database_Item($Database, $Attachment_Command);
												while (!array_key_exists('End_Of_Results', $Attachment_Item) || !$Attachment_Item['End_Of_Results'])
												{
													Delete_Item($Attachment_Item);
													Move_Next($Attachment_Item);
												}
												break;
											
											case 'Type':									
											case 'Page':
											case 'Action':
											
												// Get items
												$Content_Module_String = &New_String('Content_Module');
												$Content_Module_Command = &Parse_String_Into_Command($Content_Module_String);
												$Content_Module_Item = &Get_Value($Existing_Item, $Content_Module_Command);
												
												// Delete items
												while (!array_key_exists('End_Of_Results', $Content_Module_Item) || !$Content_Module_Item['End_Of_Results'])
												{
													Delete_Item($Content_Module_Item);
													Move_Next($Content_Module_Item);
												}
												
												// Get attachments
												$Type_Alias = &$Existing_Item['Cached_Specific_Type']['Alias'];
											
												switch ($Type_Alias)
												{
													case 'Type':									
														$Parent_Property_Alias = &New_String('Type_Item');
														break;
													case 'Page':
														$Parent_Property_Alias = &New_String('Page');
														break;
													case 'Action':
														$Parent_Property_Alias = &New_String('Action_Item');
														break;
												}

												// Delete attachments
												$Attachment_String = &New_String($Type_Alias . '_Content_Module where ' . $Parent_Property_Alias . ' = ' . $Existing_Item['Data']['ID']);
												$Attachment_Command = &Parse_String_Into_Command($Attachment_String);
												$Attachment_Item = &Get_Database_Item($Database, $Attachment_Command);												
												// Delete attachments
												while (!array_key_exists('End_Of_Results', $Attachment_Item) || !$Attachment_Item['End_Of_Results'])
												{

													Delete_Item($Attachment_Item);
													Move_Next($Attachment_Item);
												}
												break;
										}
									}
									Delete_Item($Existing_Item);
								}
								
								// Otherwise, if the behavior is to update the record, store the current record for updating.
								else
									$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Database_Item'] = &$Existing_Item;
									
								// If there's a site included, consider this an upgrade, and do extra cleanup
								// Extra cleanup for site
								if ($Existing_Item['Cached_Specific_Type']['Alias'] == 'Site')
								{
									// Get owner 
									$Owner_Command_String = &New_String('Owner');
									$Owner_Command = &Parse_String_Into_Command($Owner_Command_String);
									$Owner_Item = &Get_Value($Existing_Item, $Owner_Command);
								
									if (!array_key_exists('End_Of_Results', $Owner_Item) || !$Owner_Item['End_Of_Results'])
									{
										// Get owner teams
										$Attachment_String = &New_String('User_Team where Member = ' . $Owner_Item['Data']['ID']);
										$Attachment_Command = &Parse_String_Into_Command($Attachment_String);
										$Attachment_Item = &Get_Database_Item($Database, $Attachment_Command);
										
										// Delete owner teams
										while (!array_key_exists('End_Of_Results', $Attachment_Item) || !$Attachment_Item['End_Of_Results'])
										{
											Delete_Item($Attachment_Item);
											Move_Next($Attachment_Item);
										}
										
										// Delete owner							
										Delete_Item($Owner_Item);										
									}									
								}
							}
						}
					}
				}
			}
		}
	}

	// Save non-core types and load them into cache.
	// echo "PRE-SAVE NON-CORE TYPES\n<br/>";
	if (isset($Loaded_XML_Items_By_Type["type"]))
	{
		// Get type type 
		$Type_Cached_Type = &Get_Cached_Type($Database, 'Type');
		
		// Get XML type items
		$Loaded_XML_Type_Items = &$Loaded_XML_Items_By_Type["type"];		
		foreach ($Loaded_XML_Type_Items as &$Loaded_XML_Type_Item)
		{
			// For non-core types, create database item if it's necessary, and update with initial values
			if (!$Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Is_Core'] && $Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Behavior'] != 'Ignore')
			{
				// Create a memory item as necessary
				if (!array_key_exists('Database_Item', $Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']))
					$Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Database_Item'] = &Create_Memory_Item($Database, $Type_Cached_Type);
			
				// Get the type item 
				$Type_Item = &$Loaded_XML_Type_Item['Jelly_Import_XML_Metadata']['Database_Item'];
				
				// Update the type item with core information
				// TODO set alias/dataname if exists in XML
				if (array_key_exists('Name', $Loaded_XML_Type_Item))
					Set_Value($Type_Item, 'Name', $Loaded_XML_Type_Item['Name'][0]);
				if (array_key_exists('Parent_Type', $Loaded_XML_Type_Item))
				{		
					if (is_array($Loaded_XML_Type_Item['Parent_Type'][0]))
						$Loaded_XML_Type_Item_Parent_Type_Alias = &$Loaded_XML_Type_Item['Parent_Type'][0]['Alias'];
					else
						$Loaded_XML_Type_Item_Parent_Type_Alias = &$Loaded_XML_Type_Item['Parent_Type'][0];
						
					Set_Value($Type_Item, 'Parent_Type', $Loaded_XML_Type_Item_Parent_Type_Alias);
				}
					
				if (array_key_exists('Package', $Loaded_XML_Type_Item))
					Set_Value($Type_Item, 'Package', $Loaded_XML_Type_Item['Package'][0]);
				else
					Set_Value($Type_Item, 'Package', $Default_Package_Value);

				if (array_key_exists('Viewable_By', $Loaded_XML_Type_Item))
					Set_Value($Type_Item, 'Viewable_By', $Loaded_XML_Type_Item['Viewable_By'][0]);
				else
					Set_Value($Type_Item, 'Viewable_By', $Default_Viewable_By_Value);


					
				Save_Item($Type_Item, array("No_Row" => true, "No_Cleanup" => true));//, array("Create_Table_Only" => true));
			}
		}
	}
	
	
	// Save non-core properties and load them into cache...
	// TODO: consider that specific cases might affect the save order?
	// echo "PRE-SAVE NON-CORE PROPERTIES\n<br/>";
	if (isset($Loaded_XML_Items_By_Type["property"]))
	{
		// Get property cached type 
		$Property_Cached_Type = &Get_Cached_Type($Database, 'Property');
		
		$Loaded_XML_Property_Items = &$Loaded_XML_Items_By_Type["property"];
		// Save properties in order of simple to complex
		// TODO: this may or may not be hacky
		// TODO: maybe the most robust approach is from core simple to wild complex
		foreach (array('', 'Many-To-One', 'One-To-Many', 'Many-To-Many', 'Commutative') as $Property_Relation)
		{
			foreach ($Loaded_XML_Property_Items as &$Loaded_XML_Property_Item)
			{
				if (isset($Loaded_XML_Property_Item['Relation']))
					$Comparable_Property_Relation = &$Loaded_XML_Property_Item['Relation'][0];
				else
					$Comparable_Property_Relation = &New_String('');
				
				if (strtolower($Comparable_Property_Relation) == strtolower($Property_Relation))
				{	
					if (!$Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']['Is_Core'] && $Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']['Behavior'] != 'Ignore')
					{
						// Create property item if necessary			
						if (!array_key_exists('Database_Item', $Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']))
							$Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']['Database_Item'] = &Create_Memory_Item($Database, $Property_Cached_Type);
		
						// Get the property item 
						$Property_Item = &$Loaded_XML_Property_Item['Jelly_Import_XML_Metadata']['Database_Item'];
						
						$Save_Parameters = &New_Array();
						$Save_Parameters['No_Row'] = &New_Boolean(true);
						$Save_Parameters['No_Cleanup'] = &New_Boolean(true);
						
						// Set values from XML on property item
						if (array_key_exists('Type', $Loaded_XML_Property_Item))
						{
							if (is_array($Loaded_XML_Property_Item['Type'][0]))
								$Loaded_XML_Property_Item_Type_Alias = &$Loaded_XML_Property_Item['Type'][0]['Alias'];
							else
								$Loaded_XML_Property_Item_Type_Alias = &$Loaded_XML_Property_Item['Type'][0];
							Set_Value($Property_Item, 'Type', $Loaded_XML_Property_Item_Type_Alias);
						}
						else
							// TODO - just a code verification, not actual error checking 
							throw new Exception('XML contains Property with no Type.');
							
						if (array_key_exists('Value_Type', $Loaded_XML_Property_Item))
						{
							if (is_array($Loaded_XML_Property_Item['Value_Type'][0]))
								$Loaded_XML_Property_Item_Value_Type_Alias = &$Loaded_XML_Property_Item['Value_Type'][0]['Alias'];
							else
								$Loaded_XML_Property_Item_Value_Type_Alias = &$Loaded_XML_Property_Item['Value_Type'][0];
								
							Set_Value($Property_Item, 'Value_Type', $Loaded_XML_Property_Item_Value_Type_Alias);
						}
						if (array_key_exists('Name', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Name', $Loaded_XML_Property_Item['Name'][0]);
						if (array_key_exists('Label', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Label', $Loaded_XML_Property_Item['Label'][0]);
						else
							Set_Value($Property_Item, 'Label', $Loaded_XML_Property_Item['Name'][0]);
						if (array_key_exists('Alias', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Alias', $Loaded_XML_Property_Item['Alias'][0]);
						if (array_key_exists('Reverse_Name', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Reverse_Name', $Loaded_XML_Property_Item['Reverse_Name'][0]);

						if (array_key_exists('Reverse_Label', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Reverse_Label', $Loaded_XML_Property_Item['Reverse_Label'][0]);
						elseif (array_key_exists('Reverse_Name', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Reverse_Label', $Loaded_XML_Property_Item['Reverse_Name'][0]);	
							
						if (array_key_exists('Reverse_Alias', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Reverse_Alias', $Loaded_XML_Property_Item['Reverse_Alias'][0]);
						if (array_key_exists('Data_Name', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Data_Name', $Loaded_XML_Property_Item['Data_Name'][0]);
						if (array_key_exists('Key', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Key', $Loaded_XML_Property_Item['Key'][0]);
						if (array_key_exists('Relation', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Relation', $Loaded_XML_Property_Item['Relation'][0]);
						if (array_key_exists('Default_Value', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Default_Value', $Loaded_XML_Property_Item['Default_Value'][0]);
						if (array_key_exists('Viewable_By', $Loaded_XML_Property_Item))
							Set_Value($Property_Item, 'Viewable_By', $Loaded_XML_Property_Item['Viewable_By'][0]);
						else
							Set_Value($Property_Item, 'Viewable_By', $Property_Default_Viewable_By_Value);
														
						// TODO - explore other possibilities like this
						if (array_key_exists('Attachment_Type', $Loaded_XML_Property_Item))
						{
							if (is_array($Loaded_XML_Property_Item['Attachment_Type'][0]))
								$Loaded_XML_Property_Item_Attachment_Type_Alias = &$Loaded_XML_Property_Item['Attachment_Type'][0]['Alias'];
							else
								$Loaded_XML_Property_Item_Attachment_Type_Alias = &$Loaded_XML_Property_Item['Attachment_Type'][0];
								
							Set_Value($Property_Item, 'Attachment_Type', $Loaded_XML_Property_Item_Attachment_Type_Alias);
							if (array_key_exists(strtolower($Loaded_XML_Property_Item_Attachment_Type_Alias), $Alias_Lookup_By_Type['type']))
								$Save_Parameters['No_Attachment_Structure'] = &New_Boolean(true);
						}
						
						// Save property item structure only.
						Save_Item($Property_Item, $Save_Parameters);//, array("Create_Table_Only" => true));
					}
				}
			}
		}
	}

	// TODO - hack to make upgrades save time a little bit - (1/2)
	if ($Parameters['First_Time'])
	{
		$Initial_Item_Package_Default_Value = &$Database['Cached_Type_Lookup']['item']['Cached_Specific_Properties']['package']['Default_Value'];
		$Database['Cached_Type_Lookup']['item']['Cached_Specific_Properties']['package']['Default_Value'] = &New_String('Core');
	}
					
	// Save all xml items rows (with 'no structure')
	// echo "PRE-SAVE ALL ITEMS\n<br/>";
	foreach ($Loaded_XML_Items_By_Type as $XML_Type_Lookup => &$Loaded_XML_Items)
	{
		// Get cached type for this type.
		$Item_Cached_Type = &Get_Cached_Type($Database, $XML_Type_Lookup);

		foreach ($Loaded_XML_Items as &$Loaded_XML_Item)
		{							
			if ($Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] != 'Ignore')
			{				
				// Create item if necessary, and set core information
				if (!array_key_exists('Database_Item', $Loaded_XML_Item['Jelly_Import_XML_Metadata']))
				{
					// Create item 
					$Item = &Create_Memory_Item($Database, $Item_Cached_Type);
					
					// Set initial values
					// TODO set alias/dataname if exists in XML
					if (array_key_exists('Name', $Loaded_XML_Item))
						Set_Value($Item, 'Name', $Loaded_XML_Item['Name'][0]);	
						
					// TODO set alias/dataname if exists in XML
					if (array_key_exists('Package', $Loaded_XML_Item))
						Set_Value($Item, 'Package', $Loaded_XML_Item['Package'][0]);
						
					// Store item					
					$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Database_Item'] = &$Item;
				}
				
				else
				{
					// Get the item
					$Item = &$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Database_Item'];
					
					// Refactor types as needed...
					// TODO maybe Has_Property can actually do this from the item's own database property each time, if that's more robust.
					
					// Refactor cached base type
					$Item['Cached_Base_Type'] = &Get_Cached_Type($Database, $Item['Cached_Base_Type']['Alias']);
					
					// Refactor cached specific type
					$Item['Cached_Specific_Type'] = &Get_Cached_Type($Database, $Item['Cached_Specific_Type']['Alias']);
				}
			
				// Save item to database with no structure.
				Save_Item($Item, array('No_Structure' => true, "No_Cache" => true));
			}
		}
	}
	
	// echo "SAVE ALL ITEMS\n<br/>";
	foreach ($Loaded_XML_Items_By_Type as $XML_Type_Lookup => &$Loaded_XML_Items)
	{
		foreach ($Loaded_XML_Items as &$Loaded_XML_Item)
		{
			if ($Loaded_XML_Item['Jelly_Import_XML_Metadata']['Behavior'] != 'Ignore')
			{
				 // Get database item from xml cache
				$Item = &$Loaded_XML_Item['Jelly_Import_XML_Metadata']['Database_Item'];

				// Loop through all xml data
				foreach ($Loaded_XML_Item as $Loaded_XML_Item_Property_Alias => &$Loaded_XML_Item_Property_Values)
				{	
					if ($Loaded_XML_Item_Property_Alias == 'Jelly_Import_XML_Metadata')
						continue;
				
					// Verify that this type has this property
					if (!Has_Property($Item, $Loaded_XML_Item_Property_Alias))
					{
						traverse($Loaded_XML_Item);
						throw new Exception('Property does not exist: ' . $Loaded_XML_Item_Property_Alias . ' in ' . $XML_Type_Lookup);
					}
					else
					{
						// Get property information
						$Cached_Property = &Get_Property($Item, $Loaded_XML_Item_Property_Alias);
						$Cached_Property_Value_Type = &$Cached_Property['Cached_Value_Type'];
						$Cached_Property_Alias = &$Cached_Property['Alias'];

						// If value type of property simple, simply set value
						if (Is_Simple_Type($Cached_Property_Value_Type))
						{
							// Translate xml value to php value
							$Cached_Property_Value_Type_Alias = &$Cached_Property_Value_Type['Alias'];
							$Item_Value = &$Loaded_XML_Item_Property_Values[0];

							switch(strtolower($Cached_Property_Value_Type_Alias))
							{
								// TODO - how to handle null for text value types? 
							
								case 'number':
									if (!is_numeric($Item_Value))
										$Item_Value = &New_Null();
									break;
								
								case 'date':
								case 'date_time':
								case 'time':
									// TODO - TIME ZONE PARADISE
									// TODO - strtotime is actually successful for null values, perhaps, which makes this behave incorrectly.
									if (strtotime($Item_Value) !== false)
										$Item_Value = &New_Number(strtotime($Item_Value));
									else
										$Item_Value = &New_Null();
									break;

								case 'boolean':
									switch(strtolower($Item_Value))
									{	
										case 'true':
										case '1':
											$Item_Value = &New_Boolean(true);
											break;
										case 'false':
										case '0':
											$Item_Value = &New_Boolean(false);
											break;
										default:
											$Item_Value = &New_Null();
											break;
									}
								default:
									break;
							}
						
							// Set item data to php value
							Set_Value($Item, $Cached_Property_Alias, $Item_Value);
						}

						else			
						{	
							// Handle complex value type properties
							$Cached_Property_Relation = &$Cached_Property['Relation'];
							$Cached_Value_Type_Alias = &$Cached_Property_Value_Type['Alias'];
						
							// Catch multiple values for single value property error
							if ($Cached_Property_Relation == MANY_TO_ONE && count($Loaded_XML_Item_Property_Values) > 1)
								throw new Exception ('Mulitple values given for single value property');
															
							// Set or add refactored value items to item
							foreach ($Loaded_XML_Item_Property_Values as &$Loaded_XML_Item_Property_Value)
							{
								//Get item value lookup
								if (is_array($Loaded_XML_Item_Property_Value))
									$Item_Value_Lookup = &$Loaded_XML_Item_Property_Value['ID'];
								else
									$Item_Value_Lookup = &$Loaded_XML_Item_Property_Value;
	
								// TODO:  Do by original key
							
								// TODO - don't know if you need is_null in XML lookups, makes the code funny anyway. 
								if ($Item_Value_Lookup == '' || is_null($Item_Value_Lookup))
								{
									$Item_Value = &New_Null();
								}
							
								// If string match type (non-unique aliases, or type), use simple string value
								// TODO - don't really know why string match types was setup the way it was... so carefully adding pieces
								else if ( !is_numeric($Item_Value_Lookup) && in_array(strtolower($Cached_Value_Type_Alias), $String_Match_Types))
								{	
									$Item_Value = &$Item_Value_Lookup;
								}
						
								// Otherwise, try matching by alias
								elseif (isset($Alias_Lookup_By_Type[strtolower($Cached_Value_Type_Alias)][strtolower($Item_Value_Lookup)]['Jelly_Import_XML_Metadata']['Database_Item']))
								{
									$Item_Value = &$Alias_Lookup_By_Type[strtolower($Cached_Value_Type_Alias)][strtolower($Item_Value_Lookup)]['Jelly_Import_XML_Metadata']['Database_Item'];
								}
							
								// Otherwise, try matching by ID.
								else if (is_numeric($Item_Value_Lookup) && isset($ID_Lookup[$Item_Value_Lookup]))
								{
									$Item_Value_XML_Item = &$ID_Lookup[$Item_Value_Lookup];
									$Item_Value = &$Item_Value_XML_Item['Jelly_Import_XML_Metadata']['Database_Item'];
								}
														
								// Otherwise, try matching by database lookup within the value type of the property
								else
								{
									$Item_Value_Found = &New_Boolean(false);
									
									// If additional lookup data exists, first try looking it up with additional lookup data
									if (is_array($Loaded_XML_Item_Property_Value))
									{
										$Item_Value_Lookup_Meta_Type = &$Loaded_XML_Item_Property_Value['Meta_Type'];
										$Item_Value_Lookup_Alias = &$Loaded_XML_Item_Property_Value['Alias'];
										$Item_Value_Lookup_Type = &$Loaded_XML_Item_Property_Value['Type'];

										if ($Item_Value_Lookup_Type)
											$Item_Value_Alias_Command_String = &New_String(
													'1' . ' ' . $Item_Value_Lookup_Meta_Type . ' ' . 'from' . ' ' . 'Database' . ' ' . 'where' . ' ' . 
														'(' . 
															'Alias' . ' ' . '=' . ' ' . ('"'. $Item_Value_Lookup_Alias . '"') . ' ' .						
																'and' . ' ' . 
															'Type' . ' ' . '=' . ' ' . ('"'. $Item_Value_Lookup_Type . '"') . 
														')'
												);
										else
											$Item_Value_Alias_Command_String = &New_String(
													'1' . ' ' . $Item_Value_Lookup_Meta_Type . ' ' . 'from' . ' ' . 'Database' . ' ' . 'where' . ' ' . 
														'(' . 
															'Alias' . ' ' . '=' . ' ' . ('"'. $Item_Value_Lookup_Alias . '"') . 
														')'
												);

										traverse($Item_Value_Alias_Command_String);
										$Item_Value_Alias_Command = &Parse_String_Into_Command($Item_Value_Alias_Command_String);
										$Item_Value_Alias_Item = &Get_Database_Item($Database, $Item_Value_Alias_Command);
										if (!array_key_exists('End_Of_Results', $Item_Value_Alias_Item) || !$Item_Value_Alias_Item['End_Of_Results'])
										{
											$Item_Value_Found = &New_Boolean(true);
											$Item_Value = &$Item_Value_Alias_Item;
										}
									}
									
									if (!$Item_Value_Found)
									{
										// Try matching database item by alias
										$Item_Value_Alias_Command_String = '1' . ' ' . $Cached_Value_Type_Alias . ' ' . 'from' . ' ' . 'Database' . ' ' . 'where' . ' ' . 'Alias' . ' ' . '=' . ' ' . ('"'. $Item_Value_Lookup . '"');
										$Item_Value_Alias_Command = &Parse_String_Into_Command($Item_Value_Alias_Command_String);
										$Item_Value_Alias_Item = &Get_Database_Item($Database, $Item_Value_Alias_Command);
										if (!array_key_exists('End_Of_Results', $Item_Value_Alias_Item) || !$Item_Value_Alias_Item['End_Of_Results'])
										{
											$Item_Value_Found = &New_Boolean(true);
											$Item_Value = &$Item_Value_Alias_Item;
										}
									}
									
									if(!$Item_Value_Found)
									{
										// Try matching database item by ID
										if (is_numeric($Item_Value))
										{
											$Item_Value_ID_Command_String = '1' . ' ' . $Cached_Value_Type_Alias . ' ' . 'from' . ' ' . 'Database' . ' ' . 'where' . ' ' . 'ID' . ' ' . '=' . ' ' . $Item_Value_Lookup;
											$Item_Value_ID_Command =  &Parse_String_Into_Command($Item_Value_ID_Command_String);
											$Item_Value_ID_Item = &Get_Database_Item($Database, $Item_Value_ID_Command);
											if (!array_key_exists('End_Of_Results', $Item_Value_ID_Item) || !$Item_Value_ID_Item['End_Of_Results'])
											{
												$Item_Value_Found = &New_Boolean(true);
												$Item_Value = &$Item_Value_ID_Item;
											}
										}
									}
								
									// Otherwise, set item value to null, because it didn't match anything in the XML or in the database and is therefore an invalid value.
									if(!$Item_Value_Found)
									{
										traverse('failed to match: ' . $Item_Value_Lookup);
										$Item_Value = &New_Null();
									}
								}							
						
								switch (strtolower($Cached_Property_Relation))
								{
									case MANY_TO_MANY:
									case ONE_TO_MANY:
										// Add value item to item property value
										// TODO: Support add value by string.
										if (!is_null($Item_Value))
										{
											Add_Value($Item, $Cached_Property_Alias, $Item_Value);
										}
										break;

									case MANY_TO_ONE:
										// Set value item to item property value	
										if (!is_null($Item_Value))
										{
											Set_Value($Item, $Cached_Property_Alias, $Item_Value);
										}
										break;
								}
							}	
						}
					}
				}
				
				// Handle default package value...
				// TODO - handle case
				if (!isset($Loaded_XML_Item['Package']))
				{	
					$Item_Cached_Specific_Type_Alias = &$Item['Cached_Specific_Type']['Alias'];
					switch (strtolower($Item_Cached_Specific_Type_Alias))
					{
						case 'type':
							$Package_Value = &$Default_Package_Value;
							break;
						case 'property':
						case 'template':
						case 'type_action':
							$Type_Property_Value_Item = &$Item['Data']['Type'];
							// TODO - cleanup
							if (Is_Item($Type_Property_Value_Item))
								$Type_Property_Value_Alias = &$Type_Property_Value_Item['Data']['Alias'];
							else
								$Type_Property_Value_Alias = &$Type_Property_Value_Item;
							$Type_Property_Cached_Value = &Get_Cached_Type($Database, $Type_Property_Value_Alias);
							$Package_Value = &$Type_Property_Cached_Value['Package'];
							break;
						default:
							$Package_Value = &$Item['Cached_Specific_Type']['Package'];
							break;
					}
					Set_Value($Item, 'Package', $Package_Value);
				}

				// Save item to database with no structure.
				$Save_Item_Parameters = &New_Array();
				$Save_Item_Parameters['No_Structure'] = &New_Boolean(true);
				$Save_Item_Parameters['No_Cache'] = &New_Boolean(true);

				// On reset database, assume plaintext password, on every import/export case, assume hashed pw.
				if (!$Parameters['First_Time'])
					$Save_Item_Parameters['No_Hash'] = &New_Boolean(true);

				// Save the type item
				Save_Item($Item, $Save_Item_Parameters);
			}
		}
	}
	
	// TODO - hack to make upgrades save time a little bit - (2/2)
	if ($Parameters['First_Time'])
	{
		$Database['Cached_Type_Lookup']['item']['Cached_Specific_Properties']['package']['Default_Value'] = &$Initial_Item_Package_Default_Value;		
	}
}
?>