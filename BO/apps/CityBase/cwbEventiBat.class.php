<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOmnis.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaModelService.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaModelServiceData.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaOmnis.class.php';

class cwbEventiBat {

    const EVENTO_INIZIO = 1;
    const EVENTO_MESSAGGIO = 2;
    const EVENTO_CONCLUSIONE_OK = 3;
    const EVENTO_CONCLUSIONE_KO = 4;
    const EVENTO_RIEPILOGO = 5;
    const VISUALIZZATORE_EVENTI = "cwbEseguiAsync";

    public static $eventoEnum = array(
        1 => "Inizio",
        2 => "Messaggio",
        3 => "Conclusione Positiva",
        4 => "Conclusione Negativa"
    );

    /**
     * Apre la pagina standard per visualizzare la situazione degli eventi (cwbEseguiAsync)
     *
     * @param int $idElab Id del gruppo di messaggi
     */
    public static function apriVisualizzatoreByIdElab($idElab) {
        $model = cwbLib::apriFinestra(cwbEventiBat::VISUALIZZATORE_EVENTI, null, null, null, null);
        $model->setIdElaborazione($idElab);
        $model->parseEvent();
    }

    /**
     * Apre la pagina standard per visualizzare la situazione degli eventi (cwbEseguiAsync)
     * 
     * @param string $chiaveGenerica chiave specifica dell'applicativo
     * @param string $nameForm nameform a cui è associato l'evento
     * @param string $chiaveGenerica eventuale chiave specifica
     * @param string $utenteRich eventuale utente che ha effettuato la richiesta (se si vuole far vedere solo le tue azioni)
     */
    public static function apriVisualizzatore($nameForm, $chiaveGenerica = null, $utenteRich = null) {
        $model = cwbLib::apriFinestra(cwbEventiBat::VISUALIZZATORE_EVENTI, null, null, null, null);
        $model->setChiaveGenerica($chiaveGenerica);
        $model->setUtenteRichiesta($utenteRich);
        $model->setNameFormElab($nameForm);
        $model->parseEvent();
    }

    /**
     * Apre la pagina standard per visualizzare la situazione degli eventi (cwbEseguiAsync)
     *
     * @param int $idElab Id del gruppo di messaggi
     */
    public static function innestaVisualizzatoreByIdElab($divInnesto, $idElab, $vediUltimo = false, $delay = 10) {
        Out::html($divInnesto, "");
        $model = cwbLib::innestaForm(cwbEventiBat::VISUALIZZATORE_EVENTI, $divInnesto);
        if (!$model) {
            Out::msgStop("Errore", "Errore apertura form Eventi");
            return null;
        }
        $model->setIdElaborazione($idElab);
        $model->setEvent('openform');
        $model->setVediUltimo($vediUltimo);
        $model->setDelay($delay);
        $model->parseEvent();
        return $model;
    }

    /**
     * Apre la pagina standard per visualizzare la situazione degli eventi (cwbEseguiAsync)
     * 
     * @param string $chiaveGenerica chiave specifica dell'applicativo
     * @param string $nameForm nameform a cui è associato l'evento
     * @param string $chiaveGenerica eventuale chiave specifica
     * @param string $utenteRich eventuale utente che ha effettuato la richiesta (se si vuole far vedere solo le tue azioni)
     */
    public static function innestaVisualizzatore($divInnesto, $nameForm, $chiaveGenerica = null, $utenteRich = null, $vediUltimo = false, $delay = 10) {
        Out::html($divInnesto, "");
        $model = cwbLib::innestaForm(cwbEventiBat::VISUALIZZATORE_EVENTI, $divInnesto);
        if (!$model) {
            Out::msgStop("Errore", "Errore apertura form Eventi");
            return null;
        }
        $model->setChiaveGenerica($chiaveGenerica);
        $model->setUtenteRichiesta($utenteRich);
        $model->setNameFormElab($nameForm);
        $model->setEvent('openform');
        $model->setVediUltimo($vediUltimo);
        $model->setDelay($delay);
        $model->parseEvent();
        return $model;
    }

