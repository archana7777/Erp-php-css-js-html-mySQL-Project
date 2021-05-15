<?php
$page_security = 'SA_GLTRANSVIEW';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/reporting/includes/Workbook.php");
include_once($path_to_root . "/gl/includes/db/gst_db.inc");

$js = '';
if ($SysPrefs->use_popup_windows)
	$js .= get_js_open_window(800, 500);
if (user_use_date_picker())
	$js .= get_js_date_picker();

page(_($help_context = "GSTR-1 Inquiry"), false, false, '', $js);

start_form();
    start_table(TABLESTYLE_NOBORDER);
	$date = today();
	if (!isset($_POST['TransToDate']))
		$_POST['TransToDate'] = end_month($date);
	if (!isset($_POST['TransFromDate']))
		$_POST['TransFromDate'] = add_days(end_month($date), -user_transaction_days());
	start_row();
	date_cells(_("From:"), 'TransFromDate');
	date_cells(_("To:"), 'TransToDate');
    submit_cells('Show',_("Show"),'','', 'default');
	end_row();
	end_table();

	echo '<hr>';
    end_form();

if (get_post('Show')) 
{
	display_notification(_("Please Click on View/Download File to download GSTR-1 Report."));
// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer_Workbook('GSTR1-Report.xls');

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('Help Instruction');

// The actual data
$worksheet->write(5, 1, 'Help Instructions');
$worksheet->write(6, 1, 'Generated From Tech Integra ERP');

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('b2b');
// The actual data
$worksheet->write(0, 0, 'Summary For B2B(4)');
$worksheet->write(4, 0, 'GSTIN/UIN of Recipient');
$worksheet->write(4, 1, 'Invoice Number');
$worksheet->write(4, 2, 'Invoice date');
$worksheet->write(4, 3, 'Invoice Value');
$worksheet->write(4, 4, 'Place Of Supply');
$worksheet->write(4, 5, 'Reverse Charge');
$worksheet->write(4, 6, 'Applicable % of Tax Rate');
$worksheet->write(4, 7, 'Invoice Type');
$worksheet->write(4, 8, 'E-Commerce GSTIN');
$worksheet->write(4, 9, 'Rate');
$worksheet->write(4, 10, 'Taxable Value');
$worksheet->write(4, 11, 'Cess Amount');
//fetching b2b data
$b2b_transactons=get_b2b_transactions($_POST['TransFromDate'],$_POST['TransToDate']);
$i=5;
global $tax_payer_types;

while($b2b_transacton=db_fetch($b2b_transactons))
{
 $tax_rate=get_gst_tax_rate($b2b_transacton['trans_no']);
$worksheet->write($i, 0, $b2b_transacton['branch_gst']);
$worksheet->write($i, 1, $b2b_transacton['reference']);
$tran_date=date("j-F-Y", strtotime($b2b_transacton['tran_date']));
$worksheet->write($i, 2, $tran_date);
$worksheet->write($i, 3, $b2b_transacton['ov_amount']+$b2b_transacton['ov_gst']+$b2b_transacton['ov_freight']+$b2b_transacton['ov_freight_tax']);
$worksheet->write($i, 4, $b2b_transacton['state_name']);
$worksheet->write($i, 5, 'N');
$worksheet->write($i, 7, $tax_payer_types[$b2b_transacton['tax_payer_type']]);
$worksheet->write($i, 9, $tax_rate);
$worksheet->write($i, 10, $b2b_transacton['ov_amount']+$b2b_transacton['ov_freight']);
$i++;
}

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('b2cl');
// The actual data
$worksheet->write(0, 0, 'Summary For B2CL(5)');
$worksheet->write(4, 0, 'Invoice Number');
$worksheet->write(4, 1, 'Invoice date');
$worksheet->write(4, 2, 'Invoice Value');
$worksheet->write(4, 3, 'Place Of Supply');
$worksheet->write(4, 4, 'Applicable % of Tax Rate');
$worksheet->write(4, 5, 'Rate');
$worksheet->write(4, 6, 'Taxable Value');
$worksheet->write(4, 7, 'Cess Amount');
$worksheet->write(4, 8, 'E-Commerce GSTIN');
$worksheet->write(4, 9, 'Sale from Bonded WH');
//fetching b2cl data
$b2cl_transactons=get_b2cl_transactions($_POST['TransFromDate'],$_POST['TransToDate']);
$i=5;
while($b2cl_transacton=db_fetch($b2cl_transactons))
{
 $tax_rate=get_gst_tax_rate($b2cl_transacton['trans_no']);
$worksheet->write($i, 0, $b2cl_transacton['reference']);
$tran_date=date("j-F-Y", strtotime($b2cl_transacton['tran_date']));
$worksheet->write($i, 1, $tran_date);
$worksheet->write($i, 2, $b2cl_transacton['ov_amount']+$b2cl_transacton['ov_gst']+$b2cl_transacton['ov_freight']+$b2cl_transacton['ov_freight_tax']);
$worksheet->write($i, 3, $b2cl_transacton['state_name']);
$worksheet->write($i, 4, $tax_rate);
$worksheet->write($i, 6, $b2cl_transacton['ov_amount']+$b2cl_transacton['ov_freight']);
$i++;
}


// Creating a worksheet
$worksheet =& $workbook->addWorksheet('b2cs');
// The actual data
$worksheet->write(0, 0, 'Summary For B2CS(7)');
$worksheet->write(4, 0, 'Type');
$worksheet->write(4, 1, 'Place Of Supply');
$worksheet->write(4, 2, 'Rate');
$worksheet->write(4, 3, 'Taxable Value');
$worksheet->write(4, 4, 'Cess Amount');
$worksheet->write(4, 5, 'E-Commerce GSTIN');
//fetching b2cs data
$b2cs_transactons=get_b2cs_transactions($_POST['TransFromDate'],$_POST['TransToDate']);
$i=5;
while($b2cs_transacton=db_fetch($b2cs_transactons))
{
 $tax_rate=get_gst_tax_rate($b2cs_transacton['trans_no']);
$worksheet->write($i, 0, "OE");
$worksheet->write($i, 1, $b2cs_transacton['state_name']);
$worksheet->write($i, 2, $tax_rate);
$worksheet->write($i, 3, $b2cs_transacton['ov_amount']+$b2cl_transacton['ov_freight']);
$i++;
}

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('cdnr');
// The actual data
$worksheet->write(0, 0, 'Summary For CDNR(9B)');
$worksheet->write(4, 0, 'GSTIN/UIN of Recipient');
$worksheet->write(4, 1, 'Invoice/Advance Receipt Number');
$worksheet->write(4, 2, 'Invoice/Advance Receipt date');
$worksheet->write(4, 3, 'Note/Refund Voucher Number');
$worksheet->write(4, 4, 'Note/Refund Voucher date');
$worksheet->write(4, 5, 'Document Type');
$worksheet->write(4, 6, 'Place Of Supply');
$worksheet->write(4, 7, 'Note/Refund Voucher Value');
$worksheet->write(4, 8, 'Applicable % of Tax Rate');
$worksheet->write(4, 9, 'Rate');
$worksheet->write(4, 10, 'Taxable Value');
$worksheet->write(4, 11, 'Cess Amount');
$worksheet->write(4, 12, 'Pre GST');


// Creating a worksheet
$worksheet =& $workbook->addWorksheet('cdnur');
// The actual data
$worksheet->write(0, 0, 'Summary For CDNUR(9B)');
$worksheet->write(4, 0, 'UR Type');
$worksheet->write(4, 1, 'Note/Refund Voucher Number');
$worksheet->write(4, 2, 'Note/Refund Voucher date');
$worksheet->write(4, 3, 'Document Type');
$worksheet->write(4, 4, 'Invoice/Advance Receipt Number');
$worksheet->write(4, 5, 'Invoice/Advance Receipt date');
$worksheet->write(4, 6, 'Place Of Supply');
$worksheet->write(4, 7, 'Note/Refund Voucher Value');
$worksheet->write(4, 8, 'Applicable % of Tax Rate');
$worksheet->write(4, 9, 'Rate');
$worksheet->write(4, 10, 'Taxable Value');
$worksheet->write(4, 11, 'Cess Amount');
$worksheet->write(4, 12, 'Pre GST');


// Creating a worksheet
$worksheet =& $workbook->addWorksheet('exp');
// The actual data
$worksheet->write(0, 0, 'Summary For EXP(6)');
$worksheet->write(4, 0, 'Export Type');
$worksheet->write(4, 1, 'Invoice Number');
$worksheet->write(4, 2, 'Invoice date');
$worksheet->write(4, 3, 'Invoice Value');
$worksheet->write(4, 4, 'Port Code');
$worksheet->write(4, 5, 'Shipping Bill Number');
$worksheet->write(4, 6, 'Shipping Bill Date');
$worksheet->write(4, 7, 'Applicable % of Tax Rate');
$worksheet->write(4, 8, 'Rate');
$worksheet->write(4, 9, 'Taxable Value');
$worksheet->write(4, 10, 'Cess Amount');

//fetching export data
$export_transactons=get_export_transactions($_POST['TransFromDate'],$_POST['TransToDate']);
$i=5;
while($export_transacton=db_fetch($export_transactons))
{
 $tax_rate=get_gst_tax_rate($export_transacton['trans_no']);
$worksheet->write($i, 0, "WOPAY");
$worksheet->write($i, 1, $export_transacton['reference']);
$tran_date=date("j-F-Y", strtotime($export_transacton['tran_date']));
$worksheet->write($i, 2, $tran_date);
$worksheet->write($i, 3, $export_transacton['ov_amount']+$export_transacton['ov_gst']+$export_transacton['ov_freight']+$export_transacton['ov_freight_tax']);
$worksheet->write($i, 4, $export_transacton['port_code']);
$worksheet->write($i, 5, $export_transacton['shipping_bill_no']);
$tran_bill_date=date("j-F-Y", strtotime($export_transacton['shipping_bill_date']));
$worksheet->write($i, 6, $tran_bill_date);
$worksheet->write($i, 8, $tax_rate);
$worksheet->write($i, 9, $export_transacton['ov_amount']+$b2cl_transacton['ov_freight']);
$i++;
}

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('at');
// The actual data
$worksheet->write(0, 0, 'Summary For Advance Received (11B)');
$worksheet->write(4, 0, 'Place Of Supply');
$worksheet->write(4, 1, 'Rate');
$worksheet->write(4, 2, 'Gross Advance Received');
$worksheet->write(4, 3, 'Cess Amount');

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('atadj');
// The actual data
$worksheet->write(0, 0, 'Summary For Advance Adjusted (11B)');
$worksheet->write(4, 0, 'Place Of Supply');
$worksheet->write(4, 1, 'Rate');
$worksheet->write(4, 2, 'Gross Advance Adjusted');
$worksheet->write(4, 3, 'Cess Amount');

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('exemp');
// The actual data
$worksheet->write(0, 0, 'Summary For Nil rated, exempted and non GST outward supplies (8)');
$worksheet->write(4, 0, 'Description');
$worksheet->write(4, 1, 'Nil Rated Supplies');
$worksheet->write(4, 2, 'Exempted (other than nil rated/non GST supply )');
$worksheet->write(4, 3, 'Non-GST supplies');

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('hsn');
// The actual data
$worksheet->write(0, 0, 'Summary For HSN(12)');
$worksheet->write(4, 0, 'HSN');
$worksheet->write(4, 1, 'Description');
$worksheet->write(4, 2, 'UQC');
$worksheet->write(4, 3, 'Total Quantity');
$worksheet->write(4, 4, 'Total Value');
$worksheet->write(4, 5, 'Taxable Value');
$worksheet->write(4, 6, 'Integrated Tax Amount');
$worksheet->write(4, 7, 'Central Tax Amount');
$worksheet->write(4, 8, 'State/UT Tax Amount');
$worksheet->write(4, 10, 'Cess Amount');
//fetching hsn data
$hsn_transactons=get_hsn_transactions($_POST['TransFromDate'],$_POST['TransToDate']);
$i=5;
$company = get_company_prefs();
$company_state_code=$company['state_code'];
while($hsn_transacton=db_fetch($hsn_transactons))
{
$worksheet->write($i, 0, $hsn_transacton['hsn_code']);
$worksheet->write($i, 1, $hsn_transacton['description']);
$worksheet->write($i, 2, $hsn_transacton['units']);
$worksheet->write($i, 3, $hsn_transacton['quantity']);
$worksheet->write($i, 4, $hsn_transacton['total_value']);
$worksheet->write($i, 5, $hsn_transacton['taxable_value']);

$igst_tax=get_integrated_tax($hsn_transacton['hsn_code'],$company_state_code,$_POST['TransFromDate'],$_POST['TransToDate']);
$state_tax=get_state_tax($hsn_transacton['hsn_code'],$company_state_code,$_POST['TransFromDate'],$_POST['TransToDate']);
$worksheet->write($i, 6, $igst_tax);
$worksheet->write($i, 7, $state_tax/2);
$worksheet->write($i, 8, $state_tax/2);

$i++;
}

// Creating a worksheet
$worksheet =& $workbook->addWorksheet('docs');
// The actual data
$worksheet->write(0, 0, 'Summary of documents issued during the tax period (13)');
$worksheet->write(4, 0, 'Nature of Document');
$worksheet->write(4, 1, 'Sr. No. From');
$worksheet->write(4, 2, 'Sr. No. To');
$worksheet->write(4, 3, 'Total Number');
$worksheet->write(4, 4, 'Cancelled');
// Let's send the file
$workbook->close();
echo "<a href='$path_to_root/gl/inquiry/GSTR1-Report.xls'>View/Download file</a>";
//header("Location: ".$path_to_root."/gl/inquiry/GSTR1-Report.xls");
}
?>