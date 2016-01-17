<?php

// TODO - Pass in current item ID, and then we can bottom out at the one that is the current id instead of incrementing one too far -- just thought of a use case in which that would happen.

function &Generate_Unique_Value(&$Database, &$Cached_Type, $Property_Lookup, $Value, $Increment_Delimiter = '_', $Query_Condition = null)
{
	// If the cached type doesn't have a database row, then there are no conflicts in the database
	// TODO - better check? 
	// TODO: should data names be escaped? They should be safe, but probably better to escape
	if (is_null($Cached_Type['ID']))
		$Unique_Value = &$Value;

	// Increment and test values for conflicts in the database, until there is no conflict.
	else
	{
		// Type
		$Cached_Type_Data_Name = &$Cached_Type['Data_Name'];
		
		// Property
		$Cached_Property = &$Cached_Type['Cached_Property_Lookup'][strtolower($Property_Lookup)];
		$Cached_Property_Data_Name = &$Cached_Property['Data_Name'];

		$Increment = 0; 
		do
		{
			$Increment++;
			if ($Increment > 1) 
				$Test_Value = &New_String($Value . $Increment_Delimiter . $Increment);
			else
				$Test_Value = &$Value;
		
			$Test_Query_String = 
				'SELECT' . ' ' . 
					('*' . ' ' . 'FROM' . ' ' . '`' . $Cached_Type_Data_Name . '`') . ' ' . 
				'WHERE' . ' ' . 
					('(' . '`' .  $Cached_Property_Data_Name . '`' . ' ' .  '=' . ' ' . '\'' .  mysqli_real_escape_string($Database['Link'], $Test_Value) . '\'' . ')');
			
			if ($Query_Condition)
				$Test_Query_String .= 
				' ' . 'AND' . ' ' .
					'(' . $Query_Condition . ')';
	
			$Test_Result = &Query($Database, $Test_Query_String);
		}
		while (mysqli_num_rows($Test_Result) > 0);
		$Unique_Value = &$Test_Value;

		// TODO - not sure if necessary -- but the manual said these are freed "end of script" rather than at object destruction. so they're in here in case.
		mysqli_free_result($Test_Result);
	}
	
	return $Unique_Value;
}

?>