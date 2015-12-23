Jelly.Interface.Create_Inspector = function(Parameters)
{
	// Open Inspector	
	Jelly.jQuery('#Jelly_Inspector').addClass("Visible");
		
	// Narrow content
	if (!Parameters["Maintain_Content_Size"])
		Jelly.jQuery('#Jelly_Content').addClass("Inspector_Visible");

	// Get Inspector element
	var Inspector_Element = document.getElementById("Jelly_Inspector");
	
	// Set up Inspector refresh parameters
	Inspector_Parameters = 
	{
		URL: Parameters["URL"],
		Element: Inspector_Element,
		Create_Reference:true		
	};
	
	// Clear Inspector
	Inspector_Element.innerHTML = "<div class=\"Inline_Loading\"></div>";

	// Refresh Inspector
	var Inspector_Reference = Jelly.References.Fill(Inspector_Parameters);
	
	// Add Inspector focus handler
	Jelly.Handlers.Register_Handler({"Element": Inspector_Element, "Event": "Focus", "Code": function()
		{				
			// Set the browser focus on first input in window, or remove focus if no input is found.
			if (!Jelly.Interface.Focus_First_Control(Inspector_Element))
			{
				// If no input found, remove focus from all elements.
				var Links = document.getElementsByTagName("a");
				for (var i = 0; i < Links.length; i++)
					Links[i].blur();
			}
		}});

	// Add Inspector load handler (once)
	Jelly.Handlers.Register_Handler({"Element": Inspector_Element, "Event": "On_Load", "Code": function(Parameters)
		{
			// Focus
			Jelly.Handlers.Call_Handler_For_Target({
						'Event': 'Focus', 
						'Target':Parameters['Target'],
					}
				);
			
			// Destroy focus handler
			delete Inspector_Reference["Handlers"]["On_Load"];
		}});
};