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
function get_emp_code($sess)
{
	$sql="SELECT emp_id from ".TB_PREF."users where user_id=".db_escape($sess).""; 
	$result = db_query($sql, "could not get");
	$row = db_fetch_row($result);
	return $row[0];
}

function get_emp_id($emp_code)
{
	$sql1 = "SELECT empl.id FROM ".TB_PREF."kv_empl_info as empl 
	LEFT JOIN ".TB_PREF."users as user ON user.emp_id=empl.empl_id
	WHERE user.emp_id=".db_escape($emp_code).""; 
	$result1 = db_query($sql1, "could not get");

	$row1 = db_fetch_row($result1);
	return $row1[0];

}
function get_dept_id($emp_code)
{
$sql="select department from ".TB_PREF."kv_empl_job where empl_id=".db_escape($emp_code)."";

$result1 = db_query($sql, "could not get");

	$row1 = db_fetch_row($result1);
	return $row1[0];
}
function add_indent_adjustment($items, $location, $date_, $reference, $memo_,$emp_id,$dept_id)
{

	global $SysPrefs, $path_to_root, $Refs;

	begin_transaction();
	$args = func_get_args();
	$args = (object)array_combine(array('items', 'location', 'date_', 'reference', 'memo_'), $args);
	$args->trans_no = 0;
	hook_db_prewrite($args, ST_INDENTREQUEST);

	
$tran_date = date2sql($date_); 	 	
	 	$trans_type=ST_INDENTREQUEST;

	$sql = "INSERT INTO ".TB_PREF."indent_request(reference,type, tran_date, loc_code,
		memo,emp_id,dept_id) VALUES ("
		.db_escape($reference).","
		.db_escape($trans_type).", "
		.db_escape($tran_date).", ".db_escape($location).", "
		.db_escape($memo_).", "
		.db_escape($emp_id).", "
		.db_escape($dept_id).")";

	db_query($sql, "The indent record cannot be inserted");

	$adj_id=db_insert_id();


	if ($SysPrefs->loc_notification() == 1)
	{
		include_once($path_to_root . "/inventory/includes/inventory_db.inc");
		$st_ids = array();
		$st_names = array();
		$st_num = array();
		$st_reorder = array();
	}
	
		foreach ($items as $line_item)
	{
	$i=0;
			
		
	$req_date = date2sql($line_item->required_date); 	 	

	$sql1 = "INSERT INTO ".TB_PREF."indent_request_details(trans_no,stock_id,qty,req_date,supplier_id) VALUES (
	".db_escape($adj_id).",".db_escape($line_item->stock_id).", "
		.db_escape($line_item->quantity).","
		
		.db_escape($req_date).","
		.db_escape($line_item->supplier_id).")";

	db_query($sql1, "The stock movement record cannot be inserted");

		
	} /* inserted line items into sales order details */

		add_comments(ST_INDENTREQUEST, $adj_id, $date_, $memo_);

	$Refs->save(ST_INDENTREQUEST, $adj_id, $reference);
	add_audit_trail(ST_INDENTREQUEST, $adj_id, $date_);

	$args->trans_no = $adj_id;
	hook_db_postwrite($args, ST_INDENTREQUEST);
	commit_transaction();
	if ($SysPrefs->loc_notification() == 1 && count($st_ids) > 0)
		send_reorder_email($loc, $st_ids, $st_names, $st_num, $st_reorder);

	return $adj_id;
}


