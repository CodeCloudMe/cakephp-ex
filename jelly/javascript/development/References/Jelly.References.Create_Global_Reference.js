Jelly.References.Create_Global_Reference = function()
{
	// Registers new control reference with a new corresponding element	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create_Global_Reference");
	}
	
	// Generate global namespace by incrementing global reference count.
	// TODO, name of the Last_Unique_Reference_Index variable
	Jelly.References.Current_Global_Reference_Index++;
	var Namespace = Jelly.Interface.Global_Controls_Element.id + Jelly.Namespace_Delimiter + Jelly.References.Current_Global_Reference_Index;

	// Create control element and add it to the controls element
	var Reference_Element = document.createElement("span");
	Reference_Element.id = Namespace;
	Jelly.Interface.Global_Controls_Element.appendChild(Reference_Element);
	
	// Register control reference
	var Parameters = {};
	Parameters["Parent_Namespace"] = "Jelly";
	Parameters["Namespace"] = Namespace;
	Parameters["Kind"] = "HTML";
	var Reference = Jelly.References.Register(Parameters);
	
	if (Debug)
	{
		Jelly.Debug.Log("Reference...");
		Jelly.Debug.Log(Reference);
		Jelly.Debug.End_Group("");
	}
	
	// Return new reference
	return Reference;
};