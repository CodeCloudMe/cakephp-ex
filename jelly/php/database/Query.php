<?php

function &Query(&$Database, &$Query_String)
{
	// Localize Variables
	$Database_Link = &$Database['Link'];
	
	// Debug	
// 	traverse($Query_String);
	
	// Query database
	$Result = mysqli_query($Database_Link, $Query_String);
	if ($Result === false)
		throw new Exception('MySQL Error: ' . mysqli_error($Database_Link) . ' in ' . $Query_String);
	
	return $Result;
}

?>