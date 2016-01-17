Jelly.Interface.Hide_Browse_Bar = function(Parameters)
{
	if (Jelly.jQuery('#Jelly_Browse_Bar').hasClass('Visible'))
	{
		// Open Sidebar
		Jelly.jQuery('#Jelly_Browse_Bar').removeClass('Visible');
		Jelly.jQuery('#Jelly_Content').removeClass('Browse_Bar_Visible');
		Jelly.jQuery('#Jelly_Sidebar').removeClass('Browse_Bar_Visible');
	}
};
