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
function write_grade($selected, $grade, $description)
{
    if($selected!='')
		$sql = "UPDATE ".TB_PREF."grade_master SET
	 	grade = ".db_escape($grade).",
	 	description = ".db_escape($description)."
		
        	WHERE id = ".db_escape($selected);
    else
		$sql = "INSERT INTO ".TB_PREF."grade_master
			(grade, description) VALUES( ".db_escape($grade).",
	  		".db_escape($description).")";
display_error($sql);die;

	db_query($sql,"an item unit could not be updated");
}
function is_unique_grade($grade,$selected_id)
{
if($selected_id =='')
{
    $sql = "SELECT grade FROM ".TB_PREF."grade_master WHERE grade=".db_escape($grade);
	$result = db_query($sql,"an item could not be retreived");

	return db_num_rows($result);
}
else
{
 
    $sql = "SELECT grade FROM ".TB_PREF."grade_master WHERE id!=".db_escape($selected_id)." and grade=".db_escape($grade);
	$result = db_query($sql,"an item could not be retreived");
	return db_num_rows($result);
}	
}
function delete_grade($unit)
{
	$sql="DELETE FROM ".TB_PREF."grade_master WHERE id=".db_escape($unit);
	

	db_query($sql,"an unit of measure could not be deleted");
}

function get_grade($unit)
{
	$sql="SELECT * FROM ".TB_PREF."grade_master WHERE id=".db_escape($unit);
	
	
	$result = db_query($sql,"an unit of measure could not be retrieved");

	return db_fetch($result);
}



function get_all_grade($all=false) {
    $sql = "SELECT * FROM ".TB_PREF."grade_master";
	if (!$all) $sql .= " WHERE !inactive";
	$sql .= " ORDER BY id";
    return  db_query($sql, "could not get stock categories");
}


