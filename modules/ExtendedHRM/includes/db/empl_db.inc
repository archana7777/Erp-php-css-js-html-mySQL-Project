<?php
/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
*****************************************/
function add_employee($empl_id, $empl_salutation, $empl_firstname,$empl_lastname, $addr_line1, $addr_line2,
$home_phone, $mobile_phone, $email, $gender, $date_of_birth, $age, $marital_status,  $status,$empl_city,$empl_state,$country){
	 	
		$date_of_birt = date2sql($date_of_birth); 
		$sql = "INSERT INTO ".TB_PREF."kv_empl_info (empl_id, empl_salutation, empl_firstname,empl_lastname, addr_line1,addr_line2,home_phone,mobile_phone,email, gender, date_of_birth, age, marital_status, status,empl_city,empl_state,country) VALUES ("
		.db_escape($empl_id).", "
		.db_escape($empl_salutation).", "
		.db_escape($empl_firstname).", "
		.db_escape($empl_lastname).", "
		.db_escape($addr_line1).", "
		.db_escape($addr_line2).", "
		.db_escape($home_phone).", " 
		.db_escape($mobile_phone).", " 
		.db_escape($email).", " 
		.db_escape($gender).", "
		.db_escape($date_of_birt).", "
		.db_escape($age).", "
		.db_escape($marital_status).", "
		.db_escape($status).","
		.db_escape($empl_city).","
		.db_escape($empl_state).","
		.db_escape($country).")";

	db_query($sql,"The employee could not be added");
}


//--------------------------------------------------------------------------------------------
function update_employee($selected_id, $empl_salutation, $empl_firstname,$empl_lastname, $addr_line1, $addr_line2, $home_phone, $mobile_phone, $email, $gender, $date_of_birth, $age, $marital_status, $status,$empl_city,$empl_state,$country){

	$date_of_birt = date2sql($date_of_birth); 
	$sql = "UPDATE ".TB_PREF."kv_empl_info SET
		empl_id=".db_escape($selected_id).",
		empl_salutation=".db_escape($empl_salutation).",
		empl_firstname=".db_escape($empl_firstname).",
		empl_lastname=".db_escape($empl_lastname).",
		addr_line1=".db_escape($addr_line1).",
		addr_line2=".db_escape($addr_line2).",
		home_phone=".db_escape($home_phone).",
		mobile_phone=".db_escape($mobile_phone).",
		email=".db_escape($email).",
		gender=".db_escape($gender).",
		date_of_birth=".db_escape($date_of_birt).",
		age=".db_escape($age).",
		marital_status=".db_escape($marital_status).",
		status=".db_escape($status).",
		empl_city=".db_escape($empl_city).",
		empl_state=".db_escape($empl_state).",
		country=".db_escape($country)."
		WHERE empl_id=".db_escape($selected_id);

	db_query($sql,"The employee could not be updated");

}

function kv_get_employees_based_on_dept($dept_id) {

	if($dept_id == 0){
		//get_all_employees();
		$sql = "SELECT empl_id FROM ".TB_PREF."kv_empl_info";
  
		return db_query($sql, "The employee table is inaccessible");

	} else{
		$sql="SELECT empl_id FROM ".TB_PREF."kv_empl_job WHERE department=".db_escape($dept_id);
		return db_query($sql, "could not get the selected Employees");
	}
}

function kv_get_employees_count_based_on_dept($dept_id) {

	if($dept_id == 0){
		//get_all_employees();
		$sql = "SELECT COUNT(empl_id) FROM ".TB_PREF."kv_empl_info";
  
		return check_empty_result($sql);

	} else{
		$sql="SELECT COUNT(empl_id) FROM ".TB_PREF."kv_empl_job WHERE department=".db_escape($dept_id);
		return check_empty_result($sql);
	}
}

function kv_get_empl_name($id) {
	$sql = "SELECT empl_firstname FROM ".TB_PREF."kv_empl_info WHERE empl_id=".db_escape($id)."LIMIT 1";

	$result = db_query($sql, "could not get sales type");

	$row = db_fetch_row($result);
	return $row[0];
}
 

//--------------------------------------------------------------------------------------------
function get_all_employees($active=true){
  //get only active employees by default
  if ($active == true)
    $act_sql = "WHERE inactive = 0";

  $sql = "SELECT empl_id, empl_firstname FROM ".TB_PREF."kv_empl_info $act_sql";
  
  return db_query($sql, "The employee table is inaccessible");

}
//--------------------------------------------------------------------------------------------
function get_all_employees_list($active=true){
  //get only active employees by default
  if ($active == true)
    $act_sql = "WHERE inactive = 0";

  $sql = "SELECT * FROM ".TB_PREF."kv_empl_info $act_sql";
  
  return db_query($sql, "The employee table is inaccessible");

}

