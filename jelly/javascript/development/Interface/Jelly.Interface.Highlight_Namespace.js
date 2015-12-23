Jelly.Interface.Highlight_Namespace = function(Namespace)
{
	var Debug = false && Jelly.Debug.Debug_Mode;	
	if (Debug)
	{
		Jelly.Debug.Group("Highlight_Namespace" + " (" + Namespace + ")");
	}
	
	// Protect against highlight bubbling.
	Jelly.Interface.Set_Event_Protection('Highlights');	
	
	// Return if target is already highlighted,
	if (Jelly.Interface.Highlight_Target_Namespace == Namespace)
	{
		if (Debug)
		{
			Jelly.Debug.Log("Already highlighted");
			Jelly.Debug.End_Group("Highlight Namespace");
			return;
		}
	}

	// otherwise store new highlight target.
	else
		Jelly.Interface.Highlight_Target_Namespace = Namespace;
			
	// Get target element
	var Target_Element = document.getElementById(Jelly.Interface.Highlight_Target_Namespace);
	
	// Get bounds of element
	var Element_Bounds;

	// TODO: verify that this is the best way to get the bounds
	if (Target_Element)
	{
		Element_Bounds = Jelly.Interface.Calculate_Bounds(Target_Element);
	}
	else
	{
		// TODO: This is for references without elements; better way?
		// TODO: actually it's for root-level elements like Site
		Element_Bounds = {Left: 10, Top: 10, Width: window.innerWidth - 20, Height: window.innerHeight - 20, Right: window.innerWidth - 10, Bottom: window.innerHeight - 10};
	}
	
	// TODO: make sure z-index is correct
	/*
	
	// Show highlight box
	Jelly.Interface.Highlight_Parts["Left"].style.left = Element_Bounds["Left"] + "px";
	Jelly.Interface.Highlight_Parts["Left"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Left"].style.height = Element_Bounds["Height"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.left = (Element_Bounds["Right"] - Highlight_Thickness) + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.height = Element_Bounds["Height"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.left = Element_Bounds["Left"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.width = Element_Bounds["Width"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.left = Element_Bounds["Left"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.top = (Element_Bounds["Bottom"] - Highlight_Thickness) + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.width = Element_Bounds["Width"] + "px";
	
	*/
	
	// Setup highlight parts
	if (!Jelly.Interface.Highlight_Parts)
	{
		Jelly.Interface.Highlight_Parts = {};
		
		// Create highlight part elements
		Jelly.Interface.Highlight_Parts["Left"] = document.createElement("div");
		Jelly.Interface.Highlight_Parts["Top"] = document.createElement("div");
		Jelly.Interface.Highlight_Parts["Right"] = document.createElement("div");
		Jelly.Interface.Highlight_Parts["Bottom"] = document.createElement("div");
		
		// Set highlight part class names
		Jelly.Interface.Highlight_Parts["Left"].className = "Jelly_Highlight_Edge Jelly_Highlight_Edge_Left";
		Jelly.Interface.Highlight_Parts["Top"].className = "Jelly_Highlight_Edge Jelly_Highlight_Edge_Top";
		Jelly.Interface.Highlight_Parts["Right"].className = "Jelly_Highlight_Edge Jelly_Highlight_Edge_Right";
		Jelly.Interface.Highlight_Parts["Bottom"].className = "Jelly_Highlight_Edge Jelly_Highlight_Edge_Bottom";
		
		// Add highlight parts to page
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Highlight_Parts["Left"]);
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Highlight_Parts["Top"]);
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Highlight_Parts["Right"]);
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Highlight_Parts["Bottom"]);
		
		Jelly.Interface.Highlight_Parts["Left"].style.display = "none";
		Jelly.Interface.Highlight_Parts["Right"].style.display = "none";
		Jelly.Interface.Highlight_Parts["Top"].style.display = "none";
		Jelly.Interface.Highlight_Parts["Bottom"].style.display = "none";
		
		Jelly.jQuery(Jelly.Interface.Highlight_Parts["Left"]).fadeIn("fast");
		Jelly.jQuery(Jelly.Interface.Highlight_Parts["Top"]).fadeIn("fast");
		Jelly.jQuery(Jelly.Interface.Highlight_Parts["Right"]).fadeIn("fast");
		Jelly.jQuery(Jelly.Interface.Highlight_Parts["Bottom"]).fadeIn("fast");
	}
	
	// Dim area outside highlight
	var Document_Size = {Width: Jelly.jQuery(document).width(), Height: Jelly.jQuery(document).height()};
	Jelly.Interface.Highlight_Parts["Left"].style.left = "0px";
	Jelly.Interface.Highlight_Parts["Left"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Left"].style.width = Element_Bounds["Left"] + "px";
	Jelly.Interface.Highlight_Parts["Left"].style.height = Element_Bounds["Height"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.left = Element_Bounds["Right"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.width = Document_Size["Width"] - Element_Bounds["Right"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.height = Element_Bounds["Height"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.left = "0px";
	Jelly.Interface.Highlight_Parts["Top"].style.top = "0px";
	Jelly.Interface.Highlight_Parts["Top"].style.width = Document_Size["Width"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.height = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.left = "0px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.top = Element_Bounds["Bottom"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.width = Document_Size["Width"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.height = Document_Size["Height"] - Element_Bounds["Bottom"] + "px";
	
	if (Debug)
		Jelly.Debug.End_Group("Highlight Namespace");
};