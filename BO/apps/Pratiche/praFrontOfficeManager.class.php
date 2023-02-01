<?php

/**
 *
 * LIBRERIA PER LA GESTIONE DATI DA SITO FRONT
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Simone Franchi / Michele Moscioni
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    03.04.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php';

class praFrontOfficeManager extends itaModel {

    const TYPE_FO_CART_WS = 'cart-ws';
    const TYPE_FO_STAR_WS = 'star-ws';
    const TYPE_FO_ITALSOFT_WS = 'italsoft-ws';
    const TYPE_FO_ITALSOFT_LOCAL = 'italsoft-local';
    const TYPE_BO_ITALSOFT_WS = 'italsoft-bo-ws';
    const STIMOLO_FO_PRESENTAZIONE_PRATICA = 'presentazione-pratica';
    const STIMOLO_FO_INVIO_INTEGRAZIONI = "invio-integrazioni";
    const STIMOLO_FO_PARERI_ESTERNI = "pareri-esterni";
    const STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI = "richiesta-conformazioni-esterni";
    const STIMOLO_FO_INVIO_CONFORMAZIONI = "invio-conformazioni";
    const STIMOLO_FO_ESITO_NEGATIVO_ESTERNI = "esito-negativo-esterni";
    const STIMOLO_FO_COMUNICAZIONI_GENERICHE = "comunicazioni-generiche";
    const STIMOLO_FO_COMUNICA = 'presentazione-pratica-comunica';
    const STIMOLO_FO_RICHIESTA_COLLEGATA = 'pratica-collegata';

    static public $FRONT_OFFICE_TYPES = array(
        self::TYPE_FO_ITALSOFT_LOCAL => 'ItalsoftLocal',
        self::TYPE_FO_ITALSOFT_WS => 'ItalsoftWs',
        self::TYPE_FO_STAR_WS => 'StarWs',
        self::TYPE_FO_CART_WS => 'CartWs',
        self::TYPE_BO_ITALSOFT_WS => 'ItalsoftBoWs'
    );
    static public $FRONT_OFFICE_STIMOLI = array(
        self::STIMOLO_FO_PRESENTAZIONE_PRATICA => "Presentazione Pratica",
        self::STIMOLO_FO_INVIO_INTEGRAZIONI => "Invio Integrazioni",
        self::STIMOLO_FO_PARERI_ESTERNI => "Pareri Esterni",
        self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI => "Richiesta Conformazioni Esterni",
        self::STIMOLO_FO_INVIO_CONFORMAZIONI => "Invio Conformazioni",
        self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI => "Esito Negativo Esterni",
        self::STIMOLO_FO_COMUNICAZIONI_GENERICHE => "Comunicazioni Generiche",
        self::STIMOLO_FO_COMUNICA => 'Presentazione Pratica Comunica',
        self::STIMOLO_FO_RICHIESTA_COLLEGATA => 'Pratica Collegata'
    );
    static public $FRONT_OFFICE_TYPES_DESCRIPTIONS = array(
        self::TYPE_FO_ITALSOFT_LOCAL => 'Richieste Portale Italsoft locale',
        self::TYPE_FO_ITALSOFT_WS => 'Richieste Portale Italsoft Remoto',
        self::TYPE_FO_STAR_WS => 'Richieste Portale STAR da WebService Selec',
        self::TYPE_FO_CART_WS => 'Richieste Portale STAR dal CART',
        self::TYPE_BO_ITALSOFT_WS => 'Fascicolo Back Office Italsoft Remoto'
    );
    static public $FRONT_OFFICE_COLORS_STIMOLI = array(
        self::STIMOLO_FO_PRESENTAZIONE_PRATICA => "black",
        self::STIMOLO_FO_INVIO_INTEGRAZIONI => "blue",
        self::STIMOLO_FO_PARERI_ESTERNI => "green",
        self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI => "yellow",
        self::STIMOLO_FO_INVIO_CONFORMAZIONI => "brown",
        self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI => "yellow",
        self::STIMOLO_FO_COMUNICAZIONI_GENERICHE => "blue",
        self::STIMOLO_FO_COMUNICA => "red",
        self::STIMOLO_FO_RICHIESTA_COLLEGATA => "navy",
    );
    static public $FRONT_OFFICE_TYPES_STIMOLI = array(
        self::TYPE_FO_ITALSOFT_LOCAL => array(
            self::STIMOLO_FO_PRESENTAZIONE_PRATICA => self::STIMOLO_FO_PRESENTAZIONE_PRATICA,
            self::STIMOLO_FO_INVIO_INTEGRAZIONI => self::STIMOLO_FO_INVIO_INTEGRAZIONI,
            self::STIMOLO_FO_PARERI_ESTERNI => self::STIMOLO_FO_PARERI_ESTERNI,
            self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI => self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI,
            self::STIMOLO_FO_INVIO_CONFORMAZIONI => self::STIMOLO_FO_INVIO_CONFORMAZIONI,
            self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI => self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI,
            self::STIMOLO_FO_COMUNICAZIONI_GENERICHE => self::STIMOLO_FO_COMUNICAZIONI_GENERICHE,
            self::STIMOLO_FO_COMUNICA => self::STIMOLO_FO_COMUNICA,
            self::STIMOLO_FO_RICHIESTA_COLLEGATA => self::STIMOLO_FO_RICHIESTA_COLLEGATA),
        self::TYPE_FO_ITALSOFT_WS => array(
            self::STIMOLO_FO_PRESENTAZIONE_PRATICA => self::STIMOLO_FO_PRESENTAZIONE_PRATICA,
            self::STIMOLO_FO_INVIO_INTEGRAZIONI => self::STIMOLO_FO_INVIO_INTEGRAZIONI,
            self::STIMOLO_FO_PARERI_ESTERNI => self::STIMOLO_FO_PARERI_ESTERNI,
            self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI => self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI,
            self::STIMOLO_FO_INVIO_CONFORMAZIONI => self::STIMOLO_FO_INVIO_CONFORMAZIONI,
            self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI => self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI,
            self::STIMOLO_FO_COMUNICAZIONI_GENERICHE => self::STIMOLO_FO_COMUNICAZIONI_GENERICHE,
            self::STIMOLO_FO_COMUNICA => self::STIMOLO_FO_COMUNICA,
            self::STIMOLO_FO_RICHIESTA_COLLEGATA => self::STIMOLO_FO_RICHIESTA_COLLEGATA),
        self::TYPE_FO_STAR_WS => array(
            self::STIMOLO_FO_PRESENTAZIONE_PRATICA => 'Presentazione Pratica',
            self::STIMOLO_FO_INVIO_INTEGRAZIONI => 'invioIntegrazioni',
            self::STIMOLO_FO_PARERI_ESTERNI => 'valutazioneIntegrazione',
            self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI => 'valutazioneConformazione',
            self::STIMOLO_FO_INVIO_CONFORMAZIONI => 'invioConformazioni',
            self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI => 'esitoNegativo',
            self::STIMOLO_FO_COMUNICAZIONI_GENERICHE => 'comunicazione',
            self::STIMOLO_FO_COMUNICA => ''),
        self::TYPE_FO_CART_WS => array(
            self::STIMOLO_FO_PRESENTAZIONE_PRATICA => 'Presentazione Pratica',
            self::STIMOLO_FO_INVIO_INTEGRAZIONI => 'invioIntegrazioni',
            self::STIMOLO_FO_PARERI_ESTERNI => 'valutazioneIntegrazione',   // 'Richiesta Integrazione ente terzo',
            self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI => 'valutazioneConformazione',   // Richiesta Conformazione ente terzo',
            self::STIMOLO_FO_INVIO_CONFORMAZIONI => 'invioConformazioni',
            self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI => 'esitoNegativo',
            self::STIMOLO_FO_COMUNICAZIONI_GENERICHE => 'comunicazione',
            self::STIMOLO_FO_COMUNICA => ''),
        self::TYPE_BO_ITALSOFT_WS => array(
            self::STIMOLO_FO_PRESENTAZIONE_PRATICA => self::STIMOLO_FO_PRESENTAZIONE_PRATICA,
            self::STIMOLO_FO_INVIO_INTEGRAZIONI => self::STIMOLO_FO_INVIO_INTEGRAZIONI,
            self::STIMOLO_FO_PARERI_ESTERNI => self::STIMOLO_FO_PARERI_ESTERNI,
            self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI => self::STIMOLO_FO_RICHIESTA_CONFORMAZIONI_ESTERNI,
            self::STIMOLO_FO_INVIO_CONFORMAZIONI => self::STIMOLO_FO_INVIO_CONFORMAZIONI,
            self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI => self::STIMOLO_FO_ESITO_NEGATIVO_ESTERNI,
            self::STIMOLO_FO_COMUNICAZIONI_GENERICHE => self::STIMOLO_FO_COMUNICAZIONI_GENERICHE,
            self::STIMOLO_FO_COMUNICA => self::STIMOLO_FO_COMUNICA),
    );
    static public $lasErrCode;
    static public $lasErrMessage;
    protected $foTipo;
    protected $praLib;
    protected $errMessage;
    protected $errCode;
    protected $retStatus = array();

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function getFoTipo() {
        return $this->foTipo;
    }

    public function setFoTipo($foTipo) {
        $this->foTipo = $foTipo;
    }

    public static function getActiveFrontOffices() {
        /*
         * TODO: Questo metodo è fake va completato con dei dettagli di configurazione
         *
         */
        $frontofficesList = array(
            self::TYPE_FO_STAR_WS
        );
        return $frontofficesList;
    }

    /**
     * Lancia il giusto controllo pre-condizioni in base al tipo di front-office
     * @param type $param
     * @return type
     */
    public static function checkAcqPreconditions($param) {
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($param['prafolist_rec']['FOTIPO']);
        return $FoManager->checkFoAcqPreconditions($param);
    }

    /**
     * Salva su Tabella PRAFOLIST e PRAFOFILES
     *
     * @return array
     */
    public function salvaPratica($data) {
        $praFoList_rec = $data['PRAFOLIST'];
        $praFoFiles_tab = $data['PRAFOFILES'];
        try {
            $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAFOLIST', 'ROWID', $praFoList_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento su PRAFOLIST non avvenuto.");
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Pratica " . $praFoList_rec['FOPRAKEY'] . " già riletta dal sistema ");
            return false;
        }

        $anno = substr($praFoList_rec['FOPRADATA'], 0, 4);

        foreach ($praFoFiles_tab as $praFoFiles_rec) {

            $srcFile = $praFoFiles_rec['TMP_SOURCEFILE'];
            unset($praFoFiles_rec['TMP_SOURCEFILE']);
            // Elimina elemento dall'array
            // Copia il file dalla cartella temporea in quella restituida dal metodo SetDirectoryPratiche
            $dir = $this->praLib->SetDirectoryPratiche($anno, $praFoFiles_rec['FOPRAKEY'], $praFoFiles_rec['FOTIPO']);
            $destFile = $dir . "/" . $praFoFiles_rec['FILEFIL'];


            if (!copy($srcFile, $destFile)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Copia di $srcFile su $destFile non avvenuto.");
                return false;
            }

            try {
                $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAFOFILES', 'ROWID', $praFoFiles_rec);
                if ($nRows == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Inserimento su PRAFOFILES non avvenuto.");
                    return false;
                }
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in Inserimento su PRAFOFILES " . $e->getMessage());
                return false;
            }
            // Elimina il file dalla cartella temporanea
            if ($praFoList_rec['FOTIPO'] != praFrontOfficeManager::TYPE_FO_CART_WS) {
                unlink($srcFile);
            }
        }
        return true;
    }

    /**
     * 
     * @param type $rowid
     * @param array $ret_esito
     * @return boolean
     */
