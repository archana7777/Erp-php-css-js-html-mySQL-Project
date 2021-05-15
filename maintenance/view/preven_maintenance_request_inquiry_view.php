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
$page_security = 'SA_MAIN_REQINQ';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");

page(_($help_context = "View Preventive Maintenance Request"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/tico/includes/db/tico_test_item_issue_inquiry_db.inc");



if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}

br(1);
// Sandeep


function get_define_preventive_maintenance($trans_no)
{
	$sql = "SELECT ms.id,if(ms.schedule_date='0000-00-00','Not Defined',DATE_FORMAT(ms.schedule_date,'%d/%m/%Y')) as schedule_date,ms.remarks,ma.mac_code,meq.mac_eqp,ms.mc_problem_type FROM ".TB_PREF."machine_maintenance_schedule AS ms LEFT JOIN ".TB_PREF."machine as ma ON ma.id=ms.machine_id LEFT JOIN ".TB_PREF."machine_equipment as meq ON meq.id=ma.mac_eqp
	WHERE ms.id=".db_escape($trans_no);
	$result=db_query($sql, "could not get Machine Maintenance Schedules");
	return $result;
}

function get_define_item_for_preventive_maintenance($trans_no)
{	
	$sql = "SELECT pmrt.stock_id,pmrt.qty,stm.description,pmrt.qty_rec FROM ".TB_PREF."pre_maintenance_req_items as pmrt INNER JOIN ".TB_PREF."pre_maintenance_req as pmr ON pmr.id=pmrt.pre_req_id INNER JOIN stock_master as stm ON stm.stock_id=pmrt.stock_id WHERE pmr.schedule_id=".db_escape($trans_no);
	$result=db_query($sql, "could not get Machine Maintenance Schedules");
	return $result;
}
function get_issued_items_details_for_view($entry_id)
{
	$sql="SELECT sm.*,stm.description FROM ".TB_PREF."stock_moves as sm INNER JOIN stock_master as stm ON stm.stock_id=sm.stock_id WHERE sm.trans_no=".db_escape($entry_id)." AND sm.type=1103 AND sm.qty>=0";
	$result=db_query($sql, "could not get Machine issue item details!");
	return $result;
}
function get_issued_entry_view($req_id)
{
	$sql="SELECT * FROM ".TB_PREF."pre_maintenance_req as req INNER JOIN ".TB_PREF."preventive_issue_entry as entry ON req.id=entry.req_id WHERE req.schedule_id=".db_escape($req_id)."";
	$result=db_query($sql, "could not get issue entry details!");
	return $result;
}
function get_all_checklist_details_inquiry_view($selected_id)
{
	$sql="SELECT mms.*,mc.*,mmc.*,mcr.*,if(mcr.verified=1,'Yes','No') as verified,if(mcr.remarks='','Not Defined',mcr.remarks)as remarks FROM ".TB_PREF."machine_maintenance_schedule as mms LEFT JOIN ".TB_PREF."machine as mc ON mms.machine_id=mc.id LEFT JOIN  ".TB_PREF."machine_maintenance_checklists as mmc ON mmc.equipment_id=mc.mac_eqp LEFT JOIN ".TB_PREF."mm_checklist_result as mcr ON mcr.chk_list_id=mmc.id WHERE mcr.sch_id=".db_escape($selected_id)." AND mms.mc_problem_type=mmc.mc_problem_type AND mms.mac_fre=mmc.machine_fre_id GROUP BY mmc.id";
	$result=db_query($sql, "could not get Machine Maintenance Schedules");
	return $result;
}
function get_process_remarks($trans_no)
{
	$sql="SELECT process_remarks FROM ".TB_PREF."machine_maintenance_schedule WHERE id=".db_escape($trans_no)."";
	$res=db_query($sql, "could not get process Remarks!");
	$result=db_fetch($res);
	return $result['process_remarks'];
}
display_heading("Preventive Maintenance Requested");
br();
$test_items1 = get_define_preventive_maintenance($trans_no);
$k = 0;
start_table(TABLESTYLE, "width='70%'");
    $th = array(_("Machine Name"),_("Machine Code"),_("Schedule Id"),_("Problem Type"), _("schedule Date"), _("Remarks"));
    table_header($th);
	
	while ($test = db_fetch($test_items1))
{
	
	
    alt_table_row_color($k);
    label_cell($test['mac_eqp'],'align="center"');
	label_cell($test['mac_code'],'align="center"');
//	label_cell($test['ref'],'align="center"');
	label_cell($test['id'],'align="center"');
	global $mc_analysis_type;
	label_cell($mc_analysis_type[$test['mc_problem_type']],'align="center"');
    label_cell($test['schedule_date'],'align="center"');
	label_cell($test['remarks'],'align="center"');
}
	
    end_table();

br(2);
display_heading("Preventive Maintenance Requested Items");
br();

$items = get_define_item_for_preventive_maintenance($trans_no);
$k = 0;
start_table(TABLESTYLE, "width='70%'");
    $th = array(_("Item Code"),_("Item Name"),_("Req Qty"),_("Issued Qty"));
    table_header($th);
	
	while ($test = db_fetch($items))
{
	
    alt_table_row_color($k);
    label_cell($test['stock_id'],'align="left"');
   	label_cell($test['description'],'align="left"');
	qty_cell($test['qty'],'align="center"');
	qty_cell($test['qty_rec'],'align="center"');
}
	end_table();
br(2);
display_heading("Issued Items Details");


$issue_det = get_issued_entry_view($trans_no);
start_table(TABLESTYLE, "width='70%'");
		$th = array(_("Reference"),_("Item Code"),_("Item Name"),_("Issued  Qty"),_("Taken By"),_("Issued Date"));
		table_header($th);
while ($entry_dt=db_fetch($issue_det)){
	
	$item_det = get_issued_items_details_for_view($entry_dt['issue_id']);
	$k = 0;
	
		
	while ($issue = db_fetch($item_det))
	{
		
		alt_table_row_color($k);
		label_cell($entry_dt['reference'],'align="left"');
		label_cell($issue['stock_id'],'align="left"');
		label_cell($issue['description'],'align="left"');
		qty_cell($issue['qty'],'align="center"');
		label_cell($entry_dt['taken_by'],'align="center"');
		label_cell(sql2date($entry_dt['issue_date']),'align="center"');
	}
}
	end_table();
	br(2);
$check_list = get_all_checklist_details_inquiry_view($trans_no);

display_heading("Machine Maintenance Checklist Inquiry");
br(1);
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
	$i++;

}
// label_cells(_("Reference"), $trans['reference'], "class='tableheader2'");
end_table(1);
start_table(TABLESTYLE, "width='50%'");
	$process_remarks=get_process_remarks($trans_no);
label_cells(_("Process Remarks"), $process_remarks, "class='tableheader2'");
end_table(2);
end_page(true, false, false, ST_PREVENTITEM, $trans_no);
