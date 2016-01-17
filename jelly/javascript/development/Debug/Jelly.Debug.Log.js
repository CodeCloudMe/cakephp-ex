Jelly.Debug.Log = function(Text)
{
	var Smart_Debug = false;
	
	// If in Debug Mode, verify console compatibility and display text in console
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
		if (window.console )
			if (console.log)
			{
				if (Smart_Debug)
				{
					console.group('Log');
					var Trace = printStackTrace();
					console.log(Trace[4]);
//					console.group(Trace[Trace.length - 1]);
//					console.log(Trace);
				}
				console.log(Text);
				if (Smart_Debug)
					console.groupEnd();
			}
};