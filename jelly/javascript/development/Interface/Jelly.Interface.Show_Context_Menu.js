Jelly.Interface.Show_Context_Menu = function(Parameters)
{
	// Display context menu for parameters.

	// Parameters: Event, Target_Element
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Show_Context_Menu");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Get calling event 
	var Calling_Event = Parameters["Event"];	
	
	if (Debug)
	{
		Jelly.Debug.Log("Calling_Event...");
		Jelly.Debug.Log(Calling_Event);
	}
	
	// Get target element
	var Target_Element = Parameters["Target_Element"];

	// Get target reference
	var Target_Reference = Jelly.References.Get_Reference_For_Element(Target_Element);

	// If there is no reference for this element, default to the Site Item reference.
	if (!Target_Reference)
		Target_Reference = Jelly.References.Reference_Lookups_By_Kind['Specific']['Site']['Item'];
		
	// Check if the target reference is a child of a menu reference, and halt in that case... 
	var Recursive_Base_Child_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
	while (Recursive_Base_Child_Menu_Reference)
	{	
		var Recursive_Target_Parent_Reference = Target_Reference;
		while (Recursive_Target_Parent_Reference)
		{	
			// If the target reference is a child of any menu reference, return from function.
			// TODO - should the normal menu be possible? RIght now this just halts as is, but triggering default behavior is possible.
			if (Recursive_Target_Parent_Reference == Recursive_Base_Child_Menu_Reference)
				return;
				
			// Try the next parent reference.
			if (Recursive_Target_Parent_Reference.Parent_Reference)
				Recursive_Target_Parent_Reference = Recursive_Target_Parent_Reference.Parent_Reference;
		}
		
		// Try the next child of this menu reference.
		if (Recursive_Base_Child_Menu_Reference.Child_Menu)
			Recursive_Base_Child_Menu_Reference = Recursive_Base_Child_Menu_Reference.Child_Menu;
	}
		
	if (Debug)
	{
		Jelly.Debug.Log("Reference...");
		Jelly.Debug.Log(Target_Reference);
	}
	
	// Instantiate menu reference
	var Menu_Reference_Parameters = {};

	// Set parent of menu to target element.
	Menu_Reference_Parameters["Parent_Namespace"] = Target_Element.id;
	
	// Set menu alias
	Menu_Reference_Parameters["Alias"] = "Context_Menu";
	
	// Generate menu namespace
	Menu_Reference_Parameters["Namespace"] = Menu_Reference_Parameters["Parent_Namespace"] + Jelly.Namespace_Delimiter + Menu_Reference_Parameters["Alias"];
	
	// Generate URL... 
	
	// Generate URL base.
	var URL =  Target_Reference["Type_Alias"] + "/" + Target_Reference["ID"] + "/Context_Menu";
	
	// Generate URL Value String... 

	// Instantiate url values array
	var URL_Values = {};
	
	// Set property
	// TODO Finish
	var Recursive_Click_Target_Element = Calling_Event.target;
	while (Recursive_Click_Target_Element)
	{
		if (Debug)
		{
			Jelly.Debug.Log("Recursive_Click_Target_Element...");
			Jelly.Debug.Log(Recursive_Click_Target_Element);
		}
		if (Recursive_Click_Target_Element.getAttribute("data-property"))
		{
			URL_Values["Property_Alias"] = Recursive_Click_Target_Element.getAttribute("data-property");
			break;
		}
		Recursive_Click_Target_Element = Recursive_Click_Target_Element.parentNode;
	}
	
	// Set template...
	URL_Values["Template_ID"] = Target_Reference["Template_ID"];
	
	if (Target_Reference["Order"])
		URL_Values["Order"] = Reference["Order"];
	URL_Values["Show_Root_Extras"] = "True";
			
	// Serialize URL values
	var URL_Values_String = "";
	URL_Values_String = Jelly.Utilities.Serialize(
				{
					'Values': URL_Values,
//					'Is_URI': true,
					'Token': ','
				}
			);

	// Append serialized URL values to URL, with ":" marker.
	// TODO: This if always returns true
	if (URL_Values_String)
		URL += ":" + URL_Values_String;
		
	// Store URL
	Menu_Reference_Parameters["URL"] = URL;

	// Set post values
	
	// Instantiate post values array
	var Post_Values = {}
	
	// Set target namespace into post values
	Post_Values["Target_Namespace"] = Menu_Reference_Parameters["Parent_Namespace"];
	
	// Generate array of parent references and set it into post values... 
	
	// Instantiate parent references array
	var Parent_References = [];
	
	// Generate array of parent references
	var Recursive_Parent_Reference = Target_Reference;

	// TODO: when only one context parent is submitted, Jelly doesn't add a "count" to its POST variables
	do
	{
		switch (Recursive_Parent_Reference["Kind"])
		{
			case "Item":
				// For items, store ID, Type, and Namespace
				Parent_References.push({Item: Recursive_Parent_Reference["ID"], Item_Type: Recursive_Parent_Reference["Type_Alias"], Namespace: Recursive_Parent_Reference["Namespace"]});
				break;
			case "Iterator":
			case "Container":
				// TODO: Anything interesting here? 
				break;
			default:
				break;		
		}
	}
		while (Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"]);

	// Store parent references into post values
	// TODO: Naming
	Post_Values["Context_Parent"] = Parent_References;
	
	// Store post values into reference
	Menu_Reference_Parameters["Post_Values"] = Post_Values;
	
	// Position menu at mouse
	Menu_Reference_Parameters["Attach"] = "Mouse";	
	
	// Copy browser event data
	Menu_Reference_Parameters["Event"] = Calling_Event;
	
	// Create menu
	Jelly.Interface.Create_Menu(Menu_Reference_Parameters);	
		
	if (Debug)
		Jelly.Debug.End_Group("Show_Context_Menu");
	
	return false;
};