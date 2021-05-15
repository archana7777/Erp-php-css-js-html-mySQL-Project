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

//--------------------------------------------------------------------------------------

function display_bom($item_check)
{
    $result = get_bom($item_check);

    if (db_num_rows($result) == 0)
    {
    	display_note(_("The bill of material for this item is empty."), 0, 1);
    }
    else
    {

        start_table(TABLESTYLE);
        $th = array(_("Component"), _("Description"), _("Work Centre"),
        	_("From Location"), _("Quantity"), _("Unit Cost"), _("Cost"));

        table_header($th);

        $j = 1;
        $k = 0; //row colour counter

        $total_cost = 0;

        while ($myrow = db_fetch($result))
        {

			alt_table_row_color($k);

			label_cell($myrow["component"]);
			label_cell($myrow["description"]);
			label_cell($myrow["WorkCentreDescription"]);
			label_cell($myrow["location_name"]);
			qty_cell($myrow["quantity"], false, get_qty_dec($myrow["component"]));
			amount_cell($myrow["ProductCost"]);
			amount_cell($myrow["ComponentCost"]);
			end_row();
        	$total_cost += $myrow["ComponentCost"];

        	$j++;
        	if ($j == 12)
        	{
        		$j = 1;
        		table_header($th);
        	}
		}

		$item = get_item($item_check);
		if ($item['labour_cost'] != 0)
		{
			$total_cost += $item['labour_cost'];
			alt_table_row_color($k);
	        label_cells(_("Standard Labour Cost"), number_format2($item['labour_cost'],user_price_dec()),
    	    	"colspan=6 align=left", "class=odd_row nowrap align=right");
    	    end_row();
		}
		if ($item['overhead_cost'] != 0)
		{
			$total_cost += $item['overhead_cost'];
			alt_table_row_color($k);
	        label_cells(_("Standard Overhead Cost"), number_format2($item['overhead_cost'],user_price_dec()),
    	    	"colspan=6 align=left", "nowrap align=right");
    	    end_row();
		}
        label_row("<b>" . _("Total Cost") . "</b>", "<b>" . number_format2($total_cost,user_price_dec()) ."</b>",
        	"colspan=6 align=right", "nowrap align=right");
		end_table();
    }
}

//--------------------------------------------------------------------------------------

function display_wo_requirements($woid, $quantity, $show_qoh=false, $date=null)
{
	global $SysPrefs;

    $result = get_wo_requirements($woid);

    if (db_num_rows($result) == 0)
    {
    	display_note(_("There are no Requirements for this Order."), 1, 0);
    }
    else
    {

        start_table(TABLESTYLE, "width='80%'");
        $th = array(_("Component"), _("From Location"), _("Work Centre"),
        	_("Unit Quantity"), _("Total Quantity"), _("Units Issued"), _("On Hand"));

        table_header($th);

        $k = 0; //row colour counter
		$has_marked = false;

		if ($date == null)
			$date = Today();

        while ($myrow = db_fetch($result))
        {

			$qoh = 0;
			$show_qoh = true;
			// if it's a non-stock item (eg. service) don't show qoh
			if (!has_stock_holding($myrow["mb_flag"]))
				$show_qoh = false;

			if ($show_qoh)
				$qoh = get_qoh_on_date($myrow["stock_id"], $myrow["loc_code"], $date);

			if ($show_qoh && ($myrow["units_req"] * $quantity > $qoh) &&
				!$SysPrefs->allow_negative_stock())
			{
				// oops, we don't have enough of one of the component items
				start_row("class='stockmankobg'");
				$has_marked = true;
			}
			else
				alt_table_row_color($k);

        	if (user_show_codes())
        		label_cell($myrow["stock_id"]. " - " . $myrow["description"]);
        	else
        		label_cell($myrow["description"]);

			label_cell($myrow["location_name"]);
			label_cell($myrow["WorkCentreDescription"]);
			$dec = get_qty_dec($myrow["stock_id"]);
            qty_cell($myrow["units_req"], false, $dec);
			qty_cell($myrow["units_req"] * $quantity, false, $dec);
            qty_cell($myrow["units_issued"], false, $dec);
			if ($show_qoh)
				qty_cell($qoh, false, $dec);
			else
				label_cell("");
			end_row();

		}

		end_table();

		if ($has_marked)
			display_note(_("Marked items have insufficient quantities in stock."), 0, 0, "class='red'");
    }
}

