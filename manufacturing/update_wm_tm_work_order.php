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
$page_security = 'SA_MANUFWMTMUPDATE';
$path_to_root = "..";

include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = "Work Order Update (WM-TM)"), false, false, "", $js);

if (isset($_GET['UpdatedID'])) 
{
	$id = $_GET['UpdatedID'];
   	display_notification(_("The work order has been updated by Works Manager."));

    display_note(get_trans_view_str(ST_WORKORDER, $id, _("View this Work Order")));

	display_footer_exit();
}


//ravi
$mode=0; // default
if(isset($_GET['trans_no']))
{
	$mode=1; // first time pageloading
}
//-----------------------------------------------------------------------------------------------

if (isset($_GET['AddedID'])) 
{
	$id = $_GET['AddedID'];
   	display_notification(_("The work order issue has been entered."));

    display_note(get_trans_view_str(ST_WORKORDER, $id, _("View this Work Order")));

   	display_note(get_gl_view_str(ST_WORKORDER, $id, _("View the GL Journal Entries for this Work Order")), 1);

   	hyperlink_no_params("search_work_orders.php", _("Select another &Work Order to Process"));

	display_footer_exit();
}
//--------------------------------------------------------------------------------------------------

function line_start_focus() {
  global 	$Ajax;

  $Ajax->activate('items_table');
  set_focus('_stock_id_edit');
}

//--------------------------------------------------------------------------------------------------

function handle_new_order()
{
	if (isset($_SESSION['issue_items']))
	{
		$_SESSION['issue_items']->clear_items();
		unset ($_SESSION['issue_items']);
	}

     $_SESSION['issue_items'] = new items_cart(ST_MANUISSUE);
     $_SESSION['issue_items']->order_id = $_GET['trans_no'];
}

//-----------------------------------------------------------------------------------------------
function can_process()
{
	if (strlen($_POST['rm_available_options']) == 0) 
	{
		display_error(_("The Raw Material Available Options must be entered."));
		set_focus('rm_available_options');
		return false;
	} 
	if (strlen($_POST['machine_booked']) == 0) 
	{
		display_error(_("The Machine Booked information must be entered."));
		set_focus('machine_booked');
		return false;
	}
	
	return true;
}

if (isset($_POST['Process']) && can_process())
{

	// updated in wm_tm_work_orders table
	update_wm_tm_work_order($_SESSION['issue_items']->order_id,$_POST['wo_memo_status'],$_POST['proposed_work_order_memo'],$_POST['units_reqd_status'],$_POST['proposed_units_reqd'],$_POST['units_reqs_per_day_status'],$_POST['proposed_units_reqs_per_day'],$_POST['execute_by_status'],$_POST['proposed_execute_by'],$_POST['required_by_status'],$_POST['proposed_required_by'],$_POST['grace_period'],$_POST['rm_available_options'],$_POST['rm_procurement'],$_POST['rm_remarks'],$_POST['machine_booked'],$_POST['other_remarks']);

		meta_forward($_SERVER['PHP_SELF'], "UpdatedID=".$_SESSION['issue_items']->order_id);
	

} /*end of process credit note */


if (isset($_GET['trans_no']))
{
	handle_new_order();
}


//-----------------------------------------------------------------------------------------------

display_wo_details($_SESSION['issue_items']->order_id);
hidden('woid',$_SESSION['issue_items']->order_id);

$wo_details=get_tm_wm_details($_SESSION['issue_items']->order_id);
if($mode==1)
{
	$_POST['wo_memo_status']=$wo_details['wo_memo_status'];
	$_POST['proposed_work_order_memo']=$wo_details['proposed_work_order_memo'];
	$_POST['execute_by_status']=$wo_details['execute_by_status'];	
	$_POST['proposed_execute_by']=sql2date($wo_details['proposed_execute_by']);	
	$_POST['required_by_status']=$wo_details['required_by_status'];	
	$_POST['proposed_required_by']=sql2date($wo_details['proposed_required_by']);	
	$_POST['units_reqd_status']=$wo_details['units_reqd_status'];
	$_POST['proposed_units_reqd']=$wo_details['proposed_units_reqd'];	
	$_POST['units_reqs_per_day_status']=$wo_details['units_reqs_per_day_status'];	
	$_POST['proposed_units_reqs_per_day']=$wo_details['proposed_units_reqs_per_day'];	
	$_POST['grace_period']=$wo_details['grace_period'];

	// resource requirement	
	$_POST['rm_available_options']=$wo_details['rm_available_options'];
	$_POST['rm_procurement']=$wo_details['rm_procurement'];	
	$_POST['machine_booked']=$wo_details['machine_booked'];	
	$_POST['rm_remarks']=$wo_details['rm_remarks'];	
	$_POST['other_remarks']=$wo_details['other_remarks'];	
	
}
if(isset($_POST['wo_memo_status']) || isset($_POST['execute_by_status']) || isset($_POST['required_by_status']) || isset($_POST['units_reqd_status']) || isset($_POST['units_reqs_per_day_status']))
{
	$Ajax->activate('_page_body');
}

