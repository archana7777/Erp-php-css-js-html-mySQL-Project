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
/*
	Common entry procedure for all work order types
*/
function add_work_order($wo_ref, $loc_code, $units_reqd, $stock_id,
	$type, $date_, $required_by, $memo_, $costs, $cr_acc, $labour, $cr_lab_acc,$units_reqs_per_day,$execute_by)
{
	global $Refs;

	begin_transaction();
	$args = func_get_args();
	$args = (object)array_combine(array('wo_ref', 'loc_code', 'units_reqd', 'stock_id',
		'type', 'date_', 'required_by', 'memo_', 'costs', 'cr_acc', 'labour', 'cr_lab_acc'), $args);

	$args->woid = 0;
	hook_db_prewrite($args, ST_WORKORDER);

	if ($type != WO_ADVANCED)
		$required_by = $date_;

	$date = date2sql($date_);
	$required = date2sql($required_by);
	$execute_by = date2sql($execute_by);
	$sql = "INSERT INTO ".TB_PREF."workorders (wo_ref, loc_code, units_reqd, stock_id,
		type, date_, required_by,units_reqs_per_day,execute_by)
    	VALUES (".db_escape($wo_ref).", ".db_escape($loc_code).", "
    	.db_escape($units_reqd).", ".db_escape($stock_id).",
		".db_escape($type).", '$date', ".db_escape($required).", ".db_escape($units_reqs_per_day).", ".db_escape($execute_by).")";
	db_query($sql, "could not add work order");

	$woid = db_insert_id();
	
	
	$sql = "INSERT INTO ".TB_PREF."wm_tm_workorders (wo_ref, loc_code, units_reqd, stock_id,
		type, date_, required_by,units_reqs_per_day,execute_by,work_order_memo)
    	VALUES (".db_escape($wo_ref).", ".db_escape($loc_code).", "
    	.db_escape($units_reqd).", ".db_escape($stock_id).",
		".db_escape($type).", '$date', ".db_escape($required).", ".db_escape($units_reqs_per_day).", ".db_escape($execute_by).", ".db_escape($memo_).")";
	db_query($sql, "could not add work order");

	add_comments(ST_WORKORDER, $woid, $required_by, $memo_);

	$Refs->save(ST_WORKORDER, $woid, $wo_ref);
	add_audit_trail(ST_WORKORDER, $woid, $date_);

	$args->woid = $woid;
	hook_db_postwrite($args, ST_WORKORDER);

	if ($type != WO_ADVANCED)
	{
	 	$stockitem = get_item($stock_id);
		release_work_order($woid, $date_, '');
		if ($costs != 0)
			add_wo_costs_journal($woid, $costs, WO_OVERHEAD, $cr_acc, $date_, $stockitem["dimension_id"], $stockitem["dimension2_id"]);

		if ($labour != 0)
			add_wo_costs_journal($woid, $labour, WO_LABOUR, $cr_lab_acc, $date_, $stockitem["dimension_id"], $stockitem["dimension2_id"]);

		if ($type == WO_UNASSEMBLY)
			$units_reqd = -$units_reqd;

		$ref = $Refs->get_next(ST_MANURECEIVE, null, $date_);
		work_order_produce($woid, $ref, $units_reqd, $date_, '', true);
	}

	commit_transaction();

	return $woid;
}

//--------------------------------------------------------------------------------------

function update_work_order($woid, $loc_code, $units_reqd, $stock_id,
					$date_, $required_by, $memo_,$units_reqs_per_day,$execute_by)
{
	begin_transaction();
	$args = func_get_args();
	$args = (object)array_combine(array('woid', 'loc_code', 'units_reqd', 'stock_id',
		'date_', 'required_by', 'memo_'), $args);
	hook_db_prewrite($args, ST_WORKORDER);

	$date = date2sql($date_);
	$required = date2sql($required_by);
	$execute_by = date2sql($execute_by);

	$sql = "UPDATE ".TB_PREF."workorders SET loc_code=".db_escape($loc_code).",
		units_reqd=".db_escape($units_reqd).", stock_id=".db_escape($stock_id).",
		required_by=".db_escape($required).",
		execute_by=".db_escape($execute_by).",
		units_reqs_per_day=".db_escape($units_reqs_per_day).",
		date_='$date'
		WHERE id = ".db_escape($woid);

	db_query($sql, "could not update work order");
	
	$sql = "UPDATE ".TB_PREF."wm_tm_workorders SET loc_code=".db_escape($loc_code).",
		units_reqd=".db_escape($units_reqd).", stock_id=".db_escape($stock_id).",
		required_by=".db_escape($required).",
		execute_by=".db_escape($execute_by).",
		work_order_memo=".db_escape($memo_).",
		units_reqs_per_day=".db_escape($units_reqs_per_day).",
		date_='$date'
		WHERE id = ".db_escape($woid);

	db_query($sql, "could not update work order");

	update_comments(ST_WORKORDER, $woid, null, $memo_);
	add_audit_trail(ST_WORKORDER, $woid, $date_, _("Updated."));

	hook_db_postwrite($args, ST_WORKORDER);
	commit_transaction();
}

function delete_work_order($woid, $stock_id, $qty, $date)
{
	begin_transaction();
	hook_db_prevoid(ST_WORKORDER, $woid);

	// delete the work order requirements
	delete_wo_requirements($woid);

	// delete the actual work order
	$sql = "DELETE FROM ".TB_PREF."workorders WHERE id=".db_escape($woid);
	db_query($sql,"The work order could not be deleted");

	delete_comments(ST_WORKORDER, $woid);
	add_audit_trail(ST_WORKORDER, $woid, $date, _("Canceled."));

	commit_transaction();
}

//--------------------------------------------------------------------------------------

function get_work_order($woid, $allow_null=false)
{
    $sql = "SELECT wo.*,st.description As StockItemName,l.location_name,
    		l.delivery_address,l.email, l.contact, st.inventory_account, st.wip_account
		FROM ".TB_PREF."workorders wo, "
			.TB_PREF."stock_master st, "
			.TB_PREF."locations l
		WHERE st.stock_id=wo.stock_id
		AND	l.loc_code=wo.loc_code
		AND wo.id=".db_escape($woid)."
		GROUP BY wo.id";

	$result = db_query($sql, "The work order could not be retrieved");

	if (!$allow_null && db_num_rows($result) == 0)
	{
		display_db_error("Could not find work order ".(int)$woid, $sql);
		return false;
	}

	return db_fetch($result);
}

function get_pickled_items_summary($woid)
{
    $sql = "SELECT count(*) as count,sum(qty) as total_qty FROM ".TB_PREF."manuf_pickling WHERE woid=".db_escape($woid);

	$result = db_query($sql, "The work order could not be retrieved");

	return db_fetch($result);
}


//--------------------------------------------------------------------------------------

