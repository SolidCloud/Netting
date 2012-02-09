<?php
session_start();
unset($_SESSION['user']);
if ($rum = $_GET['rum'])
	header("Location: ../rum.php?rum=".$rum);
else
	header("Location: ../main.php");
?>