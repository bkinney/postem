<?php
myecho("common");
$self =  $_SERVER['PHP_SELF'];
$writable = "php path to a writable file above the site root";
myecho($self);

$instructor=false;
session_start();
ini_set('auto_detect_line_endings',true);

if($context->valid){
//note to self. these session variables should not be set= $_SESSION['isInstructor'] = $_SESSION['contextid']= $_SERVER['uid']	
	$instructor  = $context->isInstructor();
	$contextid = $context->info['context_id'];
	//$uid =$context->info['lis_person_sourcedid'];
	if (isset($context->info['lis_person_sourcedid'])) {  // normal student
		$uid = $context->info['lis_person_sourcedid'];
	}
		else {  // test student
		$uid = $context->info['custom_canvas_user_id'];
	}
}else{
	echo '<a href="'.$canvashtml .'logout.php">log out</a> 
	';

		
	die($context->message);

}

function cleanCell($cell){
	
	$arr = array("<tbody>","<tr>","<td>");
	$rep = array("","","");
	$cleaned = str_replace($arr,"",$cell);
	//if($cleaned=="")$cleaned="&nbsp;";
	return strtolower($cleaned);
	
}



function canvas_upload_postem($data,$info,$api){
	$handle2 = fopen($writable,"w");
	if(!$handle2)echo "failed to open";
	$file = array();
$file['size']= fwrite($handle2,$data);

$file['tmp_name']=$writable; 
$file['name']='temp.html';
$file['type']="text/html";
$courseid = $info['custom_canvas_course_id'];
$uid = $info['custom_canvas_user_id'];
//print_r($file);
$uri='/api/v1/courses/'.$courseid .'/files?name='.$courseid.'.csv&parent_folder_path=/postem&locked=true&as_user_id='.$uid;

fclose($handle2);
	
	return $api->upload($uri,$file);
	
}
if(!empty($_POST['overwrite'])){
	myecho('overwrite');
	$d = $_POST['overwrite'];
	echo canvas_upload_postem($d,$context->info,$api);
	exit();
}
if(!empty($_FILES["postemfile"])){
	//echo mime_content_type($_FILES['postemfile']["tmp_name"]);
	$instructor=true;
	
	$data = '<table class="stickyHeader" ><thead>';
	$handle=fopen($_FILES["postemfile"]["tmp_name"],"r");
	
	$cols = fgetcsv($handle, 10000, ",");//just the first row
	
	$data .= "<tr><th>" . implode("</th><th>",$cols) . "</th></tr>";
	$data .= "</thead><tbody>";
	
	while (($cols = fgetcsv($handle, 10000, ",")) !== FALSE) {
		$clean = array_map('htmlspecialchars',$cols,array_fill(0 , count($cols) , ENT_QUOTES));
		$data .= "<tr><td>" . implode("</td><td>",$clean) . "</td></tr>";
	}
	$data .= "</tbody></table>";
	fclose($handle);
	canvas_upload_postem($data,$context->info,$api);
}
?><html><head><title>PostEm</title>
<meta name="viewport" content="width=device-width"/>
<link rel="stylesheet" href="<?php echo $canvashtml ?>postem/stickyheader.css">
<style>
#msg{
	
	background-color:#FFC;
	width:100%;
	text-align:center;
}
caption{
	background-color: #ccc;
	padding: 3px;
	font-size:small;
	text-align:center;
}
caption div{text-align:left;display:none}

#toolset blockquote{
	text-align:center;
	font-size:smaller;
	width:90%;
	margin:0 auto 3px auto;
	background-color:white;
	
}
#toolset input[type=submit]{float:right}
.studentview caption{display:none}

.hidden{display:none}

body,td,th {
	font-family: Verdana, Geneva, sans-serif;
}
h1,h2,h3,h4 {
	
	color:#2e6e9e;
}
h2 {
	font-size: 120%;
	
}
h3 {
	font-size: 110%;
	
}
h4{
	color:#e17009;
	font-size:100%
}
table{
	
	margin-bottom:20px;
	
}
blockquote{
	border: double;
	padding: .5em;
	width: 60%;
	margin: 0 0 20px 15px;
}
table.studentview td:nth-child(-n+2),table.studentview th:nth-child(-n+2){
	display:none;
}

