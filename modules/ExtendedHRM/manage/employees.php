<?php
/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
*****************************************/

$page_security = 'SA_OPEN';
$path_to_root = "../../..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc" );
include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

 
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


page(_($help_context = "Employees"), @$_REQUEST['popup'], false, "", $js);

if(kv_check_payroll_table_exist()){}else {
	display_error(_("There are no Allowance defined in this system. Kindly Setup <a href='".$path_to_root."/modules/ExtendedHRM/manage/pay_items_setup.php' target='_blank'>Allowances</a> Your Allowances."));
	end_page();
    exit;
}
if(db_has_basic_pay()){ } else{
	display_error(_("Basic Pay is not Setup in the system. Kindly Setup <a href='".$path_to_root."/modules/ExtendedHRM/manage/pay_items_setup.php' target='_blank'>Basic Pay</a> here."));
	end_page();
    exit;
}
if(db_has_tax_pay()){ } else{
	display_error(_("Tax is not Setup in the system. Kindly Setup <a href='".$path_to_root."/modules/ExtendedHRM/manage/pay_items_setup.php' target='_blank'>Tax </a> here."));
	end_page();
    exit;
}
check_db_has_salary_account(_("There are no Salary Account defined in this system. Kindly Open <a href='".$path_to_root."/modules/ExtendedHRM/manage/hrm_settings.php' target='_blank'>Settings</a> to update it."));

check_db_has_Departments(_("There is no Department in the system to add employees. Please Add some <a href='".$path_to_root."/modules/ExtendedHRM/manage/department.php' target='_blank'>Department</a> "));
$new_item = get_post('selected_id')=='' || get_post('cancel') ;

if (isset($_GET['selected_id']))
{
	$_POST['selected_id'] = $_GET['selected_id'];
}
$selected_id = get_post('selected_id');
if (list_updated('selected_id')) {
	$_POST['empl_id'] = $selected_id = get_post('selected_id');
    clear_data();
	$Ajax->activate('details');
	$Ajax->activate('controls');
}
$basic_id = kv_get_basic();
if(list_updated($basic_id ) || get_post('RefreshInquiry')) {
		$month = get_post($basic_id );   
		$Ajax->activate('payroll_tbl');
}

if (get_post('cancel')) {
	$_POST['empl_id'] = $selected_id = $_POST['selected_id'] = '';
    clear_data();
	set_focus('selected_id');
	$Ajax->activate('_page_body');
}
if (list_updated('category_id') || list_updated('mb_flag')) {
	$Ajax->activate('details');
}

function clear_data(){
	unset($_POST['empl_id']);
	unset($_POST['empl_salutation']); 
	unset($_POST['empl_firstname']); 
	unset($_POST['empl_lastname']); 
	unset($_POST['addr_line1']); 
	unset($_POST['addr_line2']); 
	unset($_POST['empl_city']); 
	unset($_POST['empl_state']); 
	unset($_POST['gender']); 
	unset($_POST['date_of_birth']); 
	unset($_POST['age']);  
	unset($_POST['marital_status']); 

	unset($_POST['home_phone']);	
	unset($_POST['mobile_phone']);  
	unset($_POST['email']);  
	unset($_POST['status']); 
	unset($_POST['del_image']); 
	unset($_POST['pic']); 
}

