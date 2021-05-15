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

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc" );

if (isset($_GET['vw']))
	$view_id = $_GET['vw'];
else
	$view_id = find_submit('view');
if ($view_id != -1)
{		//echo $view_id;
	$row = get_employee_cv($view_id);
	if ($row['filename'] != "")
	{
		if(in_ajax()) {
			$Ajax->popup($_SERVER['PHP_SELF'].'?vw='.$view_id);
		} else {
			$type = ($row['filetype']) ? $row['filetype'] : 'application/octet-stream';	
    		header("Content-type: ".$type);
    		header('Content-Length: '.$row['filesize']);
	    	
	 		header("Content-Disposition: inline");
	    	echo file_get_contents(company_path(). "/attachments/emplcv/".$row['filename']);
    		exit();
		}
	}	
}
if (isset($_GET['dl']))
	$download_id = $_GET['dl'];
else
	$download_id = find_submit('download');

if ($download_id != -1){
	$row = get_employee_cv($download_id);
	if ($row['filename'] != ""){
		if(in_ajax()) {
			$Ajax->redirect($_SERVER['PHP_SELF'].'?dl='.$download_id);
		} else {
			$type = ($row['filetype']) ? $row['filetype'] : 'application/octet-stream';	
    		header("Content-type: ".$type);
	    	header('Content-Length: '.$row['filesize']);
    		header('Content-Disposition: attachment; filename='.$row['filename']);
    		echo file_get_contents(company_path()."/attachments/emplcv/".$row['filename']);
	    	exit();
		}
	}	
}

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
page(_($help_context = "Attach Employee CV"), false, false, "", $js);

simple_page_mode(true);

check_db_has_employees(_("There is no employee in this system. Kindly Open <a href='".$path_to_root."/modules/ExtendedHRM/manage/employees.php'>Add And Manage Employees</a> to update it"));

//----------------------------------------------------------------------------------------
if (isset($_GET['empl_id']))
	 $selected_id = $_POST['empl_id'] = $_GET['empl_id'];

if ($Mode == 'ADD_ITEM' || $Mode == 'UPDATE_ITEM'){
		if(!isset($max_image_size))
			$max_image_size = 500;
		$upload_file = "";
		if (isset($_FILES['kv_attach_name']) && $_FILES['kv_attach_name']['name'] != '') {
		
			$empl_id = $selected_id  ;
			$result = $_FILES['kv_attach_name']['error'];
			$upload_file = 'Yes'; 
			$attr_dir = company_path().'/attachments' ; 
			if (!file_exists($attr_dir)){
				
				mkdir($attr_dir);
			}
			$filename = company_path().'/attachments/emplcv';
			if (!file_exists($filename)){
				mkdir($filename);
			}	
			$doc_ext = substr(trim($_FILES['kv_attach_name']['name']), strlen($_FILES['kv_attach_name']['name']) - 3) ; 
			if($doc_ext == 'ocx' ) {
				$doc_ext = substr(trim($_FILES['kv_attach_name']['name']), strlen($_FILES['kv_attach_name']['name']) - 4) ; 
			}
			$filename .= "/".empl_img_name($empl_id).'.'.$doc_ext;
				$kv_file_name = $empl_id.'.'.$doc_ext;
			if ( $_FILES['kv_attach_name']['size'] > ($max_image_size * 1024)) { //File Size Check
				display_warning(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $max_image_size);
				$upload_file ='No';
			} 
			elseif (file_exists($filename)){
				$result = unlink($filename);
				if (!$result) 	{
					display_error(_('The existing CV could not be removed'));
					$upload_file ='No';
				}
			}
			
			if ($upload_file == 'Yes'){
				$result  =  move_uploaded_file($_FILES['kv_attach_name']['tmp_name'], $filename);
				kv_add_or_update_cv($empl_id, $_POST['empl_firstname'], $_POST['cv_title'], $kv_file_name ); 
				display_notification(_("Employee CV has been attached!."));
			}
			$Ajax->activate('_page_body');
	}	
}

