<?php
function ft_is_sort($tab)
{
	$i = 0;
	while ($tab[$i] && $tab[$i + 1])
	{
		if (strcmp($tab[$i], $tab[$i + 1]) > 0)
			return (FALSE);
		$i++;
	}
	return (TRUE);
}
?>
