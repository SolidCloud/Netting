<?php
	session_start();
	function translate($string) {
		$search  = array( "\\t",    "\\n",    "\\r",   " ");
		$replace = array( "&#09;",  "<br/>",  "<br/>", "&#32;");
		return str_replace($search, $replace, $string);
	}
	
	include "upload.php";
	require_once "auth.php";
	require_login();
	 
	function rrmdir($path){
		return is_file($path)?@unlink($path):array_map('rrmdir',glob($path.'/*'))==@rmdir($path);
	} 
	if($_SESSION["user"]!="admin"){
		include_once("_adminclude.php");
		exit("<div style='
			background-color:#9f9;
			background-color:rgba(200, 148, 225,0.6);
			border-radius:7px;
			padding:20px;
		' >Du har inte tillräcklig behörighet för att redigera denna sidan!<br/>
		<a href=\"rum.php?rum=". str_replace(" ","_",$_SESSION["user"])."\">Gå tillbaka till din sida.</a><img src='../img/premission-denied.png' style='float:right;width:200px;' /></div>");
	}
	if($_POST["text_content_sida"]){	//Redigera hemsidan
		$sim = simplexml_load_file("../rum/data.xml");
		$sim->tab->desk = "<![CDATA[".$_POST["text_content_sida"]."]]>";
		$ifle=fopen("../rum/data.xml","w") or die("error!");
		fwrite($ifle,$sim->asXML());
		fclose($ifle);
	}
	
	if($_POST["nyheter"]=="nyhet"){
		$path="../rum/data.xml";
		$sim=simplexml_load_file($path);
		unset($sim->nyheter);
		$sim->addChild("nyheter");
		for ($i=0; $i < count($_POST["nyhet"]); $i++) { //$i < count($_POST["nyhet"]) || $i < count($sim->nyheter->nyhet)
			$sim->nyheter->addChild("nyhet",$_POST["nyhet"][$i]);
		}
		fwrite(fopen($path,"w"),$sim->asXML());
	}
	if($_POST["kalender"]=="date"){
		$path="../rum/data.xml";
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
		$path="../rum/data.xml";
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
	
	if($_POST["del_rum"]=="true"){	//delete rum
		if($_POST["cur_rum"]&&$_POST["cur_rum"]!=""&&is_dir("../rum/".str_replace(" ","_",$_POST["cur_rum"]))){
			rrmdir("../rum/".str_replace(" ","_",$_POST["cur_rum"]));
			deletePass($_POST["cur_rum"]);
		}
	}
	else if($_POST["rum"]){			//redigera rum eller lägg till
		$path="../rum/".$_POST["cur_rum"];
		$newPath=str_replace(" ","_","../rum/".$_POST["rum"]);
		if($_POST["cur_rum"]==""){	//Lägga till nytt rum
			mkdir($newPath);
			chmod($newPath, 0777);
			$sim = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rum><inledning>'."<![CDATA[".$_POST["text_content_sida"]."]]>".'</inledning><nyheter></nyheter><kalender></kalender><kontakter></kontakter></rum>');
			$hand=fopen($newPath."/data.xml","w") or die("Hejdå");
			fwrite($hand,$sim->asXML());
			fclose($hand);
			if($_POST["access"]!="password1337")
				addPass($_POST["rum"],$_POST["access"]);
			else addPass($_POST["rum"],"");
		}
		else{		// Ändrar befintligt rum
			if(file_exists(str_replace(" ","_",$path)."/data.xml")){
				$sim = simplexml_load_file(str_replace(" ","_",$path)."/data.xml");
				$sim->inledning=$_POST["text_content"];
				$rand=fopen(str_replace(" ","_",$path)."/data.xml","w") or die ("Hejdå");
				fwrite($rand,$sim->asXML());
				fclose($rand);
				unset($sim);
				rename(str_replace(" ","_",$path), str_replace(" ","_",$newPath));
				
				if($_POST["access"]!="password1337"){
					deletePass($_POST["cur_rum"]);
					addPass($_POST["rum"],$_POST["access"]);
				}
			}
		}
	}
	if(isset($_POST['data'])){
		$simpleData=$_POST['data'];
		$SimpleDesc=$simpleData["inledning"];
		$SimpleNyh=$simpleData["nyheter"]["nyhet"];
		$SimpleCal=$simpleData["kalender"]["date"];
		$SimpleCont=$simpleData["kontakter"];
	}
	else if (file_exists('../rum/data.xml')){
		$simpleData=simplexml_load_file('../rum/data.xml');
		$SimpleDesc=$simpleData->inledning;
		$SimpleDesc = str_replace("]]>","",str_replace("<![CDATA[","",$SimpleDesc));
		
		$SimpleTab=$simpleData->tab;
		
		$SimpleNyh=$simpleData->nyheter->nyhet;
		$SimpleCal=$simpleData->kalender->date;
		$SimpleCont=$simpleData->kontakter;
	}
	else {
		header("Location: 404.php");
		exit("Sidan finns inte!");
	}
	include "_adminclude.php";
	
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
		$exist=false;
		for ($i=0;$i<count($indexes);$i++){
			if (strpos($indexes[$i],"\"".$room."\"=>")!==FALSE){
				unset($indexes[$i]);
				$exist=true;
			}
		}
		if($exist)
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
?>
<script type="text/javascript" charset="utf-8">
	var ar = <?php echo json_encode($roomusers); ?>;
	$(document).ready(function(){
		$('#edit-room').dialog({
			width:"auto" ,
			resizable: false,
			autoOpen: false,
			modal: true,
			open:function(ev){
				if($('#cur_rum').val()!=""){
					var clicked=$(".ui-state-hover");
					var i=clicked.parent().parent().children("span").index(clicked.parent());
					var active=$(clicked.parent().parent().children("a:not(.add)")[i]);
					roomname = active.find(".namn").html();
					$("#room").val(roomname);
					$("#text_content").val(besk[i].replace(/<br>/g,"\n").replace(/<br\/>/g,"\n"));	
					$("#cur_rum").val(roomname);
					$("#cur_rum_vis").val(roomname);
					$("#access").val(ar[roomname]);
					$("#edit-room").next().find(".ui-button")[0].style.display='inline-block';
					$("#edit-room").next().find(".ui-button-text")[1].innerHTML='Spara';
					//$("#access").val(active.find(".access").html());
				}
				else{
					$("#edit-room").next().find(".ui-button")[0].style.display='none';
					$("#edit-room").next().find(".ui-button-text")[1].innerHTML='Lägg till';
					$("#room").val("");
					$("#text_content").val("");	
					$("#cur_rum").val("");
					$("#cur_rum_vis").val("");
				}
			},
			buttons: {
				'Ta bort rum': function() {
					$("#del_rum").val("true");
					$('#room-form').submit();
					$(this).dialog("close");
				},
				Spara: function() {
					error = $('#room');
					if ((error = $('#room')).val()!="" && error.val()!="_"){
						$('#room-form').submit();
						$(this).dialog("close");
					} else {
						error.addClass("error");
						error.attr("placeholder","Du måste ange ett namn på fliken");
					}
				},
				Avbryt: function() {
					$(this).dialog("close");
				}
			}
		});
		$('#edit-desk').dialog({
			width: "auto",
			resizable: false,
			autoOpen: false,
			modal: true,
			open:function(){
				$("#title").val($("#Namn").html());
				$("#text_content_sida").val($(".desk_replace").html().replace(/\n/g,"").replace(/\t/gi,"").replace(/<br>/g,"\n").replace(/<br\/>/g,"\n"));
			},
			buttons: {
				Spara: function() {
					if ((error = $('#title')).val()!="" && error.val()!="_"){
						$('#desk-form').submit();
						$(this).dialog("close");
					} else {
						error.addClass("error");
						error.attr("placeholder","Du måste ange ett namn på hemsidan");
					}
				},
				Avbryt: function() {
					$(this).dialog("close");
				}
			}
		});
		$('input:button').button();
		$('#tab_type').change(function(){
			$('[id^="tab-style-"]').css('display','none');
			$('#tab-style-'+$(this).val()).css('display','block');
		});
		$('#mer-media').click(function(e){
			langd = $('#tab-style-galleri div').length;
			e.preventDefault();
			mall = $("<div />");
			mall.html($('#media0').html());
			mall.attr('id','media'+langd);
			mall.find('#remove-media').click(function(){
				if($('#tab-style-galleri div').length > 1)
					$(this).parent().parent().remove();
			});
			$('#tab-style-galleri section').append(mall);
			$('img').each(function(index){
				if($(this).attr('src')=="")
					$(this).css('display','none');
				else
					$(this).css('display','block');
			});
		});
		$('#tab-style-galleri').css('display','none');
		$('#mer-media').click();

		$(".inneboende:not(.add)").mouseover(function(){
			$(this).css("background-position","0px 220px");
			$(this).parent().find(".desc").css("visibility","visible");
		});
		$(".inneboende:not(.add)").mouseout(function(){
			$(this).css("background-position","0px 0px");
			$(this).parent().find(".desc").css("visibility","hidden");
		});
	});
</script>
<style type="text/css" media="screen">
	#beskrivning, #list-rum {color:#000;}
	#beskrivning {height:150px;overflow-y:auto;}
	#list-rum {width:475px;margin-left:5px;padding-left:15px;}
	#list-rum a {width:475px;margin-bottom: 5px;text-decoration:none;}
	/* Alltså alla olika rummen om någon inte fattar*/
	.inneboende{
		width:220px;
		height:220px;
		background:url("../img/door_icon.png");
		float:left;
		margin:15px 5px 15px 5px;
		border-radius:2px;
		text-align:center;
		position:relative;
		color: #333;
		font-weight: bold;
		overflow: hidden;
	}
	.inneboende.add{
		width:120px;
		height:160px;
		background:rgba(50,50,50,0.7);
		float:left;
		margin:20px;
		border-radius:2px;
		text-align:center;
		padding-top:20px;
	}
	.inneboende img{
		width:200px;
		box-shadow:0 0 2px #000;
		padding:10px;
	}
	.inneboende.add img{
		width:100px;
		box-shadow:0 0 0px #000;
		padding:10px;
	}
	#list-rum a div.desc {
		text-decoration:none;
		width: 220px;
		height: 190px;
		padding-top:30px;
		position:absolute;
		visibility: hidden;
	}
	#list-rum a div div.namn {
		text-decoration:underline;
		text-transform:capitalize;
	}
	#list-rum a div div.besk {
		margin-top:10px;
		text-decoration:none;
	}
	#list-rum .add {
		width:auto;
		padding:30px;
		height:auto;
		background:rgba(200,200,200,0.2);
	}
	#list-rum .add:hover {
		width:auto;
		padding:30px;
		height:auto;
		background:rgba(200,200,200,0.4);
	}
	.left_edit , .right_edit{
		float: left;
		margin-left: -20px;
		position:relative;
		z-index: 2;
		/*background-color: #fed22f;*/
		background-color: #ccc;
		border:1px solid #000;
		border-radius:2px;
	}
	.left_edit{
		left:10px;
		top:15px;
	}
	.right_edit{
		top:265px;
		left:-450px;
	}
	#beskr{
		padding: 0; 
	}
	#accordion input{
		width:100px;
	}
