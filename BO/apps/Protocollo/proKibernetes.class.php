<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    06.02.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPKibernetes/itaKibernetesSdiLayerClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPKibernetes/itaKibernetesSdiLayerParam.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';

class proKibernetes {

    const PAR_TIPOPROTDIPARTENZA = 'TIPOPROTDIPARTENZA';
    const PAR_INTERAFATTURA = 'INTERAFATTURA';
    const PAR_SINGOLAFATTURA = 'SINGOLAFATTURA';
    const FONTE_ESITO_FATTURAWITHARGS11 = 'ESITO_CARICA_FATTURA';
    const FONTE_CTR_ESITO_FATTURAWITHARGS11 = 'STORICO_FATTURA_CTR';
    const ESITO_PRESENTE = 'PRESENTE';

    function PreparaParamProtocolloFatturaWithArgs($Protocllo, $ExtraParam = array()) {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $anaent_38 = $proLib->GetAnaent('38');
        $AnaproPre_rec = array(); // Anapro Fattura

        /* */
        $AnnoProtocollo = $Protocllo['AnnoProtocollo'];
        $NumeroProtocollo = $Protocllo['NumeroProtocollo'];
        $TipoProtocollo = $Protocllo['TipoProtocollo'];
        //
        $NumeroProtocollo = str_pad($NumeroProtocollo, 6, '0', STR_PAD_LEFT);
        $Codice = $AnnoProtocollo . $NumeroProtocollo;
        $Anapro_rec = $proLib->GetAnapro($Codice, 'codice', $TipoProtocollo);
        if (!$Anapro_rec) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Protocollo $Codice $TipoProtocollo inesistente.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        /* Controllo se parametrizzati EFAA o SDIP o SDIA */
        if (!$Anapro_rec['PROCODTIPODOC']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Tipo documento non definito per il protocollo.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        /* Caso di default Intera Fattura */
        $ExtraParam[self::PAR_INTERAFATTURA] = true;
        switch ($Anapro_rec['PROCODTIPODOC']) {
            case $anaent_38['ENTDE4']://SDIP: EC
                $ExtraParam[self::PAR_TIPOPROTDIPARTENZA] = proSdi::TIPOMESS_EC;
                /* 1. Controllo se è un EC riferito ad un lotto o intera fattura. */
                $TabDagEC_rec = $proLibTabDag->GetTabdag('ANAPRO', 'valore', '', 'Tipo', proSdi::TIPOMESS_EC, false, '', 'MESSAGGIO_SDI');
                if (!$TabDagEC_rec) {
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "Attenzione il protocollo non risulta essere collegato ad un Esito Committente.";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'NumeroFattura', '', false, '', 'MESSAGGIO_SDI');
                if ($TabDag_rec) {
                    /* Caso Singola Fattura */
                    $ExtraParam[self::PAR_SINGOLAFATTURA] = $TabDag_rec['TDAGVAL'];
                    $ExtraParam[self::PAR_INTERAFATTURA] = false;
                }
                /* 2. Ricavo l'EFAA */
                $AnaproPre_rec = array();
                if ($Anapro_rec['PROPRE']) {
                    /* 1- Tramite Protocollo Collegato [PROPRE] */
                    $AnaproPre_rec = $proLib->GetAnapro($Anapro_rec['PROPRE'], 'codice', $Anapro_rec['PROPARPRE']);
                } else {
                    /*  2- Tramite FileFatturaUnivoco [Metadati->FileUnivocoFattura] */
                    $TabDagFileFattura_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'FileUnivocoFattura', '', false, '', 'MESSAGGIO_SDI'); // Prevedere controllo esistenza?
                    $NomeUnivocoFattura = $TabDagFileFattura_rec['TDAGVAL'];
                    $TabDagFatt_rec = $proLibTabDag->GetTabdag('ANAPRO', 'valore', '', 'CodUnivocoFile', $NomeUnivocoFattura, false, '', 'FATT_ELETTRONICA');
                    if (!$TabDagFatt_rec) {
                        $ritorno["Status"] = "-1";
                        $ritorno["Message"] = "Attenzione, impossibile trovare il metadato del File Fattura Univoco.";
                        $ritorno["RetValue"] = false;
                        return $ritorno;
                    }

                    $AnaproPre_rec = $proLib->GetAnapro($TabDagFatt_rec['ROWID'], 'rowid');
                }
                break;

            case $anaent_38['ENTDE3']://SDIA: DT
                $ExtraParam[self::PAR_TIPOPROTDIPARTENZA] = proSdi::TIPOMESS_DT;
                $TabDagEC_rec = $proLibTabDag->GetTabdag('ANAPRO', 'valore', '', 'Tipo', proSdi::TIPOMESS_DT, false, '', 'MESSAGGIO_SDI');
                if (!$TabDagEC_rec) {
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "Attenzione il protocollo non risulta essere collegato ad un Decorrenza Termini.";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                /* Se è arrivata una decorrenza termini, è arrivata sia per lotto che per la fattura. */
                $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'NumeroFattura', '', false, '', 'MESSAGGIO_SDI');
                if ($TabDag_rec) {
                    /* Caso Singola Fattura */
                    $ExtraParam[self::PAR_SINGOLAFATTURA] = $TabDag_rec['TDAGVAL'];
                    $ExtraParam[self::PAR_INTERAFATTURA] = false;
                }
                /* 2. Ricavo l'EFAA */
                if ($Anapro_rec['PROPRE']) {
                    /* 1- Tramite Protocollo Collegato [PROPRE] */
                    $AnaproPre_rec = $proLib->GetAnapro($Anapro_rec['PROPRE'], 'codice', $Anapro_rec['PROPARPRE']);
                } else {
                    /*  2- Tramite FileFatturaUnivoco [Metadati->NomeFile] */
                    $TabDagFileFattura_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'NomeFile', '', false, '', 'MESSAGGIO_SDI'); // Prevedere controllo esistenza?
                    $NomeUnivocoFattura = $TabDagFileFattura_rec['TDAGVAL'];
                    $TabDagFatt_rec = $proLibTabDag->GetTabdag('ANAPRO', 'valore', '', 'CodUnivocoFile', $NomeUnivocoFattura, false, '', 'FATT_ELETTRONICA');
                    if (!$TabDagFatt_rec) {
                        $ritorno["Status"] = "-1";
                        $ritorno["Message"] = "Attenzione, impossibile trovare il metadato del File Fattura Univoco.";
                        $ritorno["RetValue"] = false;
                        return $ritorno;
                    }
                    $AnaproPre_rec = $proLib->GetAnapro($TabDagFatt_rec['ROWID'], 'rowid');
                }
                break;
            case $anaent_38['ENTDE1']://EFAA
                $ExtraParam[self::PAR_TIPOPROTDIPARTENZA] = 'EFAA';
                // Qui prevedere un $ExtraParam per singolo DT?
                // Passato tramite parametro esterno? 
                // Da qui non è possibile individuare a quale DT si sta facendo riferimento.
                $ExtraParam[self::PAR_INTERAFATTURA] = true;
                $AnaproPre_rec = $Anapro_rec;
                break;

            default:
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Il protocollo selezionato non è di un tipo documento accettato per le fatture elettroniche.";
                $ritorno["RetValue"] = false;
                return $ritorno;
                break;
        }

