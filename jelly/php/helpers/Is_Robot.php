<?php

function Is_Robot()
{
	// TODO: clean up
	$HTTP_User_Agent = &$_SERVER['HTTP_USER_AGENT'];
	if (stripos($HTTP_User_Agent, 'bot') !== false
		|| stripos($HTTP_User_Agent, 'google') !== false
		|| stripos($HTTP_User_Agent, 'facebook') !== false
		|| stripos($HTTP_User_Agent, 'spider') !== false)
		return true;
	return false;
}

?>