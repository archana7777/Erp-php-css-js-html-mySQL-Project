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
$page_security = $_POST['PARAM_0'] == $_POST['PARAM_1'] ?
	'SA_SALESTRANSVIEW' : 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Print Sales Orders
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/taxes/tax_calc.inc");

//----------------------------------------------------------------------------------------------------

function get_state_name($state_code)
{
	$sql="SELECT state_name FROM ".TB_PREF."gst_state_codes WHERE state_code=".db_escape($state_code)."";
	return $res=db_query($sql);
	
}


print_sales_orders();

function print_sales_orders()
{
	global $path_to_root, $SysPrefs;

	include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$currency = $_POST['PARAM_2'];
	$email = $_POST['PARAM_3'];
	$print_as_quote = $_POST['PARAM_4'];
	$comments = $_POST['PARAM_5'];
	$orientation = $_POST['PARAM_6'];

	if (!$from || !$to) return;

	$orientation = ($orientation ? 'L' : 'P');
	$dec = user_price_dec();

	$cols = array(4, 35, 110, 280, 330, 380, 430,480,500, 560);

	// $headers in doctext.inc
	$aligns = array('left',	'left',	'left', 'left', 'left','right','right', 'left', 'right');

	$params = array('comments' => $comments, 'print_quote' => $print_as_quote);

	$cur = get_company_Pref('curr_default');

	if ($email == 0)
	{

		if ($print_as_quote == 0)
			$rep = new FrontReport(_("SALES ORDER"), "SalesOrderBulk", user_pagesize(), 9, $orientation);
		else
			$rep = new FrontReport(_("QUOTE"), "QuoteBulk", user_pagesize(), 9, $orientation);
	}
    if ($orientation == 'L')
    	recalculate_cols($cols);

	for ($i = $from; $i <= $to; $i++)
	{
		$myrow = get_sales_order_header($i, ST_SALESORDER);
		if ($currency != ALL_TEXT && $myrow['curr_code'] != $currency) {
			continue;
		}
		$baccount = get_default_bank_account($myrow['curr_code']);
		$params['bankaccount'] = $baccount['id'];
		$branch = get_branch($myrow["branch_code"]);
		if ($email == 1)
			$rep = new FrontReport("", "", user_pagesize(), 9, $orientation);
		$rep->SetHeaderType('Header2');
		$rep->currency = $cur;
		$rep->Font();
		if ($print_as_quote == 1)
		{
			$rep = new FrontReport("", "", user_pagesize(), 9, $orientation);
			if ($print_as_quote == 1)
			{
				$rep->title = _('QUOTE');
				$rep->filename = "Quote" . $i . ".pdf";
			}
			else
			{
				$rep->title = _("SALES ORDER");
				$rep->filename = "SalesOrder" . $i . ".pdf";
			}
		}
		else
			$rep->title = ($print_as_quote==1 ? _("QUOTE") : _("SALES ORDER"));
		$rep->currency = $cur;
		$rep->Font();
		$rep->Info($params, $cols, null, $aligns);

		$contacts = get_branch_contacts($branch['branch_code'], 'order', $branch['debtor_no'], true);
		$rep->SetCommonData($myrow, $branch, $myrow, $baccount, ST_SALESORDER, $contacts);
		$rep->SetHeaderType('Header7');
		$rep->NewPage();

		$result = get_sales_order_details($i, ST_SALESORDER);
		$SubTotal = 0;
		$k=1;
		$total_qty =0;
		$items = $prices = array();
		while ($myrow2=db_fetch($result))
		{
			$Net = round2(((1 - $myrow2["discount_percent"]) * $myrow2["unit_price"] * $myrow2["quantity"]),
			   user_price_dec());
			$prices[] = $Net;
			$items[] = $myrow2['stk_code'];
			$SubTotal += $Net;
			$DisplayPrice = number_format2($myrow2["unit_price"],$dec);
			$DisplayQty = number_format2($myrow2["quantity"],get_qty_dec($myrow2['stk_code']));
			$DisplayNet = number_format2($Net,$dec);
			if ($myrow2["discount_percent"]==0)
				$DisplayDiscount ="";
			else
				$DisplayDiscount = number_format2($myrow2["discount_percent"]*100,user_percent_dec()) . "%";
			
			$rep->TextCol(0, 1, $k, -2);
				
			$rep->TextCol(1, 2,	$myrow2['number_of_packages'], -2);
			
			
			$oldrow = $rep->row;
			$rep->TextColLines(2, 3, $myrow2['description'], -2);
			$newrow = $rep->row;
			$rep->row = $oldrow;
			if ($Net != 0.0 || !is_service($myrow2['mb_flag']) || !$SysPrefs->no_zero_lines_amount())
			{
				$rep->TextCol(3, 4,	$myrow2['hsn_code'], -2);
				$rep->TextCol(4, 5,	sql2date($myrow2['due_on']), -2);
				$rep->TextCol(5, 6,	$DisplayQty, -2);
				$rep->TextCol(6, 7,	$DisplayPrice, -2);
				$rep->TextCol(7, 8,	$myrow2['units'], -2);
				//$rep->TextCol(5, 6,	$DisplayDiscount, -2);
				$rep->TextCol(8, 9,	$DisplayNet, -2);
			}
			$rep->row = $newrow;
			if ($rep->row < $rep->bottomMargin + (20 * $rep->lineHeight))
					$rep->NewPage();
				
				$k++;
				$DisplayQty1 = str_replace(',', '', $DisplayQty);
				$total_qty += $DisplayQty1;
		}
		if ($myrow['comments'] != "")
		{
			$rep->NewLine();
			$rep->TextColLines(1, 3, $myrow['comments'], -2);
		}
		$DisplaySubTot = number_format2($SubTotal,$dec);

		$rep->row = $rep->bottomMargin + (20 * $rep->lineHeight);
		$doctype = ST_SALESORDER;

		$rep->TextCol(4, 8, _("Sub-total"), -2);
		$rep->TextCol(8, 9,	$DisplaySubTot, -2);
		$rep->NewLine();
		if ($myrow['freight_cost'] != 0.0)
		{
			$DisplayFreight = number_format2($myrow["freight_cost"],$dec);
			$rep->TextCol(4, 8, _("Shipping"), -2);
			$rep->TextCol(8, 9,	$DisplayFreight, -2);
			$rep->NewLine();
		}	
		$DisplayTotal = number_format2($myrow["freight_cost"] + $SubTotal, $dec);
		

		$tax_items = get_gst_tax_for_items($items, $prices, $myrow["freight_cost"],'',$branch['branch_code'],
		  $myrow['tax_group_id'], $myrow['tax_included'],  null);
		$first = true;
		foreach($tax_items as $tax_item)
		{
			if ($tax_item['Value'] == 0)
				continue;
			$DisplayTax = number_format2($tax_item['Value'], $dec);

			$tax_type_name = $tax_item['tax_type_name'];

			if ($myrow['tax_included'])
			{
				if ($SysPrefs->alternative_tax_include_on_docs() == 1)
				{
					if ($first)
					{
						$rep->TextCol(4, 8, _("Total Tax Excluded"), -2);
						$rep->TextCol(8, 9,	number_format2($tax_item['net_amount'], $dec), -2);
						$rep->NewLine();
					}
					$rep->TextCol(4, 8, $tax_type_name, -2);
					$rep->TextCol(8, 9,	$DisplayTax, -2);
					$first = false;
				}
				else
					$rep->TextCol(3, 7, _("Included") . " " . $tax_type_name . " " . _("Amount"). ": " . $DisplayTax, -2);
			}
			else
			{
				$SubTotal += $tax_item['Value'];
				$rep->TextCol(4, 8, $tax_type_name, -2);
				$rep->TextCol(8, 9,	$DisplayTax, -2);
			}
			$rep->NewLine();
		}

		$rep->NewLine();
		
		$rep->Line($rep->row - 3);
    	$rep->NewLine();
        $rep->Line($rep->row - 2);
		$DisplayTotal = number_format2($myrow["freight_cost"] + $SubTotal, $dec);
		$rep->Font('bold');
		$rep->TextCol(4, 5, _("TOTAL"), - 2);
		$rep->TextCol(5, 6, number_format2($total_qty,2), -2);
		
		//$rep->TextCol(8, 9,	$myrow['curr_code'] ." " .$DisplayTotal, -2);
		$rep->Text($mcol+520, $myrow['curr_code'] ." " .$DisplayTotal);
		
		
		$rep->NewLine();
		$rep->Line($rep->row - 2);
		
		$words = no_to_words($myrow["freight_cost"] + $SubTotal);
			if ($words != "")
		{
			$rep->TextCol(0, 8,  _("Amount Chargeable (in words) : ") .  $myrow['curr_code'] ." ". $words. "Only", - 2);
			$rep->Font();
			$rep->NewLine();
		}
		$rep->NewLine_Sales();
		 
		
		 
		$rep->TextCol(0, 2, _("Company's PAN     :    "), -2);
		$rep->Font('bold');
		$rep->TextCol(2, 3, $rep->company['pan_no'], -2);
		$rep->Font();
		
		$rep->NewLine_Sales(1);
		$rep->Font('bold');
		$rep->TextCol(0, 2, _("Declaration"), -2);
		
		
		$rep->Font();
		$rep->NewLine_Sales();
		$rep->TextCol(0, 4, _("We declare that this invoice shows the actual price of the goods "), -2);
		$rep->NewLine_Sales();
		$rep->TextCol(0, 4, _("described and that all particulars are true and correct."), -2);
		$rep->NewLine_Sales(1);
		
		$rep->Font('bold');
		$rep->TextCol(0, 2, _("Terms & Conditions:"), -2);
		$rep->Font();
		$rep->Text($mcol+350,_("Company Bank Details:"));
		$rep->NewLine_Sales();
		$rep->Text($mcol+350,_("Bank Name"));
		$rep->Text($mcol+440,_(":"));
		$rep->Font('bold');
		$rep->Text($mcol+460,_("HDFC BANK LTD CC A/C(41)"));
		$rep->Font();
		$rep->TextCol(0,3 ,_("2. Interest will be charged @ 12% P.A after Due Date."), -2);
		
		$rep->NewLine_Sales();
		$rep->Text($mcol+350,_("A/C No."));
		$rep->Text($mcol+440,_(":"));
		$rep->Font('bold');
		$rep->Text($mcol+460,_("50200013328041"));
		$rep->Font();
		$rep->TextCol(0,3 ,_("2. Weight variance +/- 0.5% will not be allowed for any claim"), -2);
		
		$rep->NewLine_Sales();
		$rep->Text($mcol+350,_("Branch & IFS Code"));
		$rep->Text($mcol+440,_(":"));
		$rep->Font('bold');
		$rep->Text($mcol+460,_("RATLAM & HDFC0000475"));
		$rep->Font();
		$rep->TextCol(0,3 ,_("3. All disputes are Subject to RATLAM Jurisdiction"), -2);
		
		$rep->NewLine_Sales();
		$rep->TextCol(0,3 ,_("4. E & O.E"), -2);
		
		$rep->row = $rep->bottomMargin + (4 * $rep->lineHeight);
		$rep->Font('bold');
        $rep->Text($ccol+420, _("for ") . $rep->company['coy_name'], $c2col);
		$rep->Font();
		$rep->row = $rep->bottomMargin + (1 * $rep->lineHeight);
		//$rep->Text($mcol+320,_("Prepared By"));
		//$rep->Text($mcol+420,_("Verified By"));
		$rep->Text($mcol+480,_("Authorised Signatory"));
		
		
			$rep->Font();
			if ($email == 1)
			{
				$rep->End($email);
			}
	}
	if ($email == 0)
		$rep->End();
}


