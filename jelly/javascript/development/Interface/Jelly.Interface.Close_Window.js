Jelly.Interface.Close_Window = function(Window_Reference)
{
	// Fades out a window and removes it from the DOM, removes its corresponding jelly reference, removes it from the windows stack, and repositions lightbox as appropriate.
	// Window_Reference: reference to stored reference in Jelly.Interface.Modal_Windows
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Close_Window");
		Jelly.Debug.Log("Window_Reference...");
		Jelly.Debug.Log(Window_Reference);
	}
	
	// Get the window element
	var Window_Control_Element = Window_Reference["Control_Element"];
	
	// Fade it out and remove it
	Jelly.Interface.Fade_Out_And_Remove(Window_Control_Element);
	
	// If this is the top-modal window, remove it from the stack, and reposition lightbox, or hide it.  
	// TODO: we probably only need to check the latter, unless we're building in handling for closing modal, non-top windows
	// TODO: implement Modal
	if (Window_Reference == Jelly.Interface.Modal_Windows[Jelly.Interface.Modal_Windows.length - 1])
	{
		// Remove from modal windows stack
		Jelly.Interface.Modal_Windows.pop();
		
		// Reposition the lightbox if there are modal windows remaining
		if (Jelly.Interface.Modal_Windows.length > 0)
			Jelly.Interface.Show_Lightbox();	
			
		// Or hide the lightbox if there are no modal windows remaining.
		else
			Jelly.Interface.Hide_Lightbox();
	}
	
	// Remove the parent global reference (created specifically for this window)
	Jelly.References.Remove_Reference(Window_Reference);
	
	// If this is the last window unlock the modal state.
	if (Jelly.Interface.Modal_Windows.length == 0)
	{
		Jelly.Interface.Unlock();
	}
	
	if (Debug)
		Jelly.Debug.End_Group("Close_Window");
};