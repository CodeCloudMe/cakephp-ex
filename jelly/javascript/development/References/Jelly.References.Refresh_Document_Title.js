Jelly.References.Refresh_Document_Title = function()
{		
	// Get container item
	var Container_Item_Reference = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"];
	
	var URL = Jelly.Directory + "?";
	URL += Container_Item_Reference["Type_Alias"].toLowerCase() + "/" + Container_Item_Reference["ID"].toLowerCase() + "/" + "Title";
	
	var Post_Values = {
			"Raw": 1, 
			"Never_Wrap": 1
		};
		
	var Request_Parameters = {
		Post_Variables: Post_Values,
		URL: URL,
		On_Complete: function(Request_Object)
		{
			// Update title
			document.title = Request_Object.responseText;
		}
	};
	
	// Execute the refresh request
	Jelly.AJAX.Request(Request_Parameters);
};