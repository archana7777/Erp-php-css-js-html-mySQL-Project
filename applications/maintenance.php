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
class maintenance_app extends application
{
	function __construct()
	{
		parent::__construct("maintenance", _($this->help_context = "&Maintenance"));

		$this->add_module(_("Transactions"));
		
		$this->add_lapp_function(0, _("Material Requisition Entry"),
			"inventory/indent_request.php?NewTransfer=1", 'SA_INDENTREQUEST', MENU_TRANSACTION);
		$this->add_lapp_function(0, _("&Preventive Maintenance "),"maintenance/inquiry/machine_maintenance_inquiry.php?", 'SA_MACHMAINTAIN', MENU_INQUIRY);
		$this->add_lapp_function(0, _("&Breakdown Maintenance"),
			"maintenance/manage/machine_breakdown_req.php",'SA_MC_BRK_REQ', MENU_TRANSACTION);
		
		/*$this->add_lapp_function(0, _("&Calibrations Entry"),
			"maintenance/manage/machine_calibration_entry.php?", 'SA_MACHINE_CALIBRATION', MENU_MAINTENANCE);*/ 
		
		$this->add_lapp_function(0, _("&Attach Machine Documents"),
			"maintenance/attach_machine_documents.php?", 'SA_MACHINE_ATTACH_DOC', MENU_MAINTENANCE);
		
		
		/*$this->add_lapp_function(0, _("&Attach Calibration Documents "),
			"maintenance/calibration_attach_documents.php?", 'SA_MACHINE_CALIB_ATTACH_DOC', MENU_MAINTENANCE);*/ 
		$this->add_lapp_function(0, _("&Check List "),
			"maintenance/inquiry/check_list.php?", 'SA_CHECK_LIST', MENU_MAINTENANCE);
		$this->add_lapp_function(0, _("&Upgrade Request Entry"),
			"maintenance/inquiry/upgrade_request_entry.php?", 'SA_UPGRADE_REQUEST_ENT', MENU_MAINTENANCE);
	
			
		// $this->add_module(_("Requests"));
		// $this->add_lapp_function(1, _("&QAU Request"),
		//	"invivo/manage/qau_request.php?", 'SA_INVIVO_QAUREQ', MENU_TRANSACTION);

		// $this->add_lapp_function(1, _("&Archival Request"),
		//	"invivo/manage/study_details.php?", 'SA_INVIVO_DE_AR_RE', MENU_TRANSACTION);

		$this->add_module(_("Inquiries and Reports")); 
		
			$this->add_lapp_function(1, _("Material Requisition Inquiry View"),
			"inventory/inquiry/indent_request_inquiry_view.php?type=95", 'SA_INDENTREQUEST_INQUIRY_VIEW', MENU_REPORT);
		$this->add_lapp_function(1, _("Preventive Maintenance Inquiry"),
			"maintenance/inquiry/pre_maintenance_request_inquiry.php?",'SA_MAIN_REQINQ', MENU_REPORT);
		$this->add_lapp_function(1, _("Breakdown Maintenance Inquiry"),
			"maintenance/inquiry/breakdown_request_inquiry.php?",'SA_MM_BREK_REQINQ', MENU_REPORT);
			$this->add_lapp_function(1, _("&Upgrade Request Inquiry"),
			"maintenance/inquiry/upgrade_request.php?type=1109", 'SA_UPGRADE_REQUEST', MENU_REPORT);
				 $this->add_lapp_function(1, _("Upgrade Request View"),
			"maintenance/inquiry/upgrade_request_inquiry.php?type=1110", 'SA_UPGRADE_REQUESTINQ', MENU_INQUIRY);
			
			$this->add_lapp_function(1, _("Maintenance &Reports"),
			"reporting/reports_main.php?Class=8", 'SA_MACHINMAIN_REPORTS', MENU_REPORT);
			
			
			
		$this->add_module(_("Masters"));
		$this->add_lapp_function(2, _("&Machine  Master"),
			"maintenance/manage/machine.php?", 'SA_MACHINE', MENU_MAINTENANCE);
			
		$this->add_lapp_function(2, _("&Machine Category Master(Equipment Master)"),
			"maintenance/manage/machine_equipment.php?", 'SA_MACHINE_EQUIPMENT', MENU_MAINTENANCE);
			$this->add_lapp_function(2, _("&Machine Make Master"),
			"maintenance/manage/machine_make.php?", 'SA_MACHINE_MAKE', MENU_MAINTENANCE);
			$this->add_lapp_function(2, _("&Machine Capacity Master"),
			"maintenance/manage/machine_capacity.php?", 'SA_MACHINE_CAPACITY', MENU_MAINTENANCE); 
			$this->add_lapp_function(2, _("Machine Frequency Master"),
			"maintenance/manage/machine_frequency.php?", 'SA_MACHINE_FREQUENCY', MENU_MAINTENANCE); 
			$this->add_lapp_function(2, _("&Preventive Maintenance Schedule"),
			"maintenance/manage/machine_maintenance_schedule.php?", 'SA_MACHINE_SCHEDULE', MENU_MAINTENANCE);
			$this->add_lapp_function(2, _("Maintenance Checklist"),
			"maintenance/manage/check_list_master.php?", 'SA_MACHINE_CHECKLIST', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


