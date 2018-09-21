<?php 

	session_start();
	include 'include/header.php';
	include 'include/form_handlers/settings_handler.php';
	
?>

<div class="container main panel settings p20">
	<div class="row ">
		<div class="col-sm-12 p20">
			<h3 class="sub_pg_title"><i class="fas fa-cog"></i> Account Settings</h3>
		</div>
	</div>
	
	<div class="row bb">
		<div class="col-sm-6">
			<?php 
				echo '<img src="'.$user['profile_pic'].'" class="profile_pic_lg img-responsive" />';
			?>

			<a href="upload.php"><i class="fas fa-upload"></i> Upload new profile picture</a>
		</div>

		<div class="col-sm-6">
			<h4>Close Account</h4>
			<a href="close_account.php" class="btn btn-danger mb20">Close Account</a>	
			<br />
			<p>Closing your account will hide your profile and all activicty from all users.</p>
		</div>
	</div>	

	<div class="row">
		<div class="col-sm-6">

			<?php 
				$sql = "SELECT * FROM users WHERE username = ?";
				$stmt= $pdo->prepare($sql);
				$stmt->execute([$userLoggedIn]);
				$user_details = $stmt->fetchAll();

			?>
			<h4>Update User Details</h4>
			<form action="settings.php" method="post" class="form">
				<div class="form-group">
					<label for="fname">First Name</label>
					<input type="text" name="fname" class="form-control" value="<?php echo $user_details[0]['first_name'];?>">
				</div>

				<div class="form-group">
					<label for="lname">Last Name</label>
					<input type="text" name="lname" class="form-control" value="<?php echo $user_details[0]['last_name'];?>">
				</div>

				<div class="form-group">
					<label for="email">Email</label>
					<input type="text" name="email" class="form-control" value="<?php echo $user_details[0]['email'];?>">
				</div>

				<input type="submit" class="btn btn-primary" value="Update Info" name="update_details" id="save_details">

				<?php 
					if(!empty($message)){
				?>
					<div class="alert alert-success">
						<p><?php echo $message;?></p>
					</div>

				<?php 
				}else{
					?>
				
					<?php 
						if(!empty($errors)){
							echo '<div class="alert alert-danger">';
							foreach ($errors as $e) {
								echo '<p>'.$e.'</p>';
							}
							echo '</div>';
						}
						
					?>
				
				<?php

				}
				?>

			</form>
		</div>

		<div class="col-sm-6">
			<h4>Change Password</h4>
			<form action="settings.php" method="post" class="form">
				<div class="form-group">
					<label for="old_password">Current Password</label>
					<input type="password" name="old_password" class="form-control">
				</div>

				<div class="form-group">
					<label for="new_password1">New Password</label>
					<input type="password" name="new_password1" class="form-control" >
				</div>

				<div class="form-group">
					<label for="new_password2">Confirm New Password</label>
					<input type="password" name="new_password2" class="form-control" >
				</div>
				<input type="submit" class="btn btn-primary" value="Update Password" name="update_password" id="update_password">


				<?php 
					if(!empty($message2)){
				?>
					<div class="alert alert-success">
						<p><?php echo $message2;?></p>
					</div>

				<?php 
				}else{
					?>
				
					<?php 
						if(!empty($errors2)){
							echo '<div class="alert alert-danger">';
							foreach ($errors2 as $e) {
								echo '<p>'.$e.'</p>';
							}

							echo '</div>';
						}
						
					?>
				
				<?php

				}
				?>
			</form>
		</div>
	</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script src="js/site.js"></script>

</body>
</html>