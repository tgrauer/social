<?php 
	session_start();
	ob_start();
	include 'include/header.php';

	if(isset($_GET['q'])){
		$query = $_GET['q'];
	}else{
		$query='';
	}
	// $query='j';

	if(isset($_GET['type'])){
		$type = $_GET['type'];
	}else{
		$type='name';
	}

	echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';

?>

<div class="container main">
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2 panel p20 requests">
			<?php 
				if($query == ''){
					echo 'You must enter something in the search box.';
				}else{

					if($type=='username'){
						$sql = "SELECT * FROM users WHERE username LIKE '$query' AND user_closed = 0 AND username !='$userLoggedIn'";
					}else{
						$names = explode(' ', $query);
						if (count($names) == 3) {
							$sql = "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[2]%') AND user_closed = 0 AND username !='$userLoggedIn' ";
						}else if (count($names) == 2){
							$sql = "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed = 0 AND username !='$userLoggedIn'";
						}else{
							$sql = "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed = 0 AND username !='$userLoggedIn'";
						}
					}

					$stmt = $pdo->prepare($sql);
					$stmt->execute();
					$returnedUsers =$stmt->fetchAll();

					if(empty($returnedUsers)){
						echo '<h4 class="mb20">We can\'t find anyone with a '. $type .' like '. $query.'</h4>';
					}else{
						
						echo '<h4 class="mb20">'.count($returnedUsers) .' results found:</h4>';

						foreach ($returnedUsers as $u) {
							$user_obj = new USER($userLoggedIn);
							$button ='';
							$mutual_friends='';

							if($userLoggedIn != $u['username']){
								if($user_obj->isFriends($u['username'])){
									$button = '<input type="submit" name="'.$u['username'].'" class="btn btn-sm btn-danger" value="Remove Friend"/>';
									$mutual_friends = $user_obj->getMutualFriends($u['username']) . " friends in common";
								}else if($user_obj->didReceiveRequest($u['username'])){
									$button = '<input type="submit" name="'.$u['username'].'" class="btn btn-sm btn-info" value="Respond to Request"/>';
									$mutual_friends = $user_obj->getMutualFriends($u['username']) . " friends in common";
								}else if($user_obj->didSendRequest($u['username'])){
									$button = '<input type="submit" class="btn btn-sm btn-default" name="'.$u['username'].'" value="Cancel Request"/>';
									$mutual_friends = $user_obj->getMutualFriends($u['username']) . " friends in common";
								}else{
									$button = '<input type="submit" name="'.$u['username'].'" class="btn btn-sm btn-primary" value="Add Friend"/>';
									$mutual_friends = $user_obj->getMutualFriends($u['username']) . " friends in common";
								}

								if(isset($_POST[$u['username']])){
									if($user_obj->isFriends($u['username'])){
										$user_obj->removeFriend($u['username']);
										header("Location: search.php?q=".$query);										
									}else if($user_obj->didReceiveRequest($u['username'])){
										header("Location: ".$u['username']);										
									}else if ($user_obj->didSendRequest($u['username'])) {
										$user_obj->cancelRequest($u['username']);
										header("Location: search.php?q=".$query);										
									}else{
										$user_obj->sendRequest($u['username']);
										header("Location: search.php?q=".$query);
										
									}
								}
							}

							echo '<div class="search_result row">
									<div class="profile_pic col-sm-4">
										<a href="'.$u['username'].'"><img src="'.$u['profile_pic'].'" class="img-responsive profile_pic"/></a>
									</div>

									<div class="col-sm-4">
										<a href="'.$u['username'].'"><b>'.$u['first_name'].' ' . $u['last_name'] .'</b></a>
							 			<p class="grey">'.$mutual_friends.'</p>
									</div>

									<div class="searchPageFriendButtons col-sm-6">
										<form action="" method="POST">
											'.$button.'<br />
										</form>
									</div>
								</div>'
							;
						}
					}
				}					
				
			?>
		</div>
	</div>
</div>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script src="js/bootbox.min.js"></script>
<script src="js/site.js"></script>

</body>
</html>