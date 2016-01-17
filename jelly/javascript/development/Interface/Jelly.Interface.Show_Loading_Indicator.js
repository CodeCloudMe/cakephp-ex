Jelly.Interface.Show_Loading_Indicator = function(Parameters)
{
	// Display the loading indicator of the action reference passed in.
	// Parameters: Calling_Element	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Show Loading Indicator");
		Jelly.Debug.Log(Parameters);
	}

	// Ensure that this calling reference is an action with a loading element.
	var Loading_Element = Jelly.Interface.Generate_Loading_Indicator(Parameters);
	
	// If there is a loading element, make it visible.
	if (Loading_Element)
	{
		Loading_Element.style.visibility = "visible";
	}
};