function work_order_has_productions($woid)
{
	$sql = "SELECT COUNT(*) FROM ".TB_PREF."wo_manufacture WHERE workorder_id=".db_escape($woid);
	$result = db_query($sql, "query work order for productions");

	$myrow = db_fetch_row($result);
	return ($myrow[0] > 0);
}


//--------------------------------------------------------------------------------------

function work_order_has_issues($woid)
{
	$sql = "SELECT COUNT(*) FROM ".TB_PREF."wo_issues WHERE workorder_id=".db_escape($woid);
	$result = db_query($sql, "query work order for issues");

	$myrow = db_fetch_row($result);
	return ($myrow[0] > 0);
}

//--------------------------------------------------------------------------------------

function work_order_has_payments($woid)
{
	$result = get_gl_wo_cost_trans($woid);

    return (db_num_rows($result) != 0);
}

//--------------------------------------------------------------------------------------

function release_work_order($woid, $releaseDate, $memo_)
{
	begin_transaction();

	$myrow = get_work_order($woid);
	$stock_id = $myrow["stock_id"];

	$date = date2sql($releaseDate);

	$sql = "UPDATE ".TB_PREF."workorders SET released_date='$date',
		released=1 WHERE id = ".db_escape($woid);
	db_query($sql, "could not release work order");

	// create wo_requirements records according to current BOM
	// create_wo_requirements($woid, $stock_id);

	add_comments(ST_WORKORDER, $woid, $releaseDate, $memo_);
	add_audit_trail(ST_WORKORDER, $woid, sql2date($myrow['date_']), _("Released."));

	commit_transaction();
}

//ravi
function approve_work_order($woid, $approveDate, $memo_)
{
	begin_transaction();

	$myrow = get_work_order($woid);
	
	$wm_tm_details=get_tm_wm_details($woid);
	
	if($wm_tm_details['wo_memo_status'])
	update_comments(ST_WORKORDER, $woid, null, $wm_tm_details['proposed_work_order_memo']);

    if($wm_tm_details['units_reqd_status'])
	$units_reqd=$wm_tm_details['proposed_units_reqd'];
    else	
	$units_reqd=$myrow['units_reqd'];
	
	if($wm_tm_details['units_reqd_status'])
	$units_reqd=$wm_tm_details['proposed_units_reqd'];
    else	
	$units_reqd=$myrow['units_reqd'];
	
	
	if($wm_tm_details['execute_by_status'])
	$execute_by=$wm_tm_details['proposed_execute_by'];
    else	
	$execute_by=$myrow['execute_by'];
	
	if($wm_tm_details['required_by_status'])
	$required_by=$wm_tm_details['proposed_required_by'];
    else	
	$required_by=$myrow['required_by'];
	
	if($wm_tm_details['units_reqs_per_day_status'])
	$units_reqs_per_day=$wm_tm_details['proposed_units_reqs_per_day'];
    else	
	$units_reqs_per_day=$myrow['units_reqs_per_day'];
	
	
	$date = date2sql($approveDate);

	$sql = "UPDATE ".TB_PREF."workorders SET approved_date='$date',
		approved=1 , units_reqd=".db_escape($units_reqd).", execute_by=".db_escape($execute_by).", required_by=".db_escape($required_by).", units_reqs_per_day=".db_escape($units_reqs_per_day)."   WHERE id = ".db_escape($woid);
	db_query($sql, "could not approve work order");
	
	add_comments(ST_WORKORDER, $woid, $approveDate, $memo_);
	add_audit_trail(ST_WORKORDER, $woid, sql2date($myrow['date_']), _("Approved."));

	commit_transaction();
}
//--------------------------------------------------------------------------------------

function close_work_order($woid)
{
	$sql = "UPDATE ".TB_PREF."workorders SET closed=1 WHERE id = ".db_escape($woid);
	db_query($sql, "could not close work order");
}

//--------------------------------------------------------------------------------------

function work_order_is_closed($woid)
{
	$sql = "SELECT closed FROM ".TB_PREF."workorders WHERE id = ".db_escape($woid);
	$result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return ($row[0] > 0);
}

//--------------------------------------------------------------------------------------
/*
	Update finished items quantity in work order, and close order either if all the order is produced,
	or on user demand. Returns calculated unit cost on close, or null otherwise.
*/
function work_order_update_finished_quantity($woid, $quantity, $force_close=0)
{

	$sql = "UPDATE ".TB_PREF."workorders SET units_issued = units_issued + ".db_escape($quantity)."
		WHERE id = ".db_escape($woid). " AND !closed";

	db_query($sql, "The work order issued quantity couldn't be updated");

	$sql = "UPDATE ".TB_PREF."workorders SET closed = ((units_issued >= units_reqd) OR ".db_escape($force_close).")
		WHERE id = ".db_escape($woid);

	db_query($sql, "The work order couldn't be closed");

	return db_num_affected_rows();	// returns 1 if WO has been closed
}

function reopen_work_order($woid)
{
		// FIXME: update_material_cost
//		update_material_cost($work_order['stock_id'], -$work_order['units_issued'], ... );

		void_gl_trans(ST_WORKORDER, $woid);

		void_stock_move(ST_WORKORDER, $woid);

		$sql = "UPDATE ".TB_PREF."workorders SET closed=0 WHERE id = "
			.db_escape($woid);

		db_query($sql, "The work order couldn't be reopened");
}

//--------------------------------------------------------------------------------------
/*
	Void all work order related operations
*/
function void_work_order($woid)
{
	begin_transaction();
	hook_db_prevoid(ST_WORKORDER, $woid);

	$work_order = get_work_order($woid);

	if ($work_order['closed'])
		reopen_work_order($woid);

	if ($work_order['units_issued'])
	{
		$prods = get_work_order_productions($woid);
		while ($prod = db_fetch($prods))
			void_work_order_produce($prod['id']);
	}

	// void addtional material issues
	$issues = get_work_order_issues($woid);
	while($issue = db_fetch($issues))
		void_work_order_issue($issue['issue_no']);

	// void all related stock moves
	void_stock_move(ST_WORKORDER, $woid);

	// void any related costing records
	void_wo_costing($woid);

	// clear the requirements units received
	void_wo_requirements($woid);

	// close workorder
	// FIXME: due to bug in 2.4 db scheme (conflicting wo_ref unique key) we have to fix wo_ref here:
	$sql = "UPDATE ".TB_PREF."workorders SET closed=1,units_reqd=0,units_issued=0, wo_ref=CONCAT(wo_ref, ' ', '".uniqid()."') WHERE id = "
		.db_escape($woid);
	db_query($sql, "The work order couldn't be voided");

	commit_transaction();
}

