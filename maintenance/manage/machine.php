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
$page_security = 'SA_MACHINE';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/maintenance/includes/db/machine_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Machine  Master"), false, false, "", $js);
simple_page_mode(false);

check_db_has_machine_equipment(_("There are no Machine Equipments defined in the system."));
check_db_has_machine_frequency(_("There are no Machine Frequency's defined in the system."));
check_db_has_machine_make(_("There are no Machine Make defined in the system."));
check_db_has_machine_capacity(_("There are no Machine Capacities defined in the system."));
check_db_has_suppliers(_("There are no Suppliers defined in the system."));
check_db_has_locations(("There are no inventory locations defined in the system."));
//----------------------------------------------------------------------------------

 if(list_updated('wr_type')){
	
	$Ajax->activate('wr');
	$Ajax->activate('_page_body');
	
}
if(list_updated('file_loc_cat'))
{
	$Ajax->activate('file_location');
}
if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;
	if (strlen($_POST['mac_code']) == 0)
	{
		$input_error = 1;
		display_error(_("The machine code  cannot be empty."));
		set_focus('mac_code');
	}
    
	 if (strstr($_POST['mac_code'], " ") || strstr($_POST['mac_code'],"'") || 
		strstr($_POST['mac_code'], "+") || strstr($_POST['mac_code'], "\"") || 
		strstr($_POST['mac_code'], "&") || strstr($_POST['mac_code'], "\t") ||
		strstr($_POST['mac_code'], ",") || strstr($_POST['mac_code'], ".")) 
	{
		display_error( _('Machine code can not allow Special Characters.'));
		set_focus('mac_code');
		return false;
	}
	
	if($Mode=='ADD_ITEM')
	{	
       $pname = getvalid_machine_code(trim($_POST['mac_code']));
       if(!empty(trim($pname))){
		   $input_error = 1;
		   display_error(_("Machine Code should be Unique."));
		   set_focus('mac_code');
		   return false;
		}
	}	
	
	if($Mode=='UPDATE_ITEM')
	{
		$pname = getvalid_machine_code_edit(trim($_POST['mac_code']),$selected_id);
	
       if(!empty(trim($pname))){
		display_error(_("Machine Code should be Unique and Required."));
		set_focus('mac_code');
		return false;
		}
	}

	
	if ($input_error !=1) {
		
		// $mac_fre=implode(',',$_POST["mac_fre"]);
		
		$mac_fre="";
		 
    	add_machine($selected_id, $_POST['mac_code'], $_POST['mac_eqp'], $_POST['mac_make'], $_POST['mac_cap'], $mac_fre,$_POST['file_loc_cat'],$_POST['file_location'],$_POST['remarks'],$_POST['mac_model_no'],$_POST['wr_type'],$_POST['wr_date'],$_POST["supplier_id"],$_POST['inst_date']);
		
		if($selected_id != '')
			display_notification(_('Selected machine  has been updated'));
		else
			display_notification(_('New machine  has been added'));
		$Mode = 'RESET';
	}
}

//----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{
	
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'stock_master'

	if (key_in_foreign_table($selected_id, 'machine_maintenance_schedule', 'machine_id'))
	{
		
		$cancel_delete = 1;
		display_error(_("This machine cannot be deleted because there are machine maintenance schedule  that refer to it."));
	}
	
	/* if (key_in_foreign_table($selected_id, 'machine_maintenance_checklists', 'machine_id'))
	{
		$cancel_delete = 1;
		display_error(_("This machine cannot be deleted because there are machine maintenance check lists  that refer to it."));
	} */
	
	if (key_in_foreign_table($selected_id, 'machine_attachments', 'machine_id'))
	{
		$cancel_delete = 1;
		display_error(_("This machine cannot be deleted because there are machine attachments that refer to it."));
	}
	if (key_in_foreign_table($selected_id, 'machine_calibration', 'machine_id'))
	{
		$cancel_delete = 1;
		display_error(_("This machine cannot be deleted because there are machine calibration that refer to it."));
	}
	if (key_in_foreign_table($selected_id, 'calibration_attachment', 'machine_id'))
	{
		$cancel_delete = 1;
		display_error(_("This machine cannot be deleted because there are calibration attachments that refer to it."));
	}
	if (key_in_foreign_table($selected_id, 'mm_brkd_req', 'machine_id'))
	{
		$cancel_delete = 1;
		display_error(_("This machine cannot be deleted because there are machine breakdown maintenance requests that refer to it."));
	}
	else
	{
		delete_machine($selected_id);
		display_notification(_('Selected machine  has been deleted'));
	}
	$Mode = 'RESET';
}


