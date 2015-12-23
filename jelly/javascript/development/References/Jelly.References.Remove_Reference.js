Jelly.References.Remove_Reference = function(Reference)
{
	// Remove this reference from lists of references by namespace and by id, as well as the refresh queue, and recursively do the same for all children.
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Remove Reference...");
		Jelly.Debug.Log(Reference);
		Jelly.Debug.Log(Reference);
	}
	
	var Reference_Kind = Reference["Kind"];

	if (Debug) Jelly.Debug.Log("Remove Reference: Is a"  + Reference_Kind);

	switch (Reference_Kind)
	{					
		case "Attachment_Iterator":
		case "Iterator":
			var Reference_Lookup = Reference["Type_Alias"];
			var Matching_Reference_Lists = [
					Jelly.References.Reference_Lookups_By_Kind['Iterator'][Reference_Lookup]
				];
			break;
		case "Attachment":			
		case "Item":
			var Reference_Lookup = Reference["ID"];
			var Matching_Reference_Lists = [
					Jelly.References.Reference_Lookups_By_Kind['Item'][Reference_Lookup]
				];

			// TODO - below should be implemented, but incomplete.
			// Remove container item
// 			if (Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"])
// 				if (Reference_Lookup == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"]["ID"])
// 					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"] = null;
// 
// 			// Remove site item
// 			else if (Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"])
// 				if (Reference_Lookup == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"]["ID"])
// 					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"] = null;
				
			break;
			
		case "URL":
			var Matching_Reference_Lists = [
					Jelly.References.Reference_Lookups_By_Kind['URL']
				];			
			break
			
		case "Non_Standard_Wrapper":
			var Reference_Name = Reference["Name"];
			switch (Reference_Name)
			{
				case "Site_Icon":
					var Matching_References_Lists = [
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"]
						];
					break;

				case "Current_Path":
					var Matching_References_Lists = [
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"]
						];						
					break;		

				case "Document_Title":
					var Matching_Reference_Lists = [
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"],
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"],	
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Secondary"]
						];						
					break;		
				default:
					// TODO - throw error.
					break;
			}
			break;
		case "Container":
			Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Primary"] = null;
			var Matching_Reference_Lists = [];
			break;
		case "HTML":
			var Matching_Reference_Lists = [];
			break;
		default:
			// TODO - throw error
			break;
	}

	// Remove reference from reference lookup.
	for (List_Index = 0; List_Index < Matching_Reference_Lists.length; List_Index++)
	{
		var Matching_References = Matching_Reference_Lists[List_Index];
		for (Reference_Index = 0; Reference_Index < Matching_References.length; Reference_Index++)
		{
			if (Matching_References[Reference_Index] == Reference)
			{
				// Splice index out of array
				Matching_References.splice(Reference_Index, 1);
				break;
			}
		}
	}

	// Remove reference from its Parent reference
	if (Reference.Parent_Reference)
	{
		for (Reference_Index in Reference.Parent_Reference.Child_References)
		{
			if (Reference.Parent_Reference.Child_References.hasOwnProperty(Reference_Index))
			{
				if (Reference.Parent_Reference.Child_References[Reference_Index] == Reference)
				{
					// Splice index out of array
					Reference.Parent_Reference.Child_References.splice(Reference_Index, 1);
					break;
				}
			}
		}
	}

	// "Do not remove reference from its DOM element so that Jelly event bubbling can still occur"
	// TODO: this is fishy, indicator that we should rewrite events handling, then uncomment this
//			if (Reference.Element)
//				delete Reference.Element.Jelly_Reference; // leave commented

	// Delete item from Jelly.References.References_By_Namespace object
	delete Jelly.References.References_By_Namespace[Reference["Namespace"]];

	// Delete item from Jelly.References.References_To_Refresh object
	delete Jelly.References.References_To_Refresh[Reference["Namespace"]];
	
	// Delete reference to DOM element
	// TODO not sure if this is correct or if we want to keep it around for any reason
	if (Reference["Element"])
		delete Reference["Element"];

	// Remove children
	while (Reference["Child_References"].length)
	{
		var Child_Reference = Reference["Child_References"][0];
		Jelly.References.Remove_Reference(Child_Reference);
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("Remove Reference...");
	}
};