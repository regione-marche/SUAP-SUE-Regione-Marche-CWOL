<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaMaster.class.php';
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOmnis.class.php';
include_once(ITA_LIB_PATH . '/itaPHPEFill/itaEFillClient.class.php');
include_once(ITA_LIB_PATH . '/zip/itaZipCommandLine.class.php');
include_once (ITA_LIB_PATH . '/itaPHPCore/itaSFtpUtils.class.php');
include_once ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPDFUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtTributiHelper.php';

/**
 *
 * Classe per la gestione dell'intermediario E-FilZZ (PagoPa)
 * 
 */
class cwbPagoPaEfilZZ extends cwbPagoPaMaster {

    const ESITO_NEGATIVO = 'Nok';
    const ESITO_POSITIVO_ZZ = 'OK';
    const RIGHE_DELEGA_MAX = 10;
    const BLOCCO_PUBBLICAZIONE = 50;
    const REPORT_BARCODE_F24_ZZ = "cwbBarcodeF24ZZ";
    const REPORT_QRCODE_F24_ZZ = 'cwbQrCodeF24ZZ';
    const TEMPLATE_F24_ZZ = "/apps/CityBase/resources/template/ModelloF24SemplificatoZZ.pdf";
    const SOAP_ACTION_PAGASPONTANEOZZ_PREFIX = 'http://e-fil.eu/IDigitPos/';
    const SOAP_ACTION_FEEDZZ_PREFIX = 'http://e-fil.eu/FB/ZeroCodeFeed/IZeroCodeFeed/';
    const NAMESPACES_FEEDZZ = 'http://e-fil.eu/FB/ZeroCodeFeed';
    const NAMESPACES_FEEDZZ1 = 'http://e-fil.eu/FB/ZeroCodeFeed/CaricaDeleghe';
    const ENDPOINT_PAGAMENTOZZ_PRODUZIONE = 'https://f24onlinepos.plugandpay.it/DigitPosF24.svc'; // produzione
    const ENDPOINT_FEEDZZ_PRODUZIONE = 'https://siagtw.vaservices.eu/feed/ZeroCodeFeed.svc'; // produzione
    const ENDPOINT_RECUPERADELEGAZZ_PRODUZIONE = 'https://f24onlinepos.plugandpay.it/DigitPosF24.svc'; // produzione
    const NAMESPACES_PAYMENT_EFIL = "http://schemas.datacontract.org/2004/07/Efil.DigitPOS.ServiceImpl.DigitPOS.Dto";
    const NAMESPACES_PAYMENT_EFIL1 = "http://schemas.datacontract.org/2004/07/Efil.DigitPOS.ServiceImpl.Impl.DigitPOS.Model";
    const NAMESPACES_PAYMENT_EFIL2 = "http://schemas.datacontract.org/2004/07/Efil.DigitPOS.ServiceImpl.Contracts";
    const NAMESPACES_PAYMENT_E = "http://e-fil.eu/";
    const URL_PAYMENT = "https://pagaf24online.f24zerocodezeroerrori.it/Deleghe?";

    private function generaBollettinoPagoPaZZ($codRiferimento, $scadenza = null) {
        if (!$scadenza) {
            $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $codRiferimento, 'STATO' => 5));
        }
        if (!$scadenze) {
            $this->handleError(-1, "Errore. Scadenza in fase di pubblicazione");
            return false;
        } else {
            $scadenza = $scadenze[0];
        }
//        if (!$pendenza) {
//            $pendenza = $this->getLibDB_BWE()->leggiBwePendenChiave($scadenza['IDPENDEN']);
//        }
//        if (!$pendenza) {
//            $this->handleError(-1, "Errore. Pendenza non trovata");
//            return false;
//        }
        $confefil = $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array(), true);

        $idFornitore = trim($confefil['IDFORNITORE']);
        if (!$idFornitore) {
            $this->handleError(-1, "Errore IDFORNITORE mancante su tabella configurazione");
            return false;
        }
        $codContratto = trim($confefil['CODICECONTRATTO']);
        if (!$codContratto) {
            $this->handleError(-1, "Errore CODICECONTRATTO mancante su tabella configurazione");
            return false;
        }

//        $delega = $this->ricercaPosizioneDaIUV($codRiferimento);

        $cwtTributiHelper = new cwtTributiHelper();
