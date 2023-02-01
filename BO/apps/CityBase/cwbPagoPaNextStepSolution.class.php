<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbPagoPaMaster.class.php';
include_once ITA_LIB_PATH . '/itaPHPLinkNext/itaLinkNextClient.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibPagoPaUtils.php';
include_once ITA_BASE_PATH . '/apps/CityFee/cwsLibDB_SBO.class.php';
include_once ITA_LIB_PATH . '/itaPHPLinkNext/itaPosizioneDebitoriaCw.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibOmnis.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

/**
 *
 * Classe per la gestione dell'intermediario NextStepSolution (PagoPa)
 * 
 */
class cwbPagoPaNextStepSolution extends cwbPagoPaMaster {

    const ESITO_POSITIVO = 'OK';
    const ESITO_NEGATIVO = 'KO';

    public function aggiornaBgeScadenzeNSS($toUpdate, $startedTransaction = true) {
        return $this->insertUpdateRecord($toUpdate, 'BGE_AGID_SCA_NSS', false, $startedTransaction);
    }

    public function inserisciBgeAgidRiscoNSS($toInsert, $startedTransaction = true) {
        return $this->insertUpdateRecord($toInsert, 'BGE_AGID_RISCO_NSS', true, $startedTransaction);
    }

    protected function leggiScadenzePerPubblicazioniTEST($annoemi, $numemi, $idbol_sere) {
        //Lettura dati pubblicazioni grezzi da DB
        return $this->pubblicazioneDirettaLeggiDatiDaElaborare($annoemi, $numemi, $idbol_sere);
    }

    protected function leggiScadenzePerPubblicazioni($progkeytabScadenze = null, $stato = null) {
        if ($progkeytabScadenze) {
            $filtri['PROGKEYTAB_SCADENZA_IN'] = $progkeytabScadenze;
        }
        if ($stato) {
            $filtri['STATO'] = $stato;
        }
        $scadenzePerPubbli = $this->getLibDB_BTA()->leggiBgeAgidScadenzePerPubblicazioniNSS($filtri);
        return $scadenzePerPubbli;
    }

