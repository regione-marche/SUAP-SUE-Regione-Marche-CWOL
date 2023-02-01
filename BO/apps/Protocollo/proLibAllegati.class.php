<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    06.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_LIB_PATH . '/QXml/QXml.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
include_once ITA_LIB_PATH . '/itaPHPAlfcity/itaDocumentaleAlfrescoUtils.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php'; //***@Alfresco
include_once ITA_BASE_PATH . '/apps/Protocollo/proProtocollo.class.php'; //***@Alfresco
include_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php'; //***@Alfresco
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaUUID.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proDocReader.class.php';

class proLibAllegati {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    const ALL_FUNC_MARCA = 'Marcare con la segnatura';
    const ALL_FUNC_MARCA_E_DA_FIRMARE = 'Marcare con la segnatura<br>e Metti da Firmare';
    const ALL_FUNC_DA_FIRMARE = 'Metti alla Firma';
    const ALL_FUNC_FIRMA = 'Firma Allegato';
    const ALL_FUNC_TOGLI_DA_FIRMARE = 'Togli dalla Firma';
    const ALL_FUNC_GESTSEGNATURA = 'Marca con la segnatura <br><u>Scegli Posizione</u>';
    const ALL_FUNC_GESTSEGNATURA_FIRMA = '<u>Scegli posizione</u> segnatura,<br>marca e metti da Firmare';
    const ALL_FUNC_APRI_TESTOBASE = 'Modifica testo base';
    const ALL_FUNC_COPIA_ANALOGICA = 'Copia Analogica';
    const ALL_FUNC_GESTEXT = 'Inserisci Estensione';
    // 
    const ALLEGATO_FATT = 'SDI_CP_RICEZ';
    const ALLEGATO_ANNESSO_FATT = 'SDI_CP_ANNESSI';
    const ALLEGATO_PROT = 'DOC_GEN';

    public $proLib;
    public $proLibConservazione;
    public $eqAudit;
    private $errCode;
    private $errMessage;
    private $risultatoRitorno;
    private $datiMarcatura;
    private $noteMessage = array();

    function __construct() {
        $this->proLib = new proLib();
        $this->proLibConservazione = new proLibConservazione();
        $this->eqAudit = new eqAudit();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getRisultatoRitorno() {
        return $this->risultatoRitorno;
    }

    public function setRisultatoRitorno($risultatoRitorno) {
        $this->risultatoRitorno = $risultatoRitorno;
    }

    public function getDatiMarcatura() {
        return $this->datiMarcatura;
    }

    public function setDatiMarcatura($datiMarcatura) {
        $this->datiMarcatura = $datiMarcatura;
    }

    function getNoteMessage() {
        return $this->noteMessage;
    }

    function setNoteMessage($noteMessage) {
        $this->noteMessage = $noteMessage;
    }

    public function GetDocfirma($codice, $tipo = 'rowid', $multi = false, $where = '') {
        if ($tipo == 'rowidanadoc') {
            $sql = "SELECT * FROM DOCFIRMA WHERE ROWIDANADOC='$codice'";
        } else if ($tipo == 'rowidarcite') {
            $sql = "SELECT * FROM DOCFIRMA WHERE ROWIDARCITE='$codice'";
        } else if ($tipo == 'rowid') {
            $sql = "SELECT * FROM DOCFIRMA WHERE ROWID = '$codice'";
        }
//        App::log($sql . $where);
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql . $where, $multi);
    }

    public function GetDocFirmaFromArcite($itepro, $itepar, $multi = true, $where = '') {
        $sql = "SELECT DOCFIRMA.* 
                FROM ARCITE ARCITE 
                LEFT OUTER JOIN DOCFIRMA DOCFIRMA 
                ON ARCITE.ROWID = DOCFIRMA.ROWIDARCITE
                WHERE ARCITE.ITEPRO = $itepro AND ARCITE.ITEPAR = '$itepar'";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql . $where, $multi);
    }

    public function checkPresenzaAllegati($pronum, $propar, $checkP7m = false) {
        $allegati = 0;
        $anadoc_tab = $this->proLib->getGenericTab("SELECT ROWID,DOCFIL FROM ANADOC WHERE DOCKEY LIKE '" . $pronum . $propar . "%' AND DOCSERVIZIO=0");
        if ($checkP7m) {
            foreach ($anadoc_tab as $anadoc_rec) {
                if (strtolower(pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION) == 'p7m')) {
                    $allegati += 1;
                }
            }
        } else {
            $allegati = count($anadoc_tab);
        }
        return $allegati;
    }

    public function VisualizzaFirme($file, $fileORiginale, $segnatura = array(), $ParamCopiaAnalogica = array()) {
        // Se il file passato è un DOCUUID devo copiaremi il file.
        if (substr($file, 0, 8) == 'DOCUUID:') {
            $Anadoc_rec = $this->GetRowidAnadocByUUID(substr($file, 7));
            if (!$Anadoc_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in apertura file firmato.');
                return false;
            }
            $CopyPathFile = $this->CopiaDocAllegato($Anadoc_rec['ROWID'], '', true);
            if (!$CopyPathFile) {
                return false;
            }
        }
        $model = "utiP7m";
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $_POST['event'] = "openform";
        $_POST['file'] = $file;
        if ($segnatura) {
            $_POST['segnatura'] = $segnatura;
        }
        if ($ParamCopiaAnalogica) {
            // ANAPRO_REC, ANADOC_REC, POSMARC(F)
            $_POST['paramCopiaAnalogica'] = $ParamCopiaAnalogica;
        }
        $_POST['fileOriginale'] = $fileORiginale;
        $model();
    }

    public function setFunzioneAllegati($nameForm, $allegato, $pronum, $propar, $flagInviati = false) {
        if ($allegato['ISFATTURAPA'] || $allegato['ISMESSAGGIOFATTURAPA']) {
            include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
            include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
            $proLibSdi = new proLibSdi();

            $DocPath = $this->GetDocPath($allegato['ROWID']);
            if (!$DocPath) {
                Out::msgStop("Attenzione", "Errore nella lettura dell'allegato Sdi." . $this->getErrMessage());
                return;
            }
//            $FileSdi = array('LOCAL_FILEPATH' => $allegato['FILEPATH'], 'LOCAL_FILENAME' => $allegato['DOCNAME']);
            $FileSdi = array('LOCAL_FILEPATH' => $DocPath['DOCPATH'], 'LOCAL_FILENAME' => $allegato['DOCNAME']);
            $ExtraParam = array('PARSEALLEGATI' => true);
            $objProSdi = proSdi::getInstance($FileSdi, $ExtraParam);
            if (!$objProSdi) {
                Out::msgStop("Attenzione", "Errore nell'istanziare proSdi.");
                return;
            }
            if ($objProSdi->isMessaggioSdi()) {
                $Xmlstyle = proSdi::$ElencoStiliMessaggio[$objProSdi->getTipoMessaggio()];
                $FilePath = $objProSdi->getFilePathMessaggio();
                if ($Xmlstyle && $FilePath) {
                    $proLibSdi->VisualizzaXmlConStile($Xmlstyle, $FilePath);
                }
            } elseif ($objProSdi->isFatturaPA()) {
                $FilePathFattura = $objProSdi->getFilePathFattura();
                $Xmlstyle = proSdi::StileFattura;
                $FilePath = $FilePathFattura[0];
                $Anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
                $proLibSdi->openInfoFattura($objProSdi, $Anapro_rec);
            }
            if ($objProSdi->getWarningMessages()) {
                $Messaggi = implode('<br>', $objProSdi->getWarningMessages());
                Out::msgInfo('Attenzione', $Messaggi);
            }
            return;
        }
        $flMarcatura = false;
        $flMarcaturaFirma = false;
        $messaggio = '';

        $MessSegnatura = '<br><span style="color:green">Posizione predefinita</span>';
        $StyleBottoni = "width:250px;height:40px;";
        $ext = strtolower(pathinfo($allegato['DOCNAME'], PATHINFO_EXTENSION));
        $ALL_FUNC_TEMPLATE = array(
            self::ALL_FUNC_MARCA => array('id' => $nameForm . '_SegnaDocumento', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_MARCA_E_DA_FIRMARE => array('id' => $nameForm . '_SegnaDocumentoDaFirmare', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_DA_FIRMARE => array('id' => $nameForm . '_DaFirmare', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-new-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_TOGLI_DA_FIRMARE => array('id' => $nameForm . '_TogliDaFirmare', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-new-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_FIRMA => array('id' => $nameForm . '_FirmaFile', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-sigillo-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_GESTSEGNATURA => array('id' => $nameForm . '_GesSegnatura', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_GESTSEGNATURA_FIRMA => array('id' => $nameForm . '_GesSegnaturaFirma', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-ingranaggio-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_APRI_TESTOBASE => array('id' => $nameForm . '_ApriTestoBase', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-testobase-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_COPIA_ANALOGICA => array('id' => $nameForm . '_CreaCopiaAnalogica', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-timbro-32x32'", 'model' => $nameForm),
            self::ALL_FUNC_GESTEXT => array('id' => $nameForm . '_GestEstensione', "style" => $StyleBottoni, 'metaData' => "iconLeft:'ita-icon-edit-32x32'", 'model' => $nameForm)
        );

        /*
         * Caso di un Allegato Senza estensione, provvisorio.
         */
        if (!$ext && !$allegato['ROWID']) {
            $arrayBottoni = array();
            $arrayBottoni[self::ALL_FUNC_GESTEXT] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_GESTEXT];
            Out::msgQuestion("Gestione Allegato", '', $arrayBottoni, 'auto', 'auto', 'true', false, true, true);
            Out::html($nameForm . '_SegnaDocumento_lbl', $MessSegnatura, 'append');
            return;
        }

        $docmeta = unserialize($allegato['DOCMETA']);
        $anaent_35 = $this->proLib->GetAnaent('35');
        $anaent_47 = $this->proLib->GetAnaent('47');

        if (strtolower($ext) == "pdf" && $propar === 'A') {
            if ($anaent_35['ENTDE2'] != '' && $anaent_35['ENTDE3'] != '') {
                if ($docmeta['SEGNATURA'] !== true) {
                    $arrayBottoni = array(
                        self::ALL_FUNC_MARCA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_MARCA]
                    );
                    if ($anaent_47['ENTDE1']) {
                        $arrayBottoni[self::ALL_FUNC_GESTSEGNATURA] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_GESTSEGNATURA];
                        unset($arrayBottoni[self::ALL_FUNC_MARCA]);
                    }
                    if ($allegato['DOCROWIDBASE']) {
                        $AnadocRowidBase_rec = $this->proLib->GetAnadoc($allegato['DOCROWIDBASE'], 'rowid');
                        if (strtolower(pathinfo($AnadocRowidBase_rec['DOCFIL'], PATHINFO_EXTENSION) == 'xhtml')) {
                            $arrayBottoni[self::ALL_FUNC_APRI_TESTOBASE] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_APRI_TESTOBASE];
                        }
                    }
                    /* Copia Analogica Documenti */
                    $arrayBottoni[self::ALL_FUNC_COPIA_ANALOGICA] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_COPIA_ANALOGICA];
                    $arrayBottoni = $this->CheckBottoniConservazione($arrayBottoni, $pronum, $propar);
                    Out::msgQuestion("Gestione Allegato", $messaggio, $arrayBottoni, 'auto', 'auto', 'true', false, true, true);
                    Out::html($nameForm . '_SegnaDocumento_lbl', $MessSegnatura, 'append');
                } else {
                    /* Nuova per modelli base */
                    if ($allegato['DOCROWIDBASE']) {
                        $AnadocRowidBase_rec = $this->proLib->GetAnadoc($allegato['DOCROWIDBASE'], 'rowid');
                        if (strtolower(pathinfo($AnadocRowidBase_rec['DOCFIL'], PATHINFO_EXTENSION) == 'xhtml')) {
                            $arrayBottoni[self::ALL_FUNC_APRI_TESTOBASE] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_APRI_TESTOBASE];
                        }
                    }
                    /* Copia Analogica Documenti */
                    $arrayBottoni[self::ALL_FUNC_COPIA_ANALOGICA] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_COPIA_ANALOGICA];
                    $arrayBottoni = $this->CheckBottoniConservazione($arrayBottoni, $pronum, $propar);
                    Out::msgQuestion("Gestione Allegato", $messaggio, $arrayBottoni, 'auto', 'auto', 'true', false, true, true);
                    Out::html($nameForm . '_SegnaDocumento_lbl', $MessSegnatura, 'append');
                }
            }
            return;
        }
        $docfirma_check = $this->GetDocfirma($allegato['ROWID'], 'rowidanadoc');

        if (strtolower($ext) == "pdf" && $docmeta['SEGNATURA'] !== true && $anaent_35['ENTDE2'] != '' && $anaent_35['ENTDE3'] != '') {
            if (!$docfirma_check) {
                $arrayBottoni = array(
                    self::ALL_FUNC_MARCA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_MARCA],
                    self::ALL_FUNC_MARCA_E_DA_FIRMARE => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_MARCA_E_DA_FIRMARE],
                    self::ALL_FUNC_DA_FIRMARE => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_DA_FIRMARE],
                    self::ALL_FUNC_FIRMA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_FIRMA],
                    self::ALL_FUNC_COPIA_ANALOGICA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_COPIA_ANALOGICA]/* Copia Analogica */
                );
                $flMarcatura = true;
                $flMarcaturaFirma = true;
            } else {
                $arrayBottoni = array(
                    self::ALL_FUNC_MARCA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_MARCA],
                    self::ALL_FUNC_TOGLI_DA_FIRMARE => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_TOGLI_DA_FIRMARE],
                    self::ALL_FUNC_COPIA_ANALOGICA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_COPIA_ANALOGICA]/* Copia Analogica *///,
//                    self::ALL_FUNC_FIRMA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_FIRMA]
                );
                $flMarcatura = true;
            }
        } elseif (strtolower($ext) == "p7m") {
            $FilePathCopy = $this->CopiaDocAllegato($allegato['ROWID'], '', true);
            $Anadoc_rec = $this->proLib->GetAnadoc($allegato['ROWID'], 'ROWID');
            $Anapro_rec = $this->proLib->GetAnapro($Anadoc_rec['DOCNUM'], 'codice', $Anadoc_rec['DOCPAR']);

            $ParamCopiaAnalogica = array();
            $ParamCopiaAnalogica['ANAPRO_REC'] = $Anapro_rec;
            $ParamCopiaAnalogica['ANADOC_REC'] = $Anadoc_rec;
            // Qui passo model ed event.
            $ParamCopiaAnalogica['CustomReturnModel'] = 'proWizardCopiaAnalogica';
            $ParamCopiaAnalogica['CustomReturnEvent'] = 'openform';
            $this->VisualizzaFirme($FilePathCopy, $allegato['DOCNAME'], array(), $ParamCopiaAnalogica);
            return;
        } else {
            if (!$docfirma_check) {
                $arrayBottoni = array(
                    self::ALL_FUNC_DA_FIRMARE => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_DA_FIRMARE],
                    self::ALL_FUNC_FIRMA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_FIRMA],
                    self::ALL_FUNC_COPIA_ANALOGICA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_COPIA_ANALOGICA]
                );
            } else {
                $arrayBottoni = array(
                    self::ALL_FUNC_TOGLI_DA_FIRMARE => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_TOGLI_DA_FIRMARE],
                    self::ALL_FUNC_COPIA_ANALOGICA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_COPIA_ANALOGICA]////,
