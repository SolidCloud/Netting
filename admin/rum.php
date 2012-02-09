<?php
	session_start();
	function translate($string) {
		$search  = array( "\\t",    "\\n",    "\\r",   " ");
		$replace = array( "&#09;",  "<br/>",  "<br/>", "&#32;");
		return str_replace($search, $replace, $string);
	}
	require_once "auth.php";
	require_login();
	$currentRum=$_GET["rum"];
	if(str_replace(" ","_",$_SESSION["user"])!=$currentRum && $_SESSION["user"]!="admin"){
		include_once("_adminclude.php");
		exit("<div style='
			background-color:#9f9;
			background-color:rgba(200, 148, 225,0.6);
			border-radius:7px;
			padding:20px;
		' >Du har inte tillräcklig behörighet för att redigera denna sidan!<br/>
		<a href=\"rum.php?rum=". str_replace(" ","_",$_SESSION["user"])."\">Gå tillbaka till din sida.</a><img src='../img/premission-denied.png' style='float:right;width:200px;' /></div>");
	}
	
	if($_POST["nyheter"]=="nyhet"){
		$path="../rum/".$currentRum."/data.xml";
		$sim=simplexml_load_file($path);
		
		for($i=0;$i < count($_POST["nyhet"]) || $i < count($sim->nyheter->nyhet) ;$i++){
			if($i < count($_POST["nyhet"]))
				$sim->nyheter->nyhet[$i]=$_POST["nyhet"][$i];
			else 
				unset($sim->nyheter->nyhet[$i]);
		}
		fwrite(fopen($path,"w"),$sim->asXML());
	} 
	if($_POST["kalender"]=="date"){
		$path="../rum/".$currentRum."/data.xml";
		$sim=simplexml_load_file($path);
		for($i=0;$i < count($_POST["date"]) || $i < count($sim->kalender->date) ;$i++){
			if($i < count($_POST["date"]))
				$sim->kalender->date[$i]=$_POST["date"][$i];
			else 
				unset($sim->kalender->date[$i]);
		}
		fwrite(fopen($path,"w"),$sim->asXML());
	}
	if(isset($_POST["kontakt"])){ //redigera kontakter
		$path="../rum/".$currentRum."/data.xml";
		$sim=simplexml_load_file($path);
		if($_POST["kontakt"]=="new"){
			$sim->kontakter->addChild("kontakt");
			$i=count($sim->kontakter->kontakt) - 1;
			$sim->kontakter->kontakt[$i]->namn = $_POST["namn"];
			$sim->kontakter->kontakt[$i]->email = $_POST["mail"];
			$sim->kontakter->kontakt[$i]->img = $_POST["image-kont"];
		}
		else if( strpos( $_POST["kontakt"], "rase" ) !== false ) {
			$i = (int)substr( $_POST["kontakt"], 4);
			unset($sim->kontakter->kontakt[$i]);
		}
		else if( strpos( $_POST["kontakt"], "edit" ) !== false) {
			$i = (int)substr( $_POST["kontakt"], 4);

			$sim->kontakter->kontakt[$i]->namn = $_POST["namn"];
			$sim->kontakter->kontakt[$i]->email = $_POST["mail"];
			$sim->kontakter->kontakt[$i]->img = $_POST["image-kont"];
		}
		fwrite(fopen($path,"w"),$sim->asXML());
	}

	if ($_GET["rum"] && file_exists('../rum/'.$currentRum.'/data.xml')){
		$simpleData=simplexml_load_file('../rum/'.$currentRum.'/data.xml');
		$SimpleDesc=$simpleData->inledning;
		$SimpleNyh=$simpleData->nyheter->nyhet;
		$SimpleCal=$simpleData->kalender->date;
		$SimpleCont=$simpleData->kontakter;
	}
	else {
		header("Location: 404.php");
		exit("Sidan finns inte!");
	}
	include("upload.php");
	
	function force_rmdir($path) {
		if (!file_exists($path)) return false;
		if (is_file($path) || is_link($path)) {
			return unlink($path);
		}
		if (is_dir($path)) {
			closedir(opendir($path));
			$result = true;
			$dir = new DirectoryIterator($path);
			foreach ($dir as $file) {
				if (!$file->isDot()) {
					$result &= force_rmdir($path . $file->getFilename(), false, $sizeErased);
				}
			}
			$result &= rmdir($path);
			return $result;
		}
	}

	function addTextTab($rum,$name){
		mkdir("../rum/".$rum."/text_".$name);
		fwrite(fopen("../rum/".$rum."/text_".$name."/data.txt","w"), "");
	}
	function addGalleryTab($rum,$name){
		mkdir("../rum/".$rum."/galleri_".$name);
		fwrite(fopen("../rum/".$rum."/galleri_".$name."/data.xml","w"), '<?xml version="1.0" encoding="utf-8"?><galleri></galleri>');
	}
	if(($imgbesk = $_POST["imgbesk"]) && ($imgnamn = $_POST["imgnamn"])){
		if($_FILES["imgfiles"]) {
			$sim= simplexml_load_file("../rum/".$_GET["rum"]."/".$_POST["tab_title"]."/data.xml");
			
			for($i=0; $i<count($_FILES["imgfiles"]["name"])||$i<count($sim->media); $i++){
				$path = "../rum/".$_GET["rum"]."/".$_POST["tab_title"]."/";
				if(count($sim->media)>$i&&$_POST["src"][$i]!="new"){
					$sim->media[$i]->namn=$_POST["imgnamn"][$i];
					$sim->media[$i]->beskrivning=$_POST["imgbesk"][$i];
					$sim->media[$i]->attributes()->type=$_POST["type"][$i];
					$nlm=str_replace( $path, "", $_POST["src"][$i] );
					if($_POST["type"][$i]=="audio"||$_POST["type"][$i]=="video")
						$pos=strrpos( $nlm, ".", -1 );
					else $pos=false;
					if($pos!==false)
						$sim->media[$i]->src=substr( $nlm, 0, $pos );
					else
						$sim->media[$i]->src=$nlm;
				}
				else {
					$sim->addChild("media");
					$type = explode("/",$_FILES["imgfiles"]["type"][$i]);
					$sim->media[$i]->addAttribute("type",$type[0]);
					$sim->media[$i]->addChild("namn",$_POST["imgnamn"][$i]);
					$sim->media[$i]->addChild("src","");		
					$sim->media[$i]->addChild("beskrivning",$_POST["imgbesk"][$i]);
				}	
				if($i<count($_FILES["imgfiles"]["name"]) && $_FILES["imgfiles"]["name"][$i] !== ""){
					if(!$_FILES["imgfiles"]["name"][$i] || $path.$_FILES["imgfiles"]["name"][$i]==$_POST["src"][$i] || 
								is_file($path.$_FILES["imgfiles"]["name"][$i]))
						continue; 
					
					if($string[count($string)-1]=="\\")
					if($_POST["src"][$i][count($_POST["src"][$i])-1]!="/"
							&& $_POST["src"][$i][count($_POST["src"][$i])-1]!="\\")
						unlink($_POST["src"][$i]);
					$path = $path.safePath($_FILES["imgfiles"]["name"][$i]);
					if(($fileError = $_FILES["imgfiles"]["error"][$i]) > 0)
						echo $fileError;
					if (strpos($_FILES["imgfiles"]["type"][$i],"image")!==false){
						$file = processImage($_FILES["imgfiles"]["tmp_name"][$i],420,280);
						$path = $path.".png";
					} 
					else {
						$file = $_FILES["imgfiles"]["tmp_name"][$i];
						$path=$path.".".pathinfo($_FILES["imgfiles"]["name"][$i], PATHINFO_EXTENSION);
					}
					uploadFile($file,$path);
					$sim->media[$i]->src=substr($path,strrpos($path,"/")+1);
				}
				else {
					if (!in_array($path.$sim->media[$i]->src,$_POST["src"])){
						if(is_file($path.$sim->media[$i]->src))
							unlink($path.$sim->media[$i]->src);
						unset($sim->media[$i]);
					}
				}
			}
			$path= "../rum/".$_GET["rum"]."/".$_POST["tab_title"]."/data.xml";
			fwrite(fopen($path,"w"),$sim->asXML());
		}
	}
	else if($_POST["tab_title"]){	//Redigera flik
		if(isset($_POST["index"])){
			if($_POST["tabort_flik"]=="true"){
				if(is_dir("../rum/".$currentRum."/text_".$_POST["tab_title"])){
					$path="../rum/".$currentRum."/text_".$_POST["tab_title"];
					closedir(opendir($path));
					force_rmdir($path."/");
				}
				else if(is_dir("../rum/".$currentRum."/galleri_".$_POST["tab_title"])){
					$path="../rum/".$currentRum."/galleri_".$_POST["tab_title"];
					closedir(opendir($path));
					force_rmdir($path."/");
				}
				else {
					echo "Post".rmdir("../rum/".$currentRum."/galleri_".$_POST["tab_title"]);
					exit("<br/>Något fel inträffade, kontakta gärna SolidCloud för förbättring av hemsidan!");
				}
			}
			if($_POST["index"]=="-1"){
				if($_POST["tab_type"]=="text")
					addTextTab($currentRum, $_POST["tab_title"]);
				else if($_POST["tab_type"]=="galleri")
					addGalleryTab($currentRum,$_POST["tab_title"]);
			}
			else if(is_file('../rum/'.$currentRum.'/galleri_'.$_POST["tab_title_prev"]."/data.xml")){
				if($_POST["tab_title_prev"]!=$_POST["tab_title"])
					rename ('../rum/'.$currentRum.'/galleri_'.$_POST["tab_title_prev"],'../rum/'.$currentRum.'/galleri_'.$_POST["tab_title"]);
			}
			else if(is_file('../rum/'.$currentRum.'/text_'.$_POST["tab_title_prev"]."/data.txt"))
				if($_POST["tab_title_prev"]!=$_POST["tab_title"])
					rename ('../rum/'.$currentRum.'/text_'.$_POST["tab_title_prev"],'../rum/'.$currentRum.'/text_'.$_POST["tab_title"]);
		}
	}
	else if($_POST["besk_textA"]) {
		$path="../rum/".$_GET["rum"]."/".$_POST["rum_cur"]."/data.txt";
		if(is_file($path)){
			fwrite(fopen($path,"w"),$_POST["besk_textA"]);
		}
	}
	$rum=array();
	$Rubrum=array();
	$index=0;
	if($handle = opendir('../rum/'.$currentRum)){
		$tabs = array();
		while ($file = readdir($handle))
			if (strpos($file,"galleri_")!==false)
				$tabs[] = str_replace("galleri_","",$file)."_galleri";
			else if (strpos($file,"text_")!==false)
				$tabs[] = str_replace("text_","",$file)."_text";
		natsort($tabs);
		for ($i = 0; $i < count($tabs); $i++){
			$part = explode("_",$tabs[$i]);
			$tabs[$i] = $part[count($part)-1]."_";
			unset($part[count($part)-1]);
			$tabs[$i] = $tabs[$i].implode("_",$part);
		}
		foreach ($tabs as $file){
			$rum[$index]=null;
			$Rubrum[$index]=null;
			if(strpos($file,"galleri_")!==false){
				$Rubrum[$index]=$file;
				if (file_exists("../rum/$currentRum/$file/data.xml"))
					$rum[$index]=simplexml_load_file("../rum/$currentRum/$file/data.xml");
			}
			else if(strpos($file,"text_")!==false){
				$Rubrum[$index]=$file;
				$rum[$index]=file_get_contents("../rum/$currentRum/$file/data.txt");
			}
			$index++;
		}
	}
	if($_POST["rum"] && $_POST["access"]){
		addPass($_POST["rum"],$_POST["access"]);
		createRoom($_POST["rum"]);
	}
	function changePass($room,$newpass){
		$passfile = fread(fopen("pass.php","r"),filesize("pass.php"));
		$startpos = strpos($passfile,"\"".$room."\"=>")+strlen($room)+5;
		$slutpos = strpos($passfile,"\"",$startpos);
		$newpass = substr($passfile,0,$startpos).$newpass.substr($passfile,$slutpos);
		fwrite(fopen("pass.php","w"),$newpass);
	}
	function deletePass($room){
		$passfile = fread(fopen("pass.php","r"),filesize("pass.php"));
		$passfile = substr($passfile,25,strlen($passfile)-29);
		if(!($indexes = explode(",",$passfile)))
			$indexes = array($passfile);
		for ($i=0;$i<count($indexes);$i++){
			if (strpos($indexes[$i],"\"".$room."\"=>")!==FALSE)
				unset($indexes[$i]);
		}
		fwrite(fopen("pass.php","w"),'<?php $roomusers = array('.join(",",array_values($indexes)).");?>");
	}
	function addPass($room,$pass){
		$passfile = fread(fopen("pass.php","r"),filesize("pass.php"));
		$passfile = substr($passfile,25,strlen($passfile)-29);
		if(!($indexes = explode(",",$passfile)))
			$indexes = array($passfile);
		$indexes[] = "\"".$room."\"=>\"".$pass."\"";
		fwrite(fopen("pass.php","w"),'<?php $roomusers = array('.join(",",$indexes).");?>");
	}
	function createRoom($name){
		if(is_dir("../rum/".$name))
			return "Finns redan ett rum med samma namn!";
		mkdir("../rum/".$name);
		fwrite(fopen("../rum/".$name."/data.xml","w"),'<?xml version="1.0" encoding="utf-8"?><hus></hus>');
		header("Location: rum.php?rum=".$name);
	}
	include_once "_adminclude.php";
