<?php
/****************************************
/*  Author  : Kvvaradha
/*  Module  : Extended HRM
/*  E-mail  : admin@kvcodes.com
/*  Version : 1.0
/*  Http    : www.kvcodes.com
*****************************************/
define ('SS_EXHRM', 250<<8);
define ('SS_EXHRM_SETTINGS', 251<<8);
define ('SS_EXHRM_PAYROLL', 252<<8);

class ExtendedHRM_app extends application{
    var $apps;
    function __construct()  {
		
		parent::__construct("extendedhrm", _($this->help_context = "&HRM"));

       // $this->application("extendedhrm", _($this->help_context = "&HRM"));
       $this->add_module(_("Transactions"));
        
        $this->add_lapp_function(0, _('PaySlip Entry'), 'modules/ExtendedHRM/payslip.php', 'HR_PAYSLIP', MENU_TRANSACTION);
        $this->add_lapp_function(0, _('Payroll'), 'modules/ExtendedHRM/manage/payroll.php', 'HR_PAYSLIP', MENU_TRANSACTION);
		
        $this->add_lapp_function(0, _('Attachments'), 'modules/ExtendedHRM/manage/attachments.php', 'HR_EMPL_INFO', MENU_TRANSACTION);
        
        $this->add_rapp_function(0, _('Attendance Entry'), 'modules/ExtendedHRM/empl_attend.php', 'HR_ATTENDANCE', MENU_TRANSACTION);     
       // $this->add_lapp_function(0, _('Advance Salary'), 'modules/ExtendedHRM/advance_salary.php', 'HR_PAYSLIP', MENU_TRANSACTION);        

        $this->add_rapp_function(0, _('Loan Entry'), 'modules/ExtendedHRM/manage/loan_form.php', 'HR_LOANFORM', MENU_TRANSACTION);

     //   $this->add_lapp_function(0, _('Claims And Reimbursement Entry'), 'modules/ExtendedHRM/claims.php', 'SA_EMPLOYEE', MENU_TRANSACTION);

        $this->add_module(_("Inquires"));

        $this->add_lapp_function(1, _('Payroll Inquiry'), 'modules/ExtendedHRM/inquires/payroll_history_inquiry.php', 'HR_PAYSLIP', MENU_INQUIRY);

        $this->add_lapp_function(1, _('Attendance Inquiry'), 'modules/ExtendedHRM/inquires/attendance_inquiry.php', 'HR_SELATTENDANCE', MENU_INQUIRY);

        $this->add_rapp_function(1, _('Loan Inquiry'), 'modules/ExtendedHRM/inquires/loan_inquiry.php', 'HR_LOANIN', MENU_INQUIRY);

        $this->add_lapp_function(1, _('Employees Inquiry'), 'modules/ExtendedHRM/inquires/employees_inquiry.php', 'HR_EMPLOYEE_INQ', MENU_INQUIRY);
        

       // $this->add_rapp_function(1, _('Leave Inquiry'), 'modules/ExtendedHRM/inquires/leave_inquiry.php', 'HR_LEAVEFORM', MENU_INQUIRY);

        $this->add_rapp_function(1, _('HRM Reports'), 'modules/ExtendedHRM/reports/hrm_reports.php?Class=8&REP_ID=801', 'HR_REPORTS', MENU_INQUIRY);

        $this->add_module(_("Maintainance"));

        $this->add_lapp_function(2, _('Add And Manage Employees'), 'modules/ExtendedHRM/manage/employees.php', 'HR_EMPL_INFO', MENU_ENTRY);

        $this->add_lapp_function(2, _('Department'), 'modules/ExtendedHRM/manage/department.php', 'HR_EMPL_INFO', MENU_MAINTENANCE);

        $this->add_lapp_function(2, _('Allowance Setup'), 'modules/ExtendedHRM/manage/pay_items_setup.php', 'HR_PAYSLIP', MENU_MAINTENANCE);

        $this->add_rapp_function(2, _('Taxes'), 'modules/ExtendedHRM/tax/', 'HR_EMPL_TAX', MENU_MAINTENANCE);

        $this->add_lapp_function(2, _('Loan Types'), 'modules/ExtendedHRM/manage/loan_type.php', 'HR_LOANTYPE', MENU_MAINTENANCE);       
	   
       $this->add_rapp_function(2, _('Settings'), 'modules/ExtendedHRM/manage/hrm_settings.php', 'HR_EMPLOYEE_SETUP', MENU_MAINTENANCE);
       
       // $this->add_rapp_function(2, _('Gazetted Off Days'), 'modules/ExtendedHRM/manage/off_days.php', 'SA_EMPLOYEE', MENU_MAINTENANCE);

        $this->add_extensions();
        
    }      
}

class hooks_ExtendedHRM extends hooks {
	var $module_name = 'ExtendedHRM';

	/*
		Install additonal menu options provided by module
	*/
    function install_tabs($app) {
        $app->add_application(new ExtendedHRM_app);
    }
  
