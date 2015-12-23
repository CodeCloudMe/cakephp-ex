Jelly.Actions.Is_Action_Reference = function(Reference)
{	
	// Validates Action reference for a namespace, or throws an error.	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Is Action Reference");
		Jelly.Debug.Log(Reference);
	}
	
	// Verify that this reference is an action reference
	if (!Reference || !Reference["Type_Alias"] || ["Action", "Type_Action"].indexOf(Reference["Type_Alias"]) < 0)
	{
		// TODO this isn't really an error...
// 		Jelly.Debug.Display_Error("No action reference found for the namespace: " + Reference["Namespace"]);
		return false;
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}

	return true;
};