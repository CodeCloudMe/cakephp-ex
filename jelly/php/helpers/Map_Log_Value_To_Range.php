<?php

// Map Log Value To Range

function Map_Log_Value_To_Range($Current_Item_Type_Count, $Maximum_Count, $Minimum_Height, $Maximum_Height)
{
	if ($Current_Item_Type_Count == 0)
		return $Minimum_Height;
	if ($Current_Item_Type_Count > $Maximum_Count)
		return $Maximum_Height;
	return ceil((log($Current_Item_Type_Count)/log($Maximum_Count) * ($Maximum_Height - $Minimum_Height)) + $Minimum_Height);
}

?>