    function install_access()	{
        $security_sections[SS_EXHRM]               = _("HRM");
        $security_sections[SS_EXHRM_SETTINGS]       = _("HRM Settings");
        $security_sections[SS_EXHRM_PAYROLL]        = _("HRM Payroll");

        // ############################################################################################
        // HRM related functionality
        //
        // Employee Information
         $security_areas['HR_EMPL_INFO'] = array(SS_EXHRM|1, _("HRM Employee info")); 

         $security_areas['HR_PAYSLIP'] = array(SS_EXHRM_PAYROLL|4, _("Pay Slip Generation")); 

         $security_areas['HR_ATTENDANCE'] = array(SS_EXHRM|1, _("Employee Attendence"));

         $security_areas['HR_LOANFORM'] = array(SS_EXHRM|2, _("Loan Application Form"));

         $security_areas['HR_REPORTS'] = array(SS_EXHRM_SETTINGS|5, _("HRM Reports")); 

         $security_areas['HR_EMPL_TAX'] = array(SS_EXHRM_SETTINGS|9, _("Tax Setup")); 

         $security_areas['HR_EMPLOYEE_INQ'] = array(SS_EXHRM|6, _("Employee Inquiry")); 

         $security_areas['HR_SELATTENDANCE'] = array(SS_EXHRM|2, _("Selective Attendance List Show"));
         
         $security_areas['HR_LOANIN'] = array(SS_EXHRM|4, _("Loan Approve Inquiry"));

         $security_areas['HR_LEAVEFORM'] = array(SS_EXHRM|1, _("Leave Application Form"));

         $security_areas['HR_LOANTYPE'] = array(SS_EXHRM_SETTINGS|3, _("Loan Type Setup"));
		 
         $security_areas['HR_EMPLOYEE_SETUP'] = array(SS_EXHRM_SETTINGS|3, _("Setup"));

/*
         $security_areas['HR_SALSTRUCTURE'] = array(SS_EXHRM|2, _("Salary Structure")); 
         $security_areas['HR_SAL_BASIC'] = array(SS_EXHRM|3, _("Salary Basic Info")); 
         $security_areas['HR_SALPROCESSMONTH'] = array(SS_EXHRM|4, _("Salary Process Month"));
         $security_areas['HR_SALEDITOR'] = array(SS_EXHRM|5, _("Salary Editor")); 
         $security_areas['HR_UPLOADCV'] = array(SS_EXHRM|6, _("Upload CV")); 
         $security_areas['HR_EMPLOYEEIDCARD'] = array(SS_EXHRM|7, _("Employee ID Card")); 
         $security_areas['HR_EMPLOYEERESUME'] = array(SS_EXHRM|8, _("Employee Resume"));
         
         
         
         //  forms  and applications
         
         
         $security_areas['HR_APPLNFORM'] = array(SS_EXHRM|3, _("Application Form"));
         $security_areas['HR_ADVSALARYFORM'] = array(SS_EXHRM|4, _("Advance Salary Application Form"));
         $security_areas['HR_EMPLEVAL'] = array(SS_EXHRM|4, _("Employee Evaluation Form"));
         $security_areas['HR_INCREMENT'] = array(SS_EXHRM|5, _("Employee Increment And Promotion Form"));
         

         //  Attendance and inquiery 
         
          $security_areas['HR_LEAVEIN'] = array(SS_EXHRM|3, _("Leave Approve Inquiry"));
          
          $security_areas['HR_LOANINSTALLMENT'] = array(SS_EXHRM|5, _("Loan Installment Postponding Approve Inquiry"));
          $security_areas['HR_SALIN'] = array(SS_EXHRM|6, _("Advance Salary Approve Inquiry"));
          $security_areas['HR_INCREMENTIN'] = array(SS_EXHRM|7, _("Employee Increment And Promotion Inquiry"));
          $security_areas['HR_RETIRMENTIN'] = array(SS_EXHRM|8, _("Employee Retirement Inquiry"));
         

        // Assets and Salary and Payroll Reports
         $security_areas['HR_EMPLASSET'] = array(SS_EXHRM_PAYROLL|1, _("Employees Assets Record")); 
         $security_areas['HR_ADDASSET'] = array(SS_EXHRM_PAYROLL|2, _("Add Employee Asset")); 
         $security_areas['HR_SALHISTORY'] = array(SS_EXHRM_PAYROLL|3, _("Salary History")); 
         
         $security_areas['HR_PAYROLL'] = array(SS_EXHRM_PAYROLL|5, _("Payroll Reports")); 
         $security_areas['HR_SETTLEMENT'] = array(SS_EXHRM_PAYROLL|6, _("Employee - Final Settlement"));  

         
         
        // settings 
         $security_areas['HR_EMPL_INFO'] = array(SS_EXHRM_SETTINGS|1, _("Add Employee Info"));
          $security_areas['HR_BANKACC'] = array(SS_EXHRM_SETTINGS|2, _("Bank Acc"));
          
          $security_areas['HR_BONUSTYPE'] = array(SS_EXHRM_SETTINGS|4, _("Bonus Type Settings"));
          $security_areas['HR_RETIREMENT_TIME_SETTINGS'] = array(SS_EXHRM_SETTINGS|5, _("Retirement Time Settings"));
          $security_areas['HR_HOLIDAYS'] = array(SS_EXHRM_SETTINGS|6, _("Gazetted Holidays"));
          $security_areas['HR_OFFDAY'] = array(SS_EXHRM_SETTINGS|7, _("Offday Setting"));
          $security_areas['HR_EMPL_CARD'] = array(SS_EXHRM_SETTINGS|8, _("Employee Card Setup"));
          $security_areas['HR_SAL_CALCULATION'] = array(SS_EXHRM_SETTINGS|9, _("Employee Salary Calculation Setup"));
*/
		return array($security_areas, $security_sections);
	}

    /* This method is called on extension activation for company.   */
    function activate_extension($company, $check_only=true)
    {
        global $db_connections;

        $updates = array(
            'update.sql' => array('SimpleHRM')
        );

        return $this->update_databases($company, $updates, $check_only);
    }

    function deactivate_extension($company, $check_only=true)
    {
        global $db_connections;

        $updates = array(
            'drop.sql' => array('ugly_hack') // FIXME: just an ugly hack to clean database on deactivation
        );

        return $this->update_databases($company, $updates, $check_only);
    }
}

?>