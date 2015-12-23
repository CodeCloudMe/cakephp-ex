Jelly.Debug.Print_All_Actions = function()
{
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
	{
		Jelly.Debug.Group("Print All Actions");

		for (Namespace in Jelly.References.References_By_Namespace)
		{
			if (Jelly.References.References_By_Namespace.hasOwnProperty(Namespace))
			{
				var Reference = Jelly.References.References_By_Namespace[Namespace];
			
				if (Reference["Kind"] == "Item" && Reference["Type_Alias"] && (["Action", "Type_Action"].indexOf(Reference["Type_Alias"]) >= 0))
				{
					Jelly.Debug.Log(Reference["Element"]);
					Jelly.Debug.Log(Reference["Input_Elements"]);
// 					for (Handler_Event in Reference["Handlers"])
// 					{
// 						if (Reference["Handlers"].hasOwnProperty(Handler_Event))
// 						{
// 							Jelly.Debug.Log(Namespace + " - " + Handler_Event +":");
// 							Jelly.Debug.Log(Reference["Handlers"][Handler_Event]);
// 						}
// 					}
				}
			}
		}
	
		Jelly.Debug.End_Group("Print All Actions");
	}
};