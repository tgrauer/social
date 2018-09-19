<?php 

// session_start();

$reg_errors=[];

if(isset($_POST['register'])){
    unset($_SESSION['fname']);
    unset($_SESSION['lname']);
    unset($_SESSION['email']);

    if(!empty($_POST['fname'])){
        $fname = clean_input(ucfirst(strtolower($_POST['fname'])));
        $_SESSION['fname']=$fname;
    }else{
        array_push($reg_errors, 'Please enter your first name');
    }

    if(!empty($_POST['lname'])){
        $lname = clean_input(ucfirst(strtolower($_POST['lname'])));
        $_SESSION['lname']=$lname;
    }else{
        array_push($reg_errors, 'Please enter your last name');
    }

    if(!empty($_POST['email'])){
        $email = clean_input(strtolower($_POST['email']));

        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $email=filter_var($email, FILTER_VALIDATE_EMAIL);
            $_SESSION['email']=$email;
        }else{
            array_push($reg_errors, 'Invalid email address');
        }

        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $stmt =$pdo->prepare($sql);
        $stmt->execute([$email]);
        
        if($email_exists= $stmt->fetchColumn()){
            array_push($reg_errors, "Email address already exists");
        }
       
    }else{
        array_push($reg_errors, 'Please enter your email');
    }

    if(!empty($_POST['password'])){
        
        if(strlen($_POST['password']) < 8 || strlen($_POST['password']) > 15){
            array_push($reg_errors, 'Password must be between 8 and 15 characters');
        }else{
            $pw = clean_input($_POST['password']);
            if(!empty($_POST['conf_password'])){
                $conf_pw = clean_input($_POST['conf_password']);
            }else{
                array_push($reg_errors, 'Please confirm your password');
            }

            if(!empty($conf_pw) && $pw !== $conf_pw){
                array_push($reg_errors, 'Passwords do not match');
            }
        }
    }else{
        array_push($reg_errors, 'Please create a password');
    }

    if(empty($reg_errors)){
        $pw = md5($pw);

        $username = strtolower(substr($fname,0,1)) . strtolower($lname);
        $sql = "SELECT COUNT(*) FROM users WHERE username =?";
        $stmt =$pdo->prepare($sql);
        $stmt->execute([$username]);
        $username_exists=$stmt->fetchColumn();

        $i=0;

        while($username_exists){
            $i++;
            $username = strtolower(substr($fname,0,1)) . strtolower($lname).$i;
            $sql = "SELECT COUNT(*) FROM users WHERE username =?";
            $stmt =$pdo->prepare($sql);
            $stmt->execute([$username]);
            $username_exists=$stmt->fetchColumn();
        }

        $rand = rand(1,16);
        $profile_pic='img/profile_pics/default/default'.$rand.'.png';

        $reg_date = date('Y-m-d');
        $sql = "INSERT INTO users (username, first_name, last_name, email, password, register_date, profile_pic, friend_array)VALUES(?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $fname, $lname, $email, $pw, $reg_date, $profile_pic,',']);

        if($stmt){
            $_SESSION['reg_success']=1;
            unset($_SESSION['fname']);
            unset($_SESSION['lname']);
            unset($_SESSION['email']);
            $_SESSION['username'] =$username;
        }
    }
}


?>