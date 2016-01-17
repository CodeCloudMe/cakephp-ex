Jelly.References.Refresh_Site_Icon = function()
{		
	// TODO - can generalize, of course, if ever needed.
	// TODO - this is overzealous, based on some web forum comments. might be able to just change the URL now, without the magician act

	// Get icon element
	var Icon_Element = document.getElementById('Jelly_Site_Icon');
	
	// Duplicate element
	var Icon_Duplicate_Element = Icon_Element.cloneNode();
	
	// Store icon' parent node.
	var Icon_Parent_Node = Icon_Element.parentNode;
	
	// Remove icon element from parent node
	Icon_Parent_Node.removeChild(Icon_Element);

	// Generate timestamp
	var Current_Timestamp = new Date().getTime();
	
	// Update duplicate element href
	var Icon_URL = Icon_Duplicate_Element.href;
	var Icon_URL_Timestamp_Index = Icon_URL.lastIndexOf('=') + 1;
	var New_Icon_URL = Icon_URL.substring(0,Icon_URL_Timestamp_Index) + Current_Timestamp;	
	Icon_Duplicate_Element.href = New_Icon_URL;
	
	// Insert updated element into parent node
	Icon_Parent_Node.appendChild(Icon_Duplicate_Element);
};