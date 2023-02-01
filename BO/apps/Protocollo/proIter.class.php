<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    04.12.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proUfficio.class.php';

class proIter extends itaModel {

    const ITESTATO_DAGESTIRE = 0;
    const ITESTATO_RIFIUTATO = 1;
    const ITESTATO_INCARICO = 2;
    const ITESTATO_ARCHIVIATO = 5; //*** Nuovo
    const ITEFLA_APERTO = '';
    const ITEFLA_CHIUSO = '2';
    const ITETIP_ALLAFIRMA = 1;
    const ITETIP_PARERE = 2;
    const ITETIP_RICHIESTA_PARERI_AVVIO = 3;
    const ITETIP_RICHIESTA_PARERI_ESITO = 4;
    const ITETIP_PARERE_RIASSEGNATO = 5;
    const ITETIP_PARERE_DELEGA = 'D'; // DELEGA.
    const ITECHECK_CREA = 1;
    const ITECHECK_CREACHIUSO = 2;
    const ITECHECK_NONCREARE = 3;

    /* Da definire 
      const ITESTATO_INVIATO = 99;
      const ITESTATO_CHIUSO = 99;
     */

    public $PROT_DB;
    public $proLib;
    public $iter;
    public $iterTree;
    public $protocollo;
    private $lastExitCode;
    private $lastMessage;

    /**
     * 
     * @param type $proLib
     * @param type $protocollo
     * @param type $tipoProt
     * @return boolean|\proIter
     */
    public static function getInstance($proLib, $protocollo = '', $tipoProt = '', $caricaIter = true) {
        try {
            $obj = new proIter();
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }
        if (!$proLib) {
            return false;
        }
        $obj->proLib = $proLib;
        if (is_array($protocollo)) {
            $obj->protocollo = $protocollo;
        } else {
            if ($protocollo && $tipoProt) {
                $obj->protocollo = $obj->proLib->GetAnapro($protocollo, 'codice', $tipoProt);
            } else {
                return false;
            }
        }
        if ($caricaIter === true) {
            if (!$obj->caricaIter()) {
                return false;
            }
        }
        return $obj;
    }

    public static function checkIterMovimenti($proLib, $protocollo = '', $tipoProt = '') {
        $sql = "SELECT COUNT(ROWID) AS MOVIMENTI FROM ARCITE WHERE ITEKEY LIKE '" . $protocollo . $tipoProt . "%' AND ITENODO<>'INS'";
        $ArciteMovimenti_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($ArciteMovimenti_rec['MOVIMENTI'] > 0) {
            return true;
        }
        return false;
    }

    public function getLastExitCode() {
        return $this->lastExitCode;
    }

    public function getLastMessage() {
        return $this->lastMessage;
    }

    public function getIter() {
        return $this->iter;
    }

    public function setIter($iter) {
        $this->iter = $iter;
    }

    public function getIterTree() {
        return $this->iterTree;
    }

    public function setIterTree($iterTree) {
        $this->iterTree = $iterTree;
    }

    /**
     * Carica Iter
     * @return boolean
     */
    function caricaIter() {
        $this->iter = $this->GetIterFromDB();
        $this->iterTree = $this->GetIterTreeFromDB();
        return true;
    }

    /**
     * 
     * @return array
     */
    function GetIterFromDB() {
        if ($this->protocollo) {
            $sql = "SELECT * FROM ARCITE WHERE ITEKEY LIKE '" . $this->protocollo['PRONUM'] . $this->protocollo['PROPAR'] . "%'";
            return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        } else {
            return array();
        }
    }

    function GetIterTreeFromDB($Itepre = '', $parents = array()) {
        $treeRet = array();
        $i = 0;
        if (in_array($Itepre, $parents)) {
            return array();
        }
        $parents[] = $Itepre;
        if ($this->protocollo) {
            $sql = "SELECT * FROM ARCITE WHERE ITEPRE='$Itepre' AND ITEKEY LIKE '" . $this->protocollo['PRONUM'] . $this->protocollo['PROPAR'] . "%'";
            $Arcite_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
            if ($Arcite_tab) {
                foreach ($Arcite_tab as $Arcite_rec) {
                    $treeRet[$i] = array(
                        "RECORD" => $Arcite_rec,
                        "CHILDS" => $this->GetIterTreeFromDB($Arcite_rec['ITEKEY'], $parents)
                    );
                    $i++;
                }
            }
            return $treeRet;
        } else {
            return array();
        }
    }

    public function getIterNode($itekey) {
        $sql = "SELECT * FROM ARCITE WHERE ITEKEY = '$itekey'";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    public function leggiIterNode($iterNode) {
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        if ($arcite_rec) {
            if (!$arcite_rec['ITEDLE']) {
                $arcite_rec['ITEDLE'] = date("Ymd");
                $arcite_rec['ITEDLEORA'] = date("H:i:s");
                $update_Info = 'Letta Azione iter: ' . $arcite_rec['ITEKEY'];
                if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
                    return false;
                }
            }
        } else {
            return false;
        }
        $this->sincIterNodePadre($arcite_rec);
        return $arcite_rec;
    }

