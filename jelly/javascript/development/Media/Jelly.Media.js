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

head.load(
		Jelly_Media_Javascript_Path + '/' + 'Jelly.Media.Play_Pause_Global_Sound.js',
		Jelly_Media_Javascript_Path + '/' + 'Jelly.Media.Play_Global_Sound.js',
		Jelly_Media_Javascript_Path + '/' + 'Jelly.Media.Pause_Global_Sound.js',
		Jelly_Media_Javascript_Path + '/' + 'Jelly.Media.Register_Global_Sound_Event.js',
		Jelly_Media_Javascript_Path + '/' + 'Jelly.Media.Register_Media_Player.js'
 );	