//    public static function caricaFascicoloFromDatiEssenziali($rowid, $dati, &$ret_esito) {
//
//        /* @var $FoManager praFrontOfficeManager */
//        $FoManager = self::getFrontOfficeManagerInstanceFromPrafolistRowid($rowid);
//
//        $fotipo = $FoManager->getFoTipo();
//
//        $praLib = new praLib();
//
//        $datamodelArray = self::praFoList2DataModel($rowid, $dati);
//
//        $ret_esito = array();
//
//        /*
//         * Mi scorro i vari dataModel e per ognuno faccio un caricaDaPec
//         */
//        $model = new itaModel();
//        include_once (ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php');
//        $praLibPratica = praLibPratica::getInstance();
//        foreach ($datamodelArray as $datamodel) {
//            $result = $praLibPratica->acquisizioneRichiesta($datamodel, $model);
//            if ($result['Status'] == "-1") {
//                self::$lasErrCode = -1;
//                self::$lasErrMessage = "Acquisizione pratica fallita: " . $result['Message'];
//                return false;
//            } else {
//                $ret_aggiungi = array();
//                $ret_aggiungi['GESNUM'] = $result['GESNUM'];
//                $ret_aggiungi['PROPAK'] = $result['PROPAK'];
//                $ret_aggiungi['ExtendedMessageHtml'] = $result['ExtendedMessageHtml'];
//                array_push($ret_esito, $ret_aggiungi);
//                unset($ret_aggiungi);  // Svuota il vettore
//            }
//        }
//
//        $prafolist_rec = $praLib->GetPrafolist($rowid);
//
//        /*
//         * in caso di tipo TYPE_BO_ITALSOFT_WS, acquisico i dati del fascicolo remoto
//         */
//        if ($fotipo == self::TYPE_BO_ITALSOFT_WS) {
//            $retAcq = $FoManager->acquisisciDatiFascicolo($prafolist_rec);
//            if (!$retAcq) {
//                self::$lasErrCode = -1;
//                self::$lasErrMessage = $FoManager->errMessage;
//                return false;
//            } else {
//                array_push($ret_esito, $retAcq);
//            }
//        }
//        return true;
//    }


    public static function caricaFascicoloFromPRAFOLIST($rowid, &$ret_esito, $dati = array()) {

        /* @var $FoManager praFrontOfficeManager */
//        $FoManager = self::getFrontOfficeManagerInstanceFromPrafolistRowid($rowid);
//        $fotipo = $FoManager->getFoTipo();
//        $praLib = new praLib();
//        $praFoList_rec = $praLib->GetPrafolist($rowid);
//        switch ($fotipo) {
//            case self::TYPE_BO_ITALSOFT_WS:
//                self::openFormPraGestDatiEssenziali($praFoList_rec);
//                break;
//            case self::TYPE_FO_ITALSOFT_LOCAL:
//            case self::TYPE_FO_ITALSOFT_WS:
//                $proric_rec = $praLib->GetProric($praFoList_rec['FOPRAKEY']);
//                if ($proric_rec['RICRPA'] || $proric_rec['PROPAK']) {
//                    self::caricaFascicoloFromDatiEssenziali($rowid, array("PRORIC_REC" => $proric_rec), $ret_esito);
//                } else {
//                    $FoManager->openFormPraGestDatiEssenziali($praFoList_rec);
//                }
//                break;
//            default:
        /*
         * si Usa sempre tranne quando si preleva da un back-office cwol collegato
         * 
         */
//                $datamodelArray = self::praFoList2DataModel($rowid, $dati);
//                $model = new itaModel();
//                include_once (ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php');
//                $praLibPratica = praLibPratica::getInstance();
//                $ret_esito = array();
//                foreach ($datamodelArray as $datamodel) {
//                    $result = $praLibPratica->acquisizioneRichiesta($datamodel, $model);
//                    if ($result['Status'] == "-1") {
//                        self::$lasErrCode = -1;
//                        self::$lasErrMessage = "Acquisizione pratica fallita: " . $result['Message'];
//                        return false;
//                    } else {
//                        $ret_aggiungi = array();
//                        $ret_aggiungi['GESNUM'] = $result['GESNUM'];
//                        $ret_aggiungi['PROPAK'] = $result['PROPAK'];
//                        $ret_aggiungi['ExtendedMessageHtml'] = $result['ExtendedMessageHtml'];
//                        array_push($ret_esito, $ret_aggiungi);
//                        unset($ret_aggiungi);  // Svuota il vettore
//                    }
//                }
//                foreach ($datamodelArray as $datamodel) {
//                    $ret_aggiungi = $praLibPratica->aggiungi($this, $datamodel);
//                    if (!$ret_aggiungi) {
//                        self::$lasErrCode = -1;
//                        self::$lasErrMessage = $praLibPratica->getErrMessage();
//                        return false;
//                    } else {
//                        array_push($ret_esito, $ret_aggiungi);
//                        unset($ret_aggiungi);  // Svuota il vettore
//                    }
//                }
//                break;
//        }

        if ($rowid == "") {
            self::$lasErrCode = -1;
            self::$lasErrMessage = "Acquisizione pratica fallita: Rowid richiesta non trovato.";
            return false;
        }

        $datamodelArray = self::praFoList2DataModel($rowid, $dati);
        $model = new itaModel();
        include_once (ITA_BASE_PATH . '/apps/Pratiche/praLibPratica.class.php');
        $praLibPratica = praLibPratica::getInstance();
        $ret_esito = array();
        foreach ($datamodelArray as $datamodel) {
            $ret_aggiungi = $praLibPratica->acquisizioneRichiesta($datamodel, $model);
            if ($ret_aggiungi['Status'] == "-1") {
                self::$lasErrCode = -1;
                self::$lasErrMessage = "Acquisizione pratica fallita: " . $ret_aggiungi['Message'];
                return false;
            } else {
//                $ret_aggiungi = array();
//                $ret_aggiungi['GESNUM'] = $result['GESNUM'];
//                $ret_aggiungi['PROPAK'] = $result['PROPAK'];
//                $ret_aggiungi['ExtendedMessageHtml'] = $result['ExtendedMessageHtml'];
                array_push($ret_esito, $ret_aggiungi);
                unset($ret_aggiungi);  // Svuota il vettore
            }
        }

        /*
         * in caso di tipo TYPE_BO_ITALSOFT_WS, acquisico i dati del fascicolo remoto
         */
        $FoManager = self::getFrontOfficeManagerInstanceFromPrafolistRowid($rowid);
        $fotipo = $FoManager->getFoTipo();
        $praLib = new praLib();
        $praFoList_rec = $praLib->GetPrafolist($rowid);
        if ($fotipo == self::TYPE_BO_ITALSOFT_WS) {
            $retAcq = $FoManager->acquisisciDatiFascicolo($praFoList_rec);
            if (!$retAcq) {
                self::$lasErrCode = -1;
                self::$lasErrMessage = $FoManager->errMessage;
                return false;
            } else {
                array_push($ret_esito, $retAcq);
            }
        }

        return true;
    }

    public static function getFrontOfficeManagerInstanceFromPrafolistRowid($rowid) {
        $praLib = new praLib();
        self::$lasErrCode = 0;
        self::$lasErrMessage = '';
        $praFoList_rec = $praLib->GetPrafolist($rowid);
        if (!$praFoList_rec) {
            self::$lasErrCode = -1;
            self::$lasErrMessage = 'Caricamento istanza Manager non avventa. Record PRAFOLIST non accessibile';
            return false;
        }
        include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeFactory.class.php';
        return praFrontOfficeFactory::getFrontOfficeManagerInstance($praFoList_rec['FOTIPO']);
    }

    public static function getFrontOfficeManagerInstanceFromPrafolistRec() {
        
    }

    /**
     *
     * @param type $rowid
     * @return type obj
     */
    public function praFoList2DataModel($rowid, $dati = array()) {
        $praLib = new praLib();
        self::$lasErrCode = 0;
        self::$lasErrMessage = '';
        $praFoList_rec = $praLib->GetPrafolist($rowid);
        if (!$praFoList_rec) {
            self::$lasErrCode = -1;
            self::$lasErrMessage = 'Caricamento della pratica non avvenuto.';
            //Out::msgStop("Caricamento Pratica", "Caricamento della pratica non avvenuto.");
            return false;
        }
        include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeFactory.class.php';
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($praFoList_rec['FOTIPO']);

        $datamodelArray = $FoManager->getDataModelAcq($praFoList_rec, $dati);
        return $datamodelArray;
    }

    public static function getDescrizioneGeneraleRichiesta($prafolist_rec) {
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($prafolist_rec['FOTIPO']);
        return $FoManager->getDescrizioneGeneraleRichiestaFo($prafolist_rec);
    }

    public static function getAllegatiRichiesta($prafolist_rec, $allegatiInfocamere = array()) {
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($prafolist_rec['FOTIPO']);
        return $FoManager->getAllegatiRichiestaFo($prafolist_rec, $allegatiInfocamere);
    }

    public function ripristinaRichiesta($gesnum) {
        $praLib = new praLib();

        /*
         * TODO: Scorrere i passi e vedere se ci sono integrazioni da ripristinare lanciando il metodo ripristinaIntegrazione($gesnum, $propak)
         */
        $propas_tab = $praLib->GetPropas($gesnum, 'codice', true);
        if ($propas_tab) {

            foreach ($propas_tab as $propas_rec) {
                if ($propas_rec['PROPAK']) {
                    $this->ripristinaIntegrazione($gesnum, $propas_rec['PROPAK']);
                }
            }
        }

        $sql = "SELECT * FROM PRAFOLIST WHERE PRAFOLIST.FOGESNUM = '" . $gesnum . "' ";
        $praFoList_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, false);
        if ($praFoList_rec) {
            $annoOrig = substr($gesnum, 0, 4);
            //$dirOrig = $this->praLib->SetDirectoryPratiche($annoOrig, $praFoList_rec['FOGESNUM'], "PROGES");

            $sql = "SELECT * FROM PRAFOFILES"
                    . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                    . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

            $praFoFiles_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);

