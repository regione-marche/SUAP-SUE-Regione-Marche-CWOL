<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaMaster.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZipCommandLine.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaFtpUtils.class.php';

/**
 *
 * Classe per la gestione dell'intermediario mpay
 * 
 */
class cwbPagoPaMPay extends cwbPagoPaMaster {

    private $errMessage;
    private $timeout = 20;
    private $debug_level = 0;
    private $arrayXmlPaymentData = array();
    private $tipiTracciato = array(
        'IVP', 'IVC', 'IVS', 'IVM', 'IVF', 'IVB');

    function getTimeout() {
        return $this->timeout;
    }

    function getDebug_level() {
        return $this->debug_level;
    }

    function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    function setDebug_level($debug_level) {
        $this->debug_level = $debug_level;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    /**
     * genera il bollettino per il pagamento
     * 
     * @param array $params
     * @return binary il bollettino oppure false     
     *  
     */
    protected function customGeneraBollettino($params) {
        $iuv = null;
        if ($params['CodiceIdentificativo']) {
            $iuv = $params['CodiceIdentificativo'];
            $filtri = array('IUV' => $iuv);
        } else {
            $filtri = array(
                'CODTIPSCAD' => $params['CodTipScad'],
                'SUBTIPSCAD' => $params['SubTipScad'],
                'PROGCITYSC' => $params['ProgCitySc'],
                'ANNORIF' => $params['AnnoRif'],
                'NUMRATA' => $params['NumRata']
            );
        }
        if (!$filtri) {
            $this->handleError(-1, "filtri non trovati");
            return false;
        }

        /*
         * NOTA: se la rata è 0 viene stampato solo l'avviso per la rata unica
         * Indicando il numero di rate TOTALI viene stampato l'avviso realtivo (rata unica + 2/3 rate)
         * La prima posizione cercata è SEMPRE quella con rata 0 dato che la stampa per rata unica va sempre fatta
         */
        $pdf_arr = array();

        $agidScadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
        if (!$agidScadenza) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        }
        $soggetto = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza['PROGSOGG'], $agidScadenza['PROGSOGGEX']);
        $pdf_arr[] = $this->generaBollettinoRataUnica($agidScadenza, $soggetto);
        //vanno stampati il minor numero possibile di fogli (considerando 2 o 3 rate per foglio), calcolo massimo numero di pagine da 3 rate, poi da 2 rate
        $n_rate = $agidScadenza['NUMRATE'];
        if ($n_rate > 1) {
            $resto = $n_rate % 3;
            switch ($resto) {
                case 0:
                    $n_modelli3 = intval($n_rate / 3);
                    $n_modelli2 = 0;
                    break;
                case 1:
                    $n_modelli3 = intval($n_rate / 3) - 1;
                    $n_modelli2 = 2;
                    break;
                case 2:
                    $n_modelli3 = intval($n_rate / 3);
                    $n_modelli2 = 1;
                    break;
                default:
                    break;
            }

            $curr_rata = 0;
            for ($i = 1; $i <= $n_modelli2; $i++) {
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza1 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza2 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $pdf_arr[] = $this->generaBollettinoRate($rataScadenza1, $rataScadenza2);
            }
            for ($i = 1; $i <= $n_modelli3; $i++) {
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza1 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza2 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $curr_rata++;
                if ($agidScadenza['PROGCITYSC'] > 0) {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSC' => $agidScadenza['PROGCITYSC'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                } else {
                    $filtri = array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD'], 'PROGCITYSCA' => $agidScadenza['PROGCITYSCA'], 'ANNORIF' => $agidScadenza['ANNORIF'], 'NUMRATA' => $curr_rata);
                }
                $rataScadenza3 = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                $pdf_arr[] = $this->generaBollettinoRate($rataScadenza1, $rataScadenza2, $rataScadenza3);
            }
        }

        if (!$pdf_arr) {
            $this->setErrMessage("Errore nella creazione dei pdf: lista pdf vuota");
            return false;
        }

