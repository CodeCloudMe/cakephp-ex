// TODO - don't know what this was intended to be.
Jelly.Interface.Unlock = function()
{	
	// Unlock interface
	Jelly.Interface.Is_Locked = false;
	
	// Allow scrolling
//	Jelly.jQuery('body').css('overflow', 'auto');
	Jelly.jQuery('#Jelly_Wrapper').css('overflow', 'scroll');
	
	// Handle callbacks
	// This is an array	
	for (Unlock_Function_Index in Jelly.Interface.On_Unlock)
	{
		Jelly.Interface.On_Unlock[Unlock_Function_Index].call();
	}
};