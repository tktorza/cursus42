<?php
header("Location:../index/index.php");
		if ($_POST["login"] && $_POST["newpw"] && $_POST["submit"] == "OK" && $_POST["oldpw"])
		{
			if (file_exists("../private/passwd"))
			{

				$file = file_get_contents("../private/passwd");
				$tab = unserialize($file);
				foreach ($tab as $key => $value) {
					if ($value && $value["login"] == $_POST["login"] && hash("whirlpool", $_POST["oldpw"]) == $value["passwd"])
					{
						$new = array("login" => $_POST["login"], "passwd" => hash("whirlpool", $_POST["newpw"]));
						$tab[$key] = $new;
						file_put_contents("../private/passwd", serialize($tab));
							echo "OK\n";
						exit();
					}
			}
		}
	}
	echo "ERROR\n";
exit();
?>
