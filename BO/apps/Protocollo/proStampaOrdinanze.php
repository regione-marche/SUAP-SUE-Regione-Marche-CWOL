<?php

/**
 *
 * Stampa delle Ordinanze
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    16.04.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proStampaOrdinanze() {
    $proStampaOrdinanze = new proStampaOrdinanze();
    $proStampaOrdinanze->parseEvent();
    return;
}

class proStampaOrdinanze extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proStampaOrdinanze";

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                Out::setFocus('', $this->nameForm . '_codiceOggetto');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stampa':
                        $codiceOggetto = $_POST[$this->nameForm . '_codiceOggetto'];
                        $dal = $_POST[$this->nameForm . '_Dal'];
                        if ($dal == '') {
                            $dal = date('Y') . '0101';
                        }
                        $al = $_POST[$this->nameForm . '_Al'];
                        if ($al == '') {
                            $al = date('Y') . '1231';
                        }
                        $sql = $this->proLib->getSqlRegistro();
                        $sql .= " WHERE 
                            (PROPAR='A' OR PROPAR='P') 
                            AND PROINCOGG>0 
                            AND (PRODAR BETWEEN $dal AND $al)
                            AND PRODOGCOD='$codiceOggetto'";
                        $anaent_rec = $this->proLib->GetAnaent('2');
                        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $sql,
                            "Ente" => $anaent_rec['ENTDE1'],
                            "daPagina" => 1);
                        $itaJR->runSQLReportPDF($this->PROT_DB, 'proStampaOrdinanze', $parameters);
                        break;
                    case $this->nameForm . '_codiceOggetto_butt':
                        proRic::proRicOgg($this->nameForm, ' WHERE DOGINCREMENTALE>0', '', 'DecodOggetto');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_codiceOggetto':
                        $codice = str_pad(trim($_POST[$this->nameForm . '_codiceOggetto']), 4, "0", STR_PAD_LEFT);
                        $anadog_rec = $this->proLib->GetAnadog($codice);
                        if ($anadog_rec['DOGINCREMENTALE'] == 0) {
                            $anadog_rec = array();
                        }
                        Out::valore($this->nameForm . '_codiceOggetto', $anadog_rec['DOGCOD']);
                        Out::valore($this->nameForm . '_Oggetto', $anadog_rec['DOGDEX']);

                        break;
                }
                break;
            case 'returndog':
                $anadog_rec = $this->proLib->GetAnadog($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_codiceOggetto', $anadog_rec['DOGCOD']);
                Out::valore($this->nameForm . '_Oggetto', $anadog_rec['DOGDEX']);
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

}

?>