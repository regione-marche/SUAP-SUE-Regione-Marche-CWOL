<?php

/**
 *
 * MODEL PER ASSEGNAZIONE PRATICA
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2015 Italsoft srl
 * @license
 * @version    14.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

function praAssegnaPraticaSimple() {
    $praAssegnaPraticaSimple = new praAssegnaPraticaSimple();
    $praAssegnaPraticaSimple->parseEvent();
    return;
}

class praAssegnaPraticaSimple extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $rowidAppoggio;
    public $rowidDipendente;
    public $rowidTipoPasso;
    public $pratica;
    public $returnModel;
    public $returnEvent;
    public $daPortlet;
    public $nameForm = "praAssegnaPraticaSimple";

    function __construct() {
        parent::__construct();
        // Apro il DB
        $this->praLib = new praLib();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->rowidDipendente = App::$utente->getKey($this->nameForm . '_rowidDipendente');
        $this->rowidTipoPasso = App::$utente->getKey($this->nameForm . '_rowidTipoPasso');
        $this->pratica = App::$utente->getKey($this->nameForm . '_pratica');
        $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
        $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
        $this->daPortlet = App::$utente->getKey($this->nameForm . '_daPortlet');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_rowidDipendente', $this->rowidDipendente);
            App::$utente->setKey($this->nameForm . '_rowidTipoPasso', $this->rowidTipoPasso);
            App::$utente->setKey($this->nameForm . '_pratica', $this->pratica);
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_daPortlet', $this->daPortlet);
        }
    }

    public function getPratica() {
        return $this->pratica;
    }

    public function setPratica($pratica) {
        $this->pratica = $pratica;
    }

    public function getDaPortlet() {
        return $this->daPortlet;
    }

    public function setDaPortlet($daPortlet) {
        $this->daPortlet = $daPortlet;
    }

    public function getRowidAppoggio() {
        return $this->rowidAppoggio;
    }

    public function setRowidAppoggio($rowidAppoggio) {
        $this->rowidAppoggio = $rowidAppoggio;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                //Out::hide($this->nameForm);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_AnnullaAnnullaInCarico':
                        $this->close();
                        break;

                    case $this->nameForm . '_ConfermaAnnullaInCarico':
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        praFunzionePassi::annullaInCaricoAssegnazione($this, $this->pratica, $this->rowidAppoggio, $profilo);
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_AnnullaPresaInCarico':
                        $this->close();
                        break;
                    case $this->nameForm . '_ConfermaPresaInCarico':
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        praFunzionePassi::prendiInCaricoAssegnazione($this, $this->pratica, '');
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_RestituisciAnnulla':
                        $this->close();
                        break;
                    case $this->nameForm . '_RestituisciMotivo':
                        if ($_POST[$this->nameForm . '_motivazioneRestituzione'] == '') {
                            $this->restituisciPratica();
                            break;
                        }
                        $profilo = proSoggetto::getProfileFromIdUtente();
                        praFunzionePassi::restituisciAssegnazione($this, $this->pratica, $profilo, $_POST[$this->nameForm . '_motivazioneRestituzione']);
                        $this->returnToParent();
                        break;

                    case $this->nameForm . "_AnnullaAss":
                        $this->close();
                        break;
                    case $this->nameForm . "_ConfermaAss":
                        $this->ConfermaAssegnazione($this->pratica, $this->rowidDipendente, $this->rowidTipoPasso, $_POST[$this->nameForm . "_noteAss"], $this->daPortlet, "rowid");
                        /*
                          $proges_rec = $this->praLib->GetProges($this->pratica);
                          $praclt_rec = $this->praLib->GetPraclt($this->rowidTipoPasso, "rowid");
                          $ananom_rec = $this->praLib->GetAnanom($this->rowidDipendente, "rowid");
                          $pratica = substr($this->pratica, 4) . "/" . substr($this->pratica, 0, 4);
                          //
                          $seq = 9999;
                          //
                          //Inserisco Nuovo Passo Gestione/Assegnazione
                          //
                          $propas_new_rec = array();
                          $propas_new_rec['PRONUM'] = $this->pratica;
                          $propas_new_rec['PROPRO'] = $proges_rec['GESPRO'];
                          $propas_new_rec['PRORES'] = $ananom_rec['NOMRES'];
                          $propas_new_rec['PROSEQ'] = $seq;
                          $propas_new_rec['PRORPA'] = $ananom_rec['NOMRES'];
                          $propas_new_rec['PROUOP'] = $ananom_rec['NOMOPE'];
                          $propas_new_rec['PROSET'] = $ananom_rec['NOMSET'];
                          $propas_new_rec['PROSER'] = $ananom_rec['NOMSER'];
                          $propas_new_rec['PRODPA'] = strtoupper($praclt_rec['CLTDES'] . " N. ") . $pratica;
                          $propas_new_rec['PROCLT'] = $praclt_rec['CLTCOD'];
                          $propas_new_rec['PRODTP'] = $praclt_rec['CLTDES'];
                          $propas_new_rec['PROPAK'] = $this->praLib->PropakGenerator($this->pratica);
                          $propas_new_rec['PROOPE'] = $praclt_rec['CLTOPE'];
                          $propas_new_rec['PROANN'] = $_POST[$this->nameForm . "_noteAss"];
                          $propas_new_rec['PROUTEADD'] = $propas_new_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
                          $propas_new_rec['PRODATEADD'] = $propas_new_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
                          $insert_Info = "Oggetto: Inserisco passo " . $praclt_rec['CLTCOD'] . "-" . $praclt_rec['CLTDES'] . "  alla pratica " . $this->pratica;
                          if (!$this->insertRecord($this->PRAM_DB, 'PROPAS', $propas_new_rec, $insert_Info)) {
                          Out::msgStop("Inserimento passo", "Inserimento data set PROPAS fallito");
                          $this->close();
                          break;
                          }

                          //
                          //Ordino la sequnza dei passi dopo il nuvo inserito
                          //
                          if (!$this->praLib->ordinaPassi($this->pratica)) {
                          Out::msgStop("Errore", "Errore nel riordinare i passi della pratica n. $this->pratica");
                          $this->close();
                          break;
                          }

                          if ($this->daPortlet == true) {
                          $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                          //
                          //Chiudo il passo precedente
                          //
                          $propas_rec['PROINI'] = date("d/m/Y");
                          $propas_rec['PROFIN'] = date("d/m/Y");
                          $insert_Info = "Oggetto: Chiudo passo assegnazione della pratica " . $this->pratica;
                          if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $insert_Info)) {
                          Out::msgStop("Chiusura passo", "Aggiornamento data set PROPAS fallito");
                          $this->close();
                          break;
                          }
                          }

                          //
                          //Se Mittente e destinatario dell'assegnazione sono diversi dal responsabile assegnazione, invio notifica al responsabile
                          //
                          if ($ananom_rec['NOMRESPASS'] == 0) {
                          $profilo = proSoggetto::getProfileFromIdUtente();
                          $ananom_rec_mitt = $this->praLib->GetAnanom($profilo['COD_ANANOM']);
                          if ($ananom_rec_mitt['NOMRESPASS'] == 0) {
                          $testo = $praclt_rec['CLTDES'] . " N. " . $pratica . " dall'utente " . $ananom_rec_mitt['NOMCOG'] . " " . $ananom_rec_mitt['NOMNOM'] . " all'utente " . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'];
                          $codRespAss = proSoggetto::getCodiceResponsabileAssegnazione();
                          if (!$this->praLib->inviaNotificaResponsabileAssegnazione("praGest", $codRespAss, $proges_rec['ROWID'], $propas_new_rec['PRODPA'], $testo)) {
                          Out::msgStop("Invio Notifica", "Impossibile Inviare una Notifica al Resposansabile delle Assegnazioni");
                          $this->close();
                          break;
                          }
                          }
                          }
                         */
                        $this->returnToParent();
                }
                break;
            case 'returnUnires':
                switch ($_POST['retid']) {
                    case $this->nameForm . "_AssegnaPratica":
                        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                        $ananom_rec = $this->praLib->GetAnanom($_POST['retKey'], "rowid");
                        if (!$this->CtrAssegnazione($ananom_rec["NOMRES"])) {
                            Out::msgStop("Attenzione!", "Al dipendente <b>" . $ananom_rec["NOMCOG"] . " " . $ananom_rec["NOMNOM"] . "</b> è stata già assegnata la seguente pratica.");
                            $this->close();
                            break;
                        }
                        $pratica = substr($this->pratica, 4) . "/" . substr($this->pratica, 0, 4);
                        $this->rowidDipendente = $ananom_rec['ROWID'];
                        if ($propas_rec['PROOPE'] == praFunzionePassi::FUN_GEST_ASS) {
                            $where = " WHERE CLTOPE = '" . praFunzionePassi::FUN_GEST_GEN . "'";
                        } else {
                            $where = " WHERE CLTOPE = '" . praFunzionePassi::FUN_GEST_ASS . "' OR CLTOPE = '" . praFunzionePassi::FUN_GEST_GEN . "'";
                        }
                        $msgDetail = "Scegliere tra i tipi passo disponibili, quello da abbinare all'utente<br><b>" . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'] . "</b> per la pratica <b>$pratica</b>";
                        praRic::praRicPraclt($this->nameForm, "", "AssegnaTipoPasso", $where, $msgDetail, true);
                        break;
                    default :
                        $this->close();
                        break;
                }
                break;
            case "returnPraclt":
                //$propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
                if (!$_POST['retKey']) {
                    $this->close();
                    break;
                }
                $praclt_rec = $this->praLib->GetPraclt($_POST['retKey'], "rowid");
                $this->rowidTipoPasso = $praclt_rec['ROWID'];
                $ananom_rec = $this->praLib->GetAnanom($this->rowidDipendente, "rowid");
                $pratica = substr($this->pratica, 4) . "/" . substr($this->pratica, 0, 4);

                $valori[] = array(
                    'label' => array(
                        'value' => "Note",
                    //'style' => 'width:350px;'
                    ),
                    'id' => $this->nameForm . '_noteAss',
                    'name' => $this->nameForm . '_noteAss',
                    'type' => 'text',
                    'maxlenght' => '80',
                    'size' => '60',
                    'value' => ''
                );
                $header = "Confermi <b>" . $praclt_rec['CLTDES'] . "</b> n. $pratica all'utente <b>" . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'] . "</b>?";
                Out::msgInput(
                        'Assegnazione Pratica.', $valori
                        , array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAss', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAss', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ), $this->nameForm, 'auto', 'auto', 'false', $header
                );

