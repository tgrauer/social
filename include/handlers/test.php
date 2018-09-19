<?php 
	require_once '../../include/classes/User.php';
	require_once '../../include/classes/Post.php';
	require_once '../../include/classes/Message.php';

	$query = $_POST['query'];
	$userLoggedIn = $_POST['userLoggedIn'];
	$names = explode(' ', $query);

	if(strpos($query, '_') !== false){
		$sql = "SELECT * FROM users WHERE username LIKE ? AND user_closed=? LIMIT 8";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['$query%', 0]);
	}else if (count($names)==2) {
		$sql = "SELECT * FROM users WHERE (first_name LIKE ? AND last_name LIKE ?) AND user_closed=? LIMIT 8";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['%$names[0]%', '%$names[1]%', 0]);
	}else{
		$sql = "SELECT * FROM users WHERE (first_name LIKE ? OR last_name LIKE ?) AND user_closed=? LIMIT 8";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['%$names[0]%', '%$names[1]%', 0]);
	}

	$usersReturned = $stmt->fetchAll();

	if(!empty($usersReturned)){
		foreach ($usersReturned as $u) {
			$user = new USER($userLoggedIn);
			if($u['username'] != $userLoggedIn){
				$mutual_friends = $user->getMutualFriends($u['username']) . " friends in common";
			}else{
				$mutual_friends='';
			}

			if($user->isFriends($u['username'])){
				echo '
				<div class="result_display">
					<a href="messages.php?u='.$u['username'].'">
						<div class="live_search_profile_pic">
							<img src="'.$u['username'].'" />
						</div>
						<div class="live_search_text">
							'.$u['first_name']. ' ' . $u['last_name'].'
							<p>'.$u['username'].'</p>
							<p>'.$mutual_friends . ' Mutual Friends'.'</p>
						</div>
					</a>
				</div>';
			}
		}
	}
?>