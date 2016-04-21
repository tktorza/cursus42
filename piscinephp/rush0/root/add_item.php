<?php
header('Location:admin.php');
function my_check_exist($item, $data)
{
	if (!$data)
		return (0);
	foreach ($data as $value)
	{
		if ($value['name'] == $item['name'])
			return (1);
	}
	return (0);
}

function search_id($comp)
{
	$data = unserialize(file_get_contents("../private/cat"));
	if (!$data)
		return ;
	foreach ($data as $key => $value)
	{
		if ($comp == $value['name'])
			return ($value['id']);
	}
	return FALSE;
}

function add_link($c1, $c2, $c3, $id)
{
	$file = file_get_contents("../private/lien");
	$data = unserialize($file);
	echo "$c1 $c2 $c3";
	if ($c1 && $c1 != "Choisir cat")
	{
		$tmp = array('id_item' => $id, 'id_cat' => search_id($c1));
		$data[] = $tmp;

	}
	if ($c2 && $c2 != "Choisir cat")
	{
		$data[] = array('id_item' => $id, 'id_cat' => search_id($c2));
	}
	if ($c3 && $c3 != "Choisir cat")
	{
		$data[] = array('id_item' => $id, 'id_cat' => search_id($c3));
	}
	file_put_contents("../private/lien", serialize($data));
}

if ($_POST['submit'] === "ADD" || $_SESSION['droit'] == "1")
{
	if ($_POST['name'] == "" || $_POST['description'] == "" || $_POST['price'] == "" || $_POST['img'] == "")
	{
		header('Location:admin.php');
		exit("ERROR\n");
	}
	$data = unserialize(file_get_contents("../private/data"));
	$item = array();
	$item['id'] = count($data);
	$item['name'] = htmlspecialchars($_POST['name']);
	$item['description'] = htmlspecialchars($_POST['description']);
	if (my_check_exist($item, $data))
	{
		header('Location:admin.php');
		exit("ERROR\n");
	}
	if (!is_numeric($_POST['price']))
		return ;
	$item['price'] = $_POST['price'];
	$item['img'] = htmlspecialchars($_POST['img']);
	$data[] = $item;
	$data_str = serialize($data);
	file_put_contents("../private/data", $data_str);
	add_link($_POST['cat1'], $_POST['cat2'], $_POST['cat3'], $item['id']);
	echo "OK\n";
}
?>
