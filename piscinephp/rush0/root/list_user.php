<?php
if (!file_exists('../private/passwd'))
	return ;
$data = unserialize(file_get_contents("../private/passwd"));
if (!$data)
	return ;
foreach ($data as $key => $value)
{
	?><option><?php echo $value['login']; ?>
	<?php
}
?>
