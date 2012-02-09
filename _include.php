<?php
	session_start();
	include "admin/auth.php";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Vägkorset</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link type="text/css" href="css/custom-theme/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
	<link href='http://fonts.googleapis.com/css?family=Architects+Daughter|Permanent+Marker|Aclonica|Muli' rel='stylesheet' type='text/css' />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>

<script type="text/javascript">
<?php $cond = (isset($_GET['error']) && $_GET['error'] == 1) || (isset($_GET['login_required']) && $_GET['login_required'] == 1);?>
var cont = 1, <?php echo "error = ".($cond?"true":"false").";";
	if (current_user()) {
		if (current_user())
			echo "var cur = '".current_user()."';";
	}
?>

	$(document).ready(function(){
		$('#admin-login').dialog({
		resizable: false,
		autoOpen: false,
		modal: true,
		buttons: {
			"Logga in": function() {
					$('#loginform').submit();
				},
				Avbryt: function() {
					$(this).dialog("close");
				}
			}
		});
		$('#mainheader').find('button').button().click(function(){
		if (cur)
			window.location="admin";
		else
			$('#admin-login').dialog('open');
		});
		$('#mainheader>span').click(function(){
			window.location='index.php';
		});
		$('#accordion').accordion({ autoHeight:false, active:0});
		$('#tabs').tabs();
		$('.button').button();
		$('#loginform input').keydown(function(e){
			if(e.keyCode==13) $('#loginform').submit();
		});
		if (error){
			$('#admin-login').dialog('open');
		}
	});
</script>
