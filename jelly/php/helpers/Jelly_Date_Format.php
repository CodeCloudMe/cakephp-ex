<?php

// Date Format
function &Jelly_Date_Format(&$Date_Value, $Format_String)
{	
	global $Standard_Date_Format_String_Mappings;
	global $Standard_Date_Format_Commands;
	
	if (!is_numeric($Date_Value))
		throw new Exception('Invalid date value:' . ' ' . $Date_Value);
		
	switch(strtolower($Format_String))
	{
		// Non-Standard Mappings
		case 'short_period':
		case 'short_period_name':
			$Formatted_Date_Value = &New_String(substr(date($Standard_Date_Format_Commands['Period']['Name']['Long'], $Date_Value)), 0, 1);
			break;

		case 'unix_value':
			$Formatted_Date_Value = &$Date_Value;
			break;
			
		case 'relative_time':
			// Output relative time
			{
				// TODO: Check attribution
				// TODO: Loop might be better.
				// Credit: Gilbert Pellegrom, http://gilbert.pellegrom.me/php-relative-time-function/
				$Post_Fix = ' ago';
				$Fallback = 'F Y';
				$Difference = time() - $Date_Value;

				if ($Difference < 60)
					$Formatted_Date_Value = $Difference . ' second'. ($Difference != 1 ? 's' : '') . $Post_Fix;
			
				else
				{
					$Difference = round($Difference/60);
				
					if($Difference < 60) 
						$Formatted_Date_Value = $Difference . ' minute'. ($Difference != 1 ? 's' : '') . $Post_Fix;
					
					else
					{
						$Difference = round($Difference/60);
						if($Difference < 24) 
							$Formatted_Date_Value = $Difference . ' hour'. ($Difference != 1 ? 's' : '') . $Post_Fix;
					
						else
						{
							$Difference = round($Difference/7);

							if($Difference < 7) 
								$Formatted_Date_Value = $Difference . ' day'. ($Difference != 1 ? 's' : '') . $Post_Fix;
							else
							{
								$Difference = round($Difference/4);
							
								if($Difference < 4) 
									$Formatted_Date_Value = $Difference . ' week'. ($Difference != 1 ? 's' : '') . $Post_Fix;
								else
								{
									$Difference = round($Difference/12);

									if($Difference < 12) 
										$Formatted_Date_Value = $Difference . ' month'. ($Difference != 1 ? 's' : '') . $Post_Fix;
									
									else
									{
										$Formatted_Date_Value = &New_String($Difference . ' month'. ($Difference != 1 ? 's' : '') . $Post_Fix);
									}
								}
							}
						}
					}
				}
			}
			break;

		// Standard Mappings
		default:			
			if (array_key_exists(strtolower($Format_String), $Standard_Date_Format_String_Mappings))
				$Formatted_Date_Value = &New_String(date($Standard_Date_Format_String_Mappings[strtolower($Format_String)], $Date_Value));
			else
				throw new Exception('Unknown date format string:' . ' ' .  $Format_String);				
			break;
	}
	return $Formatted_Date_Value;	
}

?>