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
function add_machine_frequency($selected_id, $mac_fre,$description,$days)
{
//display_error($loc_sub_id);die;
    if($selected_id!='')
		$sql = "UPDATE ".TB_PREF."machine_frequency SET
	 	mac_fre = ".db_escape($mac_fre).",
	  	days = ".db_escape($days).",
	  	description = ".db_escape($description)."
        	WHERE id = ".db_escape($selected_id);
    else
		$sql = "INSERT INTO ".TB_PREF."machine_frequency
			(mac_fre, description,days) VALUES( ".db_escape($mac_fre).",
	  		 ".db_escape($description).",".db_escape($days).")";

	db_query($sql,"an machine frequency could not be updated");
}


function delete_machine_frequency_category($selected_id)
{
	$sql="DELETE FROM ".TB_PREF."machine_frequency WHERE id=".db_escape($selected_id);
	
	db_query($sql,"an machine frequency  could not be deleted");
}
function get_machine_frequency_category($selected_id)
{
	$sql="SELECT * FROM ".TB_PREF."machine_frequency WHERE id=".db_escape($selected_id);

	$result = db_query($sql,"an machine frequency  could not be retrieved");
	 
	return db_fetch($result);
	
	
}
function get_all_machine_frequency_categories($all=false) {
    $sql = "SELECT *  FROM ".TB_PREF."machine_frequency";
	if (!$all) $sql .= " WHERE !inactive";
	
	return  db_query($sql, "could not get machine frequencie");
}
