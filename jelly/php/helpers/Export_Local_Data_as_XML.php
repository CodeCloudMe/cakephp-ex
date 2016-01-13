<?php 

// TODO - lots needs to be cleaned up here, but it works.

function Export_Local_Data_as_XML($Database, $Database_Settings)
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
			$XML_String .= Generate_XML_For_Item($Database, $Database_Settings, $Type_Alias, $Type_Item_ID);
			$XML_String .= "\n\n";
		}	
	}
	$XML_String .= "</Jelly>";
	return $XML_String;
}

?>