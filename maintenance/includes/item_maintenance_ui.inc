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
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui/items_cart.inc");

//--------------------------------------------------------------------------------

function add_to_order(&$order, $new_item, $new_item_qty, $standard_cost)
{

    if ($order->find_cart_item($new_item))
         display_error(_("For Part :") . $new_item . " " . "This item is already on this document. You can change the quantity on the existing line if necessary.");
	else
         $order->add_to_cart (count($order->line_items), $new_item, $new_item_qty, $standard_cost);
}

//--------------------------------------------------------------------------------

function display_order_header(&$order)
{
	global $Refs;

	start_outer_table(TABLESTYLE2, "width='70%'"); // outer table
	table_section(1);

	locations_list_row(_("Location:"), 'StockLocation', null, false, false, $order->fixed_asset);
    date_row(_("Date:"), 'AdjDate', '', true);

	table_section(2, "50%");

	ref_row(_("Reference:"), 'ref', '', $Refs->get_next(ST_MAINTISSUES, null, array('location'=>get_post('StockLocation'), 'date'=>get_post('AdjDate'))),
		 false, ST_MAINTISSUES);
		
	//shiva	
	inventory_item_types_list_row(_("Item Type:"), 'item_type', null, true);


	end_outer_table(1); // outer table
}
//--------------------------------------------------------------------------------
//shiva
function display_order_header_new1(&$order,$schedule_id)
{

	global $Ajax;
	global $Refs;

	start_outer_table(TABLESTYLE2, "width='70%'"); // outer table
	table_section(1);

	//locations_list_row(_("Location:"), 'StockLocation', null, false, false, $order->fixed_asset);
	
	/*onchange_location_category_list_row(_("Location Category:"),'loc_cat',null, false, true);
	
		if(list_updated('loc_cat'))  // onchange Location Category
	{
		$Ajax->activate('loc_subcat');
		$Ajax->activate('loc_subsubcat');
		$Ajax->activate('StockLocation');
	}
	 */

	/* onchange_location_sub_cat_list_row(_("Location Sub Category:"),'loc_subcat',null,false,true,$_POST["loc_cat"]);
		if(list_updated('loc_subcat'))  // onchange Location Sub Category
	{
		$Ajax->activate('loc_subsubcat');
		$Ajax->activate('StockLocation');
	} */
	/* onchange_location_subsub_cat_list_row(_("Location Sub Subcategory:"),'loc_subsubcat',null,false,true,$_POST["loc_subcat"]);
			if(list_updated('loc_subsubcat'))  // onchange Location SubSubCategory
	{
		
		$Ajax->activate('StockLocation');
	} */
	loc_group_list_row(_("Location Group Name:"), 'loc_group_id', null,false,true);
	if(list_updated('loc_group_id'))  // onchange Location SubSubCategory
	{
		
		$Ajax->activate('StockLocation');
	}
	locationsgrp_list_row(_("Location:"), 'StockLocation', null,false, true,'');
	//onchange_inventory_location_list_row(_("From Location:"), 'StockLocation', null, false, true, $_POST["loc_subsubcat"]);
	table_section(2, "50%");
	
    date_row(_("Date:"), 'AdjDate', '', true);

   

	ref_row(_("Reference:"), 'ref', '', $Refs->get_next(ST_MAINTISSUES, null, array('location'=>get_post('StockLocation'), 'date'=>get_post('AdjDate'))),false, ST_MAINTISSUES);
		hidden('schedule_id',$schedule_id);
	
	label_cells(_("Schedule Id:"),$schedule_id,"class='label'");	 
		
	end_outer_table(1); // outer table
}

//---------------------------------------------------------------------------------

