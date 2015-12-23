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