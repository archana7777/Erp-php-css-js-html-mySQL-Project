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
$path_to_root="../..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/invivo/includes/db/qau_request_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);
if (user_use_date_picker())
	$js .= get_js_date_picker();
	page(_($help_context = " Preventive Maintenance Request"), false, false, "", $js);
//-----------------------------------------------------------------------------------
// Ajax updates

$Ajax->activate('orders_tbl');
if (get_post('SearchDetails')) 
{
	$Ajax->activate('orders_tbl');
} 
//--------------------------------------------------------------------------------------
start_form(false, false, $_SERVER['PHP_SELF']);
start_table(TABLESTYLE_NOBORDER);
start_row();
//ref_cells(_("Ref"), 'qaureq_refrence', '',null, '', true);

//ref_cells(_("Machine Code / Machine Name:"), 'machine_code','',null, '', true);

maintenance_process_status_list(_("Process Status:"), 'process_status', null,true,true);
machine_codes_list_cells(_("Machines"), 'machines', null,true,true);
date_cells(_("From:"), 'from_date', '', null, -user_transaction_days());
date_cells(_("To:"), 'to_date','',null,1);

submit_cells('SearchDetails', _("Search"),'',_('Select documents'),  'default'); 
end_row();
end_table();

//-----------------------------------------------------------------------------
function get_machine_maintenance_inquiry_details($machine_code,$from,$to,$process_status,$machines)
{
	
	$date_after = date2sql($from);
	$date_before = date2sql($to);
	$sql= "SELECT ms.id,meq.mac_eqp,ma.mac_code,ms.schedule_date,fre.mac_fre,ms.process_status,ms.req_status FROM ".TB_PREF."machine_maintenance_schedule as ms LEFT JOIN ".TB_PREF."machine as ma ON ma.id=ms.machine_id LEFT JOIN ".TB_PREF."machine_equipment as meq ON meq.id=ma.mac_eqp LEFT JOIN ".TB_PREF."fiscal_year as fa ON fa.id=ms.fiscal_year_id LEFT JOIN ".TB_PREF."machine_frequency as fre ON ms.mac_fre=fre.id  
	WHERE ms.schedule_date >= '$date_after' AND ms.schedule_date <= '$date_before' AND ms.process_status='0'";
	
	if($machine_code){
	$sql .="AND ma.mac_code LIKE '%$machine_code%' OR meq.mac_eqp LIKE '%$machine_code%'";
	}
	if($process_status!='-1'){
		$sql .="AND ms.process_status='$process_status'";
	}
	
	if($machines!='-1'){
		$sql .="AND ma.id='$machines'";
	}
	return $sql;
}
function machine_code_link($dummy, $order_no){
	
return get_machine_code_schedule_preventive_view_str(ST_SCHEDPREV, $order_no);

}
function process($row)
{
		//if($row["req_status"]!=0){
		if(($row["process_status"]==0)){
		return 
		pager_link(_('Process'),
			"/maintenance/inquiry/machine_maintenance_checklist.php?id=". $row['id'].""); 
		}else{
	    return 'Process Completed';
        }
		// }
}
function request($row)
{
		if($row["req_status"]==0){
		return pager_link(_('Request'),
			"/maintenance/inquiry/maintenance_request.php?id=". $row['id'].""); 
		}else{
			return "Requested";
		}
		
}


function view_link($dummy, $order_no)
{
	return get_trans_view_str(ST_MACHMAINTENQ, $order_no);
}
function edit_link($row)
{
	return trans_editor_link(ST_MACHMAINTENQ, $row["id"]);	
}

$sql = get_machine_maintenance_inquiry_details($_POST['machine_code'],$_POST["from_date"],$_POST["to_date"],$_POST['process_status'],$_POST['machines']);

$cols = array(
	_("#") => array('fun'=>'view_link', 'ord'=>''), 
	_("Machine Name"), 
	// _("Machine Code") => array('fun'=>'machine_code_link','ord'=>''), 
	_("Machine Code"), 
	_("Scheduled Date") => array('type'=>'date'),
	_("Machine Frequency"),
	array('insert'=>true, 'fun'=> 'request'),
	array('insert'=>true, 'fun'=> 'process'),
);

$table =& new_db_pager('orders_tbl', $sql, $cols);

$table->width = "90%";

display_db_pager($table);

end_form();
end_page();
