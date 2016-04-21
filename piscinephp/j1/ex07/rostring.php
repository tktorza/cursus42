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
if ($argc != 1)
{
$i = 1;
$deb = ft_split($argv[1]);
$beg = $deb[0];
$final = array_slice($deb, 1);
foreach ($final as $elem)
{
	print("$elem");
	print(" ");
}
if ($beg)
	print("$beg\n");
}
?>
