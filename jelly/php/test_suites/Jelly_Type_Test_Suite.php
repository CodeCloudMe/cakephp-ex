<?php

require_once('database/Database.php');
require_once('helpers/Helpers.php');
require_once('language/Language.php');

// Load Settings File
$Database_Settings = array(
	// Database Name
	'Database_Name' => 'jelly_demo',
			
	// Database Host Name
	'Host_Name' => '127.0.0.1',
			
	// Database Username
	'Username' => 'root',
			
	// MySQL Database Password
	'Password' => 'password',
			
	// MySQL Table Prefix
	'Table_Prefix' => ''
);

// Environment
// TODO: cleanup
date_default_timezone_set('America/New_York');

// Setup Globals
$GLOBALS['Command_Cache'] = array();

$GLOBALS['Jelly_Path'] = 'jelly';

// Test Data
$Database = &Connect_Database($Database_Settings);
//Reset_Database($Database);
Generate_Database_Cache($Database);

global $Types;
global $Type_Lookup;
global $Type_Tree;
global $Item_ID_Lookup;

// THE JELLY TYPE TEST SUITE IS IN SESSION.

/*

	Trajectories include:
		
	Create Property of Type
x		has Child Type
		
	Change Type
		Process:
x			Create Type 
x			Create Item
x			Create Property 
x				specific? multiple? 
x			Create Child Type ?
		
		Variables:		
x			Change Parent Type? 
x				to Child of Parent Type, Sibling of Parent Type, Sibling of Current Type, or Parent of Parent Type
x			Change Data Name?
x			Has Child Type?
	
	Delete Type
x		has Specific Property?
x	 	has Specific Multiple Property?
x		has Child Type?

*/


// I WAS JUST PRACTICING 

$Create_Property_Possibilities = array();
$Create_Property_Possibilities[] = &New_Boolean(false);
$Create_Property_Possibilities[] = &New_String("Single");
$Create_Property_Possibilities[] = &New_String("Multiple");

$Create_Child_Type_Possibilities = array();
$Create_Child_Type_Possibilities[] = &New_Boolean(false);
$Create_Child_Type_Possibilities[] = &New_Boolean(true);

$Change_Data_Name_Possibilities = array();
$Change_Data_Name_Possibilities[] = &New_Boolean(false);
$Change_Data_Name_Possibilities[] = &New_Boolean(true);

$Change_Parent_Type_Possibilities = array();
$Create_Property_Possibilities[] = &New_Boolean(false);
$Change_Parent_Type_Possibilities[] = &New_String("Child of Parent");
$Change_Parent_Type_Possibilities[] = &New_String("Sibling of Parent");
$Change_Parent_Type_Possibilities[] = &New_String("Sibling of Current");
$Change_Parent_Type_Possibilities[] = &New_String("Parent of Parent");

