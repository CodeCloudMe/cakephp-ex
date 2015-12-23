Jelly.Payments.Update_Ticket_Forecast = function(Parameters)
{	
	// TODO - Notate Description & Inputs Up here
	
	// Get values
	var Namespace = Parameters['Namespace'];
	console.log(Namespace);
	var Ticket_Price = Jelly.jQuery('#' + Namespace + ' ' + '.Price .Input input').val();
	var Capacity = Jelly.jQuery('#' + Namespace + ' ' + '.Capacity .Input input').val();
	var Volunteers = Jelly.jQuery('#' + Namespace + ' ' + '.Volunteers .Input input').is(':checked');
	var Donations = Jelly.jQuery('#' + Namespace + ' ' + '.Donations .Input input').is(':checked');
	
	var Forecast = "";
	
	if (Ticket_Price && Capacity && Ticket_Price != 0 && Capacity != 0)
	{
		Forecast = '$' + '<span class="Number">' + Math.round(Ticket_Price * Capacity).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</span>' + ' ' + 'at sellout';
		
		if (Donations)
			Forecast += ' + ' + '$' + '<span class="Number">' + Math.round(Ticket_Price * Capacity * .15).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '</span>' + ' ' + 'in donations';
		
		if (Volunteers)
		{
			Forecast += ' + ' + '<span class="Number">' + Math.round(Capacity * .05) + '</span>' + ' ' + 'volunteer';
			if (Math.round(Capacity * .05) != 1) Forecast += 's';
		}
	}
				
	var Forecast_Element = Jelly.jQuery('#' + Namespace + ' ' + '.Forecast');
	
	if (Forecast)
	{
		var Forecast_Value_Element = Jelly.jQuery('#' + Namespace + ' ' + '.Forecast .Value');
		Forecast_Value_Element.html(Forecast);	

		Forecast_Element.addClass('Show');

		if (Donations || Volunteers)
			Forecast_Element.addClass('Show_Hint');
		else
			Forecast_Element.removeClass('Show_Hint');
	}
	else
	{
		Forecast_Element.removeClass('Show')
		Forecast_Element.removeClass('Show_Hint')
	}
};