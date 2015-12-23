Jelly.Actions.Get_Input_From_Action_By_Alias = function(Parameters)
{
	// Returns a registered input by alias
	// Parameters: Namespace, Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Get Action Input Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Get action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Get input
	var Input_Element = Action_Reference.Inputs[Parameters["Alias"]]["Element"];
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
	
	return Input_Element;
};