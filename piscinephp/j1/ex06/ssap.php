#!/usr/bin/php
<?php
function ft_split($char)
{
	$i = 0;
	$tab = array();
	$array = explode(" ", $char);
	foreach ($array as $elem)
	{
		if ($elem)
		{
			$tab[$i] = $elem;
			$i++;
		}
	}
	return ($tab);
}
if (argc != 1)
{
$i = 1;
$final = array();
while ($i < $argc)
{
	$tmp = array();
	$tmp = ft_split($argv[$i]);
	$final = array_merge($final, $tmp);
	$i++;
}
sort($final);
foreach ($final as $elem)
{
	print("$elem");
	print("\n");
}
}
?>