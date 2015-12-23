<?php

// Is Simple Type

function Is_Simple_Type(&$Cached_Type)
{
	global $Simple_Types;
	$Cached_Type_Alias = &$Cached_Type['Alias'];
	if (in_array(strtolower($Cached_Type_Alias), $Simple_Types))
		return true;
	return false;
}

?>