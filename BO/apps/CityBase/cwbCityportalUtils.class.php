<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_Cityportal.class.php';
include_once ITA_BASE_PATH . '/apps/CityMedia/cwaLibDB_ADE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BWE.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

/**
 * Utils per cityportal 
 *
 * @author l.cardinali
 */
class cwbCityportalUtils {

    const APOATTI = 'apoatti';
    const APOATTIIT = 'apoattiit';
    const APOALLEG = 'apoalleg';
    const APOATTITE = 'apoattite';

    private $lastErrorCode;
    private $lastErrorDescription;
    private $lastAction;

    public function handleError($code, $description) {
        $this->setLastErrorCode($code);
        $this->setLastErrorDescription($description);
        error_log($this->getLastErrorCode() . " - " . $this->getLastErrorDescription());
    }

    public function resetLastError() {
        $this->setLastErrorCode(0);
        $this->setLastErrorDescription("");
    }

    public function getLastErrorCode() {
        return $this->lastErrorCode;
    }

    public function getLastErrorDescription() {
        return $this->lastErrorDescription;
    }

    public function setLastErrorCode($lastErrorCode) {
        $this->lastErrorCode = $lastErrorCode;
    }

    public function setLastErrorDescription($lastErrorDescription) {
        $this->lastErrorDescription = $lastErrorDescription;
    }

    public function getLastAction() {
        return $this->lastAction;
    }

    public function setLastAction($lastAction) {
        $this->lastAction = $lastAction;
    }

    public function allineaDatiDelibere() {
        // leggo da db cityportal (deve esserci tag [CITYPORTAL] SU CONNECTION.INI)
        $libCityportal = new cwbLibDB_Cityportal();
        $attiCityp = $libCityportal->leggiApoAtti(array());

        if ($attiCityp && !array_key_exists('DATAOPER', $attiCityp[0])) {
            $this->handleError(-1, "Campo DATAOPER mancante su apoatti");
            return false;
        }

        // leggo da db cityware
        $libAde = new cwaLibDB_ADE();
        $attiCw = $libAde->leggiAdeAttiSchedulatore(array());

        $daInserire = array();
        $daCancellare = array();
        $daAggiornare = array();
        $this->checkDati($attiCw, $attiCityp, $daInserire, $daCancellare, $daAggiornare);
        $modelService = itaModelServiceFactory::newModelService('', true, true);
        $modelServiceData = new itaModelServiceData(new cwbModelHelper());

        if ($daInserire) {
            foreach ($daInserire as $insert) {
                cwbDBRequest::getInstance()->startManualTransaction(null, $libCityportal->getCityportalDb());
                try {
                    // insert apoatti
                    $insertApoattiDecod = $this->decodificaDaCitywareACityportal($insert);
                    $modelServiceData->addMainRecord($this->APOATTI, $insertApoattiDecod);
                    $modelService->insertRecord($libCityportal->getCityportalDb(), $this->APOATTI, $modelServiceData->getData(), "");
                    // insert apoattit
                    $listApoattit = $libAde->leggiAdeAttiit(array('PROG_ATTO' => $insert['PROG_ATTO']));
                    if ($listApoattit) {
                        foreach ($listApoattit as $insertApoattit) {
                            $insertApoattitDecod = $this->decodificaDaCitywareACityportal($insertApoattit);
                            $modelServiceData->addMainRecord($this->APOATTIIT, $insertApoattitDecod);
                            $modelService->insertRecord($libCityportal->getCityportalDb(), $this->APOATTIIT, $modelServiceData->getData(), "");
                        }
                    }

                    // insert apoalleg
                    $listApoattiAl = $libAde->leggiAdeAttial(array('PROG_ATTO' => $insert['PROG_ATTO']));
                    if ($listApoattiAl) {
                        foreach ($listApoattiAl as $insertApoattiAl) {
                            $insertApoattiAlDecod = $this->decodificaDaCitywareACityportal($insertApoattiAl);
                            $modelServiceData->addMainRecord($this->APOALLEG, $insertApoattiAlDecod);
                            $modelService->insertRecord($libCityportal->getCityportalDb(), $this->APOALLEG, $modelServiceData->getData(), "");
                        }
                    }

                    // insert apoattite
                    $listApoattite = $libAde->leggiAdeAttite(array('PROG_ATTO' => $insert['PROG_ATTO']));
                    if ($listApoattite) {
                        foreach ($listApoattite as $insertApoattite) {
                            $insertApoattiteDecod = $this->decodificaDaCitywareACityportal($insertApoattite);
                            $modelServiceData->addMainRecord($this->APOATTITE, $insertApoattiteDecod);
                            $modelService->insertRecord($libCityportal->getCityportalDb(), $this->APOATTITE, $modelServiceData->getData(), "");
                        }
                    }

                    cwbDBRequest::getInstance()->commitManualTransaction();
                } catch (Exception $exc) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                }
            }
        }

