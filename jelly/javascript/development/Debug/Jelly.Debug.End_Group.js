Jelly.Debug.End_Group = function(Name)
{
	// Verify debug mode and console compatibility, end log group
	
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
		if (window.console)
			if (console.groupEnd)
			{
				Jelly.Debug.Log("/endgroup (" + Name + ")");
				console.groupEnd();
			}
};