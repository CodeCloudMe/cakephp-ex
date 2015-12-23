Jelly.Handlers = 
{
	// Initialize blank instance variables.
	
	// TODO: label these.
	Default_Target: null,	
	
	// Refresh Handlers
	Refresh_Handlers: [],
}

head.load(
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Document_Click.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Document_Scroll.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Document_Mouse_Move.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Document_Mouse_Down.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Document_Key_Down.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Document_Context_Click.js',
		
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Cancel_Event.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Set_Default_Target.js',
		
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Handle_Link_From_Click.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Visit_Link.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Change_URL.js',

		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Register_Handler.js',
		Jelly_Handlers_Javascript_Path + '/' + 'Jelly.Handlers.Call_Handler_For_Target.js'
	);
