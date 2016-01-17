<?php

function Find_Files($Search_Path, $Extension, &$Found_Files)
{
	// Check if search path is a file or a directory
	if (!is_dir($Search_Path))
	{
		// Check if search path's extension matches
		if (substr($Search_Path, -(strlen($Extension))) == $Extension)
		{
			// Add search path to found files
			$Found_Files[] = $Search_Path;
		}
	}
	else
	{
		// Scan files in directory
		$Files = scandir($Search_Path);
		foreach ($Files as $File_Name)
		{
			// Skip meta-files
			if ($File_Name != "." && $File_Name != "..")
			{
				// Recursively check if child file matches
				Find_Files($Search_Path . "/" . $File_Name, $Extension, $Found_Files);
			}
		}
	}
}

?>