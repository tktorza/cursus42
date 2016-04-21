<?php

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

function is_cat($comp, $value)
{
	$id = search_id($comp);
	$data = unserialize(file_get_contents("../private/lien"));
	if (!$data)
		return (FALSE);
	foreach ($data as $key => $elem)
	{
		if ($id == $elem['id_cat'] && $value == $elem['id_item'])
			return (TRUE);
	}
	return (FALSE);
}

function selectioned_item($value, $data, $key)
{
	if ($value == "Choisir Item" || !$data)
		return ;
	foreach ($data as $k => $elem)
	{
		if ($elem['name'] == $value)
			return ($elem[$key]);
	}
	return (FASLE);
}

$data = unserialize(file_get_contents("../private/data"));
if (!$data)
	return ;
$check = $_GET['val'];
if (!$check)
	$check = "tous";
foreach ($data as $key => $value)
{
	if (is_cat($check, $value['id']))
		include('item.html');
}
?>