</style>
</head>
<body>
<div id="media0" style="display:none;">
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
</div>
<!--[BEGIN edit-room]-->
<div id="edit-room" style="display:none;" title="Redigera rum">
	<form id="room-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
		<fieldset class="ui-helper-reset">
			<input type="hidden" name="cur_rum" id="cur_rum" value="null">
			<input type="hidden" name="del_rum" id="del_rum" value="">
			<label for="cur_rum">Namn:</label><br/>
			<input type="text" value="" style="width:100%;" name="rum" id="cur_rum_vis" />
			<label for="text_content">Beskrivning:</label><br/>
			<textarea id="text_content" style="height:120px;width:300px;" name="text_content"></textarea>
			<br/>
			<label for="access">Access:</label><br/>
			<input type="text" value="" style="width:100%;" name="access" id="access" />
		</fieldset>
	</form>
</div>
<!--[END edit-room]-->
<div id="edit-desk" style="display:none;" title="Redigera rummet">
	<form id="desk-form" action="<?php echo $_SERVER['PHP_SELF'].'?rum='.$_GET['rum']; ?>" method="post" enctype="multipart/form-data">
		<fieldset class="ui-helper-reset">
			
			<div id="tab-style-text">
				<label for="text_content_sida">Beskrivning av sidan:</label><br/>
				<textarea id="text_content_sida" cols="60" rows="20" name="text_content_sida"></textarea>
			</div>
			
		</fieldset>
	</form>