//                    self::ALL_FUNC_FIRMA => $ALL_FUNC_TEMPLATE[self::ALL_FUNC_FIRMA]
                );
            }
        }
        if ($allegato['DOCROWIDBASE']) {
            $AnadocRowidBase_rec = $this->proLib->GetAnadoc($allegato['DOCROWIDBASE'], 'rowid');
            if (strtolower(pathinfo($AnadocRowidBase_rec['DOCFIL'], PATHINFO_EXTENSION) == 'xhtml')) {
                $arrayBottoni[self::ALL_FUNC_APRI_TESTOBASE] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_APRI_TESTOBASE];
            }
        }
        if ($flagInviati === true) {
            unset($arrayBottoni[self::ALL_FUNC_MARCA]);
            unset($arrayBottoni[self::ALL_FUNC_MARCA_E_DA_FIRMARE]);
            unset($arrayBottoni[self::ALL_FUNC_DA_FIRMARE]);
            unset($arrayBottoni[self::ALL_FUNC_FIRMA]);
            unset($arrayBottoni[self::ALL_FUNC_TOGLI_DA_FIRMARE]);
            unset($arrayBottoni[self::ALL_FUNC_APRI_TESTOBASE]);
            $flMarcatura = false;
            $flMarcaturaFirma = false;
        }
        if ($flMarcatura && $anaent_47['ENTDE1']) {
            $arrayBottoni[self::ALL_FUNC_GESTSEGNATURA] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_GESTSEGNATURA];
            unset($arrayBottoni[self::ALL_FUNC_MARCA]);
            //$arrayBottoni[self::ALL_FUNC_MARCA] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_GESTSEGNATURA];
        }
        if ($flMarcaturaFirma && $anaent_47['ENTDE1']) {
            $arrayBottoni[self::ALL_FUNC_GESTSEGNATURA_FIRMA] = $ALL_FUNC_TEMPLATE[self::ALL_FUNC_GESTSEGNATURA_FIRMA];
            unset($arrayBottoni[self::ALL_FUNC_MARCA_E_DA_FIRMARE]);
        }

        $anaent_55 = $this->proLib->GetAnaent('55');

        if ($anaent_55['ENTDE2']) {
            /*
             * Qui inizia personalizzazione: Controllo
             */
            include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
            $devLib = new devLib();
            $ParametroProtUte = $devLib->getEnv_config('PROTUTEALLAFIRMA', 'codice', 'UTEALLAFIRMA', false);
            $UtentiAttivaAllaFirma = $ParametroProtUte['CONFIG'];
            //$UtentiAttivaAllaFirma = '.wsitalsuap.';
            $AnaproSave_tab = $this->proLib->GetAnaproSave($pronum, $propar);
            if ($AnaproSave_tab) {
                $Anapro_rec = $AnaproSave_tab[0];
            } else {
                $Anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
            }
            if (strpos($UtentiAttivaAllaFirma, '.' . $Anapro_rec['PROUTE'] . '.') === false || !$UtentiAttivaAllaFirma) {
                unset($arrayBottoni[self::ALL_FUNC_FIRMA]);
                unset($arrayBottoni[self::ALL_FUNC_DA_FIRMARE]);
                unset($arrayBottoni[self::ALL_FUNC_MARCA_E_DA_FIRMARE]);
            }
        }

        if ($arrayBottoni) {
            $arrayBottoni = $this->CheckBottoniConservazione($arrayBottoni, $pronum, $propar);
            $arrayBottoni = $this->CheckBottoniAnnullato($arrayBottoni, $pronum, $propar);
            Out::msgQuestion("Gestione Allegato", $messaggio, $arrayBottoni, 'auto', 'auto', 'true', false, true, true);
            Out::html($nameForm . '_SegnaDocumento_lbl', $MessSegnatura, 'append');
        } else {
            Out::msgInfo("Funzione Allegato.", "Non ci sono funzioni disponibili.");
        }
        return;
    }

    public function CheckBottoniConservazione($arrayBottoni, $pronum, $propar) {
        if ($pronum && $propar) {
            if ($this->proLibConservazione->CheckProtocolloVersato($pronum, $propar)) {
                unset($arrayBottoni[self::ALL_FUNC_MARCA]);
                unset($arrayBottoni[self::ALL_FUNC_MARCA_E_DA_FIRMARE]);
                unset($arrayBottoni[self::ALL_FUNC_DA_FIRMARE]);
                unset($arrayBottoni[self::ALL_FUNC_TOGLI_DA_FIRMARE]);
                unset($arrayBottoni[self::ALL_FUNC_FIRMA]);
                unset($arrayBottoni[self::ALL_FUNC_GESTSEGNATURA]);
                unset($arrayBottoni[self::ALL_FUNC_GESTSEGNATURA_FIRMA]);
                unset($arrayBottoni[self::ALL_FUNC_APRI_TESTOBASE]);
            }
        }
        return $arrayBottoni;
    }

    public function CheckBottoniAnnullato($arrayBottoni, $pronum, $propar) {
        if ($pronum && $propar) {
            if ($this->proLib->CheckProtAnnullato($pronum, $propar)) {
                unset($arrayBottoni[self::ALL_FUNC_MARCA]);
                unset($arrayBottoni[self::ALL_FUNC_MARCA_E_DA_FIRMARE]);
                unset($arrayBottoni[self::ALL_FUNC_DA_FIRMARE]);
                unset($arrayBottoni[self::ALL_FUNC_TOGLI_DA_FIRMARE]);
                unset($arrayBottoni[self::ALL_FUNC_FIRMA]);
                unset($arrayBottoni[self::ALL_FUNC_GESTSEGNATURA]);
                unset($arrayBottoni[self::ALL_FUNC_GESTSEGNATURA_FIRMA]);
                unset($arrayBottoni[self::ALL_FUNC_APRI_TESTOBASE]);
            }
        }
        return $arrayBottoni;
    }

    public function checkAbilitazioneAllaFirma($pronum, $propar) {
        $profilo = proSoggetto::getProfileFromIdUtente();
        $firmatario = $this->proLib->getGenericTab("SELECT ROWID FROM ARCITE 
                            WHERE 
                            ITEPRO=$pronum AND ITEPAR='$propar' AND ITEDES='{$profilo['COD_SOGGETTO']}' AND (ITENODO='INS' OR ITENODO='MIT')", false);
        if ($firmatario) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param itaModel $model 
     * @param type $numeroProtocollo
     * @param type $tipoProt
     * @param type $proArriAlle
     * @param type $procon
     * @param type $pronom
     * @return boolean
     */
    public function GestioneAllegati($model, $numeroProtocollo, $tipoProt, $proArriAlle, $procon, $pronom, $objProtocollo = null) {
        $this->gestioneAllegatiResult = array();
        $this->errCode = 0;
        $this->noteMessage = array();
        $destinazione = $this->proLib->SetDirectory($numeroProtocollo, $tipoProt);
        if (!$destinazione) {
            $this->errCode = -1;
            $this->setErrMessage("Archiviazione File. Errore creazione cartella di destinazione.");
            return false;
        }
        // Funzione per controllare se c'è il fle principale:
//        $AllegatoPrincipale = false;
//        foreach ($proArriAlle as $AllegatoproArriAlle) {
//            if ($AllegatoproArriAlle['DOCTIPO'] == '') {
//                $AllegatoPrincipale = true;
//                break;
//            }
//        }
        // 

        /*
         * Controllo utilizzo di ALFRESCO 
         * per salvare i documenti.
         */
        $anaent_49 = $this->proLib->GetAnaent('49');
        if ($anaent_49['ENTDE1']) {
            /*
             * Lettura dei Librerie e Parametri Necessari:
             */
            $itaDocumentaleAlfrescoUtils = new itaDocumentaleAlfrescoUtils();
            $utiEnte = new utiEnte();
            $anaent_26 = $this->proLib->GetAnaent('26');
            $ParametriEnte_rec = $utiEnte->GetParametriEnte();
            /* Setto Parametri per Salvataggio File */
            $itaDocumentaleAlfrescoUtils->setCodiceAoo($anaent_26['ENTDE2']);
            $itaDocumentaleAlfrescoUtils->setCodiceEnte($ParametriEnte_rec['ISTAT']);
            $itaDocumentaleAlfrescoUtils->setCodiceUtente(App::$utente->getKey('nomeUtente'));
            $itaDocumentaleAlfrescoUtils->setDescrizioneEnte($ParametriEnte_rec['DENOMINAZIONE']);
        }
        if ($objProtocollo === null) {
            $numero = substr($numeroProtocollo, 4);
            $anno = substr($numeroProtocollo, 0, 4);
            $objProtocollo = proProtocollo::getInstance($this->proLib, $numero, $anno, $tipoProt, '');
        }

        $rowidAggiunti = array();
        $rowidAggiornati = array();

        $NumAllegato = 0;
        $UuidFattura = '';
        $mettiAllaFirma = false;

        foreach ($proArriAlle as $allegato) {
            /*
             * Controllo se deve essere salvato su Alfresco
             */
            $SalvaSuAlfresco = true;
            if ($anaent_49['ENTDE4']) {
                if ($allegato['TIPO_ALLEGATO'] != self::ALLEGATO_FATT && $allegato['TIPO_ALLEGATO'] != self::ALLEGATO_ANNESSO_FATT) {
                    $SalvaSuAlfresco = false;
                }
            }

            if ($allegato['ROWID'] == 0) {
                /*
                 * Salvataggio su Server:
                 * Solo se non è indicato un FILEREL
                 */
                if (!$allegato['FILEREL']) {
                    if (!rename($allegato['FILEPATH'], $destinazione . "/" . $allegato['FILENAME'])) {
                        $this->errCode = -1;
                        $this->setErrMessage("Archiviazione File Fisico. Errore in salvataggio del file " . $allegato['DOCNAME'] . " !");
                        return false;
                    }
                }
                /*
                 * Controllo se devo salvare anche su Alfresco
                 * Condizione aggiuntiva: solo se non è indicato un FILEREL 
                 */
                $Uuid = '';
                if ($anaent_49['ENTDE1'] && $SalvaSuAlfresco == true && !$allegato['FILEREL']) {
                    /*
                     * Utilizio Salvataggio su Alfresco
                     */
                    if (!$allegato['DOCUUID']) {
                        $content = file_get_contents($destinazione . "/" . $allegato['FILENAME']);
//                    Out::msgInfo('contenut',print_r($content,true));

                        switch ($allegato['TIPO_ALLEGATO']) {
                            case self::ALLEGATO_FATT:
                                $itaDocumentaleAlfrescoUtils->setDizionario($objProtocollo->GetDizionarioDocumentale($allegato['TIPO_ALLEGATO'], $allegato['DOCNAME']));
                                $retIns = $itaDocumentaleAlfrescoUtils->inserisciFlussoCP($allegato['DOCNAME'], $content);
                                break;

                            case self::ALLEGATO_ANNESSO_FATT:
                                if (!$UuidFattura) {
                                    $Anapro_rec = $this->proLib->GetAnapro($numeroProtocollo, 'codice', $tipoProt);
                                    $UuidFattura = $this->GetUuidPadreFattura($Anapro_rec, $allegato['DOCNAME']);
                                    if (!$UuidFattura) {
                                        return false;
                                    }
                                }
                                $itaDocumentaleAlfrescoUtils->setDizionario($objProtocollo->GetDizionarioDocumentale($allegato['TIPO_ALLEGATO'], $allegato['DOCNAME'], $UuidFattura));
                                $retIns = $itaDocumentaleAlfrescoUtils->inserisciAnnessoCP($allegato['DOCNAME'], $content);
                                break;

                            default:
                            case self::ALLEGATO_PROT:
                                $itaDocumentaleAlfrescoUtils->setDizionario($objProtocollo->GetDizionarioDocumentale($allegato['TIPO_ALLEGATO'], $allegato['DOCNAME']));
                                $retIns = $itaDocumentaleAlfrescoUtils->inserisciProtocollo($allegato['DOCNAME'], $content);
                                break;
                        }
                        if (!$retIns) {
                            $this->errCode = -1;
                            $this->setErrMessage("Archiviazione. Errore in salvataggio su alfresco del file " . $allegato['FILENAME'] . ". " . $itaDocumentaleAlfrescoUtils->getErrMessage());
                            $this->noteMessage[] = "Archiviazione. Errore in salvataggio su alfresco del file " . $allegato['FILENAME'] . ". " . $itaDocumentaleAlfrescoUtils->getErrMessage();
//return false;
                        } else {
                            $Uuid = $itaDocumentaleAlfrescoUtils->getResult();
                        }
                        if ($allegato['TIPO_ALLEGATO'] == self::ALLEGATO_FATT) {
                            $UuidFattura = $Uuid;
                        }
                    } else {
                        $Uuid = $allegato['DOCUUID'];
                    }
                }
                //
                if ($allegato['DOCUUID']) {
                    $Uuid = $allegato['DOCUUID'];
                }

                $anadoc_rec = array();
                $iteKey = $this->proLib->IteKeyGenerator($numeroProtocollo, '', date('Ymd'), $tipoProt);
                if (!$iteKey) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($this->proLib->getErrMessage());
                    return false;
                }

                if ($allegato['FILEREL']) {
                    $FileRel = $allegato['FILEREL'];
                    $anadoc_rec['DOCRELCLASSE'] = $FileRel['REL_CLASSE'];
                    $anadoc_rec['DOCRELCHIAVE'] = $FileRel['REL_CHIAVE'];
                    $anadoc_rec['DOCRELUUID'] = itaUUID::getV4();
                    // Genero DocFil per i rel
                }

                $anadoc_rec['DOCKEY'] = $iteKey;
                $anadoc_rec['DOCNUM'] = $numeroProtocollo;
                $anadoc_rec['DOCPAR'] = $tipoProt;
                $anadoc_rec['DOCFIL'] = $allegato['FILENAME'];
                if ($allegato['DOCLNK']) {
                    $anadoc_rec['DOCLNK'] = $allegato['DOCLNK'];
                } else {
                    $anadoc_rec['DOCLNK'] = "allegato://" . $allegato['FILENAME'];
                }

                $anadoc_rec['DOCUTC'] = $procon;
                $anadoc_rec['DOCUTE'] = 'DA PROTOCOLLO: ' . $pronom;
                $anadoc_rec['DOCNOT'] = $allegato['FILEINFO'];
                $anadoc_rec['DOCTIPO'] = $allegato['DOCTIPO'];
                $anadoc_rec['DOCDAFIRM'] = $allegato['DOCDAFIRM'];


                /*
                 * Calcolo sha per ora solo se non presente FILEREL
                 */
                if ($allegato['FILEREL']) {
                    $anadoc_rec['DOCMD5'] = '';
                    $anadoc_rec['DOCSHA2'] = $allegato['FILEREL']['REL_SHA256'];
                } else {
                    $anadoc_rec['DOCMD5'] = md5_file($destinazione . "/" . $allegato['FILENAME']);
                    $anadoc_rec['DOCSHA2'] = hash_file('sha256', $destinazione . "/" . $allegato['FILENAME']);
                }
                $anadoc_rec['DOCNAME'] = $allegato['DOCNAME'];
                if ($anadoc_rec['DOCNAME'] == '') {
                    $anadoc_rec['DOCNAME'] = $anadoc_rec['DOCFIL'];
                }
                if ($allegato['DOCFDT']) {
                    $anadoc_rec['DOCFDT'] = $allegato['DOCFDT'];
                } else {
                    $anadoc_rec['DOCFDT'] = date('Ymd');
                }
                $anadoc_rec['DOCFTM'] = $allegato['DOCFTM'];
                $anadoc_rec['DOCSTA'] = $allegato['DOCSTA'];
                $anadoc_rec['DOCORF'] = $allegato['DOCORF'];
                $anadoc_rec['DOCRELEASE'] = $allegato['DOCRELEASE'];
                $anadoc_rec['DOCIDMAIL'] = $allegato['DOCIDMAIL'];
                $anadoc_rec['DOCSERVIZIO'] = $allegato['DOCSERVIZIO'];
                $anadoc_rec['DOCEVI'] = $allegato['DOCEVI'];
                $anadoc_rec['DOCLOCK'] = $allegato['DOCLOCK'];
                $anadoc_rec['DOCCLA'] = $allegato['DOCCLA'];
                $anadoc_rec['DOCCLAS'] = $allegato['DOCCLAS'];
                $anadoc_rec['DOCDEST'] = $allegato['DOCDEST'];
                $anadoc_rec['DOCNOTE'] = $allegato['DOCNOTE'];
                $anadoc_rec['DOCMETA'] = $allegato['DOCMETA'];
                $anadoc_rec['DOCDATAFIRMA'] = $allegato['DOCDATAFIRMA'];
                if ($allegato['DOCUTELOG']) {
                    $anadoc_rec['DOCUTELOG'] = $allegato['DOCUTELOG'];
                } else {
                    $anadoc_rec['DOCUTELOG'] = App::$utente->getKey('nomeUtente');
                }
                if ($allegato['DOCDATADOC']) {
                    $anadoc_rec['DOCDATADOC'] = $allegato['DOCDATADOC'];
                } else {
                    $anadoc_rec['DOCDATADOC'] = date('Ymd');
                }
                if ($allegato['DOCORADOC']) {
                    $anadoc_rec['DOCORADOC'] = $allegato['DOCORADOC'];
                } else {
                    $anadoc_rec['DOCORADOC'] = date('H:i:s');
                }
                /* Modello Base */
                $anadoc_rec['DOCROWIDBASE'] = '';
                if ($allegato['DOCROWIDBASE']) {
                    $anadoc_rec['DOCROWIDBASE'] = $allegato['DOCROWIDBASE'];
                }
                $anadoc_rec['DOCSUBTIPO'] = '';
                if ($allegato['DOCSUBTIPO']) {
                    $anadoc_rec['DOCSUBTIPO'] = $allegato['DOCSUBTIPO'];
                }
                if ($allegato['DOCLOG']) {
                    $anadoc_rec['DOCLOG'] = $allegato['DOCLOG'];
                }
                /* Valorizzazione del codice UUid */
                $anadoc_rec['DOCUUID'] = $Uuid;

                try {
                    $insert_Info = 'Inserimento: ' . $anadoc_rec['DOCKEY'] . ' ' . $anadoc_rec['DOCFIL'];

                    if (!$model->insertRecord($this->proLib->getPROTDB(), 'ANADOC', $anadoc_rec, $insert_Info)) {
                        $this->errCode = -1;
                        $this->setErrMessage("Archiviazione File. Errore Inserimento Record.");
                        return false;
                    }
                    $anadoc_rec['ROWID'] = $model->getLastInsertId();
                    $rowidAggiunti[] = $anadoc_rec['ROWID'];
                } catch (Exception $e) {
                    $this->errCode = -1;
                    $this->setErrMessage("Archiviazione File. Errore Inserimento Record. " . $e->getMessage());
                    return false;
                }
                if ($allegato['ROWIDORIGINE']) {
                    $delete_Info = "Oggetto: rimuovo indice documento {$allegato['ROWIDORIGINE']} di provenienza protocollazione assistita";
                    $model->deleteRecord($this->proLib->getPROTDB(), 'ANADOC', $allegato['ROWIDORIGINE'], $delete_Info);
                }
            } else {
                $anadoc_rec = $this->proLib->GetAnadoc($allegato['ROWID'], 'ROWID');
                $anadoc_rec['DOCNOT'] = $allegato['FILEINFO'];
                $anadoc_rec['DOCTIPO'] = $allegato['DOCTIPO'];
                $anadoc_rec['DOCDAFIRM'] = $allegato['DOCDAFIRM'];
                try {
                    $update_Info = 'Oggetto: ' . $anadoc_rec['DOCFIL'] . " " . $anadoc_rec['DOCNOT'];
                    if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANADOC', $anadoc_rec, $update_Info)) {
                        $this->errCode = -1;
                        $this->setErrMessage("Archiviazione File. Errore Aggiornamento Record.");
                        return false;
                    }
                    $rowidAggiornati[] = $anadoc_rec['ROWID'];
                } catch (Exception $e) {
                    $this->errCode = -1;
                    $this->setErrMessage("Archiviazione File. Errore Inserimento Record. " . $e->getMessage());
                    return false;
                }
            }
            $this->setRisultatoRitorno(
                    array('ROWIDAGGIUNTI' => $rowidAggiunti), array('ROWIDAGGIORNATI' => $rowidAggiornati)
            );
            if ($allegato['METTIALLAFIRMA']) {
                $this->DaFirmare($model, $allegato['METTIALLAFIRMA'], $anadoc_rec['ROWID']);
                $mettiAllaFirma = true;
            }
        }
        $anapro_rec = $this->proLib->GetAnapro($numeroProtocollo, 'codice', $tipoProt);
        $iter = proIter::getInstance($this->proLib, $anapro_rec);
        if ($iter && $mettiAllaFirma) {
            $iter->sincronizzaIterFirma('aggiungi');
        }
        itaLib::deletePrivateUploadPath();
        $this->setRisultatoRitorno(
                array('ROWIDAGGIUNTI' => $rowidAggiunti), array('ROWIDAGGIORNATI' => $rowidAggiornati)
        );
        // Salvo note errore se presenti.
        $noteMessage = $this->getNoteMessage();
        if ($noteMessage) {
            $this->salvaNoteMessage($anapro_rec);
        }
        return true;
    }

    public function AggiornAllegato($model, $allegato) {
        $anadoc_rec = $this->proLib->GetAnadoc($allegato['ROWID'], 'ROWID');
        $anadoc_rec['DOCNOT'] = $allegato['FILEINFO'];
        $anadoc_rec['DOCTIPO'] = $allegato['DOCTIPO'];
        $anadoc_rec['DOCDAFIRM'] = $allegato['DOCDAFIRM'];
        try {
            $update_Info = 'Oggetto: ' . $anadoc_rec['DOCFIL'] . " " . $anadoc_rec['DOCNOT'];
            if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANADOC', $anadoc_rec, $update_Info)) {
                $this->errCode = -1;
                $this->setErrMessage("Archiviazione File. Errore Aggiornamento Record.");
                return false;
            }
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->setErrMessage("Archiviazione File. Errore Inserimento Record. " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function SegnaDocumento($model, $destFile, $anapro_rec, $anadoc_rowid, $PosMarcat = array()) {
        $retSegnaPdf = $this->SegnaPDF($model, $destFile, $anapro_rec, $anadoc_rowid, $PosMarcat);
        if (!$retSegnaPdf) {
            Out::msgStop("Attenzione!", $this->getErrMessage());
            return false;
        }
        Out::msgBlock('', 2000, true, "Documento Marcato Correttamente con la Segnatura.");
        return true;
    }

    public function SegnaPDF($model, $destFile, $anapro_rec, $anadoc_rowid, $PosMarcat = array()) {
        $output = $this->ComponiPDFconSegnatura($anapro_rec, $destFile, $PosMarcat);
        if (!$output) {
            $errMsg = $this->getErrMessage();
            $this->setErrCode(-1);
            $this->setErrMessage("$errMsg <br><br>Marcatura del documento impossibile.");
            return false;
        }
        if (!@rename($output, $destFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in salvataggio del file con Segnatura!");
            return false;
        }

        $anadoc_rec = $this->proLib->GetAnadoc($anadoc_rowid, 'rowid');
        /*
         *  Inserisco l'anadocsave
         */
        $savedata = date('Ymd');
        $saveora = date('H:i:s');
        $saveutente = App::$utente->getKey('nomeUtente');
        $anadocSave_rec = $anadoc_rec;
        $anadocSave_rec['ROWID'] = '';
        $anadocSave_rec['SAVEDATA'] = $savedata;
        $anadocSave_rec['SAVEORA'] = $saveora;
        $anadocSave_rec['SAVEUTENTE'] = $saveutente;
        if (!$model->insertRecord($this->proLib->getPROTDB(), 'ANADOCSAVE', $anadocSave_rec, '', 'ROWID', false)) {
            Out::msgStop("Firma File", "Errore in salvataggio ANADOCSAVE.");
            return false;
        }
        $anadoc_rec['DOCUUID'] = '';
        /* Se attivo parametri alfresco - salvo su alfresco */
        $anaent_49 = $this->proLib->GetAnaent('49');
        if ($anaent_49['ENTDE1']) {
            $Uuid = $this->AggiungiAllegatoAlfresco($anapro_rec, $destFile, $anadoc_rec['DOCNAME']);
            if (!$Uuid) {
                return false;
            }
            $anadoc_rec['DOCUUID'] = $Uuid;
        }
        $anadoc_rec['DOCFDT'] = date('Ymd');
        $anadoc_rec['DOCMD5'] = md5_file($destFile);
        $anadoc_rec['DOCSHA2'] = hash_file('sha256', $destFile);
        $docmeta = unserialize($anadoc_rec['DOCMETA']);
        $docmeta['SEGNATURA'] = true;

        $anadoc_rec['DOCMETA'] = serialize($docmeta);
        $update_Info = 'Oggetto: ' . $anadoc_rec['DOCFIL'] . " " . $anadoc_rec['DOCNOT'];
        if (!$model->updateRecord($this->proLib->getPROTDB(), 'ANADOC', $anadoc_rec, $update_Info)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in salvataggio del recond file con Segnatura!");
            return false;
        }

        return true;
    }

    public function ComponiPDFconSegnatura($anapro_rec, $input, $PosMarcat = array(), $endorserParams = array()) {
        /*
         * Controllo se è un documento pdf firmato internamente
         * Si potrebbe prevedere un parametro per la creazione comunque di una copia: Caso di "Copia Analogica".
         * Occorre abilitarla tramite parametro?
         */
//        if ($this->CheckPdfFirmato_pkcs7($input)) {
//            $this->setErrCode(-1);
//            $this->setErrMessage("PDF firmato digitalmente. Non è possibile alterare un PDF firmato.");
//            return false;
//        }
        $itaPDFHelper = new itaPDFUtils();
        $CountFirme = $itaPDFHelper->hasSignatures($input);
        if ($CountFirme != 0) {
            $this->setErrCode(-1);
            $this->setErrMessage("PDF firmato digitalmente. Non è possibile alterare un PDF firmato.");
            return false;
        }



        // Prendo il default per il tipo protocollo.
        if (!$PosMarcat) {
            $PosMarcat = $this->GetPosMarcaturaFromTipoProt($anapro_rec['PROPAR']);
        }
        if (!$endorserParams) {
            $endorserParams = $this->proLib->getScannerEndorserParams($anapro_rec);
        }
        $ret = '';
        $xmlPATH = itaLib::createAppsTempPath('praPDFComposer');
        $xmlFile = $xmlPATH . "/" . md5(rand() * time()) . ".xml";
        $xmlRes = fopen($xmlFile, "w");
        if (!file_exists($xmlFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Componi PDF - Errore in composizione PDF!");
            //Out::msgStop("Componi PDF", "Errore in composizione PDF");// 
            return false;
        } else {
            $output = $xmlPATH . "/" . md5(rand() * time()) . "." . pathinfo($input, PATHINFO_EXTENSION);
            $xml = $this->CreaXmlPdf($endorserParams['CAP_PRINTERSTRING'], $input, $output, $PosMarcat);
            fwrite($xmlRes, $xml);
            fclose($xmlRes);
            $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF/itaJPDF.jar ' . $xmlFile;
            exec($command, $ret);
            $taskXml = false;

            foreach ($ret as $value) {
                $arrayExec = explode("|", $value);
                if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                    $taskXml = true;
                    break;
                }
            }
            if ($taskXml == false) {
                return false;
            } else {
                return $output;
            }
        }
    }

    public function CreaXmlPdf($testo, $input, $output, $forcePos = array()) {
        $ParamSegn = array();
        if ($forcePos) {
            $ParamSegn = $forcePos;
        } else {
            $anaent_35 = $this->proLib->GetAnaent('35');
            $ParamSegn['FIRST_PAGE'] = $anaent_35['ENTDE1'];
            $ParamSegn['X_COORD'] = $anaent_35['ENTDE2'];
            $ParamSegn['Y_COORD'] = $anaent_35['ENTDE3'];
            $ParamSegn['ROTAZ'] = $anaent_35['ENTDE4'];
        }

        $xml .= "<root>\r\n";
        $xml .= "   <task name=\"watermark\">\r\n";
        $xml .= "        <debug>0</debug>\r\n";
        $xml .= "		<firstpageonly>" . (int) $ParamSegn['FIRST_PAGE'] . "</firstpageonly>\r\n";
        $xml .= "		<x-coord>" . (int) $ParamSegn['X_COORD'] . "</x-coord>\r\n";
        $xml .= "		<y-coord>" . (int) $ParamSegn['Y_COORD'] . "</y-coord>\r\n";
        $xml .= "		<rotation>" . (int) $ParamSegn['ROTAZ'] . "</rotation>\r\n";
        if ($forcePos['FONT-SIZE']) {
            $xml .= "		<font-size>" . (int) $ParamSegn['FONT-SIZE'] . "</font-size>\r\n";
        }
        $xml .= "        <string>$testo</string>\r\n";
        $xml .= "        <input>$input</input>\r\n";
        $xml .= "        <output>$output</output>\r\n";
        $xml .= "    </task>\r\n";
        $xml .= "</root>\r\n";
        return $xml;
    }

    public function DaFirmare($model, $anades_mitt, $anadoc_rowid) {
        if (!$anades_mitt['DESCOD'] || !$anades_mitt['DESCUF'] || !$anadoc_rowid) {
            return;
        }
        $anadoc_rec = $this->proLib->GetAnadoc($anadoc_rowid, "ROWID");
        if (!$anadoc_rec) {
            Out::msgStop("Attenzione", "Record del documento errato.");
            return;
        }
        $docfirma_tab = $this->GetDocfirma($anadoc_rec['ROWID'], 'rowidanadoc', true);
        if ($docfirma_tab) {
            Out::msgStop("Attenzione", "Verifica la richiesta di firma.");
            return;
        }
        $docfirma_rec = array();
        $profilo = proSoggetto::getProfileFromIdUtente();
        $docfirma_rec['FIRCODRICH'] = $profilo['COD_SOGGETTO'];
        $docfirma_rec['FIRDATARICH'] = date('Ymd');
        $docfirma_rec['FIRCOD'] = $anades_mitt['DESCOD'];
        $docfirma_rec['FIRUFF'] = $anades_mitt['DESCUF'];
        $docfirma_rec['ROWIDANADOC'] = $anadoc_rec['ROWID'];
        $docfirma_rec['ROWIDARCITE'] = 0;
        $docfirma_rec['FIRDATA'] = "";
        $docfirma_rec['FIRORA'] = "";
        $insert_Info = 'Oggetto: Inserimento richiesta di firma' . $anadoc_rec['DOCKEY'];
        if (!$model->insertRecord($this->proLib->getPROTDB(), 'DOCFIRMA', $docfirma_rec, $insert_Info)) {
            Out::msgStop("Attenzione", "Richiesta di firma non inserita");
            return;
        }
    }

    public function ScriviFileXml($indice) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proInteropMsg.class.php';
        $Anapro_rec = $this->proLib->GetAnapro($indice, 'rowid');
        $chiaveProtocollo = array('PRONUM' => $Anapro_rec['PRONUM'], 'PROPAR' => $Anapro_rec['PROPAR']);
        $InteropMsg = proInteropMsg::getInteropInstanceUscita($chiaveProtocollo, proInteropMsg::TIPOMSG_SEGNATURA);
        if ($InteropMsg->getErrCode() == -1) {
            $risultato = array('stato' => '-2', 'messaggio' => "File Segnatura.xml non inserito nell'invio.<br><br><br>La mail è comunque corretta ma non interoperabile.<br>Si consiglia di segnalare l'anomalia al servizio di assistenza.<br>" . $InteropMsg->getErrMessage());
            return $risultato;
        }
        return $InteropMsg->getPathFileMessaggio();
    }

    public function ScriviFileXMLOld($indice) {
        $anapro_rec = $this->proLib->GetAnapro($indice, 'rowid');
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                $risultato = array('stato' => '-1', 'messaggio' => 'Creazione ambiente di lavoro temporaneo fallita.');
                return $risultato;
            }
        }
        $randName = "Segnatura.xml";
        $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
        $arrayXml = $this->proLib->getSegnaturaArray($anapro_rec);
        if ($arrayXml === false || $arrayXml['stato'] === false) {
            $risultato = array('stato' => '-2', 'messaggio' => "File Segnatura.xml non inserito nell'invio.<br><br><br>La mail è comunque corretta ma non interoperabile.<br>Si consiglia di segnalare l'anomalia al servizio di assistenza.");
            if ($arrayXml['messaggio']) {
                $risultato = array('stato' => '-1', 'messaggio' => $arrayXml['messaggio']);
            }
            return $risultato;
        }
        $xmlObj = new QXML;
        $rootTag = "";
        $xmlObj->noCDATA();
        $xmlObj->noAddslashesattr();
        $xmlObj->toXML($arrayXml, $rootTag);
        $fileXml = $xmlObj->getXml();
        $File = fopen($destFile, "w+");
        fwrite($File, "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>");
        fwrite($File, '<Segnatura>');
        fwrite($File, $fileXml);
        fwrite($File, "</Segnatura>");
        fclose($File);
        include_once ITA_BASE_PATH . '/apps/Protocollo/proSegnatura.class.php';
        $ret = proSegnatura::testSegnatura($destFile);
        if ($ret['Status'] != 0) {
            $risultato = array('stato' => '-2', 'messaggio' => "File Segnatura.xml non inserito nell'invio.<br><br><br>La mail è comunque corretta ma non interoperabile.<br>Si consiglia di segnalare l'anomalia al servizio di assistenza.");
            return $risultato;
        }
        return $destFile;
    }

    public function checkMarcati($pronum, $propar) {
        $anadoc_tab = $this->proLib->GetAnadoc($pronum, 'protocollo', true, $propar);
        $marcati = array();
        $nonMarcati = array();
        foreach ($anadoc_tab as $anadoc_rec) {
            if (strtolower(pathinfo($anadoc_rec['DOCFIL'], PATHINFO_EXTENSION) != 'pdf')) {
                continue;
            }
            $docmeta = unserialize($anadoc_rec['DOCMETA']);
            if ($docmeta['SEGNATURA'] === true) {
                $marcati[] = $anadoc_rec;
            } else {
                $nonMarcati[] = $anadoc_rec;
            }
        }
        return array('TOTALI' => count($anadoc_tab), 'MARCATI' => count($marcati), 'ELENCO_MARCATI' => $marcati, 'ELENCO_NON_MARCATI' => $nonMarcati);
    }

    public function SbustaP7m($FileFirmato, $NomeFileOriginale = '') {
        include_once(ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php');
        $p7m = itaP7m::getP7mInstance($FileFirmato);
        if (!$p7m) {
            $this->setErrCode(-1);
            $this->setErrMessage("Verifica File Firmato Fallita");
            return false;
        }
        if (!file_exists($p7m->getContentFileName())) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella estrazione file dal p7m.");
            return false;
        }
        $subPath = "proP7mCheck-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);

        $basename = pathinfo($p7m->getContentFileName(), PATHINFO_BASENAME);
        if ($NomeFileOriginale) {
            $NomeFile = itaLib::pathinfoFilename($NomeFileOriginale, PATHINFO_FILENAME);
            $ext = pathinfo($NomeFile, PATHINFO_EXTENSION);
            $basename .= '.' . $ext;
        }

        $DestinoFile = $tempPath . '/' . $basename;
        if (!@copy($p7m->getContentFileName(), $DestinoFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Sbusta File. Errore nella copia del file da p7m.");
            return false;
        }
        $p7m->cleanData();
        $elencoFirme = $p7m->getInfoSummary();
        // Preparo gli elementi che occorre far tornare..
        $returnData = array();
        $returnData['FILE'] = $DestinoFile;
        $returnData['ELENCO_FIRME'] = $elencoFirme;
        return $returnData;
    }

    public function AggiungiAllegato($model, $Anapro_Rec, $FilePath, $FileName = '', $Docuuid = '', $ExtraDati = array()) {
        if (!$FileName) {
            $FileName = pathinfo($FilePath, PATHINFO_BASENAME);
        }
        $allegati[] = array(
            'ROWID' => 0,
            'FILEPATH' => $FilePath,
            'FILENAME' => pathinfo($FilePath, PATHINFO_BASENAME),
            'FILEINFO' => "File originale : " . $FileName,
            'DOCNAME' => $FileName,
            'DOCTIPO' => 'ALLEGATO',
            'DOCFDT' => date('Ymd'),
            'DOCRELEASE' => '1',
            'DOCSERVIZIO' => $ExtraDati['DOCSERVIZIO'] != '' ? $ExtraDati['DOCSERVIZIO'] : 0,
            'DOCMETA' => "",
            'METTIALLAFIRMA' => '',
            'DOCUUID' => $Docuuid != '' ? $Docuuid : '',
            'DOCIDMAIL' => $ExtraDati['DOCIDMAIL'] != '' ? $ExtraDati['DOCIDMAIL'] : '',
            'FILEREL' => $ExtraDati['FILEREL'] != '' ? $ExtraDati['FILEREL'] : ''
        );

        $risultato = $this->GestioneAllegati($model, $Anapro_Rec['PRONUM'], $Anapro_Rec['PROPAR'], $allegati, $Anapro_Rec['PROCON'], $Anapro_Rec['PRONOM']);
        if (!$risultato) {
            return false;
        }
        return $risultato;
    }

    public function GetPosizioniSegnatura() {
        $anaent_47 = $this->proLib->GetAnaent('47');
        $PosizioniSegn = unserialize($anaent_47['ENTVAL']);
        return $PosizioniSegn;
    }

    public function GetPosMarcaturaFromTipoProt($TipoPro) {
        $anaent_47 = $this->proLib->GetAnaent('47');
        $anaent_35 = $this->proLib->GetAnaent('35');
        $anaent_55 = $this->proLib->GetAnaent('55');
        $PosizioniSegn = $this->GetPosizioniSegnatura();

        $ParamSegn['FIRST_PAGE'] = $anaent_35['ENTDE1'];
        $ParamSegn['X_COORD'] = $anaent_35['ENTDE2'];
        $ParamSegn['Y_COORD'] = $anaent_35['ENTDE3'];
        $ParamSegn['ROTAZ'] = $anaent_35['ENTDE4'];
        $ParamSegn['DESC'] = 'Posizione predefinita';
        switch (substr($TipoPro, 0, 1)) {
            case 'P':
            case 'C':
                if ($anaent_47['ENTDE3']) {
                    $ParamSegn = $PosizioniSegn[$anaent_47['ENTDE3']];
                }
                break;
            case 'A':
                if ($anaent_47['ENTDE2']) {
                    $ParamSegn = $PosizioniSegn[$anaent_47['ENTDE2']];
                }
                break;
            case 'I':
                if ($anaent_55['ENTDE5']) {
                    $ParamSegn = $PosizioniSegn[$anaent_55['ENTDE5']];
                }
                break;
        }
        return $ParamSegn;
    }

    public function GetPosMarcaturaCopiaAnalogica() {
        $anaent_51 = $this->proLib->GetAnaent('51');
        $PosizioniSegn = $this->GetPosizioniSegnatura();

        $ParamSegn['FIRST_PAGE'] = $anaent_35['ENTDE1'];
        $ParamSegn['X_COORD'] = $anaent_35['ENTDE2'];
        $ParamSegn['Y_COORD'] = $anaent_35['ENTDE3'];
        $ParamSegn['ROTAZ'] = $anaent_35['ENTDE4'];
        $ParamSegn['DESC'] = 'Posizione predefinita';
        if ($anaent_51['ENTDE2']) {
            $ParamSegn = $PosizioniSegn[$anaent_51['ENTDE2']];
        }
        return $ParamSegn;
    }

    /* Nuova per modelli base */

    public function caricaTestoBase($model, $anapro_rec, $codice, $tipo = "codice", $Allegati = array()) {
        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';
        $docLib = new docLib();
        $destino = $docLib->importAllegatoFromDocumento($codice, $tipo);
        $Doc_documenti_rec = $docLib->getDocumenti($codice, $tipo);
        if (!$destino) {
            $this->setErrMessage($docLib->getErrMessage());
            return false;
        }
        $Ext = pathinfo($destino, PATHINFO_EXTENSION);
        $FileName = $Doc_documenti_rec['OGGETTO'] . '.' . $Ext;
        if ($destino) {
            $Allegati[] = array(
                'ROWID' => 0,
                'FILEPATH' => $destino,
                'FILENAME' => pathinfo($destino, PATHINFO_BASENAME),
                'FILEINFO' => 'File originale ' . $FileName,
                'DOCNAME' => $FileName,
                'DOCTIPO' => 'ALLEGATO',
                'DOCFDT' => date('Ymd'),
                'DOCRELEASE' => '1',
                'DOCSERVIZIO' => 1,
                'DOCMETA' => "",
                'METTIALLAFIRMA' => ""
            );
        }

        $risultato = $this->GestioneAllegati($model, $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $Allegati, $anapro_rec['PROCON'], $anapro_rec['PRONOM']);
        if (!$risultato) {
            return false;
        }
        // prendo rowid Aggiunto 
        $risRitorno = $this->getRisultatoRitorno();
        $rowidIns = $risRitorno['ROWIDAGGIUNTI'][0];
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidIns, 'rowid');
        // Path documento

        $pdfElaborato = $this->GeneraPdfTestoBase($Anadoc_rec);
        if (!$pdfElaborato) {
            return false;
        }
        $Anadoc_tab = $this->proLib->GetAnadoc($Anadoc_rec['DOCNUM'], 'protocollo', true, $Anadoc_rec['DOCPAR']);
        $TipoDoc = '';
        foreach ($Anadoc_tab as $allegato) {
            if ($allegato['DOCTIPO'] == '') {
                $TipoDoc = 'ALLEGATO';
            }
        }
        $Ext = pathinfo($pdfElaborato, PATHINFO_EXTENSION);
        $FileName = $Doc_documenti_rec['OGGETTO'] . '.' . $Ext;
        $Allegati = array();
        $Allegati[] = array(
            'ROWID' => 0,
            'FILEPATH' => $pdfElaborato,
            'FILENAME' => pathinfo($pdfElaborato, PATHINFO_BASENAME),
            'FILEINFO' => 'File Testo Originale ' . $FileName,
            'DOCNAME' => $FileName,
            'DOCTIPO' => $TipoDoc,
            'DOCFDT' => date('Ymd'),
            'DOCRELEASE' => '1',
            'DOCSERVIZIO' => 0,
            'DOCMETA' => "",
            'METTIALLAFIRMA' => "",
            'DOCROWIDBASE' => $Anadoc_rec['ROWID'],
            'DOCSUBTIPO' => 'E'
        );
        $risultato = $this->GestioneAllegati($model, $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $Allegati, $anapro_rec['PROCON'], $anapro_rec['PRONOM']);
        if (!$risultato) {
            return false;
        }

        $risRitorno = $this->getRisultatoRitorno();
        $rowidIns = $risRitorno['ROWIDAGGIUNTI'][0];
        return $rowidIns;
    }

    /* Nuova per modelli base */

    public function GeneraPdfTestoBase($Anadoc_rec) {
        include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

        $docLib = new docLib();
        $proLibVariabili = new proLibVariabili();
        if (strtolower(pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION) != 'xhtml')) {
            $this->setErrMessage('Generazione Pdf Testo base, possibile solo con xhtml');
            return false;
        }
        $anapro_rec = $this->proLib->GetAnapro($Anadoc_rec['DOCNUM'], 'codice', $Anadoc_rec['DOCPAR']);
