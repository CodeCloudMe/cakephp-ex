Jelly.Interface.Create_Location = function(Parameters)
{	
	// Create a named map, with a default tile server, and a default location
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create Map Marker");
		Jelly.Debug.Log(Parameters);
	}
	
	// Get Map
	var Map_Alias = Parameters["Map_Alias"];
	var Map_Object = Jelly.Interface.Maps[Map_Alias];
	var Map = Map_Object["Map"];
	
	//Create marker
	var Marker_Position = Parameters["Position"]
	var Marker_Content = Parameters["Content"];
	
	// Create marker icon parameters 
	// TODO - don't have to keep repeating this...
	var Icon_Parameters = {
		icon: "circle",
		prefix: 'fa', 	
		iconColor: 'white',
		markerColor: 'blue',
		extraClasses: 'fa-2x'
	  };	  

	// Use custom icon if specified.
	if (Parameters["Icon"])
		Icon_Parameters["icon"] = Parameters["Icon"];
		
	// Create marker icon
	var Marker_Icon = L.AwesomeMarkers.icon(Icon_Parameters);
	
	// Create marker
	var Marker = L.marker(Marker_Position, {icon: Marker_Icon}).addTo(Map)
		.bindPopup(Marker_Content,  {closeButton: false})
		.openPopup();
		
	// Store Marker
	if (!Map_Object["Markers"])
		Map_Object["Markers"] = [];		
	Map_Object["Markers"].push(Marker);

	
	// Show marker in view
	var Default_Map_Zoom = 13;
	if (Parameters["Show"])
		Map.setView(Marker_Position, Default_Map_Zoom);	
			
	if (Debug)
		Jelly.Debug.End_Group("Create Map Marker");
};