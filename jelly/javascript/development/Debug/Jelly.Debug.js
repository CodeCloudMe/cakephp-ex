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

head.load(
		Jelly_Debug_Javascript_Path + '/' + 'Jelly.Debug.Display_Error.js',
		Jelly_Debug_Javascript_Path + '/' + 'Jelly.Debug.Log.js',
		Jelly_Debug_Javascript_Path + '/' + 'Jelly.Debug.Dir.js',
		Jelly_Debug_Javascript_Path + '/' + 'Jelly.Debug.Group.js',
		Jelly_Debug_Javascript_Path + '/' + 'Jelly.Debug.End_Group.js',
		Jelly_Debug_Javascript_Path + '/' + 'Jelly.Debug.Print_All_Handlers.js',
		Jelly_Debug_Javascript_Path + '/' + 'Jelly.Debug.Print_All_Actions.js'
	);