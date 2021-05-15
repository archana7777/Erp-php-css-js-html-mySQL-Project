<?php
/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
/*  Payslip PDF
*****************************************/
$page_security = 'SA_OPEN';

$path_to_root="../../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

global $path_to_root, $systypes_array, $kv_empl_gender;

	if(isset($_GET['rep_v'])){
		include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc");
	}
    $year = (isset($_POST['PARAM_0']) ? $_POST['PARAM_0'] : (isset($_GET['PARAM_0']) ? $_GET['PARAM_0'] : 1));
    $month = (isset($_POST['PARAM_1']) ? $_POST['PARAM_1'] : (isset($_GET['PARAM_1']) ? $_GET['PARAM_1'] : 01));
    $empl_id = (isset($_POST['PARAM_2']) ? $_POST['PARAM_2'] : (isset($_GET['PARAM_2']) ? $_GET['PARAM_2'] : 0));
	$comment = (isset($_POST['PARAM_3']) ? $_POST['PARAM_3'] : (isset($_GET['PARAM_3']) ? $_GET['PARAM_3'] : ''));;
    //$destination = (isset($_POST['PARAM_4']) ? $_POST['PARAM_4'] : (isset($_GET['PARAM_4']) ? $_GET['PARAM_4'] : ''));
    $_POST['REP_ID'] = 802; 

