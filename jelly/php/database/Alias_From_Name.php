<?php

function &Alias_From_Name($Value)
{
	// TODO - delete

	// Replace groups of punctuation with an underscore
	$Result = preg_replace('/[( )\-_\.]+/', '_', $Value);
	
	// Remove non alphanumerics/underscores
	$Result = preg_replace('/[^_[:alnum:]]/', '', $Result);
	
	// Trim trailing or leading underscores
	$Result = trim($Result, '_');
	
	// Retrn result
	return $Result;
}

?>