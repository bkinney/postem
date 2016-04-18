<?php
	
	
class CanvasAPI{
    public $ready = false;
	public $error = "not ready:";
	public $status = "";
	public $courseinfo;
	public $isAdmin;
    private $token;
	private $domain;
	private $canvas_user;

    function __construct($token="",$domain="",$canvas_user=false) {
		$this->token = $token;
		$this->domain = $domain;
		$this->canvas_user = $canvas_user;//current logged in user
		$this->ready = $this->is_valid_token();
		
	}//end construct
	function getCurlValue($filename,$contentType,$postname){
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename, $contentType, $postname);
        }
        // Use the old style if using an older version of PHP
        $value = "@" . $filename;
        return $value;
    }

	function is_valid_token(){
		if(!empty($_SESSION['apistatus'])){
			if($_SESSION['apistatus']==$this->token){
				$this->status = "using existing validation";
				return true;
			}else{//check validity of new token
				$_SESSION['apistatus']=false;//prevent recurse loop - not sure what unset would do
				return $this->is_valid_token();
			}
		}
			
		if(empty($this->token)){
			$this->error .= "token not found ";
			return false;
		}
		if(empty($this->domain)){
			$this->error .= "domain not found";
			return false;
		}else{
			$this->ready = true;
			$response = $this->get_canvas('/api/v1/users/self',false);//do not paginate
			//echo $response['status'];
			//echo gettype($response['status']);
			 if($response['errors'][0]['message']=="Invalid access token." ){
				 $this->error = "token invalid";
				 $this->ready = false;
				// session_unset();
				return false;
			 }else{
				 $this->courseinfo = $response;
				 $this->isAdmin = $this->is_admin($response['id']);
				 $this->error="token validated at " . date('m/d/Y H:i:s');
				 $_SESSION['apistatus']=$this->token;
				 return true;
			 }
		}
	}//end valid token
	function is_admin($id){//private, but sets public variable isAdmin
		$response=$uri='https://udel.instructure.com/api/v1/accounts/self/admins?user_id='.$id;
		return isset($response['id']);
	}
	
	//------------------------------------------------------
	
	public function get_canvas($uri,$paginate=true){
		if(!$this->ready)return $this->error;
		$host = "https://".$this->domain;
		
		$roster = array();
		
		$access_key = $this->token;
	//$uri .= "?access_token=" . $access_key;
	//echo $host.$uri;
		$curl = curl_init();
		$urip=$host.$uri;
		if($paginate){
			$max=200;//get as many as the server will give
			if(strpos($uri,"?")===false)$urip .= "?";
			$urip .= '&page=1&per_page=' . $max;
		}
		
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $access_key ) );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION,TRUE);
		curl_setopt($curl, CURLOPT_HEADER,TRUE);
		while(isset($urip)){
			curl_setopt($curl, CURLOPT_URL,$urip);
			$json = curl_exec( $curl );
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = substr($json, 0, $header_size);
			//echo $header;
			$body = substr($json, $header_size);
		  	$proster = json_decode($body,true);
			//echo count($proster) . " " . $urip;
			if(!$paginate || !is_array($proster)){
				curl_close($curl);
				return $proster;
			}
			$roster = array_merge($roster,$proster);
			
			$header_info = explode("\r\n",$header);
		//	print_r($header_info);
		//$current = $urip;
			unset($urip); 
			$link_arr = array();	
			foreach($header_info as $element){	
				if(strpos($element,"Link")==0){
					//echo $element;
					$links = explode(',', $element);
					foreach ($links as $value) {
						if (preg_match('/^\s*<(.*?)>;\s*rel="(.*?)"/', $value, $match)) {
							$link_arr[$match[2]] = $match[1];
						}
					}
					if(array_key_exists('last',$link_arr)){
						$splitURL = explode('?', $link_arr['last']);
						parse_str($splitURL[1],$output);
							//next should be undefined if this is the last page, but just in case	
							if(isset($link_arr['next'])){
									$urip=$link_arr['next'];
							}
					}
				}
			}
	/*		print_r($link_arr);
			if(preg_match('/^\s*<(.*?)>;\s*rel="next"/', $header, $match)){
				$urip=$match[0];
				//echo $urip . "<br/>";
			}else{
				unset($urip);
			}*/
		}
		curl_close($curl);
		return $roster;
	}//end get_canvas
	function masquerade(&$params){
		if($this->isAdmin){//using an admin token, be sure to masquerade
			if(!array_key_exists('as_user_id') && strpos($endpoint,'as_user_id=')===false && $this->canvas_user){
				$params['as_user_id']=$this->canvas_user;
			}
		}
	}
	function post_canvas($endpoint, $method,$params=array()){
		if(!$this->ready)return $this->error;
		$this->masquerade($params);//add the as_user_id param to masquerade as logged in user
		$postfields = http_build_query($params);
		$host = "https://".$this->domain;
	
	//echo $host . $uri . "<br><br>";
		$uri = $host . $endpoint;
		$access_key = $this->token; 
			
		$curl = curl_init($uri);
		//curl_setopt($curl, CURLOPT_URL,$uri); 
		$headrs = array();
		$headrs[]= 'Content-type: multipart/form-data';
		$headrs[]='Authorization: Bearer '.$access_key;
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headrs );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,TRUE);
		if($method=="POST")curl_setopt($curl, CURLOPT_POST,TRUE);
		//if($method=="PUT")curl_setopt($curl, CURLOPT_PUT,TRUE);
		if($method=="PUT")curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		if($method=="DELETE")curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($curl, CURLOPT_POSTFIELDS,$postfields);
		$json = curl_exec( $curl );
		$roster = json_decode($json,true);
		//print_r($roster);
		curl_close($curl);
		return $roster;
	}//end post_canvas
	

	function upload($uri,$file=array()){
	
		//print_r($_FILES['upload']);
		$filepath=$file['tmp_name'];
		//step 1
		$postdata = array(
			"size" => $file['size'],
			
			"content_type" => $file['type'],
			//"parent_folder_path" => "/postem",
			"name" => $file['name']
	);
	
	//print_r($postdata);
		 $result = $this->post_canvas($uri,"POST",$postdata); 
		//print_r($result['upload_params']);
		 //step 2
		$conn = curl_init();
		$postdata = $result['upload_params']; //Load returned upload parameters
		$postdata['file'] = $this->getCurlValue($filepath,$file['type'],$file['name']);
		curl_setopt($conn, CURLOPT_URL, $result['upload_url']); //URL for request
		curl_setopt($conn, CURLOPT_POST, TRUE); //Set POST method
		curl_setopt($conn, CURLOPT_POSTFIELDS, $postdata); //Set POST data
		curl_setopt($conn, CURLOPT_FOLLOWLOCATION, TRUE);//THIS IS THE KEY!
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($conn, CURLOPT_HEADER, true); //Show return headers in result
		if( ! $result2 = curl_exec($conn)) 
		{ 
			$this->error = curl_error($conn); 
			return($this->error);
			
		} 
	
				preg_match('/Location:(.*?)\n/', $result2, $matches);
				$newurl = trim(array_pop($matches));
	 
	  preg_match('/{(.*?)}/',$result2,$a);
	  $json= array_pop($a);
	  //echo "<pre>" . $result2 . "</pre>";
	  $roster = json_decode("{" . $json . "}",true);
	    //$roster = json_decode($json);
		curl_close($conn); //Close CURL session
		$host = "https://".$this->domain;
		
		$uri = str_replace($host,'',$newurl);
		//echo $result2;
		$final = $this->post_canvas($uri,"POST");
		//step 3 - is this really necessary? Seems to work without the token
		//print_r($postdata);
		//echo count($matches);
		//echo "result2 <br>";
		//echo $newurl;echo '<br>------------<br>'; 
		//$ch = curl_init($newurl);	
		//curl_setopt($ch, CURLOPT_POST, TRUE); //Set POST method
		//curl_setopt( $ch, CURLOPT_HTTPHEADER, 'Authorization: Bearer ' . $this->token);
		//$result = curl_exec($ch);
		//$roster = json_decode($result);
		
		print_r($final);
		echo 'Upload successful';
		return $final;
		curl_close($ch);
	
	}//end upload, end fn list

}//close obj	

?>