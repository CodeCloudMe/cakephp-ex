<?php

// Process Command

function &Process_Command(&$Database, &$Command, &$Memory_Stack_Reference = null, &$Inner_Content_Block = null)
{
	// Create new result block
	$Processed_Command = &New_Processed_Chunk('Processed_Command_Chunk');
	$Processed_Command['Original_Command'] = &$Command;
	$Processed_Command['Chunks'] = &$_;$_ = [];
	unset($_);

	// Load globals
	// TODO - probably don't need this check?
	if (!$Database)
		throw new exception("No database");
	$Cached_Type_Lookup = &$Database['Cached_Type_Lookup'];
	
	// Get command name
	// TODO @feature-language actually execute the tree
	$Command_Name = &$Command['Clauses']['name']['Tree']['Value'];
		
	// Check if command is a language command or an item lookup
	global $Language_Terms;
	
	// Create command variables item 
	$Simple_Item_Type = &Get_Cached_Type($Database, 'Simple_Item');
	$Command_Variables_Item = &Create_Memory_Item($Database, $Simple_Item_Type);

	// Set command variables on variables item
	if (isset($Command['Clauses']['with']))
	{			
		// TODO: Comment
		$Recursive_With_Tree = &$Command['Clauses']['with']['Tree'];
		while (isset($Recursive_With_Tree))
		{
			if ($Recursive_With_Tree['Value'] == ',')
			{
				$With_Item = &$Recursive_With_Tree['Terms'][0];
	
				// Move up to left term
				$Recursive_With_Tree = &$Recursive_With_Tree['Terms'][1];
			}
			else
			{
				$With_Item = &$Recursive_With_Tree;
	
				// Stop moving up the tree
				unset($Recursive_With_Tree);
			}

			// Handle various kinds of with item trees
			switch (strtolower($With_Item['Kind']))
			{
				case 'variable':
				{
					// Variables without values default to true
					$With_Property_Alias = &$With_Item['Value'];
					Set_Simple($Command_Variables_Item, $With_Property_Alias, true);
					break;
				}
				case 'operator':
				{
					// Get property alias from left term
					$With_Property_Alias = &$With_Item['Terms'][1]['Value'];
		
					// Evaluate right term to get value
					$Evaluated_With_Tree = &Evaluate_Clause_Tree($Database, $With_Item['Terms'][0], $Memory_Stack_Reference);
					$With_Value = &$Evaluated_With_Tree['Value'];
					Set_Value($Command_Variables_Item, $With_Property_Alias, $With_Value);
					break;
				}
				default:
				{
					throw new exception("Unknown with tree kind: " . $With_Item['Kind']);
					break;
				}
			}
		}
	}
	
	if (array_key_exists(strtolower($Command_Name), $Language_Terms))
	{
		// Instantiate refreshing lists
		$Command_Items_To_Refresh = &$_;$_ = [];
		unset($_);

		$Command_Iterators_To_Refresh = &$_;$_ = [];
		unset($_);
	
		// Language commands...
		
		// Process various command
		// TODO - put in alphabetical order?
		switch (strtolower($Command_Name))
		{
			case 'debug':
				if (isset($Command['Clauses']['target']))
				{
					// Check if the Target clause includes a dot
					$Target_Clause_Tree = &$Command['Clauses']['target']['Tree'];
				
					// Build a new command to get the target
					$Debug_Target_Command = array(
						'Kind' => 'Command',
						'Clauses' => array(
							'name' => array('Name' => 'Name', 'Tree' => &$Target_Clause_Tree),
							'as' => array('Name' => 'As', 'Tree' => array('Kind' => 'Variable', 'Value' => 'Reference'))
						)
					);
				
					// Process the command
					$Processed_Debug_Target_Command = &Process_Command($Database, $Debug_Target_Command, $Memory_Stack_Reference);
				
					// Fetch the target
					$Debug_Target = &$Processed_Debug_Target_Command['Chunks'][0]['Item'];
	
					if (count($Debug_Target['References']))
						$Debug_Reference = &$Debug_Target['References'][count($Debug_Target['References']) -1];
					else
						$Debug_Reference = &New_Not_Set();
						
					if (array_key_exists('Variables', $Debug_Reference))
						$Debug_Variables = &$Debug_Reference['Variables'];
					else
						$Debug_Variables = &New_Not_Set();

					if (array_key_exists('Cached_Base_Type', $Debug_Target))
						$Debug_Type = &$Debug_Target['Cached_Base_Type'];
					else
						$Debug_Type = &New_Not_Set();
						
					if (array_key_exists('Cached_Specific_Properties', $Debug_Target))
						$Debug_Properties = &$Debug_Target['Cached_Specific_Properties'];
					else
						$Debug_Properties = &New_Not_Set();
				
					// Traverse reference, or item
					if (array_key_exists('reference', $Command['Clauses']))
						traverse($Debug_Reference);
					else if (array_key_exists('variables', $Command['Clauses']))
						traverse($Debug_Variables);
					else if (array_key_exists('properties', $Command['Clauses']))
						traverse($Debug_Properties);
					else if (array_key_exists('type', $Command['Clauses']))
						traverse($Debug_Type);
					else
						traverse($Debug_Target);
						
					if (array_key_exists('exit', $Command['Clauses']))
						exit();
				}
				break;
				
			case 'clean':	
				// TODO - Copy block, probably.
				$Cleaned_Block = &$Inner_Content_Block;
				
				// Clean the text chunks in each block...
				$Inner_Content_Block_Chunks = &$Cleaned_Block['Chunks'];
				foreach ($Inner_Content_Block_Chunks as &$Inner_Content_Block_Chunk)
				{
					if ($Inner_Content_Block_Chunk['Kind'] == 'Text_Chunk')
					{
						$Inner_Content_Block_Chunk_Content = &$Inner_Content_Block_Chunk['Content'];
						
						// Clean content of text chunk...
						
						// Clean newlines
						if (isset($Command['Clauses']['newlines']))
						{
							// TODO - better single pass regex
							$Cleaned_Inner_Content_Block_Chunk_Content = &$_;$_ = str_replace("\n", '', $Inner_Content_Block_Chunk_Content); 
							unset($_);

							$Next_Cleaned_Inner_Content_Block_Chunk_Content = &$_;$_ = str_replace("\r", '', $Cleaned_Inner_Content_Block_Chunk_Content);
							$Cleaned_Inner_Content_Block_Chunk_Content = &$Next_Cleaned_Inner_Content_Block_Chunk_Content;
							unset($_);unset($Next_Cleaned_Inner_Content_Block_Chunk_Content);

						}
						
						// Clean newlines + surrounding whitespace
						// TODO - name? 
						else if (isset($Command['Clauses']['whitespace']))
						{
							// Spilt content by newlines
							// TODO - single pass various newline regex
							$Inner_Content_Block_Chunk_Content_Parts = explode("\n", $Inner_Content_Block_Chunk_Content);
							$Cleaned_Inner_Content_Block_Chunk_Content_Parts = &$_;$_ = [];
							unset($_);

							// Trim each line
							foreach ($Inner_Content_Block_Chunk_Content_Parts as &$Inner_Content_Block_Chunk_Content_Part)
							{
								$Cleaned_Inner_Content_Block_Chunk_Content_Part = &$_;$_ = trim($Inner_Content_Block_Chunk_Content_Part); 
								unset($_);
								
								if ($Cleaned_Inner_Content_Block_Chunk_Content_Part != '')
									$Cleaned_Inner_Content_Block_Chunk_Content_Parts[] = &$Cleaned_Inner_Content_Block_Chunk_Content_Part;
							}
							
							// Combine trimmed lines
							$Cleaned_Inner_Content_Block_Chunk_Content = &$_;$_ = implode('', $Cleaned_Inner_Content_Block_Chunk_Content_Parts);
							unset($_);
							
							// Cleanup variables not by reference
							unset($Inner_Content_Block_Chunk_Content_Parts);
						}
						
						// Replace text chunk content with cleaned content.
						$Inner_Content_Block_Chunk['Content'] = &$Cleaned_Inner_Content_Block_Chunk_Content;
					}
				}
				
				// Directly process cleaned block
				$Processed_Cleaned_Block = &Process_Block($Database, $Cleaned_Block, $Memory_Stack_Reference);
				
				// Append result to result block
				$Processed_Command['Chunks'][] = $Processed_Cleaned_Block;
				break;
			
			case 'ignore':
				$Result = &New_Block();
				$Result['Kind'] = 'False_Evaluation_Without_Else_Chunk';
				$Processed_Command['Chunks'][] = &$Result;
				break;
				
			// Metadata
			case 'no_wrap':
			case 'no_scripts':
			case 'no_refresh':
				// Note: No_Wrap and No_Scripts functionality handled in rendering code
				
				// Directly process content
				$Processed_Inner_Content_Block = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
				
				// Append result to result block
				$Processed_Command['Chunks'][] = &$Processed_Inner_Content_Block;
				
				break;
				
				
			// Formatting...
			
			// Format
			case 'format':
			{
				// Check if formatting an expression in the command, or the content of the tags
				if ($Inner_Content_Block)
				{
					// Process content (note: processed result does not get added to main result i.e. never printed to browser)
					$Processed_Inner_Content_Block = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
					
					Render_Processed_Block($Processed_Inner_Content_Block, array('No_Wrap' => true));
					
					// Get processed block content
					$Processed_Inner_Content_Text = &$Processed_Inner_Content_Block['Content'];
					$Script = &$Processed_Inner_Content_Block['Script'];
					
					// Get input text
					$Input_Text = &$Processed_Inner_Content_Text;
				}
				elseif (isset($Command['Clauses']['expression']))
				{
					// Get result from expression
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['expression']['Tree'], $Memory_Stack_Reference);
					
					// Get input text
					$Input_Text = &$Evaluated_Expression['Value'];
				}
				else
					throw new exception('No data to format.');
				
				// Copy input to result
				$Result_Text = $Input_Text;
				
				// Format result according to clauses
				// TODO @feature-language: treat 'digits', 'decimals' and 'as' as expressions
				if (isset($Command['Clauses']['digits']) || isset($Command['Clauses']['decimals']))
				{
					if (isset($Command['Clauses']['decimals']))
						$Result_Text = number_format($Result_Text, $Command['Clauses']['decimals']['Tree']['Value'], '.', '');
					if (isset($Command['Clauses']['digits']))
					{
						if ($Result_Text == 0)
							$Current_Digits = 0;
						else
							$Current_Digits = floor(log10($Result_Text));
						$Result_Text = str_repeat('0', max(0, $Command['Clauses']['digits']['Tree']['Value'] - $Current_Digits - 1)) . (string)$Result_Text;
					}
				}
				else
				{
					// TODO: Treat 'as' tree as expression?
					if (array_key_exists('as', $Command['Clauses']))
						$Result_Text = Jelly_Format($Result_Text, $Command['Clauses']['as']['Tree']['Value']);
				}
				
				if (isset($Command['Clauses']['characters']))
				{
					// TODO - Evaluate
					$Character_Count = $Command['Clauses']['characters']['Tree']['Value'];
					$Result_Text = substr($Result_Text, 0, $Character_Count);
				}
				
				// Append result to processed command
				$Text_Chunk = &New_Chunk('Text_Chunk');
				$Text_Chunk['Content'] = &$Result_Text;
				if ($Script)
					$Text_Chunk['Script'] = &$Script;					
				$Processed_Command['Chunks'][] = &$Text_Chunk;
				
				break;
			}
				
				
			// Special...
			
			// HTTP_Host
			case 'http_host':
			{
				$HTTP_Host_String = &$_SERVER['HTTP_HOST'];
				
				// Append result to processed command
				$Text_Chunk = &New_Chunk('Text_Chunk');
				$Text_Chunk['Content'] = &$HTTP_Host_String;
				$Processed_Command['Chunks'][] = &$Text_Chunk;
				
				break;
			}
			
			// Geocode
			case 'geocode':	
				// Get target	
				if (!isset($Command['Clauses']['target']))
					throw new Exception("Error: no target provided to geocode");
				else
				{				
					// Check if the Target clause includes a dot
					$Target_Clause_Tree = &$Command['Clauses']['target']['Tree'];
				
					// Build a new command to get the target
					$Geocode_Target_Command = array(
						'Kind' => 'Command',
						'Clauses' => array(
							'name' => array('Name' => 'Name', 'Tree' => &$Target_Clause_Tree),
							'as' => array('Name' => 'As', 'Tree' => array('Kind' => 'Variable', 'Value' => 'Reference'))
						)
					);
				
					// Process the command
					$Processed_Geocode_Target_Command = &Process_Command($Database, $Geocode_Target_Command, $Memory_Stack_Reference);
				
					// Fetch the target
					$Geocode_Target_Item = &$Processed_Geocode_Target_Command['Chunks'][0]['Item'];			
				}
				
				// Validate target
				// TODO - and descendants...
				$Cached_Geocode_Target_Type = &$Geocode_Target_Item['Cached_Specific_Type'];
				$Cached_Geocode_Target_Type_Alias = $Cached_Geocode_Target_Type['Alias'];
				if (strtolower($Cached_Geocode_Target_Type_Alias) != 'location')
					throw new Exception("Error: Can only geocode locations");	

				// Geocode target
				$Address_Value = &$Geocode_Target_Item['Data']['Original_Address'];
				$Geocoded_Address = &Geocode($Address_Value);
				$Geocode_Evaluation = &New_Boolean(false);

				// Save geocoded value
				if ($Geocoded_Address !== false)
				{
					$Geocode_Evaluation = &New_Boolean(true);
					Set_Value($Geocode_Target_Item, 'Latitude', $Geocoded_Address['lat']);
					Set_Value($Geocode_Target_Item, 'Longitude', $Geocoded_Address['lng']);
					Set_Value($Geocode_Target_Item, 'Name', $Geocoded_Address['Address']);
				}
			
				// Process content depending on evaluation
				$Result = &Process_Block_With_Evaluation($Database, $Inner_Content_Block, $Geocode_Evaluation, $Memory_Stack_Reference);
				
				// Append result to result block
				$Processed_Command['Chunks'][] = &$Result;
				
				break;
			
			// Cache Transformed Picture Size
			// TODO - these are just PHP shorthands to invoke in templates, don't know how to organize them correctly
			case 'cache_transformed_picture':
			case 'get_transformed_picture_file_size':
			case 'get_transformed_picture_path':
			case 'get_transformed_picture_aspect_ratio':
			case 'get_transformed_picture_width':
			case 'get_transformed_picture_height':
			case 'write_transformed_picture':
				// TODO - maybe implement from clause.
				
				// Get picture from context
				$Picture_Command_String = &$_;$_ = '1 Picture from Memory as Reference';
				unset($_);
				$Processed_Picture_Command = &Process_Command_String($Database, $Picture_Command_String, $Memory_Stack_Reference);
				$Picture_Item = &$Processed_Picture_Command['Chunks'][0]['Item'];
				
				// Get picture path
				$Picture_Path = &$Picture_Item['Data']['Path'];
				$Picture_ID = &$Picture_Item['Data']['ID'];

				// Get picture width & height
				$Picture_Original_Width = &$Picture_Item['Data']['Width'];
				$Picture_Original_Height = &$Picture_Item['Data']['Height'];
				$Picture_Original_Aspect_Ratio = &$Picture_Item['Data']['Aspect_Ratio'];
				if (!$Picture_Original_Width)
				{
					ini_set('user_agent', 'better/1');
					list($Width, $Height, $Type, $Attr) = GetImageSize($Picture_Path);
					$Picture_Original_Width = &$Width;
					$Picture_Original_Height = &$Height;
					$Picture_Original_Aspect_Ratio = &New_Number($Picture_Original_Width / $Picture_Original_Height);
					Set_Value($Picture_Item, "Width", $Picture_Original_Width);
					Set_Value($Picture_Item, "Height", $Picture_Original_Height);
					Set_Value($Picture_Item, "Aspect_Ratio", $Picture_Original_Aspect_Ratio);
					Save_Item($Picture_Item);
				}
				
				// Get command values
				if (array_key_exists('width', $Command['Clauses']))
				{
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['width']['Tree'], $Memory_Stack_Reference);
					$Picture_Width = &$Evaluated_Expression['Value'];
				}
				if (array_key_exists('height', $Command['Clauses']))
				{
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['height']['Tree'], $Memory_Stack_Reference);
					$Picture_Height = &$Evaluated_Expression['Value'];
				}
				if (array_key_exists('maximum_width', $Command['Clauses']))
				{
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['maximum_width']['Tree'], $Memory_Stack_Reference);
					$Picture_Maximum_Width = &$Evaluated_Expression['Value'];
				}
				if (array_key_exists('maximum_height', $Command['Clauses']))
				{
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['maximum_height']['Tree'], $Memory_Stack_Reference);
					$Picture_Maximum_Height = &$Evaluated_Expression['Value'];
				}
				
				// Set explicit size
				if (isset($Picture_Width) && $Picture_Width)
				{
					$Picture_Final_Width = &$Picture_Width;
					if (isset($Picture_Height) && $Picture_Height)
						$Picture_Final_Height = &$Picture_Height;
					else
					{
						$Picture_Final_Height = &$_;$_ = $Picture_Original_Height * ($Picture_Final_Width / $Picture_Original_Width);
						unset($_);
					}
				}
				elseif (isset($Picture_Height) && $Picture_Height)
				{
					$Picture_Final_Height = &$Picture_Height;
					$Picture_Final_Width = &$_;$_ = $Picture_Original_Width * ($Picture_Final_Height / $Picture_Original_Height); 
					unset($_);
				}
				else
				{
					$Picture_Final_Width = &$Picture_Original_Width;
					$Picture_Final_Height = &$Picture_Original_Height;
				}

				// Bound by maximum sizes
				if ((isset($Picture_Maximum_Width) && $Picture_Maximum_Width) && $Picture_Final_Width > $Picture_Maximum_Width)
				{	
					$Picture_Final_Width = &$Picture_Maximum_Width;
					$Picture_Final_Height = &$_;$_ = $Picture_Original_Height * ($Picture_Final_Width / $Picture_Original_Width);
					unset($_);					
				}
				if ((isset($Picture_Maximum_Height) && $Picture_Maximum_Height) && $Picture_Final_Height > $Picture_Maximum_Height)
				{
					$Picture_Final_Height = &$Picture_Maximum_Height;
					$Picture_Final_Width = &$_;$_ = $Picture_Original_Width * ($Picture_Final_Height / $Picture_Original_Height);
					unset($_);
				}
				
				// Round final values
				$Next_Picture_Final_Width = &$_;$_ = round($Picture_Final_Width);
				$Picture_Final_Width = &$Next_Picture_Final_Width;
				unset($_);unset($Next_Picture_Final_Width);
				
				$Next_Picture_Final_Height = &$_;$_ = round($Picture_Final_Height);
				$Picture_Final_Height = &$Next_Picture_Final_Height;
				unset($_);unset($Next_Picture_Final_Height);

				// Get file directory...
				
				// Get base directory
				$Picture_Directory = $GLOBALS["Data_Directory_Path"] . '/' . 'Cache' . '/' . 'Picture';
				
				// Get original file directory 
				if ($Picture_Final_Width == $Picture_Original_Width && $Picture_Final_Height == $Picture_Original_Height)
				{	
					// TODO - handle in file uploads...
					$Picture_Directory .=  '/' . 'Original';
				}
				
				// Get transformed file directory
				else
				{					
					if (isset($Picture_Width) || isset($Picture_Height))
					{
						if (!isset($Picture_Height))
							$Picture_Directory .= '/' . 'Width' . '/' . $Picture_Width;
						else if (!isset($Picture_Width))
							$Picture_Directory .= '/' . 'Height' . '/' . $Picture_Height;
						else
							$Picture_Directory .= '/' . 'Bounds' . '/' . $Picture_Width . '_' . 'By' . '_' . $Picture_Height;
					}
					
					if (isset($Picture_Maximum_Width) || isset($Picture_Maximum_Height))
					{
						if (!isset($Picture_Maximum_Height))
							$Picture_Directory .= '/' . 'Maximum_Width' . '/' . $Picture_Maximum_Width;
						else if (!isset($Picture_Maximum_Width))
							$Picture_Directory .= '/' . 'Maximum_Height' . '/' . $Picture_Maximum_Height;
						else
							$Picture_Directory .= '/' .  'Maximum_Bounds' . '/' . $Picture_Maximum_Width . '_' . 'By' . '_' . $Picture_Maximum_Height;	
					}
				}
				
				// Get file path
				$Transformed_File_Path = $Picture_Directory . "/" . $Picture_ID . '_' . basename($Picture_Path);
				
				switch (strtolower($Command_Name))
				{		
					case 'get_transformed_picture_path':
						// Append result to processed command
						$Text_Chunk = &New_Chunk('Text_Chunk');
						$Text_Chunk['Content'] = &$Transformed_File_Path;
						$Processed_Command['Chunks'][] = &$Text_Chunk;	
						break;
						
					case 'get_transformed_picture_aspect_ratio':
						// Append result to processed command
						$Number_Chunk = &New_Chunk('Number_Chunk');
						$Number_Chunk['Content'] = &$Picture_Final_Aspect_Ratio;
						$Processed_Command['Chunks'][] = &$Number_Chunk;	
						break;
						
					case 'get_transformed_picture_width':
						// Append result to processed command
						$Number_Chunk = &New_Chunk('Number_Chunk');
						$Number_Chunk['Content'] = &$Picture_Final_Width;
						$Processed_Command['Chunks'][] = &$Number_Chunk;	
						break;
						
					case 'get_transformed_picture_height':
						// Append result to processed command
						$Number_Chunk = &New_Chunk('Number_Chunk');
						$Number_Chunk['Content'] = &$Picture_Final_Height;
						$Processed_Command['Chunks'][] = &$Number_Chunk;	
						break;

					case 'get_transformed_picture_file_size':
						$File_Size = filesize($Transformed_File_Path);
						
						// Append result to processed command
						$Number_Chunk = &New_Chunk('Number_Chunk');
						$Number_Chunk['Content'] = &$File_Size;
						$Processed_Command['Chunks'][] = &$Number_Chunk;	
						break;

					// TODO - cropping
					case 'cache_transformed_picture':
						$Image_Parameters = array(
								"Source_Left" => 0,
								"Source_Top" => 0,
								"Source_Width" => $Picture_Original_Width,
								"Source_Height" => $Picture_Original_Height,
								"Final_Left" => 0,
								"Final_Top" => 0,
								"Final_Width" => $Picture_Final_Width,
								"Final_Height" => $Picture_Final_Height,
							);
						
						// Cache transformed image, if it doesn't already exist.
						if (!file_exists($Transformed_File_Path))
						{
							// Create directories for new transformed picture path
							Make_Directories_If_Nonexistent($Picture_Directory);
							
							// Transform image
							$Image_Parameters["File_Path"] = $Picture_Path;
							$Image_Parameters["Resized_File_Path"] = $Transformed_File_Path;
							Resize_Image($Image_Parameters);
						}
						break;

					case 'write_transformed_picture':						
						// TODO - The behavior here is dirty & quick ...
						//$File_Data = file_get_contents($Transformed_File_Path);
						$GLOBALS['Stream_Content_Path'] = $Transformed_File_Path;
						break;
				}

				break;

			// Read_File
			case 'read_file':
			{
				$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['path']['Tree'], $Memory_Stack_Reference);
				$File_Path = &$Evaluated_Expression['Value'];
				
