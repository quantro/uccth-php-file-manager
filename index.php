<?php
	/*
		Hello, this is uccth cloud manager version 1.0 (final release)
	*/
	
	//[This is your setting panel]//

	define("access_code",/*enter your access code ===========>*/"qwerty12345"); //enter your access code
	define("public_directory",/*enter your public directory =>*/"public"); //enter your public directory
	define("site_name",/*enter your site name or site title =>*/"uccth cloud"); //enter your site name or site title
	define("site_description",/*enter your site description =>*/"simple cloud explorer"); //enter your site description

	//[End of setting panel, be carefully of code below, it's belong your data server]//
	//error_reporting(0);
	/*
		function name 		: fixedPath
		function parameter 	: string of url/path of directory 
		function function 	: to fixed error url/path of directory
		function return 	: fixed directory url/path

		example input
		-------------
		[a] fixedPath(""); 
		[b] fixedPath("public/*storage//data\/media");
		[c] fixedPath(".///////hello<<<<<//data");
		[d] fixedPath("public\\\\\\\\stora>>>><<<ge\/datapath");

		return expectation
		------------------
		[a] ./
		[b] ./public/storage/data/media
		[c] ./hello/data
		[d] ./public\storage/datapath
	*/
	function fixedPath($dir){
		if(!$dir){			//[1]
			$dir 	= "./";	//[2]
		}					
		$dir = str_replace("\\", "/", $dir); //[2.1]
		/*
			Explanation
			-----------
			[1]	if parameter ($dir) is empty, it's mean $dir equal to false
				then if $dir equal to false, !$dir should equal to true, and
				if condition are true, content of "if" statment will be executed
			[2] if parameter equal to nothing or no parameter, $dir or it's parameter
				will be set equal to "./", and "./" means root directory, or current
				active directory.
			[2.1] replace all backslashes to become slashes

			Example input
			-------------
			[a] fixedPath("public");
			[b] fixedPath("");
			
			Expectation of $dir variable 
			[a] $dir = public
			[b] $dir = ./
		*/
		$dir 		= stripslashes($dir); //[3]
		/*
			Explanation
			-----------
			[3]	the stripslashes() function removes backslashes, 
				This function can be used to clean up data retrieved 
				from a database or from an HTML form.

			Example input
			-------------
			[a] public\/data\/picture
			[a] public\\data\\picture

			Expectation of $dir variable 
			[a] $dir = public/data/picture
			[b] $dir = public\data\picture
		*/
		for($i=0;$i<strlen($dir);$i++){ //[4]
			$dir 	= str_replace("./","/",$dir); //[5]
			$dir 	= str_replace("\\\\","/",$dir); //[6]
			$dir 	= str_replace("\/","/",$dir); //[6.1]
		}
		/*
			Explanation
			-----------
			I know this is just a garbage, I don't know how to remove 
			multiple ./ and multiple \\ with preg_match, so I use this code
			[4] it'll looping as much $dir character length
			[5]	replace all ./ to / in the $dir
			[6] delete all \\ in the $dir
			[6.1] replace all \/ to / in the $dir

			Example input
			-------------
			[a] ./././public/././././main/function
			[a] public\\\\\\\data\\\\\picture././/web

			Expectation of $dir variable 
			[a] $dir = public/main/function
			[b] $dir = public\data\picture/web
		*/
		if(substr($dir, 0,2)!="./"){ //[7]
			$dir 	= "./".$dir; //[8]
		}
		/*
			Explanation
			-----------
			[7] it'll get the first 2 character of string $dir,
				so, if $dir equal to public then first 2 character is pu
				other case, if $dir equal to ./public then first 2 character is ./

				if the first 2 character is not equal to ./ then the content of if 
				statment will be executed, otherwise program will ignore the if content

			[8] if first 2 character of $dir isn't equal to ./ then, this line will add
				./ to the first of the string

			Example input
			-------------
			[a] public/statment/data
			[a] ./public/data

			Expectation of $dir variable 
			[a] $dir = ./public/statment/data
			[b] $dir = ./public/data
		*/		
		$dir 		= preg_replace('#/{2,}#', '/', $dir); //[9]
		/*
			Explanation
			-----------
			I think this is the important one
			[9] it'll replace all double or multiple slash

			Example input
			-------------
			[a] public//////////statment//////data////////////
			[a] .///////public/data///////

			Expectation of $dir variable 
			[a] $dir = ./public/statment/data
			[b] $dir = ./public/data
		*/
		$dir 		= str_replace("*", "", $dir); //[10]
		$dir 		= str_replace("|", "", $dir); //[11]
		$dir 		= str_replace("\"", "", $dir); //[12]
		$dir 		= str_replace(":", "", $dir); //[13]
		$dir 		= str_replace(">", "", $dir); //[14]
		$dir 		= str_replace("<", "", $dir); //[15]
		/*
			Explanation
			-----------
			[10-15] will remove illegal character for folder name
					like * | \ : > <

			Example input
			-------------
			[a] public/*hero
			[a] ./>data</kc:/lr

			Expectation of $dir variable 
			[a] $dir = public/hero
			[b] $dir = ./data/kc/lr
		*/
		return $dir; // will return all fixed parameter ($dir)
	}
	/*
		function name 		: listDir
		function parameter 	: string of url/path of directory 
		function function 	: to return all directories and files under the url/path
		function return 	: array to json of all directories and files under the url/path
	
		example case
		------------
		./
		   - public
		   - image
		   - report.excel
		   - selfie.jpg

		example input
		-------------
		[a] fixedPath(""); 

		return expectation example
		---------------------------
		{
			data: {
				public: {
					type: 0,
					path: "./public",
					modified: "2 h",
					dirname: ".",
					size: "0.00 KB"
				},
				image: {
					type: 0,
					path: "./image",
					modified: "2 h",
					dirname: ".",
					size: "0.00 KB"
				},
				report.excel: {
					type: 1,
					path: "./report.excel",
					modified: "2 h",
					dirname: ".",
					size: "3.00 KB"
				},
				selfie.jpg: {
					type: 1,
					path: "./selfie.jpg",
					modified: "2 h",
					dirname: ".",
					size: "23.00 KB"
				}
			}
		}
	*/
	function listDir($dir){
		$dir = fixedPath($dir); //[16] 
		if(file_exists($dir) && is_dir($dir)){ //[17]
			$access_to_this_dir	= false; //[18]
			if(isset($_COOKIE["access_code"])){ //[19]
				if($_COOKIE["access_code"]==md5(access_code)){ //[20]
					$access_to_this_dir	= true; 
				}else{
					$access_to_this_dir	= false;
				}
			}else if($dir==fixedPath(public_directory) || substr(fixedPath($dir), 0, strlen(fixedPath(public_directory)))==fixedPath(public_directory)){ //[21]
				$access_to_this_dir	= true;
			}else{ //[22]
				$access_to_this_dir	= false;
			}
			if($access_to_this_dir){ //[23]
				$dir_scanned 	= scandir($dir); //[24]
				$folder_array 	= array(); //[25]
				$file_array 	= array(); //[26]
				$fixed_scanned 	= array(); //[27]
				$zip 			= new ZipArchive; //[28]
				foreach ($dir_scanned as $key => $value) { //[29]
					$realpath 	= realpath($dir.DIRECTORY_SEPARATOR.$value); //[30]
					$ext 		= pathinfo($realpath, PATHINFO_EXTENSION); //[31]
					chmod($realpath,0755); //[32]
					if($value!="." && $value!=".." && $realpath!=__FILE__){ //[33]
						if(is_dir($realpath)){ //[34]
							$folder_array[$value]["type"] = 0;
						}else{
							if(strtolower($ext)=="zip"){ //[35]
								$res = $zip->open($realpath);
								if($res===TRUE){ //[36]
									$file_array[$value]["type"] = 2;
									$zip->close(); //[37]
								}else{
									$file_array[$value]["type"] = 1;
								}
							}else{
								$file_array[$value]["type"] = 1;
							}
						}
					}
				}
				$fixed_scanned = array_merge($folder_array,$file_array); //[38]
				foreach ($fixed_scanned as $key => $value) { //[39]
					$realpath 	= realpath($dir.DIRECTORY_SEPARATOR.$key); //[40]
					$path 		= $dir.DIRECTORY_SEPARATOR.$key; //[41]
					$dirname	= dirname($dir.DIRECTORY_SEPARATOR.$key); //[42]
					$fixed_scanned[$key]["path"] 		= fixedPath($path); //[43]
					$fixed_scanned[$key]["modified"] 	= ago(filemtime($realpath)); //[44]
					$fixed_scanned[$key]["dirname"] 	= $dirname; //[45]
					$fixed_scanned[$key]["size"] 		= number_format(filesize($realpath)/1024,2)." KB"; //[46]
				}
				$status["data"] = $fixed_scanned; //[47]
				$status["status"] 	= 1; 
				$status["current"]	= $dir;
				$status["message"]	= "successfully get all directory and file.";
			}else{
				$status["status"] 	= 0; 
				$status["message"]	= "you don't have permission to access this folder.";	
			}
		}else{
			$status["status"] 	= 0;
			$status["message"]	= "the folder does not exist.";
		}
		return json_encode($status); //48
	}
	/*
		Explanation for listDir function
		--------------------------------
		[16] the parameter from listDir will be sent to fixedPath to fix the illegal url/path
		[17] will check, if it's a directory and it's exists then enter the content of "if" statment
			 otherwise will return the error message
		[18] this variable to make an access condition and by default it's set to false
		[19] if there is a cookie called access_code (access_to_this_dir) then enter the condition, 
			 otherwise it'll go to section 21
		[20] if cookie value of access_code equal to user access_code, set access_to_this_dir to true
			 otherwise it'll set access_to_this_dir to false
		[21] if opened directory is equal to public access then access_to_this_dir set to true 
		[22] otherwise of "if" condition section 19 to 21 it'll set access_to_this_dir to false
		[23] if access_to_this_dir return to true, then enter the if condition, otherwise it'll return
			 an json code said that you don't have permission to access this folder
		[24] will return array of list directory and files under url/path sent from parameter, 
		 	 and stored to dir_scanned variable
		[25] this array to store all of directories 
		[26] this array to store all of files 
		[27] this array is a combiantion of array of dir and files
		[28] create new ZipArchives object
		[29] fetch all data from section 24
		[30] get real path of directory or files
		[31] get path (from active directory) of directory or files
		[32] make all directory and files are executable
		[33] if file name scanned inside the directory isn't . and .. and the realpath of active
			 file to name of scanned inside the directory isn't same then enter the condition
		[34] if the file/directory fetched from dir is a directory, then set type of directory/file to 0
			 0 = Directory
			 1 = FIle
			 2 = Zip
		[35] if the extension of the file is a zip enter the condition otherwise set type of directory/file to 1
		[36] if the file can opened by zip object, then it is a zip, if it is a zip, set type to 2 otherwise to 1
		[37] always close the zip after opened it
		[38] combine the folder and file array to fixed array
		[39] fetch the array
		[40] get real path of directory or files
		[41] get path (from active directory) of directory or files
		[42] get the direcoty name of path
		[43] assign a fixed path to path array
		[44] assign a modified time to modified array
		[45] assign a dirname to dirname array
		[46] assign a file size to size array
		[47] assign all array to data array
		[48] return all array to json code
	*/
	/*
		function name 		: ago
		function parameter 	: date time
		function function 	: to return date time minus now time
		function return 	: result of date-time minus now time
	
		example output
		--------------
		25 m
		14 s
		16 h
		30 d

		s = second
		m = minute
		h = hour
		d = day
		w = week
		mo= month
		y = year
		de= decade
	*/
	function ago($time){ //[49]
	   	$periods = array("s", "m", "h", "d", "w", "mo", "y", "de"); //[50]
	   	$lengths = array("60","60","24","7","4.35","12","10"); //[51]
	    $difference     = time() - $time; //[52]
	   	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) { //[53]
	    	$difference /= $lengths[$j]; //[54]
	   	}
	   	$difference = round($difference); 
	   	return "$difference $periods[$j]";
	}
	/*
		Explanation
		-----------
		[49] will receive parameter $time
		[50] array of different time 
		[51] array of lengths of different time
		[52] time now minus time from parameter store to difference variable
		[53] looping while difference variable greater or equal than lengths index and j less than total lenghts data
		[54] difference equal to difference divided by lengths
	*/
	/*
		function name 		: renameDir
		function parameter 	: url/path dir and new name
		function function 	: to rename a dir or file
		function return 	: result of status with json code
	*/
	function renameDir($dir,$newname){ //[55]
		$dir 	 = fixedPath($dir); //[56]
		$dirname = dirname($dir); //[57]
		if(isset($_COOKIE["access_code"])){ //[58]
			if($_COOKIE["access_code"]==md5(access_code)){ //[59]
				if(strpbrk($newname, "\\/?%*:|\"<>")===FALSE && $newname!="." && $newname!=""){ //[60]
					if(file_exists($dir)){ //[61]
						$duplicate = false; //[62]
						$dir_scanned = scandir($dirname); //[63]
						foreach ($dir_scanned as $key => $value) { //[64]
							$value_r 	= $dirname.DIRECTORY_SEPARATOR.$value; 
							if(strtolower($newname)==strtolower($value)){ //[65]
								if(is_dir($value_r)){ //[66]
									if(is_dir($dir)){ //[67]
										$duplicate = true;
										break;
									}
								}
								if(!is_dir($value_r)){ //[68]
									if(!is_dir($dir)){ //[69]
										$duplicate = true;
										break;
									}
								}
							}
						}
						if(strtolower($newname)==strtolower(basename($dir))){ //[70]
							$duplicate = false;
						}
						if(!$duplicate){ //[71]
							if(rename($dir, $dirname."/".$newname)){ //[72]
								$status["status"] 	= 1;	
								$status["message"]	= "folder or file successfully rename.";
							}else{
								$status["status"] 	= 0;	
								$status["message"]	= "something wrong with renaming folder or directory, try again.";
							}
						}else{
							$status["status"] 	= 0;	
							$status["message"]	= "duplicate name of folder or file.";
						}
					}else{
						$status["status"] 	= 0;	
						$status["message"]	= "the folder or file does not exists.";
					}
				}else{
					$status["status"] 	= 0;	
					$status["message"]	= "illegal folder or file name";
				}
			}else{
				$status["status"] 	= 0; 
				$status["message"]	= "you don't have permission to do this action.";	
			}
		}else{
			$status["status"] 	= 0; 
			$status["message"]	= "you don't have permission to do this action.";	
		}
		return json_encode($status);
	}
	/*
		Explanation
		-----------
		[55] the function will receive 2 parameter, the first one is directory path and the second is new name
		[56] fixed url or path of directory path by fixedPath function and stored to dir variable
		[57] get directory name of file or directory and stored to dirname variable
		[58] check if there is a cookie of access code, otherwise will return that you don't have permission
		[59] check if your coocki of access code matched with your access, otherwise you don't have permission
		[60] check if the new name is the legal name for folder or file name, otherwise will return that is a illegal name
		[61] check if the directory or file is exists, otherwise return the folder or file doesn't exists
		[62] duplicate variable is the codition of is there any folder or file that has a same name with the new name
			 if yes then duplicate will set to true, the default, duplicate set to false
		[63] scan all directory or files under dirname
		[64] fetch all scan result of section 63
		[65] if file or folder name and new name are equal then enter the condition
		[66] if file or folder name is directory then enter the conditon
		[67] if file or folder you want to rename is dir, then set duplicate to true and break
		[68] if file or folder name is not directory then enter the conditon
		[69] if file or folder you want to rename is not dir, then set duplicate to true and break
		[70] if name of file or folder you want to rename equal to new name then set equal to false
		[71] if not duplicate then enter the condition
		[72] will rename the directory or file, if success enter the condition, otherwise go to else condition
	*/
	/*
		function name 		: deleteDir
		function parameter 	: url/path dir
		function function 	: to delete a dir or file
		function return 	: result of status with json code
	*/
	function deleteDir($dir){ //[73]
		$dir = fixedPath($dir); //[74]
		if(isset($_COOKIE["access_code"])){ //[75]
			if($_COOKIE["access_code"]==md5(access_code)){ //[76]
				if(is_dir($dir)){ //[77]
					$dirList	= array_diff(scandir($dir), array('.','..')); //[78]
					foreach ($dirList as $value) { //[79]
						deleteDir($dir.DIRECTORY_SEPARATOR.$value); //[80]
					}
					if(rmdir($dir)){ //[81]
						$status["status"] 	= 1; 
						$status["message"]	= "successfully remove file or directory.";
					}else{
						$status["status"] 	= 0; 
						$status["message"]	= "there is something wrong in server.";
					}
				}else{
					if(file_exists($dir)){ //[82]
						if(unlink($dir)){ //[83]
							$status["status"] 	= 1; 
							$status["message"]	= "successfully remove file or directory.";
						}else{
							$status["status"] 	= 0; 
							$status["message"]	= "there is something wrong in server.";
						}
					}else{
						$status["status"] 	= 0;	
						$status["message"]	= "the folder or file does not exists.";
					}
				}
			}else{
				$status["status"] 	= 0; 
				$status["message"]	= "you don't have permission to do this action.";
			}
		}else{
			$status["status"] 	= 0; 
			$status["message"]	= "you don't have permission to do this action.";
		}
		return json_encode($status);
	}
	/*
		Explanation
		-----------
		[73] function deleteDir receive the url or path of directory or file
		[74] get fixed path or url from fixedPath function then storing to dir variable
		[75] check is there cookie access code, otherwise return that you don't have permission 
		[76] check is your access code from coockie matched with your access code
		[77] check is url or path is a directory
		[78] get array of scandir
		[79] fetch array of scandir
		[80] call function deleteDir with parameter fetched result of scandir
		[81] if remove dir success return success message otherwise return error message
		[82] if file exists? other wise will return message folder not found
		[83] if delete file success then return success message otherwise return error message
	*/
	/*
		function name 		: createDir
		function parameter 	: url/path and new dirname
		function function 	: to create a dir
		function return 	: result of status with json code
	*/
	function createDir($path, $name){ //[84]
		if(isset($_COOKIE["access_code"])){
			if($_COOKIE["access_code"]==md5(access_code)){

				$path = fixedPath($path); //[85]
				if(file_exists($path)){ //[86]
					if(is_dir($path)){ //[87]
						if(strpbrk($name, "\\/?%*:|\"<>")===FALSE && $name!="." && $name!=""){ //[88]
							if(/*is_dir($path.DIRECTORY_SEPARATOR.$name) && */file_exists($path.DIRECTORY_SEPARATOR.$name)){ //[89]
								$status["status"] 	= 0;	
								$status["message"]	= "folder already exists.";
							}else{
								if(mkdir($path.DIRECTORY_SEPARATOR.$name)){ //[90]
									$status["status"] 	= 1;	
									$status["message"]	= "successfully create directory.";
								}else{
									$status["status"] 	= 0;	
									$status["message"]	= "something wrong with server.";
								}
							}
						}else{
							$status["status"] 	= 0;	
							$status["message"]	= "illegal folder name.";
						}
					}else{
						$status["status"] 	= 0;	
						$status["message"]	= "the path isn't a directory.";
					}
				}else{
					$status["status"] 	= 0;	
					$status["message"]	= "directory path doesn't exists.";
				}
			}else{
				$status["status"] 	= 0; 
				$status["message"]	= "you don't have permission to do this action.";
			}
		}else{
			$status["status"] 	= 0; 
			$status["message"]	= "you don't have permission to do this action.";
		}
		return json_encode($status);
	}
	/*
		Explanation
		-----------
		[84] function createDir receive 2 parameter, the first is path of directory, the second is new name of directory
		[85] get fixed path or url from fixedPath function then storing to dir variable
		[86] check is path exists, otherwise will retrun error message
		[87] check is path is directory otherwise will return error message
		[88] check illegal name directory
		[89] if new directory exits will return error message
		[90] create dir, if success return success message otherwise error message
	*/
	/*
		function name 		: loginCookie
		function parameter 	: accesscode
		function function 	: to create cookie
		function return 	: result of status with json code
	*/
	function loginCookie($access_code){ //[91]
		setcookie("access_code",""); //[92]
		if($access_code==access_code){ //[93]
			if(setcookie("access_code",md5(access_code))){ //[94]
				$status["status"] 	= 1;	
				$status["message"]	= "access granted.";
			}else{
				$status["status"] 	= 0;	
				$status["message"]	= "something went wrong.";
			}
		}else{
			$status["status"] 	= 0;	
			$status["message"]	= "wrong access code.";
		}
		return json_encode($status);
	}
	/*
		Explanation
		-----------
		[91] function loginCookie receive accesscode
		[92] remove access_code cookie first
		[93] if accesscode matched then enter the content otherwise will return error message
		[94] if cookie set success return success message otherwise will return error message
	*/

	/** this code will automatically run when program run **/
	if(!file_exists(public_directory)){mkdir(public_directory);}
	/** end of this code will automatically run when program run **/
	if($_POST){
		if(isset($_POST["uploadFile"]) && isset($_POST["dirActive"])){
			if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				if(isset($_COOKIE["access_code"])){
					if($_COOKIE["access_code"]==md5(access_code)){
						$dirActive	= str_replace("#", "", $_POST["dirActive"])."/";
						$dirActive	= fixedPath($dirActive);

						$count = 0;
						if(is_dir($dirActive)){
							foreach ($_FILES as $key => $value) {
								$tmp_files 	= $_FILES[$count]["tmp_name"];
								$name_files = $_FILES[$count]["name"];
								move_uploaded_file($tmp_files, $dirActive.$name_files);
								$count++;
							}
							$status["status"] 	= 1;
							$status["message"] 	= "successfully upload <strong>".$count."</strong> files";
						}else{
							$status["status"] 	= 0;
							$status["message"] 	= "directory destination doesn't exists";
						}
					}else{
						$status["status"] 	= 0;
						$status["message"] 	= "you don't have permission to do this action.";
					}
				}else{
					$status["status"] 	= 0;
					$status["message"] 	= "you don't have permission to do this action.";
				}
				echo json_encode($status);
				exit();
			}else{
				header("location:/");
				exit();
			}
		}
		if(isset($_POST["access_code"])){
			if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				$getAccess = $_POST["access_code"];
				echo loginCookie($getAccess);
				exit();
			}else{
				header("location:/");
				exit();
			}
		}
		if(isset($_POST["listDir"])){
			if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				$getAccess = $_POST["listDir"];
				echo listDir($getAccess);
				exit();
			}else{
				header("location:/");
				exit();
			}
		}
		if(isset($_POST["createDir"]) && isset($_POST["newName"])){
			if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				$createDir = $_POST["createDir"];
				$newName   = $_POST["newName"];
				echo createDir($createDir,$newName);
				exit();
			}else{
				header("location:/");
				exit();
			}
		}
		if(isset($_POST["renameDir"]) && isset($_POST["newName"])){
			if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				$renameDir = $_POST["renameDir"];
				$newName   = $_POST["newName"];
				echo renameDir($renameDir,$newName);
				exit();
			}else{
				header("location:/");
				exit();
			}
		}
		if(isset($_POST["deleteDir"])){
			if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
				$deleteDir = $_POST["deleteDir"];
				echo deleteDir($deleteDir);
				exit();
			}else{
				header("location:/");
				exit();
			}
		}
	}

	function pageLogin(){
		echo "
			<table class=\"fullPage paddingless\"><tr><td>
				<div class=\"topFront\">
					<div class=\"siteTitle\">
						<h1 style=\"font-size:3.5em;\">".site_name."</h1>
						<h4 style=\"font-size:1.2em;\">".site_description."</h4>
					</div>
					<div class=\"siteLogin hide\">
						<h4 style=\"font-size:1.2em;\">enter your access code : </h4>
						<form class=\"formAjax\" data-get=\"login\">
							<input type=\"password\" class=\"inputAccess\" placeholder=\"*****\" name=\"access_code\">
						</form>
					</div>
				</div>
				<div class=\"btnEnter btnShow\" data-show=\".siteLogin\" data-hide=\".siteTitle\" data-change=\"&larr; back\" add-class=\"btnEnterActive\" remove-class=\"\">enter</div>
				<div class='publicBtn requestList' data-dir=\"".public_directory."\">go to public folder &rarr;</div>
			</td></tr></table>
		";
	}
	function pageHome(){
		$newFolderBtn 	= "";
		$uploadFileBtn 	= "";
		if(isset($_COOKIE["access_code"])){
			if($_COOKIE["access_code"]==md5(access_code)){
				$newFolderBtn 	= "<div class='btnNewFolder'>Create folder</div>";
				$uploadFileBtn 	= "<div class='uploadFileBtn'>Upload file</div>";
			}
		}
		echo "
			<table class=\"listDirPage\">
				<tr>
					<td class=\"topLeft requestList\" data-dir=\"\" rowspan=\"3\" align='center'>
						<h2>".site_name."</h2>
						<p>".site_description."</p>
					</td>
					<td class='veryTop'>
						<ul class='breadC'>
						</ul>
					</td>
				</tr>
				<tr>
					<td class='middleTop'>
						<div class='leftCorner'></div>
						<div class='rightCorner'></div>
					</td>
				</tr>
				<tr>
					<td class=\"tHeader\">
						<table class=\"paddingLess tblFix fullWidth listTH\"  cellspacing=\"0\">
							<tr>
								<td>Name</td>
								<td>Type</td>
								<td>Modified</td>
								<td>Size</td>
							</tr>
						</table>	
					</td>
				</tr>
				<tr>
					<td class='leftContent' valign='top'>
						".$newFolderBtn."
						".$uploadFileBtn."
					</td>
					<td class=\"rightContent\">
						<div class=\"frameTable\">
							<table class=\"paddingLess tblFix fullWidth listDir\" cellspacing=\"0\">
								
							</table>	
						</div>
					</td>
				</tr>
			</table>
		";
	}
