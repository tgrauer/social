<?php 
	
	session_start();

	unset($_SESSION['fname']);
    unset($_SESSION['lname']);
    unset($_SESSION['email']);
    unset($_SESSION['reg_success']);

	include 'include/header.php';
	$post = new POST($userLoggedIn);

	if(isset($_POST['post'])){

		$uploadOK = 1;
		$imgName = $_FILES['fileToUpload']['name'];
		$errorMsg = "";

		if($imgName != ""){
			$targetDir='img/posts/';
			$imgName = strtolower($imgName);
			$imgName = $targetDir . uniqid() . basename($imgName);
			$imgFileType = pathinfo($imgName, PATHINFO_EXTENSION);

			if($_FILES['fileToUpload']['size'] > 1000){
				$errorMsg = "Sorry your image is too large.";
				$uploadOK=0;
			}

			if(strtolower($imgFileType) != 'jpeg' && strtolower($imgFileType) != 'jpg' && strtolower($imgFileType) != 'png'){
				$errorMsg = "Sorry, only jpeg, jpg and png files are allowed.";
				$uploadOK=0;
			}

			if($uploadOK){
				if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imgName)){

				}else{
					$uploadOK=0;
				}
			}
		}

		if(!empty($imgName) && $uploadOK){
			$post->submitPost($_POST['post_text'],'none', $imgName, $userLoggedIn);
			unset($_POST);
			header('Location:'.$_SERVER['PHP_SELF']);
		}elseif (empty($imgName) && !empty($_POST['post_text'])) {
			$post->submitPost($_POST['post_text'],'none', $imgName, $userLoggedIn);
			unset($_POST);
			header('Location:'.$_SERVER['PHP_SELF']);
		}else{
			$uploaderror= '<div class="alert alert-danger">'.$errorMsg.'</div>';
		}
	}

	$sql = "SELECT * FROM users WHERE username =?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$userLoggedIn]);
	$user_details= $stmt->fetchAll();

	if(!empty($user_details[0]['friend_array'])){
		$num_friends = substr_count($user_details[0]['friend_array'], ',') -1;
	}else{
		$num_friends=0;
	}
	
	echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>';
 ?>


	<div class="container main">
		<div class="row">
			<div class="col-sm-3 user_details">
				<div class="row">
					<div class="col-sm-5">
						<?php 
							echo '<a href="'.$user['username'].'"><img src="'.$user_details[0]['profile_pic'].'" alt="" class="img-responsive profile_pic"></a>';
						?>
						
					</div>
					<div class="col-sm-7">
						<?php 
							echo '<a href="'.$user['username'].'">'.$user['first_name'] . ' ' . $user['last_name'].'</a><br />';
							echo '<p>Friends: '.$num_friends.'</p>';
							echo '<p>Posts: '.$user_details[0]['num_posts'].'</p>';
							echo '<p>Likes: '.$user_details[0]['num_likes'].'</p>';
						?>
					</div>
				</div>
			</div>

			<div class="col-sm-8 col-sm-offset-1 main_feed">
				<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST" class="form post_form" enctype="multipart/form-data">
					<textarea name="post_text" id="post_text" cols="30" rows="7" class="form-control" placeholder="Whats on your mind?"></textarea><br />
					
					<div class="form-group col-sm-6">
						<input type="file" name="fileToUpload" id="fileToUpload" multiple="multiple">
					</div>

					<input type="submit" class="btn btn-primary btn-md fltrgt" name="post" value="Post">
				</form>

				<?php 
					if(!empty($uploaderror)){
						echo $uploaderror;
					}
				?>
				
				<div class="posts_area"></div>
				<img id="loading" src="img/ajax-loader.gif" />
			</div>

			<script>
				var userLoggedIn ='<?php echo $userLoggedIn;?>';

				$(document).ready(function(){
					$('#loading').show();

					$.ajax({
						url:'include/handlers/ajax_load_posts.php',
						type:'POST',
						data:'page=1&userLoggedIn='+userLoggedIn,
						cache:false,
						success:function(data){
							$('#loading').hide();
							$('.posts_area').html(data);
						}
					});

					$(window).scroll(function(){

						// var height = document.body.scrollHeight;
						// var scroll_top = $(this).scrollTop();
						var page = $('.posts_area').find('.nextPage').val();
						var noMorePosts = $('.posts_area').find('.noMorePosts').val();
						
						if ($(window).scrollTop() >= ($(document).height() - $(window).height())  && noMorePosts == 'false'){
							$('#loading').show();

							$.ajax({
								url:'include/handlers/ajax_load_posts.php',
								type:'POST',
								data:'page='+page+'&userLoggedIn='+userLoggedIn,
								cache:false,
								success:function(response){
									console.log('ran');
									$('.nextPage').remove();
									$('.noMorePosts').remove();
									$('#loading').hide();
									$('.posts_area').append(response);
								}
							});
						}// end if

						// if($('.nomorepost_warning').length>1){
						// 	$('.nomorepost_warning').first().remove();
						// }

						return false;
					});
				});

			</script>
		</div>
	</div>



<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script type="text/javascript" src="js/bootstrap-filestyle.js"></script>
<script src="js/bootbox.min.js"></script>
<script src="js/site.js"></script>
	<script type="text/javascript">

		$('#fileToUpload').filestyle({
			input : false,
			buttonName : 'btn-info',
			buttonText : 'Upload Image'
		});
	</script>
</body>
</html>