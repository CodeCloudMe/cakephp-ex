// TODO - the code below doesn't really make sense - see how it's used and rewrite if it's used. 
Jelly.Interface.Timeout = function(Script, Period)
{	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Timeout");
		Jelly.Debug.Log("Script...");
		Jelly.Debug.Log(Script);
	}
	
	if (!Period)
		Period = Jelly.Interface.Event_Protection_Period;
		
	if (Debug)
		Jelly.Debug.End_Group("");

	return setTimeout(Script, Period);
	
};