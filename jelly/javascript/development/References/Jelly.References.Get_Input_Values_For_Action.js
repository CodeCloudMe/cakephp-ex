Jelly.References.Get_Input_Values_For_Action = function(Parameters)
{
	var Namespace = Parameters["Namespace"];
	
	//	TODO: this should be Jelly.Action....
	// Returns an array of keys & values from all inputs that match the namespace
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
		Jelly.Debug.Group("Get_Input_Values_For_Action" + " (" + Namespace + ")");
		
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	
	// Require action reference
	if (!Action_Reference)
	{
		Jelly.Debug.Log("Trying to get values for an action reference by namespace, but no reference found for the namespace");
		return;
	}
	
	// Verify action reference type.
	if (Action_Reference["Type_Alias"] != "Action" && Action_Reference["Type_Alias"] !=  "Type_Action")
	{
		Jelly.Debug.Log("Trying to get values for an action reference by namespace, but reference is incorrect type");
		return;
	}
		
	// Instatate form value array
	var Form_Values =  {};
	
	// Search through all input elements in document.
	// TODO jQuery, or another selector query, would make this code a lot more concise
	// TODO - hopefully we don't have to suppress the following input names anymore: Action, Edit_Item, Edit_Type	
	var Inputs = Action_Reference.Inputs;
	for (Input_Index in Inputs)
	{
		if (Inputs.hasOwnProperty(Input_Index))
		{
			if (!Inputs[Input_Index]["Sensitive"] || Parameters["Include_Sensitive"])
			{
				var Input_Element = Inputs[Input_Index]["Element"];
		
				if (Debug)
				{
					Jelly.Debug.Log("Gathering Input Element with ID: " + Input_Element.id);
					Jelly.Debug.Log(Input_Element);
				}
			
				// Set input index by input id or input name
				var Input_Element_Index = Input_Element.getAttribute("name");
				
				// Set up input value
				var Input_Element_Value;

				// Convert input element value for special cases...
				switch (Input_Element.tagName)
				{
					case "INPUT":
					{
						switch (Input_Element.type)
						{
							// Convert checkbox values
							case "checkbox":
							{
								if (Input_Element.checked)
									Input_Element_Value = "1";
								else
									Input_Element_Value = "0";
								break;
							}
							
							// Convert or calculate hidden input values
							case "hidden":
							{
								// Set input value
								Input_Element_Value = Input_Element.value;
								
								var Input_Value_Element_ID = Input_Element.id;
								var Input_Value_Type_Element_ID = Input_Value_Element_ID + "_Type";
								var Input_Value_Type_Element = document.getElementById(Input_Value_Type_Element_ID);
								if (Input_Value_Type_Element)
								{	
									var Input_Value_Type = Input_Value_Type_Element.value;
									switch (Input_Value_Type.toLowerCase())
									{
										// Handle date/time input values.
										case "date":
										case "date_time":
										case "time":
										{
											Debug = false;
										
											// Get input namespace
											var Input_Namespace = Input_Value_Element_ID.substring(0, Input_Value_Element_ID.length - (Jelly.Namespace_Delimiter + "Value").length);
										
											if (Debug)
											{
												Jelly.Debug.Log(Input_Element.id);
												Jelly.Debug.Log(Input_Namespace);
											}
										
											// Clean inputs
											// TODO - this isn't exactly the right place. It's for when you submit while you're still in the input element, say via the enter key, so that you don't lose your value then.  The right place might be on the enter key handler.
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
											// TODO - the only reason this input exists is for this function right here. Makes me think about inputs, and perhaps registering some without an element at all, now that we register them?
											Jelly.Interface.Refresh_Date_Value(Input_Namespace);
										
											// Set date value
											Input_Element_Value = Input_Element.value;
										
											break;
										}
									}
								}
								break;
							}
							default:
							{
								// Set input value
								Input_Element_Value = Input_Element.value;
								
								// TODO - this can be thought through...
								// TODO - ok it's more hodge-podge now already.
								if (!Parameters["Include_Sensitive"])
								{
									if (Inputs[Input_Index]["Clear_On_Execute"])
										Input_Element.value = "";
									
									if (Inputs[Input_Index]["Blur_On_Execute"])
										Input_Element.blur();
								}
								
								break;
							}
						}
						break;
					}
					case "DIV":
					{
						Input_Element_Value = Jelly.jQuery(Input_Element).html();
						break;
					}
					case "TEXTAREA":
					{
						// Set input value
						Input_Element_Value = Input_Element.value;
						break;
					}
				}
		
				// Store input value by index into form values array
				Form_Values[Input_Element_Index] = Input_Element_Value;
			}
		}
	}
	
	if (Debug)
	{
		Jelly.Debug.Log("Post Values...");
		Jelly.Debug.Log(Form_Values);
		Jelly.Debug.End_Group("");
	}

	return Form_Values;
};