//                Out::msgQuestion("Assegnazione Pratica", "Confermi <b>" . $praclt_rec['CLTDES'] . "</b> n. $pratica all'utente <b>" . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'] . "</b>?", array(
//                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaAss', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAss', 'model' => $this->nameForm, 'shortCut' => "f5")
//                        ), 'auto', 'auto', 'true', false, true, false, "ItaCall"
//                );
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_rowidDipendente');
        App::$utente->removeKey($this->nameForm . '_rowidTipoPasso');
        App::$utente->removeKey($this->nameForm . '_pratica');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_daPortlet');
        Out::closeDialog($this->nameForm);
    }

    public function CtrAssegnazione($nomres) {
        $sql = "SELECT
                    PROPAS.PRONUM,
                    PROPAS.PROPAK,
                    PROPAS.PRORPA,
                    PROPAS.PROINI,
                    PROPAS.PROFIN
                FROM
                    PROPAS
                WHERE 
                    PROPAS.PRONUM = '$this->pratica' AND PROPAS.PROOPE<>''
                ";
//        $sql = "SELECT
//                    PROPAS.PRONUM,
//                    PROPAS.PROPAK,
//                    PROPAS.PRORPA
//                FROM
//                    PROPAS PROPAS
//                LEFT OUTER JOIN
//                    PRACLT PRACLT
//                ON
//                    PROPAS.PROCLT=PRACLT.CLTCOD
//                WHERE 
//                    PROPAS.PRONUM = '$this->pratica' AND PRACLT.CLTOPE<>''
//                ";
        $propas_tab_conAss = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($propas_tab_conAss) {
            foreach ($propas_tab_conAss as $propas_rec_conAss) {
                if ($propas_rec_conAss['PRORPA'] == $nomres && ($propas_rec_conAss['PROINI'] == '' || $propas_rec_conAss['PROFIN'] == '')) {
                    return false;
                }
            }
        }
        return true;
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['gesnum'] = $this->pratica;
        $modelObj = itaModel::getInstance($this->returnModel);
        $modelObj->setEvent($this->returnEvent);
        $modelObj->parseEvent();
        if ($close) {
            $this->close();
        }
    }

    public function assegnaPratica() {
        $pratica = substr($this->pratica, 4) . "/" . substr($this->pratica, 0, 4);
        $msgDetail = "La Pratica n. <b>$pratica</b> sarà assegnata al soggetto scelto con un passo di gestione,<br>oppure di riassegnazione da prendere in carico.";
        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Soggetto a cui assegnare la pratica: $pratica", " WHERE NOMABILITAASS = 1 ", $this->nameForm . "_AssegnaPratica", false, null, $msgDetail, true);
    }

    public function restituisciPratica() {

        $pratica = substr($this->pratica, 4) . "/" . substr($this->pratica, 0, 4);

        $profilo = proSoggetto::getProfileFromIdUtente();
        $curr_assegnazione = praFunzionePassi::getCurrAssegnazione($this->pratica, $profilo);

        /*
         * Leggo dati da assegnazione corrente
         */
        $proges_rec = $this->praLib->GetProges($this->pratica);
        $propas_rec = $this->praLib->GetPropas($curr_assegnazione['ROWID'], 'rowid');
        if (!$propas_rec) {
            Out::msgStop("Errore in restituzione", "Lettura passo da restituire fallita.");
            return false;
        }
        $nomeUtente = $propas_rec['PROUTEADD'];
        $mittente = proSoggetto::getProfileFromNomeUtente($nomeUtente);
        $ananom_rec = $this->praLib->GetAnanom($mittente['COD_ANANOM'], "codice");
        $header = "Motiva la restituzione della pratica n. $pratica all'utente <b>" . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'] . "</b> e conferma.";
        $valori[] = array(
            'label' => array(
                'value' => "Motivo",
                'style' => 'width:350px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_motivazioneRestituzione',
            'name' => $this->nameForm . '_motivazioneRestituzione',
            'type' => 'text',
            'style' => 'margin:2px;width:350px;',
            'value' => ''
        );
        Out::msgInput(
                'Motivo della Restituzione.', $valori
                , array(
            'Restituisci' => array('id' => $this->nameForm . '_RestituisciMotivo', 'model' => $this->nameForm),
            'Annulla' => array('id' => $this->nameForm . '_RestituisciAnnulla', 'model' => $this->nameForm)
                ), $this->nameForm, 'auto', 'auto', 'false', $header
        );
        Out::setFocus('', $this->nameForm . '_motivazioneRestituzione');


//        $pratica = substr($this->pratica, 4) . "/" . substr($this->pratica, 0, 4);
//        $msgDetail = "La Pratica n. <b>$pratica</b> sarà assegnata al soggetto scelto con un passo di gestione,<br>oppure di riassegnazione da prendere in carico.";
//        praRic::praRicAnanom($this->PRAM_DB, $this->nameForm, "Soggetto a cui assegnare la pratica: $pratica", "", $this->nameForm . "_AssegnaPratica", false, null, $msgDetail);
    }

    public function prendiInCarico() {
        $pratica = substr($this->pratica, 4) . "/" . substr($this->pratica, 0, 4);

        $profilo = proSoggetto::getProfileFromIdUtente();
        $curr_assegnazione = praFunzionePassi::getCurrAssegnazione($this->pratica, $profilo);

        /*
         * Leggo dati da assegnazione corrente
         */
        $proges_rec = $this->praLib->GetProges($this->pratica);
        $propas_rec = $this->praLib->GetPropas($curr_assegnazione['ROWID'], 'rowid');
        if (!$propas_rec) {
            Out::msgStop("Errore in presa in carico", "Lettura passo da accettare fallita.");
            return false;
        }
        $nomeUtente = $propas_rec['PROUTEADD'];
        $mittente = proSoggetto::getProfileFromNomeUtente($nomeUtente);
        $ananom_rec = $this->praLib->GetAnanom($mittente['COD_ANANOM'], "codice");
        $header = "Accetti e prendi in carico la pratica n. $pratica <br> inviata dall'utente <b>" . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'] . "?</b>";

        Out::msgQuestion("ATTENZIONE!", $header, array(
            'Annulla' => array('id' => $this->nameForm . '_AnnullaPresaInCarico', 'model' => $this->nameForm),
            'Conferma' => array('id' => $this->nameForm . '_ConfermaPresaInCarico', 'model' => $this->nameForm)
                ), 'auto', 'auto', 'false'
        );
    }

    public function annullaInCarico() {
        $pratica = substr($this->pratica, 4) . "/" . substr($this->pratica, 0, 4);

        $profilo = proSoggetto::getProfileFromIdUtente();
        /*
         * Leggo dati da assegnazione corrente
         */
        $proges_rec = $this->praLib->GetProges($this->pratica);
        $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, 'rowid');
        if (!$propas_rec) {
            Out::msgStop("Errore in annullamento", "Lettura presa in carico da annullare fallita.");
            return false;
        }
        $nomeUtente = $propas_rec['PROUTEADD'];
        $mittente = proSoggetto::getProfileFromNomeUtente($nomeUtente);
        $ananom_rec = $this->praLib->GetAnanom($mittente['COD_ANANOM'], "codice");
        $header = "Annulli la presa in carico della pratica n. $pratica <br> inviata dall'utente <b>" . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'] . "?</b>";
        Out::msgQuestion("ATTENZIONE!", $header, array(
            'Annulla' => array('id' => $this->nameForm . '_AnnullaAnnullaInCarico', 'model' => $this->nameForm),
            'Conferma' => array('id' => $this->nameForm . '_ConfermaAnnullaInCarico', 'model' => $this->nameForm)
                ), 'auto', 'auto', 'false'
        );
    }