//            $annoOrig = substr($gesnum, 0, 4);
            $annoDest = substr($praFoList_rec['FOPRADATA'], 0, 4);

//            $dirOrig = $this->praLib->SetDirectoryPratiche($annoOrig, $praFoList_rec['FOGESNUM'], "PROGES");
            $dirDest = $this->praLib->SetDirectoryPratiche($annoDest, $praFoList_rec['FOPRAKEY'], $praFoList_rec['FOTIPO']);


            foreach ($praFoFiles_tab as $praFoFiles_rec) {
                $pasdoc_rec = $this->praLib->GetPasdoc($praFoFiles_rec['PASDOCROWID'], "ROWID");
                if ($pasdoc_rec) {
                    $dirOrig = $this->praLib->SetDirectoryPratiche($annoOrig, $pasdoc_rec['PASKEY'], "PASSO");
                    if (strlen($pasdoc_rec['PASKEY']) == 10) {
                        $dirOrig = $this->praLib->SetDirectoryPratiche($annoOrig, $pasdoc_rec['PASKEY'], "PROGES");
                    }
                    $srcFile = $dirOrig . "/" . $pasdoc_rec['PASFIL'];
                    $destFile = $dirDest . "/" . $praFoFiles_rec['FILEFIL'];
                    if (!copy($srcFile, $destFile)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore nel ripristino degli allegati.");
                        return false;
                    }
                }
//                else {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage("Errore nel ripristino degli allegati. Non trovati gli allegati nel fascicolo");
//                    return false;
//                }
            }

            // Pulisce il campo PRAFOFILES.PASDOCROWID
            foreach ($praFoFiles_tab as $praFoFiles_rec) {
                $praFoFiles_rec['PASDOCROWID'] = '';
                $update_Info = "Oggetto: Cancellazione campo PRAFOFILES.PASDOCROWID per PRAFOFILES.ROW_ID " . $praFoFiles_rec['ROW_ID'];

                $nRows = ItaDB::DBUpdate($this->praLib->getPRAMDB(), 'PRAFOFILES', 'ROW_ID', $praFoFiles_rec);
                if ($nRows == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Cancellazione campo PRAFOFILES.PASDOCROWID per PRAFOFILES.ROW_ID " . $praFoFiles_rec['ROW_ID'] . " Fallita.");

//                self::$lasErrCode = -1;
//                self::$lasErrMessage = "Cancellazione campo PRAFOFILES.PASDOCROWID per PRAFOFILES.ROW_ID " . $praFoFiles_rec['ROW_ID'] . " Fallita.";

                    return false;
                }
            }

            //Pulisco PRAFOLIST.FOGESNUM
            $praFoList_rec['FOGESNUM'] = '';
            $praFoList_rec['FOPROPAK'] = '';
            $nRows = ItaDB::DBUpdate($this->praLib->getPRAMDB(), 'PRAFOLIST', 'ROW_ID', $praFoList_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Cancellazione campo PRAFOLIST.FOGESNUM per PRAFOLIST.ROW_ID " . $praFoList_rec['ROW_ID'] . " Fallita.");

//            self::$lasErrCode = -1;
//            self::$lasErrMessage = "Cancellazione campo PRAFOLIST.FOGESNUM per PRAFOLIST.ROW_ID " . $praFoList_rec['ROW_ID'] . " Fallita.";

                return false;
            }
        }

        return true;
    }

    public function ripristinaIntegrazione($gesnum, $propak) {
        $praLib = new praLib();

        $sql = "SELECT * FROM PRAFOLIST WHERE PRAFOLIST.FOPROPAK = '" . $propak . "' ";
        $praFoList_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, false);

        if ($praFoList_rec) {
            $annoOrig = substr($gesnum, 0, 4);

            $dirOrig = $this->praLib->SetDirectoryPratiche($annoOrig, $praFoList_rec['FOPROPAK'], 'PASSO');

            $sql = "SELECT * FROM PRAFOFILES"
                    . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                    . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

            $praFoFiles_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);

