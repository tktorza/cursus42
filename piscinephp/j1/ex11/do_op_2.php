#!/usr/bin/php
<?php
function ft_split($char)
{
	$i = 0;
	$tab = array();
	$array = explode(" ", $char);
	foreach ($array as $elem) {
		if($elem)
		{
			$tab[$i] = $elem;
			$i++;
		}
	}
	return ($tab);
}
function correct($char)
{
	$i = 0;
	while ($char[$i])
	{
		if ($char[$i] != '+' && $char[$i] != ' ' && $char[$i] != '-' && $char[$i] != '/' && $char[$i] != '%' && ($char[$i] > '9' && $char[2][$i] < '0'))
			return (FALSE);
		$i++;
	}
	return (TRUE);
}
	if ($argc == 2)
	{

		$i = 0;
		$tall = 0;
		$n1 = 0;
		$n2 = 0;
		$sign = '+';
		$final = ft_split($argv[1]);
		while ($final[$i])
		{
			if (correct($final[$i]) == FALSE)
			{
				print("Syntax Error\n");
				exit();
			}
			$i++;
		}
		$x = 0;
		$i = 0;
		while ($final[$i][$x] < '9' && $final[$i][$x] > '0')
			{
				$tall++;
				$x++;
			}
			$n1 = substr($final[$i], 0, $tall);
			if ($final[$i][$x])
			{
				$sign = $final[$i][$x];
				$x++;
				if ($final[$i][$x])
					$n2 = substr($final[$i], $x);
				else
					$n2 = $final[$i + 1];
			}
			else
			{
				$i++;
				$sign = $final[$i][0];
				if ($final[$i][1])
					$n2 = substr($final[$i], 1);
				else
					{
						$i++;
						$n2 = $final[$i];
					}
			}
			if ($sign == '+')
			$result = $n1 + $n2;
		else if ($sign == '-')
			$result = $n1 - $n2;
		else if ($sign == '*')
			$result = $n1 * $n2;
		else if ($sign == '/')
			$result = $n1 / $n2;
		else if ($sign == "%")
			$result = $n1 % $n2;
		if (is_numeric($n2))
			print("$result\n");
		else
			print("Syntax Error\n");
	}
	else
		print("Incorrect Parameters\n")
?>
