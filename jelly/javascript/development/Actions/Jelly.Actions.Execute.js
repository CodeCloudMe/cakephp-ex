Jelly.Actions.Execute = function(Parameters)
{
	// Execute a request tied to a passed in calling element and new global reference, execute the request, handle with the response, & clean up.

	// Parameters: Action, Action_Type, Calling_Element, Values
		
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Execute: " + Parameters["Action"]);
		Jelly.Debug.Log(Parameters);
	}
	
	// Initialize action value, if needed
	// TODO - this doesn't seem like it's ever populated...
	if (!Parameters["Values"])
		Parameters["Values"] = {};
	
	// Create action reference
	var Action_Reference = Jelly.References.Create_Global_Reference();
	
	// Store action reference namespace
	// TODO: Needed? Either here or in the general parameters? Unsure.  I think it is needed
// 	Parameters["Namespace"] = Action_Reference["Namespace"];
	Parameters["Values"]["Metadata_Namespace"] = Action_Reference["Namespace"];
	Parameters["Values"]["Preserve_Namespace"] = true;	
	// TODO - makes sense, no? thought about no_scripts, action results, etc, and ultimately went with this safe version, but not sure.
// 	Parameters["Values"]["Never_Wrap"] = true;

	// Set action executon URL, depending on type or general action...
	// Set action execution URL for type action
	if (Parameters["Action_Type"] == "Type_Action")
	{
		// Member actions URLs are like: /Target_Type_Alias/Target_Key/Action_Alias/Execute/Raw
		var URL = Jelly.Directory + "?" + Parameters["Target_Type"] + "/" + Parameters["Target"] + "/" + Parameters["Action"] + "/Execute";
	}
	// Set action execution URL for general action	
	else
	{
		// Set action type (either generic Action or a subtype)
		if (!Parameters["Action_Type"])
			Parameters["Action_Type"] = "Action";
	
		// General action URLs are like: /Action/Action_Alias/Execute/Raw
		var URL = Jelly.Directory + "?" + Parameters["Action_Type"] + "/" + Parameters["Action"] + "/Execute/Raw";
	}
	
	// Request raw item
	Parameters["Values"]["Raw"] = true;
	
	// Store calling reference...
	var Calling_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Calling_Element"]);
	Action_Reference["Calling_Reference"] = Calling_Reference;
	var Calling_Namespace =  Calling_Reference["Namespace"];	
	Parameters["Values"]["Calling_Namespace"] = Calling_Namespace;

	// TODO - might be superfluous - also in Execute_By_Namespace	
	// Disable action execution
	Jelly.jQuery('#' + Calling_Namespace + ' .Jelly_Action_Execute').addClass('Executing');
	
	// Hide result elements.
	Jelly.Actions.Hide_Results({Namespace: Calling_Namespace});
		
	// Show loading element
	// TODO: is this action specific? then should it be .actions.?
	Jelly.Interface.Show_Loading_Indicator(Action_Reference);
		
	// Execute action via asynchronous request
	Jelly.AJAX.Request(
	{
		URL: URL,
		Post_Variables: Parameters["Values"],
		On_Complete: function(HTTP_Request)
		{
			if (Debug)
			{
				Jelly.Debug.Group("Action Result On_Complete: " + Parameters["Action"]);
				Jelly.Debug.Log(HTTP_Request);
			}
			
			// Hide loading element
			// TODO: is this action specific? then should it be .actions.?
			Jelly.Interface.Hide_Loading_Indicator(Action_Reference);
			
			// Enable execution buttons 
			Jelly.jQuery('#' + Calling_Namespace + ' .Jelly_Action_Execute').removeClass('Executing');
						
			// Get AJAX Result
			var Result = HTTP_Request.responseText;

			// If cleaned result has content, display it in result element.
			var Cleaned_Result = Jelly.Utilities.Clean_Scripts(Result).trim();
			if (Cleaned_Result)
				Jelly.Actions.Show_Result(
						{
							Namespace: Calling_Reference["Namespace"],
							Content: Cleaned_Result
						}
					);
			
			// TODO: If there's no result element, don't register ... things from script
			// TODO: what?  fixed the code though, I think.  Though I don't know if it's needed or what it does.
			if (!Calling_Reference["Result_Element"])
				Jelly.References.No_Register = true;
			
			// Evaluate response javascript, including registering changed items.
			Jelly.Utilities.Execute_Scripts(Result);
			
			// TODO: funky code
			if (!Calling_Reference["Result_Element"])
				Jelly.References.No_Register = false;
			
			// Remove action reference if the result was not added to an element
			if (!Calling_Reference["Result_Element"])
				Jelly.References.Remove_Reference(Action_Reference);

			if (Debug)
				Jelly.Debug.Log("Action Result about to refresh all...");

			// Refresh all references in queue that were registered by the response javascript.
			Jelly.References.Refresh_All();
			
			if (Debug)
			{
				Jelly.Debug.End_Group("");
			}
		}
	});
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};