function no_to_words($no)
{   
 $words = array('0'=> '' ,'1'=> 'One' ,'2'=> 'Two' ,'3' => 'Three','4' => 'Four','5' => 'Five','6' => 'Six','7' => 'Seven','8' => 'Eight','9' => 'Nine','10' => 'Ten','11' => 'Eleven','12' => 'Twelve','13' => 'Thirteen','14' => 'Fourteen','15' => 'Fifteen','16' => 'Sixteen','17' => 'Seventeen','18' => 'Eighteen','19' => 'Nineteen','20' => 'Twenty','30' => 'Thirty','40' => 'Forty','50' => 'Fifty','60' => 'Sixty','70' => 'Seventy','80' => 'Eighty','90' => 'Ninety','100' => 'Hundred','1000' => 'Thousand','100000' => 'Lakh','10000000' => 'Crore');
    if($no == 0)
        return ' ';
    else {
	$novalue='';
	$highno=$no;
	$remainno=0;
	$value=100;
	$value1=1000;       
            while($no>=100)    {
                if(($value <= $no) &&($no  < $value1))    {
                $novalue=$words["$value"];
                $highno = (int)($no/$value);
                $remainno = $no % $value;
                break;
                }
                $value= $value1;
                $value1 = $value * 100;
            }       
          if(array_key_exists("$highno",$words))
              return $words["$highno"]." ".$novalue." ".no_to_words($remainno);
          else {
             $unit=$highno%10;
             $ten =(int)($highno/10)*10;            
             return $words["$ten"]." ".$words["$unit"]." ".$novalue." ".no_to_words($remainno);
           }
    }
} 

