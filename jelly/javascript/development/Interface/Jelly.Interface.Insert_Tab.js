Jelly.Interface.Insert_Tab = function(Target)
{
	// Get the current selection
	var Selection_Start = Target.selectionStart;
	var Selection_End = Target.selectionEnd;	
	var Has_Selected_Text = !(Selection_Start == Selection_End)

	// If there is a selection, indent the selection	
	if (Has_Selected_Text)
	{
		// Get the current text
		var Text = Target.value;

		// If first character is a new line, move earlier to make sure it is included
		if (Text.charAt(Selection_Start) == "\n")
			Selection_Start--;
			
		// Search backwards to find new line or beginning of text
		while (Selection_Start != -1 && Text.charAt(Selection_Start) != "\n")
			Selection_Start--;
			
		// If last character is a new line, remove it from the selection
		if (Text.charAt(Selection_End - 1) == "\n")
			Selection_End--;
		
		// Insert tabs after new lines until end of selection
		var Position = Selection_Start;
		while (Position < Selection_End)
		{
			// Advance past new line
			Position++;
	
			// Splice in new tab and note that selection is now longer
			Text = Text.substr(0, Position) + "\t" + Text.substr(Position);
			Selection_End++;
	
			// Search for next new line until end of selection
			while (Position < Selection_End && Text.charAt(Position) != "\n")
				Position++;
		}
			
		// Search for next new line until end of text
		while (Position < Text.length && Text.charAt(Position) != "\n")
			Position++;

		// Update the textarea with the new text.
		Target.value = Text;
		
		// Update the textarea with the new selection
		Target.selectionStart = Selection_Start + 1;
		Target.selectionEnd = Position;
	}

	// If no selection, add a tab at the cursor
	else
	{		
		// Add a tab at the cursor
		Target.value = Target.value.substr(0, Selection_Start) + "\t" + Target.value.substr(Selection_End);
		
		// Move the cursor to after the tab
		Target.selectionStart = Selection_Start + "\t".length;
		Target.selectionEnd = Selection_Start + "\t".length;
	}
};