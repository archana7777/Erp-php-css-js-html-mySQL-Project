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
include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc" );
include($path_to_root . "/includes/ui.inc");

page(_("Payroll Process"));
 
check_db_has_employees(_("There is no employee in this system. Kindly Open <a href='".$path_to_root."/modules/ExtendedHRM/manage/employees.php'>Add And Manage Employees</a> to update it"));

if (isset($_GET['selected_id'])){
	$_POST['selected_id'] = $_GET['selected_id'];
}
if (isset($_GET['month'])){
	$_POST['month'] = $_GET['month'];
}
if (isset($_GET['year'])){
	$_POST['year'] = $_GET['year'];
}
$selected_id = get_post('selected_id','');
$month = get_post('month','');
$year = get_post('year','');
 
 if(list_updated('month')) {
		$month = get_post('month');   
		$Ajax->activate('totals_tbl');
}
start_form(true);

if (db_has_employees()) {
	start_table(TABLESTYLE_NOBORDER);
	start_row();	
		kv_fiscalyears_list_cells(_("Fiscal Year:"), 'year', null, true);
		kv_current_fiscal_months_list_cell("Months", "month", null, true);
		department_list_cells(_("Select a Department: "), 'selected_id', null,	_('No Department'), true, check_value('show_inactive'));
	end_row();	
	end_table();

	if (get_post('_show_inactive_update') || get_post('month') || get_post('year')) {
		$Ajax->activate('selected_id');
		$Ajax->activate('month');
		$Ajax->activate('year');
		$Ajax->activate('sal_calculation');
		set_focus('selected_id');
	}
}
else{
	hidden('selected_id', get_post('selected_id'));
	hidden('month', get_post('month'));
	hidden('year', get_post('year'));
}

