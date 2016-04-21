#!/usr/bin/php
<?PHP
if ($argc > 1)
{
	$i = 1;
	while ($i++ != $argc)
		if (strstr(substr($argv[$i], 0, strlen($argv[1])), $argv[1]) && $argv[$i][strlen($argv[1])] == ':')
			$chain = substr($argv[$i], strlen($argv[1])+1);
	if ($chain)
		print("$chain\n");
}
?>