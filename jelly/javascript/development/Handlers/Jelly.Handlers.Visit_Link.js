// Change document location to a URL, or find or create a container and fill it with URL.
Jelly.Handlers.Visit_Link = function(Parameters)
{
	// Parameters - URL, Container, Alias, Values, On_Complete
	// TODO - Calling Element? 	
	
	// Get URL
	var URL = Parameters["URL"];
	
	if (Parameters["URL_Variables"])
	{
		// Build URL Variables string	
		/// TODO: Maybe continue to pass these in
		var URL_Variable_Strings = [];
		for (URL_Variable_Name in Parameters["URL_Variables"])
		{
			var URL_Variable_Value = Parameters["URL_Variables"][URL_Variable_Name];
			URL_Variable_Strings.push(URL_Variable_Name + "=" + URL_Variable_Value);
		}
		var URL_Variables_String = URL_Variable_Strings.join(",");
		
		URL += ":" + URL_Variables_String;
	}
	
	/*
	if (Jelly.URL.Is_Absolute(Parameters["URL"]))
		var URL = Parameters["URL"]

	// Create URL if necessary...			
	else
	{
		// TODO prepend Directory
		var URL = Jelly.URL.Format(Parameters["URL"], "raw");
		
		// Turn array of values into a query string
		if (Parameters["Values"])
			URL += ":" .  Jelly.Utilities.Serialize(Parameters["Values"], ",");
	}
	*/
	
	// If no container is specified, set the browser location to an anchor based URL, and the browser location listener will refresh the default container
	if (!Parameters["Container"])
	{
		// TODO whether to pass in an object and title
		window.history.pushState(null, null, URL);
		
		// TODO old way to remove
		// document.location = Jelly.URL.Format(Parameters["URL"], "anchor");
	}
	
	// If a container is specified, combine the  URL & Values in a raw URL, and pass that to an interface function for the specified container.
	else
	{		
		// Handle link according to container.
		switch (Parameters["Container"].toLowerCase())
		{
			// Inspector
			case "inspector":
			{
				Jelly.Interface.Create_Inspector({URL:URL});
				break;
			}
			
			// Windows
			case "window":
			{
				Jelly.Interface.Create_Window({Alias: Parameters["Alias"], URL: URL, Calling_Element: Parameters["Calling_Element"], Calling_Reference: Parameters["Calling_Reference"]});
				break;
			}
			
			// TODO - Parent?
			case "parent":
			{
				Jelly.References.Fill({Element: Parameters["Calling_Element"], Reference: Parameters["Calling_Reference"], URL: URL, Find_Parent_Container: true});
				break;
			}
			
			// TODO - Container?
			case "container":
			{
				// TODO
				// container: $Link_Script = "Jelly.References.Fill({Namespace: "$Link_Container", URL: '$Link_URL'});";
				break;
			}
			
			// Element ID
			default:
			{
				var Container_Element = document.getElementById(Parameters["Container"]);
				if (Container_Element)
				{	
					var Fill_Parameters = {
							Element: Container_Element,
							URL: URL
						};

					// TODO - this is still going to get complicated later with non-URL references that do mathc
					if (!Container_Element.Reference)
						Fill_Parameters["Create_Reference"] = true;
						
					Jelly.References.Fill(Fill_Parameters);
				}
				// TODO - should use "On_Load" to differentiate from Ajax handling , but currently not ever called, so commented out.
				// Jelly.References.Fill({Element: Container_Element, URL: URL, On_Complete: Parameters["On_Complete"]});
				else
					console.log("Unknown link container id. Perhaps the container is no longer in the DOM, for instance if the page was left while inner content was loading.");
				break;
			}
		}
	}
	
};