        if ($daCancellare) {
            foreach ($daCancellare as $cancel) {
                cwbDBRequest::getInstance()->startManualTransaction(null, $libCityportal->getCityportalDb());
                try {
                    // delete apoatti                    
                    $modelServiceData->addMainRecord($this->APOATTI, $cancel);
                    $modelService->deleteRecord($libCityportal->getCityportalDb(), $this->APOATTI, $modelServiceData->getData(), "");
                    // delete apoattit
                    $listApoattit = $libCityportal->leggiApoAttiit(array('PROGATTO' => $cancel['PROGATTO']));
                    if ($listApoattit) {
                        foreach ($listApoattit as $key => $cancelApoattit) {
                            $modelServiceData->addMainRecord($this->APOATTIIT, $cancelApoattit);
                            $modelService->deleteRecord($libCityportal->getCityportalDb(), $this->APOATTIIT, $modelServiceData->getData(), "");
                        }
                    }

                    // delete apoalleg
                    $listApoattiAl = $libCityportal->leggiApoAlleg(array('PROGATTO' => $cancel['PROGATTO']));
                    if ($listApoattiAl) {
                        foreach ($listApoattiAl as $cancelApoattiAl) {
                            $modelServiceData->addMainRecord($this->APOALLEG, $cancelApoattiAl);
                            $modelService->deleteRecord($libCityportal->getCityportalDb(), $this->APOALLEG, $modelServiceData->getData(), "");
                        }
                    }

                    // delete apoattite
                    $listApoattite = $libCityportal->leggiApoAttite(array('PROGATTO' => $cancel['PROGATTO']));
                    if ($listApoattite) {
                        foreach ($listApoattite as $cancelApoattite) {
                            $modelServiceData->addMainRecord($this->APOATTITE, $cancelApoattite);
                            $modelService->deleteRecord($libCityportal->getCityportalDb(), $this->APOATTITE, $modelServiceData->getData(), "");
                        }
                    }

                    cwbDBRequest::getInstance()->commitManualTransaction();
                } catch (Exception $exc) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                }
            }
        }

        if ($daAggiornare) {
            foreach ($daAggiornare as $update) {
                cwbDBRequest::getInstance()->startManualTransaction(null, $libCityportal->getCityportalDb());
                try {
                    // aggiorna apoatti
                    $updateApoattiDecod = $this->decodificaDaCitywareACityportal($update);
                    $modelServiceData->addMainRecord($this->APOATTI, $updateApoattiDecod);
                    $modelService->updateRecord($libCityportal->getCityportalDb(), $this->APOATTI, $modelServiceData->getData(), "");

                    // aggiorna apoattit
                    $listApoattit = $libAde->leggiAdeAttiit(array('PROG_ATTO' => $update['PROG_ATTO']));
                    if ($listApoattit) {
                        foreach ($listApoattit as $updateApoattit) {
                            $updateApoattitDecod = $this->decodificaDaCitywareACityportal($updateApoattit);
                            $apoattit = $libCityportal->leggiApoAttiit(array('PROGATTO' => $updateApoattitDecod['PROGATTO'], 'PROGRIGA' => $updateApoattitDecod['PROGRIGA'], 'RIGAITER' => $updateApoattitDecod['RIGAITER']), false);

                            $modelServiceData->addMainRecord($this->APOATTIIT, $updateApoattitDecod);
                            if (!$apoattit) {
                                $modelService->insertRecord($libCityportal->getCityportalDb(), $this->APOATTIIT, $modelServiceData->getData(), "");
                            } else {
                                $modelService->updateRecord($libCityportal->getCityportalDb(), $this->APOATTIIT, $modelServiceData->getData(), "");
                            }
                        }
                    }

                    // update apoalleg
                    $listApoattiAl = $libAde->leggiAdeAttial(array('PROG_ATTO' => $update['PROG_ATTO']));
                    if ($listApoattiAl) {
                        foreach ($listApoattiAl as $updateApoattiAl) {
                            $updateApoattiAlDecod = $this->decodificaDaCitywareACityportal($updateApoattiAl);
                            $apoattiAl = $libCityportal->leggiApoAlleg(array('PROGATTO' => $updateApoattiAlDecod['PROGATTO'], 'RIGATESTO' => $updateApoattiAlDecod['RIGATESTO']), false);

                            $modelServiceData->addMainRecord($this->APOALLEG, $updateApoattiAlDecod);
                            if (!$apoattiAl) {
                                $modelService->insertRecord($libCityportal->getCityportalDb(), $this->APOALLEG, $modelServiceData->getData(), "");
                            } else {
                                $modelService->updateRecord($libCityportal->getCityportalDb(), $this->APOALLEG, $modelServiceData->getData(), "");
                            }
                        }
                    }

                    // update apoattite
                    $listApoattite = $libAde->leggiAdeAttite(array('PROG_ATTO' => $update['PROG_ATTO']));
                    if ($listApoattite) {
                        foreach ($listApoattite as $updateApoattite) {
                            $updateApoattiteDecod = $this->decodificaDaCitywareACityportal($updateApoattite);
                            $apoAttite = $libCityportal->leggiApoAttite(array('PROGATTO' => $updateApoattiteDecod['PROGATTO'], 'RIGATESTO' => $updateApoattiteDecod['RIGATESTO']), false);

                            $modelServiceData->addMainRecord($this->APOATTITE, $updateApoattiteDecod);

                            if (!$apoAttite) {
                                $modelService->insertRecord($libCityportal->getCityportalDb(), $this->APOATTITE, $modelServiceData->getData(), "");
                            } else {
                                $modelService->updateRecord($libCityportal->getCityportalDb(), $this->APOATTITE, $modelServiceData->getData(), "");
                            }
                        }
                    }

                    cwbDBRequest::getInstance()->commitManualTransaction();
                } catch (Exception $exc) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                }
            }
        }
    }

    private function decodificaDaCitywareACityportal($daConvertire) {
        $toReturn = array();
        foreach ($daConvertire as $key => $value) {
            // su cityware i campi hanno _ su cityportal no (es. PROG_ATTO -> progatto)
            if ($key == 'RIGA_ALLEG') {
                $toReturn["RIGATESTO"] = $value;
            } else {
                $toReturn[str_replace("_", "", $key)] = utf8_encode($value);
            }
        }

        return $toReturn;
    }

    private function checkDati($datiCw, $datiCityp, &$daInserire = array(), &$daCancellare = array(), &$daAggiornare = array()) {
        $y = 0;
        $zLast = 0;
        // i 2 record sono ordinati uguali quindi se va tutto bene ogni chiave deve contenere 2 record uguali
        for ($i = 0; $i < count($datiCw); $i++) {
            $zLast = 0;

            if ($y >= count($datiCityp)) {
                // se entro qui significa che ho scorso tutto $datiCityp, quindi $datiCw[$i] manca di sicuro
                $daInserire[] = $datiCw[$i];

                continue;
            }

            // confronto i 2 record a parita di posizione
            if ($datiCw[$i]['PROG_ATTO'] != $datiCityp[$y]['PROGATTO']) {
                // se sono diversi vado avanti sulla lista $datiCityp per vedere se lo trovo piu avanti
                $trovato = false;
                for ($z = $y; $z < count($datiCityp); $z++) {
                    $zLast = $z;
                    if ($datiCw[$i]['PROG_ATTO'] == $datiCityp[$z]['PROGATTO']) {
                        // sono uguali quindi porto il contatore di $datiCityp avanti e continuo il confronto
                        // con le chiavi disallineate
                        $y = $z;
                        $trovato = true;
                        break;
                    } else {
                        // non sono uguali, devo verificare se proseguire a cercare o fermarmi guardando
                        // se i record di $datiCityp[$z] sono minori o maggiori di $datiCw[$i]
                        // se sono minori posso continuare a cercare, se sono maggiori mi fermo tanto essendo 
                        // ordinati uguali significa che non lo troverò più
                        if ($datiCityp[$z]['PROGATTO'] < $datiCw[$i]['PROG_ATTO']) {
                            // $datiCityp[$z] è minore di $datiCw[$i], essendo ordinati desc,
                            // mi fermo tanto non lo trovo più 
                            // segno $datiCw[$i] come mancante e proseguo incrementando solo $i
                            $daInserire[] = $datiCw[$i];
                            break;
                        } else {
                            // $datiCityp[$z] è maggiore di $datiCw[$i] 
                            // continuo a cercare su $datiCityp[$z]
                            // finché non lo trovo oppure $datiCw[$i] diventa maggiore di $datiCityp[$z]
                            // intanto questo record $datiCityp[$z] lo segno come mancante
                            $daCancellare[] = $datiCityp[$z];
                        }
                    }
                }
                if (!$trovato) {
                    $y = $zLast - 1;
                }
            } else {
                // se sono uguali controllo se la dataoper è diversa
                if ($datiCw[$i]['DATAOPER'] != $datiCityp[$y]['DATAOPER']) {
                    $daAggiornare[] = $datiCw[$i];
                }
            }

            $y++;
        }

        $yTemp = $zLast > $y ? $zLast : $y;
        // tutti i $datiCityp che non ho elaborato li cancello in quando non ci sono su cw
        if ($yTemp < count($datiCityp)) {
            for ($i = $yTemp; $i < count($datiCityp); $i++) {
                $daCancellare[] = $datiCityp[$i];
            }
        }
    }

    public function allineaDatiUtente($progsoggweb, $inviaMail = 0) {
        $startTransaction = false;
        try {
            if (!$progsoggweb) {
                $this->handleError(-1, "Progsoggweb mancante");
                return false;
            }

            // leggo da db cityware
            $libBwe = new cwbLibDB_BWE();
            $bweUtente = $libBwe->leggiBweUtenti(array('PROGSOGWEB' => $progsoggweb), false);

            // leggo da db cityportal (deve esserci tag [CITYPORTAL] SU CONNECTION.INI)
            $libCityportal = new cwbLibDB_Cityportal();
            $bpoUtente = $libCityportal->leggiBpoUtenti(array('ID' => $progsoggweb));
            $iduteweb = $bpoUtente['IDUTEWEB'];
            $email = $bpoUtente['EMAIL'];

            $modelService = itaModelServiceFactory::newModelService('', true, true);
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());

            cwbDBRequest::getInstance()->startManualTransaction(null, $libCityportal->getCityportalDb());
            $startTransaction = true;

            // svuoto i dati di questo utente da cityportal
            $libCityportal->deleteBpoUtenteAllinea(array('ID' => $progsoggweb));
            $libCityportal->deleteAututeAllinea(array('IDUTENTE' => $progsoggweb));

            // se su cityware c'è l'utente lo ricreo su cityportal
            if (!$bweUtente) {
                $libCityportal->deleteDatiUte(array('ID' => $progsoggweb));
            } else {
                $libCityportal->deleteDatiUteAllinea(array('ID' => $progsoggweb));
                $bpoUtente = $bweUtente;
                $bpoUtente['ID'] = $bweUtente['PROGSOGWEB'];
                $bpoUtente['DISABILITA'] = $bweUtente['FLAG_DIS'];
                $bpoUtente['DATASCADECONV'] = $bweUtente['DATASCACONV'];

                $modelServiceData->addMainRecord("Bpoutenti", $bpoUtente);
                $modelService->insertRecord($libCityportal->getCityportalDb(), "Bpoutenti", $modelServiceData->getData(), "");

                // leggo da db cityware
                $bweAututes = $libBwe->leggiBweAutute(array('PROGSOGWEB' => $progsoggweb));

                if ($bweAututes) {
                    $idNext = cwbLibCalcoli::trovaProgressivo("ID", "Bpoautute", null, null, $libCityportal->getCityportalDb());

                    foreach ($bweAututes as $bweAutute) {
                        $bpoAutute = $bweAutute;
                        $bpoAutute['ID'] = $idNext;
                        $bpoAutute['IDUTENTE'] = $bweUtente['PROGSOGWEB'];
                        $idNext++;
                        $modelServiceData->addMainRecord("Bpoautute", $bpoAutute);
                        $modelService->insertRecord($libCityportal->getCityportalDb(), "Bpoautute", $modelServiceData->getData(), "");
                    }
                }
            }

            cwbDBRequest::getInstance()->commitManualTransaction();
        } catch (Exception $exc) {
            if ($startTransaction) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
            }
            $this->handleError(-1, $exc->getMessage());
            return false;
        }

        if ($inviaMail) {
            $this->inviaMail($email, $iduteweb, $bweUtente);
        }
        return true;
    }

    private function inviaMail($email, $iduteweb, $creato) {
        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';

        $Account = '';
        $devLib = new devLib();
        /*
         * Account Mittente
         */
        $ItaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        if ($ItaEngine_mail_rec) {
            $Account = $ItaEngine_mail_rec['CONFIG'];
        } else {
            return false;
        }

        $emlMailBox = emlMailBox::getInstance($Account);

        if (!$emlMailBox) {
            return;
        }
        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            return;
        }

        $libCityportal = new cwbLibDB_Cityportal();
        $bpoParam = $libCityportal->leggiBpoParam();
        if ($bpoParam['DESENTE']) {
            $ente = 'del Comune di' . $bpoParam['DESENTE'];
        } else {
            $ente = '';
        }

        $msg .= "Cityportal - portale del cittadino " . $ente;
        $msg .= "<br>";
        if ($creato) {
            $subject = 'Profilo attivato';
            $msg .= "Profilo sull'utente " . $iduteweb . " attivato per le funzioni dei tributi.";
        } else {
            $subject = 'Profilo cancellato';
            $msg .= "Profilo utente " . $iduteweb . " cancellato.";
        }
        $outgoingMessage->setSubject($subject);
        $outgoingMessage->setBody($msg);
        $outgoingMessage->setEmail(trim($email));

        if ($emlMailBox->sendMessage($outgoingMessage, false, false)) {
            return true;
        } else {
            return false;
        }
    }

}
