Jelly.Interface.Insert_Return_With_Indented_Text = function(Target)
{
	// Auto-indent next line of text in the target element.	
	// TODO: This function
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Insert Return With Indented Text");
		Jelly.Debug.Log("Target...");
		Jelly.Debug.Log(Target);
	}

	// Get total text.
	var Text = Target.value;
	
	// TODO: Browser compatibility check, jQuery version.? 
	var Selection_Start = Target.selectionStart;
	
	// If first character is a new line, move before it
	if (Text.charAt(Selection_Start) == "\n")
		Selection_Start--;
	
	// Search backwards to find new line or beginning of text
	while (Selection_Start != -1 && Text.charAt(Selection_Start) != "\n")
		Selection_Start--;
	
	// Advance 1 character (past newline, or to the beginning of the text)
	Selection_Start++;
	
	// Search forwards to find number of tabs
	var Tab_Position = Selection_Start;
	while (Tab_Position < Text.length && Text.charAt(Tab_Position) == "\t")
		Tab_Position++;
	
	// Get the string of tab characters
	// TODO: this is cute, but I would probably rebuild the tab characters and count the tabs, rather then just copy the indentation string. 
	var Tabs = Text.substr(Selection_Start, Tab_Position - Selection_Start);
	
	// Replace or insert newline & and tabs at selection
	var Selection_Start = Target.selectionStart;
	var Selection_End = Target.selectionEnd;	
	Target.value = Text.substr(0, Selection_Start) + "\n" + Tabs + Text.substr(Selection_End);
	
	// Set the textarea cursor to the character following the new line.
	Target.selectionStart = Selection_Start + ("\n" + Tabs).length;
	Target.selectionEnd = Target.selectionStart;
	
	if (Debug)
		Jelly.Debug.End_Group("");
};