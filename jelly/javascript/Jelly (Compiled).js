var Jelly = 
{
	// Disable jquery's control of '$'
	// TODO: what happens if a user wants to use this or another $-based library
	// TODO: i feel like under the current system, we should really add $.noConflict immediately on load for Jquery, not here, doesn't belong here
	jQuery: $.noConflict(),
	
	// Register constants
	Namespace_Delimiter: "_",
 
	// Register installation directory
	Jelly_Installation_Directory_Path: "/jelly",
	
	// Register default path
	Default_Path: 'Home',

	// Initalize instance variables.
	// TODO: do these belong here? I think so, but not certain.
	Body_Element: null,
	Current_Path: null,
	Directory: null,
	
	// Override context menu for super users
	Show_Context_Menu: false
};

var Jelly_Javascript_Path = Jelly.Jelly_Installation_Directory_Path + '/' + 'javascript' + '/' + 'development';
var Jelly_Handlers_Javascript_Path = Jelly_Javascript_Path + '/' + 'Handlers';
var Jelly_References_Javascript_Path = Jelly_Javascript_Path + '/' + 'References';
var Jelly_Actions_Javascript_Path = Jelly_Javascript_Path + '/' + 'Actions';
var Jelly_Utilities_Javascript_Path = Jelly_Javascript_Path + '/' + 'Utilities';
var Jelly_AJAX_Javascript_Path = Jelly_Javascript_Path + '/' + 'AJAX';
var Jelly_Interface_Javascript_Path = Jelly_Javascript_Path + '/' + 'Interface';
var Jelly_Media_Javascript_Path = Jelly_Javascript_Path + '/' + 'Media';
var Jelly_Debug_Javascript_Path = Jelly_Javascript_Path + '/' + 'Debug';
var Jelly_URL_Javascript_Path = Jelly_Javascript_Path + '/' + 'URL';
var Jelly_Connections_Javascript_Path = Jelly_Javascript_Path + '/' + 'Connections';
var Jelly_Payments_Javascript_Path = Jelly_Javascript_Path + '/' + 'Payments';

;

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



Jelly.Add_Global_Event_Listeners = function ()
{
	// Add document level click, mouse down, mouse move, key down, and scroll handlers...
	
	// TODO: More modern binding 
	if (document.addEventListener)
	{
		document.addEventListener("click", Jelly.Handlers.Document_Click, false);
		document.addEventListener("mousedown", Jelly.Handlers.Document_Mouse_Down, false);
		document.addEventListener("keydown", Jelly.Handlers.Document_Key_Down, false);
		document.addEventListener("mousemove", Jelly.Handlers.Document_Mouse_Move, false);
		document.addEventListener("scroll", Jelly.Handlers.Document_Scroll, false);
		if (Jelly.Show_Context_Menu)
			document.addEventListener("contextmenu", Jelly.Handlers.Document_Context_Click, false);
	}
	else if (document.attachEvent)
	{
		document.attachEvent("click", Jelly.Handlers.Document_Click);
		document.attachEvent("mousedown", Jelly.Handlers.Document_Mouse_Down);
		document.attachEvent("keydown", Jelly.Handlers.Document_Key_Down);			
		document.attachEvent("mousemove", Jelly.Handlers.Document_Mouse_Move);
		document.attachEvent("scroll", Jelly.Handlers.Document_Scroll);
		if (Jelly.Show_Context_Menu)
			document.addEventListener("contextmenu", Jelly.Handlers.Document_Context_Click, false);
	}
};

Jelly.URL = 
{
};

;

// TODO:  doesn't seem to be used.
Jelly.URL.Format = function(URL, Format)
{
	// Formats any format of URL into the desired format.
	// TODO: Format isn't the right word.
	
	// URL could be given in many ways...

	// 	'http://asdfasdfsf/asdfadfasf'  - absolute
	// 	'/asdfasdfas/fasdfasdf/adsar' - relative with slash - garbage, remove slash.
	// 	'asdfasdfas/fasdfasdf/adsar' - relative  - you probably don't need this.
	// 	'#sasdadsf/asdasd/adss' - relative with anchor - Anchor(URL) produces this
	// 	'asdfasdfas/fasdfasdf/adsar/raw' - relative with raw - Raw(URL) produces this
};

// TODO: doesn't seem to be used, or needs to be written.
Jelly.URL.Is_Absolute = function(URL)
{
	// Return true if the URL includes a protocol
};

Jelly.AJAX =
{
}

;	


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

Jelly.Debug = 
{
	// Debug mode flag values
	Level: 
	{ 
		None: "None",
		Errors_Only: "Errors Only",
		All: "All"
	},
	
	// Global debug mode flag
	Debug_Mode: "All",
	Alert_Errors: false,
	
	// TODO: doesn't seem to be used.
	End_Early: 0
}

;

// TODO - doesn't seem to be used.
Jelly.Debug.Dir = function(Obj)
{
	return;
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
		if (window.console)
			if (console.dir)
				console.dir(Obj);
};

Jelly.Debug.Log = function(Text)
{
	var Smart_Debug = false;
	
	// If in Debug Mode, verify console compatibility and display text in console
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
		if (window.console )
			if (console.log)
			{
				if (Smart_Debug)
				{
					console.group('Log');
					var Trace = printStackTrace();
					console.log(Trace[4]);
//					console.group(Trace[Trace.length - 1]);
//					console.log(Trace);
				}
				console.log(Text);
				if (Smart_Debug)
					console.groupEnd();
			}
};

Jelly.Debug.Group = function(Name)
{
	// Verify debug mode and console compatibility, begin log group with name.
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
		if (window.console)
			if (console.group)
				console.group(Name);
};

Jelly.Debug.End_Group = function(Name)
{
	// Verify debug mode and console compatibility, end log group
	
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
		if (window.console)
			if (console.groupEnd)
			{
				Jelly.Debug.Log("/endgroup (" + Name + ")");
				console.groupEnd();
			}
};

Jelly.Debug.Display_Error = function(Text)
{
	// Displays text in console and alert text if according to debugging value. 	
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All || Jelly.Debug.Debug_Mode == Jelly.Debug.Level.Errors_Only)
	{
		// Display text in console
		console.log(Text);
	
		// Alert text
		if (Jelly.Debug.Alert_Errors)
			alert(Text);
	}
};

Jelly.Debug.Print_All_Actions = function()
{
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
	{
		Jelly.Debug.Group("Print All Actions");

		for (Namespace in Jelly.References.References_By_Namespace)
		{
			if (Jelly.References.References_By_Namespace.hasOwnProperty(Namespace))
			{
				var Reference = Jelly.References.References_By_Namespace[Namespace];
			
				if (Reference["Kind"] == "Item" && Reference["Type_Alias"] && (["Action", "Type_Action"].indexOf(Reference["Type_Alias"]) >= 0))
				{
					Jelly.Debug.Log(Reference["Element"]);
					Jelly.Debug.Log(Reference["Input_Elements"]);
// 					for (Handler_Event in Reference["Handlers"])
// 					{
// 						if (Reference["Handlers"].hasOwnProperty(Handler_Event))
// 						{
// 							Jelly.Debug.Log(Namespace + " - " + Handler_Event +":");
// 							Jelly.Debug.Log(Reference["Handlers"][Handler_Event]);
// 						}
// 					}
				}
			}
		}
	
		Jelly.Debug.End_Group("Print All Actions");
	}
};

Jelly.Debug.Print_All_Handlers = function()
{
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
	{
		Jelly.Debug.Group("Print All Handlers");

		for (Namespace in Jelly.References.References_By_Namespace)
		{
			if (Jelly.References.References_By_Namespace.hasOwnProperty(Namespace))
			{
				var Reference = Jelly.References.References_By_Namespace[Namespace];

				if (Reference["Handlers"])
				{
					for (Handler_Event in Reference["Handlers"])
					{
						if (Reference["Handlers"].hasOwnProperty(Handler_Event))
						{
							Jelly.Debug.Log(Namespace + " - " + Handler_Event +":");
							Jelly.Debug.Log(Reference["Handlers"][Handler_Event]);
						}
					}
				}
			}
		}
	
		Jelly.Debug.End_Group("Print All Handlers");
	}
};

// TODO
Jelly.Media =
{
	Players: {},	
	Global_Audio_Player_Controller: null,
	Global_Audio_Player_Element: null,
	Global_Audio_Player_Wrapper: null,
	Global_Audio_Source: null,
	Global_Audio_URL_Callbacks: {},
	Global_Audio_Player_Playing: false,
}	

;	

