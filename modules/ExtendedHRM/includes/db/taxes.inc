<?php
/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
*****************************************/

//--------------------------------------------------------------------------------------
function kv_add_tax($year, $min_sal, $max_sal, $percentage, $offset){
	$sql = "INSERT INTO ".TB_PREF."kv_hrm_tax (year, min_sal, max_sal, percentage, offset)  VALUES (".db_escape($year).", ".db_escape($min_sal).", ".db_escape($max_sal).", ".db_escape($percentage).", ".db_escape($offset)." )";
	return db_query($sql, "could not add bonus type");		
}

function kv_update_tax($id, $year, $min_sal, $max_sal, $percentage, $offset){

	$sql = "UPDATE ".TB_PREF."kv_hrm_tax SET year = ".db_escape($year).", min_sal =".db_escape($min_sal).", max_sal=".db_escape($max_sal).", percentage=".db_escape($percentage).", offset=".db_escape($offset)." WHERE id = ".db_escape($id);
	
	db_query($sql, "could not update bonus type");			
}

function kv_get_taxes($year){
	$sql = "SELECT * FROM ".TB_PREF."kv_hrm_tax WHERE year=".db_escape($year);
	
	$result = db_query($sql, "could not get Tax type");
	
	return $result;
}

function kv_get_tax($id){
	$sql = "SELECT * FROM ".TB_PREF."kv_hrm_tax WHERE id=".db_escape($id);
	
	$result = db_query($sql, "could not get Tax type");
	
	return db_fetch($result);
}

function kv_delete_tax($id){
	$sql="DELETE FROM ".TB_PREF."kv_hrm_tax WHERE id=".db_escape($id);
	db_query($sql,"The Tax type record could not be deleted");	
}

function kv_get_tax_value($gross_pay, $year){
	$sql = "SELECT * FROM ".TB_PREF."kv_hrm_tax WHERE min_sal <= ".db_escape($gross_pay)." AND max_sal >= ".db_escape($gross_pay)." AND year=".db_escape($year)." LIMIT 1" ;

	$result = db_query($sql, "could not get Tax type");
	
	return db_fetch($result);
}

function kv_get_tax_for_an_employee($empl_id, $year){
	$gross_pay_month =  get_empl_gross_pay($empl_id);
	$tax_row = kv_get_tax_value($gross_pay_month, $year);

	$tax_val = ($gross_pay_month - $tax_row['min_sal'])*($tax_row['percentage']/100)+$tax_row['offset'];
	return round($tax_val);
}


//---------------------------------------------------------------------------
function kv_check_payroll_table_exist(){
	$table_exist = db_query("SHOW TABLES LIKE '".TB_PREF."kv_empl_job'");
    $tbl_ext = db_num_rows($table_exist) > 0;
    if($tbl_ext){
    	return true; 
    }else{
    	$table_exist = db_query("SHOW TABLES LIKE '".TB_PREF."kv_empl_salary'");
	    $tbl_ext = db_num_rows($table_exist) > 0;
	    if($tbl_ext){
	    	return true; 
	    }else
	    	return false;
    }
    return false;
}
function get_allowances($value=null, $inactive=0){

	$sql = "SELECT * FROM ".TB_PREF."kv_allowances WHERE inactive={$inactive}";

	if($value != null)
		$sql .= " AND type=".db_escape($value); 

	$sql .= " ORDER BY description";
	return db_query($sql,"could not get kv_departments");
}

function get_all_allowances(){
	$sql = "SELECT * FROM ".TB_PREF."kv_allowances ORDER BY description";
	return db_query($sql,"could not get kv_departments");
}

function kv_get_sal_details_file($month, $year){
		$Allowance = get_all_allowances();
		$sql0 = "SELECT SUM(`gross`) AS `gross_sum`, SUM(`net_pay`) AS `net_pay_sum`, SUM(`loan`) AS `loan_sum`, SUM(`lop_amount`) AS `lop_amount_sum`, SUM(`ot_other_allowance`) AS `ot_other_allowance_sum`, SUM(`misc`) AS `misc_sum`, ";
		while ($single = db_fetch($Allowance)) {	
			$sql0 .= "SUM(`{$single['id']}`) AS `{$single['id']}_sum`," ;
		}
		$sql = substr($sql0 , 0, -1);
		$sql .=" FROM ".TB_PREF."kv_empl_salary	WHERE month=".db_escape($month)." AND year=".db_escape($year);

		return db_query($sql,"No transactions were returned");
	}