//------------------------------------------------------------------------------------
$upload_file = "";
if (isset($_POST['addupdate'])) {

	$input_error = 0;
	if ($upload_file == 'No')
		$input_error = 1;

	if (strlen($_POST['empl_id']) == 0) {
		display_error(_("The employee Id Can't be empty."));
		set_focus('empl_id');
		return false;
	} 
	if (strlen($_POST['empl_id']) < 3) {
		display_error(_("The employee Id must have minimum three characters."));
		set_focus('empl_id');
		return false;
	} 
	if($new_item && ctype_alnum($_POST['empl_id']) == false){
		display_error(_("The employee Id must be Combinations of Letters and Numbers, Not symbols."));
		set_focus('empl_id');
		return false;	
	}
	if ($new_item && db_has_selected_employee($_POST['empl_id']) !=null ) {
		display_error(_("The employee Id Already Exist."));
		set_focus('empl_id');
		return false;
	} 
	if (strlen($_POST['empl_firstname']) == 0) {
		display_error(_("The employee name cannot be empty."));
		set_focus('empl_firstname');
		return false;
	} 
	
	if (strlen($_POST['mobile_phone']) == 0) {
		display_error(_("The employee mobile number Can't be empty."));
		set_focus('mobile_phone');
		return false;
	}

	/*if(!preg_match('/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $_POST['mobile_phone']))
    {
     display_error(_("The employee mobile number Can't be Invalid."));
		set_focus('mobile_phone');
		return false;
    } */
    if ($new_item && db_has_employee_email($_POST['email'])) {
		display_error(_("The E-mail already in Use."));
		set_focus('email');
		return false;
	} 

	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {}else{
		display_error(_("The Entered E-Mail is not Valid."));
		set_focus('basic');
		return false;
	}

	if($new_item &&  date2sql($_POST['joining']) > date('Y-m-d')){
		display_error(_("Invalid Joining Date for the Employee."));
		set_focus('joining');
		return false;
	}
	if(strlen($_POST['age']) >2 || !check_num('age', 0)){
		display_error(_("The entered age is invalid."));
		set_focus('age');
		return false;
	}	
	
	 if ($new_item){
		$joining = new DateTime(date2sql($_POST['joining']));
		$dob = new DateTime(date2sql($_POST['date_of_birth']));

		$diff = $dob->diff($joining);

	 	if($diff->y < 18){
	 		display_error(_("The employee Date of Birth is not valid one."));
			set_focus('date_of_birth');
			return false;
	 	}
	 }
	 /*
	if ($new_item && strlen($_POST['basic']) == 0) {
		display_error(_("The employee Basic Salary Can't be empty."));
		set_focus('basic');
		return false;
	} 

	if ($new_item && strlen($_POST['empl_da']) == 0) {
		display_error(_("The employee DA Can't be empty."));
		set_focus('empl_da');
		return false;
	} 
	if ($new_item && strlen($_POST['empl_hra']) == 0) {
		display_error(_("The employee HRA Can't be empty."));
		set_focus('empl_hra');
		return false;
	} 

	if ($new_item && strlen($_POST['conveyance']) == 0) {
		display_error(_("The employee Conveyance Can't be empty."));
		set_focus('conveyance');
		return false;
	} 

	if ($new_item && strlen($_POST['edu_other']) == 0) {
		display_error(_("The employee Education Can't be empty."));
		set_focus('edu_other');
		return false;
	} 
	if ($new_item && strlen($_POST['medical_allowance']) == 0) {
		display_error(_("The employee Medical Allowance Can't be empty."));
		set_focus('medical_allowance');
		return false;
	} 

	if ($new_item && strlen($_POST['empl_pf']) == 0) {
		display_error(_("The employee PF Can't be empty."));
		set_focus('empl_pf');
		return false;
	} */
	if ($new_item &&  strlen($_POST['bank_name']) == 0 && $_POST['mod_of_pay']== 2) {
		display_error(_("The employee Bank Name Can't be empty."));
		set_focus('bank_name');
		return false;
	} 
	if ($new_item &&  strlen($_POST['acc_no']) == 0 && $_POST['mod_of_pay']== 2) {
		display_error(_("The employee Account Number Can't be empty."));
		set_focus('acc_no');
		return false;
	} 	
	
	if ($input_error != 1){
		if (check_value('del_image'))	{
			$filename = company_path().'/images/empl/'.empl_img_name($_POST['empl_id']).".jpg";
			if (file_exists($filename))
				unlink($filename);
		}
		
		if (!$new_item) { /*so its an existing one */
			update_employee($selected_id, $_POST['empl_salutation'],
			 			 $_POST['empl_firstname'] ,
			 			 $_POST['empl_lastname'] ,
			 			 $_POST['addr_line1']  ,
			 			 $_POST['addr_line2']  ,
			 			 $_POST['home_phone']	, 
			 			 $_POST['mobile_phone']  ,
			 			 $_POST['email']  , 
			 			 $_POST['gender']  , 
			 			 $_POST['date_of_birth']  ,
			 			 $_POST['age']  ,  
			 			 $_POST['marital_status']  , 
			 			 $_POST['status'],
			 			 $_POST['empl_city'],
			 			 $_POST['empl_state'],
			 			 $_POST['country']);
			$kv_empl_id = $selected_id; 
			set_focus('selected_id');
			$Ajax->activate('selected_id'); // in case of status change
			display_notification(_("Employee Information has been updated."));
		} 
		else { //it is a NEW part

			add_employee($_POST['empl_id'],
			 			 $_POST['empl_salutation'],
			 			 $_POST['empl_firstname'] ,
			 			 $_POST['empl_lastname'] ,
			 			 $_POST['addr_line1']  ,
			 			 $_POST['addr_line2']  ,
			 			 $_POST['home_phone']	, 
			 			 $_POST['mobile_phone']  ,
			 			 $_POST['email']  , 
			 			 $_POST['gender']  , 
			 			 $_POST['date_of_birth']  ,
			 			 $_POST['age']  ,  
			 			 $_POST['marital_status']  , 
			 			 $_POST['status'],
			 			 $_POST['empl_city'],
			 			 $_POST['empl_state'],
			 			 $_POST['country']); 
			$kv_empl_id = $_POST['empl_id']; 
			//add_empl_department($kv_empl_id, $_POST['department']); 
			$jobs_arr =  array('empl_id' => $_POST['empl_id'],
							 'grade' => $_POST['grade'],
							 'department' => $_POST['department'],
							 'desig_group' => $_POST['desig_group'],
							 'desig' => $_POST['desig'] ,
							 'joining' => array($_POST['joining'], 'date'), 
							 'empl_type' =>  $_POST['empl_type'], 
							 'working_branch' =>  $_POST['working_place'],
						 	 'mod_of_pay' => $_POST['mod_of_pay'],
							 'bank_name' => $_POST['bank_name'],
							 'acc_no' => $_POST['acc_no']);
			$Allowance = get_allowances();
			$gross_Earnings = 0 ;
			while ($single = db_fetch($Allowance)) {	
				if(isset($_POST[$single['id']]))
					$jobs_arr[$single['id']] = $_POST[$single['id']];
				if($single['type'] == 'Earnings')
					$gross_Earnings += $_POST[$single['id']];
			}
			$jobs_arr['gross'] = $gross_Earnings;
			$jobs_arr['gross_pay_annum'] = $gross_Earnings*12;

			Insert('kv_empl_job', $jobs_arr);
			$kv_empl_id = $_POST['empl_id']; 
			if(db_has_empl_id()) {
				kv_update_next_empl_id_new($kv_empl_id);   
			}else {  
				kv_add_next_empl_id_new($kv_empl_id);     
			}
			display_notification(_("A new Employee has been added."));
			//clear_data();			
			set_focus('empl_id');
			$Ajax->activate('empl_id');
		}		
		
		if (isset($_FILES['pic']) && $_FILES['pic']['name'] != '') {
			$selected_id = $kv_empl_id;
			$result = $_FILES['pic']['error'];
			$upload_file = 'Yes'; //Assume all is well to start off with
			$filename = company_path().'/images/empl';
			if (!file_exists($filename)){
				mkdir($filename);
			}	
			$filename .= "/".empl_img_name($selected_id).".jpg";
						
			if ((list($width, $height, $type, $attr) = getimagesize($_FILES['pic']['tmp_name'])) !== false)
				$imagetype = $type;
			else
				$imagetype = false;
			
			if ($imagetype != IMAGETYPE_GIF && $imagetype != IMAGETYPE_JPEG && $imagetype != IMAGETYPE_PNG){	
				display_warning( _('Only graphics files can be uploaded'));
				$upload_file ='No';
			}
			elseif (!in_array(strtoupper(substr(trim($_FILES['pic']['name']), strlen($_FILES['pic']['name']) - 3)), array('JPG','PNG','GIF'))){
				display_warning(_('Only graphics files are supported - a file extension of .jpg, .png or .gif is expected'));
				$upload_file ='No';
			} 
			elseif ( $_FILES['pic']['size'] > ($max_image_size * 1024)) { //File Size Check
				display_warning(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $max_image_size);
				$upload_file ='No';
			} 
			elseif (file_exists($filename)){
				$result = unlink($filename);
				if (!$result) 	{
					display_error(_('The existing image could not be removed'));
					$upload_file ='No';
				}
			}
			
			if ($upload_file == 'Yes'){
				$result  =  move_uploaded_file($_FILES['pic']['tmp_name'], $filename);
			}
			$Ajax->activate('details');
	 
		}
		$Ajax->activate('_page_body');
	}
}

