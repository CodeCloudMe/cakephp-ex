<?php

function &Get_Site_Item(&$Database, &$Memory_Stack_Reference)
{
	$Get_Site_Item_Command_String = '1 Site from Database as Reference';
	$Get_Site_Item_Command = &Process_Command_String($Database, $Get_Site_Item_Command_String, $Memory_Stack_Reference);
	$Get_Site_Item = $Get_Site_Item_Command['Chunks'][0]['Item'];
	return $Get_Site_Item;
}

?>