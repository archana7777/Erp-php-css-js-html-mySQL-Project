<?php

$page_security = 'SA_MACHMAINTAIN';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");
//include_once($path_to_root . "/invivo/manage/study_file_checklist_edit_db.php");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "MACHINE MAINTENANCE CHECKLIST "), false, false, "", $js);

?>
<html lang="en">
<head>
<style>
select{
	width:150px;
}
</style>
    <link rel="stylesheet" href="<?php echo $path_to_root . "/js/jquery-ui.css" ?>">
	<link rel="stylesheet" href="<?php echo $path_to_root . "/js/jquery.timepicker.css" ?>">
    <script src="<?php echo $path_to_root . "/js/jquery-1.10.2.js" ?>"></script>
   <script src="<?php echo $path_to_root . "/js/jquery-ui.js"?>"></script>
   <script src="<?php echo $path_to_root . "/js/jquery.timepicker.js"?>"></script>
</head>
<form   action="add_mm_checklist.php" method="POST">   
<body>

<center> 
 <h2><u><strong>Machine Maintenance Checklist</strong></u></h2> </center>
 <table width="100%" align="center" >
 <tr>

 <?php
 machine_name_list_row(_("Machine Name:"), 'machine_id', null, false, true);
display_error($_POST['machine_id']);
	frequency_name_list_cells(_("Frequency Type:"), 'mac_fre',null, false, true,$_POST['machine_id']);
	$chq_res=get_data($_POST['machine_id'],$_POST['mac_fre']);
?>
 </tr>
</table>
<?php 
function get_data($machine_id,$mac_fre)
{
	
	$chq_sql="SELECT details  FROM ".TB_PREF."machine_maintenance_checklists WHERE machine_id=".db_escape($machine_id)." and `machine_fre_id`=".db_escape($mac_fre)."";
display_error($chq_sql);
	return db_query($chq_sql);
}
	
?>
<table width="100%" align="center" >
<tr>
<th  class="tableheader" >SI NO.</th>
<th  class="tableheader" >DETAILS</th>
<th  class="tableheader" >VERIFIED</th>
<th  class="tableheader" >Remarks</th>
</tr>

<?php 
display_error("rgrag");			
$i=0;
while($row=db_fetch($chq_res))
{
	//$i=$i+1;
	display_error($row["order_no"]);
	$i=$row["order_no"];
if($row["order_no"]!=null){
	?>
	<tr class="evenrow">
		
	<td align="center"><?php echo $row["order_no"]; ?></td>
	<td align="left"><?php echo $row["details"];?></td>
	<td align="center"><?php check_cells_td('', $row['check_id'].'_verified', 
			null, false, false); ?></td>
	<td><textarea name =" <?php echo $row['check_id'];?>_remarks"  ></textarea></td>
	</tr>
	
	<?php 
}
}
	
if($i==0)
{
	?>
	<tr><td colspan="4"><h2>No Records Found!</h2></td></tr>
	<?php
}
?>
	<input type="hidden" name="id" value=" <?php  echo $id; ?>"/>	 
	<input type="hidden" name="machine_id" value=" <?php  echo $mac_res["machine_id"]; ?>"/>	 
	<input type="hidden" name="mac_fre_id" value=" <?php  echo $mac_res["mac_fre_id"]; ?>"/>
	<input type="hidden" name="supplier_id" value=" <?php  echo $mac_res["supplier_id"]; ?>"/>
<br></br>
</table>
<center>
<?php if($i!=0)
{ ?>
	<input type="submit" id="submit"  name="Submit " value="Submit" align="center">
<?php } ?>
	</center>

</body>
</form>
<?php 
	
end_page();
?>
