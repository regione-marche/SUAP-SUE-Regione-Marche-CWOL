<?php

define('SMSTYPE_ALTA','N');
define('SMSTYPE_STANDARD','LL');

function sdk_sms_type_valid($smstype) {
	return 
		$smstype === SMSTYPE_ALTA 
		|| $smstype === SMSTYPE_STANDARD 
		;
}

function sdk_sms_type_has_custom_tpoa($smstype) {
	return 
		$smstype === SMSTYPE_ALTA 
		;
}
	
?>