Jelly.Media.Play_Global_Sound = function(URL)
{
	// If playing a new or different URL, reinstantiate the player
	if (Jelly.Media.Global_Audio_Source != URL)
	{
		// Remove existing wrapper if exists
		if (Jelly.Media.Global_Audio_Player_Wrapper)
			Jelly.Media.Global_Audio_Player_Wrapper.parentNode.removeChild(Jelly.Media.Global_Audio_Player_Wrapper);

		// Create hidden player wrapper element
		Jelly.Media.Global_Audio_Player_Wrapper = document.createElement("div");
		Jelly.Media.Global_Audio_Player_Wrapper.id = "Jelly_Global_Audio_Player_Wrapper";

		// Create audio player element
		Jelly.Media.Global_Audio_Player_Element = document.createElement("audio");
		Jelly.Media.Global_Audio_Player_Element.id = "Jelly_Global_Audio_Player";
		
		var Source_Element = document.createElement("source");
		Source_Element.src = URL + ":Random=" + Math.floor(Math.random()*100000);
		Source_Element.type = "audio/mpeg";
		Jelly.Media.Global_Audio_Player_Element.appendChild(Source_Element);

		// Add elements to DOM
		Jelly.Media.Global_Audio_Player_Wrapper.appendChild(Jelly.Media.Global_Audio_Player_Element);
		document.body.appendChild(Jelly.Media.Global_Audio_Player_Wrapper);
		
		// Create MediaElement controller
		Jelly.Media.Global_Audio_Player_Controller = new MediaElementPlayer("#Jelly_Global_Audio_Player", {
			features: ["playpause", "progress"],
			success: function (mediaElement, domObject)
				{
					//Jelly.Debug.Log("Success");
					//Jelly.Debug.Log(mediaElement);
					//Jelly.Debug.Log(domObject);
					
					// Store reference to media element
					Jelly.Media.Global_Audio_Player_Element = mediaElement;
					
					// Play media
					Jelly.Media.Global_Audio_Player_Element.play();
					
					// Add playback event listeners
					Jelly.Media.Global_Audio_Player_Element.addEventListener("loadeddata", function(e) {}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("progress", function(e) {}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("timeupdate", function(e)
						{
							var Current_Time = Jelly.Media.Global_Audio_Player_Element.currentTime;
						}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("seeked", function(e) {}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("canplay", function(e) {}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("play", function(e)
						{
							//Jelly.Debug.Log("Play Event");
							
							// Store playing state
							Jelly.Media.Global_Audio_Player_Playing = true;
				
							if (!Jelly.Media.Global_Audio_URL_Callbacks[Jelly.Media.Global_Audio_Source]) return;
							if (!Jelly.Media.Global_Audio_URL_Callbacks[Jelly.Media.Global_Audio_Source]["Play"]) return;
							for (Callback_Index in Jelly.Media.Global_Audio_URL_Callbacks[Jelly.Media.Global_Audio_Source]["Play"])
							{
								var Callback = Jelly.Media.Global_Audio_URL_Callbacks[Jelly.Media.Global_Audio_Source]["Play"][Callback_Index];
								Callback();
							}
						}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("playing", function(e)
						{
							//Jelly.Debug.Log("Playing Event");
							
							// Store playing state
							Jelly.Media.Global_Audio_Player_Playing = true;
						}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("pause", function(e)
						{
							//Jelly.Debug.Log("Pause Event");
							
							// Store playing state
							Jelly.Media.Global_Audio_Player_Playing = false;
				
							if (!Jelly.Media.Global_Audio_URL_Callbacks["All"]) return;
							if (!Jelly.Media.Global_Audio_URL_Callbacks["All"]["Pause"]) return;
							for (Callback_Index in Jelly.Media.Global_Audio_URL_Callbacks["All"]["Pause"])
							{
								var Callback = Jelly.Media.Global_Audio_URL_Callbacks["All"]["Pause"][Callback_Index];
								Callback();
							}
						}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("loadedmetadata", function(e) {}, false);
					Jelly.Media.Global_Audio_Player_Element.addEventListener("ended", function(e) {}), false;
					Jelly.Media.Global_Audio_Player_Element.addEventListener("volumechange", function(e) {}, false);
				},
			
			error: function(mediaElement)
				{
					//Jelly.Debug.Log("Error");
					//Jelly.Debug.Log(mediaElement);
				}
		});
			
		// Store current source
		Jelly.Media.Global_Audio_Source = URL;
	}
	else
	{
		//Jelly.Debug.Log("Re-playing");
		// Play
		Jelly.Media.Global_Audio_Player_Element.play();
	}
};

Jelly.Media.Pause_Global_Sound = function()
{
	if (!Jelly.Media.Global_Audio_Player_Element)
		return;

	// Pause
	Jelly.Media.Global_Audio_Player_Element.pause();
};

Jelly.Media.Register_Media_Player = function (Parameters)
{
	//console.log(Parameters);
	var Namespace;
	if (Parameters["Namespace"])
		Namespace = Parameters["Namespace"]
	else
		Namespace = "Jelly_Globals";
	
	// Register media player delegate.
	var Media_Player = {};
	Jelly.Media.Players[Namespace] = Media_Player;
	
	Media_Player["Namespace"] = Namespace;
	Media_Player["URL"] = Parameters["Media_URL"];
	Media_Player["Type"] = Parameters["Media_Type"];
	Media_Player["Host"] = Parameters["Media_Host"];
	Media_Player["Timers"] = {'Progress': null, 'Loading_Progress': null};
	
	// Make a player element for SWFObject to replace.
	var Flash_Player_Element_ID = Namespace + Jelly.Namespace_Delimiter + "Media_Player";
//	Jelly.Debug.Log("REGSITEDER:" + Flash_Player_Element_ID);
	var Flash_Placeholder = document.createElement("div");
	Flash_Placeholder.id = Flash_Player_Element_ID;
	var Flash_Player_Wrapper = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Media_Player_Container");
	Flash_Player_Wrapper.appendChild(Flash_Placeholder);
				
	// Get URL
	var Flash_Player_URL;
	switch (Media_Player["Type"])
	{
		case "Sound":
			Flash_Player_URL = Jelly.Directory + Jelly.Jelly_Installation_Directory_Path + "/flash/Jelly_Flash_Audio_Player.swf";
			break;
			
		case "Video":
			Flash_Player_URL = Jelly.Directory + Jelly.Jelly_Installation_Directory_Path + "/flash/Jelly_Flash_Video_Player.swf";
			break;
	}			

	// Get default width & height & class.
	var Flash_Width, Flash_Height, Flash_Class;
	switch (Media_Player["Type"])
	{
		case "Sound":
			var Flash_Class = "Jelly_Flash_Audio_Player";
			var Flash_Width = 1;
			var Flash_Height = 1;

			var Controls_Width = 420;
			var Controls_Height = 16;
			break;
		case "Video":
			var Flash_Class = "Jelly_Flash_Video_Player";
			if (Parameters["Media_Width"])
				Flash_Width = Parameters["Media_Width"];
			else
				Flash_Width = 420;
			if (Parameters["Media_Height"])
				Flash_Height = Parameters["Media_Height"];
			else
				Flash_Height = 315;
				
			var Controls_Width = Flash_Width;
			var Controls_Height = 16;
			break;
	}
	
	// Resize media controls
	document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Media_Player_Controls").style.width = Controls_Width + "px";
	document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Media_Player_Controls").style.height = Controls_Height + "px";

	// Embed flash media player.
	var Flash_Vars = {"Flash_Namespace": Flash_Player_Element_ID, "Jelly_Directory": Jelly.Directory};
	var Flash_Params = {wmode: "transparent", allowScriptAccess: "always"};
	var Flash_Attributes = {"id": Flash_Player_Element_ID, "class": Flash_Class};
	var Flash_Minimum_Version = "9.0.0";
	var Flash_Installer_URL = Jelly.Jelly_Installation_Directory_Path + "/flash/expressInstall.swf";
	var Flash_Callback = null;					
	swfobject.embedSWF(Flash_Player_URL, Flash_Player_Element_ID, Flash_Width, Flash_Height, Flash_Minimum_Version, Flash_Installer_URL, Flash_Vars, Flash_Params, Flash_Attributes, Flash_Callback);
			
	// Get reference to new Flash element			
	var Media_Player_Element = document.getElementById(Flash_Player_Element_ID);
	Media_Player["Player_Element"] = Media_Player_Element;

	// Map generic media player functions to specific player implementations.
	Media_Player.Play_Media = function()
		{
			Media_Player_Element.Play_Media();
		};
	Media_Player.Pause_Media = function()
		{
			Media_Player_Element.Pause_Media();
		};
	Media_Player.Set_Position = function (Click_Event)
		{
			// Get position percentage
			var Progress_Bar_Element = document.getElementById(Media_Player["Namespace"] + Jelly.Namespace_Delimiter + 'Media_Player_Progress_Bar');
			
			var Position_Click = Click_Event.layerX;
			var Position_Percentage = Position_Click / Progress_Bar_Element.offsetWidth * 100;					
			//Jelly.Debug.Log(Position_Percentage +"%");
			
			// Set media position
			var Media_Duration = Media_Player.Get_Duration();
			var Position_Seconds = Math.floor(Position_Percentage/100 * Media_Duration);
//					alert(Position_Seconds);
			Media_Player.Set_Position(Position_Seconds);
		};
	Media_Player.Get_Position = function()
		{
			return Media_Player_Element.Get_Position();
		};
	Media_Player.Get_Duration = function()
		{
			return Media_Player_Element.Get_Duration();
		};
	Media_Player.Get_Bytes_Loaded = function()
		{
			return Media_Player_Element.Get_Bytes_Loaded();
		};
	Media_Player.Get_Bytes_Total = function()
		{
			return Media_Player_Element.Get_Bytes_Total();
		};
	Media_Player.Get_Bytes_Start = function()
		{
			// TODO GET BYTES START @TODO @KUNAL@ TRISTAN @
			return 0;
		};
		
	// Attach media handlers to flash player.
	Jelly.Flash.Add_Flash_Event_Callback(Media_Player_Element, "Media_Event",
		function (Flash_Element, Parameters)
		{
			//Jelly.Debug.Log("Media_Event");
//					Jelly.Debug.Log(Parameters);
			switch (Parameters["Event"])
			{
				case "Play":
					//Jelly.Debug.Log("PLAY2");
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Playing", "Target": Media_Player_Element});
					
					// Hack
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Loading", "Target":Media_Player_Element});
					break;
				case "Pause":
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Pausing", "Target": Media_Player_Element});
					break;
				case "Stream_Stop":
					//Jelly.Debug.Log("STOP2");
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Pausing", "Target": Media_Player_Element});
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Ended", "Target": Media_Player_Element});
					
					// Hack
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Loading_Complete", "Target":Media_Player_Element});
					break;
				case "Buffer_Empty":
					// Data is not being received quickly enough to fill the buffer. Data flow will be interrupted until the buffer refills, at which time a NetStream.Buffer.Full message will be sent and the stream will begin playing again
					//Jelly.Debug.Log("Buffer empty!");
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Buffer_Empty", "Target": Media_Player_Element});
					break;
				case "Buffer_Full":
					// The buffer is full and the stream will begin playing
					//Jelly.Debug.Log("Buffer full!");
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Buffer_Full", "Target": Media_Player_Element});
					break;
				case "Buffer_Flush":
					// Data has finished streaming, and the remaining buffer will be emptied.
					//Jelly.Debug.Log("Buffer flush!");
					Jelly.Handlers.Call_Handler_For_Target({"Event": "Media_Buffer_Flush", "Target": Media_Player_Element});
					break;
				case "Error":
					break;
			}
		});

	// Attach "On_Load" event handler to flash player.
	Jelly.Flash.Add_Flash_Event_Callback(Jelly.Media.Players[Namespace]["Player_Element"], "On_Load", function (Flash_Element)
		{
			// Load media
			Flash_Element.Load_Media("/" + Parameters["Media_URL"]);

			// Call 'On_Load' handler.
			Jelly.Handlers.Call_Handler_For_Target({"Event": "On_Load", "Target": Media_Player_Element});
		});
}

Jelly.Media.Play_Pause_Global_Sound = function(URL)
{
	// If the URL has changed, or the current URL is simply paused, play
	if (Jelly.Media.Global_Audio_Source != URL || (Jelly.Media.Global_Audio_Source == URL && !Jelly.Media.Global_Audio_Player_Playing))
	{
		Jelly.Media.Play_Global_Sound(URL);
	}
	else
	{
		Jelly.Media.Pause_Global_Sound();
	}
};

Jelly.Media.Register_Global_Sound_Event = function(Event, URL, Callback)
{
	if (!Jelly.Media.Global_Audio_URL_Callbacks[URL])
		Jelly.Media.Global_Audio_URL_Callbacks[URL] = {};
		
	if (!Jelly.Media.Global_Audio_URL_Callbacks[URL][Event])
		Jelly.Media.Global_Audio_URL_Callbacks[URL][Event] = [];
	
	Jelly.Media.Global_Audio_URL_Callbacks[URL][Event].push(Callback);
};

Jelly.Actions = 
{
};

;


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

Jelly.Actions.Validate = function(Parameters)
{
	// Runs all registered validation scripts for the action, returns overall validation value.
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
		Jelly.Debug.Group("Validate" + " (" + Parameters["Namespace"] + ")");
		
	// Get callback
	var Return = Parameters["Return"];

	// Get action namespace
	var Action_Namespace = Parameters["Namespace"];
		
	// Require action reference
	var Action_Element = document.getElementById(Action_Namespace);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		Return(Validation_Error = true);

	// Get action values
	var Action_Sensitive_Values = Jelly.References.Get_Input_Values_For_Action(
			{
				"Namespace": Action_Namespace,
				"Include_Sensitive": true
			}
		);
	
	// Run all validations in parallel...
	
	// Get validations
	var Validations = Action_Reference.Validations;
	
	// Get validation keys.
	var Validation_Keys = Object.keys(Validations);
	
	// For each validation, perform validation and return outcome via Return callback
	async.each(
		Validation_Keys,
		
		// Perform validation...
		function (Validation_Key, Return)
		{	
			// Get this validation
			var Validation = Validations[Validation_Key];
			
			// For an asynchronous, functional validation, call it with a callback function, and it takes care of validation & feedback.
			if (Validation["Function"] && Validation["Function"].length == 2)
			{	
				Validation["Function"].call(this, Action_Sensitive_Values, Return);
			}
			
			// For synchronous functions, handle callback manually.
			else
			{	
				var Validation_Result = true; 
				
				// For a synchronous, functional validation, call it and it takes care of validation & feedback.
				if (Validation["Function"])
					Validation_Result = Validation["Function"].call(this, Action_Sensitive_Values);
					
				// For named validation behaviors, handle validation and display any errors. 
				else
				{
					var Input_Names = Validation["Inputs"];

					// Perform named validation.
					switch (Validation["Behavior"].toLowerCase())
					{
						case "equals":
							for (var Input_Name_Index = 1; Input_Name_Index < Input_Names.length; Input_Name_Index++)
								if (Action_Sensitive_Values[Input_Names[Input_Name_Index]] != Action_Sensitive_Values[Input_Names[Input_Name_Index - 1]])
									Validation_Result = false;
							break;
					
						case "populated":
							for (var Input_Name_Index = 0; Input_Name_Index < Input_Names.length; Input_Name_Index++)
								if (Action_Sensitive_Values[Input_Names[Input_Name_Index]] == "")
									Validation_Result = false;
							break;
					}
			
					// If validation fails, display error if provided
					if (!Validation_Result)
					{	
						// Get error from validation or default error.
						if (Validation["Error"])
							var Error_Content = Validation["Error"];
						else
							var Error_Content = "Error";
			
						// Create result parameters with action and error content.
						var Result_Parameters = {
								Namespace: Action_Namespace,
								Content: Error_Content
							}
			
						// If validation is for a single input, display the error for that specific input, otherwise, display the error for the whole action.
						if (Input_Names.length == 1)
							Result_Parameters["Input_Alias"] = Input_Names[0];
					
						// Display the error.
						Jelly.Actions.Show_Result(Result_Parameters);
					}
				}
				
				// Call callback function with error if exists.
				var Validation_Error = null;
				if (!Validation_Result)
					Validation_Error = true;
				Return(Validation_Error);
			}
		},
		
		// Call callback function with error if it exists
		function (Validation_Error)
		{
			if (Validation_Error)
			{
				// TODO - this was a total guess as to placement
				// Re-enable action execution buttons if the validation didn't pass
				Jelly.jQuery('#' + Action_Namespace + ' .Jelly_Action_Execute').removeClass('Executing');
			}

			// Call callback function with error if it exists
			Return(Validation_Error);
			
			// TODO - Hahahah look at that trickily named return above gotcha didn't i
			if (Debug)
				Jelly.Debug.End_Group("");
		}
	);
};

Jelly.Actions.Show_Result = function(Parameters)
{	
	// Registers an action result element for a reference.
	// Parameters:  Namespace, Content, Input_Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Display Result");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	// TODO - since it's a "calling reference" that's passed in, could be anything.   Not sure if it makes sense to just work off of "Result_Element" explicitly instead of the below, which requires "Action"s for updating results.
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Get the action or input result element
	var Result_Element = null;
	if (Parameters["Input_Alias"])
	{	
		// Get input...
		var Input_Alias = Parameters["Input_Alias"];

		// Verify input
		if (!Action_Reference.Inputs[Input_Alias])
		{
			Jelly.Debug.Display_Error("Tried to add input result for non-existing input: " + Input_Alias);
			return;
		}
		var Input = Action_Reference.Inputs[Input_Alias];
		
		// Get result element 
		if (Input["Result_Element"])
			Result_Element = Input["Result_Element"];			
	}
	else
	{
		if (Action_Reference["Result_Element"])
			Result_Element = Action_Reference["Result_Element"];
	}
	
	// If there's a result element, then display the result content inside of the result element.
	if (Result_Element)
	{
		var Content = Parameters["Content"];
		Result_Element.innerHTML = Content;
		
		// Show the result if it has any actual text besides white space
		var Content_Text = Jelly.jQuery(Result_Element).text();
		if (/\S/.test(Content_Text))
			Result_Element.style.display = "block";
		
		// TODO - clean up with styles
		// TODO - add animation.
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.Actions.Hide_Results = function(Parameters)
{	
	// Registers an action result element for a reference.
	// Parameters:  Namespace
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Hide Results");
		Jelly.Debug.Log(Parameters);
	}
			
	// Get action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);	
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;

	// Hide action result element.
	// TODO - animate 
	if (Action_Reference["Result_Element"])
		Action_Reference["Result_Element"].style.display = "none";
		
	// Hide action input result elements.
	for (var Input_Alias in Action_Reference.Inputs)
	{
		var Action_Input = Action_Reference.Inputs[Input_Alias];
		if (Action_Input["Result_Element"])
			Action_Input["Result_Element"].style.display = "none";
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.Actions.Set_Input_Value = function(Parameters)
{
	// Sets the value of an input to the specified value
	// Parameters: Namespace, Alias, Value
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Set Input Value");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	
	// Lookup input
	var Input_Element = Action_Reference.Inputs[Parameters["Alias"]]["Element"];
		
	// Verify input value
	if (!Input_Element)
	{	
		Jelly.Debug.Display_Error("No input found to set");
		return;
	}
	
	// Set value
	switch (Input_Element.tagName)
	{	
		// TODO - any conversions, if applicable
		default:
			Input_Element.value = Parameters["Value"];
			break;
	}
	
	if (Debug)
		Jelly.Debug.Log(Input_Element);
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
	
	return;
};

Jelly.Actions.Is_Action_Reference = function(Reference)
{	
	// Validates Action reference for a namespace, or throws an error.	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Is Action Reference");
		Jelly.Debug.Log(Reference);
	}
	
	// Verify that this reference is an action reference
	if (!Reference || !Reference["Type_Alias"] || ["Action", "Type_Action"].indexOf(Reference["Type_Alias"]) < 0)
	{
		// TODO this isn't really an error...
// 		Jelly.Debug.Display_Error("No action reference found for the namespace: " + Reference["Namespace"]);
		return false;
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}

	return true;
};

Jelly.Actions.Execute_By_Namespace = function(Parameters)
{		
	// Populates action parameters for a namespace, and calls jelly.actions.execute with the parameters	
	var Debug = false && Jelly.Debug.Debug_Mode;

	if (Debug)
	{
		Jelly.Debug.Group("Execute_By_Namespace");
		Jelly.Debug.Log(Parameters);
	}
	
	// Initialize common values...

	// Get action namespace
	var Action_Namespace = Parameters["Namespace"];

	// Get action element
	var Action_Element = document.getElementById(Action_Namespace);

	// Get action reference
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);


	async.series([
		// Hide results...
		function (Return)
		{
			// Hide result elements.
			Jelly.Actions.Hide_Results({Namespace: Action_Namespace});
			
			// Disable action execution
			Jelly.jQuery('#' + Action_Namespace + ' .Jelly_Action_Execute').addClass('Executing');
	
			Return();
		},
	
		// Perform client-side validations for action....
		function (Return) 
		{
			// Perform validations 
			Jelly.Actions.Validate({"Namespace": Action_Namespace, "Return": Return});
		},

		// Execute action...
		function (Return) 
		{	
			// Get action type
			var Action_Type = Action_Reference["Type_Alias"];

			// Get action values
			var Action_Safe_Values = Jelly.References.Get_Input_Values_For_Action(
					{
						"Namespace": Action_Namespace,
						"Include_Sensitive": false
					}
				);
	
			// Instantiate action execute parameters
			var Action_Execute_Parameters = {};	

			// Set action parameters namespace
			Action_Execute_Parameters["Namespace"] = Action_Namespace;

			// Set action calling element 
			// TODO - should we just start doing these by reference, or are there times that we need just an element w/ no reference? 
			Action_Execute_Parameters["Calling_Element"] = Action_Element;

			// Set action values
			Action_Execute_Parameters["Values"] = Action_Safe_Values;

			// Copy action alias from reference
			Action_Execute_Parameters['Action'] = Action_Reference["Alias"];

			// Set action type
			Action_Execute_Parameters["Action_Type"] = Action_Type;

			// If this is a type action, copy the target information from the parameters
			if (Action_Type == "Type_Action")
			{
				// TODO - verify existence
				Action_Execute_Parameters['Target'] = Parameters['Target'];
				Action_Execute_Parameters['Target_Type'] = Parameters['Target_Type'];
			}

			// Execute this action.
			// TODO - perhaps Return can be integrated into execution later.
			Jelly.Actions.Execute(Action_Execute_Parameters);
			
			Return();
		}],
		
		// Debugging 
		function (err, results)
		{	
			if (Debug)
			{
				Jelly.Debug.Log(err);
				Jelly.Debug.Log(results);
				Jelly.Debug.End_Group("Execute_By_Namespace");				
			}
		}
		
	);
};

Jelly.Actions.Restart_Action_Timer = function(Parameters)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Restart Action Timer");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
		
	// Clear timer if it exists.
	if (Action_Reference.Timer)
		window.clearTimeout(Action_Reference.Timer);
		
	// Start a new timer that executes the action in 5 seconds.
	Action_Reference.Timer = window.setTimeout(function ()
		{
			Jelly.Actions.Execute_Action_Timer({'Namespace': Parameters["Namespace"]});
		}, 
		5000);
		
	if (Debug)
	{
		// Flash status
		Jelly.jQuery('#Jelly_Inspector').css("border", "solid 10px white");
		setTimeout(function () {Jelly.jQuery('#Jelly_Inspector').css("border", "solid 10px yellow");}, 200);
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.Actions.Execute_Action_Timer = function(Parameters)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Execute Action Timer");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
		
	// Clear timer if it exists.
	if (Action_Reference.Timer)
		window.clearTimeout(Action_Reference.Timer);
		
	// Execute action
	Jelly.Handlers.Call_Handler_For_Target({"Event": "Execute", "Target": document.getElementById(Parameters["Namespace"])});
	
	// Flash status
	if (Debug)
	{
		console.log("executing: " + Parameters["Namespace"]);
		Jelly.jQuery('#Jelly_Inspector').css("border", "solid 10px magenta");
		setTimeout(function () {Jelly.jQuery('#Jelly_Inspector').css("border", "none");}, 200);
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.Actions.Register_Action_Input = function(Parameters)
{
	// Registers a input element for a reference.
	// TODO: should this be more abstract than action, or am I insane?
	// Parameters: Namespace, Input_Element, Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Input Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Verify input element
	if (!Parameters["Element"])
	{
		Jelly.Debug.Display_Error("Trying to register input to action reference by namespace, but input element is empty or not set");
		return;
	}
	else
		var Input_Element = Parameters["Element"];
	
	// Verify input alias
	if (Parameters["Alias"])
		var Input_Alias = Parameters["Alias"];
	else
		var Input_Alias = Input_Element.name;
	if (!Input_Alias)
	{
		Jelly.Debug.Display_Error("Trying to register input to action reference by namespace and alias, but no alias provided in element or in parameters");
		return;
	}
	
	// Get input sensitivity
	var Input_Sensitivity = false;
	if (Parameters["Sensitive"])
		Input_Sensitivity = Parameters["Sensitive"];
		
	// TODO - ETC	.  can be in html as well.
	var Input_Clear_On_Execute = false;
	if (Parameters["Clear_On_Execute"])
		Input_Clear_On_Execute = Parameters["Clear_On_Execute"];
		
	var Input_Blur_On_Execute = false;
	if (Parameters["Blur_On_Execute"])
		Input_Blur_On_Execute = Parameters["Blur_On_Execute"];

	// Create input subreference
	var Action_Input = {Element: Input_Element, Sensitive: Input_Sensitivity, Clear_On_Execute: Input_Clear_On_Execute, Blur_On_Execute: Input_Blur_On_Execute, Loading_Element: null, Result_Element: null};
		
	// Store input subreference into action reference.
	Action_Reference.Inputs[Input_Alias] = Action_Input;

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.Actions.On_Property_Relation_Load = function(Parameters)
{	
	// Get current action namespace
	var Current_Action_Namespace = Parameters["Namespace"];
	
	// Get value type alias
	var Value_Type_Input = Jelly.Actions.Get_Input_From_Action_By_Alias({
			"Namespace": Current_Action_Namespace,
			"Alias": "Edited_Value_Type"
		});
	var Value_Type_Alias = Value_Type_Input.value;
		
	// Show the corresponding options for the value type...

	// Get simple type status of selected value type
	var Selected_Type_Is_Simple_Type = (Jelly.References.Simple_Types.indexOf(Value_Type_Alias) != -1);
	
	if (Selected_Type_Is_Simple_Type)
	{	
		// Show simple select
		var Property_Simple_Select = document.getElementById(Current_Action_Namespace + "_Edited_Relation_Select_Simple");
		Property_Simple_Select.style.display = "block";
	}
	
	else
	{	
		// Show complex select
		var Property_Complex_Select = document.getElementById(Current_Action_Namespace + "_Edited_Relation_Select_Complex");		
		Property_Complex_Select.style.display = "block";	
	}
};

Jelly.Actions.Register_Action_Validation = function(Parameters)
{
	// Registers a input element for a reference.
	// TODO: should this be more abstract than action, or am I insane?
	// Parameters: Namespace, Input_Element, Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Validation");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);	
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;	
		
	// Copy validation values to new object.
	var Validation = {};
	if (Parameters["Behavior"])
		Validation["Behavior"] = Parameters["Behavior"];
	if (Parameters["Inputs"])
		Validation["Inputs"] = Parameters["Inputs"];
	if (Parameters["Error"])
		Validation["Error"] = Parameters["Error"];
	if (Parameters["Function"])
		Validation["Function"] = Parameters["Function"];
			
	// Store validation in action 
	Action_Reference.Validations.push(Validation);
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};


Jelly.Actions.Get_Input_From_Action_By_Alias = function(Parameters)
{
	// Returns a registered input by alias
	// Parameters: Namespace, Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Get Action Input Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Get action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Get input
	var Input_Element = Action_Reference.Inputs[Parameters["Alias"]]["Element"];
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
	
	return Input_Element;
};

Jelly.Actions.Register_Action_Result_Element = function(Parameters)
{	
	// Registers an action result element for a reference.
	// Parameters:  Namespace, Result_Element
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Result Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);	
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Store result element into reference
	Action_Reference["Result_Element"] = Parameters["Result_Element"];
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.Actions.On_Property_Value_Type_Selected = function(Parameters)
{	
	// Get current action namespace
	var Current_Action_Namespace = Parameters["Namespace"];

	// Get value type
	var Value_Type_Alias = Parameters["Item"];
	
	// Get simple type status of selected value type
	var Selected_Type_Is_Simple_Type = (Jelly.References.Simple_Types.indexOf(Value_Type_Alias) != -1);

	// Update relation dropbox if changing simple type status
	var Property_Simple_Select = document.getElementById(Current_Action_Namespace + "_Edited_Relation_Select_Simple");
	var Property_Complex_Select = document.getElementById(Current_Action_Namespace + "_Edited_Relation_Select_Complex");
		
	if (Selected_Type_Is_Simple_Type)
	{	
		// If this is changing from complex to simple type, hide relation menu and set to simple
		if (Property_Simple_Select.style.display == "none")
		{									
			// Set input value to simple
			Jelly.Actions.Set_Input_Value({
					"Namespace":Current_Action_Namespace, 
					"Alias": "Edited_Relation", 
					"Value": ""
				});
	
			// Hide complex select
			Property_Complex_Select.style.display = "none";
	
			// Show simple select
			Property_Simple_Select.style.display = "block";
		}
	}

	else
	{
		// If this is changing from simple to complex type, hide relation menu and set to single
		if (Property_Complex_Select.style.display == "none")
		{									
			// Set input value to single
			Jelly.Actions.Set_Input_Value({
					"Namespace":Current_Action_Namespace, 
					"Alias": "Edited_Relation", 
					"Value": "Many-To-One"
				});

			// Hide simple select
			Property_Simple_Select.style.display = "none";

			// Show complex select
			Property_Complex_Select.style.display = "block";
		}
	}

};

Jelly.Actions.Register_Action_Loading_Element = function(Parameters)
{
	// Registers a loading element for a reference.
	// TODO: should this be more abstract than action, or am I insane?
	// Parameters: Namespace, Loading_Element
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Loading Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;

	// Store loading element into reference.
	Action_Reference["Loading_Element"] = Parameters["Loading_Element"];

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.Actions.Register_Action_Input_Result_Element = function(Parameters)
{
	// Registers a input element for a reference.
	// TODO: should this be more abstract than action, or am I insane?
	// Parameters: Namespace, Input_Element, Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Action Input Result Element");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);	
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Verify input element
	if (!Parameters["Element"])
	{
		Jelly.Debug.Display_Error("Trying to register input result element to action reference by namespace, but input result element is empty or not set");
		return;
	}
	else
		var Input_Result_Element = Parameters["Element"];
	
	// Verify input alias
	if (!Parameters["Alias"])
	{
		Jelly.Debug.Display_Error("Trying to register input to action reference by namespace and alias, but no alias provided in element or in parameters");
		return;
	}
	else
		var Input_Alias = Parameters["Alias"];

	// Store input element into action reference.
	// TODO: This hack checks if an input has been registered, but could be smarter
	if (Action_Reference.Inputs[Input_Alias])
		Action_Reference.Inputs[Input_Alias]["Result_Element"] = Input_Result_Element;
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.Actions.Register_Action_Input_Loading_Element = function(Parameters)
{
	// TODO
};

Jelly.Handlers = 
{
	// Initialize blank instance variables.
	
	// TODO: label these.
	Default_Target: null,	
	
	// Refresh Handlers
	Refresh_Handlers: [],
}

;


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

Jelly.Handlers.Change_URL = function(Parameters)
{
	// TODO: Not sure what this does or why we need it. 
	// TODO: Discard?
	// Parameters["URL"]
	
	// Point browser to new URL
	document.location = Jelly.Directory + "?" + Parameters["URL"];
};

// TODO: DElete
/* 
Jelly.Handlers.Cancel_Event = function(Event_Name, Timeout)
{
	// Creates a named variable that erases itself after a certain time, which helps with cross-browser event propogation control.	
	// TODO: Maybe this should be our framework.
	// TODO: This is based on bubble, but maybe we can do build an untimed one as well in the same function.

	var Debug = false && Jelly.Debug.Debug_Mode;

	if (Debug)
		Jelly.Debug.Log("Start Catching '" + Event_Name + "'");
		
	Jelly.Interface.Event_Bubbles[Event_Name] = true;
	
	Jelly.Interface.Timeout(function(){
	
		if (Debug)
			Jelly.Debug.Log("Stop Catching '" + Event_Name+ "'"); 
			
		delete Jelly.Interface.Event_Bubbles[Event_Name];
		},
	Timeout);
};
*/

Jelly.Handlers.Document_Click = function(event)
{
	// Call handlers functions for document level clicks		
	// No handler functions currently exist.	
};


Jelly.Handlers.Document_Scroll = function (event)
{
	// Call handlers functions for document level scroll.
	
	// Hide Flash Uploaders
	// TODO: Can't build a more reliable mouseleave function :/
	//	Jelly.Files.Hide_Flash_Uploaders();
};

Jelly.Handlers.Register_Handler = function(Parameters)
{
	// Add a named event handler to an element.
	// Parameters: Element, Event, Code
	// TODO - removed 'if parameters code exists' below, because that was a strange place to verify. ok?
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register Handler: ");
//		Jelly.Debug.Group("Register Handler: " + Parameters["Event"] + " (" + Parameters["Element"].id + ")");
		Jelly.Debug.Log(Parameters);
	}
	
	switch (Parameters["Event"])
	{
		case "Refreshed":
		{
			Jelly.Handlers.Refresh_Handlers.push(Parameters["Code"]);
		}
		break;
		default:
		{
			// Get the reference for the element.
			var Reference;
			if (Parameters["Reference"])
				Reference = Parameters["Reference"];
			else
				Reference = Jelly.References.Get_Reference_For_Element(Parameters["Element"]);
	
			// If the reference exists, save the handler by name to this reference. 
			// TODO Shitty if?
			if (Reference) 
			{
				// If reference doesn't have any handlers, instantiate a handlers array
				// If an event list exists, add handler to each event.
				if (typeof Parameters["Event"] === "object")
				{
					for (var Event_Index in Parameters["Event"])
					{
						if (Parameters["Event"].hasOwnProperty(Event_Index))
						{	
							// Get event name
							var Event_Name = Parameters["Event"][Event_Index];
					
							// Store the handler by event name into the handlers array for this reference.
							Reference["Handlers"][Event_Name] = Parameters["Code"];				
						}
					}
				}
		
				// If an event list does not exist, add handler to single event.
				else
				{
					Reference["Handlers"][Parameters["Event"]] = Parameters["Code"];
				}
		
				if (Debug)
				{
					Jelly.Debug.Log("Registered handler...");
					Jelly.Debug.Log(Reference);
				}
			}	
			else
			{
				if (Debug)
					Jelly.Debug.Log("No reference found for element");
			}
		}
		break;
	}
	
	if (Debug)
		Jelly.Debug.End_Group("");
};

Jelly.Handlers.Document_Key_Down = function(Event)
{
	// Call handlers functions for document level key down clicks.

	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Log("Document_Key_Down...");
		Jelly.Debug.Log(Event);
		Jelly.Debug.Log("Key Down: " + Key_Code);
	}
	
	// Get Event and Event Target
	if (!Event) var Event = window.event;
	var Target;
	if (Event.target) 
		Target = Event.target;
	else if (Event.srcElement) 
		Target = Event.srcElement;
	// TODO What Safari bug? 
	if (Target.nodeType == 3) // defeat Safari bug
		Target = Target.parentNode;		
	
	if (Debug)
	{
		Jelly.Debug.Log("Target...");
		Jelly.Debug.Log(Target);
	}
	
	// If document event, target last clicked element
	if (Target.id == "Body" || Target.nodeName == "HTML")
		if (Jelly.Handlers.Default_Target)
			Target = Jelly.Handlers.Default_Target;
	
	// Get Target Type
	var Target_Type = Target.nodeName;
	
	// Get Event Key Code
	var Key_Code;
	if (Event.keyCode) Key_Code = Event.keyCode;
	else if (Event.which) Key_Code = Event.which;
	var Character = String.fromCharCode(Key_Code);
	if (Debug)
		Jelly.Debug.Log("Key Down: " + Key_Code);
		
	// Get Event Modifer
	// TODO - clarify alt, meta
	var Command_Modifier = (Event.altKey || Event.metaKey);
	var Direction_Modifier = Event.shiftKey;
				
	// TODO: Not sure why we're abstracting this yet, but I see it some places, not some others, so unifying
	var Prevent_Default_Behavior = false;
	switch (Key_Code)
	{
		// Enter
		case 13:		
			if (Debug)
			{
				Jelly.Debug.Log("Enter");
			}
			
			switch (Target_Type)			
			{
				// Handle text areas.
				case "TEXTAREA":
				{
					// If the current element is a text area, and a modifier key is held down, execute the current action.
					if (Command_Modifier)
					{
						// Prevent default behavior
						Prevent_Default_Behavior = true;
						
						Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': Target});
					}
							
					// Otherwise, insert an intelligently indented return character.
					else
					{
						// Prevent default behavior
						Prevent_Default_Behavior = true;
					
						Jelly.Interface.Insert_Return_With_Indented_Text(Target);
						
						var Autosize_Update_Event = document.createEvent('Event');
						Autosize_Update_Event.initEvent('autosize:update', true, false);
						Target.dispatchEvent(Autosize_Update_Event);
					}
					
					break;
				}
				case "DIV":
				{
					// If the current element is a content-editable div, and a modifier key is held down, execute the current action.
					if (Target.contentEditable == "true" && Command_Modifier)
					{
						// Prevent default behavior
						Prevent_Default_Behavior = true;
					
						Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': Target});
					}
					
					break;
				}
					
				// Handle all non-textarea elements.
				default:
					// Execute the current action.
					Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': Target});
					break;					
			}
			break;
			
		// Tab
		case 9:
			if (Debug)
			{
				Jelly.Debug.Log("Tab");
			}
			
			switch (Target_Type)
			{
				// Handle text areas.				
// 				case "TEXTAREA":
// 				
// 					// TODO: not consistent with the above, maybe... 
// 					if (!Command_Modifier)
// 					{
// 						// Prevent default behavior for tab keys in a text area.
// 						Prevent_Default_Behavior = true;	
// 						
// 						if (Event.shiftKey)
// 							Jelly.Interface.Remove_Tab(Target);
// 						else
// 							Jelly.Interface.Insert_Tab(Target);
// 					}
// 					break;
			}
			
			break;
		
		/* Continue from Here (TODO: What?!) */


		// Escape
		case 27:
			if (Debug)
			{
				Jelly.Debug.Log("Cancel");
			}
					
			// TODO: Hm, do we need to check for target for all of these? 
			if (Target)
			{
				//TODO - "event:escape"?
				// Call cancel handler for this target.
				Jelly.Handlers.Call_Handler_For_Target({'Event': 'Cancel', 'Target': Target});
				
				// Call dismiss handler for this target.
				Jelly.Handlers.Call_Handler_For_Target({'Event': 'Dismiss', 'Target': Target});
				
				// Prevent default behavior for escape.
				Prevent_Default_Behavior = true;
			}
			break;

		//Up arrow		
		case 38:
		// Down arrow
		case 40:
		//Left arrow
		case 37:
		//Right arrow
		case 39:				
			if (Debug)
			{
			}
			
			// If a menu is open, select the next or previous menu item
			if (Jelly.Interface.Base_Menu_Reference)
			{
				// Prevent default behavior for arrow keys when a menu is open.
				Prevent_Default_Behavior = true;
						
				// Find the lowest child menu reference.
				var Current_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
				while (Current_Menu_Reference.Child_Menu)
					Current_Menu_Reference = Current_Menu_Reference.Child_Menu;

				// Find the target menu item...
				var Target_Menu_Item = null;				
				
				// Determine whether to find the next or the previous menu item.
				if (Key_Code == 38 || Key_Code == 37)
					Menu_Direction = -1;
				else
					Menu_Direction = 1;
			
				// TODO: should menus have some additional ordering attribute instead?
				// Determine whether to consider the top or the left edge of a menu
				if (Key_Code == 38 || Key_Code == 40)
					Menu_Boundary_Property = "offsetTop";
				else
					Menu_Boundary_Property = "offsetLeft";

				// TODO: Settle on tag name or class name 
				var Current_Menu_Items = Current_Menu_Reference["Element"].getElementsByTagName("a");//document.getElementsByClassName("Jelly_Menu_Item");

				// Iteratively identify the previous or next menu item as the target
				for (var Current_Menu_Item_Index in Current_Menu_Items)
				{
					//TODO - is for each deprecated?
					//TODO - list 
					if (Menu_Items.hasOwnProperty(Menu_Item_Index))
					{
						var Current_Menu_Item = Current_Menu_Items[Current_Menu_Item_Index];
						
						// Consider the current menu item as the target if it is in the right direction, or if nothing is selected
						if (!Jelly.Interface.Selected_Menu_Item || Current_Menu_Item[Menu_Boundary_Property] * Menu_Direction > Jelly.Interface.Selected_Menu_Item[Menu_Boundary_Property] * Menu_Direction)

							// Consider the current menu item as the target if it is closer than the last target, or if there is no target being considered
							if (!Target_Menu_Item || Current_Menu_Item[Menu_Boundary_Property] * Menu_Direction < Next_Menu_Item[Menu_Boundary_Property] * Menu_Direction)
								Target_Menu_Item = Current_Menu_Item;
					}
				}
				
				// Focus the target menu item, if it has been found
				if (Target_Menu_Item)
					Jelly.Interface.Highlight_Menu_Item({"Menu_Item": Target_Menu_Item});

				// TODO: verify that this is for single item menus.
				// Otherwise, focus the current selected menu item
				else
					Jelly.Interface.Highlight_Menu_Item({"Menu_Item": Jelly.Interface.Selected_Menu_Item});
			
				// TODO: There used to be a line calling document mouse move, I changed it to call the direct action.
				Jelly.Interface.Hide_Highlights();
			}
			break;

// TODO: Remove below after testing above.	
/*
		// Left arrow
		case 37:
		// Right arrow
		case 39:
			var Direction = 1;
			if (Key_Code == 37)
				Direction = -1;
			
			if (Jelly.Interface.Base_Menu_Reference)
			{
				// Find lowest menu
				var Current_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
				while (Current_Menu_Reference.Child_Menu)
					Current_Menu_Reference = Current_Menu_Reference.Child_Menu;
					
//						Jelly.Debug.Log("Selected Menu Item");
				e.preventDefault();
				var Next_Menu_Item = null;
				var Menu_Items = Current_Menu_Reference["Element"].getElementsByTagName("a");//document.getElementsByClassName("Jelly_Menu_Item");
				for (var Menu_Item_Index in Menu_Items)
				{
					if (Menu_Items.hasOwnProperty(Menu_Item_Index))
					{
						var Menu_Item = Menu_Items[Menu_Item_Index];
//							Jelly.Debug.Log(Menu_Item.offsetLeft);
						if (!Jelly.Interface.Selected_Menu_Item)
							Next_Menu_Item = Menu_Item;
						else
						{
								if (Menu_Item.offsetLeft * Direction > Jelly.Interface.Selected_Menu_Item.offsetLeft * Direction)
									if (!Next_Menu_Item || Menu_Item.offsetLeft * Direction < Next_Menu_Item.offsetLeft * Direction)
										Next_Menu_Item = Menu_Item;
						}
					}
				}
				if (Next_Menu_Item)
					Jelly.Interface.Highlight_Menu_Item({"Menu_Item": Next_Menu_Item});
			}
			break;
*/
		// TODO: Needed?
		// Hide interface highlights on key down.
		default:	
			Jelly.Interface.Hide_Highlights();		
			break;
	}
	
	// TODO: this is a hack to see if we're getting a document key event			
	// TODO: and then??? why return false? commented out for now. - kunal
//			if (Target.nodeName == "HTML")
//			{
//				Jelly.Debug.Log("DOC");
//				Return_Value = false;
//			}		
	
	if (Debug)
		Jelly.Debug.Log('Prevent_Default_Behavior:' + Prevent_Default_Behavior);	

	// Compatibility - Chrome/Safari, prevent following KeyPress event from firing and inserting character.
	if (Prevent_Default_Behavior)
	{	
		Event.preventDefault();
		// TODO: clean?
		return false;
	}
	else 
		return true;
};

Jelly.Handlers.Set_Default_Target = function(Target)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Set_Default_Target");
		Jelly.Debug.Log(Target);
	}
	
	// Store target as focus of default handlers
	Jelly.Handlers.Default_Target = Target;
	
	if (Debug)
		Jelly.Debug.End_Group("");
};

Jelly.Handlers.Document_Mouse_Move = function(event)
{
	// Call handlers functions for document level mouse movement.

	// Hide jelly reference highlights
	Jelly.Interface.Hide_Highlights();
};

Jelly.Handlers.Document_Mouse_Down = function(event)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	// Call handlers functions for document level mouse down events.
	if (Debug)
		Jelly.Debug.Log("Document Mouse Down called");

	// Close menus
	Jelly.Interface.Close_Menus();	
};

Jelly.Handlers.Handle_Link_From_Click = function(Event, Parameters)
{
	// Hides flash uploaders & visits links, unless the metakey is held down.
	// Parameters - Allow_Embed, Default_Container, ...
	// TODO: Further parameters
	// TODO - (allow embed? default container?)
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	// Debug
	if (Debug)
	{
		Jelly.Debug.Group("Handle_Link_From_Click");
		Jelly.Debug.Log("Event...")
		Jelly.Debug.Log(Event);
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// If the meta-key is down let the browser handle the link (for clicking into a new tab)
	// TODO what about option-clicking (i.e. forcing download?)
	if ((Event && Event.metaKey))// || (!Parameters["Container"]))
	{
		// Google Chrome Fix - ctrlKey clicks propagate to href otherwise.
		// TODO: didn't text, but I don't think this was needed anymore.
// 		if (Event.ctrlKey)
// 			return true;				
// 		else
		
		// Return false to signify link was not handled
		return false;
	}
	
	// If link is direct (i.e. to Logout or non-HTML templates that shouldn't render in containers), let browser handle it
	if (Parameters["Direct"])
		return false;
	
	if (Event)
		Parameters["Calling_Element"] = Event.target;
	else if (Parameters["Namespace"])
	{
		Parameters["Calling_Reference"] = Jelly.References.References_By_Namespace[Parameters["Namespace"]];
		Parameters["Calling_Element"] = document.getElementById(Parameters["Namespace"]);
		if (Debug)
		{
			Jelly.Debug.Log("Calling Information...");
			Jelly.Debug.Log(Parameters["Namespace"])
			Jelly.Debug.Log(Parameters["Calling_Reference"])
			Jelly.Debug.Log(Parameters["Calling_Element"])
			Jelly.Debug.Log(Jelly.References.Get_Reference_For_Element(Parameters["Namespace"]))
		}
	}

	// Hide Flash Uploaders
	// TODO: why is this here?
// 	Jelly.Files.Hide_Flash_Uploaders();
	
	// Visit link
	Jelly.Handlers.Visit_Link(Parameters);
	
	// Debug
	if (Debug)
		Jelly.Debug.End_Group("");
	
	// Return true to signify that the link was handled
	return true;
};

Jelly.Handlers.Document_Context_Click = function(Event)
{
	// TODO: jQuery?
	if (!Event) 
		var Event = window.event;
		
	// TODO: This was originally written as a hack, but perhaps we need to complete it.
	if (Event.shiftKey)
		return true;

	// Verify context menu click source
	if (!Jelly.Interface.Bubble_Event_Protection('Context_Click'))
		return;
		
	// Handle Webkit bug
	// TODO: Still needed?
	Jelly.Interface.Catch_Webkit_Context_Click_Bug();

	// Show context menu for reference attached to Body Element (Site?)
	// TODO - verify that this works.
	Jelly.Interface.Show_Context_Menu({"Target_Element": Jelly.Body_Element, "Event": Event});

	Event.preventDefault();
};

Jelly.Handlers.Call_Handler_For_Target = function(Parameters)
{
	// Finds and calls the closest registered handler by specified event name for the specified target, searching references upwards from the target reference via parent and calling references
	// Parameters: {Target, Event, Display_Target, Remove_After_Calling}
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Call Handler for Target: " + Parameters["Event"]);
		Jelly.Debug.Log(Parameters);
	}
	
	// Validate display target & default to target
	if (!Parameters["Display_Target"])
		Parameters["Display_Target"] = Parameters["Target"];
	
	// Set calling element to target
	Parameters["Calling_Element"] = Parameters["Target"];
	
	// Find and validate reference for the target
	var Target_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Target"]);
	if (!Target_Reference)
	{
		if (Debug)
		{
			if (!Parameters["Target"])
			{
				Jelly.Debug.Log("Call Handler For Target: No target.");				
			}
			else
			{
				Jelly.Debug.Log("Call Handler For Target: No reference for target:")			
				Jelly.Debug.Log(Parameters["Target"]);
				Jelly.Debug.Print_All_Handlers();
			}
		}
		return;
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Target Reference found...");
		Jelly.Debug.Log(Target_Reference);
	}
	
	// Search for handler in reference tree
	var Search_Reference = Target_Reference;
	while (Search_Reference)
	{
		if (Debug)
			Jelly.Debug.Log("Searching for handler: " + Search_Reference["Namespace"]);
		
		// If the current reference has a handler for the event, call the handler, and quit searching.
		if (Search_Reference.Handlers)
		{
			if (Search_Reference.Handlers[Parameters["Event"]])
			{
				if (Debug)
				{
					Jelly.Debug.Log("Found reference with handler...");
					Jelly.Debug.Log(Search_Reference);
					Jelly.Debug.Group("Calling Found Handler function for Target...");
					Jelly.Debug.Log(Search_Reference.Handlers[Parameters["Event"]]);
				}
				
				// Call handler, passing all parameters directly into the handler script.
				// TODO: display target goes here? 
				Search_Reference.Handlers[Parameters["Event"]](Parameters);
			
				// TODO - may never be using anymore.
				if (Parameters["Remove_After_Calling"])
					delete Search_Reference.Handlers[Parameters["Event"]];
				
				if (Debug)
					Jelly.Debug.End_Group("Calling Found Handler function for Target...");
				
				break;
			}
		}
		
		// Trickle up through parent references, then calling references
		if (!Search_Reference.Stop)
		{	
			// Move up via parent reference if there is a valid parent reference.
			// TODO - would the latter half of this condition be a tree bug, or valid?
			if (Search_Reference.Parent_Reference &&  Search_Reference.Parent_Reference != Search_Reference)
			{	
//				Jelly.Debug.Log("Parent Ref");
				Search_Reference = Search_Reference.Parent_Reference;
			}

			// Move up via calling reference if there is a valid calling reference.
			// TODO - would the latter half of this condition be a tree bug, or valid?
			else if (Search_Reference.Calling_Reference && Search_Reference.Calling_Reference != Search_Reference)
			{
//				Jelly.Debug.Log("CALING REF");
				Search_Reference = Search_Reference.Calling_Reference;
			}
			
			// If no parent or calling reference is found, finish searching
			else
				Search_Reference = null;
		}
		
		// If the search reference has a search block, finish searching.
		else
			Search_Reference = null;
	}
	
	if (!Search_Reference)
	{
		if (Debug)
		{
			Jelly.Debug.Log('No match found for handler.');
			Jelly.Debug.Print_All_Handlers();
		}
	}
	

	if (Debug)
		Jelly.Debug.End_Group("Call Handler for Target: " + Parameters["Event"]);
	
	return;
};

Jelly.Payments = 
{
};

;

Jelly.Payments.Update_Ticket_Forecast = function(Parameters)
{	
	// TODO - Notate Description & Inputs Up here
	
	// Get values
	var Namespace = Parameters['Namespace'];
	console.log(Namespace);
	var Ticket_Price = Jelly.jQuery('#' + Namespace + ' ' + '.Price .Input input').val();
	var Capacity = Jelly.jQuery('#' + Namespace + ' ' + '.Capacity .Input input').val();
	var Volunteers = Jelly.jQuery('#' + Namespace + ' ' + '.Volunteers .Input input').is(':checked');
	var Donations = Jelly.jQuery('#' + Namespace + ' ' + '.Donations .Input input').is(':checked');
	
	var Forecast = "";
	
	if (Ticket_Price && Capacity && Ticket_Price != 0 && Capacity != 0)
	{
		Forecast = '$' + '<span class="Number">' + Math.round(Ticket_Price * Capacity).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</span>' + ' ' + 'at sellout';
		
		if (Donations)
			Forecast += ' + ' + '$' + '<span class="Number">' + Math.round(Ticket_Price * Capacity * .15).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</span>' + ' ' + 'in donations';
		
		if (Volunteers)
		{
			Forecast += ' + ' + '<span class="Number">' + Math.round(Capacity * .05) + '</span>' + ' ' + 'volunteer';
			if (Math.round(Capacity * .05) != 1) Forecast += 's';
		}
	}
				
	var Forecast_Element = Jelly.jQuery('#' + Namespace + ' ' + '.Forecast');
	
	if (Forecast)
	{
		var Forecast_Value_Element = Jelly.jQuery('#' + Namespace + ' ' + '.Forecast .Value');
		Forecast_Value_Element.html(Forecast);	

		Forecast_Element.addClass('Show');

		if (Donations || Volunteers)
			Forecast_Element.addClass('Show_Hint');
		else
			Forecast_Element.removeClass('Show_Hint');
	}
	else
	{
		Forecast_Element.removeClass('Show')
		Forecast_Element.removeClass('Show_Hint')
	}
};

Jelly.Interface =
{	
	// Lightbox element.
	Lightbox_Element: null,

	// TODO - I feel funky about this one.
	Global_Controls_Element: null,
	
	// TODO - ???
	Highlight_Parts: null,
	Highlight_Target_Namespace: null,

	// Bubbles
	Event_Protection: {},
	Event_Protection_Period: 50,
	
	// Maps
	Maps: {},
	
	// Windows
	Window_Index: 0,
	Window_Z_Index_Start: 1000,
	Menu_Z_Index_Start: 2000,
	Modal_Windows: new Array(),
	WIndow_Buffer: 20,

	// Menus
	Active_Menu_Reference: null,
	Base_Menu_Reference: null,
	Menu_Position_Offset: {Left: 1, Top: -5},
	Selected_Menu_Item: null,
	
	// Modality...
	// State
	Is_Locked: false,
	
	// TODO - more integrated listeners
	Refresh_Container_Listener: null,
	
	// TODO - Inspector hack
	Inspect_Item: null 
};

;

// TODO - don't know what this was intended to be.
Jelly.Interface.Lock = function()
{	
	Jelly.Interface.Is_Locked = true;
	
	// Prevent scrolling
//	Jelly.jQuery('body').css('overflow', 'hidden');
	Jelly.jQuery('#Jelly_Wrapper').css('overflow', 'hidden');
};

Jelly.Interface.Manage = function(Parameters)
{
	// Navigate to manage interface for this type.
	var Type_Alias = Parameters["Type_Alias"];
	var Manage_Path = "/" + Type_Alias.toLowerCase() + "/" + "manage";
	Jelly.Handlers.Visit_Link({"URL": Manage_Path});

	// Open this item in the inspector.
	if (Parameters["Item_ID"])
	{
		var Item_ID = Parameters["Item_ID"]
		Jelly.Interface.Inspect({
				"Type_Alias": Type_Alias,
				"Item_ID": Item_ID
			}
		);
	}
	
	// If there is no item, hide the inspector.
	else
		Jelly.Interface.Hide_Inspector();
}

// TODO - don't know what this was intended to be.
Jelly.Interface.Unlock = function()
{	
	// Unlock interface
	Jelly.Interface.Is_Locked = false;
	
	// Allow scrolling
//	Jelly.jQuery('body').css('overflow', 'auto');
	Jelly.jQuery('#Jelly_Wrapper').css('overflow', 'scroll');
	
	// Handle callbacks
	// This is an array	
	for (Unlock_Function_Index in Jelly.Interface.On_Unlock)
	{
		Jelly.Interface.On_Unlock[Unlock_Function_Index].call();
	}
};

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

// TODO - the code below doesn't really make sense - see how it's used and rewrite if it's used. 
Jelly.Interface.Timeout = function(Script, Period)
{	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Timeout");
		Jelly.Debug.Log("Script...");
		Jelly.Debug.Log(Script);
	}
	
	if (!Period)
		Period = Jelly.Interface.Event_Protection_Period;
		
	if (Debug)
		Jelly.Debug.End_Group("");

	return setTimeout(Script, Period);
	
};

// TODO: this function
Jelly.Interface.Set_Time = function(Namespace, Value)
{	
	Input_Element = document.getElementById(Namespace + "_Time");
	Input_Element.value = Value;			
	Jelly.Handlers.Cancel_Event("Click");
	Input_Element.select();
};

// TODO:  comment and describe what this does
// TODO: Shit function
Jelly.Interface.Set_Date = function(Namespace, Value)
{	
	var Date_Value = new Date(Value);
	Input_Element = document.getElementById(Namespace + "_Date");
	Input_Element.value = (Date_Value.getMonth() + 1) + "/" + (Date_Value.getDate()) + "/" + (Date_Value.getFullYear());
	
	Jelly.Handlers.Cancel_Event("Click");
	Input_Element.select();
};

Jelly.Interface.Insert_Tab = function(Target)
{
	// Get the current selection
	var Selection_Start = Target.selectionStart;
	var Selection_End = Target.selectionEnd;	
	var Has_Selected_Text = !(Selection_Start == Selection_End)

	// If there is a selection, indent the selection	
	if (Has_Selected_Text)
	{
		// Get the current text
		var Text = Target.value;

		// If first character is a new line, move earlier to make sure it is included
		if (Text.charAt(Selection_Start) == "\n")
			Selection_Start--;
			
		// Search backwards to find new line or beginning of text
		while (Selection_Start != -1 && Text.charAt(Selection_Start) != "\n")
			Selection_Start--;
			
		// If last character is a new line, remove it from the selection
		if (Text.charAt(Selection_End - 1) == "\n")
			Selection_End--;
		
		// Insert tabs after new lines until end of selection
		var Position = Selection_Start;
		while (Position < Selection_End)
		{
			// Advance past new line
			Position++;
	
			// Splice in new tab and note that selection is now longer
			Text = Text.substr(0, Position) + "\t" + Text.substr(Position);
			Selection_End++;
	
			// Search for next new line until end of selection
			while (Position < Selection_End && Text.charAt(Position) != "\n")
				Position++;
		}
			
		// Search for next new line until end of text
		while (Position < Text.length && Text.charAt(Position) != "\n")
			Position++;

		// Update the textarea with the new text.
		Target.value = Text;
		
		// Update the textarea with the new selection
		Target.selectionStart = Selection_Start + 1;
		Target.selectionEnd = Position;
	}

	// If no selection, add a tab at the cursor
	else
	{		
		// Add a tab at the cursor
		Target.value = Target.value.substr(0, Selection_Start) + "\t" + Target.value.substr(Selection_End);
		
		// Move the cursor to after the tab
		Target.selectionStart = Selection_Start + "\t".length;
		Target.selectionEnd = Selection_Start + "\t".length;
	}
};

Jelly.Interface.Remove_Tab = function(Target)
{
	// Get the current selection
	var Selection_Start = Target.selectionStart;
	var Selection_End = Target.selectionEnd;	
	var Has_Selected_Text = !(Selection_Start == Selection_End)

	// If there is a selection, unindent the selection	
	if (Has_Selected_Text)
	{
		// Get the current text
		var Text = Target.value;

		// If first character is a new line, move earlier to make sure it is included
		if (Text.charAt(Selection_Start) == "\n")
			Selection_Start--;

		// Search backwards to find new line or beginning of text
		while (Selection_Start != -1 && Text.charAt(Selection_Start) != "\n")
			Selection_Start--;
			
		// If last character is a new line, remove it from the selection
		if (Text.charAt(Selection_End - 1) == "\n")
			Selection_End--;
		
		// Insert tabs after new lines until end of selection
		var Position = Selection_Start;
		while (Position < Selection_End)
		{
			// Advance past new line
			Position++;

			// Check if line starts with a tab
			if (Text.charAt(Position) == "\t")
			{
				// Splice out old tab and note that selection is now shorter
				Text = Text.substr(0, Position) + Text.substr(Position + 1);
				Selection_End--;
			}

			// Search for next new line until end of selection
			while (Position < Selection_End && Text.charAt(Position) != "\n")
				Position++;
		}
			
		// Search for next new line until end of text
		while (Position < Text.length && Text.charAt(Position) != "\n")
			Position++;

		// Update the textarea with the new text.
		Target.value = Text;
		
		// Update the textarea with the new selection
		Target.selectionStart = Selection_Start + 1;
		Target.selectionEnd = Position;

	}

	// If no selection, 	... 
	// TODO: Then what? 
	else
	{
	}
};


Jelly.Interface.Create_Menu = function(Parameters)
{
	// Generate a menu within a global reference, attach it to the existing menu stack or set it as the base menu, fill or copy in content, and attach menu handlers/behaviors.

	// Parameters: Attach, Attach_Element, Event, Edge, Restrict_Position, Do_Not_Focus_First_Item, 
	// TODO: additional parameters as x-reffed below.
	// TODO: clean up 'attach', 'attach element'
	// ... Post_Values
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create Menu");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Block refreshing on a namespace
	// TODO - remove after property refreshing
	if (Parameters["Block_Refresh"])
	{	
		Jelly.References.Block_Refresh({'Namespace': Parameters["Block_Refresh"]});
	}
		
	// Generate menu reference
	var Menu_Parameters = {};

	// TODO - handle this kind of thing better
	if (Parameters["Source_Element_ID"])
	{	
		Menu_Parameters["Kind"] = "HTML";
	}	
	else
	{
		Menu_Parameters["Kind"] = "URL";
		Menu_Parameters["URL"] = Parameters["URL"];
		Menu_Parameters["Post_Values"] = Parameters["Post_Values"];
	}
	Menu_Parameters["Parent_Namespace"] = "Jelly";
	
	// Set unique namespace
	Jelly.References.Current_Global_Reference_Index++;
	var Menu_Namespace = Jelly.Interface.Global_Controls_Element.id + Jelly.Namespace_Delimiter + Jelly.References.Current_Global_Reference_Index + Jelly.Namespace_Delimiter + Parameters["Alias"];
	Menu_Parameters["Namespace"] = Menu_Namespace;
		
	// Don't create if already exists (maybe to make sure we don't recreate menus that are already open)
	// TODO: This good?
	if (document.getElementById(Menu_Namespace))
		return;
		
	// Create menu element and add to DOM in global controls
	var Menu_Control_Element = Jelly.Interface.Generate_Menu(Menu_Namespace);
	Jelly.Interface.Global_Controls_Element.appendChild(Menu_Control_Element);
	
	// Copy over anything else needed from Parameters	
	
	// Register our new menu
	var Menu_Reference = Jelly.References.Register(Menu_Parameters);
	
	// TODO: A lot of this element hooplah can be handled with the whole Generate_Menu... approach, for better or for worse, probably for better.
	// TODO: Then this function would just handle the references and any content requests
		
	// Store menu's Control Element in reference
	Menu_Reference["Control_Element"] = Menu_Control_Element;
	// TODO - bold move... also, totally destroys any one to one relationship here.
	Menu_Control_Element.Jelly_Reference = Menu_Reference;
	
	// Set reference's element, and vice versa. 
	var Menu_Element = Menu_Reference["Element"];
	Menu_Element.Jelly_Reference = Menu_Reference;
	
	// Place the menu element at the topmost menu z-index
	// TODO - "Do I think we should have a better way to do this? Yes!" - Tristan, 2014
	var Menu_Count = 0;
	var Recursive_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
	while (Recursive_Menu_Reference)
	{
		Menu_Count++;
		
		// Traverse child menu reference
		Recursive_Menu_Reference = Recursive_Menu_Reference.Child_Menu;
	}
	Menu_Control_Element.style.zIndex = Jelly.Interface.Menu_Z_Index_Start + Menu_Count;
	
	// Attach menu to parent menu, or set as base menu.
	var Is_Base_Menu = true;
	
	// If an attach element is designated,  find a menu reference corresponding to it.
	// TODO: Is there a simpler format of this search? I think not...
	// TODO: Do we need "attach"?
	if (Parameters["Attach"])
	{
		// Verify that the attach element, or any of it's parents, match any reference in the menu reference stack.
		var Search_Menu_Element = Parameters["Attach_Element"];
		while (Search_Menu_Element)
		{
			// Start from base menu reference
			var Search_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
			
			while (Search_Menu_Reference)
			{
				// If the menu reference matches this attach element, store it // TODO: and break?
				if (Search_Menu_Reference["Element"] == Search_Menu_Element)
				{
					var Parent_Menu_Reference = Search_Menu_Reference;
					Is_Base_Menu = false;
					// TODO: probably break here, no? 
					// TODO: Added a break 2 here, though I didn't write this code originally, so should verify.
					// break 2;
				}
					
				// Search next child reference.
				Search_Menu_Reference = Search_Menu_Reference.Child_Menu;
			}
			
			// Search next parent element.
			Search_Menu_Element = Search_Menu_Element.parentNode;
		}
		
		// If attaching to an element, also set it as the calling reference
		// TODO: Do we still store this here, without verifying it passed the above test?
		// TODO: Do we store attach_element, or the found parent_menu_element, as the calling reference?
		Menu_Reference["Calling_Reference"] = Jelly.References.Get_Reference_For_Element(Parameters["Attach_Element"]);
	}
	
	// If this is a base menu, set up the base menu
	if (Is_Base_Menu)
	{	
		// Store reference to base menu of future menu tree
		// TODO: X-Ref and Make sure this has everything needed
		Jelly.Interface.Base_Menu_Reference = Menu_Reference;	
	}
	
	// Otherwise, add it to the parent menu
	else
	{
		// Store parent/child menu references
		Parent_Menu_Reference.Child_Menu = Menu_Reference;
		Menu_Reference.Parent_Menu = Parent_Menu_Reference;
	}

	// Request or copy content for this menu.... 
	
	// TODO: ?! (really? )
	if (Menu_Reference["Kind"] == "HTML")
	{
		// Copy inner HTML from source element
		Menu_Control_Element.innerHTML = document.getElementById(Parameters["Source_Element_ID"]).innerHTML;
		
		// Position menu in final position
		Jelly.Interface.Position_Container({
			Event: Parameters["Event"],
			Element: Menu_Control_Element,
			Edge: Parameters["Edge"],
			Attach: Parameters["Attach"],
			// TODO: Should it be this one, or the found attach_element?
			Attach_Element: Parameters["Attach_Element"],
			Restrict_Position: Parameters["Restrict_Position"],
			Update_Mouse_Position: true
		});

	}
	else
	{
		// Position menu in initial position
		Jelly.Interface.Position_Container({
			Event: Parameters["Event"],
			Element: Menu_Control_Element,
			Edge: Parameters["Edge"],
			Attach: Parameters["Attach"],
			// TODO: Should it be this one, or the found attach_element?
			Attach_Element: Parameters["Attach_Element"],
			Restrict_Position: Parameters["Restrict_Position"],
			Update_Mouse_Position: true
		});

		// Refresh by URL (will position in final position on load)
		Jelly.References.Refresh(Menu_Reference);
	}

	
	// Inform any close_menus calls in this specific event bubble,  such as the document mousedown call, to keep this menu open, via a quick ephemeral variable setting
	Jelly.jQuery(Menu_Control_Element).mousedown(function(event)
			{	
				// TODO - see if I can integrate with the rest of the bubble handling, don't quite like the special case.
				// TODO - aka Jelly.Bubble_Event_Protection_With_Value(...);
				Jelly.Interface.Active_Menu_Reference = Menu_Reference;
				Jelly.Interface.Timeout("Jelly.Interface.Active_Menu_Reference = null;");
			}
		);
		
	// Fade menu in
	Menu_Control_Element.style.display = "none";
	Jelly.jQuery(Menu_Control_Element).fadeIn();
	
	// TODO - there used to be an explicit Active_Menu_Reference bubble run where this comment lies, but I removed it, didn't seem necessary

	// Attach dismiss, cancel, done handlers to close menus
	// TODO - consistent naming? 
	Jelly.Handlers.Register_Handler(
			{
				"Element": Menu_Element, 
				"Event": "Dismiss", 
				"Code": function() 
					{
						// Release refreshing on a namespace
						// TODO - remove after property refreshing
						if (Parameters["Block_Refresh"])
						{
							Jelly.References.Release_Refresh({'Namespace': Parameters["Block_Refresh"]});
						}
						
						// Ignore the active menu reference protection for this menu, since the dismiss handler is called directly.
						Jelly.Interface.Close_Menus({'Force_Close_Active_Menu': true});
					}
			}
		);
	
	//TODO - do we need Done, Cancel in Menus? 
	Jelly.Handlers.Register_Handler(
			{
				"Element": Menu_Element, 
				"Event": 
					[
						"Cancel", 
						"Done"
					],
				"Code": function() 
					{
						var Parameters = 
						{	
							"Event": "Dismiss",
							"Target": Menu_Element
						}
						Jelly.Handlers.Call_Handler_For_Target(Parameters);
					}
			}
		);
	
	// Catch  & ignore "execute" events
	// TODO - not sure what this is about
	Jelly.Handlers.Register_Handler(
			{
				"Element": Menu_Element,
				"Event": "Execute", 
				"Code": function()
					{
					}
			}
		);
	
	// On Load: Reposition menu, focus first item, set up link behaviors, set up close menu behaviors
	Jelly.Handlers.Register_Handler(
			{
				"Element": Menu_Element,
				"Event": "On_Load",
				"Code": function()
					{	
						var Debug = false && Jelly.Debug.Debug_Mode;

						if (Debug)
						{
							Jelly.Debug.Group("Create_Menu On_Load_Function");
						}
								
						// Reposition menu, now with filled content.
						Jelly.Interface.Position_Container(
							{
								Event: Parameters["Event"],
								Element: Menu_Control_Element,
								Edge: Parameters["Edge"],
								Attach: Parameters["Attach"],
								Attach_Element: Parameters["Attach_Element"],
								Restrict_Position: Parameters["Restrict_Position"],
								Update_Mouse_Position: true
							}
						);
						
						// Set cursor to pointer for all menu rows with links
						Jelly.jQuery(Menu_Control_Element).find(".Jelly_Menu_Row").each(function (Index) {
								if (Jelly.jQuery(this).find("a:first").length > 0)
									this.style.cursor = "pointer";
							});
							
						// Add focus event to display highlight for menu row
						Jelly.jQuery(Menu_Control_Element).find("a").focus(function(event)
							{
								// Highlight the menu row
								Jelly.Interface.Highlight_Menu_Item({"Menu_Item": this});
							});

						// Focus first item, if not specified otherwise
						if (!Parameters["Do_Not_Focus_First_Item"])
						{
							Jelly.Interface.Focus_First_Menu_Item({"Menu_Element": Menu_Control_Element});
						}
		
						// Setup menu rows to pass mouseovers into inner links.
						Jelly.jQuery(Menu_Control_Element).find(".Jelly_Menu_Row").mouseover(function(event)
							{
								// If event's target isn't within a link, then highlink first link
								if (event.target.nodeName != "A" && Jelly.jQuery(event.target).parents("a").length == 0)
									Jelly.jQuery(this).find("a:first").mouseover();
							});
		
						// Convert link mouse overs to focus events, unless specified
						// TODO - test if works.
						if (!Parameters["Do_Not_Focus_On_Hover"])
						{
							Jelly.jQuery(Menu_Control_Element).find("a").mouseover(function(event)
								{
									Jelly.jQuery(this).focus();
								});
						}
						
						// If you want to keep the focus on another control, this allows the browser to look like it's focused without actually focusing.
						// TODO - test if works.
						else
						{							
							Jelly.jQuery(Menu_Control_Element).find("a").mouseover(function(event)
								{
									// Highlight the menu row.
									Jelly.Interface.Highlight_Menu_Item({"Menu_Item": this});
								});
						}
			
						// Setup menu rows to pass clicks into inner links.
						Jelly.jQuery(Menu_Control_Element).find(".Jelly_Menu_Row").click(function(event)
							{
								// If event's target isn't within a link, then click first link
								if (event.target.nodeName != "A" && Jelly.jQuery(event.target).parents("a").length == 0)
									Jelly.jQuery(this).find("a:first").click();
							});
												
						// Add click handler to menu links to close their menus, unless specified otherwise
						Jelly.jQuery(Menu_Control_Element).find("a").click(function(event)
							{	
								// Call the dismiss handler, which closes the menu on click, unless specified otherwise
								if (!Jelly.jQuery(this).hasClass("Do_Not_Close_Menu_On_Click"))
								{
									Jelly.Handlers.Call_Handler_For_Target({'Event': 'Dismiss', 'Target': this});								
								}
							});
		
						if (Debug)
							Jelly.Debug.End_Group("Create_Menu On_Load_Function");
					}
			}
		);

	// Call on load handler directly if copying loaded data,
	if (Parameters["Source_Element_ID"])
	{	
		Jelly.Handlers.Call_Handler_For_Target(
				{
					"Event": "On_Load",
					"Target": Menu_Element
				}
			);
	}

	if (Debug)
		Jelly.Debug.Log(Menu_Reference);
		
	if (Debug)
		Jelly.Debug.End_Group("Create Menu");
	
	// Return the reference
	return Menu_Reference;
};

Jelly.Interface.Close_Menus = function(Parameters)
{
	// Close all menus, or all menus below the designated active menu, and clean up as needed.
	// aka Close users if user didn't click on a menu
	// TODO: search through event target's parents to determine the active menu might be a better way?
	// Parameters: Force_Close_Active_Menu
		
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Close_Menus");
		Jelly.Debug.Log("Active Menu Reference...");
		Jelly.Debug.Log(Jelly.Interface.Active_Menu_Reference);
		Jelly.Debug.Log("Base Menu Reference...");
		Jelly.Debug.Log(Jelly.Interface.Base_Menu_Reference);
	}
	
	// Traverse through base menu's child menus, removing every menu below the active menu.
	var Active_Menu_Reference = Jelly.Interface.Active_Menu_Reference;
	var Recursive_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
	var Found_Menu = false;
	var Force_Close_Active_Menu = false;
	if (Parameters && Parameters['Force_Close_Active_Menu'])
		Force_Close_Active_Menu = true;
		
	while (Recursive_Menu_Reference)
	{
		if (Debug)
		{
			Jelly.Debug.Log("Recursive menu check...(next two lines)");
			Jelly.Debug.Log(Recursive_Menu_Reference);
			Jelly.Debug.Log(Found_Menu);
		}
		
		// Remove menu if there is no active menu, or it's a descendant the active menu, or the menu is specified to be force closed.
		if (Force_Close_Active_Menu || Active_Menu_Reference == null || (Active_Menu_Reference != null && Found_Menu !== false))
		{
			if (Debug)
				Jelly.Debug.Log("Will Close this one.");
				
			// TODO: Should this call some generic close_menu function instead? 
			// Unlink from parent menu
			if (Recursive_Menu_Reference.Parent_Menu)
			{
				Recursive_Menu_Reference.Parent_Menu.Child_Menu = null;
				Recursive_Menu_Reference.Parent_Menu = null;
			}
			
			// Remove from DOM
			var Menu_Reference = Recursive_Menu_Reference;
			Jelly.Interface.Fade_Out_And_Remove(Menu_Reference["Control_Element"]);

			// Remove reference
			Jelly.References.Remove_Reference(Menu_Reference);
		}
		
		// If the active menu has been traversed, set a flag to change subsequent behavior
		if (Recursive_Menu_Reference == Active_Menu_Reference)
			Found_Menu = true;
		
		// Traverse child menu reference
		Recursive_Menu_Reference = Recursive_Menu_Reference.Child_Menu;
	}
	
	// If all menus have been closed, clean up
	if (!Found_Menu)
	{
		// Remove reference to base menu
		Jelly.Interface.Base_Menu_Reference = null;
	}
	
	// TODO: Does this look right?
	Jelly.Interface.Selected_Menu_Item = null;
	
	// Clean references
	// TODO: Does this belong here? 
	Jelly.References.Clean_References();
	
	if (Debug)
		Jelly.Debug.End_Group("Close_Menus");
};

// TODO: Delete
/*
Jelly.Interface.Cancel_Bubble = function(Event_Name)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Cancel_Bubble");
		Jelly.Debug.Log("Script...");
		Jelly.Debug.Log(Script);
	}
	
	Jelly.Interface.Event_Bubbles[Event_Name] = false;
	
	if (Debug)
		Jelly.Debug.End_Group("");
};
*/

Jelly.Interface.Generate_Map = function(Parameters)
{	
	// Create a named map, with a default tile server, and a default location
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Generate_Map");
		console.log(Parameters);
	}
	
	// Get defaults...	
	var Default_Tile_Server_Name = "OpenStreetMap";
	var Default_Tile_Server_URL = "http://{s}.tile.osm.org/{z}/{x}/{y}.png";
	var Default_Tile_Server_Attribution_URL = "http://www.openstreetmap.org/copyright";
	// TODO - not exactly what they wanted, but looks better.
	var Default_Tile_Server_Attribution = "&copy; <a href=\"" + Default_Tile_Server_Attribution_URL + "\">" + Default_Tile_Server_Name + "</a>";
	
	// Get parameters...
	var Map_Alias = Parameters['Alias'];
	
	// Create map...
	var Map = new L.map(Map_Alias, {scrollWheelZoom: false});
	
	// Refresh icons 
	// TODO - generalize.
	Map.on('popupopen', function() {
			var iconic = IconicJS();
			iconic.inject('img.iconic');
		});
		
	// Locate user if specified.
	if (Parameters["Locate_User"])
	{			
		// Create home marker icon
		var Home_Marker_Icon = L.AwesomeMarkers.icon({
			prefix: 'fa', 	
			iconColor: 'white',
			markerColor: 'red',
			extraClasses: 'fa-2x',
			icon: 'home'
		  });
		  
		// Create marker on locate
		Map.on('locationfound',  function (e)
				{
					// Create marker
					var Home_Marker = L.marker(e.latlng, {icon: Home_Marker_Icon}).addTo(Map);
					
					// Store marker
					Jelly.Interface.Maps[Map_Alias]["Markers"].push(Home_Marker);
										
					// Create accuracy radius
					// TODO 3x'd it to be an accuracy halo
					var radius = (e.accuracy / 2) * 3 ;
					L.circle(e.latlng, radius).addTo(Map);
										
					// Recalibrate view
					Jelly.Interface.Show_All_Locations({Map_Alias: Map_Alias});
				}
			);

		// Locate user without moving view.
		var Default_Map_Zoom = 13;
		Map.locate({setView: false, maxZoom: Default_Map_Zoom});
	}
	
	// Add tiles	
	var Tile_Layer = new L.tileLayer(Default_Tile_Server_URL, {attribution: Default_Tile_Server_Attribution}
		).addTo(Map);
		
	// Store map
	var Map_Object = {"Map": Map};
	Jelly.Interface.Maps[Map_Alias] = Map_Object;
			
	if (Debug)
		Jelly.Debug.End_Group("Generate_Map");
};

Jelly.Interface.Show_Toolbar = function(Parameters)
{
	// Open Toolbar	
	Jelly.jQuery("#Jelly_Toolbar").addClass("Visible");
	Jelly.jQuery("#Jelly_Main_Wrapper").addClass("Toolbar_Visible");
	Jelly.jQuery("#Jelly_Inspector").addClass("Toolbar_Visible");
};


Jelly.Interface.Hide_Sidebar = function(Parameters)
{
	Jelly.Interface.Hide_Browse_Bar();
	Jelly.jQuery('#Jelly_Sidebar').removeClass('Visible');
	Jelly.jQuery('#Jelly_Wrapper').removeClass('Admin_Mode');
	Jelly.jQuery('#Jelly_Content').removeClass('Sidebar_Visible');
};


Jelly.Interface.Hide_Toolbar = function(Parameters)
{
	// Open Toolbar	
	Jelly.jQuery("#Jelly_Toolbar").removeClass("Visible");
	Jelly.jQuery("#Jelly_Main_Wrapper").removeClass("Toolbar_Visible");
	Jelly.jQuery("#Jelly_Inspector").removeClass("Toolbar_Visible");
};


Jelly.Interface.Close_Window = function(Window_Reference)
{
	// Fades out a window and removes it from the DOM, removes its corresponding jelly reference, removes it from the windows stack, and repositions lightbox as appropriate.
	// Window_Reference: reference to stored reference in Jelly.Interface.Modal_Windows
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Close_Window");
		Jelly.Debug.Log("Window_Reference...");
		Jelly.Debug.Log(Window_Reference);
	}
	
	// Get the window element
	var Window_Control_Element = Window_Reference["Control_Element"];
	
	// Fade it out and remove it
	Jelly.Interface.Fade_Out_And_Remove(Window_Control_Element);
	
	// If this is the top-modal window, remove it from the stack, and reposition lightbox, or hide it.  
	// TODO: we probably only need to check the latter, unless we're building in handling for closing modal, non-top windows
	// TODO: implement Modal
	if (Window_Reference == Jelly.Interface.Modal_Windows[Jelly.Interface.Modal_Windows.length - 1])
	{
		// Remove from modal windows stack
		Jelly.Interface.Modal_Windows.pop();
		
		// Reposition the lightbox if there are modal windows remaining
		if (Jelly.Interface.Modal_Windows.length > 0)
			Jelly.Interface.Show_Lightbox();	
			
		// Or hide the lightbox if there are no modal windows remaining.
		else
			Jelly.Interface.Hide_Lightbox();
	}
	
	// Remove the parent global reference (created specifically for this window)
	Jelly.References.Remove_Reference(Window_Reference);
	
	// If this is the last window unlock the modal state.
	if (Jelly.Interface.Modal_Windows.length == 0)
	{
		Jelly.Interface.Unlock();
	}
	
	if (Debug)
		Jelly.Debug.End_Group("Close_Window");
};

Jelly.Interface.Show_Sidebar = function(Parameters)
{
	Jelly.jQuery('#Jelly_Sidebar').addClass('Visible');
	Jelly.jQuery('#Jelly_Wrapper').addClass('Admin_Mode');
	Jelly.References.Trigger_Refresh({Kind: 'Element', Element: Jelly.jQuery('#Jelly_Sidebar \[data-kind=Item\]').get(0)});
	Jelly.jQuery('#Jelly_Content').addClass('Sidebar_Visible');
};


Jelly.Interface.Close_Manage = function(Parameters)
{	
	// If Inspector
	if (Jelly.Interface.Inspect_Item)
		var URL = "/" + Jelly.Interface.Inspect_Item["Type_Alias"].toLowerCase() + "/" + Jelly.Interface.Inspect_Item["ID"];

	// Else if there's a list page specified 
	else if (Parameters["List_Page_Alias"])
		var URL = "/" + "page" + "/" + Parameters["List_Page_Alias"].toLowerCase();
	
	// Otherwise go to the home page. 
	else
		var URL = "/";
	
	// Close inspector.
	Jelly.Interface.Hide_Inspector();
	
	// Go to home page.
	Jelly.Handlers.Visit_Link({"URL": URL});
};

Jelly.Interface.Hide_Lightbox = function()
{
	// Fade lightbox out
	Jelly.jQuery(Jelly.Interface.Lightbox_Element).fadeOut("fast");
};

Jelly.Interface.Show_Lightbox = function()
{
	// Generate lightbox if it doesn't exist, then move it to beneath top window, and display it. 
	 
	// Generate lightbox, if it doesn't exist.
	if (!Jelly.Interface.Lightbox_Element)
	{
		Jelly.Interface.Lightbox_Element = Jelly.Interface.Generate_Lightbox();
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Lightbox_Element);
	}

	// Move lightbox beneath highest window.
	// TODO: Strange Z-Index metric. I guess it makes sense, but I'm leaving this here in case we decide to switch to -1, 0 instead of 0, +1 as it is now.
	Jelly.Interface.Lightbox_Element.style.zIndex = Jelly.Interface.Window_Z_Index_Start + Jelly.Interface.Modal_Windows.length * 2;	
	
	// Fade in lightbox
	Jelly.Interface.Lightbox_Element.style.display = "none";
	Jelly.jQuery(Jelly.Interface.Lightbox_Element).fadeIn("fast");
};

Jelly.Interface.Generate_Menu = function(Namespace)
{
	// TODO: Possibly add something built in, via something like 
/*

return [Format as "Javascript String"]
	<div>
		yay.
	</div>
[/Format];
*/
	// TODO: Remove below
	var Menu_Element = document.createElement("div");
	Menu_Element.id = Namespace + "_Wrapper";
	Menu_Element.className = "Jelly_Menu";
	
	var Reference_Element = document.createElement("div");
	Reference_Element.id = Namespace;
	Reference_Element.style.backgroundColor = "white";
	Reference_Element.innerHTML = "<div style=\"width: 75px; height: 25px;\"></div>";
	Menu_Element.appendChild(Reference_Element);

	return Menu_Element;
}


Jelly.Interface.Create_Window = function(Parameters)
{
	// Create a window in a global reference, position it correctly and add some standard window behaviors, and fill it via a request for content. 
	// TODO: this isn't registered, it's parent global reference sort of is, and then its' child will be via Fill, but it isn't. That's ok, right? Just sort of wonder if this should be IN the parent global reference, as a kind of global reference, rather than an empty hierarchy, maybe.

	// Parameters: Alias, Calling_Element, Modal, Closeable
	// TODO: cross-reference parameters
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create_Window");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Generate parent reference
	var Window_Parameters = {};
	Window_Parameters["Kind"] = "URL";
	Window_Parameters["URL"] = Parameters["URL"];
	Window_Parameters["Parent_Namespace"] = "Jelly";
	
	// Set namespace...
	
	// Increment unique namespace index
	Jelly.References.Current_Global_Reference_Index++;
	
	// Generate base unique namespace
	var Window_Namespace = Jelly.Interface.Global_Controls_Element.id + Jelly.Namespace_Delimiter + Jelly.References.Current_Global_Reference_Index;
	
	// If alias is provided, append it to namespace
	if (Parameters['Alias'])
		Window_Namespace += Jelly.Namespace_Delimiter + Parameters["Alias"];
	
	// Store namespace
	Window_Parameters["Namespace"] = Window_Namespace;
	
	// TODO: Modal
	// TODO: Closeable
	
	// Generate a window control element
	var Window_Control_Element = Jelly.Interface.Generate_Window(Window_Namespace);

	// Append window control to global controls
	Jelly.Interface.Global_Controls_Element.appendChild(Window_Control_Element);
	
	// Register our new window
	var Window_Reference = Jelly.References.Register(Window_Parameters);

	// Add to modal windows list, and show a light-box
	Jelly.Interface.Modal_Windows.push(Window_Reference);
	Jelly.Interface.Show_Lightbox();
	
	// Store window control element
	Window_Reference["Control_Element"] = Window_Control_Element;
	
	// Set calling reference
	// TODO: check IF needed
	// TODO: Really? Set this to parent reference? Shouldn't this be set on the window parameters directly? 
	if (Parameters["Calling_Reference"])
		Window_Reference["Calling_Reference"] = Parameters["Calling_Reference"];
	else
		Window_Reference["Calling_Reference"] = Jelly.References.Get_Reference_For_Element(Parameters["Calling_Element"]);
	
	// Place the window element at the topmost modal window z-index
	// TODO: Any neater lightbox/window calculation, or is this interleaving idea the best one?
	Window_Control_Element.style.zIndex = Jelly.Interface.Window_Z_Index_Start + Jelly.Interface.Modal_Windows.length * 2 + 1;
	
	// Make window draggable
	// TODO: Can this happen in generate_window as well? 
// 	Jelly.jQuery(Window_Control_Element).draggable(
// 		{
// 			cursor: "move",
// 			handle: document.getElementById(Window_Namespace + "_Window_Handle")
// 		});	
	
	// Handle reference element...
	
	// Set reference element to container inside window element
	var Window_Element = document.getElementById(Window_Namespace);
	Window_Reference["Element"] = Window_Element;
		
	// Add some default spacing to the reference element.
	// TODO: This should be handled in the Generate_Window process, not here.
	Window_Element.innerHTML = "<div style=\"width: 75px; height: 25px;\"></div>";
	
	// Prevent window from being larger than the page
	// TODO: Should this be for Window_Element instead? 
// 	Window_Element.style.maxWidth = window.innerWidth - 25 * 2 + "px";
// 	Window_Element.style.maxHeight = window.innerHeight - 25 * 2 + "px";
// 	Window_Element.style.paddingRight = "0px";
	
	// Fade the window element in
	Jelly.jQuery(Window_Control_Element).addClass("Visible");
	Jelly.jQuery(Window_Control_Element).fadeIn();
	
	Window_Element.Jelly_Reference = Window_Reference;
	
	if (Debug)
	{
		Jelly.Debug.Group("Window Reference");
		Jelly.Debug.Log(Window_Reference);
	}
	
	// Request and fill content for the window.
	// TODO: Cross-Reference & make sure the right parameters are passed in for this
	Jelly.References.Refresh(Window_Reference);
	
	// Add dismiss handling
	// TODO: Is Parent_Element right? Why not Window_Element?
	Jelly.Handlers.Register_Handler({"Element": Window_Element, "Event": "Dismiss", "Code": function(Parameters)
		{
			if (Debug)
			{
				Jelly.Debug.Log(Window_Reference);
				Jelly.Debug.Log(Parameters);
			}

			// TODO: Cross-Reference & make sure the right parameters are passed in for this		
			Jelly.Interface.Close_Window(Window_Reference);
			
			// Focus top window.
			Jelly.Interface.Focus_Top_Window();
		}});
		
	// Add focus handling
	// TODO: Is Parent_Element? Why not Window_Element? for example, when is on_load triggered here? 
	// TODO: I think this has to be window element, no? 
	// TODO - maybe this is a function...
	Jelly.Handlers.Register_Handler({"Element": Window_Element, "Event": "Focus", "Code": function()
		{	
			// Get the window's root node
			// TODO: Why not window itself?
			var Window_Root_Node = Window_Control_Element.getElementsByTagName("span")[0].getElementsByTagName("span")[0];
			
			// Set handler focus to the window root node (for controlling keyboard events (enter/escape)) 
			Jelly.Handlers.Set_Default_Target(Window_Root_Node);
			
			// Set the browser focus on first input in window, or remove focus if no input is found.
			// TODO: This can probably happen within the function.
			if (!Jelly.Interface.Focus_First_Control(Window_Control_Element))
			{
				// If no input found, remove focus from all elements.
				var Links = document.getElementsByTagName("a");
				for (var i = 0; i < Links.length; i++)
					Links[i].blur();
			}
		}});
		
	// Add auto-focus on load.
	Jelly.Handlers.Register_Handler({"Element": Window_Element, "Event": "On_Load", "Code": function(Parameters)
		{
			// If this is the top most window, then call the focus handler.
			if (Jelly.Interface.Modal_Windows.length)
			{
				var Top_Window_Reference = Jelly.Interface.Modal_Windows[Jelly.Interface.Modal_Windows.length - 1];
				if (Parameters["Target"] == Top_Window_Reference.Element)
				{
					Jelly.Handlers.Call_Handler_For_Target({
								'Event': 'Focus', 
								'Target':Parameters['Target'],
								'Display_Target': Parameters['Display_Target'],
							}
						);
				}
			}
		}});
		
	// Lock modal state
	Jelly.Interface.Lock();
			
	if (Debug)
		Jelly.Debug.End_Group("Create_Window");
};

Jelly.Interface.Hide_Inspector = function(Parameters)
{
	// Remove inspect item id
	Jelly.Interface.Inspect_Item = null;
	
	// Close Inspector	
	Jelly.jQuery('#Jelly_Inspector').removeClass("Visible");
	
	// Widen content
	if (!Parameters || !Parameters["Maintain_Content_Size"])
		Jelly.jQuery('#Jelly_Content').removeClass("Inspector_Visible");
};

Jelly.Interface.Position_Cards = function()
{
	Jelly.jQuery(".Better_Manage_Card_List").masonry({
			itemSelector: "li > div",
			columnWidth: 300,
			gutter: 10,
			isFitWidth: true
		});

};

Jelly.Interface.Show_Browse_Bar = function(Parameters)
{
	if (!Jelly.jQuery('#Jelly_Browse_Bar').hasClass('Visible'))
	{
		// Open Sidebar
		Jelly.jQuery('#Jelly_Browse_Bar').addClass('Visible');
		Jelly.jQuery('#Jelly_Content').addClass('Browse_Bar_Visible');
		Jelly.jQuery('#Jelly_Sidebar').addClass('Browse_Bar_Visible');
	}
	Jelly.jQuery('#Jelly_Browse_Bar').html('');
	Jelly.References.Fill({Element: Jelly.jQuery('#Jelly_Browse_Bar').get(0), URL: '/Type/' + Parameters['Type_Alias'] + '/Browse_Bar', Create_Reference: true});
};


Jelly.Interface.Hide_Highlights = function()
{
	// TODO: This entire function
	var Debug = false && Jelly.Debug.Debug_Mode;	
	if (Debug)
	{
		Jelly.Debug.Group("Hide Highlights");
	}

	if (Jelly.Interface.Check_Event_Protection('Highlights'))
	{
		// Remove highlight
		if (Jelly.Interface.Highlight_Parts)
		{
			Jelly.Interface.Fade_Out_And_Remove(Jelly.Interface.Highlight_Parts["Left"]);
			Jelly.Interface.Fade_Out_And_Remove(Jelly.Interface.Highlight_Parts["Top"]);
			Jelly.Interface.Fade_Out_And_Remove(Jelly.Interface.Highlight_Parts["Right"]);
			Jelly.Interface.Fade_Out_And_Remove(Jelly.Interface.Highlight_Parts["Bottom"]);
			Jelly.Interface.Highlight_Parts = null;
		}

		// Clear highlight target
		Jelly.Interface.Highlight_Target_Namespace = null;
	}
	else if (Debug)
		Jelly.Debug.Log("Caught");
	
	if (Debug)
	{
		Jelly.Debug.End_Group("Hide Highlights");
	}

};

Jelly.Interface.Create_Location = function(Parameters)
{	
	// Create a named map, with a default tile server, and a default location
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create Map Marker");
		Jelly.Debug.Log(Parameters);
	}
	
	// Get Map
	var Map_Alias = Parameters["Map_Alias"];
	var Map_Object = Jelly.Interface.Maps[Map_Alias];
	var Map = Map_Object["Map"];
	
	//Create marker
	var Marker_Position = Parameters["Position"]
	var Marker_Content = Parameters["Content"];
	
	// Create marker icon parameters 
	// TODO - don't have to keep repeating this...
	var Icon_Parameters = {
		icon: "circle",
		prefix: 'fa', 	
		iconColor: 'white',
		markerColor: 'blue',
		extraClasses: 'fa-2x'
	  };	  

	// Use custom icon if specified.
	if (Parameters["Icon"])
		Icon_Parameters["icon"] = Parameters["Icon"];
		
	// Create marker icon
	var Marker_Icon = L.AwesomeMarkers.icon(Icon_Parameters);
	
	// Create marker
	var Marker = L.marker(Marker_Position, {icon: Marker_Icon}).addTo(Map)
		.bindPopup(Marker_Content,  {closeButton: false})
		.openPopup();
		
	// Store Marker
	if (!Map_Object["Markers"])
		Map_Object["Markers"] = [];		
	Map_Object["Markers"].push(Marker);

	
	// Show marker in view
	var Default_Map_Zoom = 13;
	if (Parameters["Show"])
		Map.setView(Marker_Position, Default_Map_Zoom);	
			
	if (Debug)
		Jelly.Debug.End_Group("Create Map Marker");
};

Jelly.Interface.Hide_Browse_Bar = function(Parameters)
{
	if (Jelly.jQuery('#Jelly_Browse_Bar').hasClass('Visible'))
	{
		// Open Sidebar
		Jelly.jQuery('#Jelly_Browse_Bar').removeClass('Visible');
		Jelly.jQuery('#Jelly_Content').removeClass('Browse_Bar_Visible');
		Jelly.jQuery('#Jelly_Sidebar').removeClass('Browse_Bar_Visible');
	}
};


Jelly.Interface.Generate_Window = function(Namespace)
{
	// TODO: Possibly add something built in, via something like 
/*

return [Format as "Javascript String"]
	<div>
		yay.
	</div>
[/Format];
*/
	// TODO: Remove below

	// Generate window HTML wrapper element with Namespace element inside
	var Window_Element = Jelly.Interface.Generate_Browser_Control({
		Browser_Control_ID: "Window",
		Replace: {"NAMESPACE": Namespace}
	});
	
	return Window_Element;
}


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

Jelly.Interface.Focus_Top_Window = function()
{
	// If the top most window exists, focus its first control.	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Focus_Top_Window");
	}
	
	if (Jelly.Interface.Modal_Windows.length > 0)
	{	
		// Get top window reference
		var Window_Reference = Jelly.Interface.Modal_Windows[Jelly.Interface.Modal_Windows.length - 1];
	
		// Get top window element
		var Window_Element = Window_Reference.Element;
	
		// Call focus handler for top window
		Jelly.Handlers.Call_Handler_For_Target({'Event': 'Focus', 'Target': Window_Element});
	}
	
	if (Debug)
		Jelly.Debug.End_Group("Focus_Top_Window");
};

Jelly.Interface.Clean_Date_Input = function(Namespace)
{
	// Cleans various date input into a standard  formatted date value
	// TODO - wonder if it should just take the input element
	var Debug = false && Jelly.Debug.Debug_Mode;	
	
	if (Debug)
	{
		Jelly.Debug.Group("Clean_Date_Input" + " (" + Namespace + ")");
	}

	// Get input
	// TODO - lol
	var Time_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Time_Input");
	var Date_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Date_Input");
	var Time_Input_Value;
	var Date_Input_Value;
	
	// Get input value
	if (Time_Input_Element)
		Time_Input_Value = Time_Input_Element.value;
	if (Date_Input_Element)
		 Date_Input_Value = Date_Input_Element.value;		
	
	// Leave blank input values unchanged
	if (Date_Input_Value)
	{
		// Convert input value into standardized value... 
	
		// Trim value
		var Date_Input_Value = Date_Input_Value.replace(/^\s+|\s+$/g,"");
	
		// Create date object
		var Value_Date = new Date();

		// Set date object value according by keywords or default conversation
		switch (Date_Input_Value.toLowerCase())
		{
			case "":
			case "today":
				// Keep existing date value.
				break;
			
			case "tomorrow":
				// Create tomorrow date value
				Value_Date.setTime(Value_Date.getTime() + 24 * 60 * 60 * 1000);
				break;
			
			case "yesterday":
				// Create yesterday date value
				Value_Date.setTime(Value_Date.getTime() - 24 * 60 * 60 * 1000);
				break;
			
			default:
				// Parse date from input value.
				var Value_Date_Parse_Result = Date.parse(Date_Input_Value);
			
				if (Debug)
				{
					Jelly.Debug.Log(Date_Input_Value);
					Jelly.Debug.Log(Value_Date_Parse_Result);
				}
			
				// Validate input value
				if (isNaN(Value_Date_Parse_Result))
				{
					// TODO - handle error more deliberately 			
				}
			
				// Create date value
				else
					Value_Date = new Date(Value_Date_Parse_Result);
				break;
		}
	
		// Update displayed value
		Date_Input_Element.value = (Value_Date.getMonth() + 1) + "/" + (Value_Date.getDate()) + "/" + (Value_Date.getFullYear());
	}
	
	// If the date of a date time is blank, clear the time too, if it exists.
	else if (Time_Input_Element)
		Time_Input_Element.value = "";
	
	if (Debug)
		Jelly.Debug.End_Group("Highlight Clean_Date_Input");
};

Jelly.Interface.Calculate_Bounds = function(Element)
{
	var Element_Bounds = {"Left": null, "Top": null, "Right": null, "Bottom": null, "Width": null, "Height": null};
	
	if (Jelly.jQuery(Element).outerWidth() == 0)
	{	
		// TODO - check left, top vs page or vs. container ("We'll Know" - Tristan Perich, 2014)
		var Element_Children = Jelly.jQuery(Element).children();
		var Element_Children_Left = null;
		Jelly.jQuery.each(Element_Children, function(Child_Index, Child_Element) {
			if (Element_Bounds["Left"] === null)
				Element_Bounds["Left"] = Jelly.jQuery(Child_Element).offset().left;
			else
				Element_Bounds["Left"] = Math.min(Element_Bounds["Left"], Jelly.jQuery(Child_Element).offset().left);
			
			if (Element_Bounds["Top"] === null)
				Element_Bounds["Top"] = Jelly.jQuery(Child_Element).offset().top;
			else
				Element_Bounds["Top"] = Math.min(Element_Bounds["Top"], Jelly.jQuery(Child_Element).offset().top);
			
			// TODO - make sure left and width are consistent in terms of padding & borders
			if (Element_Bounds["Right"] === null)
				Element_Bounds["Right"] = Jelly.jQuery(Child_Element).offset().left + Jelly.jQuery(Child_Element).outerWidth();
			else
				Element_Bounds["Right"] = Math.max(Element_Bounds["Right"], Jelly.jQuery(Child_Element).offset().left + Jelly.jQuery(Child_Element).outerWidth());
			
			// TODO - make sure top and height are consistent in terms of padding & borders
			if (Element_Bounds["Bottom"] === null)
				Element_Bounds["Bottom"] = Jelly.jQuery(Child_Element).offset().top + Jelly.jQuery(Child_Element).outerHeight();
			else
				Element_Bounds["Bottom"] = Math.max(Element_Bounds["Bottom"], Jelly.jQuery(Child_Element).offset().top + Jelly.jQuery(Child_Element).outerHeight());
			});
		
		Element_Bounds["Width"] = Element_Bounds["Right"] - Element_Bounds["Left"];
		Element_Bounds["Height"] = Element_Bounds["Bottom"] - Element_Bounds["Top"];
	}
	else
	{
		// TODO - Test below
		Element_Bounds["Left"] = Jelly.jQuery(Element).offset().left;
		Element_Bounds["Top"] = Jelly.jQuery(Element).offset().top;
		Element_Bounds["Width"] = Jelly.jQuery(Element).outerWidth();
		Element_Bounds["Height"] = Jelly.jQuery(Element).outerHeight();
		Element_Bounds["Right"] = Element_Bounds["Left"] + Element_Bounds["Width"];
		Element_Bounds["Bottom"] = Element_Bounds["Top"] + Element_Bounds["Height"];
	}
	
	return Element_Bounds;
};

Jelly.Interface.Create_Inspector = function(Parameters)
{
	// Open Inspector	
	Jelly.jQuery('#Jelly_Inspector').addClass("Visible");
		
	// Narrow content
	if (!Parameters["Maintain_Content_Size"])
		Jelly.jQuery('#Jelly_Content').addClass("Inspector_Visible");

	// Get Inspector element
	var Inspector_Element = document.getElementById("Jelly_Inspector");
	
	// Set up Inspector refresh parameters
	Inspector_Parameters = 
	{
		URL: Parameters["URL"],
		Element: Inspector_Element,
		Create_Reference:true		
	};
	
	// Clear Inspector
	Inspector_Element.innerHTML = "<div class=\"Inline_Loading\"></div>";

	// Refresh Inspector
	var Inspector_Reference = Jelly.References.Fill(Inspector_Parameters);
	
	// Add Inspector focus handler
	Jelly.Handlers.Register_Handler({"Element": Inspector_Element, "Event": "Focus", "Code": function()
		{				
			// Set the browser focus on first input in window, or remove focus if no input is found.
			if (!Jelly.Interface.Focus_First_Control(Inspector_Element))
			{
				// If no input found, remove focus from all elements.
				var Links = document.getElementsByTagName("a");
				for (var i = 0; i < Links.length; i++)
					Links[i].blur();
			}
		}});

	// Add Inspector load handler (once)
	Jelly.Handlers.Register_Handler({"Element": Inspector_Element, "Event": "On_Load", "Code": function(Parameters)
		{
			// Focus
			Jelly.Handlers.Call_Handler_For_Target({
						'Event': 'Focus', 
						'Target':Parameters['Target'],
					}
				);
			
			// Destroy focus handler
			delete Inspector_Reference["Handlers"]["On_Load"];
		}});
};

Jelly.Interface.Clean_Time_Input = function(Namespace)
{
	// Cleans various time input into a standard formatted time value
	// TODO - wonder if it should just take the input element
	var Debug = false && Jelly.Debug.Debug_Mode;	
	
	if (Debug)
	{
		Jelly.Debug.Group("Clean_Time_Input" + " (" + Namespace + ")");
	}
	
	// Get input
	// TODO - lol
	var Time_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Time_Input");
	var Date_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Date_Input");
	var Time_Input_Value;
	var Date_Input_Value;
	
	// Get input value
	if (Time_Input_Element)
		Time_Input_Value = Time_Input_Element.value;
	if (Date_Input_Element)
		 Date_Input_Value = Date_Input_Element.value;
	
	// If time and corresponding date (if exists) are blank, leave blank.
	if (Time_Input_Value || Date_Input_Value)
	{
		// Convert input value into standardized value... 

		// Trim input
		var Time_Input_Value = Time_Input_Value.replace(/^\s+|\s+$/g,"");
	
		// Create date object
		var Value_Date = new Date();
	
		// Hard-round seconds, milliseconds down.
		Value_Date.setSeconds(0);
		Value_Date.setMilliseconds(0);
	
		switch (Time_Input_Value.toLowerCase())
		{
			case "now":
				// Keep existing date value.
				break;
			
			case "noon":
				// Create noon date value
				Value_Date.setHours(12);
				Value_Date.setMinutes(0);
				break;
			
			case "midnight":
				// Create midnight date value		
				Value_Date.setHours(0);
				Value_Date.setMinutes(0);
				break;
			
			default:
				// Explicit ... 
			
				// Parse and trim am pm 
				// TODO - This is old parsing code...
				var AM_PM;
				if (Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase() == "am" || Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase() == "pm")
				{
					AM_PM = Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase();
					Time_Input_Value = Time_Input_Value.substring(0, Time_Input_Value.length - 2);
				}
				else if (Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() == "a" || Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() == "p")
				{
					AM_PM = Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() + "m";
					Time_Input_Value = Time_Input_Value.substring(0, Time_Input_Value.length - 1);
				}
				else
				{
					// Get am pm from current time.
					if (Value_Date.getHours() < 12)
						AM_PM = "am";
					else
						AM_PM = "pm";
				}
			
				// Get hour/minute
				var Input_Parts = Time_Input_Value.split(":");
				if (Input_Parts.length > 0)
				{
					Input_Hour_Value = parseInt(Input_Parts[0]);
				
					// TODO - too ballsy
					if (isNaN(Input_Hour_Value))
						break;
				}
				else
					var Input_Hour_Value = 0;

				if (Input_Parts.length > 1)
				{
					Input_Minute_Value = parseInt(Input_Parts[1]);

					// TODO - too ballsy
					if (isNaN(Input_Minute_Value))
						break;
				}
				else
					var Input_Minute_Value = 0;
					
				// Adjust hours for AM/PM
				if (AM_PM == "am" && Input_Hour_Value == 12)
					Input_Hour_Value = 0;
				else if (AM_PM == "pm" && Input_Hour_Value < 12)
					Input_Hour_Value += 12;
					
				// Setup date value
				Value_Date.setHours(Input_Hour_Value);
				Value_Date.setMinutes(Input_Minute_Value);
				break;

		}
	
		if (Debug)
			Jelly.Debug.Log(Value_Date);
	
		// Update display & form values...
		var Value_Hours, Value_AM_PM, Value_Minutes; 
	
		// Get display hours
		if (Value_Date.getHours() == 0)
			Display_Hours = "12";
		else if (Value_Date.getHours() > 12)
			Display_Hours = (Value_Date.getHours() - 12).toString();
		else
			Display_Hours = Value_Date.getHours().toString();
		
		// Get display minutes
		if (Value_Date.getMinutes().toString().length == 1)
			Display_Minutes = "0" +  Value_Date.getMinutes().toString();
		else
			Display_Minutes = Value_Date.getMinutes().toString();
		
		// Get display am pm
		if (Value_Date.getHours() < 12)
			Display_AM_PM = "am";
		else
			Display_AM_PM = "pm";
	
		// Update displayed value
		Time_Input_Element.value = Display_Hours + ":" + Display_Minutes + Display_AM_PM;		
	}
	
	if (Debug)
		Jelly.Debug.End_Group("Clean_Time_Input" + " (" + Namespace + ")");	
};

Jelly.Interface.Close_Top_Window = function()
{
	// If a top-most modal window exists, close it.
	
	// If there are modal windows open
	if (Jelly.Interface.Modal_Windows.length)
	{
		// Get the top-most window
		// TODO: Is there any neato last-item kind of thing these days (that doesn't pop it?) like peek or whatever. cute! 
		var Window_Item = Jelly.Interface.Modal_Windows[Jelly.Interface.Modal_Windows.length - 1];
		
		// Close the window.
		Jelly.Interface.Close_Window(Window_Item);
	}
};

Jelly.Interface.Show_Context_Menu = function(Parameters)
{
	// Display context menu for parameters.

	// Parameters: Event, Target_Element
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Show_Context_Menu");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Get calling event 
	var Calling_Event = Parameters["Event"];	
	
	if (Debug)
	{
		Jelly.Debug.Log("Calling_Event...");
		Jelly.Debug.Log(Calling_Event);
	}
	
	// Get target element
	var Target_Element = Parameters["Target_Element"];

	// Get target reference
	var Target_Reference = Jelly.References.Get_Reference_For_Element(Target_Element);

	// If there is no reference for this element, default to the Site Item reference.
	if (!Target_Reference)
		Target_Reference = Jelly.References.Reference_Lookups_By_Kind['Specific']['Site']['Item'];
		
	// Check if the target reference is a child of a menu reference, and halt in that case... 
	var Recursive_Base_Child_Menu_Reference = Jelly.Interface.Base_Menu_Reference;
	while (Recursive_Base_Child_Menu_Reference)
	{	
		var Recursive_Target_Parent_Reference = Target_Reference;
		while (Recursive_Target_Parent_Reference)
		{	
			// If the target reference is a child of any menu reference, return from function.
			// TODO - should the normal menu be possible? RIght now this just halts as is, but triggering default behavior is possible.
			if (Recursive_Target_Parent_Reference == Recursive_Base_Child_Menu_Reference)
				return;
				
			// Try the next parent reference.
			if (Recursive_Target_Parent_Reference.Parent_Reference)
				Recursive_Target_Parent_Reference = Recursive_Target_Parent_Reference.Parent_Reference;
		}
		
		// Try the next child of this menu reference.
		if (Recursive_Base_Child_Menu_Reference.Child_Menu)
			Recursive_Base_Child_Menu_Reference = Recursive_Base_Child_Menu_Reference.Child_Menu;
	}
		
	if (Debug)
	{
		Jelly.Debug.Log("Reference...");
		Jelly.Debug.Log(Target_Reference);
	}
	
	// Instantiate menu reference
	var Menu_Reference_Parameters = {};

	// Set parent of menu to target element.
	Menu_Reference_Parameters["Parent_Namespace"] = Target_Element.id;
	
	// Set menu alias
	Menu_Reference_Parameters["Alias"] = "Context_Menu";
	
	// Generate menu namespace
	Menu_Reference_Parameters["Namespace"] = Menu_Reference_Parameters["Parent_Namespace"] + Jelly.Namespace_Delimiter + Menu_Reference_Parameters["Alias"];
	
	// Generate URL... 
	
	// Generate URL base.
	var URL =  Target_Reference["Type_Alias"] + "/" + Target_Reference["ID"] + "/Context_Menu";
	
	// Generate URL Value String... 

	// Instantiate url values array
	var URL_Values = {};
	
	// Set property
	// TODO Finish
	var Recursive_Click_Target_Element = Calling_Event.target;
	while (Recursive_Click_Target_Element)
	{
		if (Debug)
		{
			Jelly.Debug.Log("Recursive_Click_Target_Element...");
			Jelly.Debug.Log(Recursive_Click_Target_Element);
		}
		if (Recursive_Click_Target_Element.getAttribute("data-property"))
		{
			URL_Values["Property_Alias"] = Recursive_Click_Target_Element.getAttribute("data-property");
			break;
		}
		Recursive_Click_Target_Element = Recursive_Click_Target_Element.parentNode;
	}
	
	// Set template...
	URL_Values["Template_ID"] = Target_Reference["Template_ID"];
	
	if (Target_Reference["Order"])
		URL_Values["Order"] = Reference["Order"];
	URL_Values["Show_Root_Extras"] = "True";
			
	// Serialize URL values
	var URL_Values_String = "";
	URL_Values_String = Jelly.Utilities.Serialize(
				{
					'Values': URL_Values,
//					'Is_URI': true,
					'Token': ','
				}
			);

	// Append serialized URL values to URL, with ":" marker.
	// TODO: This if always returns true
	if (URL_Values_String)
		URL += ":" + URL_Values_String;
		
	// Store URL
	Menu_Reference_Parameters["URL"] = URL;

	// Set post values
	
	// Instantiate post values array
	var Post_Values = {}
	
	// Set target namespace into post values
	Post_Values["Target_Namespace"] = Menu_Reference_Parameters["Parent_Namespace"];
	
	// Generate array of parent references and set it into post values... 
	
	// Instantiate parent references array
	var Parent_References = [];
	
	// Generate array of parent references
	var Recursive_Parent_Reference = Target_Reference;

	// TODO: when only one context parent is submitted, Jelly doesn't add a "count" to its POST variables
	do
	{
		switch (Recursive_Parent_Reference["Kind"])
		{
			case "Item":
				// For items, store ID, Type, and Namespace
				Parent_References.push({Item: Recursive_Parent_Reference["ID"], Item_Type: Recursive_Parent_Reference["Type_Alias"], Namespace: Recursive_Parent_Reference["Namespace"]});
				break;
			case "Iterator":
			case "Container":
				// TODO: Anything interesting here? 
				break;
			default:
				break;		
		}
	}
		while (Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"]);

	// Store parent references into post values
	// TODO: Naming
	Post_Values["Context_Parent"] = Parent_References;
	
	// Store post values into reference
	Menu_Reference_Parameters["Post_Values"] = Post_Values;
	
	// Position menu at mouse
	Menu_Reference_Parameters["Attach"] = "Mouse";	
	
	// Copy browser event data
	Menu_Reference_Parameters["Event"] = Calling_Event;
	
	// Create menu
	Jelly.Interface.Create_Menu(Menu_Reference_Parameters);	
		
	if (Debug)
		Jelly.Debug.End_Group("Show_Context_Menu");
	
	return false;
};

Jelly.Interface.Generate_Lightbox = function()
{
	// TODO: Possibly add something built in, via something like 
/*

return [Format as "Javascript String"]
	<div>
		yay.
	</div>
[/Format];
*/
	// TODO: Remove below
	return Jelly.Interface.Generate_Browser_Control({Browser_Control_ID: "Lightbox"});
}


Jelly.Interface.Switch_To_Backend = function(Parameters)
{
	if (!Jelly.jQuery("#Jelly_Content").hasClass("Backend"))
	{
		if (Parameters["Clear"])
			Jelly.jQuery("[data-kind*='Container']").empty();
		Jelly.jQuery("#Jelly_Content").addClass("Backend");
	}
}

Jelly.Interface.Show_All_Locations = function(Parameters)
{	
	// Map
	// Zoom map to fit all markers
	var Debug = true && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Show all locations");
		console.log(Parameters);
	}

	// Get map & map markers
	var Map_Alias = Parameters["Map_Alias"];
	var Map_Object = Jelly.Interface.Maps[Map_Alias];
	var Map = Map_Object["Map"];
	var Map_Markers = Map_Object["Markers"];
	
	// Show all markers
	if (Map_Markers && Map_Markers.length)
	{
		var Default_Map_Zoom = 13;
		var Map_Markers_Group = new L.featureGroup(Map_Markers);	
		Map.fitBounds(Map_Markers_Group.getBounds(), {maxZoom: Default_Map_Zoom, padding: [20, 20]});	
	}
	
	// Show default zoom if no markers
	else
		Jelly.Interface.Show_Default_Location({Map_Alias: Map_Alias});
			
	if (Debug)
		Jelly.Debug.End_Group("Show all locations");
};

// TODO:  maybe analyze/rewrite with some better jQuery understanding, etc.

Jelly.Interface.Position_Container = function(Parameters)
{

	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Position_Container");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	var Element = Parameters["Element"];
	var Restrict_Position = Parameters["Restrict_Position"];
	var Attach = Parameters["Attach"];
	var Attach_Element = Parameters["Attach_Element"];
	var Edge = Parameters["Edge"];
	
	var Grow_Y_Direction = "Down_Then_Up";
	var Grow_X_Direction = "Right_Then_Left";
	
	var Element_Position = {Left: Jelly.jQuery(Element).offset()["left"], Top: Jelly.jQuery(Element).offset()["top"]};
	
	// Get true element size
	var Element_Size = {};
	Element.style.width = "";
	Element.style.height = "";
	Element_Size["Width"] = Element.offsetWidth;
	Element_Size["Height"] = Element.offsetHeight;
	
	// Force fixed for now
	// TODO better way?
	var Position = "Fixed";
	
	// Set available bounds
	var Available_Bounds = {};
	switch (Position)
	{
		case "Fixed":
			// TODO: move this to better place
			Element.style.position = "fixed";
			
			// Set available bounds to window size with some padding
			var Window_Size = {Width: Jelly.jQuery(window).width(), Height: Jelly.jQuery(window).height()};

			Available_Bounds["Left"] = 16;
			Available_Bounds["Top"] = 16;
			Available_Bounds["Right"] = Window_Size["Width"] - 16;
			Available_Bounds["Bottom"] = Window_Size["Height"] - 16;
			
			break;
	}
	
	var Attach_Position = {};
	Attach_Position["Left"] = Element_Position["left"];
	Attach_Position["Top"] = Element_Position["top"];
	
	switch (Parameters["Attach"])
	{
		case "Mouse":
			if (Parameters["Update_Mouse_Position"])
			{
				Attach_Position["Left"] = Parameters["Event"].pageX || (Parameters["Event"].clientX);
				Attach_Position["Top"] = Parameters["Event"].pageY || (Parameters["Event"].clientY);
			}
			
			var Attach_Size = {Width: 0, Height: 0};
			
			break;
			
		case "Element":
			var Attach_Element = Parameters["Attach_Element"];
			
			var Attach_Position = {Left: Jelly.jQuery(Attach_Element).offset()["left"], Top: Jelly.jQuery(Attach_Element).offset()["top"]};
			var Attach_Size = {Width: Attach_Element.offsetWidth, Height: Attach_Element.offsetHeight};
			
			break;
	}
	
	// Offset scrollbar position for fixed positioning
	if (Position == "Fixed")
	{
		if (window.pageXOffset)
			var Window_Scroll_Position = {Left: window.pageXOffset, Top: window.pageYOffset};
		else
			var Window_Scroll_Position = {Left: document.body.scrollLeft, Top: document.body.scrollTop};
		Attach_Position["Left"] -= Window_Scroll_Position["Left"];
		Attach_Position["Top"] -= Window_Scroll_Position["Top"];
	}

	// Begin position at attach position
	switch (Parameters["Edge"])
	{
		case "Right":
			Element_Position["Left"] = Attach_Position["Left"] + Attach_Size["Width"];
			Element_Position["Top"] = Attach_Position["Top"];
			break;
		case "Bottom":
		default:
			Element_Position["Left"] = Attach_Position["Left"];
			Element_Position["Top"] = Attach_Position["Top"] + Attach_Size["Height"];
			break;
	}
	
	// If Restrict Position is true, contain available bound to the edge of the attach element.
	if (Restrict_Position)
	{
		switch(Edge)
		{
			case "Bottom":
				Available_Bounds["Top"] = Attach_Position["Top"] + Attach_Size["Height"];
				break;
			case "Top":
				Available_Bounds["Bottom"] = Attach_Position["Top"];
				break;
			case "Left":
				Available_Bounds["Right"] = Attach_Position["Left"];
				break;
			case "Right":
				Available_Bounds["Left"] = Attach_Position["Left"] + Attach_Size["Width"];
				break;
		}
	}
	
	// Fit within available bounds			
	if (Debug)
	{
		Jelly.Debug.Log("before");
		Jelly.Debug.Log(Element_Position);
		Jelly.Debug.Log(Element_Size);
		Jelly.Debug.Log(Available_Bounds);
	}

	if (Element_Position["Left"] + Element_Size["Width"] > Available_Bounds["Right"])
	{
		Element_Position["Left"] = Available_Bounds["Right"] - Element_Size["Width"];
		
		if (Element_Position["Left"] < Available_Bounds["Left"])
		{
			Element_Position["Left"] = Available_Bounds["Left"];
			Element_Size["Width"] = Available_Bounds["Right"] - Available_Bounds["Left"];
		}
	}
	if (Element_Position["Top"] + Element_Size["Height"] > Available_Bounds["Bottom"])
	{
		Element_Position["Top"] = Available_Bounds["Bottom"] - Element_Size["Height"];
		
		if (Element_Position["Top"] < Available_Bounds["Top"])
		{
			Element_Position["Top"] = Available_Bounds["Top"];
			Element_Size["Height"] = Available_Bounds["Bottom"] - Available_Bounds["Top"];
		}
	}

	if (Debug)
	{
		Jelly.Debug.Log("after");
		Jelly.Debug.Log(Element_Position);
		Jelly.Debug.Log(Element_Size);
		Jelly.Debug.Log(Available_Bounds);
	}
	
	// Set element's position
	Element.style.left = (Element_Position["Left"]).toString() + "px";
	Element.style.top = (Element_Position["Top"]).toString() + "px";
	
	// Set element's size
	if (Element.offsetWidth != Element_Size["Width"])
	{
		Element.style.width = Element_Size["Width"] + "px";
		Element.style.overflowX = "scroll";
	}
	if (Element.offsetHeight != Element_Size["Height"])
	{
		Element.style.height = Element_Size["Height"] + "px";
		Element.style.overflowY = "scroll";
	}
	
	if (Debug)
		Jelly.Debug.End_Group("Position_Container");
};

Jelly.Interface.Switch_To_Frontend = function(Parameters)
{
	if (Jelly.jQuery("#Jelly_Content").hasClass("Backend"))
	{
		if (Parameters["Clear"])
			Jelly.jQuery("[data-kind*='Container']").empty();
		Jelly.jQuery("#Jelly_Content").removeClass("Backend");
	}
}



Jelly.Interface.Refresh_Date_Value = function(Namespace)
{
	// Handle date input	
	var SQL_Formatted_Date_Value = "";
	
	// Get inputs
	// TODO - lol
	var Date_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Date_Input");

	if (Date_Input_Element)
	{
		var Date_Input_Value = Date_Input_Element.value;
		
		if (Date_Input_Value)
		{
			Date_Input_Value_Date = new Date(Date_Input_Value);

			// Convert date to sql formatted value...
			var Formatted_Year, Formatted_Month, Formatted_Date;
			
			// Year
			Formatted_Year = Date_Input_Value_Date.getFullYear();
	
			// Month
			if ((Date_Input_Value_Date.getMonth() + 1) < 10)
				Formatted_Month = "0" + (Date_Input_Value_Date.getMonth() + 1);
			else
				Formatted_Month = (Date_Input_Value_Date.getMonth() + 1);
	
			// Day
			if ((Date_Input_Value_Date.getDate()) < 10)
				Formatted_Date = "0" + (Date_Input_Value_Date.getDate());
			else
				Formatted_Date = (Date_Input_Value_Date.getDate());
		
			SQL_Formatted_Date_Value = Formatted_Year + "-" + Formatted_Month + "-" + Formatted_Date;
		}
	}
	
	// Handle time input	
	var SQL_Formatted_Time_Value = "";
	
	// Get inputs
	// TODO - lol
	var Time_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Time_Input");

	if (Time_Input_Element)
	{
		var Time_Input_Value = Time_Input_Element.value;
		
		if (Time_Input_Value)
		{
			// Explicit ... 
			Time_Input_Value_Date = new Date();
			
			// Parse and trim am pm 
			// TODO - old parsing logic.
			var AM_PM;
			if (Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase() == "am" || Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase() == "pm")
			{
				AM_PM = Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase();
				Time_Input_Value = Time_Input_Value.substring(0, Time_Input_Value.length - 2);
			}
			else if (Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() == "a" || Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() == "p")
			{
				AM_PM = Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() + "m";
				Time_Input_Value = Time_Input_Value.substring(0, Input_Value.length - 1);
			}
			
			// Get hour/minute
			var Input_Parts = Time_Input_Value.split(":");
			var Input_Hour_Value = parseInt(Input_Parts[0]);
			var Input_Minute_Value = parseInt(Input_Parts[1]);
					
			// Adjust hours for AM/PM
			if (AM_PM == "am" && Input_Hour_Value == 12)
				Input_Hour_Value = 0;
			else if (AM_PM == "pm" && Input_Hour_Value < 12)
				Input_Hour_Value += 12;
					
			// Setup date value
			Time_Input_Value_Date.setHours(Input_Hour_Value);
			Time_Input_Value_Date.setMinutes(Input_Minute_Value);
			
			// Convert time to sql formatted value...
			var Formatted_Hours, Formatted_Minutes;
		
			// Hours
			if (Time_Input_Value_Date.getHours().toString().length == 1)
				Formatted_Hours = "0" + Time_Input_Value_Date.getHours().toString();
			else
				Formatted_Hours = Time_Input_Value_Date.getHours().toString();
			
			// Minutes
			if (Time_Input_Value_Date.getMinutes().toString().length == 1)
				Formatted_Minutes = "0" +  Time_Input_Value_Date.getMinutes().toString();
			else
				Formatted_Minutes = Time_Input_Value_Date.getMinutes().toString();
			
			SQL_Formatted_Time_Value = Formatted_Hours + ":" + Formatted_Minutes + ":" + "00";
		}
	}
	
	// Join non-empty date and time values with a space
	var SQL_Formatted_Value = [SQL_Formatted_Date_Value, SQL_Formatted_Time_Value].filter(function (Value) {return Value;}).join(' ');
	
	// Store SQL value in input.
	var Form_Input_Element = document.getElementById(Namespace + '_Value');
	Form_Input_Element.value = SQL_Formatted_Value;
}



Jelly.Interface.Fade_Out_And_Remove = function(Element)
{
	// Fades out and removes an element
// 	Jelly.jQuery(Element).fadeOut("fast", function() {Jelly.jQuery(Element).remove();});
// TODO breaks dialogues if removes after fade out (since it's gone by the time they are completed)
	Jelly.jQuery(Element).removeClass("Visible");
	Jelly.jQuery(Element).fadeOut("fast", function() {});
};

Jelly.Interface.Highlight_Namespace = function(Namespace)
{
	var Debug = false && Jelly.Debug.Debug_Mode;	
	if (Debug)
	{
		Jelly.Debug.Group("Highlight_Namespace" + " (" + Namespace + ")");
	}
	
	// Protect against highlight bubbling.
	Jelly.Interface.Set_Event_Protection('Highlights');	
	
	// Return if target is already highlighted,
	if (Jelly.Interface.Highlight_Target_Namespace == Namespace)
	{
		if (Debug)
		{
			Jelly.Debug.Log("Already highlighted");
			Jelly.Debug.End_Group("Highlight Namespace");
			return;
		}
	}

	// otherwise store new highlight target.
	else
		Jelly.Interface.Highlight_Target_Namespace = Namespace;
			
	// Get target element
	var Target_Element = document.getElementById(Jelly.Interface.Highlight_Target_Namespace);
	
	// Get bounds of element
	var Element_Bounds;

	// TODO: verify that this is the best way to get the bounds
	if (Target_Element)
	{
		Element_Bounds = Jelly.Interface.Calculate_Bounds(Target_Element);
	}
	else
	{
		// TODO: This is for references without elements; better way?
		// TODO: actually it's for root-level elements like Site
		Element_Bounds = {Left: 10, Top: 10, Width: window.innerWidth - 20, Height: window.innerHeight - 20, Right: window.innerWidth - 10, Bottom: window.innerHeight - 10};
	}
	
	// TODO: make sure z-index is correct
	/*
	
	// Show highlight box
	Jelly.Interface.Highlight_Parts["Left"].style.left = Element_Bounds["Left"] + "px";
	Jelly.Interface.Highlight_Parts["Left"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Left"].style.height = Element_Bounds["Height"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.left = (Element_Bounds["Right"] - Highlight_Thickness) + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.height = Element_Bounds["Height"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.left = Element_Bounds["Left"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.width = Element_Bounds["Width"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.left = Element_Bounds["Left"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.top = (Element_Bounds["Bottom"] - Highlight_Thickness) + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.width = Element_Bounds["Width"] + "px";
	
	*/
	
	// Setup highlight parts
	if (!Jelly.Interface.Highlight_Parts)
	{
		Jelly.Interface.Highlight_Parts = {};
		
		// Create highlight part elements
		Jelly.Interface.Highlight_Parts["Left"] = document.createElement("div");
		Jelly.Interface.Highlight_Parts["Top"] = document.createElement("div");
		Jelly.Interface.Highlight_Parts["Right"] = document.createElement("div");
		Jelly.Interface.Highlight_Parts["Bottom"] = document.createElement("div");
		
		// Set highlight part class names
		Jelly.Interface.Highlight_Parts["Left"].className = "Jelly_Highlight_Edge Jelly_Highlight_Edge_Left";
		Jelly.Interface.Highlight_Parts["Top"].className = "Jelly_Highlight_Edge Jelly_Highlight_Edge_Top";
		Jelly.Interface.Highlight_Parts["Right"].className = "Jelly_Highlight_Edge Jelly_Highlight_Edge_Right";
		Jelly.Interface.Highlight_Parts["Bottom"].className = "Jelly_Highlight_Edge Jelly_Highlight_Edge_Bottom";
		
		// Add highlight parts to page
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Highlight_Parts["Left"]);
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Highlight_Parts["Top"]);
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Highlight_Parts["Right"]);
		Jelly.Interface.Global_Controls_Element.appendChild(Jelly.Interface.Highlight_Parts["Bottom"]);
		
		Jelly.Interface.Highlight_Parts["Left"].style.display = "none";
		Jelly.Interface.Highlight_Parts["Right"].style.display = "none";
		Jelly.Interface.Highlight_Parts["Top"].style.display = "none";
		Jelly.Interface.Highlight_Parts["Bottom"].style.display = "none";
		
		Jelly.jQuery(Jelly.Interface.Highlight_Parts["Left"]).fadeIn("fast");
		Jelly.jQuery(Jelly.Interface.Highlight_Parts["Top"]).fadeIn("fast");
		Jelly.jQuery(Jelly.Interface.Highlight_Parts["Right"]).fadeIn("fast");
		Jelly.jQuery(Jelly.Interface.Highlight_Parts["Bottom"]).fadeIn("fast");
	}
	
	// Dim area outside highlight
	var Document_Size = {Width: Jelly.jQuery(document).width(), Height: Jelly.jQuery(document).height()};
	Jelly.Interface.Highlight_Parts["Left"].style.left = "0px";
	Jelly.Interface.Highlight_Parts["Left"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Left"].style.width = Element_Bounds["Left"] + "px";
	Jelly.Interface.Highlight_Parts["Left"].style.height = Element_Bounds["Height"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.left = Element_Bounds["Right"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.top = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.width = Document_Size["Width"] - Element_Bounds["Right"] + "px";
	Jelly.Interface.Highlight_Parts["Right"].style.height = Element_Bounds["Height"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.left = "0px";
	Jelly.Interface.Highlight_Parts["Top"].style.top = "0px";
	Jelly.Interface.Highlight_Parts["Top"].style.width = Document_Size["Width"] + "px";
	Jelly.Interface.Highlight_Parts["Top"].style.height = Element_Bounds["Top"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.left = "0px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.top = Element_Bounds["Bottom"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.width = Document_Size["Width"] + "px";
	Jelly.Interface.Highlight_Parts["Bottom"].style.height = Document_Size["Height"] - Element_Bounds["Bottom"] + "px";
	
	if (Debug)
		Jelly.Debug.End_Group("Highlight Namespace");
};

Jelly.Interface.Focus_First_Control = function(Focus_Element)
{
	// Finds the first visible input in the element, and focuses on it and returns truel, or returns false if none exist
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Log("Focus_First_Control");
		Jelly.Debug.Log(Focus_Element);
	}

	// Find first visible input or textarea
	var Child_Node = Jelly.jQuery(Focus_Element).find("input:visible,textarea:visible").get(0);

	// If there is a first visible input or textarea, then focus on it
	if (Child_Node)
	{
		// TODO: What do these do, exactly?
		Child_Node.focus()
		Child_Node.select();
		return true;
	}
	// Or return false if there are no inputs to focus on.
	else
		return false;
};

// TODO: Not sure what this was going to do so I duplicated it and left this one alone.
// It doesn't seem to do anything, at all.
// TODO: to delete?
Jelly.Interface.Setup_Date_Selector = function(Parameters)
{
	var Value_Date = new Date();
	var Value_Month = Value_Date.getMonth();
	var Month_Names = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	var Value_Month_Name = Month_Names[Value_Month];
};

Jelly.Interface.Highlight_Menu_Item = function(Parameters)
{
	// Removes focus from previous selected menu item as needed, and sets the focus to the passed in menu item (by class, browser focus, and jelly handler focus)
	
	//	Parameters: Menu_Item
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Focus_Menu_Item");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	var Menu_Item = Parameters["Menu_Item"];
	
	// Unfocus last menu item, by removing focused class
	// TODO: Is this the right way to do it? Maybe. It seems to get any focused row of the registered selected item and unselect it. why these specific bounds?
	// TODO: Does jQuery protect against a variable that doesn't exist or is null, say in the initial jQuery?
	Jelly.jQuery(Jelly.Interface.Selected_Menu_Item).parents(".Jelly_Menu_Row.Jelly_Menu_Row_Focused").removeClass("Jelly_Menu_Row_Focused");
	
	// Register selected menu item as this menu item
	Jelly.Interface.Selected_Menu_Item = Menu_Item;
		
	// Select this new menu item by adding focused class
	Jelly.jQuery(Jelly.Interface.Selected_Menu_Item).parents(".Jelly_Menu_Row").addClass("Jelly_Menu_Row_Focused");
	
	// Set the default target to this menu item.
	Jelly.Handlers.Set_Default_Target(Menu_Item);
	
	if (Debug)
		Jelly.Debug.End_Group("");
};

Jelly.Interface.Show_Loading_Overlay = function(Reference)
{
	// TODO: This whole function is nonsense and needs to be done.
	return;
	
	var Debug = false && Jelly.Debug.Debug_Mode;

	if (Reference["Kind"] == "Container")			
		// TODO: find container content better
		var Target_Element = Jelly.jQuery(".Content")[0];
	else
		var Target_Element = Reference["Element"];
	
	// Find bounds of target
	var Target_Element_Bounds = Jelly.Interface.Calculate_Bounds(Target_Element);
	var Recursive_Bounds_Element = Target_Element;

	// If it's at the top left, don't display overlay
	// TODO - does this makes sense? older code says 'Hack to hide it if it's 0, 0'
	if(Target_Element_Bounds["Left"] == 0 && Target_Element_Bounds["Top"] == 0)
		return;
	
	// If height is 0, set it to a default
	if (Target_Element_Bounds["Height"] == 0)
	{
		Target_Element_Bounds["Height"] = 16;
		Target_Element_Bounds["Bottom"] += Target_Element_Bounds["Height"];
	}
	
	// Create loading overlay
	// TODO - this definitely doesn't have to happen here.., should be in "generate" whatever...
	var Loading_Overlay = document.createElement("div");
	Loading_Overlay.className = "Jelly_Loading_Overlay";
	Loading_Overlay.style.left = Target_Element_Bounds["Left"] + "px";
	Loading_Overlay.style.top = Target_Element_Bounds["Top"] + "px";
	Loading_Overlay.style.width = Target_Element_Bounds["Width"] + "px";
	Loading_Overlay.style.height = Target_Element_Bounds["Height"] + "px";	
	
	// TODO - messy, implement a better centering mechanism
	var Loading_Overlay_Table_Element = document.createElement("table");
	Loading_Overlay.appendChild(Loading_Overlay_Table_Element);

	var Loading_Overlay_Table_Row_Element = document.createElement("tr");
	Loading_Overlay_Table_Element.appendChild(Loading_Overlay_Table_Row_Element);

 	var Loading_Overlay_Table_Cell_Element = document.createElement("td");
	Loading_Overlay_Table_Row_Element.appendChild(Loading_Overlay_Table_Cell_Element);
	
	var Loading_Overlay_Indicator_Element = Jelly.Interface.Generate_Browser_Control({"Browser_Control_ID": "Loading"});
	Loading_Overlay_Indicator_Element.style.visibility = "visible";
	Loading_Overlay_Table_Cell_Element.appendChild(Loading_Overlay_Indicator_Element);
	
	// Set overlay to fixed or absolute depending on whether it's attached to a fixed or absolute element
	// TODO - 
	/*
	if (Target_Bounds["Fixed"])
		Loading_Overlay.style.position = "fixed";
	else
		Loading_Overlay.style.position = "absolute";
	*/
	
	// Append overlay to global controls
// (TODO: should it have its own namespace? probably not)
//			Loading_Overlay.style.display = "none";
// 	Jelly.Interface.Global_Controls_Element.appendChild(Loading_Overlay);

	Jelly.Interface.Global_Controls_Element.innerHTML += Loading_Overlay.outerHTML;
	Jelly.Debug.Log(Jelly.Interface.Global_Controls_Element);
	Jelly.Debug.Log(Loading_Overlay);
	
	// Store reference to overlay
	Reference["Loading_Overlay"] = Loading_Overlay;
//	Jelly.jQuery(Loading_Overlay).show("fast");
//			console.groupEnd();
};

Jelly.Interface.Set_Event_Protection = function(Event_Name)
{
	// TODO - rewrite comments
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Jelly.Interface.Event_Protection[Event_Name])
		window.clearTimeout(Jelly.Interface.Event_Protection[Event_Name]);
	
	Jelly.Interface.Event_Protection[Event_Name] = 
		Jelly.Interface.Timeout( 
				function() 
					{
						if (Debug)
							Jelly.Debug.Log("Clear Event Protection:'" + Event_Name+ "'"); 

						// Delete event protection from event protection list.
						delete Jelly.Interface.Event_Protection[Event_Name];
					}, 
					
				Jelly.Interface.Event_Protection_Period
			);
};

Jelly.Interface.Focus_First_Menu_Item = function(Parameters)
{
	// Focus on first anchor, or return false if no anchors
	// TOOD ...
	// TODO - actually return false if no anchors
	
	// Parameters: Menu_Element
	
	var Menu_Element = Parameters["Menu_Element"];
	
	Jelly.jQuery(Menu_Element).find("a:first").focus()
};

Jelly.Interface.Show_Default_Location = function(Parameters)
{	
	// Map
	// Zoom map to fit all markers
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Show Default Location");
		console.log(Parameters);
	}

	// Defaults
	// THIS IS NEW INC
	var Default_Location = 	[40.72213, -73.99277];
	var Default_Map_Zoom = 13;

	// Get map & map markers
	var Map_Alias = Parameters["Map_Alias"];
	var Map_Object = Jelly.Interface.Maps[Map_Alias];
	var Map = Map_Object["Map"];
			
	// Set view
	Map.setView(Default_Location, Default_Map_Zoom);
			
	if (Debug)
		Jelly.Debug.End_Group("Show Default Location");
};

Jelly.Interface.Show_Loading_Indicator = function(Parameters)
{
	// Display the loading indicator of the action reference passed in.
	// Parameters: Calling_Element	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Show Loading Indicator");
		Jelly.Debug.Log(Parameters);
	}

	// Ensure that this calling reference is an action with a loading element.
	var Loading_Element = Jelly.Interface.Generate_Loading_Indicator(Parameters);
	
	// If there is a loading element, make it visible.
	if (Loading_Element)
	{
		Loading_Element.style.visibility = "visible";
	}
};

