<?
	include("includes/connection.php");
	include("session.php");

	$msg = "";

	$pck_id = $_REQUEST['pck_id'];

	if(trim($pck_id) == "" or !is_numeric($pck_id) or $pck_id == 0)
	{
		redirect("viewPackages.php");
	}	
	
	$sql = "select * from packages where id = $pck_id";
	$rsGet = $DB->Execute($sql);
	
	//Add selected multiple
	$addItems = $_POST['addItems'];
	$N = count($addItems);
	
	if($N > 0)
	{		
		for($i=0; $i < $N; $i++)
		{
			$sql = "SELECT * from vodchannels where id = $addItems[$i]";
			$rsGet = $DB->execute($sql);

			$sql_1 = "SELECT * from packages where id = $pck_id";
			$rsGet_1 = $DB->execute($sql_1);

			$message = "The user ".$_SESSION['username']." has added the VOD channel '".$rsGet->fields['name']."' with ID ".$addItems[$i]." to the package '".$rsGet_1->fields['name']."' with ID ".$pck_id.".";

			$sql = "INSERT INTO packages_vodchannels (resource_id,package_id) VALUES (".$addItems[$i].",$pck_id)";
			$rsSet = $DB->execute($sql);
			
			writeToLog($message);
		}
		$msg = _("Changes done!");
	}	
	
	//delete selected multiple	
	$remItems = $_POST['remItems'];
	$N = count($remItems);
	if($N > 0)
	{
		$pck_id = $_POST['pck_id'];
		for($i=0; $i < $N; $i++)
		{
			$sql = "SELECT * from vodchannels where id = $remItems[$i]";
			$rsGet = $DB->execute($sql);

			$sql_1 = "SELECT * from packages where id = $pck_id";
			$rsGet_1 = $DB->execute($sql_1);

			$message = "The user ".$_SESSION['username']." has removed the VOD channel '".$rsGet->fields['name']."' with ID ".$addItems[$i]." from the package '".$rsGet_1->fields['name']."' with ID ".$pck_id.".";
						
			$sql = "delete from packages_vodchannels where resource_id = ".$remItems[$i]." and package_id =".$pck_id;
			$rsSet = $DB->execute($sql);
			
			writeToLog($message);
		}
		$msg = _("Changes done!");		
	}

	//Add All
	if (trim($_POST['a_all']) != ""){
		
		$pck_id = $_POST['pck_id'];
		
		$sql = "SELECT * FROM vodchannels where id not in
						(
							select 	resource_id from packages_vodchannels
							where		package_id = $pck_id
						) ORDER BY id ";  					
						
		$rsGet = $DB->execute($sql);
		
		while (!$rsGet->EOF)
		{

			$sql_res = "SELECT * from vodchannels where id = ".$rsGet->fields['id'];
			$rsGet_res = $DB->execute($sql_res);

			$sql_pck = "SELECT * from packages where id = $pck_id";
			$rsGet_pck = $DB->execute($sql_pck);

			$message = "The user ".$_SESSION['username']." has added the VOD channel '".$rsGet_res->fields['name']."' with ID ".$addItems[$i]." to the package '".$rsGet_pck->fields['name']."' with ID ".$pck_id.".";
						
			$sql = "INSERT INTO packages_vodchannels (resource_id,package_id)
							VALUES (".$rsGet->fields['id'].",$pck_id)";
			$rsSet = $DB->execute($sql);
			
			writeToLog($message);
			
			$rsGet->movenext();
		}
		$msg = _("Changes done!");
	}
	
	//Delete All
	if (trim($_POST['r_all']) != "")
	{
		$pck_id = $_POST['pck_id'];
		
		$sql_1 = "SELECT * from packages where id = $pck_id";
		$rsGet_1 = $DB->execute($sql_1);
		
		$message = "The user ".$_SESSION['username']." has removed all the VOD channels from the package '".$rsGet_1->fields['name']."' with ID ".$pck_id.".";
		
		$sql = "delete from packages_vodchannels where package_id = $pck_id";
		$rsSet = $DB->execute($sql);
		
		writeToLog($message);
		
		$msg = _("Changes done!");
	}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php include ("includes/head.php") ?>
