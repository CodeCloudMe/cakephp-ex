Jelly.Actions.Register_Action_Input = function(Parameters)
{
	// Registers a input element for a reference.
	// TODO: should this be more abstract than action, or am I insane?
	// Parameters: Namespace, Input_Element, Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Input Element");
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
		Jelly.Debug.Display_Error("Trying to register input to action reference by namespace, but input element is empty or not set");
		return;
	}
	else
		var Input_Element = Parameters["Element"];
	
	// Verify input alias
	if (Parameters["Alias"])
		var Input_Alias = Parameters["Alias"];
	else
		var Input_Alias = Input_Element.name;
	if (!Input_Alias)
	{
		Jelly.Debug.Display_Error("Trying to register input to action reference by namespace and alias, but no alias provided in element or in parameters");
		return;
	}
	
	// Get input sensitivity
	var Input_Sensitivity = false;
	if (Parameters["Sensitive"])
		Input_Sensitivity = Parameters["Sensitive"];
		
	// TODO - ETC	.  can be in html as well.
	var Input_Clear_On_Execute = false;
	if (Parameters["Clear_On_Execute"])
		Input_Clear_On_Execute = Parameters["Clear_On_Execute"];
		
	var Input_Blur_On_Execute = false;
	if (Parameters["Blur_On_Execute"])
		Input_Blur_On_Execute = Parameters["Blur_On_Execute"];

	// Create input subreference
	var Action_Input = {Element: Input_Element, Sensitive: Input_Sensitivity, Clear_On_Execute: Input_Clear_On_Execute, Blur_On_Execute: Input_Blur_On_Execute, Loading_Element: null, Result_Element: null};
		
	// Store input subreference into action reference.
	Action_Reference.Inputs[Input_Alias] = Action_Input;

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};