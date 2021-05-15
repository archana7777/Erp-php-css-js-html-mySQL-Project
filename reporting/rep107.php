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
// Title:	Print Invoices
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");

//----------------------------------------------------------------------------------------------------
function get_state_name($state_code)
{
	$sql="SELECT state_name FROM ".TB_PREF."gst_state_codes WHERE state_code=".db_escape($state_code)."";
	return $res=db_query($sql);
	
}

function get_sales_order_buyer_order_no_details($order_no)
{
	$sql="SELECT * FROM ".TB_PREF."sales_orders WHERE order_no=".db_escape($order_no)." AND trans_type=30";
	// display_error($sql); die;
	$res=db_query($sql,"Could Not Get The details!");
	return $result=db_fetch($res);
}

//delivery details for  tax invoice

function get_current_invoice_sales_delivery_details($order_no)
{
	$sql="SELECT * FROM ".TB_PREF."debtor_trans WHERE order_=".db_escape($order_no)." AND type=13";
	//display_error($sql);
	$res=db_query($sql,"Could Not Get The details!");
	return $result=db_fetch($res);
}

function get_customer_tax_id($debtor_no)
{
	$sql="SELECT tax_id,pan_no FROM ".TB_PREF."cust_branch WHERE debtor_no=".db_escape($debtor_no)."";
	return $res=db_query($sql);
	
}

function get_invoice_range($from, $to)
{
	global $SysPrefs;

	$ref = ($SysPrefs->print_invoice_no() == 1 ? "trans_no" : "reference");

	$sql = "SELECT trans.trans_no, trans.reference
		FROM ".TB_PREF."debtor_trans trans 
			LEFT JOIN ".TB_PREF."voided voided ON trans.type=voided.type AND trans.trans_no=voided.id
		WHERE trans.type=".ST_SALESINVOICE
			." AND ISNULL(voided.id)"
 			." AND trans.trans_no BETWEEN ".db_escape($from)." AND ".db_escape($to)			
		." ORDER BY trans.tran_date, trans.$ref";

	return db_query($sql, "Cant retrieve invoice range");
}

print_invoices();

function get_options(){
	
	
	if($_GET['PARAM_8'] == 1){
		return 'Original for Recipient';
	}
	if($_GET['PARAM_8'] == 2){
		return 'Duplicate for Transporter';
	}
	if($_GET['PARAM_8'] == 3){
		return 'Triplicate';
	}
	if($_GET['PARAM_8'] == 4){
		return 'Quatricate';
	}
	if($_GET['PARAM_8'] == 5){
		return 'Quintuplicate';
	}
	
}	

//----------------------------------------------------------------------------------------------------

