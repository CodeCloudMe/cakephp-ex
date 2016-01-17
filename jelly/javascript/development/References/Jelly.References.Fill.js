Jelly.References.Fill = function(Parameters)
{
	// Registers a reference that ties an element to a URL, inserts the reference into reference tree, refreshes the reference, and returns ther reference
	// Parameters: Element, URL, Post_Values, On_Complete, Find_Parent_Container
	// TODO: Find_Parent_Container??? 
	
	// TODO Parameters
	var Debug = false && Jelly.Debug.Debug_Mode;

	if (Debug)
	{
		Jelly.Debug.Group("Fill");
		Jelly.Debug.Log(Parameters);
	}
	
	// TODO: allow Fill to create a new reference instead of reusing the original reference
	
	// Get existing reference for the target element.
	var Original_Reference;
	if (Parameters["Find_Parent_Container"])
	{
		if (Debug)
			Jelly.Debug.Log("Finding parent container");
			
		// Find the first parent with a URL property
		var Recursive_Parent_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Element"])["Parent_Reference"];		
		while (Recursive_Parent_Reference)
		{
			if (Recursive_Parent_Reference["URL"])
				break;
			if (Recursive_Parent_Reference["Parent_Reference"])
				Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"];
			else if (Recursive_Parent_Reference["Calling_Reference"])
				Recursive_Parent_Reference = Recursive_Parent_Reference["Calling_Reference"];
			else
				Recursive_Parent_Reference = null;
		}
		
		// Try finding by calling reference
		if (!Recursive_Parent_Reference)
		{
			var Recursive_Parent_Reference = Parameters["Reference"]["Calling_Reference"];
			while (Recursive_Parent_Reference)
			{
				if (Recursive_Parent_Reference["URL"])
					break;
				Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"];
			}
			
			// Throw error if parent reference not found.
			if (!Recursive_Parent_Reference)
			{
				Jelly.Debug.Display_Error("Fill: trying to get a parent container, but no parent reference exists");
				return;
			}
		}
		
		// Get namespace from found parent reference
		Original_Reference = Recursive_Parent_Reference;
	}
	else if (Parameters["Create_Reference"])
	{
		if (Debug)
			Jelly.Debug.Log("Creating Reference");
		
		// Find parent reference starting with element above current one so it doesn't catch an existing reference
		Parent_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Element"].parentNode);
		
		// Generate reference
		var Container_Parameters = {};
		Container_Parameters["Kind"] = "URL";
		Container_Parameters["URL"] = Parameters["URL"];
		if (Parameters["No_Loading"])
			Container_Parameters["No_Loading"] = Parameters["No_Loading"];
			
		if (Parent_Reference)
			Container_Parameters["Parent_Namespace"] = Parent_Reference["Namespace"];
		else
			Container_Parameters["Parent_Namespace"] = "Jelly";
		
		// Generate base unique namespace
		Container_Parameters["Namespace"] = Parameters["Element"].id;
		
		var Container_Reference = Jelly.References.Register(Container_Parameters);
		
		Original_Reference = Container_Reference;
	}
	else
	{
		Original_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Element"]);
	}
	
	// TODO - think about whether we can fill a non-URL reference
	if (Original_Reference["Kind"] != "URL")
	{
		if (Debug)
			Jelly.Debug.Log(Original_Reference);
		throw "Cannot fill a non-URL reference. OR CAN YOU?";
	}
	
	// Copy parameters to original reference
	Original_Reference["URL"] = Parameters["URL"];
	Original_Reference["Post_Values"] = Parameters["Post_Values"];
	
	// Stop
	// TODO: necessary?
	// TODO: I thought the whole part of splicing this correctly was to have it flow well, not need STOP.
	// TODO - disabled until we figure this out
// 	New_Reference_Parameters["Stop"] = true;
	
	// On_Complete
	// TODO - this overwrites original on-complete. Seems incorrect (since Windows might need to resize, etc)
	// TODO - not sure if this is ever set, or called.
	// TODO - not used, destroying.
// 	if (Parameters["On_Complete"])
// 		Original_Reference["On_Complete"] = Parameters["On_Complete"];
	
	// Refresh the reference to load its content
	Jelly.References.Refresh(Original_Reference);
	
	if (Debug)
	{
		Jelly.Debug.Log("Reference at end of Fill()...");
		Jelly.Debug.Log(Original_Reference);
		Jelly.Debug.End_Group("");
	}
	
	// Return the reference
	return Original_Reference;
};