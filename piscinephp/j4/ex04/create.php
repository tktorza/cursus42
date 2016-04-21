<?php
		header("Location:index.html");
		if ($_POST["login"] && $_POST["passwd"] && $_POST["submit"] == "OK")
		{
			if (!file_exists("../private"))
				mkdir("../private");
			$new = array("login" => $_POST["login"], "passwd" => hash("whirlpool", $_POST["passwd"]));
			if (file_exists("../private/passwd"))
			{	
				$file = file_get_contents("../private/passwd");
				$tab = unserialize($file);
				foreach ($tab as $key => $value) {
					if ($value["login"] == $new["login"])
					{
						echo "ERROR\n";
						exit();
					}
				}
				$tab[] = $new;
				file_put_contents("../private/passwd", serialize($tab));
				echo "OK\n";
			}
			else
			{
				$final = array();
				$final[] = $new;
				file_put_contents("../private/passwd", serialize($final));
				echo "OK\n";
			}

			exit();
		}
		else
		{
			echo "ERROR\n";
			exit();
		}
	?>