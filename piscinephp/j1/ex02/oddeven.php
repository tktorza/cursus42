#!/usr/bin/php
<?PHP
while (42)
{
print("Entrer un nombre: ");
$val = trim(fgets(STDIN));
if (feof(STDIN) == TRUE)
	exit;
if (is_numeric($val) == TRUE)
{
	if ($val % 2 == 0)
		print("Le chiffre $val est Pair\n");
	else
		print("Le chiffre $val est Impair\n");
}
else
	print("'$val' n'est pas un chiffre\n");
}
?>
