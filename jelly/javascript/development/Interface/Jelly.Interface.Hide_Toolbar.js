Jelly.Interface.Hide_Toolbar = function(Parameters)
{
	// Open Toolbar	
	Jelly.jQuery("#Jelly_Toolbar").removeClass("Visible");
	Jelly.jQuery("#Jelly_Main_Wrapper").removeClass("Toolbar_Visible");
	Jelly.jQuery("#Jelly_Inspector").removeClass("Toolbar_Visible");
};
