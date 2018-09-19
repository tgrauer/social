<html>
<head>
	<title></title>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
	<link href="css/main.css" rel="stylesheet">

	<style type="text/css">
	body{
		background: #fff;
	}
	</style>
</head>

<body>

	<?php 
		session_start();
		require_once 'include/db.php';
		include("include/classes/User.php");
		include("include/classes/Post.php");
		include("include/classes/Notification.php");
		
		if(isset($_SESSION['username'])){
			$userLoggedIn=$_SESSION['username'];
			$user=$_SESSION['user'];

		}else{
			header("Location: index.php");
		}

		if(isset($_GET['post_id'])){
			$post_id=$_GET['post_id'];
		}

		$sql = "SELECT likes, added_by FROM posts WHERE id=?";
		$stmt= $pdo->prepare($sql);
		$stmt->execute([$post_id]);
		$likes = $stmt->fetchAll();

		$total_likes = $likes[0]['likes'];
		$user_liked = $likes[0]['added_by'];
		
		$sql = "SELECT * FROM users WHERE username = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$user_liked]);
		$user_details = $stmt->fetchAll();
		$total_user_likes = $user_details[0]['num_likes'];

		if(isset($_POST['like_button'])){
			$total_likes ++;
			$sql ="UPDATE posts SET likes = ? WHERE id=?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([$total_likes, $post_id]);

			$total_user_likes++;
			$sql ="UPDATE users SET num_likes = ? WHERE username=?";
			$stmt= $pdo->prepare($sql);
			$stmt->execute([$total_user_likes, $user_liked]);

			$sql="INSERT into likes VALUES(?,?,?)";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['', $userLoggedIn, $post_id]);
			// $returned_id=$pdo->lastInsertId();

			if($user_liked != 'none'){
				$notification = new NOTIFICATION($userLoggedIn);
				$notification->insertNotification($post_id, $user_liked, 'like');
			}
		}

		if(isset($_POST['unlike_button'])){
			$total_likes --;
			$sql ="UPDATE posts SET likes = ? WHERE id=?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([$total_likes, $post_id]);

			$total_user_likes--;
			$sql ="UPDATE users SET num_likes = ? WHERE username=?";
			$stmt= $pdo->prepare($sql);
			$stmt->execute([$total_user_likes, $user_liked]);

			$sql="DELETE FROM likes WHERE username=? AND post_id=?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([$userLoggedIn, $post_id]);
		}

		if($total_likes==1){
			$total_likes_display=$total_likes.' Like';
		}else{
			$total_likes_display=$total_likes.' Likes';
		}

		$sql = "SELECT * FROM likes WHERE username = ? AND post_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$userLoggedIn, $post_id]);
		$check = $stmt->fetchAll();
		$count = COUNT($check);

		if($count){
			echo '<form class="like_form" action="like.php?post_id='.$post_id.'" method="post">
				<input type="submit" name="unlike_button" value="Unlike"/>
				<div class="like_value">
					'.$total_likes_display.'
				</div>
				</form>
				';
		}else{
			echo '<form class="like_form" action="like.php?post_id='.$post_id.'" method="post">
				<input type="submit" name="like_button" value="Like"/>
				<div class="like_value">
					'.$total_likes_display.'
				</div>
				</form>
				';
		}

	?>
	 

</body>
</html>