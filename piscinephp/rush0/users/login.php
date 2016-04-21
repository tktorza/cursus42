<?php
header("Location:../index/index.php?val=tous");

function check_admin($admin, $passwd)
{
	$file = file_get_contents("../private/passwd");
	$tab = unserialize($file);
	foreach ($tab as $key => $value) {
		if ($value["login"] === $admin && $value["passwd"] === hash("whirlpool", $passwd))
			return ($value['droit']);
	}
}

session_start();
	include 'auth.php';
	if (auth($_POST["login"], $_POST["passwd"]))
	{
		$_SESSION["log"] = $_POST["login"];
		$_SESSION["droit"] = check_admin($_POST['login'], $_POST['passwd']);
		echo "OK\n";
	}
	else
	{
		$_SESSION["log"] = "";
		echo "ERROR\n";
	}
?>
