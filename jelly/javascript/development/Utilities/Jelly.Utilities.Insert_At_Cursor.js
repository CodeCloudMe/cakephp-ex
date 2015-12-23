Jelly.Utilities.Insert_At_Cursor = function(Parameters)
{
	// TODO: Should be in interface
	// Inserts Value in Element
	// Parameters: Element, Value, ID
	// TODO: Rewrite this function
	if (Parameters["Element"])
		var Text_Area_Element = Parameters["Element"];
	else
		var Text_Area_Element = document.getElementById(Parameters["ID"]);
//			Jelly.Debug.Log(Parameters["ID"]);
//			Jelly.Debug.Log(Text_Area_Element);
	
	if (!Text_Area_Element)
		return;
	
	var Scroll_Position = Text_Area_Element.scrollTop; 
	var Cursor_Position = 0; 

	// Determine browser.
	var Is_Internet_Explorer;
	if (Text_Area_Element.selectionStart || Text_Area_Element.selectionStart == "0")
		Is_Internet_Explorer = false;
	else
		Is_Internet_Explorer = false;

	// Get cursor position.
	if (Is_Internet_Explorer)
	{
		Text_Area_Element.focus();
		var Text_Area_Range = document.selection.createRange();
		Text_Area_Range.moveStart ('character', Text_Area_Element.value.length); 
		Cursor_Position = range.text.length; 
	}
	else
		Cursor_Position = Text_Area_Element.selectionStart; 

	// Insert text at cursor.
	Text_Area_Element.value = Text_Area_Element.value.substr(0, Cursor_Position) + Parameters["Value"] + Text_Area_Element.value.substring(Cursor_Position, Text_Area_Element.value.length);				

	// Advance cursor.
	Cursor_Position += Parameters["Value"].length;

	if (Is_Internet_Explorer)
	{
		Text_Area_Element.focus(); 	
		var Text_Area_Range = document.selection.createRange();
		range.moveStart ('character', -Text_Area_Element.value.length); 
		range.moveStart ('character', Cursor_Position); 
		range.moveEnd ('character', 0);
		range.select();
	}
	else
	{
		Text_Area_Element.selectionStart = Cursor_Position;
		Text_Area_Element.selectionEnd = Cursor_Position;
		Text_Area_Element.focus();
	}

	Text_Area_Element.scrollTop = Scroll_Position; 
};