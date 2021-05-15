<?php

/****************************************
/*  Author 	: Kvvaradha
/*  Module 	: Extended HRM
/*  E-mail 	: admin@kvcodes.com
/*  Version : 1.0
/*  Http 	: www.kvcodes.com
*****************************************/

	$this->row = $this->pageHeight - $this->topMargin;

		$upper = $this->row - 2 * $this->lineHeight;
		$lower = $this->bottomMargin + 8 * $this->lineHeight;
		$iline1 = $upper - 7.5 * $this->lineHeight;
		$iline2 = $iline1 - 8 * $this->lineHeight;
		$iline3 = $iline2 - 1.5 * $this->lineHeight;
		$iline4 = $iline3 - 1.5 * $this->lineHeight;
		$iline5 = $iline4 - 3 * $this->lineHeight;
		$iline6 = $iline5 - 1.5 * $this->lineHeight;
		$iline7 = $lower;
		$right = $this->pageWidth - $this->rightMargin;
		$width = ($right - $this->leftMargin) / 5;
		$icol = $this->pageWidth / 2;
		$ccol = $this->cols[0] + 4;
		$c2col = $ccol + 60;
		$ccol2 = $icol / 2;
		$mcol = $icol + 8;
		$mcol2 = $this->pageWidth - $ccol2;
		$cols = count($this->cols);
		$this->SetDrawColor(205, 205, 205);
		$this->rectangle($this->leftMargin, $iline2, $right - $this->leftMargin, $iline2 - $iline3, "F", null, array(222, 231, 236));   
		$this->Line($iline2);
		$this->Line($iline3);
		if ($this->formData['doctype'] == 'payslip'){
			$this->rectangle($this->leftMargin, $iline5-314, $right - $this->leftMargin, $iline5 - $iline6+12, "F", null, array(255, 211, 179));   
			//$this->Line($iline6-136);
		}
		//$this->Line($iline6-166);
		$col = $this->leftMargin;
		if ($this->formData['doctype'] == 'payslip'){
			$this->LineTo($col += $width+80, $iline2,$col, $iline7+40);
			$this->LineTo($col += $width-20, $iline2,$col, $iline7+40);
			$this->LineTo($col += $width+80, $iline2,$col, $iline7+40);
		}
		$this->LineTo($right, $iline2 ,$right, $iline7+10);
		$this->LineTo($this->leftMargin, $iline2 ,$this->leftMargin, $iline7+10);
		// Company Logo
		$this->NewLine();
		$logo = company_path() . "/images/" . $this->company['coy_logo'];
		if ($this->company['coy_logo'] != '' && file_exists($logo))	{
			$this->AddImage($logo, $ccol, $this->row, 0, 40);
		}else{
			$this->fontSize += 4;
			$this->Font('bold');
			$this->Text($ccol, $this->company['coy_name'], $icol);
			$this->Font();
			$this->fontSize -= 4;
		}
		// Document title
		$this->SetTextColor(190, 190, 190);
		$this->fontSize += 10;
		$this->Font('bold');
		$this->TextWrap($mcol, $this->row, $this->pageWidth - $this->rightMargin - $mcol - 20, $this->title, 'right');
		$this->Font();
		$this->fontSize -= 10;
		$this->NewLine();
		$this->SetTextColor(0, 0, 0);
		$adrline = $this->row;

		// Company data
		$this->TextWrapLines($ccol, $icol, $this->company['postal_address']);
		$this->Font('italic');
		if (@$this->company['phone']){
			$this->Text($ccol, _("Phone"), $c2col);
			$this->Text($c2col, $this->company['phone'], $mcol);
			$this->NewLine();
		}
		if (@$this->company['fax']){
			$this->Text($ccol, _("Fax"), $c2col);
			$this->Text($c2col, $this->company['fax'], $mcol);
			$this->NewLine();
		}
		if (@$this->company['email']){
			$this->Text($ccol, _("Email"), $c2col);

			$url = "mailto:" . $this->company['email'];
			$this->SetTextColor(0, 0, 255);
			$this->Text($c2col, $this->company['email'], $mcol);
			$this->SetTextColor(0, 0, 0);
			$this->addLink($url, $c2col, $this->row, $mcol, $this->row + $this->lineHeight);

			$this->NewLine();
		}
		
		$this->Font();
		$this->row = $adrline;
		$this->NewLine(3);
		$this->Text($mcol + 100, _("Date"));
		$this->Text($mcol + 180, sql2date($this->formData['document_date']));

		$this->NewLine();
		$this->Text($mcol + 100, $this->formData['document_name']);
		$this->Text($mcol + 180, $this->formData['document_number']);
		$this->NewLine(2);
	
		if ($this->pageNumber > 1)
			$this->Text($mcol + 180, _("Page") . ' ' . $this->pageNumber);
		$this->row = $iline1 - $this->lineHeight;
		
		$this->fontSize -= 4;
		$this->fontSize += 6;
		$this->Text($ccol, $Addr1['title'], $icol,0,-21);
		//$this->Text($mcol, $Addr1['title']);
		$this->fontSize -= 2;
 
		// address1
		$temp = $this->row = $this->row - $this->lineHeight - 5;
	if($this->formData['doctype'] == 'paysummary'){
		if($Addr1['id'] != ''){
			$this->Text($ccol, 'Month', $icol,0,-4);
			$this->Text($ccol+100, kv_month_name_by_id($Addr1['id']), $icol,0,-4);
			$this->NewLine();
		}
		
		$this->NewLine();
		$this->Text($ccol, 'Year', $icol,0,1);
		$fYear = get_fiscalyear($Addr1['year']);
		$this->Text($ccol+100, $fYear['begin'] .' - '. $fYear['end'], $icol,0,1);
	}
	else{
		$this->Text($ccol, 'Employee ID', $icol,0,-4);
		$this->Text($ccol+100, $Addr1['id'], $icol,0,-4);
	}
		
	if($this->formData['doctype'] != 'paysummary'){
		$this->Text($ccol+350, 'Department', $icol,0,-4);
		$this->Text($ccol+430, $Addr1['dept'], $icol,0,-4);
		$this->NewLine();
		$this->Text($ccol, 'Name of Employee', $icol,0,1);
		$this->Text($ccol+100, $Addr1['name'], $icol,0,1);
		$this->Text($ccol+350, 'Date of Joining', $icol,0,1);
		$this->Text($ccol+430, $Addr1['joining'], $icol,0,1);
		$this->NewLine();
		$this->Text($ccol, 'Designation', $icol,0,5);
		$this->Text($ccol+100, $Addr1['desig'], $icol,0,5);
		$this->NewLine();
	}
		
		// Line headers
		$this->row = $iline2 - $this->lineHeight - 1;
		$this->Font('bold');
		$count = count($this->headers);
		$this->cols[$count] = $right ;
		for ($i = 0; $i < $count; $i++)
			$this->TextCol($i, $i + 1, $this->headers[$i], -2);
		$this->Font();
		
		// Footer
		$this->Font('italic');
		$this->row = $iline7 - $this->lineHeight - 50;

		foreach ($Footer as $line => $txt){
			if (!is_numeric($line))	{// title => link			
				$this->fontSize -= 2;
				$this->TextWrap($ccol, $this->row, $right - $ccol, $line, 'C');
				$this->row -= $this->lineHeight;
				$this->SetTextColor(0, 0, 255);
				$this->TextWrap($ccol, $this->row, $right - $ccol, $txt, 'C');
				$this->SetTextColor(0, 0, 0);
				$this->addLink($txt, $ccol, $this->row, $this->pageWidth - $this->rightMargin, $this->row + $this->lineHeight);
				$this->fontSize += 2;
			}
			else
				$this->TextWrap($ccol, $this->row, $right - $ccol, $txt, 'C');
			$this->row -= $this->lineHeight;

		}

		$this->Font();
		$temp = $iline6 - $this->lineHeight - 2;
?>