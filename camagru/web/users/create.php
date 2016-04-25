<!DOCTYPE html>
<HTML>
	<head>
		<title>New</title>
	<link rel="stylesheet" type="text/css" href="../camagru.css" media="screen">
	</HEAD>
	<BODY class="new">
	<?php include('../header.html');
	include('../footer.html');?>
	<span class="inscription">
		<h1 class="title">SIGN IN!<h1>
		<form method="post" action="createlog.php" onSubmit="return checkMdp()">
			<h2>Login    :  
			<input type="text" name="login" id="login" required></h2>
			<br/>
			<h2>Password   :    
			<input type="password" name="passwd" id="passwd" pattern=".{5,}" required></h2>
			<br/><h2>Same password    :          
			<input type="password" name="passwdagain" id="passwdagain" pattern=".{5,}" required></h2>
			<br/><h2>Email    :
				<input type="email" name="email" id="email" required>
			</h2>
			<br/><br/><br/><input type="submit" value="OK" id="submit" name="submit">
		</form>
	</span>
	<script type="text/javascript">
function checkMdp() {
  var mdp = document.getElementById("passwd").value;
  var mdp2 = document.getElementById("passwdagain").value;
  if (mdp!=mdp2) {
    alert("Not the same password");
    return false;
  }
  else
  	return true;
}
</script>
	<span class="alien">
		<a class="alien" href="../camagru.php" title="Retour page principale" ><IMG class="alien" SRC="../images/alien.jpg"></a>
	</span>
	</BODY>
</html>
