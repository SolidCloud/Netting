<?php
		include "_adminclude.php";
?>
<style type="text/css" media="screen">
	form {
		width: 300px;
		margin: 0 auto;
	}
	#error {
		border: 3px groove #F33;
		padding: 5px;
		margin:10px 0 10px 0;
		color: #F33;
	}
</style>
</head>
<body>
	<form action="authenticate.php" method="POST" id="loginform">
		<label for="uname">Användarnamn:</label><br/>
		<input type="text" name="username" id="uname" /><br />
		<label for="pword">Lösenord:</label><br/>
		<input type="password" name="password" id="pword" /><br/>
		<?php if ($_GET['error']==1){ ?>
			<div id="error">Användarnamn och lösenord matchar inte!</div>
		<?php } ?>
		<?php if (isset($_GET['login_required'])){ ?>
			<div id="error">Du måste logga in för att kunna visa sidan!</div>
		<?php } ?>
		<input type="submit" value="Logga in"/>
	</form>
</body>
</html>