echo "<br>";

start_form();
display_heading(_("Proposal of Acceptance/Rejection OR Modification in work Order"));
br();
start_table(TABLESTYLE, "width='80%'");
$th = array(_("Complete Data Required for Work Order Execution"), _("Details Provided By TM"), _("Rejected"),
		_("Proposed By WM (if Not Accepted)"));
	table_header($th);
start_row();
label_cell(_("Any Specific Information for the Work Order"),"style='background-color:aliceblue'");
label_cell($wo_details['work_order_memo']);
check_cells(null,'wo_memo_status', null, true);
if($_POST['wo_memo_status'])
textarea_cells(null, 'proposed_work_order_memo', null, 25, 3);
else
label_cell(_(""));	
end_row();
start_row();
label_cell(_("Quantity Produce in MT"),"style='background-color:aliceblue'");
label_cell($wo_details['units_reqd'],'align=center');
check_cells(null,'units_reqd_status', null, true);
if($_POST['units_reqd_status'])
qty_cells(null, 'proposed_units_reqd', null, null, null, $dec);
else
label_cell(_(""));
end_row();
start_row();
label_cell(_("Target Start Date of the Execution of Work Order"),"style='background-color:aliceblue'");
label_cell(sql2date($wo_details['execute_by']),'align=center');
check_cells(null,'execute_by_status', null, true);
if($_POST['execute_by_status'])
date_cells(null, 'proposed_execute_by');
else
label_cell(_(""));
end_row();
start_row();
label_cell(_("Target Close Date of the Work Order"),"style='background-color:aliceblue'");
label_cell(sql2date($wo_details['required_by']),'align=center');
check_cells(null,'required_by_status', null, true);
if($_POST['required_by_status'])
date_cells(null, 'proposed_required_by');
else
label_cell(_(""));
end_row();
start_row();
label_cell(_("Target Production per Day (in MT)"),"style='background-color:aliceblue'");
label_cell($wo_details['units_reqs_per_day'],'align=center');
check_cells(null,'units_reqs_per_day_status', null, true);
if($_POST['units_reqs_per_day_status'])
qty_cells(null, 'proposed_units_reqs_per_day', null, null, null, $dec);
else
label_cell(_(""));
end_row();
start_row();
label_cell(_("Grace Period Required if any"),"style='background-color:aliceblue'");
label_cell(_(""));
label_cell(_(""));
textarea_cells(null, 'grace_period', null, 25, 3);
end_row();
end_table();
br();
display_heading(_("Resource Requirement by Works Manager to Top Management"));
br();
start_table(TABLESTYLE, "width='80%'");
$th = array(_("Requirement"), _("Available Options"), _("New Procurement"),
		_("Remark"));
	table_header($th);
start_row();
label_cell(_("Raw Material"),"style='background-color:aliceblue'");
textarea_cells(null, 'rm_available_options', null, 25, 3);
textarea_cells(null, 'rm_procurement', null, 25, 3);
textarea_cells(null, 'rm_remarks', null, 25, 3);
end_row();
start_row();
label_cell(_("Machine Booked"),"style='background-color:aliceblue'");
textarea_cells(null, 'machine_booked', null, 25, 3);
label_cell(_(""));
label_cell(_(""));
end_row();
start_row();
label_cell(_("Any Other Specific Requirement"),"style='background-color:aliceblue'");
label_cell(_(""));
label_cell(_(""));
textarea_cells(null, 'other_remarks', null, 25, 3);
end_row();
end_table();

br();
submit_center('Process', _("Update"), true, '', 'default');

end_form();

//------------------------------------------------------------------------------------------------

end_page();