//------------------------------------------------------------------------------------

if (isset($_POST['delete']) && strlen($_POST['delete']) > 1) {
	$selected_id = $_POST['empl_id'];

	if (key_in_foreign_table($selected_id, 'kv_empl_salary', 'empl_id')){
		
		display_error(_("Cannot delete this Employee because Payroll Processed to this employee And it will be  added in the financial Transactions."));
	}else {
		delete_employee($selected_id);
		$filename = company_path().'/images/empl/'.empl_img_name($selected_id).".jpg";
		if (file_exists($filename))
			unlink($filename);
		display_notification(_("Selected Employee has been deleted."));
		$_POST['selected_id'] = '';
		clear_data();
		set_focus('selected_id');
		$new_item = true;
		$Ajax->activate('_page_body');	
	}
}

function empl_personal_data(&$selected_id) {
	br();
	global $SysPrefs, $path_to_root, $new_item, $pic_height;
	
	start_outer_table(TABLESTYLE2);

	table_section(1);

	table_section_title(_("Employee Informations"));
	
	//------------------------------------------------------------------------------------
	if ($new_item) {
		//$_POST['empl_id'] = $empl_id = (int)date('Y').(int)date("m").(count_employees()+1);
		
		if(!isset($_POST['empl_id']))
			$_POST['empl_id'] = '';
			
		text_row(_("Employee Id:"), 'empl_id', $_POST['empl_id'], 21, 20);		
		//unset($_POST['empl_id']);
		$_POST['inactive'] = 0;
		if(!isset($_POST['empl_firstname']))
			$_POST['empl_firstname'] = '';
		if(!isset($_POST['empl_lastname']))
			$_POST['empl_lastname'] = '';
		if(!isset($_POST['addr_line1']))
			$_POST['addr_line1']= ''; 
		if(!isset($_POST['addr_line2']))
			$_POST['addr_line2']= ''; 
		if(!isset($_POST['empl_city']))
			$_POST['empl_city']= ''; 
		if(!isset($_POST['empl_state']))
			$_POST['empl_state']= ''; 
		if(!isset($_POST['date_of_birth']))
			$_POST['date_of_birth']=add_years(Today(), -20);
	} else	{ // Must be modifying an existing item
		if (get_post('empl_id') != get_post('selected_id') || get_post('addupdate')) { // first item display

			$_POST['empl_id'] = $_POST['selected_id'];

			$myrow = get_employee($_POST['empl_id']);

			$_POST['empl_id'] = $myrow["empl_id"];
			$_POST['empl_salutation'] = $myrow["empl_salutation"];
			$_POST['empl_firstname'] = $myrow["empl_firstname"];
			$_POST['empl_lastname'] = $myrow["empl_lastname"];
			$_POST['addr_line1']  = $myrow["addr_line1"];
			$_POST['addr_line2']  = $myrow["addr_line2"];
			$_POST['empl_city']  = $myrow["empl_city"];
			$_POST['country']  = $myrow["country"];
			$_POST['empl_state']  = $myrow["empl_state"];
			$_POST['home_phone']	= $myrow["home_phone"];
			$_POST['mobile_phone']  = $myrow["mobile_phone"];
			$_POST['email']  = $myrow["email"];
			$_POST['gender']  = $myrow["gender"];
			$_POST['date_of_birth']  = sql2date($myrow["date_of_birth"]);
			$_POST['age']  = $myrow["age"];
			$_POST['marital_status']  = $myrow["marital_status"];
			
			$_POST['status'] = $myrow["status"];
			$_POST['del_image'] = 0;
			$_POST['pic'] = '';

	}
		label_row(_("Employee Id:*"),$_POST['empl_id']);

		hidden('empl_id', $_POST['empl_id']);
		
		set_focus('description');
	}
	hidden('ethnic_origin', 0);
	kv_empl_salutation_list_row( _("Salutation:"), 'empl_salutation', null);
	text_row(_("First Name:*"), 'empl_firstname', $_POST['empl_firstname'], 35, 100);
	text_row(_("Last Name:"), 'empl_lastname', $_POST['empl_lastname'],  35, 100);
	//text_row(_("Last Name:*"), 'empl_lastname', $_POST['empl_lastname'], 40, 80);	 

	table_section_title(_("Address"));
	//textarea_row(_("Present Address:"), 'pre_address', $_POST['pre_address'], 35, 5);
	text_row(_("Line 1:"), 'addr_line1', $_POST['addr_line1'], 35, 100);
	text_row(_("Line 2:"), 'addr_line2', $_POST['addr_line2'], 35, 100);
	text_row(_("City:"), 'empl_city', $_POST['empl_city'], 35, 100);
	text_row(_("State:"), 'empl_state', $_POST['empl_state'], 35, 100);
 	country_list_row(_("Country:"), 'country', null);
	
	table_section_title(_("Contact Details"));
	//text_row(_("Office Phone:"), 'office_phone', null, 40, 40);
	text_row(_("Home Phone:"), 'home_phone', null,  35, 100);
	text_row(_("Mobile Phone:*"), 'mobile_phone', null,  35, 100);
	text_row(_("Email:*"), 'email', null, 35, 100);	

	if (!isset($_POST['empl_id']) || $new_item) {
		//check_row(_("Create User Account*:"), 'register_user_acc', null);	
		
		table_section_title(_("Job Details"));
		
		hrm_empl_grade_list( _("Grade :"), 'grade', null);	
		department_list_row( _("Department :"), 'department', null);	
		hrm_empl_desig_group(_("Desgination Group *:"), 'desig_group', null);
		text_row(_("Desgination *:"), 'desig', null,  35, 100);
		//text_row(_("Basic Salary *:"), 'basic_salary', null, 30, 30);
		date_row(_("Date of Join") . ":", 'joining');
		hrm_empl_type_row(_("Employment Type*:"), 'empl_type', null);
		workcenter_list_row(_("Working Place*:"), 'working_place');
		
	}
	hrm_empl_status_list(_("Status*:"), 'status', null);
		
	table_section(2);

	div_start('payroll_tbl');

	// Add image upload for New Item 
	table_section_title(_("Personal Details"));
	$stock_img_link = "";
	$check_remove_image = false;
	if ($selected_id!= '' && file_exists(company_path().'/images/empl/'.empl_img_name($_POST['empl_id']).".jpg")){	
		$stock_img_link .= "<img id='empl_profile_pic' alt = '[".$_POST['empl_id'].".jpg"."]' src='".company_path().'/images/empl/'.empl_img_name($_POST['empl_id']).	".jpg?nocache=".rand()."'"." height='150' border='1'>";
		$check_remove_image = true;
	} 
	else {
		$stock_img_link .= "<img id='empl_profile_pic' alt = '[".$_POST['empl_id'].".jpg"."]' src='".$path_to_root.'/modules/ExtendedHRM/images/no-image.png'. "?nocache=".rand()."'"." height='150' border='1'>";
	}
	label_row("&nbsp;", $stock_img_link);	
	
	kv_image_row(_("Photo (.jpg)") . ":", 'pic', 'pic');	
	
	if ($check_remove_image)
		check_row(_("Delete Image:"), 'del_image');	 	
	
	//text_row(_("Skype ID:"), 'skype', null, 40, 40);	
	//text_row(_("LinkedIn:"), 'linkedin', null, 40, 40);
	kv_empl_gender_list_row( _("Gender:"), 'gender', null);
	date_row(_("Date of Birth") . ":", 'date_of_birth');
	text_row(_("Age:"), 'age', null, 3, 10);	
	hrm_empl_marital_list_row( _("Marital Status:"), 'marital_status', null);
	
	hidden('empl_page', 'info') ; 
	if (!isset($_POST['empl_id']) || $new_item) {

		table_section_title(_("Payment Details - Earnings"));	
		$EarAllowance = get_allowances('Earnings');
		$DedAllowance = get_allowances('Deductions');
		$basic_id = kv_get_basic();
		kv_basic_row(get_allowance_name($basic_id), $basic_id, 15, 100, null, true);
		while ($single = db_fetch($EarAllowance)) {	
			if($single['value'] == 'Percentage' && $single['percentage']>0){			
				$default_value = get_post($basic_id)*($single['percentage']/100);
			}else {
				$default_value = null;
			}
			if($single['id'] != $basic_id){
				kv_text_row_ex(_($single['description']." ".($single['type']=='Deductions' ? '(-)': '')." :"), $single['id'], 15, 100, null, $default_value); 			
			}		
		}
		table_section_title(_("Deductions"));
		$prof_tax = kv_get_Tax_allowance();
		while ($single = db_fetch($DedAllowance)) {	
			if($single['value'] == 'Percentage' && $single['percentage']>0){			
				$default_value = get_post($basic_id)*($single['percentage']/100);
			}else {
				$default_value = null;
			}
			if($single['id'] != $prof_tax){
				kv_text_row_ex(_($single['description']." ".($single['type']=='Deductions' ? '(-)': '')." :"), $single['id'], 15, 100, null, $default_value); 			
			}		
		}

		table_section_title(_("Payment Mode"));
		
		hrm_empl_mop_list(_("Mode of Pay *:"), 'mod_of_pay', null);		
		text_row(_("Bank Name *:"), 'bank_name', null,  15, 100);
		text_row(_("Bank Account No *:"), 'acc_no', null,  15, 100);
	}
	
	end_outer_table(1);
	div_end();
	div_start('controls');
	if (!isset($_POST['empl_id']) || $new_item) {
		submit_center('addupdate', _("Add New Employee"), true, '', 'default');
	} else {
		submit_center_first('addupdate', _("Update Employee Information"), '',@$_REQUEST['popup'] ? true : 'default');
		submit_return('select', get_post('selected_id'), _("Select this items and return to document entry."), 'default');		
		submit('delete', _("Delete employee"), true, '', true);
		submit_center_last('cancel', _("Cancel"), _("Cancel Edition"), 'cancel');
	}
	div_end();
}

