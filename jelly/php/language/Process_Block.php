<?php

// Process Block

function &Process_Block(&$Database, &$Block, &$Memory_Stack_Reference)
{
// 	echo "Process Block";
// 	traverse($Block);

	// Setup resulting processed block
	$Processed_Block = &New_Chunk('Processed_Block_Chunk');
	$Processed_Block['Chunks'] = &New_Array();
	
	// Process each chunk in block
	foreach ($Block['Chunks'] as &$Chunk)
	{
		// Process chunk depending on kind (i.e. Text, Number or Tag)
		switch ($Chunk['Kind'])
		{
			// Text Chunks
			case 'Text_Chunk':
				// Un-escape slashes
				$Content = &$Chunk['Content'];
				$Unescaped_Content = &New_String(str_replace('\\[', '[', $Content));
				$Unescaped_Content = &New_String(str_replace('\\]', ']', $Unescaped_Content));
				
				// Append unescaped text chunk
				$Unescaped_Chunk = &New_Chunk('Text_Chunk');
				$Unescaped_Chunk['Content'] = &$Unescaped_Content;
				
				$Processed_Block['Chunks'][] = &$Unescaped_Chunk;
				break;
				
			// Number Chunks
			case 'Number_Chunk':
				// Append number chunks as-is
				$Processed_Block['Chunks'][] = &$Chunk;
				break;
			
			// Tag Chunks
			case 'Tag_Chunk':
				// Process tag
				$Processed_Tag = &Process_Tag($Database, $Chunk, $Memory_Stack_Reference);
				
				// Append processed tag to processed block
				$Processed_Block['Chunks'][] = &$Processed_Tag;
				
				break;
			
			// Unknown Chunk
			default:
				throw new Exception('Unknown chunk type');
				break;
		}
	}
	
	// Return Processed Block
	return $Processed_Block;
}

?>