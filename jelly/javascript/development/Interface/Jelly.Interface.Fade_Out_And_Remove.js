Jelly.Interface.Fade_Out_And_Remove = function(Element)
{
	// Fades out and removes an element
// 	Jelly.jQuery(Element).fadeOut("fast", function() {Jelly.jQuery(Element).remove();});
// TODO breaks dialogues if removes after fade out (since it's gone by the time they are completed)
	Jelly.jQuery(Element).removeClass("Visible");
	Jelly.jQuery(Element).fadeOut("fast", function() {});
};