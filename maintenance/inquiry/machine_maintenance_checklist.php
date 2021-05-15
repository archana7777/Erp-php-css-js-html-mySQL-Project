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
<?php $id=$_GET["id"];
	$sql="SELECT mms.*,m.mac_code,mf.mac_fre,me.mac_eqp,mms.mac_fre as mac_fre_id,m.supplier_id FROM ".TB_PREF."machine_maintenance_schedule as mms LEFT JOIN ".TB_PREF."machine as m ON mms.machine_id=m.id LEFT JOIN ".TB_PREF."machine_frequency as mf ON mms.mac_fre=mf.id LEFT JOIN ".TB_PREF."machine_equipment as me ON m.mac_eqp=me.id WHERE mms.id=".db_escape($id)."";
	$res=db_query($sql);
	$mac_res=db_fetch($res);
?>
<center> 
 <h2><u><strong>Machine Maintenance Checklist</strong></u></h2> </center>
 <table width="100%" align="center" >
 <tr>
 <th><h3>Machine Code:<?php echo $mac_res["mac_code"];?><h3></th>
 <th><h3>Machine Name:<?php echo $mac_res["mac_eqp"];?><h3></th>
 <th><h3>Machine Frequency:<?php echo $mac_res["mac_fre"];?><h3></th>
 <th><h3>Problem Type:<?php 
 global $mc_analysis_type;
 echo $mc_analysis_type[$mac_res["mc_problem_type"]];?><h3></th>
 </tr>
</table>
<?php 
$chq_sql="SELECT mmc.*,mms.*,mmc.id as check_id FROM ".TB_PREF."machine_maintenance_schedule as mms LEFT JOIN ".TB_PREF."machine as mc ON mms.machine_id=mc.id LEFT JOIN  ".TB_PREF."machine_maintenance_checklists as mmc ON mmc.equipment_id=mc.mac_eqp LEFT JOIN ".TB_PREF."mm_checklist_result as mcr ON mcr.chk_list_id=mmc.id WHERE mms.id=".db_escape($id)." AND mms.mc_problem_type=mmc.mc_problem_type AND mms.mac_fre=mmc.machine_fre_id GROUP BY mmc.id";
	$chq_res=db_query($chq_sql);
	
?>
<table width="100%" align="center" >
<tr>
<th  class="tableheader" >SI NO.</th>
<th  class="tableheader" >DETAILS</th>
<th  class="tableheader" >VERIFIED</th>
<th  class="tableheader" >Remarks</th>
</tr>

<?php 
			
$i=0;
while($row=db_fetch($chq_res))
{
	//$i=$i+1;
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
	<table align="center"><td ><strong>Remarks</strong></td><td><textarea name ="main_remarks"  ></textarea></td></table>
	<input type="hidden" name="id" value=" <?php  echo $id; ?>"/>	 
	<input type="hidden" name="machine_id" value=" <?php  echo $mac_res["machine_id"]; ?>"/>	 
	<input type="hidden" name="mac_fre_id" value=" <?php  echo $mac_res["mac_fre_id"]; ?>"/>
	<input type="hidden" name="supplier_id" value=" <?php  echo $mac_res["supplier_id"]; ?>"/>
<br></br>
</table>
<center>
<?php  // if($i!=0) { ?>
	<input type="submit" id="submit"  name="Submit " value="Submit" align="center">
<?php // } ?>
	</center>

</body>
</form>
<?php 
	
end_page();
?>
