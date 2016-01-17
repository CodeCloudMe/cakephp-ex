Jelly.Interface.Position_Cards = function()
{
	Jelly.jQuery(".Better_Manage_Card_List").masonry({
			itemSelector: "li > div",
			columnWidth: 300,
			gutter: 10,
			isFitWidth: true
		});

};