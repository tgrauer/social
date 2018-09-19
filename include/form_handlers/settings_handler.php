<?php 

	if(isset($_POST['update_details'])){

		$valid=true;
		$errors =[];

		if(!empty($_POST['fname'])){
			$fname = $_POST['fname'];
		}else{
			$error = 'First Name is required';
			array_push($errors, $error);
			$valid=false;
		}

		if(!empty($_POST['lname'])){
			$lname = $_POST['lname'];
		}else{
			$error = 'Last Name is required';
			array_push($errors, $error);
			$valid=false;
		}

		if(!empty($_POST['email'])){
			$email = $_POST['email'];
			$sql = "SELECT * FROM users WHERE email = ?";
			$stmt= $pdo->prepare($sql);
			$stmt->execute([$email]);
			$email_check = $stmt->fetchAll();
			$matched_user= $email_check[0]['username'];

			if($matched_user == '' || $matched_user == $userLoggedIn){
				$sql = "UPDATE users SET first_name=?, last_name =?, email = ? WHERE username = ?";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([$fname, $lname, $email, $userLoggedIn]);
				$message = "User details updated";
				header("Location: settings.php");
			}else{
				$error = "That email is already in use";
				array_push($errors, $error);
				header("Location: settings.php");
			}
		}else{
			$error = 'Email is required';
			array_push($errors, $error);
			$valid=false;
		}
	}

	if(isset($_POST['update_password'])){

		$valid=true;
		$errors2 =[];

		if(!empty($_POST['old_password'])){
			$old_password = strip_tags($_POST['old_password']);
		}else{
			$error2 = 'Current password is required';
			array_push($errors2, $error2);
			$valid=false;
		}

		if(!empty($_POST['new_password1'])){
			$new_password1 = strip_tags($_POST['new_password1']);
		}else{
			$error2 = 'New password is required';
			array_push($errors2, $error2);
			$valid=false;
		}

		if(!empty($_POST['new_password2'])){
			$new_password2 = strip_tags($_POST['new_password2']);
		}else{
			$error2 = 'Confirm new password';
			array_push($errors2, $error2);
			$valid=false;
		}

		$sql = "SELECT password FROM users WHERE username=?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$userLoggedIn]);
		$current_password = $stmt->fetchColumn();

		if(md5($old_password) == $current_password){
			if($new_password1 == $new_password2){
				if(strlen($new_password1) < 8){
					$error2 = 'Your new password must be at least 8 characters';
					array_push($errors2, $error2);
					$valid=false;
				}else{
					$new_password_md5 = md5($new_password1);
					$sql = "UPDATE users SET password = ? WHERE username=?";
					$stmt = $pdo->prepare($sql);
					$stmt->execute([$new_password_md5, $userLoggedIn]);
					$message2 = 'Your password has been updated';
				}
			}else{
				$error2 = 'New Passwords do not match';
				array_push($errors2, $error2);
				$valid=false;
			}
		}else{
			$error2 = 'Your current password is incorrect';
			array_push($errors2, $error2);
			$valid=false;
		}
	}
	
?>