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
$page_security = 'SA_MACHMAINTAIN';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");

page(_($help_context = "View Machine Maintenance Schedule Inquiry"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
// include_once($path_to_root . "/tico/includes/db/tico_test_item_issue_inquiry_db.inc");

if (isset($_GET["trans_no"]))
{
	$machine_code= $_GET["trans_no"];
}

br(1);

// Sandeep
function getMachineId($machine_code)
{
	$sql = "SELECT id FROM ".TB_PREF."machine WHERE mac_code=".db_escape($machine_code);
	$result=db_query($sql, "could not get Machine Maintenance Schedules");
	$row=db_fetch_row($result);
	return $row[0];
}

  function get_machine_master_details_for_machine_code($machine_code)
  {
	  $sql = "SELECT 
   location.location_name as location,loc_grp.loc_group_name as group_name,
machine.*,eqp.mac_eqp as mac_eqp,capacity.mac_cap as mac_cap,make.mac_make as mac_make,GROUP_CONCAT(fre.mac_fre) AS mc_fre,machine.warranty_exp_date AS warranty_exp_date,sp.supp_name
FROM
   ".TB_PREF."machine machine
LEFT JOIN
    ".TB_PREF."locations location ON location.loc_code=machine.file_location
LEFT JOIN
    ".TB_PREF."location_groups loc_grp ON loc_grp.id=machine.loc_group_id
LEFT JOIN
    ".TB_PREF."machine_capacity capacity ON machine.mac_cap=capacity.id
LEFT JOIN
    ".TB_PREF."machine_equipment eqp ON machine.mac_eqp=eqp.id
LEFT JOIN
    ".TB_PREF."machine_frequency fre ON FIND_IN_SET(fre.id,machine.mac_fre)
LEFT JOIN
   ".TB_PREF."machine_make make ON machine.mac_make=make.id
 LEFT JOIN 
    suppliers sp ON machine.supplier_id=sp.supplier_id WHERE machine.mac_code=".db_escape($machine_code);
	
	 
	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
	
  }
  
   function get_machine_maintenance_schedule_for_machine_code($machine_id)
   {
	   $sql = "SELECT mps.*,meq.mac_eqp,mf.mac_fre,mps.schedule_date as schedule_date  FROM ".TB_PREF."machine_maintenance_schedule as mps LEFT JOIN ".TB_PREF."machine as m ON m.id=mps.machine_id LEFT JOIN ".TB_PREF."machine_equipment as meq ON meq.id=m.mac_eqp LEFT JOIN ".TB_PREF."machine_frequency as mf ON mf.id=mps.mac_fre WHERE mps.machine_id=".db_escape($machine_id);
	   
	   $result=db_query($sql, "could not get Machine Maintenance Schedules");

	  return $result;
   }
function get_all_test_item_for_machine_code($machine_id)
{
	$sql = "SELECT ms.*,if(ms.process_status='','Pending','Process Completed') as process_status,if(ms.remarks='','Not Defined',ms.remarks) as remarks,ms.schedule_date as schedule_date,ms.process_date as process_date,CONCAT(fa.begin,'-',fa.end) as fiscal_year,ma.mac_code,meq.mac_eqp,mf.mac_fre as machine_frequency FROM ".TB_PREF."machine_maintenance_schedule as ms LEFT JOIN ".TB_PREF."fiscal_year as fa ON fa.id=ms.fiscal_year_id LEFT JOIN ".TB_PREF."machine_frequency as mf ON mf.id=ms.mac_fre LEFT JOIN ".TB_PREF."machine as ma ON ma.id=ms.machine_id LEFT JOIN ".TB_PREF."machine_equipment as meq ON meq.id=ma.mac_eqp WHERE ms.machine_id=".db_escape($machine_id);
		
	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}

function get_item_for_preventive_maintenance_machine_code($machine_id)
{
	$sql = "SELECT ms.*,sm.stock_id,ABS(sm.qty) as qty,stm.description FROM ".TB_PREF."machine_maintenance_schedule as ms LEFT JOIN ".TB_PREF."stock_moves as sm ON sm.schedule_id=ms.id JOIN stock_master as stm ON stm.stock_id=sm.stock_id WHERE ms.id=".db_escape($machine_id);

	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}

function get_item_for_breakdown_maintenance_machine_code($machine_id)
{
	$sql = "SELECT ms.*,sm.stock_id,ABS(sm.qty) as qty,stm.description FROM ".TB_PREF."machine_maintenance_schedule as ms LEFT JOIN ".TB_PREF."stock_moves as sm ON sm.schedule_id=ms.id JOIN stock_master as stm ON stm.stock_id=sm.stock_id WHERE ms.id=".db_escape($machine_id);

	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}

function get_all_checklist_details_machine_code($schedule_id)
{
	$sql="SELECT mms.*,mc.*,mmc.*,mcr.*,if(mcr.verified=1,'Yes','No') as verified,if(mcr.remarks='','Not Defined',mcr.remarks)as remarks FROM ".TB_PREF."machine_maintenance_schedule as mms LEFT JOIN ".TB_PREF."machine as mc ON mms.machine_id=mc.id LEFT JOIN  ".TB_PREF."machine_maintenance_checklists as mmc ON mmc.equipment_id=mc.mac_eqp LEFT JOIN ".TB_PREF."mm_checklist_result as mcr ON mcr.chk_list_id=mmc.id WHERE mms.id=".db_escape($schedule_id)." AND mms.mc_problem_type=mmc.mc_problem_type AND mms.mac_fre=mmc.machine_fre_id  AND mcr.sch_id=".db_escape($schedule_id)." GROUP BY mmc.id";
	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}

function get_item_for_preventive_maintenance_all($trans_no)
{
	$sql = "SELECT mbr.*,sm.stock_id,ABS(sm.qty) as qty,stm.description FROM ".TB_PREF."mm_brkd_req as mbr LEFT JOIN ".TB_PREF."stock_moves as sm ON sm.brkd_req_id=mbr.id JOIN stock_master as stm ON stm.stock_id=sm.stock_id WHERE mbr.id=".db_escape($trans_no);
  //  display_error($sql);
	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}

function get_breakdown_maintenance_machine_code($machine_id)
{
	$sql="SELECT req.*,mc.mac_code,me.mac_eqp,info.empl_firstname,if(req.req_date='0000-00-00','Not Defined',req.req_date as req_date,IF(req.process_status='','No','Yes') as process_status,IF(req.verify_status='','No','Yes')AS verify_status,kd.description AS department,sp.supp_name
    FROM ".TB_PREF."mm_brkd_req AS req  LEFT JOIN ".TB_PREF."kv_empl_info AS info ON info.empl_id=req.operator_id LEFT JOIN ".TB_PREF."machine AS mc ON mc.id=req.machine_id LEFT JOIN ".TB_PREF."machine_equipment AS me ON mc.mac_eqp=me.id  JOIN ".TB_PREF."kv_departments AS kd ON kd.id=req.department_id LEFT JOIN suppliers as sp ON mc.supplier_id=sp.supplier_id WHERE req.machine_id=".db_escape($machine_id);

	//display_error($sql);
	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}

function get_process_of_breakdown_maintenance($break_id)
{
	$sql="SELECT sbp.prob_status,sbp.solved_by,sbp.attend_by,sbp.remarks,sbp.process_date,'Not Defined',sbp.process_date AS process_date
    FROM ".TB_PREF."breakdown_process AS sbp LEFT JOIN ".TB_PREF."mm_brkd_req AS req ON sbp.req_id=req.id WHERE req.id=".db_escape($break_id);

	display_error($sql);
	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}

$machine_id=getMachineId($machine_code);
// From Machine Master 
display_heading("Machine Details");
$machine_master = get_machine_master_details_for_machine_code($machine_code);

start_table(TABLESTYLE, "width='95%'");
$th = array(_('Machine Code'),_('Machine Model No'),_('Machine Equipment'),_('Machine Make'),_('Machine Capacity'),_('Machine Frequency'),_('Warranty Type'),_('Warranty Date'),_('Supplier'),_('Location Group'),_('Locations'), _('Description'));
inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($machine_master))
{
	alt_table_row_color($k);
	label_cell($myrow["mac_code"]);
	label_cell($myrow["mac_model_no"]);
	label_cell($myrow["mac_eqp"]);
	label_cell($myrow["mac_make"]);
	label_cell($myrow["mac_cap"]);
	label_cell($myrow["mc_fre"]);
	label_cell($wrarranty_type[$myrow["warranty_type"]]);
	label_cell(sql2date($myrow["warranty_exp_date"]));
	label_cell($myrow["supp_name"]);
	label_cell($myrow["group_name"]);
	label_cell($myrow["location"]);
	label_cell($myrow["remarks"]);  
}
end_table(1);
br(2);
// From Machine Maintenance Schedule Master 
display_heading("Machine Maintenance Schedule Details");
$schedule_master=get_machine_maintenance_schedule_for_machine_code($machine_id);
//br(2);
start_table(TABLESTYLE, "width='40%'");
$th = array(_('Machine name'),_('Frequency Type'), _('Schedule Date'), _('Remarks'));
inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter
while ($myrow1 = db_fetch($schedule_master))
{
	alt_table_row_color($k);
    label_cell($myrow1["mac_eqp"]);
	label_cell($myrow1["mac_fre"]);
	label_cell(sql2date($myrow1["schedule_date"]));
	label_cell($myrow1["remarks"]);
}

end_table(2);
br(2);

// From Preventive Machine Maintenance Inquiry

$test_items = get_all_test_item_for_machine_code($machine_id);
$k = 0;
while ($test = db_fetch($test_items))
{  
    display_heading("Machine Maintenance Schedule Inquiry");
    start_table(TABLESTYLE, "width='90%'");
    $i=1;
    $th = array(_("Trans.No."),_("Fiscal Year"), _("Machine Name"),_("Machine Code"),_("Machine Frequency"), _("Scheduled Date"),_("Remarks"),_("Processed date"),_("Process Status"));
    table_header($th);
	alt_table_row_color($k);
    label_cell($test['id'],'align="center"');
	label_cell($test['fiscal_year'],'align="center"');
    label_cell($test['mac_eqp'],'align="center"');
   	label_cell($test['mac_code'],'align="center"');
	label_cell($test['machine_frequency'],'align="center"');
	label_cell(sql2date($test['schedule_date']),'align="center"');
	label_cell($test['remarks'],'align="center"');
	label_cell(sql2date($test['process_date'],'align="center"'));
	label_cell($test['process_status'],'align="center"');   
	
    $preventive_data = get_item_for_preventive_maintenance_machine_code($test['id']);
	$check_list = get_all_checklist_details_machine_code($test['id'],$test["mac_fre"],$test['mc_problem_type']);
	end_table();
	br(1);
	
	display_heading("Items Used For Preventive Maintenance");
    $k = 0;
    start_table(TABLESTYLE, "width='60%'");
    $th = array(_("Item Code"),_("Item Name"),_("Item Quantity"));
    table_header($th);
	while ($test = db_fetch($preventive_data))
   {
    alt_table_row_color($k);
	label_cell($test['stock_id'],'align="left"');
   	label_cell($test['description'],'align="left"');
	qty_cell($test['qty'],'align="center"');
   }
   end_table();
   br(1);
   display_heading("Machine Maintenance Checklist Inquiry");
   $k = 0;
   start_table(TABLESTYLE, "width='50%'");
   $th = array(_("SI No."),_("Details"), _("Verified"),_("Remarks"));
   table_header($th);
	$i=1;
	while ($check = db_fetch($check_list))
{  
    alt_table_row_color($k);
    label_cell($i,'align="center"');
	label_cell($check['details'],'align="center"');
    label_cell($check['verified'],'align="center"');
   	label_cell($check['remarks'],'align="center"');
	++$i;
}
end_table(1);
br(3);

}
// Breakdown Maintenance Request Inquiry
   $break_date=get_breakdown_maintenance_machine_code($machine_id);
    global $solved_by;
	global $brkd_pro_status;
	global $brkd_pro_status;
	$preventive_data1;
	while ($break = db_fetch($break_date))
{  display_heading("Machine Breakdown Maintenance");
	
    $k = 0;
    start_table(TABLESTYLE, "width='90%'");
    $th = array(_("Reference"),_("Machine Name"),_("Machine Code"), _("Description"),_("Department"), _("Operator Name"), _("Request Date"),_("Problem Description"),_("Verify Status"),_("Supplier Name"),_("Process Status"));
    table_header($th);
    alt_table_row_color($k);

    label_cell($break['ref'],'align="center"');
	label_cell($break['mac_eqp'],'align="center"');
	label_cell($break['mac_code'],'align="center"');
	label_cell($break['description'],'align="center"');
	label_cell($break['department'],'align="center"');
	label_cell($break['empl_firstname'],'align="center"');
    label_cell(sql2date($break['req_date']),'align="center"');
    label_cell($break['prob_desc'],'align="center"');
	label_cell($break['verify_status'],'align="center"');
	label_cell($break['supp_name'],'align="center"');
    label_cell($break['process_status'],'align="center"');
	$preventive_data1=get_item_for_preventive_maintenance_all($break['id']);
	$break_date1=get_process_of_breakdown_maintenance($break['id']);
	end_table();
	br();
	 display_heading("Process Of Breakdown Maintenance");
	$k = 0;
    start_table(TABLESTYLE, "width='60%'");
    $th = array(_("Problem Status"),_("Solved By"),_("Attend By"),_("Remarks"),_("Process Date"));
    table_header($th);
		while ($break_process = db_fetch($break_date1))
{  
    alt_table_row_color($k);
	label_cell($brkd_pro_status[$break_process['prob_status']],'align="center"');
	label_cell($solved_by[$break_process['solved_by']],'align="center"');
	label_cell($break_process['attend_by'],'align="center"');
	label_cell($break_process['remarks'],'align="center"');
	label_cell(sql2date($break_process['process_date']),'align="center"');
}
	end_table();
	br();
	display_heading("Issues Items For Breakdown Maintenance");
    start_table(TABLESTYLE, "width='60%'");
    $th = array(_("Item Code"),_("Item Name"),_("Item Quantity"));
    table_header($th);
	while ($test = db_fetch($preventive_data1))
   {
    alt_table_row_color($k);
	label_cell($test['stock_id'],'align="left"');
   	label_cell($test['description'],'align="left"');
	qty_cell($test['qty'],'align="center"');
   }
   end_table(1);
 
   
br(3);
}

br(2);

end_page(true, false, false, ST_INVTRTIREQ, $trans_no);
