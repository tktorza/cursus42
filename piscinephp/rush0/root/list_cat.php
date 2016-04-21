<?php

if (!file_exists('../private/cat'))
	return ;
$data = unserialize(file_get_contents("../private/cat"));
if (!$data)
	return ;
foreach ($data as $key => $value)
{
	?><option><?php echo $value['name']; ?></a>
	<?php
}
?>
