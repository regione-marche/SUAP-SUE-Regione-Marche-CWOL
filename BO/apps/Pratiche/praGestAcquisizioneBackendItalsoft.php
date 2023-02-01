<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/MIS
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */

include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praWsFrontOffice.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';

//include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAcquisizioneBackendRemoto.class.php';

function praGestAcquisizioneBackendItalsoft() {
    $praGestAcquisizioneBackendItalsoft = new praGestAcquisizioneBackendItalsoft();
    $praGestAcquisizioneBackendItalsoft->parseEvent();
    return;
}

class praGestAcquisizioneBackendItalsoft extends itaModel {

    public $nameForm = 'praGestAcquisizioneBackendItalsoft';
    public $praLib;
    private $codiceParametri = 'BACKENDFASCICOLIITALSOFT';
    private $currentGESNUM;
    private $errCode;
    private $errMessage;

    public function __construct() {
        parent::__construct();
        $this->praLib = new praLib;
        $this->currentGESNUM = App::$utente->getKey($this->nameForm . '_currentGESNUM');
    }

    public function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_currentGESNUM', $this->currentGESNUM);
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currentGESNUM');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function getRemoteConfig($istanza) {
        $arrayConfig = array(
            'URI' => '',
            'WSDL' => '',
            'NAMESPACE' => '',
            'UTENTE' => '',
            'PASSWORD' => '',
            'ENTE' => ''
        );

        $classeParametri = addslashes($istanza);

        $envLib = new envLib();
        $sql = "SELECT * FROM ENV_CONFIG WHERE CLASSE = '{$classeParametri}'";
        $env_config_tab = ItaDB::DBSQLSelect($envLib->getITALWEB_DB(), $sql);
        foreach ($env_config_tab as $env_config_rec) {
            $arrayConfig[$env_config_rec['CHIAVE']] = $env_config_rec['CONFIG'];
        }

        return $arrayConfig;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $envLib = new envLib();
                $istanzeParams = $envLib->getIstanze($this->codiceParametri);
                Out::select($this->nameForm . '_Params', 1, '', 0, '');
                foreach ($istanzeParams as $istanzaParams) {
                    Out::select($this->nameForm . '_Params', 1, $istanzaParams['CLASSE'], 0, $istanzaParams['DESCRIZIONE_ISTANZA']);
                }

                if (count($istanzeParams) === 1) {
                    $configParams = $this->getRemoteConfig($istanzeParams[0]['CLASSE']);
                    Out::valore($this->nameForm . '_Params', $istanzeParams[0]['CLASSE']);
                    Out::valore($this->nameForm . '_WSDL', $configParams['WSDL']);
                }

                Out::hide($this->nameForm . '_boxInfoFascicolo');
                Out::setFocus($this->nameForm, $this->nameForm . '_Pratica');

                $this->buttonBar('Ricerca');
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Params':
                        $configParams = $this->getRemoteConfig($_POST[$_POST['id']]);
                        Out::valore($this->nameForm . '_WSDL', $configParams['WSDL']);
                        Out::setFocus($this->nameForm, $this->nameForm . '_Pratica');
                        break;

                    case $this->nameForm . '_Pratica':
                        $GESNUM = $_POST[$this->nameForm . '_Anno'] . str_pad($_POST[$this->nameForm . '_Pratica'], 6, 0, STR_PAD_LEFT);
                        if ($GESNUM !== $this->currentGESNUM) {
                            Out::hide($this->nameForm . '_boxInfoFascicolo');
                            $this->buttonBar('Ricerca');
                        }
                        break;

                    case $this->nameForm . '_Anno':
                        $GESNUM = $_POST[$this->nameForm . '_Anno'] . str_pad($_POST[$this->nameForm . '_Pratica'], 6, 0, STR_PAD_LEFT);
                        if ($GESNUM !== $this->currentGESNUM) {
                            Out::hide($this->nameForm . '_boxInfoFascicolo');
                            $this->buttonBar('Ricerca');
                        }
                        break;