</div>
<div id="dialog-kontakt" style="display:none;" title="Kontaktdata">
	<form id="kontakt-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
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
<?php if(!isset($_POST["data"])){

?>
<div id="tools" style="display:none;">
	<button class="first" onclick='data.tab_counter=tab_counter;$.post("confirm.php", {"data":data},function(d){if(d=="")alert("Ditt rum är publicerat");})'>Spara</button>
	<button onclick="window.location='./';">Återställ</button>
	<button onclick='//$.post("index.php", {"data":data},function(d){var w=window.open();w.document.writeln(d);});' style="float:right;" >Förhandsgranska(kommer sen)</button>
</div>
<?php }?>
<div id="mainheader">
	<span>
		<h1 id="Namn"><?php if($simpleData->Namn)echo $simpleData->Namn; else echo "Vägkorset";?></h1>
	</span>
	<button title="Logga ut från <?php echo $_SESSION['user']; ?>" >Logga ut</button>
</div>
<div id="bread"><a href="../main.php" ><?php if($simpleData->navigation)echo $simpleData->navigation; else echo "Hem";?></a></div>
<div id="container">
	<h2 id="navigation" ><?php if($simpleData->navigation)echo $simpleData->navigation; else echo "Hem";?></h2>
	<table>
		<tr>
			<td valign=top >
				<div id="besk">
					<div style="float:right;position:relative;top:-20px;left:20px;" >
						<span class="ui-icon ui-icon-tag" style="float:left;" title="Redigera" onclick="$('#edit-desk').dialog('open');"></span>
					</div>
					<?php
						if(count($SimpleTab)>0){
							$i=0;
							foreach($SimpleTab as $tab){
								$desk = $tab->desk;
								$desk = str_replace("]]>","",str_replace("<![CDATA[","",$desk));
								$desk = str_replace("\n","<br/>",$desk);
								?>
								<div class="desk_replace" id="<?php echo $i.$tab->rub; ?>" >
									<?php echo $desk; ?>
								</div>
								<?php
								$i++;
								break;
							}
						}
					?>
				</div>
				<br style="clear:both;" />
				<div id="list-rum">
					<h2>Rum</h2>
					<?php
					if($handle = opendir('../rum/')){
						$index=1;
						echo "<script>var besk=[];</script>";
						$rooms = array();
						while (false !== ($file = readdir($handle)))
							if ($file != "." && $file != ".." && $file != ".DS_Store" && is_dir('../rum/'.$file))
								$rooms[] = $file;
						natsort($rooms);
						foreach($rooms as $file){
							$beskriv=simplexml_load_file("../rum/$file/data.xml")->inledning;
							echo "<script>besk[".($index-1)."]='".str_replace(array( "\r\n", "\n"), "<br/>", str_replace("'","\'",$beskriv ) ) ."';</script>";
							if(strlen($beskriv)>150)
								$beskriv=substr($beskriv,0,150)."<br/>...";
							if(($index % 2) != 0&&$index!=1)
								echo '<span class="right_edit">
									<span class="button ui-icon ui-icon-tag" style="float:left;" title="Redigera" onclick="$(\'#cur_rum\').val(\'null\');$(\'#edit-room\').dialog(\'open\');">
									</span>
								</span>';
							else if(($index % 2) != 0&&$index==1)
								echo '<span class="left_edit">
									<span class="button ui-icon ui-icon-tag" style="float:left;" title="Redigera" onclick="$(\'#cur_rum\').val(\'null\');$(\'#edit-room\').dialog(\'open\');"></span>									
								</span>';
							echo '<a href="rum.php?rum='.$file.'"><div class="inneboende"><div class="desc"><div class="namn" >'.str_replace('_',' ',$file).'</div><div class="besk">'.$beskriv.'</div><input type="hidden" value="'.$access.'" /></div></div></a>';
							if(($index % 2) == 0)
								echo '<span class="left_edit">
									<span class="button ui-icon ui-icon-tag" style="float:left;" title="Redigera" onclick="$(\'#cur_rum\').val(\'null\');$(\'#edit-room\').dialog(\'open\');"></span>									
								</span>';
							
							$index++;
						}
					}
					echo '<a href="javascript:$(\'#cur_rum\').val(\'\');$(\'#edit-room\').dialog(\'open\');void(0);" class="inneboende add">
							<div>
								<img src="../img/plus.png" />
							</div>
						</a>';
					?>
				</div>
			</td>
			<td valign=top >
				<div id="accordion">
					<div>
						<h3><a href="#">Nyheter</a><div class="ui-icon ui-icon-tag" style="float:right;position:relative;left:-10px;top:-15px;" title="Redigera" onclick="$('.nuhet').css('display','inline-block');$('.del').css('display','block');change('nyheter');" ></div></h3>
						<div style="overflow:hidden;" >

							<form id="form-nyheter" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
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
							<form id="form-kalender" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
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
						<div style="overflow:hidden;padding:12px 0 12px 0;" >
							<?php
								echo '<div id="kont" >';
								if(count($SimpleCont) >0){
									if(isset($_POST["data"])){ // om förhandsgranskning skall ske
										for($i=0;$i<count($SimpleCont);$i++){
											$kontakt=$SimpleCont["kontakt"];
											if ($kontakt["img"]&&$kontakt["img"]!='#')
												echo '<img width="30" align="left" src="'.$kontakt["img"].'" />';
											if ($kontakt["email"])
												echo '<span><a href="mailto:'.$kontakt["email"].'">'.$kontakt["email"].'</a></span><br/>';
											if($kontakt["tel"])
												echo '<span>'.$kontakt["tel"].'</span><br/>';
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
								echo '</div>';
							?>
							<a class="button" style="width:100%;" onclick="kontakterClick();" >Lägg till inneboende</a>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table> 
</div>
</body>
</html>
