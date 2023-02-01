<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2017 Italsoft snc
 * @license
 * @version    01.03.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praSoggetti.class.php';
include_once (ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praMessage.class.php';

class praLibPratica {

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private $praLib;
    private $proLibSerie;
    private $PRAM_DB;
    private $errMessage;
    private $errCode;
    private $workYear;

    public static function getInstance($ditta = '') {
        $obj = new praLibPratica();
        try {
            $obj->praLib = new praLib();
            $obj->proLibSerie = new proLibSerie();
            $obj->PRAM_DB = $obj->praLib->getPRAMDB();
            $obj->workDate = date('Ymd');
            $obj->workYear = date('Y', strtotime($obj->workDate));
        } catch (Exception $e) {
            return false;
        }
        return $obj;
    }

    function __construct($ditta = '') {
        
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function aggiungi($model, $dati) {
        $progressivodaRichiesta = false;
        if (isset($dati['ProgressivoDaRichiesta'])) {
            $progressivodaRichiesta = $dati['ProgressivoDaRichiesta'];
        }

        /*
         * Prenoto N.Pratica
         */
        $retLock = $this->praLib->bloccaProgressivoPratica();
        if (!$retLock) {
            $this->setErrCode(-1);
            $this->setErrMessage("Accesso esclusivo al progressivo pratica fallito con codice " . $this->praLib->getErrCode());
            return false;
        }
        if ($progressivodaRichiesta === false) {
            $procedimento = $this->praLib->leggiProgressivoPratica($this->workYear);
            if (!$procedimento) {
                $this->setErrCode(-1);
                $this->setErrMessage("Prenotazione Numero Pratica Fallito.");
                $this->praLib->sbloccaProgressivoPratica($retLock);
                return false;
            }
            $Ctr_Proges_rec = $this->praLib->GetProges($this->workYear . $procedimento);
            if ($Ctr_Proges_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Il numero pratica $this->workYear$procedimento è già esistente.<br>Riallineare il progressivo all'ultimo numero di pratica");
                $this->praLib->sbloccaProgressivoPratica($retLock);
                return false;
            }
            $procedimento = $this->workYear . $procedimento;
        } else {
            $procedimento = $dati['PROGES_REC']['GESPRA'];
            $Ctr_Proges_rec = $this->praLib->GetProges($procedimento);
            if ($Ctr_Proges_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Il numero pratica $procedimento è già esistente.<br>Riallineare il progressivo all'ultimo numero di pratica");
                $this->praLib->sbloccaProgressivoPratica($retLock);
                return false;
            }
        }
        if ($dati["PROGES_REC"]['GESPRA']) {
            $CtrGespra_Proges_rec = $this->praLib->GetProges($dati["PROGES_REC"]['GESPRA'], "richiesta");
            if ($CtrGespra_Proges_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("La richiesta on-line " . $dati["PROGES_REC"]['GESPRA'] . " risulta essere già caricata.");
                $this->praLib->sbloccaProgressivoPratica($retLock);
                return false;
            }
        }


        /*
         * Aggiungo testata
         */
        $proges_rec = $dati["PROGES_REC"];
        $proges_rec['GESNUM'] = $procedimento;
        $insert_Info = 'Oggetto : Inserisco pratica n. ' . $proges_rec['GESNUM'];


        /*
         * TODO: Rendere astratto e legato alla tipologia di front-office
         *
         * 1- Italsoft
         * 2- Cart
         * 3- Es. Mude
         * 4- ...........................
         *
         */
        $proges_rec = $this->praLib->GetClassificazioneFascicolo($dati, $proges_rec);
        /*
         * Attribuisco il progressivo di serie temporaneo al procedimento appena completato
         */
        if (!$this->attribuisciProgressivoSerieTemporaneo($proges_rec)) {
            $this->setErrCode(-1);
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        /*
         * Sincronizzo Giorni e data scadenza 
         * Leggo i dati di anagrafica procedimento per calcolare i giorni
         */

        $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);
        $arrayScadenza = $this->praLib->SincDataScadenza("PRATICA", $proges_rec['GESNUM'], $proges_rec['GESDSC'], $anapra_rec['PRAGIO'], $proges_rec['GESGIO'], $proges_rec['GESDRE'], true);
        if (!$arrayScadenza) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore Sincronizzazione Data Scadenza Pratica " . $proges_rec['GESNUM']);
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }
        $proges_rec['GESDSC'] = $arrayScadenza['SCADENZA'];
        $proges_rec['GESGIO'] = $arrayScadenza['GIORNI'];
        if (!$model->insertRecord($this->PRAM_DB, 'PROGES', $proges_rec, $insert_Info)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserimento Testata Pratica " . $proges_rec['GESNUM'] . " Fallito.");
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }
        if ($progressivodaRichiesta === false) {
            if (!$this->praLib->aggiornaProgressivoPratica(intval(substr($procedimento, 4, 6)))) {
                $this->praLib->sbloccaProgressivoPratica($retLock);
                $this->setErrCode(-1);
                $this->setErrMessage("Aggiornamento progressivo Pratiche fallito.</br>Contattare l'assistenza Software");
                return false;
            }
        }

        $progesNew_rec = $this->praLib->GetProges($procedimento, 'codice');
        if (!$progesNew_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Inserimento Pratica Fallito.");
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        /*
         * Inserimento record su Tabella PROGESSUB
         * Tabella che contiene i sub-Procedimenti
         */
        if (!$this->praLib->RegistraSubFascicoli($proges_rec['GESNUM'], "", 0, $proges_rec)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore inserimento fascicolo padre n. " . $proges_rec['GESNUM'] . " su PROGESSUB");
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        /*
         * Aggiunta records passi e delle tabelle collegate
         */
        switch ($dati['tipoInserimento']) {
            case "PRAFOLIST":
                /*
                 * Aggiungo passi da file front-office
                 */
                if ($this->praLib->ribaltaPassiXML($dati['XMLINFO'], $procedimento, $dati['ALLEGATI'], false, $dati['ALLEGATIACCORPATE']) != true) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($this->praLib->getErrMessage());
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }
                break;
            case "ANAGRAFICA":
                /*
                 * Aggiungo solo passi
                 */
                if ($this->praLib->ribaltaPassi($procedimento, $dati['ALLEGATI'], true) != true) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($this->praLib->getErrMessage());
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }

                break;

            case "PECSUAP": // DA PORTALE
                /*
                 * Aggiungo passi da file front-office
                 */
                if ($this->praLib->ribaltaPassiXML($dati['XMLINFO'], $procedimento, $dati['ALLEGATI'], false, $dati['ALLEGATIACCORPATE']) != true) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($this->praLib->getErrMessage());
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }

                /*
                 * Aggiungo allegati da front-office
                 */
                if ($dati['ALLEGATICOMUNICA']) {
                    $propas_rec_infocamere = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '$procedimento' AND PROZIP =1", false);
                    //if ($this->praLib->ribaltaAllegatiInfocamere($procedimento, $dati['ALLEGATICOMUNICA']) != true) {
                    if ($this->praLib->ribaltaAllegatiInfocamere($propas_rec_infocamere['PROPAK'], $dati['ALLEGATICOMUNICA']) != true) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($this->praLib->getErrMessage());
                        $this->praLib->sbloccaProgressivoPratica($retLock);
                        return false;
                    }
                }
                break;
            case "PECGENERICA":

                /*
                 * Aggiungo allegati da pec esterna infocamere
                 */
                if ($dati['ALLEGATICOMUNICA']) {
                    $propas_rec_infocamere = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM = '$procedimento' AND PROZIP =1", false);
                    //if ($this->praLib->ribaltaAllegatiInfocamere($procedimento, $dati['ALLEGATICOMUNICA']) != true) {
                    if ($this->praLib->ribaltaAllegatiInfocamere($propas_rec_infocamere['PROPAK'], $dati['ALLEGATICOMUNICA']) != true) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($this->praLib->getErrMessage());
                        $this->praLib->sbloccaProgressivoPratica($retLock);
                        return false;
                    }
                }

                /*
                 * Aggiungo gli allegati della mail come allegati generici del fascicolo
                 */
                if ($dati['ALLEGATI']) {
                    if ($this->praLib->ribaltaAllegatiEsterna($procedimento, $dati['ALLEGATI']) != true) {//, $dati['esterna']) != true) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($this->praLib->getErrMessage());
                        $this->praLib->sbloccaProgressivoPratica($retLock);
                        return false;
                    }
                }

                /*
                 * Aggiungo solo passi
                 */

                if ($this->praLib->ribaltaPassi($procedimento, $dati['ALLEGATI'], $dati['EscludiPassiFO']) != true) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($this->praLib->getErrMessage());
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }
                break;
            case "WSPROTOCOLLO":
                /*
                 * rilettura protocollo
                 */
                $proObject = proWSClientFactory::getInstance();
                if (!$proObject) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore inizializzazione driver protocollo");
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }

                $annoProt = substr($progesNew_rec['GESNPR'], 0, 4);
                $numProt = substr($progesNew_rec['GESNPR'], 4);
                $param = array(
                    'NumeroProtocollo' => $numProt,
                    'AnnoProtocollo' => $annoProt,
                    'DataProtocollo' => $dati['dataProtocollo'],
                    'TipoProtocollo' => $progesNew_rec['GESPAR'],
                    'Docnumber' => $dati['idDocumento'],
                    'Segnatura' => $dati['segnatura'],
                );

                $ret = $proObject->LeggiProtocollo($param);
                if ($ret['Status'] == 0) {
                    //$datiWs = $ret['RetValue']['Dati'];
                    $datiWs = $ret['RetValue']['DatiProtocollo'];
                    $metadati = $ret['RetValue']['DatiProtocollazione'];
                } else {
                    $this->setErrCode(-1);
                    $this->setErrMessage($ret['Message']);
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }
                $progesNew_rec['GESOGG'] = $datiWs['Oggetto'];
                $progesNew_rec['GESDRI'] = substr($datiWs['Data'], 0, 4) . substr($datiWs['Data'], 5, 2) . substr($datiWs['Data'], 8, 2);
                $progesNew_rec['GESORA'] = substr($datiWs['Data'], 11, 8);
                $progesNew_rec['GESPAR'] = "A"; //$datiWs['Origine'] == 'I' ? 'C' : $datiWs['Origine'];
                $progesNew_rec['GESMETA'] = serialize($metadati);
                if ($progesNew_rec['GESNPR'] == 0) {
                    $annoProt = $datiWs['Anno'];
                    $numProt = $datiWs['NumeroProtocollo'];
                    $progesNew_rec['GESNPR'] = $annoProt . $numProt;
                }
                if ($progesNew_rec['GESDPR'] == "") {
                    $progesNew_rec['GESDPR'] = $datiWs['Data'];
                }
                $update_Info = "Oggetto: aggiorno dati protocollo N $numProt del fascicolo N. $procedimento";
                if (!$model->updateRecord($this->PRAM_DB, 'PROGES', $progesNew_rec, $update_Info)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Aggiornamento dati protocollo N $numProt del fascicolo N. $procedimento fallito");
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }

                /*
                 * destinatari
                 */
                $this->praLib = new praLib();
                $this->praSoggetti = praSoggetti::getInstance($this->praLib, $procedimento);
                $sog_tab = $dati['MITTDEST']; //$this->praSoggetti->GetSoggetti();
                foreach ($sog_tab as $key => $soggetto) {
                    $soggetto['DESNUM'] = $procedimento;
                    $this->praSoggetti->SetSoggetto($soggetto, $key); //aggiorno i soggetti col numero pratica
                }

                $msgSogg = $this->praSoggetti->RegistraSoggetti($model);
                if ($msgSogg != true) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($msgSogg);
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }

                /*
                 * Aggiungo gli allegati del protocollo come allegati generici del fascicolo
                 */
                $Allegati = $datiWs['Allegati'];
                if ($Allegati) {
                    $pramPath = $this->praLib->SetDirectoryPratiche(substr($procedimento, 0, 4), $procedimento, "PROGES");
                    foreach ($Allegati as $key => $Allegato) {
                        $contentFile = base64_decode($Allegato['Stream']);
                        $randName = md5(rand() * time()) . "." . $Allegato['Estensione'];
                        file_put_contents($pramPath . "/" . $randName, $contentFile);

                        $pasdoc_rec = array();
                        $pasdoc_rec['PASKEY'] = $procedimento;
                        $pasdoc_rec['PASFIL'] = $randName;
                        $pasdoc_rec['PASLNK'] = "allegato://" . $randName;
                        $pasdoc_rec['PASNOT'] = $Allegato['Note'];
                        $pasdoc_rec['PASCLA'] = "GENERALE";
                        $pasdoc_rec['PASNAME'] = $Allegato['NomeFile'];
                        $pasdoc_rec['PASUTELOG'] = App::$utente->getKey('nomeUtente');
                        $pasdoc_rec['PASORADOC'] = date("H:i:s");
                        $pasdoc_rec['PASDATADOC'] = date("Ymd");
                        $pasdoc_rec['PASSHA2'] = hash_file('sha256', $pramPath . "/" . $pasdoc_rec["PASFIL"]);
                        $insert_Info = "Oggetto: Inserisco l'allegato " . $Allegato['Note'] . " del protocollo $numProt/$annoProt pratica $procedimento";
                        if (!$model->insertRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec, $insert_Info)) {
                            $this->setErrCode(-1);
                            $this->setErrMessage("Errore Inserimento Allegato " . $pasdoc_rec['PASNOT']);
                            $this->praLib->sbloccaProgressivoPratica($retLock);
                            return false;
                        }
                    }
                }

                /*
                 * qui creare il file a partire dal base64 e gestire caricamento allegati
                 */
                if ($dati['ALLEGATI']) {
                    if ($this->praLib->ribaltaAllegatiEsterna($procedimento, $dati['ALLEGATI']) != true) {//, $dati['esterna']) != true) {
                        $this->setErrCode(-1);
                        $this->setErrMessage($this->praLib->getErrMessage());
                        $this->praLib->sbloccaProgressivoPratica($retLock);
                        return false;
                    }
                }
                /*
                 * salvataggio metadati
                 */
                $progesNew_rec['GESMETA'] = serialize(array('DatiProtocollazione' => $metadati));
                $update_Info = "Oggetto rowid:" . $progesNew_rec['ROWID'] . ' num:' . $progesNew_rec['GESNPR'] . " - aggiunta metadati";
                if (!$model->updateRecord($this->PRAM_DB, 'PROGES', $progesNew_rec, $update_Info)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Aggiornamento dopo inserimento da Protocollo fallito.");
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }

                /*
                 * Aggiungo solo passi
                 */
                if ($this->praLib->ribaltaPassi($procedimento, $dati['ALLEGATI'], $dati['senzaSuap']) != true) {
                    $this->setErrCode(-1);
                    $this->setErrMessage($this->praLib->getErrMessage());
                    $this->praLib->sbloccaProgressivoPratica($retLock);
                    return false;
                }
                break;
        }

        $progesNew_rec = $this->praLib->GetProges($procedimento, 'codice');
        if (!$progesNew_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Rilettura dati  Pratica Fallito.");
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        /*
         * Valorizza se necessario i metadati di diagramma
         */
        $progesNew_rec['GESDIAG'] = $this->praLib->setMetaDatiDiagramma($progesNew_rec);


        /**
         * POST-ELABORAZIONI
         *
         */
        if (!$this->praLib->valorizzaDatiAggiuntivi($progesNew_rec['GESNUM'], $dati)) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->praLib->getErrMessage());
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }


        if (!$this->praLib->elaboraDatiAggiuntiviCatasto($progesNew_rec['GESNUM'])) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->praLib->getErrMessage());
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        if (!$this->praLib->elaboraDatiAggiuntiviSoggetti($progesNew_rec['GESNUM'], $proges_rec['GESDRE'], $proges_rec['GESDCH'])) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->praLib->getErrMessage());
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        if (!$this->praLib->elaboraDatiLocalizzazioneIntervento($progesNew_rec['GESNUM'], $proges_rec['GESDRE'])) {
//        if (!$this->praLib->elaboraDatiLocalizzazioneIntervento($progesNew_rec['GESNUM'], $proges_rec['GESDRE'], $proges_rec['GESDCH'])) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->praLib->getErrMessage());
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        if (!$this->praLib->elaboraDatiAggiuntiviInsProduttivo($progesNew_rec['GESNUM'], $proges_rec['GESDRE'], $proges_rec['GESDCH'])) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->praLib->getErrMessage());
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        if (!$this->praLib->elaboraDatiAggiuntiviPagamento($progesNew_rec['GESNUM'], $proges_rec['GESDRE'])) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->praLib->getErrMessage());
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        if ($dati['DatiAssegnazione']) {
            include_once ITA_BASE_PATH . '/apps/Pratiche/praAssegnaPraticaSimple.php';
            $praAssegnaPraticaSimple = new praAssegnaPraticaSimple();
            $retAss = $praAssegnaPraticaSimple->ConfermaAssegnazione($progesNew_rec['GESNUM'], $dati['DatiAssegnazione']['ASSEGNATARIO'], $dati['DatiAssegnazione']['TIPOPASSO'], $dati['DatiAssegnazione']['NOTE'], false);
            if (!$retAss) {
                return false;
            }
        }

        include_once (ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php');
        $sql = "SELECT * FROM ANADES WHERE DESNUM = " . $progesNew_rec['GESNUM'] . " AND DESRUO = '" . praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD'] . "'";
        $Esibente_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$Esibente_rec) {
            /*
             * Aggiungo dati esibente da $dati xml front-office
             */
            $anades_rec = $dati["ANADES_REC"];
            $anades_rec['DESNUM'] = $proges_rec['GESNUM'];
            $anades_rec['DESSON'] = "N";
            $anades_rec['DESDRE'] = $proges_rec['GESDRE'];
            $anades_rec['DESDCH'] = $proges_rec['GESDCH'];

            $insert_Info = 'Oggetto : Inserisco intestatario pratica ' . $anades_rec['DESNUM'];
            if (!$model->insertRecord($this->PRAM_DB, 'ANADES', $anades_rec, $insert_Info)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento Dati Anagrafici Pratica " . $anades_rec['DESNUM'] . " Fallito.");
                $this->praLib->sbloccaProgressivoPratica($retLock);
                return false;
            }
        }

        $this->praLib->sincronizzaStato($procedimento);

        /*
         * Assegno l'oggetto appena elaborato a GESOGG
         */
        $progesNew_rec['GESOGG'] = $this->praLib->GetOggettoPratica($progesNew_rec);

        /*
         * Popolo, se c'è, numero pratica antecedente, settore e attività
         */
        //$proges_recAntecedente = $this->praLib->GetPraticaAntecedente($progesNew_rec['GESNUM'], $progesNew_rec['GESPRA']);
        $proges_recAntecedente = $this->praLib->GetPraticaAntecedente($progesNew_rec['GESNUM'], $dati['PRORIC_REC']);
        if ($proges_recAntecedente) {
            $progesNew_rec['GESPRE'] = $proges_recAntecedente['GESNUM'];
            $progesNew_rec['GESTSP'] = $proges_recAntecedente['GESTSP'];
            $progesNew_rec['GESTIP'] = $proges_recAntecedente['GESTIP'];
            $progesNew_rec['GESSTT'] = $proges_recAntecedente['GESSTT'];
            $progesNew_rec['GESATT'] = $proges_recAntecedente['GESATT'];
        }

        //
        //Aggiornamento data e ora dopo Inserimeno Pratica
        //
        $progesNew_rec['GESDATAREG'] = date("Ymd");
        $progesNew_rec['GESORAREG'] = date("H:i:s");

        /*
         * Attribuisco il progressivo di serie al procedimento appena completato
         */
        if (!$this->attribuisciProgressivoSerie($progesNew_rec)) {
            $this->setErrCode(-1);
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }
        
        /*
         * Controllo Esistenza Progressivo Serie Archivistica
         */
        $gesnumCTR = $this->praLib->GetGesnum($progesNew_rec['SERIEANNO'], $progesNew_rec['SERIEPROGRESSIVO'], $progesNew_rec['SERIECODICE']);
        if ($gesnumCTR) {
            $Serie_rec = $this->praLib->ElaboraProgesSerie($gesnumCTR, $progesNew_rec['SERIECODICE'], $progesNew_rec['SERIEANNO'], $progesNew_rec['SERIEPROGRESSIVO']);
            $formatGesnumCTR = substr($gesnumCTR, 4) . "/" . substr($gesnumCTR, 0, 4);
            $this->setErrCode(-1);
            $this->setErrMessage("Serie $Serie_rec già trovata in archivio per il fascicolo con identificativo N. $formatGesnumCTR");
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }

        $update_Info = "Oggetto: Aggiornamento Data e ora dopo inserimento pratica " . $progesNew_rec['GESNUM'];
        if (!$model->updateRecord($this->PRAM_DB, 'PROGES', $progesNew_rec, $update_Info)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Aggiornamento dopo inserimento Pratica " . $progesNew_rec['GESNUM'] . " Fallito.");
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }


        /*
         * setto FOGESNUM con il numero del fascicolo
         */
        if (!praFrontOfficeManager::setMarcaturaPrafolist($dati['PRAFOLIST_REC']['ROW_ID'], $progesNew_rec['GESNUM'])) {
            $this->setErrCode(praFrontOfficeManager::$lasErrCode);
            $this->setErrMessage(praFrontOfficeManager::$lasErrMessage);
            $this->praLib->sbloccaProgressivoPratica($retLock);
            return false;
        }


        $this->praLib->sbloccaProgressivoPratica($retLock);

        return $progesNew_rec;
    }

    function cancella($model, $numero, $anno) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praImmobili.class.php';
        include_once ITA_BASE_PATH . '/apps/Pratiche/praSoggetti.class.php';
        //
        $pratica = $anno . $numero;

        /*
         * Controllo esistenza Pratica
         */
        $proges_rec = $this->praLib->GetProges($pratica, 'codice');
        if (!$proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Pratica $numero/$anno non trovata");
            return false;
        }


        $praFrontOfficeManager = new praFrontOfficeManager();

        if (!$praFrontOfficeManager->ripristinaRichiesta($pratica)) {
            $this->setErrCode($praFrontOfficeManager->getErrCode());
            $this->setErrMessage($praFrontOfficeManager->getErrMessage());
            return false;
        }

