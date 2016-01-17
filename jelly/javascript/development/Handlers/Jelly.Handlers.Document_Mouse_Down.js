Jelly.Handlers.Document_Mouse_Down = function(event)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	// Call handlers functions for document level mouse down events.
	if (Debug)
		Jelly.Debug.Log("Document Mouse Down called");

	// Close menus
	Jelly.Interface.Close_Menus();	
};