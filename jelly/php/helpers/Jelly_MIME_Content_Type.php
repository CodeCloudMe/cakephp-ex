<?php

// Jelly MIME Content Type

function Jelly_MIME_Content_Type($File_Path)
{
	$FInfo = finfo_open(FILEINFO_MIME_TYPE);
	$Mime_Type = finfo_file($FInfo, $File_Path);
	$FInfo = finfo_close($FInfo);
	return $Mime_Type;
	
	// TODO - older php would use mime_content_type
	
	// TODO - older older php, with permissions, might use the below around just in case the above is not installed 
// 	$File_Extension = pathinfo($f, PATHINFO_EXTENSION);
// 	switch ($File_Extension)
// 	{
// 		// Set explicit mime type for some extensions.
// 		case "mp3":
// 			return "audio/mp3";
// 			break;
// 		case "mov":
// 			return "video/quicktime";
// 			break;
// 		case "flv":
// 			return "video/x-flv";
// 			break;
// 		case "jpg":
// 		case "jpeg":
// 			return "image/jpg";
// 			break;
// 		case "gif":
// 			return "image/gif";
// 			break;
// 		case "png":
// 			return "image/png";
// 			break;
// 		default:
// 			// TODO: Doesn't run in windows...
// 			// Use unix command to probe file for mime type
// 			$Mime_Type = trim ( exec ('file -bi ' . escapeshellarg ( $f ) ) ) ;
// 			// Trim 'charset' string
// 			if (strpos($Mime_Type, ';') !== false)
// 				$Mime_Type = substr($Mime_Type, 0, strpos($Mime_Type, ';'));
// 			return $Mime_Type;
// 			break;
// 	}
}

?>