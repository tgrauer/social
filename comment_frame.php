<html>
<head>
	<title></title>
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
	<link href="css/main.css" rel="stylesheet">
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
	?>

	<script>
		function toggle(){
			var element = document.getElementById("comment_section");
			if(element.style.display =='block'){
				element.style.display='none';
			}else{
				element.style.display ='block';
			}
		}
	</script>

	<?php 
		if(isset($_GET['post_id'])){
			$post_id=$_GET['post_id'];
		}

		$sql ="SELECT added_by, user_to FROM posts WHERE id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$post_id]);
		$posted_toby = $stmt->fetchAll();
		$user_to = $posted_toby[0]['user_to'];
		$posted_to = $posted_toby[0]['added_by'];

		if(isset($_POST['postComment'.$post_id])){
			$post_body = $_POST['post_body'];
			$date_time_now = date('Y-m-d H:i:s');

			$sql = "INSERT INTO comments VALUES(?,?,?,?,?,?,?)";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['', $post_body, $userLoggedIn, $posted_toby[0]['added_by'], $date_time_now, 'no', $post_id]);

			if($posted_to !=$userLoggedIn){
				$notification = new NOTIFICATION($userLoggedIn);
				$notification->insertNotification($post_id, $posted_to, 'comment');
			}

			if($user_to != 'none' && $user_to != $userLoggedIn){
				$notification = new NOTIFICATION($userLoggedIn);
				$notification->insertNotification($post_id, $user_to, 'profile_comment');
			}

			$sql = "SELECT * FROM comments WHERE post_id =?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([$post_id]);
			$get_commenters = $stmt->fetchAll();
			$notified_users = array();

			foreach ($get_commenters as $commenter) {
				if($commenter['posted_by'] != $posted_to && $commenter['posted_by'] != $user_to && $commenter['posted_by'] != $userLoggedIn && !in_array($commenter['posted_by'], $notified_users)){
					$notification = new NOTIFICATION($userLoggedIn);
					$notification->insertNotification($post_id, $commenter['posted_by'], 'comment_non_owner');
					array_push($notified_users, $commenter['posted_by']);
					// break;
				}
			}
		}
	?>
	
	<form action="comment_frame.php?post_id=<?php echo $post_id;?>" id="comment_form" name="postComment<?php echo $post_id;?>" method="post">
		<div class="form-group">
			<textarea name="post_body" id="post_body" cols="30" rows="10" class="form-control"></textarea>
			<input type="submit" class="btn btn-primary" name="postComment<?php echo $post_id;?>" value="Post">
		</div>
	</form>

	<?php 
		$sql = "SELECT * FROM comments WHERE post_id =? ORDER BY id DESC";
		$stmt=$pdo->prepare($sql);
		$stmt->execute([$post_id]);
		$get_comments = $stmt->fetchAll();

		foreach ($get_comments as $comment ) {
			$comment_body = $comment['post_body'];
			$posted_to = $comment['posted_to'];
			$posted_by = $comment['posted_by'];
			$date_added = $comment['date_added'];
			$removed = $comment['removed'];
			
			$post = new POST($userLoggedIn);
			$time_message = $post->time_message($date_added);
			
			$user_obj = new USER($posted_by);

			?>

			<div class="comment_section row">
				<div class="col-xs-2 col-sm-4">
					<a href="<?php echo $posted_by;?>" target="_parent"><img class="comment_profile_pic" src="<?php echo $user_obj->getProfilePic();?>"/></a>
				</div>
				<div class="col-xs-10">
				<a href="<?php echo $posted_by;?>" target="_parent"><b><?php echo $user_obj->getuserFullName();?></b></a>
				<?php 
					echo $time_message;

					echo '<p class="comment_body">'.$comment_body.'</p>';
				?>
				</div>

			</div>
			<?php 
		}

		if(!count($get_comments)){
			echo '<p class="text-center">No Comments to show!</p>';
		}
		
	?>
	
</body>
</html>