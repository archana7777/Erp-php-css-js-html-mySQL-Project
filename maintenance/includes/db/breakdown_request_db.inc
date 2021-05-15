
<?php

/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
//-----------------------------------------------------------------------------

function add_breakdown_request($ref,$type,$machine_id,$description, $department_id, $operator_id,$req_date,$mc_problem_type)
{
	$sql="INSERT INTO ".TB_PREF."mm_brkd_req(ref,type,machine_id,description,department_id,operator_id,req_date,mc_problem_type) VALUES (".db_escape($ref).",".db_escape($type).",".db_escape($machine_id).",".db_escape($description).",".db_escape($department_id).",".db_escape($operator_id).",".db_escape($req_date).",".db_escape($mc_problem_type).")";
	// display_error($sql);die;
	db_query($sql,"something went wrong");
	
} 
function update_breakdown_request($selected_id,$ref,$machine_id,$description,$department_id, $operator_id,$req_date,$mc_problem_type)
{
	
	$sql="UPDATE  ".TB_PREF."mm_brkd_req SET machine_id=".db_escape($machine_id).",description=".db_escape($description)." , department_id=".db_escape($department_id)." , operator_id=".db_escape($operator_id).",req_date=".db_escape($req_date).",mc_problem_type=".db_escape($mc_problem_type)." WHERE id=".db_escape($selected_id)."";
	db_query($sql,"Could Not Update the request!");
	
}
function get_breakdown_Request_edit($selected_id)
{
	$sql="SELECT * FROM ".TB_PREF."mm_brkd_req WHERE id=".db_escape($selected_id)."";
	$res=db_query($sql);
	return $result=db_fetch($res);
}
function get_breakdown_information($selected_id)
{
	$sql="SELECT mc.mac_code,
		me.mac_eqp,
		info.empl_firstname,mc.warranty_type,mc.mac_model_no,mc.warranty_exp_date,sp.supp_name,mc.supplier_id,req.description as ope_problem FROM ".TB_PREF."mm_brkd_req as req  LEFT JOIN ".TB_PREF."kv_empl_info as info ON info.empl_id=req.operator_id LEFT JOIN ".TB_PREF."machine as mc ON mc.id=req.machine_id LEFT JOIN ".TB_PREF."machine_equipment as me ON mc.mac_eqp=me.id LEFT JOIN suppliers as sp ON mc.supplier_id=sp.supplier_id WHERE req.id=".db_escape($selected_id)."";
		$res=db_query($sql);
	return $result=db_fetch($res);
}
function get_breakdown_requests_inquiry($from,$to,$machine_code,$prob_status,$problem_type)
{
	$date_after = date2sql($from);
	$date_before = date2sql($to);

	 $sql="SELECT 
		req.id,
		req.ref,
		mc.mac_code,
		me.mac_eqp,
		info.empl_firstname,
		req.req_date,
		req.prob_desc,
		CASE 
			WHEN req.mc_problem_type ='1' THEN 'Electrical'
			ELSE 'Mechanical'
		END as pbm_type,
		req.verify_status,
		mc.id as machine_id,
		req.process_status	
		
	 FROM ".TB_PREF."mm_brkd_req as req  LEFT JOIN ".TB_PREF."kv_empl_info as info ON info.empl_id=req.operator_id LEFT JOIN ".TB_PREF."machine as mc ON mc.id=req.machine_id LEFT JOIN ".TB_PREF."machine_equipment as me ON mc.mac_eqp=me.id WHERE req.req_date >= '$date_after' AND req.req_date <= '$date_before'";
	 
	 if($machine_code){
	 $sql .="AND mc.mac_code LIKE ".db_escape($machine_code)." OR me.mac_eqp LIKE ".db_escape($machine_code)."";
	 }
	 if($prob_status!=0)
	 {
		$sql .="AND req.process_status =".db_escape($prob_status)." ";
	 }if($problem_type!=0)
	 {
		$sql .="AND req.mc_problem_type =".db_escape($problem_type)." ";
	 }
	 $sql .= " ORDER BY req.id ASC";
	// display_error($sql);
	 return $sql;
} 

function update_verification_details($req_id,$prob_desc,$item_req,$mt_info,$supplier_id,$user_empl_id,$mc_problem_type)
{
	$sql="UPDATE  ".TB_PREF."mm_brkd_req SET prob_desc=".db_escape($prob_desc).",item_req=".db_escape($item_req)." , mt_info=".db_escape($mt_info)." , supplier_id=".db_escape($supplier_id).",verify_status='1',user_empl_id=".db_escape($user_empl_id).",mc_problem_type=".db_escape($mc_problem_type)." WHERE id=".db_escape($req_id)."";
	//display_error($sql);die;
	db_query($sql,"Could Not Update the verification De!");

} 
function add_breakdown_process_details($req_id,$prob_status,$perminent_solution,$solved_by,$attend_by,$remarks,$process_date)
{
	//display_error($prob_status);
	
	$sql="INSERT INTO ".TB_PREF."breakdown_process (req_id,prob_status,perminent_solution,solved_by,attend_by,remarks,process_date)
	VALUES(".db_escape($req_id).",".db_escape($prob_status).",".db_escape($perminent_solution).",".db_escape($solved_by).",".db_escape($attend_by).",
	".db_escape($remarks).",".db_escape($process_date).")";
	db_query($sql,"something went wrong!");
	
	if($prob_status == '2'){
	$req_sql="UPDATE ".TB_PREF."mm_brkd_req SET process_status='1' WHERE id=".db_escape($req_id)."";
		db_query($req_sql,"something went wrong!");
	}else if($prob_status == '3'){
	$req_sql="UPDATE ".TB_PREF."mm_brkd_req SET process_status='2' WHERE id=".db_escape($req_id)."";
		db_query($req_sql,"something went wrong!");
	}
	
}

function add_break_down_items($items,$date_, $reference, $memo_,$schedule_id,$type)
{
	$date_=date2sql($date_);
	$sql="INSERT INTO ".TB_PREF."break_down_request(ref,type,req_date,schedule_id,memo_)
		VALUES (".db_escape($reference).",".db_escape($type).",".db_escape($date_).",".db_escape($schedule_id).",".db_escape($memo_).")";
		
	db_query($sql, "could not add maintenance items.!");
	$id=db_insert_id();
	foreach ($items as $line_item)
	{
		$sql="INSERT INTO ".TB_PREF."break_own_request_items (break_down_id,stock_id,qty)
		VALUES (".db_escape($id).",".db_escape($line_item->stock_id).",".db_escape($line_item->quantity).")";
		db_query($sql, "could not add maintenance items.!");
	}
	return $id;
}
?>