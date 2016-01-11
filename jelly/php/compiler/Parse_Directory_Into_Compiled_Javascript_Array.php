<?php

	function Parse_Directory_Into_Compiled_Javascript_Array($Current_Directory_Path)
	{
		$Compiled_Javascript_Array = []; 
	
		$Current_Filenames = scandir($Current_Directory_Path);
	
		usort($Current_Filenames, 'Get_Difference_In_String_Length');

		foreach ($Current_Filenames as $Current_Filename)
		{
			// Ignore unhelpful values
			if (!in_array($Current_Filename, array(".","..")))
			{
				$Current_File_Path = $Current_Directory_Path . '/' . $Current_Filename;
			
				// If this is a file			
				if (!is_dir($Current_File_Path))
				{	
					$Current_File_Extension = substr(strrchr($Current_File_Path, "."), 1);
					if (strtolower($Current_File_Extension) == 'js')
					{
						// Remove head.load(...);
						$Cleaner = "/head.load\([^);]*\)/i";
	//					echo ($Current_File_Path) . "<br/>\n";
						$Current_File_Content = file_get_contents($Current_File_Path);
						$Current_File_Cleaned_Content = preg_replace($Cleaner, '', $Current_File_Content);
					
						// Add cleaned content to array
						$Compiled_Javascript_Array[] = $Current_File_Cleaned_Content;
					}
				}
			}
		}
	
		foreach ($Current_Filenames as $Current_Filename)
		{
			// Ignore unhelpful values
			if (!in_array($Current_Filename, array(".","..")))
			{
				$Current_File_Path = $Current_Directory_Path . '/' . $Current_Filename;
			
				// If this is a directory			
				if (is_dir($Current_File_Path))
				{	
					$Compiled_Javascript_Array = array_merge($Compiled_Javascript_Array, Parse_Directory_Into_Compiled_Javascript_Array($Current_File_Path));
				}
			}
		}
	
	
		return $Compiled_Javascript_Array;
	}

?>