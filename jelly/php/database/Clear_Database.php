<?php

	function Clear_Database(&$Database)
	{
		// Get Table Prefix
		$Table_Prefix = &$Database['Table_Prefix'];
	
		// Fetch list of all tables
		$Tables_Query = &New_String('SHOW TABLES');
		$Tables_Result = &Query($Database, $Tables_Query);
	
		// Filter tables that start with the correct prefix
		// Warning: $Tables_Row not by reference
		$Table_Names = &New_Array();
		while ($Tables_Row = mysqli_fetch_array($Tables_Result))
		{
			$Table_Name = &$Tables_Row[0];
			if (substr($Table_Name, 0, strlen($Table_Prefix)) == $Table_Prefix)
				$Table_Names[] = &New_String('`' . mysqli_real_escape_string($Database['Link'], $Table_Name) . '`');
		}	
		mysqli_free_result($Tables_Result);
	
		// Drop matching tables
		if (count($Table_Names) > 0)
		{
			$Drop_Tables_Query = &New_String('DROP TABLE ' . implode(', ', $Table_Names));
			Query($Database, $Drop_Tables_Query);
		}
	}
	
?>