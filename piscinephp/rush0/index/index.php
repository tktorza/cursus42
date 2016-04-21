<?php include('../users/user_connect.php'); ?>
<html>
	<head>
		<meta charset="utf-8"/>
		<link rel="stylesheet" href="index.css" type="text/css" media="screen"/>
	</head>
<body>
	<?php include('../install/install.php'); ?>
	<?php include('header.html'); ?>
	<table id="table">
		<?php include('item.php'); ?></br>
	</table>
	<?php include('menu.html'); ?>
	<form method="post" action="../users/supp.php">
		<center><input id="bouton" value="suppression compte" type="submit" name="suppression" /></center>
	</form>
</body>
</html>