// 				readfile($File_Path);
				
				// TODO: this is reading the file into memory so that we don't have to exit (headers handled last by jelly); better way?
				
				$File_Data = file_get_contents($File_Path);
				
				// Append result to processed command
				$Text_Chunk = &New_Chunk('Text_Chunk');
				$Text_Chunk['Content'] = &$File_Data;
				$Processed_Command['Chunks'][] = &$Text_Chunk;
				
				break;
			}
			
			// Read_File
			case 'file_size':
			{
				$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['path']['Tree'], $Memory_Stack_Reference);
				$File_Path = &$Evaluated_Expression['Value'];
				
				$File_Size = filesize($File_Path);
				
				// Append result to processed command
				$Text_Chunk = &New_Chunk('Number_Chunk');
				$Text_Chunk['Content'] = &$File_Size;
				$Processed_Command['Chunks'][] = &$Text_Chunk;
// 				traverse($Text_Chunk);
				
				break;
			}
			
			
			// Functions...
			
			// Math
			case 'math':
				
				// Check if formatting an expression in the command, or the content of the tags
				if ($Inner_Content_Block)
				{
					// Process content (note: processed result does not get added to main result i.e. never printed to browser)
					$Processed_Inner_Content_Block = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
					
					Render_Processed_Block($Processed_Inner_Content_Block, array('No_Wrap' => true));
					
					// Get processed block content
					$Processed_Inner_Content_Text = &$Processed_Inner_Content_Block['Content'];
					
					// TODO @feature-language finish
					throw new exception('Math not finished');
				}
				elseif (isset($Command['Clauses']['expression']))
				{
					// Get result from expression
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['expression']['Tree'], $Memory_Stack_Reference);
					
					$Result_Text = &$Evaluated_Expression['Value'];
				}
				else
					throw new exception('No data to format.');
				
				// Append result to processed command
				$Math_Result_Chunk = &New_Chunk('Number_Chunk');
				$Math_Result_Chunk['Content'] = &$Result_Text;
				$Processed_Command['Chunks'][] = &$Math_Result_Chunk;
				
				break;
				
			
			// Code...
			
			// PHP
			case 'php':
				
				// Check if formatting an expression in the command, or the content of the tags
				if ($Inner_Content_Block)
				{
					// Process content (note: processed result does not get added to main result i.e. never printed to browser)
					$Processed_Inner_Content_Block = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
					
					Render_Processed_Block($Processed_Inner_Content_Block, array('No_Wrap' => true));
					
					// Get processed block content
					$Processed_Inner_Content_Text = &$Processed_Inner_Content_Block['Content'];
					
					// Get input text
					$Input_Text = &$Processed_Inner_Content_Text;
				}
				elseif (isset($Command['Clauses']['expression']))
				{
					// Get result from expression
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['expression']['Tree'], $Memory_Stack_Reference);
					
					// Get input text
					$Input_Text = &$Evaluated_Expression['Value'];
				}
				else
					throw new exception('No PHP script to execute.');
				
				// Copy processed content to PHP script
				$PHP_Script = $Input_Text;
				
				// Unescape escaped brackets
				// TODO: not sure about this
				$PHP_Script = str_replace('\\[', '[', $PHP_Script);
				$PHP_Script = str_replace('\\]', ']', $PHP_Script);
				
				// Run the PHP script and reference it into Result
				$PHP_Script_Result = eval($PHP_Script);
				$Result_Text = &$PHP_Script_Result;
				
				// Append result to processed command
				$Text_Chunk = &New_Chunk('Text_Chunk');
				$Text_Chunk['Content'] = &$Result_Text;
				$Processed_Command['Chunks'][] = &$Text_Chunk;
				break;
			
			
			// Environmental
			
			case 'external_script':
			
				// TODO double check this...
				
				if ($Inner_Content_Block)
				{
					// Process content (note: processed result does not get added to main result i.e. never printed to browser)
					$Processed_Inner_Content_Block = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
					
					Render_Processed_Block($Processed_Inner_Content_Block, array('No_Wrap' => true));
					
					// Get processed block content
					if (!is_null($Processed_Inner_Content_Block['Script']))
					{
						$Processed_Inner_Content_Text = &$_;$_ = $Processed_Inner_Content_Block['Content'] . $Processed_Inner_Content_Block['Script']; 
						unset($_);
					}
					else
						$Processed_Inner_Content_Text =  &$Processed_Inner_Content_Block['Content'];
					
					$Processed_Command['Script'] = &$Processed_Inner_Content_Text;
				}
				
				break;
				
			
			// Object Commands
			
			case 'save':
				// TODO untested
				
				// Check if the Target clause includes a dot
				$Target_Clause_Tree = &$Command['Clauses']['target']['Tree'];
				
				// Build a new command to get the target
				$Save_Target_Command = array(
					'Kind' => 'Command',
					'Clauses' => array(
						'name' => array('Name' => 'Name', 'Tree' => &$Target_Clause_Tree),
						'as' => array('Name' => 'As', 'Tree' => array('Kind' => 'Variable', 'Value' => 'Reference'))
					)
				);
				
				// Process the command
				$Processed_Save_Target_Command = &Process_Command($Database, $Save_Target_Command, $Memory_Stack_Reference);
				
				// Fetch the target
				$Save_Target = &$Processed_Save_Target_Command['Chunks'][0]['Item'];
				
				// Make sure item is ready
				if ($Save_Target['End_Of_Results'])
					throw new Exception('Could not find item to save.');

				// Handle statuses
				if(isset($Command['Clauses']['as']))
				{
					$As_Clause_Tree = &$Command['Clauses']['as']['Tree'];
					$Evaluated_As_Clause = &Evaluate_Clause_Tree($Database, $As_Clause_Tree, $Memory_Stack_Reference);
					$Save_Target_Status = &$Evaluated_As_Clause['Value'];

					// TODO - this is for temporary debugging until  the great item status ascension
					// TODO - should actually resolve value from Status type
					// TODO - with aliases or names...
					switch (strtolower($Save_Target_Status))
					{
						case 'unsaved':
						case 'draft':
						case 'published':
							Set_Simple($Save_Target, 'Status', $Save_Target_Status); 
							break;
						default:
							throw new Exception ('Unknown status: ' . $Save_Target_Status);
							break;
					}
				}

				// Save item normally
				Save_Item($Save_Target);
				
				// Update browser
				// TODO - update to expression? 
				if (!isset($Command['Clauses']['no_refresh']))
				{
					$Save_Target_ID = &$Save_Target['Data']['ID'];
					$Save_Target_Cached_Specific_Type = &$Save_Target['Cached_Specific_Type'];
					$Save_Target_Cached_Specific_Type_Alias = &$Save_Target_Cached_Specific_Type['Alias'];
					
					// Set item to refresh.
					$Command_Items_To_Refresh[] = &$Save_Target_ID;

					// Set iterators to refresh...
					
					// Set target iterator to refresh.
					$Command_Iterators_To_Refresh[] = &$Save_Target_Cached_Specific_Type_Alias;

					// Set additional iterators to refresh...
					switch (strtolower($Save_Target_Cached_Specific_Type_Alias))
					{
						case 'session':
							// If this is the current session, set up a special refresh.
							if ($GLOBALS['Current_Session_Item'])
							{	
								$Current_Session_Item = &$GLOBALS['Current_Session_Item'];
								if ($Current_Session_Item['Data']['ID']== $Save_Target_ID)
									$Command_Special_Refreshes['current_session'] = true;
							}
							break;
						
						// Templates
						case 'template':
							$Saved_Template_Type_Alias = &As_Key($Save_Target['Data']['Type']);
							$Command_Iterators_To_Refresh[] =  &$Saved_Template_Type_Alias;
							break;
					
						// Properties
						case 'property':
							$Saved_Property_Type_Alias = &As_Key($Save_Target['Data']['Type']);
							$Saved_Property_Value_Type_Alias = &As_Key($Save_Target['Data']['Value_Type']);
							$Command_Iterators_To_Refresh[] =  &$Saved_Property_Type_Alias;
							$Command_Iterators_To_Refresh[] =  &$Saved_Property_Value_Type_Alias;
							// TODO - I think this is necessary but added this w/o thought.
							if (isset($Save_Target['Data']['Attachment_Type']))
							{
								$Saved_Property_Attachment_Type_Alias = &As_Key($Save_Target['Data']['Attachment_Type']);
								$Command_Iterators_To_Refresh[] =  &$Saved_Property_Attachment_Type_Alias;
							}
							break;
					}
				}
				break;
				
			// Remove
			case 'remove':
			{
				
				// Check if the Target clause includes a dot
				$Target_Clause_Tree = &$Command['Clauses']['target']['Tree'];
				
				// Build a new command to get the target
				$Remove_Target_Command = array(
					'Kind' => 'Command',
					'Clauses' => array(
						'name' => array('Name' => 'Name', 'Tree' => &$Target_Clause_Tree),
						'as' => array('Name' => 'As', 'Tree' => array('Kind' => 'Variable', 'Value' => 'Reference'))
					)
				);
				
				// Process the command
				$Processed_Remove_Target_Command = &Process_Command($Database, $Remove_Target_Command, $Memory_Stack_Reference);
				
				// Fetch the target
				$Remove_Target = &$Processed_Remove_Target_Command['Chunks'][0]['Item'];
				
				// Make sure item is ready
				if ($Remove_Target['End_Of_Results'])
					throw new Exception('Could not find item to remove.');
			
				// TODO...
				Delete_Item($Remove_Target);

				if (!isset($Command['Clauses']['no_refresh']))
				{
					// Set item to refresh		
					$Remove_Target_ID = &$Remove_Target['Data']['ID'];
					$Command_Items_To_Refresh[] =  &$Remove_Target_ID;
	
					// Set iterator to refresh
					$Remove_Target_Cached_Specific_Type = &$Remove_Target['Cached_Specific_Type'];
					$Remove_Target_Cached_Specific_Type_Alias = &$Remove_Target_Cached_Specific_Type['Alias'];				
					$Command_Iterators_To_Refresh[] =  &$Remove_Target_Cached_Specific_Type_Alias;
				}
				break;			
			}	
				
			// Add
			case 'add':
			{
				// Get target
				$Evaluated_Target_Clause = &Evaluate_Clause_Tree($Database, $Command['Clauses']['target']['Tree'], $Memory_Stack_Reference);
				$Add_Target_Item = &$Evaluated_Target_Clause['Value'];
				if ($Add_Target_Item['End_Of_Results'])
					throw new Exception('Could not find item to add');
				
				$To_Clause_Tree = &$Command['Clauses']['to']['Tree'];
				
				// Skip the bottom term in the To clause tree
				$Progressive_To_Clause_Tree = $To_Clause_Tree['Terms'][1];
				
				// Build a new command to get the To
				$Add_To_Command = &New_Command();
				
				// Set new commands name to the progressive tree
				$Add_To_Command['Clauses']['name'] = &New_Clause();
				$Add_To_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
				unset($_);
				$Add_To_Command['Clauses']['name']['Tree'] = &$Progressive_To_Clause_Tree;
				
				// Add "As Reference" to new comamnd
				$Add_To_Command['Clauses']['as'] = &New_Clause();
				$Add_To_Command['Clauses']['as']['Name'] = &$_;$_ = 'As'; 
				unset($_);
				$Add_To_Command['Clauses']['as']['Tree'] = &New_Tree();
				$Add_To_Command['Clauses']['as']['Tree']['Kind'] = &$_;$_ = 'Variable'; 
				unset($_);
				$Add_To_Command['Clauses']['as']['Tree']['Value'] = &$_;$_ = 'Reference'; 
				unset($_);				

				// Process the command
				$Processed_Add_To_Command = &Process_Command($Database, $Add_To_Command, $Memory_Stack_Reference);
				
				// Fetch the to
				$Add_To_Item = &$Processed_Add_To_Command['Chunks'][0]['Item'];
			
				// Get property from the right-most dot operator
				$Add_Property_Lookup = &$To_Clause_Tree['Terms'][0]['Value'];
				
				if ($Add_To_Item['End_Of_Results'])
					throw new Exception('Could not find item to add to');
				
				// Add 'to' to target
				$Attachment_Item = Add_Value($Add_To_Item, $Add_Property_Lookup, $Add_Target_Item);
				
				/*
				// TODO: Reimplement this?
				// If content is set, render it within the context of the new attachment item
				if ($Inner_Content_Block)
				{
					// Add attachment to context
					$Memory_Stack_Reference_Length = count($Memory_Stack_Reference);
					$Memory_Stack_Reference[] = $Attachment_Item;
					
					// Process content
					$Processed_Inner_Content_Block = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
					
					// Append result to result block
					$Processed_Command['Chunks'][] = &$Processed_Inner_Content_Block;
					
					// Restore context
					array_splice($Memory_Stack_Reference, $Memory_Stack_Reference_Length);
				}
				*/
				
				// Update Browser
				// TODO - update to expression? 
				// TODO - commented out, because I think save should handle this exclusively? Might be wrong.
// 				if (!isset($Command['Clauses']['no_refresh']))
// 				{
// 					$Add_To_Item_ID = &$Add_To_Item['Data']['ID'];
// 					$Add_To_Item_Cached_Base_Type_Alias = &$Add_To_Item['Cached_Base_Type']['Alias'];
// 					$Processed_Command['Script'] .= 'Jelly.References.Trigger_Refresh({\'Kind\': \'Item\', \'Lookup_Key\': \'' . $Add_To_Item_ID . '\'});' . "\n";
// 					$Processed_Command['Script'] .= 'Jelly.References.Trigger_Refresh({\'Kind\': \'Iterator\', \'Lookup_Key\': \'' . $Add_To_Item_Cached_Base_Type_Alias . '\'});' . "\n";
// 					// TODO if reverse property, update original in browser (Tristan: I don't remember what this means)
// 				}
				break;
			}
			
			// Move
			case 'move':
			
				// TODO...
				throw new exception('Move not done.');
				
				break;
			
			// Set
			case 'set':
			{
				// TODO - set values should not be from database (i.e. context-only)				
			
// 				echo "SET>...>>\n\n\n\nn";

				// TODO: setting to negative values failes (i.e. set test to -1)
				// Load the "to" clause
				// TODO: this changes the command. Generate new command? Or just cull the to clauses?
				if (isset($Command['Clauses']['=']))
					$To_Clause = &$Command['Clauses']['='];
				elseif (isset($Command['Clauses']['to']))
					$To_Clause = &$Command['Clauses']['to'];

// 					traverse($To_Clause);
				// TODO: setting value types to runtime variable properties may be useful?
				//Load the "As" clause
// 				if (isset($Command['Clauses']['as']))
// 					$As_Clause = &$Command['Clauses']['as'];

				// Get new value from "to" clause or inner content
				if (isset($To_Clause))
				{
					// Get result from expression
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $To_Clause['Tree'], $Memory_Stack_Reference);

					// Get expression value
					$Set_Value = &$Evaluated_Expression['Value'];
				}
				elseif ($Inner_Content_Block)
				{
					// Process content (note: processed result does not get added to main result i.e. never printed to browser)
					$Processed_Inner_Content_Block = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
			
					Render_Processed_Block($Processed_Inner_Content_Block, array('No_Wrap' => true));
			
					// Get processed block content
					$Set_Value = &$Processed_Inner_Content_Block['Content'];
				}
				else
				{
					traverse($Command);
					throw new exception('Set: no data to set.');
				}
	
				// Check if the Target clause includes a dot
				$Target_Clause_Tree = &$Command['Clauses']['target']['Tree'];
				if (strtolower($Target_Clause_Tree['Kind']) == 'operator')
				{
					// Check if dot tree starts with Current_Session
					$Recursive_Target_Clause_Tree = &$Target_Clause_Tree;
					while ($Recursive_Target_Clause_Tree['Kind'] == 'Operator')
						$Recursive_Target_Clause_Tree = &$Recursive_Target_Clause_Tree['Terms'][1];
					if (strtolower($Recursive_Target_Clause_Tree['Value']) == 'current_session')
					{
						{
							// Create session if doesn't already exist
							if (!isset($GLOBALS['Current_Session_Item']))
							{
								// Create session database item
								$Session_Cached_Type = &Get_Cached_Type($Database, 'Session');
								$Session_Item = &Create_Memory_Item($Database, $Session_Cached_Type);
								Set_Simple($Session_Item, 'Code', rand(10000000, 99999999));
								Save_Item($Session_Item);
						
								// Trigger special case refresh 
								// TODO - looksa gooda to meea
								if (!isset($Command['Clauses']['no_refresh']))								
									$Processed_Command['Script'] .= 'Jelly.References.Trigger_Refresh({\'Kind\': \'Current_Session\'});' . "\n";
									
								// Replace session marker in context and globals
								Set_Value($GLOBALS['Globals_Item'], 'Current_Session', $Session_Item);
								$GLOBALS['Current_Session_Item'] = &$Session_Item;
						
								// Set session cookies
								// TODO: strtotime() no longer works (gives an error about system's timezone)... should figure out how to get working again
								// TODO: these session variables might have been cleared earlier by Jelly.php (i.e. if creating a new session in the same request as the previous session expired.). Research whether clearing a cookie and then setting the same cookie in a response is valid HTTP.
								setcookie('Session_ID', $Session_Item['Data']['ID'], time() + 60 * 60 * 24 * 30 * 6, '/');
								setcookie('Session_Code', $Session_Item['Data']['Code'], time() + 60 * 60 * 24 * 30 * 6, '/');
							}
							else
							{
								$Session_Item = &$GLOBALS['Current_Session_Item'];
							}
						}
					}
			
					// Skip the bottom term in the target clause tree
					// TODO - limit to in memory...
					$Progressive_Target_Clause_Tree = $Target_Clause_Tree['Terms'][1];
			
					// Build a new command to get the target
					$Set_Target_Command = &New_Command();
			
					// Set new commands name to the progressive tree
					$Set_Target_Command['Clauses']['name'] = &New_Clause();
					$Set_Target_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
					unset($_);					
					$Set_Target_Command['Clauses']['name']['Tree'] = &$Progressive_Target_Clause_Tree;
			
					// Add "As Reference" to new comamnd
					$Set_Target_Command['Clauses']['as'] = &New_Clause();
					$Set_Target_Command['Clauses']['as']['Name'] = &$_;$_ = 'As'; 
					unset($_);					
					$Set_Target_Command['Clauses']['as']['Tree'] = &New_Tree();
					$Set_Target_Command['Clauses']['as']['Tree']['Kind'] = &$_;$_ = 'Variable'; 
					unset($_);
					$Set_Target_Command['Clauses']['as']['Tree']['Value'] = &$_;$_ = 'Reference'; 
					unset($_);					
			
					// Process the command
					$Processed_Set_Target_Command = &Process_Command($Database, $Set_Target_Command, $Memory_Stack_Reference);
// 					traverse($Set_Target_Command);
			
					// Fetch the target
					$Set_Target = &$Processed_Set_Target_Command['Chunks'][0]['Item'];
		
					// Get property from the right-most dot operator
					$Set_Property_Lookup = &$Target_Clause_Tree['Terms'][0]['Value'];
					
					// If the target doesn't have this matching property, then set the value to the bottom-most reference's variables item instead.
					// TODO - might not be a good idea.  trying to merge variables and values automatically. 
					// TODO - might be but is incompatible with our current implementation.  reconsider and uncomment in time.
					/*
					if (!(strtolower($Set_Target['Cached_Specific_Type']['Alias']) == 'simple_item'))
					{
						// TODO - this points to an implementation confusion
						if (!Has_Property($Set_Target, $Set_Property_Lookup) && strtolower($Set_Property_Lookup) != 'target')
						{
							$Set_Target = &$Set_Target['References'][count($Set_Target['References']) -1]['Variables'];
						}
					}
					*/
				}
				else
				{
					// If no dot is set, use current memory stack reference as set target
					$Set_Target = &$Memory_Stack_Reference['Variables'];
			
					// Use the simple tree value as the property lookup
					$Set_Property_Lookup = &$Target_Clause_Tree['Value'];
				}
				
				Set_Value($Set_Target, $Set_Property_Lookup, $Set_Value);
				
				// TODO: get actual property for proper case
				
				// Automatically save when flagged
				// TODO: Auto-Save
				if (isset($Command['Clauses']['save']))
				{
					if (!isset($Command['Clauses']['no_refresh']))
					{
						Save_Item($Set_Target);
						
						$Result_Script = '' . 
							'Jelly.jQuery(\'[data-parent=' . $Set_Target['Data']['ID'] . '][data-property=' . $Set_Property_Lookup . ']\').each(function (index) {' .
								'Jelly.References.Trigger_Refresh({Kind: \'Element\', Element: this});' .
							'});';
						
						// Return script. 
						$Processed_Command['Script'] =  &$Result_Script;
					}
				}
				break;
			}
			
			// Validate 
			// TODO - make generic perhaps, just does alias's right now.
			case 'validate':
				// Get value
				$Value_Clause = 	&$Command['Clauses']['value'];
				if (isset($Value_Clause))
				{
					// Get result from expression
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Value_Clause['Tree'], $Memory_Stack_Reference);

					// Get expression value
					$Value = &$Evaluated_Expression['Value'];
				}
				else
					throw new exception('No value set to validate');
					
				// Get target
				$Target_Clause_Tree = &$Command['Clauses']['for']['Tree'];
				if (strtolower($Target_Clause_Tree['Kind']) == 'operator')
				{
					while ($Recursive_Target_Clause_Tree['Kind'] == 'Operator')
						$Recursive_Target_Clause_Tree = &$Recursive_Target_Clause_Tree['Terms'][1];
					$Progressive_Target_Clause_Tree = $Target_Clause_Tree['Terms'][1];

					// Build a new command to get the target
					$Validate_Target_Command = &New_Command();
					
					// Set new commands name to the progressive tree
					$Validate_Target_Command['Clauses']['name'] = &New_Clause();
					$Validate_Target_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
					unset($_);
					$Validate_Target_Command['Clauses']['name']['Tree'] = &$Progressive_Target_Clause_Tree;
					
					// Add "From Memory" to new comamnd
					$Validate_Target_Command['Clauses']['from'] = &New_Clause();
					$Validate_Target_Command['Clauses']['from']['Name'] = &$_;$_ = 'From'; 
					unset($_);					
					$Validate_Target_Command['Clauses']['from']['Tree'] = &New_Tree();
					$Validate_Target_Command['Clauses']['from']['Tree']['Kind'] = &$_;$_ = 'Variable'; 
					unset($_);					
					$Validate_Target_Command['Clauses']['from']['Tree']['Value'] = &$_;$_ = 'Memory'; 
					unset($_);
			
					// Add "As Reference" to new comamnd
					$Validate_Target_Command['Clauses']['as'] = &New_Clause();
					$Validate_Target_Command['Clauses']['as']['Name'] = &$_;$_ = 'As'; 
					unset($_);
					$Validate_Target_Command['Clauses']['as']['Tree'] = &New_Tree();
					$Validate_Target_Command['Clauses']['as']['Tree']['Kind'] = &$_;$_ = 'Variable'; 
					unset($_);
					$Validate_Target_Command['Clauses']['as']['Tree']['Value'] = &$_;$_ = 'Reference'; 
					unset($_);
					
					// Process the command
					$Processed_Validate_Target_Command = &Process_Command($Database, $Validate_Target_Command, $Memory_Stack_Reference);

					// Fetch the target item
					$Validate_Target_Item = &$Processed_Validate_Target_Command['Chunks'][0]['Item'];
		
					// Get property from the right-most dot operator
					$Validate_Property_Lookup = &$Target_Clause_Tree['Terms'][0]['Value'];
					
				}
				else
				{
					// If no dot operator, use current memory stack reference item 
					$Validate_Target_Item = &$Memory_Stack_Reference['Item'];
			
					// Use the simple tree value as the property lookup
					$Validate_Property_Lookup = &$Target_Clause_Tree['Value'];
				}	
				
				// TODO - desired behavior unclear for empty target case.
				if (Is_Not_Set($Validate_Target_Item))
					throw new exception('No target item found to validate');
									
				// Invalidate some alias property values, or return true for other properties
				// TODO - make generic for other properties
				$Validate_Evaluation = &$_;$_ = true;
				unset($_);
				if (strtolower($Validate_Property_Lookup) == 'alias')
				{
					// Get target type alias
					$Validate_Target_Cached_Base_Type = &$Validate_Target_Item['Cached_Base_Type'];
					$Validate_Target_Cached_Base_Type_Alias = &$Validate_Target_Cached_Base_Type['Alias'];
					
					// Get target item id
					$Validate_Target_ID = &$Validate_Target_Item['Data']['ID'];
										
					// Validate with special rules per type
					switch (strtolower($Validate_Target_Cached_Base_Type_Alias))
					{
						// For types, invalidate language command conflicts or existing types of the specified name but a different id...
						case 'type':
							// Invalidate blank types
							 if (!strlen($Value))
							 {
								$Validate_Evaluation = &$_;$_ = false;
								unset($_);
							}

							// Invalidate language command conflict
							if (array_key_exists(strtolower($Value), $Language_Terms))
							{
								$Validate_Evaluation = &$_;$_ = false;
								unset($_);
							}

							// Invalidate type alias lookup conflict
							else if (isset($Cached_Type_Lookup[strtolower($Value)]))
							{
								$Cached_Type = &$Cached_Type_Lookup[strtolower($Value)];
								$Cached_Type_ID = &$Cached_Type['ID'];
								if ($Cached_Type_ID != $Validate_Target_ID)
								{
									$Validate_Evaluation = &$_;$_ = false;
									unset($_);
								}
							}
							break;

						case 'property':						
							// Invalidate blank property alias
							 if (!strlen($Value))
							 {
								$Validate_Evaluation = &$_;$_ = false;
								unset($_);
							}

							// Invalidate type property alias conflict, if type exists.
							if ($Validate_Target_Item['Data']['Type'])
							{
								// Get type alias 
								// TODO - not sure if this is necessary
								if(Is_Item($Validate_Target_Item['Data']['Type']))
								{
									$Validate_Target_Property_Type_Item = &$Validate_Target_Item['Data']['Type'];
									$Validate_Target_Property_Type_Alias = &$Validate_Target_Property_Type_Item['Data']['Alias'];
								}	
								else
									$Validate_Target_Property_Type_Alias = &$Validate_Target_Item['Data']['Type'];
							
								// Get cached type 
								$Validate_Target_Property_Cached_Type = &Get_Cached_Type($Database, $Validate_Target_Property_Type_Alias);
								
								// Check for conflicts and invalidate conflicts
								if(isset($Validate_Target_Property_Cached_Type['Cached_Property_Lookup'][strtolower($Value)]))
								{
									$Cached_Property = &$Validate_Target_Property_Cached_Type['Cached_Property_Lookup'][strtolower($Value)];
									$Cached_Property_ID = &$Cached_Property['ID'];
									if ($Cached_Property_ID != $Validate_Target_ID)
									{
										$Validate_Evaluation = &$_;$_ = false;
										unset($_);
									}
								}
							}
							else
							{
								// TODO - desired behavior unclear.
								;
 							}
							
							break;

						case 'type_action':
						case 'template':
							// Invalidate blank template/action alias
							 if (!strlen($Value))
							 {
								$Validate_Evaluation = &$_;$_ = false;
								unset($_);
							}
								
							// Invalidate template/type_action alias conflict for specific type.
							if ($Validate_Target_Item['Data']['Type'])
							{
								// Get type alias 
								// TODO - not sure if this is necessary
								if(Is_Item($Validate_Target_Item['Data']['Type']))
								{
									$Validate_Target_Property_Type_Item = &$Validate_Target_Item['Data']['Type'];
									$Validate_Target_Property_Type_Alias = &$Validate_Target_Property_Type_Item['Data']['Alias'];
								}	
								else
									$Validate_Target_Property_Type_Alias = &$Validate_Target_Item['Data']['Type'];
								
								// Create jelly command string to match alias for template/type_action against target item's query type.
								$Conflict_Command_String = 
									$Validate_Target_Cached_Base_Type_Alias . ' ' . 
										'from' . ' ' . 
											'Database' . ' ' . 
										'where' . ' ' . 
											('Type' . ' ' . '=' . ' ' . '"'  . $Validate_Target_Property_Type_Alias .'"') . ' ' .
												 'and' . ' ' . 
											('Alias' . ' ' . '=' . ' ' . '"'  . $Value .'"') . ' ';
								// TODO - hopefully don't need this check 
								if (!is_null($Validate_Target_ID) && $Validate_Target_ID != '')
									$Conflict_Command_String .= 
												'and' . ' ' .
											('ID' . ' ' . '!=' . ' ' . $Validate_Target_ID) . ' ';
								$Conflict_Command_String .= 	
										'as' . ' ' .
											'Reference';
											
								// Get item from command string
								$Processed_Conflict_Command = &Process_Command_String($Database, $Conflict_Command_String, $Memory_Stack_Reference);
								$Conflict_Item = &$Processed_Conflict_Command['Chunks'][0]['Item'];
								
								// If the item contains a valid result, invalidate this name as a conflict
								if (!$Conflict_Item['End_Of_Results'])
								{
									$Validate_Evaluation = &$_;$_ = false;
									unset($_);	
								}
							}
							else
							{
								// TODO - desired behavior unclear.
								;
 							}
							break;
							
						// No other types that need alias validation.
						default:
							break;					
					}
			 	}
							
				// Act conditiionally	
				$Result = &Process_Block_With_Evaluation($Database, $Inner_Content_Block, $Validate_Evaluation, $Memory_Stack_Reference);
				
				// Append result to result block
				$Processed_Command['Chunks'][] = &$Result;


				break;
			
			// Protocol...
			
			// Header
			case 'header':
				// TODO: create new processed block?
				// TODO: should this process headers immediately. Not sure why they are staged like this.
				
				// Create new header
				$Header = &$_;$_ = [];
				unset($_);
				$Header['Kind'] = &$_;$_ = 'Header'; 
				unset($_);
				$Header['Header'] = &$Command['Clauses']['header']['Tree']['Value'];
				
				$Evaluated_Value_Clause_Tree = &Evaluate_Clause_Tree($Database, $Command['Clauses']['value']['Tree'], $Memory_Stack_Reference);
				$Header['Value'] = &$Evaluated_Value_Clause_Tree['Value'];
				
				// Add header to processed command
				$Processed_Command['Headers'][] = &$Header;
