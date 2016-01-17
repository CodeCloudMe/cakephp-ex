<?php 

// TODO - lots needs to be cleaned up here, but it works.

function Export_Local_Data_as_XML($Database, $Database_Settings)
{
	header('HTTP/1.1 200 OK');
	header('Status: 200 OK');
	header('Content-type:' . ' ' . 'text/xml' . ';' . ' ' . 'charset=utf-8');	
	
	// Start XML String
	$XML_String = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
	$XML_String .='<Jelly>' . "\n";
	
	// TODO - take out jelly language	
	$Explicit_Types_Block_String = '';
	$Types_Command_String = &New_String('Types from Database by ID as Reference where (Parent_Type exists and Parent_Type is not "")');
	$Types_Processed_Command = &Process_Command_String($Database, $Types_Command_String, $Memory_Stack_Reference);
	$Types_Item = &$Types_Processed_Command['Chunks'][0][Item];						

	$Type_Alias_Array = &New_Array();
	$Type_Data_Name_Array = &New_Array();
	while(!array_key_exists('End_Of_Results', $Types_Item) || !$Types_Item['End_Of_Results'])
	{
		$Type_Alias_Array[] = &$Types_Item['Data']['Alias'];
		$Type_Data_Name_Array[] = &$Types_Item['Data']['Data_Name'];
		Move_Next($Types_Item);	
	}
	unset($Types_Item);
	unset($Types_Processed_Command);
	
	/*
		- Get last ID
		- Get timestamp of last id
		- update the below to include things where Modified > timestamp
	*/
	
	// Get last default ID
	$Data_Table_Name = &New_String($Table_Prefix . 'Data');
	$Data_Query = &New_String('(SELECT * FROM `' . $Data_Table_Name . '`)');
	try
	{
		$Data_Result = &Query($Database, $Data_Query);
	}	
	catch (Exception $e)
	{
		throw new Exception('nothing to export');	
	}
	if (!($Data_Row = mysqli_fetch_assoc($Data_Result)))
		throw new Exception('nothing to export');
	
	$Last_Default_ID = &$Data_Row['Last_Default_ID'];
	mysqli_free_result($Data_Result);
	
	// Find item with this ID 
	$Search_Query_String_Parts = array();
	foreach($Type_Data_Name_Array as $Search_Query_String_Part_Data_Name)
	{
		$Search_Query_String_Parts[] = "SELECT `Item`.`ID`, `Item`.`Modified` FROM `" . $Search_Query_String_Part_Data_Name . "` AS `Item`";
	}
	$Search_Query_String = "SELECT `Item`.`ID`, `Item`.`Modified` FROM \n(" . implode("\n UNION \n", $Search_Query_String_Parts) . ")\n AS `Item` WHERE (`Item`.`ID` = " . $Last_Default_ID . ")";	
	try
	{
		$Search_Result = &Query($Database, $Search_Query_String);
	}	
	catch (Exception $e)
	{
		throw $e;
	}
	if (!($Search_Row = mysqli_fetch_assoc($Search_Result)))
	{
		// TODO search backwards from here...
		throw new Exception('Last Default Item ID not matched to any record.');
	}

	$Last_Default_Modified_Time = &$Search_Row['Modified'];
	
	// TODO - take out Jelly Language
	$Type_Alias_Count = count ($Type_Alias_Array);
	for ($Type_Alias_Index = 0; $Type_Alias_Index < $Type_Alias_Count; $Type_Alias_Index++)
	{
		$Type_Alias = $Type_Alias_Array[$Type_Alias_Index];
	
		$Type_Items_Command_String = &New_String($Type_Alias . ' ' . 'from Database where (Item.Package does not exist or Item.Package = "Local" or Item.Modified > "' . $Last_Default_Modified_Time . '") No_Child_Types as Reference by ID');
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
			$XML_String .= Generate_XML_For_Item($Database, $Database_Settings, $Type_Alias, $Type_Item_ID);
			$XML_String .= "\n\n";
		}
	}
	$XML_String .= "</Jelly>";
	return $XML_String;
}

?>