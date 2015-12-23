Jelly.References.Create_Reference = function()
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create_Global_Reference");
	}
	
	var Reference = {};
	Reference["Refresh_Requests"] = [];
	
	if (Debug)
	{
		Jelly.Debug.Log("Reference...");
		Jelly.Debug.Log(Reference);
		Jelly.Debug.End_Group("");
	}
	
	// Return new reference
	return Reference;
};