<?php
header('Location:admin.php');
function my_check_exist($item, $data)
{
	$i = 0;
	if (!$data)
		return (FALSE);
	foreach ($data as $value)
	{
		if ($value['name'] === $item)
			return ($i);
		$i++;
	}
	return (FALSE);
}

function my_check_del($item, $n)
{
	if ($item['id'] == $n)
		return (TRUE);
	return (FALSE);
}


if (($_POST['modif'] === "MODIF" || $_POST['delete'] === "DELETE") )
{
	if ($_POST['name'] == "" || $_POST['description'] == "" || $_POST['price'] == "" || $_POST['img'] == "")
	{
		header('Location:admin.php');
		exit("ERROR1\n");
	}
	$data = unserialize(file_get_contents("../private/data"));
	if (($n = my_check_exist($_POST['name'], $data)) === FALSE)
	{
		header('Location:admin.php');
		exit("ERROR2\n");
	}
	if ($_POST['delete'] === "DELETE")
	{
		unset($data[$n]);
		foreach ($data as $key => $value)
		{
			if ($value['name'])
				$new_data[] = $value;
		}
		$data = $new_data;
	}
	else
	{
		$item['id'] = $n;
		$item['name'] = $_POST['name'];
		$item['description'] = $_POST['description'];
		$item['price'] = $_POST['price'];
		$item['img'] = $_POST['img'];
		$data[$n] = $item;
	}
	$data_str = serialize($data);
	file_put_contents("../private/data", $data_str);
	echo "OK\n";
}
?>
