#!/usr/bin/php
<?php
	if ($argc == 4)
	{
		$i = 0;
		while ($argv[2][$i] && $argv[2][$i] != '+' && $argv[2][$i] != '-' && $argv[2][$i] != '/' && $argv[2][$i] != '%' && $argv[2][$i] != '*') 
				$i++;
		if ($argv[2][$i] == "+")
			$result = $argv[1] + $argv[3];
		else if ($argv[2][$i] == "-")
			$result = $argv[1] - $argv[3];
		else if ($argv[2][$i] == "/")
			$result = $argv[1] / $argv[3];
		else if ($argv[2][$i] == "%")
			$result = $argv[1] % $argv[3];
		else if ($argv[2][$i] == "*")
			$result = $argv[1] * $argv[3];
		print("$result\n");
	}
	else
		print("Incorrect Parameters\n")
?>
