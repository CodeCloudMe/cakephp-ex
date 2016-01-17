Jelly.Utilities.Require_SSL = function()
{
	// Checks for https protocol, redirect to https if not alreay
	// TODO: lol obsolete
	
	// If not https protocol
	if (document.location.href.substr(0, 5) != "https")
	{
		// Assumes http protocol, replaces https in place of the the first four letters in the document location
		// TODO: i guess this works..., but it's not the right code.
		document.location.href = "https" + document.location.href.substr(4);
	}
};