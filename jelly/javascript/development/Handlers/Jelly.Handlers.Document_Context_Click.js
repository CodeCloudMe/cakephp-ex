Jelly.Handlers.Document_Context_Click = function(Event)
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

	// Show context menu for reference attached to Body Element (Site?)
	// TODO - verify that this works.
	Jelly.Interface.Show_Context_Menu({"Target_Element": Jelly.Body_Element, "Event": Event});

	Event.preventDefault();
};