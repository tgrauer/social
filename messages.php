<?php 

	session_start();

	include 'include/header.php';

	$message_obj = new MESSAGE($userLoggedIn);

	$sql = "SELECT * FROM users WHERE username =?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$userLoggedIn]);
	$user_details= $stmt->fetchAll();
	$num_friends = substr_count($user_details[0]['friend_array'], ',') -1;
	
	if(isset($_GET['u'])){
		$user_to = $_GET['u'];
	}else{
		$user_to = $message_obj->getMostRecentUser();
		if(!$user_to){
			$user_to='new';
		}
	}

	if($user_to !='new'){
		$user_to_obj = new USER($user_to);
	}

	if(isset($_POST['post_message'])){
		$body = $_POST['message_body'];
		$date = date('Y-m-d H:i:s');
		$message_obj->sendMessage($user_to, $body, $date);
	}
?>

<div class="container main">
	
	<div class="row">
		
		<div class="col-sm-3 user_details">
			<div class="row">
				<div class="col-sm-5">
					<?php 
						echo '<a href="'.$user['username'].'"><img src="'.$user_details[0]['profile_pic'].'" alt="" class="img-responsive profile_pic"></a>';
					?>
					
				</div>
				<div class="col-sm-7">
					<?php 
						echo '<a href="'.$user['username'].'">'.$user['first_name'] . ' ' . $user['last_name'].'</a><br />';
						echo '<p>Friends: '.$num_friends.'</p>';
						echo '<p>Posts: '.$user_details[0]['num_posts'].'</p>';
						echo '<p>Likes: '.$user_details[0]['num_likes'].'</p>';
					?>
				</div>
			</div>

			<div class="conversations">
				<h3>Converstations</h3>
				<div class="loaded_conversations">
					<?php 
						$convos = $message_obj->getConvos();
						$return_string = '';
						foreach ($convos as $username) {
							$user_found_obj = new USER($username);
							$latest_message_details = $message_obj->getLatestMessage($userLoggedIn, $username);

							$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
							$split = str_split($latest_message_details[1], 12);
							$split = $split[0] . $dots;

							$return_string .= "<a href='messages.php?u=".$username."'><div class='user_found_messages'>
								<img class='profile_pic' src='".$user_found_obj->getProfilePic()."'/>
								".$user_found_obj->getuserFullName()."
								<span class='timestamp_smaller'>".$latest_message_details[2]."</span>
								<p>".$latest_message_details[0]. $split ."</p>
								</div>
								</a>";
						}

						echo $return_string;

					?>
				</div>
				<a href="messages.php?u=new" class="btn btn-primary">New Message</a>
			</div>
		</div>

		<div class="col-sm-8 col-sm-offset-1 main_feed convo">
			<?php 

				if($user_to != 'new'){
					echo '<h3 class="mb20">You and <a href="'.$user_to.'">'.$user_to_obj->getuserFullName().'</a></h3>';
					echo '<div class="loaded_messages" id="scroll_messages">';
					echo $message_obj->getMessages($user_to);
					echo '</div>';
				}else{
					echo '<h3>New Message</h3>';
				}
			?>

			<div class="message_post">
				<form action="#" method="POST" class="form">
					<?php 
						if($user_to=='new'){
							echo '<p class="mb20">Select the friend you want to message</p>';
							?> 
							<div class="form-group"><label>TO:</label><input type="text" class="form-control" onkeyup="getUsers(this.value, '<?php echo $userLoggedIn;?>')" name="q" placeholder="Name" autocomplete="off" id="search_text_input" /></div>
							<?php
							echo '<div class="results"></div>';
						}else{
							echo '<div class="form-group mt30"><textarea class="form-control" name="message_body" id="message_area" placeholder="Write your message..."></textarea></div>';
							echo '<input type="submit" class="btn btn-primary" name="post_message" id="message_submit" value="Send" />';
						}
					?>
				</form>
			</div>
			
			<?php 

				if($user_to != 'new'){
			?>
			<script>
				var div = document.getElementById("scroll_messages");
				div.scrollTop = div.scrollHeight;
			</script>
			<?php 
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