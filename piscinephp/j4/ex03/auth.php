<?php
	function auth($login, $passwd)
	{
		if ($login && $passwd && file_exists("../private/passwd"))
		{
			$file = file_get_contents("../private/passwd");
			$tab = unserialize($file);
			foreach ($tab as $key => $value) {
				if ($value["login"] === $login && $value["passwd"] === hash("whirlpool", $passwd))
					return (TRUE);
			}
		}
		return (FALSE);		
	}
?>