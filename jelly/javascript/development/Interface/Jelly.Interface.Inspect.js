// TODO - hack, maybe easier when we have multiple containers.  I'm really just trying to store the inspector item reference.
Jelly.Interface.Inspect = function(Parameters)
{	
	console.log(Parameters);
	
	// Get vars
	var Type_Alias = Parameters["Type_Alias"];	
	var Item_ID = Parameters["Item_ID"];	
	var Browser_Event = null;
	if (Parameters["Event"])
		Browser_Event = Parameters["Event"];	
		
	// Don't open if it's already open dis for smoothness
	if (!Jelly.Interface.Inspect_Item || (Jelly.Interface.Inspect_Item.ID != Item_ID))
	{
		// Save Inspect Item
		Jelly.Interface.Inspect_Item = {
				'Type_Alias': Type_Alias,
				'ID': Item_ID
			};
		
		// Load item in inspector
		var URL = Type_Alias + "/" + Item_ID + "/" + "Inspect";
		var Link_Parameters = {
				"URL": URL,
				"Container": "Inspector"
			};
		Jelly.Handlers.Handle_Link_From_Click(Browser_Event, Link_Parameters);
	}
};