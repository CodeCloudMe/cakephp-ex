Jelly.Actions.Set_Input_Value = function(Parameters)
{
	// Sets the value of an input to the specified value
	// Parameters: Namespace, Alias, Value
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Set Input Value");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	
	// Lookup input
	var Input_Element = Action_Reference.Inputs[Parameters["Alias"]]["Element"];
		
	// Verify input value
	if (!Input_Element)
	{	
		Jelly.Debug.Display_Error("No input found to set");
		return;
	}
	
	// Set value
	switch (Input_Element.tagName)
	{	
		// TODO - any conversions, if applicable
		default:
			Input_Element.value = Parameters["Value"];
			break;
	}
	
	if (Debug)
		Jelly.Debug.Log(Input_Element);
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
	
	return;
};