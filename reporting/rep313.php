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
//include_once($path_to_root . "/inventory/includes/db/grade_master_db.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/includes/db/manufacturing_db.inc");
 
//----------------------------------------------------------------------------------------------------

print_inventory_purchase();

function getTransactions($item,$category)
{
if($item==''&&$category==-1)
{	
	$sql = "SELECT
master.*,master.stock_id,master.description,master.units,master.size_id,size.size,master.coil_no_req,master.inward_qc_check_req,master.hsn_code,master.tax_slab_for_good FROM ".TB_PREF."stock_master AS master LEFT JOIN ".TB_PREF."size_master AS size ON master.size_id=size.id";
}
if($item!=''&& $category==-1)
{
	$sql = "SELECT
master.*,master.stock_id,master.description,size.size,master.units,master.size_id,master.coil_no_req,master.inward_qc_check_req,master.hsn_code,master.tax_slab_for_good FROM ".TB_PREF."stock_master AS master LEFT JOIN ".TB_PREF."size_master AS size ON master.size_id=size.id WHERE stock_id=".db_escape($item);
}
if($item==''&& $category!=-1)
{
	
		$sql = "SELECT
master.*,master.stock_id,master.description,master.units,master.size_id,master.coil_no_req,size.size,master.inward_qc_check_req,master.hsn_code,master.tax_slab_for_good FROM ".TB_PREF."stock_master AS master LEFT JOIN ".TB_PREF."size_master AS size ON master.size_id=size.id  WHERE category_id=".db_escape($category);
	
}
if($item!=''&& $category!=-1)
{
	$sql = "SELECT
master.*,master.stock_id,master.description,master.units,master.size_id,master.coil_no_req,size.size,master.inward_qc_check_req,master.hsn_code,master.tax_slab_for_good FROM ".TB_PREF."stock_master AS master LEFT JOIN ".TB_PREF."size_master AS size ON master.size_id=size.id WHERE stock_id=".db_escape($item)."AND category_id=".db_escape($category);
}	
	
	
    return db_query($sql,"No transactions were returned");

}
function get_category($category)
{
	$sql="SELECT cat.*,cat.description FROM ".TB_PREF."stock_category AS cat WHERE category_id=".db_escape($category);
	
	$result=db_query($sql,"No transactions were returned");
    $row = db_fetch_row($result);
	return $row[1];
}


//----------------------------------------------------------------------------------------------------

function print_inventory_purchase()
{
    global $path_to_root;
    $item=$_POST['PARAM_0'];
	$category = $_POST['PARAM_1'];
	$orientation = $_POST['PARAM_2'];
	$destination = $_POST['PARAM_3'];
	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$orientation = ($orientation ? 'L' : 'P');
    $dec = user_price_dec();
	$cat=get_category($category);
	if($cat=='')
	{
		$cat='ALL ITEMS';
	}
	


	$cols = array(0, 60, 120, 200, 280, 350, 420, 480,	520);

	$headers = array(_('Stock'), _('Description'),_('UOM'), _('Size'),_('Coil Number '),_('Qc Status'),_('Hsn Code'),_('Tax Slab'));
	if ($fromsupp != '')
		$headers[4] = '';

	$aligns = array('left',	'left',	'right', 'right', 'right', 'right', 'right', 'right');

    $params =   array(0 => $comments,
	    	1 => array('text' => _('Category'), 'from' => $cat, 'to' => ''),
);
 	
           

    $rep = new FrontReport(_('Item  Listing '), "ItemListingReport", user_pagesize(), 9, $orientation);
    if ($orientation == 'L')
    	recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();

	$res = getTransactions($item,$category);

	//($total = $total_supp = $grandtotal = 0.0); //left if someone needs them for own needs
	//($total_qty = 0.0);
	 $i=1;
	while ($trans=db_fetch($res))
	{
		

		
			 
			$rep->TextCol(0 , 1, $trans['stock_id']);
		    $rep->TextCol(1 , 2, $trans['description']);
			$rep->TextCol(2 , 3, $trans['units']);
			if($trans['size']=='')
			{
			$rep->TextCol(3 , 4, "N/A");
			}
			else
			{	
			$rep->TextCol(3 , 4, $trans['size']);
			}
			if($trans['coil_no_req']==0)
			{
				$ans='No';
			}
			else
			{
				$ans='yes';
			}
			$rep->TextCol(4 , 5, $ans);
		   if($trans['inward_qc_check_req']==0)
			{
				$ans='No';
			}
			else
			{
				$ans='yes';
			}
			$rep->TextCol(5 , 6, $ans);
			$rep->TextCol(6 , 7, $trans['hsn_code']);
			$rep->TextCol(7 , 8, $trans['tax_slab_for_good']);
			 



			
		 
			
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

