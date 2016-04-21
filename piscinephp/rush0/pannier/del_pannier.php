<?php
function ret_item($name)
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

function n_item($name)
{
	foreach ($_SESSION['pannier'] as $key => $value)
	{
		if ($value == $name)
			return ($key);
	}
	return (FALSE);
}


session_start();
$value = ret_item($_GET['val']);
$n = n_item($value['name']);
unset($_SESSION['pannier'][$n]);
$_SESSION['total'] -= $value['price'];
header("Refresh:0;url=../pannier/pannier.php");
?>
<script>alert('Correctement retiré')</script>
