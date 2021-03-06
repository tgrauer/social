
<?php 
session_start();
include("include/header.php");

$profile_id = $user['username'];
$imgSrc = "";
$result_path = "";
$msg = "";

/***********************************************************
	0 - Remove The Temp image if it exists
***********************************************************/
	if (!isset($_POST['x']) && !isset($_FILES['image']['name']) ){
		//Delete users temp image
			$temppath = 'img/profile_pics/'.$profile_id.'_temp.jpeg';
			if (file_exists ($temppath)){ @unlink($temppath); }
	} 


if(isset($_FILES['image']['name'])){	
/***********************************************************
	1 - Upload Original Image To Server
***********************************************************/	
	//Get Name | Size | Temp Location		    
		$ImageName = $_FILES['image']['name'];
		$ImageSize = $_FILES['image']['size'];
		$ImageTempName = $_FILES['image']['tmp_name'];
	//Get File Ext   
		$ImageType = @explode('/', $_FILES['image']['type']);
		$type = $ImageType[1]; //file type	
	//Set Upload directory    
		$uploaddir = $_SERVER['DOCUMENT_ROOT'].'/social/img/profile_pics';
	//Set File name	
		$file_temp_name = $profile_id.'_original.'.md5(time()).'n'.$type; //the temp file name
		$fullpath = $uploaddir."/".$file_temp_name; // the temp file path
		$file_name = $profile_id.'_temp.jpeg'; //$profile_id.'_temp.'.$type; // for the final resized image
		$fullpath_2 = $uploaddir."/".$file_name; //for the final resized image
	//Move the file to correct location
		$move = move_uploaded_file($ImageTempName ,$fullpath) ; 
		chmod($fullpath, 0777);  
		//Check for valid uplaod
		if (!$move) { 
			die ('File didnt upload');
		} else { 
			$imgSrc= "img/profile_pics/".$file_name; // the image to display in crop area
			// $msg= "Upload Complete!";  	//message to page
			$src = $file_name;	 		//the file name to post from cropping form to the resize		
		} 

/***********************************************************
	2  - Resize The Image To Fit In Cropping Area
***********************************************************/		
		//get the uploaded image size	
			clearstatcache();				
			$original_size = getimagesize($fullpath);
			$original_width = $original_size[0];
			$original_height = $original_size[1];	
		// Specify The new size
			$main_width = 500; // set the width of the image
			$main_height = $original_height / ($original_width / $main_width);	// this sets the height in ratio									
		//create new image using correct php func			
			if($_FILES["image"]["type"] == "image/gif"){
				$src2 = imagecreatefromgif($fullpath);
			}elseif($_FILES["image"]["type"] == "image/jpeg" || $_FILES["image"]["type"] == "image/pjpeg"){
				$src2 = imagecreatefromjpeg($fullpath);
			}elseif($_FILES["image"]["type"] == "image/png"){ 
				$src2 = imagecreatefrompng($fullpath);
			}else{ 
				$msg .= "There was an error uploading the file. Please upload a .jpg, .gif or .png file. <br />";
			}
		//create the new resized image
			$main = imagecreatetruecolor($main_width,$main_height);
			imagecopyresampled($main,$src2,0, 0, 0, 0,$main_width,$main_height,$original_width,$original_height);
		//upload new version
			$main_temp = $fullpath_2;
			imagejpeg($main, $main_temp, 90);
			chmod($main_temp,0777);
		//free up memory
			imagedestroy($src2);
			imagedestroy($main);
			//imagedestroy($fullpath);
			@ unlink($fullpath); // delete the original upload					
									
}//ADD Image 	

