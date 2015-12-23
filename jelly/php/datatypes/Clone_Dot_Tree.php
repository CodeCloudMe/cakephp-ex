<?php

// Clone Dot Tree Without Base
// TODO: check if this is used at all. 

function &Clone_Dot_Tree(&$Tree)
{
	// Check if current tree is an operator
	if (strtolower($Tree['Kind']) == 'operator')
	{
		// Clone the current tree and return
		$New_Tree = &New_Tree();
		$New_Tree['Kind'] = &$Tree['Kind'];
		$New_Tree['Value'] = &$Tree['Value'];
		$New_Tree['Terms'][] = &Clone_Dot_Tree($Tree['Terms'][0]);
		$New_Tree['Terms'][] = &Clone_Dot_Tree($Tree['Terms'][1]);
		return $New_Tree;
	}
	else
	{
		// If tree is not an operator, return it as-is
		return $Tree;
	}
}

?>