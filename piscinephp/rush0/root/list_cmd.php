<?php

if(!file_exists("../private/cmd"))
	return ;
$data = unserialize(file_get_contents("../private/cmd"));
if (!$data)
	return ;
foreach ($data as $key => $value)
{
	?><p><?php echo $value['login'].' | '.$value['cmd'].'| total : '.$value['price']."$"; ?></p>
	<?php
}

?>
