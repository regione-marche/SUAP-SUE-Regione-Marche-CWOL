<?php

/**
 *
 * Stampa Etichette
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    19.07.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proStampaEtichette() {
    $proStampaEtichette = new proStampaEtichette();
    $proStampaEtichette->parseEvent();
    return;
}

class proStampaEtichette extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proStampaEtichette";

    function __construct() {
        parent::__construct();
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
            case 'openform':
                $this->CreaCombo();
                Out::valore($this->nameForm . '_Anno', date('Y'));
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Stampa':
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        $daProt = $_POST[$this->nameForm . '_DaProt'];
                        $aProt = $_POST[$this->nameForm . '_AProt'];
                        $tipo = $_POST[$this->nameForm . '_tipo'];
                        $whereTipo = "(PROPAR='A' OR PROPAR='P')";
                        switch ($tipo) {
                            case 'A':
                            case 'P':
                            case 'C':
                                $whereTipo = "PROPAR='$tipo'";
                                break;
                        }
                        $sql = "SELECT * FROM ANAPRO WHERE $whereTipo AND (PRONUM BETWEEN $anno$daProt AND $anno$aProt)";
                        $anapro_tab = $this->proLib->getGenericTab($sql);
                        if ($anapro_tab) {
                            $anaent_2 = $this->proLib->GetAnaent('2');
                            $utente = App::$utente->getKey('nomeUtente');
                            $tmppro_tab = $this->proLib->getGenericTab("SELECT ROWID FROM TMPPRO WHERE UTENTE='$utente'");
                            if ($tmppro_tab) {
                                foreach ($tmppro_tab as $tmppro_del) {
                                    if (!$this->deleteRecord($this->PROT_DB, 'TMPPRO', $tmppro_del['ROWID'], '', 'ROWID', false)) {
                                        break;
                                    }
                                }
                            }

                            //
                            // Ciclo il risultato e carico la tabella di appoggio TMPPRO con gli uffici coinvolti salvati in una 
                            // unica variabile (CAMPO1) in orizzontale per sepmplificare il report.
                            //
                            foreach ($anapro_tab as $anapro_rec) {
                                $uffici = $this->proLib->getStringaUffici($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
                                $tmppro_rec = array();
                                $tmppro_rec['UTENTE'] = $utente;
                                $tmppro_rec['CHIAVENUM'] = $anapro_rec['PRONUM'];
                                $tmppro_rec['CAMPO1'] = $uffici;
                                $this->insertRecord($this->PROT_DB, 'TMPPRO', $tmppro_rec, '', 'ROWID', false);
                            }
                            $sql = "SELECT * FROM ANAPRO LEFT OUTER JOIN TMPPRO ON PRONUM=CHIAVENUM 
                                WHERE UTENTE='$utente' AND $whereTipo AND PRONUM BETWEEN $anno$daProt AND $anno$aProt";
                            include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                            $itaJR = new itaJasperReport();
                            $parameters = array("Sql" => $sql,
                                "Ente" => $anaent_2['ENTDE1']
                            );
                            $itaJR->runSQLReportPDF($this->PROT_DB, 'proStampaZebra', $parameters);
                            $tmppro_tabfin = $this->proLib->getGenericTab("SELECT ROWID FROM TMPPRO WHERE UTENTE='$utente'");
                            if ($tmppro_tabfin) {
                                foreach ($tmppro_tabfin as $tmppro_del) {
                                    if (!$this->deleteRecord($this->PROT_DB, 'TMPPRO', $tmppro_del['ROWID'], '', 'ROWID', false)) {
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    case $this->nameForm . '_DaProt_butt':
                        proRic::proRicNumAntecedenti($this->nameForm, "ANAPRO.PROPAR<>'N' AND ANAPRO.PROPAR<>'F'", '', 'returnNumAnteDa');
                        break;
                    case $this->nameForm . '_AProt_butt':
                        proRic::proRicNumAntecedenti($this->nameForm, "ANAPRO.PROPAR<>'N' AND ANAPRO.PROPAR<>'F'", '', 'returnNumAnteA');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_DaProt':
                        $daProt = str_pad($_POST[$this->nameForm . '_DaProt'], 6, "0", STR_PAD_LEFT);
                        Out::valore($this->nameForm . '_DaProt', $daProt);
                        break;
                    case $this->nameForm . '_AProt':
                        $aProt = str_pad($_POST[$this->nameForm . '_AProt'], 6, "0", STR_PAD_LEFT);
                        Out::valore($this->nameForm . '_AProt', $aProt);
                        break;
                }
                break;
            case 'returnNumAnteDa':
                $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_DaProt', substr($anapro_rec['PRONUM'], 4));
                break;
            case 'returnNumAnteA':
                $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_AProt', substr($anapro_rec['PRONUM'], 4));
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_tipo', 1, "X", "1", "Arrivi/Partenze");
        Out::select($this->nameForm . '_tipo', 1, "A", "0", "Arrivo");
        Out::select($this->nameForm . '_tipo', 1, "P", "0", "Partenza");
        Out::select($this->nameForm . '_tipo', 1, "C", "0", "Documento Formale");
    }

}

?>