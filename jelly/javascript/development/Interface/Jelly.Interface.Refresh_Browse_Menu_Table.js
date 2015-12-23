// TODO: not sure this is in use, but it seems to have been for a searchable dropdown.
Jelly.Interface.Refresh_Browse_Menu_Table = function(Parameters)
{
	// Parameters: Namespace, Type
	var Namespace = Parameters["Namespace"];
	var Type = Parameters["Type"];
	
	var Text_Box_Value = document.getElementById(Namespace + "_Text_Box").value;
	var Browse_Table_Wrapper_Element = document.getElementById(Namespace + "_Table");
	
	Jelly.References.Fill(
		{
			Element: Browse_Table_Wrapper_Element, 
			URL: "/Type/" + Type + "/Browse_Menu_Table:Show_Items=True,Search=" + Text_Box_Value,
			Create_Reference: true
		}
	);
};