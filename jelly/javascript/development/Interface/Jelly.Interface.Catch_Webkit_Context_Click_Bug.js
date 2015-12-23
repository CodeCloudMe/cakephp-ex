Jelly.Interface.Catch_Webkit_Context_Click_Bug = function()
{	
	// Compatibility - Chrome & Safari bug where context click event is followed by an undesired click event
	// TODO - Still needed? Probably not. Test.
	
	// Place a fixed, viewport-sized intercept element at the top layer.
	var Cancel_Next_Click_Element = document.createElement("div");
	Cancel_Next_Click_Element.id = "Cancel_Next_Click";
	Cancel_Next_Click_Element.style.position = "fixed";
	Cancel_Next_Click_Element.style.left = "0px";
	Cancel_Next_Click_Element.style.top = "0px";
	Cancel_Next_Click_Element.style.zIndex = "2000";
//			Cancel_Next_Click_Element.style.background = "red";
	Cancel_Next_Click_Element.style.width = (window.innerWidth || document.documentElement.clientWidth) + "px";
	Cancel_Next_Click_Element.style.height =(window.innerHeight || document.documentElement.clientHeight) + "px";
	document.body.appendChild(Cancel_Next_Click_Element);
		
	// Attach a mouse-up listener to the document which removes the intercept element, and then removes itself.
	var Cancel_Next_Click_Element_Listener = function() {
			document.body.removeChild(Cancel_Next_Click_Element);
			document.removeEventListener('mouseup', Cancel_Next_Click_Element_Listener, false);
		}				
	document.addEventListener('mouseup', Cancel_Next_Click_Element_Listener, false);
};