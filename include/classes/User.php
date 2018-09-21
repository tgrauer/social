<?php 
	
	class USER{

		private $db;
		private $user;

		public function __construct($user){
			$servername = "localhost";
			$username = "root";
			$password = "root";
			$dbname='social';
			
			try {
			    $this->db = new PDO("mysql:host=$servername;port=8888;dbname=$dbname", $username, $password);
			    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			    $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

			    $sql = "SELECT * FROM users WHERE username = '$user'";
			    $stmt = $this->db->prepare($sql);
			    $stmt->execute();
			    $user = $stmt->fetch();
			    $this->user= $user;

			}catch(PDOException $e){
			    echo "Connection failed: " . $e->getMessage();
			}
		}

		public function getUsername(){
			return $this->user['username'];
		}

		public function getNumPosts(){
			$username = $this->user['username'];
			$sql = "SELECT num_posts FROM users WHERE username=?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$username]);
			$num_posts= $stmt->fetchColumn();
			return $num_posts;
		}

		public function getuserFullName(){
			$username = $this->user['username'];
			$sql = "SELECT first_name, last_name FROM users WHERE username = '$username'";
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$fullname= $stmt->fetchAll();
			return $fullname[0]['first_name'].' '. $fullname[0]['last_name'];
		}

		public function getProfilePic(){
			$username = $this->user['username'];
			$sql = "SELECT profile_pic FROM users WHERE username = '$username'";
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$fullname= $stmt->fetchAll();
			return $fullname[0]['profile_pic'];
		}

		public function getFriendArray(){
			$username = $this->user['username'];
			$sql = "SELECT friend_array FROM users WHERE username = '$username'";
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$friend_array= $stmt->fetchAll();
			return $friend_array;
		}

		public function isClosed(){
			$username = $this->user['username'];
			$sql = "SELECT user_closed FROM users WHERE username= ?";
			$stmt= $this->db->prepare($sql);
			$stmt->execute([$username]);
			$closed = $stmt->fetchColumn();
			return $closed;
		}

		public function isFriends($username_to_check){

			$usernameComma = ',' . $username_to_check . ',';
			if(strstr($this->user['friend_array'], $usernameComma) || $username_to_check == $this->user['username']){
				return true;
			}else{
				return false;
			}
		}

		public function didReceiveRequest($user_from){
			$user_to = $this->user['username'];
			$sql = "SELECT * FROM friend_requests WHERE user_to=? AND user_from = ?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$user_to, $user_from]);
			$check_request = $stmt->fetchAll();
			if(empty($check_request)){
				return false;
			}else{
				return true;
			}
		}

		public function didSendRequest($user_to){
			$user_from = $this->user['username'];
			$sql = "SELECT * FROM friend_requests WHERE user_to=? AND user_from = ?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$user_to, $user_from]);
			$check_request = $stmt->fetchAll();
			if(empty($check_request)){
				return false;
			}else{
				return true;
			}
		}

		public function removeFriend($user_to_remove){
			$logged_in_user = $this->user['username'];

			$sql = "SELECT friend_array FROM users WHERE username =?";
			$stmt= $this->db->prepare($sql);
			$stmt->execute([$user_to_remove]);
			$friend_array = $stmt->fetchColumn();

			$new_friend_array = str_replace($user_to_remove .',', '', $this->user['friend_array']);
			$sql = "UPDATE users SET friend_array = ? WHERE username = ?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$new_friend_array, $logged_in_user]);

			$new_friend_array = str_replace($this->user['username'] .',', '', $friend_array);
			$sql = "UPDATE users SET friend_array = ? WHERE username = ?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$new_friend_array, $user_to_remove]);
		}

		public function sendRequest($user_to){
			$user_from = $this->user['username'];
			$sql = "SELECT COUNT(*) FROM friend_requests WHERE user_to=? AND user_from=?";
			$stmt= $this->db->prepare($sql);
			$stmt->execute([$user_to, $user_from]);
			$cnt=$stmt->fetchColumn();

			$datetime = date('Y-m-d H:i:s');

			if(!$cnt){
				$sql = "INSERT INTO friend_requests VALUES(?,?,?,?)";
				$stmt= $this->db->prepare($sql);
				$stmt->execute(['', $datetime, $user_to, $user_from]);

				$sql = "INSERT INTO notifications VALUES(?,?,?,?,?,?,?,?,?)";
				$stmt = $this->db->prepare($sql);
				$stmt->execute(['', 'friend_request', $user_to, $user_from, 'sent you a friend request', $user_from, $datetime, 0 ,0]);
			}
		}

		public function cancelRequest($user_to){
			$user_from = $this->user['username'];
			$sql = "DELETE FROM friend_requests WHERE user_to = ? AND user_from=?";
			$stmt= $this->db->prepare($sql);
			$stmt->execute([$user_to, $user_from]);

			$sql = "DELETE FROM notifications WHERE user_to=? AND user_from=? AND type=?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$user_to, $user_from, 'friend_request']);
		}

		public function countFriendRequests($user){
			$sql="SELECT COUNT(*) FROM friend_requests WHERE user_to = ? ORDER BY id desc";
			$stmt=$this->db->prepare($sql);
			$stmt->execute([$user]);
			return $stmt->fetchColumn();
		}
		public function getFriendRequests($userLoggedIn){
			$sql="SELECT * FROM friend_requests WHERE user_to = ? ORDER BY id desc";
			$stmt=$this->db->prepare($sql);
			$stmt->execute([$userLoggedIn]);
			$requests = $stmt->fetchAll();

			foreach ($requests as $req => &$value) {
				$post = new POST($userLoggedIn);
				$time_message = $post->time_message($value['datetime']);
				$value['datetime']=$time_message;
			}

			return $requests;
		}

		public function getUsersFriends($userLoggedIn){
			$sql = "SELECT friend_array FROM users WHERE username = ? ";
			$stmt=$this->db->prepare($sql);
			$stmt->execute([$userLoggedIn]);
			$users_friends = $stmt->fetchAll();
			$users_friends_explode = explode(',', $users_friends[0]['friend_array']);

			$remove_first = array_shift($users_friends_explode);
			$remove_last = array_pop($users_friends_explode);
			sort($users_friends_explode);

			$friends = [];

			$sql = "SELECT * FROM users WHERE username = ?";
			$stmt = $this->db->prepare($sql);

			foreach ($users_friends_explode as $key =>$value) {
				$stmt->execute([$users_friends_explode[$key]]);
				$friend = $stmt->fetchAll();
				array_push($friends, $friend);
			}



			return $friends;
		}

		public function getMutualFriends($user_to_check){
			$mutual_friends = 0;
			$user_array = $this->user['friend_array'];
			$user_array_explode = explode(',', $user_array);

			$sql = "SELECT friend_array FROM users WHERE username = ? ";
			$stmt=$this->db->prepare($sql);
			$stmt->execute([$user_to_check]);
			$user_to_check_array = $stmt->fetchAll();
			$user_to_check_array_explode = explode(',', $user_to_check_array[0]['friend_array']);
			
			foreach ($user_array_explode as $key =>$value) {
				foreach ($user_to_check_array_explode as $j ) {
					if($value == $j && $value!=''){
						$mutual_friends++;
					}
				}
			}

			return $mutual_friends;
		}


	}

?>