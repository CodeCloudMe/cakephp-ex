<?php

// Database

// Includes...

require_once('Connect_Database.php');
require_once('Load_XML_Files.php');
require_once('Load_XML_Files_Setup.php');
require_once('Reset_Database.php');
require_once('Query.php');
require_once('Get_Cached_Type.php');
require_once('Create_Item.php');
require_once('Create_Memory_Item.php');
require_once('Get_Database_Item.php');
require_once('Get_Not_Found_Item.php');
require_once('Get_Site_Item.php');
require_once('Get_Value.php');
require_once('Get_Value_With_Command_String.php');
require_once('Set_Value.php');
require_once('Set_Simple.php');
require_once('Save_Item.php');
require_once('Has_Property.php');
require_once('Get_Property.php');
require_once('Reset_Database_Item.php');
require_once('Move_Next.php');
require_once('Alias_From_Name.php');
require_once('Render_SQL_Expression_Tree.php');
require_once('Render_SQL_Name_Expression_Tree.php');
require_once('Add_Value.php');
require_once('Create_Value.php');
require_once('Delete_Item.php');
require_once('Is_Child_Type_Of.php');
require_once('Min_Parent_Type.php');
require_once('Is_Simple_Type.php');
require_once('As_Key.php');

require_once('Generate_ID.php');
require_once('Generate_Unique_Value.php');

require_once('Cache/Generate_Database_Cache.php');
require_once('Cache/Remove_Type_From_Cache.php');
require_once('Cache/Remove_Property_From_Cache.php');
require_once('Cache/Add_Type_To_Cache.php');
require_once('Cache/Add_Property_To_Cache.php');

// Constants...

// Relations
define('ONE_TO_ONE', 'one-to-one');
define('MANY_TO_ONE', 'many-to-one');
define('ONE_TO_MANY', 'one-to-many');
define('MANY_TO_MANY', 'many-to-many');
define('COMMUTATIVE', 'commutative');
$Relations = Array(
	ONE_TO_ONE => 'One-To-One',
	MANY_TO_ONE => 'Many-To-One',
	ONE_TO_MANY => 'One-To-Many',
	MANY_TO_MANY => 'Many-To-Many',
	COMMUTATIVE => 'Commutative'
);
$Relation_Inverses = Array(
	ONE_TO_ONE => &$Relations[ONE_TO_ONE],
	MANY_TO_ONE => &$Relations[ONE_TO_MANY],
	ONE_TO_MANY => &$Relations[MANY_TO_ONE],
	MANY_TO_MANY => &$Relations[MANY_TO_MANY],
	COMMUTATIVE => &$Relations[COMMUTATIVE]
);

$Simple_Types = Array('boolean', 'text', 'password', 'number', 'date', 'date_time', 'time', 'long_text');
$Date_Types = Array('date', 'time', 'date_time');

// TODO !?!?!? 
$Metadata_Properties = Array(
	'variables',
	'values',
	'namespace',
	'reverse_prefix',
	'type',
	'base_type',
	'specific_item',
	'is_item',
	'is_attachment_value',
	'attachment',
	'attachment_id',
	'attachment_type',
	
	'forward_property',
	'forward_properties',
	'attachment_property',
	'attachment_properties',
	'property',
	'properties',
	'reverse_property',
	'reverse_properties',
	'all_property',
	'all_properties',
	'item_forward_property',
	'item_forward_properties',
	'item_attachment_property',
	'item_attachment_properties',
	'item_property',
	'item_properties',
	'item_reverse_property',
	'item_reverse_properties',
	'item_all_property',
	'item_all_properties',
	
	'action',
	'actions',
	'index',
	'target',
	'template',
	'segment_count',
	'segment_by_value'
);

$Types_To_SQL_Types = Array(
	"boolean" => "BOOLEAN",
	"text" => "VARCHAR(250)",
	"password" => "VARCHAR(250)",
	"number" => "DOUBLE",
	"date" => "DATE",
	"date_time" => "DATETIME",
	"time" => "TIME",
	"long_text" => "LONGTEXT",
	"object" => "INT"
);

?>