    /**
     * Accetta la presa in carico di un nodo iter inviato da un mittente
     * 
     * @param type $iterNode
     * @return boolean
     */
    public function accettaIterNode($iterNode) {
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        if ($arcite_rec) {
            $arcite_rec['ITEDATACC'] = date("Ymd");
            $arcite_rec['ITEDATACCORA'] = date("H:i:s");
            $arcite_rec['ITESTATO'] = self::ITESTATO_INCARICO;
            $update_Info = 'Accetta Azione iter: ' . $arcite_rec['ITEKEY'];
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
                return false;
            }
        } else {
            return false;
        }
        return $arcite_rec;
    }

    /**
     * Rifiuta un nodo iter inviato dal mittente
     * 
     * @param type $iterNode
     * @param type $motivo
     * @return boolean
     */
    public function rifiutaIterNode($iterNode, $motivo) {
        if (!$motivo) {
            return false;
        }
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        $arcite_acquisito = array();
        $arciteDaAggiornare = $arcite_rec;
        if ($arcite_rec) {
            // Caso di trasmisisone a ufficio, deve essere acquisito.
            if ($arcite_rec['ITEDES'] == '' && $arcite_rec['ITEUFF'] <> '') {
                $soggetto = proSoggetto::getInstance($this->proLib, proSoggetto::getCodiceSoggettoFromIdUtente(), $arcite_rec['ITEUFF']);
                $motivoAcq = 'TRASMISSIONE DI UFFICIO RIFIUTATA';
                $arcite_acquisito = $this->acquisisciIterNode($arcite_rec, $motivoAcq, $soggetto);
                $arciteDaAggiornare = $arcite_acquisito;
            }
            // Aggiorno info di rifiuto.
            $arciteDaAggiornare['ITEDATRIF'] = date("Ymd");
            $arciteDaAggiornare['ITEDATRIFORA'] = date("H:i:s");
            $arciteDaAggiornare['ITESTATO'] = self::ITESTATO_RIFIUTATO;
            $arciteDaAggiornare['ITEMOTIVO'] = $motivo;
            $update_Info = 'Rifiuta Azione iter: ' . $arciteDaAggiornare['ITEKEY'];
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arciteDaAggiornare, $update_Info)) {
                return false;
            }
        } else {
            return false;
        }

        if ($arcite_rec['ITEPRE']) {
            $arcite_padre = $this->getIterNode($arcite_rec['ITEPRE']);
            /* Se una delega, il rifiuto deve andare al padre del delegante. */
            if ($arcite_rec['ITETIP'] == self::ITETIP_PARERE_DELEGA) {
                /* Solo se c'è un padre: Altrimenti torno al delegante. */
                if ($arcite_padre['ITEPRE']) {
                    $arcite_padre = $this->getIterNode($arcite_padre['ITEPRE']);
                }
            }
            $anamed_ant = $this->proLib->GetAnamed($arcite_padre['ITEDES'], 'codice');
            if ($arcite_padre['ITEBASE'] && $this->protocollo['PROPAR'] == 'A' && $this->proLib->checkRiservatezzaProtocollo($this->protocollo) == false) {
                $livello = 1;
            } else {
                $livello = 0;
            }
            $itetip = '';
            $iteges = 1;
            if ($arcite_rec['ITETIP'] == '1') {
                $itetip = '1';
                $iteges = 0;
            }

            if ($arcite_acquisito) {
                $arcite_rec = $arcite_acquisito;
            }

            $this->insertIterNodeFromAnamed($anamed_ant, $arcite_rec, array(
                "UFFICIO" => $arcite_padre['ITEUFF'],
                "NOTE" => "INOLTRATO DA RIFIUTO",
                "GESTIONE" => $iteges,
                "LIVELLO" => $livello,
                "ITETIP" => $itetip
            ));
        }
        return $arcite_rec;
    }

    /**
     * Riprende in carico un nodo iter se rifiutato dal destinatario
     * 
     * @param type $iterNode
     * @param type $motivo
     * @return boolean|string
     */
    public function riprendiIterNode($iterNode, $motivo = '') {
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        if ($arcite_rec) {
            $arcite_rec['ITEDATRIF'] = '';
            $arcite_rec['ITEDATRIFORA'] = '';
            $arcite_rec['ITEMOTIVO'] = $motivo;
            $arcite_rec['ITEANN'] = "ANNULLATO RIFIUTO";
            $arcite_rec['ITESUS'] = '';
            //MM 12012015
            // se vengo da un rifiuto di un protocollo in visione
            // ripristino la visione PERCHE' è DIVERSA NEI DATI DALLA GESTIONE DELLA TRASMISSIONE
            if ($arcite_rec['ITEGES'] == 0) {
                $arcite_rec['ITEFIN'] = '';
                $arcite_rec['ITEFINORA'] = '';
                $arcite_rec['ITEFLA'] = proIter::ITEFLA_APERTO;
                $arcite_rec['ITESTATO'] = self::ITESTATO_DAGESTIRE;
                $arcite_rec['ITEDATACC'] = '';
                $arcite_rec['ITEDATACCORA'] = '';
            } else {
                $arcite_rec['ITESTATO'] = self::ITESTATO_INCARICO;
                $arcite_rec['ITEDATACC'] = date('Ymd');
                $arcite_rec['ITEDATACCORA'] = date("H:i:s");
            }

            $update_Info = 'Annullamento rifiuta Azione iter: ' . $arcite_rec['ITEKEY'];
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
                return false;
            }
        } else {
            return false;
        }

        $sql = "SELECT * FROM ARCITE WHERE ITEPRE = '" . $arcite_rec['ITEKEY'] . "' AND ITEFIN = ''";
        $arcite_sus = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        if ($arcite_sus) {
            $arcite_sus['ITEFIN'] = date("Ymd");
            $arcite_sus['ITEFINORA'] = date("H:i:s");
            $arcite_sus['ITEANNULLATO'] = 1;
            $arcite_sus['ITEANN'] = "ANNULLATO PER RIPRESA IN CARICO";
            $arcite_sus['ITEDATAANNULLAMENTO'] = date("Ymd");
            $arcite_sus['ITEDATAANNULLAMENTOORA'] = date("H:i:s");
            $update_Info = 'Annullamento rifiuta Azione iter: ' . $arcite_rec['ITEKEY'];
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_sus, $update_Info)) {
                return false;
            }
        }
        return $arcite_rec;
    }

    public function annullaIterNode($iterNode, $extraParm = array()) {

        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        $note = (isset($extraParm['NOTE'])) ? $extraParm['NOTE'] : 'INVIO ANNULLATO';
        if ($arcite_rec) {
            $arcite_rec['ITEFIN'] = date("Ymd");
            $arcite_rec['ITEFINORA'] = date("H:i:s");
            $arcite_rec['ITEANNULLATO'] = 1;
            $arcite_rec['ITEANN'] = $note;
            $arcite_rec['ITEDATAANNULLAMENTO'] = date("Ymd");
            $arcite_rec['ITEDATAANNULLAMENTOORA'] = date("H:i:s");
            $update_Info = 'Annullamento invia Azione iter: ' . $arcite_rec['ITEKEY'];
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
                return false;
            }
            $this->annullaDelega($arcite_rec);
        }
        return $arcite_rec;
    }

    public function acquisisciIterNode($iterNode, $motivo, $soggettoObj) {
        if (!$motivo) {
            return false;
        }
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        if ($arcite_rec) {
            $arcite_rec['ITEDATACQ'] = date("Ymd");
            $arcite_rec['ITEDATACQORA'] = date("H:i:s");
            $arcite_rec['ITEMOTIVO'] = $motivo;
            $update_Info = 'Acquisizione Azione iter: ' . $arcite_rec['ITEKEY'];
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
                return false;
            }
        } else {
            return false;
        }
        $soggetto_dati = $soggettoObj->getSoggetto();
        $anamed_rec = $this->proLib->GetAnamed($soggetto_dati['CODICESOGGETTO']);
        $Gestione = 1;
        if (!$arcite_rec['ITEGES']) {
            $Gestione = 0;
        }
        $arcite_acquisito = $this->insertIterNodeFromAnamed($anamed_rec, $arcite_rec, array(
            "NODO" => "ACQ",
            "UFFICIO" => $soggetto_dati['CODICEUFFICIO'],
            "NOTE" => "ACQUISITO DA TRASMISSIONE A STESSO UFFICIO",
            "GESTIONE" => $Gestione,
            "LIVELLO" => 0
        ));
        return $arcite_acquisito;
    }

    /**
     * 
     * 
     * @param array|string $iterNode record arcite o chiave itekey
     * @return boolean|int
     */
    public function chiudiIterNode($iterNode) {
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        if ($arcite_rec) {
            $arcite_rec['ITEFIN'] = date("Ymd");
            $arcite_rec['ITEFINORA'] = date("H:i:s");
            $arcite_rec['ITEFLA'] = 2;
            $update_Info = 'Chiudo Azione iter: ' . $arcite_rec['ITEKEY'];
            try {
                $this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info);
            } catch (Exception $exc) {
                App::log($exc);
                return false;
            }
        } else {
            return false;
        }
        return $arcite_rec;
    }

    public function invertiEvidenzaIterNode($iterNode) {
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        if ($arcite_rec) {
            if ($arcite_rec['ITEEVIDENZA']) {
                $arcite_rec['ITEEVIDENZA'] = 0;
            } else {
                $arcite_rec['ITEEVIDENZA'] = 1;
            }
            $update_Info = "Metto evidenza {$arcite_rec['ITEEVIDENZA']} Azione iter: {$arcite_rec['ITEKEY']}";
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
                return false;
            }
        } else {
            return false;
        }
        return $arcite_rec;
    }

    public function insertIterNodeFromAnades($anades_rec, $padre = '', $extraParm = array()) {
        $destinatario = array();
        $destinatario['DESCUF'] = $anades_rec['DESCUF'];
        $destinatario['DESCOD'] = $anades_rec['DESCOD'];
        $destinatario['DESGES'] = $anades_rec['DESGES'];
        $destinatario['DESTERMINE'] = $anades_rec['DESTERMINE'];
//        $destinatario['ITEBASE'] = $extraParm['ITEBASE'];

        return $this->insertIterNode($destinatario, $padre, $extraParm);
    }

    public function insertIterNodeFromAnamed($anamed_rec, $padre = '', $extraParm = array()) {
        $destinatario = array();
        $destinatario['DESCOD'] = $anamed_rec['MEDCOD'];
        $destinatario['DESGES'] = $extraParm['GESTIONE'];
        $destinatario['DESTERMINE'] = $extraParm['TERMINE'];
        $destinatario['DESCUF'] = $extraParm['UFFICIO'];
//        $destinatario['ITEBASE'] = $extraParm['ITEBASE'];
        return $this->insertIterNode($destinatario, $padre, $extraParm);
    }

    /**
     * 
     * @param type $destinatario
     * @param type $padre
     * @return boolean
     */
    public function insertIterNode($destinatario, $padre = '', $extraParm = '') {
        /*
         * Ufficio Sempre Obbligatorio
         * 
         */
        if (!$destinatario['DESCUF']) {
            $this->lastExitCode = "-1";
            $this->lastMessages = "Inserimento nodo iter ufficio del soggetto destinatario mancante.";
            Out::msgInfo("titolo", $this->lastMessages);
            return false;
        }

        /*
         * Controllo se la trasmissione è collettiva di ufficio
         * Se SI predispongo i parametri per ignorare il codice soggetto
         * Se NO Controllo la presenza obbligatori del codice soggetto
         * 
         */
        $trxUfficio = (isset($destinatario['TRXUFFICIO'])) ? $destinatario['TRXUFFICIO'] : 0;
        if ($trxUfficio) {
            /*
             * Trasmissione collettiva a ufficio uso un oggetto limitato per i dati di ufficio
             * 
             */
            if ($destinatario['DESCUF']) {
                $ufficio = proUfficio::getInstance($this->proLib, $destinatario['DESCUF']);
                if (!$ufficio) {
                    $this->lastExitCode = "-1";
                    $this->lastMessages = "Inserimento nodo iter: dati del'ufficio {$destinatario['DESCUF']} non reperibili.";
                    Out::msgInfo("titolo", $this->lastMessages);
                    return false;
                }
                $datiSoggetto = $ufficio->getUfficio();
                $orgKey = $ufficio->getOrgKey();
                $orgKeyLayout = $ufficio->getOrgKeyLayout();
            }
        } else {

            if ($destinatario['DESCOD']) {
//                $this->lastExitCode = "-1";
//                $this->lastMessages = "Inserimento nodo iter: dati del soggetto {$destinatario['DESCOD']} per l'ufficio {$destinatario['DESCUF']} non reperibili.";
//                Out::msgInfo("titolo", $this->lastMessages);
//                return false;
//                }

                /*
                 * Trasmissione diretta a soggetto
                 * 
                 */
                $soggetto = proSoggetto::getInstance($this->proLib, $destinatario['DESCOD'], $destinatario['DESCUF']);
                if (!$soggetto) {
                    $this->lastExitCode = "-1";
                    $this->lastMessages = "Inserimento nodo iter: dati del soggetto {$destinatario['DESCOD']} per l'ufficio {$destinatario['DESCUF']} non reperibili.";
                    Out::msgInfo("titolo", $this->lastMessages);
                    return false;
                }
                $datiSoggetto = $soggetto->getSoggetto();
                $orgKey = $soggetto->getOrgKey();
                $orgKeyLayout = $soggetto->getOrgKeyLayout();
            }
        }


        $itefin = '';
        $itefinora = '';
        $itefla = '';
        $risultato = $this->checkEsistenzaUtente($destinatario['DESCOD']);
        if ($risultato == proIter::ITECHECK_NONCREARE) {
            return true;
        } else if ($risultato == proIter::ITECHECK_CREACHIUSO) {
            $itefin = date("Ymd");
            $itefinora = date("H:i:s");
            $itefla = '2';
        }

        $note = (isset($extraParm['NOTE'])) ? $extraParm['NOTE'] : '';
        $tipoNodo = (isset($extraParm['NODO'])) ? $extraParm['NODO'] : 'TRX';
        $nodeDate = (isset($extraParm['NODEDATE'])) ? $extraParm['NODEDATE'] : date('Ymd');
        $nodeTime = (isset($extraParm['NODETIME'])) ? $extraParm['NODETIME'] : date("H:i:s");
        $arcite_rec = array();
        $arcite_rec['ITEPRO'] = $this->protocollo['PRONUM'];
        $arcite_rec['ITEPAR'] = $this->protocollo['PROPAR'];
        $arcite_rec['ITEUFF'] = $destinatario['DESCUF']; //$uffici; //$anades_rec['DESCUF'];
        $arcite_rec['ITEDAT'] = $nodeDate;
        $arcite_rec['ITEDATORA'] = $nodeTime;
        $arcite_rec['ITEDES'] = $destinatario['DESCOD'];
        $arcite_rec['ITEANT'] = '';
        $arcite_rec['ITEANN'] = $note;
        $arcite_rec['ITEFIN'] = $itefin;
        $arcite_rec['ITEFINORA'] = $itefinora;
        $arcite_rec['ITEFLA'] = $itefla;
        $arcite_rec['ITETER'] = '';
        $arcite_rec['ITEGES'] = $destinatario['DESGES'];
        $arcite_rec['ITETERMINE'] = $destinatario['DESTERMINE'];
        $arcite_rec['ITERUO'] = $datiSoggetto['CODICERUOLO'];
        $arcite_rec['ITESETT'] = $datiSoggetto['CODICESERVIZIO'];
        $arcite_rec['ITEPROTECT'] = $datiSoggetto['LIVELLOPROTEZIONE'];
        $arcite_rec['ITEBASE'] = $extraParm['ITEBASE'];
        $arcite_rec['ITETIP'] = $extraParm['ITETIP'];
        $arcite_rec['ITEEVIDENZA'] = $padre['ITEEVIDENZA'];
        $arcite_rec['ITEORA'] = $nodeTime;
        $arcite_rec['ITEORGKEY'] = $orgKey;
        $arcite_rec['ITEORGWORKLIV'] = (isset($extraParm['LIVELLO'])) ? $extraParm['LIVELLO'] : 0;
        $arcite_rec['ITEORGLAYOUT'] = $orgKeyLayout;
        $arcite_rec['ITENODO'] = $tipoNodo;
//        App::log('insertIterNode');
//        App::log($this->protocollo);
        $arcite_rec['ITEKEY'] = $this->proLib->IteKeyGenerator($this->protocollo['PRONUM']
                , $destinatario['DESCOD']
                , date('Ymd')
                , $this->protocollo['PROPAR']);
        if ($padre) {
            $arcite_rec['ITEANT'] = $padre['ITEDES'];
            $arcite_rec['ITEPRE'] = $padre['ITEKEY'];
        }
        /*
         * Prima trasmetto al destinatario:
         */
        $insert_Info = 'Inserimento: ' . $arcite_rec['ITEPRO'] . ' ' . $arcite_rec['ITEPAR'];
        if (!$this->insertRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $insert_Info)) {
            return false;
        }
        /*
         * Poi Controllo per Trasmissione a Sostituto:
         * All'interno Controllo e Verifica se trasmettere anche ad un sostituto.
         */
        if (!$extraParm['NODELEGA']) {
            $this->TrasmissioneASostituto($destinatario, $padre, $extraParm, $arcite_rec);
        }

        if (!$this->sincIterNodePadre($arcite_rec)) {
            return false;
        }
