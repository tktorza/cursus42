<?php
session_start();
?>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="admin.css" type="text/css" media="screen"/>
	</head>
	<?php include ('verif_adm.php') ?>
	<body>
		<?php include('add_item.html'); ?>
		<?php include('modif_item.html'); ?>
		<?php include('del_user.html'); ?>
		<?php include('add_cat.html'); ?>
		<?php include('command.html'); ?>
		<a href="../index/index.php" title="index">Revenir à l'index<a/></br>
	</body>
</html>
