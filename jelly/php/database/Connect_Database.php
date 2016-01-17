<?php

function &Connect_Database(&$Database_Settings)
{
	// Setup Database result
	$Database = array();
	
	// Setup database query state.
	$Database['Ready'] = &New_Boolean(true);
	
	// Copy over table prefix.
	$Database['Table_Prefix'] = &$Database_Settings['Table_Prefix'];
	
	// Connect to database server
	// TODO: implement MySQL Improved (mysqli)
	$Database_Link = @mysqli_connect(
		$Database_Settings['Host_Name'],
		$Database_Settings['Username'],
		$Database_Settings['Password']);
	if ($Database_Link === false)
		throw new Exception('Could not connect to database: ' . mysqli_error());
	$Database['Link'] = &$Database_Link;
	
	// Synchronize time zone between PHP and MySQL database
	$Time_Zone_Offset = date('P');
	$Time_Zone_Query = 'SET time_zone = \'' . $Time_Zone_Offset . '\'';
	Query($Database, $Time_Zone_Query);
	
	// Set MySQL server character encoding to unicode
	mysqli_set_charset($Database_Link, 'utf8');
		
	// Select database
	if (!mysqli_select_db($Database_Link, $Database_Settings['Database_Name']))
		throw new Exception('Could not select database: ' . mysqli_error($Database_Link));
	
	// Return database result
	return $Database;
}

?>