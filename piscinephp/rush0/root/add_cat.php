<?php
header('Location:admin.php');
function my_check_exist($item, $data)
{
	if (!$data)
		return (0);
	foreach ($data as $value)
		if ($value['name'] === $item['name'])
			return (1);
	return (0);
}

function delete_lien($lien)
{
	$file = file_get_contents("../private/lien");
	$data = unserialize($file);
	foreach ($data as $key => $value)
	{
		if ($value && $value["id_cat"] == $lien)
			unset($data[$key]);;
	}
	foreach ($data as $key => $value)
	{
		if ($value)
			$new_data[] = $value;
	}
	$data = $new_data;
	file_put_contents("../private/lien", serialize($data));
}
if ($_POST['add'] === "ADD" || $_SESSION['droit'] == "1")
{
	if ($_POST['name'] == "")
	{
		header('Location:admin.php');
		exit("ERROR1\n");
	}
	$data = unserialize(file_get_contents("../private/cat"));
	$item = array();
	$item['id'] = count($data);
	$item['name'] = $_POST['name'];
	if (my_check_exist($item, $data))
	{
		header('Location:admin.php');
		exit("ERROR2\n");
	}
	$data[] = $item;
	$data_str = serialize($data);
	file_put_contents("../private/cat", $data_str);
	echo "OK\n";
}
if ($_POST['delete'] === "DELETE" || $_SESSION['droit'] == "1")
{
	if ($_POST['item'] == "tous" || $_POST['item'] == "")
	{
		header('Location:admin.php');
		exit("ERROR1\n");
	}
	$file = file_get_contents("../private/cat");
	$data = unserialize($file);
	foreach ($data as $key => $value)
	{
		if ($value && $value["name"] == $_POST["item"])
			$tmp = $key;
	}
	unset($data[$tmp]);
	foreach ($data as $key => $value)
	{
		if ($value)
			$new_data[] = $value;
	}
	$data = $new_data;
	file_put_contents("../private/cat", serialize($data));
	delete_lien($tmp);
}
?>
