Jelly.Interface.Set_Event_Protection = function(Event_Name)
{
	// TODO - rewrite comments
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Jelly.Interface.Event_Protection[Event_Name])
		window.clearTimeout(Jelly.Interface.Event_Protection[Event_Name]);
	
	Jelly.Interface.Event_Protection[Event_Name] = 
		Jelly.Interface.Timeout( 
				function() 
					{
						if (Debug)
							Jelly.Debug.Log("Clear Event Protection:'" + Event_Name+ "'"); 

						// Delete event protection from event protection list.
						delete Jelly.Interface.Event_Protection[Event_Name];
					}, 
					
				Jelly.Interface.Event_Protection_Period
			);
};