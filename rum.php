<?php 
	$currentRum=$_GET["rum"];
	if ($_GET["rum"] && file_exists('rum/'.$currentRum.'/data.xml')){
		$simpleData=simplexml_load_file('rum/'.$currentRum.'/data.xml');
		$SimpleDesc = $simpleData->inledning;
		$SimpleDesc = str_replace("]]>","",str_replace("<![CDATA[","",$SimpleDesc));
		$SimpleNyh=$simpleData->nyheter->nyhet;
		$SimpleCal=$simpleData->kalender->date;
		$SimpleCont=$simpleData->kontakter;
		
	}
	else{
		header("Location: 404.php");
		exit("Sidan finns inte!");
	}
	$rum = array();
	$Rubrum = array();
	$index = 0;
	$tabs = array();
	if($handle = opendir('rum/'.$currentRum)){
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
				if (file_exists("rum/$currentRum/$file/data.xml")){ //OMG OMGOMGPOMGOMGOMGOMGOMGOMGO [auth.php också!!!]
					$rum[$index]=simplexml_load_file("rum/$currentRum/$file/data.xml");
				}
				$index++;
			}
			else if(strpos($file,"text_")!==false){
				$Rubrum[$index]=$file;
				$rum[$index]=str_replace("\n","<br>",file_get_contents("rum/$currentRum/$file/data.txt"));
				$index++;
			}
		}
	}
	$color="#000000";
	include "_include.php";
	
?>
<script type="text/javascript">
var time;
$(document).ready(function(){
	<?php
	if($handle = opendir('rum/'.$currentRum)){
		foreach ($tabs as $file){
			if(strpos($file,"galleri_")!==false){
	?>
				$('#thumbs_<?php echo $file; ?>').attr("imgs",1);
				var margin = 0;
				$('#thumbs_<?php echo $file; ?> .img').each(function(index){
					var tt=$('#thumbs_<?php echo $file; ?>');
					tt.attr("imgs",parseInt(tt.attr("imgs"))+1);
					if (margin == 0) $(this).addClass('current-thumb');
					$(this).data('margin',margin);
					margin+=446;
				});
				$('#thumbs_<?php echo $file; ?> .img').click(function(){ 	// När man klickar på thumbnails
					$('#thumbs_<?php echo $file; ?> .img').removeClass('current-thumb');
					$(this).addClass('current-thumb');
					$('#images_<?php echo $file; ?>').css('margin-left',-$(this).data('margin'));
				});

				$('#thumb-view_<?php echo $file; ?> span').mouseover(function(){ //När man håller musen över höger/vänster-pilarna
					if ($(this).attr('id')=='thumb-right_<?php echo $file; ?>'){
						time = setInterval(function(){
							var tt=$('#thumbs_<?php echo $file; ?>');
							left_margin = parseInt($('#thumbs_<?php echo $file; ?>').css('margin-left').replace('px',''));
							if (!(left_margin<=-((tt.attr("imgs")-5)*80+10-86))){
								$('#thumbs_<?php echo $file; ?>').css('margin-left',left_margin-2);
							}
							else
								clearInterval(time);
						},5);
					}
					else if ($(this).attr('id')=='thumb-left_<?php echo $file; ?>'){
						time = setInterval(function(){
							left_margin = parseInt($('#thumbs_<?php echo $file; ?>').css('margin-left').replace('px',''));
							if (!(left_margin>=10)){
								$('#thumbs_<?php echo $file; ?>').css('margin-left',left_margin+2);
							}
							else
								clearInterval(time);
						},5);
					}
				});
				$('#thumb-view_<?php echo $file; ?>').find('span').mouseout(function(){
					clearInterval(time);
				});
	<?php
			} 
		}
	}
	?>
});
</script>
</head>
<body>
<div id="mainheader">
	<span>
		<h1>Vägkorset</h1>
	</span>
	<button>Admin</button>
