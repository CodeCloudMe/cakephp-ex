Jelly.References.Get_Iterator_Parameters = function(Reference)
{
	// TODO: Not done yet.
	// TODO: Does this work?
	Post_Values = {};
	
	// TODO: What is this? 
	// Add kind-specific parameters
	switch (Reference["Kind"])
	{
		case "Iterator":
			if (Reference["Start"])
				Post_Values[Reference["Namespace"] + "_Start"] = Reference["Start"];
			if (Reference["Count"])
				Post_Values[Reference["Namespace"] + "_Count"] = Reference["Count"];
			break;
	}
	
	// Add parameters for children
	for (Child_Reference_Index in Reference["Child_References"])
	{
		var Child_Reference = Reference["Child_References"][Child_Reference_Index];
		// TODO does check make sense
		if(Child_Reference !== Reference)
			Jelly.References.Add_Reference_Parameters_To_Post_Values(Child_Reference, Post_Values);
	}
	
	return Post_Values;
};