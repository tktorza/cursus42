<?php
header("Location: ../principale.html");
session_start();
	include 'auth.php';
	if (auth($_POST["login"], $_POST["passwd"]))
	{
		$_SESSION["loggued_on_user"] = $_POST["login"];

	}
	else
	{
		$_SESSION["loggued_on_user"] = "";
		echo "ERROR\n";
	}
?>