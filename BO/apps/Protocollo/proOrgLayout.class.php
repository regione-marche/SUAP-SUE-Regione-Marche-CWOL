<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    19.06.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
class proOrgLayout {

    public $PROT_DB;
    public $proLib;
    public $layout;
    private $lastExitCode;
    private $lastMessage;

    public static function getInstance($proLib) {
        try {
            $obj = new proOrgLayout();
        } catch (Exception $exc) {
            return false;
        }
        if (!$proLib) {
            return false;
        }
        $obj->proLib = $proLib;
        $obj->layout = self::caricaLayout();
        return $obj;
    }

    public function getLastExitCode() {
        return $this->lastExitCode;
    }

    public function getLastMessage() {
        return $this->lastMessage;
    }

    public function getLayout() {
        return $this->layout;
    }

    public static function caricaLayout() {
        return array(
            "0" => "ENTE",
            "1" => "SETTORE",
            "2" => "UFFICIO",
            "3" => "SOGGETTO"
        );
    }

    public static function caricaLayoutDocumentale() {
        return array(
            "0" => "ENTE",
            "2" => "UFFICIO",
            "3" => "SOGGETTO"
        );
    }

}

?>
