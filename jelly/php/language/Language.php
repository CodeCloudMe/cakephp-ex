<?php

// TODO Keywords to check:
	// Preserve_Variables
	// No_Wrap
	// No_Scripts
	// No_Refresh

// Includes...
require_once('Parse_String_Into_Command.php');
require_once('Parse_Clause_Into_Tree.php');
require_once('Parse_String_Into_Block.php');
require_once('Process_URL.php');
require_once('Process_Variables_String_Into_Item.php');
require_once('Process_Block.php');
require_once('Process_Block_With_Evaluation.php');
require_once('Process_Block_String.php');
require_once('Process_Command.php');
require_once('Process_Command_String.php');
require_once('Process_Tag.php');
require_once('Process_Error.php');
require_once('Render_Processed_Block.php');
require_once('Evaluate_Clause_Tree.php');
require_once('Get_Next_Path_Item.php');

// Context Lookup Steps
define('THIS_STEP', 0);
define('MEMORY_STEP', 1);
define('TEMPLATE_STEP', 2);
define('DATABASE_STEP', 3);
define('NOT_FOUND_STEP', 4);

// Namespace Delimiter
define('NAMESPACE_DELIMITER', '_');

// TODO: fix [http_host /] in templates

// Command Language Terms...
// TODO - maybe with values can be processed generally.
$Language_Terms = Array(
	'exit' => array('count', 'print'),
	'traverse' => array('target'),
	'echo' => array('text'),
	'add' => array('target', 'to', 'with', 'no_refresh'),
	'debug' => array('target', 'reference', 'variables', 'properties', 'type', 'exit'),
	'remove' => array('target', 'no_refresh'),
	'save' => array('target', 'as', 'no_refresh'),
	'move' => array('move_type', 'up', 'down', 'to'),
	'header' => array('header', 'value'),
	'external_script' => array(),
	'if' => array('condition', 'where', 'from'),
	'for' => array('variable', 'from', 'to', 'by'),
	'while' => array('condition'),
	'set' => array('target', 'to', '=', 'global', 'save', 'no_refresh'),
	'validate' => array('value', 'for'),
	'link' => array('to', 'inspect', 'edit', 'edit_inline', 'add', 'remove', 'attach', 'detach', 'move', 'up', 'down', 'with', 'by', 'item', 'into', 'as', 'execute', 'cancel', 'logout', 'next', 'previous', 'sort', 'direct', 'with_parent_property_alias', 'submit', 'value_type', 'create_inspector'),
	// TODO - should go be the same as link?
	'go' => array('to', 'inspect', 'edit', 'edit_inline', 'add', 'remove', 'attach', 'detach', 'move', 'up', 'down', 'with', 'by', 'item', 'into', 'as', 'execute', 'cancel', 'logout', 'next', 'previous', 'sort', 'direct', 'with_parent_property_alias', 'submit', 'value_type', 'create_inspector'),
	'clean' => array('whitespace', 'newlines'),
	'ignore' => array(),
	'format' => array('expression', 'as', 'quotes', 'lines', 'brackets', 'for', 'digits', 'decimals', 'characters'),
	'math' => array('expression'),
	'php' => array('preprocess'),
	'replace' => array('subject', 'with', 'in'),
	'authenticate' => array('team', 'ignore_preview_mode'),
	'admin' => array('ignore_preview_mode'),
	'manager' => array('ignore_preview_mode'),
	'guest' => array('ignore_preview_mode'),
	'geocode' => array('target'),
	'member' => array(),
	'bot' => array(),
	'browser' => array(),
	'logout' => array(),
	// TODO: clauses shouldn't interject other clauses' values??? (remember Attachment?)
	// TODO @core-language: word boundaries for clauses yeahh
	'default' => array('which', 'process_once', 'where', 'from', 'as', 'by', 'in', 'save', 'henceforth', 'at', 'include', 'excluding', 'including', 'ascending', 'descending', 'random', 'new', 'with', 'wrap', 'no_wrap', 'no_refresh', 'no_scripts', 'no_preloader', 'nolastpathwrap', 'preload', 'query', 'preserve_variables', 'preserve_namespace', 'parent_namespace', 'iterator_wrap_element', 'item_wrap_element', 'wrap_element', 'wrap_element_attributes', 'no_child_types', 'from_container', 'from_request', 'as_attachment', 'segment_by',  'iterator_classes', 'item_classes', 'restrict_type'),
	'container' => array('from'),
	'no_wrap' => array(),
	'no_scripts' => array(),
	'no_refresh' => array(),
	'ignore' => array(),
	'show' => array('result', 'error', 'for'),
	'cache_transformed_picture' => array('width', 'height', 'maximum_width', 'maximum_height'),
	'get_transformed_picture_file_size' => array('width', 'height', 'maximum_width', 'maximum_height'),
	'get_transformed_picture_path' => array('width', 'height', 'maximum_width', 'maximum_height'),
	'get_transformed_picture_width' => array('width', 'height', 'maximum_width', 'maximum_height'),
	'get_transformed_picture_height' => array('width', 'height', 'maximum_width', 'maximum_height'),
	'write_transformed_picture' => array('width', 'height', 'maximum_width', 'maximum_height'),
	'read_file' => array('path'),
	'file_size' => array('path'),
	'http_host' => array() // TODO: HTTP_Host should probably be in Globals or something
);

