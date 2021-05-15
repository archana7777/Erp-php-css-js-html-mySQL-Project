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
$page_security = 'SA_MC_BRK_REQ';
$path_to_root = "../..";

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/maintenance/includes/db/breakdown_request_db.inc");

include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/kv_departments.inc");
$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();
page(_($help_context = "Breakdown Maintenance Request Entry"), false, false, "", $js);

//---------------------------------------------------------------------------------------
if (isset($_GET['id']))
{
	$selected_id = $_GET['id'];
}

elseif(isset($_POST['id']))
{
	$selected_id = $_POST['id'];
}
if (!isset($_POST['date_']))
{
	$_POST['date_'] = new_doc_date();
	if (!is_date_in_fiscalyear($_POST['date_']))
		$_POST['date_'] = end_fiscalyear();
}
//---------------------------------------------------------------------------------------

function can_process()
{
			
	//if (!isset($selected_id))
	if (!isset($_POST['UPDATE_ITEM']))
	{
    	if (!check_reference($_POST['ref'],ST_MMBRKREQ))
    	{
			display_error("The reference should not be empty!");
			set_focus('ref');
    		return false;
    	}
	}
	if (strlen($_POST['ref'])==0)
    	{
			display_error("The reference should not be empty!");
			set_focus('ref');
    		return false;
    	}
	if(empty($_POST['machine_id']))
	{
		
		display_error("The Machine Name Should not be Empty!");
		set_focus('machine_id');
    		return false;
	}
	if(empty($_POST['department_id']))
	{
		
		display_error("The Department Should not be Empty!");
		set_focus('department_id');
    		return false;
	}
	if(empty($_POST['operator_id']))
	{
		
		display_error("The Operator Name Should not be Empty!");
		set_focus('operator_id');
    		return false;
	}
	return true;
}

//-------------------------------------------------------------------------------------

if (isset($_POST['ADD_ITEM']) && can_process())
{
	date_default_timezone_set('Asia/Kolkata');
    $req_date = date('Y-m-d H:i:s');
	add_breakdown_request($_POST['ref'],ST_MMBRKREQ,$_POST['machine_id'],$_POST['description'], $_POST['department_id'],$_POST['operator_id'],$req_date,$_POST["mc_problem_type"]);
	display_notification(_('New breakdown maintenance Request has been added !'));
	
	$Mode = 'RESET';
	//new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$id&type=".$_POST['type']);
}

if (isset($_POST['UPDATE_ITEM']) && can_process())
{	
	date_default_timezone_set('Asia/Kolkata');
    $req_date = date('Y-m-d H:i:s');
	update_breakdown_request($_POST["selected_id"],$_POST['ref'],$_POST['machine_id'],$_POST['description'],$_POST['department_id'], $_POST['operator_id'],$req_date,$_POST["mc_problem_type"]);
	display_notification(_('Breakdown maintenance Request has been updated!'));
	//new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF'], "AddedID=$id&type=".$_POST['type']);
		//$Mode = 'RESET';
}


//-------------------------------------------------------------------------------------

if (get_post('_type_update')) 
{
  $Ajax->activate('_page_body');
}
//-------------------------------------------------------------------------------------

start_form();

start_table(TABLESTYLE2);

$existing_comments = "";

$dec = 0;
if (isset($selected_id))
{
	 //display_error($selected_id);
	$myrow = get_breakdown_Request_edit($selected_id);


	$_POST['ref'] = $myrow["ref"];
	$_POST['machine_id'] = $myrow["machine_id"];
	$_POST['description']=$myrow["description"];
	$_POST['department_id']=$myrow["department_id"];
	$_POST['operator_id'] = $myrow["operator_id"];
	$_POST['mc_problem_type'] = $myrow["mc_problem_type"];
	
	hidden('ref', $_POST['ref']);
	
	
	label_row(_("Reference:"), $_POST['ref']);
	
}
else
{
	ref_row(_("Reference :"), 'ref', '', $Refs->get_next(ST_MMBRKREQ, null, get_post('date_')), false, ST_MMBRKREQ);
   }
	machine_name_list_row(_("Machine Name:"), 'machine_id', null, false, true); 
	maintenance_mc_analysis_type_row(_("Problem Type Type:"),'mc_problem_type',null,false);
    textarea_row(_("Description:"), 'description', null, 40, 5);
	department_list_row( _("Department :"), 'department_id', null,false,true);
	dept_empl_list_row(_("Operator Name:"),'operator_id',null,null,false,$_POST["department_id"]);
	
	hidden('selected_id',  $selected_id);

end_table(1);


br();

if (($_POST["selected_id"]!=0) || ($selected_id !=0))
{
		
	echo "<table align=center><tr>";

	submit_cells('UPDATE_ITEM', _("Update"), '', _('Save changes to Study Allotment'), 'default');
	
	//submit_cells('delete', _("Delete This Test Item Request"),'','',true);

	echo "</tr></table>";
}
else
{
	
	submit_center('ADD_ITEM', _("Submit"), true, '', 'default');
	br();
	
	
}
end_form();
end_page();
?>
