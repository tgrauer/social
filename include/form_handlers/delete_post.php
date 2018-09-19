<?php 
	session_start();
	require_once '../../include/db.php';
	require_once '../../include/classes/User.php';
	require_once '../../include/classes/Post.php';	

	if(isset($_GET['post_id'])){
		$post_id = $_GET['post_id'];
	}

	if(isset($_POST['result'])){
		if($_POST['result']=='true'){
			$userLoggedIn=$_SESSION['username'];
			$sql ="UPDATE posts SET deleted =? WHERE id=?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([1, $post_id]);

			$sql = "SELECT COUNT(*) FROM posts WHERE added_by =? AND deleted=?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([$userLoggedIn, 0]);
			$cnt = $stmt->fetchColumn();

			$sql ="UPDATE users SET num_posts =? WHERE username=?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([$cnt, $userLoggedIn]);

		}
	}
?>