$Sleep = false;
$Start = 0;
$Count = 0;
foreach ($Create_Property_Possibilities as &$Create_Property_Possibility)
{
	foreach ($Create_Child_Type_Possibilities as &$Create_Child_Type_Possibility)
	{	
		foreach ($Change_Data_Name_Possibilities as &$Change_Data_Name_Possibility)
		{
			foreach ($Change_Parent_Type_Possibilities as &$Change_Parent_Type_Possibility)
			{
				//Assert possibility
				$Count++;
				if ($Count < $Start)
					continue;
				$Current_Possibility =  array('Create_Property' => &$Create_Property_Possibility, 'Create_Child_Type' =>  &$Create_Child_Type_Possibility, 'Change_Data_Name' => &$Change_Data_Name_Possibility, 'Change_Parent_Type' => &$Change_Parent_Type_Possibility);
				echo "Testing - $Count...";
				traverse($Current_Possibility);
			
				// 1. Create Type
				
				echo "1. Create Type";
				echo "<br/>";
				
				// TODO:  @type_test - default_key, default_input, package are all null
				$Type_Cached_Type = &Get_Cached_Type($Database, 'Type');
				$Current_Type_Item = &Create_Memory_Item($Database, $Type_Cached_Type);
				Set_Simple($Current_Type_Item, 'Name', 'Test Type');
				Set_Simple($Current_Type_Item, 'Alias', 'Test_Type');
				Set_Simple($Current_Type_Item, 'Data_Name', 'Test_Type');
			
				switch ($Change_Parent_Type_Possibility)
				{
					case "Child of Parent":  // Item -> Event
						$Parent_Type_Alias = &New_String('Item');
						break;
					case "Parent of Parent": // Recurring_Fauvent -> Fauvent
						$Parent_Type_Alias = &New_String('Recurring_Fauvent');
						break;
					case "Sibling of Current": // Event -> Fauvent
					case "Sibling of Parent":  // Event -> Page
						$Parent_Type_Alias = &New_String('Event');
					default: // Item
						$Parent_Type_Alias = &New_String('Item');
						break;
				}

				Set_Value($Current_Type_Item, 'Parent_Type', $Parent_Type_Alias);			
				Save_Item($Current_Type_Item);
			
				//Refresh
				Generate_Database_Cache($Database);
				$Type_Cached_Type = &Get_Cached_Type($Database, 'Type');
				$Current_Cached_Type = &Get_Cached_Type($Database, 'Test_Type');

				//2. Create item of new type
				if($Sleep)sleep(3);
				echo "2. Create item of new type";
				echo "<br/>";
				
				$Current_Type_Item_Item = &Create_Memory_Item($Database, $Current_Cached_Type);
				Set_Simple($Current_Type_Item_Item, 'Name', 'Test Type Item');
				Save_Item($Current_Type_Item_Item);

				//3. Create property of new type
				if($Sleep)sleep(3);
				echo "3 Create property of new type";
				echo "<br/>";
				
				//TODO @type-test: doesn't appear that you can pass in a type item for the 'type' property of a property item
				$Property_Cached_Type = &Get_Cached_Type($Database, 'Property');
				switch ($Create_Property_Possibility)
				{
					case "Single":
						$Property_Item = &Create_Memory_Item($Database, $Property_Cached_Type);
						Set_Simple($Property_Item, 'Type', 'Test_Type');
						Set_Simple($Property_Item, 'Name', 'Test Property');
						Set_Simple($Property_Item, 'Data_Name', 'Test_Property');
						Set_Simple($Property_Item, 'Value_Type', 'Test_Type'); //LOLOLOLOLOL
						Set_Simple($Property_Item, 'Key', 'ID');
						Set_Simple($Property_Item, 'Relation', 'Many-To-One');
						Save_Item($Property_Item);
						break;
					case "Multiple":
						$Property_Item = &Create_Memory_Item($Database, $Property_Cached_Type);
						Set_Simple($Property_Item, 'Type', 'Test_Type');
						Set_Simple($Property_Item, 'Name', 'Friend Event');
						Set_Simple($Property_Item, 'Alias', 'Friend_Event');
						Set_Simple($Property_Item, 'Data_Name', 'Friend_Event');
						Set_Simple($Property_Item, 'Reverse_Data_Name', 'Friend_Test_Type');
						Set_Simple($Property_Item, 'Value_Type', 'Event'); //LOLOLOLOLOL
						Set_Simple($Property_Item, 'Key', 'ID');
						Set_Simple($Property_Item, 'Relation', 'Many-To-Many');
						Set_Simple($Property_Item, 'Reverse_Key', 'ID');
						Save_Item($Property_Item);
						break;
					default:
						break;
				}
			
				//Refresh
				Generate_Database_Cache($Database);
				$Type_Cached_Type = &Get_Cached_Type($Database, 'Type');
				$Property_Cached_Type = &Get_Cached_Type($Database, 'Property');
				$Current_Cached_Type = &Get_Cached_Type($Database, 'Test_Type');
			
				//4. Create child type of new type
				if($Sleep)sleep(3);
				echo "4. Create child type of new type";
				echo "<br/>";
				
				if ($Create_Child_Type_Possibility)
				{
					$Child_Type_Item = &Create_Memory_Item($Database, $Type_Cached_Type);
					Set_Simple($Child_Type_Item, 'Name', 'Test Child Type');
					Set_Simple($Child_Type_Item, 'Alias', 'Test_Child_Type');
					Set_Simple($Child_Type_Item, 'Data_Name', 'Test_Child_Type');
					Set_Simple($Child_Type_Item, 'Parent_Type', 'Test_Type');
					Save_Item($Child_Type_Item);
				
					// Refresh
					Generate_Database_Cache($Database);
					$Type_Cached_Type = &Get_Cached_Type($Database, 'Type');
					$Property_Cached_Type = &Get_Cached_Type($Database, 'Property');
					$Current_Cached_Type = &Get_Cached_Type($Database, 'Test_Type');
				}
			
				// Change data name
				if ($Change_Data_Name_Possibility)
					Set_Simple($Current_Type_Item, 'Data_Name', 'Test_Type_HAHAHAHCHANGED');
			
				// Change parent type
				switch($Change_Parent_Type_Possibility)
				{
					case "Child of Parent":  // Item -> Event
						$New_Parent_Type_Alias = &New_String('Event');
						break;
					case "Sibling of Parent":  // Event -> Page
						$New_Parent_Type_Alias = &New_String('Page');
						break;
					case "Parent of Parent": // Recurring_Fauvent -> Fauvent
					case "Sibling of Current": // Event -> Fauvent
						$New_Parent_Type_Alias = &New_String('Fauvent');
						break;
					default: // Item
						$New_Parent_Type_Alias = null;
						break;
				}
				if ($New_Parent_Type_Alias)
					Set_Value($Current_Type_Item, 'Parent_Type', $New_Parent_Type_Alias);
				
				// 5. Save new data name or new parent type
				if($Sleep)sleep(3);
				echo "5. Save new data name or new parent type";
				echo "<br/>";
				
				if ($New_Parent_Type_Alias || $Change_Data_Name_Possibility)
					Save_Item($Current_Type_Item);
										
				// Refresh
				Generate_Database_Cache($Database);
				$Type_Cached_Type = &Get_Cached_Type($Database, 'Type');
				$Property_Cached_Type = &Get_Cached_Type($Database, 'Property');
				$Current_Cached_Type = &Get_Cached_Type($Database, 'Test_Type');
				
				// 6. Delete type
				if($Sleep)sleep(3);
				echo "6. Delete type";
				echo "<br/>";				
				Delete_Item($Current_Type_Item);
				if($Sleep)sleep(3);
			}
		}
	}
}

?>