?>
<style type="text/css" media="screen">
#sortable {
	list-style-type: none;
}
#sortable li {
	height: auto;
	display: block;
}
#sortable li div span.ui-icon {
	border: 1px solid #8C58BE;
	background-color: #E6B2FF;
	margin-left: 5px;
}
#sortable li div span.ui-icon.ui-icon-closethick {
	cursor: pointer;
}
#sortable li div span.ui-icon.ui-icon-arrow-4-diag {
	cursor: move;
}
.sortable-placeholder {
	border: 3px dashed #ccc;
	background: #e6e6e6;
	height: 200px;
	margin-bottom: 15px;
}
#beskrivning, #list-rum {
	color:#000;
}
#beskrivning {
	overflow:hidden;
}
#list-rum {
	width:475px;
	margin-left:5px;
}
#list-rum a {
	width:475px;
	text-transform:capitalize;
	margin-bottom: 5px;
}
#tabs li {
	/*height:33px;*/
}
#dialog-tab input,#dialog-tab textarea {
	margin:0px;
}
#dialog-kontakt input,#dialog-kontakt textarea {
	margin:0px;
}
#accordion input{
	width:100px;
}
#kont {padding: 6px;}
</style>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function(){
	$('#edit-tab').dialog({
			width:"auto",
			resizable: false,
			autoOpen: false,
			modal: true,
			open:function(event){
				$('#tabs').tabs('option','disable',$('#tabs li').index($('#tabs').find('.ui-state-active')));
				var clicked=$(".ui-state-hover");
				if(clicked.find("a").hasClass('add')){
					$('#edit-tab').parent().find('div .ui-dialog-title').html('Lägg till flik');
					$(event.target).next().find("button")[0].style.visibility="hidden";
					$(event.target).removeClass('add');
					$(event.target).next().find("button")[1].getElementsByTagName('span')[0].innerHTML="Lägg till";
					$("#tab_title").val("");
					$("#lable_tab_title").html("Den nya Flikens namn: ");
					$("#input_tab_title").value("-1");
				}
				else{
					$('#edit-tab').parent().find('div .ui-dialog-title').html('Redigera Flik');
					$('#tabs').tabs('select',clicked.parent().find("li").index(clicked));//aktiva är alltid den man redigerar
					$('#tab_title').val($("#tabs .ui-tabs-selected a").html());
					$('#tab_title_prev').val($("#tabs .ui-tabs-selected a").html());
					/* Det var denna if-satsen???
					if($($("#tabs").children()[clicked.parent().find("li").index(clicked)+1]).hasClass('editable')){
						$(event.target).next().find("button")[0].style.visibility="visible";
						$(event.target).next().find("button")[1].getElementsByTagName('span')[0].innerHTML="Spara";
						$("#tab_title")[0].value=clicked.find("a").html().replace(/\t/gi,"");
					}*/
					var index=clicked.parent().find("li").index(clicked);
					$("#lable_tab_title").html("Flikens namn: ");
					$("#input_tab_title")[0].value=index;
				}
			},
			buttons: {
				'Ta bort flik': function() {
					$('#tabort_flik').val("true");
					$("#input_tab_title").val("-2");
					$('#tab_title').val($("#tabs .ui-tabs-selected a").html());
					$(this).dialog("close");
					$('#tab-form').submit();
				},
				Spara: function(e,u) {
					error = $('#tab_title');
					var l=$('#tabs').tabs("widget").find(".ui-tabs-nav li a");
					for(var i=0;i<$('#tabs').tabs("length");i++){
						if(l[i].innerHTML==error.val()&&$(l[i]).attr("href").split("_")[0].value==$("tab_type").val()) {
							l=false;
							$("#error").css("display","block");
							return;
						}
					}
					if ((error = $('#tab_title')).val()!="" && error.val()!="_"){
						$(this).dialog("close");
						$('#tab-form').submit();
					} else {
						error.addClass("error");
						error.attr("placeholder","Du måste ange ett namn på fliken");
					}
				},
				Avbryt: function() {
					$("#error").css("display","none");
					$(this).dialog("close");
					$('#tab-form').reset();
				}
			}
		});
		$("#sortable").sortable({
			placeholder: "sortable-placeholder",
			forcePlaceholderSize: true,
			handle: 'span.ui-icon-arrow-4-diag'
		});
		$('.lagg_till').click(function(e){
			var bot = document.getElementById("bottom_"+this.id);
			var index=$(bot).data('index');
			$($('.imgadd')[this.id]).append($('#media1').html().replace(/\$i/g,index));
			$(bot).data('index',index+1);
			$("#sortable").sortable("refresh");
			scrollTo(0,bot.offsetTop);
		});
		$('input:button').button();
	});
