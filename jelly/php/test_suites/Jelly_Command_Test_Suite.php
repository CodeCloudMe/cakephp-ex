<?php

require_once('Jelly.php');

$Block_Tests = array();

// Default
$Block_Tests[] = array(
	'Block_String' => '[Page "Home"][Name /][/Page]',
	'Expected_Result' => 'Home');
$Block_Tests[] = array(
	'Block_String' => '[Page Where Name Is "Home"][Name /][/Page]',
	'Expected_Result' => 'Home');
$Block_Tests[] = array(
	'Block_String' => '[Page Where Name Is "Home" /]',
	'Expected_Result' => 'Simple Template. Simple page content. Page Name: Home. End of Template.');
$Block_Tests[] = array(
	'Block_String' => '[Page Where Name Is "Home"][Page.Content Process_Once /][/Page]',
	'Expected_Result' => 'Simple page content. Page Name: [Page.Name /].');
$Block_Tests[] = array(
	'Block_String' => 'Bot: [Bot]is bot![/Bot]!',
	'Expected_Result' => 'Bot: !');
$Block_Tests[] = array(
	'Block_String' => 'Bot: [Bot][then]is bot![/then][else]is not[/else][/Bot]!',
	'Expected_Result' => 'Bot: is not!');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "Home"][Then][Name /][/Then][Else]No Page[/Else][/Page].',
	'Expected_Result' => 'Page: Home.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "No Such Page"][Then][Name /][/Then][Else]No Page[/Else][/Page].',
	'Expected_Result' => 'Page: No Page.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "No Such Page"][Else]No Page[/Else][/Page].',
	'Expected_Result' => 'Page: No Page.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "No Such Page"][Then][Name /][/Else][/Page].',
	'Expected_Result' => 'Page: .');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "No Such Page"][Name /][/Page].',
	'Expected_Result' => 'Page: .');
$Block_Tests[] = array(
	'Block_String' => 'Page: [0 Page][Then][Name /][/Then][Else]No Page[/Else][/Page].',
	'Expected_Result' => 'Page: No Page.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "Home" Or Name Is "Overview"][Name /][/Page].',
	'Expected_Result' => 'Page: HomeOverview.');
$Block_Tests[] = array(
	'Block_String' => '1 Page: [1 Page Where Name Is "Home" And 1 is 1][Name /][/Page].',
	'Expected_Result' => '1 Page: Home.');
$Block_Tests[] = array(
	'Block_String' => '1 Page By Name: [1 Page Where Name Is "Home" Or Name Is "Overview" By Name][Name /][/Page].',
	'Expected_Result' => '1 Page By Name: Home.');
$Block_Tests[] = array(
	'Block_String' => '1 Page By Name Descending: [1 Page Where Name Is "Home" Or Name Is "Overview" By Name Descending][Name /][/Page].',
	'Expected_Result' => '1 Page By Name Descending: Overview.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is Not "Home" And Name Is "Overview"][Then][Name /][/Then][Else]No Page[/Else][/Page].',
	'Expected_Result' => 'Page: Overview.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "Home" Or Name Is "Overview"][Then][Name /][/Then][Else]No Page[/Else][/Page].',
	'Expected_Result' => 'Page: HomeOverview.');
// Other operators to test: does not exist
// TODO: Page.Related_Page, etc. queries between items
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "Home" as Key /].',
	'Expected_Result' => 'Page: 323.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "Home" as ID /].',
	'Expected_Result' => 'Page: 323.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "Home" as Alias /].',
	'Expected_Result' => 'Page: Home.');
$Block_Tests[] = array(
	'Block_String' => 'Page: [Page Where Name Is "Home" as Reference /].',
	'Expected_Result' => 'Page: .'); // TODO: confirm reference returned?
// TODO handle exceptions
// $Block_Tests[] = array(
// 	'Block_String' => 'Unknown: [Hej /].',
// 	'Expected_Result' => 'Unknown: .');
// TODO: expressions as commands
// $Block_Tests[] = array(
// 	'Block_String' => '[5 /]',
// 	'Expected_Result' => '');
$Block_Tests[] = array(
	'Block_String' => 'New Page: [New Page][Name /][/Page].',
	'Expected_Result' => 'New Page: .');
// TODO "With"
// $Block_Tests[] = array(
// 	'Block_String' => 'New Page With Name = "New Page": [New Page With Name = "New Page"][Name /][/Page].',
// 	'Expected_Result' => 'New Page With Name = "New Page": New Page.');
$Block_Tests[] = array(
	'Block_String' => '[Format Expression 15 Digits 5 /]',
	'Expected_Result' => '00015');
$Block_Tests[] = array(
	'Block_String' => '[Format 15 Digits 5 /]',
	'Expected_Result' => '00015');
$Block_Tests[] = array(
	'Block_String' => '[Format Digits 5]15[/Format]',
	'Expected_Result' => '00015');
