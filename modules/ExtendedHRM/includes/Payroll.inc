<?php
/****************************************
/*  Author  : Kvvaradha
/*  Module  : Extended HRM
/*  E-mail  : admin@kvcodes.com
/*  Version : 1.0
/*  Http    : www.kvcodes.com
*****************************************/

// Necessary File hook

include_once($path_to_root . "/modules/ExtendedHRM/includes/db/empl_db.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/db/empl_bonus_db.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/db/empl_sal_db.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/employee.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/common.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/empl_degree.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/empl_ui_array.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/empl_experience.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/empl_training.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/empl_leave.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/db/empl_sal_structure_db.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/ui/kv_departments.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/db/empl_cv_db.inc");
include_once($path_to_root . "/modules/ExtendedHRM/includes/db/taxes.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");

//---------------------------------------------------------------------------------------

// Add, Edit, Delete, Get And Get Join 

//---------------------------------------------------------------------------------------
function Insert($table_name, $data){
    $sql0 = "INSERT INTO ".TB_PREF.$table_name."(";
    $sql1 = " VALUES (";
    foreach($data as $key=>$value){
        $sql0 .= "`".$key."`,";
		if(is_array($value)) { 
			if($value[1] == 'date')				
				$sql1 .=  db_escape(date2sql($value[0])).",";
			if($value[1] == 'float')
				$sql1 .= $value.",";
		}else 
			$sql1 .= db_escape($value).",";
    }
    $sql0 = substr($sql0, 0, -1).")";
    $sql1 = substr($sql1, 0, -1).")";
	//display_notification($sql0.$sql1);
    db_query($sql0.$sql1, "Could not insert data to table {$table_name}");
	return  db_insert_id();
}
function Update($table_name, $primary_key ,$data ){
    $sql0 = "UPDATE ".TB_PREF.$table_name." SET ";
    foreach($data as $key=>$value){
		if(is_array($value)) { 
			if($value[1] == 'date')				
				$sql0 .= "`".$key."` = ". db_escape(date2sql($value[0])).",";
			if($value[1] == 'float')
				$sql0 .= "`".$key."` = ". $value.",";
		}else 
			$sql0 .= "`".$key."` = ".db_escape($value).",";
    }
    $sql0 = substr($sql0, 0, -1);
    $sql0 .= " where ".$primary_key[0]." = '".$primary_key[1]."'";
	return  db_query($sql0, "Could not update data on table {$table_name}");	 
}
function Delete($table_name, $conditions){
    $sql0 = "DELETE FROM ".TB_PREF.$table_name." WHERE 1=1";
    foreach ($conditions as $key=>$value) {
        $sql0 .= " AND ".$key."=".$value;
    }
    $result = db_query($sql0, "Could not delete data from {$table_name}");
    return $result;
}
function GetDataJoin($main_table, $joins, $columns=array(), $conditions=null){
    $sql0 = "SELECT ";
    if(count($columns)>0){
        foreach ($columns as $value) {
            $sql0 .= $value.",";
        }
        $sql0 = substr($sql0, 0, -1);
    }else{
        $sql0 .= " *";
    }
    $sql0 .= " FROM ".TB_PREF."{$main_table} ";
    foreach ($joins as $value) {
        if(isset($value['join'])){
            $sql0 .= " {$value['join']} JOIN ".TB_PREF.$value['table_name'].' ON '.$value['conditions'];
        }else{
            $sql0 .= " INNER JOIN ".TB_PREF.$value['table_name'].' ON '.$value['conditions'];
        }
    }
    $sql0 .= " WHERE 1=1 ";
    if($conditions != null) {
        foreach($conditions as $key=>$value){
            $sql0 .= " AND {$key} = ${value}";
        }
    }
    //echo $sql0;
    $result = db_query($sql0, "Could not get data!");
    $data = array();
    while($row = db_fetch_row($result)) {
        $data[] = $row;
    }
    return $data;
}
function GetAll($table_name, $conditions = null){
    $sql0 = "SELECT * FROM ".TB_PREF.$table_name." WHERE 1=1";
    if($conditions != null) {
		foreach($conditions as $key=>$value){
			$sql0 .= " AND {$key} = ${value}";
		}
    }
    $result = db_query($sql0, "Could not get data from {$table_name}");
    $data = array();
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function GetRow($table_name, $conditions = null){
    $sql0 = "SELECT * FROM ".TB_PREF.$table_name." WHERE 1=1";
    if($conditions != null) {
		foreach($conditions as $key=>$value){
			$sql0 .= " AND {$key} = '${value}'";
		}
    }
	$sql0 .= ' LIMIT 1'; 
    $result = db_query($sql0, "Could not get data from {$table_name}");
	$data = db_fetch($result);
    
    return $data;
}

function GetDataFilter($table_name, $columns= array(), $conditions=null){
    $sql0 = "SELECT ";
    if(count($columns)>0){
        foreach ($columns as $value) {
            $sql0 .= $value.",";
        }
    }else{
        $sql0 .= " * ";
    }
    // remove the last character ','
    $sql0 = substr($sql0, 0, -1);
    $sql0 .= " FROM ".TB_PREF.$table_name." WHERE 1=1";
    foreach($conditions as $key=>$value){
        $sql0 .= " AND {$key} = ${value}";
    }
    $result = db_query($sql0, "Could not get data from {$table_name}");
    $data = array();
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function GetSingleValue($tablename, $column_single, $conditions=null){
	$sql0 = "SELECT ".$column_single." FROM ".TB_PREF.$tablename." WHERE 1=1";
    foreach($conditions as $key=>$value){
        $sql0 .= " AND {$key} = '${value}'";
    }
    $result = db_query($sql0, "could not get sales type");

    $row = db_fetch_row($result);
    return $row[0];
}

//----------------------------------------------------------------------------------------
function kv_hrm_get_empl_list(){
    return "SELECT job.empl_id, "."empl_info.empl_firstname, " ."empl_info.email, "."empl_info.mobile_phone, "."dpt.description, "."job.desig,"."empl_info.addr_line1, ". " job.joining "."FROM ".TB_PREF."kv_empl_info empl_info JOIN ".TB_PREF."kv_empl_job job ON  job.empl_id = empl_info.empl_id JOIN ".TB_PREF."kv_departments dpt ON job.department= dpt.id WHERE 1=1";
}


//----------------------------------------------------------------------------------------
function kv_get_employees_list_based_on_dept($dept_id, $rep=false) {

    if($dept_id == 0){
        //get_all_employees();
        $sql = "SELECT empl_id, empl_firstname FROM ".TB_PREF."kv_empl_info";
  
        return db_query($sql, "The employee table is inaccessible");

    } else{
	if($rep == true){
		$sql="SELECT info.*, job.* FROM ".TB_PREF."kv_empl_job job JOIN ".TB_PREF."kv_empl_info info ON info.empl_id = job.empl_id WHERE job.department=".db_escape($dept_id);
	}else{	
		$sql="SELECT info.empl_firstname, job.empl_id FROM ".TB_PREF."kv_empl_job job JOIN ".TB_PREF."kv_empl_info info ON info.empl_id = job.empl_id WHERE job.department=".db_escape($dept_id);
        }
        return db_query($sql, "could not get the selected Employees");
    }
}

//----------------------------------------------------------------------------------------
function kv_get_employees_list_based_on_dept_rep($dept_id) {

    if($dept_id == 0){
        //get_all_employees();
        $sql = "SELECT empl_id, empl_firstname FROM ".TB_PREF."kv_empl_info";
  
        return db_query($sql, "The employee table is inaccessible");

    } else{	
	$sql="SELECT info.*, job.* FROM ".TB_PREF."kv_empl_job job JOIN ".TB_PREF."kv_empl_info info ON info.empl_id = job.empl_id WHERE job.department=".db_escape($dept_id);

        return db_query($sql, "could not get the selected Employees");
    }
}

//---------------------------------------------------------------------------------------
function kv_get_months_in_fiscal_year(){   
    $Year_id = get_post('year');
	if($Year_id){
		$fiscal_year_get = get_fiscalyear($Year_id);
	} else {
        $Year_id = get_post('PARAM_0');
        if($Year_id)
            $fiscal_year_get = get_fiscalyear($Year_id);
        else
		    $fiscal_year_get = get_current_fiscalyear();
	}
    $array_months = array();
    $start    = (new DateTime($fiscal_year_get['begin']))->modify('first day of this month');
    $end      = (new DateTime($fiscal_year_get['end']))->modify('first day of next month');
    $interval = DateInterval::createFromDateString('1 month');
    $period   = new DatePeriod($start, $interval, $end);

    foreach ($period as $dt) {
        $array_months[$dt->format("m")] = date("m - F", strtotime($dt->format("Y")."-".$dt->format("m")."-01"));
    }
    return $array_months;
}


//---------------------------------------------------------------------------------------
function kv_get_months_with_years_in_fiscal_year($Year_id){   
   
    $fiscal_year_get = get_fiscalyear($Year_id);
 
    $array_months = array();
    $start    = (new DateTime($fiscal_year_get['begin']))->modify('first day of this month');
    $end      = (new DateTime($fiscal_year_get['end']))->modify('first day of next month');
    $interval = DateInterval::createFromDateString('1 month');
    $period   = new DatePeriod($start, $interval, $end);

    foreach ($period as $dt) {
        $array_months[$dt->format("m")] = date("Y-m-d", strtotime($dt->format("Y")."-".$dt->format("m")."-01"));
    }
    return $array_months;
}

//---------------------------------------------------------------------------------------
function  kv_current_fiscal_months_list_row($label, $name, $selected_id=null, $submit_on_change=false, $disabled=false) {  
    
    echo "<tr><td class='label'>$label</td>";
    kv_current_fiscal_months_list_cell(null, $name, $selected_id, $submit_on_change, $disabled);
    echo "</tr>\n";
}
//---------------------------------------------------------------------------------------
function  kv_current_fiscal_months_list_cell($label=null, $name, $selected_id=null, $submit_on_change=false, $disabled=false) {  
    if ($label != null)
        echo "<td>$label</td>\n";
    echo "<td>". kv_current_fiscal_months($name, $selected_id, $submit_on_change, $disabled)."</td>\n";
}
function kv_current_fiscal_months($name, $selected_id=null, $submit_on_change=false, $disabled=false) {
   
   $Year_id = get_post('year');
   $month_id = (int)get_post($name); 
  
   if(!empty($month_id) && $month_id>0 ){} else { $selected_id = date("m");}
    $hrm_months_list = kv_get_months_in_fiscal_year();
    $options = array(
        'select_submit'=> $submit_on_change,
        'disabled' => $disabled
    );
    return  array_selector($name, $selected_id, $hrm_months_list, $options);
}

function kv_month_name_by_id($month){
    $months =  array(
                '01' => 'January',
                '02' => 'February',
                '03' => 'March',
                '04' => 'April',
                '05' => 'May',
                '06' => 'June',
                '07' => 'July',
                '08' => 'August',
                '09' => 'September',
                '10' => 'October',
                '11' => 'November',
                '12' => 'December'
                );
    return $months[$month];
}

//---------------------------------------------------------------------------------------------------

function kv_fiscalyears_list($name, $selected_id=null, $submit_on_change=false){

	$sql = "SELECT * FROM ".TB_PREF."fiscal_year";

// default to the company current fiscal year

	return combo_input($name, $selected_id, $sql, 'id', '',
	array(
		'order' => 'begin',
		'default' => get_company_pref('f_year'),
		'format' => '_format_fiscalyears',
		'select_submit'=> $submit_on_change,
		'async' => false
	) );
}


function kv_fiscalyears_list_cells($label, $name, $selected_id=null, $submit_on_change=false){
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo kv_fiscalyears_list($name, $selected_id, $submit_on_change);
	echo "</td>\n";
}

function kv_fiscalyears_list_row($label, $name, $selected_id=null, $submit_on_change=false){
	echo "<tr><td class='label'>$label</td>";
	kv_fiscalyears_list_cells(null, $name, $selected_id, $submit_on_change);
	echo "</tr>\n";
}


function kv_truncate_Complete_HRM(){
    display_notification("submitted catch");
    $featured_hrm_tables = array('kv_departments', 'kv_empl_attendancee', 'kv_empl_cv', 'kv_empl_degree', 'kv_empl_experience', 'kv_empl_info', 'kv_empl_job', 'kv_empl_loan', 'kv_empl_salary', 'kv_empl_training', 'kv_loan_types', 'kv_empl_option'); 

    foreach ($featured_hrm_tables as $tbl) {
        $sql_first =" TRUNCATE TABLE ".TB_PREF.$tbl ;
        $result = db_query($sql_first, "could not clean {$tbl} it");
    }

    $sql = "DELETE FROM ".TB_PREF."gl_trans WHERE type=99"; 
    db_query($sql, "Can't delete the GL Transactions");
            
}

?>