//--------------------------------------------------------------------------------------

function display_wo_productions($woid)
{
	global $path_to_root;

    $result = get_work_order_productions($woid);

    if (db_num_rows($result) == 0)
    {
    	display_note(_("There are no Productions for this Order."), 1, 1);
    }
    else
    {
        start_table(TABLESTYLE);
        $th = array(_("#"), _("Reference"), _("Date"), _("Quantity"));

        table_header($th);

        $k = 0; //row colour counter
		$total_qty = 0;

        while ($myrow = db_fetch($result))
        {

			alt_table_row_color($k);

			$total_qty += $myrow['quantity'];

        	label_cell(get_trans_view_str(ST_MANURECEIVE, $myrow["id"]));
			label_cell($myrow['reference']);
			label_cell(sql2date($myrow["date_"]));
			qty_cell($myrow['quantity'], false, get_qty_dec($myrow['reference']));
			end_row();
		}//end of while

		label_row(_("Total"), number_format2($total_qty,user_qty_dec()),
			"colspan=3", "nowrap align=right");

		end_table();
    }
}

//--------------------------------------------------------------------------------------

function display_wo_issues($woid)
{
	global $path_to_root;

    $result = get_work_order_issues($woid);

    if (db_num_rows($result) == 0)
    {
    	display_note(_("There are no Issues for this Order."), 0, 1);
    }
    else
    {
        start_table(TABLESTYLE);
        $th = array(_("#"), _("Reference"), _("Date"));

        table_header($th);

        $k = 0; //row colour counter

        while ($myrow = db_fetch($result))
        {

			alt_table_row_color($k);

        	label_cell(get_trans_view_str(ST_MANUISSUE, $myrow["issue_no"]));
			label_cell($myrow['reference']);
			label_cell(sql2date($myrow["issue_date"]));
			end_row();
		}

		end_table();
    }
}

//--------------------------------------------------------------------------------------

function display_wo_payments($woid)
{
	global $path_to_root, $wo_cost_types;

    $result = get_wo_costing($woid);

    if (db_num_rows($result) == 0)
    {
    	display_note(_("There are no additional costs for this Order."), 0, 1);
    }
    else
    {
        start_table(TABLESTYLE);
        $th = array(_("#"), _("Type"), _("Date"), _("Amount"));

        table_header($th);

        $k = 0;
        while ($myrow = db_fetch($result))
        {
			alt_table_row_color($k);

        	label_cell(get_gl_view_str( $myrow["trans_type"], $myrow["trans_no"], $myrow["trans_no"]));
        	label_cell($wo_cost_types[$myrow['cost_type']]);
        	$date = sql2date($myrow["tran_date"]);
        	label_cell($date);
			amount_cell($myrow['amount']);
			end_row();
		}

		end_table();
    }
}

//--------------------------------------------------------------------------------------

function display_wo_details($woid, $suppress_view_link=false)
{
	global $wo_types_array;

	$myrow = get_work_order($woid, true);

    if ($myrow == false)
    {
    	display_note(_("The work order number sent is not valid."));
    	exit;
    }

   	start_table(TABLESTYLE, "width='90%'");

	if ($myrow["released"] == true)
		$th = array(_("#"), _("Reference"), _("Manufactured Item"),
			_("Into Location"),_("Date"),_("Target Start Date"), _("Target Close Date"), _("Qty Required (in MT)"),_("Qty Per Day (in MT)"),
			_("Released Date"), _("Manufactured"));
	else
		$th = array(_("#"), _("Reference"), _("Manufactured Item"),
			_("Into Location"), _("Date"),_("Target Start Date"), _("Target Close Date"), _("Qty Required"),_("Qty Per Day"));

	table_header($th);
	start_row();
	if ($suppress_view_link)
		label_cell($myrow["id"]);
	else
		label_cell(get_trans_view_str(ST_WORKORDER, $myrow["id"]));
	label_cell($myrow["wo_ref"]);
	view_stock_status_cell($myrow["stock_id"], $myrow["StockItemName"]);
	label_cell($myrow["location_name"]);
	label_cell(sql2date($myrow["date_"]));
	label_cell(sql2date($myrow["execute_by"]));
	label_cell(sql2date($myrow["required_by"]));
	$dec = get_qty_dec($myrow["stock_id"]);
	qty_cell($myrow["units_reqd"], false, $dec);
    qty_cell($myrow["units_reqs_per_day"], false, $dec);
	if ($myrow["released"] == true)
	{
		label_cell(sql2date($myrow["released_date"]));
		qty_cell($myrow["units_issued"], false, $dec);
	}
	end_row();

	comments_display_row(ST_WORKORDER, $woid);

	end_table();

    if ($myrow["closed"] == true)
    {
    	display_note(_("This work order is closed."));
    }
}

