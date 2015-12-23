<?php

// Clone Dot Tree Without Base
// TODO: check if this is used at all. 

function &Clone_Dot_Tree_Without_Base(&$Tree)
{
	// Check if current tree is an operator
	if (strtolower($Tree['Kind']) == 'operator')
	{
		// Check if tree's left operand is also a tree (i.e. it continues to the left)
		if (strtolower($Tree['Terms'][1]['Kind']) == 'operator')
		{
			// Clone the current tree and return
			$New_Tree = &New_Tree();
			$New_Tree['Kind'] = &$Tree['Kind'];
			$New_Tree['Value'] = &$Tree['Value'];
			$New_Tree['Terms'][] = &Clone_Dot_Tree($Tree['Terms'][0]);
			$New_Tree['Terms'][] = &Clone_Dot_Tree_Without_Base($Tree['Terms'][1]);
			return $New_Tree;
		}
		else
		{
			// Otherwise, return the right operand, thus bypassing the current operator
			return Clone_Dot_Tree($Tree['Terms'][0]);
		}
	}
	else
	{
		// If tree is not an operator, return it as-is
		return $Tree;
	}
}

?>