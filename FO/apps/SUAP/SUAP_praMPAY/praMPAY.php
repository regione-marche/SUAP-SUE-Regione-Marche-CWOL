<?php

require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
require_once ITA_LIB_PATH . '/itaPHPPagoPA/itaPHPPagoPA.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';

class praMPAY extends itaModelFO {

    const ITA_DB_SUFFIX = '01';

    public $praLib;
    public $praErr;
    public $PRAM_DB;
    public $idTabella;
    public $idGrafico;
    public $lista_enti;
    public $tipo;
    public $sportello;
    public $caption;
    public $subcaption;
    public $anno;
    public $frontOfficeLib;
    public $ITALWEB_DB;
    public $itaPHPPagoPA;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);
            $this->frontOfficeLib = new frontOfficeLib();
            $this->itaPHPPagoPA = new itaPHPPagoPA();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB', ITA_DB_SUFFIX);
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        $this->html = new html();

        $this->html->appendHtml('<div class="cds-main">');

        switch ($this->request['event']) {
            case 'pagamento':
                $ITAFRONTOFFICE_DB = ItaDB::DBOpen('ITAFRONTOFFICE', ITA_DB_SUFFIX);
                $paramTmp = $this->frontOfficeLib->getEnv_config("PAGOPA", $ITAFRONTOFFICE_DB);
                $Param = array();
                foreach ($paramTmp as $arrayParametro) {
                    $Param[$arrayParametro['CHIAVE']] = $arrayParametro['CONFIG'];
                }

                $uri = $Param['PAGOPA_MODULO_URI'];
                $uri_log = $Param['PAGOPA_MODULO_URI_LOGIN'];
                $user = $Param['PAGOPA_MODULO_USER'];
                $pwd = $Param['PAGOPA_MODULO_PWD'];
                $domain = $Param['PAGOPA_MODULO_DOMAIN'];

//                $UrlMPAY = 'http://payertest.regione.marche.it/cart/extS2SRID.do';
                $UrlMPAY = 'http://payertest.regione.marche.it/mpay/cart/extS2SRID.do';

                $url = ItaUrlUtil::GetPageUrl(array('event' => 'avvenutoPagamento'));
                $url_not = ItaUrlUtil::GetPageUrl(array('event' => 'notificaPagamento'));
                $url_back = ItaUrlUtil::GetPageUrl(array('event' => 'annullaPagamento'));

                if (isset($this->request['importo'])) {
                    $importo = $this->request['importo'];
                } else {
                    $importo = 100;
                }
//                $importo = intval($importo * 100); //per modulo pa va passato il valore così com'è

                /*
                 * GetItaEngineContextToken
                 */
                $this->itaPHPPagoPA->setDebug_level(9);
                $this->itaPHPPagoPA->setTimeout(5);
                $this->itaPHPPagoPA->setCurrent_url($uri_log . "/GetItaEngineContextToken");
                $token = $this->itaPHPPagoPA->GetItaEngineContextToken($user, $pwd, $domain);
                file_put_contents("/tmp/rest_token", $token);
                if (!$token) {
                    file_put_contents("/tmp/errore_token", "token non preso");
                    print_r("ERRORE TOKEN");
                    break;
                }

                /*
                 * pubblicaPosizione
                 */
                $chiave = time();
                //preparo array di pubblicazione posizione
                $XML = '<PENDENZA>';

                $XML .= '<CODTIPSCAD>74</CODTIPSCAD>';
                $XML .= '<SUBTIPSCAD>0</SUBTIPSCAD>';
                $XML .= '<PROGCITYSC></PROGCITYSC>';
                $XML .= '<PROGCITYSCA>' . $chiave . '</PROGCITYSCA>';
                $XML .= '<DESCRPEND>test FO </DESCRPEND>';
                $XML .= '<MODPROVEN>74</MODPROVEN>';
                $XML .= '<ANNORIF>' . date('Y') . '</ANNORIF>';
                $XML .= '<NUMDOC>' . $chiave . '</NUMDOC>';
                $XML .= '<DATASCADE>' . date('Y') . '1231</DATASCADE>';
                $XML .= '<IMPDAPAGTO>' . $importo . '</IMPDAPAGTO>';
                $XML .= '<FLAG_PUBBL>4</FLAG_PUBBL>';
//                $XML .= '<ANNOEMI>' . date('Y') . '</ANNOEMI>';
//                $XML .= '<NUMEMI>' . date(His) . '</NUMEMI>';
//                $XML .= '<IDBOL_SERE></IDBOL_SERE>';
                $XML .= '<SOGGETTO>';
                $XML .= '<PROGSOGG>0</PROGSOGG>';
                $XML .= '<CODFISCALE>TTTCRL82D22F522A</CODFISCALE>';
                $XML .= '<PARTIVA></PARTIVA>';
                $XML .= '<NOME></NOME>';
                $XML .= '<COGNOME>TESTI CARLO</COGNOME>';
                $XML .= '<DATANASC>19800101</DATANASC>';
                $XML .= '<LUOGONASC>MACERATA</LUOGONASC>';
                $XML .= '<COMUNERESID>POTENZA PICENA</COMUNERESID>';
                $XML .= '<PROVINCIARESID>MC</PROVINCIARESID>';
                $XML .= '<INDIRIZZORESID>VIA DI TEST, 10</INDIRIZZORESID>';
                $XML .= '<CAP>62018</CAP>';
                $XML .= '<PEC>mario.mazza@italsoft.eu</PEC>';
                $XML .= '</SOGGETTO>';
//                $XML .= '<RATE>';
//                $XML .= '<ROW0>';
//                $XML .= '<NUMRATA>' . $this->formData[$this->name_form . '_R1_NUMRATA'] . '</NUMRATA>';
//                $XML .= '<IMPDAPAGTO>' . $this->formData[$this->name_form . '_R1_IMPDAPAGTO'] . '</IMPDAPAGTO>';
//                $XML .= '<DESCRPEND>' . $this->formData[$this->name_form . '_R1_DESCRPEND'] . '</DESCRPEND>';
//                $XML .= '<DATASCADE>' . $this->formData[$this->name_form . '_R1_DATASCADE'] . '</DATASCADE>';
//                $XML .= '</ROW0>';
//                $XML .= '<ROW1>';
//                $XML .= '<NUMRATA>' . $this->formData[$this->name_form . '_R2_NUMRATA'] . '</NUMRATA>';
//                $XML .= '<IMPDAPAGTO>' . $this->formData[$this->name_form . '_R2_IMPDAPAGTO'] . '</IMPDAPAGTO>';
//                $XML .= '<DESCRPEND>' . $this->formData[$this->name_form . '_R2_DESCRPEND'] . '</DESCRPEND>';
//                $XML .= '<DATASCADE>' . $this->formData[$this->name_form . '_R2_DATASCADE'] . '</DATASCADE>';
//                $XML .= '</ROW1>';
//                $XML .= '</RATE>';
                $XML .= '<INFOAGGIUNTIVE>';
                $XML .= '<TARGAVEICOLO>' . $this->formData[$this->name_form . '_TARGA'] . '</TARGAVEICOLO>';
                $XML .= '<DATAVERBALE>' . $this->formData[$this->name_form . '_DATAVERBALE'] . '</DATAVERBALE>';
                $XML .= '<IDENTIFICATIVOVERBALE>' . $this->formData[$this->name_form . '_IDENTIFICATIVOVERBALE'] . '</IDENTIFICATIVOVERBALE>';
                $XML .= '<TIPOUFFICIO>' . $this->formData[$this->name_form . '_TIPOUFFICIO'] . '</TIPOUFFICIO>';
                $XML .= '<CODICEUFFICIO>' . $this->formData[$this->name_form . '_CODICEUFFICIO'] . '</CODICEUFFICIO>';
                $XML .= '<TIPOLOGIASERVIZIO>AFT</TIPOLOGIASERVIZIO>';
                $XML .= '</INFOAGGIUNTIVE>';
                $XML .= '</PENDENZA>';

                $parametri = array();
                $parametri['CodTipScad'] = 74;
                $parametri['SubTipScad'] = 0;
                $parametri['Pendenza'] = $XML;

//                $risultato = cwbPagoPaHelper::pubblicaPosizione($token, 'XML', $parametri);
                $this->itaPHPPagoPA->setCurrent_url($uri . 'pubblicaPosizione');
                $risultato = $this->itaPHPPagoPA->pubblicaPosizione($token, 'XML', $parametri);
                file_put_contents("/tmp/risultato_inserisci_posizione.txt", print_r($risultato, true));
                if (!$risultato) {
                    file_put_contents("/tmp/errore.txt", print_r($risultato, true));
                    $this->itaPHPPagoPA->DestroyItaEngineContextToken($token);
                    $this->html->appendHtml($html);
                    break;
                }
                $xmlObj = new itaXML;
                if (!$xmlObj) {
                    print_r('NO XML OBJECT');
                    break;
                }
                $retXml = $xmlObj->setXmlFromString($risultato);
                $arr = $xmlObj->toArray($xmlObj->asObject());
                file_put_contents("/tmp/arr_ret.txt", print_r($arr, true));
                if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                    print_r($arr['MESSAGE'][0]['@textNode']);
                    $this->html->appendHtml($html);
                    break;
                }
                $b64_ret = $arr['MESSAGE'][0]['@textNode'];
                $xml = base64_decode($b64_ret);
                $retXml = $xmlObj->setXmlFromString($xml);
                $arr = $xmlObj->toArray($xmlObj->asObject());
                $IUV = $arr['ROW0'][0]['IUV'][0]['@textNode'];
                file_put_contents("/tmp/IUV.txt", $IUV);
                /*
                 * una volta preso lo IUV va fatta la chiamata ad eseguiPagamentoIUV
                 */
                $url = ItaUrlUtil::GetPageUrl(array('event' => 'avvenutoPagamento'));
                $parametri = array(
                    'CodiceIdentificativo' => $IUV,
                    'urlReturn' => htmlentities($url, ENT_COMPAT, 'ISO-8859-1')
                );
                $this->itaPHPPagoPA->setCurrent_url($uri . 'eseguiPagamento');
                $url_redirect = $this->itaPHPPagoPA->eseguiPagamento($token, 'IUV', $parametri);
                file_put_contents("/tmp/url_redirect.txt", $url_redirect);
                $xmlObj = new itaXML;
                if (!$xmlObj) {
                    print_r('NO XML OBJECT');
                    break;
                }
                $retXml = $xmlObj->setXmlFromString($url_redirect);
                $arr = $xmlObj->toArray($xmlObj->asObject());
                if ($arr['EXITCODE'][0]['@textNode'] == 1) {
                    print_r($arr['MESSAGE'][0]['@textNode']);
                    $this->html->appendHtml($html);
                    break;
                }
                file_put_contents("/tmp/arr_url_ret.txt", print_r($arr, true));
                $url_ret = $arr['MESSAGE'][0]['@textNode'];
                $UrlMPAYCart = 'http://payertest.regione.marche.it/mpay/cart/extCart.do';
                file_put_contents("/tmp/redirect_effettiva.txt", $url_ret);
                
                
                //destroy token 
                $desToken = $this->itaPHPPagoPA->DestroyItaEngineContextToken($token);
                
                header('Location: ' . $url_ret);
                exit();
                $this->html->appendHtml($html);
                break;
            case 'avvenutoPagamento':
                print_r("avvenuto<br/>");
                var_dump($this->request);
                $pid = $this->request['buffer'];

                sleep(10);

                $T = date('YmdHi');
                $C = "PortaleExt";
                $encryptIV = "87654321";
                $encryptKey = "987654321123456789654321";

                $H = md5($encryptIV . $pid . $encryptKey . $T);
                //preparazione del Bi
                //$Bi = $T . $C . base64_encode($BD) . $H;
                $Bi = '<Buffer>' .
                        '<TagOrario>' . $T . '</TagOrario>' .
                        '<CodicePortale>' . $C . '</CodicePortale >' .
                        '<BufferDati>' . base64_encode($pid) . '</BufferDati>' .
                        '<Hash>' . $H . '</Hash>' .
                        '</Buffer>';

                require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

                $headers = array(
                    'User-Agent' => $_SERVER['HTTP_USER_AGENT']
                );

                $parametri = array(
                    'buffer' => $Bi
                );

                $itaRestClient = new itaRestClient();
                $itaRestClient->setTimeout(5);
                $itaRestClient->setDebugLevel(9);
                $itaRestClient->setCurlopt_useragent($_SERVER['HTTP_USER_AGENT']);

                $UrlPID = 'http://payertest.regione.marche.it/mpay/cart/extS2SPID.do';
                if (!$itaRestClient->get($UrlPID, $parametri, $headers)) {
                    //errore
                    $itaRestClient->getErrMessage();
                    return false;
                }

                $return = $itaRestClient->getResult();

                $debug = $itaRestClient->getDebug();

                $status = $itaRestClient->getHttpStatus();

                print_r("<br/>RETURN: <br/>");
                print_r($return);
                print_r("<br/>FINE RETURN: <br/>");

                print_r("<br/>STATUS: <br/>");
                print_r($status);
                print_r("<br/>FINE STATUS: <br/>");

                break;
            case 'notificaPagamento':
                file_put_contents("/tmp/arriva_notifica", print_r($this->request, true));
                //chiamo il servizio del ModuloPA
                $itaRestClient = new itaRestClient();
                $itaRestClient->setTimeout(5);
                $itaRestClient->setDebugLevel(0);
                $itaRestClient->setCurlopt_useragent($_SERVER['HTTP_USER_AGENT']);

                $parametri = array(
                    'token' => $this->request['token'],
                    'CodRiferimento' => $this->request['CodRiferimento'],
                    'buffer' => $this->request['buffer']
                );

                $Url = 'https://srvcity-demo.gruppoapra.com/cwol-test/wsrest/service.php/pagoPA/rendicontazionePuntualeGet';
                file_put_contents("/tmp/notifica_chiamoRest", $Url);
                if (!$itaRestClient->get($Url, $parametri, $headers)) {
                    file_put_contents("/tmp/rest_error", $itaRestClient->getErrMessage());
                    //errore
                    $itaRestClient->getErrMessage();
                    return false;
                }
                $return = $itaRestClient->getResult();
                file_put_contents("/tmp/return_notifica", $return);
                break;
            case 'annullaPagamento':
                $this->mostra_risultati();
                break;

            default:
                $this->mostra_risultati();
                break;
        }

        $this->html->appendHtml('</div>');

        return output::$html_out = $this->html->getHtml();
    }

    public function mostra_risultati() {
        $html = '';
        $html .= $this->getHtmlPagamento($contrav_rec);
        $this->html->appendHtml($html);
    }

    public function getHtmlPagamento() {
        $html .= '<div class="ui-widget ui-widget-content ui-corner-all divDocumenti" style="text-align:center;background-color:lightyellow;">'; //div generale


        $Url = ItaUrlUtil::GetPageUrl(array('model' => 'praMPAY', 'event' => 'pagamento'));

//        $html .= "<p><b>Importo da pagare: <span style=\"color:red;font-size:2em;\"></span></b></p><br/>";
//        $html .= '<input type="text" name="scegliImporto" size="8" maxlength="10"  /><br/>';
//
//
//        $html .= '<a id="paga-adesso" class="italsoft-button" href="' . $Url . ' data-href="' . $Url . '"> Paga adesso</a>';
//        $html .= '</div><br>';
//

        $Url = ItaUrlUtil::GetPageUrl(array('model' => 'praMPAY', 'event' => 'pagamento'));
        $html .= '
            <div style="width:100%;">
                <form name="search" action="' . $Url . '" method="POST"><br/>   
                    <table class="tabella_app_italsoft" style="width: 100%;">
                        <tr>
                            <td width="200px">Importo</td>
                            <td><input type="text" name="importo" size="8" maxlength="10" value="' . $this->request['importo'] . '" /></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" name="consulta_cds" class="italsoft-button" value="Paga adesso" /></td>
                        </tr>
                    </table>
                </form>
            </div>
            <br />';

        return $html;
    }

    public function creaBuffer($BD) {
        $Bi = '<Buffer>' .
                '<TagOrario>' . $T . '</TagOrario>' .
                '<CodicePortale>' . $C . '</CodicePortale >' .
                '<BufferDati>' . base64_encode($BD) . '</BufferDati>' .
                '<Hash>' . $H . '</Hash>' .
                '</Buffer>';
        return $Bi;
    }

    static function interrogazioneOrdineNexi($path, $alias, $codTrans, $chiave_mac) {
//        $mac = strtoupper(sha1($alias . $codTrans . $id_op . $type_op . $user . $chiave_mac));
        $timeStamp = (time()) * 1000;
        // Calcolo MAC
        $mac = sha1('apiKey=' . $alias . 'codiceTransazione=' . $codTrans . 'timeStamp=' . $timeStamp . $chiave_mac);
        require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

        $parametri = array(
            'apiKey' => $alias,
            'codiceTransazione' => $codTrans,
            'timeStamp' => $timeStamp,
            'mac' => $mac
        );
        $itaRestClient = new itaRestClient();
        if (!$itaRestClient->post($path, false, array(), json_encode($parametri), 'application/json')) {
            //errore
            return false;
        }
        return $itaRestClient->getResult();
    }

}
