<?php


function getSecret($key){
if (!isset($link))include "/home/bkinney/includes/lti_db.php";	
$sql = sprintf("SELECT secret FROM blti_keys WHERE oauth_consumer_key='%s'",
         
                mysql_real_escape_string($key));
            // echo $sql;	
			$result = mysql_query($sql);
			
            $num_rows = mysql_num_rows($result);
			
            if ( $num_rows != 1 ) {
                echo "Your consumer is not authorized oauth_consumer_key=".$oauth_consumer_key . " " . $sql;
                return;
           		 } else {
				$row = mysql_fetch_assoc($result);
               
                   return $row['secret'];
					
                   
               }
                if ( ! is_string($secret) ) {
                    echo "Could not retrieve secret oauth_consumer_key=".$oauth_consumer_key;
                    return;
                }
				//mysql_close($link);
            //return $secret;
}
			?>