    /**
     * Esegue una chiamata ad omnis in modo asincrono. 
     * 
     * @param string $objectName oggetto omnis
     * @param string  $methodName metodo omnis
     * @param array $methodArgs parametri
     */
    public static function callOmnisAsync($objectName, $methodName, $methodArgs = '') {
        $omnisClient = itaOmnis::getOmnisClient();
        // timeout ad 1 secondo perché non mi interessa aspettare la risposta
        $omnisClient->getRestClient()->setExecutionTimeout(1);
        $omnisClient->callExecute($objectName, $methodName, $methodArgs);
    }

    /**
     * Cancella tutti gli eventi di un gruppo
     * 
     * @param int $idElab Id del gruppo di messaggi
     * @param int $tipoEvento tipo di evento da cancellare, se null cancella tutti
     * @return boolean true/false
     */
    public static function deleteEventGroupByIdElab($idElab, $tipoEvento = null) {
        if (!$idElab) {
            return false;
        }

        $filters['IDELAB'] = $idElab;
        $filters['TIPOEVENTO'] = $tipoEvento;
        $lib = new cwbLibDB_BGE();
        $lib->deleteBgeEventiBat($filters);
        return true;
    }

    /**
     * Cancella tutti gli eventi di un gruppo
     * 
     * @param string $chiaveGenerica chiave specifica di programma
     * @param string $nameForm nameform a cui è agganciato l'evento
     * @param int $tipoElaborazione Eventuale tipo di elaborazione specifica
     * @param string $ditta ditta associata. Se vuota prende quella di login
     * @param int $tipoEvento tipo di evento da cancellare, se null cancella tutti
     * @return boolean true/false
     */
    public static function deleteEventGroup($chiaveGenerica, $nameForm, $tipoElaborazione = null, $ditta = null, $tipoEvento = null) {
        if (!$chiaveGenerica || !$nameForm) {
            return false;
        }
        if (!$ditta) {
            $ditta = cwbParGen::getSessionVar('ditta');
        }

        $filters['KEY_ALFA'] = $chiaveGenerica;
        $filters['NAMEFORM'] = $nameForm;
        $filters['DITTA'] = $ditta;
        $filters['TIPOELABORAZIONE'] = $tipoElaborazione;
        $filters['TIPOEVENTO'] = $tipoEvento;
        $lib = new cwbLibDB_BGE();
        $lib->deleteBgeEventiBat($filters);
        return true;
    }

    private static function count($metodo, $idElaborazione = null, $chiaveGenerica = null, $nameForm = null, $tipoElaborazione = null, $ditta = null) {
        if (!$idElaborazione && !$nameForm) {
            return null;
        }

        $lib = new cwbLibDB_BGE();
        $filters = array();
        $filters['IDELAB'] = $idElaborazione;
        $filters['KEY_ALFA'] = $chiaveGenerica;
        $filters['NAMEFORM'] = $nameForm;
        $filters['DITTA'] = $ditta;
        $filters['TIPOELABORAZIONE'] = $tipoElaborazione;
        $res = $lib->$metodo($filters);
        return $res['CONTA'];
    }

    /**
     * Controlla se ci sono messaggi presenti per una certa elaborazione
     *
     * @param int $idElab Id del gruppo di messaggi
     * @return boolean true/false
     */
    public static function checkEventByIdElab($idElab) {
        $res = cwbEventiBat::count("countBgeEventiBat", $idElab);
        return $res['CONTA'] ? true : false;
    }

    /**
     * Controlla se ci sono messaggi presenti per una certa elaborazione
     * 
     * @param string $chiaveGenerica chiave specifica di programma
     * @param string $nameForm nameform a cui è agganciato l'evento
     * @param int $tipoElaborazione Eventuale tipo di elaborazione specifica
     * @param string $ditta ditta associata. Se vuota prende quella di login
     * @return boolean true/false
     */
    public static function checkEvent($chiaveGenerica, $nameForm, $tipoElaborazione = null, $ditta = null) {
        if (!$ditta) {
            $ditta = cwbParGen::getSessionVar('ditta');
        }

        $res = cwbEventiBat::count("countBgeEventiBat", null, $chiaveGenerica, $nameForm, $tipoElaborazione, $ditta);
        return $res ? true : false;
    }

