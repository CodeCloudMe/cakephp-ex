<?php

function &New_Processed_Chunk($Kind)
{
	$Array = &New_Chunk($Kind);
	$Array['Render_Flags'] = &New_Array();
	return $Array;
}

?>