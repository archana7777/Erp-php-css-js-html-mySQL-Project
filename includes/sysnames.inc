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
//----------------------------------------------------------------------------------
//	FrontAccounting global system names. They have to be stored separate from
//	sys types defines to enable inclusion after every language change
//	(which happens for multilanguage bulk reports)
//
global $systypes_array, $bank_account_types, $bank_transfer_types, 
	$payment_person_types, $wo_types_array, $wo_cost_types, $class_types,
	$quick_actions, $quick_entry_types, $stock_types, $tag_types, $bank_owner,$incoming_qc_templates,$sales_proforma_invoice_status,$oil_status,$mc_analysis_type,$wrarranty_type,$inv_print;
$mc_analysis_type=array(
1=>_("Electrical"),
2=>_("Mechanical")
);
$wrarranty_type=array(
1=>_("Manufacturer Warranty"),
2=>_("AMC"),
3=>_("CMC"),
4=>_("No Warranty")
);
//
$systypes_array = array (
	ST_JOURNAL => _("Journal Entry"),
	ST_BANKPAYMENT => _("Bank Payment"),
	ST_BANKDEPOSIT => _("Bank Deposit"),
	ST_BANKTRANSFER => _("Funds Transfer"),
	ST_SALESINVOICE => _("Sales Invoice"),
	ST_CUSTCREDIT => _("Customer Credit Note"),
	ST_CUSTPAYMENT => _("Customer Payment"),
	ST_CUSTDELIVERY => _("Delivery Note"),
	ST_LOCTRANSFER => _("Location Transfer"),
	ST_INVADJUST => _("Inventory Adjustment"),
	ST_INDENTREQUEST => _("Indent Issue"),
	ST_PURCHORDER => _("Purchase Order"),
	ST_SUPPINVOICE => _("Supplier Invoice"),
	ST_SUPPCREDIT => _("Supplier Credit Note"),
	ST_SUPPAYMENT => _("Supplier Payment"),
	ST_SUPTDSPAYMENT => _("TDS Payment"),
	ST_SUPPRECEIVE => _("Purchase Order Delivery"),
	ST_WORKORDER => _("Work Order"),
	ST_MANUISSUE => _("Work Order Issue"),
	ST_MANURECEIVE => _("Work Order Production"),
	ST_SALESORDER => _("Sales Order"),
	ST_SALESQUOTE => _("Sales Quotation"),
	ST_COSTUPDATE => _("Cost Update"),
	ST_DIMENSION => _("Dimension"),
	);

$fa_systypes_array = array (
	ST_INVADJUST => _("Fixed Assets Disposal"),
	ST_COSTUPDATE => _("Fixed Assets Revaluation"),
	);
$oil_status = array (
	1 => _("Yes"),
	0=> _("NO")
	);	

$type_shortcuts = array(
	ST_JOURNAL => _("GJ"), // general journal
	ST_BANKPAYMENT => _("BP"),
	ST_BANKDEPOSIT => _("BD"),
	ST_BANKTRANSFER => _("BT"),
	ST_SALESINVOICE => _("SI"),
	ST_CUSTCREDIT => _("CN"),
	ST_CUSTPAYMENT => _("CP"),
	ST_CUSTDELIVERY => _("DN"),
	ST_LOCTRANSFER => _("IT"), // inventory transfer
	ST_INVADJUST => _("IA"),
	ST_PURCHORDER => _("PO"),
	ST_SUPPINVOICE => _("PI"), // purchase invoice
	ST_SUPPCREDIT => _("PC"),
	ST_SUPPAYMENT => _("SP"),
	ST_SUPPRECEIVE => _("GRN"),
	ST_WORKORDER => _("WO"),
	ST_MANUISSUE => _("WI"),
	ST_MANURECEIVE => _("WP"),
	ST_SALESORDER => _("SO"),
	ST_SALESQUOTE => _("SQ"),
	ST_COSTUPDATE => _("CU"),
	ST_DIMENSION => _("Dim")
);


//----------------------------------------------------------------------------------
//		Bank transaction types
//
$bank_account_types = array (
	BT_TRANSFER => _("Savings Account"),
		_("Chequing Account"),
		_("Credit Account"),
		_("Cash Account")
	);

$bank_transfer_types = array(
	BT_TRANSFER => _("Transfer"),
			_("Cheque"),
			_("Credit"),
			_("Cash")
	);

//----------------------------------------------------------------------------------
//	Payment types
//

$payment_person_types = array (
	PT_MISC => _("Miscellaneous"),
				_("Work Order"),
				_("Customer"),
				_("Supplier"),
				_("Quick Entry")
	);

//----------------------------------------------------------------------------------
//	Manufacturing types
//
$wo_types_array = array (
	//WO_ASSEMBLY => _("Assemble"),
	//WO_UNASSEMBLY => _("Unassemble"),
	WO_ADVANCED => _("Advanced Manufacture")
	);

