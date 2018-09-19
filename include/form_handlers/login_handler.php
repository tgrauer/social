<?php 
	session_start();

	$login_errors=[];

	if(isset($_SESSION['reg_success'])){

		unset($_SESSION['fname']);
		unset($_SESSION['lname']);
		unset($_SESSION['email']);
	}

	if(isset($_POST['login'])){

		if(!empty($_POST['username'])){

		    $username = clean_input(strtolower($_POST['username']));
		    if(strpos($username, '@')){
		    	$username=filter_var($username, FILTER_VALIDATE_EMAIL);
		    }
		    
		    $_SESSION['username']=$username;
		}else{
		    array_push($login_errors, 'Please enter your username or email');
		}

		if(!empty($_POST['password'])){
			$pw=md5($_POST['password']);
		}else{
		    array_push($login_errors, 'Please enter your password');
		}

		if(empty($login_errors)){

			$sql="SELECT * FROM users WHERE (email =? OR username = ?) AND password =?";
			$stmt= $pdo->prepare($sql);
			$stmt->execute([$username, $username, $pw]);
			$login=$stmt->fetchAll();
			
			$user_closed = $login[0]['user_closed'];

			if($user_closed){
				$sql = "UPDATE users SET user_closed=? WHERE username=?";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([0,$login[0]['username']]);

				$sql = "UPDATE posts SET user_closed =? WHERE added_by = ?";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([0, $login[0]['username']]);
			}
			
			if(!empty($login)){
				$_SESSION['user'] = $login[0];
				$_SESSION['username'] = $login[0]['username'];
				header("Location: home.php");
				die();
				
			}else{
				array_push($login_errors, 'Invalid login');
			}
		}
	}

	function clean_input($data) {
	    $data = trim($data);
	    $data = stripslashes($data);
	    $data = htmlspecialchars($data);
	    return $data;
	}


?>