//        if (!praFrontOfficeManager::ripristinaRichiesta($pratica)){
//            $this->setErrCode(praFrontOfficeManager::$lasErrCode);
//            $this->setErrMessage(praFrontOfficeManager::$lasErrMessage);
//            return false;
//        }

        $praImmobili = praImmobili::getInstance($this->praLib, $pratica);
        $praSoggetti = praSoggetti::getInstance($this->praLib, $pratica);
        $pratPath = $this->praLib->SetDirectoryPratiche(substr($pratica, 0, 4), $pratica, 'PROGES', false);
        if (is_dir($pratPath)) {
            $retCancella = $this->praLib->RemoveDir($pratPath);
        } else {
            $retCancella = true;
        }
        if ($retCancella) {
            //$proges_rec = $this->praLib->GetProges($pratica, 'codice');
            $prasta_rec = $this->praLib->GetPrasta($pratica, 'codice');
            $propas_tab = $this->praLib->GetPropas($pratica, 'codice', true);
            $prodiaggruppi_tab = $this->praLib->GetProdiagGruppi($pratica, 'codice', true);
            $propasfatti_tab = $this->praLib->GetPropasFatti($pratica, 'pronum', true);
            $pramitdest_tab = $this->praLib->GetPraMitDest($pratica, 'numero', true);
            $prodst_tab = $this->praLib->GetProdst($pratica, 'numero', true);
            $pracom_tab = $this->praLib->GetPracom($pratica, 'numero', true);
            $prodag_tab = $this->praLib->GetProdag($pratica, 'numero', true);
            $pasdoc_tab = $this->praLib->GetPasdoc($pratica, 'numero', true);
            $pramail_rec = $this->praLib->GetPramail($pratica, "gesnum");
            $proimpo_tab = $this->praLib->GetProimpo($pratica, "codice", "", true);
            $proconciliazione_tab = $this->praLib->GetProconciliazione($pratica, "codice", "", true);
            $progessub_tab = $this->praLib->GetProgessub($pratica, "codice", true);
            $rowidProges = $proges_rec['ROWID'];

            $delete_Info = 'Oggetto: Cancellazione pratica' . $pratica;
            if ($proges_rec) {
                if (!$model->deleteRecord($this->PRAM_DB, 'PROGES', $proges_rec['ROWID'], $delete_Info)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Attenzione cancellazione su PROGES fallita pratica n. $pratica");
                    return false;
                }
            }
            if ($prasta_rec) {
                if (!$model->deleteRecord($this->PRAM_DB, 'PRASTA', $prasta_rec['ROWID'], $delete_Info)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Attenzione cancellazione su PRASTA fallita pratica n. $pratica");
                    return false;
                }
            }
            if ($prodiaggruppi_tab) {
                foreach ($prodiaggruppi_tab as $prodiaggruppi_rec) {

                    //Cancellare i passi associati al gruppo
                    $prodiagpassigruppi_tab = $this->praLib->GetProdiagPassiGruppi($prodiaggruppi_rec['ROW_ID'], 'gruppo', true);
                    if ($prodiagpassigruppi_tab) {
                        foreach ($prodiagpassigruppi_tab as $prodiagpassigruppi_rec) {
                            if (!$model->deleteRecord($this->PRAM_DB, 'PRODIAGPASSIGRUPPI', $prodiagpassigruppi_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                                $this->setErrCode(-1);
                                $this->setErrMessage("Attenzione cancellazione su PRODIAGPASSIGRUPPI fallita pratica n. $pratica");
                                return false;
                            }
                        }
                    }

                    if (!$model->deleteRecord($this->PRAM_DB, 'PRODIAGGRUPPI', $prodiaggruppi_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PRODIAGGRUPPI fallita pratica n. $pratica");
                        return false;
                    }
                }
            }
            if ($propasfatti_tab) {
                foreach ($propasfatti_tab as $propasfatti_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PROPASFATTI', $propasfatti_rec['ROW_ID'], $delete_Info, 'ROW_ID')) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PROPASFATTI fallita pratica n. $pratica");
                        return false;
                    }
                }
            }
            if ($propas_tab) {
                // Si controlla se Configurato italsoft-ws
                $arrayFo = $this->praLib->getArrayTipiFO();

                foreach ($arrayFo as $foTrovato) {
                    if ($foTrovato['TIPO'] == 'italsoft-ws' && $foTrovato['ATTIVO'] == 1) {
                        $istanza = $foTrovato['ISTANZA'];
                        break;
                    }
                }


                foreach ($propas_tab as $propas_rec) {

                    // Trovata configurazione per italsoft-ws
                    if ($istanza) {
                        // Passo inviato al FO, perchè campo PRODATEPUBART valorizzato
                        if ($propas_rec['PRODATEPUBART']) {

                            $pubArticolo = $propas_rec['PROPART'];
                            $pubAllegati = $propas_rec['PROPFLALLE'];
                            $ret_esito = praFrontOfficeManager::caricaArticoliFO("italsoft-ws", $propas_rec['PRONUM'], $propas_rec['PROPAK'], $istanza, "Delete", $pubArticolo, $pubAllegati);
                        }
                    }


                    $dir = $this->praLib->SetDirectoryPratiche(substr($propas_rec['PROPAK'], 0, 4), $propas_rec['PROPAK'], "PASSO", false);
                    if (is_dir($dir)) {
                        $this->praLib->RemoveDir($dir);
                    }
                    if (!$model->deleteRecord($this->PRAM_DB, 'PROPAS', $propas_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PROPAS fallita pratica n. $pratica");
                        return false;
                    }
                }
            }
            if ($pramitdest_tab) {
                foreach ($pramitdest_tab as $pramitdest_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PRAMITDEST', $pramitdest_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PRAMITDEST fallita pratica n. $pratica");
                        return false;
                    }
                }
            }
            if ($prodst_tab) {
                foreach ($prodst_tab as $prodst_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PRODST', $prodst_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PRODST fallita pratica n. $pratica");
                        return false;
                    }
                }
            }
            if ($pracom_tab) {
                foreach ($pracom_tab as $pracom_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PRACOM', $pracom_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PRACOM fallita pratica n. $pratica");
                        return false;
                    }
                }
            }
            if ($prodag_tab) {
                foreach ($prodag_tab as $prodag_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PRODAG', $prodag_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PRODAG fallita pratica n. $pratica");
                        return false;
                    }
                }
            }
            if ($pasdoc_tab) {
                foreach ($pasdoc_tab as $pasdoc_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PASDOC', $pasdoc_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PASDOC fallita pratica n. $pratica");
                        return false;
                    }
                }
            }
            if ($pramail_rec) {
                if (!$model->deleteRecord($this->PRAM_DB, 'PRAMAIL', $pramail_rec['ROWID'], $delete_Info)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Attenzione cancellazione su PRAMAIL fallita pratica n. $pratica");
                    return false;
                }
            }
            if (!$praImmobili->CancellaImmobili($model)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione cancellazione su PRAIMM fallita pratica n. $pratica");
                return false;
            }


            if (!$praSoggetti->CancellaSoggetti($model)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione cancellazione su ANADES fallita pratica n. $pratica");
                return false;
            }

            if ($proimpo_tab) {
                foreach ($proimpo_tab as $proimpo_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PROIMPO', $proimpo_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PROIMPO fallita pratica n. $pratica");
                        return false;
                    }
                }
            }

            if ($proconciliazione_tab) {
                foreach ($proconciliazione_tab as $proconciliazione_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PROCONCILIAZIONE', $proconciliazione_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PROCONCILIAZIONE fallita pratica n. $pratica");
                        return false;
                    }
                }
            }

            if ($progessub_tab) {
                foreach ($progessub_tab as $progessub_rec) {
                    if (!$model->deleteRecord($this->PRAM_DB, 'PROGESSUB', $progessub_rec['ROWID'], $delete_Info)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Attenzione cancellazione su PROGESSUB fallita pratica n. $pratica");
                        return false;
                    }
                }
            }

            //
            //Cancello gli eventi e i promemorio della pratica
            //
            include_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';
            $envLibCalendar = new envLibCalendar();
            $event_tab = $envLibCalendar->getAppEvents("SUAP_PRATICA", $rowidProges);
            if ($event_tab) {
                foreach ($event_tab as $event_rec) {
                    $envLibCalendar->deletePromemoriaFromEvent($event_rec["ROWID"]);
                }
                if (!$envLibCalendar->deleteEventApp("SUAP_PRATICA", $rowidProges, false)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Attenzione cancellazione evento su calendario fallita per la pratica n. $pratica");
                    return false;
                }
            }
            return $pratica;
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("Rimozione cartella fallito pratica n. $pratica");
            return false;
        }
        return $pratica;
    }

    public function attribuisciProgressivoSerie(&$proges_rec) {
        $serie = $this->getCodiceSeriePerPratica($proges_rec);
        if (!$serie) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attribuzione Progressivo Serie alla Pratica {$proges_rec['GESNUM']} fallito.<br>" . $this->getErrMessage());
            return false;
        }

        $anno = date('Y', strtotime($proges_rec['GESDRE']));
        $progressivo = $this->proLibSerie->PrenotaProgressivoSerieUtil($serie, $anno);
        if (!$progressivo) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attribuzione Progressivo Serie alla Pratica {$proges_rec['GESNUM']} fallito.<br>" . $this->proLibSerie->getErrMessage());
            return false;
        }
        $proges_rec['SERIECODICE'] = $serie;
        $proges_rec['SERIEANNO'] = $anno;
        $proges_rec['SERIEPROGRESSIVO'] = $progressivo;

        return true;
    }

    public function attribuisciProgressivoSerieTemporaneo(&$proges_rec) {
        $anno = date('Y', strtotime($proges_rec['GESDRE']));
        $proges_rec_maxProg = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT MAX(SERIEPROGRESSIVO) AS PROGRESSIVO FROM PROGES", false);
        if (!$proges_rec_maxProg) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attribuzione Progressivo Temporaneo Serie alla Pratica {$proges_rec['GESNUM']} fallito.");
            return false;
        }

        $progressivo = $proges_rec_maxProg['PROGRESSIVO'] + 1;
        $proges_rec['SERIECODICE'] = 9999;
        $proges_rec['SERIEANNO'] = $anno;
        $proges_rec['SERIEPROGRESSIVO'] = $progressivo;
        return true;
    }

    function getCodiceSeriePerPratica($Proges_rec) {
        if (!$Proges_rec['GESTSP']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Codice sportello di provenienza mancante nel fascicolo con identificativo {$Proges_rec['GESNUM']}. Serie non attribuibile.");
            return false;
        }

        $Anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);
        if (!$Anatsp_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Anagrafica per lo sportello {$Proges_rec['GESTSP']} non reperibile.");
            return false;
        }
        if (!$Anatsp_rec['TSPSERIE']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Codice Serie non configurato per lo sportello di provenienza {$Proges_rec['GESTSP']}.");
            return false;
        }

        $Anaseriearc_rec = $this->proLibSerie->GetSerie($Anatsp_rec['TSPSERIE'], 'codice');
        if (!$Anaseriearc_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Anagrafica serie per i codice {$Anatsp_rec['TSPSERIE']} non reperibile.");
            return false;
        }
        return $Anatsp_rec['TSPSERIE'];
    }

    public function acquisizioneRichiesta($parm_aggiungi, $model) {

        /*
         * Istanzio array di ritorno
         */
        $ret = array();
        $ret["Status"] = "0";
        $ret["Message"] = "Richiesta " . $parm_aggiungi['PRORIC_REC']['RICNUM'] . " acquisita correttamente.";
        $ret["ExtendedMessageHtml"] = "";
        $ret["ExtendedMessageTxt"] = "";
        $ret["RetValue"] = true;

        /*
         * Esternalizzato
         */
        $tipoReg = $parm_aggiungi['tipoReg'];
        /*
         * Se è una richiesta on-line e se è un'integrazione, vedo se c'è il dato aggiuntivo della variante
         */
        $variante = false;
        if ($parm_aggiungi['PRORIC_REC']['RICPC'] == "1") {
            $variante = true;
        }

        /*
         * Acquisizione richiesta
         */
        $msgErr = "";
        if ($parm_aggiungi['PRORIC_REC']['RICRPA'] && !$variante) {
            $tipoReg = "integrazione";
            $index = count($parm_aggiungi['ALLEGATI']);
            $xmlInfo = array(
                'rowid' => $index,
                'DATAFILE' => $parm_aggiungi['XMLINFO'],
                'FILENAME' => 'XMLINFO.xml',
                'FILEINFO' => 'XMLINFO.xml',
            );
            $parm_aggiungi['ALLEGATI'][$index] = $xmlInfo;
            $ret_aggiungi = $this->praLib->CaricaPassoIntegrazione($parm_aggiungi['PRORIC_REC'], $parm_aggiungi['ALLEGATI'], $parm_aggiungi['FILENAME'], $parm_aggiungi['PRAMAIL_REC'], $parm_aggiungi['archivio'], false, $parm_aggiungi['PRAFOLIST_REC']['ROW_ID']);
            if (!$ret_aggiungi) {
                $msgErr = $this->praLib->getErrMessage();
                $msgAggiungi = "Errore inserimento integrazione.";
            }
            $ret['PROPAK'] = $ret_aggiungi['PROPAK'];
            $ret['GESNUM'] = $ret_aggiungi['PRONUM'];
        } elseif ($parm_aggiungi['PRORIC_REC']['PROPAK']) {
            $tipoReg = "integrazione";
            $ret_aggiungi = $this->praLib->CaricaPassoIntegrazione($parm_aggiungi['PRORIC_REC'], $parm_aggiungi['ALLEGATI'], $parm_aggiungi['FILENAME'], $parm_aggiungi['PRAMAIL_REC'], $parm_aggiungi['archivio'], true, $parm_aggiungi['PRAFOLIST_REC']['ROW_ID']);
            if (!$ret_aggiungi) {
                $msgErr = $this->praLib->getErrMessage();
                $msgAggiungi = "Errore inserimento passo parere.";
            }
            $ret['PROPAK'] = $ret_aggiungi['PROPAK'];
            $ret['GESNUM'] = $ret_aggiungi['PRONUM'];
        } else {
            $ret_aggiungi = $this->aggiungi($model, $parm_aggiungi);
            if (!$ret_aggiungi) {
                $msgErr = $this->getErrMessage();
                $msgAggiungi = "Errore nell'aggiungere la richiesta.";
            } else {
                /*
                 * Operazioni POST REGISTRAZIONE PRATICA
                 */
                $proges_rec_new = $this->praLib->GetProges($ret_aggiungi['GESNUM']);
                if ($proges_rec_new['GESCODPROC']) {
                    $this->praLib->elaboraDatiGoBid($proges_rec_new['GESNUM']);
                }

                if ($parm_aggiungi['CALLBACK']) {
                    $cbRequire = $parm_aggiungi['CALLBACK']['REQUIRE'];
                    $cbClass = $parm_aggiungi['CALLBACK']['CLASS'];
                    $cbMethod = $parm_aggiungi['CALLBACK']['METHOD'];
                    $cbParams = $parm_aggiungi['CALLBACK_PARAMS'] ?: array();

                    if ($cbRequire) {
                        if (!file_exists($cbRequire)) {
                            $ret["Status"] = "-1";
                            $ret["Message"] = "Errore callback: File di callback non trovato per la richiesta n. " . $parm_aggiungi['PRORIC_REC']['RICNUM'];
                            $ret["RetValue"] = false;
                            return $ret;
                        }

                        include_once $cbRequire;
                    }

                    if (!class_exists($cbClass)) {
                        $ret["Status"] = "-1";
                        $ret["Message"] = "Errore callback: Classe callback $cbClass non definita per la richiesta n. " . $parm_aggiungi['PRORIC_REC']['RICNUM'];
                        $ret["RetValue"] = false;
                        return $ret;
                    }

                    $cbInstance = new $cbClass();

                    if (!method_exists($cbInstance, $cbMethod)) {
                        $ret["Status"] = "-1";
                        $ret["Message"] = "Errore callback: Metodo di callback '$cbMethod' non definito per la richiesta n. " . $parm_aggiungi['PRORIC_REC']['RICNUM'];
                        $ret["RetValue"] = false;
                        return $ret;
                    }

                    $cbresult = call_user_func(array($cbInstance, $cbMethod), $proges_rec_new['GESNUM'], $cbParams);
                    if ($cbresult === false) {
                        $ret["Status"] = "-1";
                        $ret["Message"] = "Errore callback: Errore chiamata alla funzione call_user_func per la richiesta n. " . $parm_aggiungi['PRORIC_REC']['RICNUM'];
                        $ret["RetValue"] = false;
                        return $ret;
                    }
                }

                /*
                 * Se importo da controlla FO, mi trovo il file della mail passando per PRAMAIL e MAILARCHIVIO
                 */
                if (!$this->insertMailPratica($proges_rec_new['GESPRA'], $proges_rec_new['GESNUM'], $parm_aggiungi['FILENAME'], $parm_aggiungi['IDMAIL'])) {
                    $msgErr = "Archiviazione File" . $this->getErrMessage();
                    $msgAggiungi = "Errore Archiviazione mail PEC in Arrivo";
                }
            }
            $ret['GESNUM'] = $ret_aggiungi['GESNUM'];
            $ret['GESPRA'] = $ret_aggiungi['GESPRA'];
        }



        if ($msgErr) {
            $ret["Status"] = "-1";
            $ret["Message"] = $msgAggiungi . " " . $parm_aggiungi['PRORIC_REC']['RICNUM'] . ": $msgErr";
            $ret["RetValue"] = false;
        } else {
            $ret["ExtendedMessageHtml"] = $this->getMsgAcquisizione($tipoReg, $ret);
        }
        return $ret;
    }

    public function insertMailPratica($gespra, $gesnum, $filename, $idmail) {
        if ($gespra) {
            $praMail_rec = $this->praLib->GetPramailRecRichiesta($gespra);
            if ($praMail_rec) {
                include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                $emlLib = new emlLib();
                $mailArchivio_rec = $emlLib->getMailArchivio($praMail_rec['ROWIDARCHIVIO'], "rowid");
                if ($mailArchivio_rec) {
                    $dbMailBox = emlDbMailBox::getDbMailBoxInstance();
                    $file_riletto = $dbMailBox->getEmlForROWId($mailArchivio_rec['ROWID']);
                    $filename = $file_riletto;
                    $idmail = $mailArchivio_rec['IDMAIL'];
                }
            }
        }

        /*
         * salvo eml importato se presente
         */
        if ($filename) {
            if (!$this->praLib->RegistraEml($filename, $gesnum)) {
                $this->setErrMessage("Errore in salvataggio del file EML $filename");
                return false;
            }
        }

        /*
         * Blocco Mail di origine come acquisita        
         */
        if (isset($idmail)) {
            $praMessage = new praMessage();
            $ret = $praMessage->setClasseMail($idmail, $gesnum);
            if ($ret['Status'] == "-1") {
                $this->setErrMessage("Errore nel bloccare la mail della pratica $gesnum");
                return false;
            }
            $objModel = itaModel::getInstance("praGestMail");
            $objModel->setEvent("returnPraGest");
            $objModel->parseEvent();
        }
        return true;
    }

    /**
     * 
     * @param type $pratica Identificativo del Fascicolo
     * @return type html
     */
    public function getHtmlInfoProcedimento($pratica) {
        $proges_rec = $this->praLib->GetProges($pratica);
        $anapra_rec = $this->praLib->GetAnapra($proges_rec['GESPRO']);
        $anaset_rec = $this->praLib->GetAnaset($proges_rec['GESSTT']);
        $anaatt_rec = $this->praLib->GetAnaatt($proges_rec['GESATT']);
        $desc = $anapra_rec['PRADES__1'] . $anapra_rec['PRADES__2'] . $anapra_rec['PRADES__3'] . $anapra_rec['PRADES__4'];
        return "<div class=\"ita-Wordwrap\">" . $anaset_rec['SETDES'] . "</div><div class=\"ita-Wordwrap\">" . $anaatt_rec['ATTDES'] . "</div><div class=\"ita-Wordwrap\">$desc</div>";
    }

    /**
     * 
     * @param type $pratica Identificativo del Fascicolo
     * @return type html
     */
    public function getHtmlInfoDatiAggiuntivi($pratica) {
        $datiInsProd = $this->praLib->DatiImpresa($pratica);
        return "<div class=\"ita-Wordwrap\">" . $datiInsProd['IMPRESA'] . "</div><div class=\"ita-Wordwrap\">" . $datiInsProd['FISCALE'] . "</div>";
    }

    public function getMsgAcquisizione($tipoReg, $ret) {
        $proges_rec_new = $this->praLib->GetProges($ret['GESNUM']);
        if ($tipoReg == "integrazione") {
            $gesnum = substr($ret['GESNUM'], 4) . "/" . substr($ret['GESNUM'], 0, 4);
            $propas_rec = $this->praLib->GetPropas($ret['PROPAK']);
            if ($propas_rec['PRORIN']) {
                $integ = substr($propas_rec['PRORIN'], 4) . "/" . substr($propas_rec['PRORIN'], 0, 4);
            } else {
                $integ = $propas_rec['PROKEY'];
            }
            $messaggio = "Pratica: $gesnum aggiornata da richiesta di integrazione $integ";
        } else {
            switch ($tipoReg) {
                case 'consulta':
                    $daDove = "Richiesta on-line";
                    break;
                case 'infocamere':
                    $daDove = "Zip/Eml Starweb";
                    break;
                case 'WSPROTOCOLLO':
                    $daDove = "Protocollo";
                    break;
                default:
                    $daDove = "PEC Generica";
                    break;
            }

            $Anaseriearc_rec = $this->proLibSerie->GetSerie($proges_rec_new['SERIECODICE'], 'codice');
            $prat = $Anaseriearc_rec['SIGLA'] . "/" . $proges_rec_new['SERIEPROGRESSIVO'] . "/" . $proges_rec_new['SERIEANNO'];
            $data = substr($proges_rec_new['GESDRE'], 6, 2) . "/" . substr($proges_rec_new['GESDRE'], 4, 2) . "/" . substr($proges_rec_new['GESDRE'], 0, 4);
            $ananom_rec = $this->praLib->GetAnanom($proges_rec_new['GESRES']);
            if ($ananom_rec) {
                $msgResp = "Responsabile: " . $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM'];
            }
            $messaggio = '<span style="font-weight:bold;color:red;font-size:2em;"><br>Acquisizione Pratica da ' . $daDove . '<br>N. ' . $prat
                    . '</span><br><span style="font-weight:bold;color:black;font-size:1.2em;"><br><br>' . 'Data: '
                    . $data . "<br><br>$msgResp</span><br>";
        }
        return $messaggio;
    }

    /*
     * Funzione apertura pratica:
     */

    public function ApriPratica($gesnum) {
        $Proges_rec = $this->praLib->GetProges($gesnum);
        if ($Proges_rec['GESCLOSE'] != "@forzato@" && $Proges_rec['GESCLOSE'] != "") {
            
        } else {
            $Proges_rec['GESDCH'] = '';
            $Proges_rec['GESCLOSE'] = '';

            // update
        }
    }

    public function setStatiTabPratica($proges_rec, $flagAssegnazioni = false, $flagPagamenti = false) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
        
        $proLib = new proLib();
        
        $valueAnagrafica = proLibSerie::$PANEL_LIST;
        if ($proges_rec['SERIECODICE']) {
            $anaseriearc_rec = $proLib->getAnaseriearc($proges_rec['SERIECODICE']);
            if ($anaseriearc_rec && $anaseriearc_rec['METAPANEL']) {
                $valueAnagrafica = $this->proLibSerie->decodParametriPanelFascicolo($anaseriearc_rec['METAPANEL'], 'Anagrafica');
            }
        }

        $arrayStatiTab = array();
        foreach ($valueAnagrafica as $key => $panel) {
            if ($panel['DEF_STATO'] == 1) {
                $arrayStatiTab[$key]['Stato'] = "Show";
            } else {
                $arrayStatiTab[$key]['Stato'] = "Hide";
            }
            $arrayStatiTab[$key]['FileXml'] = $panel['FILE_XML'];
            $arrayStatiTab[$key]['Id'] = $panel['ID_ELEMENT'];
            $arrayStatiTab[$key]['IdFlag'] = $panel['ID_FLAG'];
        }

        
        /*
         * Se non c'è il parametro disabilito il Tab Assegnazioni
         */
        if ($arrayStatiTab[proLibSerie::PANEL_ASSEGNAZIONI]['Stato'] == "Show") {
            $arrayStatiTab[proLibSerie::PANEL_ASSEGNAZIONI]['Stato'] = "Show";
            if (!$flagAssegnazioni) {
                $arrayStatiTab[proLibSerie::PANEL_ASSEGNAZIONI]['Stato'] = "Hide";
            }
        }

        /*
         * Se non c'è il parametro disabilito il Tab Pagamenti
         */
        if ($arrayStatiTab[proLibSerie::PANEL_PAGAMENTI]['Stato'] == "Show") {
            $arrayStatiTab[proLibSerie::PANEL_PAGAMENTI]['Stato'] = "Show";
            if (!$flagPagamenti) {
                $arrayStatiTab[proLibSerie::PANEL_PAGAMENTI]['Stato'] = "Hide";
            }
        }
        
        
        return $arrayStatiTab;
    }
    
    
    
}
