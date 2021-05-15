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
display_heading("Machine Maintenance Schedule Inquiry");
if (isset($_GET["trans_no"]))
{
	$trans_no = $_GET["trans_no"];
}
br(1);
// Sandeep
function get_all_test_item_issue_inquiry_view($selected_id)
{
	$sql = "SELECT ms.*,if(ms.process_status='','Pending','Process Completed') as process_status,if(ms.remarks='','Not Defined',ms.remarks) as remarks,if(ms.schedule_date='0000-00-00','Not Defined',DATE_FORMAT(ms.schedule_date,'%m/%d/%Y')) as schedule_date,CONCAT(DATE_FORMAT(fa.begin,'%m/%d/%Y'),'-',DATE_FORMAT(fa.end,'%m/%d/%Y')) as fiscal_year,IF(ms.process_date='0000-00-00','',DATE_FORMAT(ms.process_date,'%m/%d/%Y')) AS process_date,ma.mac_code,meq.mac_eqp,mf.mac_fre as machine_frequency FROM ".TB_PREF."machine_maintenance_schedule as ms LEFT JOIN ".TB_PREF."fiscal_year as fa ON fa.id=ms.fiscal_year_id LEFT JOIN ".TB_PREF."machine_frequency as mf ON mf.id=ms.mac_fre LEFT JOIN ".TB_PREF."machine as ma ON ma.id=ms.machine_id LEFT JOIN ".TB_PREF."machine_equipment as meq ON meq.id=ma.mac_eqp WHERE ms.id=".db_escape($selected_id);
    
	
	$result=db_query($sql, "could not get Machine Maintenance Schedules");

	return $result;
}
function get_define_item_for_preventive_maintenance($selected_id)
{	
	$sql = "SELECT pmrt.stock_id,pmrt.qty,stm.description,pmrt.qty_rec FROM ".TB_PREF."pre_maintenance_req_items as pmrt LEFT JOIN ".TB_PREF."pre_maintenance_req as pmr ON pmr.id=pmrt.pre_req_id LEFT JOIN stock_master as stm ON stm.stock_id=pmrt.stock_id WHERE pmr.schedule_id=".db_escape($selected_id);
$result=db_query($sql, "could not get Machine Maintenance Schedules");
	return $result;
}
function get_all_checklist_details_inquiry_view($selected_id)
{
		/* $sql="SELECT mms.*,mc.*,mmc.*,mcr.*,if(mcr.verified=1,'Yes','No') as verified,if(mcr.remarks='','Not Defined',mcr.remarks)as remarks FROM ".TB_PREF."machine_maintenance_schedule as mms LEFT JOIN ".TB_PREF."machine as mc ON mms.machine_id=mc.id LEFT JOIN  ".TB_PREF."machine_maintenance_checklists as mmc ON mmc.equipment_id=mc.mac_eqp LEFT JOIN ".TB_PREF."mm_checklist_result as mcr ON mcr.chk_list_id=mmc.id WHERE mms.id=".db_escape($selected_id)." AND mms.mc_problem_type=mmc.mc_problem_type AND mms.mac_fre=mmc.machine_fre_id GROUP BY mmc.id"; */	
	$sql="SELECT mms.*,mc.*,mmc.* FROM ".TB_PREF."machine_maintenance_schedule as mms LEFT JOIN ".TB_PREF."machine as mc ON mms.machine_id=mc.id LEFT JOIN  ".TB_PREF."machine_maintenance_checklists as mmc ON mmc.equipment_id=mc.mac_eqp  WHERE mms.id=".db_escape($selected_id)." AND mms.mc_problem_type=mmc.mc_problem_type AND mms.mac_fre=mmc.machine_fre_id GROUP BY mmc.id";
	$result=db_query($sql, "could not get Machine Maintenance Schedules");
	return $result;
}
function get_issued_items_details_for_view($entry_id)
{
		$sql="SELECT sm.*,stm.description FROM ".TB_PREF."stock_moves as sm LEFT JOIN stock_master as stm ON stm.stock_id=sm.stock_id WHERE sm.trans_no=".db_escape($entry_id)." AND sm.type=1103 AND sm.qty>=0";
		// display_error($sql);
	$result=db_query($sql, "could not get Machine issue item details!");

	return $result;
}
function get_issued_entry_view($req_id)
{
	$sql="SELECT * FROM ".TB_PREF."pre_maintenance_req as req LEFT JOIN ".TB_PREF."preventive_issue_entry as entry ON req.id=entry.req_id WHERE req.schedule_id=".db_escape($req_id)."";
	$result=db_query($sql, "could not get issue entry details!");
	return $result;
}
$test_items = get_all_test_item_issue_inquiry_view($trans_no);

$k = 0;

    start_table(TABLESTYLE, "width='90%'");
    $th = array(_("Trans.No."),_("Fiscal Year"), _("Machine Name"),_("Machine Code"),_("Machine Frequency"),_("Problem Type"), _("Scheduled Date"),_("Remarks"),_("Processed date"),_("Process Status"));
    table_header($th);

while ($test = db_fetch($test_items))
{
	alt_table_row_color($k);
    label_cell($test['id'],'align="center"');
	label_cell($test['fiscal_year'],'align="center"');
    label_cell($test['mac_eqp'],'align="center"');
   	label_cell($test['mac_code'],'align="center"');
	label_cell($test['machine_frequency'],'align="center"');
	global $mc_analysis_type;
	label_cell($mc_analysis_type[$test['mc_problem_type']],'align="center"');
	label_cell($test['schedule_date'],'align="center"');
	label_cell($test['remarks'],'align="center"');
	label_cell($test['process_date'],'align="center"');
	label_cell($test['process_status'],'align="center"');   
}
end_table(1);
br(2);

display_heading("Items Details For Preventive Maintenance");
br();
$test_items1 = get_define_item_for_preventive_maintenance($trans_no);
$k = 0;
start_table(TABLESTYLE, "width='60%'");
    $th = array(_("Item Code"),_("Item Name"),_("Req Qty"),_("Issued Qty"));
    table_header($th);
	
	while ($test = db_fetch($test_items1))
{
	
	
    alt_table_row_color($k);
	label_cell($test['stock_id'],'align="left"');
   	label_cell($test['description'],'align="left"');
	qty_cell($test['qty'],'align="center"');
	qty_cell($test['qty_rec'],'align="center"');
	}
    end_table();
br(2);
// Machine Maintenance Checklist Details
display_heading("Issued Items Details");
br();

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
    label_cell("No",'align="center"');
    // label_cell($check['verified'],'align="center"');
   	label_cell("",'align="center"');
	$i++;

}
end_table(1);


end_page(true, false, false, ST_INVTRTIREQ, $trans_no);
