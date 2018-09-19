<?php 
	session_start();
	ob_start();
	include 'include/header.php';

	$user_obj = new USER($userLoggedIn);
	
	if(isset($_GET['u'])){
		$u = $_GET['u'];
	}

	$friends = $user_obj->getUsersFriends($u);
	$sql = "SELECT * FROM users WHERE username =?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$u]);
	$user_details= $stmt->fetchAll();
	if(!empty($user_details[0]['friend_array'])){
		$num_friends = substr_count($user_details[0]['friend_array'], ',') -1;
	}else{
		$num_friends=0;
	}

	echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';

?>

<div class="container profile_pg">
	<div class="row">

		<div class="col-sm-3 profile_info">
			<div class="row">
				<?php 

					if($userLoggedIn==$u){echo '<a href="upload.php">';}
					echo '<img src="'.$user_details[0]['profile_pic'].'" alt="" class="img-responsive profile_pic">';
					if($userLoggedIn==$u){echo '</a>';}

					echo '<p class="text-center username">'.$user_details[0]['first_name'] . ' ' . $user_details[0]['last_name'].'</p>';
				?>
				<div class="profile_user_details">						
				
					<?php 
						echo '<p>Friends: '.$num_friends.'</p>';
						echo '<p>Posts: '.$user_details[0]['num_posts'].'</p>';
						echo '<p>Likes: '.$user_details[0]['num_likes'].'</p>';
					?>

					<a class="btn btn-warning mt30" href="<?php echo $userLoggedIn;?>">Back to Profile</a>
				
				</div>
			</div>
		</div>

		<div class="col-sm-8 col-sm-offset-1 panel p20 requests friends main_feed">
			<h3 class="sub_pg_title"><i class="fas fa-user-friends"></i> Friends</h3>
		<?php 

			foreach ($friends as $key ) {
				$user_obj = new USER($userLoggedIn);
				$button ='';
				$mutual_friends='';
				
				if($user_obj->isFriends($key[0]['username'])){
					$button = '<input type="submit" name="'.$key[0]['username'].'" class="btn btn-sm btn-danger" value="Remove"/><a href="messages.php?u='.$key[0]['username'].'" class="btn btn-sm btn-primary mr10 fltrgt">Message</a>';
					$mutual_friends = $user_obj->getMutualFriends($key[0]['username']) . " friends in common";
				}else if($user_obj->didReceiveRequest($key[0]['username'])){
					$button = '<input type="submit" name="'.$key[0]['username'].'" class="btn btn-sm btn-info" value="Respond to Request"/>';
					$mutual_friends = $user_obj->getMutualFriends($key[0]['username']) . " friends in common";
				}else if($user_obj->didSendRequest($key[0]['username'])){
					$button = '<input type="submit" class="btn btn-sm btn-default" name="'.$key[0]['username'].'" value="Cancel Request"/>';
					$mutual_friends = $user_obj->getMutualFriends($key[0]['username']) . " friends in common";
				}else{
					$button = '<input type="submit" name="'.$key[0]['username'].'" class="btn btn-sm btn-primary" value="Add Friend"/>';
					$mutual_friends = $user_obj->getMutualFriends($key[0]['username']) . " friends in common";
				}

				echo '<div class="search_result row">
						<div class="profile_pic col-sm-4">
							<a href="'.$key[0]['username'].'"><img src="'.$key[0]['profile_pic'].'" class="img-responsive profile_pic"/></a>
						</div>

						<div class="col-sm-4">
							<a href="'.$key[0]['username'].'"><b>'.$key[0]['first_name'].' ' . $key[0]['last_name'] .'</b></a>
							';
						if($userLoggedIn !=$key[0]['username']){
							echo '<p class="grey">'.$mutual_friends.'</p>';
						}
				 			
						echo '</div><div class="searchPageFriendButtons col-sm-6">';
						if($userLoggedIn !=$key[0]['username']){
							echo '<form action="" method="POST">
								'.$button.'<br />
							</form>';
						}
							
				echo '</div></div>';
				
				if(isset($_POST[$key[0]['username']])){
					if($user_obj->isFriends($key[0]['username'])){
						$user_obj->removeFriend($key[0]['username']);
						header("Location:".$_SERVER['HTTP_REFERER']);										
					}else if($user_obj->didReceiveRequest($key[0]['username'])){
						header("Location: ".$key[0]['username']);										
					}else if ($user_obj->didSendRequest($key[0]['username'])) {
						$user_obj->cancelRequest($key[0]['username']);
						header("Location:".$_SERVER['HTTP_REFERER']);									
					}else{
						$user_obj->sendRequest($key[0]['username']);
						header("Location:".$_SERVER['HTTP_REFERER']);	
						
					}
				}
			} // end foreach
		?>

		</div>
	</div>
</div>


<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script src="js/bootbox.min.js"></script>
<script src="js/site.js"></script>

</body>
</html>