        return $this->concatenaPDF($pdf_arr);
    }

    public function getCustomConfIntermediario() {
        return $this->getLibDB_BGE()->leggiBgeAgidConfMPay(array());
    }

    public function customCalcoloCodRiferimento($pendenza, $codRiferimento) {
        $filtri = array();
        $filtri['CODTIPSCAD'] = $pendenza['CODTIPSCAD'];
        $filtri['SUBTIPSCAD'] = $pendenza['SUBTIPSCAD'];
        $filtri['PROGCITYSC'] = $pendenza['PROGCITYSC'];
        $filtri['ANNORIF'] = $pendenza['ANNORIF'];
        $filtri['NUMRATA'] = $pendenza['NUMRATA'];
        $scadenzaPresente = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
        if ($scadenzaPresente) {
            // se trovo la scadenza per ripubblicare devo cambiare il codriferimento facendo +1
            $codRifTmp = substr($scadenzaPresente['CODRIFERIMENTO'], 0, -2);
            $lastTwoCod = substr($scadenzaPresente['CODRIFERIMENTO'], -2);
            $lastTwoCodInt = intval($lastTwoCod) + 1;
            $codRiferimento = $codRifTmp . $lastTwoCodInt;
            $scadenzaPresente['CODRIFERIMENTO'] = $codRiferimento;
            $this->aggiornaBgeAgidScadenze($scadenzaPresente);
        } else {
            // se non è presente aggiungo 10 alla fine del codice, poi per ripubblicare incremento il 10 finale
            $codRiferimento = $codRiferimento . '10';
        }

        return $codRiferimento;
    }

    protected function getCodiceSegregazione() {
        $filtri['INTERMEDIARIO'] = itaPagoPa::MPAY_TYPE;
        $res = $this->getLibDB_BGE()->leggiBgeAgidInterm($filtri,false);
        return $res['CODSEGREG'];
    }

    public function rettificaPosizioneDaIUV($IUV, $toUpdate) {
        if (!$IUV) {
            $this->handleError(-1, "Iuv mancante");
            return false;
        }
        $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('IUV' => $IUV));
        if (!$scadenza) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        } else if (count($scadenza) > 1) {
            $this->handleError(-1, "Errore scadenze multiple");
            return false;
        } else if (intval($scadenza['STATO']) >= 10) {
            $this->handleError(-1, "Scadenza pagata. Impossibile modificarla");
            return false;
        }

        $scadenza = $scadenza[0];

        if ($toUpdate['DataScadenza']) {
            $scadenza['DATASCADE'] = $toUpdate['DataScadenza'];
        }

        if ($toUpdate['Importo']) {
            $scadenza['IMPDAPAGTO'] = $toUpdate['Importo'];
        }

        if ($toUpdate['Causale']) {
            $scadenza['DESCRPEND'] = $toUpdate['Causale'];
        }

        try {
            cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());

            $this->aggiornaBgeAgidScadenze($scadenza, true);
            if ($toUpdate['InfoAggiuntive']) {
                $this->inserisciAggiornaBgeAgidScaInfo($scadenza['PROGKEYTAB'], $toUpdate['InfoAggiuntive'], true);
            }
            cwbDBRequest::getInstance()->commitManualTransaction();

            return true;
        } catch (Exception $ex) {
            cwbDBRequest::getInstance()->rollBackManualTransaction();

            $this->handleError(-1, $ex->getMessage());
        }

        return false;
    }

    /**
     * Ricerca una posizione da iuv
     * 
     * @param String $IUV
     * @return false se errore oppure array con la posizione
     */
    public function customRicercaPosizioneDaIUV($IUV) {
        if (!$IUV) {
            $this->handleError(-1, "Parametri non passati");
            return false;
        }
        $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('IUV' => $IUV));
        if (!$scadenze) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        }
        if (count($scadenze) > 1) {
            $this->handleError(-1, "Scadenze multiple trovate con lo stesso iuv");
            return false;
        }

        $scadenza = $scadenze[0];
        $pagato = (intval($scadenza['STATO']) === 10 || intval($scadenza['STATO']) === 12);
        $stato = $pagato ? self::STATO_PAGAMENTO_PAGATO : self::STATO_PAGAMENTO_NONPAGATO;
        return $this->formatRispostaDaRicercaPosizione($scadenza['DESCRPEND'], $stato, $scadenza['DATASCADE'], ($scadenza['IMPDAPAGTO'] * 100), $scadenza['IUV'], $scadenza['CODRIFERIMENTO'], $scadenza['NUMRATA'], $scadenza['PROGKEYTAB']);
    }

    protected function leggiScadenzePerPubblicazioni($progkeytabScadenze = null, $stato = null, $page = null) {
        if ($progkeytabScadenze) {
            $filtri['PROGKEYTAB_SCADENZA_IN'] = $progkeytabScadenze;
        }
        if ($stato) {
            $filtri['STATO'] = $stato;
        }
        if ($page === null) {
            $scadenzePerPubbli = $this->getLibDB_BTA()->leggiBgeAgidScadenzePerPubblicazioniMPay($filtri);
        } else {
            $scadenzePerPubbli = $this->getLibDB_BTA()->leggiBgeAgidScadenzePerPubblicazioniMPayBlocchi($filtri, $page, $this->customPaginatorSize());
        }
        return $scadenzePerPubbli;
    }

    protected function customPubblicazioneMassiva($progkeytabInvio, $scadenzePerPubbli, $saltaPubblicazione = false) {
// TODO chiama ws del client di mpay che carica sul nodo
//   $risposta = $this->caricaDelegheFeedZZ($scadenzePerPubbli);

        if ($risposta) {
            return $risposta;
        } else {
            return false;
        }
    }

    protected function customEseguiPagamento($scadenza, $urlReturn, $redirectVerticale = 0) {
        // url da mettere come url di notifica pagamento
//        $url_not = $this->getUrlPagamentoGet($scadenza['CODRIFERIMENTO'], null);       
        $url_not = $this->getUrlPagamento($scadenza['CODRIFERIMENTO'], null);       

        /*
          //SOVRASCRITTO URL NOTIFCA PER TEST
          list($skip, $param_url) = explode('?', $url_not);
          $url_not = "http://demo.nuvolaitalsoft.it/suap/?page_id=794&event=notificaPagamento&" . $param_url;
         * 
         * NOTA: alla pagina 749 (praMPay) all'evento notificaPagamento viene semplicemente fatta la chiamata alla url del modulo PA con i parametri ricevuti da mpay
         * Hack per aggirare il limite dell'ambiete test di MPay che non riesce a fare chiamate in https
         * 
         */

        $url_back = $urlReturn;

//        $parametriMPay = $this->getParametriMPay(); //qui va a leggere i parametri nella tabella BGE_AGID_CONF_MPAY
        $parametriMPay = $this->getCustomConfIntermediario();
        $T = date('YmdHi');

        $soggetto = cwbPagoPaHelper::getSoggettoPagoPa($scadenza['PROGSOGG'], $scadenza['PROGSOGGEX']);

        $infoAggiuntive = $this->getInfoAggiuntive($scadenza['PROGKEYTAB']);

        $CodiceUtente = $parametriMPay['CODUTE'];
        $CodiceEnte = $parametriMPay['CODENTECRED'];
        $TipoUfficio = $infoAggiuntive['TIPOUFFICIO']; //questo valore viene messo dentro i dati aggiuntivi della posizione?
        $CodiceUfficio = $infoAggiuntive['CODICEUFFICIO']; //questo valore viene messo dentro i dati aggiuntivi della posizione?
        $TipologiaServizio = $infoAggiuntive['TIPOLOGIASERVIZIO']; //questo valore viene messo dentro i dati aggiuntivi della posizione?
        //calcolare il NumeroOperazione incrementale, vedere metodo per prenderlo in automatico dalla posizione, His restituisce un intero sempre incrementale per lo stesso giorno
        $NumeroOperazione = time();
        $importo = round($scadenza['IMPDAPAGTO'] * 100, 0);
        $CF = $soggetto['CODFISCALE'] != '' ? $soggetto['CODFISCALE'] : $soggetto['PARTIVA'];

        $BD = '<PaymentRequest>' .
                '<PortaleID>' . $parametriMPay['PORTALEID'] . '</PortaleID>' .
                '<Funzione>PAGAMENTO</Funzione>' .
                '<URLDiRitorno>' . htmlentities($urlReturn, ENT_COMPAT, 'ISO-8859-1') . '</URLDiRitorno>' .
                '<URLDiNotifica>' . htmlentities($url_not, ENT_COMPAT, 'ISO-8859-1') . '</URLDiNotifica>' .
                '<URLBack>' . htmlentities($url_back, ENT_COMPAT, 'ISO-8859-1') . '</URLBack>' .
                '<CommitNotifica>S</CommitNotifica>' . //dovrà essere sempre S
                '<UserData>' .
                '<EmailUtente>' . $soggetto['PEC'] . '</EmailUtente>' .
                '<IdentificativoUtente>' . $CF . '</IdentificativoUtente>' .
                '</UserData>' .
                '<ServiceData>' .
                '<CodiceUtente>' . $CodiceUtente . '</CodiceUtente>' .
                '<CodiceEnte>' . $CodiceEnte . '</CodiceEnte>' .
                '<TipoUfficio>' . $TipoUfficio . '</TipoUfficio>' .
                '<CodiceUfficio>' . $CodiceUfficio . '</CodiceUfficio>' .
                '<TipologiaServizio>' . $TipologiaServizio . '</TipologiaServizio>' .
//                '<NumeroOperazione>' . $NumeroOperazione . '</NumeroOperazione>' .
//                '<NumeroDocumento>' . intval($scadenza['IUV']) . '</NumeroDocumento>' .
                '<NumeroOperazione>' . $NumeroOperazione . '</NumeroOperazione>' .
                '<NumeroDocumento>' . $scadenza['IUV'] . '</NumeroDocumento>' .
                '<AnnoDocumento>' . $scadenza['ANNORIF'] . '</AnnoDocumento>' .
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
//                '<AccountingData>' .
//                '<ImportiContabili>' .
//                '<ImportoContabile>' .
//                '<Identificativo></Identificativo>' .
//                '<Valore></Valore>' .
//                '</ImportoContabile>' .
//                '</ImportiContabili>' .
//                '<EntiDestinatari>' .
//                '<EnteDestinatario>' .
//                '<CodiceEntePortaleEsterno>111111111111</CodiceEntePortaleEsterno>' .
//                '<DescrEntePortaleEsterno>Ente 111111111111</DescrEntePortaleEsterno>' .
//                '<Valore>' . $importo . '</Valore>' .
//                '<Causale><![CDATA[' . $scadenza['DESCRPEND'] . ']]></Causale>' .
//                '<ImportoContabileIngresso></ImportoContabileIngresso>' .
//                '<ImportoContabileUscita></ImportoContabileUscita>' .
//                '<CodiceUtenteBeneficiario>' . $parametriMPay['CODUTEBENEFICIARIO'] . '</CodiceUtenteBeneficiario>' .
//                '<CodiceEnteBeneficiario>' . $parametriMPay['CODENTEBENEFICIARIO'] . '</CodiceEnteBeneficiario>' .
//                '<TipoUfficioBeneficiario>' . $infoAggiuntive['TipoUfficioBeneficiario'] . '</TipoUfficioBeneficiario>' .
//                '<CodiceUfficioBeneficiario>' . $infoAggiuntive['CodiceUfficioBeneficiario'] . '</CodiceUfficioBeneficiario>' .
//                '</EnteDestinatario>' .
//                '</EntiDestinatari>' .
//                '</AccountingData>' .
                '</PaymentRequest>';
        //NOTA: AccountingData non deve essere inserito. Mail di Fabrizio Quaresima del 03.05.2019

        $H = md5($parametriMPay['ENCRYPTIV'] . $BD . $parametriMPay['ENCRYPTKEY'] . $T);

        //preparazione del Bi = $T . $C . base64_encode($BD) . $H;
        $Bi = '<Buffer>' .
                '<TagOrario>' . $T . '</TagOrario>' .
                '<CodicePortale>' . $parametriMPay['PORTALEID'] . '</CodicePortale >' .
                '<BufferDati>' . base64_encode($BD) . '</BufferDati>' .
                '<Hash>' . $H . '</Hash>' .
                '</Buffer>';

        include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

        $headers = array(
            'User-Agent' => $_SERVER['HTTP_USER_AGENT']
        );

        $parametri = array(
            'buffer' => $Bi
        );

        $itaRestClient = new itaRestClient();
        $itaRestClient->setTimeout($this->timeout);
        $itaRestClient->setDebugLevel($this->debug_level);
        $itaRestClient->setCurlopt_useragent($_SERVER['HTTP_USER_AGENT']);

        $UrlMPAY = $parametriMPay['URL_PAGAM_WS'];
        if (!$itaRestClient->get($UrlMPAY, $parametri, $headers)) {
            return $itaRestClient->getErrMessage();
        }

        $rid = $itaRestClient->getResult();
//        $debug = $itaRestClient->getDebug();
        //preparo nuovo Buffer invio
        $H = md5($parametriMPay['ENCRYPTIV'] . $rid . $parametriMPay['ENCRYPTKEY'] . $T);
        $Bi = '<Buffer>' .
                '<TagOrario>' . $T . '</TagOrario>' .
                '<CodicePortale>' . $parametriMPay['PORTALEID'] . '</CodicePortale>' .
                '<BufferDati>' . base64_encode($rid) . '</BufferDati>' .
                '<Hash>' . $H . '</Hash>' .
                '</Buffer>';

        $param = array(
            'buffer' => $Bi
        );
        //ritorno l'url e lo ripasso al portale FO con l'indirizzo completo su cui fare redirect
        $UrlMPAYCart = $parametriMPay['URL_PAGAM_REDIRECT'];
//        $UrlMPAYCart = 'http://payertest.regione.marche.it/mpay/cart/extCart.do';
        return $UrlMPAYCart . "?" . http_build_query($param);
    }

    public function getEsitoPagamento($params) {
        file_put_contents("/tmp/getEsitoPagamento_params", print_r($params, true));
        //chiama il ws di mpay che ritorna il paymentdata e leggere il tag 'esito'
        $this->arrayXmlPaymentData = $this->getArrayPaymentData($params);

        file_put_contents("/tmp/paymentData_array", print_r($this->arrayXmlPaymentData, true));
        if (!$this->arrayXmlPaymentData) {
            $this->setErrMessage("Impossibile convertire XML Payment Data in Array");
            return false;
        }

        $esito = $this->arrayXmlPaymentData['Esito'][0]['@textNode'];

        /*
         * mettere la posizione come pagata facendo return OK o non pagata con return KO
         */
        switch ($esito) {
            case 'OK':
            case 'OP':
                return 'OK';
                break;

            default:
                return 'KO';
                break;
        }
    }

    /**
     * Costruisce XML di risposta per bloccare l'invio di notifiche e lo ritorna
     * 
     * @param type $params
     * @param type $esito
     * @return string XMl di ritorno per CommitNotifica
     */
    protected function customRispostaRendicontazionePuntuale($params, $esito) {

        file_put_contents("/tmp/customRispostaRendicontazionePuntuale", print_r($params, true));
        // TODO COSTRUIRE L'XML DI RISPOSTA PER BLOCCARE L'INVIO DI NOTIFICHE E 
        // RITORNARLO SU QUESTO METODO
        $parametriMPay = $this->getCustomConfIntermediario();

        $NumeroOperazione = $this->arrayXmlPaymentData['NumeroOperazione'][0]['@textNode'];
        $IDOrdine = $this->arrayXmlPaymentData['IDOrdine'][0]['@textNode'];
        $scadenza = $this->getScadenzaPagamento($params);
        if (!$scadenza) {
            $xml = "<CommitMsg>" .
                    "<PortaleID>" . $parametriMPay['PORTALEID'] . "</PortaleID>" .
                    "<NumeroOperazione>" . $NumeroOperazione . "</NumeroOperazione>" .
                    "<IDOrdine>" . $IDOrdine . "</IDOrdine>" .
                    "<Commit>NOK</Commit>" .
                    "</CommitMsg>";
            return $xml;
        }

        /*
         * ESITO
         */
//        $ret_esito = $esito == 'OK' ? 'OK' : 'NOK'; //esito del messaggio di notifica sempre OK? Indipendentemente se il pagamenot ha dato KO?
        $ret_esito = 'OK';
        $xml = "<CommitMsg>" .
                "<PortaleID>" . $parametriMPay['PORTALEID'] . "</PortaleID>" .
                "<NumeroOperazione>" . $NumeroOperazione . "</NumeroOperazione>" .
                "<IDOrdine>" . $IDOrdine . "</IDOrdine>" .
                "<Commit>" . $ret_esito . "</Commit>" .
                "</CommitMsg>";

        return $xml;
    }

    // SE LORO NON SEGANO I NOSTRI PARAMETRI, C'E' CODRIFERIMENTO DI DEFAULT QUINDI NON SERVE SOVRACRIVERE 
