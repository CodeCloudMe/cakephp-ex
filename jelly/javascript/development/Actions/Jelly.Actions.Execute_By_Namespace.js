Jelly.Actions.Execute_By_Namespace = function(Parameters)
{		
	// Populates action parameters for a namespace, and calls jelly.actions.execute with the parameters	
	var Debug = false && Jelly.Debug.Debug_Mode;

	if (Debug)
	{
		Jelly.Debug.Group("Execute_By_Namespace");
		Jelly.Debug.Log(Parameters);
	}
	
	// Initialize common values...

	// Get action namespace
	var Action_Namespace = Parameters["Namespace"];

	// Get action element
	var Action_Element = document.getElementById(Action_Namespace);

	// Get action reference
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);


	async.series([
		// Hide results...
		function (Return)
		{
			// Hide result elements.
			Jelly.Actions.Hide_Results({Namespace: Action_Namespace});
			
			// Disable action execution
			Jelly.jQuery('#' + Action_Namespace + ' .Jelly_Action_Execute').addClass('Executing');
	
			Return();
		},
	
		// Perform client-side validations for action....
		function (Return) 
		{
			// Perform validations 
			Jelly.Actions.Validate({"Namespace": Action_Namespace, "Return": Return});
		},

		// Execute action...
		function (Return) 
		{	
			// Get action type
			var Action_Type = Action_Reference["Type_Alias"];

			// Get action values
			var Action_Safe_Values = Jelly.References.Get_Input_Values_For_Action(
					{
						"Namespace": Action_Namespace,
						"Include_Sensitive": false
					}
				);
	
			// Instantiate action execute parameters
			var Action_Execute_Parameters = {};	

			// Set action parameters namespace
			Action_Execute_Parameters["Namespace"] = Action_Namespace;

			// Set action calling element 
			// TODO - should we just start doing these by reference, or are there times that we need just an element w/ no reference? 
			Action_Execute_Parameters["Calling_Element"] = Action_Element;

			// Set action values
			Action_Execute_Parameters["Values"] = Action_Safe_Values;

			// Copy action alias from reference
			Action_Execute_Parameters['Action'] = Action_Reference["Alias"];

			// Set action type
			Action_Execute_Parameters["Action_Type"] = Action_Type;

			// If this is a type action, copy the target information from the parameters
			if (Action_Type == "Type_Action")
			{
				// TODO - verify existence
				Action_Execute_Parameters['Target'] = Parameters['Target'];
				Action_Execute_Parameters['Target_Type'] = Parameters['Target_Type'];
			}

			// Execute this action.
			// TODO - perhaps Return can be integrated into execution later.
			Jelly.Actions.Execute(Action_Execute_Parameters);
			
			Return();
		}],
		
		// Debugging 
		function (err, results)
		{	
			if (Debug)
			{
				Jelly.Debug.Log(err);
				Jelly.Debug.Log(results);
				Jelly.Debug.End_Group("Execute_By_Namespace");				
			}
		}
		
	);
};