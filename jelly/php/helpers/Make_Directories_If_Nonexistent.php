<?php

// Make_Directories_If_Nonexistent

// TODO - Upgrade

function Make_Directories_If_Nonexistent($Path)
{
	$Directory_Names = explode("/", $Path);
	foreach($Directory_Names as $Directory_Name)
	{
		if (!isset($Current_Directory))
			$Current_Directory = $Directory_Name;
		else
			$Current_Directory .= "/" . $Directory_Name;
		
		if ($Current_Directory != "")
		{
			if (!file_exists($Current_Directory))
				mkdir($Current_Directory);
		}
	}
}

?>