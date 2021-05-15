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

page(_($help_context = "View Machine Breakdown Maintenance"), true);

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
//include_once($path_to_root . "/tico/includes/db/tico_test_item_issue_inquiry_db.inc");

if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}

br(1);
function get_item_for_breakdown_maintenance_id($trans_no)
{
$sql="SELECT req.ref,stock.description,items.stock_id,items.qty,items.qty_rec FROM ".TB_PREF."break_down_request req 
left join ".TB_PREF."break_down_request_items items on items.break_down_id=req.id
left join stock_master stock on stock.stock_id=items.stock_id 
WHERE req.id=".db_escape($trans_no);
$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}
function get_define_preventive_maintenance($trans_no)
{
	$sql="SELECT req.*,mc.mac_code,me.mac_eqp,info.empl_firstname,req.req_date as req_date,IF(req.process_status='','No','Yes') as process_status,IF(req.verify_status='','No','Yes')AS verify_status,kd.description AS department,sp.supp_name
FROM ".TB_PREF."mm_brkd_req AS req LEFT JOIN ".TB_PREF."break_down_request as entry ON entry.schedule_id=req.id LEFT JOIN ".TB_PREF."kv_empl_info AS info ON info.empl_id=req.operator_id LEFT JOIN ".TB_PREF."machine AS mc ON mc.id=req.machine_id LEFT JOIN ".TB_PREF."machine_equipment AS me ON mc.mac_eqp=me.id  JOIN ".TB_PREF."kv_departments AS kd ON kd.id=req.department_id LEFT JOIN suppliers as sp ON mc.supplier_id=sp.supplier_id WHERE entry.id=".db_escape($trans_no);

	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}

function get_process_of_breakdown_maintenance_based_on_id($trans_no)
{
	
    $sql="SELECT sbp.prob_status,sbp.solved_by,sbp.attend_by,sbp.remarks,sbp.process_date AS process_date
    FROM ".TB_PREF."breakdown_process AS sbp 
	LEFT JOIN ".TB_PREF."mm_brkd_req AS req ON sbp.req_id=req.id WHERE req.id=".db_escape($trans_no);


	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}
function get_issued_entry_view($req_id)
{
	$sql="SELECT * FROM ".TB_PREF."break_down_request as req INNER JOIN ".TB_PREF."break_down_issue_entry as entry ON req.id=entry.req_id WHERE req.id=".db_escape($req_id)."";
	$result=db_query($sql, "could not get issue entry details!");
	return $result;
}
function get_issued_items_details_for_view($entry_id)
{
		$sql="SELECT sm.*,stm.description FROM ".TB_PREF."stock_moves as sm INNER JOIN stock_master as stm ON stm.stock_id=sm.stock_id WHERE sm.trans_no=".db_escape($entry_id)." AND sm.type=1104 AND sm.qty>0";
	$result=db_query($sql, "could not get Machine issue item details!");

	return $result;
}
    display_heading("Machine Breakdown Maintenance");
    br();
    $test_items1 = get_define_preventive_maintenance($trans_no);
	global $brkd_pro_status;
	global$solved_by;
    $k = 0;
    start_table(TABLESTYLE, "width='90%'");
    $th = array(_("Reference"),_("Machine Name"),_("Machine Code"),_("Problem Type"), _("Description"),_("Department"), _("Operator Name"), _("Request Date"),_("Problem Description"),_("Verify Status"),_("Supplier Name"),_("Process Status"));
    table_header($th);
	
while ($break = db_fetch($test_items1))
{  
global $mc_analysis_type;
    alt_table_row_color($k);
    label_cell($break['ref'],'align="center"');
	label_cell($break['mac_eqp'],'align="center"');
	label_cell($break['mac_code'],'align="center"');
	label_cell($mc_analysis_type[$break['mc_problem_type']],'align="center"');
	label_cell($break['description'],'align="center"');
	label_cell($break['department'],'align="center"');
	label_cell($break['empl_firstname'],'align="center"');
    label_cell($break['req_date'],'align="center"');
    label_cell($break['prob_desc'],'align="center"');
	label_cell($break['verify_status'],'align="center"');
	label_cell($break['supp_name'],'align="center"');
    label_cell($break['process_status'],'align="center"');
}
	
    end_table();
br(2);

/* $break_date=get_process_of_breakdown_maintenance_based_on_id($trans_no);
    display_heading("Process Of Breakdown Maintenance");
	global $brkd_pro_status;
    $k = 0;
    start_table(TABLESTYLE, "width='60%'");
    $th = array(_("Process Date"),_("Solved By"),_("Attend By"),_("Problem Status"),_("Remarks"));
    table_header($th);
	global $solved_by;
	while ($break = db_fetch($break_date))
{  
    alt_table_row_color($k);
	label_cell($break['process_date'],'align="center"');
	label_cell($solved_by[$break['solved_by']],'align="center"');
	label_cell($break['attend_by'],'align="center"');
	label_cell($brkd_pro_status[$break['prob_status']],'align="center"');
	label_cell($break['remarks'],'align="center"');
	
	
}
end_table(4); */

$preventive_data=get_item_for_breakdown_maintenance_id($trans_no);
display_heading("Requested Item Details For Breakdown Maintenance");
start_table(TABLESTYLE, "width='60%'");
    $th = array(_("Reference"),_("Item Code"),_("Item Name"),_("Requested Qty"),_("Issued Qty"));
    table_header($th);
	while ($test = db_fetch($preventive_data))
   {
    alt_table_row_color($k);
	label_cell($test['ref'],'align="left"');
	label_cell($test['stock_id'],'align="left"');
   	label_cell($test['description'],'align="left"');
	qty_cell($test['qty'],'align="center"');
	qty_cell($test['qty_rec'],'align="center"');
	
   }
end_table();
br();
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
end_page(true, false, false, ST_BREAKMAINTENTRY, $trans_no);
