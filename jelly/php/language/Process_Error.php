<?php

// Process Tag
function &Process_Error(&$Database, &$Error, &$Context)
{
// 	throw $Error;
// 	exit();
	
	// Create error item
	$Cached_Error_Type = &Get_Cached_Type($Database, 'Error');
	$Error_Item = Create_Memory_Item($Database, $Cached_Error_Type);

	// Set error values
	$Error_Message = $Error->getMessage();
	Set_Value($Error_Item, 'Description', $Error_Message);
	
	// Create reference
	$Error_Namespace = &New_String($Context['Namespace'] . NAMESPACE_DELIMITER . 'Error');
	$Error_Item_Reference = &New_Reference($Database);
	$Error_Item_Reference['Namespace'] = &$Error_Namespace;
	$Error_Item_Reference['Item'] = &$Error_Item;
	$Error_Item['References'][] = &$Error_Item_Reference;
	
	// Advance context
	$Error_Item_Reference['Previous_Memory_Stack_Reference'] = &$Context;
	$Context = &$Error_Item_Reference;
	
	// Process error item in default template
	$Error_Command_String = 'This as Default No_Wrap No_Scripts';
	$Error_Command = Parse_String_Into_Command($Error_Command_String);
	$Processed_Error = Process_Command($Database, $Error_Command, $Context);
	$Processed_Error['Kind'] = &New_String('Processed_Error_Chunk');

	// Retreat context 
	// TODO - is this how it works? ... check.
	$Context = &$Context['Previous_Memory_Stack_Reference'];
	
	return $Processed_Error;
}

?>