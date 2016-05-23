<!DOCTYPE html>
<HTML>
	<head>
		<title>New</title>
	<link rel="stylesheet" type="text/css" href="../camagru.css" media="screen">
	</HEAD>
	<BODY class="new">
	<?php include('../header.php');
	include('../footer.html');?>
	<span class="inscription">
		<h1 class="title">SIGN IN!<h1>
		<form method="post" action="createlog.php" onSubmit="return checkMdp()">
			<h2>Login  <i class="com">(Not space(s))</i>  :
			<input type="text" name="login" id="login" required></h2>
			<br/>
			<h2>Password<i class="com">(5 characters minimum)</i>   :<br />
			<input type="password" name="passwd" id="passwd" pattern=".{4,}" required></h2>
			<br/><h2>Same password    :<br/>
			<input type="password" name="passwdagain" id="passwdagain" pattern=".{4,}" required></h2>
			<br/><h2>Email    :
				<input type="mail" name="mail" id="mail" required>
			</h2>
			<input type="submit" value="OK" id="submit" name="submit">
		</form>
	</span>
	<script type="text/javascript">

function checkMdp() {
	var login = document.getElementById('login').value;
  var mdp = document.getElementById("passwd").value;
  var mdp2 = document.getElementById("passwdagain").value;
  if (mdp!=mdp2) {
    alert("Not the same password");
    return false;
  }
  else
	{
		if (login.indexOf(" ") != -1)
			{
				alert("Space(s) in login");
				return false;
			}
  	return true;
	}
}
</script>
	<span class="alien">
		<a class="alien" href="../camagru.php" title="Retour page principale" ><IMG class="alien" SRC="../images/style/pingouin.jpg"></a>
	</span>
	</BODY>
</html>
