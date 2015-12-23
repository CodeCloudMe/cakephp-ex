Jelly.References.Refresh_All = function()
{
	// Clean all references, verify queue of references to refresh as refreshable and unique, refresh verified references, and clear the queue.
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Refresh_All");
		Jelly.Debug.Log(Jelly.References.References_To_Refresh);
	}
	
	// Remove dead references
	Jelly.References.Clean_References();	
	
	// Determine refreshable reference/parent reference
	var Refresh_List = {};
	
	for (var Reference_Index in Jelly.References.References_To_Refresh)
	{
		if (Jelly.References.References_To_Refresh.hasOwnProperty(Reference_Index))
		{
			var Reference = Jelly.References.References_To_Refresh[Reference_Index];
		
			if (Debug)
			{
				Jelly.Debug.Group("Checking if reference can be refreshed: " + Reference["Namespace"]);
				Jelly.Debug.Log(Reference);
			}
			
			// Search through parents for the first refreshable reference
			var Recursive_Reference = Reference;			
			var Refreshable_Reference_Was_Found = false;
			while (Recursive_Reference && !Refreshable_Reference_Was_Found)
			{
				var Inner_Debug = false;
				
				if (Inner_Debug)
					Jelly.Debug.Log("... " + Recursive_Reference["Namespace"]);
				
				// Only refresh references with elements in the DOM, and which are not No_Refresh
				if (!Recursive_Reference["No_Refresh"])
				{
					switch (Recursive_Reference["Kind"])
					{	
						// Non Standard Wrappers are refreshable
						case "Non_Standard_Wrapper":
						// URLs are refreshable.
						case "URL":
						// Containers are refreshable
						case "Container":
							// Stop searching
							Refreshable_Reference_Was_Found = true;
							break;
							
						// Items are refreshable if they have a template
						case "Item":
							// If this is site item itself, force a hard refresh.
							if (Recursive_Reference == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"])
							{
								// TODO - debug this out for demo mode. 
//								Refreshable_Reference_Was_Found = true;								

								// TODO - Implement this version after finishing investigating
								// Jelly.Utilities.Reload_Page();
							}

						
							// Verify element exists in DOM.
							if (Recursive_Reference["Element"] && Jelly.jQuery.contains(document, Recursive_Reference["Element"]))
							{
								// Verify template was set on the item reference
								// TODO - isn't this always true?
								if (Recursive_Reference["Template_Alias"])
								{
									// Don't allow refreshing in Design_Only until we hit a Design itself
									// TODO fix
									if (!Recursive_Reference["Design_Only"] || Recursive_Reference["Type_Alias"] == "Design")
									{
										// Stop searching
										Refreshable_Reference_Was_Found = true;
									}
								}
							}
							break;
							
						default:
							 if (Inner_Debug)
								Jelly.Debug.Log("NEXT (is not container, is not url, or item without template alias)");
							break;
					}
				}
				else
				{
					 if (Inner_Debug)
						Jelly.Debug.Log("NEXT (Does not have element or is no refresh)");
				}
				
				if (Refreshable_Reference_Was_Found)
					break;
					
				if (Recursive_Reference["Block_Refresh"])
				{	
						if (Debug)
							Jelly.Debug.Log("Refresh Blocked");
						
						// Cancel reference refresh
						Recursive_Reference = null;
				
						// Stop searching
						break;
				}
			
				// If a reference is marked to stop traversal, cancel search.
				// TODO: Is this used?
				if (Recursive_Reference["Stop"])
				{
					 if (Debug)
						Jelly.Debug.Log("STOP (Is Stop)");
						
					// Cancel reference refresh
					Recursive_Reference = null;
				
					// Stop searching
					break;
				}
				
					
				// If this reference was not refreshable, and the reference wasn't marked to stop traversal, continue search to the parent reference
				Recursive_Reference = Recursive_Reference["Parent_Reference"];
			}
		
			// If there is no refreshable reference, continue to the next reference in the queue of references to refresh.
			// TODO: we shouldn't need this since parents should bottom out at Body
			// TODO: but should they? what about windows?
			if (!Recursive_Reference)
			{
				if (Debug)
				{
					Jelly.Debug.Log("NO: Reference will not be refreshed.");
					Jelly.Debug.End_Group("");					
				}
				
				continue;
			}
		
			// Add the refreshable reference by its namespace to a list of queue of verified references to refresh
			if (Debug)
			{
				Jelly.Debug.Log("YES: Reference will be refreshed: " + Recursive_Reference["Namespace"]);
				Jelly.Debug.End_Group("");
			}
			Refresh_List[Recursive_Reference["Namespace"]] = Recursive_Reference;
		}
	}
	
	if (Debug)
	{	
		Jelly.Debug.Log('Refresh list before consolidated by parent');
		Jelly.Debug.Log(Refresh_List);
	}
	
	// For each verified reference to refresh, also verify that none of its parent references are not already in the list.
	for (var Reference_Index in Refresh_List)
	{
		if (Refresh_List.hasOwnProperty(Reference_Index))
		{
			// Get reference to verify.
			var Reference_To_Verify = Refresh_List[Reference_Index];
			
			var Inner_Debug = false;
			
			if (Inner_Debug)
				Jelly.Debug.Log('Verifying reference: ' + Reference_To_Verify['Namespace']);

			// Search through parents
			
			var Recursive_Parent_Reference = Reference_To_Verify["Parent_Reference"];
			while (Recursive_Parent_Reference)
			{
				if (Inner_Debug)
					Jelly.Debug.Log('...' + Recursive_Parent_Reference["Namespace"]);
					
				// Check if recursive reference is already in refresh list
				if (Refresh_List[Recursive_Parent_Reference["Namespace"]])
				{
					// Delete reference to verify from queue of verified references to refresh
					delete Refresh_List[Reference_To_Verify["Namespace"]];
				
					// Stop searching
					break;
				}
		
				// If a reference is marked to stop traversal, cancel search for parent reference.
				// TODO: are these used? 
				// TODO: This was from a bad day, check if there should be an OR condition
				if (Recursive_Parent_Reference["Stop"])// || )
					break;
			
				// TODO: catch for mistakes, i guess
				// TODO: this used to mark the reference as 'stop'
				// TODO: maybe these can be caught on registration, and at other splicing moments, instead.
				if (Recursive_Parent_Reference["Parent_Reference"] == Recursive_Parent_Reference)
					break;

				// If this parent reference isn't already on the list, and the search hasn't been stopped, check the next parent reference.
				Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"];
			}
		}
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Final Refresh List");
		Jelly.Debug.Log(Refresh_List);
	}
	
	// Initialize references to refresh
	Jelly.References.References_To_Refresh = {};
	
	// Refresh each reference in the queue of verified references to refresh.
	for (var Reference_Index in Refresh_List)
	{				
		if (Refresh_List.hasOwnProperty(Reference_Index))
		{
			var Reference = Refresh_List[Reference_Index];
			Jelly.References.Refresh(Reference);
		}
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("Refresh_All");
	}
};