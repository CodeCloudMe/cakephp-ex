Jelly.Interface.Bubble_Event_Protection = function(Event_Name)
{
	// Creates a named variable that erases itself after a set period, which helps with cross-browser event propogation control.	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	// If the named event bubble is being blocked, return false.
	if (Jelly.Interface.Event_Protection[Event_Name])
		return false;

	// Otherwise, begin blocking event bubble, and stop blocking it after a timeout period, and return true.
	// TODO - manage timeout and delete it
	else
	{	
		// Store event name in event bubble list.
		// Stop blocking event bubble after a timeout period.
		Jelly.Interface.Event_Protection[Event_Name] = 		
			Jelly.Interface.Timeout(
					function()
						{	
							if (Debug)
								Jelly.Debug.Log("Clear Event Protection:'" + Event_Name+ "'"); 
	
							// Delete event name from event protection list.
							delete Jelly.Interface.Event_Protection[Event_Name];
						}, 
					Jelly.Interface.Event_Protection_Period
				);
		return true;
	}
	
	if (Debug)
		Jelly.Debug.End_Group("");
};