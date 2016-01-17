Jelly.Actions.Hide_Results = function(Parameters)
{	
	// Registers an action result element for a reference.
	// Parameters:  Namespace
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Hide Results");
		Jelly.Debug.Log(Parameters);
	}
			
	// Get action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);	
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;

	// Hide action result element.
	// TODO - animate 
	if (Action_Reference["Result_Element"])
		Action_Reference["Result_Element"].style.display = "none";
		
	// Hide action input result elements.
	for (var Input_Alias in Action_Reference.Inputs)
	{
		var Action_Input = Action_Reference.Inputs[Input_Alias];
		if (Action_Input["Result_Element"])
			Action_Input["Result_Element"].style.display = "none";
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};