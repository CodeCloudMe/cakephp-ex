<?php

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
	
?>