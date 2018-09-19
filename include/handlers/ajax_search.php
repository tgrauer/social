<?php 

	require_once '../../include/db.php';
	require_once '../../include/classes/User.php';
	
	$query = $_POST['query'];
	$userLoggedIn = $_POST['userLoggedIn'];

	$names = explode(' ', $query);

	if(strpos($query, '_') !== false){
		$sql = "SELECT * FROM users WHERE username LIKE '$query' AND user_closed = 0 AND username !='$userLoggedIn' LIMIT 8";
		
	}elseif (count($names) ==2) {
		$sql = "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed = 0 AND username !='$userLoggedIn' LIMIT 8";
	}else{
		$sql = "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed = 0 AND username !='$userLoggedIn' LIMIT 8";
	}

	if($query != ''){
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$usersReturned =$stmt->fetchAll();

		foreach ($usersReturned as $u) {
			$user = new USER($userLoggedIn);

			if($u['username'] != $userLoggedIn ){
				$mutual_friends = $user->getMutualFriends($u['username']) . " friends in common";
			}else{
				$mutual_friends='';
			}

			echo '<div class="result_display">
				<a href="'.$u['username'].'">
					<div class="liveSearchProfilePic">
						<img src="'.$u['profile_pic'].'" class="img-responsive profile_pic"/>
					</div>

					<div class="liveSearchText">
						<h5>'.$u['first_name'].' ' . $u['last_name'] .'</h5>
						<p class="grey">'.$mutual_friends.'</p>
					</div>
				</a>
			</div>';
		}
		if(!empty($usersReturned) && count($usersReturned)>1){
			echo '<a class="viewAll" href="search.php?q='.$query.'">View All</a>';
		}else if(empty($usersReturned)){
			echo '<p class="p15">No users found</p>';
		}
		
	}


?>