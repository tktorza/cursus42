<?php
	if ($_GET["action"] == "set")
			setcookie($_GET["name"], $_GET["value"]);
	if ($_GET["action"] == "get")
		{
			$result = $_COOKIE[$_GET["name"]];
			if ($result)
				echo "$result\n";
		}
	if ($_GET["action"] == "del")
		setcookie($_GET["name"], "", time() - 3600);
?>