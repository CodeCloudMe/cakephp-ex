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