function add_purchase_indent_adjustment($items, $location, $date_, $reference, $memo_,$emp_id,$dept_id)
{

	global $SysPrefs, $path_to_root, $Refs;

	begin_transaction();
	$args = func_get_args();
	$args = (object)array_combine(array('items', 'location', 'date_', 'reference', 'memo_'), $args);
	$args->trans_no = 0;
	hook_db_prewrite($args, ST_PURCHINDENTREQUEST);
	
$tran_date = date2sql($date_); 	 	
	 	$trans_type=ST_PURCHINDENTREQUEST;

	$sql = "INSERT INTO ".TB_PREF."purchase_indent_request(reference,type, tran_date, loc_code,
		memo,emp_id,dept_id) VALUES ("
		.db_escape($reference).","
		.db_escape($trans_type).", "
		.db_escape($tran_date).", ".db_escape($location).", "
		.db_escape($memo_).", "
		.db_escape($emp_id).", "
		.db_escape($dept_id).")";

	db_query($sql, "The stock movement record cannot be inserted");

	$adj_id=db_insert_id();


	if ($SysPrefs->loc_notification() == 1)
	{
		include_once($path_to_root . "/inventory/includes/inventory_db.inc");
		$st_ids = array();
		$st_names = array();
		$st_num = array();
		$st_reorder = array();
	}
	
		foreach ($items as $line_item)
	{
	$i=0;
			
		
	$req_date = date2sql($line_item->required_date); 	 	

	$sql1 = "INSERT INTO ".TB_PREF."purchase_indent_request_details(trans_no,stock_id,qty,req_date,supplier_id) VALUES (
	".db_escape($adj_id).",".db_escape($line_item->stock_id).", "
		.db_escape($line_item->quantity).","
		
		.db_escape($req_date).","
		.db_escape($line_item->supplier_id).")";

	db_query($sql1, "The stock movement record cannot be inserted");

		
	} /* inserted line items into sales order details */

		add_comments(ST_PURCHINDENTREQUEST, $adj_id, $date_, $memo_);

	$Refs->save(ST_PURCHINDENTREQUEST, $adj_id, $reference);
	add_audit_trail(ST_PURCHINDENTREQUEST, $adj_id, $date_);

	$args->trans_no = $adj_id;
	hook_db_postwrite($args, ST_PURCHINDENTREQUEST);
	commit_transaction();
	if ($SysPrefs->loc_notification() == 1 && count($st_ids) > 0)
		send_reorder_email($loc, $st_ids, $st_names, $st_num, $st_reorder);

	return $adj_id;
}


function get_pi_reference($trans_no)
{
    $sql="SELECT reference FROM ".TB_PREF."purchase_indent_request WHERE trans_no=".db_escape($trans_no)."";
    	$result = db_query($sql, "Could not get transfer common data");
	$data = db_fetch_row($result);
	return $data[0];
}

function get_indent_issues($trans_no)
{
$sql="SELECT req.stock_id,master.description,master.units,req.qty,supp.supp_name,req.req_date,req.qty_rec,supp.supp_ref FROM ".TB_PREF."indent_request_details req
left join suppliers supp on supp.supplier_id=req.supplier_id
left join stock_master master on master.stock_id=req.stock_id

WHERE req.trans_no=".db_escape($trans_no)."";

	return db_query($sql, "Could not get Indent Requests");
}

function get_purchase_indent_issues($trans_no)
{
$sql="SELECT req.trans_id,req.stock_id,master.description,req.qty,supp.supp_name,req.req_date,req.qty_rec,supp.supp_ref,req.status ,supp.vendor_code,master.units FROM ".TB_PREF."purchase_indent_request_details req
left join suppliers supp on supp.supplier_id=req.supplier_id
left join stock_master master on master.stock_id=req.stock_id
WHERE req.trans_no=".db_escape($trans_no)."";
	return db_query($sql, "Could not get Indent Requests");
}

function get_pi_item_detail($trans_id)
{
    $sql="SELECT req.trans_id,req.stock_id,master.description,req.qty,supp.supp_name,req.req_date,req.qty_rec,supp.supp_ref,supp.vendor_code,master.units FROM ".TB_PREF."purchase_indent_request_details req
left join suppliers supp on supp.supplier_id=req.supplier_id
left join stock_master master on master.stock_id=req.stock_id

WHERE req.trans_id=".db_escape($trans_id)."";


	$result = db_query($sql, "Could not get transfer common data");

	$data = db_fetch($result);

	return $data;	

    
}

function get_indent_issues_from($trans_no)
{
	// retrieve common data from any two from/to move records
	$sql = "SELECT info.empl_firstname as name,trans_no, type, tran_date, reference,loc.location_name as loc_name,req.remarks,req.memo 
		FROM ".TB_PREF."indent_request as req
		LEFT JOIN ".TB_PREF."locations loc ON loc.loc_code=req.loc_code
		LEFT JOIN ".TB_PREF." kv_empl_info info ON info.id=req.emp_id
		WHERE req.trans_no = ".db_escape($trans_no)."";

	$result = db_query($sql, "Could not get transfer common data");

	$data = db_fetch($result);

	return $data;
}

