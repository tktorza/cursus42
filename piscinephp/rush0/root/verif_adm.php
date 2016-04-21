<?php
session_start();
if(!$_SESSION['droit'] == "1")
{
	echo "ERROR\n";
	exit ();
}

?>
