// TODO: DElete
/* 
Jelly.Handlers.Cancel_Event = function(Event_Name, Timeout)
{
	// Creates a named variable that erases itself after a certain time, which helps with cross-browser event propogation control.	
	// TODO: Maybe this should be our framework.
	// TODO: This is based on bubble, but maybe we can do build an untimed one as well in the same function.

	var Debug = false && Jelly.Debug.Debug_Mode;

	if (Debug)
		Jelly.Debug.Log("Start Catching '" + Event_Name + "'");
		
	Jelly.Interface.Event_Bubbles[Event_Name] = true;
	
	Jelly.Interface.Timeout(function(){
	
		if (Debug)
			Jelly.Debug.Log("Stop Catching '" + Event_Name+ "'"); 
			
		delete Jelly.Interface.Event_Bubbles[Event_Name];
		},
	Timeout);
};
*/