    /**
     * Controlla se ci sono eventi attivi (se c'è un record di inizio che non ha un corrispettivo di fine)
     * @param int $idElab Id del gruppo di messaggi
     * @return type
     */
    public static function checkActiveEventByIdElab($idElaborazione) {
        $res = cwbEventiBat::count("countBgeEventiBatAttivi", $idElaborazione);
        return $res['CONTA'] ? true : false;
    }

    /**
     * Controlla se ci sono eventi attivi (se c'è un record di inizio che non ha un corrispettivo di fine)
     * @param string $chiaveGenerica chiave specifica di programma
     * @param string $nameForm nameform a cui è agganciato l'evento
     * @param int $tipoElaborazione Eventuale tipo di elaborazione specifica
     * @param string $ditta ditta associata. Se vuota prende quella di login
     * @return type
     */
    public static function checkActiveEvent($chiaveGenerica, $nameForm, $tipoElaborazione = null, $ditta = null) {
        $res = cwbEventiBat::count("countBgeEventiBatAttivi", null, $chiaveGenerica, $nameForm, $tipoElaborazione, $ditta);
        return $res['CONTA'] ? true : false;
    }

    /**
     * Torna la riga di riepilogo di tutti gli eventi attivi
     * @param string $chiaveGenerica chiave specifica di programma
     * @param string $nameForm nameform a cui è agganciato l'evento
     * @param int $tipoElaborazione Eventuale tipo di elaborazione specifica
     * @param string $ditta ditta associata. Se vuota prende quella di login
     * @return type
     */
    public static function getActiveEventsRiepilogo($chiaveGenerica, $nameForm, $tipoElaborazione = null, $ditta = null) {
        if (!$chiaveGenerica && !$nameForm) {
            return null;
        }

        $lib = new cwbLibDB_BGE();
        $filters = array();
        $filters['KEY_ALFA'] = $chiaveGenerica;
        $filters['NAMEFORM'] = $nameForm;
        $filters['DITTA'] = $ditta;
        $filters['TIPOELABORAZIONE'] = $tipoElaborazione;
        $res = $lib->leggiBgeEventiBatRiepiloghiAttivi($filters);
        return $res;
    }

    /**
     * Torna l'evento di inizio di un elaborazione
     * 
     * @param int $idElab 
     */
    public static function getEventInizio($idElab) {
        if (!$idElab) {
            return null;
        }

        $lib = new cwbLibDB_BGE();
        $filters['IDELAB'] = $idElab;
        $filters['TIPOEVENTO'] = cwbEventiBat::EVENTO_INIZIO;
        $recordInizio = $lib->leggiBgeEventiBat($filters, false);

        return $recordInizio;
    }

    /**
     * Se una form ha più eventi schedulati questo metodo torna tutti gli eventi di inizio schedulazione disponibili.
     * 
     * @param string $nameForm 
     * @param string $utenteRich     
     * @param string $chiaveGenerica     
     * @param string $ditta 
     * @return array records
     */
    public static function getEventsTestata($nameForm, $utenteRich = null, $chiaveGenerica = null, $ditta = null, $vediUltimo = false) {
        if (!$nameForm) {
            return null;
        }

        $lib = new cwbLibDB_BGE();
        $filters = array();
        $filters['NAMEFORM'] = $nameForm;
        $filters['CODUTERICH'] = $utenteRich;
        $filters['KEY_ALFA'] = $chiaveGenerica;
        $filters['DITTA'] = $ditta;
        $filters['TIPOEVENTO'] = cwbEventiBat::EVENTO_INIZIO;
        if ($vediUltimo) {
            $records = $lib->leggiBgeEventiBatVediUltimo($filters);
        } else {
            $records = $lib->leggiBgeEventiBat($filters);
        }

        return $records;
    }

    /**
     * Torna il testo di riepilogo di un elaborazione
     * @param type $idElab
     * @return type
     */
    public static function getEventRiepilogoByIdElab($idElab) {
        if (!$idElab) {
            return null;
        }
        $lib = new cwbLibDB_BGE();
        $filters = array();
        $filters['IDELAB'] = $idElab;
        $filters['TIPOEVENTO'] = cwbEventiBat::EVENTO_RIEPILOGO;
        $records = $lib->leggiBgeEventiBat($filters, false);

        return $records['DATI'];
    }

