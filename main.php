<?php
	if (file_exists('rum/data.xml')){
		$simpleData=simplexml_load_file('rum/data.xml');
		$SimpleTab=$simpleData->tab;
		$SimpleNyh=$simpleData->nyheter->nyhet;
		$SimpleCal=$simpleData->kalender->date;
		$SimpleCont=$simpleData->kontakter;
	} else{
		header("Location: 404.php");
		exit("Sidan finns inte!");
	}
	include "_include.php";
?>
<script type="text/javascript">
$(document).ready(function(){
	$(".inneboende").mouseover(function(){
		$(this).css("background-position","0px 220px");
		$(".desc",this).show();
	});
	$(".inneboende").mouseout(function(){
		$(this).css("background-position","0px 0px");
		$(".desc",this).hide();
	});
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
<div id="bread"><a href="main.php">Hem</a></div>
<div id="admin-login" title="Logga in som Admin" style="display:none;">
	<p>
		<form action="admin/authenticate.php" method="POST" id="loginform">
			<label for="uname">Användarnamn:</label><br/>
			<input type="text" name="username" id="uname" /><br />
			<label for="pword">Lösenord:</label><br/>
			<input type="password" name="password" id="pword" />
			<?php if (isset($_GET['error'])&&$_GET['error']==1){ ?>
				<div class="ui-state-error"><span class="ui-icon ui-icon-alert" style="float:left;margin-right:0.3em;"></span><strong>Fel:</strong><br/>Användarnamn och lösenord matchar inte!</div>
			<?php } ?>
			<?php if (isset($_GET['login_required'])&&$_GET['login_required']==1){ ?>
				<div class="ui-state-error"><span class="ui-icon ui-icon-alert" style="float:left;margin-right:0.3em;"></span><strong>Fel:</strong><br/>Du måste logga in för att kunna visa sidan!</div>
			<?php } ?>
		</form>
	</p>
</div>
<div id="container">
	<h2>Hem</h2>
	<table>
		<tr>
			<td valign=top >
	<div id="besk">
		<?php
			if(count($SimpleTab)>0){
				$i=0;
				foreach($SimpleTab as $tab){
					$desk = $tab->desk;
					$desk = str_replace("]]>","",str_replace("<![CDATA[","",$desk));
					$desk = str_replace("\n","<br/>",$desk);
					?>
					<div id="<?php echo $i.$tab->rub; ?>" >
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
		<?php
		if($handle = opendir('rum/')){
			$rooms = array();
			while (false !== ($file = readdir($handle)))
				if ($file != "." && $file != ".." && $file != ".DS_Store" && is_dir('rum/'.$file))
					$rooms[] = $file;
			natsort($rooms);
			foreach ($rooms as $room){
				$beskriv=simplexml_load_file('rum/'.$room.'/data.xml')->inledning;
				if(strlen($beskriv)>150)
					$beskriv=substr($beskriv,0,150)."<br/>...";
				echo '<a href="rum.php?rum='.$room.'"><div class="inneboende"><div class="desc"><div class="namn" >'.str_replace('_',' ',$room).'</div><div class="besk">'.$beskriv.'</div></div></div></a>';
			}
		}
		?>
	<br style="clear:both;" />
	</div>
			</td>
			<td valign=top >
	<div style="width:200px;" class="ui-accordion ui-widget ui-helper-reset" role="tablist">
		<div>
			<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top" role="tab" aria-expanded="true" aria-selected="true" ><a style="cursor:default;">Nyheter</a></h3>
			<div class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active" role="tabpanel" style="display: block; overflow: auto; padding-top: 12px; padding-bottom: 12px;" >
				<?php
					echo '<div>';
					$co=count($SimpleNyh);
					if($co >= 1){
						$i=0;
						foreach ($SimpleNyh as $nyhet){
							if($co-1 ==$i)
								echo '<div style="padding:10px 0 10px 0;" >'.$nyhet.'</div>';
							else
								echo '<div style="padding:10px 0 10px 0;border-bottom:1px solid #ccc;" >'.$nyhet.'</div>';
							$i++;
						}
					}
					else
						echo '<li>Inga nyheter tillagda</li>';
					echo '</div>';
				?>
			</div>
		</div>
		<div>
			<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top" role="tab" aria-expanded="true" aria-selected="true" ><a style="cursor:default;">Kalender</a></h3>
			<div class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active" role="tabpanel" style="display: block; overflow: auto; padding-top: 12px; padding-bottom: 12px;" >
				<?php
					echo '<div>';
					$co=count($SimpleCal);
					if($co >= 1){
						$i=0;
						foreach ($SimpleCal as $kalender){ 
							if($co-1 ==$i)
								echo '<div style="padding:10px 0 10px 0;" >'.$kalender.'</div>';
							else
								echo '<div style="padding:10px 0 10px 0;border-bottom:1px solid #ccc;" >'.$kalender.'</div>';
							$i++;
						}
					}
					else
						echo '<li>Inga händelser tillagda</li>';
					echo '</div>';
				?>
			</div> 
		</div>
		
		<div>
			<h3 class="ui-accordion-header ui-helper-reset ui-state-active ui-corner-top" role="tab" aria-expanded="true" aria-selected="true"><a  style="cursor:default;">Inneboende</a></h3>
			<div class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active" role="tabpanel" style="display: block; padding: 12px 0 12px 0;overflow:hidden;">
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
			</td>
		</tr>
	</table> 
</div>
</body>
</html>
