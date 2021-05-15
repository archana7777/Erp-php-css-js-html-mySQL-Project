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
function add_machine_calibration_entry($selected_id,$machine_id,$calibration_date,$validity_start_date,$validity_end_date,$remarks)
{
       $calibration_date=date2sql($calibration_date);
	   $validity_start_date=date2sql($validity_start_date);
	   $validity_end_date=date2sql($validity_end_date);
	   
    if($selected_id!='')
		$sql = "UPDATE ".TB_PREF."machine_calibration SET
	 	machine_id = ".db_escape($machine_id).",
		remarks = ".db_escape($remarks).",
	  	calibration_date = ".db_escape($calibration_date).",
		validity_start_date = ".db_escape($validity_start_date).",
		validity_end_date = ".db_escape($validity_end_date)."
        	WHERE id = ".db_escape($selected_id);

			else
		$sql = "INSERT INTO ".TB_PREF."machine_calibration
			(machine_id,calibration_date,validity_start_date,validity_end_date,remarks) VALUES(".db_escape($machine_id).",".db_escape($calibration_date).",".db_escape($validity_start_date).",".db_escape($validity_end_date).",".db_escape($remarks).")";
     //display_error($sql);die;
	db_query($sql,"an machine equipment could not be updated");
}


function delete_machine_calibration_entry($selected_id)
{
	$sql="DELETE FROM ".TB_PREF."machine_calibration WHERE id=".db_escape($selected_id);
	
	db_query($sql,"an machine equipment could not be deleted");
}
function get_machine_calibration_edit($selected_id)
{
	$sql="SELECT  mce.*,if(mce.calibration_date='0000-00-00','',DATE_FORMAT(mce.calibration_date,'%d/%m/%Y')) as calibration_date,if(mce.validity_start_date='0000-00-00','',DATE_FORMAT(mce.validity_start_date,'%d/%m/%Y')) as validity_start_date,if(mce.validity_end_date='0000-00-00','',DATE_FORMAT(mce.validity_end_date,'%d/%m/%Y')) as validity_end_date FROM ".TB_PREF."machine_calibration as mce WHERE mce.id=".db_escape($selected_id);
	
	$result = db_query($sql,"an machine equipment could not be retrieved");
	 
	return db_fetch($result);
	
	
}
function get_all_machine_calibration_entry($all=false) {

$sql = "SELECT mce.* ,if(mce.calibration_date='0000-00-00','',DATE_FORMAT(mce.calibration_date,'%d/%m/%Y')) as calibration_date,if(mce.validity_start_date='0000-00-00','',DATE_FORMAT(mce.validity_start_date,'%d/%m/%Y')) as validity_start_date,if(mce.validity_end_date='0000-00-00','',DATE_FORMAT(mce.validity_end_date,'%d/%m/%Y')) as validity_end_date,meq.mac_eqp,m.mac_code FROM ".TB_PREF."machine_calibration as mce LEFT JOIN ".TB_PREF."machine as m ON m.id=mce.machine_id LEFT JOIN ".TB_PREF."machine_equipment as meq ON meq.id=m.mac_eqp  WHERE 1=1";

if (!$all) $sql .= " AND !mce.inactive";

return  db_query($sql, "could not get machine equipments");
}