//        $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
//        $FileXhtm = $protPath . "/" . $Anadoc_rec['DOCFIL'];
        $FileXhtmContent = $this->GetDocBinary($Anadoc_rec['ROWID']);
        if (!$FileXhtmContent) {
            return false;
        }
        $proLibVariabili->setCodiceProtocollo($anapro_rec['PRONUM']);
        $proLibVariabili->setTipoProtocollo($anapro_rec['PROPAR']);

        $dictionaryValue = $proLibVariabili->getVariabiliProtocollo()->getAllDataFormatted();
//        $pdfElaborato = $docLib->Xhtml2Pdf(file_get_contents($FileXhtm), $dictionaryValue);
        $pdfElaborato = $docLib->Xhtml2Pdf($FileXhtmContent, $dictionaryValue);
        // Qui nome univoco.
        if (!$pdfElaborato) {
            $this->setErrMessage($docLib->getErrMessage());
            return false;
        }
        return $pdfElaborato;
    }

    /* Nuova per modelli base */

    public function ApriTestoBaseAnadoc($Anadoc_rec, $NameForm, $RetEvento = '') {
        $anapro_rec = $this->proLib->GetAnapro($Anadoc_rec['DOCNUM'], 'codice', $Anadoc_rec['DOCPAR']);
        $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
        $FileXhtm = $protPath . "/" . $Anadoc_rec['DOCFIL'];

        $contentFile = @file_get_contents($FileXhtm);
        if (!$contentFile) {
            Out::msgStop("Attenzione", "Errore in lettura del contenuto del file " . $FileXhtm);
            return false;
        }
        $proLibVar = new proLibVariabili();
        $proLibVar->setCodiceProtocollo($anapro_rec['PRONUM']);
        $proLibVar->setTipoProtocollo($anapro_rec['PROPAR']);
        $dictionaryLegend = $proLibVar->getLegendaCampiProtocollo('adjacency', 'smarty');
        $dictionaryValues = $proLibVar->getVariabiliProtocollo()->getAllData();
        $model = 'utiEditDiag';
        $rowidText = $_POST['rowid'];
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['edit_text'] = $contentFile;
        $_POST['returnModel'] = $NameForm;
        $_POST['returnEvent'] = 'returnEditDiag' . $RetEvento;
        $_POST['returnField'] = '';
        $_POST['rowidText'] = $rowidText;
        $_POST['dictionaryLegend'] = $dictionaryLegend;
        $_POST['dictionaryValues'] = $dictionaryValues;
        $_POST['readonly'] = false;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /* Nuova per modelli base */

    public function AggiornaTestoBase($NewContent, $RowidAnadocPdf, $RowidDocBase = '') {
        $AnadocPDF_rec = $this->proLib->GetAnadoc($RowidAnadocPdf, 'rowid');
        if (!$RowidDocBase) {
            $RowidDocBase = $AnadocPDF_rec['DOCROWIDBASE'];
        }
        $AnadocBase_rec = $this->proLib->GetAnadoc($RowidDocBase, 'rowid');
        $anapro_rec = $this->proLib->GetAnapro($AnadocPDF_rec['DOCNUM'], 'codice', $AnadocPDF_rec['DOCPAR']);
        $protPath = $this->proLib->SetDirectory($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        $FileXhtm = $protPath . "/" . $AnadocBase_rec['DOCFIL'];
        if (!file_put_contents($FileXhtm, $NewContent)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nel salvataggio nuovo contenuto file.');
            return false;
        }

        /* Se attivo parametri alfresco - salvo su alfresco */
        $Uuid_origXthm = '';
        $anaent_49 = $this->proLib->GetAnaent('49');
        if ($anaent_49['ENTDE1']) {
            $Uuid_origXthm = $AnadocBase_rec['DOCUUID'];
            $Uuid = $this->AggiungiAllegatoAlfresco($anapro_rec, $FileXhtm, $AnadocBase_rec['DOCFIL']);
            if (!$Uuid) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in salvataggio file testo base pdf.');
                return false;
            }
            $AnadocBase_rec['DOCUUID'] = $Uuid;
        }
        // MD5 SHA file xhtm..
        $AnadocBase_rec['DOCFDT'] = date('Ymd');
        $AnadocBase_rec['DOCMD5'] = md5_file($FileXhtm);
        $AnadocBase_rec['DOCSHA2'] = hash_file('sha256', $FileXhtm);
        try {
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANADOC', 'ROWID', $AnadocBase_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento " . $exc->getMessage());
            return false;
        }
        //!Cancello il vecchio file su alfresco. tramite UUID salvato.
        if ($Uuid_origXthm) {
            if (!$this->CancellaDocUUID($Uuid_origXthm)) {
                return false;
            }
        }

        $pdfElaborato = $this->GeneraPdfTestoBase($AnadocBase_rec);
        if (!$pdfElaborato) {
            return false;
        }
        $randName = md5(rand() * time()) . "." . pathinfo($AnadocPDF_rec['DOCFIL'], PATHINFO_EXTENSION);
        $AnadocPDF_rec['DOCFIL'] = $randName;
        $FilePdf = $protPath . "/" . $randName;
        if (!@rename($pdfElaborato, $FilePdf)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in salvataggio contenuto pdf.");
            return false;
        }
        // Salvataggio su alfresco
        /* Se attivo parametri alfresco - salvo su alfresco */
        $anaent_49 = $this->proLib->GetAnaent('49');
        if ($anaent_49['ENTDE1']) {
            $Uuid_origPdf = $AnadocPDF_rec['DOCUUID'];
            $Uuid = $this->AggiungiAllegatoAlfresco($anapro_rec, $FilePdf, $randName);
            if (!$Uuid) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in salvataggio file testo base pdf.');
                return false;
            }
            $AnadocPDF_rec['DOCUUID'] = $Uuid;
        }
        $AnadocPDF_rec['DOCFDT'] = date('Ymd');
        $AnadocPDF_rec['DOCMD5'] = md5_file($FilePdf);
        $AnadocPDF_rec['DOCSHA2'] = hash_file('sha256', $FilePdf);
        $docmeta = unserialize($AnadocPDF_rec['DOCMETA']);
        $docmeta['SEGNATURA'] = false;
        $AnadocPDF_rec['DOCMETA'] = serialize($docmeta);
        try {
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANADOC', 'ROWID', $AnadocPDF_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento " . $exc->getMessage());
            return false;
        }
        //!Cancello il vecchio file su alfresco. tramite UUID salvato.
        if ($Uuid_origPdf) {
            if (!$this->CancellaDocUUID($Uuid_origPdf)) {
                return false;
            }
        }
        return true;
    }

    public function CopiaAllegatiDaProtocollo($pronum, $propar) {
        $subPath = "proCopiaAllegati-" . md5(microtime());
        $destFile = itaLib::createAppsTempPath($subPath);
        if (!$destFile) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione ambiente di lavoro temporaneo fallita. Copia Allegati. ");
            return false;
        }
        $proArriAllegati = array();
        $Anapro_rec = $this->proLib->GetAnapro($pronum, 'codice', $propar);
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo selezionato inesistente.");
            return false;
        }
        $Anadoc_tab = $this->proLib->caricaAllegatiProtocollo($pronum, $propar);
        foreach ($Anadoc_tab as $keyAlle => $elemento) {
            $randName = md5(rand() * time()) . "." . pathinfo($elemento['FILENAME'], PATHINFO_EXTENSION);
            $destTemporanea = $destFile . '/' . $randName;
            $risCopia = $this->CopiaDocAllegato($elemento['ROWID'], $destTemporanea);
            if ($risCopia) {
//            if (@copy($elemento['FILEPATH'], $destFile . '/' . $randName)) {
                $proArriAllegati[] = Array(
                    'ROWID' => 0,
                    'FILEPATH' => $destTemporanea,
                    'FILENAME' => $randName,
                    'FILEINFO' => $elemento['DOCNOT'],
                    'DOCTIPO' => $elemento['DOCTIPO'],
                    'DAMAIL' => '',
                    'DOCNAME' => $elemento['FILEORIG'],
                    'NOMEFILE' => $elemento['FILEORIG'],
                    'DOCIDMAIL' => '',
                    'DOCFDT' => date('Ymd'),
                    'DOCRELEASE' => '1',
                    'DOCSERVIZIO' => 0,
                    'PROPAR' => $propar,
                    'PRONUM' => $pronum
                );
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in copia File: {$elemento['FILEPATH']} su $destFile/$randName" . $this->getErrMessage());
                return false;
            }
        }
        // TODO@ I DOCUMENTI DI SERVIZIO SERVE PRENDERLI ? 
        return $proArriAllegati;
    }

    public function ControlloAllegatiPreProtocollo($proArriAlle) {
        foreach ($proArriAlle as $allegato) {
            /* Controllo solo i nuovi file hanno estensione e tutto. */
            if ($allegato['ROWID'] == 0) {
                /* 1. Controllo ext docfil */
                $extDocFil = pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION);
                if (!$extDocFil) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Estensione mancante del file sorgente.");
                    return false;
                }
                /* 3. Controllo presenza docname */
                if (!$allegato['DOCNAME']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Nome del documento mancante.");
                    return false;
                }
                /* 3. Controllo ext docname */
                $extDocName = pathinfo($allegato['DOCNAME'], PATHINFO_EXTENSION);
                if (!$extDocName) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Estensione mancante del documento " . $allegato['DOCNAME'] . ".");
                    return false;
                }
                // 4?. Occorre controllare se nel nome file c'è solo estensione? è possibile??
                $basenameDocName = pathinfo($allegato['DOCNAME'], PATHINFO_BASENAME);
                if ($basenameDocName == '.' . $extDocName) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Nome del documento mancante, presente solo estensione.");
                    return false;
                }
                // SOSPESO:
                // Sospendere per rendere bloccante o solo anomalia.
                // 5. Controllo estensione protocollabile:
                $ElencoEstensioniUtilizzabili = $this->proLib->getEestensioniUtilizzabili();
                if ($ElencoEstensioniUtilizzabili) {
                    $ExtKey = strtolower(pathinfo($allegato['DOCNAME'], PATHINFO_EXTENSION));
                    if (!$ElencoEstensioniUtilizzabili[$ExtKey] || $ElencoEstensioniUtilizzabili[$ExtKey]['EXTPROTO'] != 1) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Estensione non accettata in protocollazione.<br>File: " . $allegato['DOCNAME']);
                        return false;
                    }
                    /*
                     * 6. Controllo p7m estensione:
                     */
                    if ($ExtKey == 'p7m') {
                        $EstensioneString = $this->GetEstensioneP7m(strtolower($allegato['DOCNAME']), 10);
                        $ArrExt = explode('.', $EstensioneString);
                        $ExtLiv0 = $ArrExt[0];
                        if (!$ElencoEstensioniUtilizzabili[$ExtLiv0] || $ElencoEstensioniUtilizzabili[$ExtLiv0]['EXTPROTO'] != 1) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Estensione <b>$ExtLiv0</b> non accettata in protocollazione.");
                            return false;
                        }
                        if ($EstensioneString == 'p7m') {
                            $this->setErrCode(-1);
                            $this->setErrMessage("L'allegato dentro al p7m è senza estensione. Allegato non protocollabile.");
                            return false;
                        }
                    }
                }
                // SOSPESO: sostituzione dei caratteri.
                // 7. Controllo caratteri windows particolari.
                //$match = preg_match('/[*\\\\:<>?\/|"]/', $allegato['DOCNAME'], $retMatch);