//--------------------------------------------------------------------------------------

function display_wo_details_quick($woid, $suppress_view_link=false)
{
	global $wo_types_array;

	$myrow = get_work_order($woid, true);

    if ($myrow === false)
    {
    	display_note(_("The work order number sent is not valid."));
    	exit;
    }

   	start_table(TABLESTYLE, "width='80%'");

	$th = array(_("#"), _("Reference"), _("Type"), _("Manufactured Item"),
		_("Into Location"), _("Date"), _("Quantity"));
	table_header($th);

	start_row();
	if ($suppress_view_link)
		label_cell($myrow["id"]);
	else
		label_cell(get_trans_view_str(ST_WORKORDER, $myrow["id"]));
	label_cell($myrow["wo_ref"]);
	label_cell($wo_types_array[$myrow["type"]]);
	view_stock_status_cell($myrow["stock_id"], $myrow["StockItemName"]);
	label_cell($myrow["location_name"]);
	label_cell(sql2date($myrow["date_"]));

	qty_cell($myrow["units_issued"], false, get_qty_dec($myrow["stock_id"]));

	end_row();

	comments_display_row(ST_WORKORDER, $woid);

	end_table();

    if ($myrow["closed"] == true)
    {
    	display_note(_("This work order is closed."));
    }
}


//ravi