//        if ($padre) {
//            $padre['ITESUS'] = $destinatario['DESCOD'];
//            $padre['ITENTRAS'] = $padre['ITENTRAS'] + 1;
//            $update_Info = 'Collegamento iter padre a iter figlio: ' . $padre['ITEKEY'] . ' --> ' . $arcite_rec['ITEKEY'];
//            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $padre, $update_Info)) {
//                return false;
//            }
//        }

        return $this->getIterNode($arcite_rec['ITEKEY']);
    }

    public function getUltimoBase($nodes, $ultimo = '') {
        foreach ($nodes as $node) {
            if ($node["RECORD"]['ITEBASE'] == 1) {
                $ultimo = $this->getUltimoBase($node['CHILDS'], $node["RECORD"]);
            }
        }

        return $ultimo;
    }

    public function sincIterNodePadre($iterNode) {
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }
        $arcite_padre = $this->getIterNode($arcite_rec['ITEPRE']);
        if (!$arcite_padre) {
            return true;
        }
        if (!$arcite_padre['ITEKEY']) {
            return true;
        }
        $sql = "SELECT * FROM ARCITE WHERE ITEPRE='{$arcite_padre['ITEKEY']}'"; // AND ITETIP <> '" . self::ITETIP_PARERE_DELEGA . "' ";
        $arcite_figli_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        $trasmessi = count($arcite_figli_tab);
        $letti = 0;
        $ultimoItesus = "";
        foreach ($arcite_figli_tab as $arcite_figli_rec) {
            if ($arcite_figli_rec['ITETIP'] == self::ITETIP_PARERE_DELEGA) {
                continue;
            }
            if ($arcite_figli_rec['ITEDLE']) {
                $letti +=1;
            }
            $ultimoItesus = $arcite_figli_rec['ITEDES'];
        }
        $arcite_padre["ITENTRAS"] = $trasmessi;
        $arcite_padre["ITENLETT"] = $letti;
        $arcite_padre["ITESUS"] = $ultimoItesus;
        $update_Info = 'Aggiorno dati padre: ' . $arcite_padre['ITEKEY'];
        if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_padre, $update_Info)) {
            return false;
        }
        return true;
    }

    public function sincIterProtocollo($sincParm = array()) {
        $this->caricaIter();
        include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
        $accLib = new accLib();
        if (!$this->iter) {
            $utenti_rec = $accLib->GetUtenti($this->protocollo['PROUTE'], 'utelog');
            $anamed_rec = $this->proLib->GetAnamed($utenti_rec['UTEANA__1']);
            if (!$anamed_rec) {
                return false;
            }
            $pr = (int) substr($this->protocollo['PRONUM'], 4) . "/" . substr($this->protocollo['PRONUM'], 0, 4);
            $note = "TRASMISSIONE DA INSERIMENTO PROTOCOLLO N.$pr";
            if ($this->protocollo['PROPAR'] == "C") {
                $note = "TRASMISSIONE DA INSERIMENTO COMUNICAZIONE FORMALE N.$pr";
            } else if ($this->protocollo['PROPAR'] == "F") {
                $note = "INSERIMENTO FASCICOLO N.{$this->protocollo['PROFASKEY']}";
            } else if ($this->protocollo['PROPAR'] == "N") {
                $note = "INSERIMENTO SOTTOFASCICOLO N.{$this->protocollo['PROFASKEY']} - $pr";
            } else if ($this->protocollo['PROPAR'] == "T") {
                $note = "INSERIMENTO AZIONE N.{$this->protocollo['PROFASKEY']} - $pr";
            } else if ($this->protocollo['PROPAR'] == "I") {
                $note = "INSERIMENTO DOCUMENTALE N. $pr";
            } else if ($this->protocollo['PROPAR'] == "W") {
                $note = "TRASMISSIONE DA INSERIMENTO PASSO ";
            }
            $extraParms = array_merge($sincParm, array(
                "UFFICIO" => $this->protocollo['PROUOF'],
                "ITEBASE" => 1,
                "NODO" => "INS",
                "GESTIONE" => 1,
                "NOTE" => $note));
            $retPadre = $this->insertIterNodeFromAnamed($anamed_rec, '', $extraParms);
            if (!$retPadre) {
                return false;
            }
            // Sempre chiuso ?
//            if ($this->protocollo['PROPAR'] != "F" && $this->protocollo['PROPAR'] != "N" && $this->protocollo['PROPAR'] != "T") {
            $retPadre = $this->chiudiIterNode($retPadre);
            if (!$retPadre) {
                return false;
            }
//            }
            $retPadre = $this->leggiIterNode($retPadre);
            if (!$retPadre) {
                return false;
            }

            if (!$this->insertVisibilitaMittenti($retPadre, $sincParm)) {
                return false;
            }
            if ($this->protocollo['PRONOTE']) {
                $notechild = $this->protocollo['PRONOTE'];
            } else {
                $notechild = "TRASMESSO DA PROTOCOLLO";
            }
            if ($this->protocollo['PROPAR'] == "C") {
                $notechild = "TRASMESSO DA INSERIMENTO COMUNICAZIONE FORMALE";
            }
            if ($this->protocollo['PROPAR'] == "I") {
                $notechild = "TRASMESSO DA INSERIMENTO DOCUMENTALE";
            }
            if ($this->protocollo['PROPAR'] == "F") {
                $notechild = "ASSEGNATA RESPONSABILITA' DEL FASCICOLO";
            }
            if ($this->protocollo['PROPAR'] == "N") {
                $notechild = "ASSEGNATA RESPONSABILITA' DEL SOTTOFASCICOLO";
            }
            if ($this->protocollo['PROPAR'] == "W") {
                $notechild = "TRASMESSO DA PASSO";
            }
            $anades_tab = $this->proLib->GetAnades($this->protocollo['PRONUM'], 'codice', true, $this->protocollo['PROPAR'], "T");
            $extraParms = array_merge($sincParm, array(
                "NODO" => "ASS",
                "NOTE" => $notechild
            ));
            foreach ($anades_tab as $key => $anades_rec) {
                $retInsert = $this->insertIterNodeFromAnades($anades_rec, $retPadre, $extraParms);
                if (!$retInsert) {
                    return false;
                }
                /* Se è un Fascicolo o Sottofascicolo, l'assegnazione nasce chiusa */
                if ($this->protocollo['PROPAR'] == "F" || $this->protocollo['PROPAR'] == "N") {
                    $retInsert = $this->chiudiIterNode($retInsert);
                    if (!$retInsert) {
                        return false;
                    }
                    $retInsert = $this->leggiIterNode($retInsert);
                    if (!$retInsert) {
                        return false;
                    }
                }
            }
        } else {
            $anades_T = $this->proLib->GetAnades($this->protocollo['PRONUM'], 'codice', true, $this->protocollo['PROPAR'], "T");
            $idxAnades = array();
            foreach ($anades_T as $anades_rec) {
                if (!in_array($anades_rec['DESCOD'] . "/" . $anades_rec['DESCUF'], $idxAnades)) {
                    $risultato = $this->checkEsistenzaUtente($anades_rec['DESCOD']);
                    if ($risultato != proIter::ITECHECK_NONCREARE) {
                        $idxAnades[] = $anades_rec['DESCOD'] . "/" . $anades_rec['DESCUF'];
                    }
                }

                foreach ($this->iter as $arcite_rec) {
                    if ($arcite_rec['ITENODO'] == 'ASS' && $arcite_rec['ITEDES'] == $anades_rec['DESCOD'] && $arcite_rec['ITEUFF'] == $anades_rec['DESCUF']) {
                        $arcite_padre = $this->getIterNode($arcite_rec['ITEPRE']);
                        if ($arcite_padre['ITEBASE']) {
                            $arcite_rec['ITEGES'] = $anades_rec['DESGES'];
                            $arcite_rec['ITETERMINE'] = $anades_rec['DESTERMINE'];
                            itaDB::DBUpdate($this->proLib->getPROTDB(), "ARCITE", "ROWID", $arcite_rec);
                        }
                    }
                }
            }

            $idxIter = array();
            foreach ($this->iter as $arcite_rec) {
                if ($arcite_rec['ITEBASE'] == 0 && $arcite_rec['ITENODO'] == "ASS") {
                    if (!in_array($arcite_rec['ITEDES'] . "/" . $arcite_rec['ITEUFF'], $idxIter)) {
                        $idxIter[] = $arcite_rec['ITEDES'] . "/" . $arcite_rec['ITEUFF'];
                    }
                }
            }
            $daEliminare = array_diff($idxIter, $idxAnades);
            $daInserire = array_diff($idxAnades, $idxIter);


            $ultimoBase = $this->getUltimoBase($this->iterTree);
            if (!$ultimoBase) {
                $utenti_rec = $accLib->GetUtenti($this->protocollo['PROUTE'], 'utelog');
                $anamed_rec = $this->proLib->GetAnamed($utenti_rec['UTEANA__1']);
                if (!$anamed_rec) {
                    return false;
                }
                $pr = (int) substr($this->protocollo['PRONUM'], 4) . "/" . substr($this->protocollo['PRONUM'], 0, 4);
                $note = "TRASMISSIONE DA INSERIMENTO PROTOCOLLO N.$pr";
                if ($this->protocollo['PROPAR'] == "C") {
                    $note = "TRASMISSIONE DA INSERIMENTO COMUNICAZIONE FORMALE N.$pr";
                } else if ($this->protocollo['PROPAR'] == "F") {
                    $note = "INSERIMENTO FASCICOLO N.{$this->protocollo['PROFASKEY']}";
                } else if ($this->protocollo['PROPAR'] == "N") {
                    $note = "INSERIMENTO SOTTOFASCICOLO N.{$this->protocollo['PROFASKEY']} - $pr";
                } else if ($this->protocollo['PROPAR'] == "T") {
                    $note = "INSERIMENTO AZIONE N.{$this->protocollo['PROFASKEY']} - $pr";
                } else if ($this->protocollo['PROPAR'] == "I") {
                    $note = "INSERIMENTO DOCUMENTALE N. $pr";
                }
                $retPadre = $this->insertIterNodeFromAnamed($anamed_rec, $ultimoBase, array(
                    "NODO" => "INS",
                    "UFFICIO" => $this->protocollo['PROUOF'],
                    "ITEBASE" => 1,
                    "GESTIONE" => 1,
                    "NOTE" => $note));
                if (!$retPadre) {
                    return false;
                }

                $retPadre = $this->chiudiIterNode($retPadre);
                if (!$retPadre) {
                    return false;
                }
                $retPadre = $this->leggiIterNode($retPadre);
                if (!$retPadre) {
                    return false;
                }

                foreach ($this->iterTree as $treeNode) {
                    $arcite_rec = $treeNode['RECORD'];
                    $arcite_rec['ITEPRE'] = $retPadre['ITEKEY'];
                    $arcite_rec['ITEANT'] = $retPadre['ITEDES'];
                    $update_Info = 'Ricollegamento iter padre a iter figlio: ' . $retPadre['ITEKEY'] . ' --> ' . $arcite_rec['ITEKEY'];
                    if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
                        return false;
                    }

                    $retPadre['ITESUS'] = $arcite_rec['ITEDES'];
                    $update_Info = 'Ricollegamento iter padre a iter figlio (ITESUS): ' . $retPadre['ITEKEY'] . ' --> ' . $arcite_rec['ITEKEY'];
                    if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $retPadre, $update_Info)) {
                        return false;
                    }
                }
                $this->caricaIter();
                $ultimoBase = $retPadre;
                if ($daInserire) {
                    $retPadre = "";
                    $utenti_rec = $accLib->GetUtenti($this->protocollo['PROUTE'], 'utelog');
                    $anamed_rec = $this->proLib->GetAnamed($utenti_rec['UTEANA__1']);
                    if (!$anamed_rec) {
                        return false;
                    }
                    $pr = (int) substr($this->protocollo['PRONUM'], 4) . "/" . substr($this->protocollo['PRONUM'], 0, 4);
                    $note = "TRASMISSIONE DA MODIFICA PROTOCOLLO N.$pr";
                    if ($this->protocollo['PROPAR'] == "C") {
                        $note = "TRASMISSIONE DA MODIFICA COMUNICAZIONE FORMALE N.$pr";
                    } else if ($this->protocollo['PROPAR'] == "F") {
                        $note = "MODIFICA FASCICOLO N.{$this->protocollo['PROFASKEY']}";
                    } else if ($this->protocollo['PROPAR'] == "N") {
                        $note = "MODIFICA SOTTOFASCICOLO N.{$this->protocollo['PROFASKEY']} - $pr";
                    } else if ($this->protocollo['PROPAR'] == "T") {
                        $note = "MODIFICA AZIONE N.{$this->protocollo['PROFASKEY']} - $pr";
                    } else if ($this->protocollo['PROPAR'] == "I") {
                        $note = "MODIFICA DOCUMENTALE N. $pr";
                    } else if ($this->protocollo['PROPAR'] == "W") {
                        $note = "MODIFICA PASSO";
                    }
                    $retPadre = $this->insertIterNodeFromAnamed($anamed_rec, $ultimoBase, array(
                        "UFFICIO" => $this->protocollo['PROUOF'],
                        "ITEBASE" => 1,
                        "NODO" => "INS",
                        "GESTIONE" => 1,
                        "NOTE" => $note));
                    if (!$retPadre) {
                        return false;
                    }
                    $retPadre = $this->chiudiIterNode($retPadre);
                    if (!$retPadre) {
                        return false;
                    }
                    $retPadre = $this->leggiIterNode($retPadre);
                    if (!$retPadre) {
                        return false;
                    }
                }
            } else {
                if ($daInserire) {
                    $retPadre = "";
                    $utenti_rec = $accLib->GetUtenti($this->protocollo['PROUTE'], 'utelog');
                    $anamed_rec = $this->proLib->GetAnamed($utenti_rec['UTEANA__1']);
                    if (!$anamed_rec) {
                        return false;
                    }
                    $pr = (int) substr($this->protocollo['PRONUM'], 4) . "/" . substr($this->protocollo['PRONUM'], 0, 4);
                    $note = "TRASMISSIONE DA MODIFICA PROTOCOLLO N.$pr";
                    if ($this->protocollo['PROPAR'] == "C") {
                        $note = "TRASMISSIONE DA MODIFICA COMUNICAZIONE FORMALE N.$pr";
                    } else if ($this->protocollo['PROPAR'] == "F") {
                        $note = "MODIFICA FASCICOLO N.{$this->protocollo['PROFASKEY']}";
                    } else if ($this->protocollo['PROPAR'] == "N") {
                        $note = "MODIFICA SOTTOFASCICOLO N.{$this->protocollo['PROFASKEY']} - $pr";
                    } else if ($this->protocollo['PROPAR'] == "T") {
                        $note = "MODIFICA AZIONE N.{$this->protocollo['PROFASKEY']} - $pr";
                    } else if ($this->protocollo['PROPAR'] == "I") {
                        $note = "MODIFICA DOCUMENTALE N. $pr";
                    } else if ($this->protocollo['PROPAR'] == "W") {
                        $note = "MODIFICA PASSO";
                    }
                    $retPadre = $this->insertIterNodeFromAnamed($anamed_rec, $ultimoBase, array(
                        "UFFICIO" => $this->protocollo['PROUOF'],
                        "ITEBASE" => 1,
                        "NODO" => "INS",
                        "GESTIONE" => 1,
                        "NOTE" => $note));
                    if (!$retPadre) {
                        return false;
                    }
                    $retPadre = $this->chiudiIterNode($retPadre);
                    if (!$retPadre) {
                        return false;
                    }
                    $retPadre = $this->leggiIterNode($retPadre);
                    if (!$retPadre) {
                        return false;
                    }
                }
            }
            foreach ($daEliminare as $value) {
                list($xdescod, $xcoduff) = explode("/", $value);
                foreach ($this->iter as $key => $arcite_rec) {
                    if ($arcite_rec['ITEDES'] == $xdescod && $arcite_rec['ITEUFF'] == $xcoduff && $arcite_rec['ITENODO'] == "ASS") {
                        if ($arcite_rec['ITEPRE']) {
                            $arcite_padre = $this->getIterNode($arcite_rec['ITEPRE']);
                            if ($arcite_padre['ITEBASE'] == 0) {
                                continue;
                            }
                        } else {
                            continue;
                        }
                        if ($arcite_rec['ITEACC'] == '') {
                            $arcite_rec['ITENODO'] = "ANN";
                            $arcite_rec['ITEFIN'] = date("Ymd");
                            $arcite_rec['ITEFINORA'] = date("H:i:s");
                            if ($arcite_rec['ITEPAR'] == 'F' || $arcite_rec['ITEPAR'] == 'N') {
                                $arcite_rec['ITEANN'] = 'ANNULLATO: ' . $arcite_rec['ITEANN'];
                            } else {
                                $arcite_rec['ITEANN'] = "ANNULLATO PER VARIAZIONE DEL PROTOCOLLO.";
                            }
                            $arcite_rec['ITEANNULLATO'] = 1;
                            $arcite_rec['ITEDATAANNULLAMENTO'] = date("Ymd");
                            $arcite_rec['ITEDATAANNULLAMENTOORA'] = date("H:i:s");
                            $arcite_rec['ITESTATO'] = self::ITESTATO_INCARICO;
                            $arcite_rec['ITEFLA'] = '2';
                            itaDB::DBUpdate($this->proLib->getPROTDB(), "ARCITE", "ROWID", $arcite_rec);
                            /*
                             * Controlla se Annullare la TRX di Delega
                             */
                            $this->annullaDelega($arcite_rec);
                        }
                    }
                }
            }
            if ($daInserire) {
                $anades_tab = array();
                foreach ($daInserire as $value) {
                    list($xdescod, $xcoduff) = explode("/", $value);
                    $okInsert = true;
                    foreach ($this->iter as $key => $arcite_rec) {
                        if ($arcite_rec['ITEDES'] == $xdescod && $arcite_rec['ITEUFF'] == $xcoduff && $arcite_rec['ITENODO'] != "MIT") {
                            // da VERIFICARE LA VARIABILE ITEDARIF è SBAGLIATA
                            if ($arcite_rec['ITEDARIF'] != '') {
                                $okInsert = false;
                            }
                        }
                    }
                    if ($okInsert == true) {
                        $anades_tab[] = $this->proLib->GetAnades($this->protocollo['PRONUM'], 'codice', false, $this->protocollo['PROPAR'], "T", " AND DESCOD='$xdescod' AND DESCUF='$xcoduff'");
                    }
                }
                if ($this->protocollo['PRONOTE']) {
                    $notechild = $this->protocollo['PRONOTE'];
                } else if ($this->protocollo['PROPAR'] == "C") {
                    $notechild = "TRASMESSO DA INSERIMENTO COMUNICAZIONE FORMALE";
                } else if ($this->protocollo['PROPAR'] == "I") {
                    $notechild = "TRASMESSO DA INSERIMENTO DOCUMENTALE";
                } else if ($this->protocollo['PROPAR'] == "F") {
                    $notechild = "ASSEGNATA RESPONSABILITA' DEL FASCICOLO";
                } else if ($this->protocollo['PROPAR'] == "N") {
                    $notechild = "ASSEGNATA RESPONSABILITA' DEL SOTTOFASCICOLO";
                } else if ($this->protocollo['PROPAR'] == "W") {
                    $notechild = "TRASMESSO DA PASSO";
                } else {
                    $notechild = "TRASMESSO DA PROTOCOLLO";
                }
                $extraParms = array_merge($sincParm, array(
                    "NODO" => "ASS",
                    "NOTE" => $notechild
                ));
                foreach ($anades_tab as $key => $anades_rec) {
                    $retInsert = $this->insertIterNodeFromAnades($anades_rec, $retPadre, $extraParms);
                    if (!$retInsert) {
                        return false;
                    }
                    /* Se è un Fascicolo o Sottofascicolo, l'assegnazione nasce chiusa */
                    if ($this->protocollo['PROPAR'] == "F" || $this->protocollo['PROPAR'] == "N") {
                        $retInsert = $this->chiudiIterNode($retInsert);
                        if (!$retInsert) {
                            return false;
                        }
                        $retInsert = $this->leggiIterNode($retInsert);
                        if (!$retInsert) {
                            return false;
                        }
                    }
                }
            }

            $anades_M = $this->proLib->GetAnades($this->protocollo['PRONUM'], 'codice', true, $this->protocollo['PROPAR'], "M");
            $idxAnades_mitt = array();
            foreach ($anades_M as $anades_rec) {
                if (!in_array($anades_rec['DESCOD'] . "/" . $anades_rec['DESCUF'], $idxAnades_mitt)) {
                    $idxAnades_mitt[] = $anades_rec['DESCOD'] . "/" . $anades_rec['DESCUF'];
                }
            }
            if ($this->protocollo['PROPAR'] != "A") {
                $promitagg_tab = $this->proLib->getPromitagg($this->protocollo['PRONUM'], 'codice', true, $this->protocollo['PROPAR']);
                foreach ($promitagg_tab as $promitagg_rec) {
                    if (!in_array($promitagg_rec['PRODESCOD'] . "/" . $promitagg_rec['PRODESUFF'], $idxAnades_mitt)) {
                        $idxAnades_mitt[] = $promitagg_rec['PRODESCOD'] . "/" . $promitagg_rec['PRODESUFF'];
                    }
                }
            }
            $fl_ce_mitt = false; //**
            $idxIter_mitt = array();
            foreach ($this->iter as $arcite_rec) {
                if (($arcite_rec['ITEBASE'] == 0 && $arcite_rec['ITENODO'] == 'MIT')) {// || $arcite_rec['ITENODO'] == 'INS') {
                    if (!in_array($arcite_rec['ITEDES'] . "/" . $arcite_rec['ITEUFF'], $idxIter_mitt)) {
                        $idxIter_mitt[] = $arcite_rec['ITEDES'] . "/" . $arcite_rec['ITEUFF'];
                    }
                }
            }
            $daEliminare_mitt = array_diff($idxIter_mitt, $idxAnades_mitt);
            $daInserire_mitt = array_diff($idxAnades_mitt, $idxIter_mitt);

            foreach ($daEliminare_mitt as $value) {
                list($xdescod, $xcoduff) = explode("/", $value);
                foreach ($this->iter as $key => $arcite_rec) {
                    if ($arcite_rec['ITEDES'] == $xdescod && $arcite_rec['ITEUFF'] == $xcoduff && $arcite_rec['ITENODO'] == "MIT") {
                        if ($arcite_rec['ITEPRE']) {
                            $arcite_padre = $this->getIterNode($arcite_rec['ITEPRE']);
                            if ($arcite_padre['ITEBASE'] == 0) {
                                continue;
                            }
                        } else {
                            continue;
                        }
                        if ($arcite_rec['ITEACC'] == '') {
                            $arcite_rec['ITENODO'] = "MAN";
                            $arcite_rec['ITEFIN'] = date("Ymd");
                            $arcite_rec['ITEFINORA'] = date("H:i:s");
                            $arcite_rec['ITEANN'] = "ANNULLATO PER VARIAZIONE MITTENTE DEL PROTOCOLLO.";
                            $arcite_rec['ITEANNULLATO'] = 1;
                            $arcite_rec['ITEDATAANNULLAMENTO'] = date("Ymd");
                            $arcite_rec['ITEDATAANNULLAMENTOORA'] = date("H:i:s");
                            $arcite_rec['ITESTATO'] = self::ITESTATO_INCARICO;
                            $arcite_rec['ITEFLA'] = '2';
                            itaDB::DBUpdate($this->proLib->getPROTDB(), "ARCITE", "ROWID", $arcite_rec);
                            /*
                             * Controllo se annullare anche la delega.
                             */
                            $this->annullaDelega($arcite_rec);
                        }
                    }
                }
            }
            if ($daInserire_mitt) {
                if (!$retPadre) {
                    $utenti_rec = $accLib->GetUtenti($this->protocollo['PROUTE'], 'utelog');
                    $anamed_rec = $this->proLib->GetAnamed($utenti_rec['UTEANA__1']);
                    if (!$anamed_rec) {
                        return false;
                    }
                    $pr = (int) substr($this->protocollo['PRONUM'], 4) . "/" . substr($this->protocollo['PRONUM'], 0, 4);
                    $note = "TRASMISSIONE DA MODIFICA PROTOCOLLO N.$pr";
                    if ($this->protocollo['PROPAR'] == "C") {
                        $note = "TRASMISSIONE DA MODIFICA COMUNICAZIONE FORMALE N.$pr";
                    } else if ($this->protocollo['PROPAR'] == "F") {
                        $note = "MODIFICA FASCICOLO N.{$this->protocollo['PROFASKEY']}";
                    } else if ($this->protocollo['PROPAR'] == "N") {
                        $note = "MODIFICA SOTTOFASCICOLO N.{$this->protocollo['PROFASKEY']} - $pr";
                    } else if ($this->protocollo['PROPAR'] == "T") {
                        $note = "MODIFICA AZIONE N.{$this->protocollo['PROFASKEY']} - $pr";
                    } else if ($this->protocollo['PROPAR'] == "I") {
                        $note = "MODIFICA DOCUMENTALE N. $pr";
                    } else if ($this->protocollo['PROPAR'] == "W") {
                        $note = "MODIFICA PASSO ";
                    }
                    $retPadre = $this->insertIterNodeFromAnamed($anamed_rec, $ultimoBase, array(
                        "UFFICIO" => $this->protocollo['PROUOF'],
                        "ITEBASE" => 1,
                        "NODO" => "INS",
                        "GESTIONE" => 1,
                        "NOTE" => $note));
                    if (!$retPadre) {
                        return false;
                    }
                    $retPadre = $this->chiudiIterNode($retPadre);
                    if (!$retPadre) {
                        return false;
                    }
                    $retPadre = $this->leggiIterNode($retPadre);
                    if (!$retPadre) {
                        return false;
                    }
                }

                $anades_tab = array();
                foreach ($daInserire_mitt as $value) {
                    list($xdescod, $xcoduff) = explode("/", $value);
                    //$okInsert = true;
                    foreach ($this->iter as $key => $arcite_rec) {
//                        if ($arcite_rec['ITEDES'] == $xdescod && $arcite_rec['ITEUFF'] == $xcoduff && $arcite_rec['ITENODO'] == "MIT") {
//                            // da VERIFICARE LA VARIABILE ITEDARIF è SBAGLIATA
//                            if ($arcite_rec['ITEDARIF'] != '') {
//                                $okInsert = false;
//                            }
//                        }
                    }
                    //if ($okInsert == true) {
                    $anades_tab[] = array(
                        "DESCOD" => $xdescod,
                        "DESCUF" => $xcoduff
                    );
                    //}
                }
                $noteMitt = "ASSEGNATA VISIBILITA A MITTENTE/FIRMATARIO";
                if ($this->protocollo['PROPAR'] == "C") {
                    $noteMitt = "ASSEGNATA VISIBILITA A MITTENTE/FIRMATARIO COMUNICAZIONE";
                }
                foreach ($anades_tab as $key => $anades_rec) {
                    $destinatario = array();
                    $destinatario['DESCUF'] = $anades_rec['DESCUF'];
                    $destinatario['DESCOD'] = $anades_rec['DESCOD'];
                    $destinatario['DESGES'] = "0";
                    $destinatario['DESTERMINE'] = "";
                    $extraParm = array_merge($sincParm, array(
                        "NODO" => "MIT",
                        "NOTE" => $noteMitt
                    ));
                    $extraParm['CREADELEGACHIUSA'] = true;
                    $retInsert = $this->insertIterNode($destinatario, $retPadre, $extraParm);
                    if (!$retInsert) {
                        return false;
                    }
                    $retInsert = $this->chiudiIterNode($retInsert);
                    if (!$retInsert) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function insertVisibilitaMittenti($retPadre, $sincParm = array()) {
        if ($this->protocollo['PROPAR'] == "P" || $this->protocollo['PROPAR'] == "C" || $this->protocollo['PROPAR'] == "I" || $this->protocollo['PROPAR'] == "W") {
            switch ($this->protocollo['PROPAR']) {
                case "C":
                    $noteMitt = "ASSEGNATA VISIBILITA A MITTENTE/FIRMATARIO COMUNICAZIONE";
                    break;
                case "I":
                    $noteMitt = "ASSEGNATA VISIBILITA A MITTENTE/FIRMATARIO DOCUMENTO";
                    break;
                default:
                    $noteMitt = "ASSEGNATA VISIBILITA A MITTENTE/FIRMATARIO PROTOCOLLO";
                    break;
            }
            $anades_tab = $this->proLib->GetAnades($this->protocollo['PRONUM'], 'codice', true, $this->protocollo['PROPAR'], "M");
            foreach ($anades_tab as $anades_rec) {
                if ($anades_rec['DESCOD'] == '' || $anades_rec['DESCUF'] == '') {
                    continue;
                }
                if ($retPadre['ITEDES'] != $anades_rec['DESCOD']) {
                    $extraParms = array_merge($sincParm, array(
                        "NODO" => "MIT",
                        "NOTE" => $noteMitt
                    ));
                    $extraParms['CREADELEGACHIUSA'] = true;
                    $retInsert = $this->insertIterNodeFromAnades($anades_rec, $retPadre, $extraParms);
                    if (!$retInsert) {
                        return false;
                    }
                    $retInsert = $this->chiudiIterNode($retInsert);
                    if (!$retInsert) {
                        return false;
                    }
                }
            }

            $promitagg_tab = $this->proLib->getPromitagg($this->protocollo['PRONUM'], 'codice', true, $this->protocollo['PROPAR']);
            if ($promitagg_tab) {
                foreach ($promitagg_tab as $promitagg_rec) {
                    $destinatario = array();
                    $destinatario['DESCUF'] = $promitagg_rec['PRODESUFF'];
                    $destinatario['DESCOD'] = $promitagg_rec['PRODESCOD'];
                    $destinatario['DESGES'] = "0";
                    $destinatario['DESTERMINE'] = "";
                    $extraParm = array_merge($sincParm, array(
                        "NODO" => "MIT",
                        "NOTE" => $noteMitt
                    ));
                    $extraParm['CREADELEGACHIUSA'] = true;
                    $retInsert = $this->insertIterNode($destinatario, $retPadre, $extraParm);
                    if (!$retInsert) {
                        return false;
                    }
                    $retInsert = $this->chiudiIterNode($retInsert);
                    if (!$retInsert) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Prepara iter per andare alla firma.
     * @param type $flModo aggiungi|cancella
     * @param type $rowidarcite utile solo se $flModo='cancella'
     * @return boolean
     */
    public function sincronizzaIterFirma($flModo = "aggiungi", $rowidarcite = 0, $sincParm = array()) {
        if ($flModo == 'aggiungi') {
            if ($this->protocollo['PROPAR'] != 'P' && $this->protocollo['PROPAR'] != 'C' && $this->protocollo['PROPAR'] != 'I' && $this->protocollo['PROPAR'] != 'W') {
                return true;
            }
            $sql = "
            SELECT
                DOCFIRMA.FIRCOD,
                DOCFIRMA.FIRUFF,
                DOCFIRMA.FIRDATARICH
            FROM ANADOC ANADOC
            LEFT OUTER JOIN DOCFIRMA DOCFIRMA ON DOCFIRMA.ROWIDANADOC=ANADOC.ROWID AND DOCFIRMA.ROWID IS NOT NULL
            WHERE
                ANADOC.DOCNUM={$this->protocollo['PRONUM']} AND 
                ANADOC.DOCPAR='{$this->protocollo['PROPAR']}' AND 
                DOCFIRMA.FIRDATA='' AND
                DOCFIRMA.FIRANN='' AND
                DOCFIRMA.ROWIDARCITE=0
            GROUP BY DOCFIRMA.FIRCOD, DOCFIRMA.FIRUFF, FIRDATARICH";
            $check_tab = $this->proLib->getGenericTab($sql, true);

            foreach ($check_tab as $check_rec) {
                $sql1 = "
            SELECT
                DOCFIRMA.FIRCOD,
                DOCFIRMA.FIRUFF,
                DOCFIRMA.ROWIDARCITE
            FROM ANADOC ANADOC
            LEFT OUTER JOIN DOCFIRMA DOCFIRMA ON DOCFIRMA.ROWIDANADOC=ANADOC.ROWID AND DOCFIRMA.ROWID IS NOT NULL
            LEFT OUTER JOIN ARCITE ARCITE ON ARCITE.ROWID=DOCFIRMA.ROWIDARCITE
            WHERE
                ANADOC.DOCNUM={$this->protocollo['PRONUM']} AND 
                ANADOC.DOCPAR='{$this->protocollo['PROPAR']}' AND 
                DOCFIRMA.FIRDATA='' AND
                DOCFIRMA.FIRANN='' AND
                DOCFIRMA.ROWIDARCITE<>0 AND 
                ARCITE.ITEDAT='{$check_rec['FIRDATARICH']}' AND 
                ARCITE.ITEDES='{$check_rec['FIRCOD']}' AND 
                ARCITE.ITEUFF='{$check_rec['FIRUFF']}' AND 
                ARCITE.ITEFIN=''";
                $docfirma_rec = $this->proLib->getGenericTab($sql1, false);
                if ($docfirma_rec) {
                    $rowidarcite = $docfirma_rec['ROWIDARCITE'];
                } else {
                    $ultimoBase = $this->getUltimoBase($this->iterTree);
                    $extraParm = array_merge($sincParm, array(
                        "NOTE" => "RICHIESTA DI FIRMA",
                        "ITETIP" => proIter::ITETIP_ALLAFIRMA,
                        "ITEBASE" => 0
                    ));
                    $destinatario = array(
                        "DESCUF" => $check_rec['FIRUFF'],
                        "DESCOD" => $check_rec['FIRCOD'],
                        "DESGES" => 1,
                        "DESTERMINE" => ""
                    );
                    $arcite_rec = $this->insertIterNode($destinatario, $ultimoBase, $extraParm);
                    $rowidarcite = $arcite_rec['ROWID'];
                }

                $sql2 = "
                    SELECT 
                        DOCFIRMA.*
                    FROM ANADOC ANADOC
                    LEFT OUTER JOIN DOCFIRMA DOCFIRMA ON DOCFIRMA.ROWIDANADOC=ANADOC.ROWID AND DOCFIRMA.ROWID IS NOT NULL
                    WHERE
                        ANADOC.DOCNUM={$this->protocollo['PRONUM']} AND 
                        ANADOC.DOCPAR='{$this->protocollo['PROPAR']}' AND 
                        DOCFIRMA.FIRCOD='{$check_rec['FIRCOD']}' AND 
                        DOCFIRMA.FIRUFF='{$check_rec['FIRUFF']}' AND 
                        DOCFIRMA.FIRDATA='' AND
                        DOCFIRMA.FIRANN='' AND
                        DOCFIRMA.ROWIDARCITE=0
                    ";
                $docfirma_tab = $this->proLib->getGenericTab($sql2, true);
                if ($docfirma_tab) {
                    foreach ($docfirma_tab as $docfirma_rec) {
                        $docfirma_rec['ROWIDARCITE'] = $rowidarcite;
                        $update_Info = 'Aggiungo iter a richiesta firma allegato : ';
                        if (!$this->updateRecord($this->proLib->getPROTDB(), 'DOCFIRMA', $docfirma_rec, $update_Info)) {
                            $this->lastMessage = 'Errore in aggiornamento DOCFIRMA.';
                            return false;
                        }
                    }
                }
            }
            return true;
        } else if ($flModo == 'cancella') {
            $sql3 = "SELECT ROWID FROM DOCFIRMA WHERE ROWIDARCITE=$rowidarcite";
            $docfirma_tab = $this->proLib->getGenericTab($sql3, true);
            if (!$docfirma_tab) {
                $arcite_rec = $this->proLib->GetArcite($rowidarcite, $tipo = 'rowid');
                if (!$arcite_rec['ITEDATRIF'] && !$arcite_rec['ITESUS']) {
                    $this->annullaIterNode($arcite_rec);
                }
            }
            return true;
        }
    }

    public function checkEsistenzaUtente($descod) {
        $anaent_36 = $this->proLib->GetAnaent('36');
        if ($anaent_36['ENTDE2'] == '') {
            return proIter::ITECHECK_CREA;
        }
        include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
        $accLib = new accLib();
        $sql = "SELECT * FROM UTENTI WHERE UTEANA__1='$descod'";
        $utenti_rec = ItaDB::DBSQLSelect($accLib->getITW(), $sql, false);
        if ($utenti_rec) {
            return proIter::ITECHECK_CREA;
        } else {
            if ($anaent_36['ENTDE2'] == '1') {
                return proIter::ITECHECK_NONCREARE;
            } else if ($anaent_36['ENTDE2'] == '2') {
                return proIter::ITECHECK_CREACHIUSO;
            }
        }
        return proIter::ITECHECK_NONCREARE;
    }

    public function TrasmissioneASostituto($destinatario, $padre, $extraParm, $arcite_rec) {
        if ($extraParm['SOSTITUTO'] == true) {
            return true; // Blocco al primo livello, possibile rimuoverlo..
        }
        // ESCLUDERE INS?
        if ($arcite_rec['ITENODO'] == 'INS') {
            return true;
        }
        // Escludo Fascicoli e Sottofascicoli
        if ($arcite_rec['ITEPAR'] == 'F' || $arcite_rec['ITEPAR'] == 'N') {
            return true;
        }
        // Esclusione provvisoria delle I, potrebbe essere attivata da un parametro
        if ($arcite_rec['ITEPAR'] == 'I') {
            return true;
        }

        $Sostituto_tab = $this->checkSostituto($destinatario, $arcite_rec, true);
        $extraNote = $extraParm['NOTE'];
        $Anamed_rec = $this->proLib->getAnamed($destinatario['DESCOD'], 'codice');
        foreach ($Sostituto_tab as $Sostituto_rec) {
            $extraParm['SOSTITUTO'] = true;
            $extraParm['NOTE'] = 'Trasmissione per DELEGA di ' . $Anamed_rec['MEDNOM'] . ' - ' . $extraNote;
            $extraParm['NODO'] = 'TRX';
            $extraParm['ITETIP'] = self::ITETIP_PARERE_DELEGA;
            // Sostituzione:
            $destinatario['DESCOD'] = $Sostituto_rec['DELEDSTCOD'];
            $destinatario['DESCUF'] = $Sostituto_rec['DELEDSTUFF'];
            $this->insertIterNode($destinatario, $arcite_rec, $extraParm);
            //Controllo Chiusura Trasmissione Sostituto:
            if ($extraParm['CREADELEGACHIUSA']) {
                $RowidDelega = $this->getLastInsertId();
                $DelegaArcite_rec = $this->proLib->getArcite($RowidDelega, 'rowid');
                $this->chiudiIterNode($DelegaArcite_rec);
            }
        }
        return true;
    }

    public function InsertSostituto($Sostituto_rec, $arcite_rec, $extraParm) {
        $Anamed_rec = $this->proLib->getAnamed($Sostituto_rec['DELEDSTCOD'], 'codice');
        $extraNote = $extraParm['NOTE'];
        $extraParm['SOSTITUTO'] = true;
        $DescTrasm = "Trasmissione per DELEGA di";
        if ($extraParm['DESCTRASM']) {
            $DescTrasm = $extraParm['DESCTRASM'];
        }
        $extraParm['NOTE'] = $DescTrasm . ' ' . $Anamed_rec['MEDNOM'] . ' - ' . $extraNote;
        $extraParm['NODO'] = 'TRX';
        $extraParm['ITETIP'] = self::ITETIP_PARERE_DELEGA;
        // Sostituzione:
        $destinatario['DESGES'] = $arcite_rec['ITEGES'];
        $destinatario['DESCOD'] = $Sostituto_rec['DELEDSTCOD'];
        $destinatario['DESCUF'] = $Sostituto_rec['DELEDSTUFF'];
        $this->insertIterNode($destinatario, $arcite_rec, $extraParm);
        $RowidArcite = $this->getLastInsertId();
        return $RowidArcite;
    }

    public function checkSostituto($destinatario, $arcite_rec, $multi = false) {
        /*
         * Ipotesi:
         * Unico sostituto per tutti gli uffici, potrebbe avere * nel codice ufficio.
         * Prevedere altri controlli per delege..?
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibDeleghe.class.php';
        $proLibDeleghe = new proLibDeleghe();
        return $proLibDeleghe->CheckSostitutoDelega($destinatario['DESCOD'], $destinatario['DESCUF'], $arcite_rec['ITEDAT'], proLibDeleghe::DELEFUNZIONE_PROTOCOLLO, $multi);
    }

    /*
     *  Annullamento anche la ritrasmissione
     */

    public function annullaDelega($arcite_rec) {
        $destinatario = array();
        $destinatario['DESCOD'] = $arcite_rec['ITEDES'];
        $destinatario['DESCUF'] = $arcite_rec['ITEUFF'];
        $Sostituto_rec = $this->checkSostituto($destinatario, $arcite_rec);
        if ($Sostituto_rec) {
//            $sql = "SELECT * FROM ARCITE WHERE ITEPRO = " . $arcite_rec['ITEPRO'] . " AND ITEPAR = '" . $arcite_rec['ITEPAR'] . "' ";
//            $sql.=" AND ITETIP = '" . self::ITETIP_PARERE_DELEGA . "' AND ITEDES = '" . $Sostituto_rec['DELEDSTCOD'] . "' AND ITEUFF = '" . $Sostituto_rec['DELEDSTUFF'] . "'";
//            $sql.=" AND ITEPRE = '" . $arcite_rec['ITEPRE'] . "'"; // Stesso padre.
            $sql = "SELECT * FROM ARCITE WHERE ITEANT =  '" . $arcite_rec['ITEDES'] . "' AND ITEPRE = '" . $arcite_rec['ITEKEY'] . "' ";
            $sql.=" AND ITEPRO = " . $arcite_rec['ITEPRO'] . " AND ITEPAR = '" . $arcite_rec['ITEPAR'] . "'";
            $sql.=" AND ITETIP = '" . self::ITETIP_PARERE_DELEGA . "'";
            $ArciteTrxDel_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
            foreach ($ArciteTrxDel_tab as $ArciteTrxDel_rec) {
                /*
                 *  Delega da cancellare:
                 */
                $ArciteTrxDel_rec['ITENODO'] = $arcite_rec['ITENODO'];
                $ArciteTrxDel_rec['ITEFIN'] = $arcite_rec['ITEFIN'];
                $ArciteTrxDel_rec['ITEFINORA'] = $arcite_rec['ITEFINORA'];
                $ArciteTrxDel_rec['ITEANN'] = $arcite_rec['ITEANN'] . ' -' . $ArciteTrxDel_rec['ITEANN']; //Accodo annotazione prec.
                $ArciteTrxDel_rec['ITEANNULLATO'] = $arcite_rec['ITEANNULLATO'];
                $ArciteTrxDel_rec['ITEDATAANNULLAMENTO'] = $arcite_rec['ITEDATAANNULLAMENTO'];
                $ArciteTrxDel_rec['ITEDATAANNULLAMENTOORA'] = $arcite_rec['ITEDATAANNULLAMENTOORA'];
                $ArciteTrxDel_rec['ITESTATO'] = $arcite_rec['ITESTATO'];
                $ArciteTrxDel_rec['ITEFLA'] = $arcite_rec['ITEFLA'];
                itaDB::DBUpdate($this->proLib->getPROTDB(), "ARCITE", "ROWID", $ArciteTrxDel_rec);
            }
        }
    }

    public function acquisisciFirmaIterNode($iterNode, $motivo, $soggettoObj) {
        if (!$motivo) {
            return false;
        }
        if (is_array($iterNode)) {
            $arcite_rec = $iterNode;
        } else {
            if ($iterNode) {
                $arcite_rec = $this->getIterNode($iterNode);
            }
        }


        $arcite_padre_delega = $this->proLib->GetArcite($arcite_rec['ITEPRE'], 'itekey');

        /*
         * 1. Cambio il Firmatario del Protocollo
         */
        $AnadesFirmatario_rec = $this->proLib->GetAnades($arcite_rec['ITEPRO'], 'codice', false, $arcite_rec['ITEPAR'], 'M');
        $soggetto_dati = $soggettoObj->getSoggetto();

        $AnadesFirmatario_rec['DESCOD'] = $soggetto_dati['CODICESOGGETTO'];
        $AnadesFirmatario_rec['DESCUF'] = $soggetto_dati['CODICEUFFICIO'];
        $AnadesFirmatario_rec['DESNOM'] = $soggetto_dati['DESCRIZIONESOGGETTO'];
        $update_Info = 'Acquisizione iter di Firma : ' . $AnadesFirmatario_rec['DESNUM'] . $AnadesFirmatario_rec['DESPAR'];
        if (!$this->updateRecord($this->proLib->getPROTDB(), 'ANADES', $AnadesFirmatario_rec, $update_Info)) {
            return false;
        }

        /*
         * 2. Inserisco iter di acquisizione di tipo Firma per il destinatario
         */
        if ($arcite_rec) {
            $arcite_rec['ITEDATACQ'] = date("Ymd");
            $arcite_rec['ITEDATACQORA'] = date("H:i:s");
            $arcite_rec['ITEMOTIVO'] = $motivo;
            $update_Info = 'Acquisizione iter di Firma: ' . $arcite_rec['ITEKEY'];
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'ARCITE', $arcite_rec, $update_Info)) {
                return false;
            }
        }

        $anamed_rec = $this->proLib->GetAnamed($soggetto_dati['CODICESOGGETTO']);
        $Gestione = 1;
        if (!$arcite_rec['ITEGES']) {
            $Gestione = 0;
        }

        $arcite_acquisito = $this->insertIterNodeFromAnamed($anamed_rec, $arcite_rec, array(
            "NODO" => "TRX",
            "UFFICIO" => $soggetto_dati['CODICEUFFICIO'],
            "NOTE" => "ACQUISIZIONE FIRMA REMOTA DA SCRIVANIA PER DELEGA",
            "GESTIONE" => $Gestione,
            "LIVELLO" => 0,
            "ITETIP" => proIter::ITETIP_ALLAFIRMA
        ));

        /*
         * 3 Ricerco su DOCFIRMA ROWID del Padre.
         *  Cambio su Doc Firma gli estremi di Arcite
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
        $proLibAllegati = new proLibAllegati();

        $Doc_Firma_tab = $proLibAllegati->GetDocfirma($arcite_padre_delega['ROWID'], 'rowidarcite', true);
        foreach ($Doc_Firma_tab as $Doc_Firma_rec) {
            $Doc_Firma_rec['ROWIDARCITE'] = $arcite_acquisito['ROWID'];
            $Doc_Firma_rec['FIRCOD'] = $soggetto_dati['CODICESOGGETTO'];
            $Doc_Firma_rec['FIRUFF'] = $soggetto_dati['CODICEUFFICIO'];
            $update_Info = 'Acquisizione iter di Firma: ' . $arcite_acquisito['ITEKEY'];
            if (!$this->updateRecord($this->proLib->getPROTDB(), 'DOCFIRMA', $Doc_Firma_rec, $update_Info)) {
                return false;
            }
        }

        return $arcite_acquisito;
    }

}

?>