div_start('sal_calculation');
	start_table(TABLESTYLE_NOBORDER, "width=40%");
	label_row(" <center>**Here, you can Calculate Salaries.  </center>", '', null ); 
	end_table();
   $prof_tax = kv_get_Taxable_field();
	start_table(TABLESTYLE, "width=90%");

	 $th = array(_("Empl Id "),_("Employee Name") );

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
   	$th1 = array(_("Loan"),_("LOP Days"),_("LOP Amount"),_("Misc."),_("Total Deduction"),_("Net Salary"), _(""));
   	$th_final = array_merge($th, $th1);
  
	table_header($th_final);

	$ipt_error = 0;

		
	if(empty($selected_id)) $selected_id = -1;
		/*if($hrm_year_list[$year]<= date('Y')){}
		else {
			display_error(_('The Selected Year Yet to Born!'));
			$ipt_error = 1;
		}*/
		
		$months_with_years_list = kv_get_months_with_years_in_fiscal_year($year);
		$months_with_years_list[get_post('month')];
		//print_r($months_with_years_list);
		if($months_with_years_list[get_post('month')] > date('Y-m-d')){
			display_error(_('The Selected Month Yet to Born!'));
			$ipt_error = 1;
		}
		if($ipt_error ==0) {
			$get_employees_list = get_empl_ids_from_dept_id($selected_id);
			
			$Total_gross = $total_net = 0; 


			foreach($get_employees_list as $single_empl) { 
				
				$data_for_empl = GetRow('kv_empl_job', array('empl_id' => $single_empl));

				//print_r($data_for_empl);
				//echo $year;
				$empl_id = $data_for_empl['empl_id'];
				$existing_empl_sal = GetRow('kv_empl_salary', array('empl_id' => $empl_id, 'month' => $month, 'year' => $year));

			//echo $months_with_years_list[get_post('month')].'_______'. get_employee_join_date($empl_id);
				if($data_for_empl && empty($existing_empl_sal) && $months_with_years_list[get_post('month')] >= get_employee_join_date($empl_id)) {
					
					$_POST[$empl_id.'empl_id']= $empl_id; 

					$EarAllowance = get_allowances('Earnings');
					while ($single = db_fetch($EarAllowance)) {							
						$_POST[$empl_id.$single['id']]= $data_for_empl[$single['id']]; 
					}

					$DedAllowance = get_allowances('Deductions');
					while ($single = db_fetch($DedAllowance)) {	
						if($single['id'] != $prof_tax)
							$_POST[$empl_id.$single['id']]= $data_for_empl[$single['id']];
					}
					$_POST[$empl_id.$prof_tax]=kv_get_tax_for_an_employee($empl_id,$year);

					$_POST[$empl_id.'gross_salary'] = 0;
					//$_POST[$empl_id.'ot_other_allowance'] = 0;
					
					$_POST[$empl_id.'lop_days'] =0;

					$_POST[$empl_id.'lop_amount'] = 0;

					$_POST[$empl_id.'loan_amount'] = 0;

					$_POST[$empl_id.'net_deductions'] = 0; 

					$_POST[$empl_id.'net_pay'] = 0;					
						
					if(!isset($_POST[$empl_id.'misc']))
						$_POST[$empl_id.'misc']= 0; 
						
					if(!isset($_POST[$empl_id.'ot_other_allowance']))
						$_POST[$empl_id.'ot_other_allowance']= 0;  
				}
			}

		div_start('totals_tbl');
		$dat_of_pay = Today();
		hidden('dat_of_pay', $dat_of_pay);
		//print_r($get_employees_list);
		//display_error($_POST[$empl_id.'prof_tax']);
		$empl_ids = array();
			foreach($get_employees_list as $empl_id) { 
				$existing_empl_sal = GetRow('kv_empl_salary', array('empl_id' => $empl_id, 'month' => $month, 'year' => $year));
			
				if($empl_id && empty($existing_empl_sal) && $months_with_years_list[get_post('month')] > get_employee_join_date($empl_id)) {
					//display_error(get_post($empl_id.'prof_tax'));
					$empl_ids[] = $empl_id;
					$_POST[$empl_id.'gross_sal'] = $_POST[$empl_id.'gross_salary'] = 0;
					$EarAllowance = get_allowances('Earnings');
					while ($single = db_fetch($EarAllowance)) {							
						$_POST[$empl_id.'gross_sal'] += input_num($empl_id.$single['id']);
						$_POST[$empl_id.'gross_salary'] += input_num($empl_id.$single['id']); 
					}

					$_POST[$empl_id.'gross_salary'] += input_num($empl_id.'ot_other_allowance');

					$total_days =  date("t", strtotime($months_with_years_list[get_post('month')]));

					$_POST[$empl_id.'lop_days'] = get_empl_attendance_for_month($empl_id, $month, $year);

					$_POST[$empl_id.'lop_amount'] = round(input_num($empl_id.'lop_days')*input_num($empl_id.'gross_sal')/$total_days, 2);

					$_POST[$empl_id.'loan_amount']= get_empl_loan_monthly_payment($empl_id, sql2date($months_with_years_list[get_post('month')]));

					$_POST[$empl_id.'net_deductions'] =input_num($empl_id.'misc')+input_num($empl_id.'loan_amount')+input_num($empl_id.'lop_amount'); 
					$DedAllowance = get_allowances('Deductions');
					while ($single = db_fetch($DedAllowance)) {							
						$_POST[$empl_id.'net_deductions'] += input_num($empl_id.$single['id']);
					}

					$_POST[$empl_id.'net_pay'] = input_num($empl_id.'gross_salary') - input_num($empl_id.'net_deductions'); 

					start_row();
			
					label_cell($empl_id);
					label_cell(kv_get_empl_name($empl_id));
					
					$EarAllowance = get_allowances('Earnings');
					while ($single = db_fetch($EarAllowance)) {	
						label_cell($_POST[$empl_id.$single['id']]);
						hidden($empl_id.$single['id'], $_POST[$empl_id.$single['id']] ); 
					}

					text_cells(null, $empl_id.'ot_other_allowance');
					
					label_cell( $_POST[$empl_id.'gross_salary'] );
						hidden($empl_id.'basic', $_POST[$empl_id.'gross_salary'] ); 

					$DedAllowance = get_allowances('Deductions');
					while ($single = db_fetch($DedAllowance)) {	
						label_cell($_POST[$empl_id.$single['id']]);
						hidden($empl_id.$single['id'], $_POST[$empl_id.$single['id']] );
					}

					label_cell($_POST[$empl_id.'loan_amount']);
						hidden($empl_id.'basic', $_POST[$empl_id.'loan_amount'] ); 

					label_cell($_POST[$empl_id.'lop_days'] );
						hidden($empl_id.'basic', $_POST[$empl_id.'lop_days'] ); 
					//label_cell($data_for_empl['lop_amount']);							
						
					label_cell($_POST[$empl_id.'lop_amount']);
						hidden($empl_id.'basic', $_POST[$empl_id.'lop_amount'] ); 
										
					text_cells(null, $empl_id.'misc');
					//text_cells(null, $empl_id.'prof_tax');
					label_cell($_POST[$empl_id.'net_deductions'] );
						hidden($empl_id.'basic', $_POST[$empl_id.'net_deductions'] ); 
					label_cell($_POST[$empl_id.'net_pay']);
						hidden($empl_id.'basic', $_POST[$empl_id.'net_pay'] ); 
					$Total_gross += $_POST[$empl_id.'gross_salary'];
					$total_net += $_POST[$empl_id.'net_pay'];
					label_cell("");
					
					end_row();
				}
			}
			//foreach ($empl_ids as $value) {
				hidden('empl_ids', implode("-", $empl_ids));
			//}
			
		div_end();

			
			start_row();
			$Earnings_colum_count = get_allowances_count('Earnings');
			$Deductions_colum_count = get_allowances_count('Deductions');
			$gross_colm_cnt = $Earnings_colum_count+2; 
			$net_colm_cnt = $Deductions_colum_count+3; 
				echo " <td colspan='".$gross_colm_cnt."'> </td> <td><strong>Total Gross</strong></td><td><strong>".$Total_gross."</strong></td> ";
				echo "<td colspan='".$net_colm_cnt."' align='right'></td> <td colspan='2'><strong>Total Net Salary</strong></td> <td><strong>". $total_net."</strong></td>";
				submit_cells('Refreshloan', _("Refresh"),'',_('Show Results'), true);
			end_row();
		}
    end_table(1);

    submit_center('pay_salary', _("Process Payout"), true, _('Payout to Employees'), 'default');

	div_end(); 
	end_form(); 

