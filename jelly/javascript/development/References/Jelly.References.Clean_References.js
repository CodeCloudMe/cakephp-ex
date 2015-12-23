Jelly.References.Clean_References = function()
{
	// Remove all references with an element that does not exist in the DOM
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Clean_References");
	}
	
	// Remove all references with an element that does not exist in the DOM
	for (Reference_Namespace in Jelly.References.References_By_Namespace)
	{
		if (Jelly.References.References_By_Namespace.hasOwnProperty(Reference_Namespace))
		{
			var Reference = Jelly.References.References_By_Namespace[Reference_Namespace];
			
			// If the reference has an element but the element is not in the DOM, remove the reference
			if (Reference["Element"])
			{
				if (!Jelly.References.Element_Exists(Reference["Element"]))
				{
					Jelly.References.Remove_Reference(Reference);
				}
			}
		}
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("Clean_References");
	}
};