    private function customPreparazionePerPubblicazione($progkeytabInvio, &$scadenzePerPubbli, &$nomeFile, &$file, &$numPosizioni, $codServizio, &$posizioniDebitorie = null) {
        try {
            //     $datiElaboratiTEST = $this->pubblicazioneDirettaAdattaRisultato($scadenzePerPubbliTEST);
            $datiElaborati = $this->pubblicazioneDirettaAdattaArrayDati($scadenzePerPubbli);
            //Divide i dati da elaborare in gruppi di n elementi
            $posizioniDebitorie = $this->paginaDatiElaborati($datiElaborati, 100);
            //     $posizioniDebitorieTEST = $this->paginaDatiElaborati($datiElaboratiTEST, 100);
            //Creazione del file zip
            $nomeFile = "PubblicazioneNSS_" . $progkeytabInvio;
            $file = itaLib::getUploadPath() . "/" . $nomeFile . ".zip";
//            while (file_exists($file)) {
//                $file = itaLib::getUploadPath() . "/" . $nomeFile . ".zip";
//            }
            //  $nomeFile = basename($file);
            $zip = new ZipArchive();
            if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return false;
            }
            $xml = cwbLibCalcoli::arrayToXml($datiElaborati);
            $zip->addFromString($nomeFile, $xml);
            $zip->close();
        } catch (ItaException $e) {
            return false;
        }
        return true;
    }

    private function customGestioneRisposta($posizioniDebitorie, $invio, $nomeFile) {
        cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
        try {

            $file = itaLib::getUploadPath() . "/" . $nomeFile;

            // $nomeFile = basename($file);
            $zip = new ZipArchive();
            if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return false;
            }
            $xml = cwbLibCalcoli::arrayToXml($posizioniDebitorie);

            $zip->addFromString($nomeFile, $xml);

            $zip->close();

            // Salvataggio su BGE_AGID_RICEZ
            $ricezione = array(
                "TIPO" => 11,
                "INTERMEDIARIO" => $invio['INTERMEDIARIO'],
                "CODSERVIZIO" => $invio['CODSERVIZIO'],
                "DATARIC" => date('Ymd'),
                "IDINV" => $invio['PROGKEYTAB'],
            );
            $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

            $devLib = new devLib();
            $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);

            // Costruisco array per salvataggio allegato
            $allegati = array(
                'TIPO' => 11,
                'IDINVRIC' => $progkeytabRicez,
                'DATA' => date('Ymd'),
                'NOME_FILE' => $nomeFile,
            );

            if (intval($configGestBin['CONFIG']) === 0) {
                //INSERT su BGE_AGID_ALLEGATI
                $allegati['ZIPFILE'] = file_get_contents($file);
                $this->insertBgeAgidAllegati($allegati);
            } else {
                $allegati['ZIPFILE'] = null;
                $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, $zip);
            }
        } catch (Exception $exc) {
            $errore = true;
        }
        if ($errore) {
            //Salvare cmq lo zip da qualche parte???
            cwbDBRequest::getInstance()->rollBackManualTransaction();
        } else {
            cwbDBRequest::getInstance()->commitManualTransaction();
        }
        unlink($file);
    }

    protected function customEseguiInserimentoSingolo($progkeytabAgidScadenze, $pendenza) {
        // INSERT BGE_AGID_SCA_NSS from BWE_PENDEN_NSS
        $this->inserisciBgeAgidScaNss($pendenza['PROGKEYTAB'], $progkeytabAgidScadenze);
        // INSERT BGE_AGID_SCADETIVA from BWE_PENDDETIVA
        $this->inserisciBgeAgidScadetiva($pendenza['PROGKEYTAB'], $progkeytabAgidScadenze);
    }

    private function getIdRuoloFromResponse($response) {
        return $response['RuoloPosizioniDebitorie']['ID_RUOLO'];
    }

    private function refreshIdRuolo($idRuolo, &$pubblicazione) {
        $pubblicazione['ID_RUOLO'] = $idRuolo;
        unset($pubblicazione['RuoloPosizioniDebitorie']);
//        foreach($pubblicazione['PosizioniDebitorie'] as &$posizione){
//            $posizione->setID_RUOLO($idRuolo);
//        }
    }

    private function aggiornamentoAgid($risposta, $scadenzeDaPubblicare) {
        $esito = true;
        if ($risposta['Esito'] === self::ESITO_POSITIVO) {
            if (isSet($risposta['PosizioniDebitorieResult'])) {
                if (isSet($risposta['PosizioniDebitorieResult']['RiferimentoPraticaEsterna'])) {
                    $pratiche = array($risposta['PosizioniDebitorieResult']);
                } elseif (isSet($risposta['PosizioniDebitorieResult'][0]['RiferimentoPraticaEsterna'])) {
                    $pratiche = $risposta['PosizioniDebitorieResult'];
                }
            }
        } else {
            $pratiche = $scadenzeDaPubblicare;
        }

        try {
            foreach ($pratiche as $posizione) {
                // AGGIORNA BGE_AGID_SCADENZE
                if ($risposta['Esito'] === self::ESITO_POSITIVO) {
                    $rif = $posizione['RiferimentoPraticaEsterna'];
                } else {
                    $rif = $posizione['CODRIFERIMENTO'];
                }
                $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze(array('CODRIFERIMENTO' => $rif), false);
                $invio = $this->getLibDB_BGE()->leggiBgeAgidInvii(array("PROGKEYTAB" => $scadenza['PROGINV']), false);
                if ($risposta['Esito'] === self::ESITO_POSITIVO) {
                    $scadenza['IUV'] = $posizione['IUV'];
                    $scadenza['DATAPAGAM'] = $posizione['DataUltimoPagamento'];
                    $scadenza['STATO'] = 4;
                    $scadenza['DATAPUBBL'] = date('Ymd');

                    // AGGIORNA BGE_AGID_SCA_NSS
                    $scadenzaNSS = $this->getLibDB_BGE()->leggiBgeAgidScaNSS(array('PROGKEYTAB' => $scadenza['PROGKEYTAB']), false);
                    $scadenzaNSS['PROGSEL'] = $posizione['ProgressivoPosizione'];
                    $scadenzaNSS['CODICESTATO'] = $posizione['CodiceStato'];
                    $scadenzaNSS['DESCRIZIONESTATO'] = $posizione['DescrizioneStato'];
                    $scadenzaNSS['CODICESTATOPAGAMENTO'] = $posizione['Stato'];
                    $scadenzaNSS['DESCRSTATOPAGAMENTO'] = $posizione['DescrizioneStatoPagamento'];
                    $scadenzaNSS['IMPORTOPAGATO'] = $posizione['ImportoPagato'];
                    $scadenzaNSS['DATASCADEPAG'] = $posizione['Scadenza'];
                    $scadenzaNSS['IDRUOLO'] = $posizione['ID_RUOLO'];
                    $this->aggiornaBgeScadenzeNSS($scadenzaNSS);

                    // SETTO STATO INVIO
                    $invio['STATO'] = 2;
                } else {
                    // SETTO STATO PER INVIO E SCADENZA SCARTATI
                    $scadenza['STATO'] = 3;
                    $invio['STATO'] = 6;
                }
                //AGGIORNA SCADENZA E INVIO
                $this->aggiornaBgeScadenze($scadenza);
                $this->aggiornaBgeAgidInvii($invio);
            }
        } catch (Exception $exc) {
            $esito = false;
        }
        return $esito;
    }

    private function aggiornaIuv($risposta) {
        if ($risposta['Esito'] !== 'OK') {
            return false;
        }

        $libDB = new cwsLibDB_SBO();
        if (isSet($risposta['PosizioniDebitorieResult'])) {
            if (isSet($risposta['PosizioniDebitorieResult']['RiferimentoPraticaEsterna'])) {
                $pratiche = array($risposta['PosizioniDebitorieResult']);
            } elseif (isSet($risposta['PosizioniDebitorieResult'][0]['RiferimentoPraticaEsterna'])) {
                $pratiche = $risposta['PosizioniDebitorieResult'];
            }
        }
        foreach ($pratiche as $posizione) {
            $rif = $posizione['RiferimentoPraticaEsterna'];
            $libDB->aggiornaIUV(array(
                'IUV' => 'Pubblicato',
                'RiferimentoPraticaEsterna' => $rif));
        }
    }

    private function pubblicazioneDirettaLeggiDatiDaElaborare($annoEmissione, $numeroEmissione, $chiaveServizioEmittente) {
        $libDB = new cwsLibDB_SBO();
        $filtri = array();
        $filtri['ANNOEMI'] = $annoEmissione;
        $filtri['NUMEROEMI'] = $numeroEmissione;
        $filtri['CHIAVESERV'] = $chiaveServizioEmittente;
        return $libDB->leggiDatiPubblicazioneDirettaNss($filtri);
    }

    private function pubblicazioneDirettaAdattaRisultato($datiDaElaborare) {
        if (!is_array($datiDaElaborare) || empty($datiDaElaborare))
            return false;

        $datiElaborati = array();

        $posizioni = array();
        foreach ($datiDaElaborare as $row) {
            foreach ($row as &$value) {
                $value = trim($value);
            }

            if (!isSet($posizioni[$row['Posizione#AnnoImposta'] . $row['Posizione#Numero']])) {
                //ELABORAZIONE DI UNA NUOVA POSIZIONE
                $posizione = new itaPosizioneDebitoriaCw();

                //DATI BASE POSIZIONE
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Descrizione'])) {
                    $posizione->setDescrizione($row['Posizione#Descrizione']);
                }
                $posizione->setAnnoImposta($row['Posizione#AnnoImposta']);
                $posizione->setNumero($row['Posizione#Numero']);
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Sezionale'])) {
                    $posizione->setSezionale($row['Posizione#Sezionale']);
                }
//                if(cwbLibPagoPaUtils::valorized($row['Posizione#Note'])){
//                    $posizione->setNote($row['Posizione#Note']);
//                }
                $posizione->setNote('RIF.PAG. ' . $row['Posizione#RiferimentoPraticaEsterna']);

                if (cwbLibPagoPaUtils::valorized($row['Posizione#RiferimentoPraticaEsterna'])) {
                    $posizione->setRiferimentoPraticaEsterna($row['Posizione#RiferimentoPraticaEsterna']);
                }
                $posizione->setImportoDovuto($row['Posizione#ImportoDovuto']);
                $posizione->setAnnullato($row['Posizione#Annullato'] == 1);
                $posizione->setGestioneIva($row['Posizione#GestioneIva'] == 1);
                $posizione->setNumeroProtocollo($row['Posizione#NumeroProtocollo']);
                $posizione->setDataProtocollo(substr($row['Posizione#DataProtocollo'], 0, 10));
                $posizione->setDataEmissione(substr($row['Posizione#DataEmissione'], 0, 10));
                $posizione->setDataInizioPeriodo(substr($row['Posizione#DataInizioPeriodo'], 0, 10));
                $posizione->setDataFinePeriodo(substr($row['Posizione#DataFinePeriodo'], 0, 10));
                $posizione->setTipoDocumento($row['Posizione#TipoDocumento']);

                //DATI CONTRIBUENTE POSIZIONE
                $contribuente = array();
                $contribuente['NaturaGiuridica'] = $row['Posizione#Contribuente#NaturaGiuridica'];
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#RagioneSociale'])) {
                    $contribuente['RagioneSociale'] = $row['Posizione#Contribuente#RagioneSociale'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Cognome'])) {
                    $contribuente['Cognome'] = $row['Posizione#Contribuente#Cognome'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Nome'])) {
                    $contribuente['Nome'] = $row['Posizione#Contribuente#Nome'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#CodiceFiscale'])) {
                    $contribuente['CodiceFiscale'] = $row['Posizione#Contribuente#CodiceFiscale'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#PartitaIva'])) {
                    $contribuente['PartitaIva'] = $row['Posizione#Contribuente#PartitaIva'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Email'])) {
                    $contribuente['Email'] = $row['Posizione#Contribuente#Email'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#CodiceIsoNazionalita'])) {
                    $contribuente['CodiceIsoNazionalita'] = $row['Posizione#Contribuente#CodiceIsoNazionalita'];
                }

                //RESIDENZA DEL CONTRIBUENTE
                $contribuente['Residenza'] = array();
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#Riferimento'])) {
                    $contribuente['Residenza']['Riferimento'] = $row['Posizione#Contribuente#Residenza#Riferimento'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#Comune'])) {
                    $contribuente['Residenza']['Comune'] = $row['Posizione#Contribuente#Residenza#Comune'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#Localita'])) {
                    $contribuente['Residenza']['Localita'] = $row['Posizione#Contribuente#Residenza#Localita'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#Provincia'])) {
                    $contribuente['Residenza']['Provincia'] = $row['Posizione#Contribuente#Residenza#Provincia'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#CAP']) && $row['Posizione#Contribuente#Residenza#CAP'] != 0) {
                    $contribuente['Residenza']['CAP'] = $row['Posizione#Contribuente#Residenza#CAP'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#Indirizzo'])) {
                    $contribuente['Residenza']['Indirizzo'] = $row['Posizione#Contribuente#Residenza#Indirizzo'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#NumeroCivico'])) {
                    $contribuente['Residenza']['NumeroCivico'] = $row['Posizione#Contribuente#Residenza#NumeroCivico'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#Lettera'])) {
                    $contribuente['Residenza']['Lettera'] = $row['Posizione#Contribuente#Residenza#Lettera'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Residenza#Km'])) {
                    $contribuente['Residenza']['Km'] = $row['Posizione#Contribuente#Residenza#Km'];
                }
                if (empty($contribuente['Residenza']))
                    unset($contribuente['Residenza']);

                //ULTERIORI DATI BASE DEL CONTRIBUENTE
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Codice'])) {
                    $contribuente['Codice'] = $row['Posizione#Contribuente#Codice'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Pec'])) {
                    $contribuente['Pec'] = $row['Posizione#Contribuente#Pec'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Telefono'])) {
                    $contribuente['Telefono'] = $row['Posizione#Contribuente#Telefono'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Cellulare'])) {
                    $contribuente['Cellulare'] = $row['Posizione#Contribuente#Cellulare'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#CodiceIsoCittadinanza'])) {
                    $contribuente['CodiceIsoCittadinanza'] = $row['Posizione#Contribuente#CodiceIsoCittadinanza'];
                }

                //DOMICILIO DEL CONTRIBUENTE
                $contribuente['Domicilio'] = array();
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#Riferimento'])) {
                    $contribuente['Domicilio']['Riferimento'] = $row['Posizione#Contribuente#Domicilio#Riferimento'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#Comune'])) {
                    $contribuente['Domicilio']['Comune'] = $row['Posizione#Contribuente#Domicilio#Comune'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#Localita'])) {
                    $contribuente['Domicilio']['Localita'] = $row['Posizione#Contribuente#Domicilio#Localita'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#Provincia'])) {
                    $contribuente['Domicilio']['Provincia'] = $row['Posizione#Contribuente#Domicilio#Provincia'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#CAP']) && $row['Posizione#Contribuente#Domicilio#CAP'] != 0) {
                    $contribuente['Domicilio']['CAP'] = $row['Posizione#Contribuente#Domicilio#CAP'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#Indirizzo'])) {
                    $contribuente['Domicilio']['Indirizzo'] = $row['Posizione#Contribuente#Domicilio#Indirizzo'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#NumeroCivico'])) {
                    $contribuente['Domicilio']['NumeroCivico'] = $row['Posizione#Contribuente#Domicilio#NumeroCivico'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#Lettera'])) {
                    $contribuente['Domicilio']['Lettera'] = $row['Posizione#Contribuente#Domicilio#Lettera'];
                }
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Contribuente#Domicilio#Km'])) {
                    $contribuente['Domicilio']['Km'] = $row['Posizione#Contribuente#Domicilio#Km'];
                }
                if (empty($contribuente['Domicilio']))
                    unset($contribuente['Domicilio']);

                $posizione->setContribuente($contribuente);

                $rate = array();
                //RATE POSIZIONE
                for ($i = 1; $i <= 5; $i++) {
                    if (cwbLibPagoPaUtils::valorized($row['Posizione#Rata' . $i . '#Importo'])) {
                        $rata = array();
                        $rata['NumeroRata'] = $row['Posizione#Rata' . $i . '#NumeroRata'];
                        $rata['Importo'] = $row['Posizione#Rata' . $i . '#Importo'];
                        $rata['Scadenza'] = substr($row['Posizione#Rata' . $i . '#Scadenza'], 0, 10);
                        if (cwbLibPagoPaUtils::valorized($row['Posizione#Rata' . $i . '#QuintoCampo'])) {
                            $rata['QuintoCampo'] = $row['Posizione#Rata' . $i . '#QuintoCampo'];
                        }
                        if (cwbLibPagoPaUtils::valorized($row['Posizione#Rata' . $i . '#Iuv'])) {
                            $rata['IUV'] = $row['Posizione#Rata' . $i . '#Iuv'];
                        }
                        array_push($rate, $rata);
                    }
                }
                if (empty($rate))
                    unset($rate);

                $posizione->setRate($rate);

                $riepilogoIva = array();
                for ($i = 1; $i <= 5; $i++) {
                    if (cwbLibPagoPaUtils::valorized($row['Posizione#RiepilogoIva' . $i . '#AliquotaIva'])) {
                        $riepilogo = array();
                        $riepilogo['AliquotaIva'] = $row['Posizione#RiepilogoIva' . $i . '#AliquotaIva'];
                        $riepilogo['BaseImponibile'] = $row['Posizione#RiepilogoIva' . $i . '#BaseImponibile'];
                        $riepilogo['Iva'] = $row['Posizione#RiepilogoIva' . $i . '#Iva'];
                        $riepilogo['Dovuto'] = $row['Posizione#RiepilogoIva' . $i . '#Dovuto'];
                        $riepilogoIva[] = $riepilogo;
                    }
                }
                if (!empty($riepilogoIva))
                    $posizione->setRiepilogoIva($riepilogoIva);

                $sezioniParSpec = array();
                $sezioniParSpec[0] = array();
                $sezioniParSpec[0] = array();
                $sezioniParSpec[0]['Sezione'] = $row['Posizione#Sezione'];
                $sezioniParSpec[0]['ParametriSpecifici'] = array();
                for ($i = 1; $i <= 4; $i++) {
                    if (cwbLibPagoPaUtils::valorized($row['Posizione#Sezione#Nome' . $i]) ||
                            cwbLibPagoPaUtils::valorized($row['Posizione#Sezione#Valore' . $i])) {
                        $parametro = array();
                        $parametro['Nome'] = $row['Posizione#Sezione#Nome' . $i];
                        $parametro['Valore'] = $row['Posizione#Sezione#Valore' . $i];
                        $sezioniParSpec[0]['ParametriSpecifici'][] = $parametro;
                    }
                }
                if (!empty($sezioniParSpec[0]['ParametriSpecifici']))
                    $posizione->setParametriSpecifici($sezioniParSpec);

                $posizione->setID_RUOLO($row['Posizione#ID_Ruolo']);
                $posizione->setNomeFileAcquisito($row['Posizione#NomeFileAcquisito']);
                $posizione->setRiferimentoPraticaEsternaPrecedente($row['Posizione#RiferimentoPraticaEsternaPrecedente']);
                $posizione->setDocumento($row['Posizione#Documento']);
                $posizione->setScadenzaSoluzioneUnica($row['Posizione#ScadenzaSoluzioneUnica']);
                $posizione->setRiferimentoRuoloEsterno($row['Posizione#RiferimentoRuoloEsterno']);
            }
            else {
                //ELABORAZIONE DI UNA POSIZIONE GIA' ESISTENTE
                $posizione = $posizioni[$row['Posizione#AnnoImposta'] . $row['Posizione#Numero']];
            }

            $dettaglio = array();
            //$dettaglio['RiferimentoDettaglio'] = count($posizione['Dettagli'])+1;
            $dettaglio['ID_VOCE_DI_COSTO'] = $row['Posizione#Dettaglio#ID_VOCE_DI_COSTO'];
            //DEBUG
            //$dettaglio['ID_VOCE_DI_COSTO'] = 2;
            if (cwbLibPagoPaUtils::valorized($row['Posizione#Dettaglio#NomeVoceDiCosto'])) {
                $dettaglio['NomeVoceDiCosto'] = $row['Posizione#Dettaglio#NomeVoceDiCosto'];
            }

            //INSERIMENTO DETTAGLI FRUITORE
            $dettaglio['Fruitore'] = array();
            if (cwbLibPagoPaUtils::valorized($row['Posizione#Dettaglio#Fruitore#Cognome'])) {
                $dettaglio['Fruitore']['Cognome'] = $row['Posizione#Dettaglio#Fruitore#Cognome'];
            }
            if (cwbLibPagoPaUtils::valorized($row['Posizione#Dettaglio#Fruitore#Nome'])) {
                $dettaglio['Fruitore']['Nome'] = $row['Posizione#Dettaglio#Fruitore#Nome'];
            }
            if (cwbLibPagoPaUtils::valorized($row['Posizione#Dettaglio#Fruitore#CodiceFiscale'])) {
                $dettaglio['Fruitore']['CodiceFiscale'] = $row['Posizione#Dettaglio#Fruitore#CodiceFiscale'];
            }
            if (empty($dettaglio['Fruitore']))
                unset($dettaglio['Fruitore']);

            //INSERIMENTO ALTRI DETTAGLI BASE
            $dettaglio['Quantita'] = $row['Posizione#Dettaglio#Quantita'];
            $dettaglio['Importo'] = $row['Posizione#Dettaglio#Importo'];
            $dettaglio['Iva'] = $row['Posizione#Dettaglio#Iva'];
            //DEBUG
            //$dettaglio['Iva'] = 0;
            if (cwbLibPagoPaUtils::valorized($row['Posizione#Dettaglio#Descrizione'])) {
                $dettaglio['Descrizione'] = $row['Posizione#Dettaglio#Descrizione'];
            }
            $dettaglio['CausaleImporto'] = $row['Posizione#Dettaglio#CausaleImporto'];
            $dettaglio['AnnoCompetenza'] = $row['Posizione#Dettaglio#AnnoCompetenza'];

            $dettaglio['SezioniParametriSpecifici'] = array();
            $dettaglio['SezioniParametriSpecifici']['Sezione'] = $row['Posizione#Dettaglio#Sezione'];
            $dettaglio['SezioniParametriSpecifici']['ParametriSpecifici'] = array();
            for ($i = 1; $i <= 4; $i++) {
                if (cwbLibPagoPaUtils::valorized($row['Posizione#Dettaglio#Sezione#Nome' . $i])) {
                    $parametriSpecifici = array();
                    $parametriSpecifici['ParametroSpecifico'] = array();
                    $parametriSpecifici['ParametroSpecifico']['Nome'] = $row['Posizione#Dettaglio#Sezione#Nome' . $i];
                    $parametriSpecifici['ParametroSpecifico']['Valore'] = $row['Posizione#Dettaglio#Sezione#Valore' . $i];
                    $dettaglio['SezioniParametriSpecifici']['ParametriSpecifici'][] = $parametriSpecifici;
                }
            }
            if (empty($dettaglio['SezioniParametriSpecifici']['ParametriSpecifici']))
                unset($dettaglio['SezioniParametriSpecifici']);
            $dettaglio['AliquotaIva'] = $row['Posizione#Dettaglio#AliquotaIva'];

            $posizione->addDettaglio($dettaglio);

            $posizioni[$row['Posizione#AnnoImposta'] . $row['Posizione#Numero']] = $posizione;
        }
        $datiElaborati['PosizioniDebitorie'] = $posizioni;

        $row = array_shift($datiDaElaborare);

        $datiElaborati['RuoloPosizioniDebitorie'] = array();
        $ruoloPosizioni = array();
        if (cwbLibPagoPaUtils::valorized($row['Ruolo#Descrizione']))
            $ruoloPosizioni['Descrizione'] = $row['Ruolo#Descrizione'];
        $ruoloPosizioni['AnnoImposta'] = $row['Ruolo#AnnoImposta'];
        $dataInizioPeriodo = mktime(0, 0, 0, substr($row['Ruolo#DataInizioPeriodo'], 4, 2), 1, substr($row['Ruolo#DataInizioPeriodo'], 0, 4));
        $dataFinePeriodo = mktime(0, 0, 0, substr($row['Ruolo#DataFinePeriodo'], 4, 2) + 1, 0, substr($row['Ruolo#DataFinePeriodo'], 0, 4));
        $ruoloPosizioni['DataInizioPeriodo'] = date("Y-m-d", $dataInizioPeriodo);
        $ruoloPosizioni['DataFinePeriodo'] = date("Y-m-d", $dataFinePeriodo);
//        if(cwbLibPagoPaUtils::valorized($row['Ruolo#NoteAnno']) && cwbLibPagoPaUtils::valorized($row['Ruolo#NoteNumero']))
//            $ruoloPosizioni['Note'] = $row['Ruolo#NoteAnno'].'/'.$row['Ruolo#NoteNumero'];
        $ruoloPosizioni['TipoDocumento'] = $row['Ruolo#TipoDocumento'];

        if (cwbLibPagoPaUtils::valorized($row['Ruolo#Causale#CausaleImporto']) && cwbLibPagoPaUtils::valorized($row['Ruolo#Causale#Importo'])) {
            $ruoloPosizioni['Ruolo_CausaliImporti'] = array();
            $ruoloPosizioni['Ruolo_CausaliImporti'][0] = array();
            $ruoloPosizioni['Ruolo_CausaliImporti'][0]['CausaleImporto'] = $row['Ruolo#Causale#CausaleImporto'];
            if (cwbLibPagoPaUtils::valorized($row['Ruolo#Causale#Descrizione']))
                $ruoloPosizioni['Ruolo_CausaliImporti'][0]['Descrizione'] = $row['Ruolo#Causale#Descrizione'];
            $ruoloPosizioni['Ruolo_CausaliImporti'][0]['Importo'] = $row['Ruolo#Causale#Importo'];
        }
        $ruoloPosizioni['GestionePDFACaricoDelFornitore'] = ($row['Ruolo#GestionePDFACaricoDelFornitore'] == 1);
        if (cwbLibPagoPaUtils::valorized($row['Ruolo#FileAcquisizionePDF']))
            $ruoloPosizioni['FileAcquisizionePDF'] = $row['Ruolo#FileAcquisizionePDF'];

        $datiElaborati['RuoloPosizioniDebitorie'] = $ruoloPosizioni;

        $datiElaborati['ID_RUOLO'] = $row['ID_RUOLO'];
        $datiElaborati['RiferimentoRuoloEsterno'] = $row['RiferimentoRuoloEsterno'];
        return $datiElaborati;
    }

    private function pubblicazioneDirettaAdattaArrayDati($datiDaElaborare) {
        if (!is_array($datiDaElaborare) || empty($datiDaElaborare))
            return false;
        $datiElaborati = array();

        $posizioni = array();
        foreach ($datiDaElaborare as $row) {
            foreach ($row as &$value) {
                $value = trim($value);
            }
            $bge_agid_sca_nss = $this->getLibDB_BGE()->leggiBgeAgidScaNss(array('PROGKEYTAB' => $row['PROGKEYTAB']), false);
            $bge_agid_scadetiva = $this->getLibDB_BGE()->leggiBgeAgidScadetiva(array('IDSCADENZA' => $row['PROGKEYTAB']));
            $bge_agid_scadet = $this->getLibDB_BGE()->leggiBgeAgidScadet(array('IDSCADENZA' => $row['PROGKEYTAB']));

            if (!isSet($posizioni[$row['CODRIFERIMENTO']])) {
                //ELABORAZIONE DI UNA NUOVA POSIZIONE
                $posizione = new itaPosizioneDebitoriaCw();

                //DATI BASE POSIZIONE
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DESCRIZIONE'])) {
                    $posizione->setDescrizione($bge_agid_sca_nss['DESCRIZIONE']);
                }
                $posizione->setAnnoImposta($row['ANNOEMI']);
                $posizione->setNumero($row['NUMDOC']);
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['SEZIONALE'])) {
                    $posizione->setSezionale($bge_agid_sca_nss['SEZIONALE']);
                }
//                if(cwbLibPagoPaUtils::valorized($row['Posizione#Note'])){
//                    $posizione->setNote($row['Posizione#Note']);
//                }
                $posizione->setNote($bge_agid_sca_nss['NOTE']);

                if (cwbLibPagoPaUtils::valorized($row['CODRIFERIMENTO'])) {
                    $posizione->setRiferimentoPraticaEsterna($row['CODRIFERIMENTO']);
                }
                $posizione->setImportoDovuto(cwbLibOmnis::toOmnisDecimal($row['IMPDAPAGTO'], 2, "."));
                $posizione->setAnnullato($bge_agid_sca_nss['ANNULLATO'] == 1);
                $posizione->setGestioneIva($bge_agid_sca_nss['GESTIONEIVA'] == 1);
                $posizione->setNumeroProtocollo($bge_agid_sca_nss['NUMEROPROT']);
                $posizione->setDataProtocollo(substr($bge_agid_sca_nss['DATAPROT'], 0, 10));
                $posizione->setDataEmissione(substr($bge_agid_sca_nss['DATAEMI'], 0, 10));
                $posizione->setDataInizioPeriodo(substr($bge_agid_sca_nss['DATAINIPER'], 0, 10));
                $posizione->setDataFinePeriodo(substr($bge_agid_sca_nss['DATAFINPER'], 0, 10));
                $posizione->setTipoDocumento($bge_agid_sca_nss['TIPODOC']);

                //DATI CONTRIBUENTE POSIZIONE
                $contribuente = array();
                $contribuente['NaturaGiuridica'] = trim($bge_agid_sca_nss['NATURAGIURIDICA']);
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RAGSOC'])) {
                    $contribuente['RagioneSociale'] = trim($bge_agid_sca_nss['RAGSOC']);
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['COGNOME'])) {
                    $contribuente['Cognome'] = trim($bge_agid_sca_nss['COGNOME']);
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['NOME'])) {
                    $contribuente['Nome'] = trim($bge_agid_sca_nss['NOME']);
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['CODFISCALE'])) {
                    $contribuente['CodiceFiscale'] = trim($bge_agid_sca_nss['CODFISCALE']);
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['PARTIVA'])) {
                    $contribuente['PartitaIva'] = trim($bge_agid_sca_nss['PARTIVA']);
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['EMAIL'])) {
                    $contribuente['Email'] = trim($bge_agid_sca_nss['EMAIL']);
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['CODISNAZIONALITA'])) {
                    $contribuente['CodiceIsoNazionalita'] = trim($bge_agid_sca_nss['CODISNAZIONALITA']);
                }

                //RESIDENZA DEL CONTRIBUENTE
                $contribuente['Residenza'] = array();
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESRIFERIMENTO'])) {
                    $contribuente['Residenza']['Riferimento'] = $bge_agid_sca_nss['RESRIFERIMENTO'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESCOMUNE'])) {
                    $contribuente['Residenza']['Comune'] = $bge_agid_sca_nss['RESCOMUNE'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESLOCALITA'])) {
                    $contribuente['Residenza']['Localita'] = $bge_agid_sca_nss['RESLOCALITA'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESPROVINCIA'])) {
                    $contribuente['Residenza']['Provincia'] = $bge_agid_sca_nss['RESPROVINCIA'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESCAP']) && $bge_agid_sca_nss['RESCAP'] != 0) {
                    $contribuente['Residenza']['CAP'] = $bge_agid_sca_nss['RESCAP'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESINDIRIZZO'])) {
                    $contribuente['Residenza']['Indirizzo'] = $bge_agid_sca_nss['RESINDIRIZZO'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESNUMEROCIVICO'])) {
                    $contribuente['Residenza']['NumeroCivico'] = $bge_agid_sca_nss['RESNUMEROCIVICO'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESLETTERA'])) {
                    $contribuente['Residenza']['Lettera'] = $bge_agid_sca_nss['RESLETTERA'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['RESKM'])) {
                    $contribuente['Residenza']['Km'] = $bge_agid_sca_nss['RESKM'];
                }
                if (empty($contribuente['Residenza']))
                    unset($contribuente['Residenza']);

                //ULTERIORI DATI BASE DEL CONTRIBUENTE
                if (cwbLibPagoPaUtils::valorized($row['PROGSOGG'])) {
                    $contribuente['Codice'] = $row['PROGSOGG'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['PEC'])) {
                    $contribuente['Pec'] = $bge_agid_sca_nss['PEC'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['TELEFONO'])) {
                    $contribuente['Telefono'] = $bge_agid_sca_nss['TELEFONO'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['CELLULARE'])) {
                    $contribuente['Cellulare'] = $bge_agid_sca_nss['CELLULARE'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['CODISCITTADINANZA'])) {
                    $contribuente['CodiceIsoCittadinanza'] = $bge_agid_sca_nss['CODISCITTADINANZA'];
                }

                //DOMICILIO DEL CONTRIBUENTE
                $contribuente['Domicilio'] = array();
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMRIFERIMENTO'])) {
                    $contribuente['Domicilio']['Riferimento'] = $bge_agid_sca_nss['DOMRIFERIMENTO'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMCOMUNE'])) {
                    $contribuente['Domicilio']['Comune'] = $bge_agid_sca_nss['DOMCOMUNE'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMLOCALITA'])) {
                    $contribuente['Domicilio']['Localita'] = $bge_agid_sca_nss['DOMLOCALITA'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMPROVINCIA'])) {
                    $contribuente['Domicilio']['Provincia'] = $bge_agid_sca_nss['DOMPROVINCIA'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMCAP']) && $bge_agid_sca_nss['DOMCAP'] != 0) {
                    $contribuente['Domicilio']['CAP'] = $bge_agid_sca_nss['DOMCAP'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMINDIRIZZO'])) {
                    $contribuente['Domicilio']['Indirizzo'] = $bge_agid_sca_nss['DOMINDIRIZZO'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMNUMEROCIVICO'])) {
                    $contribuente['Domicilio']['NumeroCivico'] = $bge_agid_sca_nss['DOMNUMEROCIVICO'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMLETTERA'])) {
                    $contribuente['Domicilio']['Lettera'] = $bge_agid_sca_nss['DOMLETTERA'];
                }
                if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DOMKM'])) {
                    $contribuente['Domicilio']['Km'] = $bge_agid_sca_nss['DOMKM'];
                }
                if (empty($contribuente['Domicilio']))
                    unset($contribuente['Domicilio']);

                $posizione->setContribuente($contribuente);

                $rate = array();
                //RATE POSIZIONE
                for ($i = 1; $i <= 1; $i++) {
                    if (cwbLibPagoPaUtils::valorized($row['IMPDAPAGTO'])) {
                        $rata = array();
                        $rata['NumeroRata'] = $i;
                        $rata['Importo'] = cwbLibOmnis::toOmnisDecimal($row['IMPDAPAGTO'], 2, ".");
                        $rata['Scadenza'] = substr($row['DATASCADE'], 0, 10);
                        $quintoCampo = "0" . str_pad($row['CODTIPSCAD'], 2, "0", STR_PAD_LEFT) . str_pad($row['SUBTIPSCAD'], 2, "0", STR_PAD_LEFT) .
                                str_pad($row['ANNORIF'], 4, "0", STR_PAD_LEFT) . str_pad($row['PROGCITYSC'], 7, "0", STR_PAD_LEFT) .
                                str_pad($row['NUMRATA'], 2, "0", STR_PAD_LEFT);

                        if (cwbLibPagoPaUtils::valorized($quintoCampo)) {
                            $rata['QuintoCampo'] = $quintoCampo;
                        }
                        if (cwbLibPagoPaUtils::valorized($row['IUV'])) {
                            $rata['IUV'] = $row['IUV'];
                        }
                        array_push($rate, $rata);
                    }
                }
                if (empty($rate))
                    unset($rate);

                $posizione->setRate($rate);

                $riepilogoIva = array();
                foreach ($bge_agid_scadetiva as $scadetiva) {
                    if (cwbLibPagoPaUtils::valorized($scadetiva['IDIVA'])) {
                        $riepilogo = array();
                        $riepilogo['AliquotaIva'] = cwbLibOmnis::toOmnisDecimal($scadetiva['IDIVA'], 2, ".");
                        $riepilogo['BaseImponibile'] = cwbLibOmnis::toOmnisDecimal($scadetiva['BASEIMP'], 2, ".");
                        $riepilogo['Iva'] = cwbLibOmnis::toOmnisDecimal($scadetiva['IVA'], 2, ".");
                        $riepilogo['Dovuto'] = cwbLibOmnis::toOmnisDecimal($scadetiva['IMPOSTA'], 2, ".");
                        $riepilogoIva[] = $riepilogo;
                    }
                }


//                for ($i = 1; $i <= count($bge_agid_scadetiva); $i++) {
//                    if (cwbLibPagoPaUtils::valorized($bge_agid_scadetiva[$i]['IDIVA'])) {
//                        $riepilogo = array();
//                        $riepilogo['AliquotaIva'] = $bge_agid_scadetiva[$i]['IDIVA'];
//                        $riepilogo['BaseImponibile'] = $bge_agid_scadetiva[$i]['BASEIMP'];
//                        $riepilogo['Iva'] = $bge_agid_scadetiva[$i]['IVA'];
//                        $riepilogo['Dovuto'] = $bge_agid_scadetiva[$i]['IMPOSTA'];
//                        $riepilogoIva[] = $riepilogo;
//                    }
//                }
                if (!empty($riepilogoIva))
                    $posizione->setRiepilogoIva($riepilogoIva);
//
//                $sezioniParSpec = array();
//                $sezioniParSpec[0] = array();
//                $sezioniParSpec[0] = array();
//                $sezioniParSpec[0]['Sezione'] = $row['Posizione#Sezione'];
//                $sezioniParSpec[0]['ParametriSpecifici'] = array();
//                for ($i = 1; $i <= 4; $i++) {
//                    if (cwbLibPagoPaUtils::valorized($row['Posizione#Sezione#Nome' . $i]) ||
//                            cwbLibPagoPaUtils::valorized($row['Posizione#Sezione#Valore' . $i])) {
//                        $parametro = array();
//                        $parametro['Nome'] = $row['Posizione#Sezione#Nome' . $i];
//                        $parametro['Valore'] = $row['Posizione#Sezione#Valore' . $i];
//                        $sezioniParSpec[0]['ParametriSpecifici'][] = $parametro;
//                    }
//                }
//                if (!empty($sezioniParSpec[0]['ParametriSpecifici']))
//                    $posizione->setParametriSpecifici($sezioniParSpec);
                $posizione->setID_RUOLO('');
                $posizione->setNomeFileAcquisito('');
                $posizione->setRiferimentoPraticaEsternaPrecedente('');
                $posizione->setDocumento('');
                $posizione->setScadenzaSoluzioneUnica('');
                $posizione->setRiferimentoRuoloEsterno('');
            }
            else {
                //ELABORAZIONE DI UNA POSIZIONE GIA' ESISTENTE
                $posizione = $posizioni[$row['Posizione#AnnoImposta'] . $row['Posizione#Numero']];
            }

            foreach ($bge_agid_scadet as $scadet) {
                $dettaglio = array();
                if (cwbLibPagoPaUtils::valorized($scadet['IDVCOSTO'])) {
                    $dettaglio['ID_VOCE_DI_COSTO'] = $scadet['IDVCOSTO'];
                    //DEBUG
                    //$dettaglio['ID_VOCE_DI_COSTO'] = 2;
//            if (cwbLibPagoPaUtils::valorized($row['Posizione#Dettaglio#NomeVoceDiCosto'])) {
//                $dettaglio['NomeVoceDiCosto'] = '';
//            }
                    //INSERIMENTO DETTAGLI FRUITORE
                    $dettaglio['Fruitore'] = array();
                    if (cwbLibPagoPaUtils::valorized($scadet['COGNOME'])) {
                        $dettaglio['Fruitore']['Cognome'] = trim($scadet['COGNOME']);
                    }
                    if (cwbLibPagoPaUtils::valorized($scadet['NOME'])) {
                        $dettaglio['Fruitore']['Nome'] = trim($scadet['NOME']);
                    }
                    if (cwbLibPagoPaUtils::valorized($scadet['CODFISCALE'])) {
                        $dettaglio['Fruitore']['CodiceFiscale'] = trim($scadet['CODFISCALE']);
                    }
                    if (empty($dettaglio['Fruitore']))
                        unset($dettaglio['Fruitore']);

                    //INSERIMENTO ALTRI DETTAGLI BASE
                    $dettaglio['Quantita'] = cwbLibOmnis::toOmnisDecimal($scadet['QUANTITA'], 2, ".");
                    $dettaglio['Importo'] = cwbLibOmnis::toOmnisDecimal($scadet['IMPORTO'], 2, ".");
                    $dettaglio['Iva'] = cwbLibOmnis::toOmnisDecimal($scadet['IVA'], 2, ".");
                    //DEBUG
                    //$dettaglio['Iva'] = 0;
                    if (cwbLibPagoPaUtils::valorized($scadet['DESCRIZIONE'])) {
                        $dettaglio['Descrizione'] = $scadet['DESCRIZIONE'];
                    }
                    $dettaglio['CausaleImporto'] = $scadet['CAUSALEIMPORTO'];
                    $dettaglio['AnnoCompetenza'] = $scadet['ANNOCOMP'];

                    // TODO da GESTIRE
                    $dettaglio['SezioniParametriSpecifici'][] = array();
                    $dettaglio['SezioniParametriSpecifici'][0]['Sezione'] = 'Parametri Specifici';
                    //$dettaglio['SezioniParametriSpecifici'][0]['Sezione'] = 'DATI_LAMPADE_VOTIVE';
                    $dettaglio['SezioniParametriSpecifici'][0]['ParametriSpecifici'] = array();
                    for ($i = 1; $i <= 5; $i++) {
                        //if (cwbLibPagoPaUtils::valorized($scadet['Posizione#Dettaglio#Sezione#Nome' . $i])) {
                        if (cwbLibPagoPaUtils::valorized($scadet['PAR' . $i . 'NOME'])) {
                            $parametriSpecifici = array();
                            //$parametriSpecifici['ParametroSpecifico'] = array();
                            // $parametriSpecifici['ParametroSpecifico']['Nome'] = $scadet['PAR' . $i . 'NOME'];
                            // $parametriSpecifici['ParametroSpecifico']['Valore'] = $scadet['PAR' . $i . 'VALORE'];
                            $parametriSpecifici['ent:Nome'] = trim($scadet['PAR' . $i . 'NOME']);
                            $parametriSpecifici['ent:Valore'] = trim($scadet['PAR' . $i . 'VALORE']);
                            $dettaglio['SezioniParametriSpecifici'][0]['ParametriSpecifici'][] = $parametriSpecifici;
                        }
                    }
                    if (empty($dettaglio['SezioniParametriSpecifici'][0]['ParametriSpecifici']))
                        unset($dettaglio['SezioniParametriSpecifici']);
                    $dettaglio['AliquotaIva'] = $row['Posizione#Dettaglio#AliquotaIva'];
//
                    $posizione->addDettaglio($dettaglio);
//
                    $posizioni[$row['CODRIFERIMENTO']] = $posizione;
                }
            }

            //$dettaglio['RiferimentoDettaglio'] = count($posizione['Dettagli'])+1;
        }
        $datiElaborati['PosizioniDebitorie'] = $posizioni;

        $row = array_shift($datiDaElaborare);
        $filtri = array();
        $filtri['IDBOL_SERE'] = $row['IDBOL_SERE'];
        $filtri['ANNOEMI'] = $row['ANNOEMI'];
        $filtri['NUMEMI'] = $row['NUMEMI'];
        $emissione = $this->getLibDB_BTA()->leggiBtaEmi($filtri, false);
        $datiElaborati['RuoloPosizioniDebitorie'] = array();
        $ruoloPosizioni = array();
        if (cwbLibPagoPaUtils::valorized($bge_agid_sca_nss['DESCRIZIONE']))
            $ruoloPosizioni['Descrizione'] = $bge_agid_sca_nss['DESCRIZIONE'];
        $ruoloPosizioni['AnnoImposta'] = $row['ANNOEMI'];
        $dataInizioPeriodo = mktime(0, 0, 0, substr($emissione['RUOLODA'], 4, 2), 1, substr($emissione['RUOLODA'], 0, 4));
        $dataFinePeriodo = mktime(0, 0, 0, substr($emissione['RUOLOA'], 4, 2) + 1, 0, substr($emissione['RUOLOA'], 0, 4));
        $ruoloPosizioni['DataInizioPeriodo'] = date("Y-m-d", $dataInizioPeriodo);
        $ruoloPosizioni['DataFinePeriodo'] = date("Y-m-d", $dataFinePeriodo);
//        if(cwbLibPagoPaUtils::valorized($row['Ruolo#NoteAnno']) && cwbLibPagoPaUtils::valorized($row['Ruolo#NoteNumero']))
//            $ruoloPosizioni['Note'] = $row['Ruolo#NoteAnno'].'/'.$row['Ruolo#NoteNumero'];
        // $ruoloPosizioni['TipoDocumento'] = $row['Ruolo#TipoDocumento'];
        $ruoloPosizioni['TipoDocumento'] = 'Avviso';

//        if (cwbLibPagoPaUtils::valorized($row['Ruolo#Causale#CausaleImporto']) && cwbLibPagoPaUtils::valorized($row['Ruolo#Causale#Importo'])) {
//            $ruoloPosizioni['Ruolo_CausaliImporti'] = array();
//            $ruoloPosizioni['Ruolo_CausaliImporti'][0] = array();
//            $ruoloPosizioni['Ruolo_CausaliImporti'][0]['CausaleImporto'] = $row['Ruolo#Causale#CausaleImporto'];
//            if (cwbLibPagoPaUtils::valorized($row['Ruolo#Causale#Descrizione']))
//                $ruoloPosizioni['Ruolo_CausaliImporti'][0]['Descrizione'] = $row['Ruolo#Causale#Descrizione'];
//            $ruoloPosizioni['Ruolo_CausaliImporti'][0]['Importo'] = $row['Ruolo#Causale#Importo'];
//        }
        // todo controllare
        $ruoloPosizioni['GestionePDFACaricoDelFornitore'] = 0;
//        if (cwbLibPagoPaUtils::valorized($row['Ruolo#FileAcquisizionePDF']))
//            $ruoloPosizioni['FileAcquisizionePDF'] = $row['Ruolo#FileAcquisizionePDF'];
//
        $datiElaborati['RuoloPosizioniDebitorie'] = $ruoloPosizioni;

//        $datiElaborati['ID_RUOLO'] = $row['ID_RUOLO'];
        $datiElaborati['ID_RUOLO'] = '';
//        $datiElaborati['RiferimentoRuoloEsterno'] = $row['RiferimentoRuoloEsterno'];
        $datiElaborati['RiferimentoRuoloEsterno'] = '';
        return $datiElaborati;
    }

    private function paginaDatiElaborati($dati, $paginaElementi = 100) {
        $return = array();
        $posizioni = $dati['PosizioniDebitorie'];
        $arrayPosizioni = array_chunk($posizioni, $paginaElementi);
        foreach ($arrayPosizioni as $value) {
            $chunk = array();
            $chunk['PosizioniDebitorie'] = $value;
            foreach ($dati as $key => $value2) {
                if ($key != 'PosizioniDebitorie') {
                    $chunk[$key] = $value2;
                }
            }
            $return[] = $chunk;
        }
        return $return;
    }

    private function pubblicazioneDirettaInvocaWs($dati, &$esito, &$messaggio, $wsClient) {
        // Azzera esito e messaggio di ritorno
        $esito = true;
        $messaggio = '';

        $ret = $wsClient->ws_InserisciRuoloPosizioniCw($dati, true);
        if (!$ret) {
            $esito = false;
            if ($wsClient->getFault()) {
                $messaggio = $wsClient->getFault();
            } elseif ($WSClient->getError()) {
                $messaggio = $wsClient->getError();
            } else {
                $messaggio = $wsClient->getResult();
            }
            return;
        }
        $result = $wsClient->getResult();

        return $result;
    }

//    private function pubblicazioneDirettaSalvaRisposta($risposta, $esito, $messaggio) {
//        // todo salvataggio su tabella Agid o SBO
//        foreach ($risposta as $value) {
//            $sqlParams = array();
//            $sqlParams[] = array('name' => 'IUV', 'value' => $value['IUV'], 'type' => PDO::PARAM_STR);
//            $sqlParams[] = array('name' => 'KSCADENZA', 'value' => $value['RiferimentoPraticaEsterna'], 'type' => PDO::PARAM_INT);
//            //UPDATE su BGE_AGID_SCADENZE 
//            if (!$this->getSimulazione()) {
//                $sqlString = "UPDATE SBO_BOLLS SET IUV=:IUV WHERE KSCADENZA=:KSCADENZA";
//                $this->getCITYWARE_DB()->query($sqlString, $sqlParams);
//            }
//        }
//    }

    private function pubblicazioneDirettaSalvaIdPerRecuperoIUV($risposta) {
        if (isSet($risposta['Esito']) && $risposta['Esito'] == 'OK') {
            if (isSet($risposta['PosizioniDebitorieResult']['RuoloPosizioniDebitorie']['ID_RUOLO'])) {
                $libDB = new cwsLibDB_SBO();

                $libDB->scriviRigaIdRuoloNss($risposta['PosizioniDebitorieResult']['RuoloPosizioniDebitorie']['ID_RUOLO']);
            }
        }
    }

    public function leggiIUV() {
        $wsClient = $this->getWsClient();
        $param = array();
        $ret = $wsClient->ws_Login($param);
        if (!$ret) {
            $esito = false;
            if ($wsClient->getFault()) {
                $messaggio = $wsClient->getFault();
            } elseif ($wsClient->getError()) {
                $messaggio = $wsClient->getError();
            }
            return;
        }
        $ret = $wsClient->getResult();
        if ($ret['Esito'] == "OK") {
            $token = $ret['TokenAuth'];
        } else {
            $esito = false;
            $messaggio = 'E\' stato riscontrato il seguente errore in fase di Login';
            return;
        }

        // Effettua chiamata a ws specifico
        $wsClient->setToken($token);

        //Carico i ruoli da leggere dal DB
        $libDB = new cwsLibDB_SBO();
        $ruoli = $libDB->leggiRigheIdRuoloNss(array());

        foreach ($ruoli as $ruolo) {
            $ruolo = $ruolo['IDRUOLO'];
            $param = array('ID_RUOLO' => $ruolo);

            $ret = $wsClient->ws_RiceviRuoloIUV($param);
            if (!$ret) {
                $esito = false;
                if ($wsClient->getFault()) {
                    $messaggio = $wsClient->getFault();
                } elseif ($wsClient->getError()) {
                    $messaggio = $wsClient->getError();
                }
                return;
            }

            $ret = $wsClient->getResult();
            if ($ret['Esito'] != 'OK') {
                continue;
            }

            if (isSet($ret['InformazioniIUVRuolo']['InformazioniIUVRuolo']['RiferimentoDocumento'])) {
                $informazioniIuvRuolo = array($ret['InformazioniIUVRuolo']['InformazioniIUVRuolo']);
            } elseif (isSet($ret['InformazioniIUVRuolo']['InformazioniIUVRuolo'][0]['RiferimentoDocumento'])) {
                $informazioniIuvRuolo = $ret['InformazioniIUVRuolo']['InformazioniIUVRuolo'];
            }

            foreach ($informazioniIuvRuolo as $posizione) {
                $params = array(
                    'RiferimentoPraticaEsterna' => $posizione['RiferimentoPraticaEsterna'],
                    'IUV' => $posizione['IUV']);

                //Controlla cosa ritorna il metodo per l'update
                $libDB->aggiornaIuv($params);
            }

            $libDB->eliminaRigaIdRuoloNss($ruolo);
        }
    }

    protected function customRecuperaRicevutaPagamento($iuv, $arricchita) {
        
    }

    protected function customCancellazioneMassiva() {
        
    }

    protected function customGetDatiPagamentoDaIUV($IUV) {
        
    }

    public function rettificaPosizioneDaIUV($IUV, $toUpdate) {
        //ws_AggiornaPosizione
    }

    protected function customRimuoviPosizione($params) {
        //ws_AnnullaPosizioneDebitoria
        $conf = $this->getConfIntermediario();

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);

//        if ($params['CodiceIdentificativo']) {
//            $paramWs['TipoChiaveApplicativa'] = 'IUV';
//            $paramWs['ChiaveApplicativa'] = $params['CodiceIdentificativo'];
//            $qryParam['IUV'] = $params['CodiceIdentificativo'];
//        } else if ($params['Progcitysc']) {
//            $paramWs['TipoChiaveApplicativa'] = 'Gestionale';
//            $paramWs['ChiaveApplicativa'] = $params['Progcitysc'];
//            $qryParam['PROGCITYSC'] = $params['Progcitysc'];
//        }

        $paramWs['TipoChiaveApplicativa'] = 'Gestionale';
        $paramWs['ChiaveApplicativa'] = $params['CodRiferimento'];
        $qryParam['CODRIFERIMENTO'] = $params['CodRiferimento'];
        $result = $wsClient->ws_AnnullaPosizioneDebitoria($paramWs);

        if ($result) {
            $result = $wsClient->getResult();
            if ($result['Esito'] == self::ESITO_POSITIVO) {
                $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($qryParam, false);
                $scadenza['STATO'] = 7;
                $this->aggiornaBgeScadenze($scadenza, false);

                return true;
            } else if ($result['Esito'] == self::ESITO_NEGATIVO) {
                $this->handleError(-1, $result['Descrizione']);
            }
        } else {
            $this->handleError(-1, $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription());
        }
//
        return false;
    }

    public function ricercaPosizioneDaIUV($IUV) {
        $conf = $this->getConfIntermediario();

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);
        $paramWs['TipoChiaveApplicativa'] = 'IUV';
        $paramWs['ChiaveApplicativa'] = $IUV;
        $result = $wsClient->ws_VerificaPosizione($paramWs);
        // TODO PORTARE A STANDARD LA RISPOSTA DEL WS DI RICERCA
        if ($result) {
            $result = $wsClient->getResult();
            if ($result['Esito'] == self::ESITO_POSITIVO) {
                return $result;
            } else if ($result['Esito'] == self::ESITO_NEGATIVO) {
                $this->handleError(-1, $result['Descrizione']);
            }
        } else {
            $this->handleError(-1, $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription());
        }
//
        return false;
    }

    protected function customRicevutaAccettazionePubblicazione() {
        
    }

    protected function customRendicontazione($params = array()) {
        return;
        $conf = $this->getConfIntermediario();

        if (!$conf) {
            $this->handleError(-1, "Errore caricamento parametri");
            return false;
        }

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);

        // todo manca parte per settare date da passare a ws
        $riscossioni = array();
        $riscossioni = $this->getLibDB_BGE()->leggiBgeAgidRiscoRendNSS();
        if ($riscossioni) {
            //Se presente almeno una riscossione in BGE_AGID_RISCO, prendere la datains ordinata in modo discendente come data iniziale. 
            $params['DataContabileIniziale'] = date('Y-m-d', strtotime($riscossioni[0]['DATAINS']));
        } else {
            $scadenze = array();
            $scadenze = $this->getLibDB_BGE()->leggiBgeAgidScadenzeRendNSS();
            $params['DataContabileIniziale'] = date('Y-m-d', strtotime($scadenze[0]['DATAPUBBL']));
        }
        $params['DataContabileFinale'] = date('Y-m-d');
        $result = $wsClient->ws_RiceviRendicontazionePagamenti($params);

        if ($result) {
            $result = $wsClient->getResult();
            if ($result['Esito'] == self::ESITO_POSITIVO) {
                if (!$this->gestioneRendicontazione($result, $msgError)) {
                    $errore = true;
                }
            } elseif ($result['Esito'] == self::ESITO_NEGATIVO) {
                $errore = true;
                $msgError .= "Errore Rendicontazione Pagamenti: " . '' . $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription() . "\n";
            }
        } else {
            $errore = true;
            $msgError .= "Errore Rendicontazione Pagamenti: " . '' . $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription() . "\n";
        }
        if ($errore) {
            $this->handleError(-1, $msgError);
            return false;
        }
        return true;
    }

    protected function gestioneRendicontazione($result, &$msgError) {
        // todo da implementare
        $errore = false;
        cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
        if (intval($result['NumeroTotalePosizioni']) > 0) {
            try {
                foreach ($result['RendicontazionePagamenti']['RendicontazionePagamenti'] as $key => $rendi) {
                    $riscossione = $this->getLibDB_BGE()->leggiBgeAgidRisco(array('IUV' => $rendi['Iuv']));
                    if ($riscossione) {
                        unset($result['RendicontazionePagamenti']['RendicontazionePagamenti'][$key]);
                    }
                }

                if (isset($result['RendicontazionePagamenti']['RendicontazionePagamenti'])) {
                    $progintric = 0;

                    // Salvo record Ricezione
                    $ricezione = array(
                        "TIPO" => 15,
                        "INTERMEDIARIO" => itaPagoPa::NEXTSTEPSOLUTION_TYPE,
                        "CODSERVIZIO" => 0,
                        "DATARIC" => date('Ymd'),
                        "IDINV" => null,
                    );
                    $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                    $nomeFile = 'RendicontazioneNSS_' . $progkeytabRicez;

                    $file = itaLib::getUploadPath() . "/" . $nomeFile;

                    $zip = new ZipArchive();
                    if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                        return false;
                    }
                    $xml = cwbLibCalcoli::arrayToXml($result['RendicontazionePagamenti']['RendicontazionePagamenti']);

                    $zip->addFromString($nomeFile, $xml);

                    $zip->close();

                    // Costruisco array per salvataggio allegato
                    $allegati = array(
                        'TIPO' => 15,
                        'IDINVRIC' => $progkeytabRicez,
                        'DATA' => date('Ymd'),
                        'NOME_FILE' => $nomeFile,
                    );

                    // Salvataggio allegati su disco o db
                    $devLib = new devLib();
                    $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);
                    if (intval($configGestBin['CONFIG']) === 0) {
                        //INSERT su BGE_AGID_ALLEGATI
                        $allegati['ZIPFILE'] = file_get_contents($file);
                        $this->insertBgeAgidAllegati($allegati);
                    } else {
                        $allegati['ZIPFILE'] = null;
                        $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                        $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                        $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($file), $nomeAllegato);
                    }


                    foreach ($result['RendicontazionePagamenti']['RendicontazionePagamenti'] as $rendicontazione) {
                        $filtri = array();
                        $filtri['CODRIFERIMENTO'] = $rendicontazione['RiferimentoPraticaEsterna'];
                        $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                        if ($scadenza) {
                            $progintric++;
                            $toUpdate['PROGKEYTAB'] = $scadenza['PROGKEYTAB'];
                            $toUpdate['STATO'] = 10;
                            $toUpdate['DATAPAGAM'] = $rendicontazione['DataVersamento'];
                            $this->aggiornaBgeScadenze($toUpdate);

                            // Inserisco Riscossione BGE_AGID_RISCO
                            if (intval(trim(strlen($rendicontazione['CodiceFiscale']))) === 16) {
                                $tipopers = 'F';
                            } else {
                                $tipopers = 'G';
                            }
                            $riscossione = array(
                                "IDSCADENZA" => $scadenza['PROGKEYTAB'],
                                "PROGRIC" => $progkeytabRicez,
                                "PROGINTRIC" => $progintric,
                                "IUV" => $rendicontazione['Iuv'],
                                "PROVPAGAM" => 2,
                                "IMPPAGATO" => $rendicontazione['Importo'],
                                "DATAPAG" => $rendicontazione['DataVersamento'],
                                "DATAINS" => date('Ymd'),
                                "TIPOPERS" => $tipopers
                            );
                            $progkeytabRisco = $this->insertBgeAgidRisco($riscossione);

                            // Inserimento record su tabella riscossione NSS
                            $this->scriviRiscoNSS($progkeytabRisco, $rendicontazione);
                        }
                    }

                    // elimino file da disco
                    unlink($file);
                }
            } catch (Exception $exc) {
                $error = true;
                $msgError .= $exc->getMessage();
            }
        }

        if (!$error) {
            cwbDBRequest::getInstance()->commitManualTransaction();
            return true;
        } else {
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            return false;
        }
    }

    protected function scriviRiscoNSS($progkeytabRisco, $rendicontazione) {
        // Inserisco Riscossione NSS BGE_AGID_RISCO_NSS
        $risco_nss = array();
        $risco_nss['PROGKEYTAB'] = $progkeytabRisco;
        $risco_nss['ID_PAGAMENTO'] = trim($rendicontazione['ID_PAGAMENTO']);
        $risco_nss['PROVENIENZA'] = trim($rendicontazione['Provenienza']);
        $risco_nss['MODALITA'] = trim($rendicontazione['Modalita']);
        $risco_nss['ANNOIMPOSTA'] = trim($rendicontazione['AnnoImposta']);
        if ($rendicontazione['DataVersamento']) {
            $DataVersamento = date('Ymd', strtotime($rendicontazione['DataVersamento']));
        }
        if ($rendicontazione['DataRiversamento']) {
            $DataRiversamento = date('Ymd', strtotime($rendicontazione['DataRiversamento']));
        }
        if ($rendicontazione['DataAccredito']) {
            $DataAccredito = date('Ymd', strtotime($rendicontazione['DataAccredito']));
        }
        if ($rendicontazione['DataConsolidamento']) {
            $DataConsolidamento = date('Ymd', strtotime($rendicontazione['DataConsolidamento']));
        }
        if ($rendicontazione['DataScadenza']) {
            $DataScadenza = date('Ymd', strtotime($rendicontazione['DataScadenza']));
        }
        if ($rendicontazione['DataInserimento']) {
            $DataInserimento = date('Ymd', strtotime($rendicontazione['DataInserimento']));
        }

        if (strlen(trim($DataVersamento)) === 8) {
            $risco_nss['DATAVERSAMENTO'] = trim($DataVersamento);
        }
        if (strlen(trim($DataRiversamento)) === 8) {
            $risco_nss['DATARIVERSAMENTO'] = trim($DataRiversamento);
        }
        if (strlen(trim($DataAccredito)) === 8) {
            $risco_nss['DATAACCREDITO'] = trim($DataAccredito);
        }
        if (strlen(trim($DataConsolidamento)) === 8) {
            $risco_nss['DATACONSOLIDAMENTO'] = trim($DataConsolidamento);
        }
        if (strlen(trim($DataScadenza)) === 8) {
            $risco_nss['DATASCADENZA'] = trim($DataScadenza);
        }
        if (strlen(trim($DataInserimento)) === 8) {
            $risco_nss['DATAINSERIMENTO'] = trim($DataInserimento);
        }
        $risco_nss['CASSA'] = trim($rendicontazione['Cassa']);
        $risco_nss['TIPOPAGAMENTO'] = trim($rendicontazione['TipoPagamento']);
        $risco_nss['ID_FLUSSO'] = trim($rendicontazione['ID_FLUSSO']);
        $risco_nss['QUIETANZA'] = trim($rendicontazione['Quietanza']);
        if (trim($rendicontazione['Annullato']) === 'false') {
            $annullato = 0;
        } else {
            $annullato = 1;
        }
        $risco_nss['ANNULLATO'] = $annullato;
        $risco_nss['CONTOCORRENTE'] = trim($rendicontazione['ContoCorrente']);
        $risco_nss['IBAN'] = trim($rendicontazione['IBAN']);
        $this->inserisciBgeAgidRiscoNSS($risco_nss);
    }

    protected function customRicevutaArricchita() {
        $conf = $this->getConfIntermediario();

        if (!$conf) {
            $this->handleError(-1, "Errore caricamento parametri");
            return false;
        }

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);
        // Select che estrae su tutti gli idRuolo legati a degli invii con stato = 4
        $idRuoli = $this->getLibDB_BGE()->leggiBgeAgidInviiIdRuolo(array('STATO' => 4));
        if ($idRuoli) {
            foreach ($idRuoli as $idRuolo) {
                $idRuolo = $idRuolo['IDRUOLO'];
                $param = array('ID_RUOLO' => $idRuolo);

                $result = $wsClient->ws_RiceviRuoloIUV($param);

                if ($result) {
                    $result = $wsClient->getResult();
                    if ($result['Esito'] == self::ESITO_POSITIVO) {
                        if (!$this->gestioneArricchimento($result, $idRuolo, $msgError)) {
                            $errore = true;
                        }
                    } else if ($result['Esito'] == self::ESITO_NEGATIVO) {
                        $errore = true;
                        $msgError .= "IDRUOLO: " . $idRuolo . '' . $result['Descrizione'] . "\n";
                    }
                } else {
                    $errore = true;
                    $msgError .= "IDRUOLO: " . $idRuolo . '' . $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription() . "\n";
                }
            }
        } else {
            // Ok ma senza elaborazione dati
            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 13,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        if ($errore) {
            // Salvo errore su log
            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 3,
                    "OPERAZIONE" => 13,
                    "ESITO" => 3,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
            $this->handleError(-1, $msgError);
            return false;
        }

        // OK
        if ($this->getSimulazione() != true) {
            $log = array(
                "LIVELLO" => 5,
                "OPERAZIONE" => 13,
                "ESITO" => 1,
                "KEYOPER" => $progkeytabRicez,
            );
            $this->scriviLog($log, true);
        }
        return true;
    }

    protected function gestioneArricchimento($result, $idRuolo, &$msgError) {
        $errore = false;
        $datiInvio = $this->getLibDB_BGE()->leggiBgeAgidInviiDaIdRuolo(array('IDRUOLO' => $idRuolo), false);
        if (!$datiInvio) {
            $errore = true;
            $msgError .= " Errore: Reperimento Dati Invio Fallito.";
        } else {
            cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
            try {
                $nomeFile = 'PubblicazioneNSS_' . $datiInvio['PROGKEYTAB'];

                $file = itaLib::getUploadPath() . "/" . $nomeFile;

                $zip = new ZipArchive();
                if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    return false;
                }
                $xml = cwbLibCalcoli::arrayToXml($result);

                $zip->addFromString($nomeFile, $xml);

                $zip->close();

                // Aggiungo record su Ricezioni
                $ricezione = array(
                    "TIPO" => 13,
                    "INTERMEDIARIO" => $datiInvio['INTERMEDIARIO'],
                    "CODSERVIZIO" => $datiInvio['CODSERVIZIO'],
                    "DATARIC" => date('Ymd'),
                    "IDINV" => $datiInvio['PROGKEYTAB'],
                );
                $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                // Costruisco array per salvataggio allegato
                $allegati = array(
                    'TIPO' => 13,
                    'IDINVRIC' => $progkeytabRicez,
                    'DATA' => date('Ymd'),
                    'NOME_FILE' => $nomeFile,
                );

                if (intval($configGestBin['CONFIG']) === 0) {
                    //INSERT su BGE_AGID_ALLEGATI
                    $allegati['ZIPFILE'] = file_get_contents($file);
                    $this->insertBgeAgidAllegati($allegati);
                } else {
                    $allegati['ZIPFILE'] = null;
                    $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                    $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                    $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, $zipFile, $nomeAllegato);
                }

                // Aggiorno Avvio con Stato = Pubblicato con IUV
                $this->updateBgeAgidInvii($datiInvio['PROGKEYTAB'], 5);

                foreach ($result['InformazioniIUVRuolo']['InformazioniIUVRuolo'] as $IUVRuolo) {
                    $filtri['CODRIFERIMENTO'] = trim($IUVRuolo['RiferimentoPraticaEsterna']);
                    $scadenza = $this->getLibDB_BGE()->leggiBgeAgidScadenze($filtri, false);
                    if ($scadenza) {

                        // Aggiorno scadenze con STATO = Pubblicato con IUV
                        $scadenza['STATO'] = 5;
                        $scadenza['IUV'] = $IUVRuolo['IUV'];
                        $this->aggiornaBgeScadenze($scadenza);
                    } else {
                        $errore = true;
                        $msgError .= " Errore: Reperimento Dati Scadenza con Riferimento: $codRiferimento";
                    }
                }

                //Faccio select per capire se per l'invio, ci sono ancora scadenze rimaste con stato = 4, in quel caso significa che sono da scartare
                // quindi aggiorno STATO = 3 e pulisco lo IUV provvisorio
                $scadenzeScartate = $this->getLibDB_BGE()->leggiBgeAgidScadenzeArricchimentoNSS(array("PROGINV" => $datiInvio['PROGKEYTAB']));
                if ($scadenzeScartate) {
                    foreach ($scadenzeScartate as $scadScartata) {
                        $scadScartata['STATO'] = 3;
                        $scadScartata['IUV'] = ' ';
                        $this->aggiornaBgeScadenze($scadScartata);
                    }
                }
            } catch (Exception $exc) {
                $error = true;
                $msgError .= $exc->getMessage();
            }
        }
        unlink($file);
        if (!$error) {
            cwbDBRequest::getInstance()->commitManualTransaction();
            return true;
        } else {
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            return false;
        }
    }

    protected function customRicevutaPubblicazione() {
        $msgError = '';
        $errore = false;

        $conf = $this->getConfIntermediario();

        if (!$conf) {
            $this->handleError(-1, "Errore caricamento parametri");
            return false;
        }

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);

        $idRuoli = $this->getLibDB_BGE()->leggiBgeAgidInviiIdRuolo(array('STATO' => 2));
        if ($idRuoli) {
            foreach ($idRuoli as $idRuolo) {
                $idRuolo = $idRuolo['IDRUOLO'];
                $param = array('ID_RUOLO' => $idRuolo);
                $result = $wsClient->ws_VerificaStatoRuolo($param);
                if ($result) {
                    $result = $wsClient->getResult();
                    if ($result['Esito'] == self::ESITO_POSITIVO) {
                        if (!$this->gestioneRicPubblicazione($result, $idRuolo, $msgError)) {
                            $errore = true;
                        }
                    } else if ($result['Esito'] == self::ESITO_NEGATIVO) {
                        $errore = true;
                        $msgError .= "IDRUOLO: " . $idRuolo . '' . $result['Descrizione'] . "\n";
                    }
                } else {
                    $errore = true;
                    $msgError .= "IDRUOLO: " . $idRuolo . '' . $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription() . "\n";
                }
            }
        } else {
            // Ok ma senza elaborazione dati
            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 5,
                    "OPERAZIONE" => 12,
                    "ESITO" => 2,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
        }
        if ($errore) {

            // Ko
            if ($this->getSimulazione() != true) {
                $log = array(
                    "LIVELLO" => 3,
                    "OPERAZIONE" => 12,
                    "ESITO" => 3,
                    "KEYOPER" => 0,
                );
                $this->scriviLog($log);
            }
            $this->handleError(-1, $msgError);
            return false;
        }

        // Ok
        if ($this->getSimulazione() != true) {
            $log = array(
                "LIVELLO" => 5,
                "OPERAZIONE" => 12,
                "ESITO" => 1,
                "KEYOPER" => $progkeytabRicez,
            );
            $this->scriviLog($log, true);
        }
        return true;
    }

    protected function gestioneRicPubblicazione($result, $idRuolo, &$msgError) {
        $errore = false;
        $datiInvio = $this->getLibDB_BGE()->leggiBgeAgidInviiDaIdRuolo(array('IDRUOLO' => $idRuolo), false);
        if (!$datiInvio) {
            $errore = true;
            $msgError .= " Errore: Reperimento Dati Invio Fallito.";
        } else {
            // SE APPROVATO O POSTALIZZATO, Aggiorno invio con stato = 5
            if (($result['StatoRuolo'] === 'Approvato' || $result['StatoRuolo'] === 'Postalizzato') && intval($result['PosizioniApprovate']) > 0) {
                $nomeFile = 'PubblicazioneNSS_' . $datiInvio['PROGKEYTAB'];

                $file = itaLib::getUploadPath() . "/" . $nomeFile;

                $zip = new ZipArchive();
                if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    return false;
                }
                $xml = cwbLibCalcoli::arrayToXml($result);

                $zip->addFromString($nomeFile, $xml);

                $zip->close();

                cwbDBRequest::getInstance()->startManualTransaction(null, $this->getCITYWARE_DB());
                try {

                    //reperimento dati invio
                    if ($datiInvio) {
                        // Salvataggio record su BGE_AGID_RICEZ
                        $ricezione = array(
                            "TIPO" => 12,
                            "INTERMEDIARIO" => $datiInvio['INTERMEDIARIO'],
                            "CODSERVIZIO" => $datiInvio['CODSERVIZIO'],
                            "DATARIC" => date('Ymd'),
                            "IDINV" => $datiInvio['PROGKEYTAB'],
                        );
                        $progkeytabRicez = $this->insertBgeAgidRicez($ricezione);

                        // Salvataggio record su BGE_AGID_ALLEGATI
                        $devLib = new devLib();
                        $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);

                        // Costruisco array per salvataggio allegato
                        $allegati = array(
                            'TIPO' => 12,
                            'IDINVRIC' => $progkeytabRicez,
                            'DATA' => date('Ymd'),
                            'NOME_FILE' => $nomeFile,
                        );

                        if (intval($configGestBin['CONFIG']) === 0) {
                            //INSERT su BGE_AGID_ALLEGATI
                            $allegati['ZIPFILE'] = file_get_contents($file);
                            $this->insertBgeAgidAllegati($allegati);
                        } else {
                            $allegati['ZIPFILE'] = null;
                            $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                            $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                            $this->salvaAllegato($configPathAllegati, $progkeytabAlleg, file_get_contents($pathZip), $nomeAllegato);
                        }

                        // update stato =4 su invio!
                        $datiInvio['STATO'] = 4;
                        $this->aggiornaBgeAgidInvii($datiInvio);
                    } else {
                        $errore = true;
                        $msgError .= " Errore: Reperimento Dati Invio Fallito.";
                    }
                } catch (Exception $ex) {
                    $error = true;
                    $msgError .= $ex->getMessage();
                }
            }
        }
        unlink($file);
        if (!$error) {
            cwbDBRequest::getInstance()->commitManualTransaction();
            return true;
        } else {
            cwbDBRequest::getInstance()->rollBackManualTransaction();
            return false;
        }
    }

    protected function getCustomConfIntermediario() {
        return $this->getLibDB_BGE()->leggiBgeAgidConfNSS(array());
    }

    protected function customInserimentoMassivo($scadenza, $inviaBloccoUnico = false) {
        
    }

    protected function invioPuntualeScadenzaCustom($progkeytabScadenza) {
        
    }

    private function inserisciBgeAgidScaNss($progkeytabBwePenden, $progkeytabAgidScadenze) {
// Riversamento dati da BWE_PENDEN_NSS in BGE_AGID_SCA_NSS
        $penden_nss = $this->getLibDB_BWE()->leggiBwePendenNss(array('PROGKEYTAB' => $progkeytabBwePenden), false);
        if ($penden_nss) {
            $toInsert = $penden_nss;
            $toInsert['PROGKEYTAB'] = $progkeytabAgidScadenze;
        }
        $this->insertUpdateRecord($toInsert, 'BGE_AGID_SCA_NSS', true, true);
    }

    protected function verificaVariazione($filtri) {
        
    }

    public function scaricaPagamentoRT($params) {
        $conf = $this->getConfIntermediario();

        if (!$conf) {
            $this->handleError(-1, "Errore caricamento parametri");
            return false;
        }

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);

        $result = $wsClient->ws_scaricaPagamentoRT($params);

        if ($result) {
            $result = $wsClient->getResult();
            if ($result['Esito'] == self::ESITO_POSITIVO) {
                return $result;
            } else if ($result['Esito'] == self::ESITO_NEGATIVO) {
                $this->handleError(-1, $result['Descrizione']);
            }
        } else {
            $this->handleError(-1, $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription());
        }

        return false;
    }

    protected function customRimuoviPosizioni($idRuolo) {
        $conf = $this->getConfIntermediario();

        if (!$conf) {
            $this->handleError(-1, "Errore caricamento parametri");
            return false;
        }

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);

        $result = $wsClient->ws_AnnullaRuoloPosizioni(array('IdRuolo' => $idRuolo));

        if ($result) {
            $result = $wsClient->getResult();
            if ($result['Esito'] == self::ESITO_POSITIVO) {
                return true;
            } else if ($result['Esito'] == self::ESITO_NEGATIVO) {
                $this->handleError(-1, $result['Descrizione']);
            }
        } else {
            $this->handleError(-1, $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription());
        }

        return false;
    }

    protected function customGeneraBollettino($params) {
        $conf = $this->getConfIntermediario();

        if (!$conf) {
            $this->handleError(-1, "Errore caricamento parametri");
            return false;
        }

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);

        $result = $wsClient->ws_ScaricaDocumentoPDF($params);

        if ($result) {
            $result = $wsClient->getResult();
            if ($result['Esito'] == self::ESITO_POSITIVO) {
                return $result['Documento'];
            } else if ($result['Esito'] == self::ESITO_NEGATIVO) {
                $this->handleError(-1, $result['Descrizione']);
            }
        } else {
            $this->handleError(-1, $wsClient->getError() ? $wsClient->getError() : $this->getLastErrorDescription());
        }

        return false;
    }

    protected function customPubblicazioneMassiva($progkeytabInvio, $scadenzePerPubbli) {
        $risposta = array();
        if ($this->customPreparazionePerPubblicazione($progkeytabInvio, $scadenzePerPubbli, $nomeFile, $file, $numPosizioni, $scadenza['CODSERVIZIO'], $posizioniDebitorie)) {
            try {
                // INSERT su BGE_AGID_INVII
                $progint = $this->generaProgressivoFlusso(date('Ymd'), $scadenzePerPubbli[0]['CODSERVIZIO'], 1);
                $invio = array(
                    'TIPO' => 1,
                    'INTERMEDIARIO' => $scadenzePerPubbli[0]['INTERMEDIARIO'],
                    'CODSERVIZIO' => $scadenzePerPubbli[0]['CODSERVIZIO'],
                    'DATAINVIO' => date('Ymd'),
                    'PROGINT' => $progint,
                    'NUMPOSIZIONI' => count($scadenzePerPubbli),
                    'STATO' => 1,
                );

                $this->insertBgeAgidInvii($progkeytabInvio, $invio);

                $devLib = new devLib();
                $configGestBin = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_GEST_BIN', false);

                // Costruisco record da salvare in BGE_AGID_ALLEGATI
                $allegati = array(
                    'TIPO' => 1,
                    //  'IDINVRIC' => intval($progkeytabInvio) > 0 ? $progkeytabInvio : $scadenza['PROGINV'],
                    'IDINVRIC' => $progkeytabInvio,
                    'DATA' => date('Ymd'),
                    'NOME_FILE' => $nomeFile,
                );

                if (intval($configGestBin['CONFIG']) === 0) {
                    //INSERT su BGE_AGID_ALLEGATI
                    $allegati['ZIPFILE'] = file_get_contents($file);
                    $this->insertBgeAgidAllegati($allegati);
                } else {
                    //$allegati['ZIPFILE'] = 'ffff';
                    $progkeytabAlleg = $this->insertBgeAgidAllegati($allegati);
                    $configPathAllegati = $devLib->getEnv_config('PAGOPA', 'codice', 'PAGOPA_BIN_PATH_ALLEG', false);
                    if (!file_exists($configPathAllegati['CONFIG'])) {
                        mkdir($configPathAllegati['CONFIG']);
                    }
                    file_put_contents($configPathAllegati['CONFIG'] . "/Allegato_" . $progkeytabAlleg, file_get_contents($file));
                }

                if (!$this->getSimulazioneSF() || !$this->getSimulazione()) {
                    if (!$this->customInvioPubblicazione($nomeFile, $file, $scadenzePerPubbli, $posizioniDebitorie, $errMessage)) {
                        return false;
                    } else {
                        //UPDATE su BGE_AGID_SCADENZE 
                        foreach ($scadenzePerPubbli as $scadenzeP) {
                            $toUpdate['PROGKEYTAB'] = $scadenzeP['PROGKEYTAB'];
                            $toUpdate['STATO'] = 2;
                            $toUpdate['PROGINV'] = $progkeytabInvio;
                            $toUpdate['DATAINVIO'] = date('Ymd');
                            $toUpdate['TIMEINVIO'] = date('H:i:s');
                            $this->aggiornaBgeScadenze($toUpdate);

                            $this->rispostaPubblicazione($risposta, $scadenzeP, true);
                        }
                        if ($this->getSimulazioneSF() != true) {
                            $invio['PROGKEYTAB'] = $progkeytabInvio;
                            $this->customGestioneRisposta($posizioniDebitorie, $invio, $nomeFile);
                        }
                        return $risposta;
                    }
                }
            } catch (Exception $exc) {
                $msgError .= "Errore: " . $exc->getMessage() . " Servizio:" . $scadenzePerPubbli[0]['CODSERVIZIO'];
                $this->handleError(-1, $msgError);
                return false;
            }
        } else {
            $this->handleError(-1, "Errore preparazione invio");
        }
        return false;
    }

    private function customInvioPubblicazione($nomeFile, &$file, $scadenzeDaPubblicare, $posizioniDebitorie = null) {
        $conf = $this->getConfIntermediario();

        if (!$conf) {
            $this->handleError(-1, "Errore caricamento parametri");
            return false;
        }

        $wsClient = $this->getWsClient();

        $token = $this->login($wsClient);

        if (!$token) {
            return false;
        }
        $wsClient->setToken($token);

        $esito = true;
        $errMessage = '';
        $start = true;
        $idRuolo = 0;
        foreach ($posizioniDebitorie as $dati) {
            if (!$start) {
                $this->refreshIdRuolo($idRuolo, $dati);
            }
            // Richiama web service e restituisce il risultato
            $risposta = $this->pubblicazioneDirettaInvocaWs($dati, $esito, $messaggio, $wsClient);

            if (!$risposta) {
                $esito = false;
                break;
            } elseif ($start) {
                $idRuolo = $this->getIdRuoloFromResponse($risposta);
                $start = false;
            }

            // Aggiorno i dati delle tabelle AGID
            if ($risposta['Esito'] === self::ESITO_POSITIVO) {
                if (!$this->aggiornamentoAgid($risposta, $scadenzeDaPubblicare)) {
//                    $this->rimuoviPosizioni($risposta['ID_RUOLO']);
                    $esito = false;
                    $errMessage .= " Errore Aggiornamento tabelle Scadenze";
                    break;
                }
            } elseif ($risposta['Esito'] === self::ESITO_NEGATIVO) {
                foreach ($risposta['Dettagli'] as $key => $errore) {
                    $errMessage .= ' - ' . $errore;
                }
                $esito = false;
                break;
            } else {
                foreach ($risposta['Dettagli'] as $key => $errore) {
                    $errMessage .= ' - ' . $errore;
                }
                $esito = false;
                break;
            }
        }

        if (!$esito && intval($idRuolo) > 0) {
            $this->rimuoviPosizioni($idRuolo);
        }
        return $esito;
    }

    private function login($wsClient) {
        $ret = $wsClient->ws_Login(array());
        if (!$ret) {
            if ($wsClient->getFault()) {
                $messaggio = $wsClient->getFault();
            } elseif ($wsClient->getError()) {
                $messaggio = $wsClient->getError();
            }
            $this->handleError(-1, $messaggio);

            return false;
        }
        $ret = $wsClient->getResult();
        if ($ret['Esito'] == self::ESITO_POSITIVO) {
            return $ret['TokenAuth'];
        } else if ($ret['Esito'] == "FO_AUTENTICAZIONE") {
            $this->handleError(-1, $ret['Descrizione']);
        } else {
            $this->handleError(-1, "Errore Login");
        }

        return false;
    }

    public function elaborazioneScadenzeScartate($filtri) {
        
    }

    private function getWsClient() {
        $libDB = new cwbLibDB_BGE();
        $confNSS = $libDB->leggiBgeAgidConfNss(array());
        $wsClient = new itaLinkNextClient();
        $wsClient->setWebservices_uri($confNSS['URL']);
        $wsClient->setWebservices_wsdl($confNSS['URL'] . '?wsdl');
        //$wsClient->setNamespace($ns['CONFIG']);
        $wsClient->setNameSpaces(array("foh" => "http://entranext.it/fohead", "ent" => "http://entranext.it/"));
        $wsClient->setUsername($confNSS['USERNAME']);
        $wsClient->setPassword($confNSS['PASSWORD']);
        $wsClient->setIdConnettore($confNSS['IDCONNETTORE']);
        $wsClient->setIdAccesso($confNSS['IDENTIFICATIVO']);
        $wsClient->setCFEnte($confNSS['CODFISCENTE']);
        $wsClient->setGestionePDFACaricoDelFornitore(true);

        return $wsClient;
    }

    protected function getEmissioniPerPubblicazione($filtri = array()) {
        $filtri['INTERMEDIARIO'] = itaPagoPa::NEXTSTEPSOLUTION_TYPE;
        return $this->getLibDB_BGE()->leggiEmissioniDaPubblicare($filtri);
    }

    protected function leggiScadenzePerInserimento($filtri = array()) {
        $filtri['INTERMEDIARIO'] = itaPagoPa::NEXTSTEPSOLUTION_TYPE;
        $scadenze = $this->getLibDB_BWE()->leggiBwePendenScadenze($filtri);
        return $scadenze;
    }

}