function display_wo_details_view($woid, $suppress_view_link=false)
{
	global $wo_types_array;

	$wo_details = get_work_order($woid, true);
	$wo_wm_tm_details = get_wm_tm_work_order($woid, true);

    if ($wo_details == false)
    {
    	display_note(_("The work order number sent is not valid."));
    	exit;
    }

//comments_display_row(ST_WORKORDER, $woid);
  display_heading(_("WORK ORDER FROM TOP MANAGEMENT(TM) TO WORKS MANAGER(WM)"));
  br();
   	start_table(TABLESTYLE, "width='80%'");
	$th = array(_("Complete Data for Work Order Execution"), _("Details Provided by TM"));
	table_header($th);

	label_row(_("01. Unique Work Order Reference No. #"), $wo_wm_tm_details['wo_ref']);
	label_row(_("02. Item Code "), $wo_wm_tm_details['stock_id']);
	label_row(_("03. Product Name "), $wo_wm_tm_details['description']);
	label_row(_("04. Size"), $wo_wm_tm_details['size']);
	label_row(_("05. Grade"), $wo_wm_tm_details['grade']);
	label_row(_("06. Oilded or Unoiled"), $wo_wm_tm_details['oil_status']==0?'Unoiled':'Oiled');
	label_row(_("07. Reference Specification"), $wo_wm_tm_details['standard']);
	label_row(_("08. Any Specific Information for the Work Order"), $wo_wm_tm_details['work_order_memo']);
	label_row(_("09. Quantity to Produce in MT"), $wo_wm_tm_details['units_reqd']);
	label_row(_("10. Targetted Start Date for the Execution of Work Order"), sql2date($wo_wm_tm_details['execute_by']));
	label_row(_("11. Targetted Close Date of the Work Order"), sql2date($wo_wm_tm_details['required_by']));
	label_row(_("12. Targetted Production per Day in MT"), $wo_wm_tm_details['units_reqs_per_day']);
	
	end_table();
	
	br();br();
	
	if($wo_details['wm_tm_status'])
	{
	display_heading(_("RETURN OF WORK ORDER FROM WORKS MANAGER TO TOP MANAGEMENT FOR APPROVAL"));
	br();
   	start_table(TABLESTYLE, "width='80%'");
	$th = array(_("Complete Data Required for Work Order Execution"), _("Details Provided by TM"), _("Accepted"), _("Proposed By WM(if not Accepted)"));
	table_header($th);
	
	start_row();
	label_cells(_("08. Any Specific Information for the Work Order"), $wo_wm_tm_details['work_order_memo']);
	label_cells($wo_wm_tm_details['wo_memo_status']==0?'Accepted':"Not Accepted", $wo_wm_tm_details['proposed_work_order_memo']);
	end_row();
	
	start_row();
	label_cells(_("09. Quantity to Produce in MT"), $wo_wm_tm_details['units_reqd']);
	label_cells($wo_wm_tm_details['units_reqd_status']==0?'Accepted':"Not Accepted", $wo_wm_tm_details['proposed_units_reqd']);
	end_row();
	
	start_row();
	label_cells(_("10. Targetted Start Date for the Execution of Work Order"), sql2date($wo_wm_tm_details['execute_by']));
	label_cells($wo_wm_tm_details['execute_by_status']==0?'Accepted':"Not Accepted", sql2date($wo_wm_tm_details['proposed_execute_by']));
	end_row();
	
	start_row();
	label_cells(_("11. Targetted Close Date of the Work Order"), sql2date($wo_wm_tm_details['required_by']));
	label_cells($wo_wm_tm_details['required_by_status']==0?'Accepted':"Not Accepted", sql2date($wo_wm_tm_details['proposed_required_by']));
	end_row();
	
	start_row();
	label_cells(_("12. Targetted Production per Day in MT"), $wo_wm_tm_details['units_reqs_per_day']);
	label_cells($wo_wm_tm_details['units_reqs_per_day_status']==0?'Accepted':"Not Accepted", $wo_wm_tm_details['proposed_units_reqs_per_day']);
	end_row();
	
	start_row();
	label_cells(_("12. Grace Period Required if any"), $wo_wm_tm_details['grace_period']);
	end_row();
	end_table();
	
	br();
	display_heading(_("RESOURCE REQUIREMENT BY WORKS MANAGER TO TOP MANAGEMENT"));
	br();
   	start_table(TABLESTYLE, "width='80%'");
	$th = array(_("Requirement"), _("Available Options"), _("New Procurement"), _("Remarks"));
	table_header($th);
	
	start_row();
	label_cells(_("01. Raw Material"), $wo_wm_tm_details['rm_available options']);
	label_cells($wo_wm_tm_details['rm_procurement'], $wo_wm_tm_details['rm_remarks']);
	end_row();
	
	start_row();
	label_cells(_("02. Machine Booked"), $wo_wm_tm_details['machine_booked']);
	end_row();
	
	start_row();
	label_cells(_("03. Any Other Specific Requiremnt"), $wo_wm_tm_details['other_remarks']);
	end_row();
	
	end_table();
	
	br();
	
		if ($wo_details["approved"]==0)
		{
    	display_note(_("Approval Pending from Top Management."));
		}
	
	}
	else
	{
	   display_note(_("Update Pending from Works Manager."));
	}
	
	
    if ($wo_details["closed"] == true)
    {
    	display_note(_("This work order is closed."));
    }
}

function display_pickled_items_summary($woid)
{
	
	$myrow = get_work_order($woid, true);
	
	$pickled_items_summary=get_pickled_items_summary($woid);

    start_table(TABLESTYLE, "width='60%'");

	
		$th = array(_("Pickled Coils Count"), _("Work Order Qty(in MT)"), _("Pickled Coils Qty(in Kgs)"),
			_("Balance (in Kgs)"), _("View"));
	
	table_header($th);
	start_row();
	
	
	label_cell($pickled_items_summary["count"],'align=center');
	qty_cell($myrow["units_reqd"], false, 2);
    qty_cell($pickled_items_summary["total_qty"], false, 2);
	qty_cell(($myrow["units_reqd"]*1000)-($pickled_items_summary["total_qty"]), false, 2);
	label_cell(get_manuf_trans_view_str(ST_MANUFPICKLING,$woid),'align=center');	
	end_row();

	
	end_table();


}

