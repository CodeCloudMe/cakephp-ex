Jelly.Handlers.Change_URL = function(Parameters)
{
	// TODO: Not sure what this does or why we need it. 
	// TODO: Discard?
	// Parameters["URL"]
	
	// Point browser to new URL
	document.location = Jelly.Directory + "?" + Parameters["URL"];
};