</script>
</head>
<body class="brain">
<!--<div id="media0" style="display:none;">
	<span>
		Media:<br/>
		<img src="" /><br/>
		<input type="button" id="remove-media" value="Ta bort"/>
	</span>
	<span>
		<label for="name">Titel på bilden</label>
		<input id="name" name="image_title[]" type="text" /><br/>
		<label for="img_desc">Beskrivning:</label><br/>
		<textarea id="img_desc" name="img_desc[]"></textarea>
		<input type="file" name="file[]" /><br/>
	</span>
</div>-->
<div id="media1" style="display:none;">
	<li><div style="border:2px inset #ccc;padding:10px;background:#eee;margin-bottom:15px;">
		<span style='float:right;' onclick='$(this).parent().parent().remove();' class='ui-icon ui-icon-closethick button'></span>
		<span style="float:right;" class="ui-icon ui-icon-arrow-4-diag"></span>
		<input type="hidden" value="new" name="src[]" />
		<input type="hidden" value="undefined" name="type[]" />
		<a href="#" onclick="event.preventDefault();$(this).next().show();$(this).hide();">Ändra?</a>
		<input type="file" style="display:none;" name="imgfiles[]" />
		<br />
		<br />
		<label for="img_namn$i">Rubrik:</label>
		<br />
		<input id="img_namn$i" type="text" name="imgnamn[]" value="" />
		<br />
		<br />
		<div class="imgbesk">
			<label for="img_besk$i">Beskrivning:</label>
			<br />
			<textarea name="imgbesk[]" id="img_besk$i" style="width:100%;height:100px;"></textarea>
		</div>
	</div></li>
