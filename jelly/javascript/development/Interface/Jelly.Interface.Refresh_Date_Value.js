Jelly.Interface.Refresh_Date_Value = function(Namespace)
{
	// Handle date input	
	var SQL_Formatted_Date_Value = "";
	
	// Get inputs
	// TODO - lol
	var Date_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Date_Input");

	if (Date_Input_Element)
	{
		var Date_Input_Value = Date_Input_Element.value;
		
		if (Date_Input_Value)
		{
			Date_Input_Value_Date = new Date(Date_Input_Value);

			// Convert date to sql formatted value...
			var Formatted_Year, Formatted_Month, Formatted_Date;
			
			// Year
			Formatted_Year = Date_Input_Value_Date.getFullYear();
	
			// Month
			if ((Date_Input_Value_Date.getMonth() + 1) < 10)
				Formatted_Month = "0" + (Date_Input_Value_Date.getMonth() + 1);
			else
				Formatted_Month = (Date_Input_Value_Date.getMonth() + 1);
	
			// Day
			if ((Date_Input_Value_Date.getDate()) < 10)
				Formatted_Date = "0" + (Date_Input_Value_Date.getDate());
			else
				Formatted_Date = (Date_Input_Value_Date.getDate());
		
			SQL_Formatted_Date_Value = Formatted_Year + "-" + Formatted_Month + "-" + Formatted_Date;
		}
	}
	
	// Handle time input	
	var SQL_Formatted_Time_Value = "";
	
	// Get inputs
	// TODO - lol
	var Time_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Time_Input");

	if (Time_Input_Element)
	{
		var Time_Input_Value = Time_Input_Element.value;
		
		if (Time_Input_Value)
		{
			// Explicit ... 
			Time_Input_Value_Date = new Date();
			
			// Parse and trim am pm 
			// TODO - old parsing logic.
			var AM_PM;
			if (Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase() == "am" || Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase() == "pm")
			{
				AM_PM = Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase();
				Time_Input_Value = Time_Input_Value.substring(0, Time_Input_Value.length - 2);
			}
			else if (Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() == "a" || Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() == "p")
			{
				AM_PM = Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() + "m";
				Time_Input_Value = Time_Input_Value.substring(0, Input_Value.length - 1);
			}
			
			// Get hour/minute
			var Input_Parts = Time_Input_Value.split(":");
			var Input_Hour_Value = parseInt(Input_Parts[0]);
			var Input_Minute_Value = parseInt(Input_Parts[1]);
					
			// Adjust hours for AM/PM
			if (AM_PM == "am" && Input_Hour_Value == 12)
				Input_Hour_Value = 0;
			else if (AM_PM == "pm" && Input_Hour_Value < 12)
				Input_Hour_Value += 12;
					
			// Setup date value
			Time_Input_Value_Date.setHours(Input_Hour_Value);
			Time_Input_Value_Date.setMinutes(Input_Minute_Value);
			
			// Convert time to sql formatted value...
			var Formatted_Hours, Formatted_Minutes;
		
			// Hours
			if (Time_Input_Value_Date.getHours().toString().length == 1)
				Formatted_Hours = "0" + Time_Input_Value_Date.getHours().toString();
			else
				Formatted_Hours = Time_Input_Value_Date.getHours().toString();
			
			// Minutes
			if (Time_Input_Value_Date.getMinutes().toString().length == 1)
				Formatted_Minutes = "0" +  Time_Input_Value_Date.getMinutes().toString();
			else
				Formatted_Minutes = Time_Input_Value_Date.getMinutes().toString();
			
			SQL_Formatted_Time_Value = Formatted_Hours + ":" + Formatted_Minutes + ":" + "00";
		}
	}
	
	// Join non-empty date and time values with a space
	var SQL_Formatted_Value = [SQL_Formatted_Date_Value, SQL_Formatted_Time_Value].filter(function (Value) {return Value;}).join(' ');
	
	// Store SQL value in input.
	var Form_Input_Element = document.getElementById(Namespace + '_Value');
	Form_Input_Element.value = SQL_Formatted_Value;
}

