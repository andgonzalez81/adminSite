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

		 <h2><a href="#"><?=_("Video OnDemand")?></a> &raquo; <a href="#" class="active"><?=_("View channels")?></a></h2>

			<?php
				$catId = $_POST['category'];
			?>
			
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post" class="jNice">
				<select name="category" class="styledselect_form_1">
				<option value="0"><?=_("All Categories")?></option>
				<?php
								
					$sql = "SELECT DISTINCT 
										vc.*
									FROM
										vodcategories vc,
										vod_channels_categories vcc,
										packages_vodchannels pv,
										subscribers_packages sp
									WHERE
										vc.id = vcc.category_id AND
										vc.id = pv.resource_id AND
										pv.package_id = sp.package_id AND
										sp.subscriber_id = ".$_SESSION['id']." AND
										vc.parent = 0
									ORDER BY
										vc.id";
					
					$rsGet = $DB->Execute($sql);
					
					while (!$rsGet->EOF)
					{	
						?>
						<option value="<?=$rsGet->fields['id'] ?>" <?php if($catId == $rsGet->fields['id']) echo "selected='selected'" ?>><?=ucfirst(strtolower($rsGet->fields['name'])) ?></option>
						<?php
						$dad=$rsGet->fields['name'];
						//Second+ level menus - Sorry for the mess, Padre needs to be sent as a comparison value.
						make_kids($rsGet->fields['id'],$dad,$padre);
						$rsGet->MoveNext();
					}
					?>
				</select>
			</form>
			
			<div class="album">
			 <table class="gallery paginate-2 max-pages-6">
			 <tr>
			 <?php
				$sql_getData = "SELECT DISTINCT
												 vc.id,
												 vc.name,
												 tc.current_views,
												 rc.max_views,
												 rc.duration,
												 vc.stb_url,
												 vc.local_url,
												 vc.small_pic,
												 tc.current_views,
												 tc.restriction_id,
												 rc.duration,
												 DATE_ADD(tc.creation_date, INTERVAL rc.duration DAY) as restriction_date,
												 NOW() as today
												FROM
												
													packages_vodchannels pv,
													subscribers sc,
													subscribers_packages sp,
													vod_channels_categories vcc,
												
												 vodchannels vc,
												 tickets tc,
												 restrictions rc
												WHERE
																		
												 tc.restriction_id  = rc.id AND
												 tc.subscriber_id = sc.id AND 
											 
												 vc.id = vcc.channel_id AND
												 pv.resource_id = vc.id AND
												 pv.package_id = sp.package_id AND
												 sp.subscriber_id = sc.id AND 
																		
												 vc.id = tc.resource_id AND
												 tc.restriction_id  = rc.id AND
												 tc.subscriber_id = ".$_SESSION['id']." 
												 ORDER BY vc.id DESC";
										
			 $rs_getData = $DB->Execute($sql_getData);

				while (!$rs_getData->EOF)
				{
					$show = true;
					
					//Restriction by max. views
					if($rs_getData->fields['max_views'] != 0 and ($rs_getData->fields['max_views'] > $rs_getData->fields['current_views']))
					{
						$show = false;
					}
					
					//Restriction by date
					
					if($rs_getData->fields['duration'] != 0)
					{
						$restriction_date = strtotime($rs_getData->fields['restriction_date']);
						$now = strtotime($rs_getData->fields['today']);
						
						if ($restriction_date < $now){
							$show = false;	
						}
					}
					
					if($show == true){
					
						$counter++;
						$thumb=getThumbnail($rs_getData->fields['small_pic']);
						
						$sql = "select resource_path from vodchannels_resources where channel_id = ".$rs_getData->fields['id'];
						$rsGetResources = $DB->execute($sql);
						
						?>
							<td>
								<div class="imageSingle">
									<div class="image">
										<img src="../data/images/<?=$thumb ?>" />									
										<div class="caption">
											<b><?=_("Name")?> : </b><?=$rs_getData->fields['name']; ?><br />
											<b><?=_("Current Views")?> : </b><?=$rs_getData->fields['current_views']; ?><br />
											<b><?=_("Max Views")?> : </b><?=$rs_getData->fields['max_views'] != 0 ? $rs_getData->fields['max_views'] : "Unlimited" ?><br />
											<b><?=_("Days")?> : </b><?=$rs_getData->fields['duration'] != 0 ? $rs_getData->fields['duration'] : "Unlimited" ?><br />
											
											<div class="actions">
												<a href="viewVodDetailFrm.php?id=<?=$rs_getData->fields['id']?>&iframe=true&width=800&height=550" rel="prettyPhoto[details]" title="View Details for video <?=$rs_getData->fields['name']; ?>">
													<img src="images/icons/more_details.png" alt="<?=_("More Details")?>" class="icon" />
												</a>
												
												<a href="player.php?id=<?=$rs_getData->fields['id']; ?>&type=2&iframe=true&width=640&height=480" rel="prettyPhoto[player]" title="View Local">
													<img src="images/icons/view_local.png" alt="<?=_("View Local")?>" class="icon" />
												</a>
												
												<a href="player.php?id=<?=$rs_getData->fields['id']; ?>&type=3&iframe=true&width=640&height=480" rel="prettyPhoto[player]" title="View trough Internet">
													<img src="images/icons/view_internet.png" alt="<?=_("View trough Internet")?>" class="icon" />
												</a>
											</div>
										</div>
									</div>
								</div>
								
								<div class="imageSingle">
									<div class="image">
										<b><?=_("Additional Resources")?> : </b>
										<?php
											while (!$rsGetResources->EOF){
												?>
													<a href="<?=$rsGetResources->fields['resource_path']?>"><?=$rsGetResources->fields['resource_path']?></a><br />
												<?
												$rsGetResources->movenext();
											}
											if($rsGetResources->numrows()== 0){
											?>
											<br />No additional resources found.
											<?
											}
										?>
									</div>
								</div>
	
							</td>
						<?
						if ($counter%2 == 0){
							?>
							</tr>
							<tr>
						<?
						}
						
					}
					
					$rs_getData->MoveNext();
				}?>
				</tr>
			</table>
			 
			<?php
			if($counter == 0)
			{
				?>
					<h3><?=_("No data found")?></h3>
				<?php
				}
			?>


		</div>
		
		
		<script type="text/javascript" src="js/pagination.js"></script>
	 </div><!-- // #main -->
	<div class="clear"></div>
 </div><!-- // #container -->
 </div><!-- // #containerHolder -->
 <p id="footer"></p>
 </div><!-- // #wrapper -->
</body>
</html>