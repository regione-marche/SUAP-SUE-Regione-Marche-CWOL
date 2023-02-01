<?php

/**
 *
 * Classe di sincronizzaizione nel protocollo delle ricevute di consegna
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft sRL
 * @license
 * @version    28.10.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praWsClientManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

class praSyncRicevute extends itaModel {

//    private $praLib;
    private $errMessage;
    private $errCode;

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

    function SyncRicevute($compak = "") {
        $wherePratica = "";
        $whereCompak = " AND COMPAK<>''";
        if ($compak) {
            $whereCompak = " AND COMPAK = '$compak'";
        }
        $utiEnte = new utiEnte();
        $praLib = new praLib();
        $arrayDocRicevute = $retBlocca = array();
        $ritorno = array();
        $ritorno['errPraticaParam'] = false;
        $countPassi = $countPos = $countNeg = 0;
        $PARMENTE_rec = $utiEnte->GetParametriEnte();
        $praWsClientManager = praWsClientManager::getInstance($PARMENTE_rec['TIPOPROTOCOLLO']);

        /*
         * verifica parametri suap se presente numero pratica da cui iniziare
         * la protocollazione ricevute
         */
        $filent_rec = $praLib->GetFilent(28);
        if ($filent_rec['FILDE2']) {
            $pratica = $filent_rec['FILDE3'] . $filent_rec['FILDE2'];
            $proges_rec_check = $praLib->GetProges($pratica);
            /*
             * Verifico il numero di pratica. Se non c'è, torno errore
             */
            if ($proges_rec_check) {
                $wherePratica = " AND GESNUM >= '$pratica'";
            } else {
                $ritorno['errPraticaParam'] = true;
                return $ritorno;
            }
        }

        $pramail_tabCOMPAK = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT DISTINCT COMPAK FROM PRAMAIL WHERE FLPROT=0 AND ISRICEVUTA=1 $whereCompak $wherePratica", true);
        foreach ($pramail_tabCOMPAK as $pramail_recCOMPAK) {
            $pracomP_rec = $praLib->GetPracomP($pramail_recCOMPAK['COMPAK']);
            if ($pracomP_rec['COMPRT']) {
                $praWsClientManager->setKeyPasso($pramail_recCOMPAK['COMPAK']);
                //@todo: VERIFICARE CHE I DATI OGGETTO SIANO PULITI DALL'ISTANZA PRECEDENTE
                $praWsClientManager->loadAllegatiFromRicevutePartenza(true);
                $arrayDocRicevute = $praWsClientManager->getArrayDocRicevute();
                if ($arrayDocRicevute) {
                    $countPassi++;
                    $retRicevute = $praWsClientManager->AggiungiRicevute();
                    if ($retRicevute["Status"] == "-1") {
                        $countNeg++;
                    } else {
                        //$retBlocca = $this->bloccaRicevute($arrayDocRicevute['pramail_rec']);
                        $countPos++;
                    }
                    $ritorno['DettaglioEsito'][$pramail_recCOMPAK['COMPAK']]['Status'] = $retRicevute['Status'];
                    //$ritorno['DettaglioEsito'][$pramail_recCOMPAK['COMPAK']]['Message'] = $retRicevute['Message'];
                    $ritorno['DettaglioEsito'][$pramail_recCOMPAK['COMPAK']]['RetDetails'] = $retRicevute['RetDetails'];
                    $ritorno['DettaglioEsito'][$pramail_recCOMPAK['COMPAK']]['ErrDetails'] = $retRicevute['ErrDetails'];
                    $ritorno['DettaglioEsito'][$pramail_recCOMPAK['COMPAK']]['RetBlocca'] = $retRicevute['RetBlocca'];
                    $ritorno['DettaglioEsito'][$pramail_recCOMPAK['COMPAK']]['ErrBlocca'] = $retRicevute['ErrBlocca'];
                }
            }
        }
        $ritorno['Totali']['PassiEstratti'] = $countPassi;
        $ritorno['Totali']['PassiEsitoPos'] = $countPos;
        $ritorno['Totali']['PassiEsitoNeg'] = $countNeg;
        return $ritorno;
    }

//    public function bloccaRicevute($rowidArr = array()) {
//        $retBlocca = array();
//        $praLib = new praLib();
//        if (!$rowidArr) {
//            return;
//        }
//        foreach ($rowidArr as $key => $rowid) {
//            $errUpd = false;
//            $pramail_rec = $praLib->getPraMail($rowid['ROWID'], 'ROWID');
//            //$pramail_rec['FLPROT'] = 1;
//            $pramail_rec['FLPROT'] = 0;
//            try {
//                $nrow = ItaDB::DBUpdate($praLib->getPRAMDB(), 'PRAMAIL', 'ROWID', $pramail_rec);
//                if ($nrow == -1) {
//                    $this->setErrCode(-1);
//                    $retBlocca["ErrBlocca"][$key] = "blocco ricevuta " . $pramail_rec['TIPORICEVUA'] . "-" . $pramail_rec['ROWID'] . " fallito.";
//                    $errUpd = true;
//                    //$this->setErrMessage('Sincronizzazione Ricevute su PRAMAIL Fallito.');
//                }
//            } catch (Exception $e) {
//                $this->setErrCode(-1);
//                $retBlocca["ErrBlocca"][$key] = "blocco ricevuta " . $pramail_rec['TIPORICEVUA'] . "-" . $pramail_rec['ROWID'] . " fallito -->" . $e->getMessage();
//                $errUpd = true;
//                //$this->setErrMessage("blocco ricevuta " . $pramail_rec['TIPORICEVUA'] . "-" . $pramail_rec['ROWID'] . " fallito -->" . $e->getMessage());
//            }
//            $retBlocca["RetBlocca"][$key] = "blocco ricevuta " . $pramail_rec['TIPORICEVUA'] . "-" . $pramail_rec['ROWID'] . " avvenuto correttamente.";
//
//            /*
//             * Auditing operazione
//             */
//            if ($errUpd) {
//                $estremi = $retBlocca["ErrBlocca"][$key];
//            } else {
//                $estremi = $retBlocca["RetBlocca"][$key];
//            }
//            $this->eqAudit->logEqEvent($this, array(
//                'Operazione' => eqAudit::OP_UPD_RECORD,
//                'DB' => $praLib->getPRAMDB()->getDB(),
//                'DSet' => 'PRAMAIL',
//                'Estremi' => $estremi
//            ));
//        }
//        return $retBlocca;
//    }
}

?>