tbody tr td{
	background-color:rgb(215, 230, 243);
	overflow:hidden;
}
tbody td:nth-of-type(even){
	 background-color: rgb(242, 247, 252);
	 
}
th{
	background-color:rgb(143, 183, 213);
}

th,td{
	text-align:center;
	min-width:30px;
	max-width:300px;
	padding:2px;
}
tbody th{text-align:left}

table{
	
	border:solid rgb(143,183,213) 3px;
}

thead, tbody{
	border-collapse: separate;
	border-spacing: 2px;
	border-color: gray;
}
footer{
	font-size:smaller;
	text-align:center;
	background-color:rgb(242, 247, 252);
	margin-top:30px;
	padding:3px;
}

.logout{
	  /*  position: absolute;
    top: 0;
   
    width: 98%;
    margin-right: 8px;*/
	background-color: rgb(252, 229, 140);
padding: 2;
text-align: right;
font-family:Arial, Helvetica, sans-serif;
font-size:70%;
color:#559;
border-bottom: double;
margin-bottom: 5px;
 }
 .logout button{
	margin:0 1px 0 5px; 

 }
</style>

<script>
function toggleSubmit(){
	document.getElementById("submit").disabled=false;
}

</script>
</head>
<body>
<?php echo $header?>
<div id="msg"></div>	
			
<h2 id="title"><?php 
 
if($context->isInstructor()){
	echo "Instructor View";
	}else{
echo $context->info['lis_person_name_given'] . " " . $context->info['lis_person_name_family'];	
}
?></h2>
<?php if($context->isInstructor()):?>
<p>View as student <input type="text" onKeyUp="viewAs(this.value)" value="" size="2"/> (enter row num, or blank for instructor)</p>

<?php endif ?>
<div id="report">
<?php 

if(empty($data)){//no upload, look in postem folder

$courseid=$context->info['custom_canvas_course_id'];
	$uri = '/api/v1/courses/'.$courseid.'/files?search_term='.$courseid.'.csv';
	//echo gettype($api->get_canvas);
	$list=$api->get_canvas($uri,true);
	//print_r($list);
	$uri = '/api/v1/files/'.$list[0]['id'];
	$roster = $api->get_canvas($uri);
		if(!empty($roster['filename'])){
			
			$data = file_get_contents($roster['url']);
			$data = preg_replace('~>\s+<~', '><', $data);
		}else{
			//print_r($roster);
		}
//echo $data;
}