    /**
     * Torna il singolo messaggio per chiave
     * 
     * @param int $pk Chiave del record in tabella
     * @return array record
     */
    public static function getEventByPk($pk) {
        if (!$pk) {
            return null;
        }
        $lib = new cwbLibDB_BGE();
        return $lib->leggiBgeEventiBatChiave($pk);
    }

    /**
     * Torna tutti i messaggi per una certa elaborazione. Se viene trovato un messaggio di fine (ok o ko) cancella
     * Tutti i messaggi intermedi per fare pulizia, lasciando solo il messaggio di inizio, quello di riepilogo e quello di fine
     * che dovrà contenere il riepilogo per capire cosa è andato storto in caso di ko.
     * 
     * @param int $idElab Id del gruppo di messaggi
     * @return array records
     */
    public static function getEvents($idElab) {
        if (!$idElab) {
            return null;
        }
        $clean = false;
        $lib = new cwbLibDB_BGE();
        $filters = array();
        $filters['IDELAB'] = $idElab;
        $filters['TIPOEVENTO_DIVERSO'] = cwbEventiBat::EVENTO_RIEPILOGO;
        $records = $lib->leggiBgeEventiBat($filters);
        foreach ($records as $key => $value) {
            try {
                if (intval($value['TIPOEVENTO']) === cwbEventiBat::EVENTO_CONCLUSIONE_OK || intval($value['TIPOEVENTO']) === cwbEventiBat::EVENTO_CONCLUSIONE_KO) {
                    // se ho un evento di fine, cancello i record con tipoevento = 2, in modo da tenere come log
                    // solo lo stato iniziale e finale
                    $clean = true;
                }
            } catch (Exception $exc) {
                
            }
        }

        if ($clean) {
            $filters = array();
            $filters['TIPOEVENTO_NOTIN'] = array(cwbEventiBat::EVENTO_INIZIO, cwbEventiBat::EVENTO_CONCLUSIONE_OK, cwbEventiBat::EVENTO_CONCLUSIONE_KO);
            $filters['IDELAB'] = $value['IDELAB'];
            $lib = new cwbLibDB_BGE();
            $lib->deleteBgeEventiBat($filters);
        }

        return $records;
    }

    /**
     * Attiva un timer nella pagina scelta per andare a fare un polling sui messaggi
     * @param string $container
     * @param int $idElab 
     * @param int $delay default 5 sec
     */
    public static function activateTimerByIdElab($container, $idElab, $delay = 5) {
        $_POST['idElaborazione'] = $idElab; // i parametri si possono passare solo tramite la post serializzata
        Out::addTimer($container, $delay, null, true);
    }

    /**
     * Attiva un timer nella pagina scelta per andare a fare un polling sui messaggi
     * @param string $container
     * @param string $nameForm 
     * @param string $ditta 
     * @param string $utenteRich 
     * @param int $delay default 5 sec
     */
    public static function activateTimer($container, $nameForm, $ditta, $utenteRich = null, $delay = 5) {
        $_POST['nameFormElab'] = $nameForm; // i parametri si possono passare solo tramite la post serializzata
        $_POST['dittaElab'] = $ditta;
        $_POST['utenteRichiestaElab'] = $utenteRich;
        Out::addTimer($container, $delay, null, true);
    }

    /**
     * Torna l'idElab successivo (max +1)
     * @return int idElab
     */
    public static function nextIdElaborazione($ente = '') {
        $libDB = new cwbLibDB_BGE();
        if ($ente) {
            $libDB->setEnte($ente);
        }
        $db = $libDB->getCitywareDB();
        $sql = "SELECT MAX(IDELAB) AS MASSIMO FROM BGE_EVENTI_BAT";
        $result = ItaDB::DBSQLSelect($db, $sql, false);

        return $result['MASSIMO'] + 1;
    }

