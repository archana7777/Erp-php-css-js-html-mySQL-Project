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

/**
	Document blueprint use following parameters set in doctext.inc:

	$Addr1,	$Addr2 - address info
	$Payment_Terms - payment terms line
	$Footer - footer texts
	$this->company - company info
	$this->title - report title
	$this->formData - some other info
***/

		$this->row = $this->pageHeight - $this->topMargin;

		$upper = $this->row - 1.8 * $this->lineHeight_Sales;
		$lower = $this->bottomMargin + 8 * $this->lineHeight_Sales;
		$iline1 = $upper + 0.5 * $this->lineHeight_Sales;
		
		if($this->formData['doctype'] == 10){
			$iline2 = $iline1 + 0.5 * $this->lineHeight_Sales;
			$iline3 = $iline2 - 20.5 * $this->lineHeight_Sales;
			$lower = $this->bottomMargin + 0.5* $this->lineHeight_Sales;
		}
		else {
			$iline2 = $iline1 - 10 * $this->lineHeight_Sales;
			$iline3 = $iline2 - 1.5 * $this->lineHeight_Sales;
		}
		
		$iline4 = $iline3 - 3.5 * $this->lineHeight_Sales;
		$iline5 = $iline4 - 2 * $this->lineHeight_Sales;
		$iline6 = $iline5 - 1.5 * $this->lineHeight_Sales;
		$iline7 = $lower;
		
		
		$iline8 = $iline5 + 17.2 * $this->lineHeight_Sales;
		
		$iline9 = $iline5 + 8.5 * $this->lineHeight_Sales;
		
		$iline10 = $iline1 + 0.5 * $this->lineHeight_Sales;
		$iline11 = $iline3 - 2.5 * $this->lineHeight_Sales;
		
		
		$iline12 = $iline3 - 5.5 * $this->lineHeight_Sales;
		
		$right = $this->pageWidth - $this->rightMargin;
		if($this->formData['doctype'] == 10){
			$width = ($right +70) / 2;
		}
		else{
			$width = ($right - $this->leftMargin) / 5;
		}
		
		$icol = $this->pageWidth / 2;
		$ccol = $this->cols[0] + 4;
		$c2col = $ccol + 60;
		$ccol2 = $icol / 2;
		$mcol = $icol + 8;
		$mcol2 = $this->pageWidth - $ccol2;
		$cols = count($this->cols);
		
		if($this->formData['doctype'] != 10){
		$this->SetDrawColor(205, 205, 205);
		$this->Line($iline1, 3);
		$this->SetDrawColor(128, 128, 128);
		$this->Line($iline1);
		}
		
		
		$this->rectangle($this->leftMargin, $iline2, $right - $this->leftMargin, $iline2 - $iline8, "", null, array(222, 231, 236)); 
  
		$this->Line($iline8);
		$this->Line($iline9);
		$this->Line($iline12);

		$this->rectangle($this->leftMargin, $iline5, $right - $this->leftMargin, $iline5 - $iline6, "F", null, array(222, 231, 236));   
		$this->Line($iline5);
		$this->Line($iline6);
		$this->Line($iline7);
		
		
		$opt= get_options();
		
		 $this->Font('bold');
		 //$this->Text($mcol +100, $opt, $ccol);
		 //$this->fontSize += 2;
		
		if($_GET['PARAM_8'] == 1 || $_GET['PARAM_8'] == 2){
		$this->Text($mcol +180, $opt);
		}
		else{
		$this->Text($mcol + 230, $opt);
		}
		//$this->fontSize -= 2;
		 $this->Font();
		
		//$this->LineTo($this->leftMargin, $iline2 ,$this->leftMargin, $iline4);
		
		$this->LineTo($this->leftMargin, $iline2 ,$this->leftMargin, $iline12);
		
		$col = $this->leftMargin;
		
        /*
		for ($i = 0; $i < 5; $i++)
		{
			$this->LineTo($col += $width, $iline10,$col, $iline11);
		}
		*/
		
		for ($i = 0; $i < 5; $i++)
		{
			$this->LineTo($col += $width-5, $iline10,$col, $iline12);
			
			
		}
		
		$this->LineTo($right, $iline2 ,$right, $iline12);
		$this->LineTo($this->leftMargin, $iline5 ,$this->leftMargin, $iline7);
		
		if ($this->l['a_meta_dir'] == 'rtl') { // avoid line overwrite in rtl language
			$this->LineTo($this->cols[$cols - 2], $iline5 ,$this->cols[$cols - 2], $iline7);
			}
		else {	
	/*	if($this->formData['doctype'] == 10){
			$this->LineTo($this->cols[$cols - 2] - 430, $iline5 ,$this->cols[$cols - 2] -430, $iline7);
			$this->LineTo($this->cols[$cols - 2] - 370, $iline5 ,$this->cols[$cols - 2] -370, $iline7);
			$this->LineTo($this->cols[$cols - 2] - 260, $iline5 ,$this->cols[$cols - 2] -260, $iline7);
			$this->LineTo($this->cols[$cols - 2] - 205, $iline5 ,$this->cols[$cols - 2] -205, $iline7);
			$this->LineTo($this->cols[$cols - 2] - 125, $iline5 ,$this->cols[$cols - 2] -125, $iline7);	
			$this->LineTo($this->cols[$cols - 2] - 95, $iline5 ,$this->cols[$cols - 2] - 95, $iline7);			
			$this->LineTo($this->cols[$cols - 2] - 55, $iline5 ,$this->cols[$cols - 2] - 55, $iline7);
			$this->LineTo($this->cols[$cols - 2] + 2, $iline5 ,$this->cols[$cols - 2] + 2, $iline7);
		$this->LineTo($right, $iline5 ,$right, $iline7);
			}
		else {*/
			$this->LineTo($right, $iline5 ,$right, $iline7);
			//}
				
		}

		$this->fontSize += 6;
		$this->Font('bold');
		$this->Text($mcol - 60, _("Tax Invoice"));
		$this->fontSize -= 6;
		$this->Font();
		// Company Logo
		$this->NewLine_Sales();
		//$logo = company_path() . "/images/" . $this->company['coy_logo'];
		//if ($this->company['coy_logo'] != '' && file_exists($logo))
		//{
			//$this->AddImage($logo, $ccol, $this->row, 0, 40);
				//$this->NewLine_Sales();
		//}
		//else
		//{
			
		
		
		//}
		// Document title
		$this->SetTextColor(190, 190, 190);
		$this->fontSize += 10;
		$this->Font('bold');
		$this->TextWrap($mcol, $this->row, $this->pageWidth - $this->rightMargin - $mcol - 120, '', 'right');
		$this->Font();
		$this->fontSize -= 10;
		$this->NewLine_Sales();
		$this->SetTextColor(0, 0, 0);
		$adrline = $this->row;
	
		if (@$this->company['coy_name'])
		{
		 
		$this->fontSize += 2;
			$this->Font('bold');
			$this->Text($ccol, $this->company['coy_name'], $icol);
			$this->Font();
			$this->fontSize -= 2;
			$this->NewLine_Sales();
			//$this->NewLine_Sales();
			
		}

		// Company data
		$row = get_payment_terms($this->formData['payment_terms']);
		
		
		$this->TextWrapLines($ccol, $icol, $this->company['postal_address']);
		
		$logo = company_path() . "/images/" . $this->company['coy_logo'];
		  
		 
		
		//$this->Font('italic');
		if (@$this->company['gst_no'])
		{
		
			$this->Text($ccol, _("GSTIN/UIN "), $c2col);
			$this->Text($ccol+80, $this->company['gst_no'], $c2col);
			$this->NewLine_Sales();
		}
		
		 if ($this->company['coy_logo'] != '' && file_exists($logo))
		{
			//$this->AddImage($logo, $ccol+10, $this->row, 0, 50);
			//$this->NewLine_Sales();
		}
		
		$res1=get_state_name($this->company['state_code']);
		$result1=db_fetch($res1);
		$comp_state = $result1['state_name'];
		
		if (@$this->company['gst_no'])
		{    
		
			$this->Text($ccol, _("State Name "), $c2col);
			$this->Text($ccol+80,substr($comp_state,3,100), $mcol);
			$this->Text($ccol+200, _("Code : ") .substr($comp_state,0,2), $c2col);
			$this->NewLine_Sales();
		}
		
		if (@$this->company['gst_no'])
		{
	       
			$this->Text($ccol, _("CIN"), $c2col);
			$this->Text($ccol+80, _("U27300MP2004PTC029530"), $mcol);
			$this->NewLine_Sales();
		}
		
		if (@$this->company['phone'])
		{
		     
			$this->Text($ccol, _("Contact"), $c2col);
			$this->Text($ccol+80, $this->company['phone'].','.$this->company['coy_no'], $mcol);
			
			$this->NewLine_Sales();
			
		}
		
		
		
		if (@$this->company['email'])
		{
		  
			$this->Text($ccol, _("Email"), $c2col);

			$url = "mailto:" . $this->company['email'];
			$this->SetTextColor(0, 0, 255);
			$this->Text($ccol+80, $this->company['email'], $mcol);
			$this->SetTextColor(0, 0, 0);
			$this->addLink($url, $ccol+80, $this->row, $mcol, $this->row + $this->lineHeight_Sales);
			$this->NewLine_Sales();
			
		}
		
			
		
		//header right
		$this->row = $adrline;
		$this->Text($mcol + 50, $this->formData['document_name']);
		$this->Text($mcol + 120, _("e-Way Bill No"));
		$this->Text($mcol + 220, _("Dated"));
		$this->NewLine_Sales();
		$this->font('bold');
		$this->Text($mcol + 50, $this->formData['document_number']);
		$this->Text($mcol + 120, $this->formData['eway_bill_no']);
		$this->Text($mcol + 220, sql2date($this->formData['document_date']));
		$this->font();
		
		//$this->NewLine_Sales();
		$this->NewLine_Sales();
		$this->Text($mcol + 50, _("Delivery Note"));
		$this->Text($mcol + 150,_("Mode/Terms of Payment"));
		$this->NewLine_Sales();
		$this->font('bold');
		$this->Text($mcol + 50, $this->formData['delivery_note_no']);
		$this->Text($mcol + 150, $row["terms"]);
		$delivery = get_current_invoice_sales_delivery_details($this->formData['order_']);
		$buyer_order_details = get_sales_order_buyer_order_no_details($this->formData['order_']);
		$this->font();
		
		//$this->NewLine_Sales();
		$this->NewLine_Sales();
		$this->Text($mcol + 50, _("Supplier Ref"));
		$this->Text($mcol + 150,_("Other Reference(s)"));
		$this->NewLine_Sales();	
		$this->font('bold');
	    $this->Text($mcol + 50, $this->formData['customer_ref']);
		$this->Text($mcol + 150,$this->formData['other_reference']);
		$this->font();
		
		$this->NewLine_Sales();
		$this->Text($mcol + 50, _("Buyer's Order No"));
		$this->Text($mcol + 150,_("Dated"));
		$this->NewLine_Sales();
		$this->font('bold');
		// $this->Text($mcol + 50, $buyer_order_details['reference']);
		$this->Text($mcol + 50, $this->formData['customer_ref']);
		if(sql2date($this->formData['buyers_order_date'])!='00/00/0000')
               {
			$this->Text($mcol + 150,sql2date($this->formData['buyers_order_date']));
			}
        $this->font();
		$this->NewLine_Sales(2);
	
		//if ($this->pageNumber > 1)
			//$this->Text($mcol + 180, _("Page") . ' ' . $this->pageNumber);
		$this->row = $iline1 - $this->lineHeight_Sales;
		
		
		$res=get_state_name($this->formData['state_code']);
		$result=db_fetch($res);
		$cust_state = $result['state_name'];
		
		$res1=get_customer_tax_id($this->formData["debtor_no"]);
		$result1=db_fetch($res1);
		$cust_tax_id = $result1['tax_id'];
		$cust_pan_no = $result1['pan_no'];
		
		

        // Consignee left
        $temp = $this->row = $this->row - $this->lineHeight_Sales - 75;
		$this->Text($ccol, _("Consignee"));
		$this->NewLine_Sales();
		if($Addr2['name']!=''){
		$this->font('bold');
		$this->Text($ccol, $Addr2['name'], $icol);
		$this->font();
		$this->NewLine_Sales();
		$this->TextWrapLines($ccol, $icol - $ccol+50, $Addr2['address']);
		}else
		{
		$this->font('bold');
		$this->Text($ccol, $Addr1['name'], $icol);
		$this->font();
		$this->NewLine_Sales();
		$this->TextWrapLines($ccol, $icol - $ccol+50, $Addr1['address']);
		}
        $this->Text($ccol, _("GSTIN/UIN"). ": ".$this->formData["branch_gst"], $icol);
		
		$this->NewLine_Sales();
		
		if($this->formData['curr_code']=='INR'){
		$this->Text($ccol, _("State Name"). ": " . substr($cust_state,3,100). ", " ."code" . ": " . $this->formData['state_code'], $icol);
		 $this->Text($ccol+185, _("PAN/IT No"). ": " . $this->formData["pan_no"]);
		}
		
		 
		$this->NewLine_Sales();
		
		// Consignee right
		$this->row = $temp ;
		$this->Text($mcol + 50, _("Despatch Document No"));
		$this->Text($mcol + 160,_("Delivery Note Date"));
		$this->NewLine_Sales();
		$this->font('bold');
		$this->Text($mcol + 50, $this->formData["despatch_document_no"]);
		$this->Text($mcol + 160, sql2date($this->formData['buyers_order_date']));
		$this->font();
		
		$this->NewLine_Sales();
		$this->Text($mcol+50,  _("Despatched Through"));
		$this->Text($mcol+160,  _("Destination"));
		$this->NewLine_Sales();
		$this->font('bold');
		$this->Text($mcol + 50, $this->formData["dispatch_through"]);
		$this->Text($mcol + 160, $this->formData["destination"]);
		$this->font();
		
		$this->NewLine_Sales();
		$this->Text($mcol+50,  _("Bill of Landing/LR-RR No"));
		$this->Text($mcol+160,  _("Motor Vehicle No"));
		$this->NewLine_Sales();
		$this->font('bold');
		$this->Text($mcol + 50, $this->formData["bill_of_landing_lr_rr_no"]);
		$this->Text($mcol + 160, $this->formData["motor_vehicle_no"]);
		$this->font();
		
		$this->NewLine_Sales();
		$this->Text($mcol+50,  _("Driver Name"));
		$this->Text($mcol+160,  _("Driver Number"));
		$this->NewLine_Sales();
		$this->font('bold');
		$this->Text($mcol + 50, $this->formData["driver_name"]);
		$this->Text($mcol + 160, $this->formData["driver_number"]);
		$this->font();
		
		//display_error($this->formData['curr_code']); die;
		
		//Buyer left
		$this->row = $adrline - 175;
		
		$this->Text($ccol,  _("Buyer (if other than consignee)"), $icol);
		
		$this->NewLine_Sales();
		$this->font('bold');
		$this->Text($ccol, $Addr1['name'], $icol);
		$this->font();
		$this->NewLine_Sales();
		$this->TextWrapLines($ccol, $icol - $ccol+50, $Addr1['address']);
        $this->Text($ccol, _("GSTIN/UIN"). ": " .$this->formData["branch_gst"], $icol);
		$this->NewLine_Sales();
		
		if($this->formData['curr_code']=='INR'){
		$this->Text($ccol, _("State Name"). ": " . substr($cust_state,3,100). ", " ."code" . ": " . $this->formData['state_code'], $icol);
		 $this->Text($ccol+185, _("PAN/IT No"). ": " . $this->formData["pan_no"]);
		 }
		
		
		$this->row = $temp - 85;
		
		
		$this->Text($mcol+50,  _("Terms of Delivery"));
		$this->NewLine_Sales();
		$this->font('bold');
		$this->Text($mcol+50,$this->formData["terms_of_delevery"]);
		$this->font();
		
		$this->NewLine_Sales(2);
		
		// Auxiliary document information
		$col = $this->leftMargin;
		/*
		foreach($aux_info as $info_header => $info_content)
		{

			$this->row = $iline2 - $this->lineHeight_Sales - 1;
			$this->TextWrap($col, $this->row, $width, $info_header, 'C');
			$this->row = $iline3 - $this->lineHeight_Sales - 1;
			$this->TextWrap($col, $this->row, $width, $info_content, 'C');
			$col += $width;
		}
		
		// Payment terms
		$this->row -= (2 * $this->lineHeight_Sales);
		$this->Font('italic');
		$this->TextWrap($ccol, $this->row, $right-$ccol, $Payment_Terms);
 		$this->Font();
       */
		// Line headers
		$this->row = $iline5 - $this->lineHeight_Sales - 1;
		$this->Font('bold');
		$count = count($this->headers);
		$this->cols[$count] = $right - 3;
		for ($i = 0; $i < $count; $i++)
			$this->TextCol($i, $i + 1, $this->headers[$i], -2);
		$this->Font();

		// Footer
		$this->Font('italic');
		$this->row = $iline7 - $this->lineHeight_Sales - 6;

		foreach ($Footer as $line => $txt)
		{
			if (!is_numeric($line))	// title => link
			{
				$this->fontSize -= 2;
				$this->TextWrap($ccol, $this->row, $right - $ccol, $line, 'C');
				$this->row -= $this->lineHeight_Sales;
				$this->SetTextColor(0, 0, 255);
				$this->TextWrap($ccol, $this->row, $right - $ccol, $txt, 'C');
				$this->SetTextColor(0, 0, 0);
				$this->addLink($txt, $ccol, $this->row, $this->pageWidth - $this->rightMargin, $this->row + $this->lineHeight_Sales);
				$this->fontSize += 2;
			}
			else
				$this->TextWrap($ccol, $this->row, $right - $ccol, $txt, 'C');
			$this->row -= $this->lineHeight_Sales;

		}

		$this->Font();
		$temp = $iline6 - $this->lineHeight_Sales - 2;
