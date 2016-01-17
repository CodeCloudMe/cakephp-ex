<?php

// Authenticate
function &Get_Authentications()
{
	$Authentications = &$_; 
	$_ = Array();
	unset($_);
	
	// If Superuser, add admin, superuser
	if ($GLOBALS['Superuser'])
		array_push($Authentications, 'admin', 'superuser');
		
	// Get user from session
	if (isset($GLOBALS['Current_Session_Item']))
	{	
		// TODO HACK for memory reasons
		$Memory_Stack_Reference = null;	

		$Current_Session_Item = &$GLOBALS['Current_Session_Item'];
		$User_Command_String = &New_String('User');
		$User_Command = &Parse_String_Into_Command($User_Command_String);
		$User_Item = &Get_Value($GLOBALS['Current_Session_Item'], $User_Command, $Memory_Stack_Reference);

		// Check if user is logged in	
		if (Is_Item($User_Item) && $User_Item['End_Of_Results'] === false)
		{
			// Get user's teams
			// TODO - this is a slow approach..., but it matches our current "user when you need it" approach.
			$Team_Command_String = &New_String('Team');
			$Team_Command = &Parse_String_Into_Command($Team_Command_String);
			$Team_Item = &Get_Value($User_Item, $Team_Command, $Memory_Stack_Reference);

			// TODO - the where clause could handle this better, if we support that yet.
			while ($Team_Item['End_Of_Results'] === false)
			{	
				// Add this team to cache
				$Authentications[] = strtolower($Team_Item['Data']['Alias']);
				
				// If superuser, add admin too.
				if (strtolower($Team_Item['Data']['Alias']) != 'superuser')
					$Authentications[] = 'admin';
					
				Move_Next($Team_Item);
			}
		}
	}
	
	// Return authentications 
	return $Authentications;
}

?>