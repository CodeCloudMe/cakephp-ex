<?php

function &Geocode($Address)
{
    // Prepare geocoding URL
    $Geocode_URL = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=" . urlencode($Address);
 	
    // Get and decode the response
    $Geocode_Response_JSON = file_get_contents($Geocode_URL);
    $Geocode_Response = json_decode($Geocode_Response_JSON, true);
 	
    // Check if response is OK
    if ($Geocode_Response['status'] == 'OK')
    {
        // Get first geocode result data
        $First_Result = &$Geocode_Response['results'][0];
        $Result = array();
        $Result['lat'] = &$First_Result['geometry']['location']['lat'];
        $Result['lng'] = &$First_Result['geometry']['location']['lng'];
        $Result['Address'] = &$First_Result['formatted_address'];
         
        // Check if data includes the position
        if ($Result['lat'] && $Result['lng'])
        	return $Result;
        else
            return New_Boolean(false);
    }
    else
        return New_Boolean(false);
}

?>