                    case $this->nameForm . '_ric_siglaserie':
                        if ($_POST[$this->nameForm . '_ric_siglaserie']) {
                            $proLibSerie = new proLibSerie();
                            $AnaserieArc_tab = $proLibSerie->GetSerie($_POST[$this->nameForm . '_ric_siglaserie'], 'sigla', true);
                            if (!$AnaserieArc_tab) {
                                Out::msgStop("Attenzione", "Sigla Inesistente.");
                                Out::valore($this->nameForm . '_ric_codiceserie', '');
                                Out::valore($this->nameForm . '_ric_siglaserie', '');
                                Out::valore($this->nameForm . '_descRicSerie', '');
                                break;
                            }
                            $result = count($AnaserieArc_tab);
                            if ($result > 1) {
                                $proLib = new proLib();
                                $where = "WHERE " . $proLib->getPROTDB()->strUpper('SIGLA') . " = '" . strtoupper($AnaserieArc_tab[0]['SIGLA']) . "'";
                                proRic::proRicSerieArc($this->nameForm, $where);
                                break;
                            }
                            Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_tab[0]['CODICE']);
                            Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_tab[0]['SIGLA']);
                            Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_tab[0]['DESCRIZIONE']);
                            break;
                        }
                        Out::valore($this->nameForm . '_ric_codiceserie', '');
                        Out::valore($this->nameForm . '_ric_siglaserie', '');
                        Out::valore($this->nameForm . '_descRicSerie', '');
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CARICA[EVENTO]':
                        $this->decodeEvento($_POST[$this->nameForm . '_CARICA']['EVENTO'], 'evento');
                        break;

                    case $this->nameForm . '_CARICA[AGGREGATO]':
                        $this->decodeAggregato($_POST[$this->nameForm . '_CARICA']['AGGREGATO'], 'codice');
                        break;

                    case $this->nameForm . '_CARICA[RESPONSABILE]':
                        $this->decodeResponsabile($_POST[$this->nameForm . '_CARICA']['RESPONSABILE'], 'codice');
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Ricerca':
                        Out::hide($this->nameForm . '_boxInfoFascicolo');
                        $this->buttonBar('Ricerca');

                        $istanzaParams = $_POST[$this->nameForm . '_Params'];

                        $arrCampiRicerca = $this->getArrayCampiRicerca();

                        //$xmlFascicolo = $this->ricercaFascicolo($this->getRemoteConfig($istanzaParams), $_POST[$this->nameForm . '_Pratica'], $_POST[$this->nameForm . '_Anno']);
                        $xmlFascicolo = $this->ricercaFascicolo($this->getRemoteConfig($istanzaParams), $arrCampiRicerca);

                        if (!$xmlFascicolo) {
                            Out::msgStop('Errore', $this->errMessage);
                            break;
                        }

                        Out::show($this->nameForm . '_boxInfoFascicolo');
                        $this->buttonBar('Ricerca', 'Carica');

                        $html = $this->generaInfoFascicolo($xmlFascicolo);

                        Out::html($this->nameForm . '_divInfoFascicolo', $html);
                        break;

                    case $this->nameForm . '_Carica':
                        $inputFields = array(
                            array(
                                'label' => array('value' => 'Aggregato', 'style' => 'width: 120px;'),
                                'id' => $this->nameForm . '_CARICA[AGGREGATO]',
                                'name' => $this->nameForm . '_CARICA[AGGREGATO]',
                                'class' => 'ita-edit-lookup ita-edit-onblur',
                                'size' => 8,
                                'br' => false
                            ),
                            array(
                                'id' => $this->nameForm . '_CARICA[AGGREGATO_DES]',
                                'name' => $this->nameForm . '_CARICA[AGGREGATO_DES]',
                                'class' => 'ita-readonly ita-decode',
                                'type' => 'text',
                                'size' => 30
                            ),
                            array(
                                'label' => array('value' => 'Responsabile', 'style' => 'width: 120px;'),
                                'id' => $this->nameForm . '_CARICA[RESPONSABILE]',
                                'name' => $this->nameForm . '_CARICA[RESPONSABILE]',
                                'class' => 'ita-edit-lookup ita-edit-onblur required',
                                'size' => 8,
                                'br' => false
                            ),
                            array(
                                'id' => $this->nameForm . '_CARICA[RESPONSABILE_DES]',
                                'name' => $this->nameForm . '_CARICA[RESPONSABILE_DES]',
                                'class' => 'ita-readonly ita-decode',
                                'type' => 'text',
                                'size' => 30
                            )
                        );

                        Out::msgInput('Carica fascicolo', $inputFields, array(
                            'Conferma' => array(
                                'id' => $this->nameForm . '_confermaCarica',
                                'model' => $this->nameForm,
                                'class' => 'ita-button-validate',
                                'style' => 'float: right;'
                            ),
                            'Annulla' => array(
                                'id' => $this->nameForm . '_annullaCarica',
                                'model' => $this->nameForm
                            )
                                ), $this->nameForm);

                        Out::valore($this->nameForm . '_CARICA[REGISTRAZIONE]', date('Ymd'));
                        break;

                    case $this->nameForm . '_confermaCarica':
                        Out::closeCurrentDialog();

                        $istanzaParams = $_POST[$this->nameForm . '_Params'];
                        $arrCampiRicerca = $this->getArrayCampiRicerca();

                        //$xmlFascicolo = $this->ricercaFascicolo($this->getRemoteConfig($istanzaParams), $_POST[$this->nameForm . '_Pratica'], $_POST[$this->nameForm . '_Anno']);
                        $xmlFascicolo = $this->ricercaFascicolo($this->getRemoteConfig($istanzaParams), $arrCampiRicerca);

                        if (!$xmlFascicolo) {
                            Out::msgStop('Errore', $this->errMessage);
                            break;
                        }

                        $datiCaricamento = $_POST[$this->nameForm . '_CARICA'];
                        if (!$this->caricaFascicolo($xmlFascicolo, $this->getRemoteConfig($istanzaParams), $datiCaricamento)) {
                            if ($this->errCode && $this->errMessage) {
                                Out::msgStop('Errore', $this->errMessage);
                                break;
                            }
                        }
                         $this->returnToParent();
                        break;

                    case $this->nameForm . '_CARICA[AGGREGATO]_butt':
                        praRic::praRicAnaspa($this->nameForm);
                        break;

                    case $this->nameForm . '_CARICA[RESPONSABILE]_butt':
                        praRic::praRicAnanom($this->praLib->getPRAMDB(), $this->nameForm, 'Ricerca responsabile', '', 'Responsabile');
                        break;

                    case $this->nameForm . '_Annulla':
                        Out::closeDialog($this->nameForm);
                        break;

                    case $this->nameForm . '_ric_siglaserie_butt':
                        $fixedSeries = implode(",", $this->fixedSeries);
                        if ($this->fixedSeries)
                            $where = " WHERE CODICE IN ($fixedSeries)";
                        proRic::proRicSerieArc($this->nameForm, $where);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'returnAnaspa':
                $this->decodeAggregato($_POST['retKey'], 'rowid');
                break;

            case 'returnUnires':
                $this->decodeResponsabile($_POST['retKey'], $_POST['retid']);
                break;

            case 'returnSerieArc':
                $proLibSerie = new proLibSerie();
                $AnaserieArc_rec = $proLibSerie->GetSerie($_POST['retKey'], 'rowid');
                if ($AnaserieArc_rec) {
                    Out::valore($this->nameForm . '_ric_codiceserie', $AnaserieArc_rec['CODICE']);
                    Out::valore($this->nameForm . '_ric_siglaserie', $AnaserieArc_rec['SIGLA']);
                    Out::valore($this->nameForm . '_descRicSerie', $AnaserieArc_rec['DESCRIZIONE']);
                }
                break;
        }
    }

    private function decodeAggregato($codice, $tipo) {
        $anaspa_rec = $this->praLib->GetAnaspa($codice, $tipo);
        Out::valore($this->nameForm . '_CARICA[AGGREGATO]', $anaspa_rec['SPACOD']);
        Out::valore($this->nameForm . '_CARICA[AGGREGATO_DES]', $anaspa_rec['SPADES']);
    }

    private function decodeResponsabile($codice, $tipo) {
        $codice = str_pad($codice, 6, '0', STR_PAD_LEFT);

        $ananom_rec = $this->praLib->GetAnanom($codice, $tipo);
        Out::valore($this->nameForm . '_CARICA[RESPONSABILE]', $ananom_rec['NOMRES']);
        Out::valore($this->nameForm . '_CARICA[RESPONSABILE_DES]', $ananom_rec['NOMCOG'] . ' ' . $ananom_rec['NOMNOM']);
    }

    public function buttonBar() {
        Out::hide($this->nameForm . '_Ricerca');
        Out::hide($this->nameForm . '_Carica');

        if (func_num_args()) {
            foreach (func_get_args() as $button) {
                Out::show($this->nameForm . "_$button");
            }
        }
    }

    public function caricaGriglia($id, $opts, $page = null, $rows = null, $sidx = null, $sord = null) {
        TableView::clearGrid($id);

        $gridObj = new TableView($id, $opts);

        $gridObj->setPageNum($page ? : $_POST['page'] ? : $_POST[$id]['gridParam']['page'] ? : 1);
        $gridObj->setPageRows($rows ? : $_POST['rows'] ? : $_POST[$id]['gridParam']['rowNum'] ? : 9999999);
        $gridObj->setSortIndex($sidx ? : $_POST['sidx'] ? : '');
        $gridObj->setSortOrder($sord ? : $_POST['sord'] ? : '');

        $elaboraRecords = 'elaboraRecords' . ucfirst(substr($id, strlen($this->nameForm) + 1));
        if (method_exists($this, $elaboraRecords)) {
            return $gridObj->getDataPageFromArray('json', $this->$elaboraRecords($gridObj->getDataArray()));
        }

        return $gridObj->getDataPage('json');
    }

    private function getValue($node, $key, $type = '') {
        $value = $node->getElementsByTagName($key)->item(0)->nodeValue;
        switch ($type) {
            default:
                return $value;

            case 'date':
                return substr($value, 6, 2) . '/' . substr($value, 4, 2) . '/' . substr($value, 0, 4);

            case 'progressivo':
                return (int) substr($value, 4) . '/' . substr($value, 0, 4);
        }
    }

    private function getRecords($node_tab) {
        $table = array();

        if (!$node_tab) {
            return $table;
        }

        foreach ($node_tab->getElementsByTagName('RECORD') as $node_rec) {
            $record = array();

            foreach ($node_rec->childNodes as $node_col) {
                if ($node_col->nodeType === XML_ELEMENT_NODE) {
                    $record[$node_col->nodeName] = $node_col->nodeValue;
                }
            }

            $table[] = $record;
        }

        return $table;
    }

    //public function ricercaFascicolo($config, $numero, $anno) {
    public function ricercaFascicolo($config, $arrCampiRicerca) {
        $praWsFrontOffice = new praWsFrontOffice;

        $praWsFrontOffice->setWebservices_uri($config['URI']);
        $praWsFrontOffice->setWebservices_wsdl($config['WSDL']);
        $praWsFrontOffice->setNamespace($config['NAMESPACE']);
        $praWsFrontOffice->setTimeout(1200);

        /*
         * Prendo il Token
         */
        $contextToken = $this->ricercaFascicoloWsCall($praWsFrontOffice, 'ws_GetItaEngineContextToken', array(
            'userName' => $config['UTENTE'],
            'userPassword' => $config['PASSWORD'],
            'domainCode' => $config['ENTE']
        ));

        if (!$contextToken) {
            return false;
        }

        /*
         * Ricerco la pratica
         */
        $resultRicercaPratica = $this->ricercaFascicoloWsCall($praWsFrontOffice, 'ws_RicercaPratiche', array(
            'itaEngineContextToken' => $contextToken,
            'domainCode' => $config['ENTE'],
            'filtri' => $arrCampiRicerca,
        ));
        if (!$resultRicercaPratica) {
            if (!$this->errCode && !$this->errMessage) {
                $this->errCode = -1;
                $this->errMessage = "Fascicolo non trovato.";
            }

            return false;
        }
        $xmlRicerca = base64_decode($resultRicercaPratica);
        if (!$xmlRicerca) {
            $this->errCode = -1;
            $this->errMessage = "Errore in decodifica risposta 'ws_RicercaPratiche'";
            return false;
        }

        include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString(utf8_encode($xmlRicerca));
        if (!$retXml) {
            $this->errCode = -1;
            $this->errMessage = "File XML Ricerca Fascicoli: Impossibile leggere il testo nell'xml";
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->errCode = -1;
            $this->errMessage = "Lettura XML Ricerca Fascicoli: Impossibile estrarre i dati";
            return false;
        }

        /*
         * Controllo numero pratiche trovate
         */
        if (count($arrayXml['RECORD']) > 1) {
            $this->errCode = -1;
            $this->errMessage = "E' stata trovata più di una pratica. Ripetere la ricerca";
            return false;
        }

        /*
         * Leggo i dati della pratica
         */
        $numero = substr($arrayXml['RECORD'][0]['GESNUM'][0]['@textNode'], 4);
        $anno = substr($arrayXml['RECORD'][0]['GESNUM'][0]['@textNode'], 0, 4);
        $resultGetPratica = $this->ricercaFascicoloWsCall($praWsFrontOffice, 'ws_getPraticaDati', array(
            'itaEngineContextToken' => $contextToken,
            'domainCode' => $config['ENTE'],
            'numeroPratica' => $numero,
            'annoPratica' => $anno
        ));

        if (!$resultGetPratica) {
            if (!$this->errCode && !$this->errMessage) {
                $this->errCode = -1;
                $this->errMessage = "Fascicolo '$numero/$anno' non trovato.";
            }

            return false;
        }

        $xmlFascicolo = base64_decode($resultGetPratica);

        if (!$xmlFascicolo) {
            $this->errCode = -1;
            $this->errMessage = "Errore in decodifica risposta 'ws_getPraticaDati'";
            return false;
        }

        $this->ricercaFascicoloWsCall($praWsFrontOffice, 'ws_DestroyItaEngineContextToken', array(
            'token' => $contextToken,
            'domainCode' => $config['ENTE'])
        );

        return $xmlFascicolo;
    }

    private function ricercaFascicoloWsCall($wsclient, $method, $params) {
        $wsresult = $wsclient->{$method}($params);

        if (!$wsresult) {
            if ($wsclient->getFault()) {
                $this->errCode = -1;
                $this->errMessage = "Fault chiamata '$method': " . $wsclient->getFault();
            } elseif ($wsclient->getError()) {
                $this->errCode = -1;
                $this->errMessage = "Errore chiamata '$method': " . $wsclient->getError();
            }

            return false;
        }

        return $wsclient->getResult();
    }

    public function generaInfoFascicolo($xmlFascicolo) {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlFascicolo);

        $proges_xml_rec = $domDocument->getElementsByTagName('PROGES')->item(0);
        $anades_xml_tab = $domDocument->getElementsByTagName('ANADES')->item(0);
        $pasdoc_xml_tab = $domDocument->getElementsByTagName('PASDOC')->item(0);

        $span = 'style="display: inline-block; width: 120px; font-weight: bold; margin-right: 5px;"';

        $html = '';
        $html .= "<span $span>Procedimento</span>{$this->getValue($proges_xml_rec, 'GESPRO')}<br>";
        $html .= "<span $span></span>{$this->getValue($proges_xml_rec, 'PRADES')}<br>";
        $html .= "<span $span>Data ricezione</span>{$this->getValue($proges_xml_rec, 'GESDRI', 'date')} {$this->getValue($proges_xml_rec, 'GESORA')}<br>";
        $html .= "<span $span>Data acquisizione</span>{$this->getValue($proges_xml_rec, 'GESDRE', 'date')}<br>";
        $html .= "<span $span>Evento</span>{$this->getValue($proges_xml_rec, 'GESEVE')}<br>";
        $html .= "<span $span>Sportello</span>{$this->getValue($proges_xml_rec, 'GESTSP')}<br>";
        $html .= "<span $span>Aggregato</span>{$this->getValue($proges_xml_rec, 'GESSPA')}<br>";
        $numProt = substr($this->getValue($proges_xml_rec, 'GESNPR'), 4);
        $annoProt = substr($this->getValue($proges_xml_rec, 'GESNPR'), 0, 4);
        $html .= "<span $span>Protocollo N.</span>$numProt<span><b> del </b></span>$annoProt<br>";

        $anades_tab = $this->getRecords($anades_xml_tab);
        foreach ($anades_tab as $k => $anades_rec) {
            $anades_tab[$k]['DESRUO'] = praRuolo::getSystemSubjectRoleFields($anades_rec['DESRUO']);
        }

        $this->caricaGriglia($this->nameForm . '_gridSoggetti', array(
            'arrayTable' => $anades_tab,
            'rowIndex' => 'idx'
        ));

        $this->caricaGriglia($this->nameForm . '_gridAllegati', array(
            'arrayTable' => $this->getRecords($pasdoc_xml_tab),
            'rowIndex' => 'idx'
        ));

        $this->currentGESNUM = $this->getValue($proges_xml_rec, 'GESNUM');

        return $html;
    }

    public function caricaFascicolo($xmlFascicolo, $remoteConfig, $datiCaricamento) {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlFascicolo);

        $iteevt_rec = false;
        $anades_esibente = false;
        $proges_xml_rec = $domDocument->getElementsByTagName('PROGES')->item(0);
        $anades_xml_tab = $domDocument->getElementsByTagName('ANADES')->item(0);

        $iteevt_tab = $this->praLib->GetIteevt($this->getValue($proges_xml_rec, 'GESPRO'), 'codice', true);
        foreach ($iteevt_tab as $iteevt_tmp_rec) {
            if ($iteevt_tmp_rec['IEVCOD'] == $this->getValue($proges_xml_rec, 'GESEVE')) {
                $iteevt_rec = $iteevt_tmp_rec;
                break;
            }
        }

        if (!$iteevt_rec) {
            $this->errCode = -1;
            $this->errMessage = "Evento '{$this->getValue($proges_xml_rec, 'GESEVE')}' non trovato.";
            return false;
        }

        $anades_tab = $this->getRecords($anades_xml_tab);
        foreach ($anades_tab as $anades_rec) {
            if ($anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode('ESIBENTE')) {
                $anades_esibente = $anades_rec;
                unset($anades_esibente['ROWID']);
                break;
            }
        }

        if (!$anades_esibente) {
            $this->errCode = -1;
            $this->errMessage = "Soggetto 'ESIBENTE' non trovato.";
            return false;
        }

        $_POST = array();
        $_POST['carica'] = true;
        $_POST['datiMail'] = array(
            'PROGES' => array(
                'GESPRO' => $this->getValue($proges_xml_rec, 'GESPRO'),
                'GESRES' => $datiCaricamento['RESPONSABILE'],
                'GESDRE' => date('Ymd')
            ),
            'ITEEVT' => array('ROWID' => $iteevt_rec['ROWID']), // DA FORM
            'ANADES' => $anades_esibente,
            'SportelloAggregato' => $datiCaricamento['AGGREGATO'],
            'EscludiPassiFO' => true,
            'provenienza' => 'daAnagrafica',
            'CALLBACK' => array(
                'REQUIRE' => ITA_BASE_PATH . '/apps/Pratiche/praGestAcquisizioneBackendItalsoft.php',
                'CLASS' => 'praGestAcquisizioneBackendItalsoft',
                'METHOD' => 'acquisisciDatiFascicolo'
            ),
            'CALLBACK_PARAMS' => array(
                'XML_FASCICOLO' => $xmlFascicolo,
                'REMOTE_CONFIG' => $remoteConfig
            )
        );

        /* @var $praGest praGest */
        $praGest = itaModel::getInstance('praGestElenco');
        $result = $praGest->CaricaDaPec();

        if ($result === false) {
            return false;
        }
    }

    public function acquisisciDatiFascicolo($gesnum, $params) {
        $xmlFascicolo = $params['XML_FASCICOLO'];
        $remoteConfig = $params['REMOTE_CONFIG'];

        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlFascicolo);

        $anades_xml_tab = $domDocument->getElementsByTagName('ANADES')->item(0);
        $propas_xml_tab = $domDocument->getElementsByTagName('PROPAS')->item(0);
        $prodag_xml_tab = $domDocument->getElementsByTagName('PRODAG')->item(0);
        $pasdoc_xml_tab = $domDocument->getElementsByTagName('PASDOC')->item(0);
        $praimm_xml_tab = $domDocument->getElementsByTagName('PRAIMM')->item(0);
        $pramitdest_xml_tab = $domDocument->getElementsByTagName('PRAMITDEST')->item(0);
        $pracom_xml_tab = $domDocument->getElementsByTagName('PRACOM')->item(0);

        $mappaturaChiavi = array();

        /*
         * Caricamento ANADES
         */

        $anades_tab = $this->getRecords($anades_xml_tab);
        foreach ($anades_tab as $anades_rec) {
            if ($anades_rec['DESRUO'] == praRuolo::getSystemSubjectCode('ESIBENTE')) {
                continue;
            }

            unset($anades_rec['ROWID']);
            $anades_rec['DESNUM'] = $gesnum;

            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'ANADES', 'ROWID', $anades_rec);
            } catch (Exception $e) {
                Out::msgStop('Errore', $e->getMessage());
                return false;
            }
        }

        /*
         * Caricamento PROPAS
         */

        $propas_tab = $this->getRecords($propas_xml_tab);
        foreach ($propas_tab as $propas_rec) {
            $mappaturaChiavi[$propas_rec['PRONUM']] = $gesnum;
            $mappaturaChiavi[$propas_rec['PROPAK']] = $this->praLib->PropakGenerator($gesnum);

            unset($propas_rec['ROWID']);
            $propas_rec['PRONUM'] = $gesnum;
            $propas_rec['PROPAK'] = $mappaturaChiavi[$propas_rec['PROPAK']];

            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PROPAS', 'ROWID', $propas_rec);
            } catch (Exception $e) {
                Out::msgStop('Errore', $e->getMessage());
                return false;
            }
        }

        /*
         * Caricamento PRODAG
         */
        $prodag_tab = $this->getRecords($prodag_xml_tab);
        foreach ($prodag_tab as $prodag_rec) {
            unset($prodag_rec['ROWID']);
            $prodag_rec['DAGNUM'] = $gesnum;
            $prodag_rec['DAGPAK'] = $mappaturaChiavi[$prodag_rec['DAGPAK']];
            $prodag_rec['DAGSET'] = $mappaturaChiavi[$prodag_rec['DAGPAK']] . substr($prodag_rec['DAGSET'], -3);

            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRODAG', 'ROWID', $prodag_rec);
            } catch (Exception $e) {
                Out::msgStop('Errore', $e->getMessage());
                return false;
            }
        }

        /*
         * Caricamento PRAIMM
         */
        $praimm_tab = $this->getRecords($praimm_xml_tab);
        foreach ($praimm_tab as $praimm_rec) {
            unset($praimm_rec['ROWID']);
            $praimm_rec['PRONUM'] = $gesnum;
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAIMM', 'ROWID', $praimm_rec);
            } catch (Exception $e) {
                Out::msgStop('Errore', $e->getMessage());
                return false;
            }
        }

        /*
         * Caricamento PRACOM
         */
        $pracom_tab = $this->getRecords($pracom_xml_tab);
        foreach ($pracom_tab as $pracom_rec) {
            $oldRowid = $pracom_rec['ROWID'];
            unset($pracom_rec['ROWID']);
            $pracom_rec['COMNUM'] = $gesnum;
            $pracom_rec['COMPAK'] = $mappaturaChiavi[$pracom_rec['COMPAK']];
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRACOM', 'ROWID', $pracom_rec);
            } catch (Exception $e) {
                Out::msgStop('Errore', $e->getMessage());
                return false;
            }
            $newPracomRowid = $this->praLib->getPRAMDB()->getLastId();
            $mappaturaChiavi[$oldRowid] = $newPracomRowid;
        }

        /*
         * Caricamento PRAMITDEST
         */
        $pramitdest_tab = $this->getRecords($pramitdest_xml_tab);
        foreach ($pramitdest_tab as $pramitdest_rec) {
            unset($pramitdest_rec['ROWID']);
            $pramitdest_rec['KEYPASSO'] = $mappaturaChiavi[$pramitdest_rec['KEYPASSO']];
            $pramitdest_rec['ROWIDPRACOM'] = $mappaturaChiavi[$oldRowid];
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAMITDEST', 'ROWID', $pramitdest_rec);
            } catch (Exception $e) {
                Out::msgStop('Errore', $e->getMessage());
                return false;
            }
        }



        $praWsFrontOffice = new praWsFrontOffice;

        $praWsFrontOffice->setWebservices_uri($remoteConfig['URI']);
        $praWsFrontOffice->setWebservices_wsdl($remoteConfig['WSDL']);
        $praWsFrontOffice->setNamespace($remoteConfig['NAMESPACE']);
        $praWsFrontOffice->setTimeout(1200);

        $contextToken = $this->ricercaFascicoloWsCall($praWsFrontOffice, 'ws_GetItaEngineContextToken', array(
            'userName' => $remoteConfig['UTENTE'],
            'userPassword' => $remoteConfig['PASSWORD'],
            'domainCode' => $remoteConfig['ENTE']
        ));

        if (!$contextToken) {
            Out::msgStop('Errore', $this->errMessage);
            return false;
        }

        /*
         * Rilego PROGES_REC
         */
        $proges_rec = $this->praLib->getProges($gesnum);

        /*
         * Caricamento PASDOC
         */
        $pasdoc_tab = $this->getRecords($pasdoc_xml_tab);
        foreach ($pasdoc_tab as $pasdoc_rec) {
            $streamAllegato = $this->getAllegatoFascicolo($praWsFrontOffice, $contextToken, $remoteConfig['ENTE'], $pasdoc_rec['ROWID']);
            if (!$streamAllegato) {
                Out::msgStop('Errore', $this->errMessage);
                return false;
            }

            unset($pasdoc_rec['ROWID']);
            $pasdoc_rec['PASKEY'] = $mappaturaChiavi[$pasdoc_rec['PASKEY']];
            if ($pasdoc_rec['PASPRTCLASS'] == "PROGES") {
                $pasdoc_rec['PASPRTROWID'] = $proges_rec['ROWID'];
            } elseif ($pasdoc_rec['PASPRTCLASS'] == "PRACOM") {
                $pasdoc_rec['PASPRTROWID'] = $mappaturaChiavi[$oldRowid];
            }
            $destinazioneAllegati = $this->praLib->SetDirectoryPratiche(substr($pasdoc_rec['PASKEY'], 0, 4), $pasdoc_rec['PASKEY']);
            file_put_contents($destinazioneAllegati . '/' . $pasdoc_rec['PASFIL'], $streamAllegato);
            try {
                ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PASDOC', 'ROWID', $pasdoc_rec);
            } catch (Exception $e) {
                Out::msgStop('Errore', $e->getMessage());
                return false;
            }
        }

        $this->ricercaFascicoloWsCall($praWsFrontOffice, 'ws_DestroyItaEngineContextToken', array(
            'token' => $contextToken,
            'domainCode' => $remoteConfig['ENTE']
        ));

        Out::closeDialog($this->nameForm);

        return true;
    }

    public function getAllegatoFascicolo($wsclient, $contextToken, $domainCode, $rowid) {
        $resultGetAllegato = $this->ricercaFascicoloWsCall($wsclient, 'ws_getPraticaAllegatoForRowid', array(
            'itaEngineContextToken' => $contextToken,
            'domainCode' => $domainCode,
            'rowid' => $rowid
        ));

        if (!$resultGetAllegato) {
            return false;
        }

        $streamAllegato = base64_decode($resultGetAllegato);

        if (!$streamAllegato) {
            $this->errCode = -1;
            $this->errMessage = "Errore in decodifica risposta 'ws_getPraticaAllegatoForRowid'";
            return false;
        }

        return $streamAllegato;
    }

    private function getArrayCampiRicerca() {
        return array("filtro" => array(
                array("chiave" => "dal_num", "valore" => $_POST[$this->nameForm . '_Pratica']),
                array("chiave" => "al_num", "valore" => $_POST[$this->nameForm . '_Pratica']),
                array("chiave" => "anno", "valore" => $_POST[$this->nameForm . '_Anno']),
                array("chiave" => "ric_siglaserie", "valore" => $_POST[$this->nameForm . '_ric_siglaserie']),
                array("chiave" => "dal_numserie", "valore" => $_POST[$this->nameForm . '_Numero']),
                array("chiave" => "al_numserie", "valore" => $_POST[$this->nameForm . '_Numero']),
                array("chiave" => "annoserie", "valore" => $_POST[$this->nameForm . '_Annoserie']),
                array("chiave" => "nprot", "valore" => $_POST[$this->nameForm . '_NProt']),
                array("chiave" => "annoprot", "valore" => $_POST[$this->nameForm . '_AnnoProt']),
            )
        );
    }

}
