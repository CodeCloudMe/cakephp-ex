Jelly.Handlers.Set_Default_Target = function(Target)
{
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Set_Default_Target");
		Jelly.Debug.Log(Target);
	}
	
	// Store target as focus of default handlers
	Jelly.Handlers.Default_Target = Target;
	
	if (Debug)
		Jelly.Debug.End_Group("");
};