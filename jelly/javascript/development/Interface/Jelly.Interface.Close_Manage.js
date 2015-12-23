Jelly.Interface.Close_Manage = function(Parameters)
{	
	// If Inspector
	if (Jelly.Interface.Inspect_Item)
		var URL = "/" + Jelly.Interface.Inspect_Item["Type_Alias"].toLowerCase() + "/" + Jelly.Interface.Inspect_Item["ID"];

	// Else if there's a list page specified 
	else if (Parameters["List_Page_Alias"])
		var URL = "/" + "page" + "/" + Parameters["List_Page_Alias"].toLowerCase();
	
	// Otherwise go to the home page. 
	else
		var URL = "/";
	
	// Close inspector.
	Jelly.Interface.Hide_Inspector();
	
	// Go to home page.
	Jelly.Handlers.Visit_Link({"URL": URL});
};