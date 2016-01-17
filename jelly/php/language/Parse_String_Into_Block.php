<?php

// Parse Block String
function &Parse_String_Into_Block(&$Block_String, &$Position = 0)
{
	// Constants
	global $Block_Regex;
	
	$Input_Position = $Position;
	
	// Setup results variables (Chunks will be part of Result, but separated here for speed)
	$Block = Array(
		'Kind' => 'Block_Chunk',
		'Input_String' => substr($Block_String, $Position),
		'Chunks' => array()
		);
	
	// Loop until the inner regex matching reaches a breaking condition
	while (true)
	{
		// Update last position for next loop iteration
		$Last_Position = $Position;
		
		// Search for end of file or terminators (i.e.   /]  or  ]  ) or new tag (i.e.  [  )
		preg_match($Block_Regex, $Block_String, $Matches, PREG_OFFSET_CAPTURE, $Last_Position);
		
		// Check if there was a regex match
		if (isset($Matches[0][0]))
		{
			$Match = $Matches[0][0];
			// TODO: Necessary?
			// if ($Match == '[' && substr($Block_String, $Matches[0][1], 2) == '[/')
			// 	$Match = '[/';
			
			// Advance position to beginning of match
			$Position = $Matches[0][1];
		}
		else
		{
			// No match (i.e. end of file)...
			$Match = '';
			
			// Advance position to end of string
			$Position = strlen($Block_String);
		}
		
		
		// Check if the position has changed (i.e. there was text between the tags)
		if ($Position != $Last_Position)
		{
			// Get the text leading up to the next tag
			$Text_Chunk = &New_Chunk('Text_Chunk');
			$Text_Chunk['Content'] = &New_String(substr($Block_String, $Last_Position, $Position - $Last_Position));
			$Block['Chunks'][] = &$Text_Chunk;
		}
		
		// Advance position past match
		$Position += strlen($Match);
		
		// Process matches
		switch ($Match)
		{
			// Comment tag start
			case '[*':

				// TODO - quick process, didn't integrate with the rest of the approach because not storing in chunks, but makes this portion unsymmetrical and ugly.  feel free to do in a symmetrical way to the below, but without processing the internal content.

				$Comment_End_String = '*/]';
		
				// Search for close of comment tag.
				$Comment_End_Position = strpos($Block_String, $Comment_End_String, $Position);

				// Throw exception if no comment ending found. 
				if ($Comment_End_Position === false)
					throw new Exception('Comment never closed by end of file.');
				
				// Advance position to end of comment.
				$Position = $Comment_End_Position + strlen($Comment_End_String);
								
				// Don't store comment tag.				
				break;
							
			// Tag starting cases...
			case '[':
				// Found the start of a tag, parse the header
				$Tag_Header = &Parse_String_Into_Block($Block_String, $Position);
				
				// Check termination of tag header
				// (empty means end of text, ']' means normal open tag with header, content and footer, '/]' means closed tag with just a header)
				switch ($Tag_Header['Termination'])
				{
					case ']':
						// Parse the tag's content
						$Tag_Content = &Parse_String_Into_Block($Block_String, $Position);
						if ($Tag_Content['Termination'] != '[/')
							throw new Exception('No closing tag for opened tag.');
						
						// Parse the tag's footer
						$Tag_Footer = &Parse_String_Into_Block($Block_String, $Position);
						if ($Tag_Footer['Termination'] != ']')
							throw new Exception('Closing tag not terminated properly.');
						
						// Store new tag
						$Tag = Array(
							'Kind' => 'Tag_Chunk',
							'Header' => &$Tag_Header,
							'Content' => &$Tag_Content,
							'Footer' => &$Tag_Footer
							);
						
						// Add to chunks
						$Block['Chunks'][] = &$Tag;
						unset($Tag);
						
						break;
					case '/]':
						// Store new tag
						$Tag = Array(
							'Kind' => 'Tag_Chunk',
							'Header' => &$Tag_Header
							);
						
						// Add to chunks
						$Block['Chunks'][] = &$Tag;
						unset($Tag);
						
						break;
					case '[/':
						throw new Exception('Closing tag found when not expected.');
						break 2;
					case '':
						throw new Exception('Open tag never closed by end of file.');
						break 2;
				}
				break;
			
			// Tag ending cases...
			case '':
			case '/]':
			case ']':
			case '[/':
				// Either found end of file, end of current block, or closure of surrounding block...
				
				// Store block termination
				$Block['Termination'] = $Match;
				
				$Last_Position = $Position - strlen($Match);
				
				// Done parsing (break out of Select and While loops)
				break 2;
		}
	}
	
	// Save parsed string
	// TODO can this be Input String? Do we need the other Input String? Not used anywhere.
	$Block['Input_String_Selection'] = substr($Block_String, $Input_Position, $Last_Position - $Input_Position);
	
	// Return the resulting block
	return $Block;
}

?>