$Block_Tests[] = array(
	'Block_String' => 'PHP: [Page Where Name is "Home"][PHP]return "[Page.Name /]";[/PHP][/Page]',
	'Expected_Result' => 'PHP: Home');
$Block_Tests[] = array(
	'Block_String' => 'If With Condition (False): [If Condition 7<5]Yes![/If].',
	'Expected_Result' => 'If With Condition (False): .');
$Block_Tests[] = array(
	'Block_String' => 'If Implied Condition (False): [If 7<5]Yes![/If].',
	'Expected_Result' => 'If Implied Condition (False): .');
$Block_Tests[] = array(
	'Block_String' => '[If Condition 7<5][Then]Yes![/Then][Else]no[/Else][/If]',
	'Expected_Result' => 'no');
$Block_Tests[] = array(
	'Block_String' => '[If Condition 7>5][Then]Yes![/Then][Else]no[/Else][/If]',
	'Expected_Result' => 'Yes!');
$Block_Tests[] = array(
	'Block_String' => '[If Condition 7>5][Then]Yes![/Then][/If]',
	'Expected_Result' => 'Yes!');
$Block_Tests[] = array(
	'Block_String' => '[If Condition 7>5][Else]no[/Else][/If]',
	'Expected_Result' => '');
$Block_Tests[] = array(
	'Block_String' => '[If Condition 7>5]Yes![/If]',
	'Expected_Result' => 'Yes!');
// TODO: handle this gracefully
// $Block_Tests[] = array(
// 	'Block_String' => '[If]Yes![/If]',
// 	'Expected_Result' => 'Yes!');
// TODO: Set currently sets on current item. Should it add a new item to context? What about "This" getting mapped weirdly then?
$Block_Tests[] = array(
	'Block_String' => '[Page "Home"][For Variable X From 1 to 5]val [X /].[/For][/Page]',
	'Expected_Result' => 'val 1.val 2.val 3.val 4.val 5.');
$Block_Tests[] = array(
	'Block_String' => '[Page "Home"][For X From 1 to 5]val [X /].[/For][/Page]',
	'Expected_Result' => 'val 1.val 2.val 3.val 4.val 5.');
$Block_Tests[] = array(
	'Block_String' => '[While 5 > 7]is it?[/For]',
	'Expected_Result' => '');

// Set
// Property Exists, Value Types of Properties, Parent Exists, Value Type Matches, Target: Implied/This/Context/Global, To/Inner Value
// $Property_Exists = array(true, false);
// foreach (array(true, false) as $Parent_Exists)
// {
// 	foreach (array(true, false) as $Property_Exists)
// 	{
// 		if ($Property_Exists)
// 			echo "HEJ";
// 	}
// }
$Block_Tests[] = array(
	'Block_String' => '[Page Where Name is "Home"][Set Page.Name to "Tester" /][Page.Name /][/Page]',
	'Expected_Result' => 'Tester');
$Block_Tests[] = array(
	'Block_String' => '[Page Where Name is "Home"][Set Page.Name]Tester2[/Set][Page.Name /][/Page]',
	'Expected_Result' => 'Tester2');
$Block_Tests[] = array(
	'Block_String' => '[Page Where Name is "Home"][Set Name]Tester3[/Set][Page.Name /][/Page]',
	'Expected_Result' => 'Tester3');
$Block_Tests[] = array(
	'Block_String' => '[Page Where Name is "Home"][Set Name to "Tester4" /][Page.Name /][/Page]',
	'Expected_Result' => '');
	/*
*/

// TODO: tags in commands

$Memory_Stack_Reference = null;

// $Start_Index = 0;
// $End_Index = 2;

if (!isset($Start_Index))
	$Start_Index = 0;
if (!isset($End_Index))
	$End_Index = count($Block_Tests) - 1;

for ($Index = $Start_Index; $Index <= $End_Index; $Index++)

try
{
	$Block_Test = $Block_Tests[$Index];
	$Block_String = $Block_Test['Block_String'];
	
	$Traverse_String = 'Test:';
	traverse($Traverse_String);
	traverse($Block_Test);
	
	$Processed_Block = &Process_Block_String($Database, $Block_String, $Memory_Stack_Reference);
	// traverse($Processed_Block);
	
	$Traverse_String = 'Output:';
	traverse($Traverse_String);
	traverse($Processed_Block['Content']);
	
	$Expected_Result = $Block_Test['Expected_Result'];
	if ($Expected_Result)
	{
		if ($Processed_Block['Content'] != $Expected_Result)
		{
			throw new Exception('Does not match expected string: ' . $Expected_Result);
		}
	}
}
catch (exception $e)
{
// 	traverse($Block_Test);
	$Traverse_String = $e->getMessage();
	traverse($Traverse_String);
	throw $e;
}

?>