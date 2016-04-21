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


session_start();
$_SESSION['pannier'][] = $_GET['val'];
$value = select_item($_GET['val']);
$_SESSION['total'] += $value['price'];
header("Refresh:0;url=../index/index.php?val=tous");
?>
<script>alert('Correctement ajouté')</script>
