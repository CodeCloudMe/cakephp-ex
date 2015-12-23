Jelly.Interface.Focus_First_Control = function(Focus_Element)
{
	// Finds the first visible input in the element, and focuses on it and returns truel, or returns false if none exist
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Log("Focus_First_Control");
		Jelly.Debug.Log(Focus_Element);
	}

	// Find first visible input or textarea
	var Child_Node = Jelly.jQuery(Focus_Element).find("input:visible,textarea:visible").get(0);

	// If there is a first visible input or textarea, then focus on it
	if (Child_Node)
	{
		// TODO: What do these do, exactly?
		Child_Node.focus()
		Child_Node.select();
		return true;
	}
	// Or return false if there are no inputs to focus on.
	else
		return false;
};