function kv_get_sal_details_Annual($year){
		$Allowance = get_all_allowances();
		$sql0 = "SELECT SUM(`gross`) AS `gross_sum`, SUM(`net_pay`) AS `net_pay_sum`, SUM(`loan`) AS `loan_sum`, SUM(`lop_amount`) AS `lop_amount_sum`, SUM(`ot_other_allowance`) AS `ot_other_allowance_sum`, SUM(`misc`) AS `misc_sum`, ";
		while ($single = db_fetch($Allowance)) {	
			$sql0 .= "SUM(`{$single['id']}`) AS `{$single['id']}_sum`," ;
		}
		$sql = substr($sql0 , 0, -1);
		$sql .=" FROM ".TB_PREF."kv_empl_salary	WHERE year=".db_escape($year);

		return db_query($sql,"No transactions were returned");
	}

function get_all_allowances_count($value=null){
	$sql = "SELECT COUNT(*) FROM ".TB_PREF."kv_allowances ";

	if($value != null)
		$sql .= "WHERE type=".db_escape($value); 

	$sql .= " ORDER BY description";
	$result = db_query($sql,"could not get kv_departments");
	$row = db_fetch($result);
	return $row[0];
}
function get_allowances_count($value=null, $inactive=0){
	$sql = "SELECT COUNT(*) FROM ".TB_PREF."kv_allowances WHERE inactive={$inactive} ";

	if($value != null)
		$sql .= "AND type=".db_escape($value); 

	$sql .= " ORDER BY description";
	$result =  db_query($sql,"could not get kv_departments");
	$row = db_fetch($result);
	return $row[0];
}

function add_allowance($description, $type, $value, $basic, $percentage, $Tax, $status=0){
	if($value == 'Amount')
		$percentage = 0;
		
	if($basic == 1){
		$basic_id = kv_get_basic();
		if($basic_id > 0){
			$sql = "UPDATE ".TB_PREF."kv_allowances SET basic=0 WHERE id = ".db_escape($basic_id);
   			db_query($sql,"The sales department could not be updated");
		}			
	}	

	if($Tax == 1){
		$Tax_id = kv_get_Taxable_field();
		if($Tax_id > 0){
			$sql = "UPDATE ".TB_PREF."kv_allowances SET Tax=0 WHERE id = ".db_escape($Tax_id);
   			db_query($sql,"The sales department could not be updated");
		}			
	}	

	$sql = "INSERT INTO ".TB_PREF."kv_allowances (description, type, value, basic, percentage, Tax, inactive) VALUES (".db_escape($description) . ", ".db_escape($type)." , ".db_escape($value).", ".db_escape($basic).", ".db_escape($percentage).", ".db_escape($Tax).", ".db_escape($status).")";
   	db_query($sql,"The sales department could not be added");
}

function update_allowance($selected_id, $description, $type, $value, $basic, $percentage, $Tax, $status=0){
	if($value== 'Amount')
		$percentage = 0;

	if($basic == 1){
		$basic_id = kv_get_basic();
		if($basic_id > 0){
			$sql = "UPDATE ".TB_PREF."kv_allowances SET basic=0 WHERE id = ".db_escape($basic_id);
   			db_query($sql,"The sales department could not be updated");
		}			
	}	

	if($Tax == 1){
		$Tax_id = kv_get_Taxable_field();
		if($Tax_id > 0){
			$sql = "UPDATE ".TB_PREF."kv_allowances SET Tax=0 WHERE id = ".db_escape($Tax_id);
   			db_query($sql,"The sales department could not be updated");
		}			
	}	

	$sql = "UPDATE ".TB_PREF."kv_allowances SET description=".db_escape($description)." , type=".db_escape($type)." , value=".db_escape($value).", basic=".db_escape($basic).", percentage=".db_escape($percentage).", Tax=".db_escape($Tax).", inactive=".db_escape($status)." WHERE id = ".db_escape($selected_id);
   	db_query($sql,"The sales department could not be updated");
}

function db_has_basic_pay(){
	$sql = "SELECT id FROM ".TB_PREF."kv_allowances WHERE basic = 1";
	$result = db_query($sql, "could not get department");
	$exists = (db_num_rows($result))?TRUE:FALSE;
	if($exists) 
		return true;
	else
		return false;
}
function kv_get_basic(){
	$sql = "SELECT id FROM ".TB_PREF."kv_allowances WHERE basic = 1";
	$result = db_query($sql, "could not get department");
	$row = db_fetch($result);
	return $row[0];
}

function db_has_tax_pay(){
	$sql = "SELECT id FROM ".TB_PREF."kv_allowances WHERE Tax = 1";
	$result = db_query($sql, "could not get department");
	$exists = (db_num_rows($result))?TRUE:FALSE;
	if($exists) 
		return true;
	else
		return false;
}
function kv_get_Tax_allowance(){
	$sql = "SELECT id FROM ".TB_PREF."kv_allowances WHERE Tax = 1";
	$result = db_query($sql, "could not get department");
	$row = db_fetch($result);
	return $row[0];
}

