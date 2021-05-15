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
function write_size($selected, $size, $description)
{
    if($selected!='')
		$sql = "UPDATE ".TB_PREF."size_master SET
	 	size = ".db_escape($size).",
	 	description = ".db_escape($description)."
		
        	WHERE id = ".db_escape($selected);
    else
		$sql = "INSERT INTO ".TB_PREF."size_master
			(size, description) VALUES( ".db_escape($size).",
	  		".db_escape($description).")";
		

	db_query($sql,"an item unit could not be updated");
}

function delete_size($unit)
{
	$sql="DELETE FROM ".TB_PREF."size_master WHERE id=".db_escape($unit);

	db_query($sql,"an unit of measure could not be deleted");
}

function get_size($unit)
{
	$sql="SELECT * FROM ".TB_PREF."size_master WHERE id=".db_escape($unit);

	$result = db_query($sql,"an unit of measure could not be retrieved");

	return db_fetch($result);
}
function is_unique_size($size,$selected_id)
{
if($selected_id =='')
{
    $sql = "SELECT size FROM ".TB_PREF."size_master WHERE size=".db_escape($size);
	$result = db_query($sql,"an item could not be retreived");

	return db_num_rows($result);
}
else
{
 
    $sql = "SELECT size FROM ".TB_PREF."size_master WHERE id!=".db_escape($selected_id)." and size=".db_escape($size);
	$result = db_query($sql,"an item could not be retreived");
	return db_num_rows($result);
}	
}

function get_size_non_unique()
{
	$sql="SELECT size, COUNT(*) FROM ".TB_PREF."size_master 
GROUP BY size HAVING COUNT(*) =1";

	

	$result = db_query($sql,"an unit of measure could not be retrieved");

	return db_fetch($result);
}



function get_all_size($all=false) {
    $sql = "SELECT * FROM ".TB_PREF."size_master";
	if (!$all) $sql .= " WHERE !inactive";
	$sql .= " ORDER BY id";
    return  db_query($sql, "could not get stock categories");
}


