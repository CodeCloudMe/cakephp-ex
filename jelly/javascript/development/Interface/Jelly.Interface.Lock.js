// TODO - don't know what this was intended to be.
Jelly.Interface.Lock = function()
{	
	Jelly.Interface.Is_Locked = true;
	
	// Prevent scrolling
//	Jelly.jQuery('body').css('overflow', 'hidden');
	Jelly.jQuery('#Jelly_Wrapper').css('overflow', 'hidden');
};