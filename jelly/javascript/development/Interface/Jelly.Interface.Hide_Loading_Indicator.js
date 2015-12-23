Jelly.Interface.Hide_Loading_Indicator = function(Parameters)
{
	// Hide the loading indicator of the action reference passed in.
	// Parameters: Calling_Reference
 		
	// Get the reference for the calling element passed in
	var Action_Reference = Parameters["Calling_Reference"];
	
	// Verify that this is an action reference in order to continue.
	if (!Action_Reference || !Action_Reference["Type_Alias"] || ["Action", "Type_Action"].indexOf(Action_Reference["Type_Alias"]) < 0)
	{
// 		Jelly.Debug.Display_Error('Trying to hide loading indicator for action reference, but no action reference provided');
		return;
	}

	// If the calling reference exists and it has a loading element, hide the loading element.
	if (Action_Reference["Loading_Element"])
	{
		var Loading_Indicator = Action_Reference["Loading_Element"];
		Loading_Indicator.style.visibility = "hidden";
	}
};