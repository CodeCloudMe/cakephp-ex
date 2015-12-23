<?php

// Render Block

// TODO @feature-language: incorporate old On_Submit/On_Cancel code?
// TODO @feature-language: Paging (Count, Sort, etc)

function Render_Processed_Block(&$Block, $Render_Flags = null)
{
	if ($Render_Flags)
		$Copied_Render_Flags = $Render_Flags;
	else
		$Copied_Render_Flags = array();
	
	// Incorporate No_Wrap/No_Scripts tags
	if (isset($Block['Original_Command']))
	{
		// TODO: is there a better way to do this?
		if (
				//TODO - this facilitates [No_Wrap]
				strtolower($Block['Original_Command']['Clauses']['name']['Tree']['Value']) == 'no_wrap' 
				
					|| 
					
				//TODO - this facilitates tag headers, specifically, but can work more generally later.
				(isset($Block['Original_Command']['Clauses']['never_wrap']) && $Block['Original_Command']['Clauses']['never_wrap'])
			)
		{
			$Copied_Render_Flags['No_Wrap'] = true;
		}
		if (strtolower($Block['Original_Command']['Clauses']['name']['Tree']['Value']) == 'no_scripts')
			$Copied_Render_Flags['No_Scripts'] = true;
	}
	
	// Note: $Wrap not by reference
	$Wrap = false;
	$No_Wrap = false;

	// If this block has No_Wrap set explicitly, set No_Wrap to true
	if (isset($Block['Render_Flags']['No_Wrap']) && $Block['Render_Flags']['No_Wrap'])
		$No_Wrap = true;	
		
	// If the overall render flags	are set to no_wrap, and this block isn't set explicitly to wrap, then set No_Wrap to true
	if ((isset($Copied_Render_Flags['No_Wrap']) && $Copied_Render_Flags['No_Wrap']) && !(isset($Block['Render_Flags']['Wrap']) && $Block['Render_Flags']['Wrap']))
		$No_Wrap = true;	
		
	$Add_Scripts = true;
	if ((isset($Block['Render_Flags']['No_Scripts']) && $Block['Render_Flags']['No_Scripts']) || (isset($Copied_Render_Flags['No_Scripts']) && $Copied_Render_Flags['No_Scripts']))
		$Add_Scripts = false;
	$Refresh = true;
	if ((isset($Block['Render_Flags']['No_Refresh']) && $Block['Render_Flags']['No_Refresh']) || (isset($Copied_Render_Flags['No_Refresh']) && $Copied_Render_Flags['No_Refresh']))
		$Refresh = false;
	$Preloader = true;
	if ((isset($Block['Render_Flags']['No_Preloader']) && $Block['Render_Flags']['No_Preloader']) || (isset($Copied_Render_Flags['No_Preloader']) && $Copied_Render_Flags['No_Preloader']))
		$Preloader = false;
	
	// TODO @feature-language: implement various wrapping HTML elements
	$Wrap_Element = &$_;$_ = 'span'; 
	unset($_);
	
	$Wrapping_ID_ = &$_;$_ = ''; 
	unset($_);
	
	$Wrapping_Classes = &$_;$_ = []; 
	unset($_);
	
	$Wrapping_Extras = &$_;$_ = ''; 
	unset($_);
	
	$Block_Kind = &$Block['Kind'];
	
	switch ($Block_Kind)
	{
		case 'Processed_Item_Chunk':
		{
			if (isset($Block['Render_Flags']['Item_Wrap_Element']))
				$Wrap_Element = &$Block['Render_Flags']['Item_Wrap_Element'];
			break;
		}
		case 'Processed_Iterator_Chunk':
		{
			if (isset($Block['Render_Flags']['Iterator_Wrap_Element']))
				$Wrap_Element = &$Block['Render_Flags']['Iterator_Wrap_Element'];
			break;
		}
		default:
		{
			if ($Block_Kind == 'Processed_Command_Chunk' && $Block['From'] == 'Property')
			{
				if (isset($Block['Render_Flags']['Wrap_Element']))
					$Wrap_Element = &$Block['Render_Flags']['Wrap_Element'];
				else
					$Wrap_Element = 'data';
			}
			break;
		}
	}
	
	// Set up Wrap_Parts
	$Wrap_Parts = array();
	$Wrap_Parts[] = &$Wrap_Element;
	
	$Wrap_Parts[] = &New_String('data-generator' . '=' . ('"' . 'Better' . '"'));
	
	// Check if block is a simple value (text/number) or an array of chunks
	switch ($Block_Kind)
	{
		// For pre-rendered blocks, Content is already set
		case 'Text_Chunk':
		case 'Number_Chunk':
		{
			break;
		}
		
		// Blank content for fase evaluations without else chunks
		case 'False_Evaluation_Without_Else_Chunk':
		{
			$Block['Content'] = &$_;$_ = ''; 
			unset($_);
			break;
		}
			
		
		// For processed chunks, render the content from their inner chunks
		case 'Processed_Block_Chunk':
		case 'Processed_Tag_Chunk':
		case 'Processed_Command_Chunk':
		case 'Processed_Error_Chunk':
		case 'Processed_Item_Chunk':
		case 'Processed_Iterator_Chunk':
		case 'Processed_Container_Chunk':
		{
			// Initialize results
			// Warning: $Content not by reference
			$Content = '';
			$Script = &$Block['Script'];
			
			// Begin wrap

			// Generate prefix, content, suffix, and script for each kind of block...
			
			switch ($Block['Kind'])
			{
				// Processed Items
				case 'Processed_Item_Chunk':
				case 'Processed_Iterator_Chunk':
				case 'Processed_Container_Chunk':
				{
					if (!$No_Wrap)
					{
						// Set wrap to true
						$Wrap = true;

						if (array_key_exists('Attachment_ID', $Block['Metadata']))
						{
							// TODO these don't seem to do anything...
							
							// Wrap as attachment type, if needed
// 							$Wrapping_ID = &$Block['Metadata']['Attachment_Type_Namespace'];
	
							// Wrap as attachment item, if needed
// 							$Wrapping_ID = &$Block['Metadata']['Attachment_Namespace'];
						}

						// Wrap iterator, item, or container
						$Wrapping_ID = &$Block['Metadata']['Namespace'];
						$Wrap_Parts[] = &New_String('id' . '=' . ('"' . $Wrapping_ID . '"'));
						
						switch ($Block['Kind'])
						{
							// Processed Items
							case 'Processed_Item_Chunk':
							{
								// Add kind to metadata
								$Wrap_Parts[] = &New_String('data-kind' . '=' . ('"' . 'Item' . '"'));
								
								// Add type to metadata
								$Wrap_Parts[] = &New_String('data-type' . '=' . ('"' . $Block['Metadata']['Cached_Type']['Alias'] . '"'));
// 								$Wrap_Parts[] = &New_String('data-type-name' . '=' . ('"' . $Block['Metadata']['Cached_Type']['Name'] . '"'));
								
								// Add template to metadata
								if (isset($Block['Metadata']['Cached_Template']))
								{
									$Wrap_Parts[] = &New_String('data-template' . '=' . ('"' . $Block['Metadata']['Cached_Template']['Alias'] . '"'));
// 									$Wrap_Parts[] = &New_String('data-template-name' . '=' . ('"' . $Block['Metadata']['Cached_Template']['Name'] . '"'));
								}
								
								// Add alias to metadata
								if (isset($Block['Metadata']['Alias']))
									$Wrap_Parts[] = &New_String('data-alias' . '=' . ('"' . $Block['Metadata']['Alias'] . '"'));
								
								// Add alias to metadata
								if (isset($Block['Metadata']['ID']))
									$Wrap_Parts[] = &New_String('data-id' . '=' . ('"' . $Block['Metadata']['ID'] . '"'));
								
								// Add item classes
								// TODO Rename "Render_Flags" to something like "Render_Parameters"
								if (isset($Block['Render_Flags']['Item_Classes']))
									$Wrapping_Classes[] = $Block['Render_Flags']['Item_Classes'];
									
								$Wrap_Parts[] = &New_String('data-duration' . '=' . ('"' . sprintf('%f', $Block['Metadata']['Duration']) . '"'));
								
								break;
							}
							case 'Processed_Iterator_Chunk':
							{
								// Add kind to metadata
								$Wrap_Parts[] = &New_String('data-kind' . '=' . ('"' . 'Iterator' . '"'));
								
								// Add type to metadata
								$Wrap_Parts[] = &New_String('data-type' . '=' . ('"' . $Block['Metadata']['Cached_Type']['Alias'] . '"'));
// 								$Wrap_Parts[] = &New_String('data-type-name' . '=' . ('"' . $Block['Metadata']['Cached_Type']['Name'] . '"'));
								
								// Add template to metadata
								if (isset($Block['Metadata']['Cached_Template']))
								{
									$Wrap_Parts[] = &New_String('data-template' . '=' . ('"' . $Block['Metadata']['Cached_Template']['Alias'] . '"'));
// 									$Wrap_Parts[] = &New_String('data-template-name' . '=' . ('"' . $Block['Metadata']['Cached_Template']['Name'] . '"'));
								}
								
								// Add item classes
								if (isset($Block['Render_Flags']['Iterator_Classes']))
								{
									$Wrapping_Classes[] = $Block['Render_Flags']['Iterator_Classes'];
								}
					
								if ($Block['From'] == 'Property')
								{
									// Add item type to metadata
									$Wrap_Parts[] = &New_String('data-parent-type' . '=' . ('"' . $Block['Parent_Item']['Cached_Specific_Type']['Alias'] . '"'));
									
									// Add item type to metadata
									$Wrap_Parts[] = &New_String('data-parent' . '=' . ('"' . $Block['Parent_ID'] . '"'));
									
									// Add property to metadata
									$Wrap_Parts[] = &New_String('data-property' . '=' . ('"' . $Block['Parent_Cached_Property']['Alias'] . '"'));
				// 					$Wrap_Parts[] = &New_String('data-property-name' . '=' . ('"' . $Block['Parent_Cached_Property']['Name'] . '"'));
									$Wrap_Parts[] = &New_String('data-type' . '=' . ('"' . $Block['Parent_Cached_Property']['Cached_Value_Type']['Alias'] . '"'));
								}
								
								$Wrap_Parts[] = &New_String('data-duration' . '=' . ('"' . sprintf('%f', $Block['Metadata']['Duration']) . '"'));
								
								break;
							}
							case 'Processed_Container_Chunk':
							{
								// Add kind to metadata
								$Wrap_Parts[] = &New_String('data-kind' . '=' . ('"' . 'Container' . '"'));
								
								break;
							}
						}
					}

					if ($Add_Scripts)
					{
						// Register attachments
						if (array_key_exists('Attachment_ID', $Block['Metadata']))
						{
							// Register attachment references...

							// Create attachment type reference.
							$Attachment_Iterator_Reference_Parts = &$_;$_ = [];
							unset($_);
							$Attachment_Iterator_Reference_Parts['Kind'] = &$_;$_ = 'Attachment_Iterator';
							unset($_);
							$Attachment_Iterator_Reference_Parts['Type_Alias'] = &$Block['Metadata']['Attachment_Type_Alias'];
							$Attachment_Iterator_Reference_Parts['Namespace'] = &$Block['Metadata']['Attachment_Type_Namespace'];
							$Attachment_Iterator_Reference_Parts['Parent_Namespace'] = &$Block['Metadata']['Parent_Namespace'];
							
							// Create attachment reference.
							$Attachment_Item_Reference_Parts = &$_;$_ = [];
							unset($_);
							$Attachment_Item_Reference_Parts['Kind'] = &$_;$_ = 'Attachment';
							unset($_);
							$Attachment_Item_Reference_Parts['ID'] = &$Block['Metadata']['Attachment_ID'];
							$Attachment_Item_Reference_Parts['Namespace'] = &$Block['Metadata']['Attachment_Namespace'];
							$Attachment_Item_Reference_Parts['Parent_Namespace'] = &$Block['Metadata']['Attachment_Type_Namespace'];
														
							// Register attachment references.
							$Next_Script = &$_;$_ = $Script . 'Jelly.References.Register('. json_encode($Attachment_Iterator_Reference_Parts) .');' . "\n";
							unset($_);
							$Script  = &$Next_Script;
							
							$Next_Script = &$_;$_ = $Script . 'Jelly.References.Register('. json_encode($Attachment_Item_Reference_Parts) .');' . "\n";
							unset($_);
							$Script = &$Next_Script;
						}
						
						// Register this item/iterator/container						
						{
							$Reference_Parts = &$_;$_ = [];
							unset($_);
						
							switch ($Block['Kind'])
							{
								case 'Processed_Item_Chunk':
								{
									$Reference_Parts['Kind'] = &$_;$_ = 'Item';
									unset($_);
									break;
								}
								case 'Processed_Iterator_Chunk':
								{
									$Reference_Parts['Kind'] = &$_;$_ = 'Iterator';
									unset($_);
									break;
								}
								case 'Processed_Container_Chunk':
								{
									$Reference_Parts['Kind'] = &$_;$_ = 'Container';
									unset($_);
									break;
								}
								default:
									// TODO should we ever reach this?
							}
												
							// Item information 
							if (isset($Block['Metadata']['Cached_Type']))
							{
								$Reference_Parts['Type_Name'] = &$Block['Metadata']['Cached_Type']['Alias'];
								$Reference_Parts['Type_Alias'] = &$Block['Metadata']['Cached_Type']['Alias'];
							}
							// TODO: do iterators have IDs?
							if (isset($Block['Metadata']['ID']))
								$Reference_Parts['ID'] = &$Block['Metadata']['ID'];
							
							if (isset($Block['Metadata']['Name']))
								$Reference_Parts['Name'] = &$Block['Metadata']['Name'];
							if (isset($Block['Metadata']['Alias']))
								$Reference_Parts['Alias'] = &$Block['Metadata']['Alias'];
						
							// Namespacing
							$Reference_Parts['Namespace'] = &$Block['Metadata']['Namespace'];
							if (array_key_exists('Attachment_Namespace', $Block['Metadata']))
								$Reference_Parts['Parent_Namespace'] = &$Block['Metadata']['Attachment_Namespace'];
							else
								$Reference_Parts['Parent_Namespace'] = &$Block['Metadata']['Parent_Namespace'];
						
							// Container
							if (isset($Block['Metadata']['From_Container']))
								$Reference_Parts['From_Container'] = &$Block['Metadata']['From_Container'];
							
							// Request
							if (isset($Block['Metadata']['From_Request']))
								$Reference_Parts['From_Request'] = &$Block['Metadata']['From_Request'];

							// Template 
							if (isset($Block['Metadata']['Cached_Template']))
							{
								$Reference_Parts['Template_Alias'] = &$Block['Metadata']['Cached_Template']['Alias'];
								$Reference_Parts['Template_ID'] = &$Block['Metadata']['Cached_Template']['ID'];
							}
						
							// Reference variables
							if (isset($Block['Metadata']['Variables']))
								$Reference_Parts['Variables'] = &$Block['Metadata']['Variables'];

							// Add No Refresh
							if (!$Refresh)
							{
								$Reference_Parts['No_Refresh'] = &$_;$_ = true;
								unset($_);
							}
							
							// Add No Loading
							if (!$Preloader)
							{
								$Reference_Parts['No_Preloader'] = &$_;$_ = true;
								unset($_);							
							}
												
							// Dunno
							// TODO - I think this is taken care of... 
							// TODO @feature-language: WHAT?
	// 						if (isset($GLOBALS['Path_Values_String']))
	// 							$Reference_Parts['Path_Values'] = &$GLOBALS['Path_Values_String'];
	// 						else
	// 							$Reference_Parts['Path_Values'] = &New_String('');
							
							$Next_Script = &$_;$_ = $Script . 'Jelly.References.Register('. json_encode($Reference_Parts) .');' . "\n";
							unset($_);
							$Script = &$Next_Script;	
						}
					}
					break;
				}
			}
			
			// If block doesn't have content already set (i.e. simple text/number blocks), render content of inner chunks
			foreach ($Block['Chunks'] as &$Chunk)
			{
				// Render the inner block (note: result chunk is stays in main result; this is the only time a rendered result is used)
				Render_Processed_Block($Chunk, $Copied_Render_Flags);
				
				// Check if chunk content is set (i.e. not by reference)
				if (isset($Chunk['Content']))
				{
					$Content .= $Chunk['Content'];
					unset($Chunk['Content']);
				}
				
				// Check if chunk script is set
				if (!isset($Chunk['Script']))
				{
					traverse($Chunk);
					throw new Exception('Caught a malformed Chunk!');
				}
				
				$Next_Script = &$_;$_ = $Script . $Chunk['Script'];
				unset($_);
				$Script = &$Next_Script;
				unset($Chunk['Script']);
				
				// Pass up headers
				foreach ($Chunk['Headers'] as $Header_Lookup => &$Header)
					$Block['Headers'][] = &$Header;
			}
			
			if ($Block_Kind == 'Processed_Command_Chunk' && $Block['From'] == 'Property')
			{
				if (!$No_Wrap)
				{
					// Set wrap to true
					$Wrap = true;
					
					// Add kind to metadata
					$Wrap_Parts[] = &New_String('data-kind' . '=' . ('"' . 'Property' . '"'));
					
					// Add item type to metadata
					$Wrap_Parts[] = &New_String('data-parent-type' . '=' . ('"' . $Block['Parent_Item']['Cached_Specific_Type']['Alias'] . '"'));
					
					// Add property to metadata
					$Wrap_Parts[] = &New_String('data-property' . '=' . ('"' . $Block['Parent_Cached_Property']['Alias'] . '"'));
// 					$Wrap_Parts[] = &New_String('data-property-name' . '=' . ('"' . $Block['Parent_Cached_Property']['Name'] . '"'));
					$Wrap_Parts[] = &New_String('data-type' . '=' . ('"' . $Block['Parent_Cached_Property']['Cached_Value_Type']['Alias'] . '"'));
					
					
					// Add item type to metadata
					$Wrap_Parts[] = &New_String('data-parent' . '=' . ('"' . $Block['Parent_ID'] . '"'));
				}
				else
				{
				}
			}
			
			// Store resulting content
			// TODO hack test to see if things are set up properly above
			if ($Wrap)
			{
				// Merge wrapping classes
				$Wrap_Class_String = &$_;$_ = implode(' ', $Wrapping_Classes);
				unset($_);
				$Wrap_Parts[] = &New_String('class' . '=' . ('"' . $Wrap_Class_String . '"'));
				
// 				$Block['CSS_Class'] = 'Error';
// 				if (isset($Block['CSS_Style']))
// 					$Wrap_Extras = &New_String($Wrap_Extras . ' ' . 'style' . '=' . '"' . $Block['CSS_Style'] . '"' . ' ');
// 				if (isset($Block['CSS_Class']))
// 					$Wrap_Extras = &New_String($Wrap_Extras . ' ' . 'class' . '=' . '"' . $Block['CSS_Class'] . '"' . ' ');
				
				// Generate wrap prefix and suffix
				$Wrap_Prefix = &$_;$_ = '<' . implode(' ', $Wrap_Parts) . '>';
				unset($_);
				$Wrap_Suffix = &$_;$_ = '</' . $Wrap_Element . '>';
				unset($_);
				
				// Wrap content
				$Block['Content'] = &$_;$_ = $Wrap_Prefix . $Content . $Wrap_Suffix;
				unset($_);
			}
			else
			{
				$Block['Content'] = &$Content;
			}
			
			// Store resulting script
			$Block['Script'] = &$Script;
		
			break;
		}
		
		// Chunks we shouldn't expect`
		case 'Block_Chunk':
			throw new Exception('Encountered block while rendering a block\'s chunks');
			break;
		case 'Tag_Chunk':
			throw new Exception('Encountered tag block while rendering a block\'s chunks. Should have been processed.');
			break;
		case 'Item_Chunk':
			throw new Exception("Trying to render an Item_Chunk");
			// TODO @core-language: this is only for items returned by references, so not sure what to render here
			break;
		
		// Unnknown block chunk type
		default:
			throw new Exception('Unknown block chunk type: ' . $Block['Kind']);
	}
}

?>