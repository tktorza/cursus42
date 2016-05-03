<?php
	include_once('../PDO.class.php');
	session_start();
	$user = new users();
	$user->modif($_POST['login'], $_POST['oldpw'], $_POST['newpw']);
	$user->redirect('../camagru.php');
		?>
