<?php
/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
*****************************************/
$page_security = 'SA_OPEN';
$path_to_root="../..";
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
 
include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

page(_("PaySlip"));

check_db_has_employees(_("There is no employee in this system. Kindly Open <a href='".$path_to_root."/modules/ExtendedHRM/manage/employees.php'>Add And Manage Employees</a> to update it"));
 
 check_db_has_salary_account(_("There are no Salary Account defined in this system. Kindly Open <a href='".$path_to_root."/modules/ExtendedHRM/manage/hrm_settings.php'>Settings</a> to update it."));


if(isset($_GET['Added'])){
	display_notification(' The Employee Payslip is added #' .$_GET['Added']);
}
if (isset($_GET['employee_id'])){
	$_POST['employee_id'] = $_GET['employee_id'];
}
if (isset($_GET['month'])){
	$_POST['month'] = $_GET['month'];
}
if (isset($_GET['year'])){
	$_POST['year'] = $_GET['year'];
}
$employee_id = get_post('employee_id','');
$month = get_post('month','');
$year = get_post('year','');

if(list_updated('month') || get_post('RefreshInquiry')) {
	$month = get_post('month');   
	$Ajax->activate('totals_tbl');
}
	
div_start('totals_tbl');
start_form();
	if (db_has_employees()) {
		start_table(TABLESTYLE_NOBORDER);
		start_row();
		kv_fiscalyears_list_cells(_("Fiscal Year:"), 'year', null, true);
		kv_current_fiscal_months_list_cell("Months", "month", null, true);
		employee_list_cells(_("Select an Employee: "), 'employee_id', null,	_('New Employee'), true, check_value('show_inactive'));
		
		end_row();
		end_table();
		br();
		if (get_post('_show_inactive_update')) {
			$Ajax->activate('employee_id');
			$Ajax->activate('month');
			$Ajax->activate('year');
			set_focus('employee_id');
		}
	} 
	else {	
		hidden('employee_id');
		hidden('month');
		hidden('year');
	}

	$_POST['EmplName'] =  $_POST['desig']=''; 
	$_POST['ear_tot']= $_POST['deduct_tot']= $_POST['empl_dept']= $_POST['employee_id'] = $_POST['absent'] = $_POST['lop_amount'] =  $_POST['adv_sal']= $_POST['net_pay'] = $_POST['loan'] =0;

