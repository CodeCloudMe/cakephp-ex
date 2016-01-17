Jelly.Actions.Register_Action_Input_Result_Element = function(Parameters)
{
	// Registers a input element for a reference.
	// TODO: should this be more abstract than action, or am I insane?
	// Parameters: Namespace, Input_Element, Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Input Result Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);	
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Verify input element
	if (!Parameters["Element"])
	{
		Jelly.Debug.Display_Error("Trying to register input result element to action reference by namespace, but input result element is empty or not set");
		return;
	}
	else
		var Input_Result_Element = Parameters["Element"];
	
	// Verify input alias
	if (!Parameters["Alias"])
	{
		Jelly.Debug.Display_Error("Trying to register input to action reference by namespace and alias, but no alias provided in element or in parameters");
		return;
	}
	else
		var Input_Alias = Parameters["Alias"];

	// Store input element into action reference.
	// TODO: This hack checks if an input has been registered, but could be smarter
	if (Action_Reference.Inputs[Input_Alias])
		Action_Reference.Inputs[Input_Alias]["Result_Element"] = Input_Result_Element;
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};