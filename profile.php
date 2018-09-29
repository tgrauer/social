<?php 

	session_start();
	include 'include/header.php';
	
	if(isset($_GET['profile_username'])){
		
		$username = $_GET['profile_username'];

		$sql = "SELECT * FROM users WHERE username =?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$username]);
		$user_details= $stmt->fetchAll();
		if(!empty($user_details[0]['friend_array'])){
			$num_friends = substr_count($user_details[0]['friend_array'], ',') -1;
		}else{
			$num_friends=0;
		}
	}

	if(isset($_POST['remove_friend'])){
		$user= new USER($userLoggedIn);
		$user->removeFriend($username);
	}

	if(isset($_POST['add_friend'])){
		$user= new USER($userLoggedIn);
		$user->sendRequest($username);
		header("Location: ".$user_from);
	}

	if(isset($_POST['cancel_request'])){
		$user= new USER($userLoggedIn);
		$user->cancelRequest($username);
		header("Location: ".$user_from);
	}

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

		header("Location: ".$user_from);
	}
	
	if(isset($_POST['ignore_request' . $user_from])){
		$sql = "DELETE FROM notifications WHERE user_to=? AND user_from=?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$userLoggedIn, $user_from]);

		$sql2 = "DELETE FROM friend_requests WHERE user_to=? AND user_from=?";
		$stmt2 = $pdo->prepare($sql2);
		$stmt2->execute([$userLoggedIn, $user_from]);

		header("Location: ".$user_from);

	}

	$sql = 'SELECT COUNT(*), id FROM notifications WHERE link =? AND user_from=? AND user_to=?';
	$stmt=$pdo->prepare($sql);
	$stmt->execute([$username, $username, $userLoggedIn]);
	$not_exists = $stmt->fetchAll();
	$not_id = $not_exists[0]['id'];
	$not_count = $not_exists[0]['COUNT(*)'];

	if($not_count){
		$sql = "UPDATE notifications SET opened =? WHERE id=?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([1, $not_id]);
		header('Location: '.$username);
	}

	echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';

 ?>

	<div class="container profile_pg">
		<div class="row">
			<div class="col-sm-3 profile_info">
				<div class="row">
					<?php 
						if($userLoggedIn==$username){echo '<a href="upload.php">';}
						echo '<img src="'.$user_details[0]['profile_pic'].'" alt="" class="img-responsive profile_pic">';
						if($userLoggedIn==$username){echo '</a>';}

						echo '<p class="text-center username">'.$user_details[0]['first_name'] . ' ' . $user_details[0]['last_name'].'</p>';
					?>
					<div class="profile_user_details">						
					
						<?php 
							echo '<p><a class="white_link" href="friends.php?u='.$username.'">Friends: '.$num_friends.'</a></p>';
							echo '<p>Posts: '.$user_details[0]['num_posts'].'</p>';
							echo '<p>Likes: '.$user_details[0]['num_likes'].'</p>';
							$logged_in_user_obj = new USER($userLoggedIn);

							if($userLoggedIn != $username){
								echo '<p>Mutual Friends: '.$logged_in_user_obj->getMutualFriends($username).'</p>';
							}
						?>

						<!-- Button trigger modal -->
						<input type="submit" value="Post Something" class="btn btn-info mt30" data-toggle="modal" data-target="#post_form">

						<form action="<?php echo $username;?>" method="post">
							<?php 
								$profile_user_obj = new USER($username);
								if($profile_user_obj->isClosed()){
									header("Location: user_closed.php");
								}

								if($userLoggedIn != $username){
									if($logged_in_user_obj->isFriends($username)){
										echo '<input type="submit" name="remove_friend" class="btn btn-danger" value="Remove Friend"/>';
									}elseif ($logged_in_user_obj->didReceiveRequest($username)) {
										echo '<input type="submit" name="accept_request'.$user_from.'" value="Accept" class="btn btn-sm btn-primary mr10">';
										echo '<input type="submit" name="ignore_request'.$user_from.'" value="Ignore" class="btn btn-sm btn-danger">';
									}elseif ($logged_in_user_obj->didSendRequest($username)) {
										echo '<input type="submit" name="cancel_request" class="btn btn-warning" value="Cancel Request"/>';
									}else{
										echo '<input type="submit" name="add_friend" class="btn btn-success" value="Add Friend"/>';
									}
								}
							?>

						</form>
					
					</div>
				</div>
			</div>

			<div class="col-sm-8 col-sm-offset-1 main_feed">
				<div class="posts_area"></div>
				<img id="loading" src="img/ajax-loader.gif" />

			</div>

			<script>
				var userLoggedIn ='<?php echo $userLoggedIn;?>';
				var profile_username = '<?php echo $username;?>';

				$(document).ready(function(){
					$('#loading').show();

					$.ajax({
						url:'include/handlers/ajax_load_profile_posts.php',
						type:'POST',
						data:'page=1&userLoggedIn='+userLoggedIn+'&profile_username='+profile_username,
						cache:false,
						success:function(data){
							$('#loading').hide();
							$('.posts_area').html(data);
						}
					});

					$(window).scroll(function(){

						var page = $('.posts_area').find('.nextPage').val();
						var noMorePosts = $('.posts_area').find('.noMorePosts').val();

						if ($(window).scrollTop() >= ($(document).height() - $(window).height())  && noMorePosts == 'false'){
							$('#loading').show();
							
							var ajaxReq = $.ajax({
								url:'include/handlers/ajax_load_profile_posts.php',
								type:'POST',
								data:'page='+page+'&userLoggedIn='+userLoggedIn+'&profile_username='+profile_username,
								cache:false,
								async: false,
								success:function(response){
									$('.nextPage').remove();
									$('.noMorePosts').remove();
									$('#loading').hide();
									$('.posts_area').append(response);
								}
							});
						}// end if

						return false;
					});
				});

			</script>

			<!-- Modal -->
			<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="myModalLabel">Post Something</h4>
						</div>
						<div class="modal-body">
							<p>This will appear on the user's profile and also their newsfeed for your friends to see!</p>

							<form action="" method="POST" class="profile_post">
								<div class="form-group">
									<textarea name="post_body" class="form-control" cols="30" rows="10"></textarea>
									<input type="hidden" name="user_from" value="<?php echo $userLoggedIn;?>">
									<input type="hidden" name="user_to" value="<?php echo $username;?>">
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
 <script src="js/bootbox.min.js"></script>
 <script src="js/site.js"></script>

 </body>
 </html>