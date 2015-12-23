// Find the closest reference for a target element element.
Jelly.References.Get_Reference_For_Element = function(Target_Element)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Get Reference For Element");
	}

	// Search parent DOM Elements recursively for a reference, return the reference.
	var Recursive_Parent_Element = Target_Element;
	while (Recursive_Parent_Element)
	{
		if (Debug)
			Jelly.Debug.Log(Recursive_Parent_Element);
			
		// If element has a reference, return the reference.
		if (Recursive_Parent_Element.Jelly_Reference)
		{
			if (Debug)
				Jelly.Debug.End_Group("Get Reference For Element");
			return Recursive_Parent_Element.Jelly_Reference;
		}
		
		// Otherwise, search the parent DOM element.
		Recursive_Parent_Element = Recursive_Parent_Element.parentNode;
	}
	
	// If no reference has been found, return null.
	if (Debug)
		Jelly.Debug.End_Group("Get Reference For Element");
	return null;
};