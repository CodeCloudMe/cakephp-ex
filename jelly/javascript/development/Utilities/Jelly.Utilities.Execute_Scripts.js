Jelly.Utilities.Execute_Scripts = function(HTML_Text)
{	
	// Parses response for scripts, and executes them.	
	
	// TODO:  tried jQuery implementation..., but utlimately don't trust it.
	/*
	// Turn into jQuery object
	var DOM = $(HTML_Text);
	DOM.filter('script').each( function() 
			{
				var Script = (this.text || this.textContent || this.innerHTML || '';
				eval(Script);
			}
		);
	*/

	// TODO: Isn't documented, but seems to work.
	var Open_Tag = "<script";
	var Close_Tag = "</script";
	
	var Script_Count = 0;
	var Script_Position = -1;
	
	while (1)
	{
		var Open_Position = HTML_Text.indexOf(Open_Tag, Script_Position + 1);
		var Close_Position = HTML_Text.indexOf(Close_Tag, Script_Position + 1);

		if (Open_Position != -1 && (Close_Position == -1 || Open_Position < Close_Position))
		{
			Script_Count++;
			Script_Position = Open_Position;
			
			if (Script_Count == 1)
			{
				var Script_Start = Open_Position;
			}
		}
		else if (Close_Position != -1 && (Open_Position == -1 || Close_Position < Open_Position))
		{
			Script_Count--;
			Script_Position = Close_Position;
			
			if (Script_Count == 0)
			{				
				var Script = HTML_Text.substring(HTML_Text.indexOf(">", Script_Start) + 1, Close_Position);
				try 
				{
					eval(Script);
				} 
				catch (e)
				{
					Jelly.Debug.Log ("Error in Script.");
					Jelly.Debug.Log (e);
					Jelly.Debug.Log(e.stack);
					Jelly.Debug.Log (Script);
				}
			}
		}
		else
		{
			break;
		}
	}

	/*
	var Script_Div = document.createElement("div");
	Script_Div.innerHTML = HTML_Text;
	var Script_Tags = Script_Div.getElementsByTagName("script");
	for (Script_Tag_Index in Script_Tags)
	{
		var Script = Script_Tags[Script_Tag_Index].innerHTML;
		if (Script)
		{
			Jelly.Debug.Group("Script");
			Jelly.Debug.Log(Script);
			eval(Script);
			Jelly.Debug.End_Group("");
		}
	}
	*/
	
//			Jelly.Debug.End_Group("");
};