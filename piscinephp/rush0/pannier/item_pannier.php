<?php
function select_item($name)
{
	$file = file_get_contents("../private/data");
	$tab = unserialize($file);
	foreach ($tab as $key => $value)
	{
		if ($value['name'] == $name)
			return ($value);
	}
	return (FALSE);
}

if (!$_SESSION['pannier'])
	return ;
foreach ($_SESSION['pannier'] as $key => $item)
{
	$value = select_item($item);
	include('item_pannier.html');
}

?>
