<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    04.01.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';

class envLibNotifiche {

    /**
     * Libreria di funzioni Generiche e Utility per Ambiente
     */
    CONST NOTIFCHE_VIEW_MAX_DEFAULT = 4;

    private $errMessage;
    private $errCode;

    function __construct() {
        
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

}