function display_drawing_items_summary($woid)
{
	

	$drawing_items_summary=get_drawing_items_summary($woid);

    start_table(TABLESTYLE, "width='80%'");

	
		$th = array(_("Spool Code"), _("Size in (MM)"), _("Produced Length in Mtr"),
			_("Weight (in Kgs)"),_("In Time"),_("Out Time"),_("No. of Weld Joints"),_("Weld Joint Meters"), _("View"));
	
	table_header($th);
     while($drawing_item=db_fetch($drawing_items_summary))
	 {
		start_row();
		label_cell($drawing_item["spool_code"],'align=center');
		label_cell($drawing_item["size"],'align=center');
		label_cell($drawing_item["produced_length"],'align=center');
		label_cell($drawing_item["qty"],'align=center');
		label_cell($drawing_item["spool_in_time"],'align=center');
		label_cell($drawing_item["spool_out_time"],'align=center');
		label_cell($drawing_item["no_of_weld_joints"],'align=center');
		label_cell($drawing_item["weld_joint_meters"],'align=center');
		label_cell(get_manuf_trans_view_str(ST_MANUFDRAWING,$woid,$drawing_item["spool_code"]),'align=center');	
		end_row();
     }
	
	end_table();


}


function display_lrpc_details_summary($woid)
{
	
	$lrpc_items_summary=get_lrpc_items_summary($woid);

    start_table(TABLESTYLE, "width='80%'");

	
		$th = array(_("SET No"),_("Size"), _("LRPC Line No."), _("Take Up No."),_("Length in Mtr"),
			_("Weight (in Kgs)"),_("Loading Start Time"),_("Loading End Time"),_("Reason"),_("View"));
	
	global $lrpc_machine_stoppage_status;
	table_header($th);
     while($lrpc_item=db_fetch($lrpc_items_summary))
	 {
		start_row();
		label_cell($lrpc_item["set_no"],'align=center');
		label_cell($lrpc_item["size"],'align=center');
		label_cell($lrpc_item["lrpc_line_no"],'align=center');
		label_cell($lrpc_item["take_up_no"],'align=center');
		label_cell($lrpc_item["length_in_mtr"],'align=center');
		label_cell($lrpc_item["qty"],'align=center');
		label_cell($lrpc_item["loading_start_time"],'align=center');
		label_cell($lrpc_item["loading_end_time"],'align=center');
		label_cell($lrpc_machine_stoppage_status[$lrpc_item["reason"]]);
		label_cell(get_manuf_trans_view_str(ST_MANUFLRPC,$woid,$lrpc_item["set_no"]),'align=center');	
		end_row();
     }
	
	end_table();

}


function display_layer_winder_details($woid)
{
	$layer_winder_coils=get_layer_winder_coils($woid);

    start_table(TABLESTYLE, "width='80%'");

	
		$th = array(_("Coil No"),_("Size"),_("Length in Mtr"),_("Weight"),_("Coil Status"),_("View"));
	
	global $layer_winder_coil_status;
	table_header($th);
     while($layer_winder_coil=db_fetch($layer_winder_coils))
	 {
		start_row();
		label_cell($layer_winder_coil["winder_coil_no"],'align=center');
		label_cell($layer_winder_coil["size"],'align=center');
		label_cell($layer_winder_coil["length_in_mtr"],'align=center');
		label_cell($layer_winder_coil["qty"],'align=center');
		label_cell($layer_winder_coil_status[$layer_winder_coil["coil_status"]]);
		label_cell(get_manuf_trans_view_str(ST_MANUFLAYERWINDER,$woid,$layer_winder_coil['winder_coil_no']),'align=center');	
		end_row();
     }
	
	end_table();
}


function display_oiling_packing_details($woid)
{
	$oiling_packing_coils=get_oiling_packing_coils($woid);

    start_table(TABLESTYLE, "width='80%'");

	
		$th = array(_("Coil No"),_("Size"),_("Product Class"),_("Net Qty"),_("Gross Qty"),_("View"));
	
	global $layer_winder_coil_status;
	table_header($th);
     while($oiling_packing_coil=db_fetch($oiling_packing_coils))
	 {
		start_row();
		label_cell($oiling_packing_coil["coil_no"],'align=center');
		label_cell($oiling_packing_coil["size"],'align=center');
		label_cell($oiling_packing_coil["product_class"],'align=center');
		qty_cell($oiling_packing_coil["net_qty"], false, 2);
		qty_cell($oiling_packing_coil["gross_qty"], false, 2);
		label_cell(get_manuf_trans_view_str(ST_MANUFOILINGPACKING,$woid,$oiling_packing_coil['coil_no']),'align=center');	
		end_row();
     }
	
	end_table();
}

