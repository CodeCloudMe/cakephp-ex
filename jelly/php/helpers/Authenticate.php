<?php

// Authenticate
// TODO - return reference to boolean value??!?
function Authenticate($Team_Lookup, $Parameters)
{
	// Check for hard-set admin status
	// TODO - this todo is to be removed ;) don't ask.
	// TODO: to remove
	if (strtolower($Team_Lookup) == "admin" && $GLOBALS['Admin'])
		return true;
		
	$Evaluation = &New_Boolean(false);	
		
	// Check authenticated teams
	// TODO - is this a thing? I can't find a reference anywhere in the code.  If so, delete this probable security risk.
	if (isset($GLOBALS['Authenticated_Teams'][strtolower($Team_Lookup)]))
		return $GLOBALS['Authenticated_Teams'][strtolower($Team_Lookup)];
		
	// Get user	
	if (isset($GLOBALS['Current_Session_Item']))
	{	
		// If the session is in preview mode and this isn't a forced lookup, return false, otherwise, authenticate 
		$Current_Session_Item = &$GLOBALS['Current_Session_Item'];
		$Preview_Mode = &$Current_Session_Item['Data']['Preview_Mode'];
		if(!$Preview_Mode || $Parameters['ignore_preview_mode'])
		{
			$User_Command_String = &New_String('User');
			$User_Command = &Parse_String_Into_Command($User_Command_String);
			// TODO HACK for memory reasons
				$Memory_Stack_Reference = null;
		
			$User_Item = &Get_Value($GLOBALS['Current_Session_Item'], $User_Command, $Memory_Stack_Reference);
	
			// Check if user is logged in	
			if (Is_Item($User_Item) && $User_Item['End_Of_Results'] === false)
			{
				// Get team items
				// TODO - this is a slow approach..., but it matches our current "user when you need it" approach.
				$Team_Command_String = &New_String('Team');
				$Team_Command = &Parse_String_Into_Command($Team_Command_String);
				$Team_Item = &Get_Value($User_Item, $Team_Command, $Memory_Stack_Reference);

				// Check team items
				while ($Team_Item['End_Of_Results'] === false)
				{
					if ($Team_Item['Data']['Alias'] == $Team_Lookup)
					{
						$Evaluation = &New_Boolean(true);
						break;
					}
					Move_Next($Team_Item);
				}
			}
		}
	}
	
	return $Evaluation;
	
	// TODO  - some cache below code that seems obsolete but I didn't delete.
	/*	
	$User_Role_Command_String = 'Role ' . $Role_Lookup;
	$User_Role_Command = &Parse_String_Into_Command($User_Role_Command_String);
	
	// Get the simple value from the above complex value
	$Role_Item = &User_Item($Complex_Item, $User_Role_Command);
	
	// Check, cache in authenticated roles and return
	if (!$Role_Item["End_Of_Results"])
	{
		$GLOBALS['Authenticated_Roles'][strtolower($Role)] = true;
		return true;
	}
	else
	{
		$GLOBALS['Authenticated_Roles'][strtolower($Role)] = false;
		return false;
	}
	*/
}

?>