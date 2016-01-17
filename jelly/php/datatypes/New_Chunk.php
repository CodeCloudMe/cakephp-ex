<?php

function &New_Chunk($Kind)
{
	$Array = &$_;$_ = []; 
	unset($_);
	
	$Array['Kind'] = &$_;$_ = $Kind;
	unset($_);
	
	$Array['Script'] = &$_;$_ = '';
	unset($_);
	
	$Array['Headers'] = &$_;$_ = []; 
	unset($_);

	return $Array;
}

?>