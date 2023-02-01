<?php

/**
 *
 * TEST PALESO WS-CLIENT
 *
 * PHP Version 5
 *
 * @category
 * @package    Pratiche
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2019 Italsoft snc
 * @license
 * @version    02.10.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaHelper.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function praModuloPATest() {
    $praModuloPATest = new praModuloPATest();
    $praModuloPATest->parseEvent();
    return;
}

class praModuloPATest extends itaModel {

    public $name_form = "praModuloPATest";

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                itaLib::openForm($this->name_form, "", true, "desktopBody");
                Out::show($this->name_form);
                //inizializzo i valori di configurazione della chiamata
                $this->inizializzaValori();

                Out::setFocus('', $this->name_form . "_CONFIG[wsEndpoint]");
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->name_form . '_callGetItaEngineContextToken':
                        $UserName = $this->formData[$this->name_form . '_CONFIG']['wsUser'];
                        $Password = $this->formData[$this->name_form . '_CONFIG']['wsPassword'];
                        $DomainCode = $this->formData[$this->name_form . '_CONFIG']['wsDomain'];
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsLogin'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'GetItaEngineContextToken');
                        cwbPagoPaHelper::setTimeout(20);
                        cwbPagoPaHelper::setDebug_level(9);
                        $token = cwbPagoPaHelper::GetItaEngineContextToken($UserName, $Password, $DomainCode);
                        Out::msgInfo("GetItaEngineContextToken Result", $token);
                        Out::valore($this->name_form . '_TOKEN', $token);
                        break;
                    case $this->name_form . '_callDestroyItaEngineContextToken':
                        $token = $this->formData[$this->name_form . '_TOKEN'];
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsLogin'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'DestroyItaEngineContextToken');
                        cwbPagoPaHelper::setTimeout(20);
                        cwbPagoPaHelper::setDebug_level(9);
                        $result = cwbPagoPaHelper::DestroyItaEngineContextToken($token);
                        Out::msgInfo("DestroyItaEngineContextToken Result", $result);
                        if ($result == '"Success"') {
                            Out::valore($this->name_form . '_TOKEN', '');
                        }
                        break;

                    case $this->name_form . '_callPubblicaPosizione':
                        $token = $this->formData[$this->name_form . '_TOKEN'];
                        if (!$token) {
                            Out::msgStop("Attenzione", "Prima di fare la chiamate effettuare il login");
                            break;
                        }
                        //preparo array di pubblicazione posizione
                        $XML = '<PENDENZA>';
                        $XML .= '<CODTIPSCAD>' . $this->formData[$this->name_form . '_CODTIPSCAD'] . '</CODTIPSCAD>';
                        $XML .= '<SUBTIPSCAD>' . $this->formData[$this->name_form . '_SUBTIPSCAD'] . '</SUBTIPSCAD>';
                        $XML .= '<PROGCITYSC>' . $this->formData[$this->name_form . '_PROGCITYSC'] . '</PROGCITYSC>';
                        $XML .= '<PROGCITYSCA>' . $this->formData[$this->name_form . '_PROGCITYSCA'] . '</PROGCITYSCA>';
                        $XML .= '<DESCRPEND>' . $this->formData[$this->name_form . '_DESCRPEND'] . '</DESCRPEND>';
                        $XML .= '<MODPROVEN>' . $this->formData[$this->name_form . '_MODPROVEN'] . '</MODPROVEN>';
                        $XML .= '<ANNORIF>' . $this->formData[$this->name_form . '_ANNORIF'] . '</ANNORIF>';
                        $XML .= '<NUMDOC>' . $this->formData[$this->name_form . '_NUMDOC'] . '</NUMDOC>';
                        $XML .= '<DATASCADE>' . $this->formData[$this->name_form . '_DATASCADE'] . '</DATASCADE>';
                        $XML .= '<IMPDAPAGTO>' . $this->formData[$this->name_form . '_IMPDAPAGTO'] . '</IMPDAPAGTO>';
                        $XML .= '<FLAG_PUBBL>' . $this->formData[$this->name_form . '_FLAG_PUBBL'] . '</FLAG_PUBBL>';
//                        $XML .= '<ANNOEMI></ANNOEMI>';
//                        $XML .= '<NUMEMI></NUMEMI>';
//                        $XML .= '<IDBOL_SERE></IDBOL_SERE>';
                        $XML .= '<SOGGETTO>';
                        $XML .= '<PROGSOGG>' . $this->formData[$this->name_form . '_PROGSOGG'] . '</PROGSOGG>';
                        $XML .= '<CODFISCALE>' . $this->formData[$this->name_form . '_CODFISCALE'] . '</CODFISCALE>';
                        $XML .= '<PARTIVA>' . $this->formData[$this->name_form . '_PARTIVA'] . '</PARTIVA>';
                        $XML .= '<NOME>' . $this->formData[$this->name_form . '_NOME'] . '</NOME>';
                        $XML .= '<COGNOME>' . $this->formData[$this->name_form . '_COGNOME'] . '</COGNOME>';
                        $XML .= '<DATANASC>' . $this->formData[$this->name_form . '_DATANASC'] . '</DATANASC>';
                        $XML .= '<LUOGONASC>' . $this->formData[$this->name_form . '_LUOGONASC'] . '</LUOGONASC>';
                        $XML .= '<COMUNERESID>' . $this->formData[$this->name_form . '_COMUNERESID'] . '</COMUNERESID>';
                        $XML .= '<PROVINCIARESID>' . $this->formData[$this->name_form . '_PROVINCIARESID'] . '</PROVINCIARESID>';
                        $XML .= '<INDIRIZZORESID>' . $this->formData[$this->name_form . '_INDIRIZZORESID'] . '</INDIRIZZORESID>';
                        $XML .= '<CAPRESID>' . $this->formData[$this->name_form . '_CAP'] . '</CAPRESID>';
                        $XML .= '<PEC>' . $this->formData[$this->name_form . '_PEC'] . '</PEC>';
                        $XML .= '</SOGGETTO>';
                        if ($this->formData[$this->name_form . '_R1_NUMRATA'] != '') {
                            $XML .= '<RATE>';
                            $XML .= '<ROW0>';
                            $XML .= '<NUMRATA>' . $this->formData[$this->name_form . '_R1_NUMRATA'] . '</NUMRATA>';
                            $XML .= '<IMPDAPAGTO>' . $this->formData[$this->name_form . '_R1_IMPDAPAGTO'] . '</IMPDAPAGTO>';
                            $XML .= '<DESCRPEND>' . $this->formData[$this->name_form . '_R1_DESCRPEND'] . '</DESCRPEND>';
                            $XML .= '<DATASCADE>' . $this->formData[$this->name_form . '_R1_DATASCADE'] . '</DATASCADE>';
                            $XML .= '</ROW0>';
                            $XML .= '<ROW1>';
                            $XML .= '<NUMRATA>' . $this->formData[$this->name_form . '_R2_NUMRATA'] . '</NUMRATA>';
                            $XML .= '<IMPDAPAGTO>' . $this->formData[$this->name_form . '_R2_IMPDAPAGTO'] . '</IMPDAPAGTO>';
                            $XML .= '<DESCRPEND>' . $this->formData[$this->name_form . '_R2_DESCRPEND'] . '</DESCRPEND>';
                            $XML .= '<DATASCADE>' . $this->formData[$this->name_form . '_R2_DATASCADE'] . '</DATASCADE>';
                            $XML .= '</ROW1>';
                            $XML .= '</RATE>';
                        }
                        $XML .= '<INFOAGGIUNTIVE>';
                        $XML .= '<TARGAVEICOLO>' . $this->formData[$this->name_form . '_TARGAVEICOLO'] . '</TARGAVEICOLO>';
                        $XML .= '<DATAVERBALE>' . $this->formData[$this->name_form . '_DATAVERBALE'] . '</DATAVERBALE>';
                        $XML .= '<IDENTIFICATIVOVERBALE>' . $this->formData[$this->name_form . '_IDENTIFICATIVOVERBALE'] . '</IDENTIFICATIVOVERBALE>';
                        $XML .= '<TIPOUFFICIO>' . $this->formData[$this->name_form . '_TIPOUFFICIO'] . '</TIPOUFFICIO>';
                        $XML .= '<CODICEUFFICIO>' . $this->formData[$this->name_form . '_CODICEUFFICIO'] . '</CODICEUFFICIO>';
                        $XML .= '<TIPOLOGIASERVIZIO>' . $this->formData[$this->name_form . '_TIPOLOGIASERVIZIO'] . '</TIPOLOGIASERVIZIO>';
                        $XML .= '</INFOAGGIUNTIVE>';
                        $XML .= '</PENDENZA>';

                        file_put_contents("C:/tmp/pendenza.xml", $XML);

                        $parametri = array();
                        $parametri['CodTipScad'] = $this->formData[$this->name_form . '_CODTIPSCAD'];
                        $parametri['SubTipScad'] = $this->formData[$this->name_form . '_SUBTIPSCAD'];
                        $parametri['Pendenza'] = $XML;


                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsEndpoint'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'pubblicaPosizione');
                        cwbPagoPaHelper::setTimeout(20);
                        cwbPagoPaHelper::setDebug_level(9);
                        $risultato = cwbPagoPaHelper::pubblicaPosizione($token, 'XML', $parametri);

                        $xmlObj = new itaXML;
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($risultato);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        file_put_contents("C:/tmp/arr_ret.txt", print_r($arr, true));
                        if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                            Out::msgStop("Errore", $arr['MESSAGE'][0]['@textNode']);
                            break;
                        }
                        $b64_ret = $arr['MESSAGE'][0]['@textNode'];
                        $xml = base64_decode($b64_ret);
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($xml);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        Out::msgInfo("arr ret", print_r($arr, true));
                        $IUV = $arr['ROW0'][0]['IUV'][0]['@textNode'];
                        Out::valore($this->name_form . '_IUV', $IUV);
                        for ($i = 1; $i < 100; $i++) {
                            $tag = 'ROW' . $i;
                            $R_IUV = $arr[$tag][0]['IUV'][0]['@textNode'];
                            Out::valore($this->name_form . '_R' . $i . '_IUV', $R_IUV);
                        }
                        file_put_contents("C:/tmp/arr_ret_posizione.txt", print_r($arr, true));
                        Out::msgInfo("pubblicaPosizione Result", $arr['MESSAGE'][0]['@textNode']);

                        break;

                    case $this->name_form . '_callEseguiPagamento':
                        $parametri = array(
                            'CodiceIdentificativo' => $this->formData[$this->name_form . '_IUV'], //considero solo IUV rata unica per test
                            'urlReturn' => 'test' //test da BO, non torna di fatto su url per redirect
                        );
                        file_put_contents("C:/tmp/parametri_esegui", print_r($parametri, true));
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsEndpoint'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'eseguiPagamento');
                        $risultato = cwbPagoPaHelper::eseguiPagamento($this->formData[$this->name_form . '_TOKEN'], 'IUV', $parametri);

                        $xmlObj = new itaXML;
                        if (!$xmlObj) {
                            print_r('NO XML OBJECT');
                            break;
                        }
                        $retXml = $xmlObj->setXmlFromString($risultato);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                            print_r($arr['MESSAGE'][0]['@textNode']);
                            $this->html->appendHtml($html);
                            break;
                        }
                        file_put_contents("C:/tmp/arr_url_ret.txt", print_r($arr, true));
                        $url_ret = $arr['MESSAGE'][0]['@textNode'];
                        $UrlMPAYCart = 'http://payertest.regione.marche.it/mpay/cart/extCart.do';
                        file_put_contents("/tmp/redirect_effettiva.txt", $url_ret);
                        header('Location: ' . $url_ret);
                        break;

                    case $this->name_form . '_callGeneraBollettino':
                        $parametri = array(
                            'CodiceIdentificativo' => $this->formData[$this->name_form . '_IUV']
                        );
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsEndpoint'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'generaBollettino');
                        $risultato = cwbPagoPaHelper::generaBollettino($this->formData[$this->name_form . '_TOKEN'], 'IUV', $parametri);
                        file_put_contents("C:/tmp/risultato_generaBollettino", print_r($risultato, true));
                        $xmlObj = new itaXML;
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($risultato);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        file_put_contents("C:/tmp/arr_ret.txt", print_r($arr, true));
                        if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                            Out::msgStop("Errore", $arr['MESSAGE'][0]['@textNode']);
                            break;
                        }
                        $b64_ret = $arr['MESSAGE'][0]['@textNode'];
                        $file_dest = "C:/tmp/bollettinoPA.pdf";
                        file_put_contents("C:/tmp/bollettinoB64.txt", $b64_ret);
                        file_put_contents("C:/tmp/bollettinoPA.pdf", base64_decode($b64_ret));
                        Out::msgInfo("generaBollettino Result", '<pre style="font-size:1.5em">' . $b64_ret . '</pre>');
                        Out::openDocument(utiDownload::getUrl(pathinfo($file_dest, PATHINFO_BASENAME), $file_dest));
                        break;

                    case $this->name_form . '_callRicercaPosizioneIUV':
                        $token = $this->formData[$this->name_form . '_TOKEN'];
                        if (!$token) {
                            Out::msgStop("Attenzione", "Prima di fare la chiamate effettuare il login");
                            break;
                        }
                        $parametri = array(
                            'CodiceIdentificativo' => $this->formData[$this->name_form . '_IUV'] //considero solo IUV rata unica per test
                        );
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsEndpoint'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'ricercaPosizioneIUV');
                        cwbPagoPaHelper::setTimeout(20);
                        cwbPagoPaHelper::setDebug_level(9);
                        $risultato = cwbPagoPaHelper::ricercaPosizioneIUV($token, $parametri);
                        file_put_contents("C:/tmp/ricercaPosizoneIUV.txt", $risultato);
                        $xmlObj = new itaXML;
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($risultato);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        file_put_contents("C:/tmp/arr_ret_ricerca.txt", print_r($arr, true));
                        if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                            Out::msgStop("Errore", $arr['MESSAGE'][0]['@textNode']);
                            break;
                        }
                        $b64_ret = $arr['MESSAGE'][0]['@textNode'];
                        $xml = base64_decode($b64_ret);
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($xml);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        file_put_contents("C:/tmp/ricercaPagata", print_r($arr, true));
                        Out::msgInfo("arr ret ricerca", print_r($arr, true));
                        Out::msgInfo("RicercaPosizioneIUV Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;
                    case $this->name_form . '_callRicercaPosizioneChiaveEsterna':
                        $token = $this->formData[$this->name_form . '_TOKEN'];
                        if (!$token) {
                            Out::msgStop("Attenzione", "Prima di fare la chiamate effettuare il login");
                            break;
                        }
                        //cerco solo la rata unica
                        $parametri = array(
                            'CodTipScad' => $this->formData[$this->name_form . '_CODTIPSCAD'],
                            'SubTipScad' => $this->formData[$this->name_form . '_SUBTIPSCAD'],
                            'ProgCitySca' => $this->formData[$this->name_form . '_PROGCITYSCA'],
                            'AnnoRif' => $this->formData[$this->name_form . '_ANNORIF'],
                            'NumRata' => 1
                        );
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsEndpoint'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'ricercaPosizioneChiaveEsterna');
                        cwbPagoPaHelper::setTimeout(20);
                        cwbPagoPaHelper::setDebug_level(9);
//                        $risultato = cwbPagoPaHelper::ricercaPosizioneIUV($token, 'IUV', $parametri);
                        $risultato = cwbPagoPaHelper::ricercaPosizioneChiaveEsterna($token, $parametri);
                        file_put_contents("C:/tmp/ricercaPosizoneChiaveEsterna.txt", $risultato);
                        if (!$risultato) {
                            Out::msgStop("Attenzione", "Nessun risultato");
                            break;
                        }
                        $xmlObj = new itaXML;
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($risultato);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        file_put_contents("C:/tmp/arr_ret_ricerca.txt", print_r($arr, true));
                        if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                            Out::msgStop("Errore", $arr['MESSAGE'][0]['@textNode']);
                            break;
                        }
                        $b64_ret = $arr['MESSAGE'][0]['@textNode'];
                        $xml = base64_decode($b64_ret);
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($xml);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        Out::msgInfo("arr ret ricerca", print_r($arr, true));
                        Out::msgInfo("RicercaPosizioneIUV Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;
                    case $this->name_form . '_callRettificaPosizione':
                        $token = $this->formData[$this->name_form . '_TOKEN'];
                        if (!$token) {
                            Out::msgStop("Attenzione", "Prima di fare la chiamate effettuare il login");
                            break;
                        }
                        $parametri = array(
                            'CodiceIdentificativo' => $this->formData[$this->name_form . '_IUV'],
                        );
                        if ($this->formData[$this->name_form . '_RET_DATASCAD'] != '') {
                            $parametri['DataScadenza'] = $this->formData[$this->name_form . '_RET_DATASCAD'];
                        }
                        if ($this->formData[$this->name_form . '_RET_IMPORTO'] != '') {
                            $parametri['Importo'] = $this->formData[$this->name_form . '_RET_IMPORTO'];
                        }
                        if ($this->formData[$this->name_form . '_RET_CAUSALE'] != '') {
                            $parametri['Causale'] = urlencode(base64_encode($this->formData[$this->name_form . '_RET_CAUSALE']));
                        }
                        $infoAggiuntive = array();
                        if ($this->formData[$this->name_form . '_RET_IA_TARGA'] != '') {
                            $infoAggiuntive['TARGAVEICOLO'] = $this->formData[$this->name_form . '_RET_IA_TARGA'];
                        }
                        if ($this->formData[$this->name_form . '_RET_IA_TEST'] != '') {
                            $infoAggiuntive['TEST'] = $this->formData[$this->name_form . '_RET_IA_TEST'];
                        }
                        if ($infoAggiuntive) {
                            $xmlIA = '<INFOAGGIUNTIVE>';
                            foreach ($infoAggiuntive as $campo => $valore) {
                                $xmlIA .= "<$campo>$valore</$campo>";
                            }
                            $xmlIA .= '</INFOAGGIUNTIVE>';
                            $parametri['InfoAggiuntive'] = base64_encode($xmlIA);
                        }
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsEndpoint'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'rettificaPosizione');
                        cwbPagoPaHelper::setTimeout(20);
                        cwbPagoPaHelper::setDebug_level(9);
//                        $risultato = cwbPagoPaHelper::ricercaPosizioneIUV($token, 'IUV', $parametri);
                        $risultato = cwbPagoPaHelper::rettificaPosizione($token, $parametri);
                        file_put_contents("C:/tmp/rettificaPoszione.txt", $risultato);
                        file_put_contents("C:/tmp/rettificaPoszione_parametri.txt", print_r($parametri, true));
                        if (!$risultato) {
                            Out::msgStop("Attenzione", "Nessun risultato");
                            break;
                        }
                        $xmlObj = new itaXML;
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($risultato);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        file_put_contents("C:/tmp/arr_ret_rettifica.txt", print_r($arr, true));
                        if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                            Out::msgStop("Errore", $arr['MESSAGE'][0]['@textNode']);
                            break;
                        }
                        $b64_ret = $arr['MESSAGE'][0]['@textNode'];
                        $xml = base64_decode($b64_ret);
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($xml);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        Out::msgInfo("arr ret rettifica", print_r($arr, true));
                        Out::msgInfo("RicercaPosizioneIUV Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;
                    case $this->name_form . '_callRimuoviPosizione':
                        $token = $this->formData[$this->name_form . '_TOKEN'];
                        if (!$token) {
                            Out::msgStop("Attenzione", "Prima di fare la chiamate effettuare il login");
                            break;
                        }
                        $parametri = array(
                            'CodiceIdentificativo' => $this->formData[$this->name_form . '_IUV'],
                        );
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsEndpoint'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'rimuoviPosizione');
                        cwbPagoPaHelper::setTimeout(20);
                        cwbPagoPaHelper::setDebug_level(9);
//                        $risultato = cwbPagoPaHelper::ricercaPosizioneIUV($token, 'IUV', $parametri);
                        $risultato = cwbPagoPaHelper::rimuoviPosizione($token, $parametri);
                        file_put_contents("C:/tmp/rimuoviPoszione.txt", $risultato);
                        file_put_contents("C:/tmp/rimuoviPoszione_parametri.txt", print_r($parametri, true));
                        if (!$risultato) {
                            Out::msgStop("Attenzione", "Nessun risultato");
                            break;
                        }
                        $xmlObj = new itaXML;
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($risultato);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        file_put_contents("C:/tmp/arr_ret_rimuovi.txt", print_r($arr, true));
                        if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                            Out::msgStop("Errore", $arr['MESSAGE'][0]['@textNode']);
                            break;
                        }
                        $b64_ret = $arr['MESSAGE'][0]['@textNode'];
                        $xml = base64_decode($b64_ret);
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($xml);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        Out::msgInfo("arr ret rimuovi", print_r($arr, true));
                        Out::msgInfo("RimuoviPosizioneIUV Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;
                    case $this->name_form . '_callRicercaPosizioniDaA':
                        $token = $this->formData[$this->name_form . '_TOKEN'];
                        if (!$token) {
                            Out::msgStop("Attenzione", "Prima di fare la chiamate effettuare il login");
                            break;
                        }
                        $parametri = array(
                            'CodTipScad' => $this->formData[$this->name_form . '_CODTIPSCAD'],
                            'SubTipScad' => $this->formData[$this->name_form . '_SUBTIPSCAD'],
                            'DataPagamDa' => $this->formData[$this->name_form . '_cercaDaData'],
                            'DataPagamA' => $this->formData[$this->name_form . '_cercaAData']
                        );
                        Out::msgInfo("parametri", print_r($parametri, true));
                        $endpoint = $this->formData[$this->name_form . '_CONFIG']['wsEndpoint'];
                        cwbPagoPaHelper::setCurrent_url($endpoint . 'ricercaPosizioniDaA');
                        cwbPagoPaHelper::setTimeout(20);
                        cwbPagoPaHelper::setDebug_level(9);
                        $risultato = cwbPagoPaHelper::ricercaPosizioniDaA($token, $parametri);
                        file_put_contents("C:/tmp/ricercaPosizioniDaA.txt", $risultato);
                        if (!$risultato) {
                            Out::msgStop("Attenzione", "Nessun risultato");
                            break;
                        }
                        $xmlObj = new itaXML;
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($risultato);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        file_put_contents("C:/tmp/arr_ret_ricercaPosizioniDaA.txt", print_r($arr, true));
                        if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                            Out::msgStop("Errore", $arr['MESSAGE'][0]['@textNode']);
                            break;
                        }
                        $b64_ret = $arr['MESSAGE'][0]['@textNode'];
                        $xml = base64_decode($b64_ret);
                        $xmlObj->setTrimBlanks(false);
                        $retXml = $xmlObj->setXmlFromString($xml);
                        $arr = $xmlObj->toArray($xmlObj->asObject());
                        Out::msgInfo("arr ret ricercaPosizioniDaA", print_r($arr, true));
                        Out::msgInfo("ricercaPosizioniDaA Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
//        App::$utente->removeKey($this->name_Form . '_StreamDocumentoPrincipale');
        $this->close = true;
        Out::closeDialog($this->name_Form);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function inizializzaValori() {
        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_MODULO_URI', false);
        $uri_login = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_MODULO_URI_LOGIN', false);
        $user = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_MODULO_USER', false);
        $pwd = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_MODULO_PWD', false);
        $domain = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_MODULO_DOMAIN', false);

        //CONFIGURAZIONI
        Out::valore($this->name_form . '_CONFIG[wsEndpoint]', $uri['CONFIG']);
        Out::valore($this->name_form . '_CONFIG[wsLogin]', $uri_login['CONFIG']);
        Out::valore($this->name_form . '_CONFIG[wsCodTipScad]', 74); //74 = pratiche online
        Out::valore($this->name_form . '_CONFIG[wsSubTipScad]', 0); //0 = non configurato
        Out::valore($this->name_form . '_CONFIG[wsUser]', $user['CONFIG']);
        Out::valore($this->name_form . '_CONFIG[wsPassword]', $pwd['CONFIG']);
        Out::valore($this->name_form . '_CONFIG[wsDomain]', $domain['CONFIG']);

        //UFFICIO
        Out::valore($this->name_form . '_TIPOUFFICIO', '');
        Out::valore($this->name_form . '_CODICEUFFICIO', '');
        Out::valore($this->name_form . '_TIPOLOGIASERVIZIO', 'AEN');

        //POSIZIONE
        Out::valore($this->name_form . '_CODTIPSCAD', '74');
        Out::valore($this->name_form . '_SUBTIPSCAD', '0');
        Out::valore($this->name_form . '_PROGCITYSC', '');
        $chiave = time() . "FO";
        Out::valore($this->name_form . '_PROGCITYSCA', $chiave);
        Out::valore($this->name_form . '_DESCRPEND', 'SCADENZA DI TEST ' . $chiave);
        Out::valore($this->name_form . '_MODPROVEN', '74'); //71=CDS, 72=ZTL, 73=FIERE, 74=procedimenti online
        Out::valore($this->name_form . '_ANNORIF', '2019');
        Out::valore($this->name_form . '_NUMDOC', '1');
        Out::valore($this->name_form . '_DATASCADE', '20191231');
        Out::valore($this->name_form . '_IMPDAPAGTO', '10');
        Out::valore($this->name_form . '_FLAG_PUBBL', '4');
        Out::valore($this->name_form . '_ANNOEMI', '2019');
        Out::valore($this->name_form . '_NUMEMI', '1');
        Out::valore($this->name_form . '_IDBOL_SERE', '');

        //SOGGETTO
        Out::valore($this->name_form . '_CODFISCALE', 'VRDCRL80A01F386A');
        Out::valore($this->name_form . '_PARTIVA', '');
        Out::valore($this->name_form . '_NOME', '');
        Out::valore($this->name_form . '_COGNOME', 'VERDI CARLO');
        Out::valore($this->name_form . '_DATANASC', '19800101');
        Out::valore($this->name_form . '_LUOGONASC', 'TEST');
        Out::valore($this->name_form . '_COMUNERESID', 'TEST');
        Out::valore($this->name_form . '_PROVINCIARESID', 'TT');
        Out::valore($this->name_form . '_INDIRIZZORESID', 'VIA DI TEST, 10');
        Out::valore($this->name_form . '_CAP', '00000');
        Out::valore($this->name_form . '_PEC', 'testpec@testpec.it');

        //RATE
        Out::valore($this->name_form . '_R1_NUMRATA', '1');
        Out::valore($this->name_form . '_R1_IMPDAPAGTO', '5');
        Out::valore($this->name_form . '_R1_DESCRPEND', $chiave . ' Rata 1');
        Out::valore($this->name_form . '_R1_DATASCADE', '20191031');
        Out::valore($this->name_form . '_R2_NUMRATA', '2');
        Out::valore($this->name_form . '_R2_IMPDAPAGTO', '5');
        Out::valore($this->name_form . '_R2_DESCRPEND', $chiave . ' Rata 2');
        Out::valore($this->name_form . '_R2_DATASCADE', '20191130');
    }

}
