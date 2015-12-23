Jelly.Interface.Close_Menus = function(Parameters)
{
	// Close all menus, or all menus below the designated active menu, and clean up as needed.
	// aka Close users if user didn't click on a menu
	// TODO: search through event target's parents to determine the active menu might be a better way?
	// Parameters: Force_Close_Active_Menu
		
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Close_Menus");
		Jelly.Debug.Log("Active Menu Reference...");
		Jelly.Debug.Log(Jelly.Interface.Active_Menu_Reference);
		Jelly.Debug.Log("Base Menu Reference...");
		Jelly.Debug.Log(Jelly.Interface.Base_Menu_Reference);
	}
	
	// Traverse through base menu's child menus, removing every menu below the active menu.
	var Active_Menu_Reference = Jelly.Interface.Active_Menu_Reference;
	var Recursive_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
	var Found_Menu = false;
	var Force_Close_Active_Menu = false;
	if (Parameters && Parameters['Force_Close_Active_Menu'])
		Force_Close_Active_Menu = true;
		
	while (Recursive_Menu_Reference)
	{
		if (Debug)
		{
			Jelly.Debug.Log("Recursive menu check...(next two lines)");
			Jelly.Debug.Log(Recursive_Menu_Reference);
			Jelly.Debug.Log(Found_Menu);
		}
		
		// Remove menu if there is no active menu, or it's a descendant the active menu, or the menu is specified to be force closed.
		if (Force_Close_Active_Menu || Active_Menu_Reference == null || (Active_Menu_Reference != null && Found_Menu !== false))
		{
			if (Debug)
				Jelly.Debug.Log("Will Close this one.");
				
			// TODO: Should this call some generic close_menu function instead? 
			// Unlink from parent menu
			if (Recursive_Menu_Reference.Parent_Menu)
			{
				Recursive_Menu_Reference.Parent_Menu.Child_Menu = null;
				Recursive_Menu_Reference.Parent_Menu = null;
			}
			
			// Remove from DOM
			var Menu_Reference = Recursive_Menu_Reference;
			Jelly.Interface.Fade_Out_And_Remove(Menu_Reference["Control_Element"]);

			// Remove reference
			Jelly.References.Remove_Reference(Menu_Reference);
		}
		
		// If the active menu has been traversed, set a flag to change subsequent behavior
		if (Recursive_Menu_Reference == Active_Menu_Reference)
			Found_Menu = true;
		
		// Traverse child menu reference
		Recursive_Menu_Reference = Recursive_Menu_Reference.Child_Menu;
	}
	
	// If all menus have been closed, clean up
	if (!Found_Menu)
	{
		// Remove reference to base menu
		Jelly.Interface.Base_Menu_Reference = null;
	}
	
	// TODO: Does this look right?
	Jelly.Interface.Selected_Menu_Item = null;
	
	// Clean references
	// TODO: Does this belong here? 
	Jelly.References.Clean_References();
	
	if (Debug)
		Jelly.Debug.End_Group("Close_Menus");
};