<?php
//this page is used to download a starter csv. Since it is not an include file, we need to re-establish our API access.


session_start();

$info = $_SESSION['_basic_lti_context'];
$courseid= $info['custom_canvas_course_id'];
$user = $info['custom_canvas_user_id'];
header("content-disposition:attachment;filename=roster_" . $courseid . ".csv");
header("content-type:text/csv");


include $_SESSION['canvasphp'] .'canvasapi.php';
include $_SESSION['canvasphp'] .'findvalidtoken.php';

$roster = $api->get_canvas("/api/v1/courses/" . $courseid . "/users?enrollment_type=student",true);
/*echo $api->error;
echo $api->status;
echo "<pre>" . print_r($roster). "</pre>";*/
echo '"Name","SISID';
$d = '","';
foreach($roster as $member){
	echo '"
"' . $member['sortable_name'] . $d
	. $member['sis_user_id'];
}
echo '"';
?>