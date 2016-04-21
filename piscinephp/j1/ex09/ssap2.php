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
$final = array();
while ($i < $argc)
{
	$tmp = array();
	$tmp = ft_split($argv[$i]);
	$final = array_merge($final, $tmp);
	$i++;
}
$alpha = array();
$num = array();
$other = array();
foreach ($final as $elem)
{
	
	if (ctype_alpha($elem))
		$alpha[] = $elem;
	else if (is_numeric($elem))
		$num[] = $elem;
	else
		$other[] = $elem;
}
sort($alpha, SORT_FLAG_CASE | SORT_STRING);
sort($num, SORT_FLAG_CASE | SORT_STRING);
sort($other, SORT_FLAG_CASE | SORT_STRING);
foreach ($alpha as $elem) {
	print("$elem\n");
}
foreach ($num as $elem) {
	print("$elem\n");
}
foreach ($other as $elem) {
	print("$elem\n");
}
}
?>