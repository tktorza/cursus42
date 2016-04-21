<?php
	header("Location:../index/index.php?val=tous");

		if ($_POST["login"] && $_POST["samepw"] && $_POST["passwd"] && $_POST["submit"] == "OK" && $_POST["samepw"] == $_POST["passwd"])
		{
			if (!file_exists("../private"))
				mkdir("../private");
			$new = array("login" => htmlspecialchars($_POST["login"]), "passwd" => hash("whirlpool", $_POST["passwd"]));
			if (file_exists("../private/passwd"))
			{
				$file = file_get_contents("../private/passwd");
				$tab = unserialize($file);
				foreach ($tab as $key => $value) {
					if ($value["login"] == $new["login"])
					{
						echo "ERROR\n";
						$GLOBALS["err"] = 1;
						exit();
					}
				}
				$tab[] = $new;
				file_put_contents("../private/passwd", serialize($tab));
				echo "OK\n";
				$GLOBALS["err"] = 0;
			}
			else
			{
				$final = array();
				$final[] = $new;
				file_put_contents("../private/passwd", serialize($final));
				echo "OK\n";
				$GLOBALS["err"] = 0;
			}
			exit();
		}
		echo "ERROR\n";
		$GLOBALS["err"] = 1;
		exit;
	?>
