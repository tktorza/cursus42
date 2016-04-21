<?php
session_start();
	$var = $_SESSION["loggued_on_user"];
	if ($var)
		echo "$var\n";
	else
		echo "ERROR\n"
?>