/***********************************************************
	3- Cropping & Converting The Image To Jpg
***********************************************************/
if (isset($_POST['x'])){
	
	//the file type posted
		$type = $_POST['type'];	
	//the image src
		$src = 'img/profile_pics/'.$_POST['src'];	
		$finalname = $profile_id.md5(time());	
	
	if($type == 'jpg' || $type == 'jpeg' || $type == 'JPG' || $type == 'JPEG'){	
	
		//the target dimensions 150x150
			$targ_w = $targ_h = 150;
		//quality of the output
			$jpeg_quality = 90;
		//create a cropped copy of the image
			$img_r = imagecreatefromjpeg($src);
			$dst_r = imagecreatetruecolor( $targ_w, $targ_h );
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
			imagejpeg($dst_r, "img/profile_pics/".$finalname."n.jpeg", 90); 	
			 		
	}else if($type == 'png' || $type == 'PNG'){
		
		//the target dimensions 150x150
			$targ_w = $targ_h = 150;
		//quality of the output
			$jpeg_quality = 90;
		//create a cropped copy of the image
			$img_r = imagecreatefrompng($src);
			$dst_r = imagecreatetruecolor( $targ_w, $targ_h );		
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
			imagejpeg($dst_r, "img/profile_pics/".$finalname."n.jpeg", 90); 	
						
	}else if($type == 'gif' || $type == 'GIF'){
		
		//the target dimensions 150x150
			$targ_w = $targ_h = 150;
		//quality of the output
			$jpeg_quality = 90;
		//create a cropped copy of the image
			$img_r = imagecreatefromgif($src);
			$dst_r = imagecreatetruecolor( $targ_w, $targ_h );		
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
			imagejpeg($dst_r, "img/profile_pics/".$finalname."n.jpeg", 90); 	
		
	}
		//free up memory
			imagedestroy($img_r); // free up memory
			imagedestroy($dst_r); //free up memory
			@ unlink($src); // delete the original upload					
		
		//return cropped image to page	
		$result_path ="img/profile_pics/".$finalname."n.jpeg";

		//Insert image into database
		$sql ="UPDATE users SET profile_pic=? WHERE username=?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$result_path, $userLoggedIn]);

		unset($_SESSION['user']['profile_pic']);
		$_SESSION['user']['profile_pic']=$result_path;
														
}
?>
<div id="Overlay" style=" width:100%; height:100%; border:0px #990000 solid; position:absolute; top:0px; left:0px; z-index:2000; display:none;"></div>

<div class="container main">

<div class="row">

	<div id="formExample" class="col-sm-12 panel">
		
	    <p><b> <?=$msg?> </b></p>
	    
	    <form action="upload.php" method="post"  enctype="multipart/form-data">
	    	<br />
	        <h2>Update Profile Picture</h2><br /><br />
	        <input type="file" id="image" name="image" /><br /><br />
	        <input type="submit" value="Upload" class="btn btn-primary" />
	    </form><br /><br />
	    
	</div> <!-- Form-->  

    <?php
    if($imgSrc){ //if an image has been uploaded display cropping area?>

	    <script>
	    	$('#Overlay').show();
			$('#formExample').hide();
	    </script>
	    <div id="CroppingContainer" class="col-sm-12 panel">  
	    
	        <div id="CroppingArea" class="col-sm-6">	
	            <img src="<?=$imgSrc?>" border="0" id="jcrop_target" style="border:0px #990000 solid; position:relative; margin:0px 0px 0px 0px; padding:0px; " />
	        </div>  

	        <div id="InfoArea" class="col-sm-6">	
	          	<p style="margin:10px 0 20px 0; padding:0px; color:#444; font-size:18px; paddling-left:30px;">          
	                <b>Crop Profile Image</b>
	          	</p>
				<ul>
				    <li>Crop / resize your uploaded profile image.</li>
				    <li>Once you are happy with your profile image then please click save.</li>
				</ul>

				<form action="upload.php" method="post" onsubmit="return checkCoords();">
				    <input type="hidden" id="x" name="x" />
				    <input type="hidden" id="y" name="y" />
				    <input type="hidden" id="w" name="w" />
				    <input type="hidden" id="h" name="h" />
				    <input type="hidden" value="jpeg" name="type" /> <?php // $type ?> 
				    <input type="hidden" value="<?=$src?>" name="src" />
				    <input type="submit" class="btn btn-primary" value="Save" />
				</form>

				<form action="upload.php" method="post" onsubmit="return cancelCrop();">
				    <input type="submit" value="Cancel Crop" class="btn btn-danger"   />
				</form>
	        </div>  	        
	    </div><!-- CroppingContainer -->
	<?php 
	} ?>
</div>
</div> 
 
 <?php if($result_path) {
	 ?>
    
    <div class="container panel">
    	<div class="row">
    		<div class="col-sm-2">
    			<img src="<?=$result_path?>" style="position:relative; margin:10px auto; display:block; width:150px; height:150px;" />
    			<a class="btn btn-md btn-primary" href="<?php echo $user['username'];?>">Back to Profile</a>
    		</div>
    	</div>
    </div>
    
	 
 <?php } ?>
 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
<script src="js/site.js"></script>
<script src="js/jquery.jcrop.js"></script>
<script src="js/jcrop_bits.js"></script>



