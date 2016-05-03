<?php
session_start();
	include_once('../PDO.class.php');
	$users = new users();
	$users->logout();
	$users->redirect('../camagru.php');
?>
