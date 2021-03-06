<?php

function Restart_Database(&$Database)
{
	// Clear database
	Clear_Database($Database);
	
	// Get Table Prefix
	$Table_Prefix = &$Database['Table_Prefix'];
	
	// Create data table
	$Data_Table_Name = &New_String($Table_Prefix . 'Data');
	$Create_Data_Table_Query = &New_String('CREATE TABLE `' . mysqli_real_escape_string($Database['Link'], $Data_Table_Name) . '` (`Database_Version` INT NOT NULL, `Commit_Count` INT NOT NULL, `Last_ID` INT NOT NULL, `Last_Default_ID` INT NULL) CHARACTER SET utf8');
	Query($Database, $Create_Data_Table_Query);

	// Get version 
	$Version_Path = 'jelly/Version.json';
	$Version_Data = json_decode(file_get_contents($Version_Path), true);
	$Database_Version = $Version_Data['Root_Version'];
	exec('git rev-list HEAD --count', $Total_Commit_Count);
	$Total_Commit_Count = $Total_Commit_Count[0];
	$Commit_Count_Start = $Version_Data['Start_From_Commit'];
	$Commit_Count =  $Total_Commit_Count - $Commit_Count_Start;
	$Last_ID = 0;

	// Store database version and last ID
	$Insert_Data_Query = &New_String('INSERT INTO `Data` (`Database_Version`, `Commit_Count`, `Last_ID`) VALUES (\'' . $Database_Version . '\', \'' . $Commit_Count . '\', \'' . $Last_ID . '\')');
	Query($Database, $Insert_Data_Query);
	
	// Increase time limit for loading
	// TODO clean up
	set_time_limit(200);
	
	// Load Defaults...
	
	// Reset Cache
	$Database['All_Cached_Types'] = &New_Array();
	$Database['Cached_Type_Lookup'] = &New_Array();
	$Database['Ready'] = &New_Boolean(false);
	
	// Find defaults file
	global $Install_Path;
	$Packages_Search_Path = &New_String($Install_Path . '/xml');
	$Package_File_Paths = &New_Array();
	Find_Files($Packages_Search_Path, '.xml', $Package_File_Paths);
	$Package_File_Paths_2 = &New_Array();
	Find_Files($Packages_Search_Path, '.xml2', $Package_File_Paths_2);
	$Package_File_Paths_3 = &New_Array();
	Find_Files($Packages_Search_Path, '.xml3', $Package_File_Paths_3);

	// Load XML file
	Load_XML_Files($Database, $Package_File_Paths, array('First_Time' => true));
	Load_XML_Files($Database, $Package_File_Paths_2, array('First_Time' => true));
	Load_XML_Files($Database, $Package_File_Paths_3, array('First_Time' => true));
	$Database['Ready'] = &New_Boolean(true);
	
	// Set last default ID
	$Update_Last_Default_ID_Query = &New_String('UPDATE `' . mysqli_real_escape_string($Database['Link'], $Data_Table_Name) . '` SET `Last_Default_ID` = `Last_ID`');
	Query($Database, $Update_Last_Default_ID_Query);
}

?>