Jelly.Utilities.Clean_Scripts = function(HTML_Text)
{	
	// Cleans scripts from HTML text.
	// TODO - totally untested
	
	// Create a tag with the passed in text as the inner HTML
	var Content = jQuery(HTML_Text.bold());
	
	// Find and remove all script tags
   Content.find('script').remove();
   
   // Return inner HTML
   return Content.html();
};