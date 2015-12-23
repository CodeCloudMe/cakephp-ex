Jelly.Interface.Create_Global_Controls_Element = function ()
{
	// Create globals element, which will contain global references.
	
	// TODO: Should this be registered?
	Jelly.Interface.Global_Controls_Element = document.createElement("span");
	Jelly.Interface.Global_Controls_Element.id = "Jelly_Globals";
	// TODO: I felt that was too specific, below.
//	Jelly.Interface.Global_Controls_Element.className = "Jelly_Controls";
	Jelly.Body_Element.appendChild(Jelly.Interface.Global_Controls_Element);

//		Jelly.Register({
//			ID: "Path",
//			Namespace: Jelly.Body_Element.id
//		});
//		Jelly.Debug.Log(Jelly.References.References_By_Namespace);
	/*
	Jelly.Register({
		Kind: "Element",
		ID: Jelly.Interface.Global_Controls_Element.id,
		Namespace: Jelly.Interface.Global_Controls_Element.id,
		Force: true
	});
	*/
};