Jelly.Interface.Hide_Loading_Indicator = function(Parameters)
{
	// Hide the loading indicator of the action reference passed in.
	// Parameters: Calling_Reference
 		
	// Get the reference for the calling element passed in
	var Action_Reference = Parameters["Calling_Reference"];
	
	// Verify that this is an action reference in order to continue.
	if (!Action_Reference || !Action_Reference["Type_Alias"] || ["Action", "Type_Action"].indexOf(Action_Reference["Type_Alias"]) < 0)
	{
// 		Jelly.Debug.Display_Error('Trying to hide loading indicator for action reference, but no action reference provided');
		return;
	}

	// If the calling reference exists and it has a loading element, hide the loading element.
	if (Action_Reference["Loading_Element"])
	{
		var Loading_Indicator = Action_Reference["Loading_Element"];
		Loading_Indicator.style.visibility = "hidden";
	}
};

Jelly.Interface.Generate_Date_Selector = function(Namespace, Parameters)
{
	// TODO: Currently Disabled
	return;
	
	// TODO: This function
	// Get current calendar date
	if (Parameters && Parameters["Value"])
		Calendar_Date = Parameters["Value"];
	else
		Calendar_Date = new Date();
		
	// Get current calendar month
	Calendar_Month = Calendar_Date.getMonth();
	var Month_Names = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	var Calendar_Month_Name = Month_Names[Calendar_Month];
	
	// Get current calendar year.
	Calendar_Year = Calendar_Date.getFullYear();

	// Clear calendar input
	var Calendar_Input_Element_Wrapper = document.getElementById(Namespace + '_Date_Menu');
	Calendar_Input_Element_Wrapper.innerHTML = "";
	
	// Generate calendar header
	var Calendar_Input_Element = Jelly.Interface.Generate_Browser_Control({Browser_Control_ID: "Calendar_Input", Replace: {"CALENDAR_MONTH_NAME": Calendar_Month_Name, "CALENDAR_MONTH": Calendar_Month, "CALENDAR_YEAR": Calendar_Year, "INPUT_NAMESPACE": Namespace}});
	
	// Generate calendar dates
	var Calendar_Input_Date_Element_Wrapper = document.createElement("div");
	Calendar_Input_Date_Element_Wrapper.className = "Jelly_Calendar_Items";
	
	// Generate metrics to help count calendar dates
	Calendar_Month_First_Date = new Date(Calendar_Year, Calendar_Month, 1, 0, 0, 0);
	Calendar_Month_Last_Date = new Date(Calendar_Year, Calendar_Month + 1, 0,0,0,0);
	Previous_Month_Last_Date = new Date(Calendar_Year, Calendar_Month, 0, 0, 0, 0);
	Calendar_Month_First_Day = Calendar_Month_First_Date.getDay();
	Calendar_Month_Last_Day = Calendar_Month_Last_Date.getDay();
	Previous_Month_Last_Date_Index = Previous_Month_Last_Date.getDate();
	Previous_Month_First_Date_Index = Previous_Month_Last_Date_Index + 1 - Calendar_Month_First_Day;
	Calendar_Month_Last_Date_Index = Calendar_Month_Last_Date.getDate();
	Next_Month_Last_Date_Index = 6 - Calendar_Month_Last_Day;
	
	// Generate padding dates before calendar month
	for (var Date_Index = Previous_Month_First_Date_Index; Date_Index <= Previous_Month_Last_Date_Index; Date_Index++)
	{
		var Calendar_Input_Date_Element = document.createElement("div");
		Calendar_Input_Date_Element.className = "Jelly_Calendar_Items_Previous_Item";
		Calendar_Input_Date_Element.innerHTML = Date_Index;
		Calendar_Input_Date_Element_Wrapper.appendChild(Calendar_Input_Date_Element);
	}
	
	// Generate calendar input dates 
	for (var Date_Index = 1; Date_Index <= Calendar_Month_Last_Date_Index; Date_Index++)
	{
		Date_Value = new Date(Calendar_Year, Calendar_Month, Date_Index);
		Calendar_Input_Date_Element = Jelly.Interface.Generate_Browser_Control({Browser_Control_ID: "Calendar_Input_Date", Replace: {"DATE_VALUE": Date_Value, "DATE_INDEX": Date_Index, "INPUT_NAMESPACE": Namespace}});
		Calendar_Input_Date_Element_Wrapper.appendChild(Calendar_Input_Date_Element);
	}
	
	// Generate padding dates after calendar month
	for (var Date_Index = 1; Date_Index <= Next_Month_Last_Date_Index; Date_Index++)
	{
		var Calendar_Input_Date_Element = document.createElement("div");
		Calendar_Input_Date_Element.className = "Jelly_Calendar_Items_Next_Item";
		Calendar_Input_Date_Element.innerHTML = Date_Index;
		Calendar_Input_Date_Element_Wrapper.appendChild(Calendar_Input_Date_Element);
	}
	Calendar_Input_Date_Element_Wrapper.innerHTML += "<br class=\"Jelly_Clear\" />";
	Calendar_Input_Element.appendChild(Calendar_Input_Date_Element_Wrapper);

	
	// Add calendar to page.
	Calendar_Input_Element_Wrapper.appendChild(Calendar_Input_Element);
	//Jelly.Debug.Log(Calendar_Input_Element_Wrapper);
};

