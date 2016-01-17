<?php

function Traverse($Item, $Levels = 10, $Level = 0, $Printed_Wrapper = false, &$Last_Extra = 0, &$ID = 0)
{
	// Setup Skipped Properties
	$Skip_Properties = array(
		'cached_specific_child_types',
		'all_cached_child_types',
		
		'cached_specific_properties',
		'all_cached_properties',
		'cached_property_lookup',
		'cached_specific_property_lookup',
		
		'cached_specific_templates',
		'all_cached_templates',
		'cached_template_lookup',
		'all_cached_types',
		'cached_type_tree',
		'cached_type_lookup',
		'references',
		
		'package', 
		'status',
		'parent_item'
		);
	
	// Print Open Wrapper
	global $Printed_Style;
	if (!$Printed_Style)
	{
		echo "<style>\n.Traverse {padding: 0px; margin: 0px; margin-bottom: 5px; background-color: white; font-family: Arial; font-size: 15px;}\n.Traverse div {padding: 0px; margin: 0px; padding-left: 20px; overflow: hidden;}\n.Traverse .Head {padding-left: 0px; color: #aaaaaa; font-weight: bold;}\ni {color: #aaaaaa;}\n.do {}\n.dc {height: 13px;}\n</style>\n";
		$Printed_Style = true;
	}
	if (!$Printed_Wrapper)
	{
		echo "<div class=\"Traverse\">\n";
		$Debug_Backtrace = debug_backtrace(false, 2);
		echo "<div class=\"Head\">" . $Debug_Backtrace[1]['function'] . " (line " . $Debug_Backtrace[0]['line'] . ")</div>";
	}
	
	// Check if we have reached our maximum level count
	$Levels--;
	if ($Levels)
	{
		// Check if item is an array/object
		if (is_array($Item) || is_object($Item))
		{
			// Print arrays/objects recursively
			
			// Check if item has already been traversed
			if (isset($Item['Traverse_Visited']))
			{
				Print_Indented("<span class=\"da\"><i>", "RECURSION", "</i></span>", $Level, $Last_Extra);
				return;
			}
			else
			{
				if(is_array($Item))
					Print_Indented("<div><i>", "ARRAY", "</i></div>", $Level, $Last_Extra);
				else
					Print_Indented("<div><i>", "OBJECT Type: " . get_class($Item), "</i></div>", $Level, $Last_Extra);
		
				// Print Array Open Bracket
				Print_Indented("<div>", "[", "", $Level, $Last_Extra);
				
				$Level++;
				foreach($Item as $Key => &$Value)
				{
					// Print Key
					Print_Indented("<div class=\"do\"><b>", $Key, "</b>", $Level, $Last_Extra);
					
					// Print Value
					if (in_array(strtolower($Key), array("cached_base_type", "cached_parent_type", "cached_specific_type", "cached_type", "cached_property", "cached_value_type", "cached_attachment_type", "cached_attachment_type_forward_property", "cached_attachment_type_reverse_property")))
					{
						if (is_array($Value))
							Print_Indented("<i>", "[" . $Value["Alias"] . "]", "</i>", $Level + 1, $Last_Extra);
						else
							Print_Indented("<i>", "[" . $Value . "]", "</i>", $Level + 1, $Last_Extra);
					}
					elseif (strtolower($Key) == 'references')
					{
						Print_Indented("<i>", count($Value), "</i>", $Level + 1, $Last_Extra);
					}
					elseif (in_array(strtolower($Key), $Skip_Properties))
					{
						$Value_String = "";
						if (strtolower($Key) == 'package' || strtolower($Key) == 'status' || strtolower($Key) == 'parent_item')
						{
							if (is_array($Value))
							{
								if (array_key_exists('Data', $Value) && array_key_exists('Alias', $Value['Data']))
									$Value_String = "{" . $Value['Data']['Alias'] . "}" . ", ";
							}
							else
								$Value_String = "{" . $Value . "}" . ", ";
						}
						else
						{
							foreach ($Value as &$Value_Item)
							{
								if (isset($Value_Item["Alias"]))
									$Value_String .= "{" . $Value_Item["Alias"] . "}" . ", ";
								elseif (isset($Value_Item["Namespace"]))
									$Value_String .= "{" . $Value_Item["Namespace"] . "}" . ", ";
								elseif (isset($Value_Item["Property"]) && isset($Value_Item["Property"]["Alias"]))
									$Value_String .= "{" . $Value_Item["Property"]["Alias"] . "}" . ", ";
							}
						}
						Print_Indented("<i>", $Value_String, "</i>", $Level + 1, $Last_Extra);
					}
					// TODO - There was a mysqli bug, which I'm not sure is general for objects that end up in our arrays, but in any case, this fixes it for now.
					elseif (!is_object($Value))
					{
						$Item["Traverse_Visited"] = true;
						Traverse($Value, $Levels - 1, $Level + 1, true, $Last_Extra);
						unset($Item["Traverse_Visited"]);
					}
					echo "</div>";
					$Last_Extra += 6;
				}
				
				// Print Array Close Bracket
				Print_Indented("</div><div>", "]", "</div>", $Level, $Last_Extra);
			}
		}
		else
		{
			// Print Simple Values
			if ($Item === null)
				Print_Indented("<span class=\"da\"><i>", "NULL", "</i></span>", $Level, $Last_Extra);
			elseif ($Item === 0)
				Print_Indented("<span class=\"da\"><i>", "0", "</i></span>", $Level, $Last_Extra);
			elseif($Item === true)
				Print_Indented("<span class=\"da\"><i>", "TRUE", "</i></span>", $Level, $Last_Extra);
			elseif($Item === false)
				Print_Indented("<span class=\"da\"><i>", "FALSE", "</i></span>", $Level, $Last_Extra);
			elseif($Item === "")
				Print_Indented("<span class=\"da\"><i>", "EMPTY STRING", "</i></span>", $Level, $Last_Extra);
			elseif(is_resource($Item))
				Print_Indented("<span class=\"da\"><i>", "RESOURCE", "</i></span>", $Level, $Last_Extra);
			else
				Print_Indented("<span class=\"da\">", htmlspecialchars($Item), "</span>", $Level, $Last_Extra);
		}
	
		//Print Close Wrapper
		if (!$Printed_Wrapper)
		{
			echo "</div>\n";
		}
	}
}

function Print_Indented($Start, $Content, $End, $Level, &$Last_Extra)
{
	echo $Start . str_repeat(" ", $Level * 3 + 35 - $Last_Extra - strlen($Start)) . $Content . "\n" . $End;
	$Last_Extra = strlen($End);
}

?>