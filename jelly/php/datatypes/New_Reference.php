<?php

// New Reference

// Properties:

function &New_Reference(&$Database)
{
	$Reference = &New_Array();
	
	// Set Reference Kind
	$Reference['Kind'] = &New_String('Reference');
	
	// Create new variables item 
	$Simple_Item_Type = &Get_Cached_Type($Database, 'Simple_Item');
	$Variables_Item = &Create_Memory_Item($Database, $Simple_Item_Type);
	$Reference['Variables'] = &$Variables_Item;
	
	// Return reference
	return $Reference;
}

?>