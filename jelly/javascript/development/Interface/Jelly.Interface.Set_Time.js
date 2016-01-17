// TODO: this function
Jelly.Interface.Set_Time = function(Namespace, Value)
{	
	Input_Element = document.getElementById(Namespace + "_Time");
	Input_Element.value = Value;			
	Jelly.Handlers.Cancel_Event("Click");
	Input_Element.select();
};