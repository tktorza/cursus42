<?php
session_start();
?>
<!DOCTYPE html>
<HTML>
<p>Sig in or log<br/></p>
<form method="get" id="index.php" action="index.php">
<p><label for="login">Login</label> : 
<?php
	if ($_GET["login"] && $_GET["passwd"] && $_GET["submit"] == "OK")
	{
		$var = $_GET["login"];
		echo "<input type='text' value=$var size='15' name='login' id='login'/>";
		$_SESSION["login"] = $_GET["login"];
		$_SESSION["passwd"] = $_GET["passwd"];
	}
	else
		{
			if ($_SESSION["login"] && $_SESSION["passwd"])
			{
				$var = $_SESSION["login"];
				echo "<input type='text' value=$var size='15' name='login' id='login'/>";
			}
			else
				echo "<input type='text' size='15' name='login' id='login'/>";
		}
?>
</p>
<p>
<label for="passwd">Votre Mot de passe</label>
<?php
	if ($_GET["login"] && $_GET["passwd"] && $_GET["submit"] == "OK")
	{
		$vari = $_GET["passwd"];
		echo "<input type='password' value=$vari size='15' name='passwd' id='passwd'/>";
	}
	else
	{
			if ($_SESSION["login"] && $_SESSION["passwd"])
			{
				$var = $_SESSION["passwd"];
				echo "<input type='text' value=$var size='15' name='passwd' id='passwd'/>";
			}
			else
				echo "<input type='text' size='15' name='passwd' id='passwd'/>";
	}
?>
</p>
<input type="submit" name="submit" value="OK"></input>
</form>
</HTML>
