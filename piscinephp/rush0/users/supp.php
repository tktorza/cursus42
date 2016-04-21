<?php
header("Location:../index/index.php");
session_start();
if ($_SESSION['log'] == "")
	return ;
if ($_SESSION['log'] == "admin")
{
	?><script>alert('Vous ne pouvez pas del le compte admin')</script><?php
	exit() ;
}
$file = file_get_contents("../private/passwd");
$data = unserialize($file);
foreach ($data as $key => $value)
{
	if ($value && $value["login"] == $_SESSION["log"])
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
$_SESSION["log"] = "";
session_destroy()
?>
