Jelly.Interface.Generate_Date_Selector = function(Namespace, Parameters)
{
	// TODO: Currently Disabled
	return;
	
	// TODO: This function
	// Get current calendar date
	if (Parameters && Parameters["Value"])
		Calendar_Date = Parameters["Value"];
	else
		Calendar_Date = new Date();
		
	// Get current calendar month
	Calendar_Month = Calendar_Date.getMonth();
	var Month_Names = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	var Calendar_Month_Name = Month_Names[Calendar_Month];
	
	// Get current calendar year.
	Calendar_Year = Calendar_Date.getFullYear();

	// Clear calendar input
	var Calendar_Input_Element_Wrapper = document.getElementById(Namespace + '_Date_Menu');
	Calendar_Input_Element_Wrapper.innerHTML = "";
	
	// Generate calendar header
	var Calendar_Input_Element = Jelly.Interface.Generate_Browser_Control({Browser_Control_ID: "Calendar_Input", Replace: {"CALENDAR_MONTH_NAME": Calendar_Month_Name, "CALENDAR_MONTH": Calendar_Month, "CALENDAR_YEAR": Calendar_Year, "INPUT_NAMESPACE": Namespace}});
	
	// Generate calendar dates
	var Calendar_Input_Date_Element_Wrapper = document.createElement("div");
	Calendar_Input_Date_Element_Wrapper.className = "Jelly_Calendar_Items";
	
	// Generate metrics to help count calendar dates
	Calendar_Month_First_Date = new Date(Calendar_Year, Calendar_Month, 1, 0, 0, 0);
	Calendar_Month_Last_Date = new Date(Calendar_Year, Calendar_Month + 1, 0,0,0,0);
	Previous_Month_Last_Date = new Date(Calendar_Year, Calendar_Month, 0, 0, 0, 0);
	Calendar_Month_First_Day = Calendar_Month_First_Date.getDay();
	Calendar_Month_Last_Day = Calendar_Month_Last_Date.getDay();
	Previous_Month_Last_Date_Index = Previous_Month_Last_Date.getDate();
	Previous_Month_First_Date_Index = Previous_Month_Last_Date_Index + 1 - Calendar_Month_First_Day;
	Calendar_Month_Last_Date_Index = Calendar_Month_Last_Date.getDate();
	Next_Month_Last_Date_Index = 6 - Calendar_Month_Last_Day;
	
	// Generate padding dates before calendar month
	for (var Date_Index = Previous_Month_First_Date_Index; Date_Index <= Previous_Month_Last_Date_Index; Date_Index++)
	{
		var Calendar_Input_Date_Element = document.createElement("div");
		Calendar_Input_Date_Element.className = "Jelly_Calendar_Items_Previous_Item";
		Calendar_Input_Date_Element.innerHTML = Date_Index;
		Calendar_Input_Date_Element_Wrapper.appendChild(Calendar_Input_Date_Element);
	}
	
	// Generate calendar input dates 
	for (var Date_Index = 1; Date_Index <= Calendar_Month_Last_Date_Index; Date_Index++)
	{
		Date_Value = new Date(Calendar_Year, Calendar_Month, Date_Index);
		Calendar_Input_Date_Element = Jelly.Interface.Generate_Browser_Control({Browser_Control_ID: "Calendar_Input_Date", Replace: {"DATE_VALUE": Date_Value, "DATE_INDEX": Date_Index, "INPUT_NAMESPACE": Namespace}});
		Calendar_Input_Date_Element_Wrapper.appendChild(Calendar_Input_Date_Element);
	}
	
	// Generate padding dates after calendar month
	for (var Date_Index = 1; Date_Index <= Next_Month_Last_Date_Index; Date_Index++)
	{
		var Calendar_Input_Date_Element = document.createElement("div");
		Calendar_Input_Date_Element.className = "Jelly_Calendar_Items_Next_Item";
		Calendar_Input_Date_Element.innerHTML = Date_Index;
		Calendar_Input_Date_Element_Wrapper.appendChild(Calendar_Input_Date_Element);
	}
	Calendar_Input_Date_Element_Wrapper.innerHTML += "<br class=\"Jelly_Clear\" />";
	Calendar_Input_Element.appendChild(Calendar_Input_Date_Element_Wrapper);

	
	// Add calendar to page.
	Calendar_Input_Element_Wrapper.appendChild(Calendar_Input_Element);
	//Jelly.Debug.Log(Calendar_Input_Element_Wrapper);
};