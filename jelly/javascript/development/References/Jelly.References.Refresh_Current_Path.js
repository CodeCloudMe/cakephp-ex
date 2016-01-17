Jelly.References.Refresh_Current_Path = function()
{	
	// Get container item
	var Container_Item_Reference = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"];
	
	var URL = Jelly.Directory + "?";
	URL += Container_Item_Reference["Type_Alias"].toLowerCase() + "/" + Container_Item_Reference["ID"].toLowerCase() + "/" + "Path";
	
	var Post_Values = {
			"Raw": 1, 
			"Never_Wrap": 1
		};
		
	var Request_Parameters = {
		Post_Variables: Post_Values,
		URL: URL,
		On_Complete: function(Request_Object)
		{
			// Set path
			var New_Path = Request_Object.responseText;
			
			// Add template
			if (Container_Item_Reference["Template_Alias"] && Container_Item_Reference["Template_Alias"].toLowerCase() != "default")
				New_Path += "/" + Container_Item_Reference["Template_Alias"].toLowerCase();
			
			// Add URL Values
			Current_Path_Values = Jelly.Current_Path.split(':')[1];
			if (Current_Path_Values)
					New_Path += ":" + Current_Path_Values;
	
			// Update browser
			Jelly.Current_Path = New_Path;	
			window.history.pushState(null, null, "/" + New_Path);	
		}
	};
	
	// Execute the refresh request
	Jelly.AJAX.Request(Request_Parameters);


/*
	// Make a new path
	// TODO - alias is risky - looking forward to /alias/id as a robust safety url	
	Jelly.Debug.Log(Container_Item_Reference);
	var New_Path = Container_Item_Reference["Type_Alias"].toLowerCase() + "/" + Container_Item_Reference["Alias"].toLowerCase();	
	
				
	// Add template, if not default
	if (Container_Item_Reference["Template_Alias"] && Container_Item_Reference["Template_Alias"].toLowerCase() != "default")
		New_Path += "/" + Container_Item_Reference["Template_Alias"].toLowerCase();

	// Add URL Values
	Current_Path_Values = Jelly.Current_Path.split(':')[1];
	if (Current_Path_Values)
			New_Path += ":" + Current_Path_Values;
			
	// Update current path
	Jelly.Current_Path = New_Path;

	// Update browser url
	window.history.pushState(null, null, "/" + New_Path);
*/
			
	return;
};