</div>
<!--[BEGIN edit-tab]--> 
<div id="edit-tab" style="display:none;" title="Redigera Flik">
	<form id="tab-form" action="<?php echo $_SERVER['PHP_SELF'].'?rum='.$_GET['rum']; ?>" method="post" enctype="multipart/form-data">
		<fieldset class="ui-helper-reset">
			<input type="hidden" name="tabort_flik" id="tabort_flik" value="false" />
			<input type="hidden" name="tab_title_prev" id="tab_title_prev" value="" />
			<input type="hidden" name="rum" value="<?php echo ($_GET['rum'])?$_GET['rum']:'yagil'; ?>">
			
			<label for="tab_title" id="lable_tab_title"  >Fliken $1's namn:</label>
			<input style="display:none;" value="-1" name="index" id="input_tab_title" />
			<input type="text" name="tab_title" id="tab_title" />
			
			<label for="tab_type">Typ:</label>
			<select id="tab_type" name="tab_type">
				<option value="text">Text</option>
				<option value="galleri">Galleri</option>
			</select>
			<div id="error" style="display:none;" >
				Fliken kan inte ha samma namn som en annan flik.
			</div>
			
		</fieldset>
	</form>
</div>
<!--[END edit-tab]-->
<div id="dialog-kontakt" style="display:none;" title="Kontakt data">
	<form id="kontakt-form" action="<?php echo $_SERVER['PHP_SELF'].'?rum='.$_GET['rum']; ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="kontakt" class="kont" value="new" />
		<fieldset class="ui-helper-reset">
			<p>
				<label for="namn">Namn</label><br/>
				<input type="text" name="namn" id="namn" value="" class="ui-widget-content ui-corner-all" />
			</p>
			<p>
				<label for="image" >Länka till en bild</label><br/>
				<input type="text" id="image-kont" name="image-kont" class="ui-widget-content ui-corner-all" /><br/>
			</p>
			<p>
				<label for="mail">E-mail</label><br/>
				<input type="text" name="mail" id="mail" value="" class="ui-widget-content ui-corner-all" />
			</p>
		</fieldset>
	</form>