    /**
     * Esegue il log per un messaggio di inizio blocco di elaborazione
     * 
     * @param string $modelName NameForm a cui fa riferimento l'elaborazione
     * @param string $uteRichiesta Utente che ha richiesto l'elaborazione
     * @param string $msg Messaggio di fine schedulazione
     * @param int $idElab Id del gruppo di messaggi. 
     *              Se lasciato vuoto viene fatto in automatico il max + 1 e viene ritornato l'idElab generato da usare per le altre chiamate.
     * @param string $utente Utente che ha avviato la procedura 
     *              (in caso di cli, se l'utente mario rossi esegue la richiesta, ma il cli viene lanciato con l'utente schedulatore, uteRichiesta è mario rossi, utente è schedulatore)
     *              Se lasciato vuoto prende l'utente di sessione che ha eseguito il login
     * @param string $ditta Ditta di riferimento. Se lasciato vuoto prende la ditta di sessione che ha eseguito il login
     * @param int $tipoElab Tipo elaborazione specifica per pagina (es. per invio pec: 1- creazione testi, 2 - invio pec)
     * @param string $chiaveGenerica Chiave generica che può essere usata nel programma specifico per agganciare un record 
     *              (es per invio pec, contiene la chiave della tabella tba_lotti_invio per capire che la schedulazione corrente è legata a quel lotto specifico)
     * @return $idElab se ok, false se errore
     */
    public static function loggaInizio($modelName, $uteRichiesta, $msg, $idElab = null, $utente = null, $ditta = null, $tipoElab = null, $chiaveGenerica = null) {
        return cwbEventiBat::logga(cwbEventiBat::EVENTO_INIZIO, $modelName, $uteRichiesta, $msg, $idElab, $utente, $ditta, $tipoElab, $chiaveGenerica);
    }

    /**
     * Esegue il log per un messaggio intermedio (es. elaborazione record id 10)
     * 
     * @param int $idElab Id del gruppo di messaggi
     * @param string $msg messaggio
     * @return $idElab se ok, false se errore
     */
    public static function loggaMessaggio($idElab, $msg) {
        if (!$idElab) {
            return false;
        }
        $recordInizio = cwbEventiBat::getEventInizio($idElab);

        if (!$recordInizio) {
            return false;
        }
        return cwbEventiBat::logga(cwbEventiBat::EVENTO_MESSAGGIO, $recordInizio['NAMEFORM'], $recordInizio['CODUTERICH'], $msg, $idElab, $recordInizio['CODUTE'], $recordInizio['DITTA'], $recordInizio['TIPOELABORAZIONE'], $recordInizio['KEY_ALFA']);
    }

    /**
     * Esegue il log finale di esito negativo
     * 
     * @param int $idElab Id del gruppo di messaggi
     * @param string $msg messaggio
     * @return $idElab se ok, false se errore
     */
    public static function loggaEsitoKO($idElab, $msg) {
        if (!$idElab) {
            return false;
        }
        $recordInizio = cwbEventiBat::getEventInizio($idElab);

        if (!$recordInizio) {
            return false;
        }
        return cwbEventiBat::logga(cwbEventiBat::EVENTO_CONCLUSIONE_KO, $recordInizio['NAMEFORM'], $recordInizio['CODUTERICH'], $msg, $idElab, $recordInizio['CODUTE'], $recordInizio['DITTA'], $recordInizio['TIPOELABORAZIONE'], $recordInizio['KEY_ALFA']);
    }

    /**
     * Esegue il log finale di esito positivo
     * 
     * @param int $idElab Id del gruppo di messaggi
     * @param string $msg messaggio
     * @return $idElab se ok, false se errore
     */
    public static function loggaEsitoOK($idElab, $msg) {
        if (!$idElab) {
            return false;
        }
        $recordInizio = cwbEventiBat::getEventInizio($idElab);

        if (!$recordInizio) {
            return false;
        }
        return cwbEventiBat::logga(cwbEventiBat::EVENTO_CONCLUSIONE_OK, $recordInizio['NAMEFORM'], $recordInizio['CODUTERICH'], $msg, $idElab, $recordInizio['CODUTE'], $recordInizio['DITTA'], $recordInizio['TIPOELABORAZIONE'], $recordInizio['KEY_ALFA']);
    }

