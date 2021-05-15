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
class qc_qa_app extends application
{
	function __construct()
	{
		parent::__construct("qc_qa", _($this->help_context = "QC & QA"));

		$this->add_module(_("Transactions"));
		$this->add_lapp_function(0, _("Batch Pickling Report"),
			"qc_qa/batch_pickling_reoprt.php?", 'SA_PICKLING', MENU_TRANSACTION);
		
		$this->add_module(_("Inquiries and Reports"));
		$this->add_lapp_function(1, _("Batch Pickling Inquiry"),
			"qc_qa/inquiry/batch_pickling_inquiry.php?", 'SA_PICKLING_INQUIRY', MENU_INQUIRY); 
		/*$this->add_lapp_function(1, _("Inventory Item Where Used &Inquiry"),
			"manufacturing/inquiry/where_used_inquiry.php?", 'SA_WORKORDERANALYTIC', MENU_INQUIRY);
		$this->add_lapp_function(1, _("Work Order &Inquiry (TM-WM)"),
			"manufacturing/tm_wm_work_orders.php?", 'SA_MANUFTRANSVIEW', MENU_INQUIRY);
		$this->add_rapp_function(1, _("Manufacturing &Reports"),
			"reporting/reports_main.php?Class=3", 'SA_MANUFTRANSVIEW', MENU_REPORT);*/

	/*	$this->add_module(_("Maintenance"));
		$this->add_lapp_function(2, _("&Bills Of Material"),
			"manufacturing/manage/bom_edit.php?", 'SA_BOM', MENU_ENTRY);
		$this->add_lapp_function(2, _("&Work Centres"),
			"manufacturing/manage/work_centres.php?", 'SA_WORKCENTRES', MENU_MAINTENANCE); */

		$this->add_extensions();
	}
}


