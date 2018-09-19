<?php 
	
	class NOTIFICATION{

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

		public function getUnreadNumber(){
			$userLoggedIn = $this->user_obj->getUsername();
			$sql = "SELECT COUNT(*) FROM notifications WHERE viewed=? AND user_to=? AND opened=?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([0,$userLoggedIn,0]);

			return $stmt->fetchColumn();
			// return count($unread);
		}

		public function getNotifications($userLoggedIn){
			$sql = "SELECT * FROM notifications WHERE user_to=? and viewed=? ORDER by id DESC";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([$userLoggedIn, 0]);

			$notifications = $stmt->fetchAll();

			foreach ($notifications as $not => &$value) {
				$post = new POST($userLoggedIn);
				$time_message = $post->time_message($value['datetime']);
				$value['datetime']=$time_message;
			}

			return $notifications;
			
		}

		public function insertNotification($post_id, $user_to, $type){
			$userLoggedIn = $this->user_obj->getUsername();
			$userLoggedInName = $this->user_obj->getuserFullName();
			$date_time = date('Y-m-d H:i:s');

			$link = "post.php?id=". $post_id;

			switch($type){
				case 'comment':
					$message = " commented on your post";
					break;
				case 'like':
					$message = " liked your post";
					break;
				case 'profile_post':
					$message = " posted on your profile";
					break;
				case 'comment_non_owner':
					$message = " commented on a post your on";
					break;
				case 'profile_comment':
					$message = " commented on your profile post";
					break;
			}

			$sql = "INSERT INTO notifications VALUES(?,?,?,?,?,?,?,?,?)";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(['', $type, $user_to, $userLoggedIn, $message, $link, $date_time, 0 ,0]);

		}

	}

?>