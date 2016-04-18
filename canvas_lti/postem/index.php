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
include "/www/canvas/sitepaths.php";
$include=$canvasphp ."postem/common.php";

 $testing=false;
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

include $canvasphp . "all_purpose.php";
?>