Jelly.Interface.Check_Event_Protection = function(Event_Name)
{
	// TODO - rewrite comments
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	return (!Jelly.Interface.Event_Protection.hasOwnProperty(Event_Name));
};

Jelly.Interface.Bubble_Event_Protection = function(Event_Name)
{
	// Creates a named variable that erases itself after a set period, which helps with cross-browser event propogation control.	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	// If the named event bubble is being blocked, return false.
	if (Jelly.Interface.Event_Protection[Event_Name])
		return false;

	// Otherwise, begin blocking event bubble, and stop blocking it after a timeout period, and return true.
	// TODO - manage timeout and delete it
	else
	{	
		// Store event name in event bubble list.
		// Stop blocking event bubble after a timeout period.
		Jelly.Interface.Event_Protection[Event_Name] = 		
			Jelly.Interface.Timeout(
					function()
						{	
							if (Debug)
								Jelly.Debug.Log("Clear Event Protection:'" + Event_Name+ "'"); 
	
							// Delete event name from event protection list.
							delete Jelly.Interface.Event_Protection[Event_Name];
						}, 
					Jelly.Interface.Event_Protection_Period
				);
		return true;
	}
	
	if (Debug)
		Jelly.Debug.End_Group("");
};

Jelly.Interface.Generate_Browser_Control = function(Parameters)
{	
	// Return a DOM tree generated from a named cached HTML string, replacing placeholder text in the string.
	// TODO: End this for some straightforward parameters based "Generate_" functions
	// Parameters: Browser_Control_ID, Replace
	
	// Get Browser Control HTML by ID
	var Browser_Control_HTML = Browser_Controls[Parameters["Browser_Control_ID"]];
	
	// If they have been provided, replace placeholder keys in HTML with values
	if (Parameters["Replace"])
	{
		// For each placeholder key 
		for (Placeholder_Key in Parameters["Replace"])
		{
			if (Parameters["Replace"].hasOwnProperty(Placeholder_Key))
			{
				// Get value
				var Value = Parameters["Replace"][Placeholder_Key];
				
				// Replace every placeholder key in the HTML with the value.
				Browser_Control_HTML = Browser_Control_HTML.replace(new RegExp(Placeholder_Key, "g"), Value);				
			}
		}
	}
			
	// Convert the HTML string to a DOM node
	// TODO - investigate why it needs to be trimmed... shouldn't.
	var Browser_Control_Node = jQuery.parseHTML(Browser_Control_HTML.trim());
	
	// Return browser
	return Browser_Control_Node[0];

// TODO: test, delete the below of the above works.
// 	var New_Element = document.createElement("div");
// 	New_Element.innerHTML = HTML_Text;
// 	return New_Element.childNodes[0];
	
};