$wo_cost_types = array(
	WO_LABOUR => _("Labour Cost"),
	WO_OVERHEAD => _("Overhead Cost"),
	WO_MATERIALS => _("Materials")
);

//----------------------------------------------------------------------------------
//	GL account classes
//
$class_types = array(
	CL_ASSETS => _("Assets"),
	CL_LIABILITIES => _("Liabilities"),
	CL_EQUITY => _("Equity"),
	CL_INCOME => _("Income"),
	CL_COGS => _("Cost of Goods Sold"),
	CL_EXPENSE => _("Expense"),
);

//----------------------------------------------------------------------------------
//	Quick entry types
//
$quick_actions = array(
	'=' => _('Remainder'), // post current base amount to GL account
	'a' => _('Amount'), // post amount to GL account
	'a+' => _('Amount, increase base'), // post amount to GL account and increase base
	'a-' => _('Amount, reduce base'), // post amount to GL account and reduce base
	'%' => _('% amount of base'),	// store acc*amount% to GL account
	'%+' => _('% amount of base, increase base'),	// ditto & increase base amount
	'%-' => _('% amount of base, reduce base'),	// ditto & reduce base amount
	'T' => _('Taxes added'), // post taxes calculated on base amount
	'T+' => _('Taxes added, increase base'), // ditto & increase base amount
	'T-' => _('Taxes added, reduce base'), // ditto & reduce base amount
	't' => _('Taxes included'), // post taxes calculated on base amount
	't+' => _('Taxes included, increase base'), // ditto & increase base amount
	't-' => _('Taxes included, reduce base') // ditto & reduce base amount
);

$quick_entry_types = array(
	QE_DEPOSIT => _("Bank Deposit"),
	QE_PAYMENT => _("Bank Payment"),
	QE_JOURNAL => _("Journal Entry"),
	QE_SUPPINV => _("Supplier Invoice/Credit")
);

//----------------------------------------------------------------------------------
// depreciation methods
//venkat
$depreciation_methods = array(
  'D' => _("Declining balance"),
  'S' => _("Straight line"),
  'N' => _("Sum of the Year Digits"),
  'O' => _("One-time"),
);
//----------------------------------------------------------------------------------
//variable for  qc template
$incoming_qc_templates=array(
1 =>_("RM Physical"),
2=>_("RM Chemical")
);

//----------------------------------------------------------------------------------
// Types of stock items
$stock_types = array(
	'M' => _("Manufactured"),
	'B' => _("Purchased"),
	'D' => _("Service / Non Stock Items")
);

//----------------------------------------------------------------------------------

$tag_types = array (
	TAG_ACCOUNT   => _("Account"),
	TAG_DIMENSION => _("Dimension")
);

//----------------------------------------------------------------------------------
// crm contacts categories
$sys_crm_cats = array (
	'cust_branch' => _("Customer branch"),
	'supplier' => _("Supplier"),
	'shipper' => _("Shipper"),
	'company' => _("Company internal")
);
//----------------------------------------------------------------------------------

$pterm_types = array(
	PTT_PRE => _("Prepayment"),
	PTT_CASH => _("Cash"),
	PTT_DAYS => _("After No. of Days"),
	PTT_FOLLOWING => _("Day In Following Month")
);

$tax_algorithms = array( 
	TCA_LINES => _("Sum per line taxes"), 
	TCA_TOTALS => _("Taxes from totals")
);
//----------------------------------------------------------------------------------

$bank_owner_types = array(
	BO_UNKNOWN => _("Unknown"),
	BO_COMPANY => _("Company"),
	BO_CUSTBRANCH => _("Customer"),
	BO_SUPPLIER => _("Supplier")
);

// This month array is for use with the last 3 dateformats. 
$tmonths = array("", _("Jan"),_("Feb"),_("Mar"),_("Apr"),_("May"),_("Jun"),_("Jul"),_("Aug"),_("Sep"),_("Oct"),_("Nov"),_("Dec"));

//
//	FA supported db backend language settings.
//
$supported_collations = array(
  'utf8_xx' => _('Unicode (multilanguage)'),
  'utf8_is' => _('Icelandic'),
  'utf8_lv' => _('Latvian'),
  'utf8_ro' => _('Romanian'),
  'utf8_sl' => _('Slovenian'),
  'utf8_pl' => _('Polish'),
  'utf8_et' => _('Estonian'),
  'utf8_es' => _('Spanish'), // or 'spanish2',
  'utf8_sw' => _('Swedish'),
  'utf8_tr' => _('Turkish'),
  'utf8_cs' => _('Czech'),
  'utf8_da' => _('Danish'),
  'utf8_lt' => _('Lithuanian'),
  'utf8_sk' => _('Slovak'),
  'utf8_sp' => _('Spanish (alternative)'),
  'utf8_fa' => _('Persian'),
  'utf8_hu' => _('Hungarian'),
  'utf8_fr' => _('French'),
  'utf8_it' => _('Italian'),
);

