<?php

// Copy Command

function &Copy_Command(&$Original_Command)
{
	$Command = &New_Command();
	
	foreach ($Original_Command['Clauses'] as $Original_Clause_Lookup => &$Original_Clause)
		$Command['Clauses'][$Original_Clause_Lookup] = &Copy_Clause($Original_Clause);
	// TODO Anything else we're missing?
	if (isset($Original_Command['Count']))
		$Command['Count'] = &New_String($Original_Command['Count']);
	
	return $Command;
}

?>