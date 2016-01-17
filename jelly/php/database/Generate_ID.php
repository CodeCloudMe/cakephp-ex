<?php

// Generate ID

function &Generate_ID(&$Database)
{
	// Get table prefix
	$Table_Prefix = &$Database['Table_Prefix'];
	
	// Increment database ID
	$Data_Table_Name = $Table_Prefix . 'Data';
	$Update_ID_Query = 'UPDATE `' . $Data_Table_Name . '` SET `Last_ID` = `Last_ID` + 1';
	Query($Database, $Update_ID_Query);
	
	// Get new ID
	$Get_ID_Query = 'SELECT Last_ID FROM `' . $Data_Table_Name . '`';
	$Get_ID_Result = Query($Database, $Get_ID_Query);
	$Get_ID_Row = mysqli_fetch_assoc($Get_ID_Result);
	$New_ID = &$Get_ID_Row['Last_ID'];
	
	// TODO - not sure if necessary -- but the manual said these are freed "end of script" rather than at object destruction. so they're in here in case.
	mysqli_free_result($Get_ID_Result);
	
	return $New_ID;
}

?>