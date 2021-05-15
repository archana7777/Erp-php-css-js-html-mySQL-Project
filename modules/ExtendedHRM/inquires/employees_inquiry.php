<?php
/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
*****************************************/

$page_security = 'SA_OPEN';
$path_to_root="../../..";

include($path_to_root . "/includes/session.inc");
$version_id = get_company_prefs('version_id');

$js = '';
if($version_id['version_id'] == '2.4.1'){
	if ($SysPrefs->use_popup_windows) 
		$js .= get_js_open_window(900, 500);	

	if (user_use_date_picker()) 
		$js .= get_js_date_picker();
	
}else{
	if ($use_popup_windows)
		$js .= get_js_open_window(900, 500);
	if ($use_date_picker)
		$js .= get_js_date_picker();
}

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");	
include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc" );
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui/contacts_view.inc");

page(_("Employees Inquiry"), @$_REQUEST['popup'], false, "", $js); 	

check_db_has_employees(_("There is no employee in this system. Kindly Open <a href='".$path_to_root."/modules/ExtendedHRM/manage/employees.php'>Add And Manage Employees</a> to update it"));

simple_page_mode(true);
//----------------------------------------------------------------------------------------
if (isset($_GET['filterType'])) // catch up external links
	$_POST['filterType'] = $_GET['filterType'];
if (isset($_GET['trans_no']))
	$_POST['trans_no'] = $_GET['trans_no'];

if (isset($_GET['delete_id'])){
	$selected_del_id = $_GET['delete_id'];

	if (key_in_foreign_table($selected_del_id, 'kv_empl_salary', 'empl_id')){
		
		display_error(_("Cannot delete this Employee because Payroll Processed to this employee And it will be  added in the financial Transactions."));
	}else {
		delete_employee($selected_del_id);
		$filename = company_path().'/images/empl/'.empl_img_name($selected_del_id).".jpg";
		if (file_exists($filename))
			unlink($filename);
		display_notification(_("Selected Employee has been deleted."));
		$Ajax->activate('_page_body');	
	}
}

function edit_link($row){
		$str = "/modules/ExtendedHRM/manage/employees.php?selected_id=".$row['empl_id'];
  	return $str ? pager_link(_('Edit'), $str, ICON_EDIT) : '';
}

function delete_link($row){
  	$str = "/modules/ExtendedHRM/inquires/employees_inquiry.php?delete_id=".$row['empl_id'];
  	return $str ? pager_link(_('Edit'), $str, ICON_DELETE) : '';
}

function display_rows(){
	$sql = kv_hrm_get_empl_list();
	$cols = array(
		_("Empl Id") => array('name'=>'empl_id'),
	    _("Empl Name") => array('name'=>'empl_name'),	   
	    _("Email") => array('name'=>'email'),
	    _("Mobile No") => array('name'=>'email'),
	    _("Department") => array('name'=>'email'),
	    _("Grade") => array('name'=>'grade'),
	    _("Present Address") => array('name'=>'present_address'),
	    _("Date of Join") => array('name'=>'tran_date', 'type'=>'date'),
		array('insert'=>true, 'fun'=>'edit_link'),
		array('insert'=>true, 'fun'=>'delete_link')
	);	
	$table =& new_db_pager('kv_empl_info', $sql, $cols);
	$table->width = "80%";
	display_db_pager($table);
}

//----------------------------------------------------------------------------------------
	start_form(true);
if (isset($_GET['delete_id'])){} else{
	display_warning(_(" Once you delete the Employee, The whole informations can be removed from the Database"));
	}
	

	display_rows();

	end_form();
end_page();
?>
