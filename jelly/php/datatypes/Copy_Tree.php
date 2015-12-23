<?php

// Copy Tree

function &Copy_Tree(&$Original_Tree)
{
	$Tree = &New_Tree();
	
	$Tree['Kind'] = &$Original_Tree['Kind'];
	$Tree['Value'] = &$Original_Tree['Value'];
	
	// If the tree is an operator, copy its terms
	if (strtolower($Original_Tree['Kind']) == 'operator')
		foreach ($Original_Tree['Terms'] as &$Original_Term)
			$Tree['Terms'][] = &$Original_Term;
	
	return $Tree;
}

?>