if ($Mode == 'Delete')
{
	$row = get_employee_cv($selected_id);
	$dir =  company_path()."/attachments/emplcv";
	if (file_exists($dir."/".$row['filename']))
		unlink($dir."/".$row['filename']);
	delete_cv($selected_id);	
	display_notification(_("Employee CV has been deleted.")); 
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	unset($_POST['trans_no']);
	unset($_POST['description']);
	$selected_id = -1;
}



function edit_link($row){
  	return button('Edit'.$row["empl_id"], _("Edit"), _("Edit"), ICON_EDIT);
}

function view_link($row){
  	return button('view'.$row["empl_id"], _("View"), _("View"), ICON_VIEW);
}

function download_link($row){
  	return button('download'.$row["empl_id"], _("Download"), _("Download"), ICON_DOWN);
}

function delete_link($row){
  	return button('Delete'.$row["empl_id"], _("Delete"), _("Delete"), ICON_DELETE);
}

function display_rows(){
	$sql = 'SELECT empl_id, empl_firstname, cv_title FROM '.TB_PREF.'kv_empl_cv WHERE 1=1'; 
	//display_error($sql);
	$cols = array(
		_("Employee Id") => array('fun'=>'employee_id', 'ord'=>''),
		_("Employee Id") => array('name'=>'employee_id'),
	    _("Employee Name") => array('name'=>'employee_name'),
	    _("CV Title") => array('name'=>'title'),	    
	    	array('insert'=>true, 'fun'=>'edit_link'),
	    	//array('insert'=>true, 'fun'=>'view_link'),
	    	array('insert'=>true, 'fun'=>'download_link'),
	    	array('insert'=>true, 'fun'=>'delete_link')
	    );	
		$table =& new_db_pager('kv_cv_table', $sql, $cols);

		$table->width = "60%";

		display_db_pager($table);
}

//----------------------------------------------------------------------------------------

start_form(true);

display_rows();

br(2);

if (db_has_employees()) {
	start_table(TABLESTYLE_NOBORDER);
	start_row();
    //stock_items_list_cells(_("Select an item:"), 'selected_id', null, _('New item'), true, check_value('show_inactive'));
	employee_list_cells(_("Select an Employee: "), 'selected_id', null,	_('New Employee'), true, check_value('show_inactive'));
	$new_item = get_post('selected_id')=='';
	//check_cells(_("Show inactive:"), 'show_inactive', null, true);
	end_row();
	end_table();

	if (get_post('_show_inactive_update')) {
		$Ajax->activate('selected_id');
		set_focus('selected_id');
	}
}
else{
	hidden('selected_id', get_post('selected_id'));
}
start_table(TABLESTYLE2);


if ($selected_id != -1){
	if($Mode == 'Edit')	{		
		$row = get_employee_cv($selected_id);
		$_POST['empl_id']  = $row["empl_id"];
		$_POST['empl_firstname']  = $row["empl_firstname"];
		$_POST['cv_title']  = $row["cv_title"];
		hidden('empl_id', $row['empl_id']);
		hidden('empl_firstname', $row['empl_firstname']);		

		label_row(_("Employee Id"), $row['empl_id']);
		label_row(_("Employee Name"), $row['empl_firstname']);
	} else {
		$row = get_employee($selected_id);		
		$_POST['empl_id']  = $row["empl_id"];
		$_POST['empl_firstname']  = $row["empl_firstname"];
		hidden('empl_id', $row['empl_id']);
		hidden('empl_firstname', $row['empl_firstname']);
		$_POST['cv_title'] = ''; 
		label_row(_("Employee Id"), $row['empl_id']);
		label_row(_("Employee Name"), $row['empl_firstname']);
	}
}
else {
	text_row_ex(_("Employee Id").':', 'empl_id', 10);
	text_row_ex(_("Employee Name").':', 'empl_firstname', 40);
}

text_row_ex(_("CV Title").':', 'cv_title', 40);

kv_doc_row(_("Select CV") . ":", 'kv_attach_name', 'kv_attach_name');

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'process');

end_form();

end_page();

?>