<?php include "_include.php"; ?>
<html>
<head>
	<style type="text/css" media="screen">
		#container {
			text-align: center;
		}
	</style>
</head>
<body>
	<div id="mainheader">
		<span>
			<h1>Nätverkstan!</h1>
		</span>
		<button>Admin</button>
	</div>
	<div id="bread"><a href="main.php">Hem</a></div>
	<div id="admin-login" title="Logga in som Admin">
		<p>
			<form>
				Användarnamn:<br/>
				<input type="text" id="uname" /><br />
				Lösenord:<br/>
				<input type="password" id="pword" />
			</form>
		</p>
	</div>
	<div id="container">
		<h2><?php echo "404"; ?></h2>
		<h3>Kan inte hitta sidan du letade efter!</h3>
		<h4>Gå tillbaka till <a href="main.php">Bottenvåningen</a> och pröva igen, eller välj en annan våning!</h4>
		<br style="clear:both" />
	</div>
</body>
</html>