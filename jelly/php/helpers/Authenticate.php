<?php

// Authenticate
// TODO - return reference to boolean value??!?
function Authenticate($Team_Alias, $Parameters)
{
	$Team_Lookup = strtolower($Team_Alias);
	
	// If the session is in preview mode and this isn't a forced lookup, return false,
	if (
		isset($GLOBALS['Current_Session_Item']) && 
		$GLOBALS['Current_Session_Item']['Data']['Preview_Mode'] && 
		!$Parameters['ignore_preview_mode']
	)
		return false;
		
	// Otherwise, authenticate from cache.
	else
		return(in_array($Team_Lookup, $GLOBALS['Cached_Authentications']));
}

?>