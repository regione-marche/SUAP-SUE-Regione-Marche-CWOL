<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Documenti
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */

function docParametri() {
    $docParametri = new docParametri();
    $docParametri->parseEvent();
    return;
}

class docParametri extends itaModel {

    public $ITALWEB_DB;
    public $nameForm = 'docParametri';

    function __construct() {
        parent::__construct();

        $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->caricaParametri();
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $param_tab = $_POST[$this->nameForm . '_PARAM'];
                        if (!$this->salvaParametri($param_tab)) {
                            break;
                        }

                        Out::msgBlock($this->nameForm, 1200, false, 'Aggiornamento eseguito con successo.');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_params');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function getParametri() {
        $return_tab = array();
        $sql = "SELECT * FROM ENV_CONFIG WHERE CLASSE = 'SEGPARAMVARI'";
        $envconfig_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, true);
        foreach ($envconfig_tab as $envconfig_rec) {
            $return_tab[$envconfig_rec['CHIAVE']] = $envconfig_rec['CONFIG'];
        }
        return $return_tab;
    }

    public function getParametro($key) {
        $envconfig_tab = $this->getParametri();
        return $envconfig_tab[$key];
    }

    public function caricaParametri() {
        $envconfig_tab = $this->getParametri();
        foreach ($envconfig_tab as $key => $value) {
            Out::valore($this->nameForm . "_PARAM[$key]", $value);
        }
    }

    private function salvaParametri($param_tab) {
        foreach ($param_tab as $paramKey => $paramValue) {
            $param_rec = array();
            $param_rec['CHIAVE'] = $paramKey;
            $param_rec['CONFIG'] = $paramValue;

            switch ($paramKey) {
                case 'SEG_OPENOO_DOCX':
                case 'SEG_MODDIR_DOCX':
                    $param_rec['CLASSE'] = 'SEGPARAMVARI';
                    break;

                default:
                    continue 2;
            }

            $envconfig_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, sprintf("SELECT * FROM ENV_CONFIG WHERE CLASSE = 'SEGPARAMVARI' AND CHIAVE = '%s'", $paramKey), false);

            if ($envconfig_rec) {
                /*
                 * Update record.
                 */

                $param_rec['ROWID'] = $envconfig_rec['ROWID'];
                $update_info = sprintf('Oggetto: aggiornamento parametro %s \'%s\'.', $envconfig_rec['CLASSE'], $paramKey);
                if (!$this->updateRecord($this->ITALWEB_DB, 'ENV_CONFIG', $param_rec, $update_info)) {
                    return false;
                }
            } else {
                /*
                 * Insert record.
                 */

                $insert_Info = sprintf('Oggetto: inserimento nuovo parametro %s \'%s\'.', $envconfig_rec['CLASSE'], $paramKey);
                if (!$this->insertRecord($this->ITALWEB_DB, 'ENV_CONFIG', $param_rec, $insert_Info)) {
                    return false;
                }
            }
        }

        return true;
    }

}
