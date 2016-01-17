Jelly.Debug.Print_All_Handlers = function()
{
	if (Jelly.Debug.Debug_Mode == Jelly.Debug.Level.All)
	{
		Jelly.Debug.Group("Print All Handlers");

		for (Namespace in Jelly.References.References_By_Namespace)
		{
			if (Jelly.References.References_By_Namespace.hasOwnProperty(Namespace))
			{
				var Reference = Jelly.References.References_By_Namespace[Namespace];

				if (Reference["Handlers"])
				{
					for (Handler_Event in Reference["Handlers"])
					{
						if (Reference["Handlers"].hasOwnProperty(Handler_Event))
						{
							Jelly.Debug.Log(Namespace + " - " + Handler_Event +":");
							Jelly.Debug.Log(Reference["Handlers"][Handler_Event]);
						}
					}
				}
			}
		}
	
		Jelly.Debug.End_Group("Print All Handlers");
	}
};