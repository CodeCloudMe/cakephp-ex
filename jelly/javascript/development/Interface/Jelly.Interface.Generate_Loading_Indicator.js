Jelly.Interface.Generate_Loading_Indicator = function(Parameters)
{
	// Generate a loading element as needed for this action reference, either after the first execute link, or at the end of the element, and registers the element.
	// Parameters: Action_Reference
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Generate Loading Indicator");
		Jelly.Debug.Log(Parameters);
	}

	// Get the reference for the calling element passed in
	var Action_Reference = Parameters["Calling_Reference"];
	
	// Verify that this is an action reference in order to continue.
	if (!Action_Reference || !Action_Reference["Type_Alias"] || ["Action", "Type_Action"].indexOf(Action_Reference["Type_Alias"]) < 0)
	{
// 		Jelly.Debug.Display_Error('Trying to show loading indicator for action reference, but no action reference provided');
		return;
	}

	// Return loading element if it already exists
	if (Action_Reference["Loading_Element"])
		return Action_Reference["Loading_Element"];
		
	// Otherwise, generate a new one, register it, and return it.
	else
	{	
		// Verify that the Action has a DOM element in order to continue making a loading indicator.
		if (!Action_Reference["Element"])
			return;
			
		// Get the action DOM element
		var Action_Element = Action_Reference["Element"];
		
		// Get the action namespace
		var Action_Namespace = Action_Reference["Namespace"];
		
		// Create a loading indicator
		var Loading_Element = Jelly.Interface.Generate_Browser_Control(
				{
					Browser_Control_ID: "Loading", 
					Replace: {"NAMESPACE": Action_Namespace}
				}
			);
	
		// If an execute link exists, insert the loading indicator after the first execute link 
		var Execute_Link_Elements = Action_Element.getElementsByClassName('Jelly_Action_Execute');	
		if (Execute_Link_Elements	[0])
		{	
			var First_Execute_Link_Element = Execute_Link_Elements[0];
			Jelly.jQuery(First_Execute_Link_Element).after(Loading_Element);
		}
		
		// If no execute link exists, insert the loading indicator at the end of the action element.
		else
		{
			Action_Element.appendChild(Loading_Element);
		}
		
		// Register the loading element to this action reference		
		Jelly.Actions.Register_Action_Loading_Element(
				{
					Namespace: Action_Namespace, 
					Loading_Element: Loading_Element
				}
			);
		
		// Return loading element
		return Loading_Element;
	}
}
