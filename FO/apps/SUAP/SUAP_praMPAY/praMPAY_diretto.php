<?php

class praMPAY extends itaModelFO {

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

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        $this->html = new html();

        $this->html->appendHtml('<div class="cds-main">');

        switch ($this->request['event']) {
            case 'pagamento':

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
                $importo = intval($importo * 100);

//                String sTagOrario = Utilities.getTagOrario();
//			String hash = Utilities.getMD5Hash(encryptIV + bufferDati + encryptKey + sTagOrario);
//			String bufferDatiCrypt = Base64.encode(bufferDati.getBytes()); //URLEncoder.encode(cryptoService.encryptBASE64(bufferDati), "UTF-8");
//			
//			/*cryptoService.destroy();
//			cryptoService = null;*/
//			
//			buffer = "<Buffer>" +
//	        	"<TagOrario>" + sTagOrario + "</TagOrario>" + 
//	        	"<CodicePortale>" + codicePortale + "</CodicePortale>" +
//	        	"<BufferDati>" + bufferDatiCrypt + "</BufferDati>" + 
//	        	"<Hash>" + hash + "</Hash>" +
//	    	"</Buffer>";

                $T = date('YmdHi');

                $C = "PortaleExt";
                $encryptIV = "87654321";
                $encryptKey = "987654321123456789654321";

                $BD = '<PaymentRequest>' .
                        '<PortaleID>' . $C . '</PortaleID>' .
                        '<Funzione>PAGAMENTO</Funzione>' .
                        '<URLDiRitorno>' . htmlentities($url, ENT_COMPAT, 'ISO-8859-1') . '</URLDiRitorno>' .
                        '<URLDiNotifica>' . htmlentities($url_not, ENT_COMPAT, 'ISO-8859-1') . '</URLDiNotifica>' .
                        '<URLBack>' . htmlentities($url_back, ENT_COMPAT, 'ISO-8859-1') . '</URLBack>' .
                        '<CommitNotifica>S</CommitNotifica>' .
                        '<UserData>' .
                        '<EmailUtente>mario.mazza@italsoft.eu</EmailUtente>' .
                        '<IdentificativoUtente>MZZMRA82D22F522Q</IdentificativoUtente>' .
                        '</UserData>' .
                        '<ServiceData>' .
                        '<CodiceUtente>000RM</CodiceUtente>' .
                        '<CodiceEnte>00936</CodiceEnte>' .
                        '<TipoUfficio></TipoUfficio>' .
                        '<CodiceUfficio></CodiceUfficio>' .
                        '<TipologiaServizio>SSS</TipologiaServizio>' .
                        '<NumeroOperazione>' . time() . '</NumeroOperazione>' .
                        '<NumeroDocumento>' . rand(10000, 99999) . '</NumeroDocumento>' .
                        '<AnnoDocumento>2019</AnnoDocumento>' .
                        '<Valuta>EUR</Valuta>' .
                        '<Importo>' . $importo . '</Importo>' .
                        '<MarcaDaBolloDigitale>' .
                        '<ImportoMarcaDaBolloDigitale></ImportoMarcaDaBolloDigitale>' .
                        '<SegnaturaMarcaDaBolloDigitale></SegnaturaMarcaDaBolloDigitale>' .
                        '<TipoBolloDaErogare></TipoBolloDaErogare>' .
                        '<ProvinciaResidenza></ProvinciaResidenza>' .
                        '</MarcaDaBolloDigitale>' .
                        '<DatiSpecifici></DatiSpecifici>' .
                        '</ServiceData>' .
                        '<AccountingData>' .
                        '<ImportiContabili>' .
                        '<ImportoContabile>' .
                        '<Identificativo></Identificativo>' .
                        '<Valore></Valore>' .
                        '</ImportoContabile>' .
                        '</ImportiContabili>' .
                        '<EntiDestinatari>' .
                        '<EnteDestinatario>' .
                        '<CodiceEntePortaleEsterno>111111111111</CodiceEntePortaleEsterno>' .
                        '<DescrEntePortaleEsterno>Ente 111111111111</DescrEntePortaleEsterno>' .
                        '<Valore>' . $importo . '</Valore>' .
                        '<Causale><![CDATA[Causale per ente 111111111111]]></Causale>' .
                        '<ImportoContabileIngresso></ImportoContabileIngresso>' .
                        '<ImportoContabileUscita></ImportoContabileUscita>' .
                        '<CodiceUtenteBeneficiario>000TO</CodiceUtenteBeneficiario>' .
                        '<CodiceEnteBeneficiario>06954</CodiceEnteBeneficiario>' .
                        '<TipoUfficioBeneficiario></TipoUfficioBeneficiario>' .
                        '<CodiceUfficioBeneficiario></CodiceUfficioBeneficiario>' .
                        '</EnteDestinatario>' .
                        '</EntiDestinatari>' .
                        '</AccountingData>' .
                        '</PaymentRequest>';
                
                //NOTA: AccountingData non deve essere inserito. Mail di Fabrizio Quaresima del 03.05.2019

                /*
                //BD di test preso da documentazione
                $BD = "<PaymentRequest>" .
                        "<PortaleID>$C</PortaleID>" .
                        "<Funzione>PAGAMENTO</Funzione>" .
                        "<URLDiRitorno>" . htmlentities($url, ENT_COMPAT, 'ISO-8859-1') . "</URLDiRitorno>" .
                        "<URLDiNotifica>" . htmlentities($url_back, ENT_COMPAT, 'ISO-8859-1') . "</URLDiNotifica>" .
                        "<URLBack>" . htmlentities($url_back, ENT_COMPAT, 'ISO-8859-1') . "</URLBack>" .
                        "<CommitNotifica>S</CommitNotifica>" .
                        "<UserData>" .
                        "<EmailUtente>mario.mazza@italsoft.eu</EmailUtente>" .
                        "<IdentificativoUtente>MZZMRA82D22F522Q</IdentificativoUtente>" .
                        "</UserData>" .
                        "<ServiceData>" .
                        "<CodiceUtente>000RM</CodiceUtente>" .
                        "<CodiceEnte>00936</CodiceEnte>" .
                        "<TipoUfficio></TipoUfficio>" .
                        "<CodiceUfficio>1</CodiceUfficio>" .
                        "<TipologiaServizio>SSS</TipologiaServizio>" .
                        "<NumeroOperazione>937264gi8841837Kppdf</NumeroOperazione>" .
                        "<NumeroDocumento>123412348636242874</NumeroDocumento>" .
                        "<AnnoDocumento>2010</AnnoDocumento>" .
                        "<Valuta>EUR</Valuta>" .
                        "<Importo>" . $importo . "</Importo>" .
                        "<DatiSpecifici />" .
                        "</ServiceData>" .
                        "</PaymentRequest>";
*/

                $H = md5($encryptIV . $BD . $encryptKey . $T);
                
                file_put_contents("/tmp/old_BD", $BD);

                //preparazione del Bi
                //$Bi = $T . $C . base64_encode($BD) . $H;
                $Bi = '<Buffer>' .
                        '<TagOrario>' . $T . '</TagOrario>' .
                        '<CodicePortale>' . $C . '</CodicePortale >' .
                        '<BufferDati>' . base64_encode($BD) . '</BufferDati>' .
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
                $itaRestClient->setTimeout(20);
                $itaRestClient->setDebugLevel(9);
                $itaRestClient->setCurlopt_useragent($_SERVER['HTTP_USER_AGENT']);

                if (!$itaRestClient->get($UrlMPAY, $parametri, $headers)) {
                    //errore
                    $itaRestClient->getErrMessage();
                    return false;
                }

                $return = $itaRestClient->getResult();

                $debug = $itaRestClient->getDebug();

                print_r("<br/>RETURN: <br/>");
                print_r($return);
                print_r("<br/>FINE RETURN: <br/>");

                $rid = $return;

                print_r("<br/>RID: <br/>");
                print_r($rid);
                print_r("<br/>FINE RID: <br/>");


                print_r("<br/>DEBUG: <br/>");
                print_r($debug);
                print_r("<br/>FINE DEBUG: <br/>");


                /*
                 * REDIRECT
                 */

                //preparo nuovo Buffer invio
                $H = md5($encryptIV . $rid . $encryptKey . $T);
                $Bi = '<Buffer>' .
                        '<TagOrario>' . $T . '</TagOrario>' .
                        '<CodicePortale>' . $C . '</CodicePortale>' .
                        '<BufferDati>' . base64_encode($rid) . '</BufferDati>' .
                        '<Hash>' . $H . '</Hash>' .
                        '</Buffer>';

                $UrlMPAYCart = 'http://payertest.regione.marche.it/mpay/cart/extCart.do';
//                $Bi = $itaPHPMPay->getBufferInvio($rid);
                $param = array(
                    'buffer' => $Bi
                );
                $redirect = $UrlMPAYCart . "?" . http_build_query($param);
                file_put_contents("/tmp/old_rediret", $redirect);
                //User-Agent: $_SERVER['HTTP_USER_AGENT']
                header('Location: ' . $UrlMPAYCart . "?" . http_build_query($param));
                exit();
//
//                $html = '<form action="http://payertest.regione.marche.it/mpay/cart/extCart.do" name="myform" id="myform" method ="post">
//                        <input type="hidden" name="formName" id=" formName " value="formExtern">
//                        <input type="hidden" name="buffer" id="buffer" value="' . $Bi . '">
//                        </form>
//                        <script>document.myform.submit();</script>
//                        ';
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
                $itaRestClient->setTimeout(20);
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
                print_r("notifica<br/>");
                var_dump($this->request);
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
