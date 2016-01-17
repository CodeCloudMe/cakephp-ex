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

head.load(		
		// Functions
		Jelly_Javascript_Path + '/' + 'Jelly.Start.js',
		Jelly_Javascript_Path + '/' + 'Jelly.Add_Global_Event_Listeners.js',
		Jelly_Javascript_Path + '/' + 'Jelly.Watch_Address_Bar.js',
		
		// Groups
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.js', 
		Jelly_References_Javascript_Path + '/' + 'Jelly.References.js', 
		Jelly_Actions_Javascript_Path + '/' + 'Jelly.Actions.js', 
		Jelly_Utilities_Javascript_Path + '/' + 'Jelly.Utilities.js', 
		Jelly_AJAX_Javascript_Path + '/' + 'Jelly.AJAX.js', 
		Jelly_Interface_Javascript_Path + '/' + 'Jelly.Interface.js', 
		Jelly_Media_Javascript_Path + '/' + 'Jelly.Media.js', 
		Jelly_Debug_Javascript_Path + '/' + 'Jelly.Debug.js',
		Jelly_URL_Javascript_Path + '/' + 'Jelly.URL.js',
		Jelly_Connections_Javascript_Path + '/' + 'Jelly.Connections.js',
		Jelly_Payments_Javascript_Path + '/' + 'Jelly.Payments.js'
	);