<?
include("includes/connection.php");
include("session.php");

$large_image_location = "data/images/";
$gallery_upload_path = "data/images/";
$max_width = 300;
$max_height = 410;
$error = '';

$userfile_name = $_FILES['pic']['name'];
$userfile_tmp = $_FILES['pic']['tmp_name'];
$userfile_size = $_FILES['pic']['size'];
$filename = basename($userfile_name);

$file_ext = substr($filename, strrpos($filename, '.') + 1);	 //remove the ext
$filename_strip= substr($filename,0,strrpos($filename, '.'));

//Only process if the file is a JPG and below the allowed limit
if((!empty($_FILES['pic']['name'])) && ($_FILES['pic']['error'] == 0))
{
	if (($file_ext!="jpg"))
	{
		$error= _("Only JPG images are accepted for uploading");
	}
	
	//If is ok, so we can upload the image.
	if (strlen($error)==0)
	{
		if (isset($_FILES['pic']['name']))
		{
			$filename =$filename_strip."_big".".".$file_ext;
			
			if(is_file($gallery_upload_path.$filename))
			{
				while (file_exists($gallery_upload_path.$filename))
				{
					$filename_strip .= rand(100, 999);
					$filename =$filename_strip."_big".".".$file_ext;
				}
			}

			$large_image_location = $gallery_upload_path .$filename;
			move_uploaded_file($userfile_tmp, $large_image_location);
			chmod($large_image_location, 0777);
					
			$width = getWidth($large_image_location);
			$height = getHeight($large_image_location);
				
			//Scale the image if it is greater than the width set above
			if (($width > $max_width) or ($height > $max_height))
			{
				$uploaded = resizeImage($large_image_location,$max_width,$max_height);
			}
			createThumbnail($filename);
		}
	}
}

if ($_POST["MM_insert"] == "true")
{
	$postArray = &$_POST ;
	
	$pic = escape_value($filename);
	$name = escape_value($postArray['name']);
	$description = escape_value($postArray['description']);
	$stb_url = escape_value($postArray['stb_url']);
	$download_url = escape_value($postArray['download_url']);
	$pc_url = escape_value($postArray['pc_url']);
	$trainer = escape_value($postArray['trainer']);
	$date_release = escape_value($postArray['date_release']);
	$keywords = escape_value($postArray['keywords']);
	$rating = escape_value($postArray['rating']);
	$price = escape_value($postArray['price']);
	
	$insertSql = "INSERT INTO vodchannels
								(pic,name,description,stb_url,download_url,pc_url,trainer,date_release,keywords,rating,price)
								VALUES ('$pic','$name','$description',
												'$stb_url','$download_url','$pc_url',
												'$trainer','$date_release','$keywords',$rating,$price)";
	
	$rsInsVod = $DB->Execute($insertSql);
	
	redirect("viewVod.php");
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php include ("includes/head.php") ?>
<body>
 <div id="wrapper">
  <h1><a href="menuadmin.php"></a></h1>
	<?php include("includes/mainnav.php") ?>
	<!-- // #end mainNav -->
	<div id="containerHolder">
	 <div id="container">
		<div id="sidebar">
		 <?php include("includes/sidenav.php") ?>
		</div>    
		<!-- // #sidebar -->
		<div id="main">
			<h2><a href="#"><?=_("Video on demand")?></a> &raquo; <a href="#" class="active"><?=_("Add a VOD movie")?></a></h2>
			<form method="post" enctype="multipart/form-data" action="<?php echo $currentPage; ?>" class="jNice">
				<fieldset>
					<p>
						<label><?=_("Upload a logo (300x410px)") ?></label>
						<input name="pic" type="file" size="23" />
					</p>
					<p>
						<label><?=_("Movie Name")?> : </label>
						<input name="name" type="text" maxlength="200" class="text-long" />
					</p>
					<p>
						<label><?=_("Movie Description")?> : </label>
						<label><textarea name="description" cols="100" /></textarea></label>
					</p>

					<p>
						<label><?=_("Movie STB URL")?> : </label>
						<input name="stb_url" type="text" maxlength="350"  class="text-long" />
					</p>
					
					<p>
						<label><?=_("Movie Download URL")?> : </label>
						<input name="download_url" type="text" maxlength="350"  class="text-long" />
					</p>
										
					<p>
						<label><?=_("Movie PC URL")?> : </label>
						<input name="pc_url" type="text" maxlength="350"  class="text-long" />
					</p>
					<p>
						<label><?=_("Movie Director / Trainer")?> : </label>
						<input name="trainer" type="text" maxlength="350"  class="text-long" />
					</p>
					<p>
						<label><?=_("Release Date")?> : </label>
						<input type="text" name="date_release" id="date_release" class="text-medium" readonly="readonly" />
						<img src="images/calendar.png" id="btn_date_release" alt="" />
						<script type="text/javascript">
						Calendar.setup({
							inputField : "date_release", // ID of the input field
							showsTime: true, // show time
							ifFormat : "%Y/%m/%d %H:%M:%S", // the date format
							button : "btn_date_release" // ID of the button
						})
						</script>
					</p>
					<p>
						<label><?=_("Keywords (Comma Separated)")?> : </label>
						<input name="keywords" type="text" maxlength="150"  class="text-long" />
					</p>
					<p>
						<label><?=_("Rating")?> : </label>
						<input name="rating" type="text" maxlength="150"  class="text-long" />
					</p>
					<p>
						<label><?=_("Price")?> : </label>
						<input name="price" type="text" maxlength="150"  value="0" class="text-long" />
					</p>
					<p>
						<label>&nbsp;</label>
						<input type="hidden" name="MM_insert" value="true" />
						<input type="submit" value="<?=_("Add VOD Movie")?>" />
					</p>
				</fieldset>
			</form>	
		 
			 </div><!-- // #main -->
      <div class="clear"></div>
      </div><!-- // #container -->
    </div><!-- // #containerHolder -->
    <p id="footer"></p>
  </div><!-- // #wrapper -->
</body>
</html>