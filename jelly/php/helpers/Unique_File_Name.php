<?php

// Unique File Name

// TODO - Upgrade

function Unique_File_Name($FilePath)
{
	// Create path to new file if doesn't already exist
	$File_Path_Parts = explode("/", $FilePath);
	$New_File_Path = "";
	for ($File_Path_Index = 0; $File_Path_Index < count($File_Path_Parts) - 1; $File_Path_Index++)
	{
		$File_Path_Part = $File_Path_Parts[$File_Path_Index];
		
		if ($New_File_Path)
			$New_File_Path .= "/";
		$New_File_Path .= $File_Path_Part;
		
		if (!file_exists($New_File_Path))
			mkdir($New_File_Path);
	}
	
	if (!file_exists($FilePath))
		return $FilePath;
	
	$FileName = basename($FilePath);
	$DotPosition = strrpos($FileName, ".");
	$SlashPosition = strrpos($FilePath, "/");
	$FileDirectory = substr($FilePath, 0, $SlashPosition + 1);
	if ($DotPosition === false)
		$FileRoot = $FileName;
	else
	{
		$FileRoot = substr($FileName, 0, $DotPosition);
		$FileSuffix = substr($FileName, $DotPosition + 1);
	}
	$NewPath = $FileDirectory . $FileName;
	$FileIndex = 0;
	while (file_exists($NewPath))
	{
		$FileIndex++;
		$NewPath = $FileDirectory . $FileRoot . "_" . $FileIndex . "." . $FileSuffix;
	}
	return $NewPath;
}
?>