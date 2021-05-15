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
$page_security = 'SA_MACHINMAIN_REPORTS';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Inventory Sales Report
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/banking.inc");
include_once($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/maintenance/includes/db/breakdown_maintenance_item_db.inc");

//----------------------------------------------------------------------------------------------------

print_inventory_purchase();

function getTransactions($machine_id,$from, $to,$department)
{
	
	$from = date2sql($from);
	$to = date2sql($to);
	
	$sql = "SELECT pmr.id,mms.*,meq.mac_eqp,m.mac_code,pmr.req_date,mms.mc_problem_type FROM ".TB_PREF."machine_maintenance_schedule AS mms 
			LEFT JOIN ".TB_PREF."pre_maintenance_req AS pmr ON mms.id = pmr.schedule_id 
			LEFT JOIN ".TB_PREF."machine AS m ON m.id = mms.machine_id 
			LEFT JOIN ".TB_PREF."machine_equipment as meq ON m.mac_eqp=meq.id
			WHERE pmr.req_date >= ".db_escape($from)." AND pmr.req_date<=".db_escape($to)."";
 			if ($machine_id!=0){
				$sql .= " AND mms.machine_id = ".db_escape($machine_id);
				
			}
			
			if ($department!= ""){
				$sql .= " AND mbr.department_id = ".db_escape($department);
				
			
			}
			//display_error($sql);die;
			$sql .= " GROUP BY mms.id";	
			return db_query($sql,"No transactions were returned");
}
//display_error(hi);die;
function getProcess_info($req_id)
{
	
$sql = "SELECT mms.*,mc.*,mmc.*,mcr.*,if(mcr.verified=1,'Yes','No') as verified,if(mcr.remarks='','Not Defined',mcr.remarks)as remarks FROM ".TB_PREF."machine_maintenance_schedule as mms LEFT JOIN ".TB_PREF."machine as mc ON mms.machine_id=mc.id LEFT JOIN  ".TB_PREF."machine_maintenance_checklists as mmc ON mmc.equipment_id=mc.mac_eqp LEFT JOIN ".TB_PREF."mm_checklist_result as mcr ON mcr.chk_list_id=mmc.id WHERE mms.id=".db_escape($req_id)." AND mms.mac_fre=mmc.machine_fre_id GROUP BY mmc.id";
//display_error($sql);die;
	return db_query($sql,"No transactions were returned");
}
//----------------------------------------------------------------------------------------------------

function print_inventory_purchase()
{
	
    global $path_to_root,$mc_analysis_type,$brkd_pro_status,$solved_by;

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
    $machine_id = $_POST['PARAM_2'];
	$department = $_POST['PARAM_3'];
	$comments = $_POST['PARAM_4'];
	$orientation = $_POST['PARAM_5'];
	$destination = $_POST['PARAM_6'];
	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");

	$orientation = ($orientation ? 'L' : 'P');
    $dec = user_price_dec();

	if ($machine_id == ALL_NUMERIC)
		$machine_id = 0;
	if ($machine_id == 0)
		$machine = _('All');
	else
		$machine = get_machine_name_based_stock($machine_id);

	$cols = array(0, 100, 180, 250,380,490,550);

	$headers = array(_('Machine Name'),_('Machine Code'),_('Process Date'),_('Problem Type'), _('status'), _('Remarks'));
	

	$aligns = array('left',	'left',	'left', 'left', 'left','left','left');

    $params =   array( 	0 => $comments,
    				    1 => array('text' => _('Period'),'from' => $from, 'to' => $to),
    				    );
    
    $rep = new FrontReport(_('Preventive Maintenance Report '), "PreventiveMaintenance", user_pagesize(), 9, $orientation);
    if ($orientation == 'L')
    	recalculate_cols($cols);

    $rep->Font();
    $rep->Info($params, $cols, $headers, $aligns);
    $rep->NewPage();
	
	$res = getTransactions($machine_id,  $from, $to,$department);
	//$res1 = getProcess_info($req_id);
	
	while ($trans=db_fetch($res))
	{
		$verification = $trans['process_status'];
		
		if($verification == 0){
			$verification = "not verified";
		}else{
			$verification = "verified";
		}
		
		
			
			$rep->NewLine();
			$rep->NewLine();
			$rep->TextCol(0, 1, $trans['mac_eqp']);
			$rep->TextCol(1, 2, $trans['mac_code']);
			$rep->TextCol(2, 3, sql2date($trans['req_date']));
			$rep->TextCol(3, 4, $mc_analysis_type[$trans['mc_problem_type']]);
			$rep->TextCol(4, 5, $verification);
			$rep->TextCol(5, 6, $trans['remarks']);
			//$rep->TextCol(6, 7, $trans['remarks']);	
			$rep->NewLine();
			$rep->Line($rep->row  - 4);
			$rep->NewLine();
			$rep->TextCol(0, 2, "Process Information:");
			
			//display_error('hie');die;
			
			$res1 = getProcess_info($trans['id']);
			while($trans1=db_fetch($res1)){
					$rep->NewLine();
					$oldrow = $rep->row;
					$rep->TextColLines(2, 4, $trans1['details']);
					$newrow = $rep->row;
					$rep->row = $oldrow;
					$rep->TextCol(4, 5, $trans1['verified']);
					$rep->TextCol(5, 6, $trans1['remarks']);
					$rep->NewLine();
					$rep->NewLine();
					
					$rep->row = $newrow;
			}
			$rep->Line($rep->row  - 4);	
	}
    $rep->End();
}

