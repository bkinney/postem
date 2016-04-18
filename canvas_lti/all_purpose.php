<?php
session_start();

//must set testing in including file
if(empty($secret)) $secret = array("table"=>"blti_keys","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");
//$testing=false;
if(!function_exists('myecho')){//we already have this
function myecho($m){
	global $testing;

	if($testing) echo "<br>" . $m . "</br>";
}
}
myecho($include);

//edit these on each server
//move this to the index or sharedphp page. That way, this page need not be unique across versions.
/*require_once("/www/git/canvas/sitepaths.php");
$_SESSION['canvasphp']=$canvasphp;
$_SESSION['canvashtml']=$canvashtml;
$_SESSION['toolserver']=$toolserver;*/

//end site paths
/*------------------*******************-----------

uncomment the next line if you are serving tokens and/or secrets from a db, and replace with the path to your dbconnect file. This must create a php var $link which will be a mysqli link object */
//include "/home/bkinney/includes/lti_mysqli.php";

//------------------------*******************-----------


//must set testing in including file
//changelog: had to add canvasphp path here
include $canvasphp . 'ims-blti/blti.php';//changelog: database lookup via retrieve_secret.php, which always gets obj $link

$context = new BLTI($secret,true,false);
if($context->valid){
	$context_id = $context->info['context_id'];
	myecho("context_id  = " .$context_id . " " . $testing);
		$domain =$_SESSION['domain'] = $context->info['custom_canvas_api_domain'];
		if(!$domain)myecho(print_r($_REQUEST));
				  $isAdmin = $_SESSION['isAdmin']= $context->isAdministrator();
				  $isInstructor = $_SESSION['isInstructor']= $context->isInstructor();
			  	$_SESSION['context_id']=$context_id;
				$_SESSION['lti_url']=$toolserver .$_SERVER['PHP_SELF'];
		/*		setcookie("context",$context_id,0,'/');
				setcookie("isAdmin",$isAdmin,0,'/');
				setcookie("lti_url",$toolserver .$_SERVER['PHP_SELF'],0,'/');*/


		include $canvasphp."findvalidtoken.php";
	
	if($api){
		//we are creating the api to test tokens, so we won't recreate it here
		//include "canvasapi.php";
		//$api = new CanvasAPI($token,$domain,$context->info['custom_canvas_user_id']);
		$valid = $api->ready;
		if($valid){
			$exitmessage=$tokentype=="temp" ? "Logging out will delete your single-use token" : "Logging out is recommended before switching to another UD hosted LTI tool.";
			$header='<form action="'.$canvashtml . 'logout.php" ><p class="logout" ><img src="https://apps.ats.udel.edu/UDcircle20.png" align="left" border="0"/>'.$exitmessage.'<button>logout</button></p></form>';
		$header .='<script>
			var lastUpdate = new Date().getTime();
			function keep_alive() {
			if(new Date().getTime() - lastUpdate < 3000000){//give them more time
			console.log(lastUpdate + "ping");
         http_request = new XMLHttpRequest();
          http_request.open(\'GET\', "/canvas/ping.php");
          http_request.send(null);
			}
};
window.document.onmousedown = window.document.onkeypress = function(){
  lastUpdate = new Date().getTime();
  console.log(lastUpdate);

}
setInterval(keep_alive,299000);  //My session expires at 5 minutes
</script>';
			myecho($include);
			if(!empty($include))include $include;
		}else{//we found a token somewhere, but it is invalid
			
			echo "I have an invalid token. Try <a href='" . $canvashtml . "logout.php'>logging out</a>";
			echo "<p>You seem to have deleted a token. The one we have is invalid. You may re-authorize by completing the form below</p>";
			myecho(gettype($api));
			if($context->isInstructor() || $context->isAdministrator())include $canvasphp . "dance.php";
			//logout has a link back here, but session gets lost - what to do?
		}
	}else{
		//should have been redirected to dance.php, which initiates a request for a token
		//echo "something's wrong, I can't get a token via findsessiontoken.php";
	}
	//if no token found, I should end up at a token request
	
}else{
	
	echo "invalid context " . $context->message . " <a href='" . $canvashtml . "logout.php'>Log out</a><pre>";
	myecho(print_r($_REQUEST));
	echo "</pre>";
}
//-------------------end include code
?>