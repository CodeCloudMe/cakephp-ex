Jelly.Media.Pause_Global_Sound = function()
{
	if (!Jelly.Media.Global_Audio_Player_Element)
		return;

	// Pause
	Jelly.Media.Global_Audio_Player_Element.pause();
};