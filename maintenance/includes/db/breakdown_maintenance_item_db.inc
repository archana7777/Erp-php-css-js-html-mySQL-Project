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
//-------------------------------------------------------------------------------------------------------------

function add_breakdown_maintenance_items($items, $location, $date_, $reference, $memo_,$item_typ='',$loc_cat='',$loc_subcat='',$loc_subsubcat='',$brkd_req_id='')
{

	global $loc_notification, $path_to_root, $Refs;
  
	begin_transaction();
	$args = func_get_args();
	$args = (object)array_combine(array('items', 'location', 'date_', 'item_typ',
		'reference', 'memo_','loc_cat','loc_subcat','loc_subsubcat','brkd_req_id'), $args);
	$args->trans_no = 0;
	hook_db_prewrite($args, ST_BREAKMAINTISSUES);

	$adj_id = get_next_trans_no(ST_BREAKMAINTISSUES);

	if ($loc_notification == 1 && !$increase)
	{
		include_once($path_to_root . "/inventory/includes/inventory_db.inc");
		$st_ids = array();
		$st_names = array();
		$st_num = array();
		$st_reorder = array();
	}
	foreach ($items as $line_item)
	{
			//display_error($line_item->stock_id);die;
		if ($loc_notification == 1 && !$increase)
			$loc = calculate_reorder_level($location, $line_item, $st_ids, $st_names, $st_num, $st_reorder); 

		if (!$increase)
		
			$line_item->quantity = -$line_item->quantity;
		add_stock_adjustment_item1($adj_id, $line_item->stock_id,  $location, $date_, $reference,
			$line_item->quantity,$line_item->standard_cost,$memo_,$item_type,$loc_cat,$loc_subcat,$loc_subsubcat,$brkd_req_id);
			
	}
	//update_service_process_status($visit_id);
	add_comments(ST_BREAKMAINTISSUES, $adj_id, $date_, $memo_);

	$Refs->save(ST_BREAKMAINTISSUES, $adj_id, $reference);
	add_audit_trail(ST_BREAKMAINTISSUES, $adj_id, $date_);

	$args->trans_no = $adj_id;
	hook_db_postwrite($args, ST_BREAKMAINTISSUES);
	commit_transaction();
	if ($loc_notification == 1 && !$increase && count($st_ids) > 0)
		send_reorder_email($loc, $st_ids, $st_names, $st_num, $st_reorder);

	return $adj_id;
}
function add_stock_adjustment_item1($adj_id, $stock_id, $location, $date_,$reference,
	$quantity, $standard_cost,$memo_,$item_type="",$loc_cat='',$loc_subcat='',$loc_subsubcat='',$brkd_req_id)
{
	
	$mb_flag = get_mb_flag($stock_id);

    if (is_service($mb_flag))
    {
    	display_db_error("Cannot do inventory adjustment for Service item : $stock_id", "");
    }

	update_average_material_cost(null, $stock_id, $standard_cost, $quantity, $date_);

	if (is_fixed_asset($mb_flag)) {
		$sql = "UPDATE stock_master SET inactive=1
			WHERE stock_id=".db_escape($stock_id);
		db_query($sql,"The inactive flag for the fixed asset could not be updated");
	}
	//-----------------------------------------------------------------------------

	//display_error($quantity);
	add_stock_move(ST_BREAKMAINTISSUES, $stock_id, $adj_id, $location,
        $date_, $reference, $quantity, $standard_cost,'',$item_type,'','','',$loc_cat,$loc_subcat,'','','',$loc_subsubcat,'','','',$brkd_req_id);
		

	$inv_value = $standard_cost * $quantity;
	$adj_value = $standard_cost * -($quantity);

	if (is_fixed_asset($mb_flag)) {
		// get the initial value of the fixed assset.
		$row = get_fixed_asset_move($stock_id, ST_SUPPRECEIVE);
		$inv_value = $row['price'] * $quantity;
		$adj_value = (-($row['price']) + $standard_cost) * $quantity;
	}

	if ($standard_cost > 0 || is_fixed_asset($mb_flag))
	{

		$stock_gl_codes = get_stock_gl_code($stock_id);

		add_gl_trans_std_cost(ST_BREAKMAINTISSUES, $adj_id, $date_,
			$stock_gl_codes['adjustment_account'], $stock_gl_codes['dimension_id'], $stock_gl_codes['dimension2_id'], $memo_, $adj_value  );

		add_gl_trans_std_cost(ST_BREAKMAINTISSUES, $adj_id, $date_, $stock_gl_codes['inventory_account'], 0, 0, $memo_, $inv_value);
	}

	if (is_fixed_asset($mb_flag)) {
		// Additional gl entry for fixed asset.
		$grn_act = get_company_pref('default_loss_on_asset_disposal_act');
		add_gl_trans_std_cost(ST_BREAKMAINTISSUES, $adj_id, $date_, $grn_act, 0, 0, $memo_, ($standard_cost * -($quantity)));
	}
}

function get_breakdown_maintenance_items($trans_no)
{
	$result = get_stock_moves(ST_BREAKMAINTISSUES, $trans_no);

	if (db_num_rows($result) == 0)
	{
		return null;
	}

	return $result;
}
function get_machine_name_based_stock($machine_id)
{
	$sql = "SELECT m.id, CONCAT(meq.mac_eqp,'-',m.mac_code) as mac_eqp FROM ".TB_PREF."machine as m LEFT JOIN ".TB_PREF."machine_equipment as meq ON m.mac_eqp=meq.id WHERE m.id=".db_escape($machine_id);
	
	$result=db_query($sql);
	$myrow = db_fetch_row($result);
	return $myrow[1]; 
	
}


function get_machin_name($selected_id)
{
	$sql="SELECT DISTINCT(meq.id) as id,meq.mac_eqp FROM ".TB_PREF."machine_equipment as meq JOIN ".TB_PREF."machine as m ON m.mac_eqp=meq.id JOIN ".TB_PREF."machine_maintenance_schedule as msp ON msp.machine_id=m.id WHERE m.id=".db_escape($selected_id);
	$sql="SELECT DISTINCT(meq.id) as id,meq.mac_eqp FROM ".TB_PREF."machine_equipment as meq JOIN ".TB_PREF."machine as m ON m.mac_eqp=meq.id JOIN ".TB_PREF."machine_maintenance_schedule as msp ON msp.machine_id=m.id WHERE m.id=".db_escape($selected_id);
	display_error($sql);
	$result = db_query($sql,"an machine equipment could not be retrieved");
	if (db_num_rows($result) == 1)
	{
		$row = db_fetch_row($result);
		return $row[1];
	}
	display_db_error("could not retreive the location name for $selected_id", $sql, true);
	
}
?>