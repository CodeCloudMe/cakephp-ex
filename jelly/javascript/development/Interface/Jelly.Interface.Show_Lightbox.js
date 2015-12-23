Jelly.Interface.Show_Lightbox = function()
{
	// Generate lightbox if it doesn't exist, then move it to beneath top window, and display it. 
	 
	// Generate lightbox, if it doesn't exist.
	if (!Jelly.Interface.Lightbox_Element)
	{
		Jelly.Interface.Lightbox_Element = Jelly.Interface.Generate_Lightbox();
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Lightbox_Element);
	}

	// Move lightbox beneath highest window.
	// TODO: Strange Z-Index metric. I guess it makes sense, but I'm leaving this here in case we decide to switch to -1, 0 instead of 0, +1 as it is now.
	Jelly.Interface.Lightbox_Element.style.zIndex = Jelly.Interface.Window_Z_Index_Start + Jelly.Interface.Modal_Windows.length * 2;	
	
	// Fade in lightbox
	Jelly.Interface.Lightbox_Element.style.display = "none";
	Jelly.jQuery(Jelly.Interface.Lightbox_Element).fadeIn("fast");
};