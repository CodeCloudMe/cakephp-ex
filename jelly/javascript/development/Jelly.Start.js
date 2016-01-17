Jelly.Start = function(Parameters)
{
	// On Load function - assigns values and call functions that need to be called after the document is loaded.
	// Parameters - URL_Prefix
	
	var Debug = false && Jelly.Debug.Debug_Mode;	

	if (Debug)
	{
		Jelly.Debug.Group("Start Jelly");	
		Jelly.Debug.Log("Jelly.References.Reference_Lookup...");
		Jelly.Debug.Log(Jelly.References.Reference_Lookup);
		Jelly.Debug.Log("Jelly.References.References_By_Namespace...");
		Jelly.Debug.Log(Jelly.References.References_By_Namespace);
	}
	
	// Register directory.
	// TODO: if javascript is in jelly, then this can move to environment variables
	// TODO fix URL Prefix
	Jelly.Directory = "/"; // Parameters["URL_Prefix"];
	
	// Register body element
	Jelly.Body_Element = document.body;
	
	// Get context menu setting
	if (Parameters && Parameters["Show_Context_Menu"])
		Jelly.Show_Context_Menu = Parameters["Show_Context_Menu"];
		
	// Add document level listeners	
	Jelly.Add_Global_Event_Listeners();
	
	// Create (and register?) a global 'controls' element.
	Jelly.Interface.Create_Global_Controls_Element();
	
	// Create and register default references.
	Jelly.References.Register(
			{
				'Kind': 'Non_Standard_Wrapper',
				'Name' :'Document_Title', 
				'Namespace': 'Jelly_Document_Title', 
				'Parent_Namespace':'Jelly'
			}
		);
	Jelly.References.Register(
			{
				'Kind': 'Non_Standard_Wrapper',
				'Name' :'Current_Path', 
				'Namespace': 'Jelly_Document_Current_Path', 
				'Parent_Namespace':'Jelly'
			}
		);
	Jelly.References.Register(
			{
				'Kind': 'Non_Standard_Wrapper',
				'Name' :'Site_Icon', 
				'Namespace': 'Jelly_Site_Icon', 
				'Parent_Namespace':'Jelly'
			}
		);
		
	// Start watching address bar for changes.
	Jelly.Watch_Address_Bar();
	window.setInterval(Jelly.Watch_Address_Bar, 100);
	
	if (Debug)
		Jelly.Debug.End_Group("");
};
