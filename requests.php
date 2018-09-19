<?php 
	// phpinfo()
	session_start();
	ob_start();
	include 'include/header.php';
?>

<div class="container main">
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2 panel p20 requests">
			<h2>Friend Requests</h2>
			<?php 
				if(!$cnt_requests){
					echo 'You have no friend requests at this time';
				}else{
					foreach ($requests as $req ) {
						$user_from = $req['user_from'];
						$user_from_obj = new USER($user_from);
						$user_from_friend_array = $user_from_obj->getFriendArray();

						if(isset($_POST['accept_request' . $user_from])){
							$sql ="UPDATE users SET friend_array=CONCAT(friend_array, '$user_from,') WHERE username='$userLoggedIn'";
							$stmt = $pdo->prepare($sql);
							$stmt->execute();

							$sql ="UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$user_from'";
							$stmt = $pdo->prepare($sql);
							$stmt->execute();

							$sql = "DELETE FROM friend_requests WHERE user_to=? AND user_from=?";
							$stmt = $pdo->prepare($sql);
							$stmt->execute([$userLoggedIn, $user_from]);

							$sql = "DELETE FROM notifications WHERE user_to=? AND user_from=?";
							$stmt = $pdo->prepare($sql);
							$stmt->execute([$userLoggedIn, $user_from]);

							header("Location: requests.php");
						}

						if(isset($_POST['ignore_request' . $user_from])){
							$sql = "DELETE FROM friend_requests WHERE user_to=? AND user_from=?";
							$stmt = $pdo->prepare($sql);
							$stmt->execute([$userLoggedIn, $user_from]);

							$sql = "DELETE FROM notifications WHERE user_to=? AND user_from=?";
							$stmt = $pdo->prepare($sql);
							$stmt->execute([$userLoggedIn, $user_from]);
							
							header("Location: requests.php");
						}

						echo '<div class="request row">';
							echo '<div class="col-sm-2 col-xs-4">';
							echo '<img class="profile_pic" src="'.$user_from_obj->getProfilePic().'"/>';
							echo '</div>';
							echo '<div class="col-sm-6 col-xs-8">';
							echo '<p><b><a href="'.$user_from.'">'.$user_from_obj->getuserFullName() . '</a></b> sent you a friend request</p>';	
							echo '<span class="timestamp_smaller">' . $req["datetime"].'</span>';					
							echo '</div>';
							echo '<div class="col-sm-4 col-xs-8">';
							echo '<form action="requests.php" method="post">';
							echo '<input type="submit" name="accept_request'.$user_from.'" value="Accept" class="btn btn-sm btn-primary">';
							echo '<input type="submit" name="ignore_request'.$user_from.'" value="Ignore" class="btn btn-sm btn-danger">';
							echo '</form>';
							echo '</div>';
						echo '</div>';
					}
				}

			?>


		</div>
	</div>
</div>


 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
 <script src="js/site.js"></script>

</body>
 </html>