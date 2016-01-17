Jelly.Interface.Manage_Container = function()
{
	var Container_Item_Reference = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"];

	// If already in manage interface, then navigate to the manage interface home
	if (Container_Item_Reference["Template_Alias"] == "Manage")
	{	
		Jelly.Interface.Manage({
				"Type_Alias": "Type"
			});	
	}
	
	// Otherwise, open the manage interface for the container item.
	else
	{
		Jelly.Interface.Manage({
				"Type_Alias": Container_Item_Reference["Type_Alias"],
				"Item_ID":Container_Item_Reference["ID"]
			});
	}
}