        if (!$AnaproPre_rec) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Attenzione, impossibile trovare il protocollo della fattura elettronica.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $Protocllo['AnnoProtocollo'] = substr($AnaproPre_rec['PRONUM'], 0, 4);
        $Protocllo['NumeroProtocollo'] = substr($AnaproPre_rec['PRONUM'], 4);
        $Protocllo['TipoProtocollo'] = $AnaproPre_rec['PROPAR'];
        $ReturnParamFattura = $this->GetParamFatturaWithArgs($Protocllo, $ExtraParam);
        return $ReturnParamFattura;
    }

    /* ExtraParam ustao per forzare un EC di un lotto a non essere inviato o altre info. */

    function GetParamFatturaWithArgs($Protocllo, $ExtraParam = array()) {
        /* Se non sono passati tra i parametri da dove li legge? */
        if ($ExtraParam['ISTAT']) {
            $Istat = $ExtraParam['ISTAT'];
        }
        if ($ExtraParam['UTENTE']) {
            $Utente = $ExtraParam['UTENTE'];
        }
        if ($ExtraParam['PASSWORD']) {
            $Password = $ExtraParam['PASSWORD'];
        }
        /* Lettura di utente/password/istat da */
        // Lettura da proKibernetes Param

        /*  -------------   */
        /* Predisposizione delle librerie e varibaili base */
        $proLib = new proLib();
        $proSdi = new proSdi();
        $proLibSdi = new proLibSdi();
        $proLibTabDag = new proLibTabDag();
        $emlLib = new emlLib();
        $ParamFattura = array();
        $anaent_38 = $proLib->GetAnaent('38');
        $anaent_46 = $proLib->GetAnaent('46');
        $PresenzaDecorrenzaTermini = 0;
        $LottoFatture = false;
        $ArrAnomalie = array();

        $AnnoProtocollo = $Protocllo['AnnoProtocollo'];
        $NumeroProtocollo = $Protocllo['NumeroProtocollo'];
        $TipoProtocollo = $Protocllo['TipoProtocollo'];
        /* ACCETTATI EFAA di tipo A. PER ORA. */
        $NumeroProtocollo = str_pad($NumeroProtocollo, 6, '0', STR_PAD_LEFT);
        if ($TipoProtocollo != 'A') {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Accettati solo protocolli di tipo A (Arrivo).";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $Codice = $AnnoProtocollo . $NumeroProtocollo;
        $Anapro_rec = $proLib->GetAnapro($Codice, 'codice', $TipoProtocollo);
        if (!$Anapro_rec) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Protocollo $Codice $TipoProtocollo inesistente.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        if ($Anapro_rec['PROCODTIPODOC'] != $anaent_38['ENTDE1'] || !$anaent_38['ENTDE1']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Il protocollo selezionato non è una Fattura Elettronica in Arrivo.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        /* Preparo la path Del protocollo */
        $protPath = $proLib->SetDirectory($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        /* Prendo il file FatturaPa */
        $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], 'FileFatturaUnivoco', '', false, '', 'FATT_ELETTRONICA');
        if (!$TabDag_rec) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Attenzione, impossibile trovare il metadato del File Fattura Univoco.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }

        $FileNameFattura = $TabDag_rec['TDAGVAL'];
        $ElementiNomeFile = proSdi::GetElementiNomeFile($FileNameFattura);
        list($CodUnivocoP2, $resto) = explode('.', $ElementiNomeFile['CodUnivocoFileP2']);
        $CodUnivocoFile = $ElementiNomeFile['CodUnivocoFileP1'] . '_' . $CodUnivocoP2;

        $RetAnadoc_export = $proLibSdi->GetExportFileFromAnadoc($FileNameFattura, $Anapro_rec);
        if (!$RetAnadoc_export) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Attenzione, il file $FileNameFattura non è presente tra i Documenti.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        /* Preparazione OggettoSDI FatturaPa */
        $FilePathFattura = $RetAnadoc_export['SOURCE'];
        $Anadoc_fattura_rec = $proLib->GetAnadoc($RetAnadoc_export['ROWID_ANADOC'], 'rowid');
        $FileSdi = array('LOCAL_FILEPATH' => $FilePathFattura, 'LOCAL_FILENAME' => $FileNameFattura);
        $ExtraParamSdi = array('PARSEALLEGATI' => true);
        $objProSdi = proSdi::getInstance($FileSdi, $ExtraParamSdi);
        if (!$objProSdi) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Errore nell'istanziare proSdi.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        if ($objProSdi->getErrCode() == -9) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $objProSdi->getErrMessage();
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $FilesPathFattura = $objProSdi->getFilePathFattura();
        $FilePathFattura = $FilesPathFattura[0];

        /*
         * Prendo il file MT
         */
        $ParamExport = array();
        $RetFileMT = $proLibSdi->getExportFileMetadatoSDI($Anapro_rec, proSdi::TIPOMESS_MT, $ParamExport);
        if (!$RetFileMT) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Impossibile trovare il file dei Metadati.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        /* Ritorna un array di anadoc e prendo il primo MT */
        $FilePathMT = $RetFileMT[0]['SOURCE'];
        if (!file_exists($FilePathMT)) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "File dei Metadati non trovato.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $fh = fopen($FilePathMT, 'rb');
        if ($fh) {
            $binaryMt = fread($fh, filesize($FilePathMT));
            fclose($fh);
            $binaryMt = base64_encode($binaryMt);
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Lettura file Metadati fallita.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        if (!$Anadoc_fattura_rec['DOCIDMAIL']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Mail di riferimento SDI non trovata.";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $EstrattoFattura = $objProSdi->getEstrattoFattura();
        /* Tratto sempre e comunque l'xml 
         * per l'eventuale rimozione 
         * degli Allegati */
        $FattureXmlLotti = $proSdi->GetFileXmlFattureLotti($FilePathFattura);
        /* Conto ogni fattura estratta */
        if (count($EstrattoFattura[0]['Body']) > 1) {
            $LottoFatture = true;
        }

        $mailArchivio = $emlLib->getMailArchivio($Anadoc_fattura_rec['DOCIDMAIL'], 'id');
        $PecRicezione = $mailArchivio['TOADDR'];
        $PECSdI = $mailArchivio['FROMADDR'];
        $metadata = unserialize($mailArchivio["METADATA"]);
        if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
            if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                $PECSdI = $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'];
            }
        }

        /*
         * Preparazione Parametri Fatture:
         */
        $ParametriFatture = array();
        $Body = $EstrattoFattura[0]['Body'];
        foreach ($Body as $key => $BodyFattura) {
            $PresenzaDecorrenzaTermini = 0;
            /* Sia per EC che DT, se singola fattura, le altre le scarto. */
            if ($ExtraParam[self::PAR_SINGOLAFATTURA]) {
                if ($ExtraParam[self::PAR_SINGOLAFATTURA] != $BodyFattura['NumeroFattura']) {
                    continue;
                }
            }
            $ParamFattura['ISTAT'] = $Istat;
            $ParamFattura['Utente'] = $Utente;
            $ParamFattura['Password'] = $Password;
            $ParamFattura['MetaDati'] = $binaryMt;
            $ParamFattura['NumeroProtocollo'] = $Anapro_rec['PRONUM'];
            $DataProt = date("Y-m-d", strtotime($Anapro_rec['PRODAR']));
            $ParamFattura['DataProtocollo'] = $DataProt . 'T' . $Anapro_rec['PROORA'];
            $ParamFattura['NumeroFattura'] = $BodyFattura['NumeroFattura'];
            $ParamFattura['DataFattura'] = $BodyFattura['DataFattura'];
            // Per Default FileFattura è L'intera Fattura.
            $FileFattura = $FattureXmlLotti[$BodyFattura['NumeroFattura']];

            /* Se indicata forzatura per la singola fattura */
            $Esito = '';
            $DescEsito = '';
            if ($LottoFatture) {
                /* 1. Controllo la presenza di una decorrenza termini per la singola */
                $TabDag_DT_rec = $proLibSdi->GetDecorrenzaTermini($CodUnivocoFile, $BodyFattura['NumeroFattura']);
                if ($TabDag_DT_rec) {
                    $PresenzaDecorrenzaTermini = 1;
                }
                /* 2.Controllo la presenza di un Esito Committente, se non è presente la decorrenza */
                if (!$PresenzaDecorrenzaTermini) {
                    $LastEsito = $proLibSdi->GetLastEsitoCommittente($CodUnivocoFile, $BodyFattura['NumeroFattura']);
                    if ($LastEsito) {
                        if ($LastEsito == 'EC01') {
                            $Esito = 'Accettata';
                            $DescEsito = 'Accettata tramite Esito Committente.';
                        } else {
                            $Esito = 'Rifiutata';
                            $DescEsito = 'Rifiutata tramite Esito Committente.';
                        }
                    }
                }
                // $FileFattura per la singola
                // su proSdi GetXmlSingolaFattura($FilePathFattura,$NumeroFattura);
            } else {
                /* Altrimenti controllo per intera fattura */
                /* 1. Controllo la presenza di una decorrenza termini */
                $TabDag_DT_rec = $proLibSdi->GetDecorrenzaTermini($CodUnivocoFile);
                if ($TabDag_DT_rec) {
                    $PresenzaDecorrenzaTermini = 1;
                }
                /* 2.Controllo la presenza di un Esito Committente, se non è presente la decorrenza */
                if (!$PresenzaDecorrenzaTermini) {
                    $LastEsito = $proLibSdi->GetLastEsitoCommittente($CodUnivocoFile);
                    if ($LastEsito) {
                        if ($LastEsito == 'EC01') {
                            $Esito = 'Accettata';
                            $DescEsito = 'Accettata tramite Esito Committente.';
                        } else {
                            $Esito = 'Rifiutata';
                            $DescEsito = 'Rifiutata tramite Esito Committente.';
                        }
                    }
                }
            }
            // Per ora sempre Tramite Decorrenza Termini.
//            $PresenzaDecorrenzaTermini = 1;
            if ($PresenzaDecorrenzaTermini) {
                $Esito = 'Accettata';
                $DescEsito = 'Accettata per decorrenza termini.';
            }

            /*
             * Parametro default tutte accettate:
             *      SOLO SE non è già stata rifiutata per EC o Altro.
             */
            if ($anaent_46['ENTDE3'] && $Esito != 'Rifiutata') {
                $Esito = 'Accettata';
                $DescEsito = 'Accettata da Parametro.';
            }

            if (!$Esito) {
                if ($anaent_46['ENTDE1']) {
                    $Esito = 'DaValutare';
                    $DescEsito = 'Nessun esito presente, fattura ancora da valutare';
                } else {
                    $MessAnomalia = "Fattura N. " . $BodyFattura['NumeroFattura'] . " non trasmettibile. Non è stata Accettata/Rifiutata o non è ancora pervenuta la Decorrenza Termini.";
                    $ArrAnomalie[] = $MessAnomalia;
                    continue;
                }
            }

            // @TODO QUI PARAMETRO PER "Rifiutata" DA NON ESPORTARE
            if ($Esito == 'Rifiutata') {
                $MessAnomalia = "Fattura N. " . $BodyFattura['NumeroFattura'] . " non trasmettibile perchè rifiutata.";
                $ArrAnomalie[] = $MessAnomalia;
                continue;
            }


            $ParamFattura['Esito'] = $Esito; // AccettataSospesaContabilita?
            $ParamFattura['DescrizioneMotivo'] = $DescEsito; // Non obbligatorio
            $fh = fopen($FileFattura, 'rb');
            if ($fh) {
                $binaryFattura = fread($fh, filesize($FileFattura));
                fclose($fh);
                $binaryFattura = base64_encode($binaryFattura);
            } else {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Lettura file Fattura fallita.";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            $ParamFattura['Fattura'] = $binaryFattura;
            $ParamFattura['CanaleRicezione'] = 'PEC';
            $ParamFattura['PECSdI'] = $PECSdI;
            $ParamFattura['PECRicezione'] = $PecRicezione;
            $ParamFattura['RicevutaNotificaDecorrenzaTermini'] = $PresenzaDecorrenzaTermini; // DOCIDMAIL
            $ParametriFatture[] = $ParamFattura;
        }
        if (!$ParametriFatture) {
            //$ArrAnomalie
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Nessuna fattura trasmittibile:<br>" . implode("<br>", $ArrAnomalie);
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $ritorno["Status"] = true;
        $ritorno["RetValue"] = $ParametriFatture;
        if ($ArrAnomalie) {
            $ritorno["Anomalia"] = "Sono presenti fatture non trasmittibili:<br>" . implode("<br>", $ArrAnomalie);
        }
        return $ritorno;
    }

    /**
     * 
     * @param type $param array("AnnoProtocollo", "NumeroProtocollo","TipoProtocollo")
     * @return type array("Stato","Messaggio","Risultati"=>array(),"Anomalia")
     */
    function CaricaFatturaWithArgs_11($Protocllo) {
        // Qui funzione per preparare paramfattura
        // Qui funzione per prendere Utente e Password
        $itaKibernetesSdiLayerParam = new itaKibernetesSdiLayerParam();
        $itaKibernetesSdiLayerParam->getParamFromDB();
        $kibernetesClient = new itaKibernetesSdiLayerClient($itaKibernetesSdiLayerParam);
        $RisultatoCaricaFattura = array();

        $ExtraParam = array();
        $ExtraParam['ISTAT'] = $itaKibernetesSdiLayerParam->getIstat();
        $ExtraParam['UTENTE'] = $itaKibernetesSdiLayerParam->getUtente();
        $ExtraParam['PASSWORD'] = $itaKibernetesSdiLayerParam->getPassword();

        $paramsFatture = $this->PreparaParamProtocolloFatturaWithArgs($Protocllo, $ExtraParam);
        if ($paramsFatture["Status"] < 0) {
            $RisultatoCaricaFattura['Status'] = "-1";
            $Messaggio = "Non è possibile procedere con il caricamento.<br>";
            $RisultatoCaricaFattura['Messaggio'] = $Messaggio . $paramsFatture['Message'];
            return $RisultatoCaricaFattura;
        }
        $Fatture = $paramsFatture['RetValue'];
        $RisultatoCaricaFattura['Anomalia'] = $paramsFatture['Anomalia'];
        // Scorro tutte le fatture trasmissibili.
        foreach ($Fatture as $paramFattura) {
            $NumeroProtocollo = $paramFattura['NumeroProtocollo'];
            $NumeroFattura = $paramFattura['NumeroFattura'];
            $ret = $kibernetesClient->ws_caricaFatturaWithArgs($paramFattura);
            $retElab = $this->ElaboraRisultatoRitorno($kibernetesClient, $ret);

            if ($retElab["Status"] < 0) {
                $Risultato = "Anomalia in caricamento Fattura n. $NumeroFattura. " . $retElab["Message"];
            } else {
                $Risultato = "Fattura n. $NumeroFattura: ";
            }

            $RetXml = $retElab['RetXml'];
            $retEsito = $this->SalvaMetadatiEsito($NumeroProtocollo, $NumeroFattura, $RetXml);
            if (!$retEsito['statoEsito']) {
                $RisultatoCaricaFattura['Stato'] = "-1";
                $RisultatoCaricaFattura['Messaggio'] = "Caricamento File interrotto.";
                $RisultatoCaricaFattura['Risultati'][] = $Risultato . $retEsito['messaggioEsito'];
                return $RisultatoCaricaFattura;
            }
            $Risultato.=$retEsito['messaggioEsito'];
            $RisultatoCaricaFattura['Risultati'][] = $Risultato;
        }
        $RisultatoCaricaFattura['Stato'] = 1;
        $RisultatoCaricaFattura['Messaggio'] = "Caricamento File terminato con successo.";
        return $RisultatoCaricaFattura;
    }

    public function ElaboraRisultatoRitorno($kibernetesClient, $ret) {
        $ritorno['RetXml'] = $kibernetesClient->getResult();
        if (!$ret) {
            if ($kibernetesClient->getFault()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Fault: <br>" . print_r($kibernetesClient->getFault(), true);
                $ritorno["RetValue"] = false;
            } elseif ($kibernetesClient->getError()) {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Error: <br>" . print_r($kibernetesClient->getError(), true);
                $ritorno["RetValue"] = false;
            }
            return $ritorno;
        }
        $risultato = $kibernetesClient->getResult();

        if (isset($risultato['Errore'])) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore..................: <br>" . $risultato['Errore'] . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Success";
            $ritorno["RetValue"] = $risultato;
            $ritorno['RetXml'] = $kibernetesClient->getResult();
            $ritorno["RetValue"] = array(
            );
        }
        return $ritorno;
    }

    public function SalvaMetadatiEsito($Protocllo, $NumeroFattura, $RetXml) {
        $proLib = new proLib();
        $proLibTabDag = new proLibTabDag();
        $retEsito = array();

        // Trasmissibile solo il protocollo principale EFAA
        $TipoProtocollo = 'A';
        $Anapro_rec = $proLib->GetAnapro($Protocllo, 'codice', $TipoProtocollo);

        $Fonte = self::FONTE_ESITO_FATTURAWITHARGS11;
        if ($RetXml['CaricaFatturaWithArgsResult']['Status'] == self::ESITO_PRESENTE) {
            $Fonte = self::FONTE_CTR_ESITO_FATTURAWITHARGS11;
        }
        $Nprog = $proLibTabDag->GetProssimoProgFonte("ANAPRO", $Anapro_rec['ROWID'], $Fonte);
        $ArrDati['Status'] = $RetXml['CaricaFatturaWithArgsResult']['Status'];
        $ArrDati['Descrizione'] = $RetXml['CaricaFatturaWithArgsResult']['Descrizione'];
        $ArrDati['Fattura'] = $NumeroFattura;
        $ArrDati['Data'] = date('Y-m-d');
        $ArrDati['Ora'] = date('H:i:s');
        if (!$proLibTabDag->SalvataggioFonteTabdag("ANAPRO", $Anapro_rec['ROWID'], $Fonte, $ArrDati, $Nprog)) {
            $retEsito['statoEsito'] = false;
            $retEsito['messaggioEsito'] = $proLibTabDag->getErrMessage();
            return $retEsito;
        }
        $retEsito['statoEsito'] = true;
        $retEsito['messaggioEsito'] = $ArrDati['Descrizione'] . '.<br> Metadati Esito salvati correttamente.';
        return $retEsito;
    }

}

?>