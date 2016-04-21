<?php
header('Content-Type: text/html');
header("WWW-Authenticate: Basic realm=''Espace membres''");

if ($_SERVER["PHP_AUTH_USER"] === "zaz" && $_SERVER["PHP_AUTH_PW"] === "jaimelespetitsponeys")
{
	header("HTTP/1.1 200 OK");
	echo "<html><body>\nBonjour Zaz<br />\n";
	$file = file_get_contents("../img/42.png");
	echo "<img src='data:image/png;base64,".base64_encode($file)."'>\n</body></html>\n";
}
else
{
	header('HTTP/1.0 401 unauthorized');
	echo "<html><body>Cette zone est accessible uniquement aux membres du site</body></html>\n";
}

?>t accessible uniquement aux membres du site</body></html>
* Closing connection #0