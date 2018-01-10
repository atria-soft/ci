<?php

// force display of error
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
//date_default_timezone_set('Europe/Paris');

// check if all is here ...
//echo("<br/>USER = ".$_GET['USER']);
//echo("<br/>LIB_NAME = ".$_GET['LIB_NAME']);
//echo("<br/>branch = ".$_GET['branch']);
//die("<br/>die");
header("Content-Type: image/svg+xml");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé

function errorSVG($_name) {
	echo('<svg xmlns="http://www.w3.org/2000/svg" width="180" height="20">');
	echo('	<linearGradient id="a" x2="0" y2="100%">');
	echo('		<stop offset="0" stop-color="#bbb" stop-opacity=".1"/>');
	echo('		<stop offset="1" stop-opacity=".1"/>');
	echo('	</linearGradient>');
	echo('	<rect rx="3" width="120" height="20" fill="#F00"/>');
	echo('	<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">');
	echo('		<text x="60" y="15" fill="#010101" fill-opacity=".3">BUILD: '.$_name.'</text>');
	echo('		<text x="60" y="14">BUILD: '.$_name.'</text>');
	echo('	</g>');
	echo('</svg>');
	exit();
}

include_once("connect.php");
@include_once("connect_server.php");

$COVERAGE_bdd = mysqli_connect($sqlServer, $sqlLogin, $sqlPass, $sqlBDD);
// Check connection
if (mysqli_connect_errno()) {
	errorSVG("SQL ERROR");
}
// check if all is here ...
$branch = "master";
$tag = "Linux";
if (isset($_GET['USER']) == FALSE) {
	errorSVG("USER??");
}
if (isset($_GET['LIB_NAME']) == FALSE) {
	errorSVG("LIB_NAME??");
}
if (isset($_GET['branch']) == TRUE) {
	$branch = $_GET['branch'];
}
if (isset($_GET['tag']) == TRUE) {
	$tag = $_GET['tag'];
}

//echo "register ".$_POST['JSON_FILE'];

$sql = "SELECT   `BUILD_snapshot`.`".$tag."` "
       ." FROM   `BUILD_snapshot`"
       ."      , `CI_group`"
       ." WHERE     `CI_group`.`user-name` = '".$_GET['USER']."'"
       ."       AND `CI_group`.`lib-name` = '".$_GET['LIB_NAME']."'"
       ."       AND `CI_group`.`lib-branch` = '".$branch."'"
       ."       AND `CI_group`.`id` = `BUILD_snapshot`.`id-group`"
       ." LIMIT 1";

//echo("sql : ".$sql);
$result = $COVERAGE_bdd->query($sql);
//
if ($result == NULL) {
	errorSVG("UNKNOW");
}
//echo("find result : ".$result);

if ($result->num_rows > 0) {
	if ($result->num_rows > 1) {
		errorSVG("To much value");
	}
	$row = $result->fetch_assoc();
	$jsonRaw = $row["json"];
} else {
	$jsonRaw = "{}";
	//errorSVG("No Value");
}

$data = json_decode($jsonRaw);
$status = $data[$tag];

//some coverage value :
if ($status == "UNKNOW") {
	$color = "333";
} else if ($status == "START") {
	$color = "11F";
} else if ($status == "ERROR") {
	$color = "c11";
} else if ($status == "OK") {
	$color = "4c1";
} else {
	$color = "FF0";
}

echo('<svg xmlns="http://www.w3.org/2000/svg" width="180" height="20">');
echo('	<linearGradient id="a" x2="0" y2="100%">');
echo('		<stop offset="0" stop-color="#bbb" stop-opacity=".1"/>');
echo('		<stop offset="1" stop-opacity=".1"/>');
echo('	</linearGradient>');
echo('	<rect rx="3" width="180" height="20" fill="#555"/>');
echo('	<rect rx="3" x="40" width="75" height="20" fill="#A60"/>');
echo('	<rect rx="3" x="110" width="70" height="20" fill="#'.$color.'"/>');
echo('	<rect rx="3" width="180" height="20" fill="url(#a)"/>');
echo('	<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">');
echo('		<text x="19" y="15" fill="#010101" fill-opacity=".3">build</text>');
echo('		<text x="19" y="14">Build</text>');
echo('		<text x="75" y="15" fill="#010101" fill-opacity=".3">'.$tag.'</text>');
echo('		<text x="75" y="14">'.$tag.'</text>');
echo('		<text x="145" y="15" fill="#010101" fill-opacity=".3">'.$status.'</text>');
echo('		<text x="145" y="14">'.$status.'</text>');
echo('	</g>');
echo('</svg>');

// simply close link with the DB...
$COVERAGE_bdd->close();

?>