//$allow = kv_get_sal_details_file(12, 1);
		//	while ($single = db_fetch($allow)) {	
		//		print_r($single);
		//	}

	$Allowance = get_allowances();
	while ($single = db_fetch($Allowance)) {	
		$_POST[$single['id']]=0;				
	}

	$dat_of_pay = Today();

	$prof_tax = kv_get_Taxable_field();

 	if(isset($_POST[$prof_tax]) && $_POST[$prof_tax] > 0){ }
 	else{
 		$_POST[$prof_tax] = 0 ;
 	}
 	if(isset($_POST['misc']) && $_POST['misc'] > 0){ }
 	else{
 		$_POST['misc'] = 0 ;
 	} 	
	if(isset($_POST['ot_other_allowance']) && $_POST['ot_other_allowance'] > 0){}
 	else{
 		$_POST['ot_other_allowance'] = 0 ;
 	}
 	$months_with_years_list = kv_get_months_with_years_in_fiscal_year($year);

 	$months_with_years_list[get_post('month')];

 	$joining_date = get_employee_join_date($employee_id);

 	$month_name = kv_month_name_by_id(get_post('month'));
	
		$_POST['today_date']=date("d-F-Y");
		if(isset($employee_id) && $employee_id != '' && $months_with_years_list[get_post('month')] >= $joining_date ) {
			$sal_row = get_empl_sal_details($employee_id, $month, $year); 
			$name_and_dept = get_empl_name_dept($employee_id); 
			$_POST['empl_dept']=get_department_name($name_and_dept ['deptment']);
			$_POST['absent'] = get_empl_attendance_for_month($employee_id, $month, $year);
			$_POST['desig'] = kv_get_empl_desig($employee_id);
			$_POST['EmplName']=$name_and_dept ['name'];
			
			$Allowance = get_allowances();
			while ($single = db_fetch($Allowance)) {
				$_POST[$single['id']] = $sal_row[$single['id']];
			}
			$_POST['employee_id'] = $employee_id;

			if(isset($_POST[$prof_tax]) && $_POST[$prof_tax] == 0 && isset($sal_row[$prof_tax])&& $sal_row[$prof_tax]> 0){
				$_POST[$prof_tax] = $sal_row[$prof_tax];
			}
			
			$_POST['loan'] = get_empl_loan_monthly_payment($employee_id, sql2date($months_with_years_list[get_post('month')]));

			$Allowance = get_allowances('Earnings');
			$gross_4_LOP = 0 ;
			while ($single = db_fetch($Allowance)) {	
				$gross_4_LOP += $_POST[$single['id']];
			}
			
			$total_days =  date("t", strtotime($months_with_years_list[get_post('month')]));
			$_POST['lop_amount'] =  round($_POST['absent']*$gross_4_LOP/$total_days, 2);

			if(isset($sal_row['net_pay'])){
				$_POST['lop_amount'] =  $sal_row['lop_amount'];
				$_POST[$prof_tax] = $sal_row[$prof_tax];
				$_POST['adv_sal']= $sal_row['adv_sal'];
				$_POST['misc'] =  $sal_row['misc']; 
				$_POST['net_pay'] = $sal_row['net_pay'];			 
				$_POST['ot_other_allowance'] = $sal_row['ot_other_allowance'];			 
				$_POST['today_date'] = $sal_row['date'];	
				$_POST['loan'] = $sal_row['loan']		 ;
			} 		
		}else{
			if($months_with_years_list[get_post('month')] < $joining_date)
				display_warning(_("You can't Pay Employee Salary before his Joining Date!"));
		}
		if(!isset($sal_row['net_pay'])){
			$_POST[$prof_tax] = kv_get_tax_for_an_employee($_POST['employee_id'], $year);
		}
		//display_notification($month_name);
		$deduct_tot = $_POST['adv_sal']+$_POST['lop_amount']+$_POST['loan'];
		$DedAllowance = get_allowances('Deductions');
		while ($single = db_fetch($DedAllowance)) {	
			if($single['id'] != $prof_tax)
				$deduct_tot += $_POST[$single['id']];
		}
		if(isset($_POST[$prof_tax])){
			$deduct_tot += get_post($prof_tax);
		}
		if(isset($_POST['misc'])){
			$deduct_tot += get_post('misc');
		}
		$EarAllowance = get_allowances('Earnings');
		$_POST['ear_tot'] = $_POST['ot_other_allowance'];
		while ($single = db_fetch($EarAllowance)) {	
			$_POST['ear_tot'] += $_POST[$single['id']];
		}

		if(!isset($sal_row['net_pay'])){
			$_POST['net_pay'] = $_POST['ear_tot']-$deduct_tot;
		}	

		start_outer_table(TABLESTYLE);
		table_section(1);
		label_row(_(" Employee No:"), $_POST['employee_id'], null, 30, 30);
		label_row(_(" Employee Name:"), $_POST['EmplName'], null, 30, 30);
		label_row(_(" Department:"), $_POST['empl_dept'], null, 30, 30);
		label_row(_(" Month of Payment:"), $month_name, null, 30, 30);
		
		table_section_title(_("Earnings"));
		$Allowance = get_allowances('Earnings');
		while ($single = db_fetch($Allowance)) {	
			label_row(_($single['description']), $_POST[$single['id']], null, 30, 30);
		}
		if(isset($sal_row['net_pay'])){
			label_row(_("OT & Other Expenses:"), $_POST['ot_other_allowance']);			
		}else{
			text_row(_("OT & Other Expenses :"), 'ot_other_allowance', null, 10, 10);
		}	
		
		//label_row(_(" "), '', null, 30, 30);
		table_section_title(_(""));
		label_row(_(" Total Earning(Gross Salary):"), $_POST['ear_tot'], 'style="color:#FF9800; background-color:#f9f2bb;"', 'style="color:#FF9800; background-color:#f9f2bb;"');

		table_section(2);
		
		label_row(_(" Date of Payment:"), date("d-F-Y", strtotime($_POST['today_date'])), null, 30, 30);
		label_row(_(" LOP Days:"), $_POST['absent'], null, 30, 30);
		label_row(_(" Designation:"), $_POST['desig'], null, 30, 30);
	   
		table_section_title(_("Deduction"));
				
		$Allowance = get_allowances('Deductions');
		while ($single = db_fetch($Allowance)) {	
			label_row(_($single['description']), $_POST[$single['id']], null, 30, 30);
		}
		label_row(_(" LOP Amount:"), $_POST['lop_amount'], null, 30, 30);
		label_row(_(" Loan :"), $_POST['loan'], null, 30, 30);
		if(isset($sal_row['net_pay'])){
			label_row(_(" Misc. :"), $_POST['misc'], null, 10, 30);
		}else{			
			text_row(_(" Misc. :"), 'misc', null, 10, 30);
			submit_cells('RefreshInquiry', _("Refresh"),'',_('Show Results'), 'default');
		}
		
		table_section_title(_(""));

		label_row(_(" Total Deductions"), $deduct_tot, 'style="color:#f55; background-color:#fed;"', 'style="color:#f55; background-color:#fed;"');
		label_row(_(" "), '', null, 30, 30);
		label_row(_(" Net Salary Payable:"), $_POST['net_pay'], 'style="color:#107B0F; background-color:#B7DBC1;"', 'style="color:#107B0F; background-color:#B7DBC1;"');

		end_outer_table();

		
		if(db_has_employee_payslip($employee_id, $month, $year) == false && $employee_id != null){
			br();
			br();

			$Allowance = get_allowances('Deductions');
			while ($single = db_fetch($Allowance)) {	
				hidden($single['id'], $_POST[$single['id']]);
			}
			hidden('lop_amount', $_POST['lop_amount']);
			hidden('loan', $_POST['loan']);
			hidden('net_pay', $_POST['net_pay']);
			hidden('date_of_pay', Today());
			$end_day_of_this = date("Y-m-t", strtotime($months_with_years_list[get_post('month')]));
			if( $end_day_of_this < date('Y-m-d'))
				submit_center('pay_salary', _("Process Payout"), true, _('Payout to Employees'), 'default');
			else
				display_warning(_("You can't Process Payroll of future!"));
			br();
			end_form();
		}
		if(db_has_employee_payslip($employee_id, $month, $year) == true && $employee_id != null){
			br();
			br(); 
			//print_document_link($row['trans_no'], _("Print"), true, ST_CUSTDELIVERY, ICON_PRINT);

			if(!isset($sal_row['id']))
				$sal_row['id'] = 0;
				$result = get_gl_trans(99, $sal_row['id']);

			if (db_num_rows($result) == 0){
				echo "<p><center>" . _("No general ledger transactions have been created for") . "</center></p><br><br>";
				end_page(true);
				exit;
			}

			/*show a table of the transactions returned by the sql */
			$dim = get_company_pref('use_dimension');

			if ($dim == 2)
				$th = array(_("Account Code"), _("Account Name"), _("Dimension")." 1", _("Dimension")." 2",
					_("Debit"), _("Credit"), _("Memo"));
			else if ($dim == 1)
				$th = array(_("Account Code"), _("Account Name"), _("Dimension"),
					_("Debit"), _("Credit"), _("Memo"));
			else		
				$th = array(_("Account Code"), _("Account Name"),
					_("Debit"), _("Credit"), _("Memo"));
			$k = 0; //row colour counter
			$heading_shown = false;

			$credit = $debit = 0;
			while ($myrow = db_fetch($result)) 	{
				if ($myrow['amount'] == 0) continue;
				if (!$heading_shown){
					//display_gl_heading($myrow);
					start_table(TABLESTYLE, "width='95%'");
					table_header($th);
					$heading_shown = true;
				}	

				alt_table_row_color($k);
				
				label_cell($myrow['account']);
				label_cell($myrow['account_name']);
				if ($dim >= 1)
					label_cell(get_dimension_string($myrow['dimension_id'], true));
				if ($dim > 1)
					label_cell(get_dimension_string($myrow['dimension2_id'], true));

				display_debit_or_credit_cells($myrow['amount']);
				label_cell($myrow['memo_']);
				end_row();
				if ($myrow['amount'] > 0 ) 
					$debit += $myrow['amount'];
				else 
					$credit += $myrow['amount'];
			}
			if ($heading_shown){
				start_row("class='inquirybg' style='font-weight:bold'");
				label_cell(_("Total"), "colspan=2");
				if ($dim >= 1)
					label_cell('');
				if ($dim > 1)
					label_cell('');
				amount_cell($debit);
				amount_cell(-$credit);
				label_cell('');
				end_row();
				end_table(1);
			}

			echo '<center> <a href="'.$path_to_root.'/modules/ExtendedHRM/reports/rep802.php?PARAM_0='.$year.'&PARAM_1='.$month.'&PARAM_2='.$employee_id.'&rep_v=yes" target="_blank" class="printlink"> Print </a> </center>'; 
			br();
		}
	div_end();

	if(get_post('pay_salary')) {
		$salary_account= GetSingleValue('kv_empl_option', 'option_value', array('option_name'=>'salary_account'));
		$paid_from_account= GetSingleValue('kv_empl_option', 'option_value', array('option_name'=>'paid_from_account'));

		$jobs_arr =  array('empl_id' => $_POST['employee_id'],
							 'month' => $_POST['month'],
							 'year' => $_POST['year'],
							 'gross' => $_POST['ear_tot'],
							 'loan' => $_POST['loan'] ,
							 'date' => array(Today(), 'date'), 
							 'adv_sal' => $_POST['adv_sal'],
							 'net_pay' =>  $_POST['net_pay'], 
							 'misc' =>  $_POST['misc'], 
							 'ot_other_allowance' =>  $_POST['ot_other_allowance'],
						 	 'lop_amount' => $_POST['lop_amount']);
			$Allowance = get_allowances();
			while ($single = db_fetch($Allowance)) {	
				$jobs_arr[$single['id']] = $_POST[$single['id']];				
			}
			$pay_slip_id = Insert('kv_empl_salary', $jobs_arr);
		
		if($_POST['loan'] > 0 )
			paid_empl_loan_month_payment($_POST['employee_id']);
			
		add_gl_trans(99, $pay_slip_id, $_POST['date_of_pay'], $salary_account, 0,0, 'Employee Salary #'.$_POST['employee_id'].'-'. kv_get_empl_name($_POST['employee_id']), $_POST['net_pay']);
		add_gl_trans(99, $pay_slip_id, $_POST['date_of_pay'], $paid_from_account, 0,0, 'Employee Salary #'.$_POST['employee_id'].'-'. kv_get_empl_name($_POST['employee_id']), -$_POST['net_pay']);
		
		meta_forward($_SERVER['PHP_SELF'], "Added=$pay_slip_id&employee_id=".$_POST['employee_id'].'&month='.$_POST['month'].'&year='.$_POST['year']);
	}	

end_page(); ?>