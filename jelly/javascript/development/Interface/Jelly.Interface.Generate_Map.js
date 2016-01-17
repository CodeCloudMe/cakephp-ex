Jelly.Interface.Generate_Map = function(Parameters)
{	
	// Create a named map, with a default tile server, and a default location
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Generate_Map");
		console.log(Parameters);
	}
	
	// Get defaults...	
	var Default_Tile_Server_Name = "OpenStreetMap";
	var Default_Tile_Server_URL = "http://{s}.tile.osm.org/{z}/{x}/{y}.png";
	var Default_Tile_Server_Attribution_URL = "http://www.openstreetmap.org/copyright";
	// TODO - not exactly what they wanted, but looks better.
	var Default_Tile_Server_Attribution = "&copy; <a href=\"" + Default_Tile_Server_Attribution_URL + "\">" + Default_Tile_Server_Name + "</a>";
	
	// Get parameters...
	var Map_Alias = Parameters['Alias'];
	
	// Create map...
	var Map = new L.map(Map_Alias, {scrollWheelZoom: false});
	
	// Refresh icons 
	// TODO - generalize.
	Map.on('popupopen', function() {
			var iconic = IconicJS();
			iconic.inject('img.iconic');
		});
		
	// Locate user if specified.
	if (Parameters["Locate_User"])
	{			
		// Create home marker icon
		var Home_Marker_Icon = L.AwesomeMarkers.icon({
			prefix: 'fa', 	
			iconColor: 'white',
			markerColor: 'red',
			extraClasses: 'fa-2x',
			icon: 'home'
		  });
		  
		// Create marker on locate
		Map.on('locationfound',  function (e)
				{
					// Create marker
					var Home_Marker = L.marker(e.latlng, {icon: Home_Marker_Icon}).addTo(Map);
					
					// Store marker
					Jelly.Interface.Maps[Map_Alias]["Markers"].push(Home_Marker);
										
					// Create accuracy radius
					// TODO 3x'd it to be an accuracy halo
					var radius = (e.accuracy / 2) * 3 ;
					L.circle(e.latlng, radius).addTo(Map);
										
					// Recalibrate view
					Jelly.Interface.Show_All_Locations({Map_Alias: Map_Alias});
				}
			);

		// Locate user without moving view.
		var Default_Map_Zoom = 13;
		Map.locate({setView: false, maxZoom: Default_Map_Zoom});
	}
	
	// Add tiles	
	var Tile_Layer = new L.tileLayer(Default_Tile_Server_URL, {attribution: Default_Tile_Server_Attribution}
		).addTo(Map);
		
	// Store map
	var Map_Object = {"Map": Map};
	Jelly.Interface.Maps[Map_Alias] = Map_Object;
			
	if (Debug)
		Jelly.Debug.End_Group("Generate_Map");
};