//    private function inviaNotificaResponsabileAssegnazione($codRespAss, $rowid, $titolo, $testo) {
//        include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
//        //$codRespAss = proSoggetto::getCodiceResponsabileAssegnazione();
//        if (!$codRespAss) {
//            return false;
//        }
//        include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
//        $accLib = new accLib();
//        $Utenti_rec = $accLib->GetUtenti($codRespAss, "uteana3");
//        if (!$Utenti_rec) {
//            return false;
//        }
//        $nomeUtente = $Utenti_rec['UTELOG'];
//        $envLib = new envLib();
//        if (!$envLib->inserisciNotifica($this->nameForm, $titolo, $testo, $nomeUtente, array(
//                    'ACTIONMODEL' => $this->nameForm,
//                    'ACTIONPARAM' => serialize(
//                            array(
//                                'setOpenMode' => array('edit'),
//                                'setOpenRowid' => array($rowid)
//                                //'setOpenRowid' => array($proges_rec['ROWID'])
//                            )
//                    )
//                        )
//                )
//        ) {
//            return false;
//        }
//        return true;
//    }
    function ConfermaAssegnazione($pratica, $assegnatario, $tipoPasso, $note, $daPortlet, $tipoRic = "codice") {
        $proges_rec = $this->praLib->GetProges($pratica);
        $praclt_rec = $this->praLib->GetPraclt($tipoPasso, $tipoRic);
        $ananom_rec = $this->praLib->GetAnanom($assegnatario, $tipoRic);
        //
        $praticaFormatted = substr($pratica, 4) . "/" . substr($pratica, 0, 4);
        //
        $seq = 9999;

        //
        //Inserisco Nuovo Passo Gestione/Assegnazione
        //
        $propas_new_rec = array();
        $propas_new_rec['PRONUM'] = $pratica;
        $propas_new_rec['PROPRO'] = $proges_rec['GESPRO'];
        $propas_new_rec['PRORES'] = $ananom_rec['NOMRES'];
        $propas_new_rec['PROSEQ'] = $seq;
        $propas_new_rec['PRORPA'] = $ananom_rec['NOMRES'];
        $propas_new_rec['PROUOP'] = $ananom_rec['NOMOPE'];
        $propas_new_rec['PROSET'] = $ananom_rec['NOMSET'];
        $propas_new_rec['PROSER'] = $ananom_rec['NOMSER'];
        $propas_new_rec['PRODPA'] = strtoupper($praclt_rec['CLTDES'] . " N. ") . $praticaFormatted;
        $propas_new_rec['PROCLT'] = $praclt_rec['CLTCOD'];
        $propas_new_rec['PRODTP'] = $praclt_rec['CLTDES'];
        $propas_new_rec['PROPAK'] = $this->praLib->PropakGenerator($pratica);
        $propas_new_rec['PROOPE'] = $praclt_rec['CLTOPE'];
        $propas_new_rec['PROANN'] = $note;
        $propas_new_rec['PROUTEADD'] = $propas_new_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $propas_new_rec['PRODATEADD'] = $propas_new_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        $insert_Info = "Oggetto: Inserisco passo " . $praclt_rec['CLTCOD'] . "-" . $praclt_rec['CLTDES'] . "  alla pratica " . $praticaFormatted;
        if (!$this->insertRecord($this->PRAM_DB, 'PROPAS', $propas_new_rec, $insert_Info)) {
            Out::msgStop("Inserimento passo", "Inserimento data set PROPAS fallito");
            $this->close();
            return false;
        }

        //
        //Ordino la sequnza dei passi dopo il nuvo inserito
        //
        if (!$this->praLib->ordinaPassi($pratica)) {
            Out::msgStop("Errore", "Errore nel riordinare i passi della pratica n. $praticaFormatted");
            $this->close();
            return false;
        }

        if ($daPortlet == true) {
            $propas_rec = $this->praLib->GetPropas($this->rowidAppoggio, "rowid");
            //
            //Chiudo il passo precedente
            //
            $propas_rec['PROINI'] = date("d/m/Y");
            $propas_rec['PROFIN'] = date("d/m/Y");
            $insert_Info = "Oggetto: Chiudo passo assegnazione della pratica " . $praticaFormatted;
            if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $insert_Info)) {
                Out::msgStop("Chiusura passo", "Aggiornamento data set PROPAS fallito");
                $this->close();
                return false;
            }
        }

        //
        //Se Mittente e destinatario dell'assegnazione sono diversi dal responsabile assegnazione, invio notifica al responsabile
        //
        if ($ananom_rec['NOMRESPASS'] == 0) {
            $profilo = proSoggetto::getProfileFromIdUtente();
            $ananom_rec_mitt = $this->praLib->GetAnanom($profilo['COD_ANANOM']);
            if ($ananom_rec_mitt['NOMRESPASS'] == 0) {
                $testo = $praclt_rec['CLTDES'] . " N. " . $praticaFormatted . " dall'utente " . $ananom_rec_mitt['NOMCOG'] . " " . $ananom_rec_mitt['NOMNOM'] . " all'utente " . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'];
                $codRespAss = proSoggetto::getCodiceResponsabileAssegnazione();
                if (!$this->praLib->inviaNotificaResponsabileAssegnazione("praGest", $codRespAss, $proges_rec['ROWID'], $propas_new_rec['PRODPA'], $testo)) {
                    Out::msgStop("Invio Notifica", "Impossibile Inviare una Notifica al Resposansabile delle Assegnazioni");
                    $this->close();
                    return false;
                }
            }
        }
        return true;
    }

}

?>