function display_galvanising_details($woid)
{
	$galvanised_coils=get_galvanised_coils($woid);

    start_table(TABLESTYLE, "width='80%'");

	
		$th = array(_("Coil No"),_("Size"),_("Length in Mtr"),_("Weight"),_("View"));
	
	  table_header($th);
     while($galvanised_coil=db_fetch($galvanised_coils))
	 {
		start_row();
		label_cell($galvanised_coil["coil_no"],'align=center');
		label_cell($galvanised_coil["size"],'align=center');
		label_cell($galvanised_coil["length_in_mtr"],'align=center');
		label_cell($galvanised_coil["qty"],'align=center');
		label_cell(get_manuf_trans_view_str(ST_MANUFGALVANISING,$woid,$galvanised_coil['coil_no']),'align=center');	
		end_row();
     }
	
	end_table();
}

function display_srfurnace_details($woid)
{
	$srfurnaced_coils=get_srfurnaced_coils($woid);

    start_table(TABLESTYLE, "width='80%'");

	
		$th = array(_("Coil No"),_("Size"),_("Length in Mtr"),_("Weight"),_("Machine Status"),_("View"));
	
	global $lrpc_machine_stoppage_status;
	table_header($th);
     while($srfurnaced_coil=db_fetch($srfurnaced_coils))
	 {
		start_row();
		label_cell($srfurnaced_coil["coil_no"],'align=center');
		label_cell($srfurnaced_coil["size"],'align=center');
		label_cell($srfurnaced_coil["length_in_mtr"],'align=center');
		label_cell($srfurnaced_coil["qty"],'align=center');
		label_cell($lrpc_machine_stoppage_status[$srfurnaced_coil["machine_status"]]);
		label_cell(get_manuf_trans_view_str(ST_MANUFSRFURNACE,$woid,$srfurnaced_coil['coil_no']),'align=center');	
		end_row();
     }
	
	end_table();
}


function display_extruder_details($woid)
{
	$extruder_coils=get_extruder_coils($woid);

    start_table(TABLESTYLE, "width='80%'");

	
		$th = array(_("Coil No"),_("Take Up"),_("Size"),_("Length in Mtr"),_("Weight"),_("Steel Weight"),_("Grease Weight"),_("HDPE Weight"),_("View"));
	
	table_header($th);
     while($extruder_coil=db_fetch($extruder_coils))
	 {
		start_row();
		label_cell($extruder_coil["extruder_coil_no"],'align=center');
		label_cell($extruder_coil["take_up_no"],'align=center');
		label_cell($extruder_coil["size"],'align=center');
		label_cell($extruder_coil["length_in_mtr"],'align=center');
		label_cell($extruder_coil["qty"],'align=center');
		label_cell($extruder_coil["steel_qty"],'align=center');
		label_cell($extruder_coil["grease_qty"],'align=center');
		label_cell($extruder_coil["hdpe_qty"],'align=center');
		label_cell(get_manuf_trans_view_str(ST_MANUFEXTRUDER,$woid,$extruder_coil['extruder_coil_no']),'align=center');	
		end_row();
     }
	
	end_table();
}

function display_extrudercoiling_details($woid)
{
	$extrudercoiling_coils=get_extrudercoiling_coils($woid);

    start_table(TABLESTYLE, "width='80%'");

	
		$th = array(_("Coil No"),_("Take Up"),_("Size"),_("Length in Mtr"),_("Weight"),_("Steel Weight"),_("Grease Weight"),_("HDPE Weight"),_("View"));
	
	table_header($th);
     while($extrudercoiling_coil=db_fetch($extrudercoiling_coils))
	 {
		start_row();
		label_cell($extrudercoiling_coil["extrudercoiling_coil_no"],'align=center');
		label_cell($extrudercoiling_coil["rm_take_up_no"],'align=center');
		label_cell($extrudercoiling_coil["size"],'align=center');
		label_cell($extrudercoiling_coil["length_in_mtr"],'align=center');
		label_cell($extrudercoiling_coil["qty"],'align=center');
		label_cell($extrudercoiling_coil["steel_qty"],'align=center');
		label_cell($extrudercoiling_coil["grease_qty"],'align=center');
		label_cell($extrudercoiling_coil["hdpe_qty"],'align=center');
		label_cell(get_manuf_trans_view_str(ST_MANUFEXTRUDERCOILING,$woid,$extrudercoiling_coil['extrudercoiling_coil_no']),'align=center');	
		end_row();
     }
	
	end_table();
}