// 				$Header_String = &New_String($Header['Header'] . ':' . $Header['Value']);
// 				echo $Header_String . "\n";
// 				header($Header_String);
				
				break;
				
			
			// Branching
			
			// If
			case 'if':
				
				// Check if a 'where' or 'from' clause is set
				if (
					(isset($Command['Clauses']['where']) && $Command['Clauses']['where'])
					||
					(isset($Command['Clauses']['from']) && strtolower($Command['Clauses']['from']['Items'][0]['Value']) == 'database')
				)
				{
					// TODO: clean up
					$New_Selectors = array(array());
					$New_Selectors[0]['Name'] = $Command['Clauses']['condition']['Items'][0]['Value'];
					if (isset($Command['Clauses']['where']) && $Command['Clauses']['where'])
						$New_Selectors[0]['where'] = $Command['Clauses']['where'];
					if (isset($Command['Clauses']['from']) && $Command['Clauses']['from'])
						$New_Selectors[0]['from'] = $Command['Clauses']['from'];
					$New_Selectors[0]['as'] = array('Name' => 'As', 'Items' => array(array('Value' => 'Reference')));
					$New_Selector_Item = &Process_Selectors($New_Selectors, $Memory_Stack_Reference);
					
					$Memory_Stack_Reference_Length = count($Memory_Stack_Reference);
					$Memory_Stack_Reference[] = $New_Selector_Item;
				}
				
				// Evaluate the If statement
				$Condition_Clause_Tree = &$Command['Clauses']['condition']['Tree'];
				$Evaluated_Condition_Clause_Tree = &Evaluate_Clause_Tree($Database, $Condition_Clause_Tree, $Memory_Stack_Reference);
				
				// TODO - This is explicitly: true for not not set,  not EOF, not 0, not null, not empty string, not false, else false
				if (Is_Not_Set($Evaluated_Condition_Clause_Tree['Value']))
				{
					$Evaluation = false;
				}

				else if (Is_Item($Evaluated_Condition_Clause_Tree['Value']))
				{
					if (!$Evaluated_Condition_Clause_Tree['Value']['End_Of_Results'])
						$Evaluation = true;
					else
						$Evaluation = false;
				}
				
				else
				{
					if ($Evaluated_Condition_Clause_Tree['Value'])
						$Evaluation = true;
					else
						$Evaluation = false;
				}
				
				// Restore context
				// TODO
