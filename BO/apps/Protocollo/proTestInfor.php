<?php

/**
 *
 * TEST infor jProtocollo
 *
 * PHP Version 5
 *
 * @category
 * @package    Protocollo
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2020 Italsoft srl
 * @license
 * @version    14.02.2020
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaInforClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaLeggiProtocollo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaInserisciArrivo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaInserisciPartenza.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaInserisciInterno.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaAllegaDocumento.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaConfermaSegnatura.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaInviaProtocollo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaLeggiAllegato.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaServiziRicerca.class.php');
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function proTestInfor() {
    $proTestInfor = new proTestInfor();
    $proTestInfor->parseEvent();
    return;
}

class proTestInfor extends itaModel {

    public $name_form = "proTestInfor";
    public $StreamDocumentoPrincipale;
    public $StreamDocumentoPrincipaleP;
    public $StreamDocumentoPrincipaleI;
    public $StreamDocumento;
    public $StreamDocumentoS;
    public $variabiliRicerca = 2;

    function __construct() {
        parent::__construct();
        $this->StreamDocumentoPrincipale = App::$utente->getKey($this->nameForm . '_StreamDocumentoPrincipale');
        $this->StreamDocumentoPrincipaleP = App::$utente->getKey($this->nameForm . '_StreamDocumentoPrincipaleP');
        $this->StreamDocumentoPrincipaleI = App::$utente->getKey($this->nameForm . '_StreamDocumentoPrincipaleI');
        $this->StreamDocumento = App::$utente->getKey($this->nameForm . '_StreamDocumento');
        $this->StreamDocumentoS = App::$utente->getKey($this->nameForm . '_StreamDocumentoS');
        //$this->variabiliRicerca = App::$utente->getKey($this->nameForm . '_variabiliRicerca');
    }

    function __destruct() {

        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_StreamDocumentoPrincipale', $this->StreamDocumentoPrincipale);
            App::$utente->setKey($this->nameForm . '_StreamDocumentoPrincipaleP', $this->StreamDocumentoPrincipaleP);
            App::$utente->setKey($this->nameForm . '_StreamDocumentoPrincipaleI', $this->StreamDocumentoPrincipaleI);
            App::$utente->setKey($this->nameForm . '_StreamDocumento', $this->StreamDocumento);
            App::$utente->setKey($this->nameForm . '_StreamDocumentoS', $this->StreamDocumentoS);
            //App::$utente->setKey($this->nameForm . '_variabiliRicerca', $this->variabiliRicerca);
        }
    }

    private function setClientConfig($InforClient) {
        $config = $_POST[$this->name_form . '_CONFIG'];
        $InforClient->setWebservices_uri($config['wsEndpoint']);
        $InforClient->setWebservices_wsdl($config['wsWsdl']);
        $InforClient->setNamespace($config['wsNamespace']);
    }

    private function setClientConfigBase($InforClient) {
        $config = $_POST[$this->name_form . '_CONFIG'];
        $InforClient->setWebservices_uri($config['wsEndpointBase']);
        $InforClient->setWebservices_wsdl($config['wsWsdlBase']);
        $InforClient->setNamespace($config['wsNamespaceBase']);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                itaLib::openForm($this->name_form, "", true, "desktopBody");
                Out::show($this->name_form);

                include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
                $devLib = new devLib();
                $uri = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOENDPOINT', false);
                $wsdl = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOWSDL', false);
                $user = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOUSER', false);
                $corrispondente = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOCORRISPONDENTE', false);
                $namespace = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLONAMESPACE', false);
                $uriBase = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JBASEENDPOINT', false);
                $wsdlBase = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JBASEWSDL', false);
                $namespaceBase = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JBASENAMESPACE', false);


                //inizializzo i valori di configurazione della chiamata
                Out::valore($this->name_form . "_CONFIG[wsEndpoint]", $uri['CONFIG']);
                Out::valore($this->name_form . "_CONFIG[wsWsdl]", $wsdl['CONFIG']);
                Out::valore($this->name_form . "_CONFIG[wsUser]", $user['CONFIG']);
                Out::valore($this->name_form . "_CONFIG[wsCorrispondente]", $corrispondente['CONFIG']);
                Out::valore($this->name_form . "_CONFIG[wsNamespace]", $namespace['CONFIG']);
                Out::valore($this->name_form . "_CONFIG[wsEndpointBase]", $uriBase['CONFIG']);
                Out::valore($this->name_form . "_CONFIG[wsWsdlBase]", $wsdlBase['CONFIG']);
                Out::valore($this->name_form . "_CONFIG[wsNamespaceBase]", $namespaceBase['CONFIG']);

                Out::valore($this->name_form . "_ServiziRicerca_siglaCampoModulo", "LISTA_ETICHETTE");

                Out::setFocus('', $this->name_form . "_CONFIG[wsEndpoint]");
                break;

            case 'onClick':
                switch ($_POST['id']) {

                    case $this->name_form . '_callLeggiProtocollo':
                        $InforClient = new itaInforClient();
                        $this->setClientConfig($InforClient);
                        $Leggiprotocollo = new itaLeggiProtocollo();
                        $Leggiprotocollo->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        $Leggiprotocollo->setNumero($_POST[$this->name_form . '_LeggiProtocollo_numero']);
                        $Leggiprotocollo->setAnno($_POST[$this->name_form . '_LeggiProtocollo_anno']);
                        $Leggiprotocollo->setAllegati($_POST[$this->name_form . '_LeggiProtocollo_allegati']);
                        $ret = $InforClient->ws_leggiProtocollo($Leggiprotocollo);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $arr_risultato = $InforClient->getResult();
                        $risultato = print_r($arr_risultato, true);
                        if (isset($arr_risultato['protocollo']['documenti'])) {
                            $Documenti = $arr_risultato['protocollo']['documenti']['documento'];
                            if (!$Documenti[0]) {
                                $Documenti = array($Documenti);
                            }
                            foreach ($Documenti as $key => $Documento) {
                                $Filename = './' . App::$utente->getkey('privPath') . '/' . App::$utente->getKey('TOKEN') . "-" . $Documento['nomeFile'];
                                $ptr = fopen($Filename, 'wb');
                                fwrite($ptr, base64_decode($Documento['file']));
                                fclose($ptr);
                                Out::openDocument(utiDownload::getUrl($Documento['nomeFile'], $Filename));
                            }
                        }
                        Out::msgInfo("leggiProtocollo Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;
                    case $this->name_form . '_callInserisciArrivo':
                        $InforClient = new itaInforClient();
                        $this->setClientConfig($InforClient);
                        $InserisciArrivo = new itaInserisciArrivo();
                        //username
                        $InserisciArrivo->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        //registro
                        if ($_POST[$this->name_form . '_Registro_codice'] != '') {
                            $registro = array(
                                'codice' => $_POST[$this->name_form . '_Registro_codice'],
                                'descrizione' => $_POST[$this->name_form . '_Registro_descrizione']
                            );
                            $InserisciArrivo->setRegistro($registro);
                        }
                        //sezione
                        if ($_POST[$this->name_form . '_Sezione_codice'] != '') {
                            $sezione = array(
                                'codice' => $_POST[$this->name_form . '_Sezione_codice'],
                                'descrizione' => $_POST[$this->name_form . '_Sezione_descrizione']
                            );
                            $InserisciArrivo->setSezione($sezione);
                        }
                        //corrispondente
                        if ($_POST[$this->name_form . '_Corrispondente_codice'] != '') {
                            $corrispondente = array(
                                'codice' => $_POST[$this->name_form . '_Corrispondente_codice'],
                                'descrizione' => $_POST[$this->name_form . '_Corrispondente_descrizione']
                            );
                            $InserisciArrivo->setCorrispondente($corrispondente);
                        }
                        //soggetti (forma ridotta)
                        $InserisciArrivo->setSoggetti(array(
                            'denominazione' => $_POST[$this->name_form . '_InserisciArrivo_denominazione_soggetto'],
                            'indirizzo' => $_POST[$this->name_form . '_InserisciArrivo_indirizzo_soggetto']
                        ));
                        //smistamento (forma ridotta)
                        $smistamenti = array();
                        $smistamenti[] = array(
                            'codice' => $_POST[$this->name_form . '_InserisciArrivo_corrispondente_smistamento']
                        );
                        $InserisciArrivo->setSmistamenti($smistamenti);
                        //oggetto
                        $InserisciArrivo->setOggetto($_POST[$this->name_form . '_InserisciArrivo_oggetto']);
                        //classificazione
                        if ($_POST[$this->name_form . '_Classificazione_titolario'] != '') {
                            $classificazione = array();
                            $classificazione['titolario'] = $_POST[$this->name_form . '_Classificazione_titolario'];
                            if ($_POST[$this->name_form . '_Classificazione_f_anno'] != '') {
                                $classificazione['fascicolo'] = array();
                                $classificazione['fascicolo']['anno'] = $_POST[$this->name_form . '_Classificazione_f_anno'];
                                $classificazione['fascicolo']['numero'] = $_POST[$this->name_form . '_Classificazione_f_numero'];
                            }
                            $InserisciArrivo->setClassificazione($classificazione);
                        }
                        //dataRicezione
                        if ($_POST[$this->name_form . '_InserisciArrivo_dataRicezione'] != '') {
                            $InserisciArrivo->setDataRicezione($_POST[$this->name_form . '_InserisciArrivo_dataRicezione']);
                        }
                        //documento
                        if ($_POST[$this->name_form . '_DocumentoPrincipale_nomeFile'] != '') {
                            $documento = array();
                            $documento['titolo'] = $_POST[$this->name_form . '_DocumentoPrincipale_descrizione'];
                            $documento['nomeFile'] = $_POST[$this->name_form . '_DocumentoPrincipale_nomeFile'];
                            $documento['file'] = $this->StreamDocumentoPrincipale;
                        }
                        $InserisciArrivo->setDocumento($documento);
                        //segnatura
                        app::log('conferma segnatura');
                        app::log($_POST[$this->name_form . '_InserisciArrivo_confermaSegnatura']);
                        $InserisciArrivo->setConfermaSegnatura($_POST[$this->name_form . '_InserisciArrivo_confermaSegnatura']);


                        $ret = $InforClient->ws_inserisciArrivo($InserisciArrivo);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = print_r($InforClient->getResult(), true);
                        Out::msgInfo("inserisciArrivo Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;

                    case $this->name_form . '_callAllegaDocumento':
                        $InforClient = new itaInforClient();
                        $this->setClientConfig($InforClient);
                        $AllegaDocumento = new itaAllegaDocumento();
                        //username
                        $AllegaDocumento->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        //riferimento
                        if ($_POST[$this->name_form . '_Riferimento_anno'] != '') {
                            $riferimento = array();
                            $riferimento['anno'] = $_POST[$this->name_form . '_Riferimento_anno'];
                            $riferimento['numero'] = $_POST[$this->name_form . '_Riferimento_numero'];
                            if ($_POST[$this->name_form . '_RegistroRif_codice'] != '') {
                                $riferimento['registro'] = array();
                                $riferimento['registro']['codice'] = $_POST[$this->name_form . '_RegistroRif_codice'];
                                $riferimento['registro']['descrizione'] = $_POST[$this->name_form . '_RegistroRif_descrizione'];
                            }
                            $AllegaDocumento->setRiferimento($riferimento);
                        }
                        //titolo
                        if ($_POST[$this->name_form . '_Documento_titolo'] != '') {
                            $AllegaDocumento->setTitolo($_POST[$this->name_form . '_Documento_titolo']);
                        }
                        //volume
                        if ($_POST[$this->name_form . '_Volume_codice'] != '') {
                            $volume = array(
                                'codice' => $_POST[$this->name_form . '_Volume_codice'],
                                'descrizione' => $_POST[$this->name_form . '_Volume_descrizione']
                            );
                            $AllegaDocumento->setVolume($volume);
                        }
                        //formato
                        if ($_POST[$this->name_form . '_Formato_codice'] != '') {
                            $formato = array(
                                'codice' => $_POST[$this->name_form . '_Formato_codice'],
                                'descrizione' => $_POST[$this->name_form . '_Formato_descrizione']
                            );
                            $AllegaDocumento->setFormato($formato);
                        }
                        //nomeFile
                        $AllegaDocumento->setNomeFile($_POST[$this->name_form . '_Documento_nomeFile']);
                        //file
                        if (isset($this->StreamDocumento)) {
                            $AllegaDocumento->setFile($this->StreamDocumento);
                        }

                        $ret = $InforClient->ws_allegaDocumento($AllegaDocumento);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = print_r($InforClient->getResult(), true);
                        Out::msgInfo("allegaDocumento Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;

                    case $this->name_form . '_callConfermaSegnatura':
                        $InforClient = new itaInforClient();
                        $this->setClientConfig($InforClient);
                        $ConfermaSegnatura = new itaConfermaSegnatura();
                        //username
                        $ConfermaSegnatura->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        //riferimento
                        if ($_POST[$this->name_form . '_RiferimentoS_anno'] != '') {
                            $riferimento = array();
                            $riferimento['anno'] = $_POST[$this->name_form . '_RiferimentoS_anno'];
                            $riferimento['numero'] = $_POST[$this->name_form . '_RiferimentoS_numero'];
                            if ($_POST[$this->name_form . '_RegistroS_codice'] != '') {
                                $riferimento['registro'] = array();
                                $riferimento['registro']['codice'] = $_POST[$this->name_form . '_RegistroS_codice'];
                                $riferimento['registro']['descrizione'] = $_POST[$this->name_form . '_RegistroS_descrizione'];
                            }
                            $ConfermaSegnatura->setRiferimento($riferimento);
                        }
                        //titolo
                        if ($_POST[$this->name_form . '_DocumentoS_titolo'] != '') {
                            $ConfermaSegnatura->setTitolo($_POST[$this->name_form . '_DocumentoS_titolo']);
                        }
                        //volume
                        if ($_POST[$this->name_form . '_VolumeS_codice'] != '') {
                            $volume = array(
                                'codice' => $_POST[$this->name_form . '_VolumeS_codice'],
                                'descrizione' => $_POST[$this->name_form . '_VolumeS_descrizione']
                            );
                            $ConfermaSegnatura->setVolume($volume);
                        }
                        //formato
                        if ($_POST[$this->name_form . '_FormatoS_codice'] != '') {
                            $formato = array(
                                'codice' => $_POST[$this->name_form . '_FormatoS_codice'],
                                'descrizione' => $_POST[$this->name_form . '_FormatoS_descrizione']
                            );
                            $ConfermaSegnatura->setFormato($formato);
                        }
                        //nomeFile
                        $ConfermaSegnatura->setNomeFile($_POST[$this->name_form . '_DocumentoS_nomeFile']);
                        //file
                        if (isset($this->StreamDocumentoS)) {
                            $ConfermaSegnatura->setFile($this->StreamDocumentoS);
                        }

                        $ret = $InforClient->ws_confermaSegnatura($ConfermaSegnatura);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = print_r($InforClient->getResult(), true);
                        Out::msgInfo("confermaSegnatura Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;

                    case $this->name_form . '_callInviaProtocollo':
                        $InforClient = new itaInforClient();
                        $this->setClientConfig($InforClient);
                        $InviaProtocollo = new itaInviaProtocollo();
                        //username
                        $InviaProtocollo->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        //riferimento
                        if ($_POST[$this->name_form . '_RiferimentoIP_anno'] != '') {
                            $riferimento = array();
                            $riferimento['anno'] = $_POST[$this->name_form . '_RiferimentoIP_anno'];
                            $riferimento['numero'] = $_POST[$this->name_form . '_RiferimentoIP_numero'];
                            if ($_POST[$this->name_form . '_RegistroIP_codice'] != '') {
                                $riferimento['registro'] = array();
                                $riferimento['registro']['codice'] = $_POST[$this->name_form . '_RegistroIP_codice'];
                                $riferimento['registro']['descrizione'] = $_POST[$this->name_form . '_RegistroIP_descrizione'];
                            }
                            $InviaProtocollo->setRiferimento($riferimento);
                        }
                        //account
                        if ($_POST[$this->name_form . '_InviaProtocollo_account'] != '') {
                            $InviaProtocollo->setAccount($_POST[$this->name_form . '_InviaProtocollo_account']);
                        }

                        $ret = $InforClient->ws_inviaProtocollo($InviaProtocollo);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = print_r($InforClient->getResult(), true);
                        Out::msgInfo("inviaProtocollo Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;

                    case $this->name_form . '_callLeggiAllegato':
                        $InforClient = new itaInforClient();
                        $this->setClientConfig($InforClient);
                        $LeggiAllegato = new itaLeggiAllegato();
                        //username
                        $LeggiAllegato->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        //riferimento
                        if ($_POST[$this->name_form . '_RiferimentoLA_anno'] != '') {
                            $riferimento = array();
                            $riferimento['anno'] = $_POST[$this->name_form . '_RiferimentoLA_anno'];
                            $riferimento['numero'] = $_POST[$this->name_form . '_RiferimentoLA_numero'];
                            if ($_POST[$this->name_form . '_RegistroLA_codice'] != '') {
                                $riferimento['registro'] = array();
                                $riferimento['registro']['codice'] = $_POST[$this->name_form . '_RegistroLA_codice'];
                                $riferimento['registro']['descrizione'] = $_POST[$this->name_form . '_RegistroLA_descrizione'];
                            }
                            $LeggiAllegato->setRiferimento($riferimento);
                        }
                        //progressivo
                        if ($_POST[$this->name_form . '_LeggiAllegato_progressivo'] != '') {
                            $LeggiAllegato->setProgressivo($_POST[$this->name_form . '_LeggiAllegato_progressivo']);
                        }

                        $ret = $InforClient->ws_leggiAllegato($LeggiAllegato);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        //$risultato = print_r($InforClient->getResult(), true);
                        $risultato = $InforClient->getResult();
                        app::log('risultato');
                        app::log($risultato);
                        if ($risultato['esito'] == 'OK') {
                            $fileBase64 = $risultato['documento']['file'];
                            $file_decoded = base64_decode($fileBase64);
                            if (!@is_dir(itaLib::getPrivateUploadPath())) {
                                if (!itaLib::createPrivateUploadPath()) {
                                    Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                    $this->returnToParent();
                                }
                            }
                            $Nome_file = itaLib::getPrivateUploadPath() . "/" . $risultato['documento']['nomeFile'];
                            //$Nome_file = $dati['CartellaAllegati'] . "/XMLINFO.xml";
                            $File = fopen($Nome_file, "w+");
                            if (!file_exists($Nome_file)) {
                                return false;
                            } else {
                                //$xml = $this->CreaXML($dati, $PRAM_DB);
                                fwrite($File, $file_decoded);
                                fclose($File);
                            }
                            app::log('percorso file');
                            app::log($Nome_file);
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $risultato['documento']['nomeFile'], $Nome_file
                                    )
                            );
                        }
                        //Out::msgInfo("inviaProtocollo Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;

                    //gestione dell'upload del Documento Principale
                    case $this->name_form . '_DocumentoPrincipale_stream':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            $fileName = $_POST['file'];
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $fileName;
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        Out::valore($this->name_form . "_DocumentoPrincipale_nomeFile", $fileName);
                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            //fclose($fp);
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumentoPrincipale = $base64File;
                        break;
                    //fine upload del file principale
                    //
                    //gestione dell'upload del Documento Principale Partenza
                    case $this->name_form . '_DocumentoPrincipaleP_stream':
                        app::log('doc prin. partenza');
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            $fileName = $_POST['file'];
                            app::log('uplFile');
                            app::log($uplFile);
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $fileName;
                            app::log('destinazione');
                            app::log($destFile);
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        Out::valore($this->name_form . "_DocumentoPrincipaleP_nomeFile", $fileName);
                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            //fclose($fp);
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumentoPrincipaleP = $base64File;
                        break;
                    //gestione dell'upload del Documento Principale Interno
                    case $this->name_form . '_DocumentoPrincipaleI_stream':
                        app::log('doc prin. interno');
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            $fileName = $_POST['file'];
                            app::log('uplFile');
                            app::log($uplFile);
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $fileName;
                            app::log('destinazione');
                            app::log($destFile);
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        Out::valore($this->name_form . "_DocumentoPrincipaleI_nomeFile", $fileName);
                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            //fclose($fp);
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumentoPrincipaleI = $base64File;
                        break;

                    case $this->name_form . '_Documento_stream':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            $fileName = $_POST['file'];
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $fileName;
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        Out::valore($this->name_form . "_Documento_nomeFile", $fileName);
                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            //fclose($fp);
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumento = $base64File;
                        break;

                    case $this->name_form . '_DocumentoS_stream':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            $fileName = $_POST['file'];
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $fileName;
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        Out::valore($this->name_form . "_DocumentoS_nomeFile", $fileName);
                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            //fclose($fp);
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumentoS = $base64File;
                        break;

                    case $this->name_form . '_callInserisciPartenza':
                        $InforClient = new itaInforClient();
                        $this->setClientConfig($InforClient);
                        $InserisciPartenza = new itaInserisciPartenza();
                        //username
                        $InserisciPartenza->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        //registro
                        if ($_POST[$this->name_form . '_RegistroP_codice'] != '') {
                            $registro = array(
                                'codice' => $_POST[$this->name_form . '_RegistroP_codice'],
                                'descrizione' => $_POST[$this->name_form . '_RegistroP_descrizione']
                            );
                            $InserisciPartenza->setRegistro($registro);
                        }
                        //sezione
                        if ($_POST[$this->name_form . '_SezioneP_codice'] != '') {
                            $sezione = array(
                                'codice' => $_POST[$this->name_form . '_SezioneP_codice'],
                                'descrizione' => $_POST[$this->name_form . '_SezioneP_descrizione']
                            );
                            $InserisciPartenza->setSezione($sezione);
                        }
                        //corrispondente
                        if ($_POST[$this->name_form . '_CorrispondenteP_codice'] != '') {
                            $corrispondente = array(
                                'codice' => $_POST[$this->name_form . '_CorrispondenteP_codice'],
                                'descrizione' => $_POST[$this->name_form . '_CorrispondenteP_descrizione']
                            );
                            $InserisciPartenza->setCorrispondente($corrispondente);
                        }
                        //mittententerno
                        if ($_POST[$this->name_form . '_MittenteInterno_codice'] != '') {
                            $mittint = array(
                                'corrispondente' => array(
                                    'codice' => $_POST[$this->name_form . '_MittenteInterno_codice'],
                                    'descrizione' => $_POST[$this->name_form . '_MittenteInterno_descrizione']
                                )
                            );
                            $InserisciPartenza->setMittenteInterno($mittint);
                        }
                        //soggetti (forma ridotta)
                        $InserisciPartenza->setSoggetti(array(
                            'denominazione' => $_POST[$this->name_form . '_InserisciPartenza_denominazione_soggetto'],
                            'indirizzo' => $_POST[$this->name_form . '_InserisciPartenza_indirizzo_soggetto']
                        ));
                        //oggetto
                        $InserisciPartenza->setOggetto($_POST[$this->name_form . '_InserisciPartenza_oggetto']);
                        //smistamento (forma ridotta)
                        $smistamenti = array();
                        $smistamenti[] = array(
                            'codice' => $_POST[$this->name_form . '_InserisciPartenza_corrispondente_smistamento']
                        );
                        $InserisciPartenza->setSmistamenti($smistamenti);
                        //classificazione
                        if ($_POST[$this->name_form . '_Classificazione_titolario'] != '') {
                            $classificazione = array();
                            $classificazione['titolario'] = $_POST[$this->name_form . '_ClassificazioneP_titolario'];
                            if ($_POST[$this->name_form . '_Classificazione_f_anno'] != '') {
                                $classificazione['fascicolo'] = array();
                                $classificazione['fascicolo']['anno'] = $_POST[$this->name_form . '_ClassificazioneP_f_anno'];
                                $classificazione['fascicolo']['numero'] = $_POST[$this->name_form . '_ClassificazioneP_f_numero'];
                            }
                            $InserisciPartenza->setClassificazione($classificazione);
                        }
                        //dataInvio
                        if ($_POST[$this->name_form . '_InserisciPartenza_dataInvio'] != '') {
                            $InserisciPartenza->setDataInvio($_POST[$this->name_form . '_InserisciPartenza_dataInvio']);
                        }
                        //documento
                        if ($_POST[$this->name_form . '_DocumentoPrincipaleP_nomeFile'] != '') {
                            $documento = array();
                            $documento['titolo'] = $_POST[$this->name_form . '_DocumentoPrincipaleP_descrizione'];
                            $documento['nomeFile'] = $_POST[$this->name_form . '_DocumentoPrincipaleP_nomeFile'];
                            app::log('file ');
                            app::log($this->StreamDocumentoPrincipaleP);
                            $documento['file'] = $this->StreamDocumentoPrincipaleP;
                        }
                        $InserisciPartenza->setDocumento($documento);


                        $ret = $InforClient->ws_inserisciPartenza($InserisciPartenza);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = print_r($InforClient->getResult(), true);
                        Out::msgInfo("inserisciPartenza Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;
                    case $this->name_form . '_callInserisciInterno':
                        $InforClient = new itaInforClient();
                        $this->setClientConfig($InforClient);
                        $InserisciInterno = new itaInserisciInterno();
                        //username
                        $InserisciInterno->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        //registro
                        if ($_POST[$this->name_form . '_RegistroI_codice'] != '') {
                            $registro = array(
                                'codice' => $_POST[$this->name_form . '_RegistroI_codice'],
                                'descrizione' => $_POST[$this->name_form . '_RegistroI_descrizione']
                            );
                            $InserisciInterno->setRegistro($registro);
                        }
                        //sezione
                        if ($_POST[$this->name_form . '_SezioneI_codice'] != '') {
                            $sezione = array(
                                'codice' => $_POST[$this->name_form . '_SezioneI_codice'],
                                'descrizione' => $_POST[$this->name_form . '_SezioneI_descrizione']
                            );
                            $InserisciInterno->setSezione($sezione);
                        }
                        //corrispondente
                        if ($_POST[$this->name_form . '_CorrispondenteI_codice'] != '') {
                            $corrispondente = array(
                                'codice' => $_POST[$this->name_form . '_CorrispondenteI_codice'],
                                'descrizione' => $_POST[$this->name_form . '_CorrispondenteI_descrizione']
                            );
                            $InserisciInterno->setCorrispondente($corrispondente);
                        }
                        //mittententerno
                        if ($_POST[$this->name_form . '_MittenteInternoI_codice'] != '') {
                            $mittint = array(
                                'corrispondente' => array(
                                    'codice' => $_POST[$this->name_form . '_MittenteInternoI_codice'],
                                    'descrizione' => $_POST[$this->name_form . '_MittenteInternoI_descrizione']
                                )
                            );
                            $InserisciInterno->setMittenteInterno($mittint);
                        }

                        //oggetto
                        $InserisciInterno->setOggetto($_POST[$this->name_form . '_InserisciInterno_oggetto']);
                        //smistamento (forma ridotta)
                        $smistamenti = array();
                        $smistamenti[] = array(
                            'codice' => $_POST[$this->name_form . '_InserisciInterno_corrispondente_smistamento']
                        );
                        $InserisciInterno->setSmistamenti($smistamenti);
                        //classificazione
                        if ($_POST[$this->name_form . '_ClassificazioneI_titolario'] != '') {
                            $classificazione = array();
                            $classificazione['titolario'] = $_POST[$this->name_form . '_ClassificazioneI_titolario'];
                            if ($_POST[$this->name_form . '_ClassificazioneI_f_anno'] != '') {
                                $classificazione['fascicolo'] = array();
                                $classificazione['fascicolo']['anno'] = $_POST[$this->name_form . '_ClassificazioneI_f_anno'];
                                $classificazione['fascicolo']['numero'] = $_POST[$this->name_form . '_ClassificazioneI_f_numero'];
                            }
                            $InserisciInterno->setClassificazione($classificazione);
                        }
                        //dataInvio
                        if ($_POST[$this->name_form . '_InserisciInterno_dataInvio'] != '') {
                            $InserisciInterno->setDataInvio($_POST[$this->name_form . '_InserisciInterno_dataInvio']);
                        }
                        //documento
                        if ($_POST[$this->name_form . '_DocumentoPrincipaleI_nomeFile'] != '') {
                            $documento = array();
                            $documento['titolo'] = $_POST[$this->name_form . '_DocumentoPrincipaleI_descrizione'];
                            $documento['nomeFile'] = $_POST[$this->name_form . '_DocumentoPrincipaleI_nomeFile'];
                            app::log('file ');
                            app::log($this->StreamDocumentoPrincipaleI);
                            $documento['file'] = $this->StreamDocumentoPrincipaleI;
                        }
                        $InserisciInterno->setDocumento($documento);


                        $ret = $InforClient->ws_inserisciInterno($InserisciInterno);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = print_r($InforClient->getResult(), true);
                        Out::msgInfo("inserisciPartenza Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;
                    case $this->name_form . '_callServiziRicerca':
                        $InforClient = new itaInforClient();
                        $this->setClientConfigBase($InforClient);

                        $arrayVariabili = array();
                        for ($numVar = 0; $numVar <= $this->variabiliRicerca; $numVar++) {
                            if ($_POST[$this->name_form . "_ServiziRicerca_variabile$numVar"] && $_POST[$this->name_form . "_ServiziRicerca_valore$numVar"]) {
                                $arrayVariabili[$_POST[$this->name_form . "_ServiziRicerca_variabile$numVar"]] = $_POST[$this->name_form . "_ServiziRicerca_valore$numVar"];
                            }
                        }

                        $serviziRicerca = new itaServiziRicerca();
                        $serviziRicerca->setUsername($_POST[$this->name_form . '_CONFIG']['wsUser']);
                        $serviziRicerca->setSigla($_POST[$this->name_form . '_ServiziRicerca_siglaCampoModulo']);
                        $serviziRicerca->setVariabili($arrayVariabili);

                        $ret = $InforClient->ws_serviziRicerca($serviziRicerca);
                        if (!$ret) {
                            if ($InforClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                            } elseif ($InforClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        $risultato = print_r($InforClient->getResult(), true);
                        Out::msgInfo("serviziRicerca Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>', '700', '1500');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
