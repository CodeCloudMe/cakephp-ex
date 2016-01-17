<?php

// Process Tag
function &Process_Tag(&$Database, &$Tag, &$Context)
{
	// Use the tag header as the command block.
	$Command_Block = &$Tag['Header'];
	
	// Process the command block (note: processed result does not get added to main result i.e. never printed to browser)
	$Processed_Command_Block = &Process_Block($Database, $Command_Block, $Context);
	
	// Render
	// TODO - make never_wrap hack more general
	$Processed_Command_Block['Original_Command'] = &New_Command();
	$Processed_Command_Block['Original_Command']['Clauses']['never_wrap'] = &New_Clause();
	Render_Processed_Block($Processed_Command_Block);

	// Get the result string from processed block's content
	$Processed_Command_Block_Content = &$Processed_Command_Block['Content'];
	
	// Parse the command string into a command
	$Command = &Parse_String_Into_Command($Processed_Command_Block_Content, $Context);

	try 
	{
		// Process tag's header command with or without inner content
		if (isset($Tag['Content']))
			$Processed_Command = &Process_Command($Database, $Command, $Context, $Tag['Content']);
		else
			$Processed_Command = &Process_Command($Database, $Command, $Context);
			
		// Create processed tag
		$Processed_Tag = &New_Chunk('Processed_Tag_Chunk');
		$Processed_Tag['Chunks'] = &New_Array();
		$Processed_Tag['Chunks'][] = &$Processed_Command;
	
		// Return processed tag
		return $Processed_Tag;

	}

	catch (exception $Error)
	{
		$Processed_Error = &Process_Error($Database, $Error, $Context);
		
		// Create processed tag
		$Processed_Tag = &New_Chunk('Processed_Tag_Chunk');
		$Processed_Tag['Chunks'] = &New_Array();
		$Processed_Tag['Chunks'][] = &$Processed_Error;
	
		// Return processed tag
		return $Processed_Tag;
	}
	
}

?>