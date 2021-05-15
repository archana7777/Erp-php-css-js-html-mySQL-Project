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
//display_error('hie');die;
function getTransactions($mach_id,$machine_fre_id)
{
	$sql = "SELECT mac.mac_code,macfre.mac_fre,checklist.* FROM ".TB_PREF."machine_maintenance_checklists as checklist LEFT JOIN 
	".TB_PREF."machine as mac ON checklist.machine_fre_id=mac.id
	LEFT JOIN 
	".TB_PREF."machine_frequency as macfre ON checklist.machine_fre_id = macfre.id WHERE 1=1  ";
     if ($mach_id != -1)
	$sql .= " AND checklist.machine_fre_id =".db_escape($mach_id);
	if ($machine_fre_id != -1)
	$sql .= " AND checklist.machine_fre_id =".db_escape($machine_fre_id);

	$sql .= " ORDER BY checklist.id";
display_error($sql);
    return db_query($sql, "No transactions were returned");
}

function get_machine_name($machine_id)
{
	$sql="Select mac_code FROM ".TB_PREF."machine WHERE id=".db_escape($machine_id);
	$res=db_query($sql);
	$row=db_fetch_row($res);
	return $row[0];
	
	
}
//----------------------------------------------------------------------------------------------------

function print_outstanding_GRN()
{

 global $path_to_root;

    $machine_id = $_POST['PARAM_0'];
    $machine_fre_id = $_POST['PARAM_1'];
    $comments = $_POST['PARAM_2'];
	$orientation = $_POST['PARAM_3'];
	$destination = $_POST['PARAM_4'];
	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");
	

	$orientation = ($orientation ? 'L' : 'P');

    $dec = user_price_dec();

	$cols = array(0, 40,200,	300);

	$headers = array(_('S.No'), _('Machine Code'),_('Machine Frequency'),   _('Details'));

	$aligns = array('left',	'left',);

    $params =   array( 	0 => $comments,
    				    1 => array('text' => _('Supplier'), 'from' => '', 'to' => ''));

    $rep = new FrontReport(_('Machine Checklist Report'), "machinechecklistreport", user_pagesize(), 9, $orientation);
	
    if ($orientation == 'L')
    	recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$k=1;
	 //display_error('hie');
	$res = getTransactions($machine_id,$machine_fre_id);
	
$catt ='';
	While ($testtype = db_fetch($res))
	{
		$machine = get_machine_name($testtype['machine_id']);
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
		$rep->TextCol(1, 2, $testtype['mac_code']);
		$rep->TextCol(2, 3, $testtype['mac_fre']);
		$rep->TextCol(3, 6, $testtype['details']);
		$k++;
		$rep->NewLine(0, 1);
	}
	
	
	$rep->Line($rep->row - 2);
	$rep->NewLine();
    $rep->End();
}

