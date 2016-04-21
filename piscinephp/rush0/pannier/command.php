<?php
header("Refresh:0;url=pannier.php");
function my_check_exist($item, $data)
{
	if (!$data)
		return (0);
	foreach ($data as $value)
		if ($value['name'] === $item['name'])
			return (1);
	return (0);
}
session_start();
if ($_SESSION['log'] == "")
{
	?><script>alert('Vous devez vous connecter !')</script><?php
	return ;
}
if ($_SESSION['total'] == "")
{
	?><script>alert('Votre panier est vide.')</script><?php
	exit();
}
if ($_POST['commander'] === "Commander")
{
	if (file_exists('../private/cmd'))
		$data = unserialize(file_get_contents("../private/cmd"));
	$item = array();
	$item['login'] = $_SESSION['log'];
	foreach ($_SESSION['pannier'] as $key => $value)
	{
		$item['cmd'] .= $value." : ";
	}
	$item['price'] = $_SESSION['total'];
	$data[] = $item;
	$data_str = serialize($data);
	file_put_contents("../private/cmd", $data_str);
}
unset($_SESSION['pannier']);
unset($_SESSION['total']);
?>
<script>alert('Merci pour votre commande !!')</script>
