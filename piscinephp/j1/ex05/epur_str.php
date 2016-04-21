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
$tableau = ft_split($argv[1]);
$i = 0;
while ($tableau[$i])
{
	print($tableau[$i]);
	if ($tableau[$i + 1])
		print(" ");
	$i++;
}
if ($argv[1])
	print("\n");
?>
