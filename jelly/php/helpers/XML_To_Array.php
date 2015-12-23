<?php

class XML_To_Array
{
	var $Parser;
	var $Items;
	var $Depth;
	var $Item;
	var $Type;
	var $Value;
	
	function Parse_XML($XML_Data)
	{
		$this->Items = array();
		$this->Depth = 0;
		
		$this->Parser = xml_parser_create();
		
        xml_set_object($this->Parser, $this);
		xml_set_element_handler($this->Parser, "Start_Element", "End_Element");
		xml_set_character_data_handler($this->Parser, "Character_Data");
		xml_parser_set_option($this->Parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($this->Parser, XML_OPTION_SKIP_WHITE, true);
		
		if (!xml_parse($this->Parser, $XML_Data, true))
			die(sprintf("XML error: " . xml_error_string(xml_get_error_code($this->Parser)) . " at line " . xml_get_current_line_number($this->Parser)));
		xml_parser_free($this->Parser);
		
		return $this->Items;
	}
	
	function Start_Element($Parser, $Element_Name, $Element_Attributes)
	{
		$this->Depth++;
		
		switch ($this->Depth)
		{
			case 2:
				$this->Element = array();
				$this->Type = $Element_Name;
				break;
			case 3:
				$this->Property = $Element_Name;
				
				unset ($this->Value);
				$this->Value = "";
				
				if (isset($this->Item[$this->Property]))
				{
					if (!is_array($this->Item[$this->Property]))
						$this->Item[$this->Property] = array($this->Element[$this->Property]);
					$this->Item[$this->Property][] = &$this->Value;
				}
				else
					$this->Item[$this->Property] = &$this->Value;
				
				break;
		}
	}
	function End_Element($Parser, $Element_Name)
	{
		switch ($this->Depth)
		{
			case 2:
				$this->Items[$this->Type][] = $this->Item;
				unset ($this->Item);
				break;
			case 3:
				unset($this->Value);
				break;
		}
		
		$this->Depth--;
	}
	function Character_Data($Parser, $Character_Data)
	{
		global $Parser_Depth;
		
		switch ($this->Depth)
		{
			case 3:
				$this->Value .= $Character_Data;
				break;
		}
	}
}

?>