function get_sql_for_work_orders($outstanding_only, $stock_id, $location = ALL_TEXT, $order_no='', $order_ref = '', $overdue = false)
{
	$sql = "SELECT
		workorder.id,
		workorder.wo_ref,
		workorder.type,
		location.location_name,
		item.description,
		workorder.units_reqd,
		workorder.units_reqs_per_day,
		workorder.units_issued,
		workorder.date_,
		workorder.execute_by,
		workorder.required_by,
		workorder.released_date,
		workorder.closed,
		workorder.released,
		workorder.stock_id,
		unit.decimals
		FROM ".TB_PREF."workorders as workorder
			LEFT JOIN ".TB_PREF."voided v ON v.id=workorder.id and v.type=".ST_WORKORDER.","
			.TB_PREF."stock_master as item,"
			.TB_PREF."item_units as unit,"
			.TB_PREF."locations as location
		WHERE ISNULL(v.id)
			AND workorder.stock_id=item.stock_id 
			AND workorder.loc_code=location.loc_code
			AND workorder.approved=1
			AND item.units=unit.abbr";

	if (check_value('OpenOnly') || $outstanding_only != 0)
	{
		$sql .= " AND workorder.closed=0";
	}

	if ($location != ALL_TEXT)
	{
		$sql .= " AND workorder.loc_code=".db_escape($location);
	}

	if ($order_no != '')
	{
		$sql .= " AND workorder.id LIKE ".db_escape('%'.$order_no.'%');
	}

	if ($order_ref != '')
	{
		$sql .= " AND workorder.wo_ref LIKE ".db_escape('%'.$order_ref.'%');
	}

	if ($stock_id != ALL_TEXT)
	{
		$sql .= " AND workorder.stock_id=".db_escape($stock_id);
	}

	if ($overdue)
	{
		$Today = date2sql(Today());

		$sql .= " AND workorder.required_by < '$Today' ";
	}
	$sql .= " ORDER BY workorder.id DESC";
	
	return $sql;
}

function get_sql_for_where_used($stock_id)
{
	$sql = "SELECT 
			bom.parent,
			workcentre.name As WorkCentreName,
			location.location_name,
			bom.quantity,
			parent.description
			FROM ".TB_PREF."bom as bom, "
				.TB_PREF."stock_master as parent, "
				.TB_PREF."workcentres as workcentre, "
				.TB_PREF."locations as location
			WHERE bom.parent = parent.stock_id 
				AND bom.workcentre_added = workcentre.id
				AND bom.loc_code = location.loc_code
				AND bom.component=".db_escape($stock_id);
	return $sql;
}
//--------------------------------------------------------------------------------------
function get_gl_wo_cost($woid, $cost_type)
{
	$cost = 0;
	$result = get_gl_wo_cost_trans($woid, $cost_type);
	while ($row = db_fetch($result))
		$cost += -$row['amount'];
	return $cost;
}

function get_item_info($stock_id)
{
	$sql = "SELECT 
			stock.standard,
			size.size,
			grade.grade
			FROM ".TB_PREF."stock_master as stock, "
				.TB_PREF."size_master as size, "
				.TB_PREF."grade_master as grade
			WHERE stock.size_id = size.id 
				AND stock.grade_id = grade.id
				AND stock.stock_id=".db_escape($stock_id);
	$result=db_query($sql, "The item information couldn't be called");
	return db_fetch($result);	
}

function get_product_type_from_work_order($wid)
{
	$sql = "SELECT 
			stock.product_type_id
			FROM ".TB_PREF."stock_master as stock, "
				.TB_PREF."workorders as wo
			WHERE stock.stock_id = wo.stock_id
				AND wo.id=".db_escape($wid);
	$result=db_query($sql, "The item information couldn't be called");
	$row=db_fetch_row($result);	
	return $row[0];
}

function get_sql_for_tm_wm_work_orders($outstanding_only, $stock_id, $location = ALL_TEXT, $order_no='', $order_ref = '', $overdue = false)
{
	$sql = "SELECT
		workorder.id,
		workorder.wo_ref,
		workorder.type,
		location.location_name,
		item.description,
		workorder.units_reqd,
		workorder.units_reqs_per_day,
		workorder.units_issued,
		workorder.date_,
		workorder.execute_by,
		workorder.required_by,
		workorder.released_date,
		workorder.closed,
		workorder.released,
		workorder.stock_id,
		unit.decimals,
		workorder.wm_tm_status,
		workorder.approved
		FROM ".TB_PREF."workorders as workorder
			LEFT JOIN ".TB_PREF."voided v ON v.id=workorder.id and v.type=".ST_WORKORDER.","
			.TB_PREF."stock_master as item,"
			.TB_PREF."item_units as unit,"
			.TB_PREF."locations as location
		WHERE ISNULL(v.id)
			AND workorder.stock_id=item.stock_id 
			AND workorder.loc_code=location.loc_code
			AND item.units=unit.abbr";

	if (check_value('OpenOnly') || $outstanding_only != 0)
	{
		$sql .= " AND workorder.closed=0";
	}

	if ($location != ALL_TEXT)
	{
		$sql .= " AND workorder.loc_code=".db_escape($location);
	}

	if ($order_no != '')
	{
		$sql .= " AND workorder.id LIKE ".db_escape('%'.$order_no.'%');
	}

	if ($order_ref != '')
	{
		$sql .= " AND workorder.wo_ref LIKE ".db_escape('%'.$order_ref.'%');
	}

	if ($stock_id != ALL_TEXT)
	{
		$sql .= " AND workorder.stock_id=".db_escape($stock_id);
	}

	if ($overdue)
	{
		$Today = date2sql(Today());

		$sql .= " AND workorder.required_by < '$Today' ";
	}
	$sql .= " ORDER BY workorder.id DESC";
	return $sql;
}

function get_sql_for_return_work_orders($outstanding_only, $stock_id, $location = ALL_TEXT, $order_no='', $order_ref = '', $overdue = false)
{
	$sql = "SELECT
		workorder.id,
		workorder.wo_ref,
		workorder.type,
		location.location_name,
		item.description,
		workorder.units_reqd,
		workorder.units_reqs_per_day,
		workorder.units_issued,
		workorder.date_,
		workorder.execute_by,
		workorder.required_by,
		workorder.released_date,
		workorder.closed,
		workorder.released,
		workorder.stock_id,
		unit.decimals,
		workorder.approved
		FROM ".TB_PREF."workorders as workorder
			LEFT JOIN ".TB_PREF."voided v ON v.id=workorder.id and v.type=".ST_WORKORDER.","
			.TB_PREF."stock_master as item,"
			.TB_PREF."item_units as unit,"
			.TB_PREF."locations as location
		WHERE ISNULL(v.id)
			AND workorder.stock_id=item.stock_id
			AND workorder.loc_code=location.loc_code
			AND item.units=unit.abbr";

	if (check_value('OpenOnly') || $outstanding_only != 0)
	{
		$sql .= " AND workorder.closed=0";
	}

	if ($location != ALL_TEXT)
	{
		$sql .= " AND workorder.loc_code=".db_escape($location);
	}

	if ($order_no != '')
	{
		$sql .= " AND workorder.id LIKE ".db_escape('%'.$order_no.'%');
	}

	if ($order_ref != '')
	{
		$sql .= " AND workorder.wo_ref LIKE ".db_escape('%'.$order_ref.'%');
	}

	if ($stock_id != ALL_TEXT)
	{
		$sql .= " AND workorder.stock_id=".db_escape($stock_id);
	}

	if ($overdue)
	{
		$Today = date2sql(Today());

		$sql .= " AND workorder.required_by < '$Today' ";
	}
	$sql .= " ORDER BY workorder.id DESC";
	return $sql;
}

