<?php include('users/user_connect.php'); ?>
<HTML>
	<HEAD>
		<TITLE>Camagru</TITLE>
 <link rel="stylesheet" type="text/css" href="camagru.css" media="screen">
	</HEAD>
	<BODY class="fond">
	<?php include('header.html'); ?>
		<?php if ($_SESSION['log'] == "") {
				include('begin.html'); }
			if ($_SESSION['log'] != ""){
			include('general.html');}
			include('footer.html');
		?>
		
	</BODY>
</html>