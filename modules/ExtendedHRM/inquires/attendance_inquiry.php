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
include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc" );
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
include_once($path_to_root . "/includes/db_pager.inc");
page(_("Attendance Inquiry"));
 
 check_db_has_employees(_("There is no employee in this system. Kindly Open <a href='".$path_to_root."/modules/ExtendedHRM/manage/employees.php'>Add And Manage Employees</a> to update it"));
 
 simple_page_mode(true);
//----------------------------------------------------------------------------------------
	$new_item = get_post('selected_id')=='' || get_post('cancel') ;
	$month = get_post('month','');
	$year = get_post('year','');
	if (isset($_GET['selected_id'])){
		$_POST['selected_id'] = $_GET['selected_id'];
	}
	$selected_id = get_post('selected_id');
	 if (list_updated('selected_id')) {
		$_POST['empl_id'] = $selected_id = get_post('selected_id');
	    $Ajax->activate('details');
	}
	if (isset($_GET['month'])){
		$_POST['month'] = $_GET['month'];
	}
	if (isset($_GET['year'])){
		$_POST['year'] = $_GET['year'];
	}

	
	if (list_updated('month')) {
		$month = get_post('month');   
		$Ajax->activate('details');
	}

//$month = date("m");
//----------------------------------------------------------------------------------------

	start_form(true);
		start_table(TABLESTYLE_NOBORDER);
			echo '<tr>';
				kv_fiscalyears_list_cells(_("Fiscal Year:"), 'year', null, true);
			 	kv_current_fiscal_months_list_cell("Months", "month", null, true);
			 	department_list_cells(_("Select a Department: "), 'selected_id', null,	_('No Department'), true, check_value('show_inactive'));
				$new_item = get_post('selected_id')=='';
		 	echo '</tr>';
	 	end_table(1);

	 	if (get_post('_show_inactive_update')) {
			$Ajax->activate('month');
			$Ajax->activate('details');
			$Ajax->activate('selected_id');		
			set_focus('month');
		}
		if($month==null){			 
			$month = $_POST['month'];
		}
		if($year==null){			 
			$year = $_POST['year'];
		}
		//echo $month;
		$total_days =  date("t", strtotime($year."-".$month."-01"));
		div_start('details');
		
			$selected_empl = kv_get_employees_list_based_on_dept($selected_id);
			//$selected_empl_attend_details=kv_get_attend_details($selected_id);
			start_table(TABLESTYLE);

			$months_with_years_list = kv_get_months_with_years_in_fiscal_year($year);
 			$ext_year = date("Y", strtotime($months_with_years_list[get_post('month')]));

				echo  "<tr>
					<td rowspan=2 class='tableheader'>" . _("Empl ID") . "</td>
					<td rowspan=2 class='tableheader'>" . _("Empl Name") . "</td>					
					<td colspan=".$total_days." class='tableheader'>" . _(date("Y - F", strtotime($ext_year."-".$month."-01"))) . "</td>
					<td rowspan=2 class='tableheader'>" . _("Working Days") . "</td>
					<td rowspan=2 class='tableheader'>" . _("Leave Days") . "</td>
					<td rowspan=2 class='tableheader'>" . _("LOP Days") . "</td>
					<td rowspan=2 class='tableheader'>" . _("Payable Days") . "</td>
					</tr><tr>";
					$weekly_off = GetSingleValue('kv_empl_option','option_value', array('option_name'=>'weekly_off'));
					$weekly_offdate= 0 ; 
					for($kv=1; $kv<=$total_days; $kv++){
						if(date("D", strtotime($ext_year."-".$month."-".$kv))  == $weekly_off){
							echo "<td style='background-color:#e0db98' class='tableheader'>". _(date("D d", strtotime($ext_year."-".$month."-".$kv))) . "</td>";
							if($weekly_offdate==0)
								$weekly_offdate=$kv;
						}else{
							echo "<td class='tableheader'>". _(date("D d", strtotime($ext_year."-".$month."-".$kv))) . "</td>";
						}
						
					}
					
					echo "</tr>";
				//$sql = kv_hrm_get_employee_list();
					while ($row = db_fetch_assoc($selected_empl)) {
						$details_single_empl = GetRow('kv_empl_attendancee', array('month' => $month, 'year' => $year, 'empl_id' => $row['empl_id'])); 
						
						echo '<tr style="text-align:center"><td>'.$row['empl_id'].'</td><td>'.$row['empl_firstname'].'</td>';
						$leave_Day = 0 ;
						$week_end=1;
						$weekly_offdat = $weekly_offdate;
						for($kv=5; $kv<=$total_days+4; $kv++){
							
							if($weekly_offdat == $week_end){
								$style="style='background-color: #fda8a8;'"; 
								$week_end=1;
								$weekly_offdat = 7;
							}else{
								$style=""; 
								$week_end++;
							}
							$vj = $kv-4; 
							echo '<td '.$style.' >'. ($details_single_empl[$vj]? $details_single_empl[$vj]: '-').'</td>';
							if($details_single_empl[$vj] == 'A')
								$leave_Day += 1;
							if($details_single_empl[$vj] == 'HD')
								$leave_Day += 0.5;

						}
						$Payable_days=$total_days-$leave_Day;
						echo '<td>'.$total_days.' </td>  <td>'. $leave_Day.'</td> <td>'. $leave_Day.' </td> <td>'.$Payable_days.' </td>';
						echo '<tr>';
					}
			end_table(1);
		
		div_end();
	end_form();
 
end_page(); ?>