function get_tm_wm_details($woid)
{
	$sql = "SELECT * FROM ".TB_PREF."wm_tm_workorders WHERE id = ".db_escape($woid);
	$result = db_query($sql, "could not query work order");
	$row = db_fetch($result);
	return $row;
}


function update_wm_tm_work_order($woid,$wo_memo_status,$proposed_work_order_memo,$units_reqd_status,$proposed_units_reqd,$units_reqs_per_day_status,$proposed_units_reqs_per_day,$execute_by_status,$proposed_execute_by,$required_by_status,$proposed_required_by,$grace_period,$rm_available_options,$rm_procurement,$rm_remarks,$machine_booked,$other_remarks)
{

  $proposed_execute_by=date2sql($proposed_execute_by);
  $proposed_required_by=date2sql($proposed_required_by);

	$sql = "UPDATE ".TB_PREF."wm_tm_workorders SET
      	wo_memo_status=".db_escape($wo_memo_status).",
		proposed_work_order_memo=".db_escape($proposed_work_order_memo).",
		units_reqd_status=".db_escape($units_reqd_status).",
		proposed_units_reqd=".db_escape($proposed_units_reqd).",
		units_reqs_per_day_status=".db_escape($units_reqs_per_day_status).",
		proposed_units_reqs_per_day=".db_escape($proposed_units_reqs_per_day).",
		execute_by_status=".db_escape($execute_by_status).",
		proposed_execute_by=".db_escape($proposed_execute_by).",
		required_by_status=".db_escape($required_by_status).",
		proposed_required_by=".db_escape($proposed_required_by).",
		grace_period=".db_escape($grace_period).",
		rm_available_options=".db_escape($rm_available_options).",
		rm_procurement=".db_escape($rm_procurement).",
		rm_remarks=".db_escape($rm_remarks).",
		machine_booked=".db_escape($machine_booked).",
		other_remarks=".db_escape($other_remarks)."
		WHERE id=".db_escape($woid);
		
		
		db_query($sql, "could not update wm tm work order");
		
				
	$sql = "UPDATE ".TB_PREF."workorders SET
      		wm_tm_status=1
		WHERE id =".db_escape($woid);
		
	db_query($sql, "could not update work order");			
				
}

function get_wm_tm_work_order($woid)
{
	$sql = "SELECT wo.*,sm.description,size.size,grade.grade,sm.standard,sm.oil_status FROM ".TB_PREF."wm_tm_workorders wo,".TB_PREF."stock_master sm,".TB_PREF."size_master size,".TB_PREF."grade_master grade WHERE wo.stock_id=sm.stock_id and sm.size_id=size.id and sm.grade_id=grade.id and wo.id = ".db_escape($woid);
	$result = db_query($sql, "could not query work order");
	$row = db_fetch($result);
	return $row;
}

function get_coil_details($stock_id,$coil_no)
{
$sql = "SELECT mov.*,size.size,grade.grade FROM ".TB_PREF."stock_moves mov,".TB_PREF."stock_master sm,".TB_PREF."size_master size,".TB_PREF."grade_master grade WHERE mov.qty>0 and mov.stock_id=sm.stock_id and sm.size_id=size.id and sm.grade_id=grade.id and mov.stock_id = ".db_escape($stock_id)." and mov.coil_no=".db_escape($coil_no)."";

	$result = db_query($sql, "could not query work order");
	$row = db_fetch($result);
	return $row;
}




function add_pickling($woid,$date_,$shift_id,$operator_id,$supervisor_id,$stock_id,$coil_no,$heat_no,$qty,$at_in,$at_out,$pt_in,$pt_out,$bt_in,$bt_out,$remarks,$location_id)
{
	$tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_pickling (woid,tran_date,shift_id,operator_id,supervisor_id,stock_id,coil_no,heat_no,qty,at_in,at_out,pt_in,pt_out,bt_in,bt_out,remarks,parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($stock_id).", ".db_escape($coil_no).", ".db_escape($heat_no).", "
    	.db_escape($qty).", ".db_escape($at_in).",
		".db_escape($at_out).", ".db_escape($pt_in).", ".db_escape($pt_out).", ".db_escape($bt_in).", "
    	.db_escape($bt_out).", ".db_escape($remarks).",
		".db_escape($woid).")";
	db_query($sql, "could not add work order");
	
	$trans_no = db_insert_id();
	
	$sql = "UPDATE ".TB_PREF."stock_moves SET pickled=1
		WHERE coil_no = ".db_escape($coil_no)."and stock_id=".db_escape($stock_id)." and heat_no= ".db_escape($heat_no)."";
		
	db_query($sql, "could not update_stock_moves)");	
	
	
	$sql = "INSERT INTO ".TB_PREF."stock_moves (stock_id, trans_no, type, loc_code,
		tran_date, reference,qty, coil_no, heat_no) VALUES ("
		.db_escape($stock_id).", ".db_escape($trans_no).",51, ".db_escape($location_id).", '$tran_date','auto', "
		.db_escape(-$qty).", ".db_escape($coil_no)."," .db_escape($heat_no).")";
		
		
	db_query($sql, "The stock movement record cannot be inserted");
}

function get_pickled_coil_details($stock_id,$coil_no,$woid)
{
$sql = "SELECT pickling.*,size.size,grade.grade FROM ".TB_PREF."manuf_pickling pickling,".TB_PREF."stock_master sm,".TB_PREF."size_master size,".TB_PREF."grade_master grade WHERE  pickling.stock_id=sm.stock_id and sm.size_id=size.id and sm.grade_id=grade.id and pickling.stock_id = ".db_escape($stock_id)." and pickling.coil_no=".db_escape($coil_no)." and pickling.woid=".db_escape($woid)."";


	$result = db_query($sql, "could not query work order");
	$row = db_fetch($result);
	return $row;
}


