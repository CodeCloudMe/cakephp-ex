Jelly.Interface.Calculate_Bounds = function(Element)
{
	var Element_Bounds = {"Left": null, "Top": null, "Right": null, "Bottom": null, "Width": null, "Height": null};
	
	if (Jelly.jQuery(Element).outerWidth() == 0)
	{	
		// TODO - check left, top vs page or vs. container ("We'll Know" - Tristan Perich, 2014)
		var Element_Children = Jelly.jQuery(Element).children();
		var Element_Children_Left = null;
		Jelly.jQuery.each(Element_Children, function(Child_Index, Child_Element) {
			if (Element_Bounds["Left"] === null)
				Element_Bounds["Left"] = Jelly.jQuery(Child_Element).offset().left;
			else
				Element_Bounds["Left"] = Math.min(Element_Bounds["Left"], Jelly.jQuery(Child_Element).offset().left);
			
			if (Element_Bounds["Top"] === null)
				Element_Bounds["Top"] = Jelly.jQuery(Child_Element).offset().top;
			else
				Element_Bounds["Top"] = Math.min(Element_Bounds["Top"], Jelly.jQuery(Child_Element).offset().top);
			
			// TODO - make sure left and width are consistent in terms of padding & borders
			if (Element_Bounds["Right"] === null)
				Element_Bounds["Right"] = Jelly.jQuery(Child_Element).offset().left + Jelly.jQuery(Child_Element).outerWidth();
			else
				Element_Bounds["Right"] = Math.max(Element_Bounds["Right"], Jelly.jQuery(Child_Element).offset().left + Jelly.jQuery(Child_Element).outerWidth());
			
			// TODO - make sure top and height are consistent in terms of padding & borders
			if (Element_Bounds["Bottom"] === null)
				Element_Bounds["Bottom"] = Jelly.jQuery(Child_Element).offset().top + Jelly.jQuery(Child_Element).outerHeight();
			else
				Element_Bounds["Bottom"] = Math.max(Element_Bounds["Bottom"], Jelly.jQuery(Child_Element).offset().top + Jelly.jQuery(Child_Element).outerHeight());
			});
		
		Element_Bounds["Width"] = Element_Bounds["Right"] - Element_Bounds["Left"];
		Element_Bounds["Height"] = Element_Bounds["Bottom"] - Element_Bounds["Top"];
	}
	else
	{
		// TODO - Test below
		Element_Bounds["Left"] = Jelly.jQuery(Element).offset().left;
		Element_Bounds["Top"] = Jelly.jQuery(Element).offset().top;
		Element_Bounds["Width"] = Jelly.jQuery(Element).outerWidth();
		Element_Bounds["Height"] = Jelly.jQuery(Element).outerHeight();
		Element_Bounds["Right"] = Element_Bounds["Left"] + Element_Bounds["Width"];
		Element_Bounds["Bottom"] = Element_Bounds["Top"] + Element_Bounds["Height"];
	}
	
	return Element_Bounds;
};