<?php
	session_start();
	
	$codeSecret		= "1234";

	$lang_es		= array(
		 "get" 		=> "Obtener"
		,"save" 		=> "Guardar"		
		,"refresh" 	=> "Recargar"		
		,"restore" 	=> "Restaurar"		
		,"clear" 		=> "Limpiar"		
		,"logout" 	=> "salir"		
		,"focus" 		=> "Foco"		
		,"example" => "Ej"		
		,"close"		=> "Cerrar"
		,"emptyFileName"=> "El nombre del archivo no debe ser vacio"
		,"notExistFile"	=> "No existe el archivo"
		,"changeTheme"	=> "Cambiando al tema"
		,"reload"		=> "Recargando"
		,"saving"		=> "Guardando"
		,"getting"		=> "Obteniendo"
		,"formatDate"	=> "d/m/Y H:i:s"	
		,"placeholder" => "nombreArchivo.ext o ruta/a/nombreArchivo.ext o ../../ruta/a/nombreArchivo.ext donde esta incluido phpWebEdit"
		,"history"		=> "Historial"
		,"backups"		=> "Cant. backups"
		,"theme"		=> "Cambiar Tema"		
	);
	
	$lang_en		= array(
		 "get" 		=> "Get"
		,"save" 		=> "Save"		
		,"refresh" 	=> "Refresh"		
		,"restore" 	=> "Restore"		
		,"clear" 		=> "Clear"		
		,"logout" 	=> "Logout"		
		,"focus" 		=> "Focus"		
		,"example" => "Ex"		
		,"close"		=> "Close"
		,"emptyFileName"=> "File name not is empty"
		,"notExistFile"	=> "Not file exist"
		,"changeTheme"	=> "Changed Theme"
		,"reload"		=> "Reload"
		,"saving"		=> "Saving"
		,"getting"		=> "Getting"
		,"formatDate"	=> "Y-m-d H:i:s"		
		,"placeholder" => "fileName.ext or path/to/fileName.ext or ../../path/to/fileName.ext where include phpWebEdit"		
		,"history"		=> "History"
		,"backups"		=> "Count backups"
		,"theme"		=> "Change Theme"				
	);
	
	$projectName 	= "phpWebEdit";
	$userLog 		= false;
	$file 			= "";
	$text 			= "";
	$fileNotExist 	= 1;
	$emptyName 		= "No";
	$lastUpdateFile = "-";
	$mode			= "application/x-httpd-php";
	
	if( isset( $_GET['logout'] ) ) {
		unset($_SESSION["phpWebEdit_LOG"]);
		unset($_SESSION["phpWebEdit_LANG"]);		
		session_destroy();
	}
	
	if( isset( $_POST['btnLogin'] ) ) {
		if( $_POST["pss"] == $codeSecret) {
			$_SESSION["phpWebEdit_LOG"] = true;
			$_SESSION["phpWebEdit_LANG"] = ( $_POST[ "selLang" ] == "ES" ) ? "ES" : "EN";			
		}
	}

	if ( isset( $_SESSION["phpWebEdit_LOG"] ) ) {
		$lang = ( $_SESSION["phpWebEdit_LANG"] == "EN" ) ? $lang_en : $lang_es;		
		$userLog = true;
		$file = isset( $_GET['file'] ) ? $_GET['file'] : null;
		if ( isset( $_POST['getFile'] ) ) {
			$file = stripslashes( $_POST['file'] );
			$url = 'index.php?file=' . $file;
			header( sprintf( 'Location: %s', $url ) );
			printf( '<a href="%s">Moved</a>.', htmlspecialchars( $url ) );
			exit();		
		}
		if ( isset( $_POST['save'] ) ) {
			if (  $_POST['file'] != "" ) {

				$file = stripslashes( $_POST['file'] );
				
				$charsFile = str_split($file);
				if ( count( $charsFile ) > 0  ) {
					for ( $i = 0; $i < count( $charsFile ); $i++ ) {
						if ( $charsFile[$i] == "/" ) {
							$file = substr($file,1,strlen($file));
						}
						else {
							break;
						}
					}
				}
				$url = 'index.php?file=' . $file;
				$contBackup = 1;
				$run = true;
					
				if ( !file_exists ( $file  ) ) {
					$folders = explode("/", $file );
					if ( count( $folders ) >  1 ) {
						$folder = str_replace( "/" . $folders[ count( $folders ) - 1 ], "", $file ) ;
						if (!file_exists($folder)) {
							mkdir($folder, 0777, true);
						}
					}
					$newFile = fopen( $_POST['file'] , "a+" );	
				}
				else {
					do {
						if ( $contBackup < 10 ) { $contBackup = "00". $contBackup; }
						if ( $contBackup >= 10 ) { $contBackup = "0". $contBackup;}
						if ( !file_exists ( $file . "-" . $contBackup . ".backup" ) ) {
							rename( $file , $file . "-" . $contBackup . ".backup" );
							$run = false;
						}						
						$contBackup++;
					} while($run);
				}

				file_put_contents( $file , stripslashes( $_POST['text'] ) );

				header( sprintf( 'Location: %s', $url ) );
				printf('<a href="%s">Moved</a>.', htmlspecialchars( $url ) );
				exit();
			}
			else {
				$emptyName = "Yes";
			}
		}

		$countBackup = 0;
		$filesNames = "";
		$isDir = false;
		$dirList = "";
		if ( file_exists( $file ) ) {
			$fileNotExist 	= 0;
			$fileInfo 		= pathinfo($file);
			if ( isset ( $fileInfo[ "extension" ] ) ) {
				$extension		= $fileInfo[ "extension" ];
				$extension		= strtolower($extension);

				if ( $extension == "css" ) 	{ $mode = "text/css"; 				}
				if ( $extension == "js" ) 		{ $mode = "text/javascript"; 	}		
				if ( $extension == "xml" ) 	{ $mode = "application/xml"; 	}
				if ( $extension == "html" ) 	{ $mode = "text/html"; 			}
				if ( $extension == "sql" ) 	{ $mode = "text/x-sql"; 			}


				$lastUpdateFile	= date ( $lang[ "formatDate" ], filemtime( $file ) );	
				$file_aux = explode("-",$file);
				$file_aux = $file_aux[0];
				$filesNames = '- <a href="index.php?file='.$file_aux.'">'.$file_aux.'</a><br>';			
				foreach (glob($file_aux . "-*.backup") as $file_name) {
					$lastUpdateFileN	= date ( $lang[ "formatDate" ], filemtime( $file_name ) );
					$filesNames .= '- <a href="index.php?file='.$file_name.'">'.$file_name.'</a> <small>('. $lastUpdateFileN .')</small><br>';
					$countBackup++;				
				}
				$text = file_get_contents($file);				
			}
			else{
				$isDir = true;
				if ($handle = opendir($file)) {											
					while (false !== ($entry = readdir($handle))) {
						if ($entry != "." && $entry != "..") {
							$dirList .= '- <a href="index.php?file='.$file . "/" . $entry.'">'.$entry.'</a><br>';							
						}
					}
					$dirUp = dirname($file);
					$dirNow = str_replace( $dirUp, "", $file);
					$dirList = '<a href="index.php?file='.$dirUp .'">'.$dirUp.'</a>'.$dirNow.'<br/>' . $dirList;						
					closedir($handle);
				}
			}
		}

	} // end phpWebEdit_LOG
