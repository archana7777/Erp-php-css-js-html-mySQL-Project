<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
//-----------------------------------------------------------------------------
//
//	Entry/Modify Sales Quotations
//	Entry/Modify Sales Order
//	Entry Direct Delivery
//	Entry Direct Invoice
//

$path_to_root = "../..";
//echo '<pre>'; print_r($_POST); die;
include_once($path_to_root . "/includes/session.inc");

		$id=$_POST["id"];
		$machine_id=$_POST["machine_id"];
		$mac_fre_id=$_POST["mac_fre_id"];
		$supplier_id=$_POST["supplier_id"];
		$main_remarks=$_POST["main_remarks"];
		$chq_sql="SELECT mmc.*,mms.*,mmc.id as check_id FROM ".TB_PREF."machine_maintenance_schedule as mms LEFT JOIN ".TB_PREF."machine_maintenance_checklists as mmc ON mms.mac_fre=mmc.machine_fre_id WHERE mms.id=".db_escape($id)." AND mms.mc_problem_type=mmc.mc_problem_type";
	$result=db_query($chq_sql,"something was went wrong!");
	$records_count = db_num_rows($result);
	if($records_count>0){
		while($row=db_fetch($result))
		{
			$list_id=$row['check_id'];
			
			$sql1="INSERT INTO ".TB_PREF."mm_checklist_result(sch_id,machine_id,machine_fre_id,chk_list_id,verified,remarks) VALUES ('".$id."','".$machine_id."','".$mac_fre_id."','".$list_id."','".$_POST[$list_id.'_verified']."','".$_POST[$list_id.'_remarks']."')";
				
			db_query($sql1,"something went wrong");
			date_default_timezone_set('Asia/Kolkata');
			$process_date = date('Y-m-d H:i:s');
			$pro_sql="UPDATE ".TB_PREF."machine_maintenance_schedule SET supplier_id='$supplier_id',process_status='1' ,process_date='$process_date' WHERE id=".db_escape($id)."";
			db_query($pro_sql);
		}
	}
	$pro_sql="UPDATE ".TB_PREF."pre_maintenance_req SET process_status='1'   WHERE schedule_id=".db_escape($id)."";
		db_query($pro_sql);
		$req_sql="UPDATE ".TB_PREF."machine_maintenance_schedule SET req_status='1',process_remarks='$main_remarks' WHERE id=".db_escape($id)."";
	db_query($req_sql);
header("Location: machine_maintenance_inquiry.php");


?>