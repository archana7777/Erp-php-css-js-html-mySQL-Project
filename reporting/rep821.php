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

//----------------------------------------------------------------------------------------------------

print_outstanding_GRN();

function getTransactions($testtype)
{
	$sql = "SELECT * FROM ".TB_PREF."machine_equipment WHERE 1=1 ";
     if ($testtype == -1)
 	 $sql .= " AND !inactive";
		else
	$sql .= " AND id =".db_escape($testtype);

	$sql .= " ORDER BY id";
//display_error($sql);
    return db_query($sql, "No transactions were returned");
}

 function get_machine_frequency_rep($maccode)
{
		$sql="SELECT GROUP_CONCAT(fre.mac_fre) AS mac_fre FROM ".TB_PREF."machine_equipment as eqp LEFT JOIN ".TB_PREF."machine_frequency as fre ON FIND_IN_SET(fre.id,eqp.mac_fre)
     
	WHERE eqp.id= '$maccode' ";

		$res=db_query($sql);
		$row=db_fetch_row($res);
		return $row["0"];

}

function get_machine_fre_rep($id)
{
	$sql="Select mac_fre FROM ".TB_PREF."machine_equipment WHERE id=".db_escape($id);
	$res=db_query($sql);
	$row=db_fetch_row($res);
	return $row[0];
	
	
}


function get_machine_equipement_frequency($maccode)
{
		$sql="SELECT GROUP_CONCAT(fre.mac_fre) AS mac_fre FROM ".TB_PREF."machine_equipment as eqp LEFT JOIN ".TB_PREF."machine_frequency as fre ON FIND_IN_SET(fre.id,eqp.mac_fre)
     
	WHERE eqp.id= '$maccode' ";
 
		$res=db_query($sql);
		$row=db_fetch_row($res);
		return $row["0"];

}
function get_machine_frequency_rep1($sno)
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

    $testtype = $_POST['PARAM_0'];
    $comments = $_POST['PARAM_1'];
	$orientation = $_POST['PARAM_2'];
	$destination = $_POST['PARAM_3'];
	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$orientation = ($orientation ? 'L' : 'P');

    $dec = user_price_dec();

	$cols = array(0, 40,150,350,500);

	$headers = array(_('S.No'), _('Machine Make'),   _('Description'), _('Frequency'));

	$aligns = array('left',	'left',);

    $params =   array( 	0 => $comments,
    				    1 => array('text' => _('Supplier'), 'from' => '', 'to' => ''));

    $rep = new FrontReport(_('Machine Equipments Report'), "machineequipmentsreport", user_pagesize(), 9, $orientation);
    if ($orientation == 'L')
    	recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$k=1;
	
	$res = getTransactions($testtype);

	While ($testtype = db_fetch($res))
	{
		
		$rep->NewLine();
		$rep->TextCol(0, 1, $k);
		$rep->TextCol(1, 2, $testtype['mac_eqp']);
		$rep->TextCol(2, 3, $testtype['description']);
		
	$mac_frequency=get_machine_frequency_rep($testtype["mac_fre"]);
		$rep->TextColLines(3, 4, $mac_frequency);
		//$rep->TextCol(3, 4, $testtype['mac_fre']);
		$k++;
		$rep->NewLine(0, 1);
	}
	
	
	$rep->Line($rep->row - 2);
	$rep->NewLine();
    $rep->End();
}

