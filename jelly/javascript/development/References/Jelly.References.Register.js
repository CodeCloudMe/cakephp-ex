Jelly.References.Register = function(Parameters)
{
	// Stores reference information by Namespace and by ID for different kinds of metadata, tying the information to an element if it exists, and tying the information to a reference tree. 

	// Parameters: ID, Namespace, Force, Kind, Type, URL, Start, Count, Sort, Alias, Template, Template_Type, Parent_Namespace, Post_Values, On_Complete, One_To_Many_Parent, One_To_Many_Parent_Type
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Stop registering if no register (i.e. in Action results)
	// TODO "Force" is added from all local registrations so that Action results don't register things... bad hack but should work
	// TODO: this doesn't even do anything? 
	if (Jelly.References.No_Register && !Parameters["Force"])
	{
//			return;
	}
		
	// Initialize Reference
	var Reference = {};

	// Initialize Child References array
	Reference.Child_References = [];
	
	// Initialize Handlers array
	Reference.Handlers = {};
	
	// Verify & Store Namespace
	if (Parameters["Namespace"])
		Reference["Namespace"] = Parameters["Namespace"];
	else
	{
		Jelly.Debug.Display_Error("Register: Parameters must have Namespace"); 
		return;
	}
	
	// Verify & Store Kind
	if (Parameters["Kind"])
		Reference["Kind"] = Parameters["Kind"];
	else
	{
		Jelly.Debug.Display_Error("Register: Parameters must have Kind"); 
		Jelly.Debug.Log(Parameters);
		return;
	}
	
	// Store additional parameters for specific kinds.
	switch (Reference["Kind"])
	{
		// Iterator references store paging information
		// TODO: Does this work?
		case "Iterator":
			// TODO - think about naming
			Reference["Type_Alias"] = Parameters["Type_Alias"];
			Reference["Original_Start"] = Parameters["Start"];
			Reference["Original_Count"] = Parameters["Count"];
			Reference["Original_Sort"] = Parameters["Sort"];
			
			// No Refresh
			Reference["No_Refresh"] = Parameters["No_Refresh"];
			Reference["No_Preloader"] = Parameters["No_Preloader"];
			
			break;
			
		// Item references stores additional descriptors & additional template information.
		case "Item":				
			// Store additional data
			Reference["ID"] = Parameters["ID"];			
			if (Parameters["Name"])
				Reference["Name"] = Parameters["Name"];
			if (Parameters["Alias"])
				Reference["Alias"] = Parameters["Alias"];
			
			// No Refresh
			Reference["No_Refresh"] = Parameters["No_Refresh"];
			Reference["No_Preloader"] = Parameters["No_Preloader"];

			// TODO - think about naming
			Reference["Type_Alias"] = Parameters["Type_Alias"];
			
			if (Parameters["Template_Alias"])
				Reference["Template_Alias"] = Parameters["Template_Alias"];

			// TODO - needed?
			if (Parameters["Template_ID"])
				Reference["Template_ID"] = Parameters["Template_ID"];				
				
			if (Parameters['Variables'])
				Reference['Variables'] = Parameters['Variables'];
				
			// Special Items
			switch (Parameters["Type_Alias"])
			{
				case "Type_Action":
					// TODO: seemingly unnecessary based on the fact that type covers the situation or now.				
					if (Parameters["One_To_Many_Parent"])
					{
						Reference["One_To_Many_Parent"] = Parameters["One_To_Many_Parent"];
						Reference["One_To_Many_Parent_Type"] = Parameters["One_To_Many_Parent_Type"];
					}

				// NO BREAK
				case "Action":
					// TODO: make sure this causes no conflict with refreshing, but it shouldn't
					// TODO - I want Action_Element too for consistency but that's work. 
					Reference.Loading_Element = null;
					Reference.Result_Element = null;
					Reference.Validations = [];
					Reference.Inputs = {};
					break;
			}

			// TODO: // seemingly unnecessary based on the fact that type covers the situation or now.
			if (Parameters["One_To_Many_Parent"])
			{
				// HACK: only doing this for Type_Actions so far
				if (Parameters["Type_Alias"] == "Type_Action")
				{
					Reference["One_To_Many_Parent"] = Parameters["One_To_Many_Parent"];
					Reference["One_To_Many_Parent_Type"] = Parameters["One_To_Many_Parent_Type"];
				}
			}
			break;
			
		// URLs references store the URL, post values, and an on_complete function.
		case "URL":			
			// Validate kind
			// TODO: I think we can take out this validation, or add them everywhere.
			if (!Parameters["URL"])
				{
					Jelly.Debug.Display_Error("Register: reference is URL but no URL passed in parameters"); 
					return;
				}
			
			// Store URL, Post Values, and On Complete
			// TODO: Do we use Post_Values or On_Complete anywhere? Verify.
			Reference["URL"] = Parameters["URL"];
			Reference["Post_Values"] = Parameters["Post_Values"];			
			Reference["No_Preloader"] = Parameters["No_Preloader"];

			// TODO: What? ... maybe this means On_Load, and should be called so. 
			// TODO: redundant or not with load handler? hm......... 
			// TOOD: Also, either way, shouldn't this be more general than URL?
			// TODO :- destroyed, not used.
// 			if (Parameters["On_Complete"])
// 				Reference["On_Complete"] = Parameters["On_Complete"];
			
			break;


		// Special 	references...			
		case "Non_Standard_Wrapper":
			Reference["Name"] = Parameters["Name"];
			break;
		case "HTML":
			// TODO: Anything to do here?
			// TODO - APPARENTLY THIS WAS USED
		case "Container":
			break;
		case "Attachment_Iterator":
			Reference["Type_Alias"] = Parameters["Type_Alias"];
			break;
		case "Attachment":
			Reference["ID"] = Parameters["ID"];
			break;
		// Unknown Kind
		default:
			Jelly.Debug.Log("Register: Unknown reference kind: " + Reference["Kind"]);
			Jelly.Debug.Log(Parameters);
			return;
			break;
	}
	
	// Initialize refresh request array.
	Reference["Refresh_Requests"] = [];
	
	// If a corresponding element exists in the DOM, store the element and reference relationship.
	if (Reference["Kind"] != "Non_Standard_Wrapper")
	{
		if (document.getElementById(Reference["Namespace"]))
		{
			// Store the element into the reference
			Reference["Element"] = document.getElementById(Reference["Namespace"]);
		
			// Store the reference into the element
			Reference["Element"].Jelly_Reference = Reference;
		
			// Register a click handler on the element...

			// Copy original click handler if it exists.
			var Original_Click_Handler = null;
			if (Reference["Element"].onclick)
				Original_Click_Handler = Reference["Element"].onclick;

			// Register new click handler.
			Reference["Element"].onclick = 
				function(Event)
				{	
					// TODO: jQuery? 
					if (!Event)
						var Event = window.event;							
				
					// Call the element's original click handler.
					if (Original_Click_Handler)
						Original_Click_Handler(Event);
			
					// Verify click source
					if (!Jelly.Interface.Bubble_Event_Protection('Namespace_Click'))
						return;
							
					// Get target.
					// TODO: jQuery?
					var Target;
					if (Event.target)
						Target = Event.target;
					else if (Event.srcElement) 
						Target = Event.srcElement;
					if (Target.nodeType == 3) // defeat Safari bug
						Target = Target.parentNode;
				
					// Set focus to target
					Jelly.Handlers.Set_Default_Target(Target);
				};
		
			// If Reference is an "Item" in the Database, register a context menu click handler on the reference element.
			// TODO: consider rendering templates for "New" items for the sake of editing their templates (but not including regular edit links)
			if (Reference["Kind"] == "Item" && Reference["ID"] != "New" && Jelly.Show_Context_Menu)
			{
				Reference["Element"].oncontextmenu =
					function(Event)
					{
						// TODO: jQuery?
						if (!Event) 
							var Event = window.event;
					
						// TODO: This was originally written as a hack, but perhaps we need to complete it.
						if (Event.shiftKey)
							return true;
					
						// Verify context menu click source
						if (!Jelly.Interface.Bubble_Event_Protection('Context_Click'))
							return;

						// Handle Webkit bug
						// TODO: Still needed?
						Jelly.Interface.Catch_Webkit_Context_Click_Bug();
					
						// Show context menu
						Jelly.Interface.Show_Context_Menu({"Target_Element": this, "Event": Event});
					
						return false;
					};
			}

			// Add draggable info
			// TODO: this isn't reliable now because of <span> problems in most browsers...
			// TODO: FEATURE
			/*
			switch (Reference["Kind"])
			{
				case "Iterator":
					if (Parameters["Parent_Property_Alias"])
					{
	//						if (Parameters["Parent_Property_Alias"] == "Child_Page")
						jQuery(Reference["Element"]).droppable(
							{
								hoverClass: 'Jelly_Droppable',
								tolerance: "pointer",
								greedy: true,
								drop: function (Event, UI)
								{
									Jelly.Debug.Log(UI.draggable.context);
									Jelly.Debug.Log("dropped");
								
									var Draggable_Reference = Jelly.References.Get_Reference_For_Element(UI.draggable.context);
								
									// Make sure type aligns
									// TODO: incorporate subtypes, and move this to while it's dragging so targets don't highlight
									Jelly.Debug.Log(Parameters);
									Jelly.Debug.Log(Draggable_Reference);
									if (Parameters["Parent_Property_Value_Type"] != Draggable_Reference.Type)
										return;
								
									switch (Parameters["Parent_Property_Relation"])
									{
										case "Many-To-One":
										case "One-To-One":
											var Action_Parameters =
											{
												Edit_Item: Parameters["Parent"],
												Edit_Item_Type: Parameters["Parent_Type"]
											};
											Action_Parameters["Edited_" + Parameters["Parent_Property_Alias"]] = Draggable_Reference.ID;
											Action_Parameters["Edited_" + Parameters["Parent_Property_Alias"] + "_Type"] = Draggable_Reference.Type;
											Jelly.Actions.Execute
											(
												{
													Action: "Edit",
													Namespace: "",
													Parameters: Action_Parameters
												}
											);
											break;
										case "Many-To-Many":
										case "One-To-Many":
											Jelly.Actions.Execute
											(
												{
													Action: "Add_Item_To_Item",
													Namespace: "",
													Parameters:
													{
														Item: Draggable_Reference.ID,
														Item_Type: Draggable_Reference.Type,
														Target: Parameters["Parent"],
														Target_Type: Parameters["Parent_Type"],
														Target_Property: Parameters["Parent_Property_Alias"]
													}
												}
											);
											break;
									}
								}
							}
						);
					}
					break;
				case "Item":
					{
						if (Parameters["Draggable"])
						{
							jQuery(Reference["Element"]).draggable
							(
								{
									cursor: "move",
									helper: "clone",
									appendTo: "body",
									revert: true
								}
							);
						}
					}
					break;
			}
			*/
		}
	}

	// If previous reference for namespace exists, copy parent reference and handlers and then remove the previous reference.
	// TODO - anything else to copy? should handlers even be copied?
	// TODO - make sure inputs are correctly re-registered, if we're doing shit this way.
	if (Jelly.References.References_By_Namespace[Parameters["Namespace"]])
	{
		var Previous_Reference_For_Namespace = Jelly.References.References_By_Namespace[Parameters["Namespace"]];

		// Copy parent reference
		// TODO - allow escape? 
		if (Previous_Reference_For_Namespace['Parent_Reference'])
		{
			Reference['Parent_Reference'] = Previous_Reference_For_Namespace['Parent_Reference'];
			Reference['Parent_Namespace'] = Reference['Parent_Reference']['Namespace'];
		}
		else
			Reference['Parent_Reference'] = null;
		
		// Copy handlers
		for (var Event_Name in Previous_Reference_For_Namespace.Handlers)
			Reference.Handlers[Event_Name] = Previous_Reference_For_Namespace.Handlers[Event_Name];

		// Remove existing reference
		// TODO: "Is this the best way to handle garbage collection... ?" - Tristan Perich, 2014	
		Jelly.References.Remove_Reference(Previous_Reference_For_Namespace);
	}
	
	// Otherwise get parent reference from parameterss
	else
	{
		// Store parent reference relationship.
		if (Parameters["Parent_Namespace"] != "Jelly")
		{
			if (!Parameters["Parent_Namespace"])
			{
				Jelly.Debug.Display_Error("Register: No Parent_Namespace provided");
				Jelly.Debug.Log(Parameters);
				return;
			}

			// Validate parent namespace.
			if (!Jelly.References.References_By_Namespace[Parameters["Parent_Namespace"]])
			{
				// TODO: report original DOM element no longer exists (i.e. on a [Go /] action result)
				Jelly.Debug.Display_Error("Register: Reference for Parent_Namespace does not exist: " + Parameters["Parent_Namespace"])
				if (Debug)
					Jelly.Debug.Log(Reference);
				return;
			}

			// Store parent reference to this reference
			Reference["Parent_Reference"] = Jelly.References.References_By_Namespace[Parameters["Parent_Namespace"]];
		}
		else
		{
			// Global references have no parent reference
			Reference["Parent_Reference"] = null;
		}
	}
	
	// Add this reference to parent reference's children
	if (Reference["Parent_Reference"])
		Reference["Parent_Reference"]["Child_References"].push(Reference);
		
	// Store reference by namespace
	Jelly.References.References_By_Namespace[Reference["Namespace"]] = Reference;
				
	// Append reference to list of references for this kind (and lookup)
	switch(Reference["Kind"])
	{
		case "Attachment_Iterator":
		case "Iterator":
			// If it doesn't exist, instantiate a list of references for this ID.		
			if (!Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]])
				Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]] = new Array();

			// Add this reference to the list.			
			Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]].push(Reference);
			break;
			
		case "Attachment":
		case "Item":
			// If it doesn't exist, instantiate a list of references for this ID.		
			if (!Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["ID"]])
				Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["ID"]] = new Array();

			// Add this reference to the list.			
			Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["ID"]].push(Reference);
			
			// If is container item, set container item reference.
			if (Parameters['From_Container'])
				Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"] = Reference;
				
			// If is site item, set site item reference
			if (Parameters["Type_Alias"] == "Site" && Parameters['From_Request'])
				Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"] = Reference;
			break;
			
		case "URL":
			// Add this reference to the list.
			Jelly.References.Reference_Lookups_By_Kind["URL"].push(Reference);
			break;
			
		case "Non_Standard_Wrapper":
			// Special case references with specific  handling.
			switch (Reference["Name"])
			{
				case "Site_Icon":
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"].push(Reference);
					break;
		
				case "Document_Title":
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"].push(Reference);
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"].push(Reference);
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Secondary"].push(Reference);
					break;
		
				case "Current_Path":
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"].push(Reference);
					break;
				default:
					break;
			}
			break;
			
			
		case "Container":
			// Register primary path reference.
			Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Primary"] = Reference;

			// Mark all parent references as "design only" to fix refreshing problems
			// TODO improve design-only refreshing. Perhaps by marking the main site design specifically, etc. Well, definitely by doing that.
			var Recursive_Parent_Reference = Reference["Parent_Reference"];
			var Set_To_Design_Only = true;
			while (Recursive_Parent_Reference)
			{
				// If we have not yet passed a design item, then keep setting Design Only to true
				if (Set_To_Design_Only)
					Recursive_Parent_Reference["Design_Only"] = true;

				// If we pass a design item, stop setting Design Only to true.				
				if (Recursive_Parent_Reference["Kind"] == "Item" && Recursive_Parent_Reference["Type_Alias"] == "Design")
					Set_To_Design_Only = false;

				Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"];
			}
			break;
		
		case "HTML":
			// TODO anything to do here?
			// TODO - does this do anything? no? 
			break;
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Reference...");
		Jelly.Debug.Log(Reference);
		Jelly.Debug.End_Group("Register");
	}
	
	// Return Reference
	return Reference;
};