// TODO: not sure this is in use, but it seems to have been for a searchable dropdown.
Jelly.Interface.Refresh_Browse_Menu_Table = function(Parameters)
{
	// Parameters: Namespace, Type
	var Namespace = Parameters["Namespace"];
	var Type = Parameters["Type"];
	
	var Text_Box_Value = document.getElementById(Namespace + "_Text_Box").value;
	var Browse_Table_Wrapper_Element = document.getElementById(Namespace + "_Table");
	
	Jelly.References.Fill(
		{
			Element: Browse_Table_Wrapper_Element, 
			URL: "/Type/" + Type + "/Browse_Menu_Table:Show_Items=True,Search=" + Text_Box_Value,
			Create_Reference: true
		}
	);
};

Jelly.Interface.Generate_Loading_Indicator = function(Parameters)
{
	// Generate a loading element as needed for this action reference, either after the first execute link, or at the end of the element, and registers the element.
	// Parameters: Action_Reference
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Generate Loading Indicator");
		Jelly.Debug.Log(Parameters);
	}

	// Get the reference for the calling element passed in
	var Action_Reference = Parameters["Calling_Reference"];
	
	// Verify that this is an action reference in order to continue.
	if (!Action_Reference || !Action_Reference["Type_Alias"] || ["Action", "Type_Action"].indexOf(Action_Reference["Type_Alias"]) < 0)
	{
// 		Jelly.Debug.Display_Error('Trying to show loading indicator for action reference, but no action reference provided');
		return;
	}

	// Return loading element if it already exists
	if (Action_Reference["Loading_Element"])
		return Action_Reference["Loading_Element"];
		
	// Otherwise, generate a new one, register it, and return it.
	else
	{	
		// Verify that the Action has a DOM element in order to continue making a loading indicator.
		if (!Action_Reference["Element"])
			return;
			
		// Get the action DOM element
		var Action_Element = Action_Reference["Element"];
		
		// Get the action namespace
		var Action_Namespace = Action_Reference["Namespace"];
		
		// Create a loading indicator
		var Loading_Element = Jelly.Interface.Generate_Browser_Control(
				{
					Browser_Control_ID: "Loading", 
					Replace: {"NAMESPACE": Action_Namespace}
				}
			);
	
		// If an execute link exists, insert the loading indicator after the first execute link 
		var Execute_Link_Elements = Action_Element.getElementsByClassName('Jelly_Action_Execute');	
		if (Execute_Link_Elements	[0])
		{	
			var First_Execute_Link_Element = Execute_Link_Elements[0];
			Jelly.jQuery(First_Execute_Link_Element).after(Loading_Element);
		}
		
		// If no execute link exists, insert the loading indicator at the end of the action element.
		else
		{
			Action_Element.appendChild(Loading_Element);
		}
		
		// Register the loading element to this action reference		
		Jelly.Actions.Register_Action_Loading_Element(
				{
					Namespace: Action_Namespace, 
					Loading_Element: Loading_Element
				}
			);
		
		// Return loading element
		return Loading_Element;
	}
}


