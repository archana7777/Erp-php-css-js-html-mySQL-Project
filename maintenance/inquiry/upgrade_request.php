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
$page_security = 'SA_UPGRADE_REQUEST';
$path_to_root="../..";
include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui.inc");
include($path_to_root . "/maintenance/includes/db/upgrade_request_entry_db.inc");
if (isset($_GET['type']))
{
  if($_GET['type']==1109)
  $trans_type= ST_UPGRADEREQ;

  
$_SESSION["tran_type"] =$trans_type;
}


$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();


	 if($trans_type == 1109){
		page(_($help_context = "Upgrade Request"), false, false, "", $js);
}

//---------------------------------------------------------------------------------------------




 function waiting_approval($row)
{
	
	global $trans_type;
	//display_error($row['completed_date']);
	if ($trans_type == ST_UPGRADEREQ)
		return (approval($row['completed_date']));
	
} 
 



//display_error($_SESSION["wa_current_user"]->loginname);
$sess=$_SESSION["wa_current_user"]->loginname;

function sess_name(){
	$sess=$_SESSION["wa_current_user"]->loginname;
	
	return $sess;
}




function view_link($dummy, $order_no)
{
	
	return get_trans_view_str(ST_UPGRADEREQ, $order_no);
}

function update_link($row)
{

	return 
		pager_link(_('Upgrade'),
			"/maintenance/inquiry/upgrade_req_update.php?id=".$row["id"]);


	
	
}
function request($row)
{
		if($row["process_status"]!=3){
		return pager_link(_('Request'),	"/maintenance/inquiry/upgrade_items_request.php?id=". $row['id'].""); 
		}
		
}

//-----------------------------------------------------------------------------------



start_form();


start_form(false, true);
start_table(TABLESTYLE_NOBORDER);
start_row();
	machine_name_list_cells(_("Machine Name:"), 'machine_id', null, _("All Machines"), true);

submit_cells('SearchOrders', _("Search"),'',_('Select documents'),  'default'); 
	br();

end_row();
if (isset($_POST['SearchOrders']))
{
	$selected_id = -1;
	$Ajax->activate('_page_body');	
}
end_table();
br();

end_form();
start_form(true);

$sql3 = get_all_upgrade_req_ent($_POST['machine_id']);	

	
//display_error($emp_id);

$cols = array(

	_("#")=> array('fun'=>'view_link', 'ord'=>''),
	_("Refernce ") 	,
	_("Machine Name") 	,
	_("Details ") 	,
	_("Items Request") =>array('insert'=>true, 'fun'=> 'request'),
	 _("Action")=> array('fun'=>'update_link', 'ord'=>''),
);


//---------------------------------------------------------------------------------------------------


//display_error($sql3);
$table =& new_db_pager('test_request', $sql3, $cols);


$table->width = "60%";

display_db_pager($table);

end_form();
end_page();