function kv_get_Taxable_field(){
	$sql = "SELECT id FROM ".TB_PREF."kv_allowances WHERE Tax = 1";
	$result = db_query($sql, "could not get department");
	$row = db_fetch($result);
	return $row[0];
}

function delete_allowance($selected_id){
	$sql="DELETE FROM ".TB_PREF."kv_allowances WHERE id=".db_escape($selected_id);	
	$result = db_query("SHOW COLUMNS FROM `".TB_PREF."kv_empl_salary` LIKE '{$selected_id}'", "Can't Query this Table");
	$exists = (db_num_rows($result))?TRUE:FALSE;
	if($exists) {
		$sql1="ALTER TABLE ".TB_PREF."kv_empl_salary DROP COLUMN `{$selected_id}`";
		db_query($sql1,"could not delete sales department");
	}
	$result = db_query("SHOW COLUMNS FROM `".TB_PREF."kv_empl_job` LIKE '{$selected_id}'", "Can't Query this Table");
	$exists = (db_num_rows($result))?TRUE:FALSE;
	if($exists) {
		$sql1="ALTER TABLE ".TB_PREF."kv_empl_job DROP COLUMN `{$selected_id}`";
		db_query($sql1,"could not delete sales department");
	}
	db_query($sql,"could not delete sales department");
}


function get_allowance($selected_id){
	$sql = "SELECT * FROM ".TB_PREF."kv_allowances WHERE id=".db_escape($selected_id);

	$result = db_query($sql,"could not get department");
	return db_fetch($result);
}

function get_allowance_name($id){
	$sql = "SELECT description FROM ".TB_PREF."kv_allowances WHERE id = ".db_escape($id);
	$result = db_query($sql, "could not get department");
	$row = db_fetch($result);
	return $row[0];
}

//--------------------------------------------------------------------------------
/*   Earnings Deductions Drop-down   */ 

function earning_deductions_list($name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false, $disabled= false){
	$items = array();
	$items['Earnings'] = strlen($name_no) ? $name_no : _("Earnings");
	$items['Deductions'] = strlen($name_yes) ? $name_yes : _("Deductions");

	return array_selector($name, $selected_id, $items, 
		array( 
			'select_submit'=> $submit_on_change,
			'async' => false,
			'disabled' => $disabled ) ); // FIX?
}

function earning_deductions_list_cells($label, $name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false, $disabled=false){
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo earning_deductions_list($name, $selected_id, $name_yes, $name_no, $submit_on_change, $disabled);
	echo "</td>\n";
}

function earning_deductions_list_row($label, $name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false, $disabled=false){
	echo "<tr><td class='label'>$label</td>";
	earning_deductions_list_cells(null, $name, $selected_id, $name_yes, $name_no, $submit_on_change, $disabled);
	echo "</tr>\n";
}

//--------------------------------------------------------------------------------
/*   Amount Percentage Drop-down   */ 

function percentage_amount_list($name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false, $disabled=false){
	$items = array();
	$items['Amount'] = strlen($name_no) ? $name_no : _("Amount");
	$items['Percentage'] = strlen($name_yes) ? $name_yes : _("Percentage");

	return array_selector($name, $selected_id, $items, 
		array( 
			'select_submit'=> $submit_on_change,
			'async' => false,
			'disabled' => $disabled ) ); // FIX?
}

function percentage_amount_list_cells($label, $name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false, $disabled=false){
	if ($label != null)
		echo "<td>$label</td>\n";
	echo "<td>";
	echo percentage_amount_list($name, $selected_id, $name_yes, $name_no, $submit_on_change, $disabled);
	echo "</td>\n";
}

function percentage_amount_list_row($label, $name, $selected_id=null, $name_yes="", $name_no="", $submit_on_change=false, $disabled=false){
	echo "<tr><td class='label'>$label</td>";
	percentage_amount_list_cells(null, $name, $selected_id, $name_yes, $name_no, $submit_on_change, $disabled);
	echo "</tr>\n";
}


/*
$allowances_count = array(
  1 => _('One'),
  2 => _('Two'),
  3 => _('Three'),
  4 => _('Four'),
  5 => _('Five'),
  6 => _('Six'),
  7 => _('Seven'),
  8 => _('Eight'),
  9 => _('Nine'),
  10 => _('Ten'),
  0 => _('Custom')
);


//--------------------------------------------------------------------------------------
function allowances_count($label, $name, $selected_id=null, $submit_on_change=false) {
	global $allowances_count;
		
	echo "<td>";
	$options = array(
	    'select_submit'=> $submit_on_change
	);
	echo array_selector($name, $selected_id, $allowances_count, $options );
	echo "</td></tr>\n";
}*/