Jelly.Interface.Catch_Webkit_Context_Click_Bug = function()
{	
	// Compatibility - Chrome & Safari bug where context click event is followed by an undesired click event
	// TODO - Still needed? Probably not. Test.
	
	// Place a fixed, viewport-sized intercept element at the top layer.
	var Cancel_Next_Click_Element = document.createElement("div");
	Cancel_Next_Click_Element.id = "Cancel_Next_Click";
	Cancel_Next_Click_Element.style.position = "fixed";
	Cancel_Next_Click_Element.style.left = "0px";
	Cancel_Next_Click_Element.style.top = "0px";
	Cancel_Next_Click_Element.style.zIndex = "2000";
//			Cancel_Next_Click_Element.style.background = "red";
	Cancel_Next_Click_Element.style.width = (window.innerWidth || document.documentElement.clientWidth) + "px";
	Cancel_Next_Click_Element.style.height =(window.innerHeight || document.documentElement.clientHeight) + "px";
	document.body.appendChild(Cancel_Next_Click_Element);
		
	// Attach a mouse-up listener to the document which removes the intercept element, and then removes itself.
	var Cancel_Next_Click_Element_Listener = function() {
			document.body.removeChild(Cancel_Next_Click_Element);
			document.removeEventListener('mouseup', Cancel_Next_Click_Element_Listener, false);
		}				
	document.addEventListener('mouseup', Cancel_Next_Click_Element_Listener, false);
};

Jelly.Interface.Create_Global_Controls_Element = function ()
{
	// Create globals element, which will contain global references.
	
	// TODO: Should this be registered?
	Jelly.Interface.Global_Controls_Element = document.createElement("span");
	Jelly.Interface.Global_Controls_Element.id = "Jelly_Globals";
	// TODO: I felt that was too specific, below.
//	Jelly.Interface.Global_Controls_Element.className = "Jelly_Controls";
	Jelly.Body_Element.appendChild(Jelly.Interface.Global_Controls_Element);

//		Jelly.Register({
//			ID: "Path",
//			Namespace: Jelly.Body_Element.id
//		});
//		Jelly.Debug.Log(Jelly.References.References_By_Namespace);
	/*
	Jelly.Register({
		Kind: "Element",
		ID: Jelly.Interface.Global_Controls_Element.id,
		Namespace: Jelly.Interface.Global_Controls_Element.id,
		Force: true
	});
	*/
};

// Set reset container script
Jelly.Interface.Set_Refresh_Container_Listener = function(Script)
{
	Jelly.Interface.Refresh_Container_Listener = Script;
};

// Calls reset container script
Jelly.Interface.Call_Refresh_Container_Listener = function(Parameters)
{
	if (Jelly.Interface.Refresh_Container_Listener)
		Jelly.Interface.Refresh_Container_Listener.call();
};

Jelly.Interface.Insert_Return_With_Indented_Text = function(Target)
{
	// Auto-indent next line of text in the target element.	
	// TODO: This function
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Insert Return With Indented Text");
		Jelly.Debug.Log("Target...");
		Jelly.Debug.Log(Target);
	}

	// Get total text.
	var Text = Target.value;
	
	// TODO: Browser compatibility check, jQuery version.? 
	var Selection_Start = Target.selectionStart;
	
	// If first character is a new line, move before it
	if (Text.charAt(Selection_Start) == "\n")
		Selection_Start--;
	
	// Search backwards to find new line or beginning of text
	while (Selection_Start != -1 && Text.charAt(Selection_Start) != "\n")
		Selection_Start--;
	
	// Advance 1 character (past newline, or to the beginning of the text)
	Selection_Start++;
	
	// Search forwards to find number of tabs
	var Tab_Position = Selection_Start;
	while (Tab_Position < Text.length && Text.charAt(Tab_Position) == "\t")
		Tab_Position++;
	
	// Get the string of tab characters
	// TODO: this is cute, but I would probably rebuild the tab characters and count the tabs, rather then just copy the indentation string. 
	var Tabs = Text.substr(Selection_Start, Tab_Position - Selection_Start);
	
	// Replace or insert newline & and tabs at selection
	var Selection_Start = Target.selectionStart;
	var Selection_End = Target.selectionEnd;	
	Target.value = Text.substr(0, Selection_Start) + "\n" + Tabs + Text.substr(Selection_End);
	
	// Set the textarea cursor to the character following the new line.
	Target.selectionStart = Selection_Start + ("\n" + Tabs).length;
	Target.selectionEnd = Target.selectionStart;
	
	if (Debug)
		Jelly.Debug.End_Group("");
};

Jelly.Utilities = 
{
};

;

// TODO Some of this needs major debugging.
Jelly.Utilities.Serialize = function(Parameters)
{
	// Returns string of the key and values of Values array separate by Token
	// Parameters: Values, Token, Is_URI, Namespace
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Serialize");
		Jelly.Debug.Log(Parameters);
	}

	// Instantiate array of value strings
	var Serialized_Strings = [];
	
	// Generate new namespace as blank, or Namespace_
	var New_Namespace = "";
	if (Parameters["Namespace"])
		New_Namespace = Parameters["Namespace"] + "_";
	
	// For each value in array, serialize to key=value, and add to serialized strings array
	for (Value_Key in Parameters["Values"])
	{
		if (Parameters["Values"].hasOwnProperty(Value_Key))
		{			
			var Value = Parameters["Values"][Value_Key];
			switch (typeof(Value))
			{
				case "boolean":
				case "number":
				case "string":				
					// Encode URI if specified
					// TODO: should Value_Key be encodeURIComponented??
//					if (Parameters["IS_URI"])
					Value = encodeURIComponent(Value);
						
					// Serialize to key=value and store in serialized strings array
					Serialized_Strings.push(New_Namespace + Value_Key + "=" + Value);
					break;

				// TODO: This seems like an old thing
				case "function":
					Jelly.Debug.Log("AJAX Request: cannot submit functions as post variables");
					break;
				
				case "object":
					// If array, store namespace type as array, store value count, and serialize values with namespace
					if (Value.hasOwnProperty('length'))
					{
						// Add type for this value 
						Serialized_Strings.push(New_Namespace + Value_Key + "_" + "Type" + "="  + "Array");
						
						// Add count for this value
						Serialized_Strings.push(New_Namespace + Value_Key + "_" + "Count" + "="  + Value.length);
						
						// Add serialized values for this value
						Serialized_Strings.push(Jelly.Utilities.Serialize(
								{
									'Values': Value,
									'Namespace': New_Namespace + Value_Key,
//									'Is_URI': Parameters['Is_URI'],
								}
							));							
					}
					else
					{
						// If an item, store namespace type as item,  and serialize values with namespace
					
						// Add type for this value 
						Serialized_Strings.push(New_Namespace + Value_Key + "_" + "Type" + "="  + "Item");

						// Add serialized values for this value
						Serialized_Strings.push(Jelly.Utilities.Serialize(
								{
									'Values': Value,
									'Namespace': New_Namespace + Value_Key,
//									'Is_URI': Parameters['Is_URI'],
								}
							));
						}
					break;
				default:
					// Handle undefined case
					if (Value == undefined)
					{
						// Serialize to "key=" and store in serialized strings array
						Serialized_Strings.push(New_Namespace + Value_Key + "=");
						break;					
					}					
					// Handle unknown case
					else
					{
						// Throw error.
						Jelly.Debug.Display_Error("Unsupported Post Value Type: " + typeof(Value));
					}
					break;
			}
		}
	}
	
	// Join each serialized string by join_token
	if (Parameters["Token"])
		Token = Parameters["Token"];
	else
		Token = "&";

	var Value_String = Serialized_Strings.join(Token);
	
	if (Debug)
	{
		Jelly.Debug.Log(Value_String);
		Jelly.Debug.End_Group("Serialize");
	}
	
	// Return 
	return Value_String;
}


Jelly.Utilities.Require_SSL = function()
{
	// Checks for https protocol, redirect to https if not alreay
	// TODO: lol obsolete
	
	// If not https protocol
	if (document.location.href.substr(0, 5) != "https")
	{
		// Assumes http protocol, replaces https in place of the the first four letters in the document location
		// TODO: i guess this works..., but it's not the right code.
		document.location.href = "https" + document.location.href.substr(4);
	}
};

Jelly.Utilities.Reload_Page = function()
{
	// Reloads page.
	
//			alert("Reload");
	// TODO: parameter forceGet = true? false (default) loads from cache.
	document.location.reload();
// 	throw 'FAKE RELOAD PAGE';
};

Jelly.Utilities.Clean_Scripts = function(HTML_Text)
{	
	// Cleans scripts from HTML text.
	// TODO - totally untested
	
	// Create a tag with the passed in text as the inner HTML
	var Content = jQuery(HTML_Text.bold());
	
	// Find and remove all script tags
   Content.find('script').remove();
   
   // Return inner HTML
   return Content.html();
};

Jelly.Utilities.Execute_Scripts = function(HTML_Text)
{	
	// Parses response for scripts, and executes them.	
	
	// TODO:  tried jQuery implementation..., but utlimately don't trust it.
	/*
	// Turn into jQuery object
	var DOM = $(HTML_Text);
	DOM.filter('script').each( function() 
			{
				var Script = (this.text || this.textContent || this.innerHTML || '';
				eval(Script);
			}
		);
	*/

	// TODO: Isn't documented, but seems to work.
	var Open_Tag = "<script";
	var Close_Tag = "</script";
	
	var Script_Count = 0;
	var Script_Position = -1;
	
	while (1)
	{
		var Open_Position = HTML_Text.indexOf(Open_Tag, Script_Position + 1);
		var Close_Position = HTML_Text.indexOf(Close_Tag, Script_Position + 1);

		if (Open_Position != -1 && (Close_Position == -1 || Open_Position < Close_Position))
		{
			Script_Count++;
			Script_Position = Open_Position;
			
			if (Script_Count == 1)
			{
				var Script_Start = Open_Position;
			}
		}
		else if (Close_Position != -1 && (Open_Position == -1 || Close_Position < Open_Position))
		{
			Script_Count--;
			Script_Position = Close_Position;
			
			if (Script_Count == 0)
			{				
				var Script = HTML_Text.substring(HTML_Text.indexOf(">", Script_Start) + 1, Close_Position);
				try 
				{
					eval(Script);
				} 
				catch (e)
				{
					Jelly.Debug.Log ("Error in Script.");
					Jelly.Debug.Log (e);
					Jelly.Debug.Log(e.stack);
					Jelly.Debug.Log (Script);
				}
			}
		}
		else
		{
			break;
		}
	}

	/*
	var Script_Div = document.createElement("div");
	Script_Div.innerHTML = HTML_Text;
	var Script_Tags = Script_Div.getElementsByTagName("script");
	for (Script_Tag_Index in Script_Tags)
	{
		var Script = Script_Tags[Script_Tag_Index].innerHTML;
		if (Script)
		{
			Jelly.Debug.Group("Script");
			Jelly.Debug.Log(Script);
			eval(Script);
			Jelly.Debug.End_Group("");
		}
	}
	*/
	
//			Jelly.Debug.End_Group("");
};

