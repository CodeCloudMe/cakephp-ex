Jelly.Interface.Generate_Browser_Control = function(Parameters)
{	
	// Return a DOM tree generated from a named cached HTML string, replacing placeholder text in the string.
	// TODO: End this for some straightforward parameters based "Generate_" functions
	// Parameters: Browser_Control_ID, Replace
	
	// Get Browser Control HTML by ID
	var Browser_Control_HTML = Browser_Controls[Parameters["Browser_Control_ID"]];
	
	// If they have been provided, replace placeholder keys in HTML with values
	if (Parameters["Replace"])
	{
		// For each placeholder key 
		for (Placeholder_Key in Parameters["Replace"])
		{
			if (Parameters["Replace"].hasOwnProperty(Placeholder_Key))
			{
				// Get value
				var Value = Parameters["Replace"][Placeholder_Key];
				
				// Replace every placeholder key in the HTML with the value.
				Browser_Control_HTML = Browser_Control_HTML.replace(new RegExp(Placeholder_Key, "g"), Value);				
			}
		}
	}
			
	// Convert the HTML string to a DOM node
	// TODO - investigate why it needs to be trimmed... shouldn't.
	var Browser_Control_Node = jQuery.parseHTML(Browser_Control_HTML.trim());
	
	// Return browser
	return Browser_Control_Node[0];

// TODO: test, delete the below of the above works.
// 	var New_Element = document.createElement("div");
// 	New_Element.innerHTML = HTML_Text;
// 	return New_Element.childNodes[0];
	
};