function get_purchase_indent_issues_from($trans_no)
{
	// retrieve common data from any two from/to move records
	$sql = "SELECT info.empl_firstname as name,trans_no, type, tran_date, reference,loc.location_name as loc_name,memo 
		FROM ".TB_PREF."purchase_indent_request as req
		LEFT JOIN ".TB_PREF."locations loc ON loc.loc_code=req.loc_code
		LEFT JOIN ".TB_PREF."kv_empl_info info ON info.id=req.emp_id
		WHERE req.trans_no = ".db_escape($trans_no)."";

	$result = db_query($sql, "Could not get transfer common data");

	$data = db_fetch($result);

	return $data;
}

function get_indent_dept_issues($type, $trans_no)
{
$sql="SELECT ind.loc_code,req.*,req.qty-req.qty_rec as rem_qty,stock.description,stock.units,supp.supp_name FROM ".TB_PREF."indent_request_details req 
left join ".TB_PREF."indent_request ind on ind.trans_no=req.trans_no
left join stock_master stock on stock.stock_id=req.stock_id
left join suppliers supp on supp.supplier_id=req.supplier_id
WHERE req.trans_no=".db_escape($trans_no)." ORDER BY req.trans_id";

	return db_query($sql, "Could not get Indent Requests");
}
function check_quantity_available($stock_id,$batch_code,$loc_code)
{
$sql="SELECT sum(qty) as qty FROM ".TB_PREF."stock_moves WHERE stock_id=".db_escape($stock_id)." and batch_code=".db_escape($batch_code)." and loc_code=".db_escape($loc_code)." ";

$row=db_query($sql);
$result=db_fetch_row($row);
return $result[0];

}
function check_quantity_available_loc($stock_id,$loc_code)
{
$sql="SELECT sum(qty) as qty FROM ".TB_PREF."stock_moves WHERE stock_id=".db_escape($stock_id)."  and loc_code=".db_escape($loc_code)." ";

$row=db_query($sql);
$result=db_fetch_row($row);
return $result[0];

}
function check_quantity_available_batch($stock_id,$batch_code)
{
$sql="SELECT sum(qty) as qty FROM ".TB_PREF."stock_moves WHERE stock_id=".db_escape($stock_id)."  and batch_code=".db_escape($batch_code)." ";
$row=db_query($sql);
$result=db_fetch_row($row);
return $result[0];

}
function check_quantity($stock_id)
{
$sql="SELECT sum(qty) as qty FROM ".TB_PREF."stock_moves WHERE stock_id=".db_escape($stock_id)."  ";

$row=db_query($sql);
$result=db_fetch_row($row);
return $result[0];

}
function get_qty($id)
{
$sql="select qty_rec from ".TB_PREF."indent_request_details where trans_id=".db_escape($id)." ";

$row=db_query($sql);
$result=db_fetch_row($row);
return $result[0];
}
function update_indent_quantity($id,$stock_id,$qty_rec,$status,$close_indent,$from_location)
{

$sql="UPDATE ".TB_PREF."indent_request_details SET qty_rec=".db_escape($qty_rec).",status=".db_escape($status).",close_indent=".db_escape($close_indent)." ,from_location=".db_escape($from_location)." WHERE trans_id=".db_escape($id)." ";

db_query($sql);
}
function update_indent_status($trans_no,$indent_status,$remarks)
{
$sql="UPDATE ".TB_PREF."indent_request SET indent_status=".db_escape($indent_status).",remarks=".db_escape($remarks)." WHERE trans_no=".db_escape($trans_no)." ";

db_query($sql);
}
function get_items_status($trans_no)
{
$sql="select status from ".TB_PREF."indent_request_details where trans_no=".db_escape($trans_no)."";
return db_query($sql);
}

function get_ordered_qty($indent_ref,$stock_id)
{
	$sql="SELECT sum(quantity_ordered) FROM ".TB_PREF."purch_order_details pod,".TB_PREF."purch_orders po WHERE po.order_no=pod.order_no and po.indent_ref='$indent_ref' and item_code='$stock_id'";
	$row=db_query($sql);
$result=db_fetch_row($row);
if($result[0]==null)
return 0;
else
return $result[0];
}

function get_received_qty($indent_ref,$stock_id)
{
	$sql="SELECT sum(quantity_received) FROM ".TB_PREF."purch_order_details pod,".TB_PREF."purch_orders po WHERE po.order_no=pod.order_no and po.indent_ref='$indent_ref' and item_code='$stock_id'";
	$row=db_query($sql);
	$result=db_fetch_row($row);
	if($result[0]==null)
	return 0;
	else
	return $result[0];
}