<?php

function &Extract_Post_Variables(&$Database)
{
// 	traverse($_POST);
// 	traverse($_FILES);

	// TODO - maybe things should be deferenced first, and the value set cleaned, and then everything should be saved? might be too simple though.

	$Debug = false;
	
	// Any relevant post values get submitted
	$Form_Type = &Get_Cached_Type($Database, 'Item');
	$Form_Item = &Create_Memory_Item($Database, $Form_Type);
	Set_Simple($Form_Item, 'Name', 'Form');
	
	// Note: $Post_Name and $Post_Value not by reference
	foreach ($_POST as $Post_Name => &$Post_Value)
	{
		if ($Debug)
			traverse("Name: $Post_Name");

		// Strip slashes if PHP adds them automatically to POST
		// TODO: is this necessary?
		if (get_magic_quotes_gpc())
			$Post_Value = &New_String(stripslashes($Post_Value));
		
		// Dereference items
		$Cached_Type_Lookup = &$Database['Cached_Type_Lookup'];
		
// 		echo $Post_Name;
		if (isset($_POST[$Post_Name . "_Type"]) && isset($Cached_Type_Lookup[strtolower($_POST[$Post_Name . "_Type"])]))
		{
			$Post_Item_Type_Lookup = &New_String($_POST[$Post_Name . "_Type"]);
			$Post_Item_Type = &Get_Cached_Type($Database, $Post_Item_Type_Lookup);
			$Post_Item_Type_Alias = &$Post_Item_Type['Alias'];
			
// 			echo $Post_Item_Type_Lookup;
// 			echo $Post_Item_Type_Alias;
			
			// Preprocess inputs
			switch (strtolower($Post_Item_Type_Alias))
			{	
				// Only pass-through non-blank passwords.
				case 'password':
					if (!(is_null($Post_Value) || $Post_Value == ''))
						Set_Value($Form_Item, $Post_Name, $Post_Value);
					break;

				// Interpret multi-part dates and times
				// TODO
				case "date":
				case "time":
				case "date_time":
				{
					$Post_Item = &New_Number(strtotime($Post_Value));;
					Set_Value($Form_Item, $Post_Name, $Post_Item);
					break;
				}
			
				case "json":
					break;
				
				// TODO
				case "file":
				case "picture":
				case "sound":
				case "video":
				{
					// Check if upload meta POST property is set
					if (isset($_POST[$Post_Name . "_Upload"]) && strtolower($_POST[$Post_Name . "_Upload"]) == "true")
					{
// 						echo "FILEEEEEE"
// 						print_r($_POST);
// 						print_r($_FILES);
					
// 						if ($Debug)
// 							echo "CHECK FILE" . $Post_Name . ":" . $Post_Value . "\n";
						
						// Check if file was uploaded in $_FILES
						if ($_FILES[$Post_Value])
						{
// 							if ($Debug)
// 								echo "FILE";
						
							$Upload_Descriptor = $_FILES[$Post_Value];
							// If uploading from Flash, double quotes fail in file name so get file name from Flash-specific Filename property
							// TODO: is urldecode correct here? or just for our current uploader library
							$Upload_Name = urldecode($Upload_Descriptor["name"]);
						
							// Check upload for errors.
							if ($Upload_Descriptor["error"])
							{
								$Upload_Errors = array(
									UPLOAD_ERR_OK => "No error.",
									UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
									UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
									UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
									UPLOAD_ERR_NO_FILE => "No file was uploaded.",
									UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
									UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
									UPLOAD_ERR_EXTENSION => "File upload stopped by extension.",
								);
							
								if ($Upload_Descriptor["error"] == UPLOAD_ERR_INI_SIZE)
								{
									echo "<script>alert('Could not upload file because it was larger than the maximum supported size of " . ini_get("upload_max_filesize") . ".');</script>";
									continue 2;
								}
								else
								{
									$Error_Text = $Upload_Errors[$Upload_Descriptor["error"]];
									echo "<script>alert('Could not upload file. Error: $Error_Text');</script>";
									continue 2;
								}
							}
						
							// Get specified file type
							$Specified_File_Type_Lookup = $_POST[$Post_Name . "_Type"];
							$Specified_File_Type = &Get_Cached_Type($Database, $Specified_File_Type_Lookup);
							$Specified_File_Type_Alias = &$Specified_File_Type['Alias'];
						
							// Move file to a permanent location and set URL.
							// TODO: is this the correct permissions
							// TODO: should create any directories that don't exist
							$Specified_File_Type_Path = $GLOBALS['Files_Directory_Path'] . '/' . $Specified_File_Type_Alias . '/';
							if (!is_dir($Specified_File_Type_Path))
								mkdir($Specified_File_Type_Path, 0777);
							$File_Path = Unique_File_Name($Specified_File_Type_Path . $Upload_Name);
							if (!move_uploaded_file($Upload_Descriptor["tmp_name"], $File_Path))
							{
								echo "<script>alert('Could not move uploaded temporary file.');</script>";
								continue 2;
							}
							// TODO: necessary?
							chmod($File_Path, 0755);
						
							// Get MIME content type, file content type.
							$File_Mime_Type = Jelly_MIME_Content_Type($File_Path);
							$File_Mime_Type_Part = substr($File_Mime_Type, 0, strpos($File_Mime_Type, '/'));
							switch (strtolower($File_Mime_Type_Part))
							{
								case "image":
									$File_Type_Lookup = "Picture";
									break;
								case "video":
									$File_Type_Lookup = "Video";
									break;
								case "audio":
									$File_Type_Lookup = "Sound";
									break;
								default:
									$File_Type_Lookup = "File";
									break;
							}
							$File_Type = &Get_Cached_Type($Database, $File_Type_Lookup);
							$File_Type_Alias = &$File_Type['Alias'];
						
							// Verify file content type with specified file type.
							// TODO fix mime types
							if (!Is_Child_Type_Of($File_Type, $Specified_File_Type))
							{
								// If the file type is not a child of the specified file type, the uploaded file is incompatible for this item.
								echo "<script>alert(\"Incompatible file types: Uploaded a $File_Type_Alias for an $Specified_File_Type_Alias item\");</script>";
								continue 2;	

							}

							// TODO: Determine file type specific errors?
							switch (strtolower($File_Type_Alias))
							{
								case "picture":
								// TODO: intelligent picture constraints?
								{
									list($Image_Width, $Image_Height) = getimagesize($File_Path);
									
									// Check maximum image size
									if ($Image_Width * $Image_Height > 10000000)
									{
										$Result .= "<script>alert('Image too large. Please try to keep under 4000x2500 pixels.');</script>";
										echo $Result;
										continue 2;
									}
							
									if ($Image_Width * $Image_Height == 0)
									{
										$Result .= "<script>HideWindow('Loading');</script>";
										$Result .= "<script>alert('Uploaded file was not an image. Please select another.');</script>";
										echo $Result;
										continue 2;
									}						
									
									break;
								}		
								
								case "video":
									break;
								case "sound":
									break;
							}
						
						
							// Reference a file item
							// TODO - for 'Edit'-ing files
							// TODO - isn't used anywhere else.
							if (isset($GLOBALS["Type_Action_Target"]))
							{
								// TODO: Remove existing file.
								// TODO: Update file type to the uploaded file type?
								$File_Item = &$GLOBALS["Type_Action_Target"];
						
								// TODO: upgrade incorrect class to correct subclass (don't do this by deleting/recreating since that changes IDs)
								/*
								// Destroy referenced parent type item if it doesn't match file content type.
								// TODO: AM I INSANE?? -KGAMC
								if ($File_Type != $Specified_File_Type)
								{
									if ($Debug)
										echo "DELETING WRONG TYPE FILE\n";
									$Last_File_Name = &Get_Value($File_Item, "Name");
									Delete_Item($File_Item);
									unset($File_Item);
								}
								*/
							}
						
							// Create a file item if no reference was found.
							if (!isset($File_Item))
							{
								if ($Debug)
									echo "CREATING FILE ITEM";
								$File_Item = &Create_Memory_Item($Database, $File_Type);
								Set_Value($File_Item, "Name", $Upload_Name);
								Set_Simple($File_Item, 'Status', 'Unsaved');
								Save_Item($File_Item);
								// TODO make sure setting name here is okay
							}
						
// 							if ($Debug)
// 							{
// 								echo "SETTING PATH ETC\n";
// 								echo $File_Path . "!\n";
// 								echo $File_Mime_Type . "!\n";
// 								echo $File_Host . "!\n";
// 							}
						
							// Set file item path, mime type, and local
							Set_Value($File_Item, "Path", $File_Path);
							Set_Value($File_Item ,"Mime_Type", $File_Mime_Type);
// 							$File_Host = "Local";
// 							Set_Value($File_Item, "Host", $File_Host);
							
							// Set specific values
							switch (strtolower($File_Type['Alias']))
							{
								case "picture":
								{
									Set_Value($File_Item, "Width", $Image_Width);
									Set_Value($File_Item, "Height", $Image_Height);		
									Set_Value($File_Item, "Aspect_Ratio", New_Number($Image_Width / $Image_Height));
									break;
								}
							}

							// Save item
							// TODO: why wasn't this here before?
							// TODO - make sure that the replacement below works (I commented out the temporary line, because it should be automatic judging by this code)
							// Save_Item($File_Item, Array("Temporary" => $File_Item["Values"]["Temporary"]));
							Save_Item($File_Item);
						
							// TODO: Not sure what Post_Item is here for.
							// TODO: seems to do it below anyway
							$Post_Value = &$File_Item;
							
							Set_Value($Form_Item, "Target", $File_Item);
						
							break;
						}

					}
					//break;
					// NO BREAK (treat files and pictures by reference (instead of uploads) as default)
				}
			
				// Convert item references into items
				default:
				{
					// Look up item by default key
					// TODO: Alias vs. ID debate (i.e. Types/Templates/Properties/Items)
					//TODO: Can perhaps move to set_value, but i think this is better?
//								echo $Post_Name;
					$Post_Item_Key_Lookup = &$Post_Item_Type["Default_Key"];
					$Post_Item_Key_Property = &$Post_Item_Type["Cached_Property_Lookup"][strtolower($Post_Item_Key_Lookup)];
					$Post_Item_Key_Property_Alias = &$Post_Item_Key_Property["Alias"];
					
					// Check if post item is null
					// TODO: this is a hack until we find better way to do null values
					if (!$Post_Value || strtolower($Post_Value) == 'null')
					{
						// Make an end-of-results query of the correct type
						// TODO: this is a hack
						// TODO Keep it hacky
						$Post_Item_Command_String = &New_String($Post_Item_Type_Alias . ' Where ' . '1 = 0');
					}
					else
					{
						// Use the Post_Value as the item's key
						$Post_Item_Key = &New_String($Post_Value);
					
						// Get the post item from the database
					
						// TODO: !!NOTSECURE!! sanitize lookup
						// TODO: query should include temporary items
						$Post_Item_Command_String = &New_String('1 ' . $Post_Item_Type_Alias . ' Where ' . $Post_Item_Key_Property_Alias . ' = ' . '"'. $Post_Value . '"');
					}
					
					$Post_Item_Command = &Parse_String_Into_Command($Post_Item_Command_String);
					$Post_Item = &Get_Database_Item($Database, $Post_Item_Command);
				
					// Store post item in form item
// 						traverse($Form_Item);					
					Set_Value($Form_Item, $Post_Name, $Post_Item);
// 						traverse($Form_Item);

					break;
				}
			}
		}
		else
		{
			// TODO think about hacks (i.e. submitting an "_Original" in _POST)/
			// TODO restrict things like "tags-in-tags"
			// TODO - better name for original.
			
			$Post_Name_With_Original = &New_String($Post_Name . "_Original");
			$Post_Name_Without_Original = &New_String($Post_Name);
			
			// TODO: better type-handling
			$Original_Post_Value = &New_String($Post_Value);
			
			// Sanitize post item (escape brackets)
			$Cleaned_Post_Value = &New_String(str_replace("]", "\\]", str_replace("[", "\\[", $Post_Value)));
			
			// Store post value in form item
			Set_Value($Form_Item, $Post_Name_With_Original, $Original_Post_Value);
			Set_Value($Form_Item, $Post_Name_Without_Original, $Cleaned_Post_Value);
		}
	}
	
	if ($Debug)
		traverse($Form_Item);
	
	return $Form_Item;
}

?>