</div>
<?php if(!isset($_POST["data"])){?>
<?php }?>
<div id="mainheader">
	<span>
		<h1 id="Namn" ><?php if($simpleData->Namn)echo $simpleData->Namn; else echo "Vägkorset";?></h1>
	</span>
	<button title="Logga ut från <?php echo $_SESSION['user']; ?>" >Logga ut</button>
</div>
<div id="bread"><a href="index.php"><?php echo "Hem";?></a> &#187; <?php echo str_replace("_"," ",$_GET["rum"]); ?></div>
<div id="container">
	<h2 id="navigation"><?php echo str_replace("_"," ",$_GET["rum"]); ?></h2>
	<table>
		<tr>
			<td valign="top">
				<div id="tabs">
		<ul> 
			<?php 
				for($index=0;$index<count($Rubrum);$index++){
			?>
							<li><a href="#<?php echo str_replace(" ","_", $Rubrum[$index]); ?>"><?php echo substr($Rubrum[$index],strpos($Rubrum[$index],"_")+1); ?></a><span class="ui-icon ui-icon-tag" style="float:left;" title="Redigera" onclick="$('#edit-tab').dialog('open');"></span></li>
			<?php } ?>
			<li><a href="#add" class="add" onclick="$('#edit-tab').toggleClass('add');$('#edit-tab').dialog( 'open' );" >+</a></li>

		</ul>
		<?php 
			for($index=0;$index<count($Rubrum);$index++){
				if($rum[$index]!=null&&!is_string($rum[$index])){
					$media = array();
					for($i=0;$i<count($rum[$index]->media);$i++)
						$media[] = array(
							"src"=>"../rum/$currentRum/".$Rubrum[$index]."/".$rum[$index]->media[$i]->src,
							"namn"=>$rum[$index]->media[$i]->namn,
							"besk"=>$rum[$index]->media[$i]->beskrivning,
							"type"=>$rum[$index]->media[$i]->attributes()->type
						);
		?>
		<div id="<?php echo $Rubrum[$index]; ?>">
			<div class="main-view" id="main-view_<?php echo $Rubrum[$index]; ?>">
				<div class="images" id="images_<?php echo $Rubrum[$index]; ?>">
				<form  action="<?php echo $_SERVER["PHP_SELF"]."?rum=".$_GET['rum']; ?>" method="post" enctype="multipart/form-data">
				<input type="hidden" name="tab_title" value="<?php echo $Rubrum[$index]; ?>" />
				<div style="padding:8px;">
					<input type="button" class="button lagg_till" id="<?php echo ''.$index;?>" value="Lägg till" />
					<input type="submit" class="button" value="Verkställ" />
				</div>
				<div class="imgadd">
				<ul id="sortable">
				<?php
				$i = 0;
				if (count($media) > 0)
					foreach ($media as $m){
						echo "<li><div style='border:2px inset #ccc;padding:10px;background:#eee;margin-bottom:15px;' >
									<span style='float:right;' onclick='$(this).parent().parent().remove();' class='ui-icon ui-icon-closethick'></span>
		<span style='float:right;' class='ui-icon ui-icon-arrow-4-diag'></span>";
							echo "<div style='float:right;margin-right:60px;'>";
						if($m["type"] !="image"&&$m["type"] != "application"){
							echo "Video info:<br/>";
							if ($m["type"] == "video"){
								if (file_exists($m["src"].".mp4")||file_exists($m["src"].".ogv"))
									echo "IE9, Chrome6, Safari5<br/>";
							}
							else if($m["type"]=="audio"){
								if (file_exists($m["src"].".mp3"))
									echo "IE9, Chrome6, Safari5<br/>";
								if (file_exists($m["src"].".ogg"))
									echo "Firefox4.0, Chrome6, Opera10.6<br/>";
							}
							echo "<br/><a href='http://www.w3schools.com/html5/html5_video.asp' >mer info</a>";
						}
						else {
							if($m["type"] =="image")
								echo "Mediatyp: Bild";
							else echo "Mediatyp: Fil";
						}
						echo "</div>";
						if($m["type"] == "image")
							echo "<img src='".$m["src"]."' onclick='$(this).next().click();' height='200'/>";
						else if ($m["type"] == "video"){
							if (file_exists($m["src"].".mp4"))
								echo "<a href='".$m["src"].".mp4'>".$rum[$index]->media[$i]->src.".mp4</a><br/>";
							if (file_exists($m["src"].".ogv"))
								echo "<a href='".$m["src"].".ogv'>".$rum[$index]->media[$i]->src.".ogv</a><br/><br/>";
						}
						else if ($m["type"] == "audio"){
							if (file_exists($m["src"].".mp3"))
								echo "<a href='".$m["src"].".mp3'>".$rum[$index]->media[$i]->src.".mp3</a><br/>";
							if (file_exists($m["src"].".ogg"))
								echo "<a href='".$m["src"].".ogg'>".$rum[$index]->media[$i]->src.".ogg</a><br/><br/>";
						}
						else 
							echo "<a href='".$m["src"]."' onclick='$(this).next().click();' >".$rum[$index]->media[$i]->src."</a><br/><br/>";
						echo "<input type='hidden' value='".$m["src"]."' name='src[]' />
								<input type='hidden' value='".$m["type"]."' name='type[]' />
								<a href='#' onclick='event.preventDefault();$(this).next().show();$(this).hide();'>Ändra?</a>
								<input type='file' style='display:none;' name='imgfiles[]' />
								<br/>
								<br/>
								<label for='img_namn$i' >
									Rubrik:
								</label>
								<br/>
								<input id='img_namn$i' type='text' name='imgnamn[]' value='".$m["namn"]."' />
								<br/>
								<br/>
								<div class='imgbesk'>
									<label for='img_besk$i' >
										Beskrivning:
									</label>
									<br/>
									<textarea name='imgbesk[]' id='img_besk$i' style='width:100%;height:100px;'>".$m["besk"]."</textarea>
								</div>
							</div></li>";
						$i++;
					}
				else {
					echo '<span style="display:block;margin-top:20px;color:black;font-size: 20px;">Inga bilder i galleriet</span>';
				}
				?>
				</ul>
				</div>
				<div style="padding:8px;" >
					<span id="<?php echo ''.$index;?>" class="button lagg_till">Lägg till</span>
					<input type="submit" class="button" value="Verkställ" />
				</div>
				<a id="bottom_<?php echo $index;?>" name="bottom_<?php echo $index;?>" data-index="<?php echo count($media); ?>" style="visibility:hidden;"></a>
				</form></div>
				</div>
		</div>
		<?php 
						}
						else{
							?>
								<div id="<?php echo str_replace(" ","_", $Rubrum[$index]); ?>">
								<div class="imgadd" style="display:none;"></div>
								<form id="form-besk_textA" method="post" action="<?php echo $_SERVER["PHP_SELF"]."?rum=".$_GET['rum']; ?>" >
									<input type="hidden" name="rum_cur" value="<?php echo $Rubrum[$index]; ?>" />
									<textarea name="besk_textA" id="besk_textA" style="width:100%;height:200px;" ><?php if($rum[$index]!=null) echo $rum[$index];?></textarea>
									<br/>
									<input type="submit" class="button" style="width:100%;" value="Spara" />
								</form>
								</div>
							<?php
						}
					}
		?>
	</div>
				<br style="clear:both;" />
			</td>
			<td valign=top >
				<div id="accordion">
					<div>
						<h3><a href="#">Nyheter</a><div class="ui-icon ui-icon-tag" style="float:right;position:relative;left:-10px;top:-15px;" title="Redigera" onclick="$('.nuhet').css('display','inline-block');$('.del').css('display','block');change('nyheter');" ></div></h3>
						<div style="overflow:hidden;" >

							<form id="form-nyheter" action="<?php echo $_SERVER["PHP_SELF"].'?rum='.$_GET['rum']; ?>" method="post">
								<input type="hidden" name="nyheter" value="nyhet" />
							<?php
								echo '<ul class="nyheter">'; 
								if(count($SimpleNyh) > 0){ 
									if(isset($_POST["data"])){
										for($i=0;$i<count($SimpleNyh);$i++){
											echo '<li style="padding:10px 0 10px 0;border-bottom:2px groove #ccc;" >'.$SimpleNyh["nyhet"].' <span>X</span></li>';
										}
									}
									else{ 
										$i=0;
										foreach ($SimpleNyh as $nyhet){
											echo '<li style="padding:10px 0 10px 0;border-bottom:2px groove #ccc;" >'.$nyhet.'</li>';
											$i++;
										} 
									}
								}
								echo '</ul>';
								echo '<button class="button nuhet" onclick="rum.add(\'nyheter\');" style="width:100%;display:none;" type="button" >Lägg till nyhet</button>';
								echo '<br/><br/><input type="submit" class="button nuhet" style="width:100%;display:none" value="Spara" />';
							?>
							</form>
						</div>
					</div>
					<div>
						<h3><a href="#">Kalender</a><div class="ui-icon ui-icon-tag" style="float:right;position:relative;left:-10px;top:-15px;" title="Redigera" onclick="$('.kal').css('display','inline-block');$('.delK').css('display','block');change('kalender');" ></div></h3>
						<div style="overflow:hidden;" >
							<form id="form-kalender" action="<?php echo $_SERVER["PHP_SELF"].'?rum='.$_GET['rum']; ?>" method="post">
								<input type="hidden" name="kalender" value="date" />
							<?php
								echo '<ul class="kalender" >';
								if(count($SimpleCal) >0){
									if(isset($_POST["data"])){
										for($i=0;$i<count($SimpleCal);$i++){
											echo '<li style="padding:10px 0 10px 0;border-bottom:2px groove #ccc;" >'.$SimpleCal["date"].'</li>';
										}
									}
									else{
										foreach ($SimpleCal as $date){
											echo '<li style="padding:10px 0 10px 0;border-bottom:2px groove #ccc;" >'.$date.'</li>';
										}
									}
								}
								echo '</ul>';
								echo '<button class="button kal" onclick="rum.add(\'handelse\');" style="width:100%;display:none;" type="button" >Lägg till händelse</button>';
								echo '<br/><br/><input type="submit" class="button kal" style="width:100%;display:none;" value="Spara" />';
							?>
							</form>
						</div> 
					</div>
					<div>
						<h3><a href="#">Inneboende</a></h3>
						<div style="overflow:hidden;padding:12px 0 12px 0;text-align:center;" >
							<?php
								echo '<div id="kont">';
								if(count($SimpleCont) >0){
									if(isset($_POST["data"])){
										for($i=0;$i<count($SimpleCont);$i++){
											$kontakt=$SimpleCont["kontakt"];
											if ($kontakt["img"]&&$kontakt["img"]!='#')
												echo '<img width="30" align="left" src="'.$kontakt["img"].'" />';
											if ($kontakt["email"])
												echo '<span style="float:left;" ><a href="mailto:'.$kontakt["email"].'">'.$kontakt["email"].'</a></span><br/>';
											if($kontakt->namn)
												echo '<span style="float:left;" >'.$kontakt->namn.'</span>';
										}
									}
									else{
										foreach($SimpleCont->children() as $kontakt){
											echo "<table class='ui-button ui-corner-all' style='width:100%;border:0px solid #000;padding:2px;text-align:left;' onmouseover='this.className=\"ui-button ui-corner-all ui-state-hover\";' onmouseout='this.className=\"ui-button ui-corner-all\";' ><tr>";
											if ($kontakt->img&&$kontakt->img!='#')
												echo '<td><img width="30" align="left" onerror="this.src=\'../img/top_bottom.png\';console.warn(\'Försök att läsa kontakt bild misslyckades!\');return false;" style="margin-right:3px;" src="'.$kontakt->img.'" /></td>';
											else
												echo '<td><img width="30" align="left" style="margin-right:3px;display:inline;" src="../img/top_bottom.png" /></td>';
											echo "<td>";
											if ($kontakt->email)
												echo '<span style="display:inline;" ><a href="javascript:\'mailto:'.$kontakt->email.'\';void(0);">'.$kontakt->email.'</a></span><br/>';
											if($kontakt->namn)
												echo '<span style="display:inline;" >'.$kontakt->namn.'</span>';
											echo "</td></tr></table><p/>";
										}
									}
								}
								else
										echo '<span><a href="#" class="mer">Vi saknar kontaktuppgifterna i det här rummet</a></span>';
							?>
								<a class="button" onclick="kontakterClick();" >Lägg till inneboende</a>
							</div>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table> 
</div>
</body>
</html>