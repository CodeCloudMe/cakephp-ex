Jelly.Media.Register_Global_Sound_Event = function(Event, URL, Callback)
{
	if (!Jelly.Media.Global_Audio_URL_Callbacks[URL])
		Jelly.Media.Global_Audio_URL_Callbacks[URL] = {};
		
	if (!Jelly.Media.Global_Audio_URL_Callbacks[URL][Event])
		Jelly.Media.Global_Audio_URL_Callbacks[URL][Event] = [];
	
	Jelly.Media.Global_Audio_URL_Callbacks[URL][Event].push(Callback);
};