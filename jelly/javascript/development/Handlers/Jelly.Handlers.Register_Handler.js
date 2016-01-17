Jelly.Handlers.Register_Handler = function(Parameters)
{
	// Add a named event handler to an element.
	// Parameters: Element, Event, Code
	// TODO - removed 'if parameters code exists' below, because that was a strange place to verify. ok?
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Handler: ");
//		Jelly.Debug.Group("Register Handler: " + Parameters["Event"] + " (" + Parameters["Element"].id + ")");
		Jelly.Debug.Log(Parameters);
	}
	
	switch (Parameters["Event"])
	{
		case "Refreshed":
		{
			Jelly.Handlers.Refresh_Handlers.push(Parameters["Code"]);
		}
		break;
		default:
		{
			// Get the reference for the element.
			var Reference;
			if (Parameters["Reference"])
				Reference = Parameters["Reference"];
			else
				Reference = Jelly.References.Get_Reference_For_Element(Parameters["Element"]);
	
			// If the reference exists, save the handler by name to this reference. 
			// TODO Shitty if?
			if (Reference) 
			{
				// If reference doesn't have any handlers, instantiate a handlers array
				// If an event list exists, add handler to each event.
				if (typeof Parameters["Event"] === "object")
				{
					for (var Event_Index in Parameters["Event"])
					{
						if (Parameters["Event"].hasOwnProperty(Event_Index))
						{	
							// Get event name
							var Event_Name = Parameters["Event"][Event_Index];
					
							// Store the handler by event name into the handlers array for this reference.
							Reference["Handlers"][Event_Name] = Parameters["Code"];				
						}
					}
				}
		
				// If an event list does not exist, add handler to single event.
				else
				{
					Reference["Handlers"][Parameters["Event"]] = Parameters["Code"];
				}
		
				if (Debug)
				{
					Jelly.Debug.Log("Registered handler...");
					Jelly.Debug.Log(Reference);
				}
			}	
			else
			{
				if (Debug)
					Jelly.Debug.Log("No reference found for element");
			}
		}
		break;
	}
	
	if (Debug)
		Jelly.Debug.End_Group("");
};