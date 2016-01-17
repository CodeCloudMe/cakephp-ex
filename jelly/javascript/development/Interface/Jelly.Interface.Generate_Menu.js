Jelly.Interface.Generate_Menu = function(Namespace)
{
	// TODO: Possibly add something built in, via something like 
/*

return [Format as "Javascript String"]
	<div>
		yay.
	</div>
[/Format];
*/
	// TODO: Remove below
	var Menu_Element = document.createElement("div");
	Menu_Element.id = Namespace + "_Wrapper";
	Menu_Element.className = "Jelly_Menu";
	
	var Reference_Element = document.createElement("div");
	Reference_Element.id = Namespace;
	Reference_Element.style.backgroundColor = "white";
	Reference_Element.innerHTML = "<div style=\"width: 75px; height: 25px;\"></div>";
	Menu_Element.appendChild(Reference_Element);

	return Menu_Element;
}
