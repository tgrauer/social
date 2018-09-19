<?php 
	
	require_once '../../include/db.php';
	require_once '../../include/classes/User.php';
	require_once '../../include/classes/Post.php';
	require_once '../../include/classes/Notification.php';	

	$imgName='';
	
	if(isset($_POST['post_body'])){
		$post = new POST($_POST['user_from']);
		$post->submitPost($_POST['post_body'], $_POST['user_to'], $imgName, $_POST['user_from']);
	}
?>