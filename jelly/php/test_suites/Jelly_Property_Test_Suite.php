<?php

require_once('Jelly.php');

// THE JELLY TEST SUITE PROCEEDS.

/*
hereafter i declare the following

parent_type: event
base_type: recurring_event
child_type: recurring_fauvent
unrelated_type: page

*/

/*
- make a matrix of values for each property of properties
- special ones:  
x type:  parent_type, base_type, child_type, unrelated_type
- attachment_type: uh...
- data_name: √  thing, fing
- key: √ alias, id
- relation: for not simple ones
x value_type:  parent_type, base_type, child_type, unrelated_type 
- reverse_data_name: √ reverse_thing, reverse_fing
- reverse_key: √ alias, id


PROCEDURE
- for each from, to pair
- announce it.
- create a from property
- populate the data
- change it to the to values
- save it
- delete it.

*/

$Names = array('Thing', 'Fing');
$Reverse_Names = array('Reverse_Thing', 'Reverse_Fing');
$Keys = array('Alias', 'ID');
$Reverse_Keys = array('Alias', 'ID');
$Types = array('Event', 'Fauvent', 'Recurring_Fauvent', 'Page');
$Value_Types = array('Event', 'Fauvent', 'Recurring_Fauvent', 'Page', 'Number');
$Relations = array('Many-To-One', 'One-To-Many', 'Many-To-Many');


$Test_Values = array();
$Test_Values['Event'] = array('Event 1', 'Event 2', 'Event 3');
$Test_Values['Fauvent'] = array('Fauvent 1', 'Fauvent 2', 'Fauvent 3');
$Test_Values['Recurring_Fauvent'] = array('Recurring Fauvent 1', 'Recurring Fauvent 2', 'Recurring Fauvent 3');
$Test_Values['Page']  = array('Page 1','Page 2', 'Page 3');
$Test_Values['Number']  = array(1,2,3);


$Property_Values_Matrix = array();

foreach ($Names as $Name)
{
	foreach($Types as $Type)
	{	
		foreach($Value_Types as $Value_Type)
		{			
			// break
			if  ($Value_Type == 'Number')
			{
				$Current_Property_Values = array();
				$Current_Property_Values['Name'] = 'Test Property';
				$Current_Property_Values['Name'] = $Name;
				$Current_Property_Values['Type'] = $Type;
				$Current_Property_Values['Value_Type'] = $Value_Type;
				$Property_Values_Matrix[] = $Current_Property_Values;
			}
			
			else
			{
				foreach ($Keys as $Key)
				{					
					foreach ($Relations as $Relation)
					{
						if ($Relation == 'Many-To-Many')
						{
							foreach ($Reverse_Names as $Reverse_Name)
							{								
								foreach ($Reverse_Keys as $Reverse_Key)
								{
									$Current_Property_Values = array();
									$Current_Property_Values['Name'] = 'Test Property';
									$Current_Property_Values['Name'] = $Name;
									$Current_Property_Values['Type'] = $Type;
									$Current_Property_Values['Value_Type'] = $Value_Type;
									$Current_Property_Values['Key'] = $Key;			
									$Current_Property_Values['Relation'] = $Relation;
									$Current_Property_Values['Reverse_Name'] = $Reverse_Name;
									$Current_Property_Values['Reverse_Key'] = $Reverse_Key;
									$Property_Values_Matrix[] = $Current_Property_Values;
								}
							}
						}
						
						else
						// break
						{
							$Current_Property_Values = array();
							$Current_Property_Values['Name'] = 'Test Property';
							$Current_Property_Values['Name'] = $Name;
							$Current_Property_Values['Type'] = $Type;
							$Current_Property_Values['Value_Type'] = $Value_Type;
							$Current_Property_Values['Key'] = $Key;			
							$Current_Property_Values['Relation'] = $Relation;
							$Property_Values_Matrix[] = $Current_Property_Values;
						}
					}
				}
			}
		}
	
	}

}

// $Initial_Start_From_Index = 0;
// $Initial_Start_To_Index = 0;
$Initial_Start_From_Index = 234;
$Initial_Start_To_Index = 0;

