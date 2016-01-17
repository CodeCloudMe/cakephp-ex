Jelly.References.Block_Refresh = function(Parameters)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Block Refresh");
		Jelly.Debug.Log(Parameters);
	}
	
	if (Debug)
		Jelly.jQuery('#Jelly_Inspector').css("background-color","red");
	
	// Lookup reference
	var Reference_Element = document.getElementById(Parameters["Namespace"]);
	var Reference = Jelly.References.Get_Reference_For_Element(Reference_Element);
	if (!Reference)
		return;
	
	// Block refresh
	Reference["Block_Refresh"] = true;
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};