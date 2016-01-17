<?php

function &Is_Item(&$Value)
{
	if (!is_array($Value) || (is_array($Value) && $Value['Kind'] != 'Item'))
	{
		$Value = &$_;$_ = false;
		unset($_);
	}
	else
	{
		$Value = &$_;$_ = true;
		unset($_);
	}
	return $Value;
}

?>