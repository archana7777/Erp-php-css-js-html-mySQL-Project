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
$page_security = 'SA_INDENTREQUEST_INQUIRY_VIEW';
$path_to_root="../..";

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc" );
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/manufacturing/includes/manufacturing_ui.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");

include_once($path_to_root . "/admin/db/users_db.inc");

if (isset($_GET['type']))
{
  if($_GET['type']==95)
  $trans_type= ST_INDENTREQUEST;

  
$_SESSION["tran_type"] =$trans_type;
}
if(isset($_GET['sucess']))
{
	display_notification("Updated Sucessfully");
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


	 if($trans_type == 95){
		page(_($help_context = "Material Requisition Inquiry View"), false, false, "", $js);
	}

//---------------------------------------------------------------------------------------------




function get_all_indent_requests($from_date,$to_date,$fromDepartment,$status,$dept_id)
{
	
	$from_date = date2sql($from_date);
	$to_date = date2sql($to_date);
	//$alloc=get_alloc_id($chemist_id);

	$sql = "select trans_no,reference,tran_date,info.empl_firstname,dept.description
	from ".TB_PREF."indent_request as req 
	left join ".TB_PREF."kv_empl_info  info on info.id=req.emp_id
	left join ".TB_PREF."kv_departments  dept on dept.id=req.dept_id
	
	
	WHERE req.tran_date >= '$from_date' AND req.tran_date <= '".$to_date."'";
	
	
	
	if ($fromDepartment!='')
	{
		$sql .= " AND req.loc_code =".db_escape($fromDepartment);
	}
	
	if ($status!='')
	{
		$sql .= " AND req.indent_status =".db_escape($status);
	}
    if($dept_id!='')
	{
		$sql .= " AND req.dept_id =".db_escape($dept_id);
	}
	 //display_error($sql);
	
	return $sql;
	
}
function check_overdue($row)
{

	global $trans_type;
	
	if ($trans_type == ST_INDENTREQUEST)
		return (date1_greater_date2(Today(), sql2date($row['tran_date'])));
	
}


function view_link($row)
{
	$trans_no=$row['trans_no'];
	return get_indent_requests_view(ST_INDENTREQUEST, $trans_no);
}
function Update_link($row)
{
	global $trans_type;

	

  		return pager_link( _("Indent Request Process"),
			"/inventory/indent_process.php?trans_no=" .$row['trans_no'], ICON_DOC);
}



//-----------------------------------------------------------------------------------


start_form(true);
start_table(TABLESTYLE_NOBORDER);
start_row();

//ref_cells(_("#"), 'reference', '',null, '', true);

//display_error($department);

locations_list_cells(_("Requested Location:"), 'FromDepartment', null, true, true, $order->fixed_asset);
date_cells(_("Start Date:"), 'start_date', '', null, -user_transaction_days());

date_cells(_("End Date:"), 'end_date','',null,1);

//follow_status_list_cells_filter(_("Status"),'status',null,true);

indent_status_list_cells(_("Indent Status"),'status',null,true);
department_list_cells( _("Department :"), 'dept_id', null,true);
submit_cells('SearchOrders', _("Search"),'',_('Select documents'),  'default'); 
end_row();
end_table();


$sql1 = get_all_indent_requests($_POST["start_date"],$_POST["end_date"],$_POST['FromDepartment'],$_POST['status'],$_POST['dept_id']);	

$cols = array(

	
	//_("#")=> array('fun'=>'view_link', 'ord'=>'','align'=>'center') ,
	_("View") => array( 'fun'=>'view_link', 'ord'=>'','align'=>'center'),
	_("Reference ")=>array('align'=>'center') ,
	_("Date") => array('name'=>'date_', 'type'=>'date','align'=>'center'), 
	_("Requested By") , 
	_("Department") , 
	//_("Action") => array('fun'=>'update_link', 'ord'=>'','align'=>'center'), 



	 
	

	
);





//---------------------------------------------------------------------------------------------------

$table =& new_db_pager('indent_requests', $sql1, $cols);
$table->set_marker('check_overdue', _("Marked indent request are expired."));
$table->width = "85%";

display_db_pager($table);

end_form();
end_page();
