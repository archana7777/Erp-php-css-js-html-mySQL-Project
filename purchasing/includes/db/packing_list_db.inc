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



function add_packing_list_grn_no($selected_parent, $coil_number, $length,$heat_no,$remarks,$no_of_packs,$packing_date,$weight)
{

	$sql = "INSERT INTO ".TB_PREF."grn_packing_list_items (po_item_id, coil_number, length, heat_no, remarks,no_of_packs,packing_date,weight)
		VALUES (".db_escape($selected_parent).", ".db_escape($coil_number) . ", ".db_escape($length) . ", ".db_escape($heat_no) . ", ".db_escape($remarks) . ",".db_escape($no_of_packs) . ",".db_escape($packing_date).",".db_escape($weight).")";
//display_error($sql); die;
	db_query($sql,"check failed");
}


function update_packing_list_grn_no($selected_roll_number, $coil_number, $length,$heat_no,$remarks,$weight)
{

	$sql = "UPDATE ".TB_PREF."grn_packing_list_items SET 
	coil_number=".db_escape($coil_number). ",
	length=".db_escape($length). ",
	heat_no=".db_escape($heat_no).",
	remarks=".db_escape($remarks). ",
	weight=".db_escape($weight)."
	WHERE id=".db_escape($selected_roll_number);
	check_db_error("Could not update this bom component", $sql);

	db_query($sql,"could not update bom");
}


function get_packing_list_grn_nos($item)
{
	$sql = "SELECT * from ".TB_PREF."grn_packing_list_items where po_item_id=".db_escape($item);
	
	return db_query($sql, "The Packing list roll numbers could not be retrieved");
}

function get_packing_list_item_grn($selected_id)
{
	$sql = "SELECT * from ".TB_PREF."grn_packing_list_items where id=".db_escape($selected_id);

	$result = db_query($sql, "could not get bom");
	return db_fetch($result);
}


function delete_packing_list($selected_id)
{
	$sql = "DELETE FROM ".TB_PREF."grn_packing_list_items WHERE id=".db_escape($selected_id);
	db_query($sql,"Could not delete this bom components");
}

function update_final_submit_purch_grn($selected_parent)
{

	$grn_sql="SELECT items.*,batch.* FROM ".TB_PREF."grn_items as items LEFT JOIN ".TB_PREF."grn_batch as batch ON items.grn_batch_id=batch.id WHERE items.id=".db_escape($selected_parent)." ";
	
	$grn_res=db_query($grn_sql,"Could not get the grn details!");
	$grn_results=db_fetch($grn_res);
	
	$pack_sql="SELECT * FROM ".TB_PREF."grn_packing_list_items WHERE po_item_id=".db_escape($selected_parent)."";
	
	$pack_res=db_query($pack_sql,"Could not get the packing details!");
	
	
		while($packing_results =db_fetch($pack_res))
		{
		//add_stock_move(25,$grn_results['item_code'],$grn_results['grn_batch_id'],$grn_results['loc_code'],sql2dat//e($grn_results['delivery_date']), //$grn_results['reference'],$packing_results['qty'],0,0,$packing_results['batch_code']); 
		
		}
	 $sql = "UPDATE ".TB_PREF."grn_items SET packing_list_status=1 WHERE id=".db_escape($selected_parent);
	check_db_error("Could not update this paking list update status", $sql);

	db_query($sql,"could not update paking list update status!"); 

} 


function get_total_qty_in_grn($grn_item_id)
{
	$sql="SELECT no_of_packages FROM ".TB_PREF."grn_items WHERE id=".db_escape($grn_item_id)."";
	
	$res=db_query($sql,"Could not get the item total sqm!");
	 $result=db_fetch($res);
	 return $result['no_of_packages'];
}

function get_packing_list_total_sqm($grn_item_id)
{
	$sql ="SELECT sum(length*width)as packing_sqm   FROM ".TB_PREF."purchased_packing_list_items WHERE po_item_id=".db_escape($grn_item_id)."";
	$res=db_query($sql,"Could not get the item total sqm!");
	 $result=db_fetch($res);
	 return $result['packing_sqm'];
}


function get_packing_list_items($grn_batch_id)
{
	$sql="SELECT items.item_code,items.description,pack.* FROM ".TB_PREF."purchased_packing_list_items as pack LEFT JOIN ".TB_PREF."grn_items as items ON items.id=pack.po_item_id LEFT JOIN ".TB_PREF."grn_batch as batch ON items.grn_batch_id=batch.id WHERE batch.id=".db_escape($grn_batch_id)." ";
	$res=db_query($sql,"Could not get the item total sqm!");
	return $res;
}
function get_packing_list_item_code($selected_parent)
{
	$grn_sql="SELECT items.*,batch.* FROM ".TB_PREF."grn_items as items LEFT JOIN ".TB_PREF."grn_batch as batch ON items.grn_batch_id=batch.id WHERE items.id=".db_escape($selected_parent)." ";
	
	$grn_res=db_query($grn_sql,"Could not get the grn details!");
	return $grn_results=db_fetch($grn_res);
}

function get_packing_list_total_packs($grn_item_id)
{
	$sql ="SELECT sum(no_of_packs)as packing_qty   FROM ".TB_PREF."grn_packing_list_items WHERE po_item_id=".db_escape($grn_item_id)."";
	//display_error($sql);
	$res=db_query($sql,"Could not get the item total sqm!");
	 $result=db_fetch($res);
	 return $result['packing_qty'];
}


//machine no unique
function check_exist_grn_packing_list_batch_no_in_stock($coil_no)
{
$sql="SELECT count(*) FROM ".TB_PREF."stock_moves WHERE coil_no=".db_escape($coil_no)."";

$result=db_query($sql);
$row=db_fetch_row($result);
return $row[0];
}  


//machine no unique
function check_exist_grn_packing_list_batch_code($coil_number,$id)
{
$sql="SELECT count(*) FROM ".TB_PREF."grn_packing_list_items WHERE coil_number=".db_escape($coil_number)."";

if($id != '-1'){
$sql .= " AND id != ".db_escape($id)."";
}
//display_error($sql);
$result=db_query($sql);
$row=db_fetch_row($result);
return $row[0];
}  


//GRN quantity
function get_total_received_qty_in_grn($grn_item_id)
{
	$sql="SELECT qty_recd FROM ".TB_PREF."grn_items WHERE id=".db_escape($grn_item_id)."";
	
	$res=db_query($sql,"Could not get the item total sqm!");
	 $result=db_fetch($res);
	 return $result['qty_recd'];
}

function get_packing_list_total_weight($grn_item_id,$id)
{
	$sql ="SELECT sum(weight)as packing_weight   FROM ".TB_PREF."grn_packing_list_items WHERE po_item_id=".db_escape($grn_item_id)."";
	
	//display_error($sql);
	$res=db_query($sql,"Could not get the item total sqm!");
	 $result=db_fetch($res);
	 return $result['packing_weight'];
}