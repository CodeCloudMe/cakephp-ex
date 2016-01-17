Jelly.References.Trigger_Refresh = function(Parameters)
{
	// Queues item, iterator, and special case reference refreshes.
	// Parameters: Kind, Type_Alias, Item_ID, Item_Alias, Namespace, URL
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Trigger_Refresh:" + Parameters['Kind'] + ','  + ' ' + Parameters['Type_Alias'] + ',' + ' ' + Parameters['Item_ID'] + ',' + ' ' + Parameters['Item_Alias'] + ',' + ' ' + Parameters['Namespace'] + ',' + ' ' + Parameters['URL']);
	}
	
	var Matching_References = [];
	
	switch (Parameters["Kind"])
	{		
		case "Iterator":
			// Queue iterator references that match type to refresh
			// TODO Parent Types
			if (Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]])
				Matching_References = Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]];			
			
			// Refresh Less for Designs and Templates
			// TODO better way to do type-specific refreshing?
			if (Parameters.Type_Alias == "Design" || Parameters.Type_Alias == "Template")
				less.refresh(true);
			break;
			
		case "Item":
			// If this is the container item, trigger container item specific refreshes.
			if (Parameters["Item_ID"] == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"]["ID"])
				Jelly.References.Trigger_Refresh({"Kind":"Container"});
			
			// If this is the site item, trigger site item specific refreshes.
			if (Parameters["Item_ID"] == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"]["ID"])
				Jelly.References.Trigger_Refresh({"Kind":"Site"});
					
			// Queue item references that match item to refresh
			if (Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["Item_ID"]])
				Matching_References = Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["Item_ID"]];
			break;
				
		case "Container":
			Matching_References = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"];
			break;
			
		// TODO - "Current_Session"->"Site" is for the the demo, needs thinking...? seems to be shorthand for the site item.
		case "Current_Session":	
				// Refresh the site item
				Jelly.References.Trigger_Refresh({"Kind":"Item", "Item_ID": Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"]["ID"]});
			break;

		case "Site":
			Matching_References = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"];
			break;
			
		// TODO - Temporary
		case "Path":
		case "Path_Primary":
			Matching_References = [Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Primary"]];
			break;
			
		case "Path_Secondary":
			Matching_References = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Secondary"];
			break;
			
		// TODO - not used.
		case "Direct":
			// Queue reference to refresh
			Matching_References.push(Jelly.References.References_By_Namespace[Parameters["Namespace"]]);
			break;
			
		case "Element":
		{
			var Reference = Jelly.References.Get_Reference_For_Element(Parameters['Element']);
			Matching_References.push(Reference);
			break;
		}

		// TODO - not used.			
		case "URL":
			if (Jelly.References.Reference_Lookups_By_Kind["URL"][Parameters["URL"]])
				Matching_References = Jelly.References.Reference_Lookups_By_Kind["URL"][Parameters["URL"]];
			break;
			
		default:
			return;
	}
	
	// Add matching references as unique by namespace to References to Refresh array.
	for (var Reference_Index in Matching_References)
	{
		if (Matching_References.hasOwnProperty(Reference_Index))
		{
			var Reference = Matching_References[Reference_Index];
			
			if (Debug)
			{
				// TODO
				Jelly.Debug.Log("Changed Reference: " +  " (" + Reference["Namespace"] + ")");
				Jelly.Debug.Log(Reference);
			}
			
			// Mark reference to be refreshed
			Jelly.References.References_To_Refresh[Reference["Namespace"]] = Reference;
		}
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};