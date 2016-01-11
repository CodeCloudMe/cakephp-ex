<?php

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