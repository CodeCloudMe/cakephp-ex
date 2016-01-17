<?php

// Creates a new base item

function &Create_Item(&$Database, &$Cached_Type)
{
	// Validate
	if (!isset($Cached_Type))
		throw new Exception('Create Item: Cannot make new Item without a type.');
	if (!isset($Database))
		throw new Exception('Create Item: Cannot make new Item without a database.');
	
	// Initialize new item
	$Item = &New_Array();

	// Set basic values
	$Item['Kind'] = &New_String('Item');
	
	// Setup database
	$Item['Database'] = &$Database;

	// Store Types & Properties
	$Item['Cached_Base_Type'] = &$Cached_Type;
	$Item['Cached_Specific_Type'] = &$Cached_Type;
	
	$Item['Cached_Specific_Properties'] = &New_Array();
	$Item['Cached_Specific_Property_Lookup'] = &New_Array();
	
	// Set up References
	$Item['References'] = &New_Array();
	
	// Setup specific values.
	// TODO @feature-language: set up specific values 
	//$Item['Override_Values'];
	
	// Return new item as reference
	
	return $Item;
}

?>