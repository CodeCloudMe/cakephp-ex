Jelly.References.Release_Refresh = function(Parameters)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Release Refresh");
		Jelly.Debug.Log(Parameters);
	}
	
	if (Debug)
		Jelly.jQuery('#Jelly_Inspector').css("background-color","#ccc");
	
	// Lookup reference
	var Reference_Element = document.getElementById(Parameters["Namespace"]);
	var Reference = Jelly.References.Get_Reference_For_Element(Reference_Element);
	if (!Reference)
		return;
	
	// Release refresh
	delete Reference["Block_Refresh"];
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};