if(get_post('Refreshloan')){
	$Ajax->activate('totals_tbl');
}


if(get_post('pay_salary')) {
		$salary_account= GetSingleValue('kv_empl_option', 'option_value', array('option_name'=>'salary_account'));
		$paid_from_account= GetSingleValue('kv_empl_option', 'option_value', array('option_name'=>'paid_from_account'));
		
		$get_employees_list = explode("-", $_POST['empl_ids']);			


	foreach($get_employees_list as $empl_id) {  

		$jobs_arr =  array('empl_id' => $empl_id,
							 'month' => $_POST['month'],
							 'year' => $_POST['year'],
							 'gross' => $_POST[$empl_id.'gross_salary'],
							 'loan' => $_POST[$empl_id.'loan_amount'] ,
							 'date' => array(Today(), 'date'), 
							 'adv_sal' => 0,
							 'net_pay' => $_POST[$empl_id.'net_pay'], 
							 'misc' =>  $_POST[$empl_id.'misc'], 
							 'ot_other_allowance'=>$_POST[$empl_id.'ot_other_allowance'],
						 	 'lop_amount' => $_POST[$empl_id.'lop_amount']);
			$Allowance = get_allowances();
			while ($single = db_fetch($Allowance)) {	
				$jobs_arr[$single['id']] = $_POST[$empl_id.$single['id']];				
			}
			$pay_slip_id = Insert('kv_empl_salary', $jobs_arr);


		//display_notification(' The Employee Payslip is added #' .$_POST['date_of_pay']);
		if($_POST[$empl_id.'loan_amount'] > 0 )
			paid_empl_loan_month_payment($empl_id);
		add_gl_trans(99, $pay_slip_id, $_POST['dat_of_pay'], $salary_account, 0,0, 'Employee Salary #'.$empl_id.'-'. kv_get_empl_name($empl_id), $_POST[$empl_id.'net_pay']);
		add_gl_trans(99, $pay_slip_id, $_POST['dat_of_pay'], $paid_from_account, 0,0, 'Employee Salary #'.$empl_id.'-'. kv_get_empl_name($empl_id), -$_POST[$empl_id.'net_pay']);
	}
		
	meta_forward($path_to_root.'/modules/ExtendedHRM/inquires/payroll_history_inquiry.php', "selected_id=".$_POST['selected_id'].'&month='.$_POST['month'].'&year='.$_POST['year'].'&Added=yes');
}

end_page(); ?>