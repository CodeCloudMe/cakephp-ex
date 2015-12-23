Jelly.Interface.Focus_First_Menu_Item = function(Parameters)
{
	// Focus on first anchor, or return false if no anchors
	// TOOD ...
	// TODO - actually return false if no anchors
	
	// Parameters: Menu_Element
	
	var Menu_Element = Parameters["Menu_Element"];
	
	Jelly.jQuery(Menu_Element).find("a:first").focus()
};