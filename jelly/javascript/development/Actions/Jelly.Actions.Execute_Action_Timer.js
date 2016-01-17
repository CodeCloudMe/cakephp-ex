Jelly.Actions.Execute_Action_Timer = function(Parameters)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Execute Action Timer");
		Jelly.Debug.Log(Parameters);
	}
	
	// Lookup action reference
	var Action_Element = document.getElementById(Parameters["Namespace"]);
	var Action_Reference = Jelly.References.Get_Reference_For_Element(Action_Element);
	if (!Jelly.Actions.Is_Action_Reference(Action_Reference))
		return;
		
	// Clear timer if it exists.
	if (Action_Reference.Timer)
		window.clearTimeout(Action_Reference.Timer);
		
	// Execute action
	Jelly.Handlers.Call_Handler_For_Target({"Event": "Execute", "Target": document.getElementById(Parameters["Namespace"])});
	
	// Flash status
	if (Debug)
	{
		console.log("executing: " + Parameters["Namespace"]);
		Jelly.jQuery('#Jelly_Inspector').css("border", "solid 10px magenta");
		setTimeout(function () {Jelly.jQuery('#Jelly_Inspector').css("border", "none");}, 200);
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};