//--------------------------------------------------------------------------------------------
function get_employee($employee_id){
	$sql = "SELECT * FROM ".TB_PREF."kv_empl_info WHERE empl_id=".db_escape($employee_id);

	$result = db_query($sql, "could not get employee");

	return db_fetch($result);
}


//--------------------------------------------------------------------------------------------
function db_has_employees(){
	return check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_empl_info");
}

//--------------------------------------------------------------------------------------------
function db_has_employee_email($email){
	return check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_empl_info WHERE email=".db_escape($email));
}

function db_has_selected_employee($empl_id){
	return check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_empl_info WHERE empl_id=".db_escape($empl_id));
}
//--------------------------------------------------------------------------------------------
function count_employees(){
	//return check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_empl_info");
	$sql = "SELECT * FROM ".TB_PREF."kv_empl_info";

	$result = db_query($sql, "could not get employee");

	return db_num_rows($result);
}


function delete_employee($empl_id){
	$tables_array = array('kv_empl_info', 'kv_empl_job', 'kv_empl_attendancee', 'kv_empl_cv', 'kv_empl_degree', 'kv_empl_experience', 'kv_empl_loan', 'kv_empl_salary', 'kv_empl_training');
	foreach($tables_array as $tabl){
		$sql="DELETE FROM ".TB_PREF."{$tabl} WHERE `empl_id`=".db_escape($empl_id);
		db_query($sql, "could not delete the selected Employee");
	}
}
function db_has_empl_id(){
	return check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_empl_option WHERE option_name = 'next_empl_id'");
}

function kv_get_next_empl_id() {
	 $sql = "SELECT option_value FROM ".TB_PREF."kv_empl_option WHERE option_name='next_empl_id' LIMIT 1";
	 $result = db_query($sql, "could not get sales type");
	 $row = db_fetch_row($result);
	 return $row[0];
}

function delete_empl_id(){
	$sql="DELETE FROM ".TB_PREF."kv_empl_option WHERE option_name= ='next_empl_id'" ;
	db_query($sql, "could not delete the selected Employee"); 
}

function kv_add_next_empl_id_new($empl_id){ 
	//$pos1 = strpos($empl_id, '-');
	$option_value = (int)substr($empl_id, 1); 
	$option_value = $option_value+1 ; 
	$option_2 = substr($empl_id, 0,1); 
	$empl_next_id = $option_2.(string)$option_value; 
		//display_notification($empl_next_id);

	//$empl_id= $option_value; 
	$sql_insert = "INSERT INTO ".TB_PREF."kv_empl_option (option_name,option_value) VALUES ('next_empl_id',$empl_next_id)";				
	db_query($sql_insert,"The employee could not be added");
	//display_notification();
	
}
function kv_update_next_empl_id_new($empl_id){ 
	//$pos1 = strpos($empl_id, '-');
	$option_value = (int)substr($empl_id, 1); 
	$option_value = 'E'.$option_value +1;  	
	$sql_update = "UPDATE ".TB_PREF."kv_empl_option SET option_value=" . db_escape($option_value) . " 
	 WHERE option_name ='next_empl_id' ";
	db_query($sql_update ,"The employee could not be updated");

}

function kv_get_empl_desig($id) {
	$sql = "SELECT desig FROM ".TB_PREF."kv_empl_job
 WHERE empl_id=".db_escape($id)."LIMIT 1";

	$result = db_query($sql, "could not get sales type");

	$row = db_fetch_row($result);
	return $row[0];
}

function check_db_has_salary_account($msg){
	global $path_to_root;
    if (!check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_empl_option WHERE option_name='salary_account'"))  {
    	display_error($msg, true);
    	end_page();
    	exit;	
    }	
}

function check_db_has_employees($msg){
	global $path_to_root;
    if (!check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_empl_info"))  {
    	display_error($msg, true);
    	end_page();
    	exit;	
    }	
}

function get_employee_join_date($empl_id){
	$sql = "SELECT joining FROM ".TB_PREF."kv_empl_job WHERE empl_id=".db_escape($empl_id)."LIMIT 1";

	$result = db_query($sql, "could not get sales type");

	$row = db_fetch_row($result);
	return $row[0];
}

function check_db_has_Departments($msg){
	global $path_to_root;
    if (!check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_departments"))  {
    	display_error($msg, true);
    	end_page();
    	exit;	
    }	
}

function check_db_has_Loan_types($msg){
	global $path_to_root;
    if (!check_empty_result("SELECT COUNT(*) FROM ".TB_PREF."kv_loan_types"))  {
    	display_error($msg, true);
    	end_page();
    	exit;	
    }	
}
?>