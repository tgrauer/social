<?php 
	
	require_once '../../include/classes/User.php';
	require_once '../../include/classes/Post.php';

	$limit = 10;
	$posts = new POST($_REQUEST['userLoggedIn']);
	$posts->load_posts_friends($_REQUEST, $limit);

?>