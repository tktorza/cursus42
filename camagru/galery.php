<?php include('users/user_connect.php'); $_POST['error'] == 0;?>
<HTML>
	<HEAD>
		<TITLE>Camagru</TITLE>
 <link rel="stylesheet" type="text/css" href="camagru.css" media="screen">
	</HEAD>
	<BODY class="fond">
	<?php include('header.php');
  include_once('PDO.class.php');
  $db = New database();
  $data = $db->query('SELECT * FROM galery');
    foreach ($data as $value) {
      ?>
      <div id="galery">
        <img id="galery" <?php print "src=\"" . $value['src'] . '"';?> >
      </div>
        <?php
    }
  ?>

		<?php	include('footer.html');?>

	</BODY>
</html>
