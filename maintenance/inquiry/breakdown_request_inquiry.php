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
$path_to_root="../..";

include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/maintenance/includes/db/breakdown_request_db.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 600);
if (user_use_date_picker())
	$js .= get_js_date_picker();
	page(_($help_context = "Breakdown Maintenance Inquiry"), false, false, "", $js);
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
//ref_cells(_("Ref"), 'ref', '',null, '', true);
text_cells(_("Machine Code / Machine Name:"),'machine_code','',null);

maintenance_mc_analysis_type_row(_("Problem Type Type:"),'problem_type',null,false,true);
breakdown_process_status_row( _("Problem Status:"), 'prob_status', null,true,true);
date_cells(_("From:"), 'from_date', '', null, -user_transaction_days());
date_cells(_("To:"), 'to_date','',null,1);

submit_cells('SearchDetails', _("Search"),'',_('Select documents'),  'default'); 
end_row();
end_table();

//-----------------------------------------------------------------------------
function verify($row)
{
		if($row["verify_status"]!=1){
		return 
		pager_link(_('Verification'),
			"/maintenance/inquiry/breakdown_request_verification.php?id=". $row['id'].""); 
		}else {
			 return 'Verified';
		}
		
}

function machine_code_link($dummy, $order_no){
	
return get_machine_code_schedule_preventive_view_str(ST_SCHEDBREAK, $order_no);

}


function Process($row)
{
	if($row['verify_status']!=0){
		if($row['verify_status'] !=0 ){
			if($row["process_status"]==0){
				return 
				pager_link(_('Process'),
					"/maintenance/inquiry/breakdown_process.php?id=". $row['id'].""); 
				}if($row["process_status"]==1){
					return pager_link(_('Process'),
					"/maintenance/inquiry/breakdown_process.php?id=". $row['id'].""); 
				}
				else{
					return "<h1 align='center'>--</h1>";
				}
		}
	}else{
		return "<h1 align='center'>--</h1>";
	}
		
}

function Process_status($row)
{
	if($row['verify_status']!=0){
		if($row['verify_status'] !=0 ){
			if($row["process_status"]==1){
				return 
				"Solved Temporarily ";
			}else if($row["process_status"] == 2){
				return 'Solved Completly';
			}else{
				return "<h1 align='center'>--</h1>";
			}
		}
	}else{
		return "<h1 align='center'>--</h1>";
	}
		
}



function view_link($dummy, $order_no)
{
	return get_breakdown_maintenance_entry_view_str(ST_BREAKMAINTENTRY, $order_no);
}
function edit_link($row)
{
	
	if($row["verify_status"]!=1){
	return trans_editor_link(ST_MMBRKREQ, $row["id"]);	
	}
}
function request($row)
{
		if($row["process_status"]!=3){
		return pager_link(_('Request'),	"/maintenance/inquiry/bkd_items_request.php?id=". $row['id'].""); 
		}
		
}
$sql = get_breakdown_requests_inquiry($_POST["from_date"],$_POST["to_date"],$_POST["machine_code"],$_POST["prob_status"],$_POST['problem_type']);
$cols = array(
	_("#") => array('fun'=>'view_link', 'ord'=>''), 
	_("Reference"), 
	//_("Machine Code") => array('fun'=>'machine_code_link','ord'=>''), 
	_("Machine Code") , 
	_("machine Name"), 
	_("Operator"),
	//_("Request Date") => array('type'=>'date'),
	_("Request Date"),
	_("Problem Description"),
	_("Problem Type"),
	_("Edit")=> array('insert'=>true, 'fun'=> 'edit_link'),
	_("Verification") => array('insert'=>true, 'fun'=> 'verify'),
	_("Items Request") =>array('insert'=>true, 'fun'=> 'request'),
	_("Process") => array('insert'=>true, 'fun'=> 'Process'),
	_("Process Status") => array('insert'=>true, 'fun'=> 'Process_status'),
);

$table =& new_db_pager('orders_tbl', $sql, $cols);

$table->width = "90%";

display_db_pager($table);

end_form();
end_page();
