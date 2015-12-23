Jelly.Interface.Switch_To_Frontend = function(Parameters)
{
	if (Jelly.jQuery("#Jelly_Content").hasClass("Backend"))
	{
		if (Parameters["Clear"])
			Jelly.jQuery("[data-kind*='Container']").empty();
		Jelly.jQuery("#Jelly_Content").removeClass("Backend");
	}
}

