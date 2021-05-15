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
$page_security = 'SA_SUPPLIERANALYTIC';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Stefan Sotirov, modified slightly by Joe Hunt.
// date_:	01-12-2017
// Title:	Inventory Purchasing - Transaction Based
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/inventory/includes/db/size_master_db.inc");

//----------------------------------------------------------------------------------------------------

print_inventory_purchase();

function getTransactions()
{
      $sql = "SELECT * FROM ".TB_PREF."size_master";
	
    return db_query($sql,"No transactions were returned");

}



//----------------------------------------------------------------------------------------------------

function print_inventory_purchase()
{
    global $path_to_root;

	
	$orientation = $_POST['PARAM_0'];
	$destination = $_POST['PARAM_1'];
	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$orientation = ($orientation ? 'L' : 'P');
    $dec = user_price_dec();


	$cols = array(0, 60, 180, 230, 300, 400, 420, 465,	520);

	$headers = array(_('S.No'), _('Size'), _('Description'),_('Inactive'));
	if ($fromsupp != '')
		$headers[4] = '';

	$aligns = array('left',	'left',	'left', 'right', 'left', 'left', 'right', 'right');

    $params =   array(); 	
           

    $rep = new FrontReport(_('Size listing'), "SizeListingReport", user_pagesize(), 9, $orientation);
    if ($orientation == 'L')
    	recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$res = getTransactions($category, $location, $fromsupp, $item, $from, $to);

	//($total = $total_supp = $grandtotal = 0.0); //left if someone needs them for own needs
	//($total_qty = 0.0);
	$catt = $stock_description = $stock_id = $supplier_name = $event = '';
	$i=1;
	while ($trans=db_fetch($res))
	{
		

		
			$rep->TextCol(0, 1, $i);
			$rep->TextCol(1, 2, $trans['size']);
			$rep->TextCol(2, 3, $trans['description'] );
			$i++;
			
		$rep->NewLine();

//------------Left if somebody needs them for own needs
//		$total += $amt;
//		$total_supp += $amt;
//		$grandtotal += $amt;
//		$total_qty += $trans['qty'];
	}

	$rep->Line($rep->row - 4);
	$rep->NewLine();
	$rep->End();
}