if(db_has_employee_payslip($empl_id, $month, $year)){
//	if ($destination)
	//	include_once($path_to_root . "/reporting/includes/excel_report.inc");
	//else
		include_once($path_to_root . "/modules/ExtendedHRM/reports/pdf_report.inc");

	$orientation = 'P';

	$cols = array(10,215,300,490);	
	
	$headers = array(_("Earnings"), _("Amount"), _("Deductions"), _("Amount"));

	$aligns = array('left',	'left', 'left', 'left');

    $rep = new FrontReport(_('Payslip'), "Payslip", user_pagesize(), 9, $orientation);

	function get_empl_sal_details_file($empl_id, $month, $year){
		$sql = "SELECT * FROM ".TB_PREF."kv_empl_salary	WHERE empl_id=".db_escape($empl_id)." AND month=".db_escape($month)." AND year=".db_escape($year);

		return db_query($sql,"No transactions were returned");
	}
	
    $result = get_empl_sal_details_file($empl_id, $month, $year);	
	
	if ($myrow = db_fetch($result))	{
		$name_and_dept = get_empl_name_dept($myrow['empl_id']);
		$employee_info = array(
					'id' => $myrow['id'],
					'empl_id' => $myrow['empl_id'],
					'empl_name' => $name_and_dept['name'],
					'department' => get_department_name($name_and_dept ['deptment']),
					'desig' => kv_get_empl_desig($myrow['empl_id']),
					'joining' => sql2date(get_employee_join_date($myrow['empl_id'])),
					'month' => $month,
					'year' => $year );

			$baccount = get_empl_bank_acc_details($myrow['empl_id']);
			
			$rep->SetHeaderType('Header2');	
		    $rep->Font();
		    $rep->Info(null, $cols, $headers, $aligns);
		    
		   //display_error(json_encode($myrow)."ygukgygug".json_encode($employee_info));

		    $contacts = array( 
		    			'email' => $name_and_dept['email'],
		    			'name2'	=>	null,
		    			'name'  => 	$name_and_dept['name'],
		    			'lang'	=> null,
		    		);

		    $rep->SetCommonData($employee_info, $baccount, array( $contacts),  'payslip');
		    $rep->NewPage();
			$rep->NewLine();		

			$text_value=40;
			$line_value=670;
			$earallows = array();
			$EarAllowance = get_allowances('Earnings');
			while($EarAllow = db_fetch($EarAllowance)){
				$earallows[] = $EarAllow;
			}

			$DedAllowance = get_allowances('Deductions');
			$dedallows=  array();
			while($DedAllow = db_fetch($DedAllowance)){
				$dedallows[] = $DedAllow;
			}
			$earnings_count = get_allowances_count('Earnings');
			$deductions_count = get_allowances_count('Deductions');

			//display_error(json_encode($dedallows));
			if($earnings_count > $deductions_count){
				$count_final  = $earnings_count;
			}else{
				$count_final = $deductions_count;
			}
			//display_error($count_final);
			$Value = -70;
			$total_deduction = 0;
			$count_difference = $count_final- $deductions_count;
			if($count_difference >= 2)
				$else_deduct = 0;
			else
				$else_deduct = 3;

			for($vj=0; $vj<$count_final;$vj++){
				if(isset($earallows[$vj])){
					$rep->Text($text_value, $earallows[$vj]['description'],0,0,$Value);
					$rep->Text(250, $myrow[$earallows[$vj]['id']],0,0,$Value);
				}
				if(isset($dedallows[$vj])){
					$rep->Text(330, $dedallows[$vj]['description'],0,0,$Value);
					$rep->Text(530, $myrow[$dedallows[$vj]['id']],0,0,$Value);
					$total_deduction += $myrow[$dedallows[$vj]['id']];
				}elseif($else_deduct==0){
					$rep->Text(330, 'Loan Amount ',0,0,$Value);
					$rep->Text(530, $myrow['loan'],0,0,$Value);
					$total_deduction += $myrow['loan'];
					$else_deduct++;
				}elseif($else_deduct==1){
					$rep->Text(330, 'LOP Amount',0,0,$Value);
					$rep->Text(530, $myrow['lop_amount'],0,0,$Value);
					$total_deduction += $myrow['lop_amount'];
					$else_deduct++;
				}
				
				//$rep->Line($line_value, 0.00001,0,0);
				$rep->NewLine(2);
				$Value++;
			}

			$rep->Text($text_value, 'OT & Other Allowance',0,0,$Value);
			$rep->Text(250, $myrow['ot_other_allowance'],0,0,$Value);
			$rep->Text(330, 'Misc',0,0,$Value);
			$rep->Text(530, $myrow['misc'],0,0,$Value);
			$total_deduction += $myrow['misc'];
		//	$rep->Line($line_value-125, 0.00001,0,0);
			$rep->NewLine(2);
			$Value++;
			if($else_deduct == 3){
				$rep->Text($text_value, ' ',0,0,-$Value);
				$rep->Text(250,  ' ',0,0,$Value);
				$rep->Text(330, 'Loan Amount ',0,0,$Value);
				$rep->Text(530, $myrow['loan'],0,0,$Value);
				$total_deduction += $myrow['loan'];
			//	$rep->Line($line_value-125, 0.00001,0,0);
				$rep->NewLine(2);
				$Value++;

				$rep->Text($text_value, '',0,0,$Value);
				$rep->Text(250, '',0,0,-65);
				$rep->Text(330, 'LOP Amount',0,0,$Value);
				$rep->Text(530, $myrow['lop_amount'],0,0,$Value);
				$total_deduction += $myrow['lop_amount'];
			//	$rep->Line($line_value-125, 0.00001,0,0);
				$rep->NewLine(2);
				$Value++;
			}
			
			$rep->row = 120;
			$rep->Line(205, 0.00001,0,0);	
			/* Gross pay*/
			$rep->SetTextColor(255, 152, 0);
			$rep->Text($text_value, 'Gross Pay(Total Earnings)',0,0,$Value);
			$rep->Text(250, $myrow['gross'],0,0,$Value);
			$rep->SetTextColor(203, 0, 0);
			$rep->Text(330, 'Total Deduction',0,0,$Value);
			$rep->Text(530, $total_deduction,0,0,$Value);
		//	$rep->Line($line_value-150, 0.00001,0,0);
			$rep->NewLine(1);
			$rep->SetTextColor(0, 0, 0);		
			
			/* $rep->Text($text_value, 'Advance Salary',0,0,1);
			$rep->Text(400, $myrow['adv_sal'],0,0,1);
			$rep->Line($line_value-225, 0.00001,0,0);
			$rep->NewLine(2);
			*/				
			$rep->Line(165, 0.00001,0,0);	
			$rep->SetTextColor(16, 123, 15);
			$rep->Text($text_value, 'Net Amount ( Total Earnings - Total Deduction)',0,0,-40);
			$rep->Text(530, $myrow['net_pay'],0,0,-40);			
			$rep->NewLine(1);
			$rep->Line(135, 0.00001,0,0);
			$rep->Line($line_value-585, 0.00001,0,0);	
			$rep->row = 180;	
			if($comment){				
				$rep->SetTextColor(0, 0, 0);
				$rep->Text($text_value, 'Comments',0,0,65);
				$rep->Text(200, $comment,0,0,65);  //$rep->NewLine(2);	
			}		
			$rep->Line($line_value-635, 0.00001,0,0);		
	}
			
	if ($rep->row < $rep->bottomMargin )
		$rep->NewPage();	
	$rep->End(); //1, 'Payslip ');
}else{
	display_warning("No Payroll Entry Found For Selected Period.");
}
?>