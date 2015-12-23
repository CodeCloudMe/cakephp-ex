Jelly.Interface.Clean_Date_Input = function(Namespace)
{
	// Cleans various date input into a standard  formatted date value
	// TODO - wonder if it should just take the input element
	var Debug = false && Jelly.Debug.Debug_Mode;	
	
	if (Debug)
	{
		Jelly.Debug.Group("Clean_Date_Input" + " (" + Namespace + ")");
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
	
	// Leave blank input values unchanged
	if (Date_Input_Value)
	{
		// Convert input value into standardized value... 
	
		// Trim value
		var Date_Input_Value = Date_Input_Value.replace(/^\s+|\s+$/g,"");
	
		// Create date object
		var Value_Date = new Date();

		// Set date object value according by keywords or default conversation
		switch (Date_Input_Value.toLowerCase())
		{
			case "":
			case "today":
				// Keep existing date value.
				break;
			
			case "tomorrow":
				// Create tomorrow date value
				Value_Date.setTime(Value_Date.getTime() + 24 * 60 * 60 * 1000);
				break;
			
			case "yesterday":
				// Create yesterday date value
				Value_Date.setTime(Value_Date.getTime() - 24 * 60 * 60 * 1000);
				break;
			
			default:
				// Parse date from input value.
				var Value_Date_Parse_Result = Date.parse(Date_Input_Value);
			
				if (Debug)
				{
					Jelly.Debug.Log(Date_Input_Value);
					Jelly.Debug.Log(Value_Date_Parse_Result);
				}
			
				// Validate input value
				if (isNaN(Value_Date_Parse_Result))
				{
					// TODO - handle error more deliberately 			
				}
			
				// Create date value
				else
					Value_Date = new Date(Value_Date_Parse_Result);
				break;
		}
	
		// Update displayed value
		Date_Input_Element.value = (Value_Date.getMonth() + 1) + "/" + (Value_Date.getDate()) + "/" + (Value_Date.getFullYear());
	}
	
	// If the date of a date time is blank, clear the time too, if it exists.
	else if (Time_Input_Element)
		Time_Input_Element.value = "";
	
	if (Debug)
		Jelly.Debug.End_Group("Highlight Clean_Date_Input");
};