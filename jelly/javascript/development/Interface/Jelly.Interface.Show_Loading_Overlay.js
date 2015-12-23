Jelly.Interface.Show_Loading_Overlay = function(Reference)
{
	// TODO: This whole function is nonsense and needs to be done.
	return;
	
	var Debug = false && Jelly.Debug.Debug_Mode;

	if (Reference["Kind"] == "Container")			
		// TODO: find container content better
		var Target_Element = Jelly.jQuery(".Content")[0];
	else
		var Target_Element = Reference["Element"];
	
	// Find bounds of target
	var Target_Element_Bounds = Jelly.Interface.Calculate_Bounds(Target_Element);
	var Recursive_Bounds_Element = Target_Element;

	// If it's at the top left, don't display overlay
	// TODO - does this makes sense? older code says 'Hack to hide it if it's 0, 0'
	if(Target_Element_Bounds["Left"] == 0 && Target_Element_Bounds["Top"] == 0)
		return;
	
	// If height is 0, set it to a default
	if (Target_Element_Bounds["Height"] == 0)
	{
		Target_Element_Bounds["Height"] = 16;
		Target_Element_Bounds["Bottom"] += Target_Element_Bounds["Height"];
	}
	
	// Create loading overlay
	// TODO - this definitely doesn't have to happen here.., should be in "generate" whatever...
	var Loading_Overlay = document.createElement("div");
	Loading_Overlay.className = "Jelly_Loading_Overlay";
	Loading_Overlay.style.left = Target_Element_Bounds["Left"] + "px";
	Loading_Overlay.style.top = Target_Element_Bounds["Top"] + "px";
	Loading_Overlay.style.width = Target_Element_Bounds["Width"] + "px";
	Loading_Overlay.style.height = Target_Element_Bounds["Height"] + "px";	
	
	// TODO - messy, implement a better centering mechanism
	var Loading_Overlay_Table_Element = document.createElement("table");
	Loading_Overlay.appendChild(Loading_Overlay_Table_Element);

	var Loading_Overlay_Table_Row_Element = document.createElement("tr");
	Loading_Overlay_Table_Element.appendChild(Loading_Overlay_Table_Row_Element);

 	var Loading_Overlay_Table_Cell_Element = document.createElement("td");
	Loading_Overlay_Table_Row_Element.appendChild(Loading_Overlay_Table_Cell_Element);
	
	var Loading_Overlay_Indicator_Element = Jelly.Interface.Generate_Browser_Control({"Browser_Control_ID": "Loading"});
	Loading_Overlay_Indicator_Element.style.visibility = "visible";
	Loading_Overlay_Table_Cell_Element.appendChild(Loading_Overlay_Indicator_Element);
	
	// Set overlay to fixed or absolute depending on whether it's attached to a fixed or absolute element
	// TODO - 
	/*
	if (Target_Bounds["Fixed"])
		Loading_Overlay.style.position = "fixed";
	else
		Loading_Overlay.style.position = "absolute";
	*/
	
	// Append overlay to global controls
// (TODO: should it have its own namespace? probably not)
//			Loading_Overlay.style.display = "none";
// 	Jelly.Interface.Global_Controls_Element.appendChild(Loading_Overlay);

	Jelly.Interface.Global_Controls_Element.innerHTML += Loading_Overlay.outerHTML;
	Jelly.Debug.Log(Jelly.Interface.Global_Controls_Element);
	Jelly.Debug.Log(Loading_Overlay);
	
	// Store reference to overlay
	Reference["Loading_Overlay"] = Loading_Overlay;
//	Jelly.jQuery(Loading_Overlay).show("fast");
//			console.groupEnd();
};