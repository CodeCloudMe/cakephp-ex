// TODO: Not sure what this was going to do so I duplicated it and left this one alone.
// It doesn't seem to do anything, at all.
// TODO: to delete?
Jelly.Interface.Setup_Date_Selector = function(Parameters)
{
	var Value_Date = new Date();
	var Value_Month = Value_Date.getMonth();
	var Month_Names = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	var Value_Month_Name = Month_Names[Value_Month];
};