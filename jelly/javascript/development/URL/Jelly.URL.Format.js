// TODO:  doesn't seem to be used.
Jelly.URL.Format = function(URL, Format)
{
	// Formats any format of URL into the desired format.
	// TODO: Format isn't the right word.
	
	// URL could be given in many ways...

	// 	'http://asdfasdfsf/asdfadfasf'  - absolute
	// 	'/asdfasdfas/fasdfasdf/adsar' - relative with slash - garbage, remove slash.
	// 	'asdfasdfas/fasdfasdf/adsar' - relative  - you probably don't need this.
	// 	'#sasdadsf/asdasd/adss' - relative with anchor - Anchor(URL) produces this
	// 	'asdfasdfas/fasdfasdf/adsar/raw' - relative with raw - Raw(URL) produces this
};