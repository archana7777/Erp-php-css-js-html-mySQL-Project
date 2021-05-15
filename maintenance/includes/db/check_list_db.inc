
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

function add_checklists($equipment_id,$mac_fre_id,$details,$order_no,$input_req,$mc_problem_type)
{
   $sql = "INSERT INTO ".TB_PREF."machine_maintenance_checklists (equipment_id,machine_fre_id,details,order_no,input_req,mc_problem_type)
		VALUES (".db_escape($equipment_id).",".db_escape($mac_fre_id).",".db_escape($details).",".db_escape($order_no).",".db_escape($input_req).",".db_escape($mc_problem_type).")";
	db_query($sql, "could not add check list.!");
}
function get_check_lists($equipment_id,$machine_fre_id,$mc_problem_type)
{
 $sql = "SELECT * FROM ".TB_PREF."machine_maintenance_checklists  WHERE equipment_id=".db_escape($equipment_id)." AND machine_fre_id=".db_escape($machine_fre_id)." AND mc_problem_type=".db_escape($mc_problem_type)." ORDER BY order_no ASC ";
 
 	//if (!$all) $sql .= " WHERE !check list.inactive";
	return db_query($sql, "could not get all check list...!");
}
function get_check_list_edit($id)
{
	$sql = "SELECT * FROM ".TB_PREF."machine_maintenance_checklists WHERE id=".db_escape($id);

	$result = db_query($sql, "could not get check list...!");

	return db_fetch($result);
	
}

function getOrderNo($id)
{
	$sql = "SELECT * FROM ".TB_PREF."machine_maintenance_checklists WHERE id=".db_escape($id);

	$result = db_query($sql, "could not get check list...!");

	return db_fetch($result);
	
}

function update_check_lists($id, $equipment_id,$mac_fre_id, $details,$order_no,$input_req,$mc_problem_type)
{
	$sql = "UPDATE ".TB_PREF."machine_maintenance_checklists SET equipment_id=".db_escape($equipment_id).",machine_fre_id=".db_escape($mac_fre_id).", details=".db_escape($details).",order_no=".db_escape($order_no).",
	input_req=".db_escape($input_req).",mc_problem_type=".db_escape($mc_problem_type)."	WHERE id=".db_escape($id);
	//display_error($sql);
	db_query($sql, "could not update Check List...!");
}
function delete_check_list($id)
{
	$sql="DELETE FROM ".TB_PREF."machine_maintenance_checklists WHERE id=".db_escape($id);

	db_query($sql, "could not delete checklist!");
} 

function getvalid_order_no($equipment_id,$mac_fre_id,$order_no,$mc_problem_type)
{
   $sql="SELECT COUNT(*) FROM ".TB_PREF."machine_maintenance_checklists WHERE equipment_id=".db_escape($equipment_id)." AND machine_fre_id=".db_escape($mac_fre_id)." AND mc_problem_type =".db_escape($mc_problem_type)."AND order_no=".db_escape($order_no);
		//display_error($sql);
	$result = db_query($sql, "could not do check empty query");	
	$myrow = db_fetch_row($result);
		//display_error($$myrow[0]);die;
	return $myrow[0] > 0; 
}
function get_machine_details($machine_id,$mac_fre,$mc_problem_type)
{
$sql="select mc.details from ".TB_PREF."machine_maintenance_checklists mc,".TB_PREF."machine m where mc.equipment_id=m.mac_eqp and m.id=".db_escape($machine_id)." and mc.machine_fre_id=".db_escape($mac_fre)."
AND mc.mc_problem_type=".db_escape($mc_problem_type)."";
//display_error($sql);
return db_query($sql);
}


?>