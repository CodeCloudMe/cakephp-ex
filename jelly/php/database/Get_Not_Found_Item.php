<?php

function &Get_Not_Found_Item(&$Database, &$Memory_Stack_Reference)
{
	$Get_Not_Found_Item_Command_String = 'Not_Found_Item where Alias = "Not_Found" as Reference';
	$Get_Not_Found_Item_Command = &Process_Command_String($Database, $Get_Not_Found_Item_Command_String, $Memory_Stack_Reference);
	$Get_Not_Found_Item = $Get_Not_Found_Item_Command['Chunks'][0]['Item'];
	return $Get_Not_Found_Item;
}

?>