//ravi gst
$business_types = array(
	1 => _("B2B"),
	2 => _("B2C"),
	3 => _("Imports"),
	4 => _("Exports")
);

$tax_payer_types = array(
	1 => _("Regular"),
	2 => _("Job Worker"),
	3 => _("SEZ without Payment"),
	4 => _("SEZ with Payment"),
	5 => _("Imports/Exports"),
	6 => _("Unregisterd GST Dealer")
);

$quantity_types = array(
	1 => _("Measure"),
	2 => _("Volume"),
	3 => _("Length"),
	4 => _("Weight"),
	5 => _("Area"),
	6 => _("Other")
);

$pan_status_types = array(
	1 => _("Available"),
	2 => _("Applied"),
	3 => _("Not Available"),
	4 => _("Not Required")
);

$tds_service_types = array(
	1 => _("Artificial Judicial Person"),
	2 => _("Body of Individuals"),
	3 => _("Company-Non Resident"),
	4 => _("Company-Resident")
);

$purchase_types = array(
	1 => _("General Purchase"),
	2 => _("RCM"),
	3 => _("Purchase Service"),
	4 => _("Purchase Expense")
);


$tds_applicable_status = array(
	1 => _("Not Applicable"),
	2 => _("Applicable")
);

//-------------------------------------------------------------------------------------------------------

$sales_proforma_invoice_status = array(
	'1' => _("Amount Initiated"),
	'2' => _("Amount Received")
);

$production_processes = array(
	'1' => _("Pickling"),
	'2' => _("Drawing"),
	'3' => _("LRPC Line / Stranding"),
	'4' => _("Layer Winder"),
	'5' => _("Oiling & Packing"),
	'6' => _("SR Furnace"),
	'7' => _("Galvanising"),
	'8' => _("Extruder"),
	'9' => _("Extruder Coiling"),
	'10' => _("No Oiling , Only Packing")
);

$product_types = array(
	'1' => _("Uncoated Stress Relieved Low Relaxation 7 Wire (Ply) Strand for Prestressed Concrete (LRPC)"),
	'2' => _("HDPE Coated Stress Relieved Low Relaxation 7 Wire (Ply) Strand for Prestressed Concrete (LRPC)"),
	'3' => _("Steel Wire for Mechanical Springs (Spring Wire)"),
	'4' => _("Indented Wire for Prestressed Concrete (IPC Wire)"),
	'5' => _("Uncoated Stress Relieved Prestressed Concrete (SR) 3 Ply & 7 Ply"),
	'6' => _("High Tensile Galvanised Wire (HTGS Wire)"),
	'7' => _("Galvanised Strand for Earthing (Earth Wire)"),
	'8' => _("Hot Dip Galvanising Stay Strand (Stay Wire)"),
	'9' => _("Mild Steel Wire for General Engineering (GI Wire)")
);

$shift_types = array(
	'A' => _("Shift A"),
	'B' => _("Shift B"),
	'C' => _("Shift C")
);
$qc_result = array(
	0 => _("Pass"),
	1 => _("Fail") 
);


$lrpc_machine_stoppage_status = array(
	'1' => _("Stop Due to Manpower"),
	'2' => _("Stop Due to Raw Material"),
	'3' => _("Stop Due to Mechanical Maintainance"),
	'4' => _("Stop Due to Electrical Maintainance"),
	'5' => _("Any Other Reason"),
	'6' => _("Stop Due to Set Change")
);

$layer_winder_coil_status = array(
	'1' => _("Weight above 3 MT"),
	'2' => _("Coil Weight in between 2.5 MT to 3 MT"),
	'3' => _("Coil Weight in between 2 MT to  2.5 MT"),
	'4' => _("Coil Weight in between 1.5 MT to 2 MT"),
	'5' => _("Coil Weight in between 1 MT to 1.5 MT"),
	'6' => _("Coil Weight in between 0.7 MT to 1 MT"),
	'7' => _("Short Coil Bundle"),
	'8' => _("Scrap Bundle")
);

$oiling_status = array(
	'1' => _("Completed"),
	'2' => _("Not Completed"),
	'3' => _("Oiling Not Required")
);


$coil_suface_condition_status = array(
	'1' => _("Smooth Surface"),
	'2' => _("Rust on the Surface"),
	'3' => _("Other Comments")
);


$final_packing_status = array(
	'1' => _("Completed"),
	'2' => _("Not Completed")
);


 $inv_print = array(
	1   => _("Original for Recipient"),
	2   => _("Duplicate for Transporter"),
	3   => _("Triplicate"),
	4   => _("Quatricate"),
	5   => _("Quintuplicate"),
	
); 

