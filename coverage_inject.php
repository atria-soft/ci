<?php
// force display of error
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Paris');

// check if all is here ...
/*
if (isset($_GET) == FALSE) {
	echo "[ERROR] Missing GET";
} else if (isset($_GET['JSON_FILE']) == FALSE) {
	echo "[ERROR] Missing GET : 'JSON_FILE'";
} else if (isset($_GET['LIB_NAME']) == FALSE) {
	echo "[ERROR] Missing GET : 'LIB_NAME'";
} else if (isset($_GET['LIB_BRANCH']) == FALSE) {
	echo "[ERROR] Missing GET : 'LIB_BRANCH'";
} else {
	
	echo "[OK] registered done";
}
echo "\n";
*/
if (isset($_POST) == FALSE) {
	die("[ERROR] Missing POST");
}
if (isset($_POST['REPO']) == FALSE) {
	die("[ERROR] Missing POST : 'REPO' (max string 256 char)");
}
if (isset($_POST['JSON_FILE']) == FALSE) {
	die("[ERROR] Missing POST : 'JSON_FILE'");
}
if (isset($_POST['LIB_BRANCH']) == FALSE) {
	die("[ERROR] Missing POST : 'LIB_BRANCH' (max string 256 char)");
}
if (isset($_POST['SHA1']) == FALSE) {
	die("[ERROR] Missing POST : 'SHA1' (max string 256 char)");
}
// check json data:
$val = json_decode($_POST['JSON_FILE'], true);
if (    $val == NULL
     && strlen($_POST['JSON_FILE']) > 0) {
	switch (json_last_error()) {
		case JSON_ERROR_NONE:
			die("[ERROR] JSON parse: JSON_ERROR_NONE");
		case JSON_ERROR_DEPTH:
			die("[ERROR] JSON parse: JSON_ERROR_DEPTH");
		case JSON_ERROR_STATE_MISMATCH:
			die("[ERROR] JSON parse: JSON_ERROR_STATE_MISMATCH");
		case JSON_ERROR_CTRL_CHAR:
			die("[ERROR] JSON parse: JSON_ERROR_CTRL_CHAR");
		case JSON_ERROR_SYNTAX:
			die("[ERROR] JSON parse: JSON_ERROR_SYNTAX");
		default:
			die("[ERROR] JSON parse: ???");
	}
	die("[ERROR] JSON parse: ".json_last_error_msg());
}

if (isset($val["executed"]) == FALSE) {
	die("[ERROR] Missing JSON data (root): 'executed'");
}
if (isset($val["executable"]) == FALSE) {
	die("[ERROR] Missing JSON data (root): 'executable'");
}
$newJsonData = array();
if (isset($val["list"]) == TRUE) {
	foreach ($val["list"] as $value) {
		if (isset($value['file']) == FALSE) {
			die("[ERROR] Missing JSON data (list): file");
		}
		if (isset($value['executed']) == FALSE) {
			die("[ERROR] Missing JSON data (list): executed");
		}
		if (isset($value['executable']) == FALSE) {
			die("[ERROR] Missing JSON data (list): executable");
		}
		array_push($newJsonData, array("file" => $value['file'], "executed" => $value['executed'], "executable" => $value['executable']));
	}
}
$dataJSON = json_encode($newJsonData);

list($userName, $libName) = explode("/", $_POST['REPO'], 2);
//die("[ERROR] test : ".$userName."  ".$libName);
if ($userName == "") {
	die("[ERROR] missing the user-name in '".$_POST['REPO']."'");
}
if ($libName == "") {
	die("[ERROR] missing the lib-name in '".$_POST['REPO']."'");
}

include_once("connect.php");
@include_once("connect_server.php");

$COVERAGE_bdd = mysqli_connect($sqlServer, $sqlLogin, $sqlPass, $sqlBDD);
/* Check connection */
if (mysqli_connect_errno()) {
	die("[ERROR] my-SQL-connection ERROR: '".mysqli_connect_error()."'");
}

$idGroup = -1;
// first step : check if the group exist ...
$sql = " SELECT * FROM `CI_group`"
      ." WHERE     `CI_group`.`user-name` = '".$userName."'"
      ."       AND `CI_group`.`lib-name` = '".$libName."'"
      ."       AND `CI_group`.`lib-branch` = '".$_POST['LIB_BRANCH']."'"
      ." LIMIT 1";
$result = $COVERAGE_bdd->query($sql);
$exist = TRUE;
if ($result == NULL) {
	// no result ...
	$exist = FALSE;
}
//echo("find result : ".$result);

if ($result->num_rows > 0) {
	$userGroup = $result->fetch_assoc();
	$idGroup = $userGroup['id'];
} else {
	$exist = FALSE;
}
if ($exist == FALSE) {
	// create a new one ...
	$sql = " INSERT INTO `CI_group` (`user-name`, `lib-name`, `lib-branch`)"
	      ." VALUES ('".$userName."',"
	      ."         '".$libName."',"
	      ."         '".$_POST['LIB_BRANCH']."')";
	$result = $COVERAGE_bdd->query($sql);
	if ($result == TRUE) {
		$exist == TRUE;
		$idGroup = $COVERAGE_bdd->insert_id;
	} else {
		echo "[ERROR] Can not CREATE new group ...";
	}
}

if ($idGroup <= -1) {
	echo("[ERROR] can not create or find group");
} else {
	$sql = " INSERT INTO `COVERAGE_list` (`time`, `id-group`, `sha1`, `executed`, `executable`)"
	      ." VALUES ('".time()."',"
	      ."         '".$idGroup."',"
	      ."         '".$_POST['SHA1']."',"
	      ."         '".$val["executed"]."',"
	      ."         '".$val["executable"]."')";
	//echo $sql;
	$result = $COVERAGE_bdd->query($sql);
	if ($result == FALSE) {
		echo("[ERROR] Can not register in db ... (LIST)");
	} else {
		// get the id inserted
		$idList = $COVERAGE_bdd->insert_id;
		// try to update the curent values:
		$sql = " UPDATE `COVERAGE_snapshot`"
		      ." SET   `COVERAGE_snapshot`.`id-list` = '".$idList."'"
		      ."     , `COVERAGE_snapshot`.`json` = '".$dataJSON."'"
		      ." WHERE `COVERAGE_snapshot`.`id-group` = '".$idGroup."'";
		$result = $COVERAGE_bdd->query($sql);
		if ($result == TRUE) {
			echo("[OK] registered done");
		} else {
			$sql = " INSERT INTO `COVERAGE_snapshot` (`id-group`, `id-list`, `json`)"
			      ." VALUES ('".$idGroup."',"
			      ."         '".$idList."',"
			      ."         '".$dataJSON."')";
			$result = $COVERAGE_bdd->query($sql);
			if ($result == TRUE) {
				echo("[OK] registered done");
			} else {
				echo("[ERROR] Can not register in db ... (snapshot)");
			}
		}
	}
}
// simply close link with the DB...
$COVERAGE_bdd->close();

?>