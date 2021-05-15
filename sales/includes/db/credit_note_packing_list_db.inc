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
//--------------------------------------------------------------------------------------
include_once($path_to_root . "/includes/db/inventory_db.inc");

function add_credit_packing_list_coil_number($selected_parent, $coil_number, $no_of_packages, $remarks,$process_date)
{
	$sql = "INSERT INTO ".TB_PREF."credit_note_packing_list_items (sales_item_id, coil_number, no_of_packages, remarks,date_)
		VALUES (".db_escape($selected_parent).", ".db_escape($coil_number) . ","
		.db_escape($no_of_packages) . ", ".db_escape($remarks) . ",".db_escape($process_date).")";
   //display_error($sql); die;
	db_query($sql,"check failed");
}

function update_credit_packing_list_coil_number($selected_roll_number, $coil_number, $no_of_packages,$remarks,$process_date)
{
	$sql = "UPDATE ".TB_PREF."credit_note_packing_list_items SET coil_number=".db_escape($coil_number)
	 . ",no_of_packages=".db_escape($no_of_packages).",
		remarks=".db_escape($remarks).",
		date_=".db_escape($process_date)."
		WHERE id=".db_escape($selected_roll_number);
		
	check_db_error("Could not update this bom component", $sql);

	db_query($sql,"could not update bom");
}

function get_credit_packing_list_coil_nos($item)
{
	$sql = "SELECT * from ".TB_PREF."credit_note_packing_list_items where sales_item_id=".db_escape($item);
	
	return db_query($sql, "The Packing list roll numbers could not be retrieved");
}

function get_credit_packing_list_item_coil_no($selected_id)
{
	$sql = "SELECT * from ".TB_PREF."credit_note_packing_list_items where id=".db_escape($selected_id);

	$result = db_query($sql, "could not get bom");
	return db_fetch($result);
}


function delete_credit_packing_list_coil_no($selected_id)
{
	$sql = "DELETE FROM ".TB_PREF."credit_note_packing_list_items WHERE id=".db_escape($selected_id);
	db_query($sql,"Could not delete this bom components");
}



function update_final_submit_sales_credit_packing($selected_parent)
{
	$grn_sql="SELECT items.*,trans.* FROM ".TB_PREF."debtor_trans_details as items LEFT JOIN ".TB_PREF."debtor_trans as trans ON items.debtor_trans_no=trans.trans_no WHERE items.id=".db_escape($selected_parent)." AND trans.type=11 ";
	
	$grn_res=db_query($grn_sql,"Could not get the grn details!");
	$grn_results=db_fetch($grn_res);
	
	$pack_sql="SELECT * FROM ".TB_PREF."credit_note_packing_list_items WHERE sales_item_id=".db_escape($selected_parent)."";
	$pack_res=db_query($pack_sql,"Could not get the packing details!");
	
		while($packing_results =db_fetch($pack_res)){
		// ramesh Stockmoves jumbo roll status
		
		add_stock_move(11,$grn_results['stock_id'],$grn_results['debtor_trans_no'],$grn_results['del_loc'],sql2date($grn_results['tran_date']), $grn_results['reference'],$packing_results['no_of_packages'],0,$grn_results['unit_price'],$packing_results['coil_number']); 
		 
		
		}
	 $sql = "UPDATE ".TB_PREF."debtor_trans_details SET packing_list_status=1	WHERE id=".db_escape($selected_parent);
	check_db_error("Could not update this paking list update status", $sql);

	db_query($sql,"could not update paking list update status!"); 

}
 

function get_total_no_of_packages_in_credit_note($grn_item_id)
{
	$sql="SELECT number_of_packages FROM ".TB_PREF."debtor_trans_details WHERE id=".db_escape($grn_item_id)."";
	
	$res=db_query($sql,"Could not get the item total sqm!");
	 $result=db_fetch($res);
	 return $result['number_of_packages'];
}




function get_credit_packing_list_item_code($selected_parent)
{
	$grn_sql="SELECT items.*,trans.* FROM ".TB_PREF."debtor_trans_details as items LEFT JOIN ".TB_PREF."debtor_trans as trans ON items.debtor_trans_no=trans.trans_no WHERE items.id=".db_escape($selected_parent)." AND trans.type=11";
	
	$grn_res=db_query($grn_sql,"Could not get the grn details!");
	return $grn_results=db_fetch($grn_res);
}

function get_credit_packing_list_total_weight($grn_item_id)
{
	$sql ="SELECT sum(no_of_packages)as packing_qty  FROM ".TB_PREF."credit_note_packing_list_items WHERE sales_item_id=".db_escape($grn_item_id)."";
	//display_error($sql);
	$res=db_query($sql,"Could not get the item total sqm!");
	 $result=db_fetch($res);
	 return $result['packing_qty'];
}

function check_exist_credit_packing_list_Coil_no_in_stock($coil_number)
{
$sql="SELECT count(*) FROM ".TB_PREF."stock_moves WHERE coil_no=".db_escape($coil_number)."";
//display_error($sql);
$result=db_query($sql);
$row=db_fetch_row($result);
return $row[0];
}  


//coil no unique
function check_exist_sales_credit_packing_list_coil_no($coil_number)
{
$sql="SELECT count(*) FROM ".TB_PREF."credit_note_packing_list_items WHERE coil_number=".db_escape($coil_number)."";
//display_error($sql);
$result=db_query($sql);
$row=db_fetch_row($result);
return $row[0];
}  

//coil no info from stock moves
function get_credit_coil_number_info_from_stock_moves($coil_no)
{
	$sql="SELECT * FROM ".TB_PREF."stock_moves WHERE coil_no=".db_escape($coil_no)." HAVING sum(qty)>0";
	//display_error($sql);
	$res=db_query($sql,"Could not get the roll/bale Info!");
	return  $result=db_fetch($res);
}