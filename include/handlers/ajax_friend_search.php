<?php 
	require_once '../../include/db.php';
	require_once '../../include/classes/Message.php';
	require_once '../../include/classes/User.php';

	$query = $_POST['query'];
	$userLoggedIn = $_POST['userLoggedIn'];
	$names = explode(' ', $query);

	if(strlen($query)){
		if(strpos($query, '_') !== false){
			$sql = "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed=0 LIMIT 8";
		}else if (count($names)==2) {
			$sql = "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND user_closed=0 LIMIT 8";
		}else{
			$sql = "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND user_closed=0 LIMIT 8";
		}

		$stmt = $pdo->prepare($sql);
		$stmt->execute();
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
					echo '<div class="result_display">
						<a href="messages.php?u='.$u['username'].'">
							<div class="live_search_profile_pic">
								<img src="'.$u['profile_pic'].'" />
							</div>
							<div class="live_search_text">
								<p class="search_name">'.$u['first_name']. ' ' . $u['last_name'].'</p>
								<p>'.$u['username'].'</p>
								<p class="grey">'.$mutual_friends .'</p>
							</div>
						</a>
					</div>';
				}
			}
		}
	}
	
?>