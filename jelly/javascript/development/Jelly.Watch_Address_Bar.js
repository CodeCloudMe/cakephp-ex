Jelly.Watch_Address_Bar = function()
{
	// Called on an interval, compares the new path against the registered path, triggering a changed container when the path has changed.

	// Get path
	var New_Path = document.location.href;
	
	// Offset URL
	// TODO - cleanup
	var Pound_Position = New_Path.indexOf("#");
	if (Pound_Position > -1)
	{
		New_Path = New_Path.substring(Pound_Position + 1);
	}
	else
	{
		var Base_Position = New_Path.indexOf("http://");
		if (Base_Position > -1)
			New_Path = New_Path.substring(Base_Position + "http://".length + 1);
		var SSL_Base_Position = New_Path.indexOf("https://");
		if (SSL_Base_Position > -1)
			New_Path = New_Path.substring(SSL_Base_Position + "https://".length + 1);
		var Slash_Position = New_Path.indexOf("/");
		if (Slash_Position > -1)
			New_Path = New_Path.substring(Slash_Position + 1);
	}
	
	// Strip slashes
	New_Path = New_Path.replace(/^\/*/, "");
	New_Path = New_Path.replace(/\/*$/, "");
	
	// If no path is specified, set the default path
	if (New_Path == "")
		New_Path = Jelly.Default_Path;
	
	// Check if path has changed.
	if (Jelly.Current_Path === null)
		Jelly.Current_Path = New_Path;

	if (New_Path != Jelly.Current_Path)
	{
		// Store current path.
		Jelly.Current_Path = New_Path;
		
// 		Jelly.Debug.Log(New_Path);
		
		// Register path changed.
		// TODO: "We'll know." - Kunal Gupta (2014)
		Jelly.References.Trigger_Refresh({"Kind": "Path"});
		
		// Refresh all references.
		Jelly.References.Refresh_All();
	}
};

// TODO: not using path parts as an array until Containers are implemented
// TODO: This is all leftover code from path parts array

/*
// Split path
var New_Path_Parts = New_Path.split("/");
//		for (var Path_Index in New_Path_Parts)


var Path_Index = 0;
{		

	if (New_Path != Jelly.Current_Path)
//			if (New_Path_Parts[Path_Index] != Jelly.Current_Path_Parts[Path_Index])
	{

		Jelly.Current_Path = New_Path;
		
//				Jelly.Debug.Log("YO");
//				Jelly.Debug.Log(Path_Index);			
		var Item_Key = "Container";
		if (Jelly.References.Items[Item_Key])
		{

			for (var Reference_Index in Jelly.References.Items[Item_Key])
			{
				Reference = Jelly.References.Items[Item_Key][Reference_Index];
				// Mark reference to be refreshed
				Jelly.References.References_To_Refresh[Reference["Namespace"]] = Reference;						
				if (Reference["Level"] == Path_Index)
				{

				}
			}
		}
		
		Jelly.References.Refresh_All();
		
//				break;
	}
}
*/

