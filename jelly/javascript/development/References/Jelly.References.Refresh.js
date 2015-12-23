Jelly.References.Refresh = function(Reference)
{	
	// Build an AJAX request to refresh this reference, store the request, execute the request, and show a loading overlay as necessary.
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Refresh: " + (", Kind: " + Reference["Kind"]) + " (" + Reference["Namespace"] + ")");
		Jelly.Debug.Log(Reference);
	}
	
	// Instantiate post values
	var Post_Values = {};
	
	// Build URL according to kind of reference
	var URL = Jelly.Directory + "?";

	switch (Reference["Kind"])
	{
		case "Non_Standard_Wrapper":
			switch (Reference["Name"])
			{
				case "Document_Title":
					Jelly.References.Refresh_Document_Title();
					break;

				case "Current_Path":
					Jelly.References.Refresh_Current_Path();
					break;				

				case "Site_Icon":
					Jelly.References.Refresh_Site_Icon();
					break;
			}
			return;
			break;
			
		// Containers: get URL from Current_Path
		case "Container":
			URL += "/" + Jelly.Current_Path;
			
			// Request raw
			Post_Values["Raw"] = 1;
			
			// Request from container
			Post_Values["From_Container"] = 1;
			
			// Set Backend or Frontend container mode.
			// TODO - remove when containers are done better.
			if (Jelly.Current_Path.search(/manage([/:].*)*$/g) != -1)
				Jelly.Interface.Switch_To_Backend({'Clear':true});
			else
				Jelly.Interface.Switch_To_Frontend({'Clear':true});
			break;
			
		// URLs: get URL from reference's URL
		case "URL":
			if (Debug)
				Jelly.Debug.Log("Refresh URL");
			
			// Add reference's URL
			URL += Reference["URL"];
			
			// Request raw
			Post_Values["Raw"] = 1;
			
			break;
			
		case "Item":
			if (Debug)
				Jelly.Debug.Log("Refresh Item");
				
			// Special case for the site item reference...
			if (Reference == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"])
			{	
				Jelly.References.Refresh_Site();
				break;
			}

			// Special case for a design item reference that is marked as design-only...
			else if (Reference["Design_Only"])
			{
				// Make sure the item is a Design
				// TODO cleanup design refreshing
				if (Reference["Type_Alias"] != "Design")
					throw "Can only refresh design-only on designs";				
				
				var URL_Reference = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"];
				
				URL += "/";
				
				Post_Values["Design_Only"] = 1;
			}
			
			// General case for all other item references...
			else
			{
				// Request item refreshes as raw
				Post_Values["Raw"] = 1;
				
				URL_Reference = Reference;
			}
			
			// Check if item came from a Many-To-One property
			// TODO Hack for Type_Item, I believe. Need it?
			if (URL_Reference["One_To_Many_Parent"])
			{
				URL += "/" + URL_Reference["One_To_Many_Parent_Type"];
				URL += "/" + URL_Reference["One_To_Many_Parent"];
				URL += "/" + URL_Reference["Alias"];
			}
			else
			{
				// Add Type to URL
				URL += URL_Reference["Type_Alias"];
		
//				Jelly.Debug.Log(URL_Reference);
				// Add ID to URL
				URL += "/" + URL_Reference["ID"];
			}
		
			// Add Template to URL
			URL += "/" + URL_Reference["Template_Alias"];
		
			// Add URL Variables.
			if (URL_Reference["Variables"])
			{
				var URL_Variables_Parts = [];
				for (URL_Variable_Key in URL_Reference["Variables"])
				{
					URL_Variable_Value = URL_Reference["Variables"][URL_Variable_Key];
					URL_Variables_Part = URL_Variable_Key + "=" + URL_Variable_Value;
					URL_Variables_Parts.push(URL_Variables_Part);
				}
				if (URL_Variables_Parts.length > 0)
					URL += ":" + URL_Variables_Parts.join(",");
			}
							
			// Do not wrap item refreshes (already wrapped)
			Post_Values["No_Wrap"] = 1;
			// TODO - might be redundant, not sure, texted tristan
			Post_Values["Preserve_Namespace"] = 1;
			break;
			
		default:
			// Unknown reference kind
			{
				Jelly.Debug.Display_Error("Reference: Unknown reference kind"); 
				if (Debug)
					Jelly.Debug.Log(Reference); 
				return;
			}
			break;
	}
	
	// Add reference post values to post values.
	// TODO  - not sure if it needs to be "URL_Reference", but I don't think so.
	if (Reference["Post_Values"])
	{
		for (Reference_Post_Value_Index in Reference["Post_Values"])
		{
			if (Reference["Post_Values"].hasOwnProperty(Reference_Post_Value_Index))
			{
				Post_Values[Reference_Post_Value_Index] = Reference["Post_Values"][Reference_Post_Value_Index];
			}
		}
	}
	
	// Add namespace to post values.
	Post_Values["Metadata_Namespace"] = Reference["Namespace"];

	// Add iterator parameters to post values
	// TODO: Does this really do anything? 
//	Post_Values.concat(Jelly.References.Get_Iterator_Parameters(Reference));
	
	// Add local input values to post values.
	if (Debug)
		Jelly.Debug.Log('Getting local values');
	
	var Local_Form_Values = Jelly.References.Get_Local_Values_For_Namespace({"Namespace": Reference["Namespace"]});
	for (Local_Form_Value_Index in Local_Form_Values)
	{
		// TODO Disabled local values for now
		//Post_Values["Local_" + Local_Form_Value_Index] = Local_Form_Values[Local_Form_Value_Index];
	}
// 	Post_Values.concat();

	if (Reference["Element"] && !Reference["No_Preloader"])
	{	
		var Original_Progress_Bar = null;
		var Original_Progress_Bar_Interval = null;
		
		if (Reference["Kind"] == "Container")
		{
			var Preloader_Element = document.getElementById("Jelly_Content");

			if (Jelly.References.Container_Progress_Bar)
				var Original_Progress_Bar = Jelly.References.Container_Progress_Bar;

			if (Jelly.References.Container_Progress_Bar_Interval)
			{
				window.clearInterval(Jelly.References.Container_Progress_Bar_Interval);
				Jelly.References.Container_Progress_Bar_Interval = null;
			}
			
		}
		else
		{
			var Preloader_Element = Reference["Element"];
		}

		if (!Original_Progress_Bar)
			var Original_Progress_Bar = new Nanobar ({bg: '#41bde1', target: Preloader_Element, className: "Jelly_Progress_Bar"});
		
		var Progress = 30;
		Original_Progress_Bar.go(Progress);
		
		if (!Original_Progress_Bar_Interval)
		{
			Original_Progress_Bar_Interval = window.setInterval( function () {
						if (Progress <= 60 - 2)
							Progress += 1;
						Original_Progress_Bar.go(Progress);
					},
					100
				)
		}
		
		if (Reference["Kind"] == "Container")
		{
			Jelly.References.Container_Progress_Bar = Original_Progress_Bar;
			Jelly.References.Container_Progress_Bar_Interval = Original_Progress_Bar_Interval;
		}
	}
	
	// Make the a refresh request
	var Request_Parameters = {
		Post_Variables: Post_Values,
		Reference: Reference,
		URL: URL,
		On_Complete: function(Request_Object)
		{
			
			// If there are no pending refresh requests, or the most current request isn't this request, then abort on_complete handler.
			// TODO: needs explanation, but I bet this basically ensures that on complete only runs once for an otherwise asynchronous request system.
			if (Reference.Refresh_Requests.length == 0 || (Reference.Refresh_Requests[Reference.Refresh_Requests.length - 1] != Request_Parameters))
				return;
			
			// Clear refresh requests 
			Reference.Refresh_Requests = [];
			
			// Remove the loading overlay
			if (Reference["Loading_Overlay"])
			{
				Reference["Loading_Overlay"].parentNode.removeChild(Reference["Loading_Overlay"]);
				Reference["Loading_Overlay"] = null;
			}
			
			// Update this reference's element with the server response.
// 			Jelly.Debug.Log(Reference);
// 			Jelly.Debug.Log(Request_Object);
// 			Jelly.Debug.Log("Reference Element ID: " + Reference["Element"].id);

			if (Reference["Kind"] == "Container")
				Jelly.Interface.Call_Refresh_Container_Listener();

			//  If this reference has an element (aka - is not the site element)
			
			if (Reference["Element"])
			{	
				// TODO - cleanup
				if (Reference["Element"].id == "Jelly_Inspector")
				{
					Jelly.jQuery(Reference["Element"]).css("opacity", 1);
				}
				Reference["Element"].innerHTML = Request_Object.responseText;
				
				if (!Reference["No_Preloader"])
				{
					window.clearInterval(Original_Progress_Bar_Interval);
					
					if (Reference["Kind"] == "Container")
					{	
						Progress = 100;
						Original_Progress_Bar.go(Progress);

						Jelly.References.Container_Progress_Bar_Interval = null;	
						Jelly.References.Container_Progress_Bar = null;
					}
					else
					{
						New_Progress_Bar = new Nanobar ({bg: '#41bde1', target: Preloader_Element, className: "Jelly_Progress_Bar"});
						Progress = 100;
						New_Progress_Bar.go(Progress);
					}
				}
			}
			
			// Call any inline scripts the server response.
			Jelly.Utilities.Execute_Scripts(Request_Object.responseText);
			
			// If it's a container refresh, scroll to the top!
			// TODO: Scroll to element, if such a parameter is set.
			// window.scroll(0,0);
			
			// Refresh any items waiting on a container change.
			// TODO - this is a temporary implementation.
			if (Reference["Kind"] == "Container")
				Jelly.References.Trigger_Refresh({"Kind": "Path_Secondary"});
			
			Jelly.References.Refresh_All();
			
			// Remove dead references
			// TODO This sure happens often...
			Jelly.References.Clean_References();
			
			// Execute Refresh Handlers
			var Refresh_Handler_Index;
			for (Refresh_Handler_Index = 0; Refresh_Handler_Index < Jelly.Handlers.Refresh_Handlers.length; Refresh_Handler_Index++)
			{
				var Refresh_Handler = Jelly.Handlers.Refresh_Handlers[Refresh_Handler_Index];
				console.log(Refresh_Handler);
				Refresh_Handler();
			}
			Jelly.Handlers.Refresh_Handlers = [];
			
			// Call the on complete handler for this reference.
			// TODO - never registered, destroying.
// 			if (Reference["On_Complete"])
// 				Reference["On_Complete"]();
			
			// Call  the on load handler for the element attached to this reference.
			// Jelly.Handlers.Call_Handler_For_Target({'Event': 'On_Load', 'Target': Reference["Element"], 'Remove_After_Calling': true});
			Jelly.Handlers.Call_Handler_For_Target({'Event': 'On_Load', 'Target': Reference["Element"]});
			
			var iconic = IconicJS();
			iconic.inject('img.iconic');
		}
	};
	
	if (Debug)
		Jelly.Debug.Log(Request_Parameters);
		
	if (Reference.Refresh_Requests.length > 0)
	{
		for (var Refresh_Request_Index = 0; Refresh_Request_Index < Reference.Refresh_Requests.length; Refresh_Request_Index++)
		{
			var Previous_Request = Reference.Refresh_Requests[Refresh_Request_Index];
			var Previous_HTTP_Request = Previous_Request["HTTP_Request"];
			Previous_HTTP_Request.abort();
		}
	}
		
	// Store the refresh request
	Reference.Refresh_Requests.push(Request_Parameters);
	
	// Execute the refresh request
 	Jelly.AJAX.Request(Request_Parameters);
//	setTimeout(Jelly.AJAX.Request, 1000, Request_Parameters);
	
	//  If this reference has an element (aka - is not the site element)
	if (Reference["Element"])
	{
		// Show loading overlay
		// TODO - cleanup
		if (Reference["Element"].id == "Jelly_Inspector")
		{
			Jelly.jQuery(Reference["Element"]).css("opacity", 0.5).find('a, input, textarea, button, select').prop('disabled', true);
		}
				
		// If this is the first refresh request, show loading overlay.	
		// TODO - show loading overlay doesn't do anything.
		if (Reference.Refresh_Requests.length == 1)
			Jelly.Interface.Show_Loading_Overlay(Reference);
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};