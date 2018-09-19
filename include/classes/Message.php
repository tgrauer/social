<?php 
	
	class MESSAGE{

		private $db;
		private $user_obj;

		public function __construct($user){
			$servername = "localhost";
			$username = "root";
			$password = "root";
			$dbname='social';
			
			try {
			    $this->db = new PDO("mysql:host=$servername;port=8888;dbname=$dbname", $username, $password);
			    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			    $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

			    $this->user_obj = new User($user);

			}catch(PDOException $e){
			    echo "Connection failed: " . $e->getMessage();
			}
		}

		public function getMostRecentUser(){
			$userLoggedIn = $this->user_obj->getUsername();

			$sql = "SELECT user_to, user_from FROM messages WHERE user_to=? OR user_from = ? ORDER BY id DESC LIMIT 1";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$userLoggedIn, $userLoggedIn]);
			$recent_user = $stmt->fetchAll();
			// return $user_to;

			if(empty($recent_user)){
				return false;
			}

			$user_to = $recent_user[0]['user_to'];
			$user_from = $recent_user[0]['user_from'];

			if($user_to != $userLoggedIn){
				return $user_to;
			}else{
				return $user_from;
			}
			
		}

		public function sendMessage($user_to, $body, $date){
			if($body !=''){
				$userLoggedIn = $this->user_obj->getUsername();
			}

			$sql = "INSERT INTO messages VALUES(?,?,?,?,?,?,?,?)";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(['',$user_to, $userLoggedIn, $body, $date, 0, 0, 0]);
		}

		public function getMessages($otherUser){
			$userLoggedIn = $this->user_obj->getUsername();
			$data = '';
			$sql ="UPDATE messages SET opened =? WHERE user_to=? AND user_from=?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([1, $userLoggedIn, $otherUser]);

			$sql ="SELECT * FROM messages WHERE (user_to=? AND user_from=?) OR (user_from=? AND user_to=?)";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$userLoggedIn, $otherUser, $userLoggedIn, $otherUser]);
			$messages = $stmt->fetchAll();

			foreach ($messages as $msg) {
				$user_to = $msg['user_to'];
				$user_from = $msg['user_from'];
				$body = $msg['body'];
				$div_top = ($user_to == $userLoggedIn) ? "<div class='message green'>" : "<div class='message blue'>";
				$data = $data . $div_top . $body . "</div><br /><br />";
			}

			return $data;

		}

		public function getLatestMessage($userLoggedIn, $user2){
			$details_array = array();
			$sql = "SELECT body, user_to, date FROM messages WHERE (user_to =? AND user_from=?) OR (user_to =? AND user_from=?) ORDER BY id DESC LIMIT 1";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$userLoggedIn, $user2, $user2, $userLoggedIn]);
			$msg = $stmt->fetchAll();

			$sent_by = ($msg[0]['user_to'] == $userLoggedIn) ? strtoupper($user2).' said: '  : "You said: ";
			$post = new POST($userLoggedIn);
			$time_message = $post->time_message($msg[0]['date']);

			array_push($details_array, $sent_by);
			array_push($details_array, $msg[0]['body']);
			array_push($details_array, $time_message);

			return $details_array;
		}

		public function getConvos(){
			$userLoggedIn = $this->user_obj->getUsername();
			$return_string = '';
			$convos = array();

			$sql = "SELECT user_to, user_from FROM messages WHERE user_to=? OR user_from = ? ORDER BY id DESC";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$userLoggedIn, $userLoggedIn]);
			$converstaions = $stmt->fetchAll();

			foreach ($converstaions as $convo) {
				$user_to_push = ($convo['user_to'] != $userLoggedIn) ? $convo['user_to'] : $convo['user_from'];
				
				if(!in_array($user_to_push, $convos)){
					array_push($convos, $user_to_push);
				}
			}

			// foreach ($convos as $username) {
			// 	$user_found_obj = new USER($username);
			// 	$latest_message_details = $this->getLatestMessage($userLoggedIn, $username);

			// 	$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
			// 	$split = str_split($latest_message_details[1], 12);
			// 	$split = $split[0] . $dots;

			// 	$return_string .= "<a href='messages.php?u=".$username."'><div class='user_found_messages'>
			// 		<img class='profile_pic' src='".$user_found_obj->getProfilePic()."'/>
			// 		".$user_found_obj->getuserFullName()."
			// 		<span class='timestamp_smaller'>".$latest_message_details[2]."</span>
			// 		<p>".$latest_message_details[0]. $split ."</p>
			// 		</div>
			// 		</a>";
			// }
			
			return $convos;
		}

		public function unreadMessages($userLoggedIn){
			$sql ="SELECT COUNT(*) FROM messages WHERE user_to=? AND opened=?";
			$stmt= $this->db->prepare($sql);
			$stmt->execute([$userLoggedIn, 0]);
			return $stmt->fetchColumn();
		}

	}

?>