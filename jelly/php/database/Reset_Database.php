<?php

// Create Value

function &Reset_Database(&$Database)
{
	// Clear database
	Clear_Database($Database);
	
	// Get SQL File
	$Compiled_SQL_Path = 'jelly/xml/' . 'Compiled' . '.' .'sql';
	if (!file_exists($Compiled_SQL_Path))
		die('File does not exist: ' . $Compiled_SQL_Path);
	$Compiled_SQL_Content = file_get_contents($Compiled_SQL_Path);
	
	// Execute SQL File
	if (!mysqli_multi_query($Database['Link'], $Compiled_SQL_Content))
	  echo("Error description: " . mysqli_error($Database['Link']));
	else
		echo "Done";
	
}

?>