//            $annoOrig = substr($gesnum, 0, 4);
            $annoDest = substr($praFoList_rec['FOPRADATA'], 0, 4);

//            $dirOrig = $this->praLib->SetDirectoryPratiche($annoOrig, $praFoList_rec['FOGESNUM'], "PROGES");
            $dirDest = $this->praLib->SetDirectoryPratiche($annoDest, $praFoList_rec['FOPRAKEY'], $praFoList_rec['FOTIPO']);


            foreach ($praFoFiles_tab as $praFoFiles_rec) {
                $pasdoc_rec = $this->praLib->GetPasdoc($praFoFiles_rec['PASDOCROWID'], "ROWID");
                if ($pasdoc_rec) {
                    $srcFile = $dirOrig . "/" . $pasdoc_rec['PASFIL'];
                    $destFile = $dirDest . "/" . $praFoFiles_rec['FILEFIL'];
                    if (!copy($srcFile, $destFile)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore nel ripristino degli allegati.");
                        return false;
                    }
                }
//                else {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage("Errore nel ripristino degli allegati. Non trovati gli allegati nel fascicolo");
//                    return false;
//                }
            }

            // Pulisce il campo PRAFOFILES.PASDOCROWID
            foreach ($praFoFiles_tab as $praFoFiles_rec) {
                $praFoFiles_rec['PASDOCROWID'] = '';
                $update_Info = "Oggetto: Cancellazione campo PRAFOFILES.PASDOCROWID per PRAFOFILES.ROW_ID " . $praFoFiles_rec['ROW_ID'];

                $nRows = ItaDB::DBUpdate($this->praLib->getPRAMDB(), 'PRAFOFILES', 'ROW_ID', $praFoFiles_rec);
                if ($nRows == -1) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Cancellazione campo PRAFOFILES.PASDOCROWID per PRAFOFILES.ROW_ID " . $praFoFiles_rec['ROW_ID'] . " Fallita.");

//                self::$lasErrCode = -1;
//                self::$lasErrMessage = "Cancellazione campo PRAFOFILES.PASDOCROWID per PRAFOFILES.ROW_ID " . $praFoFiles_rec['ROW_ID'] . " Fallita.";

                    return false;
                }
            }

            //Pulisco PRAFOLIST.FOGESNUM
            $praFoList_rec['FOGESNUM'] = '';
            $praFoList_rec['FOPROPAK'] = '';
            $nRows = ItaDB::DBUpdate($this->praLib->getPRAMDB(), 'PRAFOLIST', 'ROW_ID', $praFoList_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Cancellazione campo PRAFOLIST.FOGESNUM per PRAFOLIST.ROW_ID " . $praFoList_rec['ROW_ID'] . " Fallita.");

//            self::$lasErrCode = -1;
//            self::$lasErrMessage = "Cancellazione campo PRAFOLIST.FOGESNUM per PRAFOLIST.ROW_ID " . $praFoList_rec['ROW_ID'] . " Fallita.";

                return false;
            }
        }

        return true;
    }

    public static function scaricaPraticheFO($arrayFo) {
        /*
         * Ciclo sui FO abilitati
         */
        $htmlDefinitivo = "";
        foreach ($arrayFo as $foTrovato) {
            if ($foTrovato['ATTIVO'] == 1) {
                $FOManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($foTrovato['TIPO']);

                /*
                 * Scarico le nuove pratiche e costruisco l'output html
                 */
                $FOManager->scaricaPraticheNuove($foTrovato['ISTANZA']);
                $risposta = $FOManager->getRetStatus();
                $htmlDefinitivo .= self::getHtmlEsitoScarica($risposta, $foTrovato['TIPO'], $foTrovato['ISTANZA']);
            }
        }

        Out::msgInfo("Elaborazione Terminata", $htmlDefinitivo, "500", "600");
        Out::codice("tableToGrid(\".response\", {});");
    }

    public function getData($timeStamp) {
        // Formato $timeStamp è 2017-09-11 00:00:00.0

        $data = substr($timeStamp, 0, 4) . substr($timeStamp, 5, 2) . substr($timeStamp, 8, 2);

        return $data;
    }

    public function getOra($timeStamp) {
        // Formato $timeStamp è 2017-09-11 00:00:00.0

        $ora = substr($timeStamp, 11, 8);

        return $ora;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function getErrCode() {
        return $this->errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function getRetStatus() {
        return $this->retStatus;
    }

    function setRetStatus($retStatus) {
        $this->retStatus = $retStatus;
    }

    function getHtmlEsitoScarica($risposta, $tipo, $istanza) {
        $descIstanza = self::getDescrizioneIstanza($istanza);
        $html = "<span><b>" . $tipo . " " . $descIstanza . "</b></span><br/>";
        if ($risposta['Lette'] > 0 || $risposta['Errori'] > 0) {
            $mess = "";
            foreach ($risposta['Messages'] as $message) {
                $mess .= $message . "<br/>";
            }
            $html .= "<table id=\"tableResponse$tipo\" class=\"response\">";
            $html .= "<tr>";
            $html .= '<th>Status</th>';
            $html .= '<th>Lette</th>';
            $html .= '<th>Scaricate</th>';
            $html .= '<th>Errori</th>';
            $html .= '<th>Messaggi</th>';
            $html .= "</tr>";
            $html .= "<tbody>";
            $html .= "<tr>";
            $html .= "<td>" . $risposta['Status'] . "</td>";
            $html .= "<td>" . $risposta['Lette'] . "</td>";
            $html .= "<td>" . $risposta['Scaricate'] . "</td>";
            $html .= "<td>" . $risposta['Errori'] . "</td>";
            $html .= "<td>$mess</td>";
            $html .= "</tr>";
            $html .= "</tbody>";
            $html .= '</table>';
            $html .= '<br/>';
        } else {
            $html .= "<span>Non sono presenti nuove pratiche da scaricare</span><br/><br/><br/>";
        }
        return $html;
    }

    function getDescrizioneIstanza($istanza) {
        $envLib = new envLib();
        if ($istanza) {
            $istanzeParams = $envLib->getIstanze('BACKENDFASCICOLIITALSOFT');
            $key = array_search($istanza, array_column($istanzeParams, "CLASSE"));
        }
        return $istanzeParams[$key]['DESCRIZIONE_ISTANZA'];
    }

    public static function setMarcaturaPrafolist($row_id, $gesnum, $propak = "") {
        if ($row_id) {
            $praLib = new praLib();
            $prafolist_rec = $praLib->GetPrafolist($row_id, 'rowid');
            if (!$prafolist_rec) {
                self::$lasErrCode = -1;
                self::$lasErrMessage = "Accesso a prafolist per marcatura acquisizione fallito.";
                return false;
            }
            $prafolist_rec["FOGESNUM"] = $gesnum;
            $prafolist_rec["FOPROPAK"] = $propak;
            $nupd = ItaDB::DBUpdate($praLib->getPRAMDB(), 'PRAFOLIST', 'ROW_ID', $prafolist_rec);
            if ($nupd == -1) {
                self::$lasErrCode = -1;
                self::$lasErrMessage = "Marcatura Repository PRAFOLIST come richiesta acquisita, richiesta: $gesnum Fallito.";
                return false;
            }
        }
        return true;
    }

    public static function getProric($prafolist_rec) {
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($prafolist_rec['FOTIPO']);
        $proric_rec = $FoManager->getProricRec($prafolist_rec);
        $risposta = $FoManager->getRetStatus();
        if ($risposta['Status'] === false) {
            self::$lasErrMessage = $risposta['Messages'];
            return false;
        }
        return $proric_rec;
    }

    public static function openFormPraGestDatiEssenziali($prafolist_rec) {
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($prafolist_rec['FOTIPO']);
        $FoManager->openFormDatiEssenziali($prafolist_rec);
    }

    public static function getAllegatoRichiesta($prafolist_rec, $rowidAlle, $allegatiInfocamere) {
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($prafolist_rec['FOTIPO']);
        $arrAllegato = $FoManager->getAllegato($prafolist_rec, $rowidAlle, $allegatiInfocamere);
        $risposta = $FoManager->getRetStatus();
        if ($risposta['Status'] === false) {
            self::$lasErrMessage = $risposta['Messages'];
            return false;
        }
        return $arrAllegato;$risposta['Messages'];
            return false;
    }

//    public static function aggiornaArticoliFO() {
//        // Si prendono i record di PROPAS 
//        $sql = "SELECT * FROM PROPAS  WHERE DATAOPER > PRODATEPUBART OR (DATAOPER = PRODATEPUBART AND TIMEOPER > PROTIMEPUBART) OR (DATAOPER IS NULL AND PRODATEPUBART IS NOT NULL)";
//        $propas_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
//        
//        if ($propas_tab){
//
//            
//            
//        }
//        
//        return true;
//        
//    }

    public static function caricaArticoliFO($tipo, $gesnum, $keyPasso, $istanza, $tipoOperazione = 'Insert', $pubArticolo = '1', $pubbAllegati = '0') {
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($tipo);
        
        $esito = $FoManager->pubblicazioneArticoli($gesnum, $keyPasso, $istanza, $tipoOperazione, $pubArticolo, $pubbAllegati);
        if (!$esito){
            $risposta = $FoManager->getRetStatus();
//            Out::msginfo("Esito", print_r($risposta,true));

            self::$lasErrCode = -1;
            self::$lasErrMessage = "Pubblicazione Articolo fallita: " . $risposta['Messages'][0] ;
        }
        
        return $esito;
    }
    public static function caricaRichiesta($prafolist_rec, $dati, $allegatiInfocamere) {
        $FoManager = praFrontOfficeFactory::getFrontOfficeManagerInstance($prafolist_rec['FOTIPO']);
        $ret_esito = $FoManager->caricaRichiestaFO($prafolist_rec, $dati, $allegatiInfocamere);
        $risposta = $FoManager->getRetStatus();
        if ($risposta['Status'] === false) {
            self::$lasErrMessage = $risposta['Messages'];
            return false;
        }
        return $ret_esito;
//        switch ($prafolist_rec['FOTIPO']) {
//            case praFrontOfficeManager::TYPE_BO_ITALSOFT_WS:
//                praFrontOfficeManager::openFormPraGestDatiEssenziali($prafolist_rec);
//                break;
//            case praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL:
//            case praFrontOfficeManager::TYPE_FO_ITALSOFT_WS:
//                $proric_rec = praFrontOfficeManager::getProric($prafolist_rec);
//                if ($proric_rec['RICSTA'] == "91" && !$this->allegatiInfocamere) {
//                    Out::msgQuestion("RICHIESTA CAMERA DI COMMERCIO!", "Hai ricevuto la mail di conferma dalla camera di commercio?", array(
//                        'F8-No' => array('id' => $this->nameForm . '_NoConfermaMail', 'model' => $this->nameForm, 'shortCut' => "f8"),
//                        'F5-Si' => array('id' => $this->nameForm . '_SiConfermaMail', 'model' => $this->nameForm, 'shortCut' => "f5")
//                            ), "auto", "auto", "false"
//                    );
//                } else if ($proric_rec['RICRPA'] || $proric_rec['PROPAK']) {
//                    $ret_esito = null;
//                    if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito, $_POST['datiMail']['Dati'])) {
//                        Out::msgStop("Errore di acquisizione", praFrontOfficeManager::$lasErrMessage);
//                        break;
//                    }
//                } else {
//                    praFrontOfficeManager::openFormPraGestDatiEssenziali($prafolist_rec);
//                }
//                break;
//            default:
//                $ret_esito = null;
//                if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito)) {
//                    Out::msgStop("Errore di acquisizione", praFrontOfficeManager::$lasErrMessage);
//                    break;
//                }
//                break;
//        }
    }

}
