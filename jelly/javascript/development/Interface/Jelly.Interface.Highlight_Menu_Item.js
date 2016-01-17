Jelly.Interface.Highlight_Menu_Item = function(Parameters)
{
	// Removes focus from previous selected menu item as needed, and sets the focus to the passed in menu item (by class, browser focus, and jelly handler focus)
	
	//	Parameters: Menu_Item
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Focus_Menu_Item");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	var Menu_Item = Parameters["Menu_Item"];
	
	// Unfocus last menu item, by removing focused class
	// TODO: Is this the right way to do it? Maybe. It seems to get any focused row of the registered selected item and unselect it. why these specific bounds?
	// TODO: Does jQuery protect against a variable that doesn't exist or is null, say in the initial jQuery?
	Jelly.jQuery(Jelly.Interface.Selected_Menu_Item).parents(".Jelly_Menu_Row.Jelly_Menu_Row_Focused").removeClass("Jelly_Menu_Row_Focused");
	
	// Register selected menu item as this menu item
	Jelly.Interface.Selected_Menu_Item = Menu_Item;
		
	// Select this new menu item by adding focused class
	Jelly.jQuery(Jelly.Interface.Selected_Menu_Item).parents(".Jelly_Menu_Row").addClass("Jelly_Menu_Row_Focused");
	
	// Set the default target to this menu item.
	Jelly.Handlers.Set_Default_Target(Menu_Item);
	
	if (Debug)
		Jelly.Debug.End_Group("");
};