function spool_exist($spool_code)
{
  $sql="SELECT count(*) FROM ".TB_PREF."manuf_drawing WHERE spool_code=".db_escape($spool_code)."";
  $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
  
}
function pickled_veiw_display($woid)// written by venkat
{
$sql="SELECT pickling.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site,item.description  FROM ".TB_PREF."manuf_pickling AS pickling 
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON pickling.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON pickling.supervisor_id=supervisor.id
LEFT JOIN ".TB_PREF."stock_master AS item ON pickling.stock_id=item.stock_id
WHERE pickling.woid=".db_escape($woid);
return  db_query($sql, "could not get pickled details");
}
function drawing_veiw_display($woid,$spool_code) //written by venkat
{
$sql="SELECT drawing.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site,item.description,machine.mac_code  FROM ".TB_PREF."manuf_drawing_coil_details AS drawing
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON drawing.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON drawing.supervisor_id=supervisor.id
LEFT JOIN ".TB_PREF."stock_master AS item ON drawing.stock_id=item.stock_id
LEFT JOIN ".TB_PREF."machine AS machine ON drawing.machine_id=machine.id
WHERE drawing.woid=".db_escape($woid)."
AND spool_code=".db_escape($spool_code);
return  db_query($sql, "could not get pickled details");
}
function lrpc_veiw_display($woid,$set_no) //written by venkat
{
$sql="SELECT lrpc.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site FROM ".TB_PREF."manuf_lrpc_details AS lrpc
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON lrpc.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON lrpc.supervisor_id=supervisor.id
WHERE lrpc.woid=".db_escape($woid)."
AND set_no=".db_escape($set_no);
return  db_query($sql, "could not get pickled details");
}
function get_pickling_uom($stock_id) //written by venkat
{
$sql="SELECT units FROM ".TB_PREF."stock_master WHERE stock_id=".db_escape($stock_id);
$result = db_query($sql,"an unit of measure could not be retrieved");

return db_fetch($result);
}
function layer_winder_veiw_display($woid,$coil_no)
{
$sql="SELECT winder.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site,size.size FROM ".TB_PREF."manuf_layer_winder AS winder
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON winder.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON winder.supervisor_id=supervisor.id
LEFT JOIN ".TB_PREF."size_master As size ON winder.size_id=size.id
WHERE winder.woid=".db_escape($woid)."
AND winder_coil_no=".db_escape($coil_no);

return  db_query($sql, "could not get pickled details");
}
function sr_furnace_veiw_display($woid,$coil_no)
{
$sql="SELECT sr.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site,size.size FROM ".TB_PREF."manuf_srfurnace AS sr
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON sr.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON sr.supervisor_id=supervisor.id
LEFT JOIN ".TB_PREF."size_master As size ON sr.size_id=size.id
WHERE sr.woid=".db_escape($woid)."
AND coil_no=".db_escape($coil_no);
$result= db_query($sql, "could not get pickled details");
return db_fetch($result);

}
function sr_galvanizing_veiw_display($woid,$coil_no)
{
$sql="SELECT gal.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site,size.size AS size1,size_rm.size AS size2 FROM ".TB_PREF."manuf_galvanising AS gal
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON gal.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON gal.supervisor_id=supervisor.id
LEFT JOIN ".TB_PREF."size_master As size ON gal.size_id=size.id
LEFT JOIN ".TB_PREF."size_master As size_rm ON gal.rm_size_id=size_rm.id

WHERE gal.woid=".db_escape($woid)."
AND coil_no=".db_escape($coil_no);
$result= db_query($sql, "could not get pickled details");
return db_fetch($result);

}
function extrudercoiling__veiw_display($woid,$coil_no)
{
$sql="SELECT ex_coil.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site,size.size AS size1,size_rm.size AS size2 FROM ".TB_PREF."manuf_extruder_coiling AS ex_coil
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON ex_coil.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON ex_coil.supervisor_id=supervisor.id
LEFT JOIN ".TB_PREF."size_master As size ON ex_coil.size_id=size.id
LEFT JOIN ".TB_PREF."size_master As size_rm ON ex_coil.rm_size_id=size_rm.id

WHERE ex_coil.woid=".db_escape($woid)."
AND extrudercoiling_coil_no=".db_escape($coil_no);
$result= db_query($sql, "could not get pickled details");
return db_fetch($result);

}
function extruder_veiw_display($woid,$coil_no)
{
$sql="SELECT ex.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site,size.size AS size1,size_rm.size AS size2 FROM ".TB_PREF."manuf_extruder AS ex
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON ex.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON ex.supervisor_id=supervisor.id
LEFT JOIN ".TB_PREF."size_master As size ON ex.size_id=size.id
LEFT JOIN ".TB_PREF."size_master As size_rm ON ex.rm_size_id=size_rm.id

WHERE ex.woid=".db_escape($woid)."
AND extruder_coil_no=".db_escape($coil_no);
$result= db_query($sql, "could not get pickled details");
return db_fetch($result);

}
function oiling_packing_veiw_display($woid,$coil_no)
{
$sql="SELECT oil.*,CONCAT(operator.empl_firstname,' ',operator.empl_lastname)AS operator_at_site,CONCAT(supervisor.empl_firstname,' ',supervisor.empl_lastname) AS supervisor_at_site,size.size FROM ".TB_PREF."manuf_oiling_packing AS oil
LEFT JOIN ".TB_PREF."kv_empl_info AS operator ON oil.operator_id=operator.id
LEFT JOIN ".TB_PREF."kv_empl_info AS supervisor ON oil.supervisor_id=supervisor.id
LEFT JOIN ".TB_PREF."size_master As size ON oil.size_id=size.id
WHERE oil.woid=".db_escape($woid)."
AND coil_no=".db_escape($coil_no);

return  db_query($sql, "could not get pickled details");
}

function allow_spool_code($spool_code)
{
  $sql="SELECT count(*) FROM ".TB_PREF."manuf_drawing WHERE spool_out_time!='' and spool_code=".db_escape($spool_code)."";
  $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
  
}

function add_drawing_coil_details($woid,$date_,$shift_id,$machine_id,$operator_id,$supervisor_id,$stock_id,$coil_no,$heat_no,$qty,$used_coil_qty,$spool_code,$coil_in_time,$coil_out_time,$remarks)
{
  $tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_drawing_coil_details (woid,tran_date,shift_id,machine_id,operator_id,supervisor_id,stock_id,coil_no,heat_no,pickled_qty,qty,spool_code,coil_in_time,coil_out_time,remarks,parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).",".db_escape($machine_id).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($stock_id).", ".db_escape($coil_no).", ".db_escape($heat_no).", "
    	.db_escape($qty).", ".db_escape($used_coil_qty).",
		".db_escape($spool_code).", ".db_escape($coil_in_time).", ".db_escape($coil_out_time).", ".db_escape($remarks).",
		".db_escape($woid).")";
		
	db_query($sql, "could not add work order");
	
	
	$sql = "UPDATE ".TB_PREF."manuf_pickling SET used_qty=used_qty+".db_escape($used_coil_qty)." 
		WHERE coil_no = ".db_escape($coil_no)."and stock_id=".db_escape($stock_id)." and heat_no= ".db_escape($heat_no)."";
		
	db_query($sql, "could not update_stock_moves)");	
}

