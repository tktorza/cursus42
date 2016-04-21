<?php

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
if(!file_exists('../private/data'))
	return ;
$data = unserialize(file_get_contents("../private/data"));
if (!$data)
	return ;
foreach ($data as $key => $value)
{
	?><option><?php echo $value['name']; ?>
	<?php
}

?>
