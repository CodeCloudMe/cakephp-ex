// TODO:  maybe analyze/rewrite with some better jQuery understanding, etc.

Jelly.Interface.Position_Container = function(Parameters)
{

	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Position_Container");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	var Element = Parameters["Element"];
	var Restrict_Position = Parameters["Restrict_Position"];
	var Attach = Parameters["Attach"];
	var Attach_Element = Parameters["Attach_Element"];
	var Edge = Parameters["Edge"];
	
	var Grow_Y_Direction = "Down_Then_Up";
	var Grow_X_Direction = "Right_Then_Left";
	
	var Element_Position = {Left: Jelly.jQuery(Element).offset()["left"], Top: Jelly.jQuery(Element).offset()["top"]};
	
	// Get true element size
	var Element_Size = {};
	Element.style.width = "";
	Element.style.height = "";
	Element_Size["Width"] = Element.offsetWidth;
	Element_Size["Height"] = Element.offsetHeight;
	
	// Force fixed for now
	// TODO better way?
	var Position = "Fixed";
	
	// Set available bounds
	var Available_Bounds = {};
	switch (Position)
	{
		case "Fixed":
			// TODO: move this to better place
			Element.style.position = "fixed";
			
			// Set available bounds to window size with some padding
			var Window_Size = {Width: Jelly.jQuery(window).width(), Height: Jelly.jQuery(window).height()};

			Available_Bounds["Left"] = 16;
			Available_Bounds["Top"] = 16;
			Available_Bounds["Right"] = Window_Size["Width"] - 16;
			Available_Bounds["Bottom"] = Window_Size["Height"] - 16;
			
			break;
	}
	
	var Attach_Position = {};
	Attach_Position["Left"] = Element_Position["left"];
	Attach_Position["Top"] = Element_Position["top"];
	
	switch (Parameters["Attach"])
	{
		case "Mouse":
			if (Parameters["Update_Mouse_Position"])
			{
				Attach_Position["Left"] = Parameters["Event"].pageX || (Parameters["Event"].clientX);
				Attach_Position["Top"] = Parameters["Event"].pageY || (Parameters["Event"].clientY);
			}
			
			var Attach_Size = {Width: 0, Height: 0};
			
			break;
			
		case "Element":
			var Attach_Element = Parameters["Attach_Element"];
			
			var Attach_Position = {Left: Jelly.jQuery(Attach_Element).offset()["left"], Top: Jelly.jQuery(Attach_Element).offset()["top"]};
			var Attach_Size = {Width: Attach_Element.offsetWidth, Height: Attach_Element.offsetHeight};
			
			break;
	}
	
	// Offset scrollbar position for fixed positioning
	if (Position == "Fixed")
	{
		if (window.pageXOffset)
			var Window_Scroll_Position = {Left: window.pageXOffset, Top: window.pageYOffset};
		else
			var Window_Scroll_Position = {Left: document.body.scrollLeft, Top: document.body.scrollTop};
		Attach_Position["Left"] -= Window_Scroll_Position["Left"];
		Attach_Position["Top"] -= Window_Scroll_Position["Top"];
	}

	// Begin position at attach position
	switch (Parameters["Edge"])
	{
		case "Right":
			Element_Position["Left"] = Attach_Position["Left"] + Attach_Size["Width"];
			Element_Position["Top"] = Attach_Position["Top"];
			break;
		case "Bottom":
		default:
			Element_Position["Left"] = Attach_Position["Left"];
			Element_Position["Top"] = Attach_Position["Top"] + Attach_Size["Height"];
			break;
	}
	
	// If Restrict Position is true, contain available bound to the edge of the attach element.
	if (Restrict_Position)
	{
		switch(Edge)
		{
			case "Bottom":
				Available_Bounds["Top"] = Attach_Position["Top"] + Attach_Size["Height"];
				break;
			case "Top":
				Available_Bounds["Bottom"] = Attach_Position["Top"];
				break;
			case "Left":
				Available_Bounds["Right"] = Attach_Position["Left"];
				break;
			case "Right":
				Available_Bounds["Left"] = Attach_Position["Left"] + Attach_Size["Width"];
				break;
		}
	}
	
	// Fit within available bounds			
	if (Debug)
	{
		Jelly.Debug.Log("before");
		Jelly.Debug.Log(Element_Position);
		Jelly.Debug.Log(Element_Size);
		Jelly.Debug.Log(Available_Bounds);
	}

	if (Element_Position["Left"] + Element_Size["Width"] > Available_Bounds["Right"])
	{
		Element_Position["Left"] = Available_Bounds["Right"] - Element_Size["Width"];
		
		if (Element_Position["Left"] < Available_Bounds["Left"])
		{
			Element_Position["Left"] = Available_Bounds["Left"];
			Element_Size["Width"] = Available_Bounds["Right"] - Available_Bounds["Left"];
		}
	}
	if (Element_Position["Top"] + Element_Size["Height"] > Available_Bounds["Bottom"])
	{
		Element_Position["Top"] = Available_Bounds["Bottom"] - Element_Size["Height"];
		
		if (Element_Position["Top"] < Available_Bounds["Top"])
		{
			Element_Position["Top"] = Available_Bounds["Top"];
			Element_Size["Height"] = Available_Bounds["Bottom"] - Available_Bounds["Top"];
		}
	}

	if (Debug)
	{
		Jelly.Debug.Log("after");
		Jelly.Debug.Log(Element_Position);
		Jelly.Debug.Log(Element_Size);
		Jelly.Debug.Log(Available_Bounds);
	}
	
	// Set element's position
	Element.style.left = (Element_Position["Left"]).toString() + "px";
	Element.style.top = (Element_Position["Top"]).toString() + "px";
	
	// Set element's size
	if (Element.offsetWidth != Element_Size["Width"])
	{
		Element.style.width = Element_Size["Width"] + "px";
		Element.style.overflowX = "scroll";
	}
	if (Element.offsetHeight != Element_Size["Height"])
	{
		Element.style.height = Element_Size["Height"] + "px";
		Element.style.overflowY = "scroll";
	}
	
	if (Debug)
		Jelly.Debug.End_Group("Position_Container");
};