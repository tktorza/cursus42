<?php include('../users/user_connect.php'); ?>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="../index/index.css" type="text/css" media="screen"/>
	</head>
<body>
	<a href="../index/index.php" title="index">Revenir à l'index<a/></br>
	<?php include('../index/header.html'); ?>
	<?php include('footer_pannier.html'); ?>
	<h1> Votre pannier </h1>
	<table id="table">
		<?php include('item_pannier.php'); ?></br>
	</table>
</body>
</html>
