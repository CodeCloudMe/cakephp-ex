<?php

function &Is_Not_Set(&$Value)
{
	if (!is_array($Value) || (is_array($Value) && $Value['Kind'] != 'Not_Set'))
		return New_Boolean(false);
	else
		return New_Boolean(true);
}

?>