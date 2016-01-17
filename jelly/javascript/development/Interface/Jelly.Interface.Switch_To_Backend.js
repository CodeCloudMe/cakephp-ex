Jelly.Interface.Switch_To_Backend = function(Parameters)
{
	if (!Jelly.jQuery("#Jelly_Content").hasClass("Backend"))
	{
		if (Parameters["Clear"])
			Jelly.jQuery("[data-kind*='Container']").empty();
		Jelly.jQuery("#Jelly_Content").addClass("Backend");
	}
}