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
function add_break_down_request_items($items,$date_, $reference, $memo_,$schedule_id,$type,$req_loc,$emp_id,$dept_id)
{
	$date_=date2sql($date_);
	$sql="INSERT INTO ".TB_PREF."break_down_request(ref,type,req_date,schedule_id,memo_,loc_code,emp_id,dept_id)
		VALUES (".db_escape($reference).",".db_escape($type).",".db_escape($date_).",".db_escape($schedule_id).",".db_escape($memo_).",".db_escape($req_loc).",".db_escape($emp_id).",".db_escape($dept_id).")";
	db_query($sql, "could not add maintenance items.!");
	$id=db_insert_id();
	foreach ($items as $line_item)
	{
		$sql="INSERT INTO ".TB_PREF."break_down_request_items (break_down_id,stock_id,qty)
		VALUES (".db_escape($id).",".db_escape($line_item->stock_id).",".db_escape($line_item->quantity).")";
		db_query($sql, "could not add maintenance items.!");
	}
	return $id;
}
function get_breakdown_items_issues_from($trans_no)
{
	// retrieve common data from any two from/to move records
	$sql = "SELECT info.empl_firstname as name,req.id, type, req_date, ref,loc.location_name as loc_name,req.memo_ 
		FROM ".TB_PREF."break_down_request as req
		LEFT JOIN ".TB_PREF."locations loc ON loc.loc_code=req.loc_code
		LEFT JOIN ".TB_PREF."kv_empl_info info ON info.id=req.emp_id
		WHERE req.id = ".db_escape($trans_no)."";
	$result = db_query($sql, "Could not get transfer common data");

	$data = db_fetch($result);

	return $data;
}
function get_breakdown_req_items($type, $trans_no)
{
$sql="SELECT req.loc_code,items.*,items.qty-items.qty_rec as rem_qty,stock.description,stock.units FROM ".TB_PREF."break_down_request_items items 
INNER JOIN ".TB_PREF."break_down_request req on req.id=items.break_down_id
INNER JOIN stock_master stock on stock.stock_id=items.stock_id
WHERE items.break_down_id=".db_escape($trans_no)." ORDER BY items.stock_id";
	return db_query($sql, "Could not get preventive Requests");
}
function check_quantity_available_inventory_break($stock_id,$batch_code,$loc_code)
{
$sql="SELECT sum(qty) as qty FROM ".TB_PREF."stock_moves WHERE stock_id=".db_escape($stock_id)." and batch_code=".db_escape($batch_code)." and loc_code=".db_escape($loc_code)." ";

$row=db_query($sql);
$result=db_fetch_row($row);
return $result[0];
}
function get_breakdown_requested_qty($id)
{
$sql="select qty_rec from ".TB_PREF."break_down_request_items where item_id=".db_escape($id)." ";
$row=db_query($sql);
$result=db_fetch_row($row);
return $result[0];
}
function add_breakdown_issue_entry($type,$req_id,$reference,$issue_date,$taken_by,$remarks)
{
	$date_=date2sql($issue_date);
	$sql="INSERT INTO ".TB_PREF."break_down_issue_entry(type,req_id,reference,issue_date,taken_by,remarks)
VALUES(".db_escape($type).",".db_escape($req_id).",".db_escape($reference).",".db_escape($date_).",".db_escape($taken_by).",".db_escape($remarks).")";
	db_query($sql, "could not add maintenance items.!");
	$id=db_insert_id();
	return $id;
}
function update_berak_down_issue_quantity($id,$stock_id,$qty_rec,$status,$close_indent,$from_location)
{
$sql="UPDATE ".TB_PREF."break_down_request_items SET qty_rec=".db_escape($qty_rec).",status=".db_escape($status).",close_indent=".db_escape($close_indent)." ,from_location=".db_escape($from_location)." WHERE item_id=".db_escape($id)." ";
db_query($sql);
}
 function get_break_down_items_status($trans_no)
{
$sql="select status from ".TB_PREF."break_down_request_items where break_down_id=".db_escape($trans_no)."";
return db_query($sql);
}
function update_break_down_items_issue_status($trans_no,$indent_status,$remarks)
{
$sql="UPDATE ".TB_PREF."break_down_request SET indent_status=".db_escape($indent_status).",remarks=".db_escape($remarks)." WHERE id=".db_escape($trans_no)." ";
db_query($sql);
}
?>