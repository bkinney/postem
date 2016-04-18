<html><head></head><body><blockquote>
<?php
session_start();
if($_SESSION['token_arr']['temp']){
	include "canvasapi.php";
	$api = new CanvasAPI($_SESSION['token_arr']['temp'],$_SESSION['domain'],$_SESSION['_basic_lti_context']['custom_canvas_user_id']);
	$response = $api->post_canvas("/login/oauth2/token","DELETE");
		//print_r($response);
		echo '<p>Your single-use Canvas API access token has been deleted.</p> ';
}
//no, this won't work. Have to get _parent to reload with javascript
echo '<p>You have logged out. Please refresh your browser to reload this tool.</p>';
  
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    session_regenerate_id(true);

?>
</blockquote></body></html>