Jelly.Utilities.Insert_At_Cursor = function(Parameters)
{
	// TODO: Should be in interface
	// Inserts Value in Element
	// Parameters: Element, Value, ID
	// TODO: Rewrite this function
	if (Parameters["Element"])
		var Text_Area_Element = Parameters["Element"];
	else
		var Text_Area_Element = document.getElementById(Parameters["ID"]);
//			Jelly.Debug.Log(Parameters["ID"]);
//			Jelly.Debug.Log(Text_Area_Element);
	
	if (!Text_Area_Element)
		return;
	
	var Scroll_Position = Text_Area_Element.scrollTop; 
	var Cursor_Position = 0; 

	// Determine browser.
	var Is_Internet_Explorer;
	if (Text_Area_Element.selectionStart || Text_Area_Element.selectionStart == "0")
		Is_Internet_Explorer = false;
	else
		Is_Internet_Explorer = false;

	// Get cursor position.
	if (Is_Internet_Explorer)
	{
		Text_Area_Element.focus();
		var Text_Area_Range = document.selection.createRange();
		Text_Area_Range.moveStart ('character', Text_Area_Element.value.length); 
		Cursor_Position = range.text.length; 
	}
	else
		Cursor_Position = Text_Area_Element.selectionStart; 

	// Insert text at cursor.
	Text_Area_Element.value = Text_Area_Element.value.substr(0, Cursor_Position) + Parameters["Value"] + Text_Area_Element.value.substring(Cursor_Position, Text_Area_Element.value.length);				

	// Advance cursor.
	Cursor_Position += Parameters["Value"].length;

	if (Is_Internet_Explorer)
	{
		Text_Area_Element.focus(); 	
		var Text_Area_Range = document.selection.createRange();
		range.moveStart ('character', -Text_Area_Element.value.length); 
		range.moveStart ('character', Cursor_Position); 
		range.moveEnd ('character', 0);
		range.select();
	}
	else
	{
		Text_Area_Element.selectionStart = Cursor_Position;
		Text_Area_Element.selectionEnd = Cursor_Position;
		Text_Area_Element.focus();
	}

	Text_Area_Element.scrollTop = Scroll_Position; 
};

Jelly.References = 
{
	// List of simple types.
	// TODO - fix order here. 
	Simple_Types:  Simple_Types,
	
	// List of all references by id
	// TODO - Secondary path dependencies is a temporary placeholder - it gets triggered on load of the primary path dependency, that's all. hm.
	Reference_Lookups_By_Kind: {
			"Item": {}, 
			"Iterator": {},
			"URL": [],
			"Specific": {
					"Container": {
							"Item": null,
							"Dependencies": []
						},
					"Site": {
							"Item": null,
							"Dependencies": []
						},
					"Path": {
							"Dependencies": {
									"Primary": null,
									"Secondary": []
								}
						}
				}
		},

	// List of all references by namespace
	References_By_Namespace: {},
	
	// Queue of references to be refreshed.
	References_To_Refresh: {},
	
	// When true, prevents registrations via the Register call.
	// TODO: Doesn't seem to do anything....
	No_Register: false,
	
	// Incrementing index of global references, updated by Create_Global_Reference.
	Current_Global_Reference_Index: 0,
	
	// Container progress bar
	Container_Progress_Bar: null,
	Container_Progress_Bar_Interval: null
};

;

Jelly.References.Fill = function(Parameters)
{
	// Registers a reference that ties an element to a URL, inserts the reference into reference tree, refreshes the reference, and returns ther reference
	// Parameters: Element, URL, Post_Values, On_Complete, Find_Parent_Container
	// TODO: Find_Parent_Container??? 
	
	// TODO Parameters
	var Debug = false && Jelly.Debug.Debug_Mode;

	if (Debug)
	{
		Jelly.Debug.Group("Fill");
		Jelly.Debug.Log(Parameters);
	}
	
	// TODO: allow Fill to create a new reference instead of reusing the original reference
	
	// Get existing reference for the target element.
	var Original_Reference;
	if (Parameters["Find_Parent_Container"])
	{
		if (Debug)
			Jelly.Debug.Log("Finding parent container");
			
		// Find the first parent with a URL property
		var Recursive_Parent_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Element"])["Parent_Reference"];		
		while (Recursive_Parent_Reference)
		{
			if (Recursive_Parent_Reference["URL"])
				break;
			if (Recursive_Parent_Reference["Parent_Reference"])
				Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"];
			else if (Recursive_Parent_Reference["Calling_Reference"])
				Recursive_Parent_Reference = Recursive_Parent_Reference["Calling_Reference"];
			else
				Recursive_Parent_Reference = null;
		}
		
		// Try finding by calling reference
		if (!Recursive_Parent_Reference)
		{
			var Recursive_Parent_Reference = Parameters["Reference"]["Calling_Reference"];
			while (Recursive_Parent_Reference)
			{
				if (Recursive_Parent_Reference["URL"])
					break;
				Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"];
			}
			
			// Throw error if parent reference not found.
			if (!Recursive_Parent_Reference)
			{
				Jelly.Debug.Display_Error("Fill: trying to get a parent container, but no parent reference exists");
				return;
			}
		}
		
		// Get namespace from found parent reference
		Original_Reference = Recursive_Parent_Reference;
	}
	else if (Parameters["Create_Reference"])
	{
		if (Debug)
			Jelly.Debug.Log("Creating Reference");
		
		// Find parent reference starting with element above current one so it doesn't catch an existing reference
		Parent_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Element"].parentNode);
		
		// Generate reference
		var Container_Parameters = {};
		Container_Parameters["Kind"] = "URL";
		Container_Parameters["URL"] = Parameters["URL"];
		if (Parameters["No_Loading"])
			Container_Parameters["No_Loading"] = Parameters["No_Loading"];
			
		if (Parent_Reference)
			Container_Parameters["Parent_Namespace"] = Parent_Reference["Namespace"];
		else
			Container_Parameters["Parent_Namespace"] = "Jelly";
		
		// Generate base unique namespace
		Container_Parameters["Namespace"] = Parameters["Element"].id;
		
		var Container_Reference = Jelly.References.Register(Container_Parameters);
		
		Original_Reference = Container_Reference;
	}
	else
	{
		Original_Reference = Jelly.References.Get_Reference_For_Element(Parameters["Element"]);
	}
	
	// TODO - think about whether we can fill a non-URL reference
	if (Original_Reference["Kind"] != "URL")
	{
		if (Debug)
			Jelly.Debug.Log(Original_Reference);
		throw "Cannot fill a non-URL reference. OR CAN YOU?";
	}
	
	// Copy parameters to original reference
	Original_Reference["URL"] = Parameters["URL"];
	Original_Reference["Post_Values"] = Parameters["Post_Values"];
	
	// Stop
	// TODO: necessary?
	// TODO: I thought the whole part of splicing this correctly was to have it flow well, not need STOP.
	// TODO - disabled until we figure this out
// 	New_Reference_Parameters["Stop"] = true;
	
	// On_Complete
	// TODO - this overwrites original on-complete. Seems incorrect (since Windows might need to resize, etc)
	// TODO - not sure if this is ever set, or called.
	// TODO - not used, destroying.
// 	if (Parameters["On_Complete"])
// 		Original_Reference["On_Complete"] = Parameters["On_Complete"];
	
	// Refresh the reference to load its content
	Jelly.References.Refresh(Original_Reference);
	
	if (Debug)
	{
		Jelly.Debug.Log("Reference at end of Fill()...");
		Jelly.Debug.Log(Original_Reference);
		Jelly.Debug.End_Group("");
	}
	
	// Return the reference
	return Original_Reference;
};

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

Jelly.References.Register = function(Parameters)
{
	// Stores reference information by Namespace and by ID for different kinds of metadata, tying the information to an element if it exists, and tying the information to a reference tree. 

	// Parameters: ID, Namespace, Force, Kind, Type, URL, Start, Count, Sort, Alias, Template, Template_Type, Parent_Namespace, Post_Values, On_Complete, One_To_Many_Parent, One_To_Many_Parent_Type
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Register");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Stop registering if no register (i.e. in Action results)
	// TODO "Force" is added from all local registrations so that Action results don't register things... bad hack but should work
	// TODO: this doesn't even do anything? 
	if (Jelly.References.No_Register && !Parameters["Force"])
	{
//			return;
	}
		
	// Initialize Reference
	var Reference = {};

	// Initialize Child References array
	Reference.Child_References = [];
	
	// Initialize Handlers array
	Reference.Handlers = {};
	
	// Verify & Store Namespace
	if (Parameters["Namespace"])
		Reference["Namespace"] = Parameters["Namespace"];
	else
	{
		Jelly.Debug.Display_Error("Register: Parameters must have Namespace"); 
		return;
	}
	
	// Verify & Store Kind
	if (Parameters["Kind"])
		Reference["Kind"] = Parameters["Kind"];
	else
	{
		Jelly.Debug.Display_Error("Register: Parameters must have Kind"); 
		Jelly.Debug.Log(Parameters);
		return;
	}
	
	// Store additional parameters for specific kinds.
	switch (Reference["Kind"])
	{
		// Iterator references store paging information
		// TODO: Does this work?
		case "Iterator":
			// TODO - think about naming
			Reference["Type_Alias"] = Parameters["Type_Alias"];
			Reference["Original_Start"] = Parameters["Start"];
			Reference["Original_Count"] = Parameters["Count"];
			Reference["Original_Sort"] = Parameters["Sort"];
			
			// No Refresh
			Reference["No_Refresh"] = Parameters["No_Refresh"];
			Reference["No_Preloader"] = Parameters["No_Preloader"];
			
			break;
			
		// Item references stores additional descriptors & additional template information.
		case "Item":				
			// Store additional data
			Reference["ID"] = Parameters["ID"];			
			if (Parameters["Name"])
				Reference["Name"] = Parameters["Name"];
			if (Parameters["Alias"])
				Reference["Alias"] = Parameters["Alias"];
			
			// No Refresh
			Reference["No_Refresh"] = Parameters["No_Refresh"];
			Reference["No_Preloader"] = Parameters["No_Preloader"];

			// TODO - think about naming
			Reference["Type_Alias"] = Parameters["Type_Alias"];
			
			if (Parameters["Template_Alias"])
				Reference["Template_Alias"] = Parameters["Template_Alias"];

			// TODO - needed?
			if (Parameters["Template_ID"])
				Reference["Template_ID"] = Parameters["Template_ID"];				
				
			if (Parameters['Variables'])
				Reference['Variables'] = Parameters['Variables'];
				
			// Special Items
			switch (Parameters["Type_Alias"])
			{
				case "Type_Action":
					// TODO: seemingly unnecessary based on the fact that type covers the situation or now.				
					if (Parameters["One_To_Many_Parent"])
					{
						Reference["One_To_Many_Parent"] = Parameters["One_To_Many_Parent"];
						Reference["One_To_Many_Parent_Type"] = Parameters["One_To_Many_Parent_Type"];
					}

				// NO BREAK
				case "Action":
					// TODO: make sure this causes no conflict with refreshing, but it shouldn't
					// TODO - I want Action_Element too for consistency but that's work. 
					Reference.Loading_Element = null;
					Reference.Result_Element = null;
					Reference.Validations = [];
					Reference.Inputs = {};
					break;
			}

			// TODO: // seemingly unnecessary based on the fact that type covers the situation or now.
			if (Parameters["One_To_Many_Parent"])
			{
				// HACK: only doing this for Type_Actions so far
				if (Parameters["Type_Alias"] == "Type_Action")
				{
					Reference["One_To_Many_Parent"] = Parameters["One_To_Many_Parent"];
					Reference["One_To_Many_Parent_Type"] = Parameters["One_To_Many_Parent_Type"];
				}
			}
			break;
			
		// URLs references store the URL, post values, and an on_complete function.
		case "URL":			
			// Validate kind
			// TODO: I think we can take out this validation, or add them everywhere.
			if (!Parameters["URL"])
				{
					Jelly.Debug.Display_Error("Register: reference is URL but no URL passed in parameters"); 
					return;
				}
			
			// Store URL, Post Values, and On Complete
			// TODO: Do we use Post_Values or On_Complete anywhere? Verify.
			Reference["URL"] = Parameters["URL"];
			Reference["Post_Values"] = Parameters["Post_Values"];			
			Reference["No_Preloader"] = Parameters["No_Preloader"];

			// TODO: What? ... maybe this means On_Load, and should be called so. 
			// TODO: redundant or not with load handler? hm......... 
			// TOOD: Also, either way, shouldn't this be more general than URL?
			// TODO :- destroyed, not used.
// 			if (Parameters["On_Complete"])
// 				Reference["On_Complete"] = Parameters["On_Complete"];
			
			break;


		// Special 	references...			
		case "Non_Standard_Wrapper":
			Reference["Name"] = Parameters["Name"];
			break;
		case "HTML":
			// TODO: Anything to do here?
			// TODO - APPARENTLY THIS WAS USED
		case "Container":
			break;
		case "Attachment_Iterator":
			Reference["Type_Alias"] = Parameters["Type_Alias"];
			break;
		case "Attachment":
			Reference["ID"] = Parameters["ID"];
			break;
		// Unknown Kind
		default:
			Jelly.Debug.Log("Register: Unknown reference kind: " + Reference["Kind"]);
			Jelly.Debug.Log(Parameters);
			return;
			break;
	}
	
	// Initialize refresh request array.
	Reference["Refresh_Requests"] = [];
	
	// If a corresponding element exists in the DOM, store the element and reference relationship.
	if (Reference["Kind"] != "Non_Standard_Wrapper")
	{
		if (document.getElementById(Reference["Namespace"]))
		{
			// Store the element into the reference
			Reference["Element"] = document.getElementById(Reference["Namespace"]);
		
			// Store the reference into the element
			Reference["Element"].Jelly_Reference = Reference;
		
			// Register a click handler on the element...

			// Copy original click handler if it exists.
			var Original_Click_Handler = null;
			if (Reference["Element"].onclick)
				Original_Click_Handler = Reference["Element"].onclick;

			// Register new click handler.
			Reference["Element"].onclick = 
				function(Event)
				{	
					// TODO: jQuery? 
					if (!Event)
						var Event = window.event;							
				
					// Call the element's original click handler.
					if (Original_Click_Handler)
						Original_Click_Handler(Event);
			
					// Verify click source
					if (!Jelly.Interface.Bubble_Event_Protection('Namespace_Click'))
						return;
							
					// Get target.
					// TODO: jQuery?
					var Target;
					if (Event.target)
						Target = Event.target;
					else if (Event.srcElement) 
						Target = Event.srcElement;
					if (Target.nodeType == 3) // defeat Safari bug
						Target = Target.parentNode;
				
					// Set focus to target
					Jelly.Handlers.Set_Default_Target(Target);
				};
		
			// If Reference is an "Item" in the Database, register a context menu click handler on the reference element.
			// TODO: consider rendering templates for "New" items for the sake of editing their templates (but not including regular edit links)
			if (Reference["Kind"] == "Item" && Reference["ID"] != "New" && Jelly.Show_Context_Menu)
			{
				Reference["Element"].oncontextmenu =
					function(Event)
					{
						// TODO: jQuery?
						if (!Event) 
							var Event = window.event;
					
						// TODO: This was originally written as a hack, but perhaps we need to complete it.
						if (Event.shiftKey)
							return true;
					
						// Verify context menu click source
						if (!Jelly.Interface.Bubble_Event_Protection('Context_Click'))
							return;

						// Handle Webkit bug
						// TODO: Still needed?
						Jelly.Interface.Catch_Webkit_Context_Click_Bug();
					
						// Show context menu
						Jelly.Interface.Show_Context_Menu({"Target_Element": this, "Event": Event});
					
						return false;
					};
			}

			// Add draggable info
			// TODO: this isn't reliable now because of <span> problems in most browsers...
			// TODO: FEATURE
			/*
			switch (Reference["Kind"])
			{
				case "Iterator":
					if (Parameters["Parent_Property_Alias"])
					{
	//						if (Parameters["Parent_Property_Alias"] == "Child_Page")
						jQuery(Reference["Element"]).droppable(
							{
								hoverClass: 'Jelly_Droppable',
								tolerance: "pointer",
								greedy: true,
								drop: function (Event, UI)
								{
									Jelly.Debug.Log(UI.draggable.context);
									Jelly.Debug.Log("dropped");
								
									var Draggable_Reference = Jelly.References.Get_Reference_For_Element(UI.draggable.context);
								
									// Make sure type aligns
									// TODO: incorporate subtypes, and move this to while it's dragging so targets don't highlight
									Jelly.Debug.Log(Parameters);
									Jelly.Debug.Log(Draggable_Reference);
									if (Parameters["Parent_Property_Value_Type"] != Draggable_Reference.Type)
										return;
								
									switch (Parameters["Parent_Property_Relation"])
									{
										case "Many-To-One":
										case "One-To-One":
											var Action_Parameters =
											{
												Edit_Item: Parameters["Parent"],
												Edit_Item_Type: Parameters["Parent_Type"]
											};
											Action_Parameters["Edited_" + Parameters["Parent_Property_Alias"]] = Draggable_Reference.ID;
											Action_Parameters["Edited_" + Parameters["Parent_Property_Alias"] + "_Type"] = Draggable_Reference.Type;
											Jelly.Actions.Execute
											(
												{
													Action: "Edit",
													Namespace: "",
													Parameters: Action_Parameters
												}
											);
											break;
										case "Many-To-Many":
										case "One-To-Many":
											Jelly.Actions.Execute
											(
												{
													Action: "Add_Item_To_Item",
													Namespace: "",
													Parameters:
													{
														Item: Draggable_Reference.ID,
														Item_Type: Draggable_Reference.Type,
														Target: Parameters["Parent"],
														Target_Type: Parameters["Parent_Type"],
														Target_Property: Parameters["Parent_Property_Alias"]
													}
												}
											);
											break;
									}
								}
							}
						);
					}
					break;
				case "Item":
					{
						if (Parameters["Draggable"])
						{
							jQuery(Reference["Element"]).draggable
							(
								{
									cursor: "move",
									helper: "clone",
									appendTo: "body",
									revert: true
								}
							);
						}
					}
					break;
			}
			*/
		}
	}

	// If previous reference for namespace exists, copy parent reference and handlers and then remove the previous reference.
	// TODO - anything else to copy? should handlers even be copied?
	// TODO - make sure inputs are correctly re-registered, if we're doing shit this way.
	if (Jelly.References.References_By_Namespace[Parameters["Namespace"]])
	{
		var Previous_Reference_For_Namespace = Jelly.References.References_By_Namespace[Parameters["Namespace"]];

		// Copy parent reference
		// TODO - allow escape? 
		if (Previous_Reference_For_Namespace['Parent_Reference'])
		{
			Reference['Parent_Reference'] = Previous_Reference_For_Namespace['Parent_Reference'];
			Reference['Parent_Namespace'] = Reference['Parent_Reference']['Namespace'];
		}
		else
			Reference['Parent_Reference'] = null;
		
		// Copy handlers
		for (var Event_Name in Previous_Reference_For_Namespace.Handlers)
			Reference.Handlers[Event_Name] = Previous_Reference_For_Namespace.Handlers[Event_Name];

		// Remove existing reference
		// TODO: "Is this the best way to handle garbage collection... ?" - Tristan Perich, 2014	
		Jelly.References.Remove_Reference(Previous_Reference_For_Namespace);
	}
	
	// Otherwise get parent reference from parameterss
	else
	{
		// Store parent reference relationship.
		if (Parameters["Parent_Namespace"] != "Jelly")
		{
			if (!Parameters["Parent_Namespace"])
			{
				Jelly.Debug.Display_Error("Register: No Parent_Namespace provided");
				Jelly.Debug.Log(Parameters);
				return;
			}

			// Validate parent namespace.
			if (!Jelly.References.References_By_Namespace[Parameters["Parent_Namespace"]])
			{
				// TODO: report original DOM element no longer exists (i.e. on a [Go /] action result)
				Jelly.Debug.Display_Error("Register: Reference for Parent_Namespace does not exist: " + Parameters["Parent_Namespace"])
				if (Debug)
					Jelly.Debug.Log(Reference);
				return;
			}

			// Store parent reference to this reference
			Reference["Parent_Reference"] = Jelly.References.References_By_Namespace[Parameters["Parent_Namespace"]];
		}
		else
		{
			// Global references have no parent reference
			Reference["Parent_Reference"] = null;
		}
	}
	
	// Add this reference to parent reference's children
	if (Reference["Parent_Reference"])
		Reference["Parent_Reference"]["Child_References"].push(Reference);
		
	// Store reference by namespace
	Jelly.References.References_By_Namespace[Reference["Namespace"]] = Reference;
				
	// Append reference to list of references for this kind (and lookup)
	switch(Reference["Kind"])
	{
		case "Attachment_Iterator":
		case "Iterator":
			// If it doesn't exist, instantiate a list of references for this ID.		
			if (!Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]])
				Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]] = new Array();

			// Add this reference to the list.			
			Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]].push(Reference);
			break;
			
		case "Attachment":
		case "Item":
			// If it doesn't exist, instantiate a list of references for this ID.		
			if (!Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["ID"]])
				Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["ID"]] = new Array();

			// Add this reference to the list.			
			Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["ID"]].push(Reference);
			
			// If is container item, set container item reference.
			if (Parameters['From_Container'])
				Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"] = Reference;
				
			// If is site item, set site item reference
			if (Parameters["Type_Alias"] == "Site" && Parameters['From_Request'])
				Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"] = Reference;
			break;
			
		case "URL":
			// Add this reference to the list.
			Jelly.References.Reference_Lookups_By_Kind["URL"].push(Reference);
			break;
			
		case "Non_Standard_Wrapper":
			// Special case references with specific  handling.
			switch (Reference["Name"])
			{
				case "Site_Icon":
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"].push(Reference);
					break;
		
				case "Document_Title":
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"].push(Reference);
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"].push(Reference);
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Secondary"].push(Reference);
					break;
		
				case "Current_Path":
					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"].push(Reference);
					break;
				default:
					break;
			}
			break;
			
			
		case "Container":
			// Register primary path reference.
			Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Primary"] = Reference;

			// Mark all parent references as "design only" to fix refreshing problems
			// TODO improve design-only refreshing. Perhaps by marking the main site design specifically, etc. Well, definitely by doing that.
			var Recursive_Parent_Reference = Reference["Parent_Reference"];
			var Set_To_Design_Only = true;
			while (Recursive_Parent_Reference)
			{
				// If we have not yet passed a design item, then keep setting Design Only to true
				if (Set_To_Design_Only)
					Recursive_Parent_Reference["Design_Only"] = true;

				// If we pass a design item, stop setting Design Only to true.				
				if (Recursive_Parent_Reference["Kind"] == "Item" && Recursive_Parent_Reference["Type_Alias"] == "Design")
					Set_To_Design_Only = false;

				Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"];
			}
			break;
		
		case "HTML":
			// TODO anything to do here?
			// TODO - does this do anything? no? 
			break;
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Reference...");
		Jelly.Debug.Log(Reference);
		Jelly.Debug.End_Group("Register");
	}
	
	// Return Reference
	return Reference;
};

Jelly.References.Refresh_All = function()
{
	// Clean all references, verify queue of references to refresh as refreshable and unique, refresh verified references, and clear the queue.
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Refresh_All");
		Jelly.Debug.Log(Jelly.References.References_To_Refresh);
	}
	
	// Remove dead references
	Jelly.References.Clean_References();	
	
	// Determine refreshable reference/parent reference
	var Refresh_List = {};
	
	for (var Reference_Index in Jelly.References.References_To_Refresh)
	{
		if (Jelly.References.References_To_Refresh.hasOwnProperty(Reference_Index))
		{
			var Reference = Jelly.References.References_To_Refresh[Reference_Index];
		
			if (Debug)
			{
				Jelly.Debug.Group("Checking if reference can be refreshed: " + Reference["Namespace"]);
				Jelly.Debug.Log(Reference);
			}
			
			// Search through parents for the first refreshable reference
			var Recursive_Reference = Reference;			
			var Refreshable_Reference_Was_Found = false;
			while (Recursive_Reference && !Refreshable_Reference_Was_Found)
			{
				var Inner_Debug = false;
				
				if (Inner_Debug)
					Jelly.Debug.Log("... " + Recursive_Reference["Namespace"]);
				
				// Only refresh references with elements in the DOM, and which are not No_Refresh
				if (!Recursive_Reference["No_Refresh"])
				{
					switch (Recursive_Reference["Kind"])
					{	
						// Non Standard Wrappers are refreshable
						case "Non_Standard_Wrapper":
						// URLs are refreshable.
						case "URL":
						// Containers are refreshable
						case "Container":
							// Stop searching
							Refreshable_Reference_Was_Found = true;
							break;
							
						// Items are refreshable if they have a template
						case "Item":
							// If this is site item itself, force a hard refresh.
							if (Recursive_Reference == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"])
							{
								// TODO - debug this out for demo mode. 
//								Refreshable_Reference_Was_Found = true;								

								// TODO - Implement this version after finishing investigating
								// Jelly.Utilities.Reload_Page();
							}

						
							// Verify element exists in DOM.
							if (Recursive_Reference["Element"] && Jelly.jQuery.contains(document, Recursive_Reference["Element"]))
							{
								// Verify template was set on the item reference
								// TODO - isn't this always true?
								if (Recursive_Reference["Template_Alias"])
								{
									// Don't allow refreshing in Design_Only until we hit a Design itself
									// TODO fix
									if (!Recursive_Reference["Design_Only"] || Recursive_Reference["Type_Alias"] == "Design")
									{
										// Stop searching
										Refreshable_Reference_Was_Found = true;
									}
								}
							}
							break;
							
						default:
							 if (Inner_Debug)
								Jelly.Debug.Log("NEXT (is not container, is not url, or item without template alias)");
							break;
					}
				}
				else
				{
					 if (Inner_Debug)
						Jelly.Debug.Log("NEXT (Does not have element or is no refresh)");
				}
				
				if (Refreshable_Reference_Was_Found)
					break;
					
				if (Recursive_Reference["Block_Refresh"])
				{	
						if (Debug)
							Jelly.Debug.Log("Refresh Blocked");
						
						// Cancel reference refresh
						Recursive_Reference = null;
				
						// Stop searching
						break;
				}
			
				// If a reference is marked to stop traversal, cancel search.
				// TODO: Is this used?
				if (Recursive_Reference["Stop"])
				{
					 if (Debug)
						Jelly.Debug.Log("STOP (Is Stop)");
						
					// Cancel reference refresh
					Recursive_Reference = null;
				
					// Stop searching
					break;
				}
				
					
				// If this reference was not refreshable, and the reference wasn't marked to stop traversal, continue search to the parent reference
				Recursive_Reference = Recursive_Reference["Parent_Reference"];
			}
		
			// If there is no refreshable reference, continue to the next reference in the queue of references to refresh.
			// TODO: we shouldn't need this since parents should bottom out at Body
			// TODO: but should they? what about windows?
			if (!Recursive_Reference)
			{
				if (Debug)
				{
					Jelly.Debug.Log("NO: Reference will not be refreshed.");
					Jelly.Debug.End_Group("");					
				}
				
				continue;
			}
		
			// Add the refreshable reference by its namespace to a list of queue of verified references to refresh
			if (Debug)
			{
				Jelly.Debug.Log("YES: Reference will be refreshed: " + Recursive_Reference["Namespace"]);
				Jelly.Debug.End_Group("");
			}
			Refresh_List[Recursive_Reference["Namespace"]] = Recursive_Reference;
		}
	}
	
	if (Debug)
	{	
		Jelly.Debug.Log('Refresh list before consolidated by parent');
		Jelly.Debug.Log(Refresh_List);
	}
	
	// For each verified reference to refresh, also verify that none of its parent references are not already in the list.
	for (var Reference_Index in Refresh_List)
	{
		if (Refresh_List.hasOwnProperty(Reference_Index))
		{
			// Get reference to verify.
			var Reference_To_Verify = Refresh_List[Reference_Index];
			
			var Inner_Debug = false;
			
			if (Inner_Debug)
				Jelly.Debug.Log('Verifying reference: ' + Reference_To_Verify['Namespace']);

			// Search through parents
			
			var Recursive_Parent_Reference = Reference_To_Verify["Parent_Reference"];
			while (Recursive_Parent_Reference)
			{
				if (Inner_Debug)
					Jelly.Debug.Log('...' + Recursive_Parent_Reference["Namespace"]);
					
				// Check if recursive reference is already in refresh list
				if (Refresh_List[Recursive_Parent_Reference["Namespace"]])
				{
					// Delete reference to verify from queue of verified references to refresh
					delete Refresh_List[Reference_To_Verify["Namespace"]];
				
					// Stop searching
					break;
				}
		
				// If a reference is marked to stop traversal, cancel search for parent reference.
				// TODO: are these used? 
				// TODO: This was from a bad day, check if there should be an OR condition
				if (Recursive_Parent_Reference["Stop"])// || )
					break;
			
				// TODO: catch for mistakes, i guess
				// TODO: this used to mark the reference as 'stop'
				// TODO: maybe these can be caught on registration, and at other splicing moments, instead.
				if (Recursive_Parent_Reference["Parent_Reference"] == Recursive_Parent_Reference)
					break;

				// If this parent reference isn't already on the list, and the search hasn't been stopped, check the next parent reference.
				Recursive_Parent_Reference = Recursive_Parent_Reference["Parent_Reference"];
			}
		}
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Final Refresh List");
		Jelly.Debug.Log(Refresh_List);
	}
	
	// Initialize references to refresh
	Jelly.References.References_To_Refresh = {};
	
	// Refresh each reference in the queue of verified references to refresh.
	for (var Reference_Index in Refresh_List)
	{				
		if (Refresh_List.hasOwnProperty(Reference_Index))
		{
			var Reference = Refresh_List[Reference_Index];
			Jelly.References.Refresh(Reference);
		}
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("Refresh_All");
	}
};

