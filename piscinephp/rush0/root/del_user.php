<?php

header("Location:../root/admin.php");
session_start();
if ($_POST['login'] == "admin")
{
	?><script>alert('Vous ne pouvez pas del le compte admin')</script><?php
	exit() ;
}
$file = file_get_contents("../private/passwd");
$data = unserialize($file);
foreach ($data as $key => $value)
{
	if ($value && $value["login"] == $_POST["login"])
		$tmp = $key;
}
unset($data[$tmp]);
foreach ($data as $key => $value)
{
	if ($value)
		$new_data[] = $value;
}
$data = $new_data;
file_put_contents("../private/passwd", serialize($data));

?>
