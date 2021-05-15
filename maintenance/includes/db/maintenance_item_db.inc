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

function add_maintenance_items($items, $location, $date_, $reference, $memo_,$item_typ='',$loc_cat='',$loc_subcat='',$loc_subsubcat='',$schedule_id='')
{

	global $loc_notification, $path_to_root, $Refs;
  
	begin_transaction();
	$args = func_get_args();
	$args = (object)array_combine(array('items', 'location', 'date_', 'item_typ',
		'reference', 'memo_','loc_cat','loc_subcat','loc_subsubcat','schedule_id'), $args);
	$args->trans_no = 0;
	hook_db_prewrite($args, ST_MAINTISSUES);

	$adj_id = get_next_trans_no(ST_MAINTISSUES);

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
			$line_item->quantity,$line_item->standard_cost,$memo_,$item_type,$loc_cat,$loc_subcat,$loc_subsubcat,$schedule_id);
	}
	//update_service_process_status($visit_id);
	add_comments(ST_MAINTISSUES, $adj_id, $date_, $memo_);

	$Refs->save(ST_MAINTISSUES, $adj_id, $reference);
	add_audit_trail(ST_MAINTISSUES, $adj_id, $date_);

	$args->trans_no = $adj_id;
	hook_db_postwrite($args, ST_MAINTISSUES);
	commit_transaction();
	if ($loc_notification == 1 && !$increase && count($st_ids) > 0)
		send_reorder_email($loc, $st_ids, $st_names, $st_num, $st_reorder);

	return $adj_id;
}
function add_stock_adjustment_item1($adj_id, $stock_id, $location, $date_,$reference,
	$quantity, $standard_cost,$memo_,$item_type="",$loc_cat='',$loc_subcat='',$loc_subsubcat='',$schedule_id)
{

	$mb_flag = get_mb_flag($stock_id);

    if (is_service($mb_flag))
    {
    	display_db_error("Cannot do inventory adjustment for Service item : $stock_id", "");
    }

	add_stock_move(ST_MAINTISSUES, $stock_id, $adj_id, $location,
        $date_, $reference, $quantity, $standard_cost,'',$item_type,$schedule_id,'','',$loc_cat,$loc_subcat,'','','',$loc_subsubcat,'','');


}

function get_maintenance_items($trans_no)
{
    
	$result = get_stock_moves(ST_MAINTISSUES, $trans_no);
	
	// display_error($result);

	if (db_num_rows($result) == 0)
	{
		return null;
	}

	return $result;
}

?>