Jelly.References.Refresh_Site = function()
{	
	// If the interface isn't locked, refresh the page
	if (!Jelly.Interface.Is_Locked)
		Jelly.Utilities.Reload_Page();
	
	// Otherwise, add a refresh page callback;
	else
		Jelly.Interface.On_Unlock.push(Jelly.Utilities.Reload_Page);
};

Jelly.References.Block_Refresh = function(Parameters)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Block Refresh");
		Jelly.Debug.Log(Parameters);
	}
	
	if (Debug)
		Jelly.jQuery('#Jelly_Inspector').css("background-color","red");
	
	// Lookup reference
	var Reference_Element = document.getElementById(Parameters["Namespace"]);
	var Reference = Jelly.References.Get_Reference_For_Element(Reference_Element);
	if (!Reference)
		return;
	
	// Block refresh
	Reference["Block_Refresh"] = true;
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.References.Element_Exists = function(Test_Element)
{	
	// Verify that the test element exists in the DOM.
	// TODO: There are faster ways, here... http://jsperf.com/closest-vs-contains/4, but they're much uglier, with fake test ids.
	// TODO But seemingly at least 30x faster.
	
	var Recursive_Test_Element = Test_Element;
 
 	// Verify recursive parent nodes of test element until reaching document, or return false.
	while (Recursive_Test_Element)
	{
		if (Recursive_Test_Element == document)
			return true;
		Recursive_Test_Element = Recursive_Test_Element.parentNode;
	}
	
	return false;
};

Jelly.References.Release_Refresh = function(Parameters)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Release Refresh");
		Jelly.Debug.Log(Parameters);
	}
	
	if (Debug)
		Jelly.jQuery('#Jelly_Inspector').css("background-color","#ccc");
	
	// Lookup reference
	var Reference_Element = document.getElementById(Parameters["Namespace"]);
	var Reference = Jelly.References.Get_Reference_For_Element(Reference_Element);
	if (!Reference)
		return;
	
	// Release refresh
	delete Reference["Block_Refresh"];
	
	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.References.Trigger_Refresh = function(Parameters)
{
	// Queues item, iterator, and special case reference refreshes.
	// Parameters: Kind, Type_Alias, Item_ID, Item_Alias, Namespace, URL
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Trigger_Refresh:" + Parameters['Kind'] + ','  + ' ' + Parameters['Type_Alias'] + ',' + ' ' + Parameters['Item_ID'] + ',' + ' ' + Parameters['Item_Alias'] + ',' + ' ' + Parameters['Namespace'] + ',' + ' ' + Parameters['URL']);
	}
	
	var Matching_References = [];
	
	switch (Parameters["Kind"])
	{		
		case "Iterator":
			// Queue iterator references that match type to refresh
			// TODO Parent Types
			if (Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]])
				Matching_References = Jelly.References.Reference_Lookups_By_Kind["Iterator"][Parameters["Type_Alias"]];			
			
			// Refresh Less for Designs and Templates
			// TODO better way to do type-specific refreshing?
			if (Parameters.Type_Alias == "Design" || Parameters.Type_Alias == "Template")
				less.refresh(true);
			break;
			
		case "Item":
			// If this is the container item, trigger container item specific refreshes.
			if (Parameters["Item_ID"] == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"]["ID"])
				Jelly.References.Trigger_Refresh({"Kind":"Container"});
			
			// If this is the site item, trigger site item specific refreshes.
			if (Parameters["Item_ID"] == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"]["ID"])
				Jelly.References.Trigger_Refresh({"Kind":"Site"});
					
			// Queue item references that match item to refresh
			if (Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["Item_ID"]])
				Matching_References = Jelly.References.Reference_Lookups_By_Kind["Item"][Parameters["Item_ID"]];
			break;
				
		case "Container":
			Matching_References = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"];
			break;
			
		// TODO - "Current_Session"->"Site" is for the the demo, needs thinking...? seems to be shorthand for the site item.
		case "Current_Session":	
				// Refresh the site item
				Jelly.References.Trigger_Refresh({"Kind":"Item", "Item_ID": Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"]["ID"]});
			break;

		case "Site":
			Matching_References = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"];
			break;
			
		// TODO - Temporary
		case "Path":
		case "Path_Primary":
			Matching_References = [Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Primary"]];
			break;
			
		case "Path_Secondary":
			Matching_References = Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Secondary"];
			break;
			
		// TODO - not used.
		case "Direct":
			// Queue reference to refresh
			Matching_References.push(Jelly.References.References_By_Namespace[Parameters["Namespace"]]);
			break;
			
		case "Element":
		{
			var Reference = Jelly.References.Get_Reference_For_Element(Parameters['Element']);
			Matching_References.push(Reference);
			break;
		}

		// TODO - not used.			
		case "URL":
			if (Jelly.References.Reference_Lookups_By_Kind["URL"][Parameters["URL"]])
				Matching_References = Jelly.References.Reference_Lookups_By_Kind["URL"][Parameters["URL"]];
			break;
			
		default:
			return;
	}
	
	// Add matching references as unique by namespace to References to Refresh array.
	for (var Reference_Index in Matching_References)
	{
		if (Matching_References.hasOwnProperty(Reference_Index))
		{
			var Reference = Matching_References[Reference_Index];
			
			if (Debug)
			{
				// TODO
				Jelly.Debug.Log("Changed Reference: " +  " (" + Reference["Namespace"] + ")");
				Jelly.Debug.Log(Reference);
			}
			
			// Mark reference to be refreshed
			Jelly.References.References_To_Refresh[Reference["Namespace"]] = Reference;
		}
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};

Jelly.References.Remove_Reference = function(Reference)
{
	// Remove this reference from lists of references by namespace and by id, as well as the refresh queue, and recursively do the same for all children.
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Remove Reference...");
		Jelly.Debug.Log(Reference);
		Jelly.Debug.Log(Reference);
	}
	
	var Reference_Kind = Reference["Kind"];

	if (Debug) Jelly.Debug.Log("Remove Reference: Is a"  + Reference_Kind);

	switch (Reference_Kind)
	{					
		case "Attachment_Iterator":
		case "Iterator":
			var Reference_Lookup = Reference["Type_Alias"];
			var Matching_Reference_Lists = [
					Jelly.References.Reference_Lookups_By_Kind['Iterator'][Reference_Lookup]
				];
			break;
		case "Attachment":			
		case "Item":
			var Reference_Lookup = Reference["ID"];
			var Matching_Reference_Lists = [
					Jelly.References.Reference_Lookups_By_Kind['Item'][Reference_Lookup]
				];

			// TODO - below should be implemented, but incomplete.
			// Remove container item
// 			if (Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"])
// 				if (Reference_Lookup == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"]["ID"])
// 					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Item"] = null;
// 
// 			// Remove site item
// 			else if (Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"])
// 				if (Reference_Lookup == Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"]["ID"])
// 					Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Item"] = null;
				
			break;
			
		case "URL":
			var Matching_Reference_Lists = [
					Jelly.References.Reference_Lookups_By_Kind['URL']
				];			
			break
			
		case "Non_Standard_Wrapper":
			var Reference_Name = Reference["Name"];
			switch (Reference_Name)
			{
				case "Site_Icon":
					var Matching_References_Lists = [
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"]
						];
					break;

				case "Current_Path":
					var Matching_References_Lists = [
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"]
						];						
					break;		

				case "Document_Title":
					var Matching_Reference_Lists = [
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Site"]["Dependencies"],
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Container"]["Dependencies"],	
							Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Secondary"]
						];						
					break;		
				default:
					// TODO - throw error.
					break;
			}
			break;
		case "Container":
			Jelly.References.Reference_Lookups_By_Kind["Specific"]["Path"]["Dependencies"]["Primary"] = null;
			var Matching_Reference_Lists = [];
			break;
		case "HTML":
			var Matching_Reference_Lists = [];
			break;
		default:
			// TODO - throw error
			break;
	}

	// Remove reference from reference lookup.
	for (List_Index = 0; List_Index < Matching_Reference_Lists.length; List_Index++)
	{
		var Matching_References = Matching_Reference_Lists[List_Index];
		for (Reference_Index = 0; Reference_Index < Matching_References.length; Reference_Index++)
		{
			if (Matching_References[Reference_Index] == Reference)
			{
				// Splice index out of array
				Matching_References.splice(Reference_Index, 1);
				break;
			}
		}
	}

	// Remove reference from its Parent reference
	if (Reference.Parent_Reference)
	{
		for (Reference_Index in Reference.Parent_Reference.Child_References)
		{
			if (Reference.Parent_Reference.Child_References.hasOwnProperty(Reference_Index))
			{
				if (Reference.Parent_Reference.Child_References[Reference_Index] == Reference)
				{
					// Splice index out of array
					Reference.Parent_Reference.Child_References.splice(Reference_Index, 1);
					break;
				}
			}
		}
	}

	// "Do not remove reference from its DOM element so that Jelly event bubbling can still occur"
	// TODO: this is fishy, indicator that we should rewrite events handling, then uncomment this
//			if (Reference.Element)
//				delete Reference.Element.Jelly_Reference; // leave commented

	// Delete item from Jelly.References.References_By_Namespace object
	delete Jelly.References.References_By_Namespace[Reference["Namespace"]];

	// Delete item from Jelly.References.References_To_Refresh object
	delete Jelly.References.References_To_Refresh[Reference["Namespace"]];
	
	// Delete reference to DOM element
	// TODO not sure if this is correct or if we want to keep it around for any reason
	if (Reference["Element"])
		delete Reference["Element"];

	// Remove children
	while (Reference["Child_References"].length)
	{
		var Child_Reference = Reference["Child_References"][0];
		Jelly.References.Remove_Reference(Child_Reference);
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("Remove Reference...");
	}
};

Jelly.References.Clean_References = function()
{
	// Remove all references with an element that does not exist in the DOM
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Clean_References");
	}
	
	// Remove all references with an element that does not exist in the DOM
	for (Reference_Namespace in Jelly.References.References_By_Namespace)
	{
		if (Jelly.References.References_By_Namespace.hasOwnProperty(Reference_Namespace))
		{
			var Reference = Jelly.References.References_By_Namespace[Reference_Namespace];
			
			// If the reference has an element but the element is not in the DOM, remove the reference
			if (Reference["Element"])
			{
				if (!Jelly.References.Element_Exists(Reference["Element"]))
				{
					Jelly.References.Remove_Reference(Reference);
				}
			}
		}
	}
	
	if (Debug)
	{
		Jelly.Debug.End_Group("Clean_References");
	}
};

Jelly.References.Create_Reference = function()
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create_Global_Reference");
	}
	
	var Reference = {};
	Reference["Refresh_Requests"] = [];
	
	if (Debug)
	{
		Jelly.Debug.Log("Reference...");
		Jelly.Debug.Log(Reference);
		Jelly.Debug.End_Group("");
	}
	
	// Return new reference
	return Reference;
};

Jelly.References.Refresh_Site_Icon = function()
{		
	// TODO - can generalize, of course, if ever needed.
	// TODO - this is overzealous, based on some web forum comments. might be able to just change the URL now, without the magician act

	// Get icon element
	var Icon_Element = document.getElementById('Jelly_Site_Icon');
	
	// Duplicate element
	var Icon_Duplicate_Element = Icon_Element.cloneNode();
	
	// Store icon' parent node.
	var Icon_Parent_Node = Icon_Element.parentNode;
	
	// Remove icon element from parent node
	Icon_Parent_Node.removeChild(Icon_Element);

	// Generate timestamp
	var Current_Timestamp = new Date().getTime();
	
	// Update duplicate element href
	var Icon_URL = Icon_Duplicate_Element.href;
	var Icon_URL_Timestamp_Index = Icon_URL.lastIndexOf('=') + 1;
	var New_Icon_URL = Icon_URL.substring(0,Icon_URL_Timestamp_Index) + Current_Timestamp;	
	Icon_Duplicate_Element.href = New_Icon_URL;
	
	// Insert updated element into parent node
	Icon_Parent_Node.appendChild(Icon_Duplicate_Element);
};

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

Jelly.References.Get_Iterator_Parameters = function(Reference)
{
	// TODO: Not done yet.
	// TODO: Does this work?
	Post_Values = {};
	
	// TODO: What is this? 
	// Add kind-specific parameters
	switch (Reference["Kind"])
	{
		case "Iterator":
			if (Reference["Start"])
				Post_Values[Reference["Namespace"] + "_Start"] = Reference["Start"];
			if (Reference["Count"])
				Post_Values[Reference["Namespace"] + "_Count"] = Reference["Count"];
			break;
	}
	
	// Add parameters for children
	for (Child_Reference_Index in Reference["Child_References"])
	{
		var Child_Reference = Reference["Child_References"][Child_Reference_Index];
		// TODO does check make sense
		if(Child_Reference !== Reference)
			Jelly.References.Add_Reference_Parameters_To_Post_Values(Child_Reference, Post_Values);
	}
	
	return Post_Values;
};

Jelly.References.Create_Global_Reference = function()
{
	// Registers new control reference with a new corresponding element	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create_Global_Reference");
	}
	
	// Generate global namespace by incrementing global reference count.
	// TODO, name of the Last_Unique_Reference_Index variable
	Jelly.References.Current_Global_Reference_Index++;
	var Namespace = Jelly.Interface.Global_Controls_Element.id + Jelly.Namespace_Delimiter + Jelly.References.Current_Global_Reference_Index;

	// Create control element and add it to the controls element
	var Reference_Element = document.createElement("span");
	Reference_Element.id = Namespace;
	Jelly.Interface.Global_Controls_Element.appendChild(Reference_Element);
	
	// Register control reference
	var Parameters = {};
	Parameters["Parent_Namespace"] = "Jelly";
	Parameters["Namespace"] = Namespace;
	Parameters["Kind"] = "HTML";
	var Reference = Jelly.References.Register(Parameters);
	
	if (Debug)
	{
		Jelly.Debug.Log("Reference...");
		Jelly.Debug.Log(Reference);
		Jelly.Debug.End_Group("");
	}
	
	// Return new reference
	return Reference;
};

Jelly.References.Set_Iterator_Parameters = function(Namespace, Parameters)
{		
	// TODO - In general
	// TODO: Is this real?
	// Get reference.
	var Reference = Jelly.References.References_By_Namespace[Namespace];
	
	// Return if no reference.
	if (!Reference)
		return;
		
	// Return if no iterator count (Should be a more distinct terminology / variable)
	if (!Reference["Count"])
		return;
		
	// Set iterator start explictly
	if (Parameters["Start"])
		Reference["Start"] = Parameters["Start"];
		
	// Move iterator start to next page
	else if (Parameters["Page"] == "Next")
	{
		if (Reference["Start"])
			Reference["Start"] = parseInt(Reference["Start"]) + parseInt(Reference["Count"]);
		else
			Reference["Start"] = Reference["Count"];
	}
	
	// Move iterator start to previous page
	else if (Parameters["Page"] == "Previous")
	{
		if (Reference["Start"])
			Reference["Start"] = parseInt(Reference["Start"]) - parseInt(Reference["Count"]);
	}
	
	// Set iterator page explicitly				
	else if (Parameters["Page"])
	{
		// TODO : Error check
		Reference["Start"] = parseInt(Parameters["Page"]) * parseInt(Reference["Count"]);
	}
	
	// Set iterator sort property
	if (Parameters["Sort"])
	{
		Reference["Sort"] = Parameters["Sort"];
		Reference["Start"] = 0;
	}
	
	Reference["Submit_Iterator_Parameters"] = true;
				
	// TODO : Currently, this is the best way to find and refresh a refreshable parent.
	// Mark reference to be refreshed
	Jelly.References.References_To_Refresh[Reference["Namespace"]] = Reference;
	
	// Refresh
	Jelly.References.Refresh_All();
};

// Find the closest reference for a target element element.
Jelly.References.Get_Reference_For_Element = function(Target_Element)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Get Reference For Element");
	}

	// Search parent DOM Elements recursively for a reference, return the reference.
	var Recursive_Parent_Element = Target_Element;
	while (Recursive_Parent_Element)
	{
		if (Debug)
			Jelly.Debug.Log(Recursive_Parent_Element);
			
		// If element has a reference, return the reference.
		if (Recursive_Parent_Element.Jelly_Reference)
		{
			if (Debug)
				Jelly.Debug.End_Group("Get Reference For Element");
			return Recursive_Parent_Element.Jelly_Reference;
		}
		
		// Otherwise, search the parent DOM element.
		Recursive_Parent_Element = Recursive_Parent_Element.parentNode;
	}
	
	// If no reference has been found, return null.
	if (Debug)
		Jelly.Debug.End_Group("Get Reference For Element");
	return null;
};

Jelly.References.Get_Input_Values_For_Action = function(Parameters)
{
	var Namespace = Parameters["Namespace"];
	
	//	TODO: this should be Jelly.Action....
	// Returns an array of keys & values from all inputs that match the namespace
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
		Jelly.Debug.Group("Get_Input_Values_For_Action" + " (" + Namespace + ")");
		
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	
	// Require action reference
	if (!Action_Reference)
	{
		Jelly.Debug.Log("Trying to get values for an action reference by namespace, but no reference found for the namespace");
		return;
	}
	
	// Verify action reference type.
	if (Action_Reference["Type_Alias"] != "Action" && Action_Reference["Type_Alias"] !=  "Type_Action")
	{
		Jelly.Debug.Log("Trying to get values for an action reference by namespace, but reference is incorrect type");
		return;
	}
		
	// Instatate form value array
	var Form_Values =  {};
	
	// Search through all input elements in document.
	// TODO jQuery, or another selector query, would make this code a lot more concise
	// TODO - hopefully we don't have to suppress the following input names anymore: Action, Edit_Item, Edit_Type	
	var Inputs = Action_Reference.Inputs;
	for (Input_Index in Inputs)
	{
		if (Inputs.hasOwnProperty(Input_Index))
		{
			if (!Inputs[Input_Index]["Sensitive"] || Parameters["Include_Sensitive"])
			{
				var Input_Element = Inputs[Input_Index]["Element"];
		
				if (Debug)
				{
					Jelly.Debug.Log("Gathering Input Element with ID: " + Input_Element.id);
					Jelly.Debug.Log(Input_Element);
				}
			
				// Set input index by input id or input name
				var Input_Element_Index = Input_Element.getAttribute("name");
				
				// Set up input value
				var Input_Element_Value;

				// Convert input element value for special cases...
				switch (Input_Element.tagName)
				{
					case "INPUT":
					{
						switch (Input_Element.type)
						{
							// Convert checkbox values
							case "checkbox":
							{
								if (Input_Element.checked)
									Input_Element_Value = "1";
								else
									Input_Element_Value = "0";
								break;
							}
							
							// Convert or calculate hidden input values
							case "hidden":
							{
								// Set input value
								Input_Element_Value = Input_Element.value;
								
								var Input_Value_Element_ID = Input_Element.id;
								var Input_Value_Type_Element_ID = Input_Value_Element_ID + "_Type";
								var Input_Value_Type_Element = document.getElementById(Input_Value_Type_Element_ID);
								if (Input_Value_Type_Element)
								{	
									var Input_Value_Type = Input_Value_Type_Element.value;
									switch (Input_Value_Type.toLowerCase())
									{
										// Handle date/time input values.
										case "date":
										case "date_time":
										case "time":
										{
											Debug = false;
										
											// Get input namespace
											var Input_Namespace = Input_Value_Element_ID.substring(0, Input_Value_Element_ID.length - (Jelly.Namespace_Delimiter + "Value").length);
										
											if (Debug)
											{
												Jelly.Debug.Log(Input_Element.id);
												Jelly.Debug.Log(Input_Namespace);
											}
										
											// Clean inputs
											// TODO - this isn't exactly the right place. It's for when you submit while you're still in the input element, say via the enter key, so that you don't lose your value then.  The right place might be on the enter key handler.
											switch (Input_Value_Type.toLowerCase())
											{
												case "date":
													Jelly.Interface.Clean_Date_Input(Input_Namespace);
													break;
												case "time":
													Jelly.Interface.Clean_Time_Input(Input_Namespace);
													break;
												case "date_time":
													Jelly.Interface.Clean_Date_Input(Input_Namespace);
													Jelly.Interface.Clean_Time_Input(Input_Namespace);
													break;
											}
										
											// Refresh date value
											// TODO - the only reason this input exists is for this function right here. Makes me think about inputs, and perhaps registering some without an element at all, now that we register them?
											Jelly.Interface.Refresh_Date_Value(Input_Namespace);
										
											// Set date value
											Input_Element_Value = Input_Element.value;
										
											break;
										}
									}
								}
								break;
							}
							default:
							{
								// Set input value
								Input_Element_Value = Input_Element.value;
								
								// TODO - this can be thought through...
								// TODO - ok it's more hodge-podge now already.
								if (!Parameters["Include_Sensitive"])
								{
									if (Inputs[Input_Index]["Clear_On_Execute"])
										Input_Element.value = "";
									
									if (Inputs[Input_Index]["Blur_On_Execute"])
										Input_Element.blur();
								}
								
								break;
							}
						}
						break;
					}
					case "DIV":
					{
						Input_Element_Value = Jelly.jQuery(Input_Element).html();
						break;
					}
					case "TEXTAREA":
					{
						// Set input value
						Input_Element_Value = Input_Element.value;
						break;
					}
				}
		
				// Store input value by index into form values array
				Form_Values[Input_Element_Index] = Input_Element_Value;
			}
		}
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Post Values...");
		Jelly.Debug.Log(Form_Values);
		Jelly.Debug.End_Group("");
	}

	return Form_Values;
};

Jelly.References.Get_Local_Values_For_Namespace = function(Parameters)
{
	var Namespace = Parameters["Namespace"];
	
	//	TODO: this should be Jelly.Action....
	// Returns an array of keys & values from all inputs that match the namespace
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
		Jelly.Debug.Group("Get_Local_Values_For_Namespace" + " (" + Namespace + ")");
		
	// Instatate form value array
	var Form_Values =  {};
	
	var Namespace = Parameters["Namespace"];
	
	// Search through all input elements in document.
	// TODO jQuery, or another selector query, would make this code a lot more concise
	
	// Gather parameters from namespaced input elements.
	var Inputs = document.getElementsByTagName("input");
	for (var InputIndex = 0; InputIndex < Inputs.length; InputIndex++)
	{
		if (Inputs[InputIndex].id.substring(0, Namespace.length) == Namespace)
		{
			switch (Inputs[InputIndex].type)
			{
				// Convert checkbox values
				case "checkbox":
					if (Inputs[InputIndex].checked)
						Form_Values[Inputs[InputIndex].id] = "1";
					else
						Form_Values[Inputs[InputIndex].id] = "0";
					break;
				
				// Convert date/time values
				// TODO - date/time treatment copied w/o comments from Get_Input_Values_For_Action --- still to understand differentiation.
				case "hidden":
					var Input_Value_Element_ID = Inputs[InputIndex].id;
					var Input_Value_Type_Element_ID = Input_Value_Element_ID + "_Type";
					var Input_Value_Type_Element = document.getElementById(Input_Value_Type_Element_ID);
					if (Input_Value_Type_Element)
					{
						var Input_Value_Type = Input_Value_Type_Element.value;
						switch (Input_Value_Type.toLowerCase())
						{	
							// Specila case
							case "date":
							case "date_time":
							case "time":
								// Get input namespace
								var Input_Namespace = Input_Value_Element_ID.substring(0, Input_Value_Element_ID.length - (Jelly.Namespace_Delimiter + "Value").length);
								
								// Clean inputs
								switch (Input_Value_Type.toLowerCase())
								{
									case "date":
										Jelly.Interface.Clean_Date_Input(Input_Namespace);
										break;
									case "time":
										Jelly.Interface.Clean_Time_Input(Input_Namespace);
										break;
									case "date_time":
										Jelly.Interface.Clean_Date_Input(Input_Namespace);
										Jelly.Interface.Clean_Time_Input(Input_Namespace);
										break;
								}
								
								// Refresh date value
								Jelly.Interface.Refresh_Date_Value(Input_Namespace);								
						}
					}
					// Set hidden value
					Form_Values[Inputs[InputIndex].id] = Inputs[InputIndex].value;
					break;
				default:
					Form_Values[Inputs[InputIndex].id] = Inputs[InputIndex].value;
					break;
			}
		}
	}
		
	// Gather parameters from namespaced select elements.
	var Inputs = document.getElementsByTagName("select");
	for (var InputIndex = 0; InputIndex < Inputs.length; InputIndex++)
		if (Inputs[InputIndex].id.substring(0, Namespace.length) == Namespace)
			Form_Values[Inputs[InputIndex].id] = Inputs[InputIndex].value;
	
	// Gather parameters from namespaced textarea elements.
	var Inputs = document.getElementsByTagName("textarea");
	for (var InputIndex = 0; InputIndex < Inputs.length; InputIndex++)
		if (Inputs[InputIndex].id.substring(0, Namespace.length) == Namespace)
			Form_Values[Inputs[InputIndex].id] = Inputs[InputIndex].value;
	
	// Gather parameters from namespaced content editble elements.
	var Inputs = document.getElementsByClassName("Inline_Text");
	for (var InputIndex = 0; InputIndex < Inputs.length; InputIndex++)
	{
		if (Inputs[InputIndex].id.substring(0, Namespace.length) == Namespace)
			Form_Values[Inputs[InputIndex].id] = Jelly.jQuery(Inputs[InputIndex]).html();
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Post Values...");
		Jelly.Debug.Log(Form_Values);
		Jelly.Debug.End_Group("");
	}

	return Form_Values;
};

Jelly.Connections = 
{
};

;

// Facebook initialization

window.fbAsyncInit = function() 
 	{
		FB.init(
				{
					appId      : '783700495040097',
					cookie     : true,  // enable cookies to allow the server to access the session
					xfbml      : true,  // parse social plugins on this page
					version    : 'v2.2' // use version 2.2
				}
			);
	};
	
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
  
  
Jelly.Connections.Facebook_Login = function(Parameters)
{
// 	console.log('entering');
// 	FB.getLoginStatus( function(Facebook_Response) 
// 			{	
// 				console.log(Facebook_Response);
// 				if (Facebook_Response.status == "connected")
// 				{
// 					FB.logout( function(Logout_Response)
// 							{
// 								Continue_Facebook_Login(Parameters);
// 							}
// 						);
// 				}
// 				else
// 					Continue_Facebook_Login(Parameters);
// 			}
// 		);
	
	var Continue_Facebook_Login = function (Parameters)
	{
// 		console.log('continuing');
		// Login 
		FB.login( function(Login_Response)
				{
// 					console.log('logged in');
					// Get account information
					FB.api('/me', function(API_Response) {
					
// 							console.log('registerring');
							// Localize account information
							var ID = API_Response["id"];
							var Email = API_Response["email"];
							var First_Name = API_Response["first_name"];
							var Last_Name = API_Response["last_name"];
							var Path_To_Profile_Photo = "http://graph.facebook.com/" + ID + "/picture?type=square";
							var Namespace = Parameters["Namespace"];
			
							// Save input values
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "Account_ID",
										Value: ID
									}
								);
								
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "Email_Address",
										Value: Email
									}
								);
								
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "First_Name",
										Value: First_Name
									}
								);
								
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "Last_Name",
										Value: Last_Name
									}
								);
								
							Jelly.Actions.Set_Input_Value(
									{
										Namespace: Namespace,
										Alias: "Path_To_Profile_Photo",
										Value: Path_To_Profile_Photo
									}
								);
							
							// Execute action.
							Jelly.Handlers.Call_Handler_For_Target({'Event': 'Execute', 'Target': document.getElementById(Namespace)});
						});
				}, 
				{scope:'public_profile,email'}
			);
	
	}
	
	Continue_Facebook_Login(Parameters);
};