if ($Mode == 'RESET')
{
	$selected_id = '';
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

//----------------------------------------------------------------------------------

global $wrarranty_type;

$result = get_all_machine_categories(check_value('show_inactive'));

start_form();

start_table(TABLESTYLE, "width='80%'");
$th = array(_('Machine Code'),_('Machine Model No'),_('Machine Equipment'),_('Machine Make'),_('Machine Capacity'),_('Warranty Type'),_('Warranty Date'),_('Supplier'),_('Locations'), ('Installation Date'), _('Description'), "", "");
inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($result))
{
//$mac_fre = explode(",", $myrow["mac_fre"]);
//display_error($myrow["mac_fre"]);
	alt_table_row_color($k);

	label_cell($myrow["mac_code"]);
	label_cell($myrow["mac_model_no"]);
	label_cell($myrow["mac_eqp"]);
	label_cell($myrow["mac_make"]);
	label_cell($myrow["mac_cap"]);
	
	$mac_frequency=get_machine_frequency($myrow["mac_code"]);
	//display_error($mac_frequency);
	//label_cell($myrow["mac_fre"]);
	//label_cell($mac_frequency);
	label_cell($wrarranty_type[$myrow["warranty_type"]]);
	if($myrow["warranty_type"] == '4')
		label_cell("NA");
	else
	label_cell(sql2date($myrow["warranty_exp_date"]));
	label_cell($myrow["supp_name"]);
	//label_cell($myrow["location_group"]);
	label_cell($myrow["location"]);
	label_cell($myrow["inst_date"]);
	label_cell($myrow["remarks"]);
	$id = htmlentities($myrow["id"]);
	inactive_control_cell($id, $myrow["inactive"], 'machine', 'id');
	edit_button_cell("Edit".$myrow["id"], _("Edit"));
 	delete_button_cell("Delete".$myrow["id"], _("Delete"));
	end_row();
}
inactive_control_row($th);
end_table(1);

//----------------------------------------------------------------------------------

start_table(TABLESTYLE2);

if ($selected_id != '') 
{
 	if ($Mode == 'Edit') {
		
$myrow = get_machine_category($selected_id);
//
$mac_fre = explode(",", $myrow["mac_fre"]);

		$_POST['mac_code'] = $myrow["mac_code"];
		$_POST['mac_model_no'] = $myrow["mac_model_no"];
		$_POST['mac_eqp'] = $myrow["mac_eqp"];
		$_POST['mac_make'] = $myrow["mac_make"];
		$_POST['mac_cap'] = $myrow["mac_cap"];
		$_POST['mac_fre'] = $mac_fre;
		// $_POST['file_loc_cat'] = $myrow["file_loc_cat"];
		// $_POST['file_loc_subcat'] = $myrow["file_loc_subcat"];
		// $_POST['file_loc_subsubcat'] = $myrow["file_loc_subsubcat"];
		//$_POST['file_loc_cat'] = $myrow["file_loc_cat"];
		$_POST['file_location'] = $myrow["file_location"];
		$_POST['wr_type'] = $myrow["warranty_type"];
		$_POST['wr_date'] = sql2date($myrow["warranty_exp_date"]);
		$_POST['inst_date'] = sql2date($myrow["inst_date"]);
		$_POST['remarks']  = $myrow["remarks"];
		$_POST['supplier_id']  = $myrow["supplier_id"];
	
	}
	hidden('selected_id', $myrow["id"]);
}
text_row(_("Machine Code: <b style='color:red'>*</b>"), 'mac_code', null, 20, 20);
text_row(_("Machine Model No.:"), 'mac_model_no', null, 20, 20);
machine_equipment_list_row(_("Machine Equipment:"), 'mac_eqp', null, false, null);
machine_make_list_row(_("Machine Make:"), 'mac_make', null, false, null);
machine_capacity_list_row(_("Machine Capacity:"), 'mac_cap', null,false, null);
//hidden('mac_cap',2); //2 for N/A
div_start('wr');
warranty_type_row(_("Warranty Type:"),'wr_type',null,true);
if($_POST["wr_type"]!=4){
date_row(_("Warranty Expire Date:"),'wr_date','',true);
}	
 supplier_list_row(_("Supplier:"), 'supplier_id', 790, false, true, false, true);
//loc_group_list_row(_("Location Group Name:"), 'file_loc_cat', null,false,true);

locationsgrp_list_row(_("Location:"), 'file_location', null,false, false);
date_row(_("Installation Date:"),'inst_date','',true);
textarea_row(_("Machine Remarks:"), 'remarks', null, 40, 5);

end_table(1);

submit_add_or_update_center($selected_id == '', '', 'both');
div_end();

end_form();

end_page();

