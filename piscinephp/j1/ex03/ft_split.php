<?PHP
function ft_split($char)
{
	$array = explode(" ", $char);
	sort($array);
	$i = 0;
	while ($i < count($array) && $array[$i] == '')
		$i += 1;
	$tab = array_slice($array, $i);
	return ($tab);
}
?>
