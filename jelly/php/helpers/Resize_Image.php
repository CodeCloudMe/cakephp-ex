<?php

// Resize_Image

// TODO - Upgrade
// TODO - Support resizing animated gifs
// TODO - maybe include an image library that can do that, like imagecraft @ https://github.com/coldume/imagecraft

function Resize_Image($Parameters)
{
	// If the final size is the original size, copy the file instead of resizing.
	if (($Parameters["Final_Left"] == $Parameters["Source_Left"]) && ($Parameters["Final_Top"] == $Parameters["Source_Top"]) && ($Parameters["Final_Width"] == $Parameters["Source_Width"]) && ($Parameters["Final_Height"] == $Parameters["Source_Height"]))
	{
		return copy($Parameters["File_Path"], $Parameters["Resized_File_Path"]);
	}
	
	// Otherwise, build a new resized image
	else
	{
		// Load image
		$Image_Type = exif_imagetype($Parameters["File_Path"]);
		switch ($Image_Type)
		{
			case IMAGETYPE_GIF:
				if (!function_exists("ImageCreateFromGIF"))
					return false;
				$Image = @imagecreatefromgif($Parameters["File_Path"]);
				break;
			case IMAGETYPE_JPEG:
				if (!function_exists("ImageCreateFromJPEG"))
					return false;
				$Image = @imagecreatefromjpeg($Parameters["File_Path"]);
				break;
			case IMAGETYPE_PNG:
				if (!function_exists("ImageCreateFromPNG"))
					return false;
				$Image = @imagecreatefrompng($Parameters["File_Path"]);
				break;
			case IMAGETYPE_SWF:
			case IMAGETYPE_PSD:
			case IMAGETYPE_BMP:
			case IMAGETYPE_TIFF_II:
			case IMAGETYPE_TIFF_MM:
			case IMAGETYPE_JPC:
			case IMAGETYPE_JP2:
			case IMAGETYPE_JPX:
			case IMAGETYPE_JB2:
			case IMAGETYPE_SWC:
			case IMAGETYPE_IFF:
			case IMAGETYPE_WBMP:
			case IMAGETYPE_XBM:
			default:
				return false;
				break;
		}

		// Return if no image was loaded
		if (!$Image)
			return false;
	
		// Resize image
		$Resized_Image = ImageCreateTrueColor($Parameters["Final_Width"], $Parameters["Final_Height"]);
	
		// Preserve transparency
		switch ($Image_Type)
		{
			case IMAGETYPE_GIF:
				imagecolortransparent($Resized_Image, imagecolorallocatealpha($Resized_Image, 0, 0, 0, 127));
				imagealphablending($Resized_Image, false);
				imagesavealpha($Resized_Image, true);
				break;
			
			case IMAGETYPE_PNG:
				// TODO - (didn't think this through, maybe imagecolortransparent from the gif area is useful here too.)
				imagealphablending($Resized_Image, false);
				imagesavealpha($Resized_Image, true);
				break;
		}		
		
		if (!ImageCopyResampled(
			$Resized_Image,
			$Image,
			$Parameters["Final_Left"],
			$Parameters["Final_Top"],
			$Parameters["Source_Left"],
			$Parameters["Source_Top"],
			$Parameters["Final_Width"],
			$Parameters["Final_Height"],
			$Parameters["Source_Width"],
			$Parameters["Source_Height"]
		))
		{
			ImageDestroy($Resized_Image);
			ImageDestroy($Image);
			return false;
		}

		// Save image
		switch ($Image_Type)
		{
			case IMAGETYPE_GIF:
				$Success = ImageGIF($Resized_Image, $Parameters["Resized_File_Path"]);
				break;
			case IMAGETYPE_JPEG:
				$Success = ImageJPEG($Resized_Image, $Parameters["Resized_File_Path"], 100);
				break;
			case IMAGETYPE_PNG:
				$Success = ImagePNG($Resized_Image, $Parameters["Resized_File_Path"]);
				break;
		}
	
		// Clean up on success
		imagedestroy($Image);
		imagedestroy($Resized_Image);
	
		if (!$Success)
			return false;
	
		return true;
	}
}
?>