function add_or_update_spool_details($woid,$spool_code,$spool_id,$size_id,$produced_length,$actual_spool_qty,$spool_in_time,$spool_out_time,$no_of_weld_joints,$weld_joint_meters)
{
	if(spool_exist($spool_code))
	{
		$sql="UPDATE ".TB_PREF."manuf_drawing SET spool_id=".db_escape($spool_id).",size_id=".db_escape($size_id).",produced_length=".db_escape($produced_length).",qty=".db_escape($actual_spool_qty).",spool_in_time=".db_escape($spool_in_time).",spool_out_time=".db_escape($spool_out_time).",no_of_weld_joints=".db_escape($no_of_weld_joints).",weld_joint_meters=".db_escape($weld_joint_meters)." WHERE spool_code=".db_escape($spool_code)."";

		db_query($sql, "could not add work order");
	}
	else
	{
	   $sql = "INSERT INTO ".TB_PREF."manuf_drawing(woid,spool_code,spool_id,size_id,produced_length,qty,spool_in_time,spool_out_time,no_of_weld_joints, weld_joint_meters, parent_woid) VALUES (".db_escape($woid).",".db_escape($spool_code).",".db_escape($spool_id).",".db_escape($size_id).",".db_escape($produced_length).",".db_escape($actual_spool_qty).",".db_escape($spool_in_time).",".db_escape($spool_out_time).",".db_escape($no_of_weld_joints).",".db_escape($weld_joint_meters).",
		".db_escape($woid).")";
 
    	db_query($sql, "could not add work order");
	}
}

function get_spool_details($spool_code,$woid)
{
	$sql = "SELECT drawing.*,size.size from ".TB_PREF."manuf_drawing drawing,".TB_PREF."size_master size where drawing.size_id=size.id and drawing.spool_code=".db_escape($spool_code)." and drawing.woid=".db_escape($woid)."";
	
	$result = db_query($sql, "could not query work order");
	$row = db_fetch($result);
	return $row;
}

function get_drawing_items_summary($woid)
{
    $sql = "SELECT drawing.*,size.size FROM ".TB_PREF."manuf_drawing drawing,".TB_PREF."size_master size WHERE drawing.size_id=size.id and woid=".db_escape($woid)." group by spool_code ";
	
	$result = db_query($sql, "The work order could not be retrieved");

	return $result;
}

function add_lrpc_details($woid,$date_,$shift_id,$lrpc_line_no,$operator_id,$supervisor_id,$core_wire,$outer1,$outer2,$outer3,$outer4,$outer5,$outer6,$length_in_mtr,$qty,$take_up_no,$set_no,$machine_start_time,$machine_stop_time,$reason,$rejected_spool_code,$remarks)
{
    $tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_lrpc_details (woid,tran_date,shift_id,lrpc_line_no,operator_id,supervisor_id,core_wire,outer1,outer2, outer3,outer4,outer5,outer6,length_in_mtr,qty,set_no,take_up_no,machine_start_time,machine_stop_time,reason,rejected_spool_code,remarks, parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).",".db_escape($lrpc_line_no).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($core_wire).", ".db_escape($outer1).", ".db_escape($outer2).",".db_escape($outer3).",".db_escape($outer4).",".db_escape($outer5).",".db_escape($outer6).", "
    	.db_escape($length_in_mtr).", ".db_escape($qty).",
		".db_escape($set_no).",".db_escape($take_up_no).", ".db_escape($machine_start_time).", ".db_escape($machine_stop_time).",".db_escape($reason).",".db_escape($rejected_spool_code).", ".db_escape($remarks).",
		".db_escape($woid).")";
		
	db_query($sql, "could not add work order");
	
}
	

function set_no_exist($set_no)
{
  $sql="SELECT count(*) FROM ".TB_PREF."manuf_lrpc WHERE set_no=".db_escape($set_no)."";
  $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
}

function get_fg_size($woid)
{
  $sql="SELECT sm.size_id FROM ".TB_PREF."stock_master sm,".TB_PREF."workorders wo WHERE sm.stock_id=wo.stock_id and wo.id=".db_escape($woid);
  $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
}	
 

function add_or_update_lrpc($woid,$set_no,$lrpc_line_no,$take_up_no,$length_in_mtr,$qty,$loading_start_time,$loading_end_time,$reason)
{
	if(set_no_exist($set_no))
	{
		$sql="UPDATE ".TB_PREF."manuf_lrpc SET lrpc_line_no=".db_escape($lrpc_line_no).",take_up_no=".db_escape($take_up_no).",length_in_mtr=".db_escape($length_in_mtr).",qty=".db_escape($qty).",loading_start_time=".db_escape($loading_start_time).",loading_end_time=".db_escape($loading_end_time).",reason=".db_escape($reason)." WHERE set_no=".db_escape($set_no)."";

		db_query($sql, "could not add work order");
	}
	else
	{
	
	   $fg_size=get_fg_size($woid);
	
	   $sql = "INSERT INTO ".TB_PREF."manuf_lrpc(woid,set_no,lrpc_line_no,take_up_no,length_in_mtr,qty,loading_start_time,loading_end_time,reason, parent_woid,size_id) VALUES (".db_escape($woid).",".db_escape($set_no).",".db_escape($lrpc_line_no).",".db_escape($take_up_no).",".db_escape($length_in_mtr).",".db_escape($qty).",".db_escape($loading_start_time).",".db_escape($loading_end_time).",".db_escape($reason).",
		".db_escape($woid).",".db_escape($fg_size).")";
		
 
    	db_query($sql, "could not add work order");
	}
}

function get_lrpc_items_summary($woid)
{
    $sql = "SELECT lrpc.*,size.size FROM ".TB_PREF."manuf_lrpc AS lrpc LEFT JOIN ".TB_PREF."size_master AS size ON lrpc.size_id=size.id  WHERE  woid=".db_escape($woid)." group by set_no";
	
	$result = db_query($sql, "The work order could not be retrieved");

	return $result;
}

function get_set_no_details($set_no,$woid)
{
$sql = "SELECT lrpc.*,size.size FROM ".TB_PREF."manuf_lrpc lrpc,".TB_PREF."size_master size WHERE lrpc.size_id=size.id and  lrpc.set_no=".db_escape($set_no)." and lrpc.woid=".db_escape($woid)."";

	$result = db_query($sql, "could not query work order");
	$row = db_fetch($result);
	return $row;
}


