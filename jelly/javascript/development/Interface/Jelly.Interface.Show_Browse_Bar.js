Jelly.Interface.Show_Browse_Bar = function(Parameters)
{
	if (!Jelly.jQuery('#Jelly_Browse_Bar').hasClass('Visible'))
	{
		// Open Sidebar
		Jelly.jQuery('#Jelly_Browse_Bar').addClass('Visible');
		Jelly.jQuery('#Jelly_Content').addClass('Browse_Bar_Visible');
		Jelly.jQuery('#Jelly_Sidebar').addClass('Browse_Bar_Visible');
	}
	Jelly.jQuery('#Jelly_Browse_Bar').html('');
	Jelly.References.Fill({Element: Jelly.jQuery('#Jelly_Browse_Bar').get(0), URL: '/Type/' + Parameters['Type_Alias'] + '/Browse_Bar', Create_Reference: true});
};