//    public function getScadenzaPagamento($params) {
//        file_put_contents("/tmp/getScadenzaPagamento_params", print_r($params, true));
//        // TODO GESTIRE, USARE NUMEROOPERAZIONE ? 
//        $numOperazione = '?';
//        return $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $numOperazione), false);
//    }

    protected function invioPuntualeScadenzaCustom($progkeytabScadenza) {
        if ($progkeytabScadenza) {
            $row_Agid_scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenzeChiave($progkeytabScadenza);
            if ($row_Agid_scadenze) {
                $this->rispostaPubblicazione($risposta, $row_Agid_scadenze, true);
            } else {
                $this->rispostaPubblicazione($risposta, null, false, "Scadenza non trovata");
            }
        } else {
            $this->rispostaPubblicazione($risposta, null, false, "Scadenza non trovata");
        }

        return $risposta;
    }

    protected function getEmissioniPerPubblicazione($filtri = array()) {
        $filtri['INTERMEDIARIO'] = itaPagoPa::MPAY_TYPE;
        return $this->getLibDB_BGE()->leggiEmissioniDaPubblicare($filtri);
    }

    protected function customRendicontazione($params) {
        // gestisce la lettura del txt di mpay e reperire i dati da li
        $msgError = '';
        $risultato = true;
        $confMpay = $this->getCustomConfIntermediario();

        // todo se la connessione sarà cambiata in sFTP, va scommentato 
        //  $this->certificatoMpay($certPath, $confMpay['SFTPFILEKEY']);
        // Select per capire se ho gia trattato altre rendicontazioni (se ne trovo qualcuna, le andrï¿½ a togliere dalla dir list
        // della cartella 'Rendicontazioni' dell'sFTP)
        // La select mi ritorna le rendicontazioni GIA' elaborate.
        $listElaborate = $this->getLibDB_BGE()->leggiBgeAgidAllegatiRend(array("TIPO" => 15));
        $filtri['INTERMEDIARIO'] = itaPagoPa::MPAY_TYPE;

        //$this->settaParametriSftp($sftp, $confMpay, $certPath);
        $host = $confMpay['SFTPHOST'];
        $user = $confMpay['SFTPUSER'];
        $password = $confMpay['SFTPPASSWORD'];

        // connetti ftp        
        $ftp_conn = itaFtpUtils::openFtpConnection($host, $user, $password);
        if (!$ftp_conn) {
            $this->handleError(-1, "Impossibile connettersi con l'utente FTP $user all'host $host.");
            return false;
        }
        // $esitoListFile = $sftp->listOfFiles('/');
        $listOfFiles = itaFtpUtils::getFilesList($ftp_conn, '/');
        if ($listOfFiles) {

            //$listOfFiles = $sftp->getResult();

            foreach ($listOfFiles as $key => $value) {
                // tolgo il carattere '/' all'inizio dei nomi file
                if (substr($value, 0, 1) == '/') {
                    $value = ltrim($value, '/');
                    $listOfFiles[$key] = $value;
                }
                $initNomeFile = substr($value, 0, 3);
                if (!in_array($initNomeFile, $this->tipiTracciato)) {
                    // elimino l'elemento dall'array se i primi 3 caratteri non sono "noti"
                    unset($listOfFiles[$key]);
                }
            }
            // Vado ad eliminare dall'array di list dir dell'sFTP, tutte le ricevute che mi ha restituito la select precedente.
            // Essendo sicuro che il risultato della query indica le RENDICONTAZIONI giï¿½ elaborate,
            // confronto il nome e se lo trovo lo elimino.
            if ($listOfFiles) {
                foreach ($listOfFiles as $key => $list) {
                    foreach ($listElaborate as $elaborato) {
                        if (trim($list) == trim($elaborato['NOME_FILE'])) {
                            // elimino l'elemento dall'array
                            unset($listOfFiles[$key]);
                        }
                    }
                }
            }
        } else {
            $listOfFiles = array();
        }
        if ($listOfFiles) {
            foreach ($listOfFiles as $value) {
                $errore = false;
                cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                try {
                    // if ($sftp->downloadFile("/" . trim($value))) {
                    $zipFile = itaFtpUtils::getBinaryFileFromFtp($ftp_conn, $value);
                    if ($zipFile) {
                        $pathZip = itaLib::getUploadPath() . "/zipTemp" . time() . '.zip';
                        file_put_contents($pathZip, $zipFile);

                        try {
                            itaZipCommandLine::unzip($pathZip);
                        } catch (ItaException $e) {
                            $errore = true;
                        }

                        //$zipFile = $sftp->getResult();
                        //   if ($sftp->downloadFile("/" . $value)) {
                        $result = file_get_contents(itaLib::getUploadPath() . "/" . trim(str_replace(".zip", ".txt", $value)));
                        $arrayTxt = explode("\n", $result);

                        // elimino .txt da disco
                        unlink(itaLib::getUploadPath() . "/" . trim(str_replace(".zip", ".txt", $value)));
                        // Salvataggio record su BGE_AGID_RICEZ
                        // faccio explode, per reperire la data di creazione dal nomeFile
                        $expNomeFile = explode(".", $value);
                        $tipoTracciato = substr($expNomeFile[0], 0, 3);
                        $dataFile = trim(substr($expNomeFile[0], 18, 8));
                        $ricezione = array(
                            "TIPO" => 15,
                            "INTERMEDIARIO" => itaPagoPa::MPAY_TYPE,
                            "CODSERVIZIO" => 0,
                            "DATARIC" => $dataFile,
                            "IDINV" => null,
                        );
                        $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                        // Costruisco array per salvataggio allegato
                        $allegati = array(
                            'TIPO' => 15,
                            'IDINVRIC' => $progkeytabRicez,
                            'DATA' => $dataFile,
                            'NOME_FILE' => $value,
                        );

                        //INSERT su BGE_AGID_ALLEGATI
                        $allegati['ZIPFILE'] = $zipFile;
                        $this->insertBgeAgidAllegati($allegati);

                        // elimino file zip da disco
                        unlink($pathZip);

                        unlink(str_replace(".zip", ".txt", $value));

                        // Facendo l'explode di "\n", mi rimane un elemento vuoto in fondo nell'array... lo vado ad eliminare.
                        // Oltre questo elimino anche l'ultimo record della rendicontazione perchè è il record riepilogativo dell'intero file. Non mi interessa quindi lo tolgo
                        unset($arrayTxt[count($arrayTxt) - 1]);
                        unset($arrayTxt[count($arrayTxt) - 1]);
                        foreach ($arrayTxt as $key => $value) {
                            $imppagato = trim(substr($value, 36, 10)) / 100;
                            $filtri = array();
                            if ($tipoTracciato === 'IVC') {

                                if (strlen(trim(substr($value, 94, 6))) == 6 && trim(substr($value, 94, 6)) > 0) {
                                    $filtri['DATAVERBALE'] = trim(substr($value, 94, 6));
                                } else {
                                    $filtri['DATAVERBALE'] = null;
                                }
                                $filtri['TARGAVEICOLO'] = trim(substr($value, 100, 10));
                                $filtri['IDENTIFICATIVOVERBALE'] = trim(substr($value, 161, 15));
                                $scadenza = $this->trovaScadenzaPerIVC($filtri, $imppagato);
                            } elseif ($tipoTracciato === 'IVP') {
                                $iuv = trim(substr($value, 151, 20)); // Numero Documento
                                $filtri['IUV'] = $iuv;
                                $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                            }

                            if ($scadenza) {
                                $risco = array();
                                $toUpdate = array();
                                $filtri = array();
                                $risco_mpay = array();
                                // Inserisco Riscossione BGE_AGID_RISCO
                                $progintric = trim(substr($value, 8, 7));

                                if (strlen(trim(substr($value, 27, 6))) == 6 && trim(substr($value, 27, 6)) > 0) {
                                    // DATA ACCETTAZIONE??? per ora prendo quella
                                    $datapag = trim(substr($value, 27, 6));
                                } else {
                                    $datapag = null;
                                }
                                // Inserimento su BGE_AGID_RISCO
                                $riscossione = array(
                                    "IDSCADENZA" => $scadenza['PROGKEYTAB'],
                                    "PROGRIC" => $progkeytabRicez,
                                    "PROGINTRIC" => $progintric,
                                    "IUV" => $scadenza['IUV'],
                                    "PROVPAGAM" => 2,
                                    "IMPPAGATO" => $imppagato,
                                    "DATAPAG" => $datapag,
                                );

                                $progkeytabRisco = $this->insertBgeAgidRisco($riscossione);

                                if ($scadenza) {
                                    $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                                    $toUpdate['STATO'] = 10;
                                    // DATA ACCETTAZIONE??? per adesso prendo questa
                                    $toUpdate['DATAPAGAM'] = $datapag;
                                    $this->aggiornaBgeScadenze($toUpdate);
                                }
                                // Inserisco Riscossione Mpay  BGE_AGID_RISCO_MPAY
                                $risco_mpay['PROGKEYTAB'] = $progkeytabRisco;
                                $risco_mpay['NUM_BENEFICIARIO'] = trim(substr($value, 15, 12));

                                if ($tipoTracciato === 'IVC') {
                                    $risco_mpay['CODFISCALE'] = trim(substr($value, 77, 16));
                                    $risco_mpay['INTESTATARIO'] = trim(substr($value, 110, 46));
                                    $risco_mpay['TIPO_VERBALE'] = trim(substr($value, 93, 1));
                                    if (strlen(trim(substr($value, 94, 6))) == 6 && trim(substr($value, 94, 6)) > 0) {
                                        $risco_mpay['DATA_VERBALE'] = trim(substr($value, 94, 6));
                                    } else {
                                        $risco_mpay['DATA_VERBALE'] = null;
                                    }
                                    $risco_mpay['TARGA'] = trim(substr($value, 100, 10));
                                    $risco_mpay['NUM_VERBALE'] = trim(substr($value, 161, 15));
                                    $risco_mpay['COD_ENTE'] = trim(substr($value, 156, 5));
                                } elseif ($tipoTracciato === 'IVP') {
                                    $risco_mpay['CODFISCALE'] = trim(substr($value, 173, 16));
                                    $risco_mpay['INTESTATARIO'] = trim(substr($value, 77, 46));
                                    $risco_mpay['COD_ENTE'] = trim(substr($value, 123, 5));
                                    $risco_mpay['INDIRIZZO'] = trim(substr($value, 128, 23));
                                    $risco_mpay['NUMRATA'] = trim(substr($value, 171, 2));
                                }

                                //campi in comune IVP e IVC
                                $risco_mpay['TIPO_DOCUMENTO'] = trim(substr($value, 33, 3));
                                $risco_mpay['DIVISA'] = trim(substr($value, 54, 1));
                                $risco_mpay['COD_CLIENTE'] = trim(substr($value, 61, 77));
                                $risco_mpay['CHIAVE_SPED_PAG'] = trim(substr($value, 189, 11));

                                // DATAVERS è DATA ACCETTAZIONE
                                // DATAREGO è DATA CONTABILE
                                // DATAPAGAM è DATA ACCETTEANZIONE
                                if (strlen(trim(substr($value, 55, 6))) == 6 && trim(substr($value, 55, 6)) > 0) {
                                    $dataContabile = trim(substr($value, 55, 6));
                                } else {
                                    $dataContabile = null;
                                }
                                $risco_mpay['DATAREG'] = $datapag;
                                $risco_mpay['DATAREGO'] = $dataContabile;
                                $risco_mpay['DATAVERS'] = $datapag;

                                $this->inserisciBgeAgidRiscoMpay($risco_mpay);
                            } else {
                                $errore = true;
                                $risultato = false;
                                $msgError .= "Errore Reperimento Scadenza!";
                            }
                        }
                    } else {
                        // TODO Errore lettura da sftp   
                        $errore = true;
                        $risultato = false;
                        $msgError .= "Errore: Download File " . $value;
                    }
                } catch (Exception $ex) {
                    $errore = true;
                    $msgError .= "Errore:" . $exc->getMessage();
                }
                if ($errore) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                    unlink($nomeAllegato);
                    if ($this->getSimulazione() != true) {
                        $log = array(
                            "LIVELLO" => 3,
                            "OPERAZIONE" => 15,
                            "ESITO" => 3,
                            "KEYOPER" => 0,
                        );
                        $this->scriviLog($log);
                    }
                    $risultato = false;
                } else {
                    cwbDBRequest::getInstance()->commitManualTransaction();
                }
            }
        } else {
            // Se non trovo nessuna ricevuta, devo cmq salvare il LOG con LIVELLO = 5, ESITO = 2.
            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 15,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        // unlink($certPath);
        if (!$risultato) {
            $this->handleError(-1, $msgError);
        }
        return $risultato;
    }

    private function trovaScadenzaPerIVC($filtri, $imppagato) {

        // C'è la possibilità di avere più di una scadenza per IVC... devo capire quale prendere per poi rendicontarla.
        // Prima verifico se l'importo rendicontato e l'importo da pagare della scadenza coincidono... se coincidono prendo subito la scadenza, altrimenti prendo quella con 
        // importo più basso.
        $scadenze = $this->leggiScadenzaPerInfoAggiuntive($filtri, true);
        $trovata = false;
        $scadenzaToReturn = array();
        if ($scadenze) {
            foreach ($scadenze as $key => $value) {
                // confronto l'importo della scadenza con quella del flusso di rend. Se sono uguali, torno la scadenza
                if ($value['IMPDAPAGTO'] == $imppagato) {
                    $trovata = true;
                    $scadenzaToReturn = $value;
                    break;
                }
            }
            if (!$trovata) {
                // torno la scadenza con importo più basso
                $numbers = array_column($scadenze, 'IMPDAPAGTO');
                $minimo = min($numbers);
                foreach ($scadenze as $key => $value) {
                    if ($value['IMPDAPAGTO'] == $minimo) {
                        $scadenzaToReturn = $value;
                        break;
                    }
                }
            }
        } else {
            return false;
        }
        return $scadenzaToReturn;
    }

    private function settaParametriSftp(&$ftp, $confMpay, $certPath) {
        $sftp = new itaSFtpUtils();
        // setto i parametri sftp
        $sftp->setParameters($confMpay['SFTPHOST'], $confMpay['SFTPUSER'], '', $certPath, $confMpay['SFTPPASSWORD']);
    }

    private function certificatoMpay(&$certPath, $sftpfilekey, $password, $devLib) {
        // il certificato va appoggiato su disco poi lo cancello una volta finito
        $certPath = itaLib::getUploadPath() . "/cert" . time() . '.pem';
        rewind($sftpfilekey);
        file_put_contents($certPath, stream_get_contents($sftpfilekey));
    }

    protected function leggiScadenzePerInserimento($filtri = array(), $page = null) {
        $filtri['INTERMEDIARIO'] = itaPagoPa::MPAY_TYPE;
        $filtri['FLAG_PUBBL_IN'] = array(3, 4, 5);
        if ($page === null) {
            $scadenze = $this->getLibDB_BWE()->leggiBwePendenScadenze($filtri);
        } else {
            $scadenze = $this->getLibDB_BWE()->leggiBwePendenScadenzeBlocchi($filtri, $page, $this->customPaginatorSize());
        }

        return $scadenze;
    }

    public function aggiornaBgeAgidRiscoMpay($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_RISCO_MPAY', false, $startedTransaction);
    }

    public function inserisciBgeAgidRiscoMpay($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_RISCO_MPAY', true, $startedTransaction);
    }

    private function generaBollettinoRataUnica($agidScadenza) {
        if (!$agidScadenza) {
            $this->setErrMessage("Scadenza non definita");
            return false;
        }
        $soggetto = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza['PROGSOGG'], $agidScadenza['PROGSOGGEX']);
        if (!$soggetto) {
            $this->setErrMessage("Soggetto non definito");
            return false;
        }
        //parametrizzazioni
        $parametriMPay = $this->getCustomConfIntermediario();
        $GLN = $parametriMPay['GLN_ENTE'];
        $aux_digit = $parametriMPay['AUXDIGIT'];
        $NumeroAvviso = $aux_digit . $agidScadenza['IUV'];
        $CFEnte = cwbParGen::getPIVAEnte();
        $LOGO = 'enti/ente' . App::$utente->getKey('ditta') . '/logo_' . App::$utente->getKey('ditta') . '.jpg';
        //parametri conto
        $parametriConto_arr = $this->getLibDB_BWE()->leggiBweTippenPPA(array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD']));
        $parametriConto = $parametriConto_arr[0];
        //QRCode
        $imp = (int) round($agidScadenza['IMPDAPAGTO'] * 100, 0);
        $QRCode = "PAGOPA|002|" . $NumeroAvviso . "|" . $CFEnte . "|" . ($imp);
        //DATAMATRIX
        $indirizzamento = 'codfase=';
        $cod_accettazione = 'NBPA';
        $separatore = ';';
        $impo = explode(".", $agidScadenza['IMPDAPAGTO']);
        $Denominazione = trim($soggetto['COGNOME'] . " " . $soggetto['NOME']);
        $Code128 = "18" . $NumeroAvviso .
                "12" . str_pad($parametriConto['CONTO_CORRENTE'], 12, "0", STR_PAD_LEFT) .
                "10" . str_pad($impo['0'], 8, '0', STR_PAD_LEFT) . $impo['1'] .
                "3" . "896";
        $Datamatrix = $indirizzamento .
                $cod_accettazione .
                $separatore .
                $Code128 .
                "1" .
                "P1" .
                str_pad($CFEnte, 11, "0", STR_PAD_LEFT) .
                str_pad($soggetto['CODFISCALE'], 16, "0", STR_PAD_LEFT) .
                str_pad($Denominazione, 40, " ", STR_PAD_RIGHT) .
                str_pad($agidScadenza['DESCRPEND'], 110, " ", STR_PAD_RIGHT) .
                str_pad("", 12, " ", STR_PAD_RIGHT) .
                "A";

        //preparo array di parametri da passare al report
        $params = array(
            "Ente" => cwbParGen::getDesente(),
            "daPagina" => 1,
            "IUV" => $aux_digit . $agidScadenza['IUV'],
            "ImportoTot" => number_format($agidScadenza['IMPDAPAGTO'], 2, ",", "."),
            "SCADENZA" => substr($agidScadenza['DATASCADE'], 8, 2) . "/" . substr($agidScadenza['DATASCADE'], 5, 2) . "/" . substr($agidScadenza['DATASCADE'], 0, 4),
            "CFENTE" => $CFEnte,
            "NCCP" => $parametriConto['CONTO_CORRENTE'],
            "QRCODE" => $QRCode,
            "BARCODE" => '', // non più usato
            "Anno" => $agidScadenza['ANNORIF'],
            "LOGO" => $LOGO,
            "CODICE" => $soggetto['PROGSOGG'],
            "DENOMINAZIONE" => $Denominazione,
            "CODICEFISCALE" => $soggetto['CODFISCALE'],
            "COMUNE" => $soggetto['COMUNERESID'],
            "INDIRIZZO" => $soggetto['INDIRIZZORESID'], //INDIRIZZORESID contiene il civico
            "CIVICO" => '',
            "CAP" => $soggetto['CAPRESID'],
            "PROVINCIA" => $soggetto['PROVINCIARESID'],
            "PIVA" => $soggetto['PARTIVA'],
            "CAUSALE" => $agidScadenza['DESCRPEND'],
            "DATAMATRIX" => $Datamatrix,
            "NRATE" => 0,
            "AUTORIZZAZIONE" => $parametriConto['AUTORIZZAZIONE'], //@TODO: parametro da aggiungere in tabella
            "CBILL" => $parametriConto['CBILL'],
            "INTESTATO" => $parametriConto['CCINFO']
        );


        //creo il task per la generazione della lettera PA
        $subPath = itaLib::getAppsTempPath('STAMPEPA_' . time());
        if (!is_dir($subPath)) {
            $subPath = itaLib::createAppsTempPath('STAMPEPA_' . time());
        }
        if (!$subPath) {
            $this->setErrMessage("Errore nella creazione della path temporanea");
            return false;
        }
        $OUTPUT_FILE = $subPath . DIRECTORY_SEPARATOR . uniqid('AvvisoPA_') . time() . ".pdf";
        $xmlJrDefPAPath = $subPath . "/xmljrdefPA.xml";
        $xjhPA = fopen($xmlJrDefPAPath, 'w');
        if ($xjhPA === false) {
            $this->setErrMessage('Errore nella apertura del File.');
            return false;
        }
        if (fwrite($xjhPA, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n") === false) {
            Out::msgStop('Errore', 'Errore nella scrittura file: Testata xml.');
            return false;
        }
        if (fwrite($xjhPA, "<root>\n") === false) {
            $this->setErrMessage('Errore nella scrittura file: <root>');
            return false;
        }

        $nomeReport = 'PAGOPA_AVVISO';
        $path_file = ITA_BASE_PATH . "/reports/PagoPA/enti/ente" . App::$utente->getKey('ditta') . "/" . $nomeReport . "_" . App::$utente->getKey('ditta') . ".jrxml";
        if (!is_file($path_file)) {
            $path_file = ITA_BASE_PATH . "/reports/PagoPA/" . $nomeReport . ".jrxml"; //se il file non c'è
        }
        $PA_jrdef_xml = "<jrDefinition>\n";
        $PA_jrdef_xml .= "<ReportFile>" . $path_file . "</ReportFile>\n";
        $PA_jrdef_xml .= "<OutputFile>" . $OUTPUT_FILE . "</OutputFile>\n";
        $PA_jrdef_xml .= "<DataSource class=\"JREmptyDataSource\" count=\"1\"></DataSource>\n";
        foreach ($params as $key => $value) {
            $PA_jrdef_xml .= "<Parameter name=\"" . $key . "\" class=\"String\"><![CDATA[" . $value . "]]></Parameter>\n";
        }
        $PA_jrdef_xml .= "</jrDefinition>\n";
        if (fwrite($xjhPA, $PA_jrdef_xml) === false) {
            $this->setErrMessage('Errore nella scrittura file: </root>');
            return false;
        }
        /*
         * GENERAZIONE STAMPE PA
         */
        if (fwrite($xjhPA, "</root>") === false) {
            $this->setErrMessage('Errore nella scrittura file: </root>');
            return false;
        }
        if (fclose($xjhPA) === false) {
            $this->setErrMessage('Errore nella chiusura del file.');
            return false;
        }
        $stdOutDest = '';
        $commandJrPA = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJRGenerator/ItaJrGenerator.jar ' . $xmlJrDefPAPath . $stdOutDest;
        exec($commandJrPA, $outJrPA, $retJrPA);
        if ($retJrPA != '0') {
            $this->setErrMessage("Errore in Generazione AG <br><br> ($jrErrPA) - " . print_r($outJrPA, true) . " - $retJrPA<br><br>" . $commandJrPA);
            return false;
        }
        return $OUTPUT_FILE;
    }

    private function generaBollettinoRate($agidScadenza1, $agidScadenza2, $agidScadenza3 = array()) {
        if (!$agidScadenza1) {
            $this->setErrMessage("Scadenza non definita");
            return false;
        }
        $soggetto1 = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza1['PROGSOGG'], $agidScadenza1['PROGSOGGEX']);
        if (!$soggetto1) {
            $this->setErrMessage("Soggetto non definito");
            return false;
        }
        $soggetto2 = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza2['PROGSOGG'], $agidScadenza2['PROGSOGGEX']);
        if (!$soggetto2) {
            $this->setErrMessage("Soggetto non definito");
            return false;
        }
        if ($agidScadenza3) {
            $soggetto3 = cwbPagoPaHelper::getSoggettoPagoPa($agidScadenza3['PROGSOGG'], $agidScadenza3['PROGSOGGEX']);
            if (!$soggetto3) {
                $this->setErrMessage("Soggetto non definito");
                return false;
            }
        }
        //parametrizzazioni
        $parametriMPay = $this->getCustomConfIntermediario();
        $GLN = $parametriMPay['GLN_ENTE'];
        $aux_digit = $parametriMPay['AUXDIGIT'];
        $CFEnte = cwbParGen::getPIVAEnte();
        $LOGO = 'enti/ente' . App::$utente->getKey('ditta') . '/logo_' . App::$utente->getKey('ditta') . '.jpg';
        //parametri conto
        $parametriConto_arr = $this->getLibDB_BWE()->leggiBweTippenPPA(array('CODTIPSCAD' => $agidScadenza['CODTIPSCAD'], 'SUBTIPSCAD' => $agidScadenza['SUBTIPSCAD']));
        $parametriConto = $parametriConto_arr[0];
        /*
         * PARAMETRI RATA 1
         */
        //QRCode
        $imp1 = (int) round($agidScadenza1['IMPDAPAGTO'] * 100, 0);
        $QRCode1 = "PAGOPA|002|" . $aux_digit . $agidScadenza1['IUV'] . "|" . $CFEnte . "|" . ($imp1);
        //DATAMATRIX
        $indirizzamento = 'codfase=';
        $cod_accettazione = 'NBPA';
        $separatore = ';';
        $impo1 = explode(".", $agidScadenza1['IMPDAPAGTO']);
        $Denominazione1 = trim($soggetto1['COGNOME'] . " " . $soggetto1['NOME']);
        $Code128_1 = "18" . $aux_digit . $agidScadenza1['IUV'] .
                "12" . str_pad($parametriConto['CONTO_CORRENTE'], 12, "0", STR_PAD_LEFT) .
                "10" . str_pad($impo1['0'], 8, '0', STR_PAD_LEFT) . $impo1['1'] .
                "3" . "896";
        $Datamatrix1 = $indirizzamento .
                $cod_accettazione .
                $separatore .
                $Code128_1 .
                "1" .
                "P1" .
                str_pad($CFEnte, 11, "0", STR_PAD_LEFT) .
                str_pad($soggetto1['CODFISCALE'], 16, "0", STR_PAD_LEFT) .
                str_pad($Denominazione1, 40, " ", STR_PAD_RIGHT) .
                str_pad($agidScadenza1['DESCRPEND'], 110, " ", STR_PAD_RIGHT) .
                str_pad("", 12, " ", STR_PAD_RIGHT) .
                "A";
        /*
         * PARAMETRI RATA 2
         */
        //QRCode
        $imp2 = (int) round($agidScadenza2['IMPDAPAGTO'] * 100, 0);
        $QRCode2 = "PAGOPA|002|" . $aux_digit . $agidScadenza2['IUV'] . "|" . $CFEnte . "|" . ($imp2);
        //DATAMATRIX
        $indirizzamento = 'codfase=';
        $cod_accettazione = 'NBPA';
        $separatore = ';';
        $impo2 = explode(".", $agidScadenza2['IMPDAPAGTO']);
        $Denominazione2 = trim($soggetto2['COGNOME'] . " " . $soggetto2['NOME']);
        $Code128_2 = "18" . $aux_digit . $agidScadenza2['IUV'] .
                "12" . str_pad($parametriConto['CONTO_CORRENTE'], 12, "0", STR_PAD_LEFT) .
                "10" . str_pad($impo2['0'], 8, '0', STR_PAD_LEFT) . $impo2['1'] .
                "3" . "896";
        $Datamatrix2 = $indirizzamento .
                $cod_accettazione .
                $separatore .
                $Code128_2 .
                "1" .
                "P1" .
                str_pad($CFEnte, 11, "0", STR_PAD_LEFT) .
                str_pad($soggetto2['CODFISCALE'], 16, "0", STR_PAD_LEFT) .
                str_pad($Denominazione2, 40, " ", STR_PAD_RIGHT) .
                str_pad($agidScadenza2['DESCRPEND'], 110, " ", STR_PAD_RIGHT) .
                str_pad("", 12, " ", STR_PAD_RIGHT) .
                "A";
        /*
         * PARAMETRI RATA 3
         */
        if ($agidScadenza3) {
            //QRCode
            $imp3 = (int) round($agidScadenza3['IMPDAPAGTO'] * 100, 0);
            $QRCode3 = "PAGOPA|002|" . $aux_digit . $agidScadenza3['IUV'] . "|" . $CFEnte . "|" . ($imp3);
            //DATAMATRIX
            $indirizzamento = 'codfase=';
            $cod_accettazione = 'NBPA';
            $separatore = ';';
            $impo3 = explode(".", $agidScadenza3['IMPDAPAGTO']);
            $Denominazione3 = trim($soggetto3['COGNOME'] . " " . $soggetto3['NOME']);
            $Code128_3 = "18" . $aux_digit . $agidScadenza3['IUV'] .
                    "12" . str_pad($parametriConto['CONTO_CORRENTE'], 12, "0", STR_PAD_LEFT) .
                    "10" . str_pad($impo3['0'], 8, '0', STR_PAD_LEFT) . $impo3['1'] .
                    "3" . "896";
            $Datamatrix3 = $indirizzamento .
                    $cod_accettazione .
                    $separatore .
                    $Code128_3 .
                    "1" .
                    "P1" .
                    str_pad($CFEnte, 11, "0", STR_PAD_LEFT) .
                    str_pad($soggetto3['CODFISCALE'], 16, "0", STR_PAD_LEFT) .
                    str_pad($Denominazione3, 40, " ", STR_PAD_RIGHT) .
                    str_pad($agidScadenza3['DESCRPEND'], 110, " ", STR_PAD_RIGHT) .
                    str_pad("", 12, " ", STR_PAD_RIGHT) .
                    "A";
        }

        //preparo array di parametri da passare al report
        $params = array(
            "Ente" => cwbParGen::getDesente(),
            "daPagina" => 1,
            "IUV1" => $aux_digit . $agidScadenza1['IUV'],
            "Importo1" => number_format($agidScadenza1['IMPDAPAGTO'], 2, ",", "."),
            "Scadenza1" => substr($agidScadenza1['DATASCADE'], 8, 2) . "/" . substr($agidScadenza1['DATASCADE'], 5, 2) . "/" . substr($agidScadenza1['DATASCADE'], 0, 4),
            "QRcode1" => $QRCode1,
            "IUV2" => $aux_digit . $agidScadenza2['IUV'],
            "Importo2" => number_format($agidScadenza2['IMPDAPAGTO'], 2, ",", "."),
            "Scadenza2" => substr($agidScadenza2['DATASCADE'], 8, 2) . "/" . substr($agidScadenza2['DATASCADE'], 5, 2) . "/" . substr($agidScadenza2['DATASCADE'], 0, 4),
            "QRcode2" => $QRCode2,
            "IUV3" => $aux_digit . $agidScadenza3['IUV'],
            "Importo3" => number_format($agidScadenza3['IMPDAPAGTO'], 2, ",", "."),
            "Scadenza3" => substr($agidScadenza3['DATASCADE'], 8, 2) . "/" . substr($agidScadenza3['DATASCADE'], 5, 2) . "/" . substr($agidScadenza3['DATASCADE'], 0, 4),
            "QRcode3" => $QRCode3,
            "Anno" => $agidScadenza1['ANNORIF'],
            "LOGO" => $LOGO,
            "CODICE" => $soggetto1['PROGSOGG'],
            "DENOMINAZIONE" => $Denominazione1,
            "CODICEFISCALE" => $soggetto1['CODFISCALE'],
            "Causale1" => $agidScadenza1['DESCRPEND'],
            "Causale2" => $agidScadenza2['DESCRPEND'],
            "Causale3" => $agidScadenza3['DESCRPEND'],
            "CFENTE" => $CFEnte,
            "INTESTATO" => $parametriConto['CCINFO'],
            "NCCP" => $parametriConto['CONTO_CORRENTE'],
            "AUTORIZZAZIONE" => $parametriConto['AUTORIZZAZIONE'],
            "Datamatrix1" => $Datamatrix1,
            "Datamatrix2" => $Datamatrix2,
            "Datamatrix3" => $Datamatrix3,
            "CBILL" => $parametriConto['CBILL'],
            "RataI" => 1,
            "RataII" => 2,
            "RataIII" => 3,
        );



        //creo il task per la generazione della lettera PA
        $subPath = itaLib::getAppsTempPath('STAMPEPA_' . time());
        if (!is_dir($subPath)) {
            $subPath = itaLib::createAppsTempPath('STAMPEPA_' . time());
        }
        if (!$subPath) {
            $this->setErrMessage("Errore nella creazione della path temporanea");
            return false;
        }
        $OUTPUT_FILE = $subPath . DIRECTORY_SEPARATOR . uniqid('AvvisoPA_') . time() . ".pdf";
        $xmlJrDefPAPath = $subPath . "/xmljrdefPA.xml";
        $xjhPA = fopen($xmlJrDefPAPath, 'w');
        if ($xjhPA === false) {
            $this->setErrMessage('Errore nella apertura del File.');
            return false;
        }
        if (fwrite($xjhPA, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n") === false) {
            Out::msgStop('Errore', 'Errore nella scrittura file: Testata xml.');
            return false;
        }
        if (fwrite($xjhPA, "<root>\n") === false) {
            $this->setErrMessage('Errore nella scrittura file: <root>');
            return false;
        }

        if ($agidScadenza3) {
            $nomeReport = 'PAGOPA3Rate_AVVISO';
        } else {
            $nomeReport = 'PAGOPA2Rate_AVVISO';
        }

        $path_file = ITA_BASE_PATH . "/reports/PagoPA/enti/ente" . App::$utente->getKey('ditta') . "/" . $nomeReport . "_" . App::$utente->getKey('ditta') . ".jrxml";
        if (!is_file($path_file)) {
            $path_file = ITA_BASE_PATH . "/reports/PagoPA/" . $nomeReport . ".jrxml"; //se il file non c'è
        }
        $PA_jrdef_xml = "<jrDefinition>\n";
        $PA_jrdef_xml .= "<ReportFile>" . $path_file . "</ReportFile>\n";
        $PA_jrdef_xml .= "<OutputFile>" . $OUTPUT_FILE . "</OutputFile>\n";
        $PA_jrdef_xml .= "<DataSource class=\"JREmptyDataSource\" count=\"1\"></DataSource>\n";
        foreach ($params as $key => $value) {
            $PA_jrdef_xml .= "<Parameter name=\"" . $key . "\" class=\"String\"><![CDATA[" . $value . "]]></Parameter>\n";
        }
        $PA_jrdef_xml .= "</jrDefinition>\n";
        if (fwrite($xjhPA, $PA_jrdef_xml) === false) {
            $this->setErrMessage('Errore nella scrittura file: </root>');
            return false;
        }
        /*
         * GENERAZIONE STAMPE PA
         */
        if (fwrite($xjhPA, "</root>") === false) {
            $this->setErrMessage('Errore nella scrittura file: </root>');
            return false;
        }
        if (fclose($xjhPA) === false) {
            $this->setErrMessage('Errore nella chiusura del file.');
            return false;
        }
        $stdOutDest = '';
        $commandJrPA = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJRGenerator/ItaJrGenerator.jar ' . $xmlJrDefPAPath . $stdOutDest;
        exec($commandJrPA, $outJrPA, $retJrPA);
        if ($retJrPA != '0') {
            $this->setErrMessage("Errore in Generazione AG <br><br> ($jrErrPA) - " . print_r($outJrPA, true) . " - $retJrPA<br><br>" . $commandJrPA);
            return false;
        }
        return $OUTPUT_FILE;
    }

    /**
     * 
     * @param type $pdf_arr array di pdf con path complete
     * @return base 64 del file definitivo
     */
    private function concatenaPDF($pdf_arr) {
        if (!is_array($pdf_arr)) {
            $this->setErrMessage("Concatenazione impossibile: oggetto non array di PDF");
            return false;
        }
        $dir = pathinfo($pdf_arr[0], PATHINFO_DIRNAME);
        if (!$dir) {
            $this->setErrMessage("Directory temporanea mancante");
            return false;
        }
        $OUTPUT_FILE = $dir . DIRECTORY_SEPARATOR . uniqid('CompletoPA_') . time() . ".pdf";
        //task di concatenazione dei bollettini
        $xmlTaskCatPath = $dir . "/xmlCtask_" . uniqid('TaskPA_') . time() . ".xml";
        $xthC = fopen($xmlTaskCatPath, 'w');
        if ($xthC === false) {
            $this->setErrMessage('Errore nella apertura del File TASK CONCAT.');
            return false;
        }
        fwrite($xthC, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
        fwrite($xthC, "<root>\n");
        fwrite($xthC, "<task name=\"cat\">\n");
        fwrite($xthC, "<inputs>\n");
        foreach ($pdf_arr as $pdf_file) {
            fwrite($xthC, "<input delete=\"1\" >{$pdf_file}</input>\n");
        }
        fwrite($xthC, "</inputs>\n");
        fwrite($xthC, "<output>$OUTPUT_FILE</output>\n");
        fwrite($xthC, "</task>\n");
        fwrite($xthC, "</root>\n");
        $command = App::getConf("Java.JVMPath") . " -jar " . App::getConf('modelBackEnd.php') . '/lib/itaJava/itaJPDF2/itaJPDF.jar ' . $xmlTaskCatPath . $stdOutDest;
        exec($command, $out, $ret);
        if ($ret != '0') {
            $this->setErrMessage("Errore in Composizione PDF <br><br><br>Out: $out<br><br>" . $command);
            return false;
        }
        if (!is_file($OUTPUT_FILE)) {
            $this->setErrMessage("Errore nella concatenazione dei pdf");
            return false;
        }
        $content = file_get_contents($OUTPUT_FILE);
        @unlink($xmlTaskCatPath);
        @unlink($OUTPUT_FILE);
        return $content;
    }

    private function getArrayPaymentData($params) {
        $pid = $params['buffer'];
        $T = date('YmdHi');
        $parametriMPay = $this->getCustomConfIntermediario();

        /*
         * ricevuto il PID si fa la chiamata per reperire il PaymentData
         */
        $H = md5($parametriMPay['ENCRYPTIV'] . $pid . $parametriMPay['ENCRYPTKEY'] . $T);

        $Bi = '<Buffer>' .
                '<TagOrario>' . $T . '</TagOrario>' .
                '<CodicePortale>' . $parametriMPay['PORTALEID'] . '</CodicePortale >' .
                '<BufferDati>' . base64_encode($pid) . '</BufferDati>' .
                '<Hash>' . $H . '</Hash>' .
                '</Buffer>';

        include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

        $headers = array(
            'User-Agent' => $_SERVER['HTTP_USER_AGENT']
        );

        $parametri = array(
            'buffer' => $Bi
        );

        $itaRestClient = new itaRestClient();
        $itaRestClient->setTimeout($this->timeout);
        $itaRestClient->setDebugLevel($this->debug_level);
        $itaRestClient->setCurlopt_useragent($_SERVER['HTTP_USER_AGENT']);

//        $UrlPID = 'http://payertest.regione.marche.it/mpay/cart/extS2SPID.do';
        $UrlPID = $parametriMPay['URL_PAGA_DATA'];
        if (!$itaRestClient->get($UrlPID, $parametri, $headers)) {
            //errore
            $itaRestClient->getErrMessage();
            return false;
        }

        $buffer = $itaRestClient->getResult();
        file_put_contents("/tmp/buffer_pid", $buffer);

        /*
         * Decodifica del PaymentData e lettura dell'esito
         */
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($buffer);
        if (!$retXml) {
            $this->setErrMessage("Impossibile impostare il buffer in XML");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->setErrMessage("Impossibile convertire XML in Array");
            return false;
        }
        $paymentData = base64_decode($arrayXml['BufferDati'][0]['@textNode']);
        file_put_contents("/tmp/paymentData", $paymentData);
        if ($paymentData == "") {
            $this->setErrMessage("Impossibile decodificare il PaymentData");
            return false;
        }

        /*
         * Leggo XML del Payment Data
         */
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($paymentData);
        if (!$retXml) {
            $this->setErrMessage("Impossibile settare XML del Payment Data");
            return false;
        }
        return $xmlObj->toArray($xmlObj->asObject());
    }

    protected function customAgidRiscoSpecifica($progkeytabScade) {
        if (!$progkeytabScade) {
            return null;
        }
        return $this->getLibDB_BGE()->leggiBgeAgidRiscoMpay(array("PROGKEYTAB" => $progkeytabScade), false);
    }

}
