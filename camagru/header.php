<header>
	<table class="header">
			<tr>
			<td align="left" class="logo">
				<IMG class="logo" SRC="images/style/logo.png">
			</td>
			<td align="center">
				<h1 class="title">Camagru</h1>
				<?php if ($_SESSION['loggued_on_user']){ ?>
				<ul id="menu">

        <li>
                <a href="camagru.php">Accueil</a>
        </li>

        <li>
                <a href="galery.php">Galery</a>
        </li>

</ul>
<?php } ?>
			</td>
			<td align="center" class="infos">
				<?php if($_SESSION['loggued_on_user'] != ""){ ?>

					<p>Bonjour <?php echo $_SESSION['user_session'];?>
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
				</p>
			</td>
		</tr>
		</table>
		<script style="text/javascript">
		function sure(){
			return (confirm('Sure?'))

			sfHover = function() {
			        var sfEls = document.getElementById("menu").getElementsByTagName("LI");
			        for (var i=0; i<sfEls.length; i++) {
			                sfEls[i].onmouseover=function() {
			                        this.className+=" sfhover";
			                }
			                sfEls[i].onmouseout=function() {
			                        this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
			                }
			        }
			}
			if (window.attachEvent) window.attachEvent("onload", sfHover);

		}
		</script>
</header>
