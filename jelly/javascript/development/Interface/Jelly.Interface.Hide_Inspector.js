Jelly.Interface.Hide_Inspector = function(Parameters)
{
	// Remove inspect item id
	Jelly.Interface.Inspect_Item = null;
	
	// Close Inspector	
	Jelly.jQuery('#Jelly_Inspector').removeClass("Visible");
	
	// Widen content
	if (!Parameters || !Parameters["Maintain_Content_Size"])
		Jelly.jQuery('#Jelly_Content').removeClass("Inspector_Visible");
};