//        $nometaOri = $cwtTributiHelper->mappingNomeTabOri[$scadenza['CODTIPSCAD']];
//        // prendo i valori da stampare da omnis 
//        if ($nometaOri == 'TDE_SCA' || $nometaOri == 'TDE_ACC' || $nometaOri == 'TBO_PRVSCA') {
//            $fieldsF24 = $cwtTributiHelper->valoriStampaF24Tari($nometaOri, $scadenza['ANNORIF'], $scadenza['PROGCITYSC'], array());
//        } else {
//            // TODO
//            //    $fieldsF24 = $cwtTributiHelper->valoriStampaF24ImuTasi($this->record['PROGSOGG'], $nometaOri, $pendenza['ANNORIF'], $this->record['KEY_PROG'], $tipoF24, $this->record['RATA_NUM'], $_POST[$this->nameForm . '_RAVVEDIMENTO'], $dataRavved);
//        }

        $paramsOmnis = array(
            'CODTIPSCAD' => $scadenza['CODTIPSCAD'],
            'SUBTIPSCAD' => $scadenza['SUBTIPSCAD'],
            'PROGCITYSC' => $scadenza['PROGCITYSC'],
            'ANNORIF' => $scadenza['ANNORIF'],
            'NUMRATA' => $scadenza['NUMRATA'],
            'DATASCADE' => $scadenza['DATASCADE'],
            'PROGKEYTAB' => $scadenza['PROGKEYTAB']
        );
        if ($scadenza['CODTIPSCAD'] >= 11 && $scadenza['CODTIPSCAD'] <= 30) {
            // tari
            $fieldsF24 = $cwtTributiHelper->valoriPagaF24Tari($paramsOmnis, 1, 1);
        } else {
            //imu
            $fieldsF24 = $cwtTributiHelper->valoriPagaF24ImuTasi($paramsOmnis);
        }

        if (!$fieldsF24 || !$fieldsF24['txtIci_SaldoFinale_P1']) {
            $this->handleError(-1, "Errore reperimento dati F24 da Omnis");
            return false;
        }
        $importo = str_replace(' ', '', $fieldsF24['txtIci_SaldoFinale_P1']);
        if (!$importo) {
            $this->handleError(-1, "Errore reperimento dati F24 da RecuperaDatiDelega. Importo Mancante");
            return false;
        }
        $importo = trim(str_pad($importo, 8, "0", STR_PAD_LEFT));
        // stampo il report con il barcode della prima pagina del bollettino
        $xmlData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $xmlData .= "<DATA><TESTOBARCODE><![CDATA[415" . $idFornitore . "8020" . $codRiferimento . "3902" . $importo . "]]></TESTOBARCODE>"
                . "<CODELINEBARCODE><![CDATA[(415)" . $idFornitore . "(8020)" . $codRiferimento . "(3902)" . $importo . "]]></CODELINEBARCODE>"
                . "<CODELINE><![CDATA[<" . $idFornitore . "  " . $codRiferimento . "  " . $importo . ">]]></CODELINE></DATA>";

        $itaJR = new itaJasperReport();
        try {
            $itaJR->runSQLReportPDF(App::$itaEngineDB, self::REPORT_BARCODE_F24_ZZ, array('XML_DATA' => $xmlData), false, 'none', $reportBarcodePath);
        } catch (Exception $exc) {
            $this->handleError(-1, "Errore generazione barcode");
            return false;
        }

        if (!$reportBarcodePath) {
            $this->handleError(-1, "Errore generazione barcode");
            return false;
        }

        $pdfTemplatePath = ITA_BASE_PATH . self::TEMPLATE_F24_ZZ;
        $itaPDFUtils = new itaPDFUtils();
        // prendo il template dell'f24 e popolo i campi
        if (!$itaPDFUtils->popolaAcrofields($pdfTemplatePath, $fieldsF24) || !$itaPDFUtils->getRisultato()) {
            $this->handleError(-1, "Errore popolamento variabili su pdf F24");
            return false;
        }

        if (!$itaPDFUtils->finalizzaPdfEditabile($itaPDFUtils->getRisultato()) || !$itaPDFUtils->getRisultato()) {
            $this->handleError(-1, "Errore finalizzazione pdf");
            return false;
        }

        $inputFiles = array(
            $reportBarcodePath,
            $itaPDFUtils->getRisultato()
        );

        // sovrappongo l'f4 compilato con il pdf che contiene barcode e timeline per farli diventare un unico pdf
        if (!$itaPDFUtils->sovrapponiPDF($inputFiles) || !$itaPDFUtils->getRisultato()) {
            $this->handleError(-1, "Errore apposizione barcode su pdf F24");
            return false;
        }

        // stampo da jarper il pdf con la seconda pagina del bollettino (qrcode)
        $cf = $fieldsF24['txtCodiceFiscale_P1'];
        $importoFormat = str_replace(' ', ',', $fieldsF24['txtIci_SaldoFinale_P1']);
        $testo = 'Scadenza del ' . date("d/m/Y", strtotime($scadenza['DATASCADE'])) . ' - Importo ' . $importoFormat . ' euro';
        $iddelega = "415" . $idFornitore . "8020" . $codRiferimento . "3902" . $importo;
        $urlPagam = self::URL_PAYMENT . 'idc=' . $codContratto . '&cf=' . $cf . '&iddelega=' . $iddelega;

        $xmlData = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $xmlData .= "<DATA><QRCODE><![CDATA[" . $urlPagam . "]]></QRCODE>"
                . "<TEXT><![CDATA[" . $testo . "]]></TEXT></DATA>";
        try {
            $rest = $itaJR->runSQLReportPDF(App::$itaEngineDB, self::REPORT_QRCODE_F24_ZZ, array('XML_DATA' => $xmlData), true, 'none', $reportQrcodePath);
        } catch (Exception $exc) {
            // se da errore vado avanti e stampo solo la prima pagina. La seconda pagina è per di più
        }
        if ($reportQrcodePath) {
            $inputFiles = array();
            $inputFiles[] = array('DELETE' => 1, 'PATH' => $itaPDFUtils->getRisultato());
            $inputFiles[] = array('DELETE' => 1, 'PATH' => $reportQrcodePath);
            $itaPDFUtils->unisciPdf($inputFiles);
        }

        $f24Content = file_get_contents($itaPDFUtils->getRisultato());

        $pathToDelete = $itaPDFUtils->getRisultato();
        unlink($pathToDelete);
        if (!$f24Content) {
            $this->handleError(-1, "Errore lettura F24");
            return false;
        }

        return $f24Content;
    }

    /**
     * genera il bollettino per il pagamento
     * 
     * @param array $params
     * @return binary il bollettino oppure false     
     *  
     */
    protected function customGeneraBollettino($params) {
        if ($params['CodiceIdentificativo']) {
            return $this->generaBollettinoPagoPaZZ($params['CodiceIdentificativo']);
        } else {
            $filtri = array(
                'CODTIPSCAD' => $params['CodTipScad'],
                'SUBTIPSCAD' => $params['SubTipScad'],
                'PROGCITYSC' => $params['ProgCitySc'],
                'ANNORIF' => $params['AnnoRif'],
                'NUMRATA' => $params['NumRata']
            );
            $pendenza = $this->getLibDB_BWE()->leggiBwePenden($filtri);

            if (!$pendenza) {
                $this->handleError(-1, "Pendenza non trovata");
                return false;
            } else if (count($pendenza) > 1) {
                $this->handleError(-1, "Pendenza non univoca");
                return false;
            } else if (count($pendenza) == 1) {
                $pendenza = $pendenza[0];
            }

            if ($pendenza['FLAG_PUBBL'] == 8 || $pendenza['FLAG_PUBBL'] == 7 || $pendenza['FLAG_PUBBL'] == 6) {
                $agidScadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('IDPENDEN' => $pendenza['PROGKEYTAB'], 'STATO' => 5));
                if (!$agidScadenze || !$agidScadenze[0]['CODRIFERIMENTO']) {
                    $this->handleError(-1, "Errore. Delega non ancora pubblicata");
                    return false;
                }
                return $this->generaBollettinoPagoPaZZ(trim($agidScadenze[0]['CODRIFERIMENTO']), $agidScadenze[0]);
            } else {
                $this->handleError(-1, "Errore flag_pubblic non compatibile (" . $pendenza['FLAG_PUBBL'] . ')');
                return null;
            }
        }
    }

    protected function customPubblicazioneScadenzeCreateMassiva($scadenzeDaPubbl) {
        $arrayScadenzePubblicate = array();
        $risposta = $this->caricaDelegheFeedZZ($scadenzeDaPubbl, $progkeytabInvio, $arrayScadenzePubblicate, true);
        if ($risposta) {
            // INSERT su BGE_AGID_INVII
            $this->inserisciDatiInvio($scadenzeDaPubbl, $arrayScadenzePubblicate, false);
            return $risposta;
        } else {
            return false;
        }
    }

    protected function customPubblicazioneMassiva($progkeytabInvio, $scadenzePerPubbli, $saltaPubblicazione = false) {
        $arrayScadenzePubblicate = array();
        if ($saltaPubblicazione) {
            // genera agid_scadenze e tba_dati_scambio ma non esegue la pubblicazione sul nodo
            $risposta = $this->generaDatiScambio($scadenzePerPubbli, $arrayScadenzePubblicate);
            // se genero solo senza pubblicare non salvo su agid_invio
            return true;
        } else {
            // genera agid_scadenze e tba_dati_scambio ed esegue la pubblicazione sul nodo
            $risposta = $this->caricaDelegheFeedZZ($scadenzePerPubbli, $progkeytabInvio, $arrayScadenzePubblicate);
        }
        if ($risposta) {
            // INSERT su BGE_AGID_INVII
            $this->inserisciDatiInvio($scadenzePerPubbli);
            return $risposta;
        } else {
            return false;
        }
    }

    protected function invioPuntualeScadenzaCustom($progkeytabScadenza) {
        $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenzeChiave($progkeytabScadenza);
        $arrayScadenzePubblicate = array();
        return $this->caricaDelegheFeedZZ(array($scadenza), null, $arrayScadenzePubblicate);
    }

    private function inserisciDatiInvio($scadenzePerPubbli, $arrayScadenzePubblicate, $transazioneAperta = true) {
        try {
            $progint = $this->generaProgressivoFlusso(date('Ymd'), $scadenzePerPubbli[0]['CODSERVIZIO'], 1);
            $invio = array(
                'TIPO' => 1,
                'INTERMEDIARIO' => $scadenzePerPubbli[0]['INTERMEDIARIO'],
                'CODSERVIZIO' => $scadenzePerPubbli[0]['CODSERVIZIO'],
                'DATAINVIO' => date('Ymd'),
                'PROGINT' => $progint,
                'NUMPOSIZIONI' => count($arrayScadenzePubblicate),
                'STATO' => 1,
            );

            $this->insertBgeAgidInvii($progkeytabInvio, $invio, $transazioneAperta);
            $xml = cwbLibCalcoli::arrayToXml($arrayScadenzePubblicate);

            $zip = $this->creaZip("PubblicazioneMassiva_" . $scadenzePerPubbli[0]['CODSERVIZIO'], $xml);

            // Costruisco record da salvare in BGE_AGID_ALLEGATI
            $allegati = array(
                'TIPO' => 1,
                //  'IDINVRIC' => intval($progkeytabInvio) > 0 ? $progkeytabInvio : $scadenza['PROGINV'],
                'IDINVRIC' => $progkeytabInvio,
                'DATA' => date('Ymd'),
                'NOME_FILE' => '',
                'ZIPFILE' => $zip
            );
            $this->insertBgeAgidAllegati($allegati, $transazioneAperta);
        } catch (Exception $exc) {
            
        }
    }

    private function generaDatiScambio($scadenzePerPubbliList) {
        $risposta = array();
        $confEfil = $this->leggiConfEfil();
        if (!$confEfil) {
            $this->handleError(-1, "Errore reperimento configurazione efil");
            return false;
        }

        $cwtTributiHelper = new cwtTributiHelper();
        $scadenzePerPubbliABlocchi = array_chunk($scadenzePerPubbliList, self::BLOCCO_PUBBLICAZIONE);

        foreach ($scadenzePerPubbliABlocchi as $key => $scadenzePerPubbli) {
            // passo ad omnis tutte le scadenze di cui mi serve l'f24, poi omnis le salva su una tba_dati_scambio
            // e io le rileggo dalla tabella. Questo perchè facendo n chiamate singole per ogni scadenza ad omnis consecutive si pianta tutto invece 
            // cosi faccio poche chiamate grosse che mi elaborano tutte le righe.
            if ($scadenzePerPubbli[0]['CODTIPSCAD'] >= 11 && $scadenzePerPubbli[0]['CODTIPSCAD'] <= 30) {
                // tari
                $esito = $cwtTributiHelper->valoriPagaF24Tari($scadenzePerPubbli, 0);
            } else {
                //imu
                $esito = $cwtTributiHelper->valoriPagaF24ImuTasi($scadenzePerPubbli, 0);
            }

            foreach ($scadenzePerPubbli as $scadenza) {
                try {
                    $lib = new cwtLibDB_TBA();
                    $filtri = array(
                        'KEY_NUM' => $scadenza['PROGKEYTAB']
                    );
                    // cerco in tabella i dati caricati precedentemente da omnis, se non li trovo rifaccio la chiamata omnis puntuale
                    $rec = $lib->leggiTbaDatiScambio($filtri, false);
                    if ($rec) {
                        $xml = stream_get_contents($rec['BINARIO']);
                        if ($xml) {
                            $resultXml = cwbLibCalcoli::stringXmlToArray($xml);
                            $f24 = $resultXml['RESULT']['LIST']['ROW'];
                        }
                    }
                    if (!$f24) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "Dati F24 da omnis non trovati";
                            $this->aggiornaBgeScadenze($toUpdate);
                        } catch (Exception $exc) {
                            
                        }
                        continue;
                    }
                    if (!$f24['SaldoFinale']) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "SaldoFinale mancante";
                            $this->aggiornaBgeScadenze($toUpdate);
                        } catch (Exception $exc) {
                            
                        }
                        continue;
                    }
                    if (!$f24['ComuneNascita']) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "ComuneNascita mancante";
                            $this->aggiornaBgeScadenze($toUpdate);
                        } catch (Exception $exc) {
                            
                        }
                        continue;
                    }
                } catch (Exception $exc) {
                    App::log("ws eccezione errore: " . $exc->getMessage() . ' ' . date("Y-m-d H:i:s") . PHP_EOL, false, true);
                    Out::systemEcho("ws eccezione errore: " . $exc->getMessage() . ' ' . date("Y-m-d H:i:s") . PHP_EOL, true);
                }
            }
        }
        return true;
    }

    private function caricaDelegheFeedZZ($scadenzePerPubbliList, $progkeytabInvio = null, &$arrayScadenzePubblicate = array(), $saltaOmnisMassivo = false) {
        $risposta = array();
        $confEfil = $this->leggiConfEfil();
        if (!$confEfil) {
            $this->handleError(-1, "Errore reperimento configurazione efil");
            return false;
        }

        // CARICA DELEGHE
        $customNamespace = array(
            'DelegaF24' => 'car', 'CodiceUfficio' => 'car', 'Contribuente' => 'car',
            'CodiceFiscalePartitaIva' => 'car', 'CognomeDenominazione' => 'car', 'ComuneOStatoEsteroDiNascita' => 'car',
            'DataNascita' => 'car', 'Email' => 'car', 'Nome' => 'car', 'ProvinciaDiNascita' => 'car', 'Sesso' => 'car',
            'DataScadenza' => 'car', 'IdentificativoOperazione' => 'car',
            'ImportoDelegaInCentesimi' => 'car', 'RigaDelegaF24' => 'car', 'RigaDelegaF24' => 'car',
            'Acconto' => 'efil1', 'Anno' => 'car', 'CodiceEnte' => 'car', 'CodiceTributo' => 'car',
            'ImportoDetrazioneInCentesimi' => 'car', 'ImportoRigaACreditoInCentesimi' => 'car',
            'ImportoRigaADebitoInCentesimi' => 'car',
            'NumeroImmobili' => 'car', 'NumeroImmobiliVariati' => 'car', 'Rateazione' => 'car',
            'Ravvedimento' => 'car', 'Saldo' => 'car', 'Sezione' => 'car'
        );

        $namespaces = array(
            'zer' => self::NAMESPACES_FEEDZZ,
            'car' => self::NAMESPACES_FEEDZZ1
        );

        $cwtTributiHelper = new cwtTributiHelper();
        // devo fare l'invio a blocchi perchè il ws non accetta piu di BLOCCO_PUBBLICAZIONE record alla volta
        $scadenzePerPubbliABlocchi = array_chunk($scadenzePerPubbliList, self::BLOCCO_PUBBLICAZIONE);

        foreach ($scadenzePerPubbliABlocchi as $key => $scadenzePerPubbli) {
            // passo ad omnis tutte le scadenze di cui mi serve l'f24, poi omnis le salva su una tba_dati_scambio
            // e io le rileggo dalla tabella. Questo perchè facendo n chiamate singole per ogni scadenza ad omnis consecutive si pianta tutto invece 
            // cosi faccio poche chiamate grosse che mi elaborano tutte le righe.
            if (!$saltaOmnisMassivo) {
                try {
                    if ($scadenzePerPubbli[0]['CODTIPSCAD'] >= 11 && $scadenzePerPubbli[0]['CODTIPSCAD'] <= 30) {
                        // tari
                        $esito = $cwtTributiHelper->valoriPagaF24Tari($scadenzePerPubbli, 0);
                    } else {
                        //imu
                        $esito = $cwtTributiHelper->valoriPagaF24ImuTasi($scadenzePerPubbli, 0);
                    }
                } catch (Exception $exc) {
                    
                }
            }

            foreach ($scadenzePerPubbli as $scadenza) {
                $i = 0;
                $f24 = array();
                $wsParams = array();
                $wsParams['IDApplicazione'] = $confEfil['IDAPPLICAZIONEF24ZZ'];
                $wsParams['IDPuntoVendita'] = $confEfil['PUNTOVENDITA'];
                $wsParams['IDSportello'] = $confEfil['SPORTELLO'];

                try {
                    if (!$scadenza['IMPDAPAGTO']) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "Scadenza non pubblicata per importo uguale a 0";
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                        $this->rispostaPubblicazione($risposta, $scadenza, false, "Scadenza non pubblicata per importo uguale a 0");
                        continue;
                    }

                    $lib = new cwtLibDB_TBA();
                    $filtri = array(
                        'KEY_NUM' => $scadenza['PROGKEYTAB']
                    );
                    // cerco in tabella i dati caricati precedentemente da omnis, se non li trovo rifaccio la chiamata omnis puntuale
                    $rec = $lib->leggiTbaDatiScambio($filtri, false);
                    if ($rec) {
                        $xml = stream_get_contents($rec['BINARIO']);
                        if ($xml) {
                            $resultXml = cwbLibCalcoli::stringXmlToArray($xml);
                            $f24 = $resultXml['RESULT']['LIST']['ROW'];
                        }
                    }

                    if (!$f24) {
                        $paramsOmnis = array(
                            'CODTIPSCAD' => $scadenza['CODTIPSCAD'],
                            'SUBTIPSCAD' => $scadenza['SUBTIPSCAD'],
                            'PROGCITYSC' => $scadenza['PROGCITYSC'],
                            'ANNORIF' => $scadenza['ANNORIF'],
                            'NUMRATA' => $scadenza['NUMRATA'],
                            'DATASCADE' => $scadenza['DATASCADE'],
                            'PROGKEYTAB' => $scadenza['PROGKEYTAB']
                        );
                        if ($scadenza['CODTIPSCAD'] >= 11 && $scadenza['CODTIPSCAD'] <= 30) {
                            // tari
                            $f24 = $cwtTributiHelper->valoriPagaF24Tari($paramsOmnis);
                        } else {
                            //imu
                            $f24 = $cwtTributiHelper->valoriPagaF24ImuTasi($paramsOmnis);
                        }
                    }

                    if (!$f24) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "Dati F24 da omnis non trovati";
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                        $this->rispostaPubblicazione($risposta, $scadenza, false, "Dati F24 da omnis non trovati");
                        continue;
                    }
                    $importoCentesimi = str_replace(" ", "", $f24['SaldoFinale']);

                    if (!$importoCentesimi || intval($importoCentesimi) <= 0) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "SaldoFinale mancante";
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                        $this->rispostaPubblicazione($risposta, $scadenza, false, "SaldoFinale dell'f24 non trovato");
                        continue;
                    }

                    $persGiuridica = $scadenza['TIPOPERS'] == 'G';

                    if (!$persGiuridica && !$f24['ComuneNascita']) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "ComuneNascita mancante";
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                        $this->rispostaPubblicazione($risposta, $scadenza, false, "ComuneNascita dell'f24 non trovato");
                        continue;
                    }

                    $cf = $this->formatCF($f24);

                    $codiceIdentificativo = "415" . trim($confEfil['IDFORNITORE']) . "8020" . $scadenza['CODRIFERIMENTO'] . "3902" . trim(str_pad($importoCentesimi, 8, "0", STR_PAD_LEFT));
                    $wsParams['DelegheF24'] = array();
                    $wsParams['DelegheF24'][$i] = array();
                    $wsParams['DelegheF24'][$i]['CodiceIdentificativo'] = $codiceIdentificativo;
                    $wsParams['DelegheF24'][$i]['Contribuente'] = array();
                    $wsParams['DelegheF24'][$i]['Contribuente']['CodiceFiscalePartitaIva'] = $cf;
                    $wsParams['DelegheF24'][$i]['Contribuente']['CognomeDenominazione'] = $f24['Cognome'];
                    if (!$persGiuridica) {
                        // solo per persone fisiche
                        $dataNascita = $f24['DataNascita_A1'] . $f24['DataNascita_A2'] . $f24['DataNascita_A3'] . $f24['DataNascita_A4'] . '-' . $f24['DataNascita_M1'] . $f24['DataNascita_M2'] . '-' . $f24['DataNascita_G1'] . $f24['DataNascita_G2'];

                        $wsParams['DelegheF24'][$i]['Contribuente']['ComuneOStatoEsteroDiNascita'] = $f24['ComuneNascita'] ? $f24['ComuneNascita'] : '';
                        $wsParams['DelegheF24'][$i]['Contribuente']['DataNascita'] = $dataNascita;
                    }
                    $wsParams['DelegheF24'][$i]['Contribuente']['Email'] = "xxx";
                    $nomeCont = '';
                    $provNascCont = '';
                    $sessoCont = '';
                    if (!$persGiuridica) {
                        $nomeCont = trim($f24['Nome']) ? trim($f24['Nome']) : '';
                        $provNascCont = $f24['Prov_1'] ? ($f24['Prov_1'] . $f24['Prov_2']) : '';
                        $sessoCont = $f24['Sesso'] == 'F' ? 'Femmina' : 'Maschio';
                    } else {
                        $sessoCont = 'PersonaNonFisica';
                    }
                    $wsParams['DelegheF24'][$i]['Contribuente']['Nome'] = $nomeCont;
                    $wsParams['DelegheF24'][$i]['Contribuente']['ProvinciaDiNascita'] = $provNascCont;
                    $wsParams['DelegheF24'][$i]['Contribuente']['Sesso'] = $sessoCont;
                    $wsParams['DelegheF24'][$i]['DataScadenza'] = date("Y-m-d", strtotime($scadenza['DATASCADE']));
                    $wsParams['DelegheF24'][$i]['IdentificativoOperazione'] = $f24['Identificativo_Operazione'];
                    $wsParams['DelegheF24'][$i]['ImportoDelegaInCentesimi'] = $importoCentesimi;
                    $wsParams['DelegheF24'][$i]['RigaDelegaF24'] = array();
                    $erroreRighe = false;
                    for ($y = 1; $y <= self::RIGHE_DELEGA_MAX; $y++) {
                        // omnis ritorna le variabili tutte a sbrodolo chiamate var1, var2,var3 fino a 10
                        // indipendentemente se sono popolate o no, quindi per capire le righe effettive me le devo scorrere 
                        // tutte e 10 e metto solo quelle popolate
                        if ($f24['Ici_Ente' . $y]) {// se codice ente vuoto la riga non è da considerare
                            $erroreRighe = $this->checkErroreRighe($f24, $y);
                            if ($erroreRighe) {
                                break;
                            }
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y] = array();
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['Acconto'] = ($f24['Ici_Acconto' . $y] == 'X') ? 1 : 0;
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['Anno'] = $f24['Ici_Annorif' . $y];
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['CodiceEnte'] = str_replace(" ", "", $f24['Ici_Ente' . $y]);
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['CodiceTributo'] = $f24['Ici_Tributo' . $y];
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['ImportoDetrazioneInCentesimi'] = 0;
                            // l'importo arriva da omnis con lo spazio al posto della virgola (es. 10 33)
                            // visto che mi serve l'importo in centesimi, tolgo direttamente lo spazio         
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['ImportoRigaACreditoInCentesimi'] = $f24['Ici_ImportoCred' . $y] ? str_replace(" ", "", $f24['Ici_ImportoCred' . $y]) : 0;
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['ImportoRigaADebitoInCentesimi'] = $f24['Ici_ImportoDeb' . $y] ? str_replace(" ", "", $f24['Ici_ImportoDeb' . $y]) : 0;
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['NumeroImmobili'] = $f24['Ici_NrImmob' . $y] ? $f24['Ici_NrImmob' . $y] : 0;
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['ImmobiliVariati'] = $f24['Ici_Immob' . $y] ? $f24['Ici_Immob' . $y] : 0;
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['Rateazione'] = trim($f24['Ici_Rateazione' . $y]);
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['Ravvedimento'] = ($f24['Ici_Ravved' . $y] == 'X') ? 1 : 0;
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['Saldo'] = ($f24['Ici_Saldo' . $y] == 'X') ? 1 : 0;
                            $wsParams['DelegheF24'][$i]['RigaDelegaF24'][$y]['Sezione'] = str_replace(" ", "", $f24['Ici_Sezione' . $y]);
                        }
                    }
                    $i++;

                    if ($erroreRighe) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "Rata incompleta";
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                        $this->rispostaPubblicazione($risposta, $scadenza, false, "Rata incompleta");
                        continue;
                    }

                    if (!$token) {
                        $token = $this->apriSessioneZZ($confEfil);
//                        App::log("***Aperta sessione token: " . $token . ' ' . date("Y-m-d H:i:s") . PHP_EOL, false, true);
//                        Out::systemEcho("***Aperta sessione token: " . $token . ' ' . date("Y-m-d H:i:s") . PHP_EOL, true);
                    }

                    if (!$token) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "token non valido";
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                        $this->rispostaPubblicazione($risposta, $scadenza, false, 'token non valido');

                        continue;
                    }
                    $wsParams['TokenIdentificativo'] = $token;
                    if (!$wsParams['TokenIdentificativo']) {
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = "token non valido";
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                        $this->rispostaPubblicazione($risposta, $scadenza, false, 'token non valido');

                        continue;
                    }

                    $result = $this->callWs($wsParams, 'caricaDelegheZZ', self::ENDPOINT_FEEDZZ_PRODUZIONE, self::SOAP_ACTION_FEEDZZ_PREFIX, $namespaces, $customNamespace, "zer");

                    if ($result['Token']) {
                        // chiamata a buon fine, prendo il nuovo token per la prossima chiamata
                        $token = $result['Token'];
//                        App::log("***Ws ok codiceIdentificativo: " . $codiceIdentificativo . ' token: ' . $token . ' ' . date("Y-m-d H:i:s") . PHP_EOL, false, true);
//                        Out::systemEcho("***Ws ok codiceIdentificativo: " . $codiceIdentificativo . ' token: ' . $token . ' ' . date("Y-m-d H:i:s") . PHP_EOL, true);


                        try {
                            $arrayScadenzePubblicate[] = array(
                                'PROGKEYTAB' => $scadenza['PROGKEYTAB'],
                                'CODRIFERIMENTO' => $scadenza['CODRIFERIMENTO']
                            );
                            $this->rispostaPubblicazione($risposta, $scadenza, true);

                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 5;
                            $toUpdate['PROGINV'] = $progkeytabInvio;
                            $toUpdate['DATAINVIO'] = date('Ymd');
                            $toUpdate['TIMEINVIO'] = date('H:i:s');
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                    } else {
//                        App::log("*****ATTENZIONEEEE: " . date("Y-m-d H:i:s") . PHP_EOL, false, true);
//                        Out::systemEcho("*****ATTENZIONEEEE: " . date("Y-m-d H:i:s") . PHP_EOL, true);
//
//                        App::log("ws KO errore: " . $this->getLastErrorDescription() . ' ' . date("Y-m-d H:i:s") . PHP_EOL, false, true);
//                        Out::systemEcho("ws KO errore: " . $this->getLastErrorDescription() . ' ' . date("Y-m-d H:i:s") . PHP_EOL, true);
                        $token = ''; // svuoto il token che poi va riaggiornato con il token tornato dal ws o riaprendo una nuova sessione

                        $this->rispostaPubblicazione($risposta, $scadenza, false, $this->getLastErrorDescription());
                        try {
                            // aggiorno la scadenza
                            $toUpdate = array();
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 3;
                            $toUpdate['NOTESOSP'] = substr($this->getLastErrorDescription(), 0, 998);
                            $this->aggiornaBgeScadenze($toUpdate, !$saltaOmnisMassivo);
                        } catch (Exception $exc) {
                            
                        }
                    }
                } catch (Exception $exc) {
//                    App::log("*****ATTENZIONEEEE: " . date("Y-m-d H:i:s") . PHP_EOL, false, true);
//                    Out::systemEcho("*****ATTENZIONEEEE: " . date("Y-m-d H:i:s") . PHP_EOL, true);
//
//                    App::log("ws eccezione errore: " . $exc->getMessage() . ' ' . date("Y-m-d H:i:s") . PHP_EOL, false, true);
//                    Out::systemEcho("ws eccezione errore: " . $exc->getMessage() . ' ' . date("Y-m-d H:i:s") . PHP_EOL, true);

                    $token = ''; // svuoto il token che poi va riaggiornato con il token tornato dal ws o riaprendo una nuova sessione
                    $this->rispostaPubblicazione($risposta, $scadenza, false, $exc->getMessage());
                }
            }
        }

        $this->chiudiSessioneZZ($token, $confEfil);
