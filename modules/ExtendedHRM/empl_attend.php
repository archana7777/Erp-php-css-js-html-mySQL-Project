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
include($path_to_root . "/includes/ui.inc");

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
include($path_to_root . "/modules/ExtendedHRM/includes/Payroll.inc" );
page(_("Employee Attendance"));
$new_item = get_post('selected_id')=='' || get_post('cancel') ;

if (isset($_GET['selected_id'])){
	$_POST['selected_id'] = $_GET['selected_id'];
}
$selected_id = get_post('selected_id');
 if (list_updated('selected_id')) {
	$_POST['empl_id'] = $selected_id = get_post('selected_id');
    $Ajax->activate('details');
}

if (isset($_POST['addupdate'])) {
		$input_error = 0;
		$employees = array();
		foreach($_POST as $empls =>$val) {			
			if (substr($empls,0,5) == 'Empl_')
				$employees[] = substr($empls, 5);
		}
		$empl_count=get_dep_employees_count($_POST['selected_id']);
		//display_notification($empl_count);
		$employees = array_values($employees);
		$attend_count=count($employees);
		//display_notification(count($employees));

		if ($empl_count !=$attend_count) {
			display_error(_("must enter missing field"));
			$input_error = 1;
			return false;
		} 

		if($input_error==0){
		$attendance_date = strtotime($_POST['attendance_date']);
		$month = date("m", $attendance_date);
		$day = date("d", $attendance_date);

		$year = get_fiscal_year_id_from_date($_POST['attendance_date']);

		foreach ($employees as $empl_id) {
			
			if(db_has_day_attendancee($empl_id, $month, $year)){
				update_employee_attendance($_POST['Empl_'.$empl_id], $empl_id, $month, $year,$day);
			}else{
				add_employee_attendance($_POST['Empl_'.$empl_id], $empl_id, $month, $year, $day,
					$_POST['selected_id']);
			}
		}
		display_notification("Attendance Register Saved Successfully");
	}
	$new_role = true;
	//clear_data();
	$Ajax->activate('_page_body');	
}

//function clear_data(){	unset($_POST);	}

start_form(true);

if (db_has_employees()) {
	if (isset($_POST['selected_id']) && $_POST['selected_id'] >0) {
		$_POST['selected_id'] = input_num('selected_id');
	}
	start_table(TABLESTYLE2);
		start_row();   
			date_cells(_("Date") . ":", 'attendance_date', null, null, 0,0,0, null, true);
			department_list_cells(_("Select a Department: "), 'selected_id', null,	_('No Department'), true, check_value('show_inactive'));
			$new_item = get_post('selected_id')=='';
		end_row();	
	end_table();

	if (get_post('_show_inactive_update')) {
		$Ajax->activate('selected_id');
		$attendance_date = get_post('attendance_date');
		set_focus('selected_id');
	}
	if(list_updated('attendance_date') ) {
		$attendance_date = get_post('attendance_date');   
		$Ajax->activate('totals_tbl');
}
	
div_start('totals_tbl');
	$selected_id = get_post('selected_id');
	$attendance_date = get_post('attendance_date');   
		$Ajax->activate('_page_body');
	if (!$selected_id) 
		$selected_id = 0 ; 	
	$day_absentees = array();
	
	//echo " <center> Select the Absentees only ...</center>";
	br();
	$disabled= '';
	if($attendance_date > Today()){
		display_warning("You can't Enter Yet to born day Attendance!");
		$disabled = 'disabled';
	}
	start_table(TABLESTYLE);
	
	//table_section_title(_("Employees List"));
	echo '<tr> <th class="tableheader"> Empl ID</th> <th class="tableheader"> Employee Name </th> <th class="tableheader"> Present</th> <th class="tableheader"> Absent </th> <th class="tableheader"> On Duty</th> <th class="tableheader"> Half Day</th> </tr>' ;
	if($selected_id == 0 ) 
		label_row(" Select a Department to note attendance ", '', "colspan=4", " ", 4 ); 
	else {
		$selected_empl = kv_get_employees_based_on_dept($selected_id);	
		$day_absentees = get_employees_attendances($attendance_date,$_POST['selected_id']);
		//display_notification(json_encode($day_absentees));
		while ($row = db_fetch_assoc($selected_empl)) {
			echo '<tr> <td>'.$row['empl_id'].'</td> <td>'.kv_get_empl_name($row['empl_id']).'</td><td>';

			if(array_key_exists($row['empl_id'], $day_absentees)  && $day_absentees[$row['empl_id']] == "P")
			echo kv_radio(" ", 'Empl_'.$row['empl_id'], "P", "selected", false, $disabled);
			else
				echo kv_radio(" ", 'Empl_'.$row['empl_id'], "P", null, false , $disabled);
				
			echo '</td><td>';

			if(array_key_exists($row['empl_id'], $day_absentees)  && $day_absentees[$row['empl_id']] == "A")
			echo kv_radio(" ", 'Empl_'.$row['empl_id'], "A", "selected", false, $disabled);
			else
				echo kv_radio(" ", 'Empl_'.$row['empl_id'], "A", null, false , $disabled);
				
			echo '</td><td>';

			if(array_key_exists($row['empl_id'], $day_absentees)  && $day_absentees[$row['empl_id']] == "OD")
			echo kv_radio(" ", 'Empl_'.$row['empl_id'], "OD", "selected", false, $disabled);
			else
				echo kv_radio(" ", 'Empl_'.$row['empl_id'], "OD", null, false , $disabled);
				
			echo '</td><td>';

			if(array_key_exists($row['empl_id'], $day_absentees)  && $day_absentees[$row['empl_id']] == "HD")
				echo kv_radio(" ", 'Empl_'.$row['empl_id'], "HD", "selected", false, $disabled);
			else
				echo kv_radio(" ", 'Empl_'.$row['empl_id'], "HD", null, false , $disabled);				
			echo '</td></tr>';				
		}		
	}
	end_table();
	br();
	if($attendance_date > Today()){} else{
		submit_center('addupdate', _("Submit Attendance"), true, '', 'default');
	}
div_end();




}else{
	check_db_has_employees(_("There is no employee in this system. Kindly Open <a href='".$path_to_root."/modules/ExtendedHRM/manage/employees.php'>Add And Manage Employees</a> to update it"));
}
 
end_form();
end_page();
 
?>