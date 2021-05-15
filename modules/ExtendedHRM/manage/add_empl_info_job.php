<?php
/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
*****************************************/

$page_security = 'HR_EMPL_INFO';
if (!@$_GET['popup'])
	$path_to_root = "..";
else	
	$path_to_root = "../../..";

include_once($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");

include_once($path_to_root . "/includes/date_functions.inc");

if (!@$_GET['popup'])
	page(_($help_context = "Employee Job informations"));

//---------------------------------------------------------------------------------------------------

simple_page_mode(true);

if (isset($_GET['selected_id'])){
	$selected_id = $_POST['empl_id'] =$_POST['selected_id'] = $_GET['selected_id'];
}

if (!@$_GET['popup'])
	start_form();
//------------------------------------------------------------------------------------------------------
if (list_updated('empl_id')) 	$Ajax->activate('price_details');

if (list_updated('empl_id') || isset($_POST['_curr_abrev_update']) || isset($_POST['_sales_type_id_update'])) {
	unset($_POST['price']);
	$Ajax->activate('price_details');
}


function can_process(){ 
	
	if(date2sql($_POST['joining']) > date('Y-m-d')){
		display_error(_("Invalid Joining Date for the Employee."));
		set_focus('joining');
		return false;
	}

	return true; 
}
if (($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM' )&& can_process()){	 
		//echo $_POST['empl_id'] ; 

		$jobs_arr =  array( 'grade' => $_POST['grade'],
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
		$gross_Earnings = 0;
		while ($single = db_fetch($Allowance)) {
			if(isset($_POST[$single['id']]))	
				$jobs_arr[$single['id']] = $_POST[$single['id']];
			if($single['type'] == 'Earnings')
				$gross_Earnings += $_POST[$single['id']];
		}

		$jobs_arr['gross'] = $gross_Earnings;
		$jobs_arr['gross_pay_annum'] = $gross_Earnings*12;

	if(!db_employee_has_job($_POST['empl_id'])) { 
		
			$jobs_arr['empl_id'] = $_POST['empl_id'];

			Insert('kv_empl_job', $jobs_arr);
			$kv_empl_id = $_POST['empl_id']; 
			set_focus('selected_id');
			$Ajax->activate('selected_id'); // in case of status change
			display_notification(_("A new Employee Job has been added. "));		
		} 
		else { 
			Update('kv_empl_job', array('empl_id', $_POST['empl_id']), $jobs_arr);
			
			$kv_empl_id = $selected_id; 
			set_focus('empl_id'); 
			$Ajax->activate('empl_id'); // in case of status change
			display_notification(_("Employee Job Information has been updated."));
		}
}

if ($Mode == 'RESET'){
	$selected_id = -1;
}

if (@$_GET['popup']){
	hidden('_tabs_sel', get_post('_tabs_sel'));
	hidden('popup', @$_GET['popup']);
}
	
//---------------------------------------------------------------------------------------
//echo $_POST['empl_id'];
$job_details = get_employee_job($_POST['empl_id']);
	
//print_r($job_details."Srserhser");
	$_POST['empl_id'] = $job_details['empl_id'];
	$_POST['grade'] = $job_details['grade'];
	$_POST['department'] = $job_details['department'];
	$_POST['desig_group'] = $job_details['desig_group'];
	$_POST['desig'] = $job_details['desig'];	
	$_POST['joining'] = sql2date($job_details['joining']);
	$_POST['empl_type'] = $job_details['empl_type'];
	//$_POST['shift'] = $job_details['shift'];
	$_POST['working_place'] = $job_details['working_branch']; 
	$_POST['mod_of_pay'] = $job_details['mod_of_pay'];
	$_POST['bank_name'] = $job_details['bank_name'];
	$_POST['acc_no'] = $job_details['acc_no'];

	$Allowance = get_allowances();
	while ($single = db_fetch($Allowance)) {	
		$_POST[$single['id']] = $job_details[$single['id']];
	}	
	
	div_start('price_details');
	br();
	start_outer_table(TABLESTYLE2);
	table_section(1);
	table_section_title(_("Job Details"));

	label_row(_("Employee Id:"),$_POST['empl_id']);	
	hrm_empl_grade_list( _("Grade :"), 'grade', null);	
	department_list_row( _("Department :"), 'department', null);	
	hrm_empl_desig_group(_("Desgination Group *:"), 'desig_group', null);
	text_row(_("Desgination *:"), 'desig', null, 30, 30);
	date_row(_("Joining") . ":", 'joining');
	//hrm_empl_status_list(_("Status*:"), 'empl_status', null);
	hrm_empl_type_row(_("Employment Type*:"), 'empl_type', null);
	//hrm_empl_shift(_("Shift*:"), 'shift', null);
	workcenter_list_row(_("Working Place*:"), 'working_place');
	//check_row(_("PF*:"), 'empl_pf', null);		
	hidden('empl_page', 'job') ; 
	table_section(2);

	table_section_title(_("Pay Details - Earnings"));

	//text_row(_("Gross Salary *:"), 'gr_salary', null, 30, 30);
	//$Allowance = get_allowances();
	
	$prof_tax = kv_get_Tax_allowance();
	$EarAllowance = get_allowances('Earnings');
	$DedAllowance = get_allowances('Deductions');
	$basic_id = kv_get_basic();
	//kv_basic_row(get_allowance_name($basic_id), $basic_id, 15, 100, null, true);
	text_row(_(get_allowance_name($basic_id)), $basic_id, null,  15, 100);
	while ($single = db_fetch($EarAllowance)) {	
		if($single['id'] != $basic_id)
			text_row(_($single['description']." ".($single['type'] =='Deductions' ? '(-)': '')." :"), $single['id'], null,  15, 100);
	}
	table_section_title(_("Deductions"));
	while ($single = db_fetch($DedAllowance)) {	
		if($single['id'] != $prof_tax)
			text_row(_($single['description']." ".($single['type'] =='Deductions' ? '(-)': '')." :"), $single['id'], null,  15, 100);
	}
	table_section_title(_("Payment Mode"));	
		hrm_empl_mop_list(_("Mode of Pay *:"), 'mod_of_pay', null);
		text_row(_("Bank Name *:"), 'bank_name', null, 30, 30);
		text_row(_("Bank Account No *:"), 'acc_no', null, 30, 30);
	
	end_outer_table(1);	
	
	submit_add_or_update_center($selected_id == -1, '', 'both');
	div_end();
if (!@$_GET['popup']){
	end_form();
	end_page(@$_GET['popup'], false, false);
}	
?>
