
<?php 
    
    require_once 'include/db.php';
    require_once 'include/form_handlers/login_handler.php';
    require_once 'include/form_handlers/register_handler.php';

?>

<!doctype html>
<html lang=en-us>
<!--[if IE 7]>         <html class="ie7"> <![endif]-->
<!--[if IE 8]>         <html class="ie8"> <![endif]--> 
<!--[if IE]>           <html class="ie"> <![endif]--> 

<head>
    <meta charset=utf-8>
    <title></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

    <!--[if lt IE 9]>
    <script src="js/html5shiv.min.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <!--[if lt IE 8]>
    <link href="css/bootstrap-ie7.css" rel="stylesheet">
    <![endif]-->
    <!--[if IE]>
    <script type="text/javascript" src="js/css3-mediaqueries.js"></script>    
    <![endif]-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
</head>

<body>
     
<div class="home_bg">
    <div class="container">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3 mt100">
                <?php 
                    if(isset($_POST['register']) && !isset($_SESSION['reg_success'])){
                        echo '<script>
                        $(document).ready(function(){
                        $(".login_form").hide();
                        $(".register_form").show();
                        });
                        </script>';
                    }elseif (isset($_SESSION['username'])) {
                         echo '<script>
                        $(document).ready(function(){
                        $(".login_form").show();
                        $(".register_form").hide();
                        });
                        </script>';
                    }
                ?>
                <div class="form_holder">
                    <div class="header">
                        <h1 class="logo">SnapMyFaceYouTwit</h1>
                        <p>Login or Sign Up Below</p>
                    </div>

                    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST" class="form login_form">
                        <?php 

                            if(isset($_SESSION['reg_success'])){
                                echo '<div class="mt0 alert alert-success text-center">';
                                echo 'Thanks for signing up. <br /><b>Login below</b><br />';
                                echo '</div>';
                            }
                        ?>
                        <div class="input-group">
                            <input type="text" class="form-control" name="username" value="<?php if(isset($_SESSION['username'])){echo $_SESSION['username'];}?>" placeholder="Username or Email">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        </div>

                        <div class="input-group">
                            <input type="password" class="form-control" name="password" value="" placeholder="Password">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        </div>

                        <input type="submit" class="btn btn-primary" value="Login" name="login">
                        <a class="fltrgt toggle_logreg" href="#register">Need an account? Register Here</a>
                        <?php 
                            if(!empty($login_errors)){
                                echo '<div class="alert alert-danger">';
                                    foreach ($login_errors as $e) {
                                        echo '<p>'.$e.'</p>';
                                    }
                                echo '</div>';
                            }
                        ?>
                    </form>

                    <form id="#register" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST" class="form register_form">
                        <div class="input-group">
                            <input type="text" class="form-control" name="fname" value="<?php if(isset($_SESSION['fname'])){echo $_SESSION['fname'];}?>" placeholder="First Name">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        </div>

                        <div class="input-group">
                            <input type="text" class="form-control" name="lname" value="<?php if(isset($_SESSION['lname'])){echo $_SESSION['lname'];}?>" placeholder="Last Name">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        </div>

                        <div class="input-group">
                            <input type="text" class="form-control" name="email" value="<?php if(isset($_SESSION['email'])){echo $_SESSION['email'];}?>" placeholder="Email">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                        </div>

                        <div class="input-group">
                            <input type="password" class="form-control" name="password" value="" placeholder="Password">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        </div>

                        <div class="input-group">
                            <input type="password" class="form-control" name="conf_password" placeholder="Confirm Password">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        </div>

                        <input type="submit" class="btn btn-primary" value="Register" name="register">
                        <a class="fltrgt toggle_logreg" href="#login">Back to Login</a>

                        <?php 
                            if(!empty($reg_errors)){
                                echo '<div class="alert alert-danger">';
                                    foreach ($reg_errors as $e) {
                                        echo '<p>'.$e.'</p>';
                                    }
                                echo '</div>';
                            }

                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script src="js/site.js"></script>

</body>
</html>