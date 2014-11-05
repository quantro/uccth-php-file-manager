<?php
	session_start();

	define("SiteTitle", "uccth data bank");
	define("SiteDescription", "simple server file processing");
	define("SiteAccess","qwerty12345");

	if(isset($_COOKIE["access_login"])){
		if($_COOKIE["access_login"]!=md5(SiteAccess)){
			setcookie("access_login","",time()-3600*24*30*12);
			echo "forbiden refresh page";
			header("location:".basename(__FILE__));
			exit();
		}
	}

	/*the function*/
	function asBytes($ini_v) {
		$ini_v = trim($ini_v);
		$s = array('g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10);
		return intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
	}
	$MAX_UPLOAD_SIZE 	= min(asBytes(ini_get('post_max_size')), asBytes(ini_get('upload_max_filesize')));
	
	function getList($dir){
		if(is_dir($dir)){
			setlocale(LC_ALL,'en_US.UTF-8');
			$active		= basename(__FILE__);
			$result 	= array();
			$cdir 		= scandir($dir);
			$listFix	= array();
			$total_data = 0;
			foreach ($cdir as $key => $value) {
				if(is_dir($dir.DIRECTORY_SEPARATOR.$value)){
					$listFix[] = $value;
				}
			}
			foreach ($cdir as $key => $value) {
				if(!is_dir($dir.DIRECTORY_SEPARATOR.$value)){
					$listFix[] = $value;
				}
			}
			foreach ($listFix as $key => $value) {
				if(!in_array($value, array(".","..")) && ($value!=$active)){
					$value 		= $dir . DIRECTORY_SEPARATOR . $value;
					chmod($value, 01777);
					$result[] 	= $value;
					$result[$value]["name"] 		= basename($value);
					$result[$value]["directory"] 	= is_dir($value);
					if(!is_dir($value)){
						$size 						= number_format(filesize($value)/1024);
					}else{
						$size						= "-";
					}
					$result[$value]["size"]			= $size;
					$result[$value]["modified"]		= filemtime($value);
					$result[$value]["path"]			= realpath($value);
					$result[$value]["perm"]			= fileperms($value);
					$result[$value]["ext"]			= pathinfo($value, PATHINFO_EXTENSION);
					$total_data++;
				}
			}
			$status["status"] 	= 1;
			$status["total"] 	= $total_data;
			$status["message"] 	= "";
			$status["data"] 	= $result;
		}else{
			$status["status"] 	= 0;
			$status["message"] 	= "re-check the path!";
			$status["data"] 	= "";
		}
		return json_encode($status);
	}
	function ago($time){
	   	$periods = array("s", "m", "h", "d", "w", "mo", "y", "de");
	   	$lengths = array("60","60","24","7","4.35","12","10");
	   	$now = time();
	    $difference     = $now - $time;
	    $tense         = "ago";

	   	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	    	$difference /= $lengths[$j];
	   	}
	   	$difference = round($difference);

	   	if($difference != 1) {
	      	$periods[$j].= "";
	   	}
	   	return "$difference $periods[$j]";
	}
	function delete($dir){
		if(file_exists($dir)){
			if(is_dir($dir)){
				$dirList	= array_diff(scandir($dir), array('.','..'));
				foreach ($dirList as $value) {
					if(file_exists($dir."/".$value)){
						if(is_dir($dir."/".$value)){
							delete($dir."/".$value);
						}else{
							unlink($dir."/".$value);
						}
					}
				}
				rmdir($dir);
			}else{
				unlink($dir);
			}
			$status["status"] 	= 1;
			$status["message"] 	= "";
			$status["data"] 	= $dir;
		}else{
			$status["status"] 	= 0;
			$status["message"] 	= "no dir/file";
			$status["data"] 	= $dir;
		}
		return json_encode($status);
	}
	function createdir($dir){
		if(file_exists($dir)){
			$status["status"] 	= 0;
			$status["message"] 	= "<span class='err_msg'>folder already exists</span>";
			$status["data"] 	= "";
		}else{
			if(mkdir($dir)){
				$status["status"] 	= 1;
				$status["message"] 	= "";
				$status["data"] 	= "";
			}else{
				$status["status"] 	= 0;
				$status["message"] 	= "";
				$status["data"] 	= "";
			}
		}
		return json_encode($status);
	}
	function renamedir($dir,$new){
		if(file_exists($dir)){
			if(rename($dir,$new)){
				$status["status"] 	= 1;
				$status["message"] 	= "";
				$status["data"] 	= "";
			}else{
				$status["status"] 	= 0;
				$status["message"] 	= "";
				$status["data"] 	= "";
			}
		}else{
			$status["status"] 	= 0;
			$status["message"] 	= "no dir/file";
			$status["data"] 	= "";
		}
		return json_encode($status);
	}
	function unzip_file($file,$dir){
		if(!is_dir($file)){
			$zip 	= new ZipArchive;
			$res 	= $zip->open($file);
			if($res===true){
				$zip->extractTo($dir);
	  			$zip->close();

	  			$status["status"] 	= 1;
				$status["message"] 	= "";
				$status["data"] 	= "";
			}else{
				$status["status"] 	= 0;
				$status["message"] 	= "no zip found";
				$status["data"] 	= "";
			}
		}else{
			$status["status"] 	= 0;
			$status["message"] 	= "no zip found";
			$status["data"] 	= "";
		}
		return json_encode($status);
	}

	function login_cookie($access_code){
		$active		= basename(__FILE__);
		if($access_code==SiteAccess){
			if(setcookie("access_login",md5(SiteAccess),time()+3600*24*30*12)){
				$status["status"] 		= 1;
				$status["message"] 		= "<div class='succ_msg'>loading...</div>";
				$status["redirect"]		= $active;
			}else{
				$status["status"] 		= 0;
				$status["message"] 		= "<div class='err_msg'>error, try again</div>";
				$status["redirect"]		= $active;
			}
		}else{
			$status["status"] 		= 0;
			$status["message"] 		= "<div class='err_msg'>wrong access code</div>";
			$status["redirect"]		= $active;
		}	
		return json_encode($status);
	}
	if(isset($_POST['doUpload'])){
		$dir_to 	= $_POST["dirActive"];
		if($dir_to=="/"){
			$dir_to = ".";
		}
		if(substr($dir_to, -1)!="/"){
			$dir_to = $dir_to."/";
		}
		setcookie("go",$dir_to);
		var_dump($_POST);
		var_dump($_FILES);
		var_dump($_FILES['file_data']['tmp_name']);
		var_dump(move_uploaded_file($_FILES['file_data']['tmp_name'], $dir_to.$_FILES['file_data']['name']));
	}
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && (isset($_POST["access_code"]) || isset($_POST["ajaxForm"]) || isset($_POST["new_folder"]) || isset($_POST["dir_delete"]) || isset($_POST["newName"]))){
		if(isset($_POST["access_code"])){
			echo login_cookie($_POST["access_code"]);
		}else if(isset($_POST["new_folder"])){
			if(isset($_COOKIE["access_login"])){
				$name_new_folder = "./".$_POST["cur_dir"].$_POST["new_folder"];
				echo createdir($name_new_folder);
			}else{
				echo "forbiden";
			}
		}else if(isset($_POST["newName"])){
			if(isset($_COOKIE["access_login"])){
				$newName 	= $_POST["newName"];
				$oldName	= $_POST["oldName"];
				$pathNew	= $_POST["pathNew"];
				echo renamedir($oldName, $pathNew.$newName);
			}else{
				echo "forbiden";
			}
		}else if(isset($_POST["dir_delete"])){
			if(isset($_COOKIE["access_login"])){
				$dir_delete = $_POST["dir_delete"];
				echo delete($dir_delete);
			}else{
				echo "forbiden";
			}
		}
	}else{
?>
<!doctype>
<html>
	<head>
		<title><?php echo SiteTitle; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<style type="text/css">
			@import url(http://fonts.googleapis.com/css?family=Open+Sans:400,300,600);
			html,body{
				padding: 0;
				margin: 0;
				width: 100%;
				height: 100%;
				font-family: 'Open Sans', sans-serif;
				font-weight: 300;
				color: #3D4658;
				min-width:500px; 
			}
			.front_page,
			.front_page td{
				width: 100%;
				height: 100%;
				text-align: center;
				vertical-align: middle;
			}
			.site_title{
				font-size: 3em;
				padding: 0;
				margin: 0;
				color:#2c3e50;
			}
			.site_description{
				padding: 0;
				margin: 0;
				color:#34495e;
			}
			.btnChange{
				padding: 0.2em;
				width: 90px;
				border-radius: 5px;
				margin: 2.5em auto;
				border: 2px solid #2980b9;
				color: #2980b9;
				cursor: pointer;
			}
			.btnChange:hover{
				border-color: #3498db;
				color: #3498db;
			}
			.inputPassword{
				padding: 0.85em;
				margin-top:1em;
				width: 300px;
				border:0;
				outline: none;
				text-align: center;
				border-bottom: 2px solid #34495e;
			}
			.hide{
				display: none;
			}
			.err_msg{
				color:#c0392b;
			}
			.succ_msg{
				color:#27ae60;
			}
			.infoLogin{
				height: 0px;
				padding: 0.5em;
				font-size: 12px;
			}
			.footer_fixed{
				position: fixed;
				bottom: 0;
				width: 100%;
				overflow: hidden;
			}
			.footer{
				width: 300px;
				font-size: 12px;
				padding: 0.5em;
				margin: 0 auto;
				text-align: center;
				border-top-left-radius: 10px;
				border-top-right-radius: 10px;
				background: #E3F2FF;
			}
			a{
				text-decoration: none;
				color: #2980b9;
				font-weight: bold;
				cursor: pointer;
			}
			.file_explorer{
				width: 100%;
				padding: 1em;
				padding-bottom: 4em;
				padding-top: 142px;
			}
			.file_explorer td{
				width: 100%;
				padding: 1em;
			}
			.dirlist{
				width: 100%;
				font-size: 0.8em;
				border-collapse: 0;
				border-collapse: collapse;
			}
			.dirlist th{
				text-align: left;
				color: #aaaaaa;
				border-bottom: 2px solid #e5e5e5;
				padding: 1em 0;
			}
			.dirlist td{
				padding: 1em 0;
				width: auto;
				border-bottom: 1px solid #e5e5e5;
				color: #3D464D;
			}
			.dirlist td:first-child,
			.dirlist th:first-child{
				padding-left: 5px;
			}
			.dirlist td:last-child,
			.dirlist th:last-child{
				padding-right: 5px;
			}
			.dirlist tr:hover,
			.dirlist tr:active{
				background: #F5FAFE;
			}
			.dirlist tr.activeTr{
				background: #E3F2FF;
			}	
			#progress{
				height: 2px;
				box-shadow: 0 0 6px 2px #E8F5FC; 
				background: #2098DD;
				position: fixed;
				top: 0;
			}
			.done{
				display: none;
			}
			.percentageDone{
				width: 100%!important;
			}
			.crumPosition, 
			.crumPosition td{
				padding: 0;
				border-collapse: collapse;
				margin: 0;
				width: auto;
				vertical-align: top;
			}
			.crumPosition{
				width: 100%;
				height: 95px;
				font-size: 0.9em;
				overflow: hidden;
			}
			.btnTop{
				cursor: pointer;
				margin-left: 4em;
			}
			.new_folder{
				display: none;
				vertical-align: top;
			}
			.inputFolderName,
			.buttonFolderNew,
			.labelNewFolder{
				padding: 0.3em 0.6em;
				border:1px solid #BDC4C9;
				outline: none;
				border-radius: 5px;
				color: #3D4658;
				margin: 0.5em 0;
			}
			.buttonFolderNew{
				margin-left: 5px;
				color: #aaaaaa;
				background: #F5FAFE;
				cursor: pointer;
			}
			.labelNewFolder{
				background: rgba(255,255,255,0);
				border-color:rgba(255,255,255,0);
				text-align: right;
				padding-right: 0.1em;
				color: #aaaaaa;
			}
			.info_new_folder{
				height: 20px;
				width: 20em;
				text-align: left;
				font-size: 11px;
			}
			.statEm{
				margin: 5em;
				color:#aaaaaa;
			}
			.clickable{
				cursor: pointer;
			}
			.crumPosition td:first-child{
				padding-left: 5px;
			}
			.crumPosition td:last-child{
				padding-right: 5px;
			}
			.topFixedCrum{
				position: fixed;
				overflow: hidden;
				width: 100%;
				top: 0;
				left: 0;
				height: 160px;
				min-width: 500px;
			}
			.paddingFixed{
				background: #fff;
				padding: 2em;
			}
			.dirlist thead{
				position: fixed;
			}
			.theaderDirlist{
				font-size: 0.8em;
				color: #aaaaaa;
				width: 100%;
				border-collapse: collapse;
			}
			.theaderDIrlist th{
				padding: 0 0 1em 0;
				border-collapse: collapse;
				text-align: left;
				color: #aaaaaa;
				border-bottom: 2px solid #e5e5e5;
			}
			.theaderDirlist th:first-child{
				padding-left: 5px;
			}
			.theaderDirlist th:last-child{
				padding-right: 5px;
			}
			.detailClicked{
				display: none;
			}
			.menuSelected,
			.menuSelected li{
				display: inline-block;
				margin: 0;
				padding: 0;
			}
			.selectedList{
				float: left;
				min-width: 40%;
				overflow: hidden;
				padding-right: 1em;
			}
			.menuSelected{
				float: right;
				overflow: hidden;
			}
			.menuSelected li{
				padding-left: 2em;
			}
			.subMenu{
				display: none;
			}
			.btnSub{
				cursor: pointer;
			}
			.inputRename{
				border:0;
				outline: none;
				border-bottom: 1px solid #aaaaaa;
				margin: 0 0.2em;
				width: 200px;
			}
			.goRename{
				margin-left: 5px;
				color: #aaaaaa;
				border:1px solid #aaaaaa;
				border-radius: 5px;
				cursor: pointer;
				background: #F5FAFE;
			}
			.goRename:hover{
				background: #FFF;
			}
			#file_drop_target{
				font-weight: bold;
				text-align: center;
				padding: 6em 0;
				margin: 1em 0;
				color: rgba(192, 57, 43,1.0);
				border: 1px dashed rgba(192, 57, 43,1.0);
				border-radius: 7px;
				cursor: default;
				width: 100%;
				height: 100%;
				background: rgba(241, 196, 15,0.8);
			}
			#file_drop_target.drag_over{
				background: rgba(241, 196, 15,0.6);
			}
			.hide{
				display: none;
			}
			.uploadModule{
				display: none;
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}
			.innerUpload{
				width: 100%;
				height: 100%;
				margin: 0 auto;
			}
			.cancelUploadBtn{
				margin: 5em auto 0 auto;
				padding: 0.3em 0.5em;
				border:2px solid rgba(236, 240, 241,1.0);
				width: 100px;
				border-radius: 10px;
				color: rgba(236, 240, 241,1.0);
				cursor: pointer;
			}
			.cancelUploadBtn:hover{
				border:2px solid rgba(236, 240, 241,0.5);
				color: rgba(236, 240, 241,0.5);
			}
		</style>
	</head>	
	<body>
		<div class="footer_fixed"><div class="footer">&copy; 2014-2015 - developed by <a href='http://instagram.com/uccth' target="_Blank">uccth</a></div></div>
		<div id="loadAjax">
			<?php
				if(!isset($_COOKIE["access_login"])){
			?>
			<table class="front_page">
				<tr>
					<td>
						<div class="showData">
							<h1 class='site_title'><?php echo SiteTitle; ?></h1>
							<h3 class='site_description'><?php echo SiteDescription; ?></h1>
						</div>
						<div class="hideData hide loginForm">
							<form method="post" class='formAjax' action="">
								<h3 class='site_description'>enter your access code : </h3>
								<input type='password' name='access_code' placeholder='type here then press enter' class='inputPassword'>
								<div class="infoLogin"></div>
							</form>
						</div>
						<div class='btnChange' data-change='cancel' data-show='hideData' data-hide='showData'>enter</div>
					</td>
				</tr>
			</table>
			<?php
				}else{
			?>
			<?php
				if(isset($_GET["dir"])){
					$myrootdir 	= $_GET["dir"];
				}else{
					$myrootdir	= "./";
				}
				if($myrootdir==""){
					$myrootdir 	= "./";
				}
				if(substr($myrootdir, -1)!="/"){
					$myrootdir	= $myrootdir."/";
				}
				if(strpos($myrootdir, '?dir=')){
					$myrootdir 	= str_replace("?dir=", "", strstr($myrootdir, '?dir='));
				}
				$myrootdir 		= str_replace("%2B", "+", $myrootdir);
				$myrootdir 		= str_replace("%20", " ", $myrootdir);
			?>
			<table class="file_explorer"><tr><td>
				<div class="topFixedCrum"><div class='paddingFixed'>
					<table class='crumPosition'>
						<tr>
							<td align="left">
								<a class="linkAjax" href='<?php echo basename(__FILE__); ?>'>Home</a>  
								<?php 
									if(isset($_GET["dir"])){
										if(file_exists($myrootdir)){
											$ex_root 	= explode("/", $myrootdir);
											foreach($ex_root as $key => $value){
												if($value!=""){
													if($myrootdir[$key]!="."){
														$link = "";
														for($i=0;$i<sizeof($ex_root);$i++){
															$link .= $ex_root[$i]."/";
															if($ex_root[$i]==$value){
																break;
															}
														}
														$link = rtrim($link, "/");
														$link = str_replace("+", "%2B", $link);
														echo " &raquo; <a dir='".$link."' title='".$link."' class='goDir'>".$value."</a>";
													}
												}
											}
										}
									}
								?>
							</td>
							<td align="right">
								<a class='btnTop btnUploadFile' data-show='uploadModule' data-change='Upload'>Upload</a>
								<a class='btnTop btnShow btnNewFolder' data-show='new_folder' data-change='Cancel'>New folder</a>
							</td>
						</tr>
						<tr class='new_folder'>
							<td colspan="2" align="right">
								<form class='newFolder'>
									<input type='text' class='labelNewFolder' value="Create new folder : " disabled="disabled">
									<input type='hidden' name='cur_dir' value="<?php echo $myrootdir; ?>">
									<input type='text' id="newFolder" name='new_folder' class='inputFolderName' placeholder='enter folder name here...'><button class='buttonFolderNew'>go</button><br />
									<div class="info_new_folder"></div>
								</form>
							</td>
						</tr>
						<tr class='detailClicked'>
							<td colspan="2" align="left">								
								<div class='selectedList'>Selected : <strong class='selectedName'>Image</strong></div>
								<ul class="menuSelected parentMenu">
									<li><a class='btnSub' data-show='subRename' data-hide="parentMenu">Rename</a></li>
									<li class="dwnldbtn"><a class='downloadBtn' href='' download>Download</a></li>
									<li><a class='btnSub' data-show='subDelete' data-hide="parentMenu">Delete</a></li>
								</ul>
								<ul class="menuSelected subDelete subMenu"><li>Are you sure? <a class="yesDeleteBtn" data-path="">Yes</a> or <a class="btnSub" data-hide="subMenu" data-show="parentMenu">Cancel</a></li></ul>
								<form class='renameSelected'>
									<input type='hidden' class="oldName" name='oldName' value=''>
									<input type='hidden' class="pathNew" name='pathNew' value=''>
									<ul class="menuSelected subRename subMenu"><li>Rename to : <input type='text' placeholder='enter new name' class='inputRename newNameN' value="" name='newName'><input type='submit' value='go' class="goRename"></li></ul>
								</form>
							</td>
						</tr>
					</table>
					<table class='theaderDirlist'>
						<tr>
							<th class='nameH' style="width:50%">Name</th>
							<th class='typeH' style="width:15%">Type</th>
							<th class='modifiedH' style="width:15%">Modified</th>
							<th class='sizeH'>Size</th>
						</tr>
					</table>
				</div></div>
				<div class="divLoad">
					<table class="dirlist">
						<?php
							if(file_exists($myrootdir)){
								$data_list 	= json_decode(getList($myrootdir));
								$tot_show 	= 0;
								foreach ($data_list->data as $key => $value) {
									$tot_show ++;
									if(isset($value->name)){
										$kind 		= "file";
										$size 		= $value->size." KB";
										$name 		= $value->name;
										if($value->directory){
											$kind 	= "folder";
											$size 	= "";
											$name 	= "<a dir='".str_replace("+", "%2B", $myrootdir).str_replace("+", "%2B", $name)."' class='goDir'>".$name."</a>";
										}
										echo "<tr data-type='".$kind."' class='clickable' data-path-dir=\"".$myrootdir."\" data-name=\"".$value->name."\" data-path=\"".$myrootdir.$value->name."\">";
										echo "<td class='nameL' style='width:50%'>".$name."</td>";
										echo "<td class='typeL' style='width:15%'>".$kind."</td>";
										echo "<td class='modifiedL' style='width:15%'>".ago($value->modified)."</td>";
										echo "<td class='sizeL'>".$size."</td>";
										echo "</tr>";
									}
								}
							}
						?>
					</table>
					<?php
							if(!file_exists($myrootdir)){
								echo "<center class='statEm'>Directory or file doesn't exists</center>";
							}else if($tot_show==0){
								echo "<center class='statEm'>Directory empty</center>";
							}
						}
					?>
				</div>	
			</td></tr></table>
		</div>
		<div id="progress"></div>
		<table class="uploadModule">
			<tr>
				<td>
					<table class="innerUpload">
						<tr>
							<td>
								<table id="file_drop_target">
									<td>
										<h1>drop files here</h1>
										<small>Or old method : <input type="file" multiple />
										<input type='hidden' name='dirUpload'><br /><br />Max file size : <?php echo $MAX_UPLOAD_SIZE/1024/1024; ?> MB</small>
										<div class='cancelUploadBtn'>Done</div>
									</td>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script type="text/javascript">
			var XSRF 			= (document.cookie.match('(^|; )_sfm_xsrf=([^;]*)')||0)[2];
			var MAX_UPLOAD_SIZE = <?php echo $MAX_UPLOAD_SIZE ?>;
			$(document).ready(function(){
				$(document).on("click",".cancelUploadBtn",function(){
					$(".uploadModule").hide();
				});
				$(document).on("click",".btnUploadFile",function(){
					$(".uploadModule").show();
				});
				$(document).on("click",".btnSub",function(){
					var showWhat = $(this).attr("data-show");
					var hideWhat = $(this).attr("data-hide");
					$("."+hideWhat).hide();
					$("."+showWhat).show();
				});
				function fixedHeader(){
					$(".nameH").css("width",$(".nameL").outerWidth());
					$(".typeH").css("width",$(".typeL").outerWidth());
					$(".modifiedH").css("width",$(".modifiedL").outerWidth());
					$(".sizeH").css("width",$(".sizeL").outerWidth());
				}

				$(document).on("submit",".newFolder",function(e){
					e.preventDefault();
					loadingStart();
					var data_f 		= $(this).serialize();
					$.ajax({
						type 		: "post",
						data 		: data_f,
						url 		: document.URL,
						success 	: function(result){
							var obj = jQuery.parseJSON(result);
							if(obj.status==0){
								$(".info_new_folder").html(obj.message);
								loadingEnd();
							}else{
								$(".divLoad").load(document.URL+" .divLoad",function(){
									$(".btnNewFolder").click();
									$(".inputFolderName").val("");
									loadingEnd();
									forClickable();
								});
							}
						}
					});
				});
				function urlFIx(str) {
				  	return encodeURIComponent(str).replace(/[!'()*]/g, function(c) {
				    	return '%' + c.charCodeAt(0).toString(16);
				  	});
				}
				$(document).on("click",".yesDeleteBtn",function(){
					var dir_path 		= $(this).attr("data-path");
					loadingStart();
					$.ajax({
						type 	: "post",
						data 	: "dir_delete="+urlFIx(dir_path).replace("+","%2B"),
						url 	: document.URL,
						success	: function(e){
							$(".divLoad").load(document.URL+" .divLoad",function(){
								$(".detailClicked").hide();
								loadingEnd();
								forClickable();
							});
						}
					});
				});
				$(document).on("submit",".renameSelected",function(e){
					e.preventDefault();
					var data_f 	= $(this).serialize();
					loadingStart();
					$.ajax({
						type 	: "post",
						data 	: data_f,
						url 	: document.URL,
						success	: function(result){
							$(".divLoad").load(document.URL+" .divLoad",function(){
								$(".detailClicked").hide();
								$(".inputRename").val("");
								loadingEnd();
								forClickable();
							});
						}
					});
				});
				$(document).on("click",".clickable",function(){
					$(".btnHide").click();
					$(".clickable").removeClass("activeTr");
					$(".detailClicked").show();
					$(this).addClass("activeTr");

					var selectedName 	= $(this).attr("data-name");
					var dir_path		= $(this).attr("data-path");
					var data_path_dir	= $(this).attr("data-path-dir");
					var data_type		= $(this).attr("data-type");

					if(data_type=="folder"){
						$(".dwnldbtn").hide();
					}else{
						$(".dwnldbtn").show();
					}

					$(".yesDeleteBtn").attr("data-path",dir_path);
					$(".oldName").val(dir_path);
					$(".pathNew").val(data_path_dir);
					$(".newNameN").val(selectedName);
					$(".selectedName").text(selectedName);
					$(".downloadBtn").attr("href",dir_path);
					
					$(".subMenu").hide();
					$(".parentMenu").show();
				});
				function forClickable(){
					$('.clickable').hover(function(){ 
				        mouse_is_inside=true; 
				    }, function(){ 
				        mouse_is_inside=false; 
				    });
				    $('.detailClicked').hover(function(){ 
				        mouse_is_inside=true; 
				    }, function(){ 
				        mouse_is_inside=false; 
				    });
					$("body").mouseup(function(){ 
				        if(!mouse_is_inside) {
							$(".detailClicked").hide();
							$(".clickable").removeClass("activeTr");
				        }
				    });
				}
				forClickable();
				$(document).on("click",".btnShow",function(){
					var show_what 	= $(this).attr("data-show");
					var default_text= $(this).html();
					var data_change	= $(this).attr("data-change");
					$(this).attr("data-change",default_text);
					$("."+show_what).show();
					$(this).html(data_change);
					$(this).addClass("btnHide");
					$(this).removeClass("btnShow");
				});
				$(document).on("click",".btnHide",function(){
					var show_what 	= $(this).attr("data-show");
					var default_text= $(this).html();
					var data_change	= $(this).attr("data-change");
					$(this).attr("data-change",default_text);
					$("."+show_what).hide();
					$(this).html(data_change);
					$(this).addClass("btnShow");
					$(this).removeClass("btnHide");
				});
				function loadingStart(){
					$("#progress").removeClass("percentageDone");
					$("#progress").removeClass("done");
					$({property: 0}).animate({property: 90}, {
				        duration: 3000,
				        step: function() {
				          	var _percent = Math.round(this.property);
				          	$("#progress").css("width",  _percent+"%");
				        }
				    });
				}
				function loadingEnd(){
					$("#progress").addClass("percentageDone");
					$("#progress").addClass("done");
				}
				$(document).on("click",".goDir",function(){
					var dir 	= $(this).attr("dir");
					loadingStart();
					$("#loadAjax").load(document.URL+"?dir="+urlFIx(dir)+" #loadAjax",function(){
						loadingEnd();
						forClickable();
					});
					window.history.pushState('obj', 'newtitle', '?dir='+dir.replace('./', ''));
				});
				$(document).on("click",".linkAjax",function(e){
					e.preventDefault();
					var link 		= $(this).attr("href");
					loadingStart();
					$("#loadAjax").load(urlFIx(link)+" #loadAjax",function(){
						loadingEnd();
						forClickable();
					});
					window.history.pushState('obj', 'newtitle', link);
				});
				$(document).on("click",".btnChange",function(){
					var data_before = $(this).html();
					var data_change = $(this).attr("data-change");
					var temp		= data_change;
					$(".btnChange").attr("data-change",data_before);
					$(".btnChange").html(data_change);

					var data_hide	= $(this).attr("data-hide");
					var data_show	= $(this).attr("data-show");
					var temp 		= data_show;
					$(".btnChange").attr("data-show",data_hide);
					$(".btnChange").attr("data-hide",temp);

					$("."+data_hide).hide();
					$("."+data_show).show();

					$(".inputPassword").val("");
					$(".infoLogin").html("");
				});
				$(document).on("submit",".formAjax",function(e){
					e.preventDefault();
					var action_form 	= $(this).attr("action");
					var method_form		= $(this).attr("method");
					var all_data		= $(this).serialize();
					loadingStart();
					$.ajax({
						type		: method_form,
						url 		: action_form,
						data 		: all_data,
						success		: function(result){
							loadingEnd();
							var obj = jQuery.parseJSON(result);
							if(obj.status==0){
								$(".inputPassword").val("");
								$(".infoLogin").html(obj.message);
							}else{
								$("#loadAjax").load(obj.redirect+" #loadAjax",function(){
									forClickable();
								});
								$(".infoLogin").html(obj.message);
							}
						}
					});
				});

				function parseURLParams(url) {
				    var queryStart = url.indexOf("?") + 1,
				        queryEnd   = url.indexOf("#") + 1 || url.length + 1,
				        query = url.slice(queryStart, queryEnd - 1),
				        pairs = query.replace(/\+/g, " ").split("&"),
				        parms = {}, i, n, v, nv;

				    if (query === url || query === "") {
				        return;
				    }

				    for (i = 0; i < pairs.length; i++) {
				        nv = pairs[i].split("=");
				        n = decodeURIComponent(nv[0]);
				        v = decodeURIComponent(nv[1]);

				        if (!parms.hasOwnProperty(n)) {
				            parms[n] = [];
				        }

				        parms[n].push(nv.length === 2 ? v : null);
				    }
				    return parms;
				}
				/*
					for file uploading drag and drop
				*/
				$('#file_drop_target').bind('dragover',function(){
					$(this).addClass('drag_over');
					return false;
				}).bind('dragend',function(){
					$(this).removeClass('drag_over');
					return false;
				}).bind('drop',function(e){
					e.preventDefault();
					var files = e.originalEvent.dataTransfer.files;
					$.each(files,function(k,file) {
						uploadFile(file);
					});
					$(this).removeClass('drag_over');
				});
				$('input[type=file]').change(function(e) {
					e.preventDefault();
					$.each(this.files,function(k,file) {
						uploadFile(file);
					});
				});

				if(typeof parseURLParams(document.URL) === 'undefined'){
					paramParam = "/";
				}else{
					paramParam = parseURLParams(document.URL)["dir"];
				}

				function uploadFile(file) {
					loadingStart();
					var folder = window.location.hash.substr(1);
					if(file.size > MAX_UPLOAD_SIZE) {
						alert("max upload size = "+MAX_UPLOAD_SIZE);
					}
					var fd = new FormData();
					fd.append('file_data',file);
					fd.append('file',folder);
					fd.append('xsrf',XSRF);
					fd.append('doUpload','upload');
					fd.append('dirActive',paramParam);
					var xhr = new XMLHttpRequest();
					xhr.open('POST', '?');
					xhr.onload = function() {
						$(".divLoad").load(document.URL+" .divLoad",function(){
							loadingEnd();
							forClickable();
							$(".uploadModule").hide();
						});
			  		};
				    xhr.send(fd);
				}
			});
		</script>
	</body>
</html>
<?php
	}
?>
