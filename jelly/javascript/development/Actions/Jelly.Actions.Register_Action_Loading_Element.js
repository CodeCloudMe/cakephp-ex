Jelly.Actions.Register_Action_Loading_Element = function(Parameters)
{
	// Registers a loading element for a reference.
	// TODO: should this be more abstract than action, or am I insane?
	// Parameters: Namespace, Loading_Element
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Loading Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;

	// Store loading element into reference.
	Action_Reference["Loading_Element"] = Parameters["Loading_Element"];

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};