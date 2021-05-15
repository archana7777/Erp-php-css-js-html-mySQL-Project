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
function add_machine($selected_id, $mac_code,$mac_eqp,$mac_make,$mac_cap,$mac_fre,$file_loc_cat,$file_location,$remarks,$mac_model_no,$wr_type,$wr_date,$supplier_id,$inst_date)
{


    if($wr_type!=4){
	$warranty_exp_date=date2sql($wr_date);
	}else{
	  $warranty_exp_date='';
	}
	$inst_date=date2sql($inst_date);
	
     
    if($selected_id!='')
		$sql = "UPDATE ".TB_PREF."machine SET
	 	mac_code = ".db_escape($mac_code).",
		mac_eqp = ".db_escape($mac_eqp).",
		mac_make = ".db_escape($mac_make).",
		mac_cap = ".db_escape($mac_cap).",
		mac_fre = ".db_escape($mac_fre).",
		file_loc_cat = ".db_escape($file_loc_cat).",
		file_location = ".db_escape($file_location).",
	  	remarks = ".db_escape($remarks).",
		mac_model_no = ".db_escape($mac_model_no).",
	    warranty_type = ".db_escape($wr_type).",
		warranty_exp_date = ".db_escape($warranty_exp_date).",
		supplier_id = ".db_escape($supplier_id).",
		inst_date = ".db_escape($inst_date)."
        WHERE id = ".db_escape($selected_id);
    else
		$sql = "INSERT INTO ".TB_PREF."machine(mac_code,mac_eqp,mac_make,mac_cap,mac_fre,file_loc_cat,file_location,remarks,mac_model_no,warranty_type,warranty_exp_date,supplier_id,inst_date) VALUES( ".db_escape($mac_code).",".db_escape($mac_eqp).",".db_escape($mac_make).",".db_escape($mac_cap).",".db_escape($mac_fre).",
			".db_escape($file_loc_cat).",".db_escape($file_location).",".db_escape($remarks).",".db_escape($mac_model_no).",".db_escape($wr_type).",".db_escape($warranty_exp_date).",".db_escape($supplier_id).",".db_escape($inst_date).")";
			
    //display_error($mac_cap);die;
	db_query($sql,"an machine  could not be updated");
}


function delete_machine($selected_id)
{
	$sql="DELETE FROM ".TB_PREF."machine WHERE id=".db_escape($selected_id);
	
	db_query($sql,"an machine could not be deleted");
}
function get_machine_category($selected_id)
{

	$sql="SELECT * FROM ".TB_PREF."machine WHERE id=".db_escape($selected_id);

	$result = db_query($sql,"an machine   could not be retrieved");
	 
	return db_fetch($result);
	
	
}
/*
function get_all_machine_categories($all=false) {

     $sql = "SELECT 
   location.location_name as location,loc_cat.description as cat_des,loc_subcat.description as sucat_des, loc_sub_sub.description as sub_sub_des,
machine.*,eqp.mac_eqp as mac_eqp,capacity.mac_cap aS mac_cap,make.mac_make as mac_make,GROUP_CONCAT(fre.mac_fre) AS mac_fre,if(machine.warranty_exp_date='0000-00-00','',DATE_FORMAT(machine.warranty_exp_date,'%m/%d/%Y')) AS warranty_exp_date,sp.supp_name
FROM
   ".TB_PREF."machine machine
INNER JOIN
    ".TB_PREF."locations location ON location.loc_code=machine.file_location
INNER JOIN
    ".TB_PREF."location_categories_db loc_cat ON loc_cat.id=machine.file_loc_cat
INNER JOIN
    ".TB_PREF."location_subcategories loc_subcat ON loc_subcat.id=machine.file_loc_subcat
INNER JOIN
    ".TB_PREF."location_sub_subcategories loc_sub_sub ON loc_sub_sub.id= machine.file_loc_subsubcat

INNER JOIN
    ".TB_PREF."machine_equipment eqp ON machine.mac_eqp=eqp.id
INNER JOIN
    ".TB_PREF."machine_frequency fre ON FIND_IN_SET(fre.id,machine.mac_fre)
INNER JOIN
   ".TB_PREF."machine_make make ON machine.mac_make=make.id
 INNER JOIN 
    suppliers sp ON machine.supplier_id=sp.supplier_id "; 
   if (!$all) $sql .= " WHERE !machine.inactive";
   
   $sql .=" group by machine.id";
  //display_error($sql);
	return  db_query($sql, "could not get machines");
	
}
*/
function get_all_machine_categories($all=false) {

     $sql = "SELECT machine.*,
   location.location_name as location,lc_grp.loc_group_name as location_group,
eqp.mac_eqp as mac_eqp,
capacity.mac_cap as mac_cap,
make.mac_make as mac_make
,sp.supp_name
FROM
   ".TB_PREF."machine machine
LEFT JOIN
    ".TB_PREF."location_groups lc_grp ON lc_grp.id=machine.file_loc_cat
LEFT JOIN
    ".TB_PREF."locations location ON location.loc_code=machine.file_location
LEFT JOIN
    ".TB_PREF."machine_capacity capacity ON machine.mac_cap=capacity.id
LEFT JOIN
    ".TB_PREF."machine_equipment eqp ON machine.mac_eqp=eqp.id

LEFT JOIN
   ".TB_PREF."machine_make make ON machine.mac_make=make.id
 LEFT JOIN 
    suppliers sp ON machine.supplier_id=sp.supplier_id "; 
  
  if (!$all) $sql .= " WHERE !machine.inactive";
   
  
   
	return  db_query($sql, "could not get machines");
}

function getvalid_machine_code($parameter)
{
	
     $sql="SELECT COUNT(*) FROM ".TB_PREF."machine WHERE mac_code=".db_escape($parameter);
		
	$result = db_query($sql, "could not do check empty query");	
	$myrow = db_fetch_row($result);
	return $myrow[0] > 0; 
}
function getvalid_machine_code_edit($parameter,$selected_id)
{
	  $sql="SELECT COUNT(*) FROM ".TB_PREF."machine WHERE mac_code=".db_escape($parameter)." and id !=".db_escape($selected_id)."";
		//display_error($sql);die;
	$result = db_query($sql, "could not do check empty query");	
	$myrow = db_fetch_row($result);
	return $myrow[0] > 0; 
}

function get_machine_frequency($maccode)
{
		$sql="SELECT GROUP_CONCAT(fre.mac_fre) AS mac_fre FROM ".TB_PREF."machine as machine LEFT JOIN ".TB_PREF."machine_frequency as fre ON FIND_IN_SET(fre.id,machine.mac_fre)
     
	WHERE machine.mac_code= '$maccode' ";

		$res=db_query($sql);
		$row=db_fetch_row($res);
		return $row["0"];

}