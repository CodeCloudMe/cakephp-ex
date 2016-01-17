Jelly.Handlers.Document_Key_Down = function(Event)
{
	// Call handlers functions for document level key down clicks.

	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Log("Document_Key_Down...");
		Jelly.Debug.Log(Event);
		Jelly.Debug.Log("Key Down: " + Key_Code);
	}
	
	// Get Event and Event Target
	if (!Event) var Event = window.event;
	var Target;
	if (Event.target) 
		Target = Event.target;
	else if (Event.srcElement) 
		Target = Event.srcElement;
	// TODO What Safari bug? 
	if (Target.nodeType == 3) // defeat Safari bug
		Target = Target.parentNode;		
	
	if (Debug)
	{
		Jelly.Debug.Log("Target...");
		Jelly.Debug.Log(Target);
	}
	
	// If document event, target last clicked element
	if (Target.id == "Body" || Target.nodeName == "HTML")
		if (Jelly.Handlers.Default_Target)
			Target = Jelly.Handlers.Default_Target;
	
	// Get Target Type
	var Target_Type = Target.nodeName;
	
	// Get Event Key Code
	var Key_Code;
	if (Event.keyCode) Key_Code = Event.keyCode;
	else if (Event.which) Key_Code = Event.which;
	var Character = String.fromCharCode(Key_Code);
	if (Debug)
		Jelly.Debug.Log("Key Down: " + Key_Code);
		
	// Get Event Modifer
	// TODO - clarify alt, meta
	var Command_Modifier = (Event.altKey || Event.metaKey);
	var Direction_Modifier = Event.shiftKey;
				
	// TODO: Not sure why we're abstracting this yet, but I see it some places, not some others, so unifying
	var Prevent_Default_Behavior = false;
	switch (Key_Code)
	{
		// Enter
		case 13:		
			if (Debug)
			{
				Jelly.Debug.Log("Enter");
			}
			
			switch (Target_Type)			
			{
				// Handle text areas.
				case "TEXTAREA":
				{
					// If the current element is a text area, and a modifier key is held down, execute the current action.
					if (Command_Modifier)
					{
						// Prevent default behavior
						Prevent_Default_Behavior = true;
						
						Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': Target});
					}
							
					// Otherwise, insert an intelligently indented return character.
					else
					{
						// Prevent default behavior
						Prevent_Default_Behavior = true;
					
						Jelly.Interface.Insert_Return_With_Indented_Text(Target);
						
						var Autosize_Update_Event = document.createEvent('Event');
						Autosize_Update_Event.initEvent('autosize:update', true, false);
						Target.dispatchEvent(Autosize_Update_Event);
					}
					
					break;
				}
				case "DIV":
				{
					// If the current element is a content-editable div, and a modifier key is held down, execute the current action.
					if (Target.contentEditable == "true" && Command_Modifier)
					{
						// Prevent default behavior
						Prevent_Default_Behavior = true;
					
						Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': Target});
					}
					
					break;
				}
					
				// Handle all non-textarea elements.
				default:
					// Execute the current action.
					Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': Target});
					break;					
			}
			break;
			
		// Tab
		case 9:
			if (Debug)
			{
				Jelly.Debug.Log("Tab");
			}
			
			switch (Target_Type)
			{
				// Handle text areas.				
// 				case "TEXTAREA":
// 				
// 					// TODO: not consistent with the above, maybe... 
// 					if (!Command_Modifier)
// 					{
// 						// Prevent default behavior for tab keys in a text area.
// 						Prevent_Default_Behavior = true;	
// 						
// 						if (Event.shiftKey)
// 							Jelly.Interface.Remove_Tab(Target);
// 						else
// 							Jelly.Interface.Insert_Tab(Target);
// 					}
// 					break;
			}
			
			break;
		
		/* Continue from Here (TODO: What?!) */


		// Escape
		case 27:
			if (Debug)
			{
				Jelly.Debug.Log("Cancel");
			}
					
			// TODO: Hm, do we need to check for target for all of these? 
			if (Target)
			{
				//TODO - "event:escape"?
				// Call cancel handler for this target.
				Jelly.Handlers.Call_Handler_For_Target({'Event': 'Cancel', 'Target': Target});
				
				// Call dismiss handler for this target.
				Jelly.Handlers.Call_Handler_For_Target({'Event': 'Dismiss', 'Target': Target});
				
				// Prevent default behavior for escape.
				Prevent_Default_Behavior = true;
			}
			break;

		//Up arrow		
		case 38:
		// Down arrow
		case 40:
		//Left arrow
		case 37:
		//Right arrow
		case 39:				
			if (Debug)
			{
			}
			
			// If a menu is open, select the next or previous menu item
			if (Jelly.Interface.Base_Menu_Reference)
			{
				// Prevent default behavior for arrow keys when a menu is open.
				Prevent_Default_Behavior = true;
						
				// Find the lowest child menu reference.
				var Current_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
				while (Current_Menu_Reference.Child_Menu)
					Current_Menu_Reference = Current_Menu_Reference.Child_Menu;

				// Find the target menu item...
				var Target_Menu_Item = null;				
				
				// Determine whether to find the next or the previous menu item.
				if (Key_Code == 38 || Key_Code == 37)
					Menu_Direction = -1;
				else
					Menu_Direction = 1;
			
				// TODO: should menus have some additional ordering attribute instead?
				// Determine whether to consider the top or the left edge of a menu
				if (Key_Code == 38 || Key_Code == 40)
					Menu_Boundary_Property = "offsetTop";
				else
					Menu_Boundary_Property = "offsetLeft";

				// TODO: Settle on tag name or class name 
				var Current_Menu_Items = Current_Menu_Reference["Element"].getElementsByTagName("a");//document.getElementsByClassName("Jelly_Menu_Item");

				// Iteratively identify the previous or next menu item as the target
				for (var Current_Menu_Item_Index in Current_Menu_Items)
				{
					//TODO - is for each deprecated?
					//TODO - list 
					if (Menu_Items.hasOwnProperty(Menu_Item_Index))
					{
						var Current_Menu_Item = Current_Menu_Items[Current_Menu_Item_Index];
						
						// Consider the current menu item as the target if it is in the right direction, or if nothing is selected
						if (!Jelly.Interface.Selected_Menu_Item || Current_Menu_Item[Menu_Boundary_Property] * Menu_Direction > Jelly.Interface.Selected_Menu_Item[Menu_Boundary_Property] * Menu_Direction)

							// Consider the current menu item as the target if it is closer than the last target, or if there is no target being considered
							if (!Target_Menu_Item || Current_Menu_Item[Menu_Boundary_Property] * Menu_Direction < Next_Menu_Item[Menu_Boundary_Property] * Menu_Direction)
								Target_Menu_Item = Current_Menu_Item;
					}
				}
				
				// Focus the target menu item, if it has been found
				if (Target_Menu_Item)
					Jelly.Interface.Highlight_Menu_Item({"Menu_Item": Target_Menu_Item});

				// TODO: verify that this is for single item menus.
				// Otherwise, focus the current selected menu item
				else
					Jelly.Interface.Highlight_Menu_Item({"Menu_Item": Jelly.Interface.Selected_Menu_Item});
			
				// TODO: There used to be a line calling document mouse move, I changed it to call the direct action.
				Jelly.Interface.Hide_Highlights();
			}
			break;

// TODO: Remove below after testing above.	
/*
		// Left arrow
		case 37:
		// Right arrow
		case 39:
			var Direction = 1;
			if (Key_Code == 37)
				Direction = -1;
			
			if (Jelly.Interface.Base_Menu_Reference)
			{
				// Find lowest menu
				var Current_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
				while (Current_Menu_Reference.Child_Menu)
					Current_Menu_Reference = Current_Menu_Reference.Child_Menu;
					
//						Jelly.Debug.Log("Selected Menu Item");
				e.preventDefault();
				var Next_Menu_Item = null;
				var Menu_Items = Current_Menu_Reference["Element"].getElementsByTagName("a");//document.getElementsByClassName("Jelly_Menu_Item");
				for (var Menu_Item_Index in Menu_Items)
				{
					if (Menu_Items.hasOwnProperty(Menu_Item_Index))
					{
						var Menu_Item = Menu_Items[Menu_Item_Index];
//							Jelly.Debug.Log(Menu_Item.offsetLeft);
						if (!Jelly.Interface.Selected_Menu_Item)
							Next_Menu_Item = Menu_Item;
						else
						{
								if (Menu_Item.offsetLeft * Direction > Jelly.Interface.Selected_Menu_Item.offsetLeft * Direction)
									if (!Next_Menu_Item || Menu_Item.offsetLeft * Direction < Next_Menu_Item.offsetLeft * Direction)
										Next_Menu_Item = Menu_Item;
						}
					}
				}
				if (Next_Menu_Item)
					Jelly.Interface.Highlight_Menu_Item({"Menu_Item": Next_Menu_Item});
			}
			break;
*/
		// TODO: Needed?
		// Hide interface highlights on key down.
		default:	
			Jelly.Interface.Hide_Highlights();		
			break;
	}
	
	// TODO: this is a hack to see if we're getting a document key event			
	// TODO: and then??? why return false? commented out for now. - kunal
//			if (Target.nodeName == "HTML")
//			{
//				Jelly.Debug.Log("DOC");
//				Return_Value = false;
//			}		
	
	if (Debug)
		Jelly.Debug.Log('Prevent_Default_Behavior:' + Prevent_Default_Behavior);	

	// Compatibility - Chrome/Safari, prevent following KeyPress event from firing and inserting character.
	if (Prevent_Default_Behavior)
	{	
		Event.preventDefault();
		// TODO: clean?
		return false;
	}
	else 
		return true;
};