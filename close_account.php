<?php 

	session_start();
	ob_start();
	include 'include/header.php';

	if(isset($_POST['cancel'])){
		header("Location: settings.php");
	}

	if(isset($_POST['close_account'])){
		$sql = "UPDATE users SET user_closed = ? WHERE username = ?";
		$stmt=$pdo->prepare($sql);
		$stmt->execute([1, $userLoggedIn]);

		$sql = "UPDATE posts SET user_closed =? WHERE added_by = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([1, $userLoggedIn]);

		session_destroy();
		header("Location: index.php");
	}
?>

<div class="container main">
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2 panel p20 settings">
			<h3>Close Account</h3>
			<p class="mb20"><b>Are you sure you want to close your account?</b></p>
			<p>Closing your account will hide your profile and all activicty from all users</p><br />
			<p>You can reopen your account at anytime by simply logging back in.</p>

			<form action="close_account.php" method="post" class="form mt50">
				<input type="submit" class="btn btn-danger mr10" name="close_account" value="Yes, close it!">
				<input type="submit" class="btn btn-default" name="cancel" value="No, nevermind!">
			</form>
		</div>
	</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script src="js/site.js"></script>

</body>
</html>