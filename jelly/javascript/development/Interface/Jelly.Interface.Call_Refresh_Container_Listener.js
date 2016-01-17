// Calls reset container script
Jelly.Interface.Call_Refresh_Container_Listener = function(Parameters)
{
	if (Jelly.Interface.Refresh_Container_Listener)
		Jelly.Interface.Refresh_Container_Listener.call();
};