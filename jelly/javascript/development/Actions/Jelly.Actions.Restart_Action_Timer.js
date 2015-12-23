Jelly.Actions.Restart_Action_Timer = function(Parameters)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Restart Action Timer");
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
		
	// Start a new timer that executes the action in 5 seconds.
	Action_Reference.Timer = window.setTimeout(function ()
		{
			Jelly.Actions.Execute_Action_Timer({'Namespace': Parameters["Namespace"]});
		}, 
		5000);
		
	if (Debug)
	{
		// Flash status
		Jelly.jQuery('#Jelly_Inspector').css("border", "solid 10px white");
		setTimeout(function () {Jelly.jQuery('#Jelly_Inspector').css("border", "solid 10px yellow");}, 200);
	}

	if (Debug)
	{
		Jelly.Debug.End_Group("");
	}
};