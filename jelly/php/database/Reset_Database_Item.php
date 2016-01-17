<?php

// Reset Item

function Reset_Item(&$Item)
{
	// TODO - not sure if necessary -- but the manual said these are freed "end of script" rather than at object destruction. so they're in here in case.
	if (array_key_exists('Result', $Item))
		mysqli_free_result($Item['Result']);
	
	// Localize variables
	$Database = &$Item['Database'];
	
	// Run database query
	$Item['Result'] = &Query($Database, $Item['Original_Query']);
	
	// Initialize item index and end-of-results
	$Item['Index'] = -1;
	$Item['End_Of_Results'] = false;
	
	// Load first row
	Move_Next($Item);
}

?>