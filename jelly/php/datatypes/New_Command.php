<?php

// New Command

// Properties:
//	Kind
//	Clauses
//	Original_String
//	Count
//	New

function &New_Command()
{
	$Command = &$_;$_ = [];
	unset($_);

	// Set Command Kind
	$Command['Kind'] = &$_;$_ = 'Command';
	unset($_);
	
	// Initialize command's Clauses array
	$Command['Clauses'] = &$_;$_ = [];
	unset($_);
	
	return $Command;
}

?>