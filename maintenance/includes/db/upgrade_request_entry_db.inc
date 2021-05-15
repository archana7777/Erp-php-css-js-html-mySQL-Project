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

function add_upgrade_req_entry($ref,$machine_id,$details)
{
$date=date('Y-m-d');
	$sql = "INSERT INTO ".TB_PREF."upgrade_req_entry(ref,machine_id,details,date)
		VALUES (".db_escape($ref).",".db_escape($machine_id).",".db_escape($details).",".db_escape($date).")";

	db_query($sql, "could not add upgrade req ent");
}
function get_all_upgrade_req_ent($machine_id)
{
$sql="SELECT up_ent.id,up_ent.ref, CONCAT(meq.mac_eqp,'-',m.mac_code) as mac_eqp,up_ent.details,up_ent.id as updated FROM ".TB_PREF."upgrade_req_entry up_ent LEFT JOIN ".TB_PREF."machine as m on up_ent.machine_id=m.id LEFT JOIN ".TB_PREF."machine_equipment as meq ON m.mac_eqp=meq.id where  up_ent.status!='2'";
if($machine_id!=-1){
$sql .="AND up_ent.machine_id=".db_escape($machine_id)."";
}
//display_error($sql);
return $sql;
}
function get_upgrade_details($id)
{
$sql="SELECT up_ent.id,up_ent.ref, CONCAT(meq.mac_eqp,'-',m.mac_code) as mac_eqp,up_ent.details,up_ent.id as updated FROM ".TB_PREF."machine as m LEFT JOIN ".TB_PREF."machine_equipment as meq ON m.mac_eqp=meq.id
left join ".TB_PREF."upgrade_req_entry up_ent on up_ent.machine_id=m.id
where up_ent.id=".db_escape($id)."";
$result = db_query($sql,"an schedule plan  could not be retrieved");
//display_error($sql);
	return db_fetch($result);
}
function update_upgrade_details($selected,$status)
{

 
   //display_error($completed_date);
    if($selected!='')
		$sql = "UPDATE ".TB_PREF."upgrade_req_entry SET
		
		status = ".db_escape($status)." WHERE id = ".db_escape($selected);

	db_query($sql,"data");
}

function add_upgrade_details($selected,$ref, $start_date,$end_date,$materials,$remarks,$status)
{

   $start_date=date2sql($start_date);
   $end_date=date2sql($end_date);
  
  $sql = "INSERT INTO ".TB_PREF."upgrade_request_details(selected_id,ref,start_date,end_date,materials,remarks,status)
		VALUES (".db_escape($selected).",".db_escape($ref).",".db_escape($start_date).",".db_escape($end_date).",".db_escape($materials).",".db_escape($remarks).",".db_escape($status).")";

	db_query($sql, "could not add date");
}