Jelly.Interface.Focus_Top_Window = function()
{
	// If the top most window exists, focus its first control.	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Focus_Top_Window");
	}
	
	if (Jelly.Interface.Modal_Windows.length > 0)
	{	
		// Get top window reference
		var Window_Reference = Jelly.Interface.Modal_Windows[Jelly.Interface.Modal_Windows.length - 1];
	
		// Get top window element
		var Window_Element = Window_Reference.Element;
	
		// Call focus handler for top window
		Jelly.Handlers.Call_Handler_For_Target({'Event': 'Focus', 'Target': Window_Element});
	}
	
	if (Debug)
		Jelly.Debug.End_Group("Focus_Top_Window");
};