function display_maintenance_items($title, &$order)
{
	global $path_to_root;

	display_heading($title);
    div_start('items_table');
	start_table(TABLESTYLE, "width='80%'");

	if ($order->fixed_asset) {
		$th = array(_("Item Code"), _("Item Description"), _("Available Quantity"), _("Quantity"),
		_("Unit"), _("Unit Cost"), _("Initial Value"), "");
	} else {
		$th = array(_("Item Code"), _("Item Description"), _("Available Quantity"), _("Quantity"),
		_("Unit"), "","");
		if ( count($order->line_items)) $th[] = '';
	}
	if ( count($order->line_items)) $th[] = '';

	table_header($th);
	$total1 = 0;
	$k = 0;  //row colour counter
	$total = 0;
	$low_stock = $order->check_qoh($_POST['StockLocation'], $_POST['AdjDate']);
	$id = find_submit('Edit');
	foreach ($order->line_items as $line_no=>$stock_item)
	{

		$total1 += ($stock_item->quantity);

		if ($id != $line_no)
		{
			if (in_array($stock_item->stock_id, $low_stock))
				start_row("class='stockmankobg'");	// notice low stock status
			else 
				alt_table_row_color($k);

    		if ($order->fixed_asset)
				label_cell($stock_item->stock_id);
      		else
				view_stock_status_cell($stock_item->stock_id);

			label_cell($stock_item->item_description);
			$tot_stk = get_tot_stock($stock_item->stock_id,$_POST['StockLocation']);
			label_cell($tot_stk, '', 'tot_stk');
    		qty_cell($stock_item->quantity, false, get_qty_dec($stock_item->stock_id));
			label_cell($stock_item->units);
		
      		if (!$order->fixed_asset) {
        		edit_button_cell("Edit$line_no", _("Edit"),	_('Edit document line'));
      		}
			delete_button_cell("Delete$line_no", _("Delete"), _('Remove line from document'));
			end_row();
		}
		else
		{
			adjustment_edit_item_controls($order, $line_no);
		}
	}

	if ($id == -1)
		adjustment_edit_item_controls($order);

	label_row(_("Total"), number_format2($total1), "align=right colspan=2", "align=right", 2);

    end_table();
	if ($low_stock)
		display_note(_("Marked items have insufficient quantities in stock as on day of adjustment."), 0, 1, "class='stockmankofg'");
	div_end();
}

//---------------------------------------------------------------------------------

function adjustment_edit_item_controls(&$order, $line_no=-1)
{
	global $Ajax;
	start_row();

	$dec2 = 0;
	$id = find_submit('Edit');
	if ($line_no != -1 && $line_no == $id)
	{
		$_POST['stock_id'] = $order->line_items[$id]->stock_id;
		$_POST['qty'] = qty_format($order->line_items[$id]->quantity, 
			$order->line_items[$id]->stock_id, $dec);
		//$_POST['std_cost'] = price_format($order->line_items[$id]->standard_cost);
		//$_POST['std_cost'] = price_decimal_format($order->line_items[$id]->standard_cost, $dec2);
		$_POST['units'] = $order->line_items[$id]->units;

		hidden('stock_id', $_POST['stock_id']);
		label_cell($_POST['stock_id']);
		label_cell($order->line_items[$id]->item_description, 'nowrap');
	    $Ajax->activate('items_table');
	}
	else
	{
		if ($order->fixed_asset)
			stock_disposable_fa_list_cells(null, 'stock_id', null, false, true, $order->line_items);
		else
			stock_costable_items_list_cells(null, 'stock_id', null, false, true);

		if (list_updated('stock_id')) {
			    $Ajax->activate('units');
			    $Ajax->activate('qty');
			    $Ajax->activate('std_cost');
			    $Ajax->activate('std_cost_view');
			    $Ajax->activate('initial_price');
				$Ajax->activate('_page_body');
		}

    	$item_info = get_item_edit_info($_POST['stock_id']);
		$dec = $item_info['decimals'];
   		$_POST['qty'] = number_format2(0, $dec);
		//$_POST['std_cost'] = price_decimal_format($item_info["material_cost"], $dec2);
		$_POST['units'] = $item_info["units"];
	}
		$tot_stk = get_tot_stock($_POST['stock_id'],$_POST['StockLocation']);
			label_cell($tot_stk, '', 'tot_stk');
	if ($order->fixed_asset) {
		hidden('qty', -1);
		qty_cell(1, false, 0);
	} else
		qty_cells(null, 'qty', $_POST['qty'], null, null, $dec);

	label_cell($_POST['units'], '', 'units');

	if ($order->fixed_asset) {
		/* TODO: check if this is a correct way of creating a fixed item
			that still has to be submitted in the form */ 
		hidden('std_cost');
		//label_cell($_POST['std_cost'], "nowrap align=right ", 'std_cost_view');
		$price = get_purchase_value($_POST['stock_id']);
		//amount_cell($price, false, '', 'initial_price');
	} else {
		//amount_cells(null, 'std_cost', $_POST['std_cost']);
		//amount_cells(null, 'std_cost', null, null, null, $dec2);
		label_cell("&nbsp;");
	}

	if ($id != -1)
	{
		button_cell('UpdateItem', _("Update"),
				_('Confirm changes'), ICON_UPDATE);
		button_cell('CancelItemChanges', _("Cancel"),
				_('Cancel changes'), ICON_CANCEL);
		hidden('LineNo', $line_no);
 		set_focus('qty');
	}
	else
	{
		submit_cells('AddItem', _("Add Item"), "colspan=2",
		    _('Add new item to document'), true);
	}

	end_row();
}