$Total_Count = count($Property_Values_Matrix);
$Traverse_String = 'Count: ' . $Total_Count;
traverse($Traverse_String);
$Traverse_String = 'Total Count: ' . ($Total_Count * $Total_Count);
traverse($Traverse_String);

while (1)
{
	if (!isset($Initial_Start_From_Index))
		$Start_From_Index = rand(0, $Total_Count - 1);
	else
		$Start_From_Index = $Initial_Start_From_Index;
	if (!isset($Initial_Start_To_Index))
		$Start_To_Index = rand(0, $Total_Count - 1);
	else
		$Start_To_Index = $Initial_Start_To_Index;
	
// 	$From_Index = $Start_From_Index;
	for ($From_Index = $Start_From_Index; $From_Index < count($Property_Values_Matrix); $From_Index++)
	{	
		$From_Property_Values = $Property_Values_Matrix[$From_Index];
		
// 		$To_Index = $Start_To_Index;
		for ($To_Index = $Start_To_Index; $To_Index < count($Property_Values_Matrix); $To_Index++)
		{
			// Reset Start_To Index
			if ($Start_To_Index)
				$Start_To_Index = 0;
			
			$Traverse_String = 'From #' . $From_Index . ' To #' . $To_Index;
			traverse($Traverse_String);
	
			$To_Property_Values = $Property_Values_Matrix[$To_Index];
		
			try
			{
				// Assert trajectory 		
			
				$Traverse_String = 'From:';
				traverse($Traverse_String);
				traverse($From_Property_Values);
		
				$Traverse_String = 'To:';
				traverse($Traverse_String);
				traverse($To_Property_Values);
			
				// Create property using 'From' values

				$Traverse_String = 'Creating a new property...';
				traverse($Traverse_String);


				$Property_Item_Type = &Get_Cached_Type($Database, 'Property');
				$From_Property_Item = &Create_Memory_Item($Database, $Property_Item_Type);	
				Set_Simple($From_Property_Item, 'Name', $From_Property_Values['Name']);
				Set_Simple($From_Property_Item, 'Type', $From_Property_Values['Type']);
				Set_Simple($From_Property_Item, 'Value_Type', $From_Property_Values['Value_Type']);
				Set_Simple($From_Property_Item, 'Name', $From_Property_Values['Name']);

				if (isset($From_Property_Values['Key']))
					Set_Simple($From_Property_Item, 'Key', $From_Property_Values['Key']);
			
				if (isset($From_Property_Values['Relation']))
					Set_Simple($From_Property_Item, 'Relation', $From_Property_Values['Relation']);
			
				if (isset($From_Property_Values['Reverse_Name']))
					Set_Simple($From_Property_Item, 'Reverse_Name', $From_Property_Values['Reverse_Name']);

				if (isset($From_Property_Values['Reverse_Key']))
					Set_Simple($From_Property_Item, 'Reverse_Key', $From_Property_Values['Reverse_Key']);
		
				Save_Item($From_Property_Item);
			
				Generate_Database_Cache($Database);
	
			if (1)
			{
				$Traverse_String = 'Populating values for this new property...';
				traverse($Traverse_String);		
		
				$Property_Alias = $From_Property_Item['Data']['Alias'];
				$Property_Type_Alias = $From_Property_Values['Type'];
				$Property_Value_Type_Alias = $From_Property_Values['Value_Type'];
				for ($Item_Index = 1; $Item_Index < 3; $Item_Index++)
				{
					// Get types items 1 and 2 
					$Type_Item_Identifier = $Test_Values[$Property_Type_Alias][$Item_Index - 1];

					$Property_Type_Item_Command_String = "1 $Property_Type_Alias from Database where Name = \"$Type_Item_Identifier\"";
					$Property_Type_Item_Command = &Parse_Command_String($Property_Type_Item_Command_String);
					$Property_Type_Item = &Get_Database_Item($Database, $Property_Type_Item_Command);
					Save_Item($Property_Type_Item);

					switch($From_Property_Values['Value_Type'])
					{
						case 'Number':
							$Value_Type_Value = $Test_Values[$Property_Value_Type_Alias][$Item_Index - 1];
							Set_Simple($Property_Type_Item, $Property_Alias, $Value_Type_Value);
							Save_Item($Property_Type_Item);
							break;

						default:				
							// Get value type item 1 to type item 1, or value type item 2 for type item 2
							$Value_Type_Item_Identifier = $Test_Values[$Property_Value_Type_Alias][$Item_Index - 1];
							$Property_Value_Type_Item_Command_String = "1 $Property_Value_Type_Alias from Database where Name = \"$Value_Type_Item_Identifier\"";
							$Property_Value_Type_Item_Command = &Parse_Command_String($Property_Type_Item_Command_String);
							$Property_Value_Type_Item = &Get_Database_Item($Database, $Property_Type_Item_Command);
							Save_Item($Property_Value_Type_Item);
					
							// If many to one, set the value 
							if ($From_Property_Values['Relation'] == 'Many-To-One') 
							{
								Set_Value($Property_Type_Item, $Property_Alias, $Property_Value_Type_Item);	
								Save_Item($Property_Type_Item);
							}

							// otherwise add the value
							else
							{
								Add_Value($Property_Type_Item, $Property_Alias, $Property_Value_Type_Item);

								// If this is type item 2, also add value type item 3 to type item 2 via this property
								if ($Item_Index == 2)
								{
									$Value_Type_Item_Identifier = $Test_Values[$Property_Value_Type_Alias][$Item_Index];
									$Property_Value_Type_Item_Command_String = "1 $Property_Value_Type_Alias from Database where Name = \"$Value_Type_Item_Identifier\"";
									$Property_Value_Type_Item_Command = &Parse_Command_String($Property_Type_Item_Command_String);
									$Property_Value_Type_Item = &Get_Database_Item($Database, $Property_Type_Item_Command);
									Save_Item($Property_Value_Type_Item);
									Add_Value($Property_Type_Item, $Property_Alias, $Property_Value_Type_Item);	
								}
						
								// Maybe save?
		// 						Save_Item($Property_Type_Item);
							}
							break;
					}
				}
			}
		
			// Change property using 'To' values.
		
				$Traverse_String = 'Changing property...';
				traverse($Traverse_String);

				if (($To_Property_Values['Type'] != $From_Property_Values['Type']))
					Set_Simple($From_Property_Item, 'Type', $To_Property_Values['Type']);

				if (($To_Property_Values['Value_Type'] != $From_Property_Values['Value_Type']))
					Set_Simple($From_Property_Item, 'Value_Type', $To_Property_Values['Value_Type']);

				if (($To_Property_Values['Name'] != $From_Property_Values['Name']))
					Set_Simple($From_Property_Item, 'Name', $To_Property_Values['Name']);

				$Extra_Values = array('Key', 'Relation', 'Reverse_Name', 'Reverse_Key');
				foreach ($Extra_Values as $Extra_Value)
				{
					if (isset($To_Property_Values[$Extra_Value]))
					{
						if ((!isset($From_Property_Values[$Extra_Value]) || (isset($From_Property_Values[$Extra_Value]) && $To_Property_Values[$Extra_Value] != $From_Property_Values[$Extra_Value])))
							Set_Simple($From_Property_Item, $Extra_Value, $To_Property_Values[$Extra_Value]);		
					}
					else 
					{
						if (isset($From_Property_Values[$Extra_Value]))
							Set_Simple($From_Property_Item, $Extra_Value, null);
					}
				}
						
				Save_Item($From_Property_Item);
			
				Generate_Database_Cache($Database);

				// Delete Property				
				$Traverse_String = 'Deleting property...';
				traverse($Traverse_String);

				Delete_Item($From_Property_Item);
			
				Generate_Database_Cache($Database);
			
			}
			catch (Exception $e)
			{
				$Traverse_String = 'Exception:';
				traverse($Traverse_String);
				$Traverse_String = $e->getMessage();
				traverse($Traverse_String);
			
				$Traverse_String = 'From:';
				traverse($Traverse_String);
				traverse($From_Property_Values);
		
				$Traverse_String = 'To:';
				traverse($Traverse_String);
				traverse($To_Property_Values);
			
				throw $e;
			}
		}
	}
}

?>