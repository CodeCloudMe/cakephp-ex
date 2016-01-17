Jelly.Interface.Clean_Time_Input = function(Namespace)
{
	// Cleans various time input into a standard formatted time value
	// TODO - wonder if it should just take the input element
	var Debug = false && Jelly.Debug.Debug_Mode;	
	
	if (Debug)
	{
		Jelly.Debug.Group("Clean_Time_Input" + " (" + Namespace + ")");
	}
	
	// Get input
	// TODO - lol
	var Time_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Time_Input");
	var Date_Input_Element = document.getElementById(Namespace + Jelly.Namespace_Delimiter + "Date_Input");
	var Time_Input_Value;
	var Date_Input_Value;
	
	// Get input value
	if (Time_Input_Element)
		Time_Input_Value = Time_Input_Element.value;
	if (Date_Input_Element)
		 Date_Input_Value = Date_Input_Element.value;
	
	// If time and corresponding date (if exists) are blank, leave blank.
	if (Time_Input_Value || Date_Input_Value)
	{
		// Convert input value into standardized value... 

		// Trim input
		var Time_Input_Value = Time_Input_Value.replace(/^\s+|\s+$/g,"");
	
		// Create date object
		var Value_Date = new Date();
	
		// Hard-round seconds, milliseconds down.
		Value_Date.setSeconds(0);
		Value_Date.setMilliseconds(0);
	
		switch (Time_Input_Value.toLowerCase())
		{
			case "now":
				// Keep existing date value.
				break;
			
			case "noon":
				// Create noon date value
				Value_Date.setHours(12);
				Value_Date.setMinutes(0);
				break;
			
			case "midnight":
				// Create midnight date value		
				Value_Date.setHours(0);
				Value_Date.setMinutes(0);
				break;
			
			default:
				// Explicit ... 
			
				// Parse and trim am pm 
				// TODO - This is old parsing code...
				var AM_PM;
				if (Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase() == "am" || Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase() == "pm")
				{
					AM_PM = Time_Input_Value.substring(Time_Input_Value.length - 2).toLowerCase();
					Time_Input_Value = Time_Input_Value.substring(0, Time_Input_Value.length - 2);
				}
				else if (Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() == "a" || Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() == "p")
				{
					AM_PM = Time_Input_Value.substring(Time_Input_Value.length - 1).toLowerCase() + "m";
					Time_Input_Value = Time_Input_Value.substring(0, Time_Input_Value.length - 1);
				}
				else
				{
					// Get am pm from current time.
					if (Value_Date.getHours() < 12)
						AM_PM = "am";
					else
						AM_PM = "pm";
				}
			
				// Get hour/minute
				var Input_Parts = Time_Input_Value.split(":");
				if (Input_Parts.length > 0)
				{
					Input_Hour_Value = parseInt(Input_Parts[0]);
				
					// TODO - too ballsy
					if (isNaN(Input_Hour_Value))
						break;
				}
				else
					var Input_Hour_Value = 0;

				if (Input_Parts.length > 1)
				{
					Input_Minute_Value = parseInt(Input_Parts[1]);

					// TODO - too ballsy
					if (isNaN(Input_Minute_Value))
						break;
				}
				else
					var Input_Minute_Value = 0;
					
				// Adjust hours for AM/PM
				if (AM_PM == "am" && Input_Hour_Value == 12)
					Input_Hour_Value = 0;
				else if (AM_PM == "pm" && Input_Hour_Value < 12)
					Input_Hour_Value += 12;
					
				// Setup date value
				Value_Date.setHours(Input_Hour_Value);
				Value_Date.setMinutes(Input_Minute_Value);
				break;

		}
	
		if (Debug)
			Jelly.Debug.Log(Value_Date);
	
		// Update display & form values...
		var Value_Hours, Value_AM_PM, Value_Minutes; 
	
		// Get display hours
		if (Value_Date.getHours() == 0)
			Display_Hours = "12";
		else if (Value_Date.getHours() > 12)
			Display_Hours = (Value_Date.getHours() - 12).toString();
		else
			Display_Hours = Value_Date.getHours().toString();
		
		// Get display minutes
		if (Value_Date.getMinutes().toString().length == 1)
			Display_Minutes = "0" +  Value_Date.getMinutes().toString();
		else
			Display_Minutes = Value_Date.getMinutes().toString();
		
		// Get display am pm
		if (Value_Date.getHours() < 12)
			Display_AM_PM = "am";
		else
			Display_AM_PM = "pm";
	
		// Update displayed value
		Time_Input_Element.value = Display_Hours + ":" + Display_Minutes + Display_AM_PM;		
	}
	
	if (Debug)
		Jelly.Debug.End_Group("Clean_Time_Input" + " (" + Namespace + ")");	
};