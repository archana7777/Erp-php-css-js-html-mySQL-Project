<?php
/**********************************************************************
    GST R1 DB
***********************************************************************/
//--------------------------------------------------------------------------------
function get_b2b_transactions($from_date,$to_date)
{
	global $SysPrefs;

	$from = date2sql($from_date);
	$to = date2sql($to_date);

	$sql = "SELECT tran.*,state.state_name FROM ".TB_PREF."debtor_trans tran,".TB_PREF."gst_state_codes state WHERE tran.business_type=1 and  state.id=tran.state_code and tran.tran_date>='$from' and tran.tran_date<='$to' and tran.type=10 and tran.tax_payer_type!=3";

	return db_query($sql, "The transactions for could not be retrieved");
}

function get_b2cl_transactions($from_date,$to_date)
{
	global $SysPrefs;

	$from = date2sql($from_date);
	$to = date2sql($to_date);

	$sql = "SELECT tran.*,state.state_name FROM ".TB_PREF."debtor_trans tran,".TB_PREF."gst_state_codes state WHERE tran.business_type=2 and  state.id=tran.state_code and tran.tran_date>='$from' and tran.tran_date<='$to' and tran.type=10 and tran.tax_payer_type!=3 and tran.ov_gst>=250000";

	return db_query($sql, "The transactions for could not be retrieved");
}

function get_b2cs_transactions($from_date,$to_date)
{
	global $SysPrefs;

	$from = date2sql($from_date);
	$to = date2sql($to_date);

	$sql = "SELECT tran.*,state.state_name FROM ".TB_PREF."debtor_trans tran,".TB_PREF."gst_state_codes state WHERE tran.business_type=2 and  state.id=tran.state_code and tran.tran_date>='$from' and tran.tran_date<='$to' and tran.type=10 and tran.tax_payer_type!=3 and tran.ov_gst<250000";

	return db_query($sql, "The transactions for could not be retrieved");
}

function get_export_transactions($from_date,$to_date)
{
	global $SysPrefs;

	$from = date2sql($from_date);
	$to = date2sql($to_date);

	$sql = "SELECT tran.* FROM ".TB_PREF."debtor_trans tran WHERE tran.business_type=4 and tran.tran_date>='$from' and tran.tran_date<='$to' and tran.type=10";

	return db_query($sql, "The transactions for could not be retrieved");
}

function get_hsn_transactions($from_date,$to_date)
{
	global $SysPrefs;
	$from = date2sql($from_date);
	$to = date2sql($to_date);
	
	$sql="SELECT stock.hsn_code,stock.description,stock.units,sum(trand.quantity) as quantity,sum(trand.quantity*trand.unit_price*(100+tax_rate)*0.01) as total_value,sum(trand.quantity*trand.unit_price) as taxable_value FROM ".TB_PREF."debtor_trans tran,".TB_PREF."debtor_trans_details trand,".TB_PREF."stock_master stock WHERE tran.trans_no=trand.debtor_trans_no and tran.type=10 and tran.business_type in (1,2) and trand.stock_id=stock.stock_id and tran.tran_date>='$from' and tran.tran_date<='$to' and trand.debtor_trans_type=10 group by stock.stock_id";

	
	return db_query($sql, "The transactions for could not be retrieved");
}

function get_integrated_tax($hsn_code,$state_code,$from_date,$to_date)
{
	global $SysPrefs;
	$from = date2sql($from_date);
	$to = date2sql($to_date);
	
	$sql="SELECT sum(trand.quantity*trand.unit_price*trand.tax_rate*0.01) FROM ".TB_PREF."debtor_trans tran,".TB_PREF."debtor_trans_details trand,".TB_PREF."stock_master stock WHERE tran.trans_no=trand.debtor_trans_no and tran.business_type in (1,2) and tran.tax_payer_type!=3 and tran.state_code!=".db_escape($state_code)." and tran.type=10 and tran.tran_date>='$from' and tran.tran_date<='$to' and stock.hsn_code=".db_escape($hsn_code)." and stock.stock_id=trand.stock_id  and trand.debtor_trans_type=10";
		
	$result=db_query($sql, "The transactions for could not be retrieved");
	$row=db_fetch_row($result);
	return $row[0];	
}

function get_state_tax($hsn_code,$state_code,$from_date,$to_date)
{
	global $SysPrefs;
	$from = date2sql($from_date);
	$to = date2sql($to_date);
	
	$sql="SELECT sum(trand.quantity*trand.unit_price*trand.tax_rate*0.01) FROM ".TB_PREF."debtor_trans tran,".TB_PREF."debtor_trans_details trand,".TB_PREF."stock_master stock WHERE tran.trans_no=trand.debtor_trans_no and tran.business_type in (1,2) and tran.tax_payer_type!=3 and tran.state_code=".db_escape($state_code)." and tran.type=10 and tran.tran_date>='$from' and tran.tran_date<='$to' and stock.hsn_code=".db_escape($hsn_code)." and stock.stock_id=trand.stock_id and trand.debtor_trans_type=10";
	
	$result=db_query($sql, "The transactions for could not be retrieved");
	$row=db_fetch_row($result);
	return $row[0];	
}

function get_gst_tax_rate($trans_no)
{	
  $sql = "SELECT max(tax_rate) FROM ".TB_PREF."debtor_trans_details WHERE debtor_trans_no=".db_escape($trans_no)." and debtor_trans_type=10";
  $result=db_query($sql, "The transactions for could not be retrieved");
  $row=db_fetch_row($result);
  return $row[0];
}
?>