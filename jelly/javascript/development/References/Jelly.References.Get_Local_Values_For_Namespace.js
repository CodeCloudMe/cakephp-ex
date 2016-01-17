Jelly.References.Get_Local_Values_For_Namespace = function(Parameters)
{
	var Namespace = Parameters["Namespace"];
	
	//	TODO: this should be Jelly.Action....
	// Returns an array of keys & values from all inputs that match the namespace
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
		Jelly.Debug.Group("Get_Local_Values_For_Namespace" + " (" + Namespace + ")");
		
	// Instatate form value array
	var Form_Values =  {};
	
	var Namespace = Parameters["Namespace"];
	
	// Search through all input elements in document.
	// TODO jQuery, or another selector query, would make this code a lot more concise
	
	// Gather parameters from namespaced input elements.
	var Inputs = document.getElementsByTagName("input");
	for (var InputIndex = 0; InputIndex < Inputs.length; InputIndex++)
	{
		if (Inputs[InputIndex].id.substring(0, Namespace.length) == Namespace)
		{
			switch (Inputs[InputIndex].type)
			{
				// Convert checkbox values
				case "checkbox":
					if (Inputs[InputIndex].checked)
						Form_Values[Inputs[InputIndex].id] = "1";
					else
						Form_Values[Inputs[InputIndex].id] = "0";
					break;
				
				// Convert date/time values
				// TODO - date/time treatment copied w/o comments from Get_Input_Values_For_Action --- still to understand differentiation.
				case "hidden":
					var Input_Value_Element_ID = Inputs[InputIndex].id;
					var Input_Value_Type_Element_ID = Input_Value_Element_ID + "_Type";
					var Input_Value_Type_Element = document.getElementById(Input_Value_Type_Element_ID);
					if (Input_Value_Type_Element)
					{
						var Input_Value_Type = Input_Value_Type_Element.value;
						switch (Input_Value_Type.toLowerCase())
						{	
							// Specila case
							case "date":
							case "date_time":
							case "time":
								// Get input namespace
								var Input_Namespace = Input_Value_Element_ID.substring(0, Input_Value_Element_ID.length - (Jelly.Namespace_Delimiter + "Value").length);
								
								// Clean inputs
								switch (Input_Value_Type.toLowerCase())
								{
									case "date":
										Jelly.Interface.Clean_Date_Input(Input_Namespace);
										break;
									case "time":
										Jelly.Interface.Clean_Time_Input(Input_Namespace);
										break;
									case "date_time":
										Jelly.Interface.Clean_Date_Input(Input_Namespace);
										Jelly.Interface.Clean_Time_Input(Input_Namespace);
										break;
								}
								
								// Refresh date value
								Jelly.Interface.Refresh_Date_Value(Input_Namespace);								
						}
					}
					// Set hidden value
					Form_Values[Inputs[InputIndex].id] = Inputs[InputIndex].value;
					break;
				default:
					Form_Values[Inputs[InputIndex].id] = Inputs[InputIndex].value;
					break;
			}
		}
	}
		
	// Gather parameters from namespaced select elements.
	var Inputs = document.getElementsByTagName("select");
	for (var InputIndex = 0; InputIndex < Inputs.length; InputIndex++)
		if (Inputs[InputIndex].id.substring(0, Namespace.length) == Namespace)
			Form_Values[Inputs[InputIndex].id] = Inputs[InputIndex].value;
	
	// Gather parameters from namespaced textarea elements.
	var Inputs = document.getElementsByTagName("textarea");
	for (var InputIndex = 0; InputIndex < Inputs.length; InputIndex++)
		if (Inputs[InputIndex].id.substring(0, Namespace.length) == Namespace)
			Form_Values[Inputs[InputIndex].id] = Inputs[InputIndex].value;
	
	// Gather parameters from namespaced content editble elements.
	var Inputs = document.getElementsByClassName("Inline_Text");
	for (var InputIndex = 0; InputIndex < Inputs.length; InputIndex++)
	{
		if (Inputs[InputIndex].id.substring(0, Namespace.length) == Namespace)
			Form_Values[Inputs[InputIndex].id] = Jelly.jQuery(Inputs[InputIndex]).html();
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Post Values...");
		Jelly.Debug.Log(Form_Values);
		Jelly.Debug.End_Group("");
	}

	return Form_Values;
};