?>
<!doctype html>
<html>
	<head>
		<title><?php echo site_name; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="<?php echo site_description; ?>">
		<meta name="author" content="Andi Muqsith Ashari">
		<link rel="author" href="https://plus.google.com/+AndiMuqsithAshari"/>
		<link rel="publisher" href="https://plus.google.com/+AndiMuqsithAshari"/>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
		<style type="text/css">
			@import url(http://fonts.googleapis.com/css?family=Open+Sans:400,300,600);
			html,body,.loadAll,.listDirPage{
				padding: 0;
				margin: 0;
				width: 100%;
				height: 100%;	
				font-family: 'Open Sans', sans-serif;
				font-weight: 300;
				color: rgba(44, 62, 80,1.0);
			}
			.topLeft,
			.topLeft *,
			.paddingless,
			.paddingless *,
			.breadC,
			.breadC li{
				margin: 0;
				padding: 0;
				border-collapse: collapse;
			}
			.fullPage,
			.fullPage td{
				width: 100%;
				height: 100%;
			}
			.fullPage td{
				text-align: center;
				vertical-align: middle;
			}
			.topFront{
				overflow: hidden;
				height: 120px;
			}
			.leftContent{

			}
			.btnEnter{
				color: #2980b9;
				border:2px solid #2980b9;
				width: 100px;
				padding: 0.4em;
				font-size: 0.8em;	
				border-radius: 5px;
				margin: 3em auto 0 auto;
				cursor: pointer;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none; 
				-o-user-select: none;
				user-select: none;
			}
			.btnEnterActive{
				border-color: #7f8c8d;
				color: #7f8c8d;
			}
			.detail_info{
				padding: 0;
				margin: 0;
				border-collapse: collapse;
				margin-bottom: 1.5em;
			}
			.detail_info td{
				padding: 0 1em;
			}
			.hide{
				display: none;
			}
			.inputAccess{
				font-size: 1.5em;
				margin: 1em 0;
				padding: 0.2em 0.5em;
				outline: none;
				border:0;
				border-bottom: 2px solid #7f8c8d;
				text-align: center;
			}
			.loadingProgress{
				position: fixed;
				top:0;
				left: 0;
				height: 3px;
				display: none;
				background: #2980b9;
				z-index: 2;
			}
			.publicBtn{
				margin: 4em 0 0 0;
				font-weight: bold;
				font-size: 0.8em;
				cursor: pointer;
				color: rgba(127, 140, 141,1.0);
			}
			@-webkit-keyframes blink {
			    0% { background: rgba(41, 128, 185,1); }
			    50% { background: rgba(41, 128, 185,0.6); }
			    100% { background: rgba(41, 128, 185,1); }
			}
			@keyframes blink {
			    0% { background: rgba(41, 128, 185,1); }
			    50% { background: rgba(41, 128, 185,0.6); }
			    100% { background: rgba(41, 128, 185,1); }
			}
			.blinkAnimate { 
			    background: rgba(41, 128, 185,1.0);
			}
			.blinkAnimate {
			    -webkit-animation-direction: normal;
			    -webkit-animation-duration: 1.5s;
			    -webkit-animation-iteration-count: infinite;
			    -webkit-animation-name: blink;
			    -webkit-animation-timing-function: ease;   

			    animation-direction: normal;
			    animation-duration: 1.5s;
			    animation-iteration-count: infinite;
			    animation-name: blink;
			    animation-timing-function: ease;       
			}
			.alertSection{
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
			}
			.alert{
				width: 250px;
				padding: 0.8em 1em;
				margin: 2em auto;
				font-size: 0.8em;
				text-align: center;
				border:1px solid #ccc;
				border-radius: 5px;
				cursor: pointer;
				display: none;
			}
			.badAlert{
				color: rgba(192, 57, 43,1.0);
				border-color: rgba(192, 57, 43,1.0);
				background: rgba(192, 57, 43,0.2);
			}
			.goodAlert{
				color: rgba(39, 174, 96,1.0);
				border-color: rgba(39, 174, 96,1.0);
				background: rgba(39, 174, 96,0.2);
			}
			.listDirPage{
				font-weight: normal;
				border-collapse: collapse;
				font-size: 0.8em;
			}
			.listDirPage .topLeft{
				width: 15em;
			}
			.listDirPage .tHeader,.veryTop,.middleTop{
				height: 3em;
			}
			.fullWidth{
				width: 100%;
			}
			.rightContent{
				vertical-align: top;
				padding-right: 0;
			}
			.rightContent .frameTable{
				width: 100%;
				height: 100%;
				overflow-y:auto;
			}
			.tblFix,.tblFix td{
				border-collapse: collapse!important;
				padding: 0!important;
				margin: 0!important;
				border:0;
			}
			.tblFix td:first-child{
				padding-left: 1.5em!important;
			}
			.tblFix td:last-child{
				padding-right: 1.5em!important;
			}
			.list td,
			.tHeader td,
			.newFolderForm td{
				padding: 1em 0!important;
				border-bottom: 1px solid #E5E5E5;
			}
			.list,.newFolderForm{				
				cursor: pointer;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none; 
				-o-user-select: none;
				user-select: none;
			}
			.tHeader td{
				border-bottom: 2px solid #E5E5E5;
			}
			.requestList{
				cursor: pointer;
				color: rgba(41, 128, 185,1.0);
			}
			.list:hover{
				background: #F5FAFE;
			}
			.activeList,
			.activeList:hover{
				background: #E3F2FF;
			}
			.FolderStyle,.selectedName{
				color: rgba(41, 128, 185,1.0);
			}
			.ZipStyle{
				color: rgba(142, 68, 173,1.0);
			}
			.breadC,
			.breadC li,
			.selected,
			.selected li{
				display: inline-block;
				list-style: none;
			}
			.breadC{
				font-weight: bold;
				margin-left: 1.5em;
			}
			.selected{
				margin-left: 1.5em;
			}
			.raquo{
				padding: 0 1em;
				float: left;
			}
			.topLeft{
				/*background: rgba(41, 128, 185,1.0);
				color: #FFF;*/
			}
			.veryTop,.middleTop,.tHeader{
				/*background: rgba(22, 160, 133,0.1);*/
			}
			.leftContent{
				/*background: rgba(22, 160, 133,0.1);*/
			}
			.btnNewFolder, .uploadFileBtn{
				width: 65%;
				margin: 0 auto;
				padding: 0.5em 0;
				text-align: center;
				border:1px solid rgba(41, 128, 185,1.0);
				background: rgba(41, 128, 185,0.3);
				color: rgba(41, 128, 185,1.0);
				border-radius: 5px;
				cursor: pointer;
				font-size: 0.9em;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none; 
				-o-user-select: none;
				user-select: none;
			}
			.uploadFileBtn{
				margin-top: 1em;
				border-color: rgba(39, 174, 96,1.0);
				background: rgba(39, 174, 96,0.3);
				color: rgba(39, 174, 96,1.0);
			}
			.uploadFileBtnA{
				background: rgba(39, 174, 96,0.1);
			}
			.btneNewFolderA{
				background: rgba(41, 128, 185,0.1);
			}
			.inputFoldername{
				padding: 5px;
				margin-top: -5px;
				margin-bottom: -5px;
				width: 250px
			}
			.leftCorner{
				float: left;
			}
			.rightCorner{
				float: right;
				margin-right: 1.5em;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none; 
				-o-user-select: none;
				user-select: none;
			}
			.menuList{
				float: right;
				margin-left: 1.5em;
				font-weight: bold;
				cursor: pointer;
			}
			.btn{
				font-weight: bold;
				cursor: pointer;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none; 
				-o-user-select: none;
				user-select: none;
				color: rgba(41, 128, 185,1.0);
			}
			.goodBtn{
				color:rgba(39, 174, 96,1.0);
			}
			.badBtn{
				color: rgba(192, 57, 43,1.0);
			}
			.uploadContent{
				width: 100%;
				height: 100%;
				position: fixed;
				background: rgba(41, 128, 185,0.1);
				padding: 1em;
			}
			.uploadContent{
				width: 100%;
				height: 100%;
				vertical-align: middle;
				text-align: center;
			}
			.uploadContent{
				padding: 2em;
			}
			.uploadContent td{
				border-radius: 5px;
				border:2px solid rgba(41, 128, 185,1.0);
				background: rgba(41, 128, 185,0.8);
				color: #fff;
			}
			.mbtn{
				margin-bottom: 5em;
			}
			.btnCancelUpload{
				font-size: 0.9em;
				margin: 0 auto;
				cursor: pointer;
				border: 1px solid #fff;
				width: 100px;
				padding: 0.2em 0;
				border-radius: 5px;
			}
			.uploadContent .rightUpload{
				width: 150px;
			}
			.frameUpload{
				height: 100%;
				width: 100%;
				overflow-y:auto;
			}
			.statusUpload{
				width: 100%;
				padding: 0 1em;
			}
			.statusUpload td{
				width: 150px;
				overflow: hidden;
			}
			.statusUpload td div{
				font-size: 0.8em;
				background: rgba(255,255,255,0.5);
				border-radius: 2px;
				overflow: hidden;
				width: 0;
			}
			.bar{
				width: 50%;
				padding: 0.3em;
				cursor: pointer;
				border: 1px solid #fff;
				border-radius: 5px;
				margin: 1em auto;
				font-size: 0.8em;
			}
			@-ms-viewport{
			  width: device-width;
			}
		</style>
	</head>
	<body data-pub="<?php echo public_directory; ?>">
		<div class="loadingProgress blinkAnimate"></div>
		<table class='uploadContent hide'>
			<td>
				<h1>Drag and drop your files here</h1>
				<h5 class='mbtn'>or click here to browse your file <input type='file' multiple></h5>
				<div class='uploadingStatus hide'>
					<div class='messageUpload'></div>
				</div>
				<div class='btnCancelUpload clsUploadMdlBtn'>close</div>
			</td>
		</table>
		<div class="alertSection"><div class="alert"></div></div>
		<div class="loadAll">
			<?php
			if(isset($_COOKIE["access_code"])){
				if($_COOKIE["access_code"]==md5(access_code)){
					pageHome();	
				}else{
					pageLogin();
				}
			}else if(isset($_GET["specialReq"])){
				pageHome();
			}else{
				pageLogin();
			}
			?>
		</div>
	</body>
	<script type="text/javascript">
		$(document).ready(function(){
			var public_directory = $("body").attr("data-pub");
			if(getCookie("access_code")){
				listDIr(location.hash);	
			}else if(location.hash.replace("#","").substr(0, public_directory.length)==public_directory){
				loadPub();
			}
			window.onhashchange = function(){
				if(location.hash.replace("#","").substr(0, public_directory.length)==public_directory){
					loadPub();
				}else{
					listDIr(location.hash);
				}
			
			};
			$(document).on("click",".clsUploadMdlBtn",function(){
				$(".uploadContent").addClass("hide");
			});
			$(document).on("click",".btnNewFolder",function(){
				$(".newFolderForm").remove();
				$(this).removeClass("btneNewFolderA");
				$('.listDir tr:first').before("<tr class='newFolderForm'><td><form class='formAjax' data-get='current'><input type='hidden' name='createDir' value=\""+location.hash.replace("#","")+"\" class='reqCurDir'><input type='text' name='newName' class='inputFoldername' placeholder='type folder name here...'></form></td><td></td><td></td><td></td></tr>");
				$(this).addClass("btneNewFolderA");
			});
			$(document).on("click",".uploadFileBtn",function(){
				$(".uploadContent").removeClass("hide");
			});
			$(document).on("click",".btneNewFolderA",function(){
				$(this).removeClass("btneNewFolderA");
				$(".newFolderForm").remove();
				$(".inputFoldername").val("");
			});

			mouse_is_insidebtnNew = false;
			$(document).on("mouseenter",".listDir",function(){
				mouse_is_insidebtnNew=true; 
			});
			$(document).on("mouseleave",".listDir",function(){
				mouse_is_insidebtnNew=false; 
			});
			$(document).on("mouseenter",".btnNewFolder",function(){
				mouse_is_insidebtnNew=true; 
			});
			$(document).on("mouseleave",".btnNewFolder",function(){
				mouse_is_insidebtnNew=false; 
			});

			$(document).on("mouseup","body",function(){
				if(!mouse_is_insidebtnNew) {
					$(".btneNewFolderA").click();
		        }
		        $(".alert").click();
			});
			$(document).on("dblclick",".list",function(){
				var dir 	= $(this).attr("data-dir");
				var type 	= $(this).attr("data-type");
				if(type.toLowerCase()=="folder"){
					window.location.hash = dir;
				}
			});
			$(document).on("click",".list",function(){
				$(".list").removeClass("activeList");
				$(this).addClass("activeList");
				var name 	= $(this).attr("dir-name");
				var path 	= $(this).attr("data-dir");
				var type 	= $(this).attr("data-type");
				var id  	= $(this).attr("id");

				$(".leftCorner").html("<div class='selected'>Selected : <span class='selectedName'>"+name+"</span></div>");
				$(".rightCorner").html("");

				if(getCookie("access_code")){
					$(".rightCorner").append("<div class='menuList deleteBtn btn' data-id='"+id+"' data-dir=\""+path+"\" data-name=\""+name+"\">Delete</div>");
				}

				if(type.toLowerCase()=="file" || type.toLowerCase()=="zip"){
					$(".rightCorner").append("<a href=\""+path+"\" class='btn' download><div class='menuList'>Download</div></a>");
				}
				if(type.toLowerCase()=="zip"){
					$(".rightCorner").append("<div class='menuList btn' data-id='"+id+"' data-dir=\""+path+"\" data-name=\""+name+"\">Unzip</div>");
				}

				if(getCookie("access_code")){
					$(".rightCorner").append("<div class='menuList renameBtn btn' data-id='"+id+"' data-dir=\""+path+"\" data-name=\""+name+"\">Rename</div>");
				}
			});
			$(document).on("click",".renameBtn",function(){
				var dataDir 	= $(this).attr("data-dir");
				var dataID  	= $(this).attr("data-id");
				var dataName  	= $(this).attr("data-name");
				$(".rightCorner").html("<form class='formAjax' data-get='current'>rename <strong>"+dataName+"</strong> to <input type='hidden' name='renameDir' value=\""+dataDir+"\"><input style='margin-left:0.5em;' class='inputFoldername' type='text' name='newName' value=\""+dataName+"\"> or <strong class='btn cancelDelBtn goodBtn' data-id='"+dataID+"'>Cancel</strong></form>");
			});
			$(document).on("click",".deleteBtn",function(){
				var dataDir 	= $(this).attr("data-dir");
				var dataID  	= $(this).attr("data-id");
				$(".rightCorner").html("are you sure? <strong class='badBtn btn yesDelBtn' data-dir=\""+dataDir+"\">Yes</strong> or <strong class='btn cancelDelBtn goodBtn' data-id='"+dataID+"'>Cancel</strong>");
			});
			$(document).on("click",".yesDelBtn",function(){
				var dataDir = $(this).attr("data-dir");
				var dataGet = "deleteDir="+dataDir;
				requestAjax("POST", dataGet, "json", "current");
			});
			$(document).on("click",".cancelDelBtn",function(){
				var dataID  	= $(this).attr("data-id");
				$("#"+dataID).click();
				$("#"+dataID).click();
			});
			$(document).on("click",".activeList",function(){
				$(this).removeClass("activeList");
				$(".leftCorner").html("");
				$(".rightCorner").html("");
			});
			$(document).on("click",".requestList",function(){
				var dir = $(this).attr("data-dir");
				window.location.hash = dir;
			});
			$(document).on("click",".btnShow",function(e){
				e.preventDefault();
				$("input").val("");

				var dataShow 	= $(this).attr("data-show");
				var dataHide 	= $(this).attr("data-hide");
				var dataChange 	= $(this).attr("data-change");
				var addWhtClass	= $(this).attr("add-class");
				var rmvWhtClass	= $(this).attr("remove-class");
				var dataDefault	= $(this).html();

				var temp = dataDefault;
				$(this).html(dataChange);
				$(this).attr("data-change",temp);

				$(dataShow).show();
				$(dataHide).hide();

				temp = addWhtClass;
				$(this).attr("add-class",rmvWhtClass);
				$(this).attr("remove-class",addWhtClass);

				$(this).addClass(addWhtClass);
				$(this).removeClass(rmvWhtClass);

				temp = dataHide;
				$(this).attr("data-hide",dataShow);
				$(this).attr("data-show",temp);
			});
			$("body").on("keyup",function(e){
				if(e.keyCode==27){
					$(".btneNewFolderA").click();
					$(".activeList").click();
					$(".btnEnterActive").click();
				}
			});
			$(document).on("click",".alert",function(){
				$(this).hide();
			});
			$(window).resize(function(){
				fixedTableWidth();
			});
			$(document).on("submit",".formAjax",function(e){
				e.preventDefault();
				var data 	= $(this).serialize();
				var dataGet = $(this).attr("data-get");
				requestAjax("POST",data,"json",dataGet);
			});

			mouse_is_inside=false; 
			$(document).on("mouseenter",".list",function(){
				mouse_is_inside=true; 
			});
			$(document).on("mouseleave",".list",function(){
				mouse_is_inside=false; 
			});
			$(document).on("mouseenter",".middleTop",function(){
				mouse_is_inside=true; 
			});
			$(document).on("mouseleave",".middleTop",function(){
				mouse_is_inside=false; 
			});

			$(document).on("mouseup","body",function(){
				if(!mouse_is_inside) {
					$(".leftCorner").html("");
					$(".rightCorner").html("");
					$(".list").removeClass("activeList");
		        }
			});

			var obj = $("body");
			obj.on('dragenter', function (e) {
			    e.stopPropagation();
			    e.preventDefault();
			    $(".uploadContent").removeClass("hide");
			});
			obj.on('dragover', function (e) {
			    e.stopPropagation();
			    e.preventDefault();
			});
			$('input[type=file]').change(function(e) {
				e.preventDefault();
				var files = this.files;
			    uploadAjaxFile(files);
			});
			obj.on('drop', function (e) {
			    e.preventDefault();
			    var files = e.originalEvent.dataTransfer.files;
			    uploadAjaxFile(files);
			});
			function loadingComputableStart(percentage){
				$(".loadingProgress").css("width","0px");
				$(".loadingProgress").show();
				if(percentComplete>=92){
					percentComplete=92;
				}
				$(".loadingProgress").css("width",percentage+"%");
			}
			function loadingStart(){
				$(".loadingProgress").css("width","0px");
				$(".loadingProgress").show();


				$({property: 0}).animate({property: 90}, {
			        duration: 3000,
			        step: function() {
			          	var _percent = Math.round(this.property);
			          	$(".loadingProgress").css("width",  _percent+"%");
			        }
			    });
			}
			function loadingStop(){
				$(".loadingProgress").hide();
				$(".loadingProgress").css("width","0px");
				$(".messageUpload").html("");
			}
			function loadPub(){
				var url = window.location.href.replace(window.location.hash,"");
				$(".loadAll").load(url+"?specialReq=public_directory .loadAll",function(){
					listDIr(location.hash);	
				});
			}
			function listDIr(dir){
				dir = dir.replace("#","");
				requestAjax("POST","listDir="+dir,"json","listDir");
			}
			function uploadAjaxFile(files){
				$(".btnCancelUpload").addClass("hide");
				$(".uploadingStatus").removeClass("hide");

				$(".messageUpload").html("Please waiting we're uploading <strong class='totFiles'>"+files.length+"</strong> files...");

				var fd = new FormData();
			    for(var i = 0;i<files.length;i++){
			        fd.append(i, files[i]);
			    }
			    fd.append("uploadFile","yes");
			    fd.append("dirActive",location.hash);
			    $.ajax({
			    	xhr: function(){
						var xhr = new window.XMLHttpRequest();
						xhr.upload.addEventListener("progress", function(evt){
						  	if (evt.lengthComputable) {
						    	var percentComplete = evt.loaded / evt.total * 100;
						    	loadingComputableStart(percentComplete);
						  	}else{
						  		loadingStart();
						  	}
						}, false);
						xhr.addEventListener("progress", function(evt){
						  	if (evt.lengthComputable) {
						    	var percentComplete = evt.loaded / evt.total * 100;
						    	loadingComputableStart(percentComplete);
						  	}else{
						  		loadingStart();
						  	}
						}, false);
						return xhr;
					},
			        url 			: 'index.php',
			        data 			: fd,
			        contentType 	: false,
			        processData 	: false,
			        type 			: 'POST',
			        success: function(data){
						var resultJson = JSON.parse(data);
			        	if(resultJson.status==0){
			        		showAlert(resultJson);
			        		loadingStop();

			        		$(".uploadingStatus").addClass("hide");
							$(".btnCancelUpload").removeClass("hide");
			        	}else{
			        		$(".alert").html(resultJson.message);
							$(".alert").addClass("goodAlert");
							$(".alert").show();
							$("input").val("");
							$(".leftCorner").html("");
							$(".rightCorner").html("");
			        		loadingStop();

			        		$(".uploadingStatus").addClass("hide");
							$(".btnCancelUpload").removeClass("hide");

							listDIr(location.hash);
							$('input[type=file]').val("");
			        	}

			        }
			    });
			}
			function requestAjax(typeGet, dataGet, methodGet, whatGet){
				$(".alert").hide();
				$.ajax({
					xhr: function(){
						var xhr = new window.XMLHttpRequest();
						xhr.upload.addEventListener("progress", function(evt){
						  	if (evt.lengthComputable) {
						    	var percentComplete = evt.loaded / evt.total * 100;
						    	loadingComputableStart(percentComplete);
						  	}else{
						  		loadingStart();
						  	}
						}, false);
						xhr.addEventListener("progress", function(evt){
						  	if (evt.lengthComputable) {
						    	var percentComplete = evt.loaded / evt.total * 100;
						    	loadingComputableStart(percentComplete);
						  	}else{
						  		loadingStart();
						  	}
						}, false);
						return xhr;
					},
					type 		: typeGet,
					data 		: dataGet,
					url 		: document.URL,
			        processData	: false,
			        cache		: false,
					success 	: function(result){
						if(methodGet=="json"){
							var resultJson = JSON.parse(result);
							if(resultJson.status==0){
								if(whatGet=="login"){
									showAlert(resultJson);
								}else if(!getCookie("access_code") && location.hash==""){
									$(".loadAll").load(window.location.href.replace(window.location.hash,"")+" .loadAll");
								}else{
									showAlert(resultJson);
								}
							}else if(whatGet=="listDir"){
								loadDir(resultJson);
							}else if(whatGet=="login"){
								requestAjax("POST","","requestPage",document.URL);
							}else if(whatGet=="current"){
								listDIr(location.hash.replace("#",""));
								$(".btneNewFolderA").click();
							}
						}else if(methodGet=="requestPage"){
							$(".loadAll").load(whatGet+" .loadAll",function(){
								listDIr("");
							});
						}
						loadingStop();
					}
				});
			}
			function showAlert(resultJson){
				$(".alert").html(resultJson.message);
				$(".alert").addClass("badAlert");
				$(".alert").show();
				$("input").val("");
				$(".leftCorner").html("");
				$(".rightCorner").html("");
			}
			function loadDir(jsonDir){
				$(".leftCorner").html("");
				$(".rightCorner").html("");
				$(".listDir").empty();
				$(".listDir tr").remove();
				var list_id  = 0;
				
				$(".breadC li").remove();
				$(".breadC").append("<li><a class='requestList' data-dir=\"\">Home</a></li>");

				var current = jsonDir.current.replace("./","");
				current_a 	= current.split("/");
				
				for(var i=0;i<current_a.length-1;i++){
					url = current.substr(0, current.indexOf(current_a[i]+"/"))+current_a[i];
					$(".breadC").append("<li><div class='raquo'>&raquo;</div><a class='requestList' data-dir=\""+url+"\"'>"+current_a[i]+"</a></li>");
				}
				if(window.location.hash!=0){
					if((current_a[current_a.length-1]).length>=1){
						$(".breadC").append("<li><div class='raquo'>&raquo;</div><a class='requestList' data-dir=\""+window.location.hash.replace("#","")+"\">"+current_a[current_a.length-1]+"</a></li>");
					}
				}
				var total_count = 0;
				$.each(jsonDir.data,function(index,value){
					var name = index;
					var modi = value.modified;
					var size = value.size;
					path 	 = value.path.replace("./","");

					if(value.type==0){
						var type="Folder";
					}else if(value.type==2){
						var type="Zip";
					}else{
						var type="File";
					}
					$(".listDir").append("<tr id='L"+list_id+"' dir-name='"+name+"' data-type='"+type+"' data-dir=\""+path+"\" class='list'></tr>");
					$("#L"+list_id).append("<td>"+name+"</td>");
					$("#L"+list_id).append("<td>"+type+"</td>");
					$("#L"+list_id).append("<td>"+modi+"</td>");
					$("#L"+list_id).append("<td>"+size+"</td>");

					if(type=="Folder"){
						$("#L"+list_id).addClass("FolderStyle");
					}
					if(type=="Zip"){
						$("#L"+list_id).addClass("ZipStyle");
					}

					list_id++;
					total_count++;
				});
				if(total_count==0){
					$(".listDir").append("<tr><td colspan='4' align='center' valign='middle' style='padding:4em 0!important;'>Folder is empty</td></tr>");
				}
				fixedTableWidth();
			}
			function fixedTableWidth(){
				$(".listTH td:first").css("width",($("body").width()-$(".topLeft").width())*40/100+"px");
				$(".listDir td:first").css("width",($("body").width()-$(".topLeft").width())*40/100+"px");
				$(".listTH td:nth-child(2)").css("width",($("body").width()-$(".topLeft").width())*20/100+"px");
				$(".listDir td:nth-child(2)").css("width",($("body").width()-$(".topLeft").width())*20/100+"px");
				$(".listTH td:nth-child(3)").css("width",($("body").width()-$(".topLeft").width())*20/100+"px");
				$(".listDir td:nth-child(3)").css("width",($("body").width()-$(".topLeft").width())*20/100+"px");
			}
			function getCookie(name) {
			    var dc = document.cookie;
			    var prefix = name + "=";
			    var begin = dc.indexOf("; " + prefix);
			    if (begin == -1) {
			        begin = dc.indexOf(prefix);
			        if (begin != 0) return null;
			    }else{
			        begin += 2;
			        var end = document.cookie.indexOf(";", begin);
			        if (end == -1) {
			        end = dc.length;
			        }
			    }
			    return unescape(dc.substring(begin + prefix.length, end));
			}
		});
	</script>
</html>
