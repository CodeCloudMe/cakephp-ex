<?php

// Jelly Format

function &Jelly_Format(&$Value, $Style)
{
	$Result = $Value;
	
	switch (strtolower($Style))
	{
		case 'scss':
			$Sass_Compiler = new Sass();
			$CSS = $Sass_Compiler->compile($Result);
			$Result = $CSS;
			break;
		case 'path':
			// Same as alias, but don't replace slash, colon, equals, comma
			$Result = ereg_replace("[( )\-_\.]+", "_", $Result);
			$Result = ereg_replace("[^_/:=,[:alnum:]]", "", $Result);
			break;
		case "alias":
			// Replace groups of punctuation with an underscore
			$Result = preg_replace('/[( )\-_\.]+/', '_', $Value);
			// Remove non alphanumerics/underscores
			$Result = preg_replace('/[^_[:alnum:]]/', '', $Result);
			// Trim trailing or leading underscores
			$Result = trim($Result, '_');
			break;
		case "javascript string":
			$Result = addslashes($Result);
			$Result = str_replace("\n", "\\n", $Result);
			$Result = str_replace("\r", "\\r", $Result);
			break;
		case "jelly text block":
			// Escape brackets
			$Result = str_replace("[", "\\[", $Result);
			$Result = str_replace("]", "\\]", $Result);
			
			break;
		case "jelly attribute":
			// Escape slashes.
			$Result = str_replace("\\", "\\\\", $Result);

			// Escape double-quotes.
			$Result = str_replace("\"", "\\\"", $Result);
			
			// Escape brackets
			$Result = str_replace("[", "\\[", $Result);
			$Result = str_replace("]", "\\]", $Result);
			
			break;
		case "base64":
			$Result = base64_encode($Value);
			break;
		case "json":
			// TODO: think about json_encode options
			$Result = json_encode($Value);
			break;
		case "file name":
			$Result = addslashes($Result);
			$Result = str_replace("\n", " ", $Result);
			$Result = str_replace("\r", " ", $Result);
			$Result = str_replace("/", "-", $Result);
			$Result = str_replace(":", "-", $Result);
			break;
		case "php single quoted string":
			$Result = str_replace("\\", "\\\\", $Result);
			$Result = str_replace("'", "\\'", $Result);
			break;
		case "php string":
			// TODO - rethink these and get quotes right.
			$Result = str_replace("\"", "\\\"", $Result);
			$Result = str_replace("\n", "\\n", $Result);
			$Result = str_replace("\r", "\\r", $Result);
			break;
		case "ical string":
			$Result = addslashes($Result);
			$Result = str_replace("\n", "\\n", $Result);
			$Result = str_replace("\r", "\\r", $Result);
			$Result = strip_tags($Result);
			break;
		case "html title":
			$Result = str_replace("\n", " ", $Result);
			$Result = str_replace("\r", " ", $Result);
			$Result = strip_tags($Result);
			break;
		// TODO: standardize HTML quoted string escaping (javascript/attributes, etc)
		case "html attribute":
			$Result = htmlspecialchars($Result);
			$Result = str_replace("\n", "&#10;", $Result);
			$Result = str_replace("\r", "&#13;", $Result);
			break;
		case "plain text":
			$Result = strip_tags($Result);
			break;
		case "single line":
			$Result = str_replace("\n", '', $Result);
			$Result = str_replace("\r", '', $Result);
			break;
		case "single line code":
			$Result_Parts = explode("\n", $Result);
			$New_Result_Parts = array();
			foreach ($Result_Parts as $Result_Part)
				$New_Result_Parts[] = trim($Result_Part);
			$Result = implode("", $New_Result_Parts);
			break;
		case "uppercase":
			$Result = strtoupper($Result);
			break;
		case "lowercase":
			$Result = strtolower($Result);
			break;
		case "html text area":
		case "xml data":
			$Result = htmlspecialchars($Result);
			break;
		case "url":
			$Result = rawurlencode($Result);
			break;
		case "":
			$Result = "Missing 'As' in Format Tag";
			break;
		default:
			throw new Exception("Unknown 'Format' type: " . $Style);
			$Result = "Unknown 'Format' type: " . $Style;
			break;
	}
	return New_String($Result);
}

?>