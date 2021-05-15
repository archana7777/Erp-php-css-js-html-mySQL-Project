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
$page_security = 'SA_MACHINE_CALIBRATION';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/maintenance/includes/db/machine_calibration_entry_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(900, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = "Machine Calibration Entry"), false, false, "", $js);
simple_page_mode(false);

//----------------------------------------------------------------------------------
if(isset($_GET['id'])){
	$selected_id=$_GET['id'];
}
if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	//initialise no input errors assumed initially before we test
	$input_error = 0;

//	$date_valid=is_date_in_fiscalyears1($_POST['schedule_date'],true,$_POST['f_year']);
//	if($date_valid){
		if ($input_error !=1) {
    	add_machine_calibration_entry($selected_id,$_POST['machine_id'],$_POST['calibration_date'],$_POST['validity_start_date'],$_POST['validity_end_date'], $_POST['remarks']);
		if($selected_id != '')
			display_notification(_('Selected Machine Maintenance Schedule has been updated'));
		
		else
			display_notification(_('New Machine Maintenance Schedule has been added'));
		$Mode = 'RESET';	
	}
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

/*if ($Mode == 'Delete')
{

		if (can_delete($selected_id)) 
	{
			
		delete_machine_calibration_entry($selected_id);
		display_notification(_('Selected machine equipment has been deleted'));
	}
	$Mode = 'RESET';
}*/

function machine_code_link($dummy, $order_no){
	
return get_machine_code_schedule_preventive_view_str(ST_SCHEDPREVBREAK, $order_no);

}

if (isset($_GET['delete_request']))
{
	
	$deleteid=$_GET['delete_request'];
	$sql="Delete FROM ".TB_PREF."machine_calibration  WHERE id='$deleteid'";
  // display_error($sql);die;
	db_query($sql);
	display_notification("Calibration Info request has been Deleted!");
	$Mode = 'RESET';
	//new_doc_date($_POST['date_']);
	meta_forward($_SERVER['PHP_SELF']);
}

function delete_link($row){	
//if($row['status']==0 || $row['status']==3)
   	$str = "/maintenance/manage/machine_calibration_entry.php?delete_request=".$row['id'];
  	return $str ? pager_link(_('Delete'), $str, ICON_DELETE) : '';
}

if ($Mode == 'RESET')
{
	$selected_id = '';
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

//----------------------------------------------------------------------------------

$Ajax->activate('orders_tbl');
if (get_post('SearchDetails')) 
{
	
	$Ajax->activate('orders_tbl');
}
start_form();
/*start_table();
start_row();
fiscalyears_list_row(_("Fiscal Year:"), 'f_year', $_POST['f_year'],true);
end_row();
end_table();
if (list_updated('f_year'))
{   
	$selected_id = -1;
	$Ajax->activate('machine_id');
	$Ajax->activate('_page_body');
	unset($_POST['remarks']);
	unset($_POST['schedule_date']);
	$Mode = 'RESET';
}*/
start_table(TABLESTYLE_NOBORDER);
start_row();
//ref_cells(_("Ref"), 'qaureq_refrence', '',null, '', true);
text_cells(_("Machine Code / Machine Name"),'machine_code','',null);
date_cells(_("Start Date:"), 'start_date', '', null, -user_transaction_days());
date_cells(_("Form Date:"), 'end_date','',null,1);

submit_cells('SearchDetails', _("Search"),'',_('Select documents'),  'default'); 
end_row();
end_table();

$sql = get_all_machine_calibration_entry(check_value('show_inactive'),$_POST['machine_code'],$_POST['start_date'],$_POST['end_date']);

function edit_link($row)
{
		if(($row["inspc_status"]!=1) && ($row["skip_status"]!=1))
		{
	return trans_editor_link(ST_CALIBRAENTRY, $row["id"]);	
		}
}


/*br(2);
start_table(TABLESTYLE, "width='40%'");
$th = array(_('Transaction'),_('Machine name'),_('Machine Code'),_('Calibration Date'), _('Validity Start Date'), _('Validity End Date'), _('Remarks'),"", "");
//inactive_control_column($th);
table_header($th);
$k = 0; //row colour counter

while ($myrow = db_fetch($result))
{
	alt_table_row_color($k);
	
	label_cell($myrow["id"]);
	label_cell($myrow["mac_eqp"]);
	label_cell($myrow["mac_code"]);
	label_cell($myrow["calibration_date"]);
	label_cell($myrow["validity_start_date"]);
	label_cell($myrow["validity_end_date"]);
	label_cell($myrow["remarks"]);
  //  $id = htmlentities($myrow["id"]);
//	inactive_control_cell($id, $myrow["inactive"], 'machine_calibration', 'id');
	edit_button_cell("Edit".$myrow["id"], _("Edit"));
 	delete_button_cell("Delete".$myrow["id"], _("Delete"));
	end_row();
}*/
//inactive_control_row($th);
//end_table(1);

$cols = array(
	_("Transaction"), 
	_("Machine name"), 
	_("Machine Code") => array('fun'=>'machine_code_link','ord'=>''), 
	_("Calibration Date"), 
	_("Validity Start Date"),
	_("Validity End Date"),
	_("Remarks"),
	array('insert'=>true, 'fun'=> 'edit_link'),
	array('insert'=>true, 'fun'=> 'delete_link')
	//_("Inspection") => array('fun'=>'insp_link'),
	
);

//display_error($sql);
$table =& new_db_pager('orders_tbl', $sql, $cols);

$table->width = "70%";

display_db_pager($table);
br();
//----------------------------------------------------------------------------------

start_table(TABLESTYLE2);

//if ($selected_id != '') 
//{
 	if (isset($_GET['id'])) {
		
		$myrow = get_machine_calibration_edit($_GET['id']);
		$_POST['machine_id'] = $myrow["machine_id"];
		$_POST['calibration_date']  = $myrow["calibration_date"];
		$_POST['validity_start_date']  = $myrow["validity_start_date"];
		$_POST['validity_end_date']  = $myrow["validity_end_date"];
		$_POST['remarks']  = $myrow["remarks"];	
	}
	hidden('selected_id', $myrow["id"]);
//}

if (list_updated('machine_id'))
{   
	$selected_id = -1;
	//$Ajax->activate('mac_fre');
	//$Ajax->activate('_page_body');
	$Mode = 'RESET';
}
machine_name_list_row(_("Machine Name:"), 'machine_id', null, false, true);


date_row(_("Calibration Date:") , 'calibration_date', '', true);
date_row(_("Validity Start Date:") , 'validity_start_date', '', true);
date_row(_("Validity End Date:") , 'validity_end_date', '', true);

textarea_row(_("Remarks:"), 'remarks', null, 40, 5);

end_table(1);

/*if(isset($_POST['f_year']) && ($Mode!="Edit")){
	$selected_id = '';
	$sav = get_post('show_inactive');
	$_POST['show_inactive'] = $sav;
}*/

submit_add_or_update_center($selected_id == '', '', 'both');


end_form();

end_page();

