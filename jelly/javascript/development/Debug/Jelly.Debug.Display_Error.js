Jelly.Debug.Display_Error = function(Text)
{
	// Displays text in console and alert text if according to debugging value. 	
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All || Jelly.Debug.Debug_Mode == Jelly.Debug.Level.Errors_Only)
	{
		// Display text in console
		console.log(Text);
	
		// Alert text
		if (Jelly.Debug.Alert_Errors)
			alert(Text);
	}
};