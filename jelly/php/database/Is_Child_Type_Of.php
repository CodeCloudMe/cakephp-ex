<?php

function &Is_Child_Type_Of(&$Cached_Type_Left, &$Cached_Type_Right)
{
	$Recursive_Left_Parent_Type = &$Cached_Type_Left;
	while (isset($Recursive_Left_Parent_Type))
	{
		if ($Recursive_Left_Parent_Type['Alias'] == $Cached_Type_Right['Alias'])
			return New_Boolean(true);
		
		if (isset($Recursive_Left_Parent_Type['Cached_Parent_Type']))
			$Recursive_Left_Parent_Type = &$Recursive_Left_Parent_Type['Cached_Parent_Type'];
		else
			unset($Recursive_Left_Parent_Type);
	}
	
	return New_Boolean(false);
}

?>