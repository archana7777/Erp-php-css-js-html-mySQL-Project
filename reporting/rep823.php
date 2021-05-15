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
	$sql = "SELECT * FROM ".TB_PREF."machine_frequency WHERE 1=1 ";
     if ($testtype == -1)
 	 $sql .= " AND !inactive";
		else
	$sql .= " AND id =".db_escape($testtype);

	$sql .= " ORDER BY id";
//display_error($sql);
    return db_query($sql, "No transactions were returned");
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

	$cols = array(0, 40,200,	300);

	$headers = array(_('S.No'), _('Machine Capacity'),   _('Description'));

	$aligns = array('left',	'left',);

    $params =   array( 	0 => $comments,
    				    1 => array('text' => _('Supplier'), 'from' => '', 'to' => ''));

    $rep = new FrontReport(_('Machine Frequency Report'), "machinefrequencyreport", user_pagesize(), 9, $orientation);
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
		$rep->TextCol(1, 2, $testtype['mac_fre']);
		$rep->TextCol(2, 3, $testtype['description']);
		$k++;
		$rep->NewLine(0, 1);
	}
	
	
	$rep->Line($rep->row - 2);
	$rep->NewLine();
    $rep->End();
}

