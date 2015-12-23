Jelly.Actions.On_Property_Relation_Load = function(Parameters)
{	
	// Get current action namespace
	var Current_Action_Namespace = Parameters["Namespace"];
	
	// Get value type alias
	var Value_Type_Input = Jelly.Actions.Get_Input_From_Action_By_Alias({
			"Namespace": Current_Action_Namespace,
			"Alias": "Edited_Value_Type"
		});
	var Value_Type_Alias = Value_Type_Input.value;
		
	// Show the corresponding options for the value type...

	// Get simple type status of selected value type
	var Selected_Type_Is_Simple_Type = (Jelly.References.Simple_Types.indexOf(Value_Type_Alias) != -1);
	
	if (Selected_Type_Is_Simple_Type)
	{	
		// Show simple select
		var Property_Simple_Select = document.getElementById(Current_Action_Namespace + "_Edited_Relation_Select_Simple");
		Property_Simple_Select.style.display = "block";
	}
	
	else
	{	
		// Show complex select
		var Property_Complex_Select = document.getElementById(Current_Action_Namespace + "_Edited_Relation_Select_Complex");		
		Property_Complex_Select.style.display = "block";	
	}
};