#!/usr/bin/php
<?php
	$hg = file_get_contents("$argv[1]");
	$fd = explode("\n", $hg);
	$tab = array_slice($fd, 1);
	$tabc = array();
	$e = 0;
	foreach ($tab as $elem) {
		$i = 0;
		if ($elem && strpos($elem, ";;") === FALSE)
		{
			while ($elem[$i] !== ";")
				$i++;
			$tmp = substr($elem, 0, $i);
			//print("tmp = $tmp\n");
			if (strpos($elem, "moulinette") !== FALSE)
			{
				$$tmp = substr($elem, $i + 1, 2);
			}
			$true = 0;
			foreach ($tabc as $index => $truc) {
				if (strpos($truc, $tmp) !== FALSE)
				{
					//print("Salut\n");
					$true++;
					$temp = substr($elem, $i + 1, 2);
					$fou = $truc;
					$tabc[$index] = $fou . " " . $temp;
				//	print("$truc\n");
				}
			}
			//reset($tabc);
			if ($true == 0)
			{
			//	print("Bye\n");
			//	print("elem = $elem[$x]\n");
				$temp = substr($elem, $i + 1, 2);
				$lol = $tmp . " " . $temp;
				//print("Bye: $lol ($tmp   /   $temp)\n");
				array_push($tabc, $lol);
				//print_r($tabc);
			}
		}
	}
	//print_r($tabc);
	if (strcmp($argv[2], "moyenne") == 0)
	{
		$term = 0;
		foreach ($tabc as $elem) {
			$i = 0;
			$tab = array();
			$str = str_replace(";", " ", $elem);
			$tab = explode(" ", $str);
			$result = "0";
			foreach ($tab as $key => $value) {
				$result += intval($tab[$key]);
			}
			$term += $result / count($tab);
		}
		$tot = $term / count($tabc);
		print("$tot\n");
	}
	


?>
