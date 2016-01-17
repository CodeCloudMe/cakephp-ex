<?php

// Process Block With Evaluation

function &Process_Block_With_Evaluation(&$Database, &$Block, $Evaluation, &$Inner_Content_Block)
{
	// Look for 'then' and 'else' tags at root level of block
	$Found = false;
	foreach($Block['Chunks'] as &$Chunk)
	{
		// Check if the chunk is a tag
		if ($Chunk['Kind'] == 'Tag_Chunk')
		{
			// Only look for chunks without tags in their headers
			// TODO should we be rendering the header block or is it enough to just search for then/else as the first chunk?
			if ($Chunk['Header']['Chunks'][0]['Kind'] == 'Text_Chunk')
			{
				// Check if the chunk header is 'then' or 'else'
				switch (strtolower($Chunk['Header']['Chunks'][0]['Content']))
				{
					// Then
					case 'then':
						$Found = true;
						if ($Evaluation)
						{
							$Processed_Then_Tag = &Process_Block($Database, $Chunk['Content'], $Inner_Content_Block);
							return $Processed_Then_Tag;
						}
						break;
						
					// Else
					case 'else':
						$Found = true;
						if (!$Evaluation)
						{
							$Processed_Else_Tag = &Process_Block($Database, $Chunk['Content'], $Inner_Content_Block);
							return $Processed_Else_Tag;
						}
						break;
				}
			}
		}
	}
	
	// If no tags were found, process entire content based on evaluation
	if (!$Found)
	{
		// Only process if the evaluation was true
		if ($Evaluation)
		{
			$Processed_Block = &Process_Block($Database, $Block, $Inner_Content_Block);
			return $Processed_Block;
		}
	}
	
	// If still not found, return empty result
	$Processed_Block = &New_Chunk('False_Evaluation_Without_Else_Chunk');
	return $Processed_Block;
}

?>