// Language Operators
// TODO @core-language get rid of partials? (i.e. Does Not)
// Copied from C language
// TODO @core-language re-order regex to put partials after longer matches (i.e. 'is' should come after 'is not')
// Implement more operators: between, sin/cos/tan, floor/ceiling/round, log/ln, sqrt, abs, sign, random
$Language_Operators = Array(
	// TODO  clean up order of listings, operations here.
	// TODO should these be type actions? is this misplaced?
	'is hash of' => Array('Mapped_Operator' => 'is hash of', 'Precedence' => 10, 'Associativity' => 'Left-To-Right', 'Term_Count' => 2, 'Regex_Character' => "\\bis hash of\\b"),
	'is parent type of' => Array('Mapped_Operator' => 'is parent type of', 'Precedence' => 10, 'Associativity' => 'Left-To-Right', 'Term_Count' => 2, 'Regex_Character' => "\\bis parent type of\\b"),
	'is child type of ' => Array('Mapped_Operator' => 'is child type of', 'Precedence' => 10, 'Associativity' => 'Left-To-Right', 'Term_Count' => 2, "Regex_Character" => "\\bis child type of\\b"),
	'is simple type' => Array('Mapped_Operator' => 'is simple type', 'Precedence' => 9, 'Associativity' => 'Left-To-Right', 'Term_Count' => 1, "Regex_Character" => "\\bis simple type\\b"),
	'is not simple type' => Array('Mapped_Operator' => 'is not simple type', 'Precedence' => 9, 'Associativity' => 'Left-To-Right', 'Term_Count' => 1, "Regex_Character" => "\\bis not simple type\\b"),
	'is complex type' => Array('Mapped_Operator' => 'is complex type', 'Precedence' => 9, 'Associativity' => 'Left-To-Right', 'Term_Count' => 1, "Regex_Character" => "\\bis complex type\\b"),
	// TODO: clean up
	"(" => Array("Precedence" => 0, "Associativity" => "Left-To-Right", "Regex_Character" => "\\("),
	")" => Array("Precedence" => 0, "Associativity" => "Left-To-Right", "Regex_Character" => "\\)"),
	// 1: Comma for Separating Expressions: ,
	"," => Array("Precedence" => 1, "Associativity" => "Left-To-Right", "Term_Count" => 2), // ?
	// 2: Assignment or Math With Assignment: =  +=  -=  *=  /=  %=  &=  ^=  |=  <<=  >>=
	// 3: Ternary: ?  :
	"between" => Array("Precedence" => 3, "Associativity" => "Right-To-Left", "Regex_Character" => "\\bbetween\\b"), // ?
	"is between" => Array("Precedence" => 3, "Associativity" => "Right-To-Left", "Regex_Character" => "\\bis between\\b"), // ?
	// 4: Logical OR: ||
	// TODO @core-language finish thinking about wrapping operators in white space
	"or" => Array("Mapped_Operator" => "||", "Precedence" => 4, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bor\\b"),
	"||" => Array("Mapped_Operator" => "||", "Precedence" => 4, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\|\\|"),
	// 5: Logical AND: &&
	"and" => Array("Mapped_Operator" => "&&", "Precedence" => 5, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\band\\b"),
	"&" => Array("Mapped_Operator" => "&&", "Precedence" => 5, "Associativity" => "Left-To_Right", "Term_Count" => 2),
	"&&" => Array("Mapped_Operator" => "&&", "Precedence" => 5, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	// 6: Bitwise OR: |
	// 7: Bitwise Exclusive OR: ^
	// 8: Bitwise AND: &
	// 9: Equal To/Is Not Equal To: ==  !=
	"=" => Array("Mapped_Operator" => "==", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	"is not" => Array("Mapped_Operator" => "!=", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bis not\\b"),
	"is" => Array("Mapped_Operator" => "==", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bis\\b"),
	"contains" => Array("Mapped_Operator" => "contains", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bcontains\\b"),
	"does not contain" => Array("Mapped_Operator" => "does_not_contain", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bdoes not contain\\b"),
	"starts with" => Array("Mapped_Operator" => "starts_with", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bstarts with\\b"),
	"does not start with" => Array("Mapped_Operator" => "does_not_start_with", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bdoes not start with\\b"),
	"ends with" => Array("Mapped_Operator" => "ends_with", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bends with\\b"),
	"does not end with" => Array("Mapped_Operator" => "does_not_end_with", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bdoes not end with\\b"),
	"!=" => Array("Mapped_Operator" => "!=", "Precedence" => 9, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	// 10: Less Than, Greater Than: <  <=  >  >=
	// TODO @feature-language Less Than or Equal To, Greater Than or Equal To
	"<" => Array("Mapped_Operator" => "<", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	"<=" => Array("Mapped_Operator" => "<=", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	"is less than" => Array("Mapped_Operator" => "<", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bis less than\\b"),
	"before" => Array("Mapped_Operator" => "<", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bbefore\\b"),
	"is before" => Array("Mapped_Operator" => "<", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bis before\\b"),
	">" => Array("Mapped_Operator" => ">", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	">=" => Array("Mapped_Operator" => ">=", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	"is greater than" => Array("Mapped_Operator" => ">", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bis greater than\\b"),
	"is more than" => Array("Mapped_Operator" => ">", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bis more than\\b"),
	"after" => Array("Mapped_Operator" => ">", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bafter\\b"),
	"is after" => Array("Mapped_Operator" => ">", "Precedence" => 10, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bis after\\b"),	
	// 11: Bitwise Shift Left/Right: <<  >>
	// 12: Addition/Subtraction: +  -
	"+" => Array("Mapped_Operator" => "+", "Precedence" => 12, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\+"),
	"-" => Array("Mapped_Operator" => "-", "Precedence" => 12, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	// 13: Multiplication/Division/Modulo: *  /  %
	"*" => Array("Mapped_Operator" => "*", "Precedence" => 13, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\*"),
	"/" => Array("Mapped_Operator" => "/", "Precedence" => 13, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\/"),
	"mod" => Array("Mapped_Operator" => "%", "Precedence" => 13, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\bmod\\b"),
	"%" => Array("Mapped_Operator" => "%", "Precedence" => 13, "Associativity" => "Left-To-Right", "Term_Count" => 2),
	"^" => Array("Mapped_Operator" => "pow", "Precedence" => 13, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\^"),
	// 14: Prefix Increment/Decrement, Unary Plus/Minus, Logical Negation, Prefix Unary: ++ --  + -  ! ~  (type)  *  &  sizeof
	"!" => Array("Mapped_Operator" => "!", "Precedence" => 14, "Associativity" => "Right-To-Left", "Term_Count" => 1),
	"not" => Array("Mapped_Operator" => "!", "Precedence" => 14, "Associativity" => "Right-To-Left", "Term_Count" => 1, "Regex_Character" => "\\bnot\\b"),
	// 15: Parentheses for function calls, Brackets for Member Selection, Postfix Increment/Decrement: ( )  [ ]  .  ->  ++ --
	"exists" => Array("Mapped_Operator" => "exists", "Precedence" => 15, "Associativity" => "Left-To-Right", "Term_Count" => 1, "Regex_Character" => "\\bexists\\b"),
	"does not exist" => Array("Mapped_Operator" => "does not exist", "Precedence" => 15, "Associativity" => "Left-To-Right", "Term_Count" => 1, "Regex_Character" => "\\bdoes not exist\\b"),
	// TODO - "is set" is getting caught by "is" above... check regex or something?
	"twas set" => Array("Mapped_Operator" => "twas set", "Precedence" => 15, "Associativity" => "Left-To-Right", "Term_Count" => 1, "Regex_Character" => "\\btwas set\\b"),
	"twas not set" => Array("Mapped_Operator" => "twas not set", "Precedence" => 15, "Associativity" => "Left-To-Right", "Term_Count" => 1, "Regex_Character" => "\\btwas not set\\b"),
	// TODO - wait a second, this is implemented above already, no? 
	"is simple" => Array("Mapped_Operator" => "is simple", "Precedence" => 15, "Associativity" => "Left-To-Right", "Term_Count" => 1, "Regex_Character" => "\\bis simple\\b"),
	"." => Array("Mapped_Operator" => ".", "Precedence" => 15, "Associativity" => "Left-To-Right", "Term_Count" => 2, "Regex_Character" => "\\."),
);

// Language Constants
$Language_Booleans = Array(
	'true', 
	'false'
);

$Language_Constants = Array(
	// TODO - i guess now is cached at the time of instantiation :)
	'now' => Array('Kind' => 'Date_Time', 'Value' => time()),
	'today' => Array('Kind' => 'Date', 'Value' => strtotime('today')),
	'tomorrow' => Array('Kind' => 'Date', 'Value' => strtotime('tomorrow')),
	'yesterday' => Array('Kind' => 'Date', 'Value' => strtotime('yesterday')),
);

// Reusable Regex Constants
$No_Preceeding_Slash_Regex = "(?<!\\\\)";

// Build Language Regex
$Command_Regex_Tokens = array();

foreach ($Language_Operators as $Operator_Token => &$Operator)
{
	if (isset($Operator["Regex_Character"]))
		$Command_Regex_Tokens[] = $Operator["Regex_Character"];
	else
		$Command_Regex_Tokens[] = $Operator_Token;
}
$Command_Regex_Tokens[] = "\\ ";
$Command_Regex_Tokens[] = "\\r";
$Command_Regex_Tokens[] = "\\n";
$Command_Regex_Tokens[] = "\\t";
$Command_Regex_Tokens[] = "\"";
$Language_Regex = "/" . $No_Preceeding_Slash_Regex . ("(" . implode("|", $Command_Regex_Tokens) . ")") . "/i";

$Block_Operators = array(
	"[*" => array("Regex_Character" => "\\[\\*"),
	"[/" => array("Regex_Character" => "\\[\\/"),
	"[" => array("Regex_Character" => "\\["),
	"/]" => array("Regex_Character" => "\\/\\]"),
	"]" => array("Regex_Character" => "\\]")
);
foreach ($Block_Operators as $Operator_Token => &$Operator)
{
	if (isset($Operator['Regex_Character']))
		$Block_Regex_Tokens[] = $Operator['Regex_Character'];
	else
		$Block_Regex_Tokens[] = $Operator_Token;
}
$Block_Regex = '/' . $No_Preceeding_Slash_Regex . ('(' . implode('|', $Block_Regex_Tokens) . ')') . '/';

// Table of standard php language format commands
$Standard_Date_Format_Commands = Array(
		'Month' => Array(
				'Name' => Array(
					'Short' =>'M',
					'Long' => 'F'
				),
				'Number' => Array(
					'Short' =>'n',
					'Long' => 'm'
				)
			),
		'Day' => Array(
				'Name' => Array(
					'Short' =>'D',
					'Long' => 'l'
				),

				// TODO - hm. this is 0 (for Sunday) through 6 (for Saturday), which is inconsistent with everything else. we can just remove days numbers for now? 
				'Number' => Array(
					'Short' =>'w'
				)
			),
		'Date' => Array(
				'Number' => Array(
					'Short' =>'j',
					'Long' => 'd'
				),
				'Other' => Array(
					'Suffix' =>'S'
				)

			),
		'Year' => Array(
				'Number' => Array(
					'Short' =>'y',
					'Long' => 'Y'
				)
			),
		'Hour' => Array(
				'Number' => Array(
					'12' => Array(
						'Short' =>'g',
						'Long' => 'h'
					),
					'24' => Array(
						'Short' =>'G',
						'Long' => 'H'
					)
				)
			),
		'Minute' => Array(
				'Number' => Array(
					'Long' => 'i'
				)
			),
		'Second' => Array(
				'Number' => Array(
					'Long' => 's'
				)
			),
		'Period' => Array(
				'Name' => Array(
					'Long' => 'A'
				),
			),
			
		'Other' => Array(
				'SQL_Value' => 'Y-m-d H:i:s'
			)
	); 

// Standard date format string mappings
$Standard_Date_Format_String_Mappings = Array(

		// Defaults
		'month' => $Standard_Date_Format_Commands['Month']['Name']['Long'],
		'day' => $Standard_Date_Format_Commands['Day']['Name']['Long'],
		'date' => $Standard_Date_Format_Commands['Date']['Number']['Short'],
		'year' => $Standard_Date_Format_Commands['Year']['Number']['Long'],
		'hour' => $Standard_Date_Format_Commands['Hour']['Number']['12']['Short'],
		'minute' => $Standard_Date_Format_Commands['Minute']['Number']['Long'],
		'second' => $Standard_Date_Format_Commands['Second']['Number']['Long'],
		'period' => $Standard_Date_Format_Commands['Period']['Name']['Long'],
	
		// Short defaults
		'short_month' => $Standard_Date_Format_Commands['Month']['Name']['Short'],
		'short_day' => $Standard_Date_Format_Commands['Day']['Name']['Short'],
		'short_date' => $Standard_Date_Format_Commands['Date']['Number']['Short'],
		'short_year' => $Standard_Date_Format_Commands['Year']['Number']['Short'],
		'short_hour' => $Standard_Date_Format_Commands['Hour']['Number']['12']['Short'],
		'short_hour_12' => $Standard_Date_Format_Commands['Hour']['Number']['12']['Short'],
		'short_hour_24' => $Standard_Date_Format_Commands['Hour']['Number']['24']['Short'],
	
		// Long defaults
		'long_month' => $Standard_Date_Format_Commands['Month']['Name']['Long'],
		'long_day' => $Standard_Date_Format_Commands['Day']['Name']['Long'],
		'long_date' => $Standard_Date_Format_Commands['Date']['Number']['Long'],
		'long_year' => $Standard_Date_Format_Commands['Year']['Number']['Long'],
		'long_hour' => $Standard_Date_Format_Commands['Hour']['Number']['12']['Long'],
		'long_hour_12' => $Standard_Date_Format_Commands['Hour']['Number']['12']['Long'],
		'long_hour_24' => $Standard_Date_Format_Commands['Hour']['Number']['24']['Long'],
		'long_minute' => $Standard_Date_Format_Commands['Minute']['Number']['Long'],
		'long_second' => $Standard_Date_Format_Commands['Second']['Number']['Long'],
		'long_period' => $Standard_Date_Format_Commands['Period']['Name']['Long'],		

		// Default names
		'month_name' => $Standard_Date_Format_Commands['Month']['Name']['Long'],
		'day_name' => $Standard_Date_Format_Commands['Day']['Name']['Long'],
		'period_name' => $Standard_Date_Format_Commands['Period']['Name']['Long'],

		// Short names
		'short_month_name' => $Standard_Date_Format_Commands['Month']['Name']['Short'],
		'short_day_name' => $Standard_Date_Format_Commands['Day']['Name']['Short'],
		// TODO - short period name

		// Long names
		'long_month_name' => $Standard_Date_Format_Commands['Month']['Name']['Long'],
		'long_day_name' => $Standard_Date_Format_Commands['Day']['Name']['Long'],
		'long_period_name' => $Standard_Date_Format_Commands['Period']['Name']['Long'],

		// Default numbers
		'month_number' => $Standard_Date_Format_Commands['Month']['Number']['Short'],
		'day_number' => $Standard_Date_Format_Commands['Day']['Number']['Short'],
		'date_number' => $Standard_Date_Format_Commands['Date']['Number']['Short'],
		'year_number' => $Standard_Date_Format_Commands['Year']['Number']['Long'],
		'hour_number' => $Standard_Date_Format_Commands['Hour']['Number']['12']['Short'],
		'minute_number' => $Standard_Date_Format_Commands['Minute']['Number']['Long'],
		'second_number' => $Standard_Date_Format_Commands['Second']['Number']['Long'],

		// Short numbers
		'short_month_number' => $Standard_Date_Format_Commands['Month']['Number']['Short'],
		'short_day_number' => $Standard_Date_Format_Commands['Day']['Number']['Short'],
		'short_date_number' => $Standard_Date_Format_Commands['Date']['Number']['Short'],
		'short_year_number' => $Standard_Date_Format_Commands['Year']['Number']['Short'],
		'short_hour_12_number' => $Standard_Date_Format_Commands['Hour']['Number']['12']['Short'],
		'short_hour_24_number' => $Standard_Date_Format_Commands['Hour']['Number']['24']['Short'],

		// Long numbers
		'long_month_number' => $Standard_Date_Format_Commands['Month']['Number']['Long'],
		'long_date_number' => $Standard_Date_Format_Commands['Date']['Number']['Long'],
		'long_year_number' => $Standard_Date_Format_Commands['Year']['Number']['Long'],
		'long_minute_number' => $Standard_Date_Format_Commands['Minute']['Number']['Long'],
		'long_second_number' => $Standard_Date_Format_Commands['Second']['Number']['Long'],
		'long_hour_12_number' => $Standard_Date_Format_Commands['Hour']['Number']['12']['Long'],
		'long_hour_24_number' => $Standard_Date_Format_Commands['Hour']['Number']['24']['Long'],

		// Extras
		'date_suffix' => $Standard_Date_Format_Commands['Date']['Other']['Suffix'],
		'sql_value' => $Standard_Date_Format_Commands['Other']['SQL_Value'],
	);

// Non-standard date format strings
$Non_Standard_Date_Format_Strings = Array('short_period_name', 'short_period', 'unix_value', 'relative_time');
	
// All date format strings
$All_Date_Format_Strings = array_merge(array_keys($Standard_Date_Format_String_Mappings), $Non_Standard_Date_Format_Strings); 

?>