function print_invoices()
{
	global $path_to_root, $SysPrefs;
	
	$show_this_payment = true; // include payments invoiced here in summary

	include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$currency = $_POST['PARAM_2'];
	$email = $_POST['PARAM_3'];
	$pay_service = $_POST['PARAM_4'];
	$comments = $_POST['PARAM_5'];
	$customer = $_POST['PARAM_6'];
	$orientation = $_POST['PARAM_7'];
	$inv_options=$_POST['PARAM_8'];
	$option = get_options();

	if (!$from || !$to) return;

	$orientation = ($orientation ? 'L' : 'P');
	$dec = user_price_dec();

 	$fno = explode("-", $from);
	$tno = explode("-", $to);
	$from = min($fno[0], $tno[0]);
	$to = max($fno[0], $tno[0]);

	//-------------code-Descr-Qty--uom--tax--prc--Disc-Tot--//
	$cols = array(4, 35, 110, 280, 330, 380, 430,460,500, 560);

	// $headers in doctext.inc
	$aligns = array('left',	'left',	'left', 'left', 'left','left','left', 'left', 'right');

	$params = array('comments' => $comments);

	$cur = get_company_Pref('curr_default');

	if ($email == 0)
		$rep = new FrontReport(_('INVOICE'), "InvoiceBulk", user_pagesize(), 9, $orientation);
	if ($orientation == 'L')
		recalculate_cols($cols);

	$range = get_invoice_range($from, $to);
	while($row = db_fetch($range))
	{
			if (!exists_customer_trans(ST_SALESINVOICE, $row['trans_no']))
				continue;
			$sign = 1;
			$myrow = get_customer_trans($row['trans_no'], ST_SALESINVOICE);

			if ($customer && $myrow['debtor_no'] != $customer) {
				continue;
			}
			if ($currency != ALL_TEXT && $myrow['curr_code'] != $currency) {
				continue;
			}
			$baccount = get_default_bank_account($myrow['curr_code']);
			$params['bankaccount'] = $baccount['id'];

			$branch = get_branch($myrow["branch_code"]);
			$sales_order = get_sales_order_header($myrow["order_"], ST_SALESORDER);
			if ($email == 1)
			{
				$rep = new FrontReport("", "", user_pagesize(), 9, $orientation);
				$rep->title = _('INVOICE');
				$rep->filename = "Invoice" . $myrow['reference'] . ".pdf";
			}	
			$rep->currency = $cur;
			$rep->Font();
			$rep->Info($params, $cols, null, $aligns);

			$contacts = get_branch_contacts($branch['branch_code'], 'invoice', $branch['debtor_no'], true);
			$baccount['payment_service'] = $pay_service;
			$rep->SetCommonData($myrow, $branch, $sales_order, $baccount, ST_SALESINVOICE, $contacts);
			$rep->SetHeaderType('Header6');
			$rep->NewPage();
			// calculate summary start row for later use
			$summary_start_row = $rep->bottomMargin + (15 * $rep->lineHeight);

			if ($rep->formData['prepaid'])
			{
				$result = get_sales_order_invoices($myrow['order_']);
				$prepayments = array();
				while($inv = db_fetch($result))
				{
					$prepayments[] = $inv;
					if ($inv['trans_no'] == $row['trans_no'])
					break;
				}

				if (count($prepayments) > ($show_this_payment ? 0 : 1))
					$summary_start_row += (count($prepayments)) * $rep->lineHeight;
				else
					unset($prepayments);
			}

   			$result = get_customer_trans_details(ST_SALESINVOICE, $row['trans_no']);
			$SubTotal = 0;
			$k=1;
			$total_qty =0;
			
			$tax_ids = get_gst_taxes_ids($myrow["tax_group_id"]);
		     while($tax_id_array =db_fetch($tax_ids))
	        {
		
		     $tax_id[]['tax_type_id']=$tax_id_array['tax_type_id'];
	          //display_error($tax_id[0]['tax_type_id']); die;
	        }
			
			while ($myrow2=db_fetch($result))
			{
				if ($myrow2["quantity"] == 0)
					continue;

				$Net = round2($sign * ((1 - $myrow2["discount_percent"]) * $myrow2["unit_price"] * $myrow2["quantity"]),
				   user_price_dec());
				$SubTotal += $Net;
	    		$DisplayPrice = number_format2($myrow2["unit_price"],$dec);
	    		$DisplayQty = number_format2($sign*$myrow2["quantity"],get_qty_dec($myrow2['stock_id']));
	    		$DisplayNet = number_format2($Net,$dec);
	    		if ($myrow2["discount_percent"]==0)
		  			$DisplayDiscount ="";
	    		else
		  			$DisplayDiscount = number_format2($myrow2["discount_percent"]*100,user_percent_dec()) . "%";
				$c=0;
				
				$rep->TextCol(0, 1, $k, -2);
				
				$rep->TextCol(1, 2,	$myrow2['number_of_packages'], -2);
				
				$oldrow = $rep->row;
				$rep->TextColLines(2, 3, $myrow2['StockDescription'], -2);
				$newrow = $rep->row;
				$rep->row = $oldrow;
				
				if ($Net != 0.0 || !is_service($myrow2['mb_flag']) || !$SysPrefs->no_zero_lines_amount())
				{
					$rep->TextCol(3, 4,	$myrow2['hsn_code'], -2);
					$rep->TextCol(4, 5,	$DisplayQty, -2);
					$rep->TextCol(5, 6,	$DisplayPrice, -2);
					$rep->TextCol(6, 7,	$myrow2['units'], -2);
					$rep->TextCol(7, 8,	$DisplayDiscount, -2);
					$rep->TextCol(8, 9,	$DisplayNet, -2);
				}
				$rep->row = $newrow;
				//$rep->NewLine(1);
				if ($rep->row < $rep->bottomMargin + (26 * $rep->lineHeight))
					$rep->NewPage();
				
				$k++;
				
				$DisplayQty1 = str_replace(',', '', $DisplayQty);
				$total_qty += $DisplayQty1;
				
				
			}

			$memo = get_comments_string(ST_SALESINVOICE, $row['trans_no']);
			if ($memo != "")
			{
				$rep->NewLine();
				$rep->TextColLines(1, 3, $memo, -2);
			}

   			$DisplaySubTot = number_format2($SubTotal,$dec);

			// set to start of summary line:
			/*
    		$rep->row = $summary_start_row;
			if (isset($prepayments))
			{
				// Partial invoices table
				$rep->TextCol(0, 3,_("Prepayments invoiced to this order up to day:"));
				$rep->TextCol(0, 3,	str_pad('', 150, '_'));
				$rep->cols[2] -= 20;
				$rep->aligns[2] = 'right';
				$rep->NewLine(); $c = 0; $tot_pym=0;
				$rep->TextCol(0, 3,	str_pad('', 150, '_'));
				$rep->TextCol($c++, $c, _("Date"));
				$rep->TextCol($c++, $c,	_("Invoice reference"));
				$rep->TextCol($c++, $c,	_("Amount"));

				foreach ($prepayments as $invoice)
				{
					if ($show_this_payment || ($invoice['reference'] != $myrow['reference']))
					{
						$rep->NewLine();
						$c = 0; $tot_pym += $invoice['prep_amount'];
						$rep->TextCol($c++, $c,	sql2date($invoice['tran_date']));
						$rep->TextCol($c++, $c,	$invoice['reference']);
						$rep->TextCol($c++, $c, number_format2($invoice['prep_amount'], $dec));
					}
					if ($invoice['reference']==$myrow['reference']) break;
				}
				$rep->TextCol(0, 3,	str_pad('', 150, '_'));
				$rep->NewLine();
				$rep->TextCol(1, 2,	_("Total payments:"));
				$rep->TextCol(2, 3,	number_format2($tot_pym, $dec));
			}
            */

			$doctype = ST_SALESINVOICE;
    		//$rep->row = $summary_start_row;
			$rep->cols[2] += 20;
			$rep->cols[3] += 20;
			$rep->aligns[3] = 'left';

			$rep->row = $rep->bottomMargin + (26 * $rep->lineHeight);
			$rep->TextCol(5, 8, _("Sub-total"), -2);
			$rep->TextCol(8, 9,	$DisplaySubTot, -2);
			$rep->NewLine();
			if ($myrow['ov_freight'] != 0.0)
			{
   				$DisplayFreight = number_format2($sign*$myrow["ov_freight"],$dec);
				$rep->TextCol(5, 8, _("Shipping"), -2);
				$rep->TextCol(8, 9,	$DisplayFreight, -2);
				$rep->NewLine();
			}	
			$tax_items = get_trans_tax_details(ST_SALESINVOICE, $row['trans_no']);
			$first = true;
    		while ($tax_item = db_fetch($tax_items))
    		{
    			if ($tax_item['amount'] == 0)
    				continue;
    			$DisplayTax = number_format2($sign*$tax_item['amount'], $dec);

    			if ($SysPrefs->suppress_tax_rates() == 1)
    				$tax_type_name = $tax_item['tax_type_name'];
    			else
    				$tax_type_name = $tax_item['tax_type_name'];

    			if ($myrow['tax_included'])
    			{
    				if ($SysPrefs->alternative_tax_include_on_docs() == 1)
    				{
    					if ($first)
    					{
							$rep->TextCol(5, 8, _("Total Tax Excluded"), -2);
							$rep->TextCol(8, 9,	number_format2($sign*$tax_item['net_amount'], $dec), -2);
							$rep->NewLine();
    					}
						$rep->TextCol(5, 8, $tax_type_name, -2);
						$rep->TextCol(8, 9,	$DisplayTax, -2);
						$first = false;
    				}
    				else
						$rep->TextCol(3, 6, _("Included") . " " . $tax_type_name . _("Amount") . ": " . $DisplayTax, -2);
				}
    			else
    			{
					$rep->TextCol(5, 8, $tax_type_name, -2);
					$rep->TextCol(8, 9,	$DisplayTax, -2);
				}
				$rep->NewLine();
    		}
			
			 $rep->Line($rep->row - 3);
    		$rep->NewLine();
			$DisplayTotal = number_format2($sign*($myrow["ov_freight"] + $myrow["ov_gst"] +
				$myrow["ov_amount"]+$myrow["ov_freight_tax"]),$dec);
			$rep->Font('bold');
			 $rep->Line($rep->row - 2);
			if (!$myrow['prepaid']) $rep->Font('bold');
				$rep->TextCol(2, 4, $rep->formData['prepaid'] ? _("TOTAL ORDER GST INCL.") : _("TOTAL"), - 2);
				
		    $rep->TextCol(4, 5, number_format2($total_qty,2), -2);
			$rep->Text($mcol+510, $myrow['curr_code'] ." " .$DisplayTotal);
			//$rep->TextCol(8, 9, $DisplayTotal, -2);
			 $rep->NewLine_Sales();	

    		
			if ($rep->formData['prepaid'])
			{
				$rep->NewLine_Sales();
				$rep->Font('bold');
				$rep->TextCol(3, 6, $rep->formData['prepaid']=='final' ? _("THIS INVOICE") : _("TOTAL INVOICE"), - 2);
				$rep->TextCol(6, 7, number_format2($myrow['prep_amount'], $dec), -2);
			}
			//$words = price_in_words($rep->formData['prepaid'] ? $myrow['prep_amount'] : $myrow['Total']
				//, array( 'type' => ST_SALESINVOICE, 'currency' => $myrow['curr_code']));
				  $rep->NewLine_Sales();
				 $rep->Line($rep->row - 2);
		
			$words = no_to_words($SubTotal+$myrow["ov_gst"]+$myrow["ov_freight"]);	
				
			if ($words != "")
		{
			
			
			$rep->TextCol(0, 8,  _("Amount Chargeable (in words) : ") .  $myrow['curr_code'] ." ". $words. "Only", - 2);
			
			$rep->Font();
				$rep->NewLine();
		}
		
		//$rep->Line($rep->row - 15);
		
		$rep->Font('bold');
		$rep->TextCol(0, 2, _("HSN/SAC"), -2);
		//$rep->TextCol(1, 2, _("Taxable Value"), -2);
		$rep->Text($ccol+120, _("Taxable Value "), $c2col);
		$rep->Text($ccol+230, _("Central Tax "), $c2col);
		$rep->Text($ccol+350, _("State Tax "), $c2col);
		$rep->TextCol(6, 8, _("Integrated Tax"), -2);
		$rep->TextCol(8, 9, _("Total"), -2);
		$rep->Font();
		
		
	 $rep->NewLine();
	 
	
		  
	$item_result = get_customer_trans_details_slab_wise(ST_SALESINVOICE, $row['trans_no']);	  
		  
		  $price = 0;
		  $state_tax=0;
		  $central_tax=0;
		  $int_tax=0;
		  $tax_total=0;
		  
		 while ($item = db_fetch($item_result))
	{
		//$Taxable_Value = round2($sign * ((1 - $item["discount_percent"]) * $item["unit_price"] * $item["quantity"]),
				   //user_price_dec());
				
				   
		$DisplayTaxable_Value = number_format2($item['FullUnitPrice'],$dec);		   
	
		//$item_taxes = get_trans_tax_details_item_slab_wise(ST_SALESINVOICE, $row['trans_no'],$item['hsn_code']);
		
		$item_taxes = get_debtor_trans_tax_details_item_slab_wise(ST_SALESINVOICE, $row['trans_no'],$item['hsn_code']);
		
	    $gst_total = 0;
		
			$rep->TextCol(0, 2,	$item['hsn_code'], -2);
	       $rep->Text($ccol+120, number_format2($item['FullUnitPrice'],2), $c2col);
		$tax_type=0;
		
		
		
	 while ($taxitem = db_fetch($item_taxes))
	{
		
		
		if ($taxitem['amount'] == 0)
    	continue;
		$DisplayTaxableAmount = $sign*$taxitem['amount'];
		//$tax_type = $taxitem['tax_type_id'];
		$DisplayTaxAmount = $DisplayTaxableAmount*$taxitem['tax_rate']/100;
		
		
		if($myrow["tax_group_id"]==3){
		    $sgst_rate = $taxitem['tax_rate']/2;
	    	$DisplayTaxPrice = $DisplayTaxAmount;
			
				 $rep->Text($ccol+220, 	$sgst_rate. "% ", $c2col);
				 $rep->Text($ccol+260, 	number_format2($DisplayTaxPrice/2,2), $c2col);
				 $rep->Text($ccol+340, 	$sgst_rate. "% ", $c2col);
				 $rep->Text($ccol+380, 	number_format2($DisplayTaxPrice/2,2), $c2col);
				 
				 $rep->Text($ccol+450, 	"0% ", $c2col);
		         $rep->Text($ccol+485, 	number_format2('',2), $c2col);
				
				$gst_total1 = str_replace(',', '', $DisplayTaxPrice);
				$gst_total += $gst_total1;	
		
			
		}
			 
	elseif($myrow["tax_group_id"]==4)
	{
		$igst_rate = $taxitem['tax_rate'];
	    $DisplayTaxPrice = $DisplayTaxAmount;
		 
		 $rep->Text($ccol+220, 	"0% ", $c2col);
		 $rep->Text($ccol+260, 	number_format2('',2), $c2col);
		 $rep->Text($ccol+340, 	"0% ", $c2col);
		 $rep->Text($ccol+380, 	number_format2('',2), $c2col);		
		 	
		$rep->Text($ccol+450, 	$igst_rate. "% ", $c2col);
		$rep->Text($ccol+485, 	number_format2($DisplayTaxPrice,2), $c2col);
         		
				
			
			$gst_total1 = str_replace(',', '', $DisplayTaxPrice);
				$gst_total += $gst_total1;	
		   
		}
		
		else
	{
		$rep->Text($ccol+220, 	"0% ", $c2col);
		$rep->Text($ccol+260, 	number_format2('',2), $c2col);
		$rep->Text($ccol+340, 	"0% ", $c2col);
		$rep->Text($ccol+380, 	number_format2('',2), $c2col);	
		$rep->Text($ccol+450, 	"0% ", $c2col);
		$rep->Text($ccol+485, 	number_format2('',2), $c2col);
	
	}	
		
		 //
	}
     $rep->TextCol(8, 9,number_format2($gst_total,2));			
	$rep->NewLine_Sales();
	
	$price1 = str_replace(',', '', $DisplayTaxable_Value);
				$price += $price1;	
	
	
	
	if($myrow["tax_group_id"]==3){
		
		
	$central_tax1 = str_replace(',', '', $DisplayTaxPrice/2);
				$central_tax += $central_tax1;		
		
   $state_tax1 = str_replace(',', '', $DisplayTaxPrice/2);
				$state_tax += $state_tax1;	
				
	//$state_tax += $DisplayTaxPrice;
	}
	elseif($myrow["tax_group_id"]==4)
	{
		
	$int_tax1 = str_replace(',', '', $DisplayTaxPrice);
				$int_tax += $int_tax1;	
		
	//$int_tax += $DisplayTaxPrice;
	}
		
	$tax_total += $gst_total;
  }
  
  
	$rep->Line($rep->row - 2);
	
		$rep->Font('bold');
		 $rep->NewLine(1);
		$rep->TextCol(0, 1, _("Total"), -2);
		$rep->Font();
		
		
	
		$rep->Text($ccol+120, number_format2($price,2), $c2col);
		$rep->Text($ccol+260, number_format2($state_tax,2), $c2col);
		
		$rep->Text($ccol+380, number_format2($central_tax,2));
		$rep->Text($ccol+480, number_format2($int_tax,2));
		$rep->TextCol(8, 9,number_format2($tax_total,2),-2);
		//$rep->TextCol(5, 6, number_format2($central_tax,2), -2);
		//$rep->TextCol(6, 8, number_format2($int_tax,2), -2);
		
		
		$rep->Line($rep->row - 6);
		$rep->NewLine_Sales();
		$gst_words = no_to_words($myrow["ov_gst"]);	
		$rep->NewLine_Sales();

		if ($gst_words != "")
		{
			$rep->Font('bold');
			
			//$rep->TextCol(0, 8,  _("Tax Amount (in words) ") . ": INR " . $gst_words. "Only", - 2);
			
			
			$rep->TextCol(0, 8,  _("Tax Chargeable (in words) : ") .  $myrow['curr_code'] ." ". $gst_words. "Only", - 2);
			
			$rep->Font();
			$rep->NewLine_Sales();
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
		$rep->Text($mcol+320,_("Prepared By"));
		$rep->Text($mcol+420,_("Verified By"));
		$rep->Text($mcol+500,_("Authorised Signatory"));
		
		
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