?>

<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />		
		
		<title><?=$projectName;?> - <?=stripslashes($file)?></title>
		
        <link rel="stylesheet" href="LibExt/codemirror/lib/codemirror.css">
		<link rel="stylesheet" href="LibExt/codemirror/doc/docs.css">
		<link rel="stylesheet" href="LibExt/codemirror/lib/codemirror.css">			
		<link rel="stylesheet" href="LibExt/bootstrap/bootstrap.css">		
		
		<script src="LibExt/codemirror/addon/edit/matchbrackets.js"></script>
		
		<script src="LibExt/codemirror/lib/codemirror.js"></script>
		<script src="LibExt/codemirror/mode/htmlmixed/htmlmixed.js"></script>
		<script src="LibExt/codemirror/mode/xml/xml.js"></script>
		<script src="LibExt/codemirror/mode/javascript/javascript.js"></script>
		<script src="LibExt/codemirror/mode/css/css.js"></script>
		<script src="LibExt/codemirror/mode/clike/clike.js"></script>
		<script src="LibExt/codemirror/mode/php/php.js"></script>
		<script src="LibExt/codemirror/mode/sql/sql.js"></script>		

		<script src="LibExt/codemirror/addon/selection/active-line.js"></script>		
		<script src="LibExt/codemirror/addon/selection/mark-selection.js"></script>		
		<script src="LibExt/codemirror/addon/search/jump-to-line.js"></script>		
		
		<script src="LibExt/js-cookie/js.cookie.js"></script>
		<script src="LibExt/jquery/1.10.2/jquery.min.js"></script>
		<script src="LibExt/bootstrap/bootstrap.js"></script>		

		<style>
			.CodeMirror {border-top: 1px solid silver; border-bottom: 1px solid silver;border-left:1px solid silver;height:75%;}
			
			.CodeMirror-selected  { background-color: blue !important; }
			.CodeMirror-selectedtext { color: white !important; }

			.styled-background-x { background-color:transparent; }				
			.styled-background-mbo { background-color: blue; }			
			.styled-background-neat { background-color: yellow; }			
			.styled-background-zenburn { background-color: blue; }						
			.styled-background-eclipse { background-color: yellow; }	
			
			.btn{border-radius:0px;}
			.form-control{border-radius:0px;}
			.modal-content{border-radius:0px;}
			body{font-family:monospace;}
		</style>	
		
    </head>
    <body>
		<?php if ( $userLog == true ) { ?>
			<form action="" method="post" id="form">
				<div class="container-fluid" style="margin-top:2px;">
					<input 	type="text" name="file"  id="inputPath"	value="<?=stripslashes($file)?>"  class="form-control input-sm" placeholder="<?=$lang["placeholder"];?>"  />			 				
					<div class="form-group pull-left"  style="margin-top:2px;">
						<div class="btn-group" role="group" aria-label="...">
							<input	type="submit" name="getFile" value="<?=$lang["get"];?>" onclick="getDoc();" data-toggle="modal" data-target="#myModal" class="btn btn-sm btn-default active" />			 				
							<input	type="submit" name="save" id="btnSave" value="<?=$lang["save"];?>"  onclick="saveDoc();"  data-toggle="modal" data-target="#myModal" class="btn btn-sm btn-default active" /> 							
							<input	type="submit" value="<?=$lang["refresh"];?>" onclick="refreshDoc();" data-toggle="modal" data-target="#myModal" class="btn btn-sm btn-default active"  />									
							<a href="" class="btn btn-sm btn-default active" onclick="replacePathInit(); return false;"  id="btnBackup" style="display:none;"><?=$lang["restore"];?></a>
							<a href="index.php" class="btn btn-sm btn-default active" id="btnClear"><?=$lang["clear"];?></a>
						</div>
					</div>		
					<div class="pull-right"  style="margin-top:2px;">
						<a class="btn btn-xs btn-default" href="#" onclick="loadHistory();" style="border:0px;"><?=$lang["history"];?></a>					
						<a class="btn btn-xs btn-default" href="#" onclick="loadBackups();" style="border:0px;"><?=$lang["backups"];?>: <?=$countBackup;?></a>
						<a class="btn btn-xs btn-default" href="#" onclick="return false;" style="border:0px;"><?=$lastUpdateFile;?></a>
						<a class="btn btn-xs btn-default active" href="#" onclick="changeTheme(); return false;" id="btnTheme"></a> 
					</div>
				</div>
				<textarea id="code" name="text" style="width:100%;"><?php echo (htmlspecialchars($text)); ?></textarea>
			</form>

			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-body"></div>
					</div>
				</div>
			</div>
		<?php } else { ?>		
			<div class="container-fluid" style="margin-top:5px;">
				<form method="POST" action="index.php">
					<input type="password" name="pss" class="form-control" style="margin-bottom:5px;" placeholder="password.." />
					Language: <select class="form-control" style="display:inline;width:70px;" name="selLang"><option>EN</option><option>ES</option></select> |
					<input type="submit" class="btn btn-ms btn-success" name="btnLogin" value="LOG" style="background:#a21313;color:white;border:1px solid #a21313;" />
				</form>
			</div>
		<?php } ?>
        <script>
			<?php if ( $userLog == true ) { ?>		
			/*!
			 * myScript
			 */
			var themeIndex 		= 0;
			var themeList 		= Array();
			var fileNotExist	= <?=$fileNotExist;?>;

			$(function(){
				loadPage();
				<?php if ( $isDir ) { ?>
					$('.modal-body').html('<?=$dirList;?><div class="text-right"><button type="button" class="btn btn-danger" data-dismiss="modal"><?=$lang["close"];?></button></div>');
					$('#myModal').modal('show');
				<?php } ?>
			});

  	        var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
	            lineNumbers: true,
	            styleActiveLine: true,
	            matchBrackets: true,
	            mode: "<?=$mode;?>", // "htmlmixed"
	            indentUnit: 4,
	            indentWithTabs: true,
				styleSelectedText: true				
  	        });

			function loadPage() {

				editor.getSelectedRange = function() {  
					return "#" + (editor.getCursor(true).line+1) + "-"+ editor.getCursor(true).ch + "_" + editor.getCursor(false).line + "-"+ editor.getCursor(false).ch;
				};
				
				if ( "<?=$emptyName;?>" == "Yes" ) {
					$('.modal-body').html('<div class="alert alert-danger"><?=$lang["emptyFileName"];?>.</div><div class="text-right"><button type="button" class="btn btn-danger" data-dismiss="modal"><?=$lang["close"];?></button></div>');					
					$('#myModal').modal('show');
				}
				
				if ( fileNotExist && $('#inputPath').val() != "" ) {
					$('.modal-body').html('<div class="alert alert-danger"><?=$lang["notExistFile"];?>.. <b>' + $('#inputPath').val()+'</b></div><div class="text-right"><button type="button" class="btn btn-danger" data-dismiss="modal"><?=$lang["close"];?></button></div>');					
					$('#myModal').modal('show');
				}
				
				if( $("#inputPath").val() != "" ) {
					$("#btnSave").attr("class", $("#btnSave").attr("class").split("disabled").join("") );
					if ( $("#inputPath").val().split(".backup").length > 1 ) {
						$("#btnBackup").show();
						$("#btnSave").hide();
					}
				}

				themeList.push("3024-day");
				themeList.push("3024-night");
				themeList.push("ambiance-mobile");
				themeList.push("ambiance");
				themeList.push("base16-dark");
				themeList.push("base16-light");
				themeList.push("blackboard");
				themeList.push("cobalt");
				themeList.push("eclipse");
				themeList.push("elegant");
				themeList.push("erlang-dark");
				themeList.push("lesser-dark");
				themeList.push("mbo");
				themeList.push("midnight");
				themeList.push("monokai");
				themeList.push("neat");
				themeList.push("night");
				themeList.push("paraiso-dark");
				themeList.push("paraiso-light");
				themeList.push("rubyblue");
				themeList.push("solarized");
				themeList.push("the-matrix");
				themeList.push("tomorrow-night-eighties");
				themeList.push("twilight");
				themeList.push("vibrant-ink");
				themeList.push("xq-dark");
				themeList.push("xq-light");
				themeList.push("zenburn");	

				themeIndex = (Cookies.get('themeIndex') == undefined) ? 0 : Cookies.get('themeIndex');				

				editor.setOption("theme", themeList[themeIndex]);
				$("#btnTheme").html(themeList[themeIndex]);

				var link = document.createElement('link');
				link.setAttribute('rel', 'stylesheet');
				link.setAttribute('type', 'text/css');
				link.setAttribute('href', 'LibExt/codemirror/theme/' + themeList[themeIndex] + '.css');
				document.getElementsByTagName('head')[0].appendChild(link);

				setTimeout(function(){loadMarketSelected();},500);

				var pather = ( ( Cookies.get('pathsFileName') == undefined ) ? '' : Cookies.get('pathsFileName') );
				Cookies.set('pathsFileName',  pather + ';<?=$file;?>');
			}
			
			function changeTheme() {
				themeIndex++;
				if(themeIndex == themeList.length)
					themeIndex = 0;				
				Cookies.set('themeIndex', themeIndex);					
				$('#myModal').modal('show');
				$('.modal-body').html('<?=$lang[ "changeTheme" ];?>.. <b>' + themeList[themeIndex] + '</b>' );
				$("#form").submit();
			}
			
			function loadMarketSelected() {
				if(window.location.hash) {
					if ( window.location.hash.split('#to-').length > 1 ) {
						editor.setCursor(parseInt(window.location.hash.split('#to-')[1])-1);
					}
					else {
						var arr = window.location.hash.split('#')[1].split("_");
						var pt1 = arr[0].split("-");
						var pt2 = arr[1].split("-");		
						editor.markText( 
							 {line: parseInt(pt1[0])-1, ch: parseInt(pt1[1])}
							,{line: parseInt(pt2[0]), ch: parseInt(pt2[1])}
							,{className: "styled-background-" + themeList[themeIndex], readOnly: true }
						);
						editor.setCursor({line: parseInt(pt1[0]), ch: parseInt(pt1[1])});
					}
				}
			}
			
			function replacePathInit() {
				var path = $("#inputPath").val();
				path = path.replace("-"+path.split("-")[1],"");
				$("#inputPath").val(path);
				$("#btnSave").click();
			}

			function refreshDoc() {
				$('.modal-body').html('<?=$lang["reload"];?>.. <b>' + $('#inputPath').val()+'</b>');
			}
			
			function saveDoc() {
				$('.modal-body').html('<?=$lang["saving"];?>.. <b>' + $('#inputPath').val()+'</b>');
				$("#form").attr( "action" , "#to-" + parseInt(editor.getCursor().line + 1) );				
			}
			
			function getDoc() {
				$('.modal-body').html('<?=$lang["getting"];?>.. <b>' + $('#inputPath').val()+'</b>');
				$("#form").attr( "action" , editor.getSelectedRange() );				
			}
			
			function loadBackups() {
				$('.modal-body').html('<?=$filesNames;?><div class="text-right"><button type="button" class="btn btn-danger" data-dismiss="modal"><?=$lang["close"];?></button></div>');
				$('#myModal').modal('show');
			}
			
			function loadHistory() {
				var historyFiles = Cookies.get('pathsFileName').split(";");
				var htmlHistory = "";
				var distincthistoryFiles = [];
				$.each(historyFiles, function(i, el){ if($.inArray(el, distincthistoryFiles) === -1) distincthistoryFiles.push(el); });				
				for( i = 1; i < distincthistoryFiles.length; i++ ) {
					htmlHistory += "- <a href='index.php?file=" + distincthistoryFiles[i] + "'>" + distincthistoryFiles[i] + "</a><br/>";
				}
				$('.modal-body').html( htmlHistory + '<div class="text-right"><button type="button" class="btn btn-danger" data-dismiss="modal"><?=$lang["close"];?></button></div>');
				$('#myModal').modal('show');				
			}

			$(document).keydown(function(e) {
				 //alert(e.keyCode);
				if ( e.altKey && e.keyCode == 83 ) { // alt + s
					if ( $("#btnSave").is(":visible") )
						$("#btnSave").click();
					else					
						$("#btnBackup").click();
				}
				if ( e.altKey && e.keyCode == 70 ) { // alt + f
					$("#inputPath").focus();
					$("#inputPath").select();					
				}	
				if ( e.altKey && e.keyCode == 73 ) { // alt + i
					loadHistory();
				}
				if ( e.altKey && e.keyCode == 66 ) { // alt + b
					loadBackups();
				}
				if ( e.altKey && e.keyCode == 75 ) { // alt + k
					changeTheme();
				}								
			});
			<?php } ?>		
        </script>
		<div class="container-fluid">
			<?php if ( $userLog == true ) { ?>				
			<small>
				<tt>
					<a href="#" class="btn btn-xs btn-default active disabled"><?=$lang["save"];?> or <?=$lang["restore"];?></a> = (alt + s) |
					<a href="#" class="btn btn-xs btn-default active disabled"><?=$lang["focus"];?> Input</a> = (alt + f) |
					<a href="#" class="btn btn-xs btn-default active disabled"><?=$lang["history"];?></a> = (alt + i) |
					<a href="#" class="btn btn-xs btn-default active disabled"><?=$lang["backups"];?></a> = (alt + b) |
					<a href="#" class="btn btn-xs btn-default active disabled"><?=$lang["theme"];?></a> = (alt + k) 										
				</tt>
			</small>
			<?php } ?>			
			<div class="footer text-right text-muted">
				<small><a href="http://github.com/republicdev/<?=$projectName;?>" target="_blank"><?=$projectName;?></a> - VerDate 1407.017</small>
				<?php if ( $userLog == true ) { ?>					
					| <small><a href="?logout" id="btnLogout"><?=$lang["logout"];?></a></small>				
				<?php } ?>							
			</div>
		</div>
    </body>
</html>
