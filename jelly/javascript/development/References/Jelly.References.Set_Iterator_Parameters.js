Jelly.References.Set_Iterator_Parameters = function(Namespace, Parameters)
{		
	// TODO - In general
	// TODO: Is this real?
	// Get reference.
	var Reference = Jelly.References.References_By_Namespace[Namespace];
	
	// Return if no reference.
	if (!Reference)
		return;
		
	// Return if no iterator count (Should be a more distinct terminology / variable)
	if (!Reference["Count"])
		return;
		
	// Set iterator start explictly
	if (Parameters["Start"])
		Reference["Start"] = Parameters["Start"];
		
	// Move iterator start to next page
	else if (Parameters["Page"] == "Next")
	{
		if (Reference["Start"])
			Reference["Start"] = parseInt(Reference["Start"]) + parseInt(Reference["Count"]);
		else
			Reference["Start"] = Reference["Count"];
	}
	
	// Move iterator start to previous page
	else if (Parameters["Page"] == "Previous")
	{
		if (Reference["Start"])
			Reference["Start"] = parseInt(Reference["Start"]) - parseInt(Reference["Count"]);
	}
	
	// Set iterator page explicitly				
	else if (Parameters["Page"])
	{
		// TODO : Error check
		Reference["Start"] = parseInt(Parameters["Page"]) * parseInt(Reference["Count"]);
	}
	
	// Set iterator sort property
	if (Parameters["Sort"])
	{
		Reference["Sort"] = Parameters["Sort"];
		Reference["Start"] = 0;
	}
	
	Reference["Submit_Iterator_Parameters"] = true;
				
	// TODO : Currently, this is the best way to find and refresh a refreshable parent.
	// Mark reference to be refreshed
	Jelly.References.References_To_Refresh[Reference["Namespace"]] = Reference;
	
	// Refresh
	Jelly.References.Refresh_All();
};