function empl_leave_data($empl_id) {
	br();
	
	div_start('details');
		$total_days =  31;
		$selected_empl = get_employee_whole_attendance($empl_id);
			//print_r($selected_empl);
		if(!empty($selected_empl)){
			start_table(TABLESTYLE);
			echo  "<tr><td class='tableheader'>" . _("Year") . "</td><td class='tableheader'>" . _("Month") . "</td>";
					 
			for($kv=1; $kv<=$total_days; $kv++){						
				echo "<td style='background-color:#e0db98' class='tableheader'>". $kv. "</td>";												
			}					
			echo "<td class='tableheader'>" . _("Working Days") . "</td><td class='tableheader'>" . _("Leave Days") . "</td><td class='tableheader'>" . _("LOP Days") . "</td><td class='tableheader'>" . _("Payable Days") . "</td></tr>";
			foreach ($selected_empl as $single_month) {
				$fiscal_yr = get_fiscalyear($single_month['year']); 
				echo '<tr style="text-align:center"><td>'.$fiscal_yr['begin'].'-'.$fiscal_yr['end'].'</td><td>'.date("F", strtotime("2016-".$single_month['month']."-01")).'</td>';
				$leave_Day = 0 ;
				$month_days =  date("t", strtotime($single_month['year']."-".$single_month['month']."-01"));
				for($kv=5; $kv<=$total_days+4; $kv++){
					echo '<td>'. ($single_month[$kv]? $single_month[$kv]: '-').'</td>';
					if($single_month[$kv] == 'A')
						$leave_Day += 1;
					if($single_month[$kv] == 'HD')
						$leave_Day += 0.5;
				}
				$Payable_days=$single_month['year']-$leave_Day;
				echo '<td>'.$single_month['year'].' </td>  <td>'. $leave_Day.'</td> <td>'. $leave_Day.' </td> <td>'.$Payable_days.' </td><tr>';
			}	
			end_table(1);
		}else 
			display_notification(_("No data Exist for the selected Employee."));
}

