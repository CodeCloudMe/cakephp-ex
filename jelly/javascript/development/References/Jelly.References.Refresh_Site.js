Jelly.References.Refresh_Site = function()
{	
	// If the interface isn't locked, refresh the page
	if (!Jelly.Interface.Is_Locked)
		Jelly.Utilities.Reload_Page();
	
	// Otherwise, add a refresh page callback;
	else
		Jelly.Interface.On_Unlock.push(Jelly.Utilities.Reload_Page);
};