Jelly.Handlers.Call_Handler_For_Target = function(Parameters)
{
	// Finds and calls the closest registered handler by specified event name for the specified target, searching references upwards from the target reference via parent and calling references
	// Parameters: {Target, Event, Display_Target, Remove_After_Calling}
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Call Handler for Target: " + Parameters["Event"]);
		Jelly.Debug.Log(Parameters);
	}
	
	// Validate display target & default to target
	if (!Parameters["Display_Target"])
		Parameters["Display_Target"] = Parameters["Target"];
	
	// Set calling element to target
	Parameters["Calling_Element"] = Parameters["Target"];
	
	// Find and validate reference for the target
	var Target_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Target"]);
	if (!Target_Reference)
	{
		if (Debug)
		{
			if (!Parameters["Target"])
			{
				Jelly.Debug.Log("Call Handler For Target: No target.");				
			}
			else
			{
				Jelly.Debug.Log("Call Handler For Target: No reference for target:")			
				Jelly.Debug.Log(Parameters["Target"]);
				Jelly.Debug.Print_All_Handlers();
			}
		}
		return;
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Target Reference found...");
		Jelly.Debug.Log(Target_Reference);
	}
	
	// Search for handler in reference tree
	var Search_Reference = Target_Reference;
	while (Search_Reference)
	{
		if (Debug)
			Jelly.Debug.Log("Searching for handler: " + Search_Reference["Namespace"]);
		
		// If the current reference has a handler for the event, call the handler, and quit searching.
		if (Search_Reference.Handlers)
		{
			if (Search_Reference.Handlers[Parameters["Event"]])
			{
				if (Debug)
				{
					Jelly.Debug.Log("Found reference with handler...");
					Jelly.Debug.Log(Search_Reference);
					Jelly.Debug.Group("Calling Found Handler function for Target...");
					Jelly.Debug.Log(Search_Reference.Handlers[Parameters["Event"]]);
				}
				
				// Call handler, passing all parameters directly into the handler script.
				// TODO: display target goes here? 
				Search_Reference.Handlers[Parameters["Event"]](Parameters);
			
				// TODO - may never be using anymore.
				if (Parameters["Remove_After_Calling"])
					delete Search_Reference.Handlers[Parameters["Event"]];
				
				if (Debug)
					Jelly.Debug.End_Group("Calling Found Handler function for Target...");
				
				break;
			}
		}
		
		// Trickle up through parent references, then calling references
		if (!Search_Reference.Stop)
		{	
			// Move up via parent reference if there is a valid parent reference.
			// TODO - would the latter half of this condition be a tree bug, or valid?
			if (Search_Reference.Parent_Reference &&  Search_Reference.Parent_Reference != Search_Reference)
			{	
//				Jelly.Debug.Log("Parent Ref");
				Search_Reference = Search_Reference.Parent_Reference;
			}

			// Move up via calling reference if there is a valid calling reference.
			// TODO - would the latter half of this condition be a tree bug, or valid?
			else if (Search_Reference.Calling_Reference && Search_Reference.Calling_Reference != Search_Reference)
			{
//				Jelly.Debug.Log("CALING REF");
				Search_Reference = Search_Reference.Calling_Reference;
			}
			
			// If no parent or calling reference is found, finish searching
			else
				Search_Reference = null;
		}
		
		// If the search reference has a search block, finish searching.
		else
			Search_Reference = null;
	}
	
	if (!Search_Reference)
	{
		if (Debug)
		{
			Jelly.Debug.Log('No match found for handler.');
			Jelly.Debug.Print_All_Handlers();
		}
	}
	

	if (Debug)
		Jelly.Debug.End_Group("Call Handler for Target: " + Parameters["Event"]);
	
	return;
};