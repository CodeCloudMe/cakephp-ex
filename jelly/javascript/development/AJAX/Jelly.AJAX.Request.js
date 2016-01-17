Jelly.AJAX.Request = function(Parameters)
{
	// TODO: description
	// Parameters: Post_Parameters, On_Complete, ... // TODO
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("AJAX Request");
		Jelly.Debug.Log(Parameters);
	}
	
	// Create new HTTP Request
	var HTTP_Request = new XMLHttpRequest();
	
	// TODO: ...
	HTTP_Request.open("POST", Parameters["URL"], true)
	
	// TODO:...
	HTTP_Request.setRequestHeader("X-Requested-With", "XMLHttpRequest");

	// TODO:...
	HTTP_Request.setRequestHeader("Accept", "text/javascript, text/html, application/xml, text/xml, */*");

	// TODO:...
	HTTP_Request.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
	
	// Handle Response
	HTTP_Request.onreadystatechange = function() 
	{
		switch (HTTP_Request.readyState)
		{
			case 0: // UNSENT
				break;
			case 1: // OPENED
				break;
			case 2: // HEADERS_RECEIVED
				break;
			case 3: // LOADING
				break;
			case 4: // DONE
				if (Debug)
					Jelly.Debug.Group("AJAX State 4: Done");
				
				if (HTTP_Request.status == 200)
				{
				}
				
				// Call On Complete Handler
				Parameters["On_Complete"](HTTP_Request);
				
				if (Debug)
					Jelly.Debug.End_Group("AJAX State 4: Done");
				
				break;
		}
	};
	
	// Pair POST parameters with "="...
	
	if (Debug)
	{
		Jelly.Debug.Log("AJAX Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Serialize post values to String.
	var Post_Values_String = Jelly.Utilities.Serialize({"Values": Parameters["Post_Variables"]});

	// TODO:should it be handled in Serialize by encoding Value_Keys? I think so...
	Post_Values_String = Post_Values_String.replace(new RegExp("[+]", "g"), "%2B");
	if (Debug)
	{
		Jelly.Debug.Log("Post_Values_String...");
		Jelly.Debug.Log(Post_Values_String);
	}
	
	// Send HTTP request, with post values string
	HTTP_Request.send(Post_Values_String);
	
	// Store request in parameters
	Parameters["HTTP_Request"] = HTTP_Request;
	
	if (Debug)
	{
		Jelly.Debug.End_Group("AJAX Request");
	}
};