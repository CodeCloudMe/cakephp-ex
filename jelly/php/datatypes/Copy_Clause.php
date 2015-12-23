<?php

// Copy Clause

function &Copy_Clause(&$Original_Clause)
{
	$Clause = &New_Clause();
	
	// Copy clause tree if it is set (i.e. if there were clause terms)
	if (isset($Original_Clause['Tree']))
		$Clause['Tree'] = &Copy_Tree($Original_Clause['Tree']);
	
	return $Clause;
}

?>