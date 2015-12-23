Jelly.Handlers.Handle_Link_From_Click = function(Event, Parameters)
{
	// Hides flash uploaders & visits links, unless the metakey is held down.
	// Parameters - Allow_Embed, Default_Container, ...
	// TODO: Further parameters
	// TODO - (allow embed? default container?)
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	// Debug
	if (Debug)
	{
		Jelly.Debug.Group("Handle_Link_From_Click");
		Jelly.Debug.Log("Event...")
		Jelly.Debug.Log(Event);
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// If the meta-key is down let the browser handle the link (for clicking into a new tab)
	// TODO what about option-clicking (i.e. forcing download?)
	if ((Event && Event.metaKey))// || (!Parameters["Container"]))
	{
		// Google Chrome Fix - ctrlKey clicks propagate to href otherwise.
		// TODO: didn't text, but I don't think this was needed anymore.
// 		if (Event.ctrlKey)
// 			return true;				
// 		else
		
		// Return false to signify link was not handled
		return false;
	}
	
	// If link is direct (i.e. to Logout or non-HTML templates that shouldn't render in containers), let browser handle it
	if (Parameters["Direct"])
		return false;
	
	if (Event)
		Parameters["Calling_Element"] = Event.target;
	else if (Parameters["Namespace"])
	{
		Parameters["Calling_Reference"] = Jelly.References.References_By_Namespace[Parameters["Namespace"]];
		Parameters["Calling_Element"] = document.getElementById(Parameters["Namespace"]);
		if (Debug)
		{
			Jelly.Debug.Log("Calling Information...");
			Jelly.Debug.Log(Parameters["Namespace"])
			Jelly.Debug.Log(Parameters["Calling_Reference"])
			Jelly.Debug.Log(Parameters["Calling_Element"])
			Jelly.Debug.Log(Jelly.References.Get_Reference_For_Element(Parameters["Namespace"]))
		}
	}

	// Hide Flash Uploaders
	// TODO: why is this here?
// 	Jelly.Files.Hide_Flash_Uploaders();
	
	// Visit link
	Jelly.Handlers.Visit_Link(Parameters);
	
	// Debug
	if (Debug)
		Jelly.Debug.End_Group("");
	
	// Return true to signify that the link was handled
	return true;
};