// TODO:  comment and describe what this does
// TODO: Shit function
Jelly.Interface.Set_Date = function(Namespace, Value)
{	
	var Date_Value = new Date(Value);
	Input_Element = document.getElementById(Namespace + "_Date");
	Input_Element.value = (Date_Value.getMonth() + 1) + "/" + (Date_Value.getDate()) + "/" + (Date_Value.getFullYear());
	
	Jelly.Handlers.Cancel_Event("Click");
	Input_Element.select();
};