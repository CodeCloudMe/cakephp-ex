<?php

// Process Block String

function &Process_Block_String(&$Database, &$Block_String, &$Context)
{
	// Parse the string into a block
	$Block = &Parse_String_Into_Block($Block_String);
	
	// Process the block
	$Processed_Block = &Process_Block($Database, $Block, $Context);
	
	// Return the result
	return $Processed_Block;
}

?>