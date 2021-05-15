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
function write_spool($selected, $spool_number, $description)
{
    if($selected!='')
		$sql = "UPDATE ".TB_PREF."spool_list_master SET
	 	spool_number = ".db_escape($spool_number).",
	 	description = ".db_escape($description)."
		
        	WHERE id = ".db_escape($selected);
    else
		$sql = "INSERT INTO ".TB_PREF."spool_list_master
			(spool_number, description) VALUES( ".db_escape($spool_number).",
	  		".db_escape($description).")";
//display_error($sql);die;

	db_query($sql,"an item unit could not be updated");
}
function delete_spool($unit)
{
	$sql="DELETE FROM ".TB_PREF."spool_list_master WHERE id=".db_escape($unit);
	

	db_query($sql,"an unit of measure could not be deleted");
}
function get_all_spool($all=false) {
    $sql = "SELECT * FROM ".TB_PREF."spool_list_master";
	if (!$all) $sql .= " WHERE !inactive";
	$sql .= " ORDER BY id";
	//display_error($sql);die;
    return  db_query($sql, "could not get stock categories");
}
function get_spool($unit)
{
	$sql="SELECT * FROM ".TB_PREF."spool_list_master WHERE id=".db_escape($unit);
	
	
	$result = db_query($sql,"an unit of measure could not be retrieved");

	return db_fetch($result);
}
function is_unique_spool($spool_number,$selected_id)
{
if($selected_id =='')
{
    $sql = "SELECT spool_number FROM ".TB_PREF."spool_list_master WHERE spool_number=".db_escape($spool_number);
	$result = db_query($sql,"an item could not be retreived");

	return db_num_rows($result);
}
else
{
 //display_error("hie");
    $sql = "SELECT spool_number FROM ".TB_PREF."spool_list_master WHERE id!=".db_escape($selected_id)." and spool_number=".db_escape($spool_number);
	$result = db_query($sql,"an item could not be retreived");
	return db_num_rows($result);
}}