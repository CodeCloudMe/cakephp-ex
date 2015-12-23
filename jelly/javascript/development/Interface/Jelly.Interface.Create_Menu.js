Jelly.Interface.Create_Menu = function(Parameters)
{
	// Generate a menu within a global reference, attach it to the existing menu stack or set it as the base menu, fill or copy in content, and attach menu handlers/behaviors.

	// Parameters: Attach, Attach_Element, Event, Edge, Restrict_Position, Do_Not_Focus_First_Item, 
	// TODO: additional parameters as x-reffed below.
	// TODO: clean up 'attach', 'attach element'
	// ... Post_Values
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create Menu");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Block refreshing on a namespace
	// TODO - remove after property refreshing
	if (Parameters["Block_Refresh"])
	{	
		Jelly.References.Block_Refresh({'Namespace': Parameters["Block_Refresh"]});
	}
		
	// Generate menu reference
	var Menu_Parameters = {};

	// TODO - handle this kind of thing better
	if (Parameters["Source_Element_ID"])
	{	
		Menu_Parameters["Kind"] = "HTML";
	}	
	else
	{
		Menu_Parameters["Kind"] = "URL";
		Menu_Parameters["URL"] = Parameters["URL"];
		Menu_Parameters["Post_Values"] = Parameters["Post_Values"];
	}
	Menu_Parameters["Parent_Namespace"] = "Jelly";
	
	// Set unique namespace
	Jelly.References.Current_Global_Reference_Index++;
	var Menu_Namespace = Jelly.Interface.Global_Controls_Element.id + Jelly.Namespace_Delimiter + Jelly.References.Current_Global_Reference_Index + Jelly.Namespace_Delimiter + Parameters["Alias"];
	Menu_Parameters["Namespace"] = Menu_Namespace;
		
	// Don't create if already exists (maybe to make sure we don't recreate menus that are already open)
	// TODO: This good?
	if (document.getElementById(Menu_Namespace))
		return;
		
	// Create menu element and add to DOM in global controls
	var Menu_Control_Element = Jelly.Interface.Generate_Menu(Menu_Namespace);
	Jelly.Interface.Global_Controls_Element.appendChild(Menu_Control_Element);
	
	// Copy over anything else needed from Parameters	
	
	// Register our new menu
	var Menu_Reference = Jelly.References.Register(Menu_Parameters);
	
	// TODO: A lot of this element hooplah can be handled with the whole Generate_Menu... approach, for better or for worse, probably for better.
	// TODO: Then this function would just handle the references and any content requests
		
	// Store menu's Control Element in reference
	Menu_Reference["Control_Element"] = Menu_Control_Element;
	// TODO - bold move... also, totally destroys any one to one relationship here.
	Menu_Control_Element.Jelly_Reference = Menu_Reference;
	
	// Set reference's element, and vice versa. 
	var Menu_Element = Menu_Reference["Element"];
	Menu_Element.Jelly_Reference = Menu_Reference;
	
	// Place the menu element at the topmost menu z-index
	// TODO - "Do I think we should have a better way to do this? Yes!" - Tristan, 2014
	var Menu_Count = 0;
	var Recursive_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
	while (Recursive_Menu_Reference)
	{
		Menu_Count++;
		
		// Traverse child menu reference
		Recursive_Menu_Reference = Recursive_Menu_Reference.Child_Menu;
	}
	Menu_Control_Element.style.zIndex = Jelly.Interface.Menu_Z_Index_Start + Menu_Count;
	
	// Attach menu to parent menu, or set as base menu.
	var Is_Base_Menu = true;
	
	// If an attach element is designated,  find a menu reference corresponding to it.
	// TODO: Is there a simpler format of this search? I think not...
	// TODO: Do we need "attach"?
	if (Parameters["Attach"])
	{
		// Verify that the attach element, or any of it's parents, match any reference in the menu reference stack.
		var Search_Menu_Element = Parameters["Attach_Element"];
		while (Search_Menu_Element)
		{
			// Start from base menu reference
			var Search_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
			
			while (Search_Menu_Reference)
			{
				// If the menu reference matches this attach element, store it // TODO: and break?
				if (Search_Menu_Reference["Element"] == Search_Menu_Element)
				{
					var Parent_Menu_Reference = Search_Menu_Reference;
					Is_Base_Menu = false;
					// TODO: probably break here, no? 
					// TODO: Added a break 2 here, though I didn't write this code originally, so should verify.
					// break 2;
				}
					
				// Search next child reference.
				Search_Menu_Reference = Search_Menu_Reference.Child_Menu;
			}
			
			// Search next parent element.
			Search_Menu_Element = Search_Menu_Element.parentNode;
		}
		
		// If attaching to an element, also set it as the calling reference
		// TODO: Do we still store this here, without verifying it passed the above test?
		// TODO: Do we store attach_element, or the found parent_menu_element, as the calling reference?
		Menu_Reference["Calling_Reference"] = Jelly.References.Get_Reference_For_Element(Parameters["Attach_Element"]);
	}
	
	// If this is a base menu, set up the base menu
	if (Is_Base_Menu)
	{	
		// Store reference to base menu of future menu tree
		// TODO: X-Ref and Make sure this has everything needed
		Jelly.Interface.Base_Menu_Reference = Menu_Reference;	
	}
	
	// Otherwise, add it to the parent menu
	else
	{
		// Store parent/child menu references
		Parent_Menu_Reference.Child_Menu = Menu_Reference;
		Menu_Reference.Parent_Menu = Parent_Menu_Reference;
	}

	// Request or copy content for this menu.... 
	
	// TODO: ?! (really? )
	if (Menu_Reference["Kind"] == "HTML")
	{
		// Copy inner HTML from source element
		Menu_Control_Element.innerHTML = document.getElementById(Parameters["Source_Element_ID"]).innerHTML;
		
		// Position menu in final position
		Jelly.Interface.Position_Container({
			Event: Parameters["Event"],
			Element: Menu_Control_Element,
			Edge: Parameters["Edge"],
			Attach: Parameters["Attach"],
			// TODO: Should it be this one, or the found attach_element?
			Attach_Element: Parameters["Attach_Element"],
			Restrict_Position: Parameters["Restrict_Position"],
			Update_Mouse_Position: true
		});

	}
	else
	{
		// Position menu in initial position
		Jelly.Interface.Position_Container({
			Event: Parameters["Event"],
			Element: Menu_Control_Element,
			Edge: Parameters["Edge"],
			Attach: Parameters["Attach"],
			// TODO: Should it be this one, or the found attach_element?
			Attach_Element: Parameters["Attach_Element"],
			Restrict_Position: Parameters["Restrict_Position"],
			Update_Mouse_Position: true
		});

		// Refresh by URL (will position in final position on load)
		Jelly.References.Refresh(Menu_Reference);
	}

	
	// Inform any close_menus calls in this specific event bubble,  such as the document mousedown call, to keep this menu open, via a quick ephemeral variable setting
	Jelly.jQuery(Menu_Control_Element).mousedown(function(event)
			{	
				// TODO - see if I can integrate with the rest of the bubble handling, don't quite like the special case.
				// TODO - aka Jelly.Bubble_Event_Protection_With_Value(...);
				Jelly.Interface.Active_Menu_Reference = Menu_Reference;
				Jelly.Interface.Timeout("Jelly.Interface.Active_Menu_Reference = null;");
			}
		);
		
	// Fade menu in
	Menu_Control_Element.style.display = "none";
	Jelly.jQuery(Menu_Control_Element).fadeIn();
	
	// TODO - there used to be an explicit Active_Menu_Reference bubble run where this comment lies, but I removed it, didn't seem necessary

	// Attach dismiss, cancel, done handlers to close menus
	// TODO - consistent naming? 
	Jelly.Handlers.Register_Handler(
			{
				"Element": Menu_Element, 
				"Event": "Dismiss", 
				"Code": function() 
					{
						// Release refreshing on a namespace
						// TODO - remove after property refreshing
						if (Parameters["Block_Refresh"])
						{
							Jelly.References.Release_Refresh({'Namespace': Parameters["Block_Refresh"]});
						}
						
						// Ignore the active menu reference protection for this menu, since the dismiss handler is called directly.
						Jelly.Interface.Close_Menus({'Force_Close_Active_Menu': true});
					}
			}
		);
	
	//TODO - do we need Done, Cancel in Menus? 
	Jelly.Handlers.Register_Handler(
			{
				"Element": Menu_Element, 
				"Event": 
					[
						"Cancel", 
						"Done"
					],
				"Code": function() 
					{
						var Parameters = 
						{	
							"Event": "Dismiss",
							"Target": Menu_Element
						}
						Jelly.Handlers.Call_Handler_For_Target(Parameters);
					}
			}
		);
	
	// Catch  & ignore "execute" events
	// TODO - not sure what this is about
	Jelly.Handlers.Register_Handler(
			{
				"Element": Menu_Element,
				"Event": "Execute", 
				"Code": function()
					{
					}
			}
		);
	
	// On Load: Reposition menu, focus first item, set up link behaviors, set up close menu behaviors
	Jelly.Handlers.Register_Handler(
			{
				"Element": Menu_Element,
				"Event": "On_Load",
				"Code": function()
					{	
						var Debug = false && Jelly.Debug.Debug_Mode;

						if (Debug)
						{
							Jelly.Debug.Group("Create_Menu On_Load_Function");
						}
								
						// Reposition menu, now with filled content.
						Jelly.Interface.Position_Container(
							{
								Event: Parameters["Event"],
								Element: Menu_Control_Element,
								Edge: Parameters["Edge"],
								Attach: Parameters["Attach"],
								Attach_Element: Parameters["Attach_Element"],
								Restrict_Position: Parameters["Restrict_Position"],
								Update_Mouse_Position: true
							}
						);
						
						// Set cursor to pointer for all menu rows with links
						Jelly.jQuery(Menu_Control_Element).find(".Jelly_Menu_Row").each(function (Index) {
								if (Jelly.jQuery(this).find("a:first").length > 0)
									this.style.cursor = "pointer";
							});
							
						// Add focus event to display highlight for menu row
						Jelly.jQuery(Menu_Control_Element).find("a").focus(function(event)
							{
								// Highlight the menu row
								Jelly.Interface.Highlight_Menu_Item({"Menu_Item": this});
							});

						// Focus first item, if not specified otherwise
						if (!Parameters["Do_Not_Focus_First_Item"])
						{
							Jelly.Interface.Focus_First_Menu_Item({"Menu_Element": Menu_Control_Element});
						}
		
						// Setup menu rows to pass mouseovers into inner links.
						Jelly.jQuery(Menu_Control_Element).find(".Jelly_Menu_Row").mouseover(function(event)
							{
								// If event's target isn't within a link, then highlink first link
								if (event.target.nodeName != "A" && Jelly.jQuery(event.target).parents("a").length == 0)
									Jelly.jQuery(this).find("a:first").mouseover();
							});
		
						// Convert link mouse overs to focus events, unless specified
						// TODO - test if works.
						if (!Parameters["Do_Not_Focus_On_Hover"])
						{
							Jelly.jQuery(Menu_Control_Element).find("a").mouseover(function(event)
								{
									Jelly.jQuery(this).focus();
								});
						}
						
						// If you want to keep the focus on another control, this allows the browser to look like it's focused without actually focusing.
						// TODO - test if works.
						else
						{							
							Jelly.jQuery(Menu_Control_Element).find("a").mouseover(function(event)
								{
									// Highlight the menu row.
									Jelly.Interface.Highlight_Menu_Item({"Menu_Item": this});
								});
						}
			
						// Setup menu rows to pass clicks into inner links.
						Jelly.jQuery(Menu_Control_Element).find(".Jelly_Menu_Row").click(function(event)
							{
								// If event's target isn't within a link, then click first link
								if (event.target.nodeName != "A" && Jelly.jQuery(event.target).parents("a").length == 0)
									Jelly.jQuery(this).find("a:first").click();
							});
												
						// Add click handler to menu links to close their menus, unless specified otherwise
						Jelly.jQuery(Menu_Control_Element).find("a").click(function(event)
							{	
								// Call the dismiss handler, which closes the menu on click, unless specified otherwise
								if (!Jelly.jQuery(this).hasClass("Do_Not_Close_Menu_On_Click"))
								{
									Jelly.Handlers.Call_Handler_For_Target({'Event': 'Dismiss', 'Target': this});								
								}
							});
		
						if (Debug)
							Jelly.Debug.End_Group("Create_Menu On_Load_Function");
					}
			}
		);

	// Call on load handler directly if copying loaded data,
	if (Parameters["Source_Element_ID"])
	{	
		Jelly.Handlers.Call_Handler_For_Target(
				{
					"Event": "On_Load",
					"Target": Menu_Element
				}
			);
	}

	if (Debug)
		Jelly.Debug.Log(Menu_Reference);
		
	if (Debug)
		Jelly.Debug.End_Group("Create Menu");
	
	// Return the reference
	return Menu_Reference;
};