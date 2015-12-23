Jelly.Interface.Generate_Window = function(Namespace)
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

	// Generate window HTML wrapper element with Namespace element inside
	var Window_Element = Jelly.Interface.Generate_Browser_Control({
		Browser_Control_ID: "Window",
		Replace: {"NAMESPACE": Namespace}
	});
	
	return Window_Element;
}
