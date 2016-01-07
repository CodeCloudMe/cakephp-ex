<?php


require_once($_SERVER['DOCUMENT_ROOT'].'/jelly/php/helpers/DBQueryFuncs.php');


function rollingUpdate($dbName1, $dbName2){


	$tablesFrom1 = getAllTables($dbName1);
	$tablesFrom2 = getAllTables($dbName2);


	//print_r($tablesFrom1);
	//print_r($tablesFrom2);

	//for each table
	for($i = 0; $i<count($tablesFrom1); $i++){
		$tableToCheck = $tablesFrom1[$i]['Tables_in_'.$dbName1];
		$exists = dbMassData("SELECT * FROM $dbName2.$tableToCheck");

		if($exists != NULL){

			
			echo($dbName1.".".$tableToCheck. " and " .$dbName2.".".$tableToCheck."<br><br>");
			$isCompatible = checkCompatible($dbName1.".".$tableToCheck, $dbName2.".".$tableToCheck);
			
			//TODO: make the compabitlity func work.. skipping for now for testing
			if($isCompatible['isCompat']==false){
				//echo('hey');
				//fromOneToTheOther($dbName1, $tableToCheck,  $dbName2, $tableToCheck);
			}

			//deleteAllLocal($dbName2, $tableToCheck);
			fromOneToTheOther($dbName1, $tableToCheck,  $dbName2, $tableToCheck);
			//echo('did it<br><hr><br><br><br>');
			//delete all local files from db2
			

		}
		else{

			echo("table does not exist creating... $dbName2.$tableToCheck <br><br>");
			dbQuery("CREATE TABLE $tableToCheck LIKE $dbName1.$tableToCheck ");
			dbQuery("INSERT $tableToCheck SELECT * FROM $dbName1.$tableToCheck;");
			echo('created...');
		}
	}

}


function deleteAllLocal($dbName, $tableName){

	dbQuery("DELETE FROM $dbName.$tableName WHERE Package = 'Local' OR Package IS NULL");
	return(true);
}


function getAllTables($dbName){

	$allTables = dbMassData("SHOW tables from $dbName");
	return $allTables;

}


function fromOneToTheOther($db1, $table1, $db2, $table2){

	//db1 and table1 is the from, db2 and table2 is the to

	$firstRecs = dbMassData("SELECT * FROM ".$db1.".".$table1." WHERE Package = 'Local' OR Package IS NULL");
	$secRecs = dbMassData("SELECT * FROM ".$db2.".".$table2." WHERE Package = 'Local' OR Package IS NULL");
	
	

	//print_r($firstRecs);
	for($i=0; $i<count($firstRecs); $i++){
		$insertString = "";
		$insertString1 = "";
		$insertString2 = "";
		$allKeys = array();
		$allVals = array();

		foreach ($firstRecs[$i] as $key => $value){


			if(in_array($key, $allKeys) == false){
				array_push($allKeys, $key);
			}
			array_push($allVals, $value);

			
		}

		//construct insert string
		for($j=0; $j <count($allKeys); $j++){

			if($j == 0){
				$insertString1 = $insertString1 .  "`".$allKeys[$j]."`";
				$insertString2 = $insertString2 .  "'".str_replace("'", "\'", $allVals[$j])."'";
			}
			else{
				$insertString1 = $insertString1 . ", `". $allKeys[$j]."`";
				$insertString2 = $insertString2 . ", '". str_replace("'", "\'", $allVals[$j])."'";

			}
			
		}

		$insertString = "INSERT INTO ".$db2.".".$table2." ($insertString1) VALUES ($insertString2)";
	
		//echo($insertString);

		dbQuery($insertString);
	}



	


}



rollingUpdate('harlem', 'better');
//fromOneToTheOther('harlem', 'Action', 'better', 'Action');



function getAllRecords($tableName, $dbName){


	$allRecords = dbMassData("SELECT * FROM $dbName.$tableName");

	return $allRecords;
}
 
function createCopyBackupTable($tableName, $dbName){

	dbQuery("DROP TABLE IF EXISTS $dbName.$tablename"."Backup;");

	dbQuery("CREATE TABLE ".$dbName.".".$tableName."Backup LIKE $dbName.$tableName");



}

function checkCompatible($dbAndTable1, $dbAndTable2){

	$recordsFrom1 = dbMassData("SHOW columns FROM $dbAndTable1");
	$recordsFrom2 = dbMassData("SHOW columns FROM $dbAndTable2");

	$compatTableNum = 0;
	$allNotCompat = array();

	for($i=0; $i<count($dbAndTable1); $i++){

		$foundCompatible = false;
		for($j=0; $j<count($dbAndTable2); $j++){

			if($dbAndTable1[$i]== $dbAndTable2[$j]){
				$compatTableNum = $compatTableNum+1;
				$foundCompatible= true;

			}
		}

		if($foundCompatible==false){

			array_push($allNotCompat, $dbAndTable1[$i]);
		}
	}

	if(count($dbAndTable1) ==$compatTableNum){
		return array("isCompat"=>true, "notCompat"=>$allNotCompat);
	}
	else{
		return array("isCompat"=>false, "notCompat"=>$allNotCompat);
	}

}

function makeCompatible($dbAndTable1, $dbAndTable2, $notCompatArr){

	//TODO: make the fields notCompatArr compatible



}

echo('no errors');



?>