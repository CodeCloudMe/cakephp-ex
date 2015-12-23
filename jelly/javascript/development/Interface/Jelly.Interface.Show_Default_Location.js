Jelly.Interface.Show_Default_Location = function(Parameters)
{	
	// Map
	// Zoom map to fit all markers
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Show Default Location");
		console.log(Parameters);
	}

	// Defaults
	// THIS IS NEW INC
	var Default_Location = 	[40.72213, -73.99277];
	var Default_Map_Zoom = 13;

	// Get map & map markers
	var Map_Alias = Parameters["Map_Alias"];
	var Map_Object = Jelly.Interface.Maps[Map_Alias];
	var Map = Map_Object["Map"];
			
	// Set view
	Map.setView(Default_Location, Default_Map_Zoom);
			
	if (Debug)
		Jelly.Debug.End_Group("Show Default Location");
};