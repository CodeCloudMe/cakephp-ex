// TODO Some of this needs major debugging.
Jelly.Utilities.Serialize = function(Parameters)
{
	// Returns string of the key and values of Values array separate by Token
	// Parameters: Values, Token, Is_URI, Namespace
	
	var Debug = false && Jelly.Debug.Debug_Mode;
	
	if (Debug)
	{
		Jelly.Debug.Group("Serialize");
		Jelly.Debug.Log(Parameters);
	}

	// Instantiate array of value strings
	var Serialized_Strings = [];
	
	// Generate new namespace as blank, or Namespace_
	var New_Namespace = "";
	if (Parameters["Namespace"])
		New_Namespace = Parameters["Namespace"] + "_";
	
	// For each value in array, serialize to key=value, and add to serialized strings array
	for (Value_Key in Parameters["Values"])
	{
		if (Parameters["Values"].hasOwnProperty(Value_Key))
		{			
			var Value = Parameters["Values"][Value_Key];
			switch (typeof(Value))
			{
				case "boolean":
				case "number":
				case "string":				
					// Encode URI if specified
					// TODO: should Value_Key be encodeURIComponented??
//					if (Parameters["IS_URI"])
					Value = encodeURIComponent(Value);
						
					// Serialize to key=value and store in serialized strings array
					Serialized_Strings.push(New_Namespace + Value_Key + "=" + Value);
					break;

				// TODO: This seems like an old thing
				case "function":
					Jelly.Debug.Log("AJAX Request: cannot submit functions as post variables");
					break;
				
				case "object":
					// If array, store namespace type as array, store value count, and serialize values with namespace
					if (Value.hasOwnProperty('length'))
					{
						// Add type for this value 
						Serialized_Strings.push(New_Namespace + Value_Key + "_" + "Type" + "="  + "Array");
						
						// Add count for this value
						Serialized_Strings.push(New_Namespace + Value_Key + "_" + "Count" + "="  + Value.length);
						
						// Add serialized values for this value
						Serialized_Strings.push(Jelly.Utilities.Serialize(
								{
									'Values': Value,
									'Namespace': New_Namespace + Value_Key,
//									'Is_URI': Parameters['Is_URI'],
								}
							));							
					}
					else
					{
						// If an item, store namespace type as item,  and serialize values with namespace
					
						// Add type for this value 
						Serialized_Strings.push(New_Namespace + Value_Key + "_" + "Type" + "="  + "Item");

						// Add serialized values for this value
						Serialized_Strings.push(Jelly.Utilities.Serialize(
								{
									'Values': Value,
									'Namespace': New_Namespace + Value_Key,
//									'Is_URI': Parameters['Is_URI'],
								}
							));
						}
					break;
				default:
					// Handle undefined case
					if (Value == undefined)
					{
						// Serialize to "key=" and store in serialized strings array
						Serialized_Strings.push(New_Namespace + Value_Key + "=");
						break;					
					}					
					// Handle unknown case
					else
					{
						// Throw error.
						Jelly.Debug.Display_Error("Unsupported Post Value Type: " + typeof(Value));
					}
					break;
			}
		}
	}
	
	// Join each serialized string by join_token
	if (Parameters["Token"])
		Token = Parameters["Token"];
	else
		Token = "&";

	var Value_String = Serialized_Strings.join(Token);
	
	if (Debug)
	{
		Jelly.Debug.Log(Value_String);
		Jelly.Debug.End_Group("Serialize");
	}
	
	// Return 
	return Value_String;
}
