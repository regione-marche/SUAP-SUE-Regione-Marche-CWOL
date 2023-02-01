<?php

/**
 *
 * Trasforma Protocolli da Arrivo a Partenza e Contrario
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    07.08.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

function proTrasformaProt() {
    $proTrasformaProt = new proTrasformaProt();
    $proTrasformaProt->parseEvent();
    return;
}

class proTrasformaProt extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proTrasformaProt";

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
                //
                // Programma bloccato perchè non compatibile con i nuovi dati di visibilità e segnatura
                //
                Out::msgInfo("Trasformazione tipo Protocollo", "Il programma non è più attivo.");
                $this->close();
                break;
                $this->CreaCombo();
                Out::valore($this->nameForm . '_Anno', date('Y'));
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Trasforma':
                        //
                        // Funzione Bloccata perchè non compatibile con i nuovi dati di visibilità e segnatura
                        //
                        break;

                        $tipo = $_POST[$this->nameForm . '_Arr_par'];
                        $numProt = $_POST[$this->nameForm . '_NumProt'];
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        if (strlen($numProt) != 6 && strlen($anno) != 4)
                            break;
                        $anapro_rec = $this->proLib->GetAnapro($anno . $numProt, 'codice', $tipo);
                        if ($anapro_rec) {
                            if ($tipo == 'A') {
                                $propar = "ARRIVO";
                                $propar_contr = "PARTENZA";
                            } else {
                                $propar = "PARTENZA";
                                $propar_contr = "ARRIVO";
                            }
                            Out::msgQuestion("Trasforma Protocollo.", "Il protocollo n° " . substr($anapro_rec['PRONUM'], 4) . " del "
                                    . substr($anapro_rec['PRONUM'], 0, 4) . " è in $propar. Vuoi Trasformarlo in $propar_contr?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaTrasforma', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaTrasforma', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    ), false
                            );
                        }
                        break;
                    case $this->nameForm . '_Anno_butt':
                        proRic::proRicNumAntecedenti($this->nameForm, '', $_POST[$this->nameForm . '_Arr_par']);
                        break;
                    case $this->nameForm . '_ConfermaTrasforma':
                        $tipo = $_POST[$this->nameForm . '_Arr_par'];
                        $numProt = $_POST[$this->nameForm . '_NumProt'];
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        $prot = $anno . $numProt;
                        if ($tipo == 'A') {
                            $propar_contr = "P";
                        } else {
                            $propar_contr = "A";
                        }
                        $anaris_tab = $this->proLib->GetAnaris($prot, 'codice', true, $tipo);
                        foreach ($anaris_tab as $key => $anaris_rec) {
                            $anaris_rec['RISPAR'] = $propar_contr;
                            $nrow = ItaDB::DBUpdate($this->PROT_DB, 'ANARIS', 'ROWID', $anaris_rec);
                        }
                        $anades_tab = $this->proLib->GetAnades($prot, 'codice', true, $tipo);
                        foreach ($anades_tab as $key => $anades_rec) {
                            $anades_rec['DESPAR'] = $propar_contr;
                            $nrow = ItaDB::DBUpdate($this->PROT_DB, "ANADES", 'ROWID', $anades_rec);
                        }
                        $anaogg_rec = $this->proLib->GetAnaogg($prot, $tipo);
                        if ($anaogg_rec) {
                            $anaogg_rec['OGGPAR'] = $propar_contr;
                            $nrow = ItaDB::DBUpdate($this->PROT_DB, 'ANAOGG', 'ROWID', $anaogg_rec);
                        }
                        $anaspe_tab = $this->proLib->GetAnaspe($prot, 'codice', true, $tipo);
                        foreach ($anaspe_tab as $key => $anaspe_rec) {
                            $anaspe_rec['PROPAR'] = $propar_contr;
                            $nrow = ItaDB::DBUpdate($this->PROT_DB, "ANASPE", 'ROWID', $anaspe_rec);
                        }
                        if ($tipo == 'A') {
                            $arcite_tab = $this->proLib->GetArcite($prot, 'codice', true, $tipo);
                            foreach ($arcite_tab as $key => $arcite_rec) {
                                ItaDb::DBDelete($this->PROT_DB, "ARCITE", 'ROWID', $arcite_rec['ROWID']);
                            }
                        }
                        $anadoc_tab = $this->proLib->GetAnadoc($prot, 'codice', true, $tipo);
                        $orig_file = $this->proLib->SetDirectory($prot, $tipo);
                        $dest_file = $this->proLib->SetDirectory($prot, $propar_contr);
                        foreach ($anadoc_tab as $key => $anadoc_rec) {
                            if (!@rename($orig_file . '/' . $anadoc_rec['DOCFIL'], $dest_file . "/" . $anadoc_rec['DOCFIL'])) {
                                Out::msgStop("Archiviazione File", "Errore nello spostamento del file " . $anadoc_rec['DOCFIL'] . " !");
                            }
                            $anadoc_rec['DOCPAR'] = $propar_contr;                             
                            $anadoc_rec['DOCKEY'] = substr($anadoc_rec['DOCKEY'], 0, 10) . $propar_contr . substr($anadoc_rec['DOCKEY'], 11);
                            $nrow = ItaDB::DBUpdate($this->PROT_DB, "ANADOC", 'ROWID', $anadoc_rec);
                        }
                        $anapro_rec = $this->proLib->GetAnapro($prot, 'codice', $tipo);
                        if ($anapro_rec) {
                            $anapro_rec['PROPAR'] = $propar_contr;
                            $nrow = ItaDB::DBUpdate($this->PROT_DB, 'ANAPRO', 'ROWID', $anapro_rec);
                        }
                        Out::msgInfo('Protocollo Trasformato.', 'Trasformazione Completata del protocollo n° ' . $numProt);
                        Out::valore($this->nameForm . '_NumProt', '');
                        Out::valore($this->nameForm . '_Anno', date('Y'));
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Anno':
                        $tipo = $_POST[$this->nameForm . '_Arr_par'];
                        $numProt = $_POST[$this->nameForm . '_NumProt'];
                        $anno = $_POST[$this->nameForm . '_Anno'];
                        if (strlen($numProt) != 6 && strlen($anno) != 4)
                            break;
                        $anapro_rec = $this->proLib->GetAnapro($anno . $numProt, 'codice', $tipo);
                        if (!$anapro_rec) {
                            Out::valore($this->nameForm . '_NumProt', '');
                        }
                        break;
                    case $this->nameForm . '_NumProt':
                        $numProt = $_POST[$this->nameForm . '_NumProt'];
                        if ($numProt == '')
                            break;
                        $numProt = str_repeat("0", 6 - strlen(trim($numProt))) . trim($numProt);
                        Out::valore($this->nameForm . '_NumProt', $numProt);
                        break;
                }
                break;
            case 'returnNumAnte':
                $anapro_rec = $this->proLib->GetAnapro($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_NumProt', substr($anapro_rec['PRONUM'], 4));
                Out::valore($this->nameForm . '_Anno', substr($anapro_rec['PRONUM'], 0, 4));
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

    public function CreaCombo() {
        Out::select($this->nameForm . '_Arr_par', 1, "A", "1", "Arrivo");
        Out::select($this->nameForm . '_Arr_par', 1, "P", "0", "Partenza");
    }

}

?>