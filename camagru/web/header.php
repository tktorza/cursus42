<header>
	<table class="header">
			<tr>
			<td align="left" class="logo">
				<IMG class="logo" SRC="images/logo.png">
			</td>
			<td align="center">
				<h1 class="title">Camagru</h1>
			</td>
			<td align="right">
				<?php if($_SESSION['loggued_on_user'] != ""){ ?>

					<p>Bonjour <?php echo $_SESSION['user_session'];?></p>
					<form method="post" action="users/modif.html">
						<input id="button" value="change password" type="submit">
					</form>
					<form method="post" action="users/logout.php">
						<input id="button" value="log out" type="submit">
					</form>
					<form method="post" action="users/delete.php" onSubmit="return sure()">
						<input id="button" value="delete profile" type="submit">
					</form>
					<?php }?>
			</td>
		</tr>
		</table>
		<script style="text/javascript">
		function sure(){
			return (confirm('Sure?'))
		}
		</script>
</header>
