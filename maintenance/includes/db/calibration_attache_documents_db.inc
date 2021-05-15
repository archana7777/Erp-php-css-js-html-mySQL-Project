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

//----------------------------------------------------------------------------------------

function add_calibration_attachment($machine_id,$trans_no,$description,$filename,$unique_name, $filesize, $filetype)
{  
	$date = date2sql(Today());
	$sql = "INSERT INTO ".TB_PREF."calibration_attachment (machine_id,trans_no,description, filename, unique_name,
		filesize, filetype, tran_date) VALUES (".db_escape($machine_id).",".db_escape($trans_no).",".db_escape($description).", "
		.db_escape($filename).", ".db_escape($unique_name).", ".db_escape($filesize)
		.", ".db_escape($filetype).", '$date')";
	
	db_query($sql, "Attachment could not be inserted");		
}
//----------------------------------------------------------------------------------------

function update_calibration_attachment($selected_id,$machine_id,$trans_no,$description,
	$filename, $unique_name, $filesize, $filetype)
{
	$date = date2sql(Today());
	$sql = "UPDATE ".TB_PREF."calibration_attachment SET
	    trans_no=".db_escape($trans_no).",
		machine_id=".db_escape($machine_id).",
		description=".db_escape($description).", ";
	if ($filename != "")
	{
		$sql .= "filename=".db_escape($filename).",
		unique_name=".db_escape($unique_name).",
		filesize=".db_escape($filesize).",
		filetype=".db_escape($filetype).",";
	}	
   
	$sql .= "tran_date='$date' WHERE id=".db_escape($selected_id);
	//display_error($sql);die;
	db_query($sql, "Attachment could not be updated");		
}

//----------------------------------------------------------------------------------------

function delete_calibration_attachment($id)
{
	$sql = "DELETE FROM ".TB_PREF."calibration_attachment WHERE id = ".db_escape($id);
	//display_error($sql);die;
	db_query($sql, "Could not delete attachment");
}

function get_calibration_attachment($id)
{
	$sql = "SELECT * FROM ".TB_PREF."calibration_attachment WHERE id=".db_escape($id);
	
	$result = db_query($sql, "Could not retrieve attachments");
	return db_fetch($result);
}

function check_transactin($id,$machine_id)
{
	$sql = "SELECT * FROM ".TB_PREF."machine_calibration WHERE id=".db_escape($id)." AND machine_id=".db_escape($machine_id)." ";
	
	$result = db_query($sql, "Could not retrieve attachments");
	$trans_data=db_fetch_row($result);
	return $trans_data[0];
}

