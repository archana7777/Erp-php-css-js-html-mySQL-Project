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
function add_machine_prevent_schedule($selected_id,$fiscal_year, $mac_code,$schedule_date,$remarks,$mac_fre,$mc_problem_type)
{
       // $schedule_date=date2sql($schedule_date);
    if($selected_id!='')
		$sql = "UPDATE ".TB_PREF."machine_maintenance_schedule SET
	 	machine_id = ".db_escape($mac_code).",
		remarks = ".db_escape($remarks).",
	  	schedule_date = ".db_escape($schedule_date).",
		mac_fre = ".db_escape($mac_fre).",
		mc_problem_type = ".db_escape($mc_problem_type)."
        	WHERE id = ".db_escape($selected_id);
			
			 
    else
		$sql = "INSERT INTO ".TB_PREF."machine_maintenance_schedule
			(fiscal_year_id, machine_id,schedule_date,remarks,mac_fre,mc_problem_type) VALUES( ".db_escape($fiscal_year).",
	  		 ".db_escape($mac_code).",".db_escape($schedule_date).",".db_escape($remarks).",".db_escape($mac_fre).",".db_escape($mc_problem_type).")";
     //display_error($sql); die;
	db_query($sql,"an machine equipment could not be updated");
}
function get_frequency_days($mac_fre)
{
	$sql="SELECT days FROM ".TB_PREF."machine_frequency WHERE id=".db_escape($mac_fre)." ";
	$res =db_query($sql,"Could not get the machine frequency days!");
	$result =db_fetch($res);
	return $result["days"];
	

}

function delete_prevent_schedule($selected_id)
{
	$sql="DELETE FROM ".TB_PREF."machine_maintenance_schedule WHERE id=".db_escape($selected_id);
	
	db_query($sql,"an machine equipment could not be deleted");
}
function get_prevent_schedule($selected_id)
{
	$sql="SELECT *,if(schedule_date='0000-00-00','',DATE_FORMAT(schedule_date,'%d/%m/%Y')) as schedule_date FROM ".TB_PREF."machine_maintenance_schedule WHERE id=".db_escape($selected_id);

	$result = db_query($sql,"an machine equipment could not be retrieved");
	 
	return db_fetch($result);
	
	
}
function get_all_prevent_schedule($fisca_year,$all=false,$machine_id) {
    $sql = "SELECT mps.*,meq.mac_eqp,mf.mac_fre,if(mps.schedule_date='0000-00-00','',DATE_FORMAT(mps.schedule_date,'%d/%m/%Y')) as schedule_date,m.mac_code  FROM ".TB_PREF."machine_maintenance_schedule as mps LEFT JOIN ".TB_PREF."machine as m ON m.id=mps.machine_id LEFT JOIN ".TB_PREF."machine_equipment as meq ON meq.id=m.mac_eqp LEFT JOIN ".TB_PREF."machine_frequency as mf ON mf.id=mps.mac_fre WHERE mps.fiscal_year_id=".db_escape($fisca_year)." AND mps.machine_id=".db_escape($machine_id)." ";
	if (!$all) $sql .= " AND !mps.inactive";
	
	//display_error($sql);
	return  db_query($sql, "could not get machine equipments");
	
}

function get_machin_name($selected_id)
{
	$sql="SELECT DISTINCT(meq.id) as id,meq.mac_eqp FROM ".TB_PREF."machine_equipment as meq JOIN ".TB_PREF."machine as m ON m.mac_eqp=meq.id JOIN ".TB_PREF."machine_maintenance_schedule as msp ON msp.machine_id=m.id WHERE m.id=".db_escape($selected_id);

	$result = db_query($sql,"an machine equipment could not be retrieved");
	if (db_num_rows($result) == 1)
	{
		$row = db_fetch_row($result);
		return $row[1];
	}
	display_db_error("could not retreive the location name for $selected_id", $sql, true);
	
}

function get_frequency_name($selected_id)
{
	$sql="SELECT DISTINCT(mf.id) as id, mf.mac_fre FROM ".TB_PREF."machine_frequency as mf JOIN ".TB_PREF."machine_maintenance_schedule as msp ON msp.mac_fre=mf.id WHERE mf.id=".db_escape($selected_id);
  
	$result = db_query($sql,"an machine equipment could not be retrieved");
	if (db_num_rows($result) == 1)
	{
		$row = db_fetch_row($result);
		return $row[1];
	}
	display_db_error("could not retreive the location name for $selected_id", $sql, true);
	
}

