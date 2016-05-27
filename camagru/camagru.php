<?php include('users/user_connect.php'); $_POST['error'] == 0;?>
<HTML>
	<HEAD>

		<TITLE>Camagru</TITLE>
 <link rel="stylesheet" type="text/css" href="camagru.css" media="screen">
	</HEAD>
	<BODY class="fond">
	<?php include('header.php'); ?>
		<?php if ($_SESSION["loggued_on_user"] == "") {
				include('begin.php');
			 }
			if ($_SESSION["loggued_on_user"] != ""){
			include('main.php');
			include('side.php');
		}
//			print_r($_SESSION);
			include('footer.html');
		?>
<script>

	var help = "<?php echo $_GET['passwd']; ?>";
	if (help == "true")
		alert('Un nouveau mot de passe a ete envoye sur votre adresse email.')
</script>
	</BODY>
</html>