    /**
     * E' il record di riepilogo della situazione, contiene il messaggio di riepilogo che mostrerà a video la 
     * situazione attuale (es. elaborati 10 record su 30).
     * Ci deve essere solo una riga di questo tipo per idElab, che va aggiornata di volta in volta con la situazione attuale.
     * @param int $idElab
     * @param string $msg messaggio di riepilogo da mostrare a video
     * @return boolean
     */
    public static function loggaRiepilogo($idElab, $msg) {
        try {
            if (!$idElab) {
                return false;
            }
            $lib = new cwbLibDB_BGE();
            $filters['IDELAB'] = $idElab;
            $filters['TIPOEVENTO'] = cwbEventiBat::EVENTO_RIEPILOGO;
            $records = $lib->leggiBgeEventiBat($filters);
            if (!$records) {
                // se non lo trovo ne creo uno nuovo copiando i dati dal messaggio di inizio
                $filters['IDELAB'] = $idElab;
                $filters['TIPOEVENTO'] = cwbEventiBat::EVENTO_INIZIO;
                $inizio = $lib->leggiBgeEventiBat($filters, false);
                cwbEventiBat::logga(cwbEventiBat::EVENTO_RIEPILOGO, $inizio['NAMEFORM'], $inizio['CODUTERICH'], $msg, $idElab, $inizio['CODUTE'], $inizio['DITTA'], $inizio['TIPOELABORAZIONE'], $inizio['KEY_ALFA']);
            } else if (count($records) > 1) {
                // ci può essere un solo riepilogo, se ne trovo piu di uno li cancello e ne reinserisco uno solo
                $riepilogoDaInserire = $records[0];
                cwbEventiBat::deleteEventGroupByIdElab($idElab, cwbEventiBat::EVENTO_RIEPILOGO);
                cwbEventiBat::logga(cwbEventiBat::EVENTO_RIEPILOGO, $riepilogoDaInserire['NAMEFORM'], $riepilogoDaInserire['CODUTERICH'], $msg, $idElab, $riepilogoDaInserire['CODUTE'], $riepilogoDaInserire['DITTA'], $riepilogoDaInserire['TIPOELABORAZIONE'], $riepilogoDaInserire['KEY_ALFA']);
            } else {
                // ne ho trovato solo uno, quindi ci vado in update del mesaggio
                $riepilogo = $records[0];
                $riepilogo['DATI'] = $msg;

                $tableName = 'BGE_EVENTI_BAT';
                $db = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');
                $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true);
                $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                $modelServiceData->addMainRecord($tableName, $riepilogo);
                $modelService->updateRecord($db, $tableName, $modelServiceData->getData(), "Aggiornato record su " . $tableName);
            }
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    private static function logga($tipoEvento, $modelName, $uteRichiesta, $msg, $idElab = null, $utente = null, $ditta = null, $tipoElab = null, $chiaveGenerica = null) {
        try {
            if (!$idElab) {
                $idElab = cwbEventiBat::nextIdElaborazione($ditta);
            }
            if (!$utente) {
                $utente = cwbParGen::getSessionVar('nomeUtente');
            }
            if (!$ditta) {
                $ditta = cwbParGen::getSessionVar('ditta');
            }
            $tableName = 'BGE_EVENTI_BAT';

            $data = array(
                'DITTA' => $ditta,
                'NAMEFORM' => $modelName,
                'CODUTERICH' => $uteRichiesta,
                'IDELAB' => $idElab,
                'TIPOEVENTO' => $tipoEvento,
                'TIPOELABORAZIONE' => $tipoElab,
                'KEY_ALFA' => $chiaveGenerica,
                'DATI' => $msg,
                'CODUTE' => $utente,
                'DATAOPER' => date("d/m/Y"),
                'TIMEOPER' => date("H:m:s")
            );
            $libDB = new cwbLibDB_BGE();
            if ($ditta) {
                $libDB->setEnte($ditta);
            }
            $db = $libDB->getCitywareDB();
            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true);
            $modelServiceData = new itaModelServiceData(new cwbModelHelper());
            $modelServiceData->addMainRecord($tableName, $data);
            $modelService->insertRecord($db, $tableName, $modelServiceData->getData(), "Inserito da cli record su " . $tableName);

            return $idElab;
        } catch (Exception $ex) {
            return false;
        }
    }

}
