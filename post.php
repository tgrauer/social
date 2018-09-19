<?php 
	
	session_start();
	include 'include/header.php';

	if(isset($_GET['id'])){
		$post_id = $_GET['id'];
	}else{
		$post_id=0;
	}

	$sql = "SELECT * FROM users WHERE username =?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$userLoggedIn]);
	$user_details= $stmt->fetchAll();
	$num_friends = substr_count($user_details[0]['friend_array'], ',') -1;

	echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';
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
		</div>

		<div class="col-sm-8 col-sm-offset-1 main_feed">
			<div class="posts_area">
				<?php 
					$post = new POST($userLoggedIn);
					$post->getSinglePost($post_id);
				?>
			</div>
		</div>

	</div>
</div>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script src="js/bootbox.min.js"></script>
<script src="js/site.js"></script>

</body>
</html>