//---------------------------------------------------------------------------------

function adjustment_options_controls()
{
	  echo "<br>";
	  start_table();

	  textarea_row(_("Memo"), 'memo_', null, 50, 3);

	  end_table(1);
}


//---------------------------------------------------------------------------------
//Shiva
//Animal Adjustments
//--------------------------------------------------------------------------------
//shiva
function display_order_header_animal(&$order)
{
		global $Ajax;
	global $Refs;

	start_outer_table(TABLESTYLE2, "width='70%'"); // outer table
	table_section(1);

	//locations_list_row(_("Location:"), 'StockLocation', null, false, false, $order->fixed_asset);
	
	onchange_location_category_list_row(_("Location Category:"),'loc_cat',null, false, true);
	
		if(list_updated('loc_cat'))  // onchange Location Category
	{
		$Ajax->activate('loc_subcat');
		$Ajax->activate('loc_subsubcat');
		$Ajax->activate('StockLocation');
	}
	

	onchange_location_sub_cat_list_row(_("Location Sub Category:"),'loc_subcat',null,false,true,$_POST["loc_cat"]);
		if(list_updated('loc_subcat'))  // onchange Location Sub Category
	{
		$Ajax->activate('loc_subsubcat');
		$Ajax->activate('StockLocation');
	}
	onchange_location_subsub_cat_list_row(_("Location Sub Subcategory:"),'loc_subsubcat',null,false,true,$_POST["loc_subcat"]);
			if(list_updated('loc_subsubcat'))  // onchange Location SubSubCategory
	{
		
		$Ajax->activate('StockLocation');
	}
	
	onchange_inventory_location_list_row(_("From Location:"), 'StockLocation', null, false, true, $_POST["loc_subsubcat"]);
	table_section(2, "50%");
	
    date_row(_("Date:"), 'AdjDate', '', true);



	ref_row(_("Reference:"), 'ref', '', $Refs->get_next(ST_MAINTISSUES, null, array('location'=>get_post('StockLocation'), 'date'=>get_post('AdjDate'))),
		 false, ST_MAINTISSUES);
		
	//shiva	
	//inventory_item_types_list_row(_("Item Type:"), 'item_type', null, true);

	//inventory_item_types_list_row(_("Item Type:"), 'item_type', null, true);


	end_outer_table(1); // outer table
}
function get_tot_stock($stock_id,$location){
$sql = "SELECT sum(qty) FROM ".TB_PREF."stock_moves WHERE stock_id=".db_escape($stock_id)." AND loc_code=".db_escape($location)."";
//display_error($sql);
$res = db_query($sql);
$result = db_fetch_row($res);
if($result['0'] == ''){
return '0';
}else{
return $result['0'];
}
}
