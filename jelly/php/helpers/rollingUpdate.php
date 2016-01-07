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

			deleteAllLocal($dbName2, $tableToCheck);

			makeCompat($dbName1, $tableToCheck,  $dbName2, $tableToCheck);

			fromOneToTheOther($dbName1, $tableToCheck,  $dbName2, $tableToCheck);
			//echo('did it<br><hr><br><br><br>');
			//delete all local files from db2
			

		}
		else{

			echo("table does not exist creating... $dbName2.$tableToCheck <br><br>");

			dbQuery("DROP TABLE $dbName2.$tableToCheck");
			dbQuery("CREATE TABLE $dbName2.$tableToCheck LIKE $dbName1.$tableToCheck ");
			dbQuery("INSERT $dbName2.$tableToCheck SELECT * FROM $dbName1.$tableToCheck;");
			echo('created...');
		}
	}

	doExceptions($dbName2);


}


function makeCompat($db1, $table1, $db2, $table2){

	$t1Results = dbMassData("SELECT * FROM $db1.$table1");
	$t2Results = dbMassData("SELECT * FROM $db2.$table2");

	if($t1Results == NULL || $t2Results == NULL){
		return array("status"=>"fail", "msg"=>"one of tables is empty");
	}

	$keys1 = array();
	$keys2= array();
	print_r($t1Results[0]);
	print_r($t2Results[0]);

	foreach ($t1Results[0] as $key => $value){
		array_push($keys1, $key);

	}

	foreach ($t2Results[0] as $key => $value){
		array_push($keys2, $key);

	}

	$keys3 = array("a"=>$keys1);
	$keys4 = array("b"=>$keys2);


	$diffs = array_diff($keys3, $keys4);

	echo('keys3=');
	print_r($keys3);

	echo('keys4=');
	print_r($keys4);

	echo('diffs=');
	print_r($diffs);
	echo("<br><br>");

	for($i=0; $i<count($diffs); $i++){
		$colName = $diffs[$i];

		dbQuery("ALTER TABLE  $db2.$table2 ADD  `$colName` TEXT NOT NULL;");
		echo("ALTER TABLE  $db2.$table2 ADD  `$colName` TEXT NOT NULL;");
	
	}
	echo('found differences!');

	return $diffs;
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



function doExceptions($dbName){


	//TODO: figure out why this stuff we ahve to add at the end doesn't happen naturally
	
	dbQuery("ALTER TABLE  $dbName.Event ADD  `What_are_you_selling` TEXT NOT NULL;");
	echo("ALTER TABLE  $dbName.Event ADD  `What_are_you_selling` TEXT NOT NULL;");

	return true;
}

?>