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
$page_security = 'SA_UPGRADE_REQUESTINQ';
$path_to_root="../..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
//include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc" );
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/admin/db/users_db.inc");

if (isset($_GET['type']))
{
  if($_GET['type']==1110)
  $trans_type= ST_UPGRADEREQINQ;

  
$_SESSION["tran_type"] =$trans_type;
}

if (isset($_GET['id']))
{	

	$_POST['id'] = $_GET['id'];
	
}

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

	 if($trans_type == 1110){
		page(_($help_context = "Upgrade Request Inquiry"), false, false, "", $js);
}
//---------------------------------------------------------------------------------------------
function get_all_follow_up($machine_id,$status)
{
	$sql3="SELECT up_ent.id,up_ent.ref, CONCAT(meq.mac_eqp,'-',m.mac_code) as mac_eqp,up_ent.details,CASE  up_ent.status WHEN '2'  THEN  'Completed' WHEN '0'  THEN  'Not Yet Started'  ELSE 'In Progress'  END as final_result FROM ".TB_PREF."upgrade_req_entry up_ent LEFT JOIN ".TB_PREF."machine as m on up_ent.machine_id=m.id LEFT JOIN ".TB_PREF."machine_equipment as meq ON m.mac_eqp=meq.id where up_ent.status=2";

if ($machine_id != -1)
	{
		$sql3 .= " AND up_ent.machine_id = ".db_escape($machine_id) ;
	}

	if ($status != "-1")
	{
		$sql3 .= " AND up_ent.status = ".db_escape($status) ;
	}
	
	return $sql3;	
}
function view_link($dummy, $order_no)
{
	
	return get_trans_view_str(ST_UPGRADEREQ, $order_no);
}
//-----------------------------------------------------------------------------------

start_form(true);
$emp_code=get_emp_code($sess);

start_table(TABLESTYLE_NOBORDER);
start_row();
 if ($outstanding_only==0)

//date_cells(_("From Date:"), 'from_date', '', null, '',-1);
//date_cells(_("To Date:"), 'to_date','',null,0);
	machine_name_list_cells(_("Machine Name:"), 'machine_id', null, _("All Machines"), true);
follow_status_list_cells_filter(_("Status:"), 'status', null, true, true);
submit_cells('SearchOrders', _("Search"),'',_('Select documents'),  'default'); 
end_row();
end_table();

 
$sql3= get_all_follow_up($_POST["machine_id"],$_POST["status"]);




$cols = array(

	_("id")  =>array('fun'=>'view_link', 'ord'=>''),
	_("Reference ") => array('align'=>'center'),
	_("Machine Name ") ,
	
	_("Details ") => array('align'=>'center'),
	_("Status") ,

	
);


//---------------------------------------------------------------------------------------------------

$table =& new_db_pager('test_request', $sql3, $cols);
//$table->set_marker('check_overdue', _("Marked tests are expired."));
$table->width = "85%";

display_db_pager($table);

end_form();
end_page();
