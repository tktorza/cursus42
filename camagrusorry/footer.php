<footer>
<div class="footer">
			<?php if($_SESSION['droit'] == 1) { ?>
			<div align="left">
			<form method="post" action="admin.php">
				<input type="submit" value="Administration">
			</form>
		</div>
		<?php }?>
			Website created by Tkindustries&copy;.<br/>All actions have consequences.
		</div>
</footer>
