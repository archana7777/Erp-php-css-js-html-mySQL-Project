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
function add_machine_equipment($selected_id, $mac_eqp,$description,$mac_fre)
{


    if($selected_id!='')
		$sql = "UPDATE ".TB_PREF."machine_equipment SET
	 	mac_eqp = ".db_escape($mac_eqp).",
		mac_fre = ".db_escape($mac_fre).",
	  	description = ".db_escape($description)."
        	WHERE id = ".db_escape($selected_id);
    else
		$sql = "INSERT INTO ".TB_PREF."machine_equipment
			(mac_eqp, description,mac_fre) VALUES( ".db_escape($mac_eqp).",
	  		 ".db_escape($description).",
	  		 ".db_escape($mac_fre).")";

	db_query($sql,"an machine equipment could not be updated");
}


function delete_machine_equipment_category($selected_id)
{
	$sql="DELETE FROM ".TB_PREF."machine_equipment WHERE id=".db_escape($selected_id);
	
	db_query($sql,"an machine equipment could not be deleted");
}
function get_machine_equipment_category($selected_id)
{
	$sql="SELECT * FROM ".TB_PREF."machine_equipment WHERE id=".db_escape($selected_id);

	$result = db_query($sql,"an machine equipment could not be retrieved");
	 
	return db_fetch($result);
	
	
}
function get_all_machine_equipment_categories($all=false) {
    $sql = "SELECT *  FROM ".TB_PREF."machine_equipment";
	if (!$all) $sql .= " WHERE !inactive";
	
	return  db_query($sql, "could not get machine equipments");
	
}

function get_machine_equipement_frequency($maccode)
{
		$sql="SELECT GROUP_CONCAT(fre.mac_fre) AS mac_fre FROM ".TB_PREF."machine_equipment as eqp LEFT JOIN ".TB_PREF."machine_frequency as fre ON FIND_IN_SET(fre.id,eqp.mac_fre)
     
	WHERE eqp.id= '$maccode' ";

		$res=db_query($sql);
		$row=db_fetch_row($res);
		return $row["0"];

}
