<?php include('users/user_connect.php'); $_POST['error'] == 0;?>
<HTML>
	<HEAD>
		<TITLE>Camagru</TITLE>
 <link rel="stylesheet" type="text/css" href="camagru.css" media="screen">
	</HEAD>
	<BODY class="fond">
	<?php include('header.php'); ?>
		<?php if ($_SESSION["loggued_on_user"] == "") {
				include('begin.php'); }
			if ($_SESSION["loggued_on_user"] != ""){
			include('principale.php');}
			print_r($_SESSION);
			include('footer.html');
		?>

	</BODY>
</html>