//-----------------------------------------------------------------------------------------

function empl_payroll_data($empl_id){
	global $SysPrefs, $path_to_root;
	br();

	$get_employees_list = get_emply_salary($empl_id);

	if(!empty($get_employees_list)){
		start_table(TABLESTYLE, "width=90%");
    $th = array(_("Fiscal Year"),_("Month"));

    $Allowance = get_allowances('Earnings');
	while ($single = db_fetch($Allowance)) {	
		$th[] = $single['description'];
	}
	$th[] = _("OT & Other Allowance");
	$th[] = _("Gross Pay");
	
	$Allowance = get_allowances('Deductions');
	while ($single = db_fetch($Allowance)) {	
		$th[] = $single['description'];
	}
   	$th1 = array(_("Loan"),_("LOP Days"),_("LOP Amount"),_("Misc."),_("Total Deduction"),_("Net Salary"), _(""), _(""));
   	$th_final = array_merge($th, $th1);

	table_header($th_final);
		
		
	$get_employees_list = get_emply_salary($empl_id);
			
	$Total_gross = $total_net = 0; 
	foreach($get_employees_list as $data_for_empl) { 

		if($data_for_empl) {
			start_row();
			$fiscal_yr = get_fiscalyear($data_for_empl['year']); 
				$employee_leave_record = get_empl_attendance_for_month($data_for_empl['empl_id'], $data_for_empl['month'], $data_for_empl['year']);
				label_cell($fiscal_yr['begin'].' '.$fiscal_yr['end']);
				label_cell(date("F", strtotime("2016-".$data_for_empl['month']."-01")));
				$EarAllowance = get_allowances('Earnings');
					while ($single = db_fetch($EarAllowance)) {	
						label_cell($data_for_empl[$single['id']]);
					}

					label_cell($data_for_empl['ot_other_allowance']);
					label_cell($data_for_empl['gross']);

					$total_deduct = $data_for_empl['misc']+$data_for_empl['loan']+$data_for_empl['lop_amount']; 
					$Allowance = get_allowances('Deductions');
					while ($single = db_fetch($Allowance)) {	
						label_cell($data_for_empl[$single['id']]);
						$total_deduct += $data_for_empl[$single['id']];
					}
					//label_cell($data_for_empl['adv_sal']);
					label_cell($data_for_empl['loan']);
					label_cell($employee_leave_record);
					label_cell($data_for_empl['lop_amount']);
					label_cell($data_for_empl['misc']);					
					label_cell($total_deduct);
					label_cell($data_for_empl['net_pay']);

					$Total_gross += $data_for_empl['gross'];
					$total_net += $data_for_empl['net_pay'];
					//label_cell($data_for_empl['other_deduction']);
					label_cell('<a href="'.$path_to_root.'/modules/ExtendedHRM/payslip.php?employee_id='.$data_for_empl['empl_id'].'&month='.$data_for_empl['month'].'&year='.$data_for_empl['year'].'" onclick="javascript:openWindow(this.href,this.target); return false;"  target="_blank" > <img src="'.$path_to_root.'/themes/default/images/gl.png" width="12" height="12" border="0" title="GL"></a>');
					label_cell('<a onclick="javascript:openWindow(this.href,this.target); return false;" href="'.$path_to_root.'/modules/ExtendedHRM/reports/rep802.php?PARAM_0='.$data_for_empl['year'].'&PARAM_1='.$data_for_empl['month'].'&PARAM_2='.$data_for_empl["empl_id"].'&rep_v=yes" target="_blank" class="printlink"> <img src="'.$path_to_root.'/themes/default/images/print.png" width="12" height="12" border="0" title="Print"> </a>');
					
				end_row();
			}
		}
		start_row();
		$Earnings_colum_count = get_allowances_count('Earnings');
			$Deductions_colum_count = get_allowances_count('Deductions');
			$gross_colm_cnt = $Earnings_colum_count+2; 
			$net_colm_cnt = $Deductions_colum_count+3; 
			echo " <td colspan='".$gross_colm_cnt."'> </td> <td><strong>Total Gross</strong></td><td><strong>".$Total_gross."</strong></td> ";
				echo "<td colspan='".$net_colm_cnt."' align='right'></td> <td colspan='2'><strong>Total Net Salary</strong></td> <td><strong>". $total_net."</strong></td><td> </td> <td> </td>";
			
		end_row();		
    end_table(1);
}else {
	display_notification(_("No data Exist for the selected Employee."));
}
	
}

