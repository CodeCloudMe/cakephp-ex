<?php

	function Get_Remote_Content($URL, $Username = null, $Password = null, $Post_Values = null)
	{
		// Initialize Handler
		$CURL_Handler = curl_init();

		// Set URL
		curl_setopt($CURL_Handler, CURLOPT_URL, $URL);
	
		// Set authentication
		if ($Username || $Password)
			curl_setopt($CURL_Handler, CURLOPT_USERPWD, $Username . ':' . $Password);
	
		// Set post values
		if ($Post_Values)
		{
			$Post_Values_Query = http_build_query($Post_Values);
			curl_setopt($CURL_Handler, CURLOPT_POST, 1);
			curl_setopt($CURL_Handler, CURLOPT_POSTFIELDS, $Post_Values_Query);
		}

		// Make request
		curl_setopt($CURL_Handler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($CURL_Handler, CURLOPT_USERAGENT, 'better/1');
		curl_setopt($CURL_Handler, CURLOPT_FOLLOWLOCATION, true);	
		curl_setopt($CURL_Handler, CURLOPT_HEADER, true);

		$Response = curl_exec($CURL_Handler);
		
		$Header_Size = curl_getinfo($CURL_Handler, CURLINFO_HEADER_SIZE);
		$Header = substr($Response, 0, $Header_Size);
		$Body = substr($Response, $Header_Size);	
        
        // From documentation for http_parse_headers
		$Header_Array = array();
        $Header_Key = '';        
        foreach(explode("\n", $Header) as $i => $h) 
        {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($Header_Array[$h[0]]))
                    $Header_Array[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]])) {
                    $Header_Array[$h[0]] = array_merge($Header_Array[$h[0]], array(trim($h[1])));
                }
                else {
                    $Header_Array[$h[0]] = array_merge(array($Header_Array[$h[0]]), array(trim($h[1])));
                }

                $Header_Key = $h[0];
            }
            else { 
                if (substr($h[0], 0, 1) == "\t")
                    $Header_Array[$Header_Key] .= "\r\n\t".trim($h[0]);
                elseif (!$key) 
                    $Header_Array[0] = trim($h[0]); 
            }
        }
        
		$Response_Array = &New_Array();
		$Response_Array['Headers'] = $Header_Array;
		$Response_Array['Body'] = $Body;
		
			
		// Check errors
		$CURL_HTTP_Code  = curl_getinfo($CURL_Handler, CURLINFO_HTTP_CODE);
		$Response_Array['Response_Code'] = $CURL_HTTP_Code;
	
		// Close connection		
		curl_close($CURL_Handler);
	
		// Pass error
		// TODO - handle better.
		if (!$CURL_HTTP_Code == 200)
		{	
			$Response_Array['Body'] = 'Get Remote Content Error' . ' ' .  '(' . $CURL_HTTP_Code . ')';
		}
		
		return $Response_Array;
	}
	
?>