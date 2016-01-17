Jelly.Interface.Show_Sidebar = function(Parameters)
{
	Jelly.jQuery('#Jelly_Sidebar').addClass('Visible');
	Jelly.jQuery('#Jelly_Wrapper').addClass('Admin_Mode');
	Jelly.References.Trigger_Refresh({Kind: 'Element', Element: Jelly.jQuery('#Jelly_Sidebar \[data-kind=Item\]').get(0)});
	Jelly.jQuery('#Jelly_Content').addClass('Sidebar_Visible');
};
