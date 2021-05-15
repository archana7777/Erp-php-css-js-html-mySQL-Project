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
$page_security = 'SA_MACHINMAIN_REPORTS';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Outstanding GRNs Report
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");

include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//----------------------------------------------------------------------------------------------

print_outstanding_GRN();

function getTransactions($machine,$machine_eqp,$machine_make,$machine_capacity,$supplier_id,$location,$model_no="")
{
	
 $sql = "SELECT machine.*,
   location.location_name as location, 
eqp.mac_eqp as mac_eqp,
capacity.mac_cap aS mac_cap,
make.mac_make as mac_make,
case 
when machine.warranty_type='1' then 'Manufacturer Warranty'
when machine.warranty_type='2' then 'AMC'
when machine.warranty_type='3' then 'CMC'
when machine.warranty_type='4' then 'No Warranty'

end as warranty,sp.supp_name
FROM
   ".TB_PREF."machine machine
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
  
  if ($model_no != '')
   $sql .= " AND machine.mac_model_no LIKE ".db_escape('%' . $model_no . '%') ;

  
  if ($machine != '-1')
   $sql .= " AND machine.id =".db_escape($machine);

  if ($machine_eqp != '-1')
   $sql .= " AND machine.mac_eqp =".db_escape($machine_eqp);

  if ($machine_make != '-1')
   $sql .= " AND machine.mac_make =".db_escape($machine_make);

  if ($machine_capacity != '-1')
   $sql .= " AND machine.mac_cap =".db_escape($machine_capacity);

  if ($supplier_id != '')
   $sql .= " AND machine.supplier_id =".db_escape($supplier_id);

  

  if ($location != '')
   $sql .= " AND machine.file_location =".db_escape($location);

    return db_query($sql, "No transactions were returned");
}
function get_machine_name($machine_id)
{
	$sql="Select mac_code FROM ".TB_PREF."machine WHERE id=".db_escape($machine_id);
	$res=db_query($sql);
	$row=db_fetch_row($res);
	return $row[0];
	
	
}
function get_machine_frequency_rep($sno)
{
		$sql="SELECT GROUP_CONCAT(fre.mac_fre) AS mac_fre FROM ".TB_PREF."machine as machine LEFT JOIN ".TB_PREF."machine_frequency as fre ON FIND_IN_SET(fre.id,machine.mac_fre)
     
	WHERE machine.mac_code= '$sno' ";

		$res=db_query($sql);
		$row=db_fetch_row($res);
		return $row["0"];

}

//----------------------------------------------------------------------------------------------------

function print_outstanding_GRN()
{
    global $path_to_root;

    $mac_code = $_POST['PARAM_0'];
    $model_no = $_POST['PARAM_1'];
    $machine_eqp = $_POST['PARAM_2'];
    $machine_make = $_POST['PARAM_3'];
    $machine_capacity= $_POST['PARAM_4'];
    $supplier_id= $_POST['PARAM_5'];
    $location= $_POST['PARAM_7'];
    $comments = $_POST['PARAM_8'];
	$orientation = $_POST['PARAM_9'];
	$destination = $_POST['PARAM_10'];
	
	if($location =='')
		$loc="All";
	else
		$loc=get_location_name($location);
	
	

	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$orientation = ($orientation ? 'L' : 'P');

    $dec = user_price_dec();

	$cols = array(0, 25,80,160,260,300,420,480,550);

	$headers = array(_('S.No'), _('Model No'), _('Equipment'), _('Make'),   _('Capacity'),   ("Waranty Type"),("Exp Date"),("Supplier"));

	$aligns = array('left',	'left','left','left','left','left','left','left','left','left');

    $params =   array( 	0 => $comments,
						1 => array('text' => _('Location'), 'from' => $loc, 'to' => ''));

    $rep = new FrontReport(_('Machine Report'), "machinesreport", user_pagesize(), 9, $orientation);
    if ($orientation == 'L')
    	recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$k=1;
	
	$res = getTransactions($mac_code,$machine_eqp,$machine_make,$machine_capacity,$supplier_id,$loc_cat,$location,$model_no);

	$wrarranty_type= array( 
	      1 => _("Manufacturer Warranty"),
		  2 => _("AMC"),
          3 => _("CMC"),
          4 => _("No Warranty"));
	While ($mac = db_fetch($res))
	{
		
		$machine = get_machine_name($mac['id']);
		if ($catt != $machine)
		{
			if ($catt != '')
			{	
				$rep->NewLine(2, 3);
				$rep->Line($rep->row - 2);
				$rep->NewLine(2, 3);
			}
			$rep->Font("bold");
			$rep->TextCol(0, 6,"Machine :  ".$machine);
			$rep->Font("");
			$rep->Line($rep->row - 10);
			$catt = $machine;
			$rep->NewLine();
		}			
		
		$rep->NewLine();
		$rep->TextCol(0, 1, $k);
	//	$rep->TextCol(1, 2, $mac['mac_code']);
		$rep->TextCol(1,2, $mac['mac_model_no']);
		$rep->TextCol(2,3, $mac['mac_eqp']);
		$rep->TextCol(3, 4, $mac['mac_make']);
		$rep->TextCol(4, 5, $mac['mac_cap']);

	//	$mac_frequency=get_machine_frequency_rep($mac["mac_code"]);
		//$rep->TextColLines(5, 6, $mac_frequency);
		$rep->TextCol(5, 6,$wrarranty_type[$mac['warranty_type']]);

		if($mac['warranty_type']!=4)
		$rep->TextCol(6,7, $mac['warranty_exp_date']);
	    else
		$rep->TextCol(6,7, "NA");

    	$rep->TextColLines(7, 8, $mac['supp_name']);
		$k++;
		$rep->NewLine(0, 1);
	}
	
	$rep->Line($rep->row - 2);
	$rep->NewLine();
    $rep->End();
}

