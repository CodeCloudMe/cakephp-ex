Jelly.Interface.Check_Event_Protection = function(Event_Name)
{
	// TODO - rewrite comments
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	return (!Jelly.Interface.Event_Protection.hasOwnProperty(Event_Name));
};