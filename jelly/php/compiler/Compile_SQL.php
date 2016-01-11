<?php

function Compile_SQL($Database_Settings)
{	
	// Load SQL Dump library
	require_once("jelly/php/libraries/Mysqldump/Mysqldump.php");

	// Create SQL Dump
	$Compiled_SQL = new Ifsnop\Mysqldump\Mysqldump('mysql:host=' . $Database_Settings['Host_Name'] . ';dbname=' . $Database_Settings['Database_Name'], $Database_Settings['Username'], $Database_Settings['Password']);
	
	// Write SQL Dump
	$Compiled_SQL_Path = 'jelly/xml/' . 'Compiled' . '.' .'sql';
	$Compiled_SQL->start($Compiled_SQL_Path);
}

?>