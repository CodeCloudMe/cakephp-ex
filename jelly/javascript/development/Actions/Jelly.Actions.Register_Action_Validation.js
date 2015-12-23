Jelly.Actions.Register_Action_Validation = function(Parameters)
{
	// Registers a input element for a reference.
	// TODO: should this be more abstract than action, or am I insane?
	// Parameters: Namespace, Input_Element, Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Validation");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);	
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;	
		
	// Copy validation values to new object.
	var Validation = {};
	if (Parameters["Behavior"])
		Validation["Behavior"] = Parameters["Behavior"];
	if (Parameters["Inputs"])
		Validation["Inputs"] = Parameters["Inputs"];
	if (Parameters["Error"])
		Validation["Error"] = Parameters["Error"];
	if (Parameters["Function"])
		Validation["Function"] = Parameters["Function"];
			
	// Store validation in action 
	Action_Reference.Validations.push(Validation);
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};
