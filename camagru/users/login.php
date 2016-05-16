<?php
header("Location:../camagru.php");
session_start();
include_once('../PDO.class.php');
	$user = new users();
	if ($user->login($_POST["login"], $_POST["passwd"]) == true)
	{
		$_SESSION["loggued_on_user"] = $_POST["login"];
	}
	else
	{
		$_SESSION["loggued_on_user"] = "";
		echo "ERROR\n" . $user->login($_POST["login"], $_POST["passwd"]) . 'bad\n';
	}
?>
