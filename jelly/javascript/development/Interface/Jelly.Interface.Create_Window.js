Jelly.Interface.Create_Window = function(Parameters)
{
	// Create a window in a global reference, position it correctly and add some standard window behaviors, and fill it via a request for content. 
	// TODO: this isn't registered, it's parent global reference sort of is, and then its' child will be via Fill, but it isn't. That's ok, right? Just sort of wonder if this should be IN the parent global reference, as a kind of global reference, rather than an empty hierarchy, maybe.

	// Parameters: Alias, Calling_Element, Modal, Closeable
	// TODO: cross-reference parameters
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Create_Window");
		Jelly.Debug.Log("Parameters...");
		Jelly.Debug.Log(Parameters);
	}
	
	// Generate parent reference
	var Window_Parameters = {};
	Window_Parameters["Kind"] = "URL";
	Window_Parameters["URL"] = Parameters["URL"];
	Window_Parameters["Parent_Namespace"] = "Jelly";
	
	// Set namespace...
	
	// Increment unique namespace index
	Jelly.References.Current_Global_Reference_Index++;
	
	// Generate base unique namespace
	var Window_Namespace = Jelly.Interface.Global_Controls_Element.id + Jelly.Namespace_Delimiter + Jelly.References.Current_Global_Reference_Index;
	
	// If alias is provided, append it to namespace
	if (Parameters['Alias'])
		Window_Namespace += Jelly.Namespace_Delimiter + Parameters["Alias"];
	
	// Store namespace
	Window_Parameters["Namespace"] = Window_Namespace;
	
	// TODO: Modal
	// TODO: Closeable
	
	// Generate a window control element
	var Window_Control_Element = Jelly.Interface.Generate_Window(Window_Namespace);

	// Append window control to global controls
	Jelly.Interface.Global_Controls_Element.appendChild(Window_Control_Element);
	
	// Register our new window
	var Window_Reference = Jelly.References.Register(Window_Parameters);

	// Add to modal windows list, and show a light-box
	Jelly.Interface.Modal_Windows.push(Window_Reference);
	Jelly.Interface.Show_Lightbox();
	
	// Store window control element
	Window_Reference["Control_Element"] = Window_Control_Element;
	
	// Set calling reference
	// TODO: check IF needed
	// TODO: Really? Set this to parent reference? Shouldn't this be set on the window parameters directly? 
	if (Parameters["Calling_Reference"])
		Window_Reference["Calling_Reference"] = Parameters["Calling_Reference"];
	else
		Window_Reference["Calling_Reference"] = Jelly.References.Get_Reference_For_Element(Parameters["Calling_Element"]);
	
	// Place the window element at the topmost modal window z-index
	// TODO: Any neater lightbox/window calculation, or is this interleaving idea the best one?
	Window_Control_Element.style.zIndex = Jelly.Interface.Window_Z_Index_Start + Jelly.Interface.Modal_Windows.length * 2 + 1;
	
	// Make window draggable
	// TODO: Can this happen in generate_window as well? 
// 	Jelly.jQuery(Window_Control_Element).draggable(
// 		{
// 			cursor: "move",
// 			handle: document.getElementById(Window_Namespace + "_Window_Handle")
// 		});	
	
	// Handle reference element...
	
	// Set reference element to container inside window element
	var Window_Element = document.getElementById(Window_Namespace);
	Window_Reference["Element"] = Window_Element;
		
	// Add some default spacing to the reference element.
	// TODO: This should be handled in the Generate_Window process, not here.
	Window_Element.innerHTML = "<div style=\"width: 75px; height: 25px;\"></div>";
	
	// Prevent window from being larger than the page
	// TODO: Should this be for Window_Element instead? 
// 	Window_Element.style.maxWidth = window.innerWidth - 25 * 2 + "px";
// 	Window_Element.style.maxHeight = window.innerHeight - 25 * 2 + "px";
// 	Window_Element.style.paddingRight = "0px";
	
	// Fade the window element in
	Jelly.jQuery(Window_Control_Element).addClass("Visible");
	Jelly.jQuery(Window_Control_Element).fadeIn();
	
	Window_Element.Jelly_Reference = Window_Reference;
	
	if (Debug)
	{
		Jelly.Debug.Group("Window Reference");
		Jelly.Debug.Log(Window_Reference);
	}
	
	// Request and fill content for the window.
	// TODO: Cross-Reference & make sure the right parameters are passed in for this
	Jelly.References.Refresh(Window_Reference);
	
	// Add dismiss handling
	// TODO: Is Parent_Element right? Why not Window_Element?
	Jelly.Handlers.Register_Handler({"Element": Window_Element, "Event": "Dismiss", "Code": function(Parameters)
		{
			if (Debug)
			{
				Jelly.Debug.Log(Window_Reference);
				Jelly.Debug.Log(Parameters);
			}

			// TODO: Cross-Reference & make sure the right parameters are passed in for this		
			Jelly.Interface.Close_Window(Window_Reference);
			
			// Focus top window.
			Jelly.Interface.Focus_Top_Window();
		}});
		
	// Add focus handling
	// TODO: Is Parent_Element? Why not Window_Element? for example, when is on_load triggered here? 
	// TODO: I think this has to be window element, no? 
	// TODO - maybe this is a function...
	Jelly.Handlers.Register_Handler({"Element": Window_Element, "Event": "Focus", "Code": function()
		{	
			// Get the window's root node
			// TODO: Why not window itself?
			var Window_Root_Node = Window_Control_Element.getElementsByTagName("span")[0].getElementsByTagName("span")[0];
			
			// Set handler focus to the window root node (for controlling keyboard events (enter/escape)) 
			Jelly.Handlers.Set_Default_Target(Window_Root_Node);
			
			// Set the browser focus on first input in window, or remove focus if no input is found.
			// TODO: This can probably happen within the function.
			if (!Jelly.Interface.Focus_First_Control(Window_Control_Element))
			{
				// If no input found, remove focus from all elements.
				var Links = document.getElementsByTagName("a");
				for (var i = 0; i < Links.length; i++)
					Links[i].blur();
			}
		}});
		
	// Add auto-focus on load.
	Jelly.Handlers.Register_Handler({"Element": Window_Element, "Event": "On_Load", "Code": function(Parameters)
		{
			// If this is the top most window, then call the focus handler.
			if (Jelly.Interface.Modal_Windows.length)
			{
				var Top_Window_Reference = Jelly.Interface.Modal_Windows[Jelly.Interface.Modal_Windows.length - 1];
				if (Parameters["Target"] == Top_Window_Reference.Element)
				{
					Jelly.Handlers.Call_Handler_For_Target({
								'Event': 'Focus', 
								'Target':Parameters['Target'],
								'Display_Target': Parameters['Display_Target'],
							}
						);
				}
			}
		}});
		
	// Lock modal state
	Jelly.Interface.Lock();
			
	if (Debug)
		Jelly.Debug.End_Group("Create_Window");
};