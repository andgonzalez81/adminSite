<?
	include("includes/connection.php");
	include("session.php");

	$id = $_REQUEST['usr_id'];
	
	if(trim($id) == "" or !is_numeric($id) or $id == 0)
	{
		redirect("viewSubscribers.php");
	}
	
	$sql = "SELECT distinct vc.id as id, vc.name as name
					FROM
						vodchannels vc,
						packages_vodchannels pv,
						subscribers sc,
						subscribers_packages sp
					WHERE
						pv.resource_id = vc.id AND
						pv.package_id = sp.package_id AND
						sp.subscriber_id = sc.id AND
						sc.id = $id";
						
	$rsGet = $DB->execute($sql);
	
	$sql = "select name from subscribers where id = $id";
	$rsGetName = $DB->execute($sql);

	
	//Create multiple
	
	$N = 0;
	
	$arrTickets = $_POST['tickets'];
	$N = count($arrTickets);
	
	if($N > 0)
	{
		$usr_id = escape_value($_REQUEST['usr_id']);
		$role_id = escape_value($_POST['globalRestriction']);

		for($i=0; $i < $N; $i++)
		{
			$vid_id = escape_value($arrTickets[$i]);
			$ticket = genRandomString();
			
			$sql = "INSERT into tickets
								(subscriber_id,resource_id,current_views,restriction_id,ticket_number,creation_date,status)
							VALUES
								($usr_id,$vid_id,0,$role_id,'$ticket',NOW(),1)";
		
			$rsSet = $DB->execute($sql);
		
			$message = "The user ".$_SESSION['username']." has created the ticket '$ticket' for the user '".$rsGetName->fields['name']."'";
			writeToLog($message);	
		
		} 
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
			<h2><a href="#"><?=_("Subscribers")?></a> &raquo; <a href="#" class="active"><?=_("Manage tickets of ").$rsGetName->fields['name']; ?></a></h2>

			<form name="frmGlobal" action="<?=$_SERVER['PHP_SELF']?>" method="post" onsubmit="return confirm('<?=_("Are you sure do you want to generate the ticket? This action cannot be undone.")?>')">

			<table class="no-arrow rowstyle-alt colstyle-alt paginate-10 max-pages-5">
				<thead>
					<tr>
						<th class="sortable"><b><?=_("VOD Resource")?></b></th>
						<th class="sortable"><b><?=_("Restriction")?></b></th>
						<th ><b><?=_("Ticket Number")?></b></th>
						<th ><input type='checkbox' name='checkall' onclick='checkedAll(frmGlobal);'></th>
					</tr>
				</thead>
				<tbody>
				<?php
					$counter = 0;
					
					while (!$rsGet->EOF)
					{
						$ticket_number = "";
						$counter++;
						$vid_id = $rsGet->fields['id'];
						
						//There is a more efficient way to do this for sure!
						
						$sql ="select * from tickets
									 where 		subscriber_id = $id  
									 and 			resource_id = $vid_id 
									 order 		by creation_date asc";
						
						$rsGetTicket = $DB->execute($sql);
						
						$all_tickets_expired = 1;
						
						while(!$rsGetTicket->EOF)
						{
							$ticket_id = $rsGetTicket->fields['id'];
							$restriction_id = $rsGetTicket->fields['restriction_id'];
							$creation_date = $rsGetTicket->fields['creation_date'];
							
							$sql = "select * from restrictions where id = $restriction_id";
							$rsGetRestriction=$DB->execute($sql);
							while(!$rsGetRestriction->EOF)
							{
								//Ticket validity
								$duration = $rsGetRestriction->fields['duration'];
								
								//Max number of views
								$max_views = $rsGetRestriction->fields['max_views'];
								
								//Zero means infinite, so we'll put a ridiculously high number of visits as a limit
								if($max_views == 0) $max_views = 1000000000;
								
								//Expired by date
								$sql = "select date_add('$creation_date', interval $duration day) > NOW() as time_validity
												from tickets where id = $ticket_id";
								$rsGetTimeRestriction = $DB->execute($sql);
								
								if($rsGetTimeRestriction->fields['time_validity'] == 1)
								{
									$all_tickets_expired = 0;
									
									//Is valid, so we validate by number of views
									$sql = "select current_views >= $max_views as views_validity
													from tickets where id = $ticket_id";
									$rsGetViewsRestriction = $DB->execute($sql);
									if($rsGetViewsRestriction->fields['views_validity'] == 1)
									{
										//Number of views overpassed, so i'll be able to open another ticket
										//even if this is valid by date.
										$all_tickets_expired = 1;
										$rsGetViewsRestriction->movenext();
									}
									$rsGetTimeRestriction->movenext();
								}
								$rsGetRestriction->movenext();
							}
							
							$ticket_number = $rsGetTicket->fields['ticket_number'];
							$restriction_id = $rsGetTicket->fields['restriction_id'];
							
							$rsGetTicket->movenext();
						}
						
						?>
						
						<tr <?php if($counter % 2) echo " class='odd'"?>>
							<td><?=$rsGet->fields['name']?></a></td>
							<td>
								<select name="restriction">
								<?php
								$sql="select * from restrictions";
								$rsGetRestriction=$DB->execute($sql);
								while(!$rsGetRestriction->EOF){
									?>
										<option value="<?=$rsGetRestriction->fields['id']?>" <? if($rsGetRestriction->fields['id']==$restriction_id) echo "selected='selected'" ?>><?=$rsGetRestriction->fields['name']?></option>
									<?
									$rsGetRestriction->movenext();
								}
								?>
								</select>
							</td>
							<td>
								<input name="usr_id" type="hidden" value="<?=$id?>" />
								<input name="vid_id" type="hidden" value="<?=$vid_id?>" />
								<input name="ticket" type="text" maxlength="10" value="<?=$ticket_number?>" class="text-small" readonly="readonly" onkeypress="return handleEnter(this, event)" />
							</td>
							<td align="center">
							<?php if($all_tickets_expired == 1){
								?>
								<input name='tickets[]' type='checkbox' value="<?=$vid_id?>" />
								<?
							}
							?>
							</td>

						</tr>
						<?php
						$rsGet->movenext();
					}  
					?>
				</tbody>
			</table>
			
			<div align="center" style="padding-bottom:20px;">
			<label>For selected, generate a ticket with the restriction : </label>
			<select name="globalRestriction">
				<?php
				$sql="select * from restrictions";
				$rsGetRestriction=$DB->execute($sql);
				while(!$rsGetRestriction->EOF){
					?>
						<option value="<?=$rsGetRestriction->fields['id']?>"><?=$rsGetRestriction->fields['name']?></option>
					<?
					$rsGetRestriction->movenext();
				}
				?>
			</select>
			<input type="submit" value="" class="button-save" />
			</div>

			</form>

			<script type="text/javascript" src="js/tablesort.js"></script>
			<script type="text/javascript" src="js/pagination.js"></script>
			
		</div><!-- // #main -->
    <div class="clear"></div>
    </div><!-- // #container -->
		</div><!-- // #containerHolder -->
    <p id="footer"></p>
  </div><!-- // #wrapper -->
</body>
</html>