// 				if (isset($Memory_Stack_Reference_Length) && $Memory_Stack_Reference_Length)
// 					array_splice($Memory_Stack_Reference, $Memory_Stack_Reference_Length);
				
				// Process content based on the evaluation
				// TODO - verify inner content block.
				$Result = &Process_Block_With_Evaluation($Database, $Inner_Content_Block, $Evaluation, $Memory_Stack_Reference);
				
				// Append result to result block
				$Processed_Command['Chunks'][] = &$Result;
				
				break;
				
			// For
			case 'for':
				
				$Variable = $Command['Clauses']['variable']['Tree']['Value'];
				
				// TODO restricts us to incrementing by constant values
				$Evaluated_From_Clause = &Evaluate_Clause_Tree($Database, $Command['Clauses']['from']['Tree'], $Memory_Stack_Reference);
				$Evaluated_To_Clause = &Evaluate_Clause_Tree($Database, $Command['Clauses']['to']['Tree'], $Memory_Stack_Reference);
				if (isset($Command['Clauses']['by']))
				{
					$Evaluated_By_Clause = &Evaluate_Clause_Tree($Database, $Command['Clauses']['by']['Tree'], $Memory_Stack_Reference);
					$By = $Evaluated_By_Clause['Value'];
				}
				else
					$By = 1;
				$From = &$Evaluated_From_Clause['Value'];
				$To = &$Evaluated_To_Clause['Value'];
				
				// If To is less than From, increment negatively
				if ($To < $From && $By > 0)
					$By = -$By;
				
				// Iterate over range
				for ($Iterator = $From; (($By > 0) && ($Iterator <= $To)) || (($By < 0) && ($Iterator >= $To)); $Iterator += $By)
				{
					// Set current iterator value
					$Set_Command = 'Set ' . $Variable . ' to ' . $Iterator;

					// TODO - this area does not look completed.
					Process_Command_String($Database, $Set_Command, $Memory_Stack_Reference);
					
					// Process the content
					$For_Result = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
					
					// Append result to result block
					$Processed_Command['Chunks'][] = &$For_Result;
				}
				
				break;
			
			
			// While
			case 'while':
				
				// Loop while evaluation is true
				while ($Evaluated_From_Clause = &Evaluate_Clause_Tree($Database, $Command['Clauses']['condition']['Tree'], $Memory_Stack_Reference) && $Evaluated_From_Clause['Value'])
				{
					// Process content
					$While_Result = &Process_Block_With_Evaluation($Database, $Inner_Content_Block, true, $Memory_Stack_Reference);
					
					// Append result to result block
					$Processed_Command['Chunks'][] = &$While_Result;
				}
				
				break;
			
			
			
			// Conditionals...
			
			// Agent...
			// Bot
			case 'bot':
			// Browser
			case 'browser':
				switch (strtolower($Command_Name))
				{
					case 'bot':
						// Determine if robot
						$Evaluation = Is_Robot();
						break;
					case 'browser':
						// Determine if a browser (not a robot)
						$Evaluation = !Is_Robot();
						break;
				}
				
				// Process content depending on evaluation
				$Result = &Process_Block_With_Evaluation($Database, $Inner_Content_Block, $Evaluation, $Memory_Stack_Reference);
				
				// Append result to result block
				$Processed_Command['Chunks'][] = &$Result;
				
				break;
			
			
			// Users...
			// Guest, Member, Authenticate, Admin, Manager
			case 'guest':
			case 'member':
			case 'authenticate':
			case 'admin':
			case 'manager':	
				// Get namespace of previous memory stack reference
				$Current_Namespace = &$Memory_Stack_Reference['Namespace'];
				
				// Generate container namespace
				$Authenticate_Namespace = &$_;$_ = $Current_Namespace . NAMESPACE_DELIMITER . 'Authenticate'; 
				unset($_);
				
				// Add metadata to processed item 
				$Processed_Container = &New_Chunk('Processed_Authenticate_Chunk');
				$$Processed_Container['Chunks'] = &$_;$_ = [];
				unset($_);
				$Processed_Container['Metadata'] = array(
					'Parent_Namespace' => &$Current_Namespace,
					'Namespace' => &$Authenticate_Namespace
				);
				
				// Get parameters
				$Authenticate_Parameters = array();
				if (array_key_exists('ignore_preview_mode', $Command['Clauses']))
					$Authenticate_Parameters['ignore_preview_mode'] = &New_Boolean(true);
				
				$Evaluation = &$_;$_ = false;
				unset($_);				
								
				switch (strtolower($Command_Name))
				{
					case 'guest':
					case 'member':
						// Get user this. 
						if (isset($GLOBALS['Current_Session_Item']))
						{
							$User_Command_String = &$_;$_ = 'User'; 
							unset($_);							
							$User_Command = &Parse_String_Into_Command($User_Command_String);
							$Current_Session_User_Item = &Get_Value($GLOBALS['Current_Session_Item'], $User_Command, $Memory_Stack_Reference);
						}
						else
							$Current_Session_User_Item = &New_Not_Set();
							
						// Determine evaluation for guest
						if ((strtolower($Command_Name) == 'guest') && (Is_Not_Set($Current_Session_User_Item) || $Current_Session_User_Item['End_Of_Results'] === true))
						{
							$Evaluation = &$_;$_ = true;
							unset($_);
						}

						// Determine evaluation for member
						if ((strtolower($Command_Name) == 'member') && (Is_Item($Current_Session_User_Item) && $Current_Session_User_Item['End_Of_Results'] === false))
						{
							$Evaluation = &$_;$_ = true;
							unset($_);
						}
						break;					
					case 'admin':
					case 'manager':						
						if (Authenticate($Command_Name, $Authenticate_Parameters))
						{
							$Evaluation = &$_;$_ = true;
							unset($_);							
						}
						break;
					case 'authenticate':
						// TODO
						$Evaluated_Team_Clause = &Evaluate_Clause_Tree($Database, $Command['Clauses']['team']['Tree'], $Memory_Stack_Reference);
						if (Authenticate($Evaluated_Team_Clause['Value'], $Authenticate_Parameters))
						{
							$Evaluation = &$_;$_ = true;
							unset($_);							
						}
						break;
				}
				
				// Process content depending on evaluation
				$Result = &Process_Block_With_Evaluation($Database, $Inner_Content_Block, $Evaluation, $Memory_Stack_Reference);
				
				// Append result to result block
				$Processed_Command['Chunks'][] = &$Result;
				
				break;
			
			// Container
			case 'container':
			{
				// Get namespace of previous memory stack reference
				$Current_Namespace = &$Memory_Stack_Reference['Namespace'];
				
				// Generate container namespace
				$Container_Namespace = &$_;$_ = $Current_Namespace . NAMESPACE_DELIMITER . 'Container';
				unset($_);
				
				// Add metadata to processed item 
				$Processed_Container = &New_Chunk('Processed_Container_Chunk');
				$Processed_Container['Chunks'] = &$_;$_ = [];
				unset($_);
				$Processed_Container['Original_Command'] = &$Command;
				$Processed_Container['Metadata'] = array(
						'Parent_Namespace' => &$Current_Namespace,
						'Namespace' => &$Container_Namespace
					);
				
				// Render path item
				// TODO - turn off wrapping for bots?
				// TODO: this should probably render Site.Default_Item or something since Path_Item is not set to it (not sure if still valid)
				// TODO - better way to pass in new namespace?
				$Container_Command_String = &$_;$_ = 'Globals.Path_Item Parent_Namespace ' . ('"' . $Container_Namespace . '"') . ' as [Path_Template_Alias no_wrap /] From_Container Preserve_Variables';
				unset($_);
				$Processed_Container_Command = &Process_Command_String($Database, $Container_Command_String, $Memory_Stack_Reference);
				$Processed_Container['Chunks'][] = $Processed_Container_Command;
				
				$Processed_Command['Chunks'][] = &$Processed_Container;
				break;
			}
				
			// Logout
			case 'logout':
			
				// Check if logged in
				// TODO clean up Current_User
				if ($GLOBALS['Current_User'])
				{
					// Logout
					Logout();
					
					// TODO...
					throw new exception('Logout not done.');
					/*
					// If admin, alert browser
					if (Authenticate('Admin'))
						Add_External_Script("Jelly.References.Item_Changed(\"Admin\");");
					
					// Alert browser user changed
					Add_External_Script("Jelly.References.Item_Changed(\"Current_User\");");
					*/
				}
				
				break;
			
			case 'show':
				// Get form from memory
				$Form_Command_String = &$_;$_ = 'Form from Memory as Reference';
				unset($_);
				$Processed_Form_Command = &Process_Command_String($Database, $Form_Command_String, $Memory_Stack_Reference);
				$Form_Item = &$Processed_Form_Command['Chunks'][0]['Item'];
				
				// Get calling namespace
				// TODO - forgot how to correctly check this.
				if (!$Form_Item['Data']['Calling_Namespace'])
					throw new Exception("Error: No calling namespace found for form");	
				$Calling_Namespace = &$Form_Item['Data']['Calling_Namespace'];

				// Set error flag for errors
				if (isset($Command['Clauses']['error']))
					Set_Simple($Form_Item, 'Error', True);
					
				// Get content
				// TODO - deal with null case
				// TODO - get it from somewhere else as needed
				$Processed_Inner_Content_Block = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
				Render_Processed_Block($Processed_Inner_Content_Block, array('No_Wrap' => true));
				$Rendered_Processed_Inner_Content_Block_Content = &$Processed_Inner_Content_Block['Content'];
				$Result_Content = &Jelly_Format($Rendered_Processed_Inner_Content_Block_Content, 'Javascript String');
				
				// Get input alias
				if (isset($Command['Clauses']['for']))
				{
					// Get result from expression
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['for']['Tree'], $Memory_Stack_Reference);
					$Result_Input_Alias = &$Evaluated_Expression['Value'];
				}
				
				// Generate script
				$Result_Script = 'Jelly.Actions.Show_Result(' . 	
						'{' . 
							'Namespace' . ':' . '"'. $Calling_Namespace . '"' . ',' . ' ' . 
							'Content' . ':' . '"'. $Result_Content . '"' . ',' . ' ';
							if ($Result_Input_Alias)
								$Result_Script .= 	
									'Input_Alias' . ':' . '"'. $Result_Input_Alias . '"';
							$Result_Script .= 
						'}' .
					');';

				// Return script. 
				$Processed_Command['Script'] =  &$Result_Script;
				break;
			
			// Navigation
			
			// Link, Go
			case 'link':
			case 'go':
			{
				// TODO - allow getting just the onclick value (i.e. something like [Link To Edit On_Click_Only /]) so that it can be used in other javascript handlers
				
				// Localize prefix variables
				$Directory = &$GLOBALS['Configuration']['URL_Prefix'];
								
				// Setup Link
				$Link = array(
						'On_Clicks' => array(),
						'Class_Names' => array(),
					);
					
				// Add command variables to URL variables for link.
				$Link['URL_Variables'] = &$_;$_ = [];
				unset($_);
				foreach ($Command_Variables_Item['Data'] as $Data_Key => $Data_Value)
				{
					// Skip the variables captured by the link renderer
					switch (strtolower($Data_Key))
					{
						case 'class':
						case 'style':
						case 'on_click':
							break;
						default:
							// Store all other variables in URL variables to pass on to the linked item.
							$Link['URL_Variables'][$Data_Key] = &$Data_Value;
					}
				}
				
				// Determine Link Type
				// TODO: Should we handle the "To" clause instead of adding clauses for each possible To value below?
				if (isset($Command['Clauses']['execute']))
					$Link_Type = 'Execute';
				elseif (isset($Command['Clauses']['submit']))
					$Link_Type = 'Execute';
				elseif (isset($Command['Clauses']['cancel']))
					$Link_Type = 'Cancel';
				elseif (isset($Command['Clauses']['add']))
					$Link_Type = 'Add';
				elseif (isset($Command['Clauses']['edit']))
					$Link_Type = 'Edit';
				elseif (isset($Command['Clauses']['edit_inline']))
					$Link_Type = 'Edit_Inline';
				elseif (isset($Command['Clauses']['inspect']))
					$Link_Type = 'Inspect';
				elseif (isset($Command['Clauses']['move']))
					$Link_Type = 'Move';
				elseif (isset($Command['Clauses']['remove']))
					$Link_Type = 'Remove';
				elseif (isset($Command['Clauses']['detach']))
					$Link_Type = 'Detach';
				elseif (isset($Command['Clauses']['logout']))
					$Link_Type = 'Logout';
				elseif (isset($Command['Clauses']['next']))
					$Link_Type = 'Next';
				elseif (isset($Command['Clauses']['previous']))
					$Link_Type = 'Previous';
				elseif (isset($Command['Clauses']['sort']))
					$Link_Type = 'Sort';
				else
					$Link_Type = 'Other';
				
				// Handlers
				if (in_array($Link_Type, array('Execute', 'Cancel')))
				{
					switch ($Link_Type)
					{
						case "Execute":
							// Generate link title from action name (note: processed result does not get added to main result i.e. never printed to browser)
							$Link_Title_Command = &$_;$_ = 'Current_Action.Name';
							unset($_);
							$Link_Title_Processed_Command = &Process_Command_String($Database, $Link_Title_Command, $Memory_Stack_Reference);
							
							Render_Processed_Block($Link_Title_Processed_Command, array('No_Wrap' => true));
							
							$Link['Title'] = $Link_Title_Processed_Command['Content'];
							
							$Link['Class_Names'][]  = 'Jelly_Action_Execute';
							
							$Link['On_Clicks'][] = "Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': this});";
							
							break;
							
						case "Cancel":
							// TODO: why both Cancel and Dismiss?
							// TODO: dismiss is for also dismissing windows/menus, which the action may not have been intended for and thus not include their own bubbling of that sort. trying to define some standardized behavior.
							$Link['On_Clicks'][] = "Jelly.Handlers.Call_Handler_For_Target({'Event': 'Cancel', 'Target': this});";
							
							break;
					}
					
					// Prevent meta-clicking action link.
					// TODO: implement
					$Link['Prevent_Meta_Click'] = false;
				}

				// Object Actions
				elseif (in_array($Link_Type, array('Edit', 'Edit_Inline', 'Inspect', 'Remove', 'Move')))
				{
					switch (strtolower($Link_Type))
					{
						case 'edit':
							$Link_Default_Container = 'Window';
							break;
						case 'edit_inline':
							$Link_Default_Container = 'Window';
							break;
						case 'inspect':
							$Link_Default_Container = 'Inspector';
							break;					
						case 'remove':
							$Link_Default_Container = 'Window';
							break;
						case 'move':
							// TODO does not seem to do anything. Figure out how to force it to execute? Add On_Clicks?
							break;
					}
					
					// Get result from expression
					$Link_Target_Tree = &$Command['Clauses'][strtolower($Link_Type)]['Tree'];
					$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Link_Target_Tree, $Memory_Stack_Reference);
					$Link_Target_Value = &$Evaluated_Expression['Value'];
					
					if (Is_Not_Set($Link_Target_Value))
						throw new Exception("Error: Could not find item to " . strtolower($Link_Type));
					
					// If value is simple, get its parent item
					if (!is_array($Link_Target_Value))
					{
						// Skip the bottom term in the target clause tree
						$Progressive_Target_Tree = $Link_Target_Tree['Terms'][1];
						
						// Build a new command to get the target
						$Target_Target_Command = &New_Command();
					
						// Set new commands name to the progressive tree
						$Target_Target_Command['Clauses']['name'] = &New_Clause();
						$Target_Target_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
						unset($_);
						$Target_Target_Command['Clauses']['name']['Tree'] = &$Progressive_Target_Tree;
					
						// Target "As Reference" to new command
						$Target_Target_Command['Clauses']['as'] = &New_Clause();
						$Target_Target_Command['Clauses']['as']['Name'] = &$_;$_ = 'As'; 
						unset($_);
						$Target_Target_Command['Clauses']['as']['Tree'] = &New_Tree();
						$Target_Target_Command['Clauses']['as']['Tree']['Kind'] = &$_;$_ = 'Variable'; 
						unset($_);
						$Target_Target_Command['Clauses']['as']['Tree']['Value'] = &$_;$_ = 'Reference'; 
						unset($_);
					
						// Process the command
						$Processed_Target_Target_Command = &Process_Command($Database, $Target_Target_Command, $Memory_Stack_Reference);
						
						// Fetch the target
						$Target_Target_Item = &$Processed_Target_Target_Command['Chunks'][0]['Item'];
				
						// Get property from the right-most dot operator
						$Target_Property_Lookup = &$Link_Target_Tree['Terms'][0]['Value'];
						
						// Make sure property exists
						if (!Has_Property($Target_Target_Item, $Target_Property_Lookup))
							throw new Exception("Error: Target does not have property to edit: " . $Target_Property_Lookup);
						
						// Get Property
						$Cached_Target_Property = &Get_Property($Target_Target_Item, $Target_Property_Lookup);
						$Cached_Target_Property_Alias = &$Cached_Target_Property['Alias'];
						$Cached_Target_Property_Name = &$Cached_Target_Property['Name'];
						
						// Get Target Type
						$Cached_Target_Type = &$Cached_Target_Property['Cached_Value_Type'];
						
						// Add indicator to add new item to parent item.
						
						// Set link target item to parent
						$Link_Target_Item = &$Target_Target_Item;
					}
					else
					{
						// Set link target item to item
						$Link_Target_Item = &$Link_Target_Value;
					}

					// Check if it's an open item
					if ($Link_Target_Item['End_Of_Results'] !== false)
						throw new Exception("Error: Could not find item to " . strtolower($Link_Type)) ;
					
					// Check if we are targeting an attachment
					if (isset($Command['Clauses']['as']))
					{
						if (strtolower($Command['Clauses']['as']['Tree']['Value']) == 'attachment')
						{
							// TODO: test
							$Link_Target_Item_Type = &Get_Cached_Type($Database, $Link_Target_Item['Data']['Attachment_Type']);
							$Link_Target_Item_ID = &$Link_Target_Item['Data']['Attachment_ID'];
							
							// Get target type information
							$Link_Target_Item_Type_Name = &$Link_Target_Item_Type['Name'];
							$Link_Target_Item_Type_Alias = &$Link_Target_Item_Type['Alias'];
							
							$Link['Title'] = $Link_Type . ' ' .  'Attachment';
						}
						else
							throw new Exception("Error: Unknown 'as' in $Link_Type.");
					}
					
					// Otherwise, get target type and id
					else
					{
						$Link_Target_Item_Type = &$Link_Target_Item['Cached_Base_Type'];
						$Link_Target_Item_ID = &$Link_Target_Item['Data']['ID'];
					
						// Get target type information
						$Link_Target_Item_Type_Name = &$Link_Target_Item_Type['Name'];
						$Link_Target_Item_Type_Alias = &$Link_Target_Item_Type['Alias'];
						
						// Generate the link title from its type name
						if (isset($Cached_Target_Property_Name))
						{
							$Link['Title'] = $Link_Type . ' ' .  $Cached_Target_Property_Name;
						}
						else
						{
							$Link['Title'] = $Link_Type . ' ' .  $Link_Target_Item_Type_Name;
						}
					}
					
					// Generate link URL
					$Link['URL'] = &$_;$_ = 
							strtolower($Directory) . '/' . 
							strtolower($Link_Target_Item_Type_Alias) . '/' . 
							strtolower($Link_Target_Item_ID) . '/' . 
							strtolower($Link_Type);
					unset($_);
					if (isset($Cached_Target_Property_Alias))
					{
						$Link['URL_Variables']['Property_Alias'] = &$Cached_Target_Property_Alias;
					}
				}
				
				// Sorting
				elseif (in_array(strtolower($Link_Type), array('next', 'previous', 'sort')))
				{
					throw new Exception('Link to next, previous, sort: Not done yet');
					
					// TODO: _Namespace...  Seemed an ok way to deal with this, but could use more thought.
					$Namespace_Lookup = $Command['Clauses'][strtolower($Link_Type)]['Items'][0]['Value'] . '_Namespace';
					$Link_Target_Namespace_Result = &Process_Command_String($Database, $Namespace_Lookup, $Memory_Stack_Reference);
					$Link_Target_Namespace = $Link_Target_Namespace_Result['Content'];
					// TODO : I couldn't get escaped full quotes to work! Shut up!  I'm not listening!!!
					if (strtolower($Link_Type) == 'sort')
					{
						$Link_Target_Sort = $Command['Clauses']['by']['Items'][0]['Value'];
						$Link['On_Clicks'][] = "Jelly.References.Set_Iterator_Parameters('$Link_Target_Namespace', {'Sort' : '$Link_Target_Sort'});";
					}
					else
						$Link['On_Clicks'][] = "Jelly.References.Set_Iterator_Parameters('$Link_Target_Namespace', {'Page' : '$Link_Type'});";
				}
				
				// Direct links
				elseif ($Link_Type == 'Other')
				{
					// If a link to an specific url is set...
					if (isset($Command['Clauses']['to']['Tree']['Kind']))
					{
						$Link_Target_Kind = &$Command['Clauses']['to']['Tree']['Kind'];
						
						switch(strtolower($Link_Target_Kind))
						{	
							case 'text':
								$Link['URL'] = &$Command['Clauses']['to']['Tree']['Value'];
								$Link['Title'] = &$_;$_ = $Link['URL'];
								unset($_);
								break;
							case 'variable':
							case 'operator':
								// Get result from expression
								$Evaluated_Expression = &Evaluate_Clause_Tree($Database, $Command['Clauses']['to']['Tree'], $Memory_Stack_Reference);
				
								// Get expression value
								$Link_Target_Item = &$Evaluated_Expression['Value'];

								// Check if it's an open item
								if ($Link_Target_Item['End_Of_Results'] !== false)
									throw new Exception("Error: Could not find item to link to.");
									
								// Get target name and type alias
								$Link_Target_Cached_Base_Type = &$Link_Target_Item['Cached_Base_Type'];
								$Link_Target_Cached_Specific_Type = &$Link_Target_Item['Cached_Specific_Type'];
								$Link_Target_Type_Alias = &$Link_Target_Cached_Base_Type['Alias'];
								$Link_Target_Name = &$Link_Target_Item['Data']['Name'];
								$Link_Target_ID = &$Link_Target_Item['Data']['ID'];
					
								// Check how to link to the item
								if (isset($Command['Clauses']['by']['Tree']['Value']))
									$By_Value = &$Command['Clauses']['by']['Tree']['Value'];
								else
								{
									$By_Value = &$_;$_ = 'Alias'; 
									unset($_);
								}
								switch (strtolower($By_Value))
								{
									case 'alias':
										$Link_Target_Key = &$Link_Target_Item['Data']['Alias'];
										break;
									case 'id':
										$Link_Target_Key = &$Link_Target_Item['Data']['ID'];
										break;
									default:
										throw new Exception("Error: Unknown link by value.");
										break;
								}
								if (!$Link_Target_Key)
									$Link_Target_Key = &$Link_Target_Item['Data']['ID'];
								
								// Check 'as'
								// TODO: this maybe should be passed to javascript for bettter handling as an object instead of just a URL
								if (isset($Command['Clauses']['as']['Tree']['Value']))
								{
									$Link_As = &$Command['Clauses']['as']['Tree']['Value'];
									
									// Check if template is set for the item
									$Link_Template_Lookup = &$Link_As;
									if (!isset($Link_Target_Cached_Base_Type['Cached_Template_Lookup'][strtolower($Link_As)]))
										throw new Exception("No template");
									
									// Get the template
									$Cached_Type_Template = &$Link_Target_Cached_Base_Type['Cached_Template_Lookup'][strtolower($Link_As)];
									$Cached_Type_Template_Alias = &$Cached_Type_Template['Alias'];
									$Link['Template'] = &$Cached_Type_Template_Alias;
									
									// Specify that the link is direct (i.e. not embeddable in a container)
									if (strtolower($Cached_Type_Template['Content_Type']) != 'text/html')
									{
										$Link['Direct'] = &$_;$_ = true;
										unset($_);
									}
								}
								
								if ($Link_Target_Name)
									$Link['Title'] = $Link_Target_Name;
								else
								{
									$Link['Title'] = &$_;$_ = $Link_Target_Type_Alias . ' ' .$Link_Target_ID;
									unset($_);
								}

								// Build URL...

								// Build base URL
								$Link['URL'] = &$_;$_ = 
										strtolower($Directory)  . '/' . 
										strtolower($Link_Target_Type_Alias) . '/' . 
										strtolower($Link_Target_Key);
								unset($_);

								// Append template if specified to URL
								if (isset($Cached_Type_Template_Alias))
								{	
									$Next_Link_URL = &$_;$_ = $Link['URL'] . '/' . strtolower($Cached_Type_Template_Alias);
									$Link['URL'] = &$Next_Link_URL;
									unset($Next_Link_URL);unset($_);
								}

								break;
							default:
								throw new Exception("Error: Unknown kind of link target.");
								break;
						}						
					}
					
					else
					{
						// WHAT THE 
						throw new Exception('Error: link target does not have a kind');
					}
				}
				
				elseif ($Link_Type == 'Add')
				{
					$Add_Withs = array();
					
					// Get add tree
					$Add_Tree = $Command['Clauses']['add']['Tree'];
					
					if (strtolower($Add_Tree['Kind']) == 'operator')
					{
						// Skip the bottom term in the target clause tree
						$Progressive_Add_Tree = $Add_Tree['Terms'][1];
						
						// Build a new command to get the target
						$Add_Target_Command = &New_Command();
					
						// Set new commands name to the progressive tree
						$Add_Target_Command['Clauses']['name'] = &New_Clause();
						$Add_Target_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
						unset($_);
						$Add_Target_Command['Clauses']['name']['Tree'] = &$Progressive_Add_Tree;
					
						// Add "As Reference" to new command
						$Add_Target_Command['Clauses']['as'] = &New_Clause();
						$Add_Target_Command['Clauses']['as']['Name'] = &$_;$_ = 'As'; 
						unset($_);
						$Add_Target_Command['Clauses']['as']['Tree'] = &New_Tree();
						$Add_Target_Command['Clauses']['as']['Tree']['Kind'] = &$_;$_ = 'Variable'; 
						unset($_);
						$Add_Target_Command['Clauses']['as']['Tree']['Value'] = &$_;$_ = 'Reference'; 
						unset($_);	
					
						// Process the command
						$Processed_Add_Target_Command = &Process_Command($Database, $Add_Target_Command, $Memory_Stack_Reference);
					
						// Fetch the target
						$Add_Target_Item = &$Processed_Add_Target_Command['Chunks'][0]['Item'];
				
						// Get property from the right-most dot operator
						$Add_Property_Lookup = &$Add_Tree['Terms'][0]['Value'];
						
						// Make sure property exists
						// TODO - metadata, date?
						if (!Has_Property($Add_Target_Item, $Add_Property_Lookup))
							throw new Exception("Error: Target does not have multiple property to Add to.");
						
						// Build tree string excluding right node
						$Tree_Values_Excluding_Right = array();
						$Current_Tree_Node = &$Add_Tree['Terms'][1];
						
						while ($Current_Tree_Node['Kind'] == 'Operator') // TODO make sure dot
						{
							$Tree_Values_Excluding_Right[] = &$Current_Tree_Node['Terms'][0]['Value'];
							$Current_Tree_Node = &$Current_Tree_Node['Terms'][1];
						}
						$Tree_Values_Excluding_Right[] = &$Current_Tree_Node['Value'];
						$Add_Tree_String_Excluding_Right = implode('.', $Tree_Values_Excluding_Right);
						
						$Add_Target_Lookup = &$Add_Tree_String_Excluding_Right;
						
						// Get Property
						$Cached_Add_Property = &Get_Property($Add_Target_Item, $Add_Property_Lookup);
						$Cached_Add_Property_Alias = &$Cached_Add_Property['Alias'];
						$Cached_Add_Property_Name = &$Cached_Add_Property['Name'];
						
						// Get Add Type from Value_Type clause or from Property
						if (isset($Command['Clauses']['value_type']['Tree']))
						{
							// If there is a Value_Type clause, use this cached type
							// TODO is this named correctly?
							$Evaluated_Value_Type = &Evaluate_Clause_Tree($Database, $Command['Clauses']['value_type']['Tree'], $Memory_Stack_Reference);
							$Cached_Add_Type = &Get_Cached_Type($Database, $Evaluated_Value_Type['Value']['Data']['Alias']);
						}
						else
						{
							// If there is no Value_Type clause, use the property's value type
							$Cached_Add_Type = &$Cached_Add_Property['Cached_Value_Type'];
						}
						$Cached_Add_Type_Alias = &$Cached_Add_Type['Alias'];
						$Cached_Add_Type_Name = &$Cached_Add_Type['Name'];
						
						// Set property label to add value type (previously the property name)
// 						$Cached_Add_Property_Label = &$_;$_ = 'Add' . ' ' . $Cached_Add_Property_Name . '...';
// 						unset($_);
						$Cached_Add_Property_Label = &$_;$_ = 'Add' . ' ' . $Cached_Add_Type_Name;
						unset($_);
						
						
						// Add indicator to add new item to parent item.
						$Add_Withs[] = 'Parent = ' . $Add_Target_Lookup;
						$Add_Withs[] = 'Parent_Property_Alias = ' . '"' . $Cached_Add_Property_Alias . '"';
						$Add_Withs[] = 'Label = ' . '"' . $Cached_Add_Property_Label . '"';
					}
					else
					{
						// Get property from tree base
						$Add_Tree_Value = &$Add_Tree['Value'];
					
						// Get add type
						$Add_Type_Lookup = &$Add_Tree_Value;
						$Cached_Add_Type = &Get_Cached_Type($Database, $Add_Type_Lookup);
						
						// Get parent property alias
						if (array_key_exists('with_parent_property_alias', $Command['Clauses']))
						{
							$Add_Withs[] = &$_;$_ = 'Parent_Property_Alias' . ' ' . '='. ' ' . ('"'. $Command['Clauses']['with_parent_property_alias']['Tree']['Value'] . '"');
							unset($_);
						}	
					}
					
					// Add inspector variable 
					if (array_key_exists('create_inspector', $Command['Clauses']))
					{
						$Add_Withs[] = &$_;$_ = 'Create_Inspector' . ' ' . '='.  ' ' . 'True';
						unset($_);
					}	
					
					//  Get class from command variables
					if (Has_Property($Command_Variables_Item, 'Class'))
					{
						$Command_Variables_Class_Name_Value_Item = &Get_Value_With_Command_String($Command_Variables_Item, 'Class');
						$Command_Variables_Class_Name_Value = &$Command_Variables_Class_Name_Value_Item['Data']['Simple_Value'];
						$Add_Withs[] = &$_;$_ = 'Class_Names' . ' ' . '='.  ' ' . '"' . $Command_Variables_Class_Name_Value . '"';
						unset($_);
					}

					//  Get style from command	variables.	
					if (Has_Property($Command_Variables_Item, 'Style'))
					{
						$Command_Variables_Style_Value_Item = &Get_Value_With_Command_String($Command_Variables_Item, 'Style');
						$Command_Variables_Style_Value = &$Command_Variables_Style_Value_Item['Data']['Simple_Value'];
						$Add_Withs[] = &$_;$_ = 'Style' . ' ' . '='.  ' ' . '"' . $Command_Variables_Style_Value . '"';
						unset($_);
					}
					
					// Get inner content
					// TODO - overrides some work above.
					if ($Inner_Content_Block)
					{
						$Processed_Content = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
						Render_Processed_Block($Processed_Content);
						$Add_Withs[] = &$_;$_ = 'Label' . ' ' . '='.  ' ' . '"' . Jelly_Format($Processed_Content["Content"], 'Jelly Attribute') . '"';
						$Link_Script = &$Processed_Content["Script"];
						unset($_);
					}
					else
					{
						$Link_Script = &$_; $_ = "";
						unset($Link_Script);
					}
						
					
					// Create command
					$Link_Jelly = "New " . $Cached_Add_Type["Alias"] . " as Add_Link";
					if (count($Add_Withs))
						$Link_Jelly .= " with " . implode(", ", $Add_Withs);
						
// 					echo "Link_Jelly: " . $Link_Jelly . "\n<br/>";
						
					/*
					// Label
					if ($Inner_Content_Block)
					{
						$New_Link_With = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
						Render_Processed_Block($New_Link_With);
						$New_Link["Withs"][] = "Label \"" . Jelly_Format($New_Link_With["Content"], "Jelly Attribute") . "\"";
					}

					
					// On Click
 					if (isset($Command['Clauses']["on_click"]))
 						$New_Link["Withs"][] = "On_Click \"" . Jelly_Format($Command['Clauses']["on_click"]["Items"][0]["Value"], "Jelly Attribute") . "\"";
					*/
					
					// Return a link with  appropriate event handling.
					$Result = &Process_Command_String($Database, $Link_Jelly, $Memory_Stack_Reference);
					
					// Append result to result block
					$Processed_Command["Chunks"][] = &$Result;
					break;
				}
				
				// TODO: Add, Detach.
				elseif (in_array(strtolower($Link_Type), array('add', 'detach')))
					throw new Exception("Error: add and detach are not done yet.");
				/*
				elseif ($Link_Type == "Detach")
				{
					// Removing a value
					
					// TODO: support other property relations
					
					// Removing property from item (i.e. Person.Friend)
					$Remove_Item = &Evaluate_Clause($Command['Clauses']["remove"], $Memory_Stack_Reference, array("Exclude_Last" => True));
					if (!$Remove_Item["Ready"])
						throw new Exception("Error: Could not find item to remove ($Link_Remove_Text).");
					$Remove_Item_Type_Item = &$Remove_Item["Type"];
					$Remove_Item_Type_Name = &$Remove_Item_Type_Item["Name"];
					
					if (!Has_Property($Remove_Item, $Link_Remove_Parts[count($Link_Remove_Parts) - 1]))
						throw new Exception("Error: Property not found to remove ($Link_Remove_Text).");
					$Property_Descriptor = &Get_Property_Descriptor($Remove_Item, $Link_Remove_Parts[count($Link_Remove_Parts) - 1]);
					$Property = &$Property_Descriptor["Property"];
					$Property_Actual_Name = &$Property[$Property_Descriptor["Reverse_Prefix"] . "Name"];
					$Property_Actual_Alias = &$Property[$Property_Descriptor["Reverse_Prefix"] . "Alias"];
					$Property_Actual_Value_Type_Alias = &$Property[$Property_Descriptor["Reverse_Prefix"] . "Value_Type"];
					$Property_Actual_Value_Type = &Get_Type($Property_Actual_Value_Type_Alias);
					$Property_Actual_Value_Type_Name = &$Property_Actual_Value_Type["Name"];
					$Property_Attachment_Type_Alias = &$Property["Attachment_Type"];
					
					$Remove_Item_ID_Command = "$Property_Actual_Value_Type_Alias:Attachment_ID";
					$Remove_Item_ID_Result = &Process_Command_String($Database, $Remove_Item_ID_Command, $Memory_Stack_Reference);
					$Remove_Item_ID = $Remove_Item_ID_Result["Content"];
					// TODO better way to get the attachment ID?
					if (!$Remove_Item_ID)
					{
						$Remove_Item_ID_Command = "$Property_Attachment_Type_Alias:ID";
						$Remove_Item_ID_Result = &Process_Command_String($Database, $Remove_Item_ID_Command, $Memory_Stack_Reference);
						$Remove_Item_ID = $Remove_Item_ID_Result["Content"];
					}
					
					$Link_On_Click = "Jelly.Interface.Create_Window({'Alias': '" . "Remove_" . $Property_Attachment_Type_Alias . NAMESPACE_DELIMITER . $Remove_Item_ID . "', 'URL': '" . $GLOBALS["Directory"] . "/Action/Remove/Raw:Remove_Item_Type=$Property_Attachment_Type_Alias,Remove_Item_ID=" . $Remove_Item_ID . "'});";
					
					$Link_Title = "Remove $Property_Actual_Value_Type_Name from $Remove_Item_Type_Name";
				}
				*/
				
				// Logout Links
				elseif ($Link_Type == "Logout")
				{
					$Link["URL"] = &$_;$_ = $Directory . '/' . 'Logout';
					unset($_);					
					
					// Do not allow link to be translated into a hash-link
					$Link["Direct"] = true;
					
					// Add Logout Action
					$Link["On_Clicks"][] = "Jelly.Actions.Execute({'Action': 'Logout'});";
				}
				
				
				// Incorporate Extra User Parameters
				{
					// Prepend user's On_Click
					
					//  Get on_click from command variables
					// TODO - verify/auto-add semi-colon
					if (Has_Property($Command_Variables_Item, 'On_Click'))
					{
						$Command_Variables_On_Click_Value_Item = &Get_Value_With_Command_String($Command_Variables_Item, 'On_Click');
						$Command_Variables_On_Click_Value = &$Command_Variables_On_Click_Value_Item['Data']['Simple_Value'];;
						$Link["On_Clicks"][] = &$Command_Variables_On_Click_Value;
					}
						
					if (isset($Command['Clauses']['direct']))
						$Link["Direct"] = true;
						
					// TODO: "With" clauses kill everything (you can't have other clauses afterwards)
					
					// Parse Into
					if (isset($Command['Clauses']['into']))
					{
						$Evaluated_Into_Clause = &Evaluate_Clause_Tree($Database, $Command['Clauses']['into']['Tree'], $Memory_Stack_Reference);
						switch (strtolower($Evaluated_Into_Clause['Value']))
						{
							case 'window':
								$Link['Container'] = 'Window';
								break;
							case 'parent':
								$Link['Container'] = 'Parent';
								break;
							default:
								$Link['Container'] = $Evaluated_Into_Clause['Value'];
								break;
						}
					}
					elseif (isset($Link_Default_Container))
						$Link['Container'] = $Link_Default_Container;

					// TODO - other attributes for the html element (onmouseover, onfocus, onmousemove, etc.)?
					/*
						maybe via any of the below - how to implement? 
						a) [Link to Edit Puppy with Mouse_Over = "alert();", Mouse_Move="hedj;"]
						b) [Link to Edit Puppy with Attributes Mouse_Over = "alert();", Mouse_Move="hedj;"]
						c) [Link to Edit Puppy with Attributes = "onmouseover=\"hej\""]
						
						Prefer a), but maybe we support a) for predicted ones and c) for the rest?
					*/
				}
				
				// TODO: think a little bit more about when links can be command-clicked (i.e. [Link /]s that just contain On_Click, etc.
				
				// Process stuff from above
				{
					if (isset($Link["URL"]))
					{	
						// Copy link URL to Href URL
						$Link["HRef_URL"] = &$_;$_ = $Link["URL"];
						unset($_);						
						
						// Add URL variables to Href URL...	
						if (isset($Link['URL_Variables']) && count($Link['URL_Variables']))
						{
							// Add URL Variables
							$Link_URL_Variable_Strings = array();
							foreach ($Link['URL_Variables'] as $Link_URL_Variable_Name => &$Link_URL_Variable_Value)
							{
								$Link_URL_Variable_Strings[] = &$_;$_ = $Link_URL_Variable_Name . '=' . $Link_URL_Variable_Value;
								unset($_);
							}
							$Link_URL_Variables_String = implode(',', $Link_URL_Variable_Strings);
					
							$Next_HRef_URL = &$_;$_ = 
									$Link["HRef_URL"] . ':' . 
									$Link_URL_Variables_String;		
							$Link["HRef_URL"] = &$Next_HRef_URL;
							unset($_);unset($Next_HRef_URL);
						}
					}
					
					// Set a default url for javascript-based types of links
					else
						$Link["HRef_URL"] = New_String('#');
				}
