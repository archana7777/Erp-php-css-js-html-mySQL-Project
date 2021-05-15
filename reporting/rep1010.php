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
$page_security = 'SA_CUSTPAYMREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Payment Report
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");

//----------------------------------------------------------------------------------------------------

print_payment_report();

function getTransactions($customer, $date,$no_zeros_status)
{
	$date = date2sql($date);
	$dec = user_price_dec();

	$sql = "SELECT  reference,tran_date, due_date, trans_no, type, rate,
			 ABS( tds_amount) AS TdsAmount,
			(ABS( ov_amount) + ABS( ov_gst) ) AS TranTotal
		FROM ".TB_PREF."debtor_trans
		WHERE  debtor_no = '$customer'
		AND ROUND(ABS( tds_amount),$dec)>0";
	
		$sql.=" AND  tran_date <='$date' and type=10 
		ORDER BY  type,
			 trans_no";
			 
    return db_query($sql, "No transactions were returned");
}

//----------------------------------------------------------------------------------------------------

function print_payment_report()
{
	global $path_to_root, $systypes_array;

	$to = $_POST['PARAM_0'];
	$fromcust = $_POST['PARAM_1'];
	$no_zeros = $_POST['PARAM_2'];
	$comments = $_POST['PARAM_3'];
	$orientation = $_POST['PARAM_4'];
	$destination = $_POST['PARAM_5'];
	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$orientation = ($orientation ? 'L' : 'P');
	if ($fromsupp == ALL_TEXT)
		$from = _('All');
	else
		$from = get_customer_name($fromcust);

    	$dec = user_price_dec();

	if ($no_zeros)
	{
	 
	  $nozeros = _('Yes');
	}
	else 
	{
		  $no_zeros_status=0;	
		$nozeros = _('No');
	}

	$cols = array(0, 100, 160, 320, 400, 515);

	$headers = array(_('Trans Type'), _('#'), _('Inv Amount'),_('Due Date'), 'TDS Amount');

	$aligns = array('left',	'left',	'right','center', 'right');

    $params =   array( 	0 => $comments,
    			1 => array('text' => _('End Date'), 'from' => $to, 'to' => ''),
    			2 => array('text' => _('Customer'), 'from' => $from, 'to' => ''),
    			3 => array(  'text' => _('Currency'),'from' => $currency, 'to' => ''),
				4 => array('text' => _('Suppress Zeros'), 'from' => $nozeros, 'to' => ''));

    $rep = new FrontReport(_('TDS Receivable Report'), "PaymentReport", user_pagesize(), 9, $orientation);
    if ($orientation == 'L')
    	recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$total = array();
	$grandtotal = array(0,0);

	$sql = "SELECT debtor_no, name AS name, ".TB_PREF."payment_terms.terms FROM ".TB_PREF."debtors_master, ".TB_PREF."payment_terms
		WHERE ";
	if ($fromcust != ALL_TEXT)
		$sql .= "debtor_no=".db_escape($fromcust)." AND ";
	$sql .= "".TB_PREF."debtors_master.payment_terms = ".TB_PREF."payment_terms.terms_indicator
		ORDER BY name";
	$result = db_query($sql, "The customers could not be retrieved");

	$tds_balance=0;
	while ($myrow=db_fetch($result))
	{
		
		$res = getTransactions($myrow['debtor_no'], $to,$no_zeros_status);
		if ($no_zeros && db_num_rows($res)==0) continue;

		$rep->fontSize += 2;
		$rep->TextCol(0, 6, $myrow['name'] . " - " . $myrow['terms']);
		$rep->fontSize -= 2;
		$rep->NewLine(1, 2);
		if (db_num_rows($res)==0)
			continue;
		$rep->Line($rep->row + 4);
		$total[0] = $total[1] = 0.0;
		while ($trans=db_fetch($res))
		{
			$rate = 1.0;

			$rep->NewLine(1, 2);
			$rep->TextCol(0, 1, $systypes_array[$trans['type']]);
			$rep->TextCol(1, 2,	$trans['reference']);
			$rep->DateCol(3, 4,	$trans['due_date'], true);
			$rep->AmountCol(2, 3, $trans['TranTotal'], $dec);
			$item[0] = $trans['TdsAmount'] * $rate;
			$rep->AmountCol(4, 5, $item[0], $dec);
			$tds_balance+=$trans['TdsAmount'];
		}
		$rep->NewLine();
		$rep->Line($rep->row + 4);
		$rep->NewLine();
	}	
	   $rep->NewLine();
		$rep->AmountCol(4, 5, $tds_balance, $dec);
		$rep->NewLine();	
	$rep->NewLine();
    $rep->End();
}

