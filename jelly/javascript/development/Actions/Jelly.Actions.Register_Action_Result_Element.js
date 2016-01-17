Jelly.Actions.Register_Action_Result_Element = function(Parameters)
{	
	// Registers an action result element for a reference.
	// Parameters:  Namespace, Result_Element
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Result Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);	
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Store result element into reference
	Action_Reference["Result_Element"] = Parameters["Result_Element"];
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};