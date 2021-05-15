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
$page_security = 'SA_MM_BREK_REQINQ';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/maintenance/includes/db/breakdown_request_db.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/kv_departments.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);
if (user_use_date_picker())
	$js .= get_js_date_picker();
	$outstanding_only = 0;
	page(_($help_context = "Machine Breakdown Process"), false, false, "", $js);

simple_page_mode(false);

//----------------------------------------------------------------------------------
if($_GET["id"])
{
	$selected_id=$_GET["id"];
}
else if($_POST['id'])
{
	$selected_id=$_POST['id'];
}
if(list_updated('prob_status'))
{
	$Ajax->activate('_page_body');
}
if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{
	//initialise no input errors assumed initially before we test
	$input_error = 0;
	
	if($_POST['prob_status'] == 2){
	
		if (strlen($_POST['perminent_solution']) == 0)
		{
			$input_error = 1;
			display_error(_("Please Enter Permanent Solution!"));
			set_focus('perminent_solution');
		}
	}
	
	
	
	if (strlen($_POST['attend_by']) == 0)
	{
		$input_error = 1;
		display_error(_("Please Enter Attend by!"));
		set_focus('attend_by');
	}
	
	if($_POST['prob_status'] == 3){
	if (strlen($_POST['solved_by']) == 0)
	{
		$input_error = 1;
		display_error(_("Please Enter Solved by!"));
		set_focus('solved_by');
	}
	}
	
	
	
	
		date_default_timezone_set('Asia/Kolkata');
		$process_date=date("Y-m-d h:i:s");
	if ($input_error !=1) {
    	add_breakdown_process_details($_POST['id'], $_POST['prob_status'],$_POST['perminent_solution'],$_POST['solved_by'],$_POST['attend_by'],$_POST["remarks"],$process_date);
		display_notification(_('Process details has been added'));
		$Mode = 'RESET';
		meta_forward($path_to_root.'/maintenance/inquiry/breakdown_request_inquiry.php');	
	}
	
}

start_form();
start_table(TABLESTYLE2);
$res=get_breakdown_Request_edit($selected_id);
label_row(_("Estimation Problem:"),$res['description']);
label_row(_("Actual Problem:"),$res['prob_desc']);
breakdown_process_status_row( _("Problem Status:"), 'prob_status', null,true);
if($_POST["prob_status"]==2){
textarea_row(_("Perminent Solution:<b style='color:red'>*</b>"),'perminent_solution',null,40, 5);
}
if($_POST["prob_status"]==3){
solved_by_list_row(_("Solved By:<b style='color:red'>*</b>"),'solved_by',null,true);
}
text_row(_("Attend by:<b style='color:red'>*</b>"),'attend_by',null);
textarea_row(_("Remarks:"), 'remarks', null, 40, 5);
hidden('id',$_GET["id"]);

end_table(1);

//submit_add_or_update_center($selected_id == '', '', 'both');
submit_center('ADD_ITEM', _("Submit"), true, '', 'default');
end_form();

end_page();
?>
<script>
 $( function() {
$("input[name='schedule_time']").timepicker();
  } );
  
</script>
