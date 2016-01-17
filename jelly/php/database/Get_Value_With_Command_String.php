<?php

// Get Value With Command String

function &Get_Value_With_Command_String(&$Item, $Command_String)
{
	$Command = &Parse_String_Into_Command($Command_String);
	$Result = &Get_Value($Item, $Command);
	
	return $Result;
}

?>