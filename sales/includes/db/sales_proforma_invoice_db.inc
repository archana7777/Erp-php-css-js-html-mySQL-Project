
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
//-----------------------------------------------------------------------------
function add_sales_proforma_invoice($reference,$type,$sales_order_no,$kind_atten,$subject,$date_,$product_type,$invoice_amount,$freight_charges,$gst_percent,$payment,$price_basis,$comment,$proforma_status,$cust_payment_reference)
{
	$date_=date2sql($date_);
	$sql="INSERT INTO ".TB_PREF."proforma_invoice(reference,type,sales_order_no,kind_atten,subject,date_,product_type,invoice_amount,freight_charges,gst_percent,payment,price_basis,comment,proforma_status,cust_payment_reference) VALUES (".db_escape($reference).",".db_escape($type).",".db_escape($sales_order_no).",".db_escape($kind_atten).",".db_escape($subject).",".db_escape($date_).",".db_escape($product_type).",".db_escape($invoice_amount).",".db_escape($freight_charges).",".db_escape($gst_percent).",".db_escape($payment).",".db_escape($price_basis).",".db_escape($comment).",".db_escape($proforma_status).",".db_escape($cust_payment_reference).")";
//display_error($sql);die;
	db_query($sql,"something went wrong");
} 


function update_sales_proforma_invoice($selected_id,$reference,$kind_atten,$subject,$date_,$product_type,$invoice_amount,$freight_charges,$gst_percent,$payment,$price_basis,$comment,$proforma_status,$cust_payment_reference)
{
	$date_=date2sql($date_);
	$sql="UPDATE ".TB_PREF."proforma_invoice SET 
	kind_atten=".db_escape($kind_atten).", 
	subject=".db_escape($subject).", 
	date_=".db_escape($date_).", 
	product_type=".db_escape($product_type).", 
	invoice_amount=".db_escape($invoice_amount).", 
	freight_charges=".db_escape($freight_charges).", 
	gst_percent=".db_escape($gst_percent).", 
	payment=".db_escape($payment).", 
	price_basis=".db_escape($price_basis).", 
	comment=".db_escape($comment).", 
	proforma_status=".db_escape($proforma_status).", 
	cust_payment_reference=".db_escape($cust_payment_reference)."
	WHERE id=".db_escape($selected_id)."";
	//display_error($sql); die;
	$res=db_query($sql);
}



function get_sales_proforma_invoice($selected_id)
{
	$sql="SELECT * FROM ".TB_PREF."proforma_invoice WHERE id=".db_escape($selected_id)."";
	$res=db_query($sql);
	return $result=db_fetch($res);
}


function get_sales_proforma_invoice_details($sales_order_no)
{
	$sql="SELECT pro.*,crm.name as crm_person_name FROM ".TB_PREF."proforma_invoice as pro,".TB_PREF."crm_persons as crm 
	WHERE crm.id=pro.kind_atten AND sales_order_no=".db_escape($sales_order_no)."";
	
	return db_query($sql, "could not query locations");
	
}
function delete_sales_proforma_invoice($type_id)
{
	$sql="DELETE FROM ".TB_PREF."proforma_invoice WHERE id=".db_escape($type_id);

	db_query($sql, "could not delete item subcategory");
}

function get_sales_order_date($sales_order_no)
{
	$sql = "SELECT ord_date FROM ".TB_PREF."sales_orders WHERE trans_type='30' AND order_no=".db_escape($sales_order_no);

	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}

function get_sales_order_customer_branch($sales_order_no)
{
	$sql = "SELECT branch_code FROM ".TB_PREF."sales_orders WHERE trans_type='30' AND order_no=".db_escape($sales_order_no);

	$result = db_query($sql, "could not get sales type");
	$row = db_fetch_row($result);
	return $row[0];
}


function get_sql_for_sales_orders_number_details($trans_no)
{

	$sql = "SELECT 
			sorder.order_no,
			sorder.reference,
			debtor.name,
			branch.br_name,"
			.($filter=='InvoiceTemplates' 
				|| $filter=='DeliveryTemplates' ?
			 "sorder.comments, " : "sorder.customer_ref, ")
			."sorder.ord_date,
			sorder.delivery_date,
			sorder.deliver_to,
			Sum(line.unit_price*line.quantity*(1-line.discount_percent))+freight_cost AS OrderValue,
			sorder.type,
			debtor.curr_code,
			Sum(line.qty_sent) AS TotDelivered,
			Sum(line.quantity) AS TotQuantity,
			Sum(line.invoiced) AS TotInvoiced,
			alloc,
			prep_amount,
			allocs.ord_payments,
			inv.inv_payments,
			sorder.total,
			sorder.trans_type
		FROM ".TB_PREF."sales_orders as sorder
		LEFT JOIN (SELECT trans_no_to, sum(amt) ord_payments FROM ".TB_PREF."cust_allocations WHERE trans_type_to=".ST_SALESORDER." GROUP BY trans_no_to)
			 allocs ON sorder.trans_type=".ST_SALESORDER." AND allocs.trans_no_to=sorder.order_no
		LEFT JOIN (SELECT order_, sum(prep_amount) inv_payments	FROM ".TB_PREF."debtor_trans WHERE type=".ST_SALESINVOICE." GROUP BY order_)
				 inv ON sorder.trans_type=".ST_SALESORDER." AND inv.order_=sorder.order_no,"
			.TB_PREF."sales_order_details as line, "
			.TB_PREF."debtors_master as debtor, "
			.TB_PREF."cust_branch as branch
			WHERE sorder.order_no = line.order_no
			AND sorder.trans_type = line.trans_type
			AND sorder.trans_type = '30'
			AND sorder.debtor_no = debtor.debtor_no
			AND sorder.branch_code = branch.branch_code
			AND debtor.debtor_no = branch.debtor_no
			AND sorder.order_no = ".db_escape($trans_no)."	";
		

		
		$sql .= " GROUP BY sorder.order_no,
					sorder.debtor_no,
					sorder.branch_code,
					sorder.customer_ref,
					sorder.ord_date,
					sorder.deliver_to";
				
	return db_query($sql, "could not query locations");
}
?>