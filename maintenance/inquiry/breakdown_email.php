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
//
//	Entry/Modify Sales Quotations
//	Entry/Modify Sales Order
//	Entry Direct Delivery
//	Entry Direct Invoice
//

$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/db/crm_contacts_db.inc");

$req_id=$_GET["id"];
	
		
		$req_sql="SELECT * FROM ".TB_PREF."mm_brkd_req WHERE id=".db_escape($req_id)."";
		$req_res=db_query($req_sql,"Something went wrong!");
		$result=db_fetch($req_res);
		$empl_sql="SELECT email FROM ".TB_PREF."kv_empl_info WHERE empl_id=".db_escape($result['user_empl_id'])."";
			$empl_res=db_query($empl_sql);
			$empl_result=db_fetch($empl_res);
			$vendor_res=get_crm_persons('supplier',null,$result['supplier_id']);
					$vendor_email=db_fetch($vendor_res);
			//echo $vendor_email['email'];die;
		$to=$vendor_email['email'];
 $mail_subject="Reg:-Machine Breakdown Repair";
	$mail_body="Material Info:    ".$result['mt_info'];
	/* display_error($to);
	display_error($mail_subject);
	display_error($mail_body);
	display_error($empl_result["email"]);die; */
	$sent=mail($to,$mail_subject,$mail_body, "From:". $empl_result["email"]);
	



header("Location: breakdown_request_verification.php?AddedID=".$req_id."&email=1");


?>