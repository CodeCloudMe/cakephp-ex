Jelly.Debug.Group = function(Name)
{
	// Verify debug mode and console compatibility, begin log group with name.
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
		if (window.console)
			if (console.group)
				console.group(Name);
};