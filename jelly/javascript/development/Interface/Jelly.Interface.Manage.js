Jelly.Interface.Manage = function(Parameters)
{
	// Navigate to manage interface for this type.
	var Type_Alias = Parameters["Type_Alias"];
	var Manage_Path = "/" + Type_Alias.toLowerCase() + "/" + "manage";
	Jelly.Handlers.Visit_Link({"URL": Manage_Path});

	// Open this item in the inspector.
	if (Parameters["Item_ID"])
	{
		var Item_ID = Parameters["Item_ID"]
		Jelly.Interface.Inspect({
				"Type_Alias": Type_Alias,
				"Item_ID": Item_ID
			}
		);
	}
	
	// If there is no item, hide the inspector.
	else
		Jelly.Interface.Hide_Inspector();
}