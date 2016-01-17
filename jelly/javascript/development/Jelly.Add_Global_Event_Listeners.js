Jelly.Add_Global_Event_Listeners = function ()
{
	// Add document level click, mouse down, mouse move, key down, and scroll handlers...
	
	// TODO: More modern binding 
	if (document.addEventListener)
	{
		document.addEventListener("click", Jelly.Handlers.Document_Click, false);
		document.addEventListener("mousedown", Jelly.Handlers.Document_Mouse_Down, false);
		document.addEventListener("keydown", Jelly.Handlers.Document_Key_Down, false);
		document.addEventListener("mousemove", Jelly.Handlers.Document_Mouse_Move, false);
		document.addEventListener("scroll", Jelly.Handlers.Document_Scroll, false);
		if (Jelly.Show_Context_Menu)
			document.addEventListener("contextmenu", Jelly.Handlers.Document_Context_Click, false);
	}
	else if (document.attachEvent)
	{
		document.attachEvent("click", Jelly.Handlers.Document_Click);
		document.attachEvent("mousedown", Jelly.Handlers.Document_Mouse_Down);
		document.attachEvent("keydown", Jelly.Handlers.Document_Key_Down);			
		document.attachEvent("mousemove", Jelly.Handlers.Document_Mouse_Move);
		document.attachEvent("scroll", Jelly.Handlers.Document_Scroll);
		if (Jelly.Show_Context_Menu)
			document.addEventListener("contextmenu", Jelly.Handlers.Document_Context_Click, false);
	}
};