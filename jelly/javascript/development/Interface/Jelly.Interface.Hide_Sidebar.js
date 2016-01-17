Jelly.Interface.Hide_Sidebar = function(Parameters)
{
	Jelly.Interface.Hide_Browse_Bar();
	Jelly.jQuery('#Jelly_Sidebar').removeClass('Visible');
	Jelly.jQuery('#Jelly_Wrapper').removeClass('Admin_Mode');
	Jelly.jQuery('#Jelly_Content').removeClass('Sidebar_Visible');
};
