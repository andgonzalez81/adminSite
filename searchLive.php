<?php

include("includes/connection.php");
include("session.php");

$strBusca = $_POST['strBusca'];
$condicion = $_POST['condicion'];
$whereCondicion="";

//Delete multiple
$arrArchivos = $_POST['archivos'];
$U = count($arrArchivos);

//Delete multiple
$arrArchivos = $_POST['archivos'];
$U = count($arrArchivos);

if($U > 0)
{
 foreach($arrArchivos as $id)
 {
  $query_rsDel = "SELECT * FROM livechannels WHERE id = $id";
	$rsDel = $DB->Execute($query_rsDel);
  $actual_filename = $rsDel->fields['pic'];
  
	$message = "The user ".$_SESSION['username']." has deleted the channel '".$rsDel->fields['name']."' With ID ".$rsDel->fields['id'];
	
  //Borrar archivos existentes
  $gallery_upload_path = "data/images/";

	$actual_filename_thumb = getThumbnail($actual_filename);
	
	if($actual_filename_thumb != "default.jpg")
	{
	 @unlink($gallery_upload_path.$actual_filename);
	 @unlink($gallery_upload_path.$actual_filename_thumb);	 
	}
	  
  $query_rsDel = "DELETE FROM livechannels WHERE id = $id";
	$rsDel = $DB->Execute($query_rsDel);
	
	writeToLog($message);
	
  redirect($currentPage);
 }
} 

if($_POST['strBusca']!= "")
{
	switch ($condicion)
	{
		case "name":
		
			if($whereCondicion != "") $whereCondicion = " and ";
			$whereCondicion .= " name LIKE '%" . $strBusca . "%' ";
			break;
		
		case "description":

			if($whereCondicion != "") $whereCondicion .= " and ";
			$whereCondicion .= " description LIKE '%" . $strBusca . "%' ";
			break;
		
		case "rating":

			if($whereCondicion != "") $whereCondicion .= " and ";
			$whereCondicion .= " rating in( select id from ratings where code LIKE '%" . $strBusca . "%')";
			break;
		
		case "number":

			if($whereCondicion != "") $whereCondicion .= " and ";
			$whereCondicion .= " number LIKE '%" . $strBusca . "%' ";
			break;
	}
	
	if($whereCondicion != "")	$whereCondicion = " where " . $whereCondicion;	
}

$query_rsConsulta = "SELECT * FROM livechannels "  . $whereCondicion;
$query_rsConsulta .= " ORDER BY id DESC";

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
					<h2><a href="#"><?=_("Live TV")?></a> &raquo; <a href="#" class="active"><?=_("Find live channels")?></a></h2>
						<form method="post" action="<?=$currentPage?>" class="jNice">
						<fieldset>
						<p>
							<label><?=_("Select a search criteria")?></label>
							<input type="text" name="strBusca" value="<?=$strBusca ?>" class="text-long">
						</p>
						<p>
							<label><?=_("Select a search filter")?></label>
							<select name="condicion">
								<option value="name" <?php if ($condicion == "name") echo "selected='selected'" ?>><?=_("Channel Name")?></option>
								<option value="description" <?php if ($condicion == "description") echo "selected='selected'" ?>><?=_("Channel Description")?></option>
								<option value="rating" <?php if ($condicion == "rating") echo "selected='selected'" ?>><?=_("Channel Rating")?></option>
								<option value="number" <?php if ($condicion == "number") echo "selected='selected'" ?>><?=_("Channel Number")?></option>
							</select>
						</p>
						<p>
							<label>&nbsp;</label>
							<input name="find" type="submit" value="<?=_("Find")?>" />
						</p>
						</fieldset>
						</form>
		
				<?php
					
					if($_POST['strBusca']!= "")
					{
						$counter = 0;
						?>
						<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
						<table class="no-arrow rowstyle-alt colstyle-alt paginate-10 max-pages-5" >
						 <thead>
							<tr>
							 <th class="sortable-keep fd-column-0"><b><?=_("Channel Name / Edit")?></b></th>
							 <th class="sortable-keep fd-column-1"><b><?=_("Description")?></b></th>
							 <th class="sortable-keep fd-column-2"><b><?=_("Rating")?></b></th>
							 <th align="center">
							 <input class="button-submit" type="submit" value="<?=_("Delete Selected")?>" name="borrar" onclick="return confirm('<?=_("Are you sure do you want to delete?")?>')" />
							</th>
						 </tr>
						</thead>
						<tbody>
						 <?php
						 $rs_getData = $DB->Execute($query_rsConsulta);
						 while (!$rs_getData->EOF)
						 {
							
							$sql = "select code from ratings where id = ".$rs_getData->fields['rating'];
							$rsGetRating = $DB->execute($sql);
							
							?>
							<tr <?php if($counter % 2) echo " class='alt'"?>>
							 <td>
								<a href="editLive.php?edit=<?=$rs_getData->fields['id']; ?>">
								<?=$rs_getData->fields['name']; ?>
								</a>
							 </td>							 
							 <td><?=$rs_getData->fields['description']; ?></td>
							 <td><?=$rsGetRating->fields['code']; ?></td>
							 <td align="center">
								<input name='archivos[]' type='checkbox' value="<?=$rs_getData->fields['id']?>">
							 </td>
							</tr>
							<?php
							$counter++;
							$rs_getData->MoveNext();
						 }
						 
						 if($counter == 0)
						 {
							?>
							<tr>
								<td colspan="4" align="center">
									<?=_("No data found")?>
								</td>
							</tr>
							<?php
						 }
						 ?>
						</tbody>
					 </table>
					</form>
					<?php
					}
					?>
				</div><!-- // #main -->
				<script type="text/javascript" src="js/tablesort.js"></script>
				<script type="text/javascript" src="js/pagination.js"></script>
      <div class="clear"></div>
    </div><!-- // #container -->
    </div><!-- // #containerHolder -->
  <p id="footer"></p>
  </div><!-- // #wrapper -->
</body>
</html>