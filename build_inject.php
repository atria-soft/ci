<?php
// force display of error
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Europe/Paris');

if (isset($_POST) == FALSE) {
	die("[ERROR] Missing POST");
}
if (isset($_POST['REPO']) == FALSE) {
	die("[ERROR] Missing POST : 'REPO' (max string 256 char)");
}
if (isset($_POST['TAG']) == FALSE) {
	die("[ERROR] Missing POST : 'TAG'");
}
if (isset($_POST['ID']) == FALSE) {
	die("[ERROR] Missing POST : 'ID'");
}
if (isset($_POST['STATUS']) == FALSE) {
	die("[ERROR] Missing POST : 'STATUS'");
}
if (isset($_POST['LIB_BRANCH']) == FALSE) {
	die("[ERROR] Missing POST : 'LIB_BRANCH' (max string 256 char)");
}
if (isset($_POST['SHA1']) == FALSE) {
	die("[ERROR] Missing POST : 'SHA1' (max string 256 char)");
}

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
	$sql = " INSERT INTO `BUILD_list` (`time`, `id-group`, `sha1`, `tag`, `status`, `build-id`)"
	      ." VALUES ('".time()."',"
	      ."         '".$idGroup."',"
	      ."         '".$_POST['SHA1']."',"
	      ."         '".$_POST['TAG']."',"
	      ."         '".$_POST['STATUS']."',"
	      ."         '".$_POST['ID']."')";
	//echo $sql;
	$result = $COVERAGE_bdd->query($sql);
	if ($result == FALSE) {
		echo("[ERROR] Can not register in db ... (LIST)");
	}
	$sql = "SELECT   `BUILD_snapshot`.`id` "
	       ." FROM   `BUILD_snapshot`"
	       ."      , `CI_group`"
	       ." WHERE     `CI_group`.`user-name` = '".$userName."'"
	       ."       AND `CI_group`.`lib-name` = '".$libName."'"
	       ."       AND `CI_group`.`lib-branch` = '".$_POST['LIB_BRANCH']."'"
	       ."       AND `CI_group`.`id` = `BUILD_snapshot`.`id-group`"
	       ." LIMIT 1";
	$result = $COVERAGE_bdd->query($sql);
	//echo("sql : ".$sql);
	if (    $result == NULL
	     || $result->num_rows == 0) {
		// simply insert:
		$sql = " INSERT INTO `BUILD_snapshot` (`id-build`, `id-group`, `".$_POST['TAG']."`)"
		      ." VALUES ('".$_POST['ID']."',"
		      ."         '".$idGroup."',"
		      ."         '".$_POST['STATUS']."')";
		$result = $COVERAGE_bdd->query($sql);
		if ($result == TRUE) {
			echo("[OK] registered done (new entry)");
		} else {
			echo("[ERROR] Can not register in db ... (snapshot 1)");
		}
	} else {
		// try to update the curent values:
		$sql = " UPDATE `BUILD_snapshot`"
		      ." SET   `BUILD_snapshot`.`".$_POST['TAG']."` = '".$_POST['STATUS']."'"
		      ." WHERE `BUILD_snapshot`.`id-group` = '".$idGroup."'";
		$result = $COVERAGE_bdd->query($sql);
		if ($result == TRUE) {
			echo("[OK] registered done (update)");
		} else {
			echo("[ERROR] Can not register in db ... (snapshot 2)");
		}
	}
}
// simply close link with the DB...
$COVERAGE_bdd->close();
?>