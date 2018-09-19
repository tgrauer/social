<?php 
	
	class POST{

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

		public function submitPost($body, $user_to, $imgName, $userLoggedIn){
			$body = strip_tags(trim($body));

			$check_empty = preg_replace('/\s+/', '', $body);

			// if($check_empty !=""){

				$body_array = preg_split("/\s+/", $body);

				foreach ($body_array as $key => $value) {
					if(strpos($value, 'www.youtube.com/watch?v=') !== false){
						$link = preg_split("!&!", $value);
						$value=preg_replace("!watch\?v=!", "embed/", $link[0]);
						$value="<br /><iframe width='420' height='315' src='".$value."'></iframe><br />";
						$body_array[$key]=$value;
					}
				}

				$body = implode(" ", $body_array);

				$date_added = date('Y-m-d H:i:s');
				$added_by = $userLoggedIn;

				if($user_to == $added_by){
					$user_to='none';
				}

				$sql = "INSERT INTO posts (body, added_by, user_to, date_added, user_closed, deleted, likes, image) VALUES(?,?,?,?,?,?,?,?)";
				$stmt= $this->db->prepare($sql);
				$stmt->execute([$body,$added_by,$user_to,$date_added,0,0,0,$imgName]);
				$returned_id=$this->db->lastInsertId();

				if($user_to !='none'){
					$notification = new NOTIFICATION($added_by);
					$notification->insertNotification($returned_id, $user_to, 'profile_post');
				}

				$num_posts = $this->user_obj->getNumPosts();
				$num_posts ++;
				$sql = "UPDATE users SET num_posts= ? WHERE username=?";
				$stmt=$this->db->prepare($sql);
				$stmt->execute([$num_posts, $added_by]);

			// }
		}

		public function load_posts_friends($data, $limit){
			$page = $data['page'];
			$userLoggedIn = $this->user_obj->getUsername();

			if($page == 1){
				$start=0;
			}else{
				$start = ($page - 1) * $limit;
			}

			$str="";
			$sql = "SELECT * FROM posts WHERE deleted = ? ORDER BY id DESC";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([0]);
			$posts = $stmt->fetchAll();
			$row_count = count($posts);

			if($row_count){

				$num_iterations = 0;
				$count = 1;

				foreach ($posts as $key) {
				 
				    $id = $key['id'];
				    $body = $key['body'];
				    $added_by = $key['added_by'];
				    $date = $key['date_added'];
				    $imgPath = $key['image'];
				    
				    if($key['user_to']=='none'){
				    	$user_to='';
				    }else{
				    	$user_to_obj = new USER($key['user_to']);
				    	$user_to_name = $user_to_obj->getuserFullName();
				    	$user_to = 'to <a href="'.$key['user_to'].'">'.$user_to_name.'</a>';
				    }

				    $added_by_obj = new USER($added_by);
				    if($added_by_obj->isClosed()){
				    	continue;
				    }

				    $user_logged_obj = new USER($userLoggedIn);

				    if($user_logged_obj->isFriends($added_by)){

					    if($num_iterations++ < $start)
					    	continue;

					    if($count > $limit){
					    	break;
					    }else{
					    	$count++;
					    }

					    if($userLoggedIn == $added_by){
					    	$delete_button = "<a href='#' class='delete_button' name='delete_post' id='post$id'>Delete Post</a>";
					    }else{
					    	$delete_button='';
					    }

					    $sql = "SELECT first_name, last_name, profile_pic FROM users WHERE username = ?";
					    $stmt = $this->db->prepare($sql);
					    $stmt->execute([$added_by]);
						$user_details = $stmt->fetchAll();

						$first_name = $user_details[0]['first_name'];
						$last_name = $user_details[0]['last_name'];
						$profile_pic = $user_details[0]['profile_pic'];

					?>
						<script>
							function toggle<?php echo $id;?>(){
								var element = document.getElementById("toggleComment<?php echo $id;?>");
								if(element.style.display =='block'){
									element.style.display='none';
								}else{
									element.style.display ='block';
								}
							}
						</script>

					<?php
						$sql = "SELECT COUNT(*) FROM comments WHERE post_id=?";
						$stmt= $this->db->prepare($sql);
						$stmt->execute([$id]);
						$comments_num = $stmt->fetchColumn();

						$time_message = $this->time_message($date);

						if($imgPath !=''){
							$imgDiv = '<div class="postedImage"><img src="'.$imgPath.'" class="img-responsive"/></div>';
						}else{
							$imgDiv = '';
						}

						$str .= '<div class="status_post row">
									<div class="profile_pic col-sm-2">
										<a href="'.$added_by.'"><img src="'.$profile_pic.'" /></a>
									</div>
									<div class="post_body col-sm-9">
										<a href="'.$added_by.'">'.$first_name .' '. $last_name.'</a> '.$user_to.' &nbsp&nbsp&nbsp&nbsp <span class="post_time">'.$time_message.'</span><br />
										
									'.$body.' '.$imgDiv.'
										<div class="newsFeedPostOptions">
											<a href="#/" onClick="javascript:toggle'.$id.'()">Comments ('.$comments_num.')</a> &nbsp;&nbsp;&nbsp;
											<iframe src="like.php?post_id='.$id.'"></iframe>'.$delete_button.'
										</div>
									</div>
								</div><div class="post_comment" id="toggleComment'.$id.'" style="display:none;">
									<iframe class="comment_frame" src="comment_frame.php?post_id='.$id.'" frameborder="0"></iframe>
								</div>';
					}

					?>
						<script>
							$(document).ready(function(){
								$('.newsFeedPostOptions').on('click', '#post<?php echo $id;?>', function(e){
									e.preventDefault();
									bootbox.confirm('Are you sure you want to delete this post?', function(result){
										$.post("include/form_handlers/delete_post.php?post_id=<?php echo $id;?>",{result:result});
										if(result){
											location.reload();
										}
									});
								});
							});
						</script>
					<?php
				} // end foreach

				if($count > $limit){
					$str.= "<input type='hidden' class='nextPage' value='".($page + 1)."' /><input type='hidden' class='noMorePosts' value='false' />";
				}else if($count < $limit && $page != 1){
					$str.= "<input type='hidden' class='noMorePosts' value='true' /><p class='nomorepost_warning text-center'>No More Posts</p>";
				}
			}
			echo $str;
		}

		public function load_profile_posts($data, $limit){
			$page = $data['page'];
			$profile_user = $data['profile_username'];
			$userLoggedIn = $this->user_obj->getUsername();
			$user_fullname = $this->user_obj->getuserFullName();

			if($page == 1){
				$start=0;
			}else{
				$start = ($page - 1) * $limit;
			}

			$str="";
			$sql = "SELECT * FROM posts WHERE deleted = ? AND ((added_by=? AND user_to=?) OR user_to=?) AND user_closed=? ORDER BY id DESC";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([0,$profile_user, 'none', $profile_user,0]);
			$posts = $stmt->fetchAll();
			$row_count = count($posts);

			if($row_count){

				$num_iterations = 0;
				$count = 1;

				foreach ($posts as $key) {
				 
				    $id = $key['id'];
				    $body = $key['body'];
				    $added_by = $key['added_by'];
				    $date = $key['date_added'];
				    $imgPath = $key['image'];

				    if($num_iterations++ < $start)
				    	continue;

				    if($count > $limit){
				    	break;
				    }else{
				    	$count++;
				    }

				    if($userLoggedIn == $added_by){
				    	$delete_button = "<a href='#' class='delete_button' name='delete_post' id='post$id'>Delete Post</a>";
				    }else{
				    	$delete_button='';
				    }

				    $sql = "SELECT first_name, last_name, profile_pic FROM users WHERE username = ?";
				    $stmt = $this->db->prepare($sql);
				    $stmt->execute([$added_by]);
					$user_details = $stmt->fetchAll();

					$first_name = $user_details[0]['first_name'];
					$last_name = $user_details[0]['last_name'];
					$profile_pic = $user_details[0]['profile_pic'];

				?>
					<script>
						function toggle<?php echo $id;?>(){
							var element = document.getElementById("toggleComment<?php echo $id;?>");
							if(element.style.display =='block'){
								element.style.display='none';
							}else{
								element.style.display ='block';
							}
						}
					</script>

				<?php
					$sql = "SELECT COUNT(*) FROM comments WHERE post_id=?";
					$stmt= $this->db->prepare($sql);
					$stmt->execute([$id]);
					$comments_num = $stmt->fetchColumn();

					$time_message = $this->time_message($date);

					if($imgPath !=''){
						$imgDiv = '<div class="postedImage"><a href="post.php?id='.$id.'"><img src="'.$imgPath.'" class="img-responsive"/></a></div>';
					}else{
						$imgDiv = '';
					}

					$str .= '<div class="status_post row" >
								<div class="profile_pic col-sm-2">
									<a href="'.$added_by.'"><img src="'.$profile_pic.'" /></a>
								</div>
								<div class="post_body col-sm-9">
									<a href="'.$added_by.'">'.$first_name .' '. $last_name.'</a> &nbsp&nbsp&nbsp&nbsp <span class="post_time">'.$time_message.'</span><br />
									
								'.$body.' '.$imgDiv.'
									<div class="newsFeedPostOptions">
										<a href="#/" onClick="javascript:toggle'.$id.'()">Comments ('.$comments_num.')</a> &nbsp;&nbsp;&nbsp;
										<iframe src="like.php?post_id='.$id.'"></iframe>'.$delete_button.'
									</div>
								</div>
							</div><div class="post_comment" id="toggleComment'.$id.'" style="display:none;">
								<iframe class="comment_frame" src="comment_frame.php?post_id='.$id.'" frameborder="0"></iframe>
							</div>';
					

					?>
						<script>
							$(document).ready(function(){
								$('.newsFeedPostOptions').on('click', '#post<?php echo $id;?>', function(e){
									e.preventDefault();
									bootbox.confirm('Are you sure you want to delete this post?', function(result){
										$.post("include/form_handlers/delete_post.php?post_id=<?php echo $id;?>",{result:result});
										if(result){
											location.reload();
										}
									});
								});
							});
						</script>
					<?php
				} // end foreach

				if($count > $limit){
					$str.= "<input type='hidden' class='nextPage' value='".($page + 1)."' /><input type='hidden' class='noMorePosts' value='false' />";
				}else if($count < $limit && $page != 1){
					$str.= "<input type='hidden' class='noMorePosts' value='true' /><p class='nomorepost_warning text-center'>No More Posts</p>";
				}
			}else{
				// $userprof_obj = new USER($profile_user);
				// $user_fullname = $this->userprof_obj->getuserFullName();
				$str = '<h4 class="text-center"><b>'. $profile_user .' has not posted anything yet</b></h4>';
			}

			echo $str;
		}

		public function time_message($date){

			$date_time_now = date('Y-m-d H:i:s');
			$start_date = new DateTime($date);
			$end_date = new DateTime($date_time_now);
			$interval = $start_date->diff($end_date);
			if($interval->y >=1){
				if($interval == 1){
					$time_message = $interval->y . " year ago";
				}else{
					$time_message = $interval->y . " years ago";
				}
			}elseif($interval->m >= 1){
				if($interval->d == 0){
					$days = " ago";
				}else if($interval->d == 1){
					$days = $interval->d . " day ago";
				}else{
					$days = $interval->d . " days ago";
				}

				if($interval->m == 1){
					$time_message = $interval->m . " month";
				}else{
					$time_message = $interval->m . " months";
				}
			}elseif ($interval->d >=1) {
				if($interval->d == 1){
					$time_message = " Yesterday";
				}else{
					$time_message = $interval->d . " days ago";
				}
			}elseif ($interval->h >=1) {
				if($interval->h == 1){
					$time_message = $interval->h . " hour ago";
				}else{
					$time_message = $interval->h . " hours ago";
				}
			}elseif ($interval->i >=1) {
				if($interval->i == 1){
					$time_message = $interval->i . " minute ago";
				}else{
					$time_message = $interval->i . " minutes ago";
				}
			}else{
				if($interval->s < 30){
					$time_message = "Just Now";
				}else{
					$time_message = $interval->s . " seconds ago";
				}
			}

			return $time_message;
		}

		public function getSinglePost($post_id){

			$userLoggedIn = $this->user_obj->getUsername();

			$sql = "UPDATE notifications SET opened =? WHERE user_to=? AND link LIKE '%=$post_id'";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([1, $userLoggedIn]);

			$str="";
			$sql = "SELECT * FROM posts WHERE deleted = ? AND id=? ";
			$stmt = $this->db->prepare($sql);
			$stmt->execute([0, $post_id]);
			$posts = $stmt->fetchAll();
			$row_count = count($posts);

			if($row_count){

				$num_iterations = 0;
				$count = 1;

				foreach ($posts as $key) {
				 
				    $id = $key['id'];
				    $body = $key['body'];
				    $added_by = $key['added_by'];
				    $date = $key['date_added'];
				    $imgPath = $key['image'];
				    
				    if($key['user_to']=='none'){
				    	$user_to='';
				    }else{
				    	$user_to_obj = new USER($key['user_to']);
				    	$user_to_name = $user_to_obj->getuserFullName();
				    	$user_to = 'to <a href="'.$key['user_to'].'">'.$user_to_name.'</a>';
				    }

				    $added_by_obj = new USER($added_by);
				    if($added_by_obj->isClosed()){
				    	return;
				    }

				    $user_logged_obj = new USER($userLoggedIn);

				    if($user_logged_obj->isFriends($added_by)){

					    if($userLoggedIn == $added_by){
					    	$delete_button = "<a href='#' class='delete_button' name='delete_post' id='post$id'>Delete Post</a>";
					    }else{
					    	$delete_button='';
					    }

					    $sql = "SELECT first_name, last_name, profile_pic FROM users WHERE username = ?";
					    $stmt = $this->db->prepare($sql);
					    $stmt->execute([$added_by]);
						$user_details = $stmt->fetchAll();

						$first_name = $user_details[0]['first_name'];
						$last_name = $user_details[0]['last_name'];
						$profile_pic = $user_details[0]['profile_pic'];

					?>
						<script>
							function toggle<?php echo $id;?>(){
								var element = document.getElementById("toggleComment<?php echo $id;?>");
								if(element.style.display =='block'){
									element.style.display='none';
								}else{
									element.style.display ='block';
								}
							}
						</script>

					<?php
						$sql = "SELECT COUNT(*) FROM comments WHERE post_id=?";
						$stmt= $this->db->prepare($sql);
						$stmt->execute([$id]);
						$comments_num = $stmt->fetchColumn();

						$time_message = $this->time_message($date);

						if($imgPath !=''){
							$imgDiv = '<div class="postedImage"><img src="'.$imgPath.'" class="img-responsive"/></div>';
						}else{
							$imgDiv = '';
						}

						$str .= '<div class="status_post row" >
									<div class="profile_pic col-sm-2">
										<a href="'.$added_by.'"><img src="'.$profile_pic.'" /></a>
									</div>
									<div class="post_body col-sm-9">
										<a href="'.$added_by.'">'.$first_name .' '. $last_name.'</a> '.$user_to.' &nbsp&nbsp&nbsp&nbsp <span class="post_time">'.$time_message.'</span><br />
									'.$body.' '.$imgDiv.'
										<div class="newsFeedPostOptions">
											<a href="#/" onClick="javascript:toggle'.$id.'()">Comments ('.$comments_num.')</a> &nbsp;&nbsp;&nbsp;
											<iframe src="like.php?post_id='.$id.'"></iframe>'.$delete_button.'
										</div>
									</div>
								</div><div class="post_comment" id="toggleComment'.$id.'" style="display:none;">
									<iframe class="comment_frame" src="comment_frame.php?post_id='.$id.'" frameborder="0"></iframe>
								</div>';

					?>
						<script>
							$(document).ready(function(){
								$('.newsFeedPostOptions').on('click', '#post<?php echo $id;?>', function(e){
									e.preventDefault();
									bootbox.confirm('Are you sure you want to delete this post?', function(result){
										$.post("include/form_handlers/delete_post.php?post_id=<?php echo $id;?>",{result:result});
										if(result){
											location.reload();
										}
									});
								});
							});
						</script>
					<?php

					}else{
						echo '<p>You cannot see this post beacuse you are not friends with this user</p>';
					}
				} // end foreach

			}else{
				echo '<p>No posts found. If you clicked a link, it may be broken</p>';
			}
			echo $str;
		}
	}

?>