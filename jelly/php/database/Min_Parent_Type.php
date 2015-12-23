<?php

function &Min_Parent_Type(&$Cached_Type_Left, &$Cached_Type_Right)
{
	if (Is_Child_Type_Of($Cached_Type_Left, $Cached_Type_Right))
		return $Cached_Type_Left;
		
	else if(Is_Child_Type_Of($Cached_Type_Right, $Cached_Type_Left))
		return $Cached_Type_Right;
	
	$Result = null;
	return $Result;
}

?>