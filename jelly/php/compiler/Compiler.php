<?php

// Includes...
// TODO - couldn't figure out why, but including these files was breaking pictures, but writing them inline was not.

function Compile_Javascript()
{
	// Recursively compile /javascript/development into a single file called /javascript/Jelly (Compiled).js
	// TODO - used no references but should be good.
	
	// Open Jelly (Compiled).js
	$Compiled_Javascript_Path = 'jelly/javascript/' . 'Jelly (Compiled)' . '.' .'js';
	$Compiled_Javascript_File = fopen($Compiled_Javascript_Path, 'w');
	
	// Examine every file in this directory	
	$Base_Directory_Path = 'jelly/javascript/development';
	$Compiled_Javascript_Array = Parse_Directory_Into_Compiled_Javascript_Array($Base_Directory_Path);
	$Compiled_Javascript_Content = implode("\n" . "\n", $Compiled_Javascript_Array);
	fwrite($Compiled_Javascript_File, $Compiled_Javascript_Content);
}

function Parse_Directory_Into_Compiled_Javascript_Array($Current_Directory_Path)
{
	$Compiled_Javascript_Array = []; 
	
	$Current_Filenames = scandir($Current_Directory_Path);
	
	usort($Current_Filenames, 'Get_Difference_In_String_Length');

	foreach ($Current_Filenames as $Current_Filename)
	{
		// Ignore unhelpful values
		if (!in_array($Current_Filename, array(".","..")))
		{
			$Current_File_Path = $Current_Directory_Path . '/' . $Current_Filename;
			
			// If this is a file			
			if (!is_dir($Current_File_Path))
			{	
				$Current_File_Extension = substr(strrchr($Current_File_Path, "."), 1);
				if (strtolower($Current_File_Extension) == 'js')
				{
					// Remove head.load(...);
					$Cleaner = "/head.load\([^);]*\)/i";
//					echo ($Current_File_Path) . "<br/>\n";
					$Current_File_Content = file_get_contents($Current_File_Path);
					$Current_File_Cleaned_Content = preg_replace($Cleaner, '', $Current_File_Content);
					
					// Add cleaned content to array
					$Compiled_Javascript_Array[] = $Current_File_Cleaned_Content;
				}
			}
		}
	}
	
	foreach ($Current_Filenames as $Current_Filename)
	{
		// Ignore unhelpful values
		if (!in_array($Current_Filename, array(".","..")))
		{
			$Current_File_Path = $Current_Directory_Path . '/' . $Current_Filename;
			
			// If this is a directory			
			if (is_dir($Current_File_Path))
			{	
				$Compiled_Javascript_Array = array_merge($Compiled_Javascript_Array, Parse_Directory_Into_Compiled_Javascript_Array($Current_File_Path));
			}
		}
	}
	
	
	return $Compiled_Javascript_Array;
}

function Get_Difference_In_String_Length($String, $Other_String)
{
	return strlen($String) - strlen($Other_String);
}

function Compile_Styles()
{
	require_once "jelly/php/libraries/lessphp/lessc.inc.php";
	
	// TODO - this wasn't thought out, but I suppose it should work.
	$Original_Site_Styles_Path = 'http://' . $_SERVER['HTTP_HOST'] . '/' . 'Less_Styles';
	$Original_Jelly_Styles_Path ='jelly/css/' . 'Jelly' . '.' . 'less';
	
	$Compiled_Styles_Path = 'jelly/css/' . 'Compiled' . '.' . 'css';

	try
	{
		$Less = new lessc;

		$Original_Site_Style = file_get_contents($Original_Site_Styles_Path);
		$Compiled_Style = $Less->compile($Original_Site_Style);
		
		$Original_Jelly_Style = file_get_contents($Original_Jelly_Styles_Path);
		$Compiled_Style .= $Less->compile($Original_Jelly_Style);
		
		file_put_contents($Compiled_Styles_Path, $Compiled_Style);
	}
	
	catch (Exception $Compile_Exception)	
	{
		echo "Less Compile Error: " . $Compile_Exception->getMessage();
	}
}
?>