//        App::log("*****chiusa sessione " . date("Y-m-d H:i:s") . PHP_EOL, false, true);
//        Out::systemEcho("*****chiusa sessione " . date("Y-m-d H:i:s") . PHP_EOL, true);

        if (!$risposta) {
            return false;
        }

        return $risposta;
    }

    private function checkErroreRighe($f24, $riga) {
        if (!$f24['Ici_Annorif' . $riga]) {
            return true;
        }
        if (!$f24['Ici_Ente' . $riga]) {
            return true;
        }
        if (!$f24['Ici_Tributo' . $riga]) {
            return true;
        }
        return false;
    }

    private function formatCF($f24) {
        $cf = '';
        for ($index = 1; $index <= 16; $index++) {
            $cfTemp = (is_array($f24['CF_' . $index]) || $f24['CF_' . $index] === null) ? '' : $f24['CF_' . $index];
            $cf .= $cfTemp;
        }

        return trim($cf);
    }

    public function apriSessioneZZ($confEfil) {
        // LOGIN       
        $data = array(
            'IDApplicazione' => $confEfil['IDAPPLICAZIONEF24ZZ'],
            'IDPuntoVendita' => $confEfil['PUNTOVENDITA'],
            'IDSportello' => $confEfil['SPORTELLO'],
            'Password' => $confEfil['PSWCHIAMANTE']
        );

        if (!$data['IDPuntoVendita']) {
            $this->handleError(-1, "Parametro IDPuntoVendita mancante");
            return false;
        }
        if (!$data['IDApplicazione']) {
            $this->handleError(-1, "Parametro IDApplicazione mancante");
            return false;
        }
        if (!$data['IDSportello']) {
            $this->handleError(-1, "Parametro IDSportello mancante");
            return false;
        }
        if (!$data['Password']) {
            $this->handleError(-1, "Parametro Password mancante");
            return false;
        }

        $wsParams = array('request' => $data);

        $namespaces = array(
            'plug' => self::NAMESPACES_FEEDZZ
        );

        $tokenResponse = $this->callWs($wsParams, 'aperturaSessioneFeedZZ', self::ENDPOINT_FEEDZZ_PRODUZIONE, self::SOAP_ACTION_FEEDZZ_PREFIX, $namespaces);
        return $tokenResponse['Token'];
    }

    private function chiudiSessioneZZ($token, $confEfil) {
        // LOGOUT
        $data = array(
            'IDApplicazione' => $confEfil['IDAPPLICAZIONEF24ZZ'],
            'IDPuntoVendita' => $confEfil['PUNTOVENDITA'],
            'IDSportello' => $confEfil['SPORTELLO'],
            'TokenIdentificativo' => $token
        );
        $wsParams = array('request' => $data);
        $namespaces = array(
            'plug' => self::NAMESPACES_FEEDZZ
        );
        $this->callWs($wsParams, 'chiusuraSessioneFeedZZ', self::ENDPOINT_FEEDZZ_PRODUZIONE, self::SOAP_ACTION_FEEDZZ_PREFIX, $namespaces);
    }

    private function eseguiPagamentoSpontaneoZZ($pendenza, $urlReturn) {
        $cwtTributiHelper = new cwtTributiHelper();
        $confEfil = $this->leggiConfEfil();
        if (!$confEfil) {
            $this->handleError(-1, "Errore reperimento configurazione efil");
            return false;
        }

        $codRiferimento = $this->eseguiInserimento(array($pendenza), false, false, false, true);
        if (!$codRiferimento) {
            // errore impostato dal metodo eseguiInserimento
            return false;
        }

        if ($pendenza['CODTIPSCAD'] >= 11 && $pendenza['CODTIPSCAD'] <= 30) {
            // tari
            $f24 = $cwtTributiHelper->valoriPagaF24Tari($pendenza);
        } else {
            //imu
            $f24 = $cwtTributiHelper->valoriPagaF24ImuTasi($pendenza);
        }

        if (!$f24) {
            $this->handleError(-1, "Errore reperimento dati da omnis server");
            return false;
        }

        // aggiungo in mezzo la chiamata a pagopa per allineare i dati su db cityware
        $urlOk = $this->getUrlPagamento($codRiferimento, $urlReturn, true);
        $urlKo = $this->getUrlPagamento($codRiferimento, $urlReturn, false);

        $customNamespace = array(
            'CodiceContratto' => 'efil', 'Delega' => 'efil', 'IdentificativoChiamante' => 'efil',
            'Password' => 'efil', 'UrlAnnullo' => 'efil', 'UrlKo' => 'efil', 'UrlOk' => 'efil',
            'CodiceAtto' => 'efil1', 'CodiceUfficio' => 'efil1', 'Contribuente' => 'efil1', 'Cellulare' => 'efil1',
            'CodiceFiscalePartitaIva' => 'efil1', 'CognomeDenominazione' => 'efil1',
            'ComuneOStatoEsteroDiNascita' => 'efil1', 'DataNascita' => 'efil1', 'Email' => 'efil1',
            'Indirizzo' => 'efil1', 'Nome' => 'efil1', 'ProvinciaDiNascita' => 'efil1', 'Sesso' => 'efil1',
            'Descrizione' => 'efil1', 'Fornitore' => 'efil1', 'IdentificativoDelegaDicontratto' => 'efil1',
            'IdentificativoOperazione' => 'efil1', 'ImportoInCentesimi' => 'efil1', 'RigheDelegaF24' => 'efil1',
            'RigaDelegaF24' => 'efil1', 'Acconto' => 'efil1', 'AnnoDiRiferimento' => 'efil1',
            'CodiceEnte' => 'efil1', 'CodiceTributo' => 'efil1', 'ImmobiliVariati' => 'efil1',
            'ImportoDetrazioneInCentesimi' => 'efil1', 'ImportoRigaACreditoInCentesimi' => 'efil1',
            'ImportoRigaADebitoInCentesimi' => 'efil1', 'NumeroImmobili' => 'efil1', 'Rateazione' => 'efil1',
            'Ravvedimento' => 'efil1', 'Saldo' => 'efil1', 'Sezione' => 'efil1', 'DataScadenza' => 'efil1'
        );

        $namespaces = array(
            'e' => self::NAMESPACES_PAYMENT_E,
            'efil' => self::NAMESPACES_PAYMENT_EFIL,
            'efil1' => self::NAMESPACES_PAYMENT_EFIL1
        );

        $dataNascita = $f24['DataNascita_A1'] . $f24['DataNascita_A2'] . $f24['DataNascita_A3'] . $f24['DataNascita_A4'] . '-' . $f24['DataNascita_M1'] . $f24['DataNascita_M2'] . '-' . $f24['DataNascita_G1'] . $f24['DataNascita_G2'];

        $cf = $f24['CF_1'] . $f24['CF_2'] . $f24['CF_3'] . $f24['CF_4'] . $f24['CF_5'] . $f24['CF_6'] . $f24['CF_7'] . $f24['CF_8'] . $f24['CF_9'] . $f24['CF_10'] . $f24['CF_11'] . $f24['CF_12'] . $f24['CF_13'] . $f24['CF_14'] . $f24['CF_15'] . $f24['CF_16'];

        $wsParams = array();
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['CodiceContratto'] = $confEfil['CODICECONTRATTO'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega'] = array();
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente'] = array();
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Cellulare'] = 0;
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['CodiceFiscalePartitaIva'] = $cf;
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['CognomeDenominazione'] = $f24['Cognome'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['ComuneOStatoEsteroDiNascita'] = $f24['ComuneNascita'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['DataNascita'] = $dataNascita;
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Email'] = "xxx";
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Indirizzo'] = $f24['IndirizzoRes'] . ' ' . $f24['LocalitaRes'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Nome'] = $f24['Nome'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['ProvinciaDiNascita'] = $f24['Prov_1'] . $f24['Prov_2'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Contribuente']['Sesso'] = $f24['Sesso'] == 'F' ? 'Femmina' : 'Maschio';
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['DataScadenza'] = date("Y-m-d", strtotime($pendenza['DATASCADE']));
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Descrizione'] = $pendenza['DESCRPEND'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['Fornitore'] = cwbParGen::getDesente();
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['IdentificativoDelegaDicontratto'] = $codRiferimento;
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['IdentificativoOperazione'] = $f24['Identificativo_Operazione'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['ImportoInCentesimi'] = str_replace(" ", "", $f24['SaldoFinale']);
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'] = array();

        for ($i = 1; $i <= self::RIGHE_DELEGA_MAX; $i++) {
            // omnis ritorna le variabili tutte a sbrodolo chiamate var1, var2,var3 fino a 10
            // indipendentemente se sono popolate o no, quindi per capire le righe effettive me le devo scorrere 
            // tutte e 10 e metto solo quelle popolate
            if ($f24['Ici_Ente' . $i]) {// se codice ente vuoto la riga non è da considerare
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i] = array();
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['Acconto'] = ($f24['Ici_Acconto' . $i] == 'X') ? 1 : 0;
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['AnnoDiRiferimento'] = $f24['Ici_Annorif' . $i];
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['CodiceEnte'] = str_replace(" ", "", $f24['Ici_Ente' . $i]);
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['CodiceTributo'] = $f24['Ici_Tributo' . $i];
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['ImmobiliVariati'] = $f24['Ici_Immob' . $i] ? $f24['Ici_Immob' . $i] : 0;
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['ImportoDetrazioneInCentesimi'] = 0;
                // l'importo arriva da omnis con lo spazio al posto della virgola (es. 10 33)
                // visto che mi serve l'importo in centesimi, tolgo direttamente lo spazio         
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['ImportoRigaACreditoInCentesimi'] = $f24['Ici_ImportoCred' . $i] ? str_replace(" ", "", $f24['Ici_ImportoCred' . $i]) : 0;
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['ImportoRigaADebitoInCentesimi'] = $f24['Ici_ImportoDeb' . $i] ? str_replace(" ", "", $f24['Ici_ImportoDeb' . $i]) : 0;
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['NumeroImmobili'] = $f24['Ici_NrImmob' . $i] ? $f24['Ici_NrImmob' . $i] : 0;
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['Rateazione'] = trim($f24['Ici_Rateazione' . $i]);
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['Ravvedimento'] = ($f24['Ici_Ravved' . $i] == 'X') ? 1 : 0;
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['Saldo'] = ($f24['Ici_Saldo' . $i] == 'X') ? 1 : 0;
                $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Delega']['RigheDelegaF24'][$i]['Sezione'] = str_replace(" ", "", $f24['Ici_Sezione' . $i]);
            }
        }

        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['IdentificativoChiamante'] = $confEfil['IDCHIAMANTE'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['Password'] = $confEfil['PSWCHIAMANTE'];
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['UrlAnnullo'] = $urlKo;
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['UrlKo'] = $urlKo;
        $wsParams['avviaTransazionePagamentoSpontaneoRequest']['UrlOk'] = $urlOk;

        $result = $this->callWs($wsParams, 'eseguiPagamentoSpontaneoZZ', self::ENDPOINT_PAGAMENTOZZ_PRODUZIONE, self::SOAP_ACTION_PAGASPONTANEOZZ_PREFIX, $namespaces, $customNamespace, 'e');

        if ($codRiferimento && $result['EsitoTransazione']['EsitoTransazione'] == self::ESITO_POSITIVO_ZZ && $result['EsitoTransazione']['UrlRiepilogoF24']) {
            try {
                // aggiorno agidscadenze con l'url del pagamento, se non funziona vado avanti uguale
                // tanto il bollettino f24 funziona anche senza l'url che sta messo in seconda pagina
                $url = $result['EsitoTransazione']['UrlRiepilogoF24'];
                $agidScadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $codRiferimento), true);
                if ($agidScadenza) {
                    $agidScadenza['URL'] = $url;
                    $this->aggiornaBgeAgidScadenze($agidScadenza, false);
                }
            } catch (Exception $ex) {
                
            }
        }

        return $result;
    }

    private function eseguiPagamentoPredeterminatoZZ($identificativo, $urlReturn, $redirectVerticale = 0) {
        $confEfil = $this->leggiConfEfil();
        if (!$confEfil) {
            $this->handleError(-1, "Errore reperimento configurazione efil");
            return false;
        }

        $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $identificativo));

        if (!$scadenze) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        }
        if (count($scadenze) > 1) {
            $this->handleError(-1, "Scadenze multiple trovate con lo stesso iuv");
            return false;
        }

        $customNamespace = array(
            'CodiceContratto' => 'efil', 'Email' => 'efil', 'IdentificativoChiamante' => 'efil', 'IdentificativoDelega' => 'efil',
            'Password' => 'efil', 'Telefono' => 'efil', 'UrlAnnullo' => 'efil', 'UrlKo' => 'efil', 'UrlOk' => 'efil'
        );

        $namespaces = array(
            'e' => self::NAMESPACES_PAYMENT_E,
            'efil' => self::NAMESPACES_PAYMENT_EFIL
        );

        if (!$redirectVerticale) {
            // aggiungo in mezzo la chiamata a pagopa per allineare i dati su db cityware
            $urlOk = $this->getUrlPagamento($identificativo, $urlReturn, true);
            $urlKo = $this->getUrlPagamento($identificativo, $urlReturn, false);
        } else {
            $var = '?';
            if (strpos($urlReturn, '?') !== false) {
                $var = '&';
            }
            $urlOk = $urlReturn . $var . 'Esito=OK';
            $urlKo = $urlReturn . $var . 'Esito=KO';
        }

        $idFornitore = trim($confEfil['IDFORNITORE']);
        $importo = $scadenze[0]['IMPDAPAGTO'] * 100;
        $importo = trim(str_pad($importo, 8, "0", STR_PAD_LEFT));

        $identificativoQrCode = '415' . $idFornitore . "8020" . $identificativo . "3902" . $importo;

        $data = array(
            'CodiceContratto' => $confEfil['CODICECONTRATTO'],
            'Email' => 'XXX',
            'IdentificativoChiamante' => $confEfil['IDCHIAMANTE'],
            'IdentificativoDelega' => $identificativoQrCode,
            'Password' => $confEfil['PSWCHIAMANTE'],
            'Telefono' => 33333,
            'UrlAnnullo' => $urlKo,
            'UrlKo' => $urlKo,
            'UrlOk' => $urlOk
        );

        $wsParams = array('avviaTransazionePagamentoPredeterminatoRequest' => $data);

        $result = $this->callWs($wsParams, 'eseguiPagamentoPredeterminatoZZ', self::ENDPOINT_PAGAMENTOZZ_PRODUZIONE, self::SOAP_ACTION_PAGASPONTANEOZZ_PREFIX, $namespaces, $customNamespace, 'e');

        if ($result['EsitoTransazione']['EsitoTransazione'] == self::ESITO_POSITIVO_ZZ && $result['EsitoTransazione']['UrlRiepilogoF24']) {
            try {
                // aggiorno agidscadenze con l'url del pagamento, se non funziona vado avanti uguale
                // tanto il bollettino f24 funziona anche senza l'url che sta messo in seconda pagina
                $url = $result['EsitoTransazione']['UrlRiepilogoF24'];
                $agidScadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $identificativo), true);
                if ($agidScadenza) {
                    $agidScadenza['URL'] = $url;
                    $this->aggiornaBgeAgidScadenze($agidScadenza, false);
                }
            } catch (Exception $ex) {
                
            }
        }

        return $result;
    }

    protected function customRecuperaIuv($res) {
        return $res['CODRIFERIMENTO'];
    }

    protected function customEseguiPagamento($scadenza, $urlReturn, $redirectVerticale = 0) {
        $result = $this->eseguiPagamentoPredeterminatoZZ($scadenza['CODRIFERIMENTO'], $urlReturn);
        return $this->elabEsitoPagopaPagam($result);
    }

    private function elabEsitoPagopaPagam($result) {
        if (!$result) {
            return null;
        } else {
            $esito = $result['EsitoTransazione']['EsitoTransazione'];
            $esitoPositivo = self::ESITO_POSITIVO_ZZ;
            if ($esito == $esitoPositivo) {
                $url = $result['EsitoTransazione']['UrlRiepilogoF24'];
                return urlencode($url);
            } else {
                // errore di validazione del ws efill               
                $err = $this->manageEfillErrorMessage($result['EsitoTransazione']['ErroriTransazione']);
                $this->handleError(-1, $err);
                return null;
            }
        }

        return null;
    }

    public function rettificaPosizioneDaIUV($IUV, $toUpdate) {
        
    }

    public function customRicercaPosizioneDaIUV($IUV) {
        if (!$IUV) {
            $this->handleError(-1, "Parametro CodiceRiferimento (IdentificativoDelega) mancante");
            return false;
        }

        $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $IUV));

        if (!$scadenze) {
            $this->handleError(-1, "Scadenza non trovata");
            return false;
        }
        if (count($scadenze) > 1) {
            $this->handleError(-1, "Scadenze multiple trovate con lo stesso iuv");
            return false;
        }

        $conf = $this->getConfIntermediario();

        $confefil = $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array(), true);

        $idFornitore = trim($confefil['IDFORNITORE']);
        $importo = $scadenze[0]['IMPDAPAGTO'] * 100;
        $importo = trim(str_pad($importo, 8, "0", STR_PAD_LEFT));

        $identificativo = '415' . $idFornitore . "8020" . $IUV . "3902" . $importo;

        $data = array(
            'CodiceContratto' => $conf['CODICECONTRATTO'],
            'IdentificativoChiamante' => $conf['IDCHIAMANTE'],
            'IdentificativoDelega' => $identificativo,
            'Password' => $conf['PSWCHIAMANTE']
        );

        $wsParams = array('recuperaDatiDelegaRequest' => $data);

        $customNamespace = array(
            'CodiceContratto' => 'efil', 'Email' => 'efil', 'IdentificativoChiamante' => 'efil', 'IdentificativoDelega' => 'efil',
            'Password' => 'efil'
        );

        $namespaces = array(
            'e' => self::NAMESPACES_PAYMENT_E,
            'efil' => self::NAMESPACES_PAYMENT_EFIL2
        );

        $result = $this->callWs($wsParams, 'RecuperaDatiDelega', self::ENDPOINT_RECUPERADELEGAZZ_PRODUZIONE, self::SOAP_ACTION_PAGASPONTANEOZZ_PREFIX, $namespaces, $customNamespace, 'e');

        return $this->formatRispDaRicercaPosizione($result);
    }

    private function formatRispDaRicercaPosizione($result) {
        if ($result && $result['DelegaF24']) {
            return $this->formatRispostaDaRicercaPosizione($result['DelegaF24']['Descrizione'], $result['DelegaF24']['StatoDelega'], $result['DelegaF24']['DataScadenza'], $result['DelegaF24']['ImportoInCentesimi'], null, $result['DelegaF24']['IdentificativoOperazione']);
        }
        return false;
    }

    private function manageEfillErrorMessage($esitoCaricamento) {
        $esiti = $esitoCaricamento;
        $toReturn = '';
        if ($esiti['Codice']) {
            // se ci sono tanti errori torna un array di array (codice - descrizione) con gli n errori 
            // se invece c'è un solo errore torna direttamente l'errore (codice - descrizione) invece che un array di array con size 1
            $esiti = array($esiti);
        }

        foreach ($esiti as $esito) {
            $toReturn .= ' - ' . $esito['Descrizione'];
        }

        return $toReturn;
    }

    protected function leggiScadenzePerPubblicazioni($progkeytabScadenze = null, $stato = null, $page = null) {
        if ($progkeytabScadenze) {
            $filtri['PROGKEYTAB_SCADENZA_IN'] = $progkeytabScadenze;
        }
        if ($stato) {
            $filtri['STATO'] = $stato;
        }
        if ($page === null) {
            $scadenzePerPubbli = $this->getLibDB_BTA()->leggiBgeAgidScadenzePerPubblicazioniEfil($filtri);
        } else {
            $scadenzePerPubbli = $this->getLibDB_BTA()->leggiBgeAgidScadenzePerPubblicazioniEfilBlocchi($filtri, $page, $this->customPaginatorSize());
        }
        return $scadenzePerPubbli;
    }

    protected function customEseguiInserimentoSingolo($progkeytabAgidScadenze, $pendenza) {
        $row_Agid_Sca_Efil = array(
            'PROGKEYTAB' => $progkeytabAgidScadenze,
            'NUMAVVISO' => null,
            'ANADEBITORE' => $pendenza['RAGSOC']
        );

        $this->inserisciBgeAgidScaEfil($row_Agid_Sca_Efil);
    }

    public function inserisciBgeAgidScaEfil($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCA_EFIL', true, $startedTransaction);
    }

    protected function getCustomConfIntermediario() {
        return $this->getLibDB_BGE()->leggiBgeAgidConfEfil(array());
    }

    protected function getEmissioniPerPubblicazione($filtri = array()) {
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_ZZ_TYPE;
        $filtri['FLAG_PUBBL_IN'] = array(6, 7, 8);
        return $this->getLibDB_BGE()->leggiEmissioniDaPubblicare($filtri);
    }

    protected function leggiScadenzePerInserimento($filtri = array(), $page = null) {
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_ZZ_TYPE;
        $filtri['FLAG_PUBBL_IN'] = array(6, 7, 8);
        if ($page === null) {
            $scadenze = $this->getLibDB_BWE()->leggiBwePendenScadenze($filtri);
        } else {
            $scadenze = $this->getLibDB_BWE()->leggiBwePendenScadenzeBlocchi($filtri, $page, $this->customPaginatorSize());
        }
        return $scadenze;
    }

    protected function customFormatoAnnoCodRiferimento() {
        return 2;
    }

    protected function customPaginatorSize() {
        return 500;
    }

    private function leggiConfEfil() {
        $conf_Efil = $this->getLibDB_BGE()->leggiBgeAgidConfEfil();
        return $conf_Efil;
    }

    protected function customCancellazioneMassiva() {
        $scadenzePerCanc = $this->leggiScadenzePerCancellazione();
        if ($scadenzePerCanc) {
            $this->deleteScadenzeCancellate($scadenzePerCanc);
        }
    }

    private function deleteScadenzeCancellate($scadenze, $transaction = false) {
        if (!$scadenze[0]) {
            $scadenzeIns[] = $scadenze;
        } else {
            $scadenzeIns = $scadenze;
        }
        foreach ($scadenzeIns as $scadenza) {
            try {
                $this->deleteRecord($scadenza, 'BGE_AGID_SCADENZE', false);

                $toInsert = array();
                $toInsert = $scadenza;
                $where = ' PROGKEYTAB=' . $scadenza['PROGKEYTAB'];
                $progintStoscade = cwbLibCalcoli::trovaProgressivo('PROGINT', 'BGE_AGID_STOSCADE', $where);
                $toInsert['PROGINT'] = $progintStoscade;
                $toInsert['STATO'] = 7;
                $this->inserisciBgeAgidStoscade($toInsert);
            } catch (Exception $exc) {
                
            }
        }
    }

    private function leggiScadenzePerCancellazione() {
        $filtri['TIP_INS'] = 3;
        $scadenzePerCanc = $this->getLibDB_BGE()->leggiBgeAgidScadenzePerCancellazione($filtri);
        return $scadenzePerCanc;
    }

    protected function customCalcoloCodRiferimento($pendenza, $codRiferimento) {
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
        $filtri['INTERMEDIARIO'] = itaPagoPa::EFILL_ZZ_TYPE;
        $res = $this->getLibDB_BGE()->leggiBgeAgidInterm($filtri);
        return $res['CODSEGREG'];
    }

    private function callWs($wsParams, $method, $endpoint, $soapActionPrefix, $namespaces = array(), $customNamespacePrefix = array(), $nameSpacePrefixDefault = 'plug') {
        $client = new itaEFillClient();
        $client->setWebservices_uri($endpoint);
        $client->setNamespacePrefix($nameSpacePrefixDefault);
        $client->setNamespaces($namespaces);
        $client->setSoapActionPrefix($soapActionPrefix);
        $client->setCustomNamespacePrefix($customNamespacePrefix);
        $result = $client->$method($wsParams);
        $xmlRequest = $client->getRequest();
        $xmlResponse = $client->getResponse();
        if (!$result) {
            $this->handleError(-1, $client->getError());
            return false;
        } else {
            return $client->getResult();
        }
    }

    protected function getCustomTipins($massivo = false) {
        return 3;
    }

    protected function customCaricaScadenzaPagamento($codiceIdentificativo) {
        return $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $codiceIdentificativo), false);
    }

    protected function preRimuoviPosizione($params) {
        $this->handleError(-1, "Rimozione non disponibile per questo intermediario");
        return false; // rimozione non disponibile
    }

}

?>
