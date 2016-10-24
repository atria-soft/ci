<?php

// force display of error
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
//date_default_timezone_set('Europe/Paris');
/*
// check if all is here ...
echo("<br/>USER = ".$_GET['USER']);
echo("<br/>LIB_NAME = ".$_GET['LIB_NAME']);
echo("<br/>branch = ".$_GET['branch']);
die("<br/>die");
*/
header("Content-Type: image/svg+xml");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date dans le passé
/*
header('Content-Type: image/svg+xml');
echo '<?xml version="1.0" standalone="no"?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN""http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg width="100%" height="100%" version="1.1"xmlns="http://www.w3.org/2000/svg">';
$c = (int)(($x*$y)/$scaler);
$prev = 0;
foreach($rgb as $k=>$v){
	if($v > 0) {
		$r = ($k >> 16) & 0xFF;
		$g = ($k >> 8) & 0xFF;
		$b = $k & 0xFF;
		$hex = str_pad(dechex($r),2,'0',STR_PAD_LEFT).str_pad(dechex($g),2,'0',STR_PAD_LEFT).str_pad(dechex($b),2,'0',STR_PAD_LEFT);
		echo '<circle cx="'.$c.'" cy="'.$c.'" r="'.($c-$prev).'" fill="#'.$hex.'" />';
		echo "\n";
		$prev += (int)($v/$scaler);
	}
}
echo '</svg>';
*/


function errorSVG($_name) {
	echo('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="20">');
	echo('	<linearGradient id="a" x2="0" y2="100%">');
	echo('		<stop offset="0" stop-color="#bbb" stop-opacity=".1"/>');
	echo('		<stop offset="1" stop-opacity=".1"/>');
	echo('	</linearGradient>');
	echo('	<rect rx="3" width="120" height="20" fill="#F00"/>');
	echo('	<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">');
	echo('		<text x="60" y="15" fill="#010101" fill-opacity=".3">WAR: '.$_name.'</text>');
	echo('		<text x="60" y="14">WAR: '.$_name.'</text>');
	echo('	</g>');
	echo('</svg>');
	exit();
}

include_once("connect.php");

@include_once("connect_server.php");

$WARNING_bdd = mysqli_connect($sqlServer, $sqlLogin, $sqlPass, $sqlBDD);
// Check connection
if (mysqli_connect_errno()) {
	errorSVG("SQL ERROR");
}
// check if all is here ...
$branch = "master";
if (isset($_GET['USER']) == FALSE) {
	errorSVG("USER??");
} else if (isset($_GET['LIB_NAME']) == FALSE) {
	errorSVG("LIB_NAME??");
}
if (isset($_GET['branch']) == TRUE) {
	$branch = $_GET['branch'];
}

//echo "register ".$_POST['JSON_FILE'];
$sql = "SELECT   `WARNING_list`.`warning` "
       ."      , `WARNING_list`.`error` "
       ." FROM   `WARNING_list`"
       ."      , `CI_group`"
       ." WHERE     `CI_group`.`user-name` = '".$_GET['USER']."'"
       ."       AND `CI_group`.`lib-name` = '".$_GET['LIB_NAME']."'"
       ."       AND `CI_group`.`lib-branch` = '".$branch."'"
       ."       AND `CI_group`.`id` = `WARNING_list`.`id-group`"
       ." ORDER BY `WARNING_list`.`time` DESC"
       ." LIMIT 1";
$result = $WARNING_bdd->query($sql);
//echo("sql : ".$sql);
if ($result == NULL) {
	errorSVG("UNKNOW");
}
//echo("find result : ".$result);

if ($result->num_rows > 0) {
	if ($result->num_rows > 1) {
		errorSVG("To much value");
	}
	$row = $result->fetch_assoc();
	$warningCount = $row['warning'] + $row['error'];
	//some warningCount value :
	if ($warningCount < 5 ) {
		$color = "4c1";
	} else if ($warningCount < 20 ) {
		$color = "c71";
	} else if ($warningCount < 50 ) {
		$color = "c1c";
	} else {
		$color = "c11";
	}
} else {
	//errorSVG("No Value");
	$warningCount = "---";
	$color = "FF0";
}

echo('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="20">');
echo('	<linearGradient id="a" x2="0" y2="100%">');
echo('		<stop offset="0" stop-color="#bbb" stop-opacity=".1"/>');
echo('		<stop offset="1" stop-opacity=".1"/>');
echo('	</linearGradient>');
echo('	<rect rx="3" width="120" height="20" fill="#555"/>');
echo('	<rect rx="3" x="67" width="53" height="20" fill="#'.$color.'"/>');
#echo('	<path fill="#4c1" d="M37 0h4v20h-4z"/>');
echo('	<rect rx="3" width="120" height="20" fill="url(#a)"/>');
echo('	<g fill="#fff" text-anchor="middle" font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="11">');
echo('		<text x="32" y="15" fill="#010101" fill-opacity=".3">warning</text>');
echo('		<text x="32" y="14">warning</text>');
echo('		<text x="92.5" y="15" fill="#010101" fill-opacity=".3">'.$warningCount.'</text>');
echo('		<text x="92.5" y="14">'.$warningCount.'</text>');
echo('	</g>');
echo('</svg>');

// simply close link with the DB...
$WARNING_bdd->close();
?>