</div>
<div id="bread"><a href="main.php">Hem</a> &#187; <a href="rum.php?rum=<?php echo $currentRum; ?>"><?php echo str_replace('_',' ',$currentRum);?></a></div>
<div id="admin-login" title="Logga in som Admin" style="display:none;">
	<p>
		<form action="admin/authenticate.php" method="POST" id="loginform">
			<label for="uname">Användarnamn:</label><br/>
			<input type="text" name="username" id="uname" /><br />
			<label for="pword">Lösenord:</label><br/>
			<input type="password" name="password" id="pword" />
			<?php if (isset($_GET['error'])&&$_GET['error']==1){ ?>
				<div id="error">Användarnamn och lösenord matchar inte!</div>
			<?php } ?>
			<?php if (isset($_GET['login_required'])&&$_GET['login_required']==1){ ?>
				<div id="error">Du måste logga in för att kunna visa sidan!</div>
			<?php } ?>
		</form>
	</p>
</div>
<div id="container">
	<h2><?php echo str_replace('_',' ',$currentRum); ?></h2>
	<div id="tabs">
		<ul>
			<?php 
				for($index=0;$index<count($Rubrum);$index++){
			?>
							<li><a href="#<?php  echo str_replace(" ","_", $Rubrum[$index]); ?>"><?php echo substr($Rubrum[$index],strpos($Rubrum[$index],"_")+1); ?></a></li>
			<?php } ?>
		</ul>
		<?php 
			for($index=0;$index<count($Rubrum);$index++){
				if($rum[$index]!=null&&!is_string($rum[$index])){
					$media = array();
					for($i=0;$i<count($rum[$index]->media);$i++){
						$media[] = array(
							"src"=>"rum/$currentRum/".$Rubrum[$index]."/".$rum[$index]->media[$i]->src,
							"namn"=>$rum[$index]->media[$i]->namn,
							"besk"=>$rum[$index]->media[$i]->beskrivning,
							"type"=>$rum[$index]->media[$i]->attributes()->type
						);
					}
		?>
		<div id="<?php echo $Rubrum[$index]; ?>">
			<div class="main-view" id="main-view_<?php echo $Rubrum[$index]; ?>">
				<div class="images" id="images_<?php echo $Rubrum[$index]; ?>">
				<?php
				if (count($media) > 0)
					foreach($media as $m){
						echo '<div style="position:relative;">';
						if ($m["type"] == "video")
							echo '<video class="img" controls="controls" title="'.$m["namn"].($m["namn"]!="" && $m["besk"]!=""?': ':'').$m["besk"].'">
								<source src="'.$m["src"].'.mp4" type="video/mp4" />
								<source src="'.$m["src"].'.ogv" type="video/ogg" />
								<a href="'.$m["src"].'.mp4">'.'Ladda ner '.$m["namn"].'</a>
								</video>';
						else if ($m["type"] == "audio")
							echo '<audio class="img" width=100% style="position:absolute;bottom:0px;" controls="controls" title="'.$m["namn"].($m["namn"]!="" && $m["besk"]!=""?': ':'').$m["besk"].'">
								<source src="'.$m["src"].'.mp3" type="audio/mp3" />
								<source src="'.$m["src"].'.ogg" type="audio/ogg" />
								<a href="'.$m["src"].'.mp3">'.'Ladda ner '.$m["namn"].'</a>
								</audio>';
						else if ($m["type"] == "image")
							echo '<img class="img" src="'.$m["src"].'" title="'.$m["namn"].($m["namn"]!="" && $m["besk"]!=""?': ':'').$m["besk"].'" />';
						else
							echo '<span style="display:block;position:absolute;top:150px;width:100%;"><a href="'.$m["src"].'">Ladda ner '.$m["namn"].'</a></span>';
						echo '</div>';
					}
				else
					echo '<span style="display:block;margin-top:20px;color:white;font-size:20px;">Inga bilder i galleriet</span>';
				?>
				</div>
				<div class="thumb-view" id="thumb-view_<?php echo $Rubrum[$index]; ?>"><div class="thumb-container" id="thumb-container_<?php echo $Rubrum[$index]; ?>"><div class="thumbs" id="thumbs_<?php echo $Rubrum[$index]; ?>">
				<?php
				if (count($media) > 0) {
					$caid = 0;
					foreach ($media as $m){
						$thumbHeight = 0;
						$thumbWidth = 0;
						$thumbRef = "";

						if($m["type"] == "image"){	//72x50
							$image = @getimagesize($m["src"]);
							$thumbWidth = $image[0];
							$thumbHeight = $image[1];
							if ($thumbWidth > 75){
								$thumbHeight = round(75/$thumbWidth*$thumbHeight);
								$thumbWidth = 75;
							}
							if ($thumbHeight > 50){
								$thumbWidth = round(50/$thumbHeight*$thumbWidth);
								$thumbHeight = 50;
							}
							$thumbRef = $m["src"];
						}
						else {
							$thumbWidth = $thumbHeight = 48;
							if ($m["type"] == "video")
								$thumbRef = "data:image/png;base64,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";
							else if ($m["type"] == "audio")
								$thumbRef = "data:image/png;base64,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";
							else
								$thumbRef = "data:image/png;base64,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";
						}
						echo '<div class="thumb-width"><img src="'.$thumbRef.'" class="img" width="'. $thumbWidth .'" height="'. $thumbHeight . '" /></div>';
					}
				}
				?>
				</div></div><span class="thumb-left" id="thumb-left_<?php echo $Rubrum[$index]; ?>">&#9664;</span><span class="thumb-right" id="thumb-right_<?php echo $Rubrum[$index]; ?>">&#9654;</span></div>
			</div>
		</div>
		<?php 
						}
						else{
							?>
								<div id="<?php  echo str_replace(" ","_", $Rubrum[$index]); ?>">
								<div class="imgadd" style="display:none;"></div>
								<?php if($rum[$index]!=null) echo $rum[$index];?>
								</div>
							<?php
						}
					}
		?>
	</div>
	<div id="accordion">
		<div>
			<h3><a href="#">Nyheter</a></h3>
			<div>
				<?php 
					echo '<ul>';
					if(count($SimpleNyh) > 0){
						foreach ($SimpleNyh as $nyhet){
							echo '<li>'.$nyhet.'</li>';
						}
						//echo '<li><a href="#" class="mer">mer...</a></li>';
					}
					else
						echo '<li>Vi hittade inga Nyheter i det här rummet</li>';
					echo '</ul>';
				?>
			</div>
		</div>
		<div>
			<h3><a href="#">Kalender</a></h3>
			<div>
				<?php
					echo '<ul>';
					if(count($SimpleCal) >0){
						foreach ($SimpleCal as $date){
							echo '<li>'.$date.'</li>';
						}
						//echo '<li><a href="#" class="mer">mer...</a></li>';
					}
					else
						echo '<li>Vi hittade inga händelser i det här rummet</li>';
					echo '</ul>';
				?>
			</div> 
		</div>
		<div>
			<h3><a href="#">Inneboende</a></h3>
			<div style="padding: 12px 0 12px 0;overflow:hidden;" >
				<?php
					echo '<div>'; 
					if(count($SimpleCont) >0){
						foreach($SimpleCont->children() as $kontakt){
							echo "<div><table class='ui-button ui-corner-all ui-state-hover' style='width:100%;border:0px solid #8C58BE;border-width:2px 0 0 1px;padding:2px;text-align:left;cursor:default;' ><tr>";
							if ($kontakt->img&&$kontakt->img!='#')
								echo '<td><img width="30" align="left" src="'.$kontakt->img.'" onerror="this.src=\'../img/top_bottom.png\';console.warn(\'Försök att läsa kontakt bild misslyckades!\');return false;" /></td>';
							echo "<td>";
							if ($kontakt->email)
								echo '<span><a href="mailto:'.$kontakt->email.'">'.$kontakt->email.'</a></span><br/>';
							if($kontakt->namn)
								echo '<span>'.$kontakt->namn.'</span><br/>';
							echo "</td></tr></table></div><p/>";
						}
					}
					else
							echo '<span><a href="#" class="mer">Vi saknar kontaktuppgifterna i det här rummet</a></span>';
					echo '</div>';
				?>
			</div>
		</div>
	</div>
	<br style="clear:both" />
</div>
</body>
</html>
