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

head.load(
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Calculate_Bounds.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Position_Container.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Fade_Out_And_Remove.js',

		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Set_Refresh_Container_Listener.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Call_Refresh_Container_Listener.js',
		
		//  TODO: Not sure if necessary.
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Timeout.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Set_Event_Protection.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Check_Event_Protection.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Bubble_Event_Protection.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Catch_Webkit_Context_Click_Bug.js',
			
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_Lightbox.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Hide_Lightbox.js',
		
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Create_Window.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Close_Top_Window.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Close_Window.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Focus_Top_Window.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Lock.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Unlock.js',

		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Create_Menu.js',		
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Close_Menus.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_Context_Menu.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Highlight_Menu_Item.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Focus_First_Menu_Item.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Refresh_Browse_Menu_Table.js',
		
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Generate_Map.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Create_Location.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_Default_Location.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_All_Locations.js',		

		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Create_Global_Controls_Element.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Generate_Browser_Control.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Generate_Loading_Indicator.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Generate_Lightbox.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Generate_Window.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Generate_Menu.js',
		
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Focus_First_Control.js',		
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Insert_Return_With_Indented_Text.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Insert_Tab.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Remove_Tab.js',
		
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Highlight_Namespace.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Hide_Highlights.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_Loading_Indicator.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Hide_Loading_Indicator.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_Loading_Overlay.js',
		
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Setup_Date_Selector.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Generate_Date_Selector.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Set_Date.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Set_Time.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Clean_Date_Input.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Clean_Time_Input.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Refresh_Date_Value.js',
		
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Position_Cards.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Create_Inspector.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Hide_Inspector.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Inspect.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_Toolbar.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Hide_Toolbar.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_Sidebar.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Hide_Sidebar.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Show_Browse_Bar.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Hide_Browse_Bar.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Manage.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Manage_Container.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Close_Manage.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Switch_To_Backend.js',
		Jelly_Interface_Javascript_Path + '/' +  'Jelly.Interface.Switch_To_Frontend.js'
	);