<?php 
// Load up the Basic LTI Support code
//require_once('/www/LTI436/ims-blti/blti.php');

/*This page is called by Flash. The code is mostly copied from one of the ims-blti sample outcomes files.
It uses blti to return grades and comments to Canvas. Other LMS's will get the grade, but most likely not the comment
or url.

Updated version - March 2, 2016

Copied from same-named file in /canvas/scope, but that one is customized to recieve a snapshot, while this one is generic. It would be best if all future Flash posts were to go to this page, but I'm not retiring the scope one.
post to this page with score, outof, comment | url (not both) - primarily intended for posts from Flash, but any post will work as long as you send the right stuff, and the blti session variables are valid.
*/
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);
require_once('OAuthBody.php');


?>


<?php
session_start();
$info=$_SESSION['_basic_lti_context'];


$oauth_consumer_key = $info['oauth_consumer_key'];

	include 'retrieve_secret.php';
	$oauth_consumer_secret=getSecret($oauth_consumer_key);
if (strlen($oauth_consumer_secret) < 1 ) $oauth_consumer_secret = 'allsecrets';

//echo $oauth_consumer_key; 
function writeP($str){
	echo "<p>" . $str . "</p>";
}
$grade=0;//default score

if ( isset($_REQUEST['score']) && isset ($_REQUEST['outof']) ) {
/*	$elapsed = time() - $_COOKIE['starttime'];
	if(Math.abs(elapsed - $_REQUEST['elapsed'])>3){
		die("suspected cheating. server time elapsed does not match client time");
	}*/
	
	//print_r($info);
	$grade = round($_REQUEST['score']*1/$_REQUEST['outof']*1,3);
	writeP('Your grade for this exercise is: ' .$grade*100 . "%");
    $message = 'basic-lis-updateresult';
	$url = $info['ext_content_return_url'];//launch_presentation_return_url
	
}
	if(isset($_REQUEST['comment'])){
		$comment=$_REQUEST['comment'];
		$datatype = "text";
		
	}else if(isset($_REQUEST['url'])){
		$datatype = "url";
		$comment = $_REQUEST['url'];
	}
if (count( $_REQUEST)==0 ) exit;
$method="POST";

/*$endpoint = 'http://localhost/~csev/php-simple/service_handle.php';
$endpoint = 'http://localhost:8080/java-servlet/service';
$endpoint = 'http://localhost:8080/imsblis/service';*/
$endpoint = $info['lis_outcome_service_url'];//ext_ims_lis_basic_outcome_urllaunch_presentation_return_url
//$endpoint=$url;
$content_type = "application/xml";

$body = '<?xml version = "1.0" encoding = "UTF-8"?>
    <imsx_POXEnvelopeRequest xmlns="http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0"> 
      <imsx_POXHeader>
        <imsx_POXRequestHeaderInfo>
          <imsx_version>V1.0</imsx_version>
          <imsx_messageIdentifier>999999123</imsx_messageIdentifier>
        </imsx_POXRequestHeaderInfo>
      </imsx_POXHeader>
      <imsx_POXBody>
        <replaceResultRequest>
          <resultRecord>
            <sourcedGUID>
              <sourcedId>SOURCEDID</sourcedId>
            </sourcedGUID>
            <result>
              <resultScore>
                <language>en</language>
                <textString>GRADE</textString>
             
             </resultScore>';
 if(isset($datatype))$body .='             <resultData>
                <DATATYPE>COMMENT</DATATYPE>
              </resultData>
			   
            </result>';
$body .='          </resultRecord>
        </replaceResultRequest>
      </imsx_POXBody>
    </imsx_POXEnvelopeRequest>';

$shortBody = '<?xml version = "1.0" encoding = "UTF-8"?>  
<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/lis/oms1p0/pox">      
	<imsx_POXHeader>         
		<imsx_POXRequestHeaderInfo>            
			<imsx_version>V1.0</imsx_version>  
			<imsx_messageIdentifier>999999123</imsx_messageIdentifier>         
		</imsx_POXRequestHeaderInfo>      
	</imsx_POXHeader>      
	<imsx_POXBody>         
		<OPERATION>            
			<resultRecord>
				<sourcedGUID>
					<sourcedId>SOURCEDID</sourcedId>
				</sourcedGUID>
			</resultRecord>       
		</OPERATION>      
	</imsx_POXBody>   
</imsx_POXEnvelopeRequest>';

if ( isset($_REQUEST['score'] ) ) {
    $operation = 'replaceResultRequest';
    $postBody = str_replace(
	array('SOURCEDID', 'GRADE', 'OPERATION','COMMENT','DATATYPE'), 
	array($info['lis_result_sourcedid'], $grade, $operation,$comment,$datatype), 
	$body);
	//echo $postBody;
} else if ( $_REQUEST['submit'] == "Read Grade" ) {
    $operation = 'readResultRequest';
    $postBody = str_replace(
	array('SOURCEDID', 'OPERATION'), 
	array($_REQUEST['lis_result_sourcedid'], $operation), 
	$shortBody);
} else if ( $_REQUEST['submit'] == "Delete Grade" ) {
    $operation = 'deleteResultRequest';
    $postBody = str_replace(
	array('SOURCEDID', 'OPERATION'), 
	array($info['lis_result_sourcedid'], $operation), 
	$shortBody);
} else {
 die("invalid request");
}



$response = sendOAuthBodyPOST($method, $endpoint, $oauth_consumer_key, $oauth_consumer_secret, $content_type, $postBody);
if(strstr($response,"success")){
	writeP($_POST['success_msg']);
}else{
	writeP("We're sorry, there seems to have been a problem. Please report this to ats-staff@udel.edu.");
	$testing = true;
}
if($context->info['custom_debug']==1 || $testing){
	echo("\n<pre>\n");
	echo("Base\n");
	echo(getLastOAuthBodyBaseString());
	
	echo("\r\n\r\n------------ POST RETURNS ------------\r\n");
	$response = str_replace("<","&lt;",$response);
	$response = str_replace(">","&gt;",$response);
	echo($response);
	
	echo("\r\n\r\n------------ WE SENT ------------\r\n");
	$postBody = str_replace("<","&lt;",$postBody);
	$postBody = str_replace(">","&gt;",$postBody);
	echo($postBody);
	echo("\n</pre>\n");
}

?>