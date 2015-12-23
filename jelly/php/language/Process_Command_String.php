<?php

// Process Command String

function &Process_Command_String(&$Database, &$Command_String, &$Memory_Stack_Reference = null)
{
// 	traverse($Command_String);
	
	// Parse the string as a block
	$Command_Block = &Parse_String_Into_Block($Command_String);

	// Process the command block (note: processed result does not get added to main result i.e. never printed to browser)
	$Processed_Command_Block = &Process_Block($Database, $Command_Block, $Memory_Stack_Reference);
	Render_Processed_Block($Processed_Command_Block);

	// Get the result string from processed block's content
	$Processed_Command_Block_Content = &$Processed_Command_Block['Content'];
	
	// Parse the command string into a command
	$Command = &Parse_String_Into_Command($Processed_Command_Block_Content, $Memory_Stack_Reference);

	// Process command
	$Processed_Command = &Process_Command($Database, $Command, $Memory_Stack_Reference, $Content);
	
	// Return result
	return $Processed_Command;
}

?>