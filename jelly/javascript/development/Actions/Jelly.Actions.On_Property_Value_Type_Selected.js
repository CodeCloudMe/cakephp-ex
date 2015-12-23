Jelly.Actions.On_Property_Value_Type_Selected = function(Parameters)
{	
	// Get current action namespace
	var Current_Action_Namespace = Parameters["Namespace"];

	// Get value type
	var Value_Type_Alias = Parameters["Item"];
	
	// Get simple type status of selected value type
	var Selected_Type_Is_Simple_Type = (Jelly.References.Simple_Types.indexOf(Value_Type_Alias) != -1);

	// Update relation dropbox if changing simple type status
	var Property_Simple_Select = document.getElementById(Current_Action_Namespace + "_Edited_Relation_Select_Simple");
	var Property_Complex_Select = document.getElementById(Current_Action_Namespace + "_Edited_Relation_Select_Complex");
		
	if (Selected_Type_Is_Simple_Type)
	{	
		// If this is changing from complex to simple type, hide relation menu and set to simple
		if (Property_Simple_Select.style.display == "none")
		{									
			// Set input value to simple
			Jelly.Actions.Set_Input_Value({
					"Namespace":Current_Action_Namespace, 
					"Alias": "Edited_Relation", 
					"Value": ""
				});
	
			// Hide complex select
			Property_Complex_Select.style.display = "none";
	
			// Show simple select
			Property_Simple_Select.style.display = "block";
		}
	}

	else
	{
		// If this is changing from simple to complex type, hide relation menu and set to single
		if (Property_Complex_Select.style.display == "none")
		{									
			// Set input value to single
			Jelly.Actions.Set_Input_Value({
					"Namespace":Current_Action_Namespace, 
					"Alias": "Edited_Relation", 
					"Value": "Many-To-One"
				});

			// Hide simple select
			Property_Simple_Select.style.display = "none";

			// Show complex select
			Property_Complex_Select.style.display = "block";
		}
	}

};