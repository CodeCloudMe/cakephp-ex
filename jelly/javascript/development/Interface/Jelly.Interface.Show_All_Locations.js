Jelly.Interface.Show_All_Locations = function(Parameters)
{	
	// Map
	// Zoom map to fit all markers
	var Debug = true && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Show all locations");
		console.log(Parameters);
	}

	// Get map & map markers
	var Map_Alias = Parameters["Map_Alias"];
	var Map_Object = Jelly.Interface.Maps[Map_Alias];
	var Map = Map_Object["Map"];
	var Map_Markers = Map_Object["Markers"];
	
	// Show all markers
	if (Map_Markers && Map_Markers.length)
	{
		var Default_Map_Zoom = 13;
		var Map_Markers_Group = new L.featureGroup(Map_Markers);	
		Map.fitBounds(Map_Markers_Group.getBounds(), {maxZoom: Default_Map_Zoom, padding: [20, 20]});	
	}
	
	// Show default zoom if no markers
	else
		Jelly.Interface.Show_Default_Location({Map_Alias: Map_Alias});
			
	if (Debug)
		Jelly.Debug.End_Group("Show all locations");
};