//                $match = preg_match('/[*\\\\:<>?|"]/', $allegato['DOCNAME'], $retMatch);
//                if ($match) {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage('Carattere speciale non accettato nel nome del file: ' . $retMatch[0] . ' .');
//                    return false;
//                }
            }
        }
        return true;
    }

    /**
     * Riposiziona gli allegati nell'ordine corretto
     * nel caso si presenti una fattura:
     * Per primo il Flusso Fattura, poi gli Annessi Fattura, poi tutti gli altri allegati.
     * Questo per avere sempre disponibiile il riferimento ALFRESCO(UUID) 
     * al flusso quando si inseriscono gli annessi.
     * 
     * @param type $proArriAlle
     * @param type $objSdi
     * @return type
     */
    public function ControlloAllegatiProtocollo($proArriAlle = array(), $objSdi = null) {
        $OrdineProArriAlle = array();
        $i = 1;
        $posizione = 1;

        foreach ($proArriAlle as $key => $Allegato) {
            if ($objSdi) {
                if ($objSdi->getFileFatturaUnivoco() == $Allegato['DOCNAME'] && $objSdi->isFatturaPA()) {
                    $posizione = 0;
                    $Allegato['TIPO_ALLEGATO'] = self::ALLEGATO_FATT;
                } else if ($objSdi->getNomeFileMessaggio() == $Allegato['DOCNAME'] && $objSdi->isMessaggioSdi()) {
                    $Allegato['TIPO_ALLEGATO'] = self::ALLEGATO_ANNESSO_FATT;
                    $posizione = $i;
                } else {
                    $Allegato['TIPO_ALLEGATO'] = self::ALLEGATO_PROT;
                    $posizione = $i;
                }
            } else {
                $Allegato['TIPO_ALLEGATO'] = self::ALLEGATO_PROT;
                $posizione = $i;
            }
            $OrdineProArriAlle[$posizione] = $Allegato;
            $i++;
        }
        ksort($OrdineProArriAlle);
        return $OrdineProArriAlle;
    }

    public function GetUuidPadreFattura($Anapro_rec, $fileName) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';

        $proLibTabDag = new proLibTabDag();
        $proLibSdi = new proLibSdi();
        /* Lettura del codice univoco fattura. */
        $Tabdag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'CodUnivocoFile', '', false);

        $Estratto = array();
        $Estratto['CodUnivocoFile'] = $Tabdag_rec['TDAGVAL'];
        $Estratto['NomeFileMessaggio'] = $fileName;

        $TabDagMT_rec = $proLibSdi->GetTabDagMessaggioMT($Estratto);
        if (!$TabDagMT_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile Trovare il Metadato Messaggio SDI.");
            return false;
        }

        /* Lettura di AnaproRec */
        $AnaproFattura_rec = $this->proLib->GetAnapro($TabDagMT_rec['TDROWIDCLASSE'], 'rowid');
        /* Ricavo tabdag fattura. */

        $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $AnaproFattura_rec['ROWID'], 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del File Fattura Univoco.");
            return false;
        }
        $FileFatturaUnivoco = $TabDag_rec['TDAGVAL'];
        $sql = "SELECT * FROM ANADOC 
                    WHERE DOCNUM = {$AnaproFattura_rec['PRONUM']} AND 
                    DOCPAR = '{$AnaproFattura_rec['PROPAR']}' 
                    AND DOCNAME = '$FileFatturaUnivoco' ";
        //Out::msgInfo('sql', $sql);
        $AnadocFattura_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);

        if (!$AnadocFattura_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, trovare il codice UUID della fattura.");
            return false;
        }
        return $AnadocFattura_rec['DOCUUID'];
    }

    public function getObjItaDocumentaleAlfrescoUtils() {
        $itaDocumentaleAlfrescoUtils = null;
        $anaent_49 = $this->proLib->GetAnaent('49');
//        if ($anaent_49['ENTDE1']) {
        /*
         * Lettura dei Librerie e Parametri Necessari:
         */
        $itaDocumentaleAlfrescoUtils = new itaDocumentaleAlfrescoUtils();
        $utiEnte = new utiEnte();
        $anaent_26 = $this->proLib->GetAnaent('26');
        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
        /* Setto Parametri per Salvataggio File */
        $itaDocumentaleAlfrescoUtils->setCodiceAoo($anaent_26['ENTDE2']);
        $itaDocumentaleAlfrescoUtils->setCodiceEnte($ParametriEnte_rec['ISTAT']);
        $itaDocumentaleAlfrescoUtils->setCodiceUtente(App::$utente->getKey('nomeUtente'));
        $itaDocumentaleAlfrescoUtils->setDescrizioneEnte($ParametriEnte_rec['DENOMINAZIONE']);
//        }
        return $itaDocumentaleAlfrescoUtils;
    }

    /**
     * Funzione che provvede ad inserire un nuovo documento
     * su alfresco. Ritorna l'Uuid se è andato a buon fine.
     * 
     * @param type $Anapro_rec
     * @param type $FileSorgente
     * @param type $FileName
     * @return boolean
     */
    public function AggiungiAllegatoAlfresco($Anapro_rec, $FileSorgente, $FileName) {
        /* Istanzio l'oggetto per salvataggio allegato */
        $itaDocumentaleAlfrescoUtils = $this->getObjItaDocumentaleAlfrescoUtils();
        /* Istanzio l'oggetto proProtocollo */
        $numero = substr($Anapro_rec['PRONUM'], 4);
        $anno = substr($Anapro_rec['PRONUM'], 0, 4);
        $tipo = $Anapro_rec['PROPAR'];
        $objProtocollo = proProtocollo::getInstance($this->proLib, $numero, $anno, $tipo, '');
        /* Lettura del contenuto fil e */
        $content = file_get_contents($FileSorgente);
        /* Salvataggio File */
        $itaDocumentaleAlfrescoUtils->setDizionario($objProtocollo->GetDizionarioDocumentale(self::ALLEGATO_PROT, $FileName));
        $retIns = $itaDocumentaleAlfrescoUtils->inserisciProtocollo($FileName, $content);
        if (!$retIns) {
            $this->setErrCode(-1);
            $this->setErrMessage("Archiviazione. Errore in salvataggio del file $FileName." . $itaDocumentaleAlfrescoUtils->getErrMessage());
            return false;
        }
        return $itaDocumentaleAlfrescoUtils->getResult();
    }

    /**
     * Restituisce la path del'allegato puntato dal record ANADOC
     * se il  contenuto è presente in un sistema documentale è
     * restituita la stringa "DOCUUID:<identificativo documentale>"
     * 
     * 
     * @param type $rowidAnadoc
     * @param type $getBinary
     * @param type $returnBase64
     * @return $forceGetFileServer boolean ** Verrà rimosso una volta completata la procedura di marcature dinamiche.
     */
    public function GetDocPath($rowidAnadoc, $getBinary = false, $returnBase64 = false, $forceGetFileServer = false, $anadocSave = false) {
        if (!$anadocSave) {
            $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        } else {
            $Anadoc_rec = $this->proLib->GetAnadocSave($rowidAnadoc, 'rowid');
        }
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura binario. Lettura di Anadoc Fallita.");
            return false;
        }
        if ($getBinary) {
            $binary = $this->GetDocBinary($rowidAnadoc, $returnBase64);
            if ($binary === false) {
                return false;
            }
        }

        /*
         * Controllo DOCRELCLASSE
         */
        if ($Anadoc_rec['DOCRELCLASSE']) {
            $proDocReader = new proDocReader();
            $obDocReader = $proDocReader->getInstance($rowidAnadoc);
            if (!$obDocReader) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura allegato. " . $proDocReader->getErrMessage());
                return false;
            }
            return $FilePathDest = $obDocReader->GetDocPath($rowidAnadoc, $getBinary, $returnBase64, $forceGetFileServer, $anadocSave);
        }

        if ($Anadoc_rec['DOCUUID'] && $forceGetFileServer == false) {
            return array('DOCPATH' => 'DOCUUID:' . $Anadoc_rec['DOCUUID'], 'DOCNAME' => $Anadoc_rec['DOCNAME'], 'BINARY' => $binary);
        } else {
            $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
            $filePathSorg = $protPath . "/" . $Anadoc_rec['DOCFIL'];
            if ($Anadoc_rec['DOCPATHASSOLUTA']) {
                $filePathSorg = $this->getFilePathAssoluta($Anadoc_rec['ROWID'], $anadocSave);
            }
            if (!is_file($filePathSorg) || !is_readable($filePathSorg)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Lettura file. Lettura allegato Fallita.");
                return false;
            }
            return array('DOCPATH' => $filePathSorg, 'DOCNAME' => $Anadoc_rec['DOCNAME'], 'BINARY' => $binary);
        }
    }

    public function GetMetadatiDocumentaleByUUID($UUID, $returnNodeValuesPlain = true) {
        $documentale = new itaDocumentale('ALFCITY');
        $documentale->setUtf8_encode(true);
        $documentale->setUtf8_decode(true);
        $ResultQery = $documentale->queryByUUID($UUID);
        if (!$ResultQery) {
            $this->setErrCode(-1);
            $this->setErrMessage("UUID non presente. " . $documentale->getErrMessage());
            return false;
        }

        $ArrayResultComplete = $documentale->getResult();
        $ArrayDati = array();
        $ArrayResult = $ArrayResultComplete['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0]['COLUMNS'][0]['COLUMN'];

        if ($returnNodeValuesPlain) {
            foreach ($ArrayResult as $Valore) {
                $Name = $Valore['NAME'][0]['@textNode'];
                $Value = $Valore['VALUE'][0]['@textNode'];
                $ArrayDati[$Name] = $Value;
            }
        } else {
            // Valutare bene quale attributo tornere.
            foreach ($ArrayResult as $Valore) {
                $Name = $Valore['NAME'][0]['@textNode'];
                $Value = $Valore['VALUE'][0]['@textNode'];
                $Attributo = $Valore['VALUE'][0]['@attributes'];
                $ArrayDati[$Name] = array('Valore' => $Value, 'Attributo' => $Attributo);
            }
        }
//        Out::msgInfo('daty', print_r($ArrayDati, true));
        return $ArrayDati;
    }

    public function GetUUIDBinary($UUID, $returnBase64 = false) {
        $documentale = new itaDocumentale('ALFCITY');
        $documentale->setUtf8_encode(true);
        $documentale->setUtf8_decode(true);

        if (!$documentale->contentByUUID($UUID)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura Contenuto non riuscita.");
            return false;
        }
        $ContenutoFile = $documentale->getResult();
        if (!$ContenutoFile) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura Contenuto. Download del contenuto non riuscita.");
            return false;
        }
        /* Controllo se deve tornare il base64 del file */
        if ($returnBase64 === true) {
            $base64 = base64_encode($ContenutoFile);
            if (!$base64) {
                $this->setErrCode(-1);
                $this->setErrMessage("Lettura Contenuto. Codifica base64 non riuscita.");
                return false;
            }
            return $base64;
        }
        return $ContenutoFile;
    }

    /**
     * Ritorna il Rowid di Anadoc tramite DOCUUID.
     * 
     * @param type $Docuuid
     */
    //TODO: FIX DEL NOME FUNZIONE E VEDERE DOVE USATA
    public function GetRowidAnadocByUUID($Docuuid) {
        $sql = "SELECT * FROM ANADOC WHERE DOCUUID = '$Docuuid'";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    /**
     * Restituisce il binario dato un anadoc
     * 
     * @param type $rowidAnadoc
     * @param type $returnBase64
     * @return boolean
     */
    public function GetDocBinary($rowidAnadoc, $returnBase64 = false) {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura binario. Lettura di Anadoc Fallita.");
            return false;
        }

        if ($Anadoc_rec['DOCUUID']) {
            $ContenutoFile = $this->GetUUIDBinary($Anadoc_rec['DOCUUID'], $returnBase64);
            if (!$ContenutoFile) {
                return false;
            }
        } else {
            /* Altrimenti lettura tramite e copia tramite setDirectory. */
            $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
            $filePathSorg = $protPath . "/" . $Anadoc_rec['DOCFIL'];
            // Path assoluta:
            if ($Anadoc_rec['DOCPATHASSOLUTA']) {
                $filePathSorg = $this->getFilePathAssoluta($Anadoc_rec['ROWID']);
            }
            /* Controllo se occorre copiare il file */
            /* Controllo se deve tornare il base64 del file */
            if ($returnBase64 === true) {
                $base64 = base64_encode(file_get_contents($filePathSorg));
                if (!$base64) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Copia Allegato. Errore nella lettura del file binario.");
                    return false;
                }
                return $base64;
            }
            return file_get_contents($filePathSorg);
        }
        return $ContenutoFile;
    }

    public function getFilePathAssoluta($rowidAnadoc, $anadocSave = false) {
        if (Config::getPath('general.itaPrAssoluta')) {
            $d_dir = Config::getPath('general.itaPrAssoluta');
        } else {
            $d_dir = Config::getPath('general.itaPrim') . 'protCIV';
        }
        if (!$anadocSave) {
            $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        } else {
            $Anadoc_rec = $this->proLib->GetAnadocSave($rowidAnadoc, 'rowid');
        }
        $filepath = $d_dir . $Anadoc_rec['DOCPATHASSOLUTA'];
        return $filepath;
    }

    public function getPathAssoluta($docpathassoluta) {
        if (Config::getPath('general.itaPrAssoluta')) {
            $d_dir = Config::getPath('general.itaPrAssoluta');
        } else {
            $d_dir = Config::getPath('general.itaPrim') . 'protCIV';
        }
        $parti = explode("/", $docpathassoluta);
        unset($parti[count($parti) - 1]);
        $filepath = $d_dir . strtolower(implode("/", $parti) . '/');
        return $filepath;
    }

    public function OpenDocAllegato($rowidAnadoc, $force_download = false, $utf8decode = false, $headers = true) {
        $DocAllegato = $this->GetDocPath($rowidAnadoc);
        if ($DocAllegato === false) {
            return false;
        }
        Out::openDocument(utiDownload::getUrl($DocAllegato['DOCNAME'], $DocAllegato['DOCPATH'], $force_download, $utf8decode, $headers));
        return true;
    }

    public function OpenDocAllegatoSave($rowidAnadoc, $force_download = false, $utf8decode = false, $headers = true) {
        $DocAllegato = $this->GetDocPath($rowidAnadoc, false, false, false, true);
        if ($DocAllegato === false) {
            return false;
        }
        Out::openDocument(utiDownload::getUrl($DocAllegato['DOCNAME'], $DocAllegato['DOCPATH'], $force_download, $utf8decode, $headers));
        return true;
    }

    /**
     * Funzione per copiare un file allegato del protocollo
     * 
     * @param type $rowidAnadoc
     * @param type $filePathDest
     * @param type $createTemporaryDest
     * @return boolean
     */
    public function CopiaDocAllegato($rowidAnadoc, $filePathDest = '', $createTemporaryDest = false) {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Lettura di Anadoc Fallita.");
            return false;
        }
        if (!$filePathDest && !$createTemporaryDest) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Destino File Non Presente.");
            return false;
        }
        if ($createTemporaryDest) {
            $subPath = "proDocAllegato-work-" . itaLib::getRandBaseName();
            $tempPath = itaLib::createAppsTempPath($subPath);
            $ext = pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION);
            if ($ext == '') {
                $filePathDest = $tempPath . '/' . $Anadoc_rec['DOCFIL'] . '.' . pathinfo($Anadoc_rec['DOCNAME'], PATHINFO_EXTENSION);
            } else {
                $filePathDest = $tempPath . '/' . $Anadoc_rec['DOCFIL'];
            }
        }
        /*
         * Controllo DOCRELCLASSE
         */
        if ($Anadoc_rec['DOCRELCLASSE']) {
            $proDocReader = new proDocReader();
            $obDocReader = $proDocReader->getInstance($rowidAnadoc);
            if (!$obDocReader) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura allegato. " . $proDocReader->getErrMessage());
                return false;
            }
            return $FilePathDest = $obDocReader->CopiaDocAllegato($rowidAnadoc, $filePathDest, $createTemporaryDest);
        }
        /* Controllo se è da effettuare la lettura da UUID */
        if ($Anadoc_rec['DOCUUID']) {
            $ContenutoFile = $this->GetDocBinary($rowidAnadoc);
            if ($ContenutoFile === false || $ContenutoFile === '') {
                return false;
            }
            /* Controllo se occorre copiare il file */
            if (!file_put_contents($filePathDest, $ContenutoFile)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Copia Allegato. Scrittura del file copiato non riuscita.");
                return false;
            }
        } else {
            /* Altrimenti lettura tramite e copia tramite setDirectory. */
            $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
            $filePathSorg = $protPath . "/" . $Anadoc_rec['DOCFIL'];
            // path assoluta
            if ($Anadoc_rec['DOCPATHASSOLUTA']) {
                $filePathSorg = $this->getFilePathAssoluta($Anadoc_rec['ROWID']);
            }
            /* Controllo se occorre copiare il file */
            if (!@copy($filePathSorg, $filePathDest)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Copia Allegato. Errore durante la copia del file nell'ambiente temporaneo di lavoro.");
                return false;
            }
        }
        return $filePathDest;
    }

    public function CopiaTmpAllegatoByUUID($Uuid) {
        $Metadati = $this->GetMetadatiDocumentaleByUUID($Uuid);
        if (!$Metadati) {
            $this->setErrCode(-1);
            $this->setErrMessage('Metadati UUID spacchettato non trovati.');
            return false;
        }

        $NomeFile = md5(rand() * time()) . "." . pathinfo($Metadati['name'], PATHINFO_EXTENSION);

        // path temporanea
        $subPath = "proDocAllegato-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        $filePathDest = $tempPath . '/' . $NomeFile;

        $ContenutoFile = $this->GetUUIDBinary($Uuid, false);
        if (!$ContenutoFile) {
            return false;
        }

        /* Controllo se occorre copiare il file */
        if (!file_put_contents($filePathDest, $ContenutoFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato Tmp. Scrittura del file copiato non riuscita.");
            return false;
        }

        return $filePathDest;
    }

    public function GetHashDocAllegato($rowidAnadoc, $hashType = 'sha256') {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Lettura di Anadoc Fallita.");
            return false;
        }

        /*
         * Controllo DOCRELCLASSE
         */
        if ($Anadoc_rec['DOCRELCLASSE']) {
            $proDocReader = new proDocReader();
            $obDocReader = $proDocReader->getInstance($rowidAnadoc);
            if (!$obDocReader) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in lettura allegato. " . $proDocReader->getErrMessage());
                return false;
            }
            return $FilePathDest = $obDocReader->GetHashDocAllegato($rowidAnadoc, $hashType);
        }


        if ($Anadoc_rec['DOCUUID']) {
            $hashFile = hash($hashType, $this->GetDocBinary($rowidAnadoc));
        } else {
            // Controllo
            $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
            $filePathSorg = $protPath . "/" . $Anadoc_rec['DOCFIL'];
            // Path assoluta:
            if ($Anadoc_rec['DOCPATHASSOLUTA']) {
                $filePathSorg = $this->getFilePathAssoluta($Anadoc_rec['ROWID']);
            }
            /*
             * Controllo File Esiste
             */
            if (!file_exists($filePathSorg)) {
                $this->setErrCode(-1);
                $this->setErrMessage("File su disco non trovato.");
                return false;
            }
            $hashFile = hash_file($hashType, $filePathSorg);
        }

        return $hashFile;
    }

    public function CancellaDocAllegato($rowidAnadoc) {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Cancellazione Allegato. Lettura di Anadoc Fallita.");
            return false;
        }
        return true;
        /* Cancellazione non più fisca ma solo logica: */
        // Cancellazione su Alfresco
        if ($Anadoc_rec['DOCUUID']) {
            if (!$this->CancellaDocUUID($Anadoc_rec['DOCUUID'])) {
                return false;
            }
        }
        $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
        $filePathSorg = $protPath . "/" . $Anadoc_rec['DOCFIL'];
        // Cancellazione su server se il file esiste.
        if (file_exists($filePathSorg)) {
            if (!@unlink($filePathSorg)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Cancellazione Allegato su Server Fallita.");
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * Cancella un documentale per UUID
     * 
     * @param type $uuid
     * @return boolean
     */
    public function CancellaDocUUID($uuid) {
        // 
        // Funzione di itaDocumentale per la cancellazione.
        // 
        $documentale = new itaDocumentale('ALFCITY');
        $documentale->setUtf8_encode(true);
        $documentale->setUtf8_decode(true);

        if (!$documentale->deleteDocumentByUUID($uuid)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Cancellazione Allegato Fallita." . $documentale->getErrMessage());
            return false;
        }
        return true;
    }

    /**
     * 
     * controlla se esiste il file fisico o i metadati documenatali
     * 
     * @param type $rowidAnadoc
     * @return boolean
     */
    public function CheckDocAllegato($rowidAnadoc) {
        $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Lettura di Anadoc Fallita.");
            return false;
        }

        if ($Anadoc_rec['DOCUUID']) {
            $itaDocumentale = new itaDocumentale('ALFCITY');
            $ResultQery = $itaDocumentale->queryByUUID($Anadoc_rec['DOCUUID']);
            if (!$ResultQery) {
                $this->setErrCode(-1);
                $this->setErrMessage("File non presente. " . $itaDocumentale->getErrMessage());
                return false;
            }
        } else {
            $protPath = $this->proLib->SetDirectory($Anadoc_rec['DOCNUM'], $Anadoc_rec['DOCPAR']);
            if (!is_file($protPath . "/" . $Anadoc_rec['DOCFIL'])) {
                $this->setErrCode(-1);
                $this->setErrMessage("File non presente. ");
                return false;
            }
        }

        return true;
    }

    public function getUuidFattura($AnaproRowid) {
        $proLibSdi = new proLibSdi();
        /* Lettura del protocollo */
        $Anapro_rec = $this->proLib->GetAnapro($AnaproRowid, 'rowid');
        if (!$Anapro_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo non trovato. ");
            return false;
        }
        /* Verifico se è una fattura elettronica: se TipoDoc è EFAA */
        $anaent_38 = $this->proLib->GetAnaent('38');
        if ($Anapro_rec['PROCODTIPODOC'] != $anaent_38['ENTDE1']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Protocollo non valido. Questo protocollo non è una fattura elettronica in arrivo. ");
            return false;
        }
        /* Prendo l'anadoc della fattura elettronica  */
        $Anadoc_rec = $proLibSdi->getAnadocFlussoFromAnapro($Anapro_rec);
        if (!$Anadoc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("File della fattura elettronica non trovato. ");
            return false;
        }
        /* Controllo la presenza del DOCUUID */
        if (!$Anadoc_rec['DOCUUID']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Identificativo UUID del file della fattura elettronica non trovato. ");
            return false;
        }

        return $Anadoc_rec['DOCUUID'];
    }

    public function CheckFatturaSpacchettata($Uuid) {
        /* Lettura dei metadati */
        $Metadati = $this->GetMetadatiDocumentaleByUUID($Uuid);
        if (!$Metadati) {
            $this->setErrCode(-1);
            $this->setErrMessage("Metadati non trovati per il file fattura.");
            return false;
        }
        /* Verifico se è già spacchettato. */
        if ($Metadati['stato_flusso'] == 1) {
            /* Restituisco errore 2 perchè non è un errore bloccante se è già spacchettata. */
            $this->setErrCode(2);
            $this->setErrMessage("Fattura già spacchettata.");
            return false;
        }
        return true;
    }

    public function SpacchettaFatturaFromUuid($Uuid) {
        if (!$this->CheckFatturaSpacchettata($Uuid)) {
            return false;
        }
        /* Log Inizio Spacchettamento */
        $Audit = 'Inizio Spacchettamento Fattura con UUID:  ' . $Uuid;
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        /* Chiamata a spacchettamento omnis */
        $utiEnte = new utiEnte();
        $anaent_26 = $this->proLib->GetAnaent('26');
        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
        $itaDocumentaleAlfrescoUtils = new itaDocumentaleAlfrescoUtils();
        /* Setto Parametri per Salvataggio File - parametri test. */
        $itaDocumentaleAlfrescoUtils->setCodiceAoo($anaent_26['ENTDE2']);
//        $itaDocumentaleAlfrescoUtils->setCodiceAoo('042002');
        $itaDocumentaleAlfrescoUtils->setCodiceEnte($ParametriEnte_rec['ISTAT']);
//        $itaDocumentaleAlfrescoUtils->setCodiceEnte('atdaa');
        $itaDocumentaleAlfrescoUtils->setCodiceUtente(App::$utente->getKey('nomeUtente'));
        $itaDocumentaleAlfrescoUtils->setDescrizioneEnte($ParametriEnte_rec['DENOMINAZIONE']);
//        $itaDocumentaleAlfrescoUtils->setDescrizioneEnte('Ente Ata');
        $result = $itaDocumentaleAlfrescoUtils->spacchettaFlusso($Uuid);
        if (!$result) {
            /* Log Spacchettamento con errore. */
            $Audit = 'Errore Spacchettamento Fattura con UUID:  ' . $Uuid . ' ' . $itaDocumentaleAlfrescoUtils->getErrMessage();
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            // Ritorno errore.
            $this->setErrCode(-1);
            $this->setErrMessage($itaDocumentaleAlfrescoUtils->getErrMessage());
            return false;
        }
        $ListUUID = $itaDocumentaleAlfrescoUtils->getResult();
        return $ListUUID;
    }

    public function SpacchettaFattura($AnaproRowid) {
        $Uuid = $this->getUuidFattura($AnaproRowid);
        if (!$Uuid) {
            return false;
        }
        $ListUUID = $this->SpacchettaFatturaFromUuid($Uuid);
        if (!$ListUUID) {
            return false;
        }
        // Ritornano gli anapro inseriti.. possibile utilizzo?

        if (!$this->ProtocollaListaUUID($ListUUID)) {
            return false;
        }

        /*
         * Aggiorna Metadati ID_SDI
         */
        $resAgg = $this->AggiornaMetadatiSDI($AnaproRowid, $Uuid, $ListUUID);
        if (!$resAgg) {
            Out::msgInfo('Informazione', $this->getErrMessage());
        }

        // Chiudo Arcite:
        if (!$this->ChiudiTrasmissioniEFAA($AnaproRowid)) {
            // msg info non usate nelle lib.
            Out::msgInfo('Informazione', $this->getErrMessage());
        }

//        Out::msgInfo('AnaproCreati', print_r($this->risultatoRitorno, true));
        return true;
    }

    /*
     * Chiude tutte le ASS della EFAA
     */

    public function ChiudiTrasmissioniEFAA($AnaproRowid) {
        $Anapro_rec = $this->proLib->GetAnapro($AnaproRowid, 'rowid');
        $Arcite_tab = $this->proLib->GetArcite($Anapro_rec['PRONUM'], 'codice', true, $Anapro_rec['PROPAR']);

        foreach ($Arcite_tab as $arcite_rec) {
            if ($arcite_rec['ITENODO'] != 'ASS') {
                continue;
            }
            $arcite_rec['ITEFIN'] = date('Ymd');
            $arcite_rec['ITEFLA'] = '2';
            $arcite_rec['ITEANN'] = 'CHIUSURA PER TRASMISSIONE EFAS. ' . $arcite_rec['ITEANN'];
            try {
                ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ARCITE', 'ROWID', $arcite_rec);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Fatture protocollate correttamente. Non è stato possibile chiudere le trasmissioni EFAA. " . $exc->getMessage());
                return false;
            }
        }

        return true;
    }

    public function ProtocollaListaUUID($ListUUID) {
        /*
         *  Loggo gli uuid da protocollare:
         */
        $Audit = 'UUID da protocollare: ' . count($ListUUID);
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        foreach ($ListUUID as $uuid) {
            $Audit = 'UUID Riga:  ' . $uuid;
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        }
        /*
         * Inizio elaborazione degli uuid da protocollare
         */
        $this->risultatoRitorno = array();
        $AnaproCreati = array();
        foreach ($ListUUID as $Uuid_riga) {
            $Audit = 'Preparazione Protocollazione UUID Riga:  ' . $Uuid_riga;
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            /*
             * 1. Rileggo i metadati dell'uuid appena spacchettato.
             */
            $Metadati = $this->GetMetadatiDocumentaleByUUID($Uuid_riga);
            if (!$Metadati) {
                $this->setErrCode(-1);
                $this->setErrMessage('Metadati UUID spacchettato non trovati.');
                return false;
            }
            /* Ricavo il protocollo padre tramite l'uuid padre "ger_uuid_padre" */
            $AnaproPadre_rec = array();
            if ($Metadati['ger_uuid_padre']) {
                $MetadatiPadre = $this->GetMetadatiDocumentaleByUUID($Metadati['ger_uuid_padre']);
                if (!$MetadatiPadre) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Metadati UUID Padre spacchettato non trovati.');
                    /* Log dell'errore riscontrato */
                    $Audit = 'Errore in protocollazione UUID Riga: ' . $Uuid_riga . ' - ' . $this->getErrMessage();
                    $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
                    return false;
                }
                // Lettura metati prot padre.
                if (!$MetadatiPadre['prot_tipo'] || !$MetadatiPadre['prot_numero']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Metadati UUID padre protocollo mancanti.');
                    /* Log dell'errore riscontrato */
                    $Audit = 'Errore in protocollazione UUID Riga: ' . $Uuid_riga . ' - ' . $this->getErrMessage();
                    $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
                    return false;
                }
                $codiceProtPadre = $MetadatiPadre['prot_anno'] . str_pad($MetadatiPadre['prot_numero'], 6, '0', STR_PAD_LEFT);
                $tipoProtPadre = $MetadatiPadre['prot_tipo'];
                $AnaproPadre_rec = $this->proLib->GetAnapro($codiceProtPadre, 'codice', $tipoProtPadre);
            }
            if (!$AnaproPadre_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Protocollo padre fattura spacchettata non trovato.');
                /* Log dell'errore riscontrato */
                $Audit = 'Errore in protocollazione UUID Riga: ' . $Uuid_riga . ' - ' . $this->getErrMessage();
                $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
                return false;
            }
            /*
             * 2. Protocollo l'uuid.
             */
            $AnaproProt_rec = $this->ProtocollaFatturaSpacchettata($AnaproPadre_rec, $Uuid_riga);
            if (!$AnaproProt_rec) {
                /* Log dell'errore riscontrato */
                $Audit = 'Errore in protocollazione UUID Riga: ' . $Uuid_riga . ' - ' . $this->getErrMessage();
                $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
                return false;
            }
            /*
             * Log UUID protocollato 
             */
            $Audit = 'Protocollato: ' . $AnaproProt_rec['PRONUM'] . $AnaproProt_rec['PROPAR'] . ' - UUID Riga: ' . $Uuid_riga;
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            /*
             * Aggiungo l'anapro appena creato all'array risultato.
             * Setto i risultati di ritorno fino ad ora 
             */
            $AnaproCreati[] = $AnaproProt_rec;
            $this->risultatoRitorno = $AnaproCreati;
        }
        return true;
    }

    public function ProtocollaFatturaSpacchettata($AnaproPadre_rec, $Uuid) {
        // Preparazione del protocollo
        // Qui lettura di eventuali parametri.
        /*
         * Rilettura dei metadati.
         */
        $Metadati = $this->GetMetadatiDocumentaleByUUID($Uuid);
        if (!$Metadati) {
            $this->setErrCode(-1);
            $this->setErrMessage('Metadati UUID spacchettato non trovati.');
            return false;
        }

        $elementi = array();
        $anaent_45 = $this->proLib->GetAnaent('45');
        $TipoDoc = $anaent_45['ENTDE5'];
        if (!$anaent_45['ENTDE5']) {
            $TipoDoc = 'EFAS';
        }
        /**
         * Dati principali
         */
        $elementi['tipo'] = 'A';
        $elementi['dati'] = array();
        $elementi['dati']['TipoDocumento'] = $TipoDoc;
        $elementi['dati']['Oggetto'] = 'FATTURA N. ' . $Metadati['numero_documento'] . ', FORNITORE ' . $Metadati['ragsoc_mittente'] . '. ID. ' . $Metadati['id_unita_doc']; // provvisorio
        $numeroAnt = substr($AnaproPadre_rec['PRONUM'], 4);
        $annoAnt = substr($AnaproPadre_rec['PRONUM'], 0, 4);
        $elementi['dati']['NumeroAntecedente'] = $numeroAnt;
        $elementi['dati']['AnnoAntecedente'] = $annoAnt;
        $elementi['dati']['TipoAntecedente'] = $AnaproPadre_rec['PROPAR'];
        $elementi['dati']['DataArrivo'] = date('Ymd'); // stessa data del protocollo padre?
        /*
         * Ricavo il titolario dal protocollo padre: 
         */
        if (strlen($AnaproPadre_rec['PROCCF']) === 4) {
            $classificazione = intval($AnaproPadre_rec['PROCCF']);
        } else if (strlen($AnaproPadre_rec['PROCCF']) === 8) {
            $classificazione = intval(substr($AnaproPadre_rec['PROCCF'], 0, 4)) . '.' . intval(substr($AnaproPadre_rec['PROCCF'], 4, 4));
        } else if (strlen($AnaproPadre_rec['PROCCF']) === 12) {
            $classificazione = intval(substr($AnaproPadre_rec['PROCCF'], 0, 4)) . '.' . intval(substr($AnaproPadre_rec['PROCCF'], 4, 4)) . '.' . intval(substr($AnaproPadre_rec['PROCCF'], 8, 4));
        } else {
            $classificazione = $AnaproPadre_rec['PROCCF'];
        }
        $elementi['dati']['Classificazione'] = $classificazione;
        // Mittente principale, preso da protocollo padre.
        $elementi['dati']['Mittenti'][0]['Denominazione'] = $AnaproPadre_rec['PRONOM'];
        $elementi['dati']['Mittenti'][0]['Indirizzo'] = $AnaproPadre_rec['PROIND'];
        $elementi['dati']['Mittenti'][0]['CAP'] = $AnaproPadre_rec['PROCAP'];
        $elementi['dati']['Mittenti'][0]['Citta'] = $AnaproPadre_rec['PROCIT'];
        $elementi['dati']['Mittenti'][0]['Provincia'] = $AnaproPadre_rec['PROPRO'];
        $elementi['dati']['Mittenti'][0]['Email'] = $AnaproPadre_rec['PROMAIL'];
        // !! Trasmissioni innterne non servono per ora...
        //Leggo le trasmissioni del protocollo padre e le assegno all'attuale.
        $anades_tab = $this->proLib->GetAnades($AnaproPadre_rec['PRONUM'], 'codice', true, $AnaproPadre_rec['PROPAR'], 'T', " AND DESCUF<>'' ");
        $Trasmissioni = array();
        foreach ($anades_tab as $anades_rec) {
            $Trasmissione = array();
            $anamed_rec = $this->proLib->GetAnamed($anades_rec['DESCOD']);
            $Trasmissione['CodiceDestinatario'] = $anades_rec['DESCOD'];
            $Trasmissione['Denominazione'] = $anamed_rec['MEDNOM'];
            $Trasmissione['Ufficio'] = $anades_rec['DESCUF'];
            $Trasmissione['Responsabile'] = '';
            $Trasmissione['Gestione'] = 1;
            $Trasmissioni[] = $Trasmissione;
        }
        $elementi['destinatari'] = $Trasmissioni;


        /* Prendo i dati dell'allegato. */
        $elementi['allegati']['Principale'] = array();
        /*
         * Istanza Oggetto ProtoDatiProtocollo
         */
        $model = 'proDatiProtocollo.class'; // @TODO cambiata per provare nuove funzioni senza creare problemi al SUAP
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proDatiProtocollo = new proDatiProtocollo();
        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /*
         * Attiva Controlli su proDatiProtocollo - necessari ?
         */
        if (!$proDatiProtocollo->controllaDati()) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /*
         * Lancia il protocollatore con i dati impostati
         */
        $model = 'proProtocolla.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proProtocolla = new proProtocolla();
        $ret_id = $proProtocolla->registraPro('Aggiungi', '', $proDatiProtocollo);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }
        /*
         * Rilettura dell'anapro inserito. 
         */
        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');
        /*
         * Aggiungo allegato al protocollo.
         */
        $FilePathDest = $this->CopiaTmpAllegatoByUUID($Uuid);
        if (!$FilePathDest) {
            return false;
        }
        $NomeFile = $FileName = pathinfo($FilePathDest, PATHINFO_BASENAME);
        if (!$this->AggiungiAllegato($proProtocolla, $Anapro_rec, $FilePathDest, $NomeFile, $Uuid)) {
            return false;
        }
        /*
         *  Salvo metadati Fattura:
         */
        if (!$this->SalvaMetadatiFatturaSpacchettata($Anapro_rec, $AnaproPadre_rec, $Uuid)) {
            $Audit = 'Errore in salvataggio metadati fatt spacc. Uuid:  ' . $Uuid;
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            $Audit = 'Errore riscontrato: ' . $this->getErrMessage();
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            // Può non bloccarsi ma dare errore.
            // Dare un Out::msgInfo?
        } else {
            $Audit = 'Metadati Fattura Spacchettata Salvati - Uuid:  ' . $Uuid;
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        }
        /*
         * Aggiornamento dei metadati UUID 
         */
        $itaDocumentaleAlfrescoUtils = new itaDocumentaleAlfrescoUtils();
        /*
         * Istanzio oggetto protocollo ..
         */
        $numero = substr($Anapro_rec['PRONUM'], 4);
        $anno = substr($Anapro_rec['PRONUM'], 0, 4);
        $objProtocollo = proProtocollo::getInstance($this->proLib, $numero, $anno, $Anapro_rec['PROPAR'], '');
        /* Setto il dizionario per l'aggiornamento dei metadati */
        $itaDocumentaleAlfrescoUtils->setDizionario($objProtocollo->GetDizionarioDocumentale(self::ALLEGATO_PROT, $NomeFile));
        $ris = $itaDocumentaleAlfrescoUtils->aggiungiMetadatiProtocollazioneSuFattura($Uuid);
        if (!$ris) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in aggiornamento metadati Pacchetto Fattura Protocollato.');
            return false;
        }


        return $Anapro_rec;
    }

    public function SpacchettaFattureFlussi() {
        /* Chiamata a spacchettamento omnis */
        $utiEnte = new utiEnte();
        $anaent_26 = $this->proLib->GetAnaent('26');
        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
        $itaDocumentaleAlfrescoUtils = new itaDocumentaleAlfrescoUtils();
        $itaDocumentaleAlfrescoUtils->setCodiceAoo($anaent_26['ENTDE2']);
        $itaDocumentaleAlfrescoUtils->setCodiceEnte($ParametriEnte_rec['ISTAT']);
        $itaDocumentaleAlfrescoUtils->setCodiceUtente(App::$utente->getKey('nomeUtente'));
        $itaDocumentaleAlfrescoUtils->setDescrizioneEnte($ParametriEnte_rec['DENOMINAZIONE']);
        $result = $itaDocumentaleAlfrescoUtils->spacchettaTuttiIFlussi();
        $ListaResultUUID = $itaDocumentaleAlfrescoUtils->getResult();
//        Out::msgInfo('ListUUid', print_r($ListaResultUUID, true));
        if (!$result) {
            $this->setErrCode(-1);
            $this->setErrMessage($itaDocumentaleAlfrescoUtils->getErrMessage());
            return false;
        }
        $AnaproCreati = array();
        $ErroriRiscontrati = array();
        foreach ($ListaResultUUID as $ResultUUID) {
            if ($ResultUUID['ERRORE'] == 1) {
                // Continua semplicemente o accoda a array di errori?
                $ErroriRiscontrati[] = $ResultUUID['MESSAGGIO_ERR'] . ". Per l'UUID: " . $ResultUUID['UUID_FLUSSO'];
                continue;
            }
            if (!$this->ProtocollaListaUUID($ResultUUID['RISULTATO'])) {
                $ErroriRiscontrati[] = $this->getErrMessage() . ' - Errore in protocollazione Lista UUID. ' . print_r($ResultUUID['RISULTATO'], true);
//                $ArrayRitorno['STATO'] = 0;
//                $ArrayRitorno['SPACCHETTATI'] = $ListaResultUUID;
//                $ArrayRitorno['ERRORI'] = $ErroriRiscontrati;
//                $ArrayRitorno['CREATI'] = $AnaproCreati;
//                return $ArrayRitorno;// Deve fermare tutto?
            }
            $AnaproCreati[] = $this->risultatoRitorno;
        }
        $ArrayRitorno = array();
        $ArrayRitorno['STATO'] = 1;
        $ArrayRitorno['SPACCHETTATI'] = $ListaResultUUID;
        $ArrayRitorno['ERRORI'] = $ErroriRiscontrati;
        $ArrayRitorno['CREATI'] = $AnaproCreati;
        return $ArrayRitorno;
    }

    public function CheckAllegatoDaFirmare($rowidAllegato) {
        /*  1 Controllo se il documento è effettivamente da firmare.
         *  2 Controllo se il parametro è attivo: 
         *      Documento visibile solo dopo la firma
         * 3. Blocco se chi sta aprendo la trasmissione non è il firmatario.
         *
         */
        $anaent48_rec = $this->proLib->GetAnaent('48');
        if ($anaent48_rec['ENTDE2']) {
            $docfirma_check = $this->GetDocfirma($rowidAllegato, 'rowidanadoc');
            if ($docfirma_check) {
                // Cerco il creatore del protocollo.                
                if (!$docfirma_check['FIRDATA'] && !$docfirma_check['FIRANN']) {
                    $CodiceUtente = proSoggetto::getCodiceSoggettoFromIdUtente();
                    if ($CodiceUtente != $docfirma_check['FIRCOD'] && $CodiceUtente != $docfirma_check['FIRCODRICH']) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Occorre attendere che il firmatario apponga la firma al Documento prima di poter consultare il file.");
                        return false;
                    } else {
                        return true;
                    }
                    return false;
                }
            }
        }
        return true;
    }

    public function GetCopiaAnalogica($model, $NomeFile, $sorgFile, $anapro_rec, $Anadoc_rec = array(), $PosMarcat = array()) {
        // Preparo una copia temporanea del file
        $subPath = "proPDF-CopiaAnalogica-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);

        // Se non ho un anadocrec per ora fermo..
        // $Anadoc_rec = $this->proLib->GetAnadoc($anadoc_rowid, 'rowid');
        if (!$Anadoc_rec) {
            return false;
        }
        $docmeta = unserialize($Anadoc_rec['DOCMETA']);
        $Marcato = true;
        if ($docmeta['SEGNATURA'] != true) {
            $Marcato = false;
        }
        $ext = pathinfo($NomeFile, PATHINFO_EXTENSION);
        $tipoModello = '';
        $ArrSegnaturaExtra = array();
        switch (strtolower($ext)) {
            case 'pdf':
                break;
            case 'p7m':
                //$Marcato = false;
                $nomeBasefile = pathinfo($NomeFile, PATHINFO_BASENAME);
                $extNomeBase = pathinfo($nomeBasefile, PATHINFO_EXTENSION);
                if (strtolower($extNomeBase) != 'pdf') {
                    // Cerco '.pdf' nel nome 
                    if (strpos(strtolower($nomeBasefile), '.pdf') === false) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("La copia analogica può essere prodotta da file PDF o file PDF Firmati.");
                        return false;
                    } else {
                        $nomeBasefile = md5(rand() * time()) . '.pdf';
                    }
                }
                $returnDataP7m = $this->SbustaP7m($sorgFile, $nomeBasefile);
                if (!$returnDataP7m) {
                    return false;
                }
                $sorgFile = $returnDataP7m['FILE'];
                $ext = 'pdf';
                $tipoModello = 'p7m';
                // Preparo marcatura FIRMA:
                $ArrCompSegnaturaFirma = $this->ComponiSegnaturaFirmaDaElencoFirme($returnDataP7m['ELENCO_FIRME']);
                if ($ArrCompSegnaturaFirma) {
                    $ArrSegnaturaExtra = array_merge($ArrSegnaturaExtra, $ArrCompSegnaturaFirma);
                }
                break;
            case 'docx':
                $output = $this->ConvertiDocxInPdf(ConvertiDocxInPdf);
                if (!$output) {
                    return false;
                }
                $sorgFile = $output;
                $ext = 'pdf';
                break;
            default:
                $this->setErrCode(-1);
                $this->setErrMessage("La copia analogica può essere prodotta da file PDF.");
                return false;
                break;
        }
        /*
         * Se il file non è marcato devo aggiungere la segnatura
         * PER ORA NESSUNA SCELTA DI POSIZIONE.
         */
        if (!$Marcato) {
            $PosSegnatrua = array();
            if ($PosMarcat['SEGNATURA']) {
                $PosSegnatrua = $PosMarcat['SEGNATURA'];
            }
            $output = $this->ComponiPDFconSegnatura($anapro_rec, $sorgFile, $PosSegnatrua);
            if (!$output) {
                return false;
            }
            $sorgFile = $output;
        }

        $basename = md5(rand() * time()) . '.' . $ext;
        $DestinoFile = $tempPath . '/' . $basename;

        if (!@copy($sorgFile, $DestinoFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Errore durante la copia del file nell'ambiente temporaneo di lavoro.");
            return false;
        }
        $PosCopiaAnalog = array();
        if ($PosMarcat['COPIA']) {
            $PosCopiaAnalog = $PosMarcat['COPIA'];
        }
        $retFile = $this->ComponiPDFCopiaAnalogica($anapro_rec, $DestinoFile, $PosCopiaAnalog, $tipoModello, $ArrSegnaturaExtra);
        if (!$retFile) {
            return false;
        }
        Out::openDocument(utiDownload::getUrl($basename, $retFile));
        return true;
    }

    /**
     * 
     * @param type $elencoFirme
     * @return array();
     * 
     * $elencoFirme[N]['signer'] = 'FIRMATARIO'
     */
    public function ComponiSegnaturaFirmaDaElencoFirme($elencoFirme) {
        $ArrSegnatura = array();
        $segnaturaFir = 'Firmato digitalmente da ';
        foreach ($elencoFirme as $key => $firma) {
            $segnaturaFir .= $firma['signer'];
            if ($elencoFirme[($key + 1)]) {
                $segnaturaFir .= " e da ";
            }
        }
        // Fisse per ora..
        $ArrSegnatura[] = array(
            'STRING' => $segnaturaFir,
            'FIRST_PAGE' => 1,
            'X_COORD' => 10,
            'Y_COORD' => 830,
            'ROTAZ' => 0,
            'FONT-SIZE' => 6
        );
        return $ArrSegnatura;
    }

    public function ComponiPDFCopiaAnalogica($anapro_rec, $input, $PosMarcat = array(), $tipoModello = '', $ArrSegnaturaExtra = array()) {
        /* Controllo e prendo i parametri copi aanalogica */
        if (!$PosMarcat) {
            $PosMarcat = $this->GetPosMarcaturaCopiaAnalogica();
        }
        $segnaturaArr = $this->GetComposizioneCopiaAnalogica($anapro_rec, $input, $PosMarcat, $tipoModello);
        if ($ArrSegnaturaExtra) {
            $segnaturaArr = array_merge($segnaturaArr, $ArrSegnaturaExtra);
        }
        /* Composizione pdf segnatura multipla */
        $output = $this->ComponiPDFconSegnaturaMultipla($segnaturaArr, $input);
        if (!$output) {
            return false;
        }
        return $output;
    }

    public function GetComposizioneCopiaAnalogica($anapro_rec, $FilePath, $PosMarcat, $tipoModello = '') {
        /* Stringa copia analogica */
        $anaent_rec = $this->proLib->GetAnaent('51');
        $Stringa = $this->GetModelloCopiaAnalogica($anapro_rec, $tipoModello);

        /* Verifico righe stringa... */
        $StringaACapo = array();
        $StringaACapo = explode('\\n', $Stringa);

        $coordX = $PosMarcat['X_COORD'];
        $coordY = $PosMarcat['Y_COORD'];
        $rotaz = $PosMarcat['ROTAZ'];
        $font_size = 6;
        if ($anaent_rec['ENTDE3']) {
            $font_size = $anaent_rec['ENTDE3'];
        }

        $segnaturaArr = array();
        foreach ($StringaACapo as $key => $str_composizione) {
            $segnaturaArr[] = array(
//                'STRING' => utf8_decode($str_composizione),
                'STRING' => htmlspecialchars(utf8_encode($str_composizione), ENT_NOQUOTES, 'UTF-8'),
                'FIRST_PAGE' => $PosMarcat['FIRST_PAGE'],
                'X_COORD' => $coordX,
                'Y_COORD' => $coordY,
                'ROTAZ' => $rotaz,
                'FONT-SIZE' => $font_size
            );
            switch ($rotaz) {
                case 0:
                    $coordY = $coordY + $font_size;
                    break;
                case 180:
                    $coordY = $coordY - $font_size;
                    break;
                case 90:
                    $coordX = $coordX + $font_size;
                    break;
                case 270:
                    $coordX = $coordX - $font_size;
                    break;
            }
        }
        return $segnaturaArr;
    }

    public function GetModelloCopiaAnalogica($Anaorg_rec, $tipoModello = '') {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
        // Qui funzione per ricavare il modello base.
        $anaent_rec = $this->proLib->GetAnaent('51');
        $ModelliSegn = unserialize($anaent_rec['ENTVAL']);
        switch ($tipoModello) {
            case 'p7m':
                $modello = $ModelliSegn['DIGITALE'];
                break;

            default:
                $modello = $ModelliSegn['AUTOGRAFA'];
                break;
        }
//        $modello = $anaent_rec['ENTVAL'];

        $separatore = '';
        if ($anaent_rec['ENTDE3']) {
            $separatore = $anaent_rec['ENTDE1'];
        }
        if (!$modello) {
            'Copia Analogica eseguida da @{$CA_UTENTE}@ il @{$CA_DATA}@';
        }
        $proLibVar = new proLibVariabili();
        $proLibVar->setAnapro_rec($Anaorg_rec);

        $dictionaryValues = $proLibVar->getVariabiliSegnatura()->getAllData();
        $wsep = '';
        foreach ($dictionaryValues as $key => $valore) {
            $search = '@{$' . $key . '}@';
            if ($valore) {
                if (strpos($modello, $search) === 0) {
                    $wsep = '';
                } else {
                    $wsep = $separatore;
                }
            } else {
                $wsep = '';
            }
            $replacement = $wsep . $valore;
            $modello = str_replace($search, $replacement, $modello);
        }

        if (strpos($modello, '@{$') !== false) {
            return false;
        }
        if (strpos($modello, '}@') !== false) {
            return false;
        }

        return $modello;
    }

    public function ComponiPDFconSegnaturaMultipla($segnaturaArr, $input) {
        foreach ($segnaturaArr as $key => $segnatura) {
            $ret = '';
            $xmlPATH = pathinfo($input, PATHINFO_DIRNAME);
            $xmlFile = $xmlPATH . "/" . md5(rand() * time()) . ".xml";
            $xmlRes = fopen($xmlFile, "w");
            if (!file_exists($xmlFile)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in composizione PDF Marcato");
                return false;
            } else {
                $output = $xmlPATH . "/" . md5(rand() * microtime(true)) . "." . pathinfo($input, PATHINFO_EXTENSION);
                $xml = $this->CreaXmlPdf($segnatura['STRING'], $input, $output, $segnatura);
                fwrite($xmlRes, $xml);
                fclose($xmlRes);
                $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF/itaJPDF.jar ' . $xmlFile;
                exec($command, $ret);
                //
                $taskXml = false;
                foreach ($ret as $value) {
                    $arrayExec = explode("|", $value);
                    if ($arrayExec[1] == 00 && $arrayExec[0] == "OK") {
                        $taskXml = true;
                        break;
                    } else {
                        $errMsg = $arrayExec[0] . " - " . $arrayExec[1] . " - " . $arrayExec[2];
                    }
                }
                if ($taskXml == false) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($errMsg);
                    return false;
                } else {
                    if (!@rename($output, $input)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore nel rinominare il PDF $output");
                        return false;
                    }
                    @unlink($xmlFile);
                }
            }
        }
        return $input;
    }

    public function ConvertiDocxInPdf($sorgFile) {
        $docLib = new docLib();
        $output = $docLib->docx2Pdf($sorgFile, false, array(), false);
        if (!$output) {
            $this->setErrCode(-1);
            $this->setErrMessage($docLib->getErrMessage());
            return false;
        }
        return $output;
    }

    public function SalvaMetadatiFatturaSpacchettata($Anapro_rec, $AnaproPadre_rec, $Uuid) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        $FilePathDest = $this->CopiaTmpAllegatoByUUID($Uuid);
        if (!$FilePathDest) {
            return false;
        }
        /* Ricavo nome file fattura univoco. FileFatturaUnivoco es: IT01641790702_10AEE.xml.p7m */
        $Tabdag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $AnaproPadre_rec['ROWID'], 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
        if (!$Tabdag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Metadato FileFatturaUnivoco non trovato. ANAPRO ROWID: " . $AnaproPadre_rec['ROWID']);
            return false;
        }
        // Ricavo il $NomeFileXml
        $NomeFileFattura = $Tabdag_rec['TDAGVAL'];
        $ElementiFile = proSdi::GetElementiNomeFile($NomeFileFattura);
        $extFile = strtolower(pathinfo($ElementiFile['CodUnivocoFile'], PATHINFO_EXTENSION));
        if ($extFile == 'p7m') {
            // metto solo fino a .xml
            $NomeFileXml = itaLib::pathinfoFilename($ElementiFile['CodUnivocoFile'], PATHINFO_FILENAME);
        } else {
            // altrimenti è gia .xml
            $NomeFileXml = $ElementiFile['CodUnivocoFile'];
        }
        //$NomeFileXml = $ElementiFile['CodUnivocoFile'] . '.xml';

        $FileSdi = array('LOCAL_FILEPATH' => $FilePathDest, 'LOCAL_FILENAME' => $NomeFileXml);
        $NewcurrObjSdi = proSdi::getInstance($FileSdi);
        if (!$NewcurrObjSdi) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nell'istanziare proSdi.");
            return false;
        }
        /* Salvo i metadati fattura: */
        if (!$proLibTabDag->SalvataggioTagFatturaSdi($Anapro_rec, $NewcurrObjSdi, 'FATT_S_ELETTRONICA')) {
            $this->setErrCode(-1);
            $this->setErrMessage($proLibTabDag->getErrMessage());
            return false;
        }
        /* Aggiungo metadati NomeFileFatturaSpacchettata. */
        $TabDagFattS_rec = array();
        $TabDagFattS_rec['TDCLASSE'] = 'ANAPRO';
        $TabDagFattS_rec['TDROWIDCLASSE'] = $Anapro_rec['ROWID'];
        $TabDagFattS_rec['TDAGCHIAVE'] = 'NomeFileFatturaSpacchettata';
        $TabDagFattS_rec['TDPROG'] = 1;
        $TabDagFattS_rec['TDAGVAL'] = $NomeFileXml;
        $TabDagFattS_rec['TDAGSEQ '] = 999;
        $TabDagFattS_rec['TDAGFONTE '] = 'FATT_S_ELETTRONICA';
        $TabDagFattS_rec['TDAGSET'] = 'ANAPRO.' . $Anapro_rec['ROWID'] . '1';
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'TABDAG', 'ROWID', $TabDagFattS_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento Metadati Spacchetta " . $exc->getMessage());
            return false;
        }
        /* Copio i metadati messaggio */
        $Tabdag_tab = $proLibTabDag->GetTabdag('ANAPRO', 'codice', $AnaproPadre_rec['ROWID'], '', '', true, '', 'MESSAGGIO_SDI');
        foreach ($Tabdag_tab as $Tabdag_rec) {
            unset($Tabdag_rec['ROWID']);
            $Tabdag_rec['TDAGFONTE'] = 'FS_MESSAGGIO_SDI';
            $Tabdag_rec['TDROWIDCLASSE'] = $Anapro_rec['ROWID'];
            $Tabdag_rec['TDAGSET'] = 'ANAPRO' . '.' . $Anapro_rec['ROWID'] . '.' . $Tabdag_rec['TDPROG'];
            try {
                ItaDB::DBInsert($this->proLib->getPROTDB(), 'TABDAG', 'ROWID', $Tabdag_rec);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in inserimento Metadati Spacchetta " . $exc->getMessage());
                return false;
            }
        }

        return true;
    }

    public function CheckRiscontroFatturaSpacchettata($Anapro_rec) {
        // Chech Metadato su file EC.
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        return $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'UuidContabilita', '', false, '', 'MESSAGGIO_SDI');
    }

    public function CheckEsitoInvioFatturaSpacchettata($Anapro_rec) {
        // Chech Metadato su file EC.
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        return $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'InvioEsitoInContabilita', '', false, '', 'MESSAGGIO_SDI');
    }

    public function AccettaRifiutaFatturaSpacchettata($Anapro_rec) {
        /*
         * Lettura dei Parametri
         */
        $anaent_49 = $this->proLib->GetAnaent('49');
        $anaent_45 = $this->proLib->GetAnaent('45');
        $anaent_38 = $this->proLib->GetAnaent('38');

        /*
         * 1. Controlli per procedere con lo spacchettamento
         */


        /* Se non è attivo il parametro di spacchettamento, deve uscire. */
        if (!$anaent_49['ENTDE2']) {
            return true;
        }
        /* Controllo se già salvata su metadati alfresco ed esce */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        if ($this->CheckRiscontroFatturaSpacchettata($Anapro_rec)) {
            return true;
        }
        /* Controllo se è SDIP ed è attivo SDIP altrimenti esce */
        if ($Anapro_rec['PROCODTIPODOC'] != $anaent_38['ENTDE4'] || !$anaent_38['ENTDE4']) {
            return true;
        }
        /* Controllo se è un Tipo: EC - Se non è un EC esco. */
        $TabDagTipo_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'Tipo', '', false, '', 'MESSAGGIO_SDI');
        if (!$TabDagTipo_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del Tipo Messaggio.");
            return false;
        }
        if ($TabDagTipo_rec['TDAGVAL'] != 'EC') {
            return true;
        }

        /*
         * 2. Controlli di malformazione EFAS ed EC.
         *    Se non è presente il prot collegato c'è un qualche problema.
         */
        if (!$Anapro_rec['PROPRE'] || !$Anapro_rec['PROPARPRE']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Protocollo collegato non trovato. Impossibile salvare i metadati della contabilità.');
            return false;
        }
        /*
         * 3. Lettura protocollo collegato 
         *   Potrebbe essere il caso in cui si accetta direttamente dal flusso fattura.
         */
        $AnaproPre_rec = $this->proLib->GetAnapro($Anapro_rec['PROPRE'], 'codice', $Anapro_rec['PROPARPRE']);
        /* Controllo se il collegato è un EFAS. */
        if ($AnaproPre_rec['PROCODTIPODOC'] != $anaent_45['ENTDE5']) {
            // Cerco l'efas collegato, testato ad ATA. Serve introdurlo?
//            $sql = "SELECT * FROM ANAPRO WHERE PROPRE = '" . $Anapro_rec['PROPRE'] . "' AND PROPARPRE = '" . $Anapro_rec['PROPARPRE'] . "' AND PROCODTIPODOC = 'EFAS' ";
//            $AnaproPre_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
//            if (!$AnaproPre_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Protocollo collegato non è una fattura elettronica spacchettata: ' . $Anapro_rec['PROPRE'] . '/' . $Anapro_rec['PROPARPRE']);
            return false;
//            }
        }
        /*
         * 4. Lettura del file xml EFAS:
         *    Da utilizzare per accettare/rifiutare fattura
         */
        $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $AnaproPre_rec['ROWID'], 'NomeFileFatturaSpacchettata', '', false, '', 'FATT_S_ELETTRONICA');
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del NomeFileFatturaSpacchettata.");
            return false;
        }
        /* Reperisco l'uuid del file spacchettato da accettare. */
        $NomeFileSdi = $TabDag_rec['TDAGVAL'];
        $sql = "SELECT *
                    FROM ANADOC 
                    WHERE DOCNUM = '" . $AnaproPre_rec['PRONUM'] . "' AND 
                    DOCPAR = '" . $AnaproPre_rec['PROPAR'] . "' AND DOCNAME LIKE '%.xml' "; // 
        $Anadoc_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        $Uuid = $Anadoc_rec['DOCUUID'];

        /* Log Inizio Accetta/Rifiuta */
        $Audit = 'Inizio Accetta/Fifiuta Fattura con UUID:  ' . $Uuid;
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        /*
         * 5. Preparazione Oggetto per Accettazione/Rifiuto Fattura
         */
        $utiEnte = new utiEnte();
        $anaent_26 = $this->proLib->GetAnaent('26');
        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
        $itaDocumentaleAlfrescoUtils = new itaDocumentaleAlfrescoUtils();
        $itaDocumentaleAlfrescoUtils->setCodiceAoo($anaent_26['ENTDE2']);
        $itaDocumentaleAlfrescoUtils->setCodiceEnte($ParametriEnte_rec['ISTAT']);
        $itaDocumentaleAlfrescoUtils->setCodiceUtente(App::$utente->getKey('nomeUtente'));
        $itaDocumentaleAlfrescoUtils->setDescrizioneEnte($ParametriEnte_rec['DENOMINAZIONE']);
        /*
         * 6. Rilettura metadato di Esito EC 
         */
        $TabDagEsito_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'Esito', '', false, '', 'MESSAGGIO_SDI');
        if (!$TabDagEsito_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato del NomeFileFatturaSpacchettata.");
            return false;
        }
        $Esito = $TabDagEsito_rec['TDAGVAL'];
        /* Verifico se Accettare o Rifiutare la fattura */
        if ($Esito == 'EC01') {
            $result = $itaDocumentaleAlfrescoUtils->accettaFattura($Uuid);
        } else {
            /* In caso di rifiuto Ricavo il motivo del rifiuto */
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'Descrizione', '', false, '', 'MESSAGGIO_SDI');
            if (!$TabDag_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, impossibile trovare il metadato del Rifiuto Fattura: Descrizione.");
                return false;
            }
            $MotivoRifiuto = $TabDag_rec['TDAGVAL'];
            $result = $itaDocumentaleAlfrescoUtils->rifiutaFattura($Uuid, $MotivoRifiuto);
        }
        if (!$result) {
            /* Log Spacchettamento con errore. */
            $Audit = 'Errore Accettazione/Rifiuto Fattura con UUID:  ' . $Uuid . ' ' . $itaDocumentaleAlfrescoUtils->getErrMessage();
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            // Ritorno errore.
            $this->setErrCode(-1);
            $this->setErrMessage($itaDocumentaleAlfrescoUtils->getErrMessage());
            return false;
        }
        // RITORNA CONTENUTO!
        // Potrebbero ritornare entrambi? UUID e CONTENUTO in array?
        $RetUUID = $itaDocumentaleAlfrescoUtils->getResult();
        $Audit = 'Fattura Accetta/Fifiuta risultato UUID:  ' . $RetUUID;
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        /*
         * 7. Aggiungo i metadato uuid contabilita
         */
        $TabDag_NomeFileEC_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'NomeFileMessaggio', '', false, '', 'MESSAGGIO_SDI');
        $NewTbaDag_rec = array();
        $NewTbaDag_rec = $TabDag_NomeFileEC_rec;
        unset($NewTbaDag_rec['ROWID']);
        $NewTbaDag_rec['TDAGCHIAVE'] = 'UuidContabilita';
        $NewTbaDag_rec['TDAGSEQ'] = 998;
        $NewTbaDag_rec['TDAGVAL'] = $RetUUID;
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'TABDAG', 'ROWID', $NewTbaDag_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento Metadati Uuid Contabilita spacchettata. " . $exc->getMessage());
            return false;
        }
        include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
        // Elaboro il messaggio SDI ritornato.
        // 
        // Nome file letto dai metadati. del file ritornato:
        $Metadati = $this->GetMetadatiDocumentaleByUUID($RetUUID);
        if (!$Metadati) {
            $this->setErrCode(-1);
            $this->setErrMessage('Metadati UUID spacchettato non trovati.');
            return false;
        }
        if ($Metadati['com_descrizione']) {
            $NomeFile = $Metadati['com_descrizione'];
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile reperire nome file documento com_descrizione uuid: ' . $RetUUID);
            return false;
        }


        $destFile = 'DOCUUID:' . $RetUUID;
        $FileSdi = array('LOCAL_FILEPATH' => $destFile, 'LOCAL_FILENAME' => $NomeFile);
        $currObjSdi = proSdi::getInstance($FileSdi);
        $EstrattoMessaggio = $currObjSdi->getEstrattoMessaggio();

        /*
         * 8. Leggo ANADOC dell'EC.
         */

        $sql = "SELECT *
                    FROM ANADOC 
                    WHERE DOCNUM = '" . $Anapro_rec['PRONUM'] . "' AND 
                    DOCPAR = '" . $Anapro_rec['PROPAR'] . "' AND DOCNAME LIKE '%.xml' ";
        // qui potrebbe usare $NomeFile = $TabDag_NomeFileEC_rec['TDAGVAL']; per il docname da cercare.
        $AnadocEC_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);

        /*
         *  9. Inserisco l'anadocsave EC creato in precedenza:
         */
        $savedata = date('Ymd');
        $saveora = date('H:i:s');
        $saveutente = App::$utente->getKey('nomeUtente');
        $anadocSave_rec = $AnadocEC_rec;
        $anadocSave_rec['ROWID'] = '';
        $anadocSave_rec['SAVEDATA'] = $savedata;
        $anadocSave_rec['SAVEORA'] = $saveora;
        $anadocSave_rec['SAVEUTENTE'] = $saveutente;
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANADOCSAVE', 'ROWID', $anadocSave_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento ANADOC_SAVE EC." . $exc->getMessage());
            return false;
        }
        /*
         *  10. Aggiorno MessageIdCommittente:
         */
        $TabDag_MsgId_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'MessageIdCommittente', '', false, '', 'MESSAGGIO_SDI');
        $TabDag_MsgId_rec['TDAGVAL'] = $EstrattoMessaggio['MessageIdCommittente'];
        try {
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'TABDAG', 'ROWID', $TabDag_MsgId_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento TABDAG " . $exc->getMessage());
            return false;
        }
        /*
         *  Aggiorno NomeFileMessaggio:
         */
        $TabDag_NomeFile_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'NomeFileMessaggio', '', false, '', 'MESSAGGIO_SDI');
        $TabDag_NomeFile_rec['TDAGVAL'] = $EstrattoMessaggio['NomeFileMessaggio'];
        try {
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'TABDAG', 'ROWID', $TabDag_NomeFile_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento TABDAG " . $exc->getMessage());
            return false;
        }
        /*
         *  Aggiorno ProgUnivoco:
         */
        $TabDag_Prog_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'ProgUnivoco', '', false, '', 'MESSAGGIO_SDI');
        $TabDag_Prog_rec['TDAGVAL'] = $EstrattoMessaggio['ProgUnivoco'];
        try {
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'TABDAG', 'ROWID', $TabDag_Prog_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento TABDAG " . $exc->getMessage());
            return false;
        }


        /*
         * 11. Sovrascrivo il file:
         */
        $ContenutoFile = $this->GetUUIDBinary($RetUUID);
        if (!$ContenutoFile) {
            return false;
        }
        $protPath = $this->proLib->SetDirectory($AnadocEC_rec['DOCNUM'], $AnadocEC_rec['DOCPAR']);
        $filePathSorg = $protPath . "/" . $AnadocEC_rec['DOCFIL'];
        file_put_contents($filePathSorg, $ContenutoFile);


        /*
         * 12. Aggiorno UUID e dati allegato:
         */
        try {
            $AnadocEC_rec['DOCUUID'] = $RetUUID;
            $AnadocEC_rec['DOCMD5'] = md5_file($filePathSorg);
            $AnadocEC_rec['DOCSHA2'] = hash_file('sha256', $filePathSorg);
            $AnadocEC_rec['DOCNAME'] = $NomeFile;
            ItaDB::DBUpdate($this->proLib->getPROTDB(), 'ANADOC', 'ROWID', $AnadocEC_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore aggiornamento TABDAG " . $exc->getMessage());
            return false;
        }

        return true;
    }

    public function CheckInvioFatturaSpacchettata($Anapro_rec) {
        $anaent_49 = $this->proLib->GetAnaent('49');
        $anaent_38 = $this->proLib->GetAnaent('38');
        $anaent_45 = $this->proLib->GetAnaent('45');

        /* Controllo se è SDIP ed è attivo SDIP altrimenti esce */
        if ($Anapro_rec['PROCODTIPODOC'] != $anaent_38['ENTDE4'] || !$anaent_38['ENTDE4']) {
            return true;
        }
        /* Se non è attivo il parametro di spacchettamento, deve uscire. */
        if (!$anaent_49['ENTDE2']) {
            return true;
        }
        /* Se non c'è accettato/rifiutato in contabilita (stato 2 o 4) deve uscire. */
        if (!$this->CheckRiscontroFatturaSpacchettata($Anapro_rec)) {
            return true;
        }
        /* Se è gia inviato esito in contabilita non occorre rifarlo */
        if ($this->CheckEsitoInvioFatturaSpacchettata($Anapro_rec)) {
            return true;
        }

        /*
         * Predisposizione per aggiornamento dati in contbailita
         */
        $AnaproPre_rec = $this->proLib->GetAnapro($Anapro_rec['PROPRE'], 'codice', $Anapro_rec['PROPARPRE']);
        /* Controllo se il collegato è un EFAS. */
        if ($AnaproPre_rec['PROCODTIPODOC'] != $anaent_45['ENTDE5']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Protocollo collegato non è una fattura elettronica spacchettata: ' . $Anapro_rec['PROPRE'] . '/' . $Anapro_rec['PROPARPRE']);
            return false;
        }
        // Ricavo l'UUID fattura:
        $sql = "SELECT *
                    FROM ANADOC 
                    WHERE DOCNUM = '" . $AnaproPre_rec['PRONUM'] . "' AND 
                    DOCPAR = '" . $AnaproPre_rec['PROPAR'] . "' AND DOCNAME LIKE '%.xml' "; // 
        $Anadoc_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
        $Uuid = $Anadoc_rec['DOCUUID'];

        //  Utilizzo libreria tabDag.
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        $TabDagEsito_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'Esito', '', false, '', 'MESSAGGIO_SDI');
        $Esito = $TabDagEsito_rec['TDAGVAL'];
        /* Verifico se Accettare o Rifiutare la fattura */
        $Accettazione = false;
        if ($Esito == 'EC01') {
            $Accettazione = true;
        }
        $Audit = 'Aggiorno Esito in Contabilita: ' . $Esito . '. UUID:  ' . $Uuid;
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
        // Prendo oggetto documentale alfresco utilis 
        $itaDocumentaleAlfrescoUtils = $this->getObjItaDocumentaleAlfrescoUtils();
        $result = $itaDocumentaleAlfrescoUtils->aggiornaMetadatiInvioEsitoFattura($Uuid, $Accettazione);
        if (!$result) {
            /* Log Spacchettamento con errore. */
            $Audit = 'Errore Invio Esito Fattura con UUID:  ' . $Uuid . ' ' . $itaDocumentaleAlfrescoUtils->getErrMessage();
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            // Ritorno errore.
            $this->setErrCode(-1);
            $this->setErrMessage($itaDocumentaleAlfrescoUtils->getErrMessage());
            return false;
        }
        $Audit = 'Completato Aggiornamento Esito in Contabilita UUID:  ' . $Uuid;
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        $NewTbaDag_rec = array();
        $NewTbaDag_rec = $TabDagEsito_rec;
        unset($NewTbaDag_rec['ROWID']);
        $NewTbaDag_rec['TDAGCHIAVE'] = 'InvioEsitoInContabilita';
        $NewTbaDag_rec['TDAGSEQ'] = 997;
        $NewTbaDag_rec['TDAGVAL'] = 1;
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'TABDAG', 'ROWID', $NewTbaDag_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento Metadati InvioEsitoInContabilita. " . $exc->getMessage());
            return false;
        }

        return true;
    }

    public function CheckAllegatiAllaFirma($Pronum, $Propar) {
        /* Controllo se sono presenti allegati alla firma: almeno uno */
        $Allegati_tab = $this->proLib->caricaAllegatiProtocollo($Pronum, $Propar);
        foreach ($Allegati_tab as $allegato) {
            $docfirma_check = $this->GetDocfirma($allegato['ROWID'], 'rowidanadoc');
            if ($docfirma_check) {
                if (!$docfirma_check['FIRDATA'] && !$docfirma_check['FIRANN']) {
                    return true;
                }
            }
        }
        return false;
    }

    /*
     * Funzione per la verifica di un file pdf con firma incorporata adbe.pkcs7
     */

    public function CheckPdfFirmato_pkcs7($FilePdf, $CtrType = 'adbe.pkcs7.detached') {
        if (strpos(file_get_contents($FilePdf), $CtrType) !== false) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $ElencoRowidAnadoc Array di rowid ANADOC
     * @return array
     */
    public function CopiaAllegatiAnadoc($ElencoRowidAnadoc = array()) {
        // Preparo cartella temporanea
        $subPath = "copy-work-" . md5(microtime());
        $destFile = itaLib::createAppsTempPath($subPath);
        $ElencoAllegati = array();
        foreach ($ElencoRowidAnadoc as $rowidAnadoc) {
            $Anadoc_rec = $this->proLib->GetAnadoc($rowidAnadoc, 'rowid');
            if (!$Anadoc_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Documento con identificativo: $rowidAnadoc non trovato.");
                return false;
            }
            // Preparo nome temporaneo
            $randName = md5(rand() * time()) . "." . pathinfo($Anadoc_rec['DOCNAME'], PATHINFO_EXTENSION);
            $destTemporanea = $destFile . '/' . $randName;
            $risCopia = $this->CopiaDocAllegato($Anadoc_rec['ROWID'], $destTemporanea);
            if ($risCopia) {
                $ElencoAllegati[] = array(
                    'FILEPATH' => $destTemporanea,
                    'FILENAME' => $randName,
                    'FILEINFO' => $Anadoc_rec['DOCNAME'],
                    'DOCNAME' => $Anadoc_rec['DOCNAME']
                );
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in copia File: {$Anadoc_rec['FILEPATH']} su $destFile/$randName" . $this->getErrMessage());
                return false;
            }
        }
        return $ElencoAllegati;
    }

    public function CheckFatturaDecorrenza($idSdi, $numFattura = null) {
        /* Lettura dei metadati */
        // Controllo metadati fatture
        // Oppure controllo tabdag.
        // per metadati occorre controllare la fattura singola se risulta mt di essa.


        $props = array(
            'ger_uuid_padre' => $uuidFlusso
        );


        return true;
    }

    public function AssegnaDecorrenzaFattureAlfresco($idSdi, $numFattura = null) {
        // Se già aggiornati non devo fare nulla, torna a true.
        if (!$this->CheckFatturaDecorrenza($idSdi, $numFattura)) {
            return true;
        }
        /*
         * Lettura dei Librerie e Parametri Necessari:
         */
        $itaDocumentaleAlfrescoUtils = new itaDocumentaleAlfrescoUtils();
        $utiEnte = new utiEnte();
        $anaent_26 = $this->proLib->GetAnaent('26');
        $ParametriEnte_rec = $utiEnte->GetParametriEnte();
        /* Setto Parametri per Salvataggio File */
        $itaDocumentaleAlfrescoUtils->setCodiceAoo($anaent_26['ENTDE2']);
        $itaDocumentaleAlfrescoUtils->setCodiceEnte($ParametriEnte_rec['ISTAT']);
        $itaDocumentaleAlfrescoUtils->setCodiceUtente(App::$utente->getKey('nomeUtente'));
        $itaDocumentaleAlfrescoUtils->setDescrizioneEnte($ParametriEnte_rec['DENOMINAZIONE']);
        //  Aggiornamento metdati ricezione DTCP:
        if (!$itaDocumentaleAlfrescoUtils->aggiornaMetadatiRicezioneDTCP($idSdi, $numFattura)) {
            $this->setErrCode(-1);
            $this->setErrMessage($itaDocumentaleAlfrescoUtils->getErrMessage());
            return false;
        }
        // Aggiorno i metadati:
    }

    public function RipristinaMailDaProt($Rowid) {
        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
        $emlLib = new emlLib();
        $Anadoc_rec = $this->proLib->GetAnadoc($Rowid, 'rowid');
        $mail_rec = $emlLib->getMailArchivio($Anadoc_rec['DOCIDMAIL'], 'id');

        include_once ITA_BASE_PATH . '/apps/Mail/emlDbMailBox.class.php';
        $emlDbMailBox = new emlDbMailBox();

        $Audit = 'Rimozione tra gli allegati. Ripristino Mail da Protocollare IDMAIL: ' . $Anadoc_rec['DOCIDMAIL'];
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        $risultatoDb = $emlDbMailBox->updateClassForRowId($mail_rec['ROWID'], '@DA_PROTOCOLLARE@');
        if ($risultatoDb === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in aggiornamento MAIL da Protocollare. " . $emlDbMailBox->getLastMessage());
            return false;
        }
        /*
         * Anadocsave:
         */
        $savedata = date('Ymd');
        $saveora = date('H:i:s');
        $saveutente = App::$utente->getKey('nomeUtente');
        $anadocSave_rec = $Anadoc_rec;
        $anadocSave_rec['ROWID'] = '';
        $anadocSave_rec['SAVEDATA'] = $savedata;
        $anadocSave_rec['SAVEORA'] = $saveora;
        $anadocSave_rec['SAVEUTENTE'] = $saveutente;
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'ANADOCSAVE', 'ROWID', $anadocSave_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in inserimento Metadati Spacchetta " . $exc->getMessage());
            return false;
        }
        // Delete di Anadoc:
        try {
            ItaDB::DBDelete($this->proLib->getPROTDB(), 'ANADOC', 'ROWID', $Anadoc_rec['ROWID']);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in cancellazione ANADOC " . $exc->getMessage());
            return false;
        }
        // Cancello promail (Potrei lasciarlo..)
        $where = " IDMAIL = '" . $Anadoc_rec['DOCIDMAIL'] . "' ";
        $promail_rec = $this->proLib->getPromail($where);
        try {
            ItaDB::DBDelete($this->proLib->getPROTDB(), 'PROMAIL', 'ROWID', $promail_rec['ROWID']);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in cancellazione PROMAIL " . $exc->getMessage());
            return false;
        }
        $Audit = 'Rimozione tra gli allegati Terminata. ' . $Anadoc_rec['DOCIDMAIL'];
        $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        return true;
        //
    }

    public function InsertDocumentoTmp($NomeFile, $Stream) {
        $retDocumento = array();
        if (!$NomeFile) {
            return false;
        }
        $ext = pathinfo($NomeFile, PATHINFO_EXTENSION);
        $idUnivocoFile = date('Ymd') . '_' . md5(uniqid(microtime(true))) . '.' . $ext;
        // Cartella
        $tempProPath = $this->proLib->SetDirectory('', 'TEMP');
        $filePathDest = $tempProPath . "/" . $idUnivocoFile;
        if (!file_put_contents($filePathDest, base64_decode($Stream))) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia Allegato. Scrittura del file non riuscita.");
            return false;
        }
        $retDocumento['idunivoco'] = $idUnivocoFile;
        $retDocumento['hashfile'] = hash_file('sha256', $filePathDest);
        return $retDocumento;
        // Funzione per pulire i file present?..
    }

    public function getAllegatoTmp($idUnivoco, $hashFile = '') {
        $tempProPath = $this->proLib->SetDirectory('', 'TEMP');
        $filePath = $tempProPath . "/" . $idUnivoco;
        if (hash_file('sha256', $filePath) != $hashFile) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura file non riuscita.");
            return false;
        }
        return $filePath;
    }

    // DA TERMINARE PER CONTORLLI CARATTERI WINDOWS
    public function proteggiCaratteriAllegati($proArriAllegati) {
        foreach ($proArriAllegati as &$Allegato) {
            $Allegato['DOCNAME'] = preg_replace('/[*\\\\:<>?|"]/', '_', $Allegato['DOCNAME']);
        }
        return $proArriAllegati;
    }

    private function GetEstensioneP7m($NomeFile, $LivCtr = 3) {
        $Ctr = 1;
        $ext = strtolower(pathinfo($NomeFile, PATHINFO_EXTENSION));
        $Estensione = ".$ext";
        while ($ext === 'p7m') {
            $NomeFile = pathinfo($NomeFile, PATHINFO_FILENAME);
            $ext = strtolower(pathinfo($NomeFile, PATHINFO_EXTENSION));
            $Estensione = '.' . $ext . $Estensione;
            if ($Ctr == $LivCtr) {
                break;
            }
            $Ctr++;
        }
        $Estensione = substr($Estensione, 1);
        return $Estensione;
    }

    public function ScaricaAllegatiZipProtocollo($pronum, $propar) {
        /*
         * Se il file zip esiste, lo cancello
         */
        $subPathZip = "proZipFile-" . md5(microtime());
        $tempPathZip = itaLib::createAppsTempPath($subPathZip);
        $NomeFileZip = "allegati_prot_" . $pronum . '-' . $propar . ".zip";
        $fileZip = $tempPathZip . '/' . $NomeFileZip;
        if (file_exists($fileZip)) {
            if (!@unlink($fileZip)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore in cancellazione file ZIP.');
                return false;
            }
        }
        /*
         * Creo il file zip
         */
        $archiv = new ZipArchive();
        if (!$archiv->open($fileZip, ZipArchive::CREATE)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in Creazione file ZIP.');
            return false;
        }
        /*
         * Carica Allegati:
         */
        $Anadoc_tab = $this->proLib->caricaAllegatiProtocollo($pronum, $propar);
        foreach ($Anadoc_tab as $keyAlle => $elemento) {
            $archiv->addFromString($elemento['DOCNAME'], $this->GetDocBinary($elemento['ROWID']));
        }
        $archiv->close();

        Out::openDocument(utiDownload::getUrl($NomeFileZip, $fileZip));
        return true;
    }

    public function CheckSalvaSuAlfresco($allegato) {
        // Controllo salvataggio attivo solo per fatture.
        $anaent_49 = $this->proLib->GetAnaent('49');
        if ($anaent_49['ENTDE4']) {
            if ($allegato['TIPO_ALLEGATO'] != self::ALLEGATO_FATT && $allegato['TIPO_ALLEGATO'] != self::ALLEGATO_ANNESSO_FATT) {
                return false;
            }
        }
        return true;
    }

    public function salvaNoteMessage($anapro_rec) {
        $noteMessage = $this->getNoteMessage();
        // Salvo Note errore in salvatagio dati.
        if ($noteMessage) {
            include_once ITA_BASE_PATH . '/apps/Protocollo/proNote.class.php';
            $noteManager = proNoteManager::getInstance($this->proLib, proNoteManager::NOTE_CLASS_PROTOCOLLO, array("PRONUM" => $anapro_rec['PRONUM'], "PROPAR" => $anapro_rec['PROPAR']));
            foreach ($noteMessage as $nota) {
                $this->eqAudit->logEqEvent($this, array(
                    'DB' => $this->proLib->getPROTDB()->getDB(),
                    'DSet' => 'ANAPRO',
                    'Operazione' => '06',
                    'Estremi' => "Errore allegati {$anapro_rec['PRONUM']} " . $nota,
                    'Key' => $anapro_rec['ROWID']
                ));
                /*
                 * 2 Inserimento tra le note 
                 */
                $dati = array(
                    'OGGETTO' => "Errore in salvataggio allegato. ",
                    'TESTO' => "Errore riscontrato: " . $nota,
                    'CLASSE' => proNoteManager::NOTE_CLASS_PROTOCOLLO,
                    'CHIAVE' => array('PRONUM' => $anapro_rec['PRONUM'], 'PROPAR' => $anapro_rec['PROPAR'])
                );
                $noteManager->aggiungiNota($dati);
                $noteManager->salvaNote();
            }
        }
    }

    /**
     * 
     * @param type $TipoProt
     * @param type $Ufficio
     * @return boolean  
     *         Ritorna true se è obbligatorio aggiungere allegati per il protocollo
     *         Ritorna false se non è obbligatorio aggiungere allegati per il protocollo
     */
    public function CheckObbligoAllegatiProt($TipoProt, $Ufficio) {
        $anaent_32 = $this->proLib->GetAnaent('32');
        $anaent_56 = $this->proLib->GetAnaent('56');
        /* Controllo se allegait sono obbligatori */
        if ($anaent_32['ENTDE4'] == '2') {
            /* Controllo se parametro generale dell'ente permette di protocollare arrivi senza allegati. */
            if ($anaent_56['ENTDE3']) {
                if ($TipoProt != 'A') {
                    return true;
                }
            } else {
                /* Controllo se ufficio abilitato a protocollare arrivi senza allegati. */
                $Anauff_rec = $this->proLib->GetAnauff($Ufficio, 'codice');
                if (!$Anauff_rec['UFFNOALL']) {
                    return true;
                } else {
                    if ($TipoProt != 'A') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function AggiornaMetadatiSDI($AnaproRowid, $Uuid, $ListUUID) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';

        $proLibTabDag = new proLibTabDag();
        $Anapro_rec = $this->proLib->GetAnapro($AnaproRowid, 'rowid');

        $Tabdag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $AnaproRowid, 'IdentificativoSdI', '', false);
        $id_sdi = $Tabdag_rec['TDAGVAL'];

        $itaDocumentaleAlfrescoUtils = $this->getObjItaDocumentaleAlfrescoUtils();

        foreach ($ListUUID as $Uuid_riga) {
            // Aggiornamento Metadati Fattura
            $itaDocumentaleAlfrescoUtils = $this->getObjItaDocumentaleAlfrescoUtils();
            $result = $itaDocumentaleAlfrescoUtils->aggiornaIDSdiFattura($Uuid_riga, $id_sdi);
            if (!$result) {
                /* Log Spacchettamento con errore. */
                $Audit = 'Errore Aggiornamento Metadati SDI su Fattura UUID:  ' . $Uuid_riga . ' ' . $itaDocumentaleAlfrescoUtils->getErrMessage();
                $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
                // Ritorno errore.
                $this->setErrCode(-1);
                $this->setErrMessage($itaDocumentaleAlfrescoUtils->getErrMessage());
                return false;
            }
        }

        // Aggiornamento Metadati Flusso
        $result = $itaDocumentaleAlfrescoUtils->aggiornaIDSdiFlusso($Uuid, $id_sdi);
        if (!$result) {
            /* Log . */
            $Audit = 'Errore Aggiornamento Metadati SDI su Fattura UUID:  ' . $Uuid . ' ' . $itaDocumentaleAlfrescoUtils->getErrMessage();
            $this->eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            // Ritorno errore.
            $this->setErrCode(-1);
            $this->setErrMessage($itaDocumentaleAlfrescoUtils->getErrMessage());
            return false;
        }

        return true;
    }

}

?>