<?php

if (!file_exists('../private/cat'))
	return ;
$data = unserialize(file_get_contents("../private/cat"));
if (!$data)
	return ;
foreach ($data as $key => $value)
{

	?><li><a href="index.php?val=<?php echo $value['name'];?>"><?php echo $value['name']; ?></a></li>
	<?php
}

?>
