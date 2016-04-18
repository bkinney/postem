<?php
/** 
This is a much abreviated version created for the purpose of sharing code without getting into too much complexity. Basically you need to provide an API token appropriate for the context in which the application is launched, and use that to initialize a CanvasAPI object. I leave it to you to decide how you want to get the token. The easiest path is to issue yourself an admin level token, and hard-code it in.
**/
session_start();
$restore=$tokentype;//change back to originally requested type if no valid tokens are found
if(!class_exists("CanvasAPI",false))include $canvasphp . "canvasapi.php";
//$_SESSION['token_arr']['temp']=12335;
//$tokentype = "context";
$token = $_SESSION['token_arr']['domain'] = "yourtokenhere";
$domain=$_SESSION['_basic_lti_context']['custom_canvas_api_domain'];
$user = $_SESSION['_basic_lti_context']['custom_canvas_user_id'];
function get_api($token,$domain,$user){
	
	$api = new CanvasAPI($token,$domain,$user);
	if($api->ready)return $api;
	return false;
	
}
$api = get_api($token,$domain,$user);
?>