Jelly.Interface.Close_Top_Window = function()
{
	// If a top-most modal window exists, close it.
	
	// If there are modal windows open
	if (Jelly.Interface.Modal_Windows.length)
	{
		// Get the top-most window
		// TODO: Is there any neato last-item kind of thing these days (that doesn't pop it?) like peek or whatever. cute! 
		var Window_Item = Jelly.Interface.Modal_Windows[Jelly.Interface.Modal_Windows.length - 1];
		
		// Close the window.
		Jelly.Interface.Close_Window(Window_Item);
	}
};