function add_laywer_winder_details($woid,$date_,$shift_id,$layer_winder_no,$operator_id,$supervisor_id,$set_no,$take_up_no,$size_id,$length_in_mtr,$qty,$winder_coil_no,$start_time,$stop_time,$coil_status,$remarks)
{

    $tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_layer_winder (woid,tran_date,shift_id,layer_winder_no,operator_id,supervisor_id,set_no,take_up_no,size_id, length_in_mtr,qty,winder_coil_no,start_time,stop_time,coil_status,remarks, parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).",".db_escape($layer_winder_no).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($set_no).", ".db_escape($take_up_no).",".db_escape($size_id).", "
    	.db_escape($length_in_mtr).", ".db_escape($qty).",
		".db_escape($winder_coil_no).",".db_escape($start_time).", ".db_escape($stop_time).",".db_escape($coil_status).", ".db_escape($remarks).",
		".db_escape($woid).")";
		
	
	db_query($sql, "could not add work order");
	
	
	$sql = "UPDATE ".TB_PREF."manuf_lrpc SET used_length=used_length+".db_escape($length_in_mtr)." 
		WHERE set_no = ".db_escape($set_no)."";
		
	db_query($sql, "could not update_stock_moves)");
	
}

function winder_coil_no_exist($winder_coil_no)
{
   $sql="SELECT count(*) FROM ".TB_PREF."manuf_layer_winder WHERE winder_coil_no=".db_escape($winder_coil_no)."";
  $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
}

function get_layer_winder_coils($woid)
{
	$sql = "SELECT winder.*,size.size FROM ".TB_PREF."manuf_layer_winder winder,".TB_PREF."size_master size WHERE winder.size_id=size.id and  winder.woid=".db_escape($woid)."";
	$result = db_query($sql, "could not query work order");	
	return $result;
}

function get_layer_winder_coil_details($coil_no,$woid)
{
	$sql="SELECT size.size,lw.*,sm.product_class FROM ".TB_PREF."manuf_layer_winder lw,".TB_PREF."size_master size,".TB_PREF."stock_master sm,".TB_PREF."workorders wo WHERE lw.woid=wo.id and lw.size_id=size.id and wo.stock_id=sm.stock_id and lw.winder_coil_no=".db_escape($coil_no)." and lw.woid=".db_escape($woid);
	
	$result = db_query($sql, "could not query work order");
	$row = db_fetch($result);
	return $row;
}

function add_oiling_packing_details($woid,$date_,$shift_id,$oil_tank_no,$operator_id,$supervisor_id,$coil_no,$size_id,$product_class,$net_qty,$gross_qty,$oiling_status,$coil_surface_condition_status,$final_packing_status,$remarks)
{
    $tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_oiling_packing (woid,tran_date,shift_id,oil_tank_no,operator_id,supervisor_id,coil_no,size_id, product_class,net_qty,gross_qty,oiling_status,coil_surface_condition_status,final_packing_status,remarks, parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).",".db_escape($oil_tank_no).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($coil_no).",".db_escape($size_id).", "
    	.db_escape($product_class).", ".db_escape($net_qty).", ".db_escape($gross_qty).",
		".db_escape($oiling_status).",".db_escape($coil_surface_condition_status).", ".db_escape($final_packing_status).", ".db_escape($remarks).",
		".db_escape($woid).")";
			
	db_query($sql, "could not add work order");
}

function get_oiling_packing_coils($woid)
{
	$sql = "SELECT oiling_packing.*,size.size FROM ".TB_PREF."manuf_oiling_packing oiling_packing,".TB_PREF."size_master size WHERE oiling_packing.size_id=size.id and  oiling_packing.woid=".db_escape($woid)."";
	$result = db_query($sql, "could not query work order");	
	return $result;
}

// spring wire
function get_drawing_coil_details($coil_no,$woid)
{
	$sql="SELECT size.size,drawing.size_id,sm.product_class FROM ".TB_PREF."manuf_drawing drawing,".TB_PREF."size_master size,".TB_PREF."stock_master sm,".TB_PREF."workorders wo WHERE drawing.woid=wo.id and drawing.size_id=size.id and wo.stock_id=sm.stock_id and drawing.spool_code=".db_escape($coil_no)." and drawing.woid=".db_escape($woid);
	$result = db_query($sql, "could not query work order");
	$row = db_fetch($result);
	return $row;
}

//galvanised wire
function galvanised_coil_no_exist($galvanised_coil_no)
{
   $sql="SELECT count(*) FROM ".TB_PREF."manuf_galvanising WHERE coil_no=".db_escape($galvanised_coil_no)."";
     $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
}


function add_galvanising_details($woid,$date_,$shift_id,$machine_id,$operator_id,$supervisor_id,$rm_coil_no,$rm_size_id,$rm_length_in_mtr,$rm_qty,$rm_in_time,$rm_out_time,$coil_no,$size_id,$length_in_mtr,$qty,$coil_in_time,$coil_out_time,$remarks)
{
	
    $tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_galvanising (woid,tran_date,shift_id,machine_id,operator_id,supervisor_id,rm_coil_no,rm_size_id, rm_length_in_mtr,rm_qty,rm_in_time,rm_out_time,coil_no,size_id,length_in_mtr,qty,coil_in_time,coil_out_time,remarks, parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).",".db_escape($machine_id).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($rm_coil_no).", ".db_escape($rm_size_id).",".db_escape($rm_length_in_mtr).", "
    	.db_escape($rm_qty).", ".db_escape($rm_in_time).", ".db_escape($rm_out_time).",
		".db_escape($coil_no).",".db_escape($size_id).", ".db_escape($length_in_mtr).",".db_escape($qty).", ".db_escape($coil_in_time).", ".db_escape($coil_out_time).", ".db_escape($remarks).",
		".db_escape($woid).")";
	
    	db_query($sql, "could not add work order");
		
		$sql = "UPDATE ".TB_PREF."manuf_drawing SET used_length=used_length+".db_escape($length_in_mtr)." 
		WHERE spool_code = ".db_escape($rm_coil_no)."";
		
		db_query($sql, "could not update_stock_moves)");
}


function get_galvanised_coils($woid)
{
	$sql="SELECT coil_no,size.size,gal.length_in_mtr,gal.qty FROM ".TB_PREF."manuf_galvanising gal,".TB_PREF."size_master size WHERE gal.size_id=size.id and gal.woid=".db_escape($woid);
	
	$result = db_query($sql, "could not query work order");
	return $result;
}

function get_galvanised_coil_detatils($coil_no,$woid)
{
	$sql="SELECT gal.*,size.size FROM ".TB_PREF."manuf_galvanising gal,".TB_PREF."size_master size WHERE gal.size_id=size.id and gal.coil_no=".db_escape($coil_no)." and  gal.woid=".db_escape($woid);
	
	$result = db_query($sql, "could not query work order");
	return db_fetch($result);
}

function srfurnace_coil_no_exist($coil_no)
{
   $sql="SELECT count(*) FROM ".TB_PREF."manuf_srfurnace WHERE coil_no=".db_escape($coil_no)."";
  $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
}

