<?php

function &htaccess_File_Data()
{
	$Script_Name = $_SERVER["SCRIPT_NAME"];
	$htaccess_File_Data = <<<EOL
ErrorDocument 404 $Script_Name
php_value upload_max_filesize 5000M
php_value post_max_size 5000M
EOL;
	return $htaccess_File_Data;
}

?>