// 					traverse($Link);
				
				// TODO - does this make sense? might be a leftover.
// 				$Link['On_Clicks'][] = "Jelly.Handlers.Call_Handler_For_Target({'Event': 'Done', 'Target': this});";
				
				
				// TODO: figure out if/how to implement Handle_Link_From_Click... 
				// TODO: then fix the below line.
				// Return false if we don't want to let the browser handle the URL itself
				$Link_Click_Parameters = array();
				
				// Add URL Variables to Link Click Parameters
				if (isset($Link['URL_Variables']) && count($Link['URL_Variables']))
					$Link_Click_Parameters['URL_Variables'] = &$Link['URL_Variables'];
				
				// Copy info to link click parameters
				if (isset($Link['Template']))
					$Link_Click_Parameters['Template'] = &$Link['Template'];
				
				$Link_Click_Parameters['Namespace'] = &$Memory_Stack_Reference['Namespace'];
				
				if (isset($Link['URL']))
				{
					// Add link URL to Link Click Parameters
					$Link_Click_Parameters['URL'] = &$Link['URL'];
					
					// Add link container to Link Click Parameters
					if (isset($Link['Container']))
						$Link_Click_Parameters['Container'] = &$Link['Container'];
					
					// Add link calling element to Link Click Parameters
					if (isset($Link['Calling_Element']))
						$Link_Click_Parameters['Calling_Element'] = &$Link['Calling_Element'];
					
					// Copy link direct to click parameters
					if (isset($Link['Direct']))
						$Link_Click_Parameters['Direct'] = &$Link['Direct'];
					
					// TODO: what other parameters should be copied over?
					// TODO: better way to convert quotes for whatever our target string wrapping will be?
					
					// Add Handle_Link_From_Click trigger to link
					// TODO - maybe reorganize...
					if (strtolower($Command_Name) == 'link')
						$Link['On_Clicks'][] = 'return !Jelly.Handlers.Handle_Link_From_Click(event, ' . str_replace('"', '&quot;', json_encode($Link_Click_Parameters)) . ');';
					else
					{
						$Link['On_Clicks'][] = 'Jelly.Handlers.Handle_Link_From_Click(null, ' . json_encode($Link_Click_Parameters) . ');';						
					}
				}
				else
				{
					// If a link doesn't point to a URL, simply return false at the end to prevent any browser behavior
					$Link['On_Clicks'][] = 'return false;';
				}
				
				// Generate links html and script.
				switch (strtolower($Command_Name))
				{
					case "link":
					{
						// Default title
						if (!array_key_exists('Title', $Link))
							$Link["Title"] = $Link_Type;
						
						// Default content
						if ($Inner_Content_Block)
						{
							// (note: processed result does not get added to main result i.e. never printed to browser)
							$Processed_Content = &Process_Block($Database, $Inner_Content_Block, $Memory_Stack_Reference);
							
							Render_Processed_Block($Processed_Content);
							
							$Link["Content"] = &$Processed_Content["Content"];
							$Link_Script = &$Processed_Content["Script"];
							unset($_);
						}
						else
						{
							$Link_Script = &$_; $_ = "";
							unset($Link_Script);
							
							if (!isset($Link["Content"]))
								$Link["Content"] = $Link["Title"];
						}
						
						//  Get class from command variables
						if (Has_Property($Command_Variables_Item, 'Class'))
						{
							$Command_Variables_Class_Name_Value_Item = &Get_Value_With_Command_String($Command_Variables_Item, 'Class');
							$Command_Variables_Class_Name_Value = &$Command_Variables_Class_Name_Value_Item['Data']['Simple_Value'];
							$Link["Class_Names"][] = &$Command_Variables_Class_Name_Value;
						}

						//  Get style from command	variables.	
						if (Has_Property($Command_Variables_Item, 'Style'))
						{
							$Command_Variables_Style_Value_Item = &Get_Value_With_Command_String($Command_Variables_Item, 'Style');
							$Command_Variables_Style_Value = &$Command_Variables_Style_Value_Item['Data']['Simple_Value'];
							$Link["Style"] = &$Command_Variables_Style_Value;
						}
						
						// Setup HTML anchor
						// TODO - this whole link area is still dirty
						$Anchor_Attributes = array();
						$Anchor_Attributes[] = "href=\"" . $Link["HRef_URL"] . "\"";
// 						if (isset($Link["Title"]))
// 							$Anchor_Attributes[] = "title=\"" . $Link["Title"] . "\"";
						if (!empty($Link["On_Clicks"]))
							$Anchor_Attributes[] = "onclick=\"" . implode("", $Link["On_Clicks"]) . "\"";
						if (!empty($Link['Class_Names']))
							$Anchor_Attributes[] = 'class' . '=' . '"' . implode(' ', $Link['Class_Names']) . '"';							
						if (isset($Link["Style"]))
							$Anchor_Attributes[] = "style=\"" . $Link["Style"] . "\"";
						// TODO - additional attributes, see above.
						
						$Link_Result = &$_;$_ = "<a " . implode(" ", $Anchor_Attributes) . ">" . $Link["Content"] . "</a>";
						unset($_);
						
						break;
					}
					
					// TODO: Go should have special Handle_Link handling that doesn't change if meta key is pressed
					case 'go':
					{
						if (!empty($Link['On_Clicks']))
						{	
							$New_Link_Script = &$_;$_ = $Link_Script . implode("", $Link["On_Clicks"]);
							unset($_);
							$Link_Script = &$New_Link_Script;
						}
						else
						{
							$New_Link_Script = &$_;$_ = $Link_Script . 'document'. '.' . 'location' . ' ' . '=' . ' ' . '"' . $Link['Href_URL'] . '"' . ';';
							unset($_);
							$Link_Script = &$New_Link_Script;
						}
					}
				}
				
				// Append result to processed command
				$Text_Chunk = &New_Chunk('Text_Chunk');
				$Text_Chunk['Content'] = &$Link_Result;
				$Processed_Command['Chunks'][] = &$Text_Chunk;
				$Processed_Command['Script'] =  &$Link_Script;
				break;
			}
		}
				
		// Generate item refreshing scripts
		foreach ($Command_Items_To_Refresh as &$Item_To_Refresh)
		{
			// Trigger item refresh
			$Item_To_Refresh_ID = &$Item_To_Refresh;
			$Processed_Command['Script'] .= 'Jelly.References.Trigger_Refresh({\'Kind\': \'Item\', \'Item_ID\': ' . $Item_To_Refresh_ID .  '});' . "\n";
		}
		
		// Generate iterator refreshing scripts
		foreach ($Command_Iterators_To_Refresh as &$Iterator_To_Refresh)
		{
			$Recursive_Iterator_Type_To_Refresh = &Get_Cached_Type($Database, $Iterator_To_Refresh);
			while (isset($Recursive_Iterator_Type_To_Refresh))
			{
				// Trigger iterator refresh
				$Recursive_Iterator_Type_To_Refresh_Alias = &$Recursive_Iterator_Type_To_Refresh['Alias'];
				$Processed_Command['Script'] .= 'Jelly.References.Trigger_Refresh({\'Kind\': \'Iterator\', \'Type_Alias\': \'' . $Recursive_Iterator_Type_To_Refresh_Alias .  '\'});' . "\n";
				
				// Move to parent type, or unset value
				if (isset($Recursive_Iterator_Type_To_Refresh['Cached_Parent_Type']))
					$Recursive_Iterator_Type_To_Refresh = &$Recursive_Iterator_Type_To_Refresh['Cached_Parent_Type'];
				else
					unset($Recursive_Iterator_Type_To_Refresh);
			}
		}
		
		// Generate special refreshes
		if (count($Command_Special_Refreshes))
		{
			if (array_key_exists('current_session', $Command_Special_Refreshes))
				$Processed_Command['Script'] .= 'Jelly.References.Trigger_Refresh({\'Kind\': \'Current_Session\'});' . "\n";
		}
	}
	
	// Otherwise, it's an item lookup instead of a language command
	else
	{
		// Items...
		
		$Iterator_Start_Time = microtime(true);

		// Get the name tree
		$Name_Tree = &$Command['Clauses']['name']['Tree'];
		
		// Check if creating a new item ('new' clause is set)
		// TODO use clauses for this?
		if (isset($Command['New']))
		{
			// Get new type
			// TODO: treat name clause as expression?
			$Type_Lookup = &$Name_Tree['Value'];
			$Cached_Type = &Get_Cached_Type($Database, $Type_Lookup);
			
			// Make new item...
			$Item = &Create_Memory_Item($Database, $Cached_Type);
			
			// TODO - this may be redundant, but may not be.
			// TODO - comment.
			$Command_Variables_Values = &$Command_Variables_Item['Data'];
			foreach ($Command_Variables_Values as $Command_Variables_Value_Alias => &$Command_Variables_Value_Value)	
			{
				if (Has_Property($Item, $Command_Variables_Value_Alias))
				{	
					Set_Value($Item, $Command_Variables_Value_Alias, $Command_Variables_Value_Value);
				}
			}
		}
		
		// 'New' clause is not set
		else
		{
			// Get item...
			
			// Get base (left-most node) of name tree
			$Recursive_Name_Tree = &$Name_Tree;

			if (!isset($Recursive_Name_Tree['Kind']))
			{
				echo "No kind on recursive name tree.";
				traverse($Command);
			}
				
			while (strtolower($Recursive_Name_Tree['Kind']) == 'operator')
			{
				// Move up through left term
				$Recursive_Name_Tree = &$Recursive_Name_Tree['Terms'][1];
			}
			$Name_Tree_Base = &$Recursive_Name_Tree;
			// TODO @core-language: this should be the left-most child, not the base value
			$Name_Tree_Base_Value = &$Name_Tree_Base['Value'];
			
			// Get item from both context and database by default
			$Skip_Memory = false;
			$Skip_Database = false;
			
			// Check if the 'from' clause has been set (i.e. 'From Database')
			$From = &$_;$_ = ''; 
			unset($_);
			if (isset($Command['Clauses']['from']))
			{
				// TODO Error Checking: validate 'from'
				
				// Get 'from' from 'from' clause
				// TODO other From types?
				// TODO calculate as expression?
				$From = &$Command['Clauses']['from']['Tree']['Value'];
			}
			
			// If 'from' is set to 'database', skip ahead to database step
			if (strtolower($From) == 'database')
				$Skip_Memory = true;
			
			// If 'from' is set to 'memory', skip ahead to database step
			if (strtolower($From) == 'memory')
				$Skip_Database = true;

			// Check if the 'which' clause has been set (i.e. 'Page 13' or 'User 'Admin'')
			if (isset($Command['Clauses']['which']))
			{
				// TODO error checking: validate 'which'
				
				// Skip ahead to the database step for 'which' queries without dot operators names
				// TODO - is this just for (identifier.Action)? 
				// TODO - do we implement memory checking for other witches (heheh), though? 
				if (!(strtolower($Name_Tree['Kind']) == 'operator' && $Name_Tree['Value'] == '.'))
					$Skip_Memory = true;
			}			
			
			// Debugging framework
			$Test_Name_Tree_Base_Value = null;
			if (strtolower($Name_Tree_Base_Value) == $Test_Name_Tree_Base_Value)
				$Debug = true;
			else
				$Debug = false;
				
			if ($Debug) echo "$Test_Name_Tree_Base_Value:\n";
				
			// TODO: support text/number tags?
			switch(strtolower($Name_Tree_Base['Kind']))
			{
				case 'variable':
					if ($Debug) echo "\tIs Variable\n";
					// TODO: using $Found because PHP's isset() fails against null values, which are valid here
					$Found = false;
					if (!$Skip_Memory)
					{
						if ($Debug) echo "\tNot Skip Memory\n";
						// Memory Step...
						// Check if we have an item in the memory stack
						// TODO: this would normally be an isset() check, but I don't know how to check that on passed-in values
						if (!$Found && !is_null($Memory_Stack_Reference))
						{							
							switch (strtolower($Name_Tree_Base_Value))
							{
								// This step...
								case 'this':
									if ($Debug) echo "\tIs This\n";
									$Item = &$Memory_Stack_Reference['Item'];	
									$Found = true;
									break;
								
								default:
									break;
							}
							
							if ($Found)
							{
								if ($Name_Tree['Kind'] == 'Operator')
								{
									$Progressive_Name_Tree = &Clone_Dot_Tree_Without_Base($Name_Tree);
					
									// Copy Command clauses to new command (by value)
									$Progressive_Command = &Copy_Command($Command);
									
									// Overwrite name clause of new command
									$Progressive_Command['Clauses']['name'] = &New_Clause();
									$Progressive_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
									unset($_);
									$Progressive_Command['Clauses']['name']['Tree'] = &$Progressive_Name_Tree;
								}
							}

							else
							{
								// Search through memory stack for a match							
								$Recursive_Memory_Stack_Reference = &$Memory_Stack_Reference;
								while (isset($Recursive_Memory_Stack_Reference))
								{
									if ($Debug) echo "\tCurrent_Item - " . $Recursive_Memory_Stack_Reference['Item']['Cached_Specific_Type']['Alias'] . " \"" . $Recursive_Memory_Stack_Reference['Item']['Data']['Alias'] . "\"\n";
									// Dereference the memory stack item
									$Recursive_Memory_Stack_Reference_Item = &$Recursive_Memory_Stack_Reference['Item'];
					
									// Check if base name matches alias of any parent type
									// TODO - determine No_Child_Types behavior.
									// TODO - this seems to prefer type alias's over properties, is that a correct interpretation? if so, is it desired?
									$Recursive_Memory_Stack_Reference_Item_Type = &$Recursive_Memory_Stack_Reference_Item['Cached_Specific_Type'];
									while (isset($Recursive_Memory_Stack_Reference_Item_Type))
									{
										if ($Debug) echo "\tChecking Type Alias - " . $Recursive_Memory_Stack_Reference_Item_Type['Alias'] . "\n";

										// Check if memory stack reference item's type's alias matches
										if (strtolower($Recursive_Memory_Stack_Reference_Item_Type['Alias']) == strtolower($Name_Tree_Base_Value))
										{
											if ($Debug) echo "\tCheck succeeded\n";
											// Found an item
											$Found = true;
									
											// Get the item from the reference
											$Item = &$Recursive_Memory_Stack_Reference_Item;
										
											// Build new name tree without the previous base
											// TODO: don't yet support the following [Page][Page /][/Page]
											if ($Name_Tree['Kind'] == 'Operator')
											{
												$Progressive_Name_Tree = &Clone_Dot_Tree_Without_Base($Name_Tree);
											
												// Copy Command clauses to new command (by value)
												$Progressive_Command = &Copy_Command($Command);
							
												// Overwrite name clause of new command
												$Progressive_Command['Clauses']['name'] = &New_Clause();
												$Progressive_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
												unset($_);
												$Progressive_Command['Clauses']['name']['Tree'] = &$Progressive_Name_Tree;
											}
							
											break 2;
										}
								
										// Move to parent type
										if (isset($Recursive_Memory_Stack_Reference_Item_Type['Cached_Parent_Type']))
											$Recursive_Memory_Stack_Reference_Item_Type = &$Recursive_Memory_Stack_Reference_Item_Type['Cached_Parent_Type'];
										else
											unset($Recursive_Memory_Stack_Reference_Item_Type);
									}		
							
									// Check if base name matches a local variable (require that property exists and is actually set)
									// TODO: this doesn't support tristan's vision of 'runtime properties' yet, maybe? check Has_Property.
									// TODO @core-language: How to allow explicit access to variables of previous items (i.e. Site.Variables.Val) or explicitly by (Variables.Val)
									{
										if ($Debug) echo "\tChecking Variables\n";
									
										$Recursive_Memory_Stack_Reference_Variables_Item = &$Recursive_Memory_Stack_Reference['Variables'];
										if (Has_Property($Recursive_Memory_Stack_Reference_Variables_Item, $Name_Tree_Base_Value))
										{
											// TODO why were we checking if the value is set? isn't it enough to check if it has the property? The below check is wrong since it should be checking against Data_Name instead of just the lookup
// 											if (array_key_exists($Name_Tree_Base_Value, $Recursive_Memory_Stack_Reference_Variables_Item['Data']))
// 											{
											if ($Debug) echo "\tCheck succeeded\n";
											// Found an item
											$Found = true;
								
											// Set item to current memory stack reference item
											$Item = &$Recursive_Memory_Stack_Reference_Variables_Item;
										
											// Use the current command as the progressive command as-is
											$Progressive_Command = &$Command;
								
											// Break out of memory stack
											break;
// 											}
										}
									}
								
									// Check if base name matches a meta-data property
									global $Metadata_Properties;									
									if (in_array(strtolower($Name_Tree_Base_Value), $Metadata_Properties))
									{
										if ($Debug) echo "\tChecking Metadata\n";
										// Additional checks based on metadata property
										switch (strtolower($Name_Tree_Base_Value))
										{
											case 'action':
												// Action needs a "which" to qualify for metadata match.
												if (isset($Command['Clauses']['which']))
													$Found = true;
												else
													$Found = false;
												break;

											default:
												$Found = true;
												break;											
										}
													
										// Item is found... 
										if ($Found)
										{
											if ($Debug) echo "\tCheck Succeeded\n";
											// Set item to current memory stack reference item
											$Item = &$Recursive_Memory_Stack_Reference_Item;
								
											// Use the current command as the progressive command as-is
											$Progressive_Command = &$Command;
									
											// Break out of memory stack
											break;
										}
									}
									
									// Check if the base name matches the alias of the type of a target of an action in context. 
									// TODO - there are alternative convenience options we should consider, or also merging this with other special cases.
									// TODO - don't need to grab these in a loop
									$Recursive_Memory_Stack_Reference_Item_Base_Type = &$Recursive_Memory_Stack_Reference_Item['Cached_Base_Type'];
									$Type_Action_Cached_Type = &Get_Cached_Type($Database, 'type_action');
									if (Is_Child_Type_Of($Recursive_Memory_Stack_Reference_Item_Base_Type, $Type_Action_Cached_Type))
									{
										// TODO - should always be set but not 100% sure, just 99%
										$Type_Action_Target_Item = &$Recursive_Memory_Stack_Reference_Item['Data']['Target'];
										if (Is_Item($Type_Action_Target_Item))
										{
											$Type_Action_Target_Item_Base_Type = $Type_Action_Target_Item['Cached_Base_Type'];											
											$Type_Action_Target_Item_Base_Type_Alias = $Type_Action_Target_Item_Base_Type['Alias'];

											// Match base name against target type alias
											if (strtolower($Name_Tree_Base_Value) == strtolower($Type_Action_Target_Item_Base_Type_Alias))
											{
												// Found an item 
												$Found = true;
												
												// Set item to target item of type acton
												$Item = &$Type_Action_Target_Item;
												
												// Build a new name tree without the previous base
												// TODO - this sure is repeated code
												if ($Name_Tree['Kind'] == 'Operator')
												{
													$Progressive_Name_Tree = &Clone_Dot_Tree_Without_Base($Name_Tree);
											
													// Copy Command clauses to new command (by value)
													$Progressive_Command = &Copy_Command($Command);
							
													// Overwrite name clause of new command
													$Progressive_Command['Clauses']['name'] = &New_Clause();
													$Progressive_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
													unset($_);
													$Progressive_Command['Clauses']['name']['Tree'] = &$Progressive_Name_Tree;
												}
											}
										}
									}
																
									// Check if base name matches a date format string for a date item...
									// TODO - merge with below
									// TODO - or do a special case for these ones where they are not recursive? 
									global $All_Date_Format_Strings;
									global $Date_Types;
									if ($Debug) echo "\tChecking Date Format Strings\n";
									if (in_array(strtolower($Name_Tree_Base_Value), $All_Date_Format_Strings))
									{
										if (in_array(strtolower($Recursive_Memory_Stack_Reference_Item['Cached_Base_Type']['Alias']), $Date_Types))
										{
											if ($Debug) echo "\tCheck Succeeded\n";										

											// Found an item
											$Found = true;
								
											// Set item to current memory stack reference item
											$Item = &$Recursive_Memory_Stack_Reference_Item;
								
											// Use the current command as the progressive command as-is
											$Progressive_Command = &$Command;
									
											// Break out of memory stack
											break;
										}
									}
							
									// Check if base name matches a set property
									if ($Debug) echo "\tChecking Item Properties\n";
									if (Has_Property($Recursive_Memory_Stack_Reference_Item, $Name_Tree_Base_Value))
									{										
										if ($Debug) echo "\tCheck Succeeded\n";
										// Found an item
										$Found = true;
								
										// Set item to current memory stack reference item
										$Item = &$Recursive_Memory_Stack_Reference_Item;
								
										// Use the current command as the progressive command as-is
										$Progressive_Command = &$Command;
									
										// Break out of memory stack
										break;
									}
						
									// Check alias
									if (!$Recursive_Memory_Stack_Reference_Item['End_Of_Results'])									
									{
										if ($Debug) echo "\tChecking Item Alias\n";
										if (array_key_exists('Alias', $Recursive_Memory_Stack_Reference_Item['Data']) && $Recursive_Memory_Stack_Reference_Item['Data']['Alias'] == $Name_Tree_Base_Value)
										{
											if ($Debug) echo "\tCheck Succeeded\n";
										
											// Found an item
											$Found = true;
								
											// Set item to current memory stack reference item
											$Item = &$Recursive_Memory_Stack_Reference_Item;
								
											// Build new name tree without the previous base
											// TODO: don't yet support the following [Page][Page /][/Page]
											if ($Name_Tree['Kind'] == 'Operator')
											{
												$Progressive_Name_Tree = &Clone_Dot_Tree_Without_Base($Name_Tree);
						
												// Copy Command clauses to new command (by value)
												$Progressive_Command = &Copy_Command($Command);
						
												// Overwrite name clause of new command
												$Progressive_Command['Clauses']['name'] = &New_Clause();
												$Progressive_Command['Clauses']['name']['Name'] = &$_;$_ = 'Name'; 
												unset($_);
												$Progressive_Command['Clauses']['name']['Tree'] = &$Progressive_Name_Tree;
											}

											// Break out of memory stack
											break;					
										}
									}
					
									// Move to previous memory stack reference
									if (isset($Recursive_Memory_Stack_Reference['Previous_Memory_Stack_Reference']))
										$Recursive_Memory_Stack_Reference = &$Recursive_Memory_Stack_Reference['Previous_Memory_Stack_Reference'];
									else
										unset($Recursive_Memory_Stack_Reference);
								}
							}
							
							// Check if the above gave us an item
							if ($Found)
							{
								// Check if there is a remaining progressive command
								if (isset($Progressive_Command))
								{
									// TODO hack that forces "As" for inner_content_block
									// TODO - is there an integrated way to handle this? Get value is the best place, due to the fact that it knows what value_type the property was, which is unavaiable afterwards
									// TODO - This "As" is currently commented out in Get_Value and has no consequence - and dates always return items.
								
									$Copied_Progressive_Command = $Progressive_Command;
								
									if ($Inner_Content_Block)
									{								
										$Copied_Progressive_Command['Clauses']['as'] = array('Name' => 'As', 'Tree' => array('Kind' => 'Variable', 'Value' => 'Reference'));
									}
									
									$Item = &Get_Value($Item, $Copied_Progressive_Command, $Memory_Stack_Reference);									
								
									unset($Copied_Progressive_Command);
								}
							}
						}
					
						// Template Step...
						// TODO: removed from old jelly because too insane
						if (!$Found)
						{
						}
					}
					
					// Database Step...
					// If no item has been retreived from the memory stack, look it up from the database
					if (!$Skip_Database)
					{
						if (!$Found)
						{
	// 						traverse($Name_Tree_Base_Value);
	// 						traverse($Cached_Type_Lookup);
							// Check if the base name matches a type
							if (isset($Cached_Type_Lookup[strtolower($Name_Tree_Base_Value)]))
							{
	// 							echo "FOUND in database step";
								$Found = true;
							
								// Query the database
								$Item = &Get_Database_Item($Database, $Command, $Memory_Stack_Reference);
							}
						}
					}
					
					// Not Found Step...
					// If no item has been retreived from the memory stack, it's not found!
					if (!$Found)
					{
						// TODO: this should be handled as an [Else] tag?
						// TODO - not sure if item should just be null here? Think about database values vs. simple values
						// TODO - should be set to "NOT SET" kind
// 						traverse($Command);
// 						traverse($Memory_Stack_Reference);
// 						echo "NOT FOUND START";
						$Item = &New_Not_Set();
// 						throw new Exception('Item not found: ' . $Command_Name);
					}
					
					break;
				default:
					traverse($Command);
					throw new exception('Unknown name tree base type');
			}
		}
				
		// Render Tag...

		// Fetch As
		// TODO - evaluate as clause?
		if (isset($Command['Clauses']['as']))
		{
			$As_Value = &$Command['Clauses']['as']['Tree']['Value'];
			$As_Kind = &$Command['Clauses']['as']['Tree']['Kind'];
		}
		else
		{
			$As_Value = &$_;$_ = 'Default'; 
			unset($_);
			$As_Kind = &$_;$_ = 'Variable'; 
			unset($_);
		}
			
		// Check if Item is an array or a simple value
		if (!is_array($Item) || (is_array($Item) && isset($Item['Treat_As_Simple_Value']) && $Item['Treat_As_Simple_Value']))
		{
			if (is_array($Item) && isset($Item['Treat_As_Simple_Value']) && $Item['Treat_As_Simple_Value'])
			{
				$Original_Item = &$Item;
				$Item = &$Item['Data']['Simple_Value'];
			}
			
			// TODO - handle not set
			// Item is simple...
			
			// Check if we should process the value;
			if (isset($Command['Clauses']['process_once']) || strtolower($As_Value) == 'reference')
			{
				// Append result to processed command
				$Text_Chunk = &New_Chunk('Text_Chunk');
				$Text_Chunk['Content'] = &$Item;
				$Processed_Command['Chunks'][] = &$Text_Chunk;			
			}
			else
			{
				if ($Item === true)
				{
					// Append result to processed command
					$Text_Chunk = &New_Chunk('Text_Chunk');
					$Text_Chunk['Content'] = 'True';
					$Processed_Command['Chunks'][] = &$Text_Chunk;			
				}
				elseif ($Item === false)
				{
					// Append result to processed command
					$Text_Chunk = &New_Chunk('Text_Chunk');
					$Text_Chunk['Content'] = 'False';
					$Processed_Command['Chunks'][] = &$Text_Chunk;			
				}
				elseif ($Item === null)
				{
					// Append result to processed command
					$Text_Chunk = &New_Chunk('Text_Chunk');
					$Text_Chunk['Content'] = '';
// 					$Text_Chunk['Content'] = 'Null';
					$Processed_Command['Chunks'][] = &$Text_Chunk;			
				}
				
				// TODO - verify this behavior....
				elseif (is_numeric($Item))
				{
					$Number_Chunk = &New_Chunk('Number_Chunk');
					$Number_Chunk['Content'] = &$Item;
					$Processed_Command['Chunks'][] = &$Number_Chunk;			
				}
				
				// TODO - verify this behavior....
				elseif ($Item == '')
				{
					$Text_Chunk = &New_Chunk('Text_Chunk');
					$Text_Chunk['Content'] = '';
					$Processed_Command['Chunks'][] = &$Text_Chunk;
				}
				
				else
				{
					// Re-process the result
					$Processed_Block = &Process_Block_String($Database, $Item, $Memory_Stack_Reference);
					
					// Append resulting processed block to processed command
					$Processed_Command['Chunks'][] = &$Processed_Block;
				}
			}
			
			// Pass in no_wrap to render flags (TODO: better way to standardize this?)
			$Chunk_Render_Flags = array();
			
			if (isset($Original_Item))
			{
				$Processed_Command['From'] = &$_;$_ = 'Property'; 
				unset($_);
				$Processed_Command['Parent_Item'] = &$Original_Item['Parent_Item'];
				$Processed_Command['Parent_ID'] = &$Original_Item['Parent_Item']['Data']['ID'];
				$Processed_Command['Parent_Cached_Property'] = &$Original_Item['Parent_Cached_Property'];
			}
			
			// Check if wrap element was set for property
			if (isset($Command['Clauses']['wrap_element']))
				$Chunk_Render_Flags['Wrap_Element'] = &$Command['Clauses']['wrap_element']['Tree']['Value'];
			
			if (isset($Command['Clauses']['no_wrap']))
			{
				$Chunk_Render_Flags['No_Wrap'] = &$_;$_ = true;
				unset($_);
			}
			else if (isset($Command['Clauses']['wrap']))
			{
				$Chunk_Render_Flags['Wrap'] = &$_;$_ = true;
				unset($_);
			}
			else 
			{
				// Don't wrap properties memory properties.
				if (array_key_exists('Parent_Cached_Property', $Processed_Command) && !array_key_exists('ID', $Processed_Command['Parent_Cached_Property']))
				{
					$Chunk_Render_Flags['No_Wrap'] = &$_;$_ = true;
					unset($_);
				}
			}
			
			$Processed_Command['Render_Flags'] = &$Chunk_Render_Flags;
		}

		else if (Is_Not_Set($Item))
		{
			switch (strtolower($As_Value))
			{
				case 'reference':
					// TODO - run by tristan...
					$Processed_Command['Chunks'][] = array('Kind' => 'Not_Set_Item_Chunk', 'Item' => &$Item);
					break;
					break;
				default:
					// TODO - fine? 
					$Text_Chunk = &New_Chunk('Text_Chunk');
					$Text_Chunk['Content'] = '';
					$Processed_Command['Chunks'][] = &$Text_Chunk;
					break;
			}
		}

		else
		{
			// Item is an object...
			
			// Render object as an identifier, function or template
			switch (strtolower($As_Value))
			{
				// Identifiers...
				
				// As Key/Alias/ID
				case 'key':
				case 'alias':
				case 'id':
				{
					// TODO: should fail if no results?
					
					switch (strtolower($As_Value))
					{
						// Key
						case 'key':
							$Cached_Item_Specific_Type = &$Item['Cached_Specific_Type'];
							$Key_Alias = &$Cached_Item_Specific_Type['Default_Key'];
							$Key = &$Cached_Item_Specific_Type['Cached_Property_Lookup'][strtolower($Key_Alias)];
							$Key_Data_Name = &$Key['Data_Name'];
							$Item_Key = &$Item['Data'][$Key_Data_Name];
							break;
						
						// Alias
						case 'alias':
							$Item_Key = &$Item['Data']['Alias'];
							break;
							
						// ID
						case 'id':
							$Item_Key = &$Item['Data']['ID'];
							break;
					}
					
					// Create new text chunk for key value
					$Text_Chunk = &New_Chunk('Text_Chunk');
					$Text_Chunk['Content'] = &$Item_Key;
					
					// Append new text chunk to processed command
					$Processed_Command['Chunks'][] = &$Text_Chunk;
					
					break;
				}
					
				
				// As Reference
				case 'reference':
				{
					// TODO: support iteration?
					// TODO: should fail if no results
					$Processed_Item_Chunk = &$_;$_ = [];
					unset($_);
					
					$Processed_Item_Chunk['Kind'] = &$_;$_ = 'Item_Chunk'; 
					unset($_);
					$Processed_Item_Chunk['Item'] = &$Item;
					
					$Processed_Command['Chunks'][] = &$Processed_Item_Chunk;
					
					break;					
				}
				
				
				// Functions...
				case 'count':
					// TODO: this is a terrible way to get count
					$Result_Count = 0;
					
					if (isset($Item['Result']))
					{
						if (count($Item['References']) || isset($Item['Item_Was_Set_As_A_Value']))
							$Result_Count = 1;
						else
						{
							while (!array_key_exists('End_Of_Results', $Item) || !$Item['End_Of_Results'])
							{
								$Result_Count++;
								Move_Next($Item);
							}
						}
					}
					
					// Create new text chunk for count value
					$Text_Chunk = &New_Chunk('Text_Chunk');
					$Text_Chunk['Content'] = &$Result_Count;
					
					// Append new text chunk to processed command
					$Processed_Command['Chunks'][] = &$Text_Chunk;
					break;
				
				
				// Template...
				default:				
					// Render Item normally...
					
					// Get namespace of previous memory stack reference or passed in parent namespace
					if (isset($Command['Clauses']['parent_namespace']))
						$Parent_Namespace = &$Command['Clauses']['parent_namespace']['Tree']['Value'];
					else
						$Parent_Namespace = &$Memory_Stack_Reference['Namespace'];
					
					if (!$Parent_Namespace)
					{
						// Echo "No parent namespace!";
						// TODO hack this to empty string for now
						$Parent_Namespace = &New_String('');
// 						traverse($Memory_Stack_Reference);
// 						exit();
					}
					
					// Get item type
					// TODO - should this be base type?
					// TODO - should the variable name reflect which one? 
					$Cached_Base_Type = &$Item['Cached_Base_Type'];
					$Cached_Base_Type_Alias = &$Cached_Base_Type['Alias'];

					// Set No_Wrap, No_Refresh, No_Script flags...
					$Chunk_Render_Flags = &$_;$_ = [];
					unset($_);
					// TODO: disabled wrapping disabling for !Saved items
// 					if (!$Item['Saved'])
// 					{	
// 						// For memory items or new items, set No_Wrap, No_Refresh, No_Script.
// 						$Chunk_Render_Flags['No_Refresh'] = &New_Boolean(true);
// 						$Chunk_Render_Flags['No_Wrap'] = &New_Boolean(true);						
// 						$Chunk_Render_Flags['No_Scripts'] = &New_Boolean(true);
// 					}
// 					else
					{
						switch (strtolower($Cached_Base_Type_Alias))
						{
							// For certain types, set No_Refresh.
							// TODO: this is a hack to disable refreshing of dependent items until we keep track of items accessed outside the current scope
							case 'input': 
							case 'property':
							case 'type_action':
							{
								$Chunk_Render_Flags['No_Refresh'] = &$_;$_ = true;
								unset($_);
								
								// NO BREAK
							}
							
							// Set No_Wrap, No_Refresh, No_Script from clause
							// TODO: evaluate clauses?
							default:
							{
								if (isset($Command['Clauses']['no_preloader']))
								{
									$Chunk_Render_Flags['No_Preloader'] = &$_;$_ = true;
									unset($_);
								}
								if (isset($Command['Clauses']['no_refresh']))
								{
									$Chunk_Render_Flags['No_Refresh'] = &$_;$_ = true;
									unset($_);
								}
								if (isset($Command['Clauses']['no_wrap']))
								{
									$Chunk_Render_Flags['No_Wrap'] = &$_;$_ = true;
									unset($_);
								}
								else if (isset($Command['Clauses']['wrap']))
								{
									$Chunk_Render_Flags['Wrap'] = &$_;$_ = true;
									unset($_);
								}
								if (isset($Command['Clauses']['no_scripts']))
								{
									$Chunk_Render_Flags['No_Scripts'] = &$_;$_ = true;
									unset($_);
								}
								if (isset($Command['Clauses']['item_classes']))
								{
									$Chunk_Render_Flags['Item_Classes'] = &$Command['Clauses']['item_classes']['Tree']['Value'];
								}
								if (isset($Command['Clauses']['iterator_classes']))
								{
									$Chunk_Render_Flags['Iterator_Classes'] = &$Command['Clauses']['iterator_classes']['Tree']['Value'];
								}
							}
						}
					}
					// Check if Wrap Element was set
					if (isset($Command['Clauses']['iterator_wrap_element']))
						$Chunk_Render_Flags['Iterator_Wrap_Element'] = &$Command['Clauses']['iterator_wrap_element']['Tree']['Value'];
					if (isset($Command['Clauses']['item_wrap_element']))
						$Chunk_Render_Flags['Item_Wrap_Element'] = &$Command['Clauses']['item_wrap_element']['Tree']['Value'];
					
					// Preserve current namespace, or advance it by creating iterator namespace
					if (isset($Command['Clauses']['preserve_namespace']))
					{
						$Iterator_Namespace = &$Parent_Namespace;
					}
					else
					{
						// Create iterator namespace
						$Iterator_Namespace = &$_;$_ = $Parent_Namespace . NAMESPACE_DELIMITER . $Cached_Base_Type_Alias;
						unset($_);
						$Iterator_Unique_Index = 0;
						while (isset($GLOBALS['Used_Namespaces'][strtolower($Iterator_Namespace)]))
						{
							$Iterator_Unique_Index++;
							$Iterator_Namespace = &$_;$_ = $Parent_Namespace . NAMESPACE_DELIMITER . $Cached_Base_Type_Alias . NAMESPACE_DELIMITER . $Iterator_Unique_Index;
							unset($_);
						}
						$GLOBALS['Used_Namespaces'][strtolower($Iterator_Namespace)] = true;
					}
					
					// Create chunk for processed iterator
					$Processed_Iterator = &New_Processed_Chunk('Processed_Iterator_Chunk');
					$Processed_Iterator['Original_Command'] = &$Command;
					$Processed_Iterator['Chunks'] = &$_;$_ = [];
					unset($_);
					
					// Check if iterator comes from a parent's property
					if (isset($Item['Parent_Item']))
					{
						$Processed_Iterator['From'] = &$_;$_ = 'Property'; 
						unset($_);
						$Processed_Iterator['Parent_Item'] = &$Item['Parent_Item'];
						$Processed_Iterator['Parent_ID'] = &$Item['Parent_Item']['Data']['ID'];
						$Processed_Iterator['Parent_Cached_Property'] = &$Item['Parent_Cached_Property'];
					}
					
					// Add metadata to processed iterator 
					$Processed_Iterator['Metadata'] = array(
						'Parent_Namespace' => &$Parent_Namespace,
						'Namespace' => &$Iterator_Namespace,
						'Cached_Type' => &$Cached_Base_Type,
					);
					
					$Processed_Iterator['Render_Flags'] = &$Chunk_Render_Flags;
// 						traverse($Processed_Iterator);
						
					$Processed_Command['Chunks'][] = &$Processed_Iterator;
					
					// Wrap Iterator
// 					$Processed_Iterator['Add_Wrap'] = true;
					
					// TODO: KUNAL's HACKERY
// 					if (strtolower($Last_Selector['Name']) == 'container')
// 						$Item['From_Context'] = false;
					
// 					if (isset($Item['From_Context']) && $Item['From_Context'])
// 						$From_Context = $Item['From_Context'];
// 					else
// 						$From_Context = false;
// 					$Item['From_Context'] = true;
					
					// Check if inner data comes from the tag
					if ($Inner_Content_Block)
					{
						$Content_Block = &$Inner_Content_Block;
						// TODO - look right? just guessed.
						unset($Cached_Template);
// 						$Template_Alias = '';
// 						$As = '';
					}
					else
					{
						switch ($As_Kind)
						{	
							// Try cached template lookup, then search for template that matches name
							case 'Text':
								
								// Try cached template lookup
								if (array_key_exists(strtolower($As_Value), $Cached_Base_Type['Cached_Template_Lookup']))
									$Cached_Template = &$Cached_Base_Type['Cached_Template_Lookup'][strtolower($As_Value)];									
									
								// Or search by name value
								else
								{								
									// TODO - if internal language can handle templates and where queries, then perhaps we should move this over to that... 
									// TODO - e.g. [{Type_Alias}.Template where Name = {Value} or Alias = {Value}
									foreach ($Cached_Base_Type['All_Cached_Templates'] as $All_Cached_Template)
									{
										if ($As_Value ==  $All_Cached_Template['Name'] )
										{
											$Cached_Template = &$All_Cached_Template;
											break;
										}
									}
								}
								break;

							// Get template from cached lookup
							case 'Variable':
								if (array_key_exists(strtolower($As_Value), $Cached_Base_Type['Cached_Template_Lookup']))
									$Cached_Template = &$Cached_Base_Type['Cached_Template_Lookup'][strtolower($As_Value)];
								break;
						}
						
						// Make sure template exists for type
						if ($Cached_Template)
						{
							// Check if template content has already been parsed
							if (!isset($Cached_Template['Cached_Content_Block']))
							{
								// TODO store in cache
								
								// Parse Template Content and store in cached template
								$Cached_Template['Cached_Content_Block'] = &Parse_String_Into_Block($Cached_Template['Content']);
							}
							
							// Update Content
							$Template_Content_Block = &$Cached_Template['Cached_Content_Block'];
							
							// Use template content block
							$Content_Block = &$Template_Content_Block; 
						}
						else
						{
							throw new Exception('"as" did not resolve: ' . strtolower($As_Value));
						}
					}
					
					// Add template to processed iterator
					if (isset($Cached_Template))
						$Processed_Iterator['Metadata']['Cached_Template'] = &$Cached_Template;
									
					// Set up new memory stack reference
					$Item_Memory_Stack_Reference = &New_Reference($Database);
					$Item_Memory_Stack_Reference['Item'] = &$Item;
					$Item_Memory_Stack_Reference['Previous_Memory_Stack_Reference'] = &$Memory_Stack_Reference;
					
					// Move parent namespace up
					$Parent_Namespace = &$Iterator_Namespace;
					
					// Handle variables...
					$Item_Reference_Variables_Item = &$Command_Variables_Item;
					
					// If Preserve_Variables specified, copy variables from the item's previous reference.
					if (isset($Command['Clauses']['preserve_variables']))
					{
						// If the item had a previous reference, copy the variables from the previous reference.
						if (count($Item['References']))
						{
							$Item_Last_Reference = &$Item['References'][count($Item['References']) -1];
							$Item_Last_Reference_Variables_Item =  &$Item_Last_Reference['Variables'];
						
							// Copy last reference variables to current reference variables
							$Item_Last_Reference_Variables_Item_Cached_Specific_Properties = &$Item_Last_Reference_Variables_Item['Cached_Specific_Properties'];
							foreach ($Item_Last_Reference_Variables_Item_Cached_Specific_Properties as $Item_Last_Reference_Variables_Item_Cached_Specific_Property)
							{
								$Item_Last_Reference_Variables_Item_Cached_Specific_Property_Alias = &$Item_Last_Reference_Variables_Item_Cached_Specific_Property['Alias'];
					
								$Item_Last_Reference_Variables_Item_Cached_Specific_Property_Data_Name = &$Item_Last_Reference_Variables_Item_Cached_Specific_Property['Data_Name'];
						
								Set_Value($Item_Reference_Variables_Item, $Item_Last_Reference_Variables_Item_Cached_Specific_Property_Alias, $Item_Last_Reference_Variables_Item['Data'][$Item_Last_Reference_Variables_Item_Cached_Specific_Property_Data_Name]);
							}
						}
					}
					
					$Item_Memory_Stack_Reference['Variables'] = &$Item_Reference_Variables_Item;
										
					// Check if item already had references or was set as a value or does not have a result
					if (count($Item['References']) || isset($Item['Item_Was_Set_As_A_Value']) || (!isset($Item['Result'])))
					{
						$Iterating = &New_Boolean(false);
					}
					else
					{
						$Iterating = &New_Boolean(true);
					}
					
					// Add current reference to item.
					$Item['References'][] = &$Item_Memory_Stack_Reference;

					// TODO - this happens to work, but it's total nonsense - maybe special case simple items with no database connection
					// TODO - wait what is this
					// TODO - wat
					if (!array_key_exists('End_Of_Results', $Item) || !$Item['End_Of_Results'])
					{
						while (!array_key_exists('End_Of_Results', $Item) || !$Item['End_Of_Results'])
						{
							$Item_Start_Time = microtime(true);
							
							// Get specific type
							$Cached_Specific_Type = &$Item['Cached_Specific_Type'];
							$Cached_Specific_Type_Alias = &$Cached_Specific_Type['Alias'];
					
							// Get Item Identifier
							if (array_key_exists('Saved', $Item) && $Item['Saved'])
								$Item_Identifier = &$Item['Data']['ID'];
							else
							{
								$Item_Identifier = &$_;$_ = 'New'; 
								unset($_);
							}
							
							// Get item name
							if (array_key_exists('Name', $Item['Data']))
								$Item_Name = &$Item['Data']['Name'];
							else
							{
								$Item_Name = &$_;$_ = null;
								unset($_);	
							}
								
							// Get item alias
							if (array_key_exists('Alias', $Item['Data']))
								$Item_Alias = &$Item['Data']['Alias'];
							else
							{
								$Item_Alias = &$_;$_ = null;
								unset($_);
							}
							
							// Get item namespace by increment current namespace by identifier, or preserving the current namespace
							if (isset($Command['Clauses']['preserve_namespace']))
								$Item_Namespace = &New_String($Parent_Namespace);
							else
							{
								// If value from attachment, create attachment namespaces.
								// TODO - should we mark this explicitly?
								if (array_key_exists('Data', $Item) && array_key_exists('Attachment_ID', $Item['Data']))
								{
									$Attachment_Type_Alias = &$Item['Data']['Attachment_Type'];
									$Attachment_ID = &$Item['Data']['Attachment_ID'];
							
									// TODO - make unique.
									$Attachment_Type_Namespace = &$_;$_ = $Parent_Namespace . NAMESPACE_DELIMITER . $Attachment_Type_Alias;
									unset($_);
									$Attachment_Type_Unique_Index = 0;
									while (isset($GLOBALS['Used_Namespaces'][strtolower($Attachment_Type_Namespace)]))
									{
										$Attachment_Type_Unique_Index++;
										$Attachment_Type_Namespace = &$_;$_ = $Parent_Namespace . NAMESPACE_DELIMITER . $Attachment_Type_Alias . NAMESPACE_DELIMITER . $Attachment_Type_Unique_Index;
										unset($_);
									}
									$GLOBALS['Used_Namespaces'][strtolower($Attachment_Type_Namespace)] = true;
									
									$Attachment_Namespace = &$_;$_ = $Attachment_Type_Namespace . NAMESPACE_DELIMITER . $Attachment_ID;
									unset($_);
									$Current_Namespace = &$Attachment_Namespace;
								}
								else
									$Current_Namespace = &$Parent_Namespace;
								
								// Generate item namespace
								$Item_Namespace = &$_;$_ = $Current_Namespace . NAMESPACE_DELIMITER . $Item_Identifier;
								unset($_);
							}

							// Set namespace in reference
							$Item_Memory_Stack_Reference['Namespace'] = &$Item_Namespace;
							
							// Create processed item and add to processed iterator's chunks
							$Processed_Item = &New_Processed_Chunk('Processed_Item_Chunk');
							$Processed_Item['Original_Command'] = &$Command;
							$Processed_Item['Chunks'] = &$_;$_ = [];
							unset($_);

							// Add metadata to processed item 
							// TODO: check exactly what we should be setting, and if they work
							$Processed_Item['Metadata'] = array(
								'Parent_Namespace' => &$Parent_Namespace,
								'Namespace' => &$Item_Namespace,
								'Cached_Type' => &$Cached_Specific_Type,
								'ID' => &$Item_Identifier,
								'Name' => &$Item_Name,
								'Alias' => &$Item_Alias
							);
					
							// Add attachment namespaces to the processed item
							if (array_key_exists('Data', $Item) && array_key_exists('Attachment_ID', $Item['Data']))
							{
								$Processed_Item['Metadata']['Attachment_Type_Namespace'] = &$Attachment_Type_Namespace;
								$Processed_Item['Metadata']['Attachment_Namespace'] = &$Attachment_Namespace;
								$Processed_Item['Metadata']['Attachment_Type_Alias'] = &$Attachment_Type_Alias;
								$Processed_Item['Metadata']['Attachment_ID'] = &$Attachment_ID;
							}
							
							// Set from container metadata
							if (isset($Command['Clauses']['from_container']))
							{
								$Processed_Item['Metadata']['From_Container'] = &$_;$_ = true;
								unset($_);
							}
								
							// Set from request metadata
							if (isset($Command['Clauses']['from_request']))
							{
								$Processed_Item['Metadata']['From_Request'] = &$_;$_ = true;
								unset($_);
							}
							
							if (isset($Cached_Template))
								$Processed_Item['Metadata']['Cached_Template'] = &$Cached_Template;
								
							// Set reference variables metadata
							{
								$Variables_Metadata = &$_;$_ = [];
								unset($_);
								$Variables_Item_Values = &$Item_Reference_Variables_Item['Data'];
								
								if (count($Variables_Item_Values) > 0)
								{
									foreach ($Variables_Item_Values as $Variables_Item_Value_Alias => &$Variables_Item_Value)
									{
										if (Is_Not_Set($Variables_Item_Value))
										{	
											// TODO - branch area for debugging 
										}
									
										else if (Is_Item($Variables_Item_Value))
										{
											// Dereference value
											$Cached_Variable_Item_Type = &$Variables_Item_Value['Cached_Specific_Type'];
											$Cached_Variable_Item_Type_Alias = &$Cached_Variable_Item_Type['Alias'];

											// For simple type items, store type and deference to simple value 
											if (Is_Simple_Type($Cached_Variable_Item_Type))
											{
												if (array_key_exists('Simple_Value', $Variables_Item_Value))
												{
													$Variables_Metadata[$Variables_Item_Value_Alias . '_Type'] = &$Cached_Variable_Item_Type_Alias;
													$Variables_Metadata[$Variables_Item_Value_Alias] = &$Variables_Item_Value['Data']['Simple_Value'];
												}
												else
												{
													// TODO - branch area for debugging.
												}
											}
											else
											{
												// TODO - more thoughtful dereferencing needed.
												if ($Variables_Item_Value['End_Of_Results'])
												{
													// TODO - nothing?  Just currently helping debugging.
												}
												elseif ($Variables_Item_Value['Data']['ID'])
												{
													$Variables_Metadata[$Variables_Item_Value_Alias . '_Type'] = &$Cached_Variable_Item_Type_Alias;
													$Variables_Metadata[$Variables_Item_Value_Alias] = &$Variables_Item_Value['Data']['ID'];
												}
												else
												{
													// TODO if not dereferenceable, skip.  Is that ok?  It could also be marked as "new" but what's the point.
// 													traverse($Command);
// 													traverse($With_Value);
// 													throw new Exception('Error dereferencing with value');
// 													exit();
												}
											}
										}
									
										else 
										{
											// Set value directly
											$Variables_Metadata[$Variables_Item_Value_Alias] = &$Variables_Item_Value;
										}
									}

									$Processed_Item['Metadata']['Variables'] = &$Variables_Metadata;
								}
							}
							
							$Processed_Iterator['Chunks'][] = &$Processed_Item;
							
							// Process the content using the new memory stack reference
							$Item_Processed_Content_Block = &Process_Block_With_Evaluation($Database, $Content_Block, true, $Item_Memory_Stack_Reference);
							
							// Add processed block to processed item's chunks
							$Processed_Item['Chunks'][] = &$Item_Processed_Content_Block;
							
							// Set Render Flags
							$Processed_Item['Render_Flags'] = &$Chunk_Render_Flags;
							
							$Processed_Item['Metadata']['Duration'] = microtime(true) - $Item_Start_Time;
						
							// Check if we should iterate or just process the current item
							if ($Iterating)
							{
// 								echo "MOVING: " . $Item['Cached_Base_Type']['Alias'] . ':' . $Item['Data']['Alias'] . "!<br/>";
								// Move to next item
								Move_Next($Item);
							}
							else
							{
								// Break now that we have processed the current item
								break;
							}
						}
					}
					else
					{
						// "Else" tag for iterators
						
						// Reuse parent namespace
						// TODO mark as preserve_namespace?
						$Item_Memory_Stack_Reference['Namespace'] = &$Parent_Namespace;
						
						// Process the content using the new memory stack reference
						$Item_Processed_Content_Block = &Process_Block_With_Evaluation($Database, $Content_Block, false, $Item_Memory_Stack_Reference);						
						
						// Add processed block to processed item's chunks
						$Processed_Iterator['Chunks'][] = &$Item_Processed_Content_Block;
					}
					
					// Restore item references
					array_pop($Item['References']);
					break;
			}
			
			// Store duration
			$Processed_Iterator['Metadata']['Duration'] = microtime(true) - $Iterator_Start_Time;
		}
	}
		
	// Return result
	return $Processed_Command;
}

?>