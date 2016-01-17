Jelly.Actions.Show_Result = function(Parameters)
{	
	// Registers an action result element for a reference.
	// Parameters:  Namespace, Content, Input_Alias
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Display Result");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	// TODO - since it's a "calling reference" that's passed in, could be anything.   Not sure if it makes sense to just work off of "Result_Element" explicitly instead of the below, which requires "Action"s for updating results.
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
	
	// Get the action or input result element
	var Result_Element = null;
	if (Parameters["Input_Alias"])
	{	
		// Get input...
		var Input_Alias = Parameters["Input_Alias"];

		// Verify input
		if (!Action_Reference.Inputs[Input_Alias])
		{
			Jelly.Debug.Display_Error("Tried to add input result for non-existing input: " + Input_Alias);
			return;
		}
		var Input = Action_Reference.Inputs[Input_Alias];
		
		// Get result element 
		if (Input["Result_Element"])
			Result_Element = Input["Result_Element"];			
	}
	else
	{
		if (Action_Reference["Result_Element"])
			Result_Element = Action_Reference["Result_Element"];
	}
	
	// If there's a result element, then display the result content inside of the result element.
	if (Result_Element)
	{
		var Content = Parameters["Content"];
		Result_Element.innerHTML = Content;
		
		// Show the result if it has any actual text besides white space
		var Content_Text = Jelly.jQuery(Result_Element).text();
		if (/\S/.test(Content_Text))
			Result_Element.style.display = "block";
		
		// TODO - clean up with styles
		// TODO - add animation.
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};