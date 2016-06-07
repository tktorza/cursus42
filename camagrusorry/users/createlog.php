<?php
	header("Location:../index.php");
	include_once('../PDO.class.php');
		if ($_POST['mail'] && $_POST["login"] && $_POST["passwd"] && $_POST["submit"] == "OK" && $_POST['passwdagain'])
		{
			$user = new users();
			if ($user->register($_POST['login'], $_POST['passwd'], $_POST['mail']))
				header("Location:../index.php");
			else {
				$_POST['error'] = 1;
			}
}
else
header("Location:create.php");
	?>