//-------------------------------------------------------------------------------------------- 

start_form(true);

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

div_start('details');

$selected_id = get_post('selected_id');
if (!$selected_id)
	unset($_POST['_tabs_sel']); // force settings tab for new customer
tabbed_content_start('tabs', array(
		'personal' => array(_('Personal Info'), $selected_id),
		'job' => array(_('Job'), $selected_id),
		//'contacts' => array(_('Contacts'), $selected_id),		
		'education' => array(_('Education'), $selected_id),
		'previous_emplment' => array(_('Previous Employment'), $selected_id),
		'training' => array(_('Training'), $selected_id),//,		
		'leave' => array(_('Leave'), $selected_id),
		'payroll' => array(_('Payroll History'), $selected_id)//,		
		//'assets' => array(_('Assets'), $selected_id)	
				
	));
	
	switch (get_post('_tabs_sel')) {
		default:
		case 'personal':
			empl_personal_data($selected_id); 
			break;
		case 'job':			
			//empl_job_data($selected_id);	
			$_GET['selected_id'] = $selected_id;
			$_GET['popup'] = 1;
			include_once($path_to_root."/modules/ExtendedHRM/manage/add_empl_info_job.php");			
			break;
		case 'education':
			$degree = new degree('degree', $selected_id, 'employee');
			$degree->show();	
			break;
		case 'training':
			$training = new training('training', $selected_id, 'employee');
			$training->show();
			break;
		case 'previous_emplment':
			$exp = new experience('previous_emplment', $selected_id, 'employee');
			$exp->show();
			break;
		case 'leave':
			empl_leave_data($selected_id); 
			break;
		case 'payroll':
			empl_payroll_data($selected_id); 
			break;
		case 'assets':
			break;

	}
br();
tabbed_content_end();

div_end();

hidden('popup', @$_REQUEST['popup']);
end_form();
end_page(@$_REQUEST['popup']);
?>
<style>
#empl_profile_pic { 
	border: 1px solid rgba(128, 128, 128, 0.68);
    border-radius: 2px;
}
</style>