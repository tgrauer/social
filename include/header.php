<?php 

	require_once 'include/db.php';
	require_once 'include/classes/User.php';
	require_once 'include/classes/Post.php';	
	require_once 'include/classes/Message.php';
	require_once 'include/classes/Notification.php';

	if(isset($_SESSION['username'])){
		$userLoggedIn=$_SESSION['username'];
		$user=$_SESSION['user'];

	}else{
		header("Location: index.php");
	}

	$friend_obj = new USER($userLoggedIn);
	$cnt_requests = $friend_obj->countFriendRequests($userLoggedIn);
	$requests = $friend_obj->getFriendRequests($userLoggedIn);

	$notification = new NOTIFICATION($userLoggedIn);
	$cnt_notifications = $notification->getUnreadNumber();
	$notifications  = $notification->getNotifications($userLoggedIn);
	
	$message_obj = new MESSAGE($userLoggedIn);
	$unreadMessagesCnt = $message_obj->unreadMessages($userLoggedIn);
	
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
    <link rel="stylesheet" href="css/jquery.Jcrop.css" type="text/css" />
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">



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
</head>

<body>
	
	<header>
	    <nav class="navbar navbar-default navbar-fixed-top">
	        <div class="container">

	            <ul class="nav navbar-nav navbar-left">
	                <li>
	                	<?php 
	                		if(isset($_SESSION['username'])){
	                			echo '<a class="logo navbar-brand" href="home.php">SnapMyFaceYouTwit</a>';
	                		}else{
	                			echo '<a class="logo navbar-brand" href="index.php">SnapMyFaceYouTwit</a>';
	                		}
	                	?>
	                    
	                </li>
	            </ul>

	            <div id="navbar" class="navbar-collapse collapse">
	            	
	                <ul class="nav navbar-nav">
							
	                    <li><a href="#" ><i class="fa fa-envelope" aria-hidden="true" role="button" tabindex="0" data-container="body" data-toggle="popover" data-placement="bottom"  data-popover-content="#messages" data-trigger="click"></i><?php if($unreadMessagesCnt){echo '<span class="badge">'.$unreadMessagesCnt.'</span>';}?></a></li>

	                    <li><a href="#"><i class="fa fa-bell" aria-hidden="true" role="button" tabindex="0" data-container="body" data-toggle="popover" data-placement="bottom" data-popover-content="#notification" data-trigger="click"></i><?php if($cnt_notifications){echo '<span class="badge">'.$cnt_notifications.'</span>';}?></a></li>

	                    <li><a href="#"><i class="fa fa-users" aria-hidden="true" role="button" tabindex="0" data-container="body" data-toggle="popover" data-placement="bottom" data-popover-content="#friendrequests" data-trigger="click"></i><?php if($cnt_requests){echo '<span class="badge">'.$cnt_requests.'</span>';}?></a></li>

	                    <li ><a class="avatar" href="#" ><img src="<?php echo $_SESSION['user']['profile_pic'];?>" alt="" role="button" tabindex="0" data-container="body" data-toggle="popover" data-placement="bottom" data-popover-content="#profile" data-trigger="click" > </a></li>
	                </ul>

	                	<div class="navbar-right">
	                		<form action="search.php" method="GET" class="search-form" role="search" id="demo-2" name="search_form">
	                			<div class="form-group pull-right" id="search">
	                				<input type="search" class="form-control" placeholder="Search" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" autocomplete="off" id="search_text_input">
	                			</div>
	                		</form>
							
	                		<div class="search_results" id="search_results"><div class="search_results_footer_empty"></div></div>
	                			
	                	</div>
	            </div>
	        </div>
	    </nav>
	</header>

	<!-- Begin Messages Popover in Nav -->
	<div class="hidden" id="messages" class="popover">
	    <div class="popover-body">

	        <ul id="message_div" class="messages popover_messages">

	        	<?php 

					$convos = $message_obj->getConvos();
					$return_string = '';
					$i=0;

					if(!$convos){
						echo '<p class="p15">You have no messages at this time.</p>';
						echo '<li class="viewAll"><a href="messages.php">Start New Message</a></li>';
					}else{

					
						foreach ($convos as $username) {
							if($i==5){break;}
							$user_found_obj = new USER($username);
							$latest_message_details = $message_obj->getLatestMessage($userLoggedIn, $username);

							$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
							$split = str_split($latest_message_details[1], 12);
							$split = $split[0] . $dots;

							$return_string .= "<a href='messages.php?u=".$username."'><li><img class='profile_pic' src='".$user_found_obj->getProfilePic()."'/> <i class='fa fa-comments' aria-hidden='true'></i><span class='message_username'>".$user_found_obj->getuserFullName() ."</span> <span class='timestamp_smaller'>" . $latest_message_details[2]."</span><p>".$latest_message_details[0]. $split ."</p></li></a>";
							$i++;

						}

						$return_string .= '<li class="viewAll"><a href="messages.php">View All</a></li>';
					}

					echo $return_string;

				?>

	            
	        </ul>

	    </div>
	</div>
	<!-- End Messages Popover in Nav -->

	<!-- Begin notification Popover in Nav -->
	<div class="hidden" id="notification" class="popover">
	    <div class="popover-body">
	        <ul id="notifications_div" class="messages popover_messages">

	        	<?php  

		        	if(!$notifications){
		        		echo '<p class="p15">You have no notifications at this time.</p>';
		        	}else{
		        		foreach ($notifications as $not) {
		        			$user_from = $not['user_from'];
		        			$user_from_obj = new USER($user_from);

		        			switch($not['type']){
		        				case 'comment':
		        					$icon = " fa-comment";
		        					break;
		        				case 'like':
		        					$icon = " fa-thumbs-up";
		        					break;
		        				case 'profile_post':
		        					$icon = " fa-chalkboard-teacher";
		        					break;
		        				case 'comment_non_owner':
		        					$icon = " fa-comments";
		        					break;
		        				case 'profile_comment':
		        					$icon = " fa-comments";
		        					break;
		        				case 'friend_request':
		        					$icon = " fa-user-plus";
		        					break;
		        			}

		        			if($not['opened']){
		        				$opened='opened';
		        			}else{
		        				$opened='';
		        			}

		        			echo "<a href='".$not['link']."'><li class='".$opened."'><img class='profile_pic' src='".$user_from_obj->getProfilePic()."'/> <i class='fa ".$icon."' aria-hidden='true'></i><span class='message_username'>".$user_from_obj->getuserFullName() ."</span> <span class='timestamp_smaller'>" . $not['datetime']."</span><p>".$not['message']."</p></li><p></p></a>";
		        		}
		        	}
	        	?>
				
	        </ul>
	    </div>
	</div>
	<!-- End notification Popover in Nav -->

	<!-- Begin Friend Requests Popover in Nav -->
	<div class="hidden" id="friendrequests" class="popover">
	    <div class="popover-body">

	        <ul id="friendrequests_div" class="messages popover_messages">

			<?php 
				if(!$cnt_requests){
					echo '<p class="p15">You have no friend requests at this time.</p>';
				}else{

					foreach ($requests as $req ) {
						$user_from = $req['user_from'];
						$user_from_obj = new USER($user_from);
						$user_from_friend_array = $user_from_obj->getFriendArray();

						// if(isset($_POST['accept_request' . $user_from])){
						// 	$sql ="UPDATE users SET friend_array=CONCAT(friend_array, '$user_from,') WHERE username='$userLoggedIn'";
						// 	$stmt = $pdo->prepare($sql);
						// 	$stmt->execute();

						// 	$sql ="UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$user_from'";
						// 	$stmt = $pdo->prepare($sql);
						// 	$stmt->execute();

						// 	$sql = "DELETE FROM friend_requests WHERE user_to=? AND user_from=?";
						// 	$stmt = $pdo->prepare($sql);
						// 	$stmt->execute([$userLoggedIn, $user_from]);
						// 	header("Location: requests.php");
						// }

						// if(isset($_POST['ignore_request' . $user_from])){
						// 	$sql = "DELETE FROM friend_requests WHERE user_to=? AND user_from=?";
						// 	$stmt = $pdo->prepare($sql);
						// 	$stmt->execute([$userLoggedIn, $user_from]);
						// 	header("Location: requests.php");
						// }

						
						echo "<a href='".$user_from_obj->getUsername()."'><li><img class='profile_pic' src='".$user_from_obj->getProfilePic()."'/> <i class='fa fa-user-plus' aria-hidden='true'></i><span class='message_username'>".$user_from_obj->getuserFullName() ."</span> <span class='timestamp_smaller'>" . $req['datetime']."</span><p></p></li><p></p></a>";
							
					}
					echo '<li class="viewAll"><a href="requests.php">View All</a></li>';
				}

			?>

	            
	        </ul>

	    </div>
	</div>
	<!-- End Friend Requests Popover in Nav -->

	<!-- Begin Search Popover in Nav -->
	<!-- <div class="hidden" id="search" class="popover">
	    <div class="popover-body">
	        <form action="search.php" method="GET" class="form">
	            <div class="input-group input-group-lg">
	                <input type="text" class="form-control" name="q" id="search_text_input" placeholder="Search..." autofocus="autofocus" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn;?>')" autocomplete="off">
	               	<span class="input-group-btn">
           	        	<button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search"></button>
           	      	</span>
	            </div>
	        </form>
	        <div class="search_results"></div>
	        <div class="search_results_footer_empty"></div>
	    </div>
	</div> -->
	<!-- End Search Popover in Nav -->

	<!-- Begin Profile Popover in Nav -->
	<div class="hidden" id="profile" class="popover">
	    <div class="popover-body">
	        <ul class="settings list-group">
	            <li class="list-group-item"><?php echo '<a href="'.$user['username'].'">My Profile';?></a></li>
	            <li class="list-group-item"><a href="settings.php">Settings</a></li>
	            <li class="list-group-item"><a href="logout.php">Log Out</a></li>
	        </ul>
	    </div>
	</div>
	<!-- End Profile Popover in Nav -->

