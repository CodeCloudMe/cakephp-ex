Jelly.Interface.Hide_Highlights = function()
{
	// TODO: This entire function
	var Debug = false && Jelly.Debug.Debug_Mode;	
	if (Debug)
	{
		Jelly.Debug.Group("Hide Highlights");
	}

	if (Jelly.Interface.Check_Event_Protection('Highlights'))
	{
		// Remove highlight
		if (Jelly.Interface.Highlight_Parts)
		{
			Jelly.Interface.Fade_Out_And_Remove(Jelly.Interface.Highlight_Parts["Left"]);
			Jelly.Interface.Fade_Out_And_Remove(Jelly.Interface.Highlight_Parts["Top"]);
			Jelly.Interface.Fade_Out_And_Remove(Jelly.Interface.Highlight_Parts["Right"]);
			Jelly.Interface.Fade_Out_And_Remove(Jelly.Interface.Highlight_Parts["Bottom"]);
			Jelly.Interface.Highlight_Parts = null;
		}

		// Clear highlight target
		Jelly.Interface.Highlight_Target_Namespace = null;
	}
	else if (Debug)
		Jelly.Debug.Log("Caught");
	
	if (Debug)
	{
		Jelly.Debug.End_Group("Hide Highlights");
	}

};