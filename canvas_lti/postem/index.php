<?php
/*
dependent php files

../the usual suspects
common.php
canvas_roster.php
 */
ob_start();
$tokentype="domain";
session_start();
include "../sitepaths.php";
$include=$canvasphp ."postem/common.php";
$secret = "yoursecret";
 $testing=false;//change as needed
 if($testing){
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);
 }
include $canvasphp . "all_purpose.php";
?>
