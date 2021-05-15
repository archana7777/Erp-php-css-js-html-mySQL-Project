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
$page_security = 'SA_MACHINE_SCHEDULE';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/maintenance/includes/db/machine_maintenance_schedule_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = "Preventive Maintenance Schedule"), false, false, "", $js);

check_db_has_machines(("There are no Machines defined in the system."));
check_db_has_machine_frequency(_("There are no Machine Frequency's defined in the system."));

simple_page_mode(false);

//----------------------------------------------------------------------------------

if ($Mode=='ADD_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;
	$days=get_frequency_days($_POST['mac_fre']);
	$schedule_date1=date2sql($_POST['schedule_date']);
			// $next_date=date('Y-m-d', strtotime($schedule_date1. ' + '.$days.' days'));
	$sql = "SELECT end FROM ".TB_PREF."fiscal_year WHERE id=".db_escape($_POST['f_year'])."";
	$res = db_query($sql, "could not get all fiscal years");
	$result=db_fetch($res);
			//display_error($result["end"]);
			 // display_error($schedule_date1);die;
			 $i=$schedule_date1;
			 //display_error(hie);die;
			while($i<$result["end"])
			{
				$date_valid=is_date_in_fiscalyears1($i,true,$_POST['f_year']);
				 if($date_valid){
					 if ($input_error !=1) {
						 $date_=date_create($i);
				 add_machine_prevent_schedule($selected_id,$_POST['f_year'],$_POST['machine_id'],$i, $_POST['remarks'], $_POST['mac_fre'], $_POST['mc_problem_type']);
				//	$i=date('Y-m-d', strtotime($i. ' + '.$days.' days'));
					date_add($date_,date_interval_create_from_date_string("".$days." days"));
					$i= date_format($date_,"Y-m-d");
					
					//display_error($i);die;
					if($selected_id != '')
						display_notification(_('Selected Machine Maintenance Schedule has been updated'));
					
					else
						display_notification(_('New Machine Maintenance Schedule has been added'));
					$Mode = 'RESET';
					  }
					 }else{
						display_error(_('New Machine Maintenance Schedule Date Should be Within the Fiscal Year'));
						
						$Mode = 'RESET';
					}
				
			}

	
}else if($Mode=='UPDATE_ITEM')
{
	$schedule_date1=date2sql($_POST['schedule_date']);
	// display_error($_POST['machine_id']);die;
	add_machine_prevent_schedule($selected_id,$_POST['f_year'],$_POST['machine_id'],$schedule_date1, $_POST['remarks'], $_POST['mac_fre'], $_POST['mc_problem_type']);
	display_notification(_('Selected Machine Maintenance Schedule has been updated'));
}

function can_delete($selected_id)
{
	/*if (key_in_foreign_table($selected_id, 'location_subcategories', 'loc_cat'))
	{
		display_error(_("Cannot delete this Location Category because Location Sub Category have been created using this Location Category."));
		return false;
	}
		if (key_in_foreign_table($selected_id, 'locations', 'category_id'))
	{
		display_error(_("Cannot delete this Location Category because Locations  have been created using this Location Category."));
		return false;
	}
		if (key_in_foreign_table($selected_id, 'foundation_production_entry', 'rec_loc_cat'))
	{
		display_error(_("Cannot delete this Location Category because Foundation Production Entry have been created using this Location Category."));
		return false;
	}*/	
	
	
		return true;
}
//----------------------------------------------------------------------------------

if ($Mode == 'Delete')
{

		if (can_delete($selected_id)) 
	{
			
		delete_prevent_schedule($selected_id);
		display_notification(_('Selected machine equipment has been deleted'));
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
start_form();
start_table();
start_row();
fiscalyears_list_row(_("Fiscal Year:"), 'f_year', $_POST['f_year'],true);
machine_name_list_row(_("Machine Name:"), 'machine_id', null, false, true);

end_row();
end_table();

if (list_updated('f_year') || list_updated('machine_id'))
{   
	$selected_id = -1;
	$Ajax->activate('machine_id');
	$Ajax->activate('_page_body');
	unset($_POST['remarks']);
	unset($_POST['schedule_date']);
	$Mode = 'RESET';
}

$result = get_all_prevent_schedule($_POST['f_year'],check_value('show_inactive'),$_POST['machine_id']);

br(2);
start_table(TABLESTYLE, "width='60%'");
$th = array(_('Machine Code'),_('Machine Name'),_('Frequency Type'), _('Schedule Date'), _('Problem Type'), _('Remarks'), "", "");
inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($result))
{
	global $mc_analysis_type;
	
	alt_table_row_color($k);
    label_cell($myrow["mac_code"]);
    label_cell($myrow["mac_eqp"]);
	label_cell($myrow["mac_fre"]);
	label_cell($myrow["schedule_date"]);
	label_cell($mc_analysis_type[$myrow["mc_problem_type"]]);
	label_cell($myrow["remarks"]);
	
    $id = htmlentities($myrow["id"]);
	inactive_control_cell($id, $myrow["inactive"], 'machine_maintenance_schedule', 'id');
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
		
		$myrow = get_prevent_schedule($selected_id);
		$_POST['machine_id'] = $myrow["machine_id"];
		$_POST['mac_fre'] = $myrow["mac_fre"];
		$_POST['schedule_date']  = $myrow["schedule_date"];
		$_POST['remarks']  = $myrow["remarks"];	
		$_POST['mc_problem_type']  = $myrow["mc_problem_type"];	
	}
	hidden('selected_id', $myrow["id"]);
}

if (list_updated('machine_id'))
{   
	$selected_id = -1;
	$Ajax->activate('mac_fre');
	//$Ajax->activate('_page_body');
	$Mode = 'RESET';
}


if($Mode=="Edit"){
	machine_frequency_name_list_row(_("Frequency Type:"), 'mac_fre',null, false, null,$_POST['machine_id']);
}else{machine_frequency_name_list_row(_("Frequency Type:"), 'mac_fre',null, false, null,$_POST['machine_id']);
}
maintenance_mc_analysis_type_row(_("Problem Type:"),'mc_problem_type',null,false);
date_row(_("Schedule Date:") , 'schedule_date', '', true);


textarea_row(_("Remarks:"), 'remarks', null, 40, 5);

end_table(1);

if(isset($_POST['f_year']) && ($Mode!="Edit")){
	$selected_id = '';
	$sav = get_post('show_inactive');
	$_POST['show_inactive'] = $sav;
}
// if(db_num_rows($result)==0){
submit_add_or_update_center($selected_id == '', '', 'both');
// }
end_form();

end_page();

?>