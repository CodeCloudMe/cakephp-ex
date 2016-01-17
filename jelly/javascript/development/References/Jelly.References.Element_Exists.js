Jelly.References.Element_Exists = function(Test_Element)
{	
	// Verify that the test element exists in the DOM.
	// TODO: There are faster ways, here... http://jsperf.com/closest-vs-contains/4, but they're much uglier, with fake test ids.
	// TODO But seemingly at least 30x faster.
	
	var Recursive_Test_Element = Test_Element;
 
 	// Verify recursive parent nodes of test element until reaching document, or return false.
	while (Recursive_Test_Element)
	{
		if (Recursive_Test_Element == document)
			return true;
		Recursive_Test_Element = Recursive_Test_Element.parentNode;
	}
	
	return false;
};