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