function add_srfurnace_details($woid,$date_,$shift_id,$sr_furnace_no,$operator_id,$supervisor_id,$spool_code,$size_id,$coil_no,$length_in_mtr,$qty,$start_time,$stop_time,$machine_status,$remarks,$product_type=4)
{

    $tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_srfurnace (woid,tran_date,shift_id,sr_furnace_no,operator_id,supervisor_id,spool_code,size_id,coil_no, length_in_mtr,qty,start_time,stop_time,machine_status,remarks, parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).",".db_escape($sr_furnace_no).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($spool_code).",".db_escape($size_id).", ".db_escape($coil_no).", "
    	.db_escape($length_in_mtr).", ".db_escape($qty).",
		".db_escape($start_time).", ".db_escape($stop_time).",".db_escape($machine_status).", ".db_escape($remarks).",
		".db_escape($woid).")";
		
	
	db_query($sql, "could not add work order");
	
	if($product_type==4)
	$sql = "UPDATE ".TB_PREF."manuf_drawing SET used_length=used_length+".db_escape($length_in_mtr)." 
		WHERE spool_code = ".db_escape($spool_code)."";
	if($product_type==5)
    $sql = "UPDATE ".TB_PREF."manuf_lrpc SET used_length=used_length+".db_escape($length_in_mtr)." 
		WHERE set_no = ".db_escape($spool_code)."";
		
		
	db_query($sql, "could not update_stock_moves)");
	
}

function get_srfurnaced_coils($woid)
{
	$sql = "SELECT furnace.*,size.size FROM ".TB_PREF."manuf_srfurnace furnace,".TB_PREF."size_master size WHERE furnace.size_id=size.id and  furnace.woid=".db_escape($woid)."";
	$result = db_query($sql, "could not query work order");	
	return $result;
}

function add_extruder_details($woid,$date_,$shift_id,$extruder_machine_no,$operator_id,$supervisor_id,$rm_coil_no,$rm_length,$rm_size_id,$rm_qty,$in_time,$out_time,$extruder_coil_no,$take_up_no,$size_id,$length_in_mtr,$qty,$steel_qty,$grease_qty,$hdpe_qty,$remarks)
{
    $tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_extruder (woid,tran_date,shift_id,extruder_machine_no,operator_id,supervisor_id,rm_coil_no,rm_length,rm_size_id,rm_qty,in_time,out_time,extruder_coil_no,take_up_no,size_id,length_in_mtr,qty,steel_qty,grease_qty,hdpe_qty,remarks, parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).",".db_escape($extruder_machine_no).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($rm_coil_no).", ".db_escape($rm_length).",".db_escape($rm_size_id).", "
    	.db_escape($rm_qty).", ".db_escape($in_time).",
		".db_escape($out_time).",".db_escape($extruder_coil_no).",".db_escape($take_up_no).",  ".db_escape($size_id).",".db_escape($length_in_mtr).",".db_escape($qty).",".db_escape($steel_qty).",".db_escape($grease_qty).",".db_escape($hdpe_qty).", ".db_escape($remarks).",
		".db_escape($woid).")";
		
	
	db_query($sql, "could not add work order");
	
	
	$sql = "UPDATE ".TB_PREF."manuf_layer_winder SET used_length=used_length+".db_escape($rm_length)." 
		WHERE winder_coil_no = ".db_escape($rm_coil_no)."";
		
	db_query($sql, "could not update_stock_moves)");
	
}
function get_extruder_coils($woid)
{
	$sql = "SELECT extruder.*,size.size FROM ".TB_PREF."manuf_extruder extruder,".TB_PREF."size_master size WHERE extruder.size_id=size.id and  extruder.woid=".db_escape($woid)."";
	$result = db_query($sql, "could not query work order");	
	return $result;
}

function get_extruder_coil_details($extruder_coil_no,$woid)
{
	$sql = "SELECT extruder.*,size.size FROM ".TB_PREF."manuf_extruder extruder,".TB_PREF."size_master size WHERE extruder.size_id=size.id and  extruder.woid=".db_escape($woid)." and extruder.extruder_coil_no=".db_escape($extruder_coil_no)."";
	$result = db_query($sql, "could not query work order");	
	$row = db_fetch($result);
	return $row;
}

function extruder_coil_no_exist($extruder_coil_no)
{
   $sql="SELECT count(*) FROM ".TB_PREF."manuf_extruder WHERE extruder_coil_no=".db_escape($extruder_coil_no)."";
  $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
}




function add_extrudercoiling_details($woid,$date_,$shift_id,$coiler_no,$operator_id,$supervisor_id,$rm_coil_no,$rm_take_up_no,$rm_length,$rm_size_id,$rm_qty,$in_time,$out_time,$extrudercoiling_coil_no,$size_id,$length_in_mtr,$qty,$steel_qty,$grease_qty,$hdpe_qty,$remarks)
{
    $tran_date = date2sql($date_);
	$sql = "INSERT INTO ".TB_PREF."manuf_extruder_coiling (woid,tran_date,shift_id,coiler_no,operator_id,supervisor_id,rm_coil_no,rm_take_up_no,rm_length,rm_size_id,rm_qty,in_time,out_time,extrudercoiling_coil_no,size_id,length_in_mtr,qty,steel_qty,grease_qty,hdpe_qty,remarks, parent_woid)
    	VALUES (".db_escape($woid).", ".db_escape($tran_date).", "
    	.db_escape($shift_id).",".db_escape($coiler_no).", ".db_escape($operator_id).",
		".db_escape($supervisor_id).", ".db_escape($rm_coil_no).", ".db_escape($rm_take_up_no).", ".db_escape($rm_length).",".db_escape($rm_size_id).", "
    	.db_escape($rm_qty).", ".db_escape($in_time).",
		".db_escape($out_time).",".db_escape($extrudercoiling_coil_no).",  ".db_escape($size_id).",".db_escape($length_in_mtr).",".db_escape($qty).",".db_escape($steel_qty).",".db_escape($grease_qty).",".db_escape($hdpe_qty).", ".db_escape($remarks).",
		".db_escape($woid).")";
		
	
	db_query($sql, "could not add work order");
	
	
	$sql = "UPDATE ".TB_PREF."manuf_extruder SET used_length=used_length+".db_escape($rm_length)." 
		WHERE extruder_coil_no = ".db_escape($rm_coil_no)."";
		
	db_query($sql, "could not update_stock_moves)");
	
}
function get_extrudercoiling_coils($woid)
{
	$sql = "SELECT coiling.*,size.size FROM ".TB_PREF."manuf_extruder_coiling coiling,".TB_PREF."size_master size WHERE coiling.size_id=size.id and  coiling.woid=".db_escape($woid)."";
	$result = db_query($sql, "could not query work order");	
	return $result;
}

function extrudercoiling_coil_no_exist($extrudercoiling_coil_no)
{
   $sql="SELECT count(*) FROM ".TB_PREF."manuf_extruder_coiling WHERE extrudercoiling_coil_no=".db_escape($extrudercoiling_coil_no)."";
  $result = db_query($sql, "could not query work order");
	$row = db_fetch_row($result);
	return $row[0];
}