<body>
 <div id="wrapper">
  <h1><a href="#">&nbsp;</a></h1>
	<?php include("includes/mainnav.php") ?>
	<!-- // #end mainNav -->
	<div id="containerHolder">
	 <div id="container">
		<div id="sidebar">
		 <?php include("includes/sidenav.php") ?>
		</div>    
		<!-- // #sidebar -->
		<div id="main">
			<h2><a href="#"><?=_("Packages")?></a> &raquo; <a href="#" class="active"><?=_("Add VOD content for ")?><?=strtolower($rsGet->fields['name'])?>	</a></h2>
			<div id="dhtmlgoodies_scrolldiv">
				<div id="scrolldiv_parentContainer">
					<div id="scrolldiv_content">
						<p id="changeNotification">
							<div id="activityIndicator" style="display:none; ">
								<img src="imagenes/loading_indicator.gif" style="margin-right:10px;" /><?=_("Updating data, please wait")?>...
							</div>
							<div id="completeIndicator" style="display:none; ">
								<?=_("Changes Done!")?>
							</div>
							<?php if(trim($msg)!=""){
								?>
								<div align="center">
									<?=$msg?>
								</div>
								<?
							}
							?>
							<br />
						</p>
						<?	
							$sql1 = "SELECT * FROM vodchannels where id not in
											(
												select 	resource_id from packages_vodchannels
												where		package_id = $pck_id
											) ORDER BY id ";  					
												 
							$sql2 = "SELECT * FROM vodchannels where id in
											(
												select 	resource_id from packages_vodchannels
												where		package_id = $pck_id
											) ORDER BY id ";  											 
											 
							$rsGetLeft = $DB->execute($sql1);
							$rsGetRight = $DB->execute($sql2);
					
							$nleft = $rsGetLeft->numrows();
							$nright = $rsGetRight->numrows();
						 
							if($nleft >= $nright){
								if($nleft < 4) $height = $nleft * 68;
								else $height = $nleft * 38;	
							}
							else{
								if($nright < 4) $height = $nright * 68;
								else $height = $nright * 38;	
							}
						?>
						<form action="<?=$currentPage?>" method="post">	
							<div class="buttons_left">
								<input type="submit" name="a_all" value="<?=_(">>")?>" class="button-submit" />
								<br /><br />
								<input type="submit" name="a_selected" value="<?=_(">")?>" class="button-submit" />
								<input type="hidden" value="<?=$pck_id?>" name="pck_id" />
							</div>
						
							<ul id="sortlist" style="height:<?=$height?>px;">
								<h4><?=_("Available VOD resources")?></h4>
								<br/>
								<?php  
								while (!$rsGetLeft->EOF)
								{  
									?>
									<li id="itemid_<?=$rsGetLeft->fields['id']?>">
										<input type="checkbox" name="addItems[]" value="<?=$rsGetLeft->fields['id']?>" />
										<?=$rsGetLeft->fields['name']?>	
									</li>
									<?php
									$rsGetLeft->movenext();	
								}  
								?>
								<br />
							</ul>
						</form>
					
						<form action="<?=$currentPage?>" method="post">
							<div class="buttons_right">
								<input type="submit" name="r_selected" value="<?=_("<")?>" class="button-submit" />
								<br /><br />
								<input type="submit" name="r_all" value="<?=_("<<")?>" class="button-submit" />
								<input type="hidden" value="<?=$pck_id?>" name="pck_id" />
							</div>
							<ul id="sortlist2" style="height:<?=$height?>px;">
								<h4><?=_("VOD resources in ")?> <?=$rsGet->fields['name']?></h4>
								<br/>
								<?php  
									while (!$rsGetRight->EOF)
									{  
										?>
										<li id="itemid_<?=$rsGetRight->fields['id']?>">
										<input type="checkbox" name="remItems[]" value="<?=$rsGetRight->fields['id']?>" />
										<?=$rsGetRight->fields['name']?>
										</li>
										<?php
										$rsGetRight->movenext();
									}  
								?>
								<br/>
							</ul>
						</form>
						
						<hr style="clear:both;visibility:hidden;" />
					</div>
				</div>
				<div id="scrolldiv_slider">
					<div id="scrolldiv_scrollUp"><img src="images/arrow_up.gif"></div>
					<div id="scrolldiv_scrollbar">
						<div id="scrolldiv_theScroll"><span></span></div>
					</div>
					<div id="scrolldiv_scrollDown"><img src="images/arrow_down.gif"></div>
				</div>
			</div>
			<script type="text/javascript" src="style/js/scrollingInit.js"></script>
			<br />
			<br />			

		</div><!-- // #main -->
    <div class="clear"></div>
    </div><!-- // #container -->
		</div><!-- // #containerHolder -->
    <p id="footer"></p>
  </div><!-- // #wrapper -->
</body>
</html>