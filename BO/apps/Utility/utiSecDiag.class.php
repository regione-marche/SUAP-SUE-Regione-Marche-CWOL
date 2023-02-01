<?php

class utiSecDiag {
	
	/**
	 * 
	 * @param string $form Nome del form
	 * @param string $titolo Titolo msgInput
	 * @param string $retid Id di ritorno ($form . "_returnPassword$retId")
	 * @param string $msg Messaggio aggiuntivo al msgInput
	 */
    public static function GetMsgInputPassword($form, $titolo, $retid = "", $msg = "") {
        $header = "<span style=\"color:red;font-weight:bold;font-size:1.2em;\">Digitare la password utilizzata per il login</span>";
        $footer = $msg;
        Out::msgInput($titolo, array(
            'label' => array('style' => "width:70px;font-weight:bold;font-size:1.1em;", 'value' => 'Password'),
            'id' => $form . '_password',
            'name' => $form . '_password',
            'type' => 'password',
            'width' => '70',
            'size' => '40',
            'maxchars' => '30'), array('F5-Conferma' => array('id' => $form . "_returnPassword$retid", 'model' => $form, 'shortCut' => "f5")), $form, "auto", "auto", true, $header, $footer
        );
    }

}
?>
