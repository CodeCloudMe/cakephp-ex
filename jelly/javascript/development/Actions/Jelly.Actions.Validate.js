Jelly.Actions.Validate = function(Parameters)
{
	// Runs all registered validation scripts for the action, returns overall validation value.
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
		Jelly.Debug.Group("Validate" + " (" + Parameters["Namespace"] + ")");
		
	// Get callback
	var Return = Parameters["Return"];

	// Get action namespace
	var Action_Namespace = Parameters["Namespace"];
		
	// Require action reference
	var Action_Element = document.getElementById(Action_Namespace);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		Return(Validation_Error = true);

	// Get action values
	var Action_Sensitive_Values = Jelly.References.Get_Input_Values_For_Action(
			{
				"Namespace": Action_Namespace,
				"Include_Sensitive": true
			}
		);
	
	// Run all validations in parallel...
	
	// Get validations
	var Validations = Action_Reference.Validations;
	
	// Get validation keys.
	var Validation_Keys = Object.keys(Validations);
	
	// For each validation, perform validation and return outcome via Return callback
	async.each(
		Validation_Keys,
		
		// Perform validation...
		function (Validation_Key, Return)
		{	
			// Get this validation
			var Validation = Validations[Validation_Key];
			
			// For an asynchronous, functional validation, call it with a callback function, and it takes care of validation & feedback.
			if (Validation["Function"] && Validation["Function"].length == 2)
			{	
				Validation["Function"].call(this, Action_Sensitive_Values, Return);
			}
			
			// For synchronous functions, handle callback manually.
			else
			{	
				var Validation_Result = true; 
				
				// For a synchronous, functional validation, call it and it takes care of validation & feedback.
				if (Validation["Function"])
					Validation_Result = Validation["Function"].call(this, Action_Sensitive_Values);
					
				// For named validation behaviors, handle validation and display any errors. 
				else
				{
					var Input_Names = Validation["Inputs"];

					// Perform named validation.
					switch (Validation["Behavior"].toLowerCase())
					{
						case "equals":
							for (var Input_Name_Index = 1; Input_Name_Index < Input_Names.length; Input_Name_Index++)
								if (Action_Sensitive_Values[Input_Names[Input_Name_Index]] != Action_Sensitive_Values[Input_Names[Input_Name_Index - 1]])
									Validation_Result = false;
							break;
					
						case "populated":
							for (var Input_Name_Index = 0; Input_Name_Index < Input_Names.length; Input_Name_Index++)
								if (Action_Sensitive_Values[Input_Names[Input_Name_Index]] == "")
									Validation_Result = false;
							break;
					}
			
					// If validation fails, display error if provided
					if (!Validation_Result)
					{	
						// Get error from validation or default error.
						if (Validation["Error"])
							var Error_Content = Validation["Error"];
						else
							var Error_Content = "Error";
			
						// Create result parameters with action and error content.
						var Result_Parameters = {
								Namespace: Action_Namespace,
								Content: Error_Content
							}
			
						// If validation is for a single input, display the error for that specific input, otherwise, display the error for the whole action.
						if (Input_Names.length == 1)
							Result_Parameters["Input_Alias"] = Input_Names[0];
					
						// Display the error.
						Jelly.Actions.Show_Result(Result_Parameters);
					}
				}
				
				// Call callback function with error if exists.
				var Validation_Error = null;
				if (!Validation_Result)
					Validation_Error = true;
				Return(Validation_Error);
			}
		},
		
		// Call callback function with error if it exists
		function (Validation_Error)
		{
			if (Validation_Error)
			{
				// TODO - this was a total guess as to placement
				// Re-enable action execution buttons if the validation didn't pass
				Jelly.jQuery('#' + Action_Namespace + ' .Jelly_Action_Execute').removeClass('Executing');
			}

			// Call callback function with error if it exists
			Return(Validation_Error);
			
			// TODO - Hahahah look at that trickily named return above gotcha didn't i
			if (Debug)
				Jelly.Debug.End_Group("");
		}
	);
};