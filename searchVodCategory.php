<?php
include("includes/connection.php");
include("session.php");

$strFind = $_POST['strBusca'];
$condition = $_POST['condition'];
$strWhere="";

//Delete multiple
$arrArchivos = $_POST['archivos'];
$U = count($arrArchivos);

if($U > 0)
{
 foreach($arrArchivos as $id)
 {
  $query_rsDel = "DELETE FROM vodcategories WHERE id = $id";
	$rsDel = $DB->Execute($query_rsDel);
  redirect($currentPage);
 }
}


if($_POST['strFind']!= "")
{
	switch ($condition)
	{
		case "name":
		
			if($strWhere != "") $strWhere= " and ";
			$strWhere .= " name LIKE '%" . $strFind . "%' ";
			break;
	
		if($strWhere != "")	$strWhere = " where " . $strWhere;	
	}
	$sqlGet = "SELECT * FROM vodcategories "  . $strWhere;
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
					<h2><a href="#"><?=_("VOD Categories")?></a> &raquo; <a href="#" class="active"><?=_("Find VOD Categories")?></a></h2>
						<form method="post" action="<?=$currentPage?>" class="jNice">
						<fieldset>
						<p>
							<label><?=_("Select a search criteria")?></label>
							<input type="text" name="strFind" value="<?=$strFind ?>" class="text-long">
						</p>
						<p>
							<label><?=_("Select a search filter")?></label>
							<select name="condition">
								<option value="name" <?php if ($condition == "name") echo "selected='selected'" ?>><?=_("Category Name")?></option>
							</select>
						</p>
						<p>
							<label>&nbsp;</label>
							<input name="find" type="submit" value="<?=_("Find")?>" />
						</p>
						</fieldset>
						</form>
		
				<?php
					
					if($_POST['strFind']!= "")
					{
						$counter = 0;
						?>
						<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
						<table class="no-arrow rowstyle-alt colstyle-alt paginate-10 max-pages-5" >
						 <thead>
							<tr>
							 <th class="sortable-keep fd-column-0"><b><?=_("Edit")?></b></th>
							 <th class="sortable-keep fd-column-1"><b><?=_("Description")?></b></th>
							 <th class="sortable-keep fd-column-2"><b><?=_("Rating")?></b></th>
							 <th align="center" style="padding:5px 0px 5px 0px">
							 <input class="button-submit" type="submit" value="<?=_("Delete Selected")?>" onclick="return confirm('<?=_("Are you sure do you want to delete?")?>')" />
							</th>
						 </tr>
						</thead>
						<tbody>
						 <?php
						 $rs_getData = $DB->Execute($sql);
						 while (!$rs_getData->EOF)
						 {
							?>
							<tr <?php if($counter % 2) echo " class='alt'"?>>
							 <td>
								<a href="<?=$currentPage?>?edit=<?=$rs_getData->fields['id']; ?>">
								<td><?=$rs_getData->fields['name']; ?></td>
								</a>
							 </td>							 
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
      <div class="clear"></div>
    </div><!-- // #container -->
    </div><!-- // #containerHolder -->
  <p id="footer"></p>
  </div><!-- // #wrapper -->
</body>
</html>