if(!empty($data)){
		$table = str_replace("<td></td>","<td>&nbsp;</td>",$data);
		$table = str_replace("<tr><td>","<tr><th>",$table);
	
	if($instructor){
		$table=str_replace("<thead",'<caption><a href="#t" name="#t" onclick="toggleEdit()">show/hide editor</a><div id="toolset" ><blockquote class="instructions">Use ctrl (win) or cmd (mac) to select multiple</blockquote>hide columns <select style="height:1.5em" onmouseover="showSelect()" onmouseleave="hideSelect()" multiple onchange="toggleCol(this.value)" id="columns"><option value="0">show all</option></select><input type="submit" onclick="updatePostem()" value="update"/></div></caption><thead',$table);
		echo $table;
	}else{//filter student view
		//echo '<table cellpadding="3">';
		//$f = explode("</tr>",$data);//str_getcsv($data,"\r\n"); ;
		//$data = str_replace('class="stickyHeader"','class="studentview"',$data);
		$t_parts = explode("<table",$data);
		$head_arr = explode("</thead>",$t_parts[1]);
		$theader = '<table' . $head_arr[0] . '</thead>';
		//preg_match('/<table(.+)<\/thead>/',$data,$header);
		//echo $header;
		//preg_match('/<tbody.+<\/tbody>/',$data,$body);
		//$f = explode("</tr><tr>",$body[1]);
		$b_parts = explode("<tbody",$data);
		$body_arr = explode("</tbody>",$b_parts[1]);
		$body = '<tbody' . $body_arr[0] . '</tbody>';
		//echo $body;
		//array_shift($f);//get rid of the first row
		//preg_match('/(<tr>(.+)<\/tr>){1}/',$body[0],$f);
		//echo count($f);
		$table = $theader;
	/*	$hl = str_getcsv($header);//explode(",",$header);
		echo $body[0];
		echo "<thead><tr>";
		foreach($hl as $cell){
			echo "<th>" . htmlspecialchars($cell) . "</th>";
		}*/
		//$body = str_replace(' class=""','',$body);
		$f = preg_split("/(<\/tr><tr>|<tbody><tr>)/",$body);
		
		//$instructor=false;
		foreach($f as $row) {
			
			//$row = str_replace("th>","td>",$row);//this could be an issue?
			$needle = strtolower($context->info['lis_person_contact_email_primary']);
			
			//$row = str_replace("<tr><tbody>","<tbody>",$row);
			//$row = str_replace("</tbody></tr><tr>","</tbody>",$row);
			//preg_match('/<td>.+<\/td>/',$row,$line);
			//$line = preg_split("/(<\/td><td>|<\/th><td>)/",$row);//preg_split('<(/?)td>',$row);//str_getcsv($row);;
			//$line = explode("</td><td>",$row);
			$line = preg_split('/<\/t[dh]><td[ class="hidden"]?>/',$row);
			//could be d or h after the t
			// must escape /
			//note []? might have contents of previous []
			
			//$haystack = array_map('cleanCell',$line);
			//echo count($line);
			if(count($line)<2)continue;
			$id= $line[1];
			
			$n = $uid*1;
			// echo $n . "," . $id . "<br>";
			$match=false;//$instructor?
			if($n==$id)$match=true;
			if($needle==strtolower($id)) $match=true;
			if($line[0]=="")$match=true;
			if(!$match){
				//echo $line[1]. "," . $context->info['custom_canvas_user_id'] . "<br>";
				if($line[1]==$context->info['custom_canvas_user_id'])$match=true;
			}
			if(count($line)>1 && $match){
				$table .=  $row . "</tr><tr>";
			}else{
				//echo gettype($uid) . "," . gettype($line[0]) . "<br>";
			//	echo $haystack[0] . ",";
				//echo gettype(cleanCell);
			}
		}
		
		//$table = str_replace("<tr><tbody>","<tbody>",$table);
		$table = str_replace("</tbody></tr><tr>","</tbody>",$table);
		$table = str_replace("<td></td>","<td>&nbsp;</td>",$table);
		$table .= '</table>';
		//echo $table ;
	}
}else{//no file
	echo "<blockquote>No data available.";
	if($context->isInstructor()){
		echo "Upload a valid csv file.";
		//download grade template
	echo ' <a href="'.$canvashtml.'postem/canvas_roster.php?tokentype=' . $tokentype . '" >Download Template</a>';
	}
	echo "</blockquote>";
	
}//end if !data

echo '</div>';//end report

if($instructor){
	
	

echo '<hr />


<h4>Upload CSV</h4>
<blockquote>Your file should have student names in the first column, and ids in the second (SISID or email address). All other columns will be treated as grades. Any row containing an SISID or email address in the second column will be visible only to that student. Rows with an empty field in the first column (no name) will be shown to all. <a href="'.$canvashtml . 'postem/canvas_roster.php?tokentype='.$tokentype .'" >Download Template</a></blockquote>
<form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data">
  <p>
    <input type="hidden" size="41" name="context_id" value="'. $contextid . '" />
   
    <input name="postemfile" type="file" id="postemfile" size="50" onchange="toggleSubmit()"  />
 
    <input id="submit" type="submit" name="submit" value="Submit" disabled>
  </p>
</form>';
}else{
	echo $table;
}//end if instructon
 

?>


<footer><a href="help.html" target="_blank">Help</a> | <a href="https://sites.google.com/a/udel.edu/ats-beta/post-em" target="site">Comments</a> and <a href="https://sites.google.com/a/udel.edu/ats-beta/post-em/post-em-bugs"  target="site">Bug Reports</a> are strongly encouraged</footer><script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="stickyheader.jquery.js"></script>
<script>
function debug(){
	console.log($('head').html());
}
$(document).ready(function(e) {
    debug();
	<?php if(!$instructor)echo 'viewAs(1,true);' ?>;
});
/*function viewAs(rownum){
	$("tbody tr").show();
	if(rownum==""){
		$("table").removeClass("studentview").addClass("stickyHeader");
		$("#title h2").text("Instructor View");
	}else{
		
		n = Number(rownum)-1;//switch from zero indexed
		$("table").removeClass("stickyHeader").addClass("studentview");
		$("tbody tr").each(function(index){
			if(index!=n){
				//alert($(this).children("th:first").text());
				if($(this).children("th:first").html()!="&nbsp;")$(this).hide();
				
			}else{
				var name = $(this).children("th:first").text();
				$("#title h2").text(name);
			}
		});
	}
	
}*/
function viewAs(rownum,stu){
	$("tbody tr").show();
	//clean up any existing student tables
	$("table:not(.stickyHeader)").not(".stickyHeader table").remove();
	if(rownum==""){
		//$("table").removeClass("studentview").addClass("stickyHeader");
		$("table.stickyHeader").removeClass("hide");
		
		
		
		$("#title").text("Instructor View");
	}else{
		$("table.stickyHeader").addClass("hide");
		if(!stu){//don't mess with this in student view
			var name = $("table.stickyHeader tbody tr:nth-child("+rownum +") th:first").text();
			$("#title").text(name);
		}
		var newt = $('<table>');
		
		var newbody = $('<tbody>').appendTo(newt);
		
	$("table.stickyHeader thead tr th").each(function(index, element) {
		//console.log($(this).text());
		if(index>1){
			var dindex = index+1;
			var hrow = $('<tr>').appendTo(newbody);
			hrow.append($(this).clone());
			var mycell = $("table.stickyHeader tbody tr:nth-child(" + rownum +") td:nth-child("+dindex+")");
			hrow.append(mycell.clone());
			//stable += '<tr><th>' + urlencode($(this).text()) + '</th><td>'+mycell+'</td></tr>';
			
			
		}
    });
	
	$("#report").after(newt);
	
	//$("#report").append($(stable));
/*		n = Number(rownum)-1;//switch from zero indexed
		$("table").removeClass("stickyHeader").addClass("studentview");
		$("tbody tr").each(function(index){
			if(index!=n){
				//alert($(this).children("th:first").text());
				if($(this).children("th:first").html()!="&nbsp;")$(this).hide();
				
			}else{
				var name = $(this).children("th:first").text();
				$("#title").text(name);
			}
		});*/
	}
	
	
}
function toggleCol(n){
	$("th,td").removeClass("hidden");
	//$("#columns option").not("selected").removeAttr("selected");
	$("#columns option:selected").each(function(index, element) {
		
		var col = Number($(this).val());
		if(col>0){
			$("th:nth-child("+col+"),td:nth-child("+col+")").addClass("hidden");
		
		}
        
    });

	
	
	
	
}
function showSelect(){
	$("#columns").css("height","auto");
}
function hideSelect(){
	$("#columns").css("height","1.5em");
}
var firstrun=true;
function toggleEdit(){
	//var bool = !$("#report").attr("contenteditable")===true;
	//$("#report").attr("contenteditable",bool);
	//$("thead").attr("contenteditable",false);
	$("#toolset").toggle();
	//if($("#columns option").length == 1){
	if(firstrun){
			
		//$("#toolset").append(' <select style="height:1.5em" onmouseover="showSelect()" onmouseleave="hideSelect()"  multiple onchange="toggleCol(this.value)" id="columns"><option value="0">show all</select>');
		$("table.stickyHeader thead th:gt(1)").each(function(index, element) {
			var n = $(this).text();
			i=index+3;
			var s = $(this).hasClass("hidden") ? "selected" : "";
		
			$("#columns").append('<option value="'+i+'" ' + s + ' >'+n+'</option>');
		});

		firstrun=false;
	}
	
}
function updatePostem(){
	$("tbody tr").removeAttr("style");
	$('td').not('.hidden').removeAttr("class");

	var t = $("#report").clone();
	t.find("caption").remove();
	t.find("table:eq(1)").remove()
	//toggleEdit();
	var obj = new Object();
	
	obj.overwrite = t.html();
	$("#msg").load("<?php echo $self ?>",obj);
}

</script>


</body>
</html>