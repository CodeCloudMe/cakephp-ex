<?php

// New Clause

// Properties:
//	Kind
//	Items
//	Tree

function &New_Clause()
{
	$Clause = &New_Array();
	
	// Set Command Kind
	$Clause['Kind'] = &New_String('Clause');
	
	return $Clause;
}

?>