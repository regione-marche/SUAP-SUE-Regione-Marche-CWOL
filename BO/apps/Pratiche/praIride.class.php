<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    16.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPIride/itaIrideClient.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';
include_once ITA_BASE_PATH . '/apps/Commercio/wcoLib.class.php';
include_once ITA_BASE_PATH . '/apps/Commercio/wcoComgen.php';

class praIride {

    /**
     * Libreria di funzioni Generiche e Utility per Importazioni Pratiche da Iride
     */
    public $praLib;
    public $basLib;
    public $wcoLib;

    function __construct() {
        $this->praLib = new praLib();
        $this->basLib = new basLib();
        $this->wcoLib = new wcoLib();
    }

    private function setClientConfig($irideClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'WSIRIDEENDPOINT', false);
        $uri2 = $uri['CONFIG'];
        $irideClient->setWebservices_uri($uri2);
        $wsdl = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'WSIRIDEWSDL', false);
        $wsdl2 = $wsdl['CONFIG'];
        $irideClient->setWebservices_wsdl($wsdl2);
        $irideClient->setNameSpaces();
        $ns = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'WSIRIDENAMESPACE', false);
        $ns2 = $ns['CONFIG'];
        $irideClient->setNamespace($ns2);
        $utente = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'UTENTE', false);
        $utente2 = $utente['CONFIG'];
        $irideClient->setUtente($utente2);
        $ruolo = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'RUOLO', false);
        $ruolo2 = $ruolo['CONFIG'];
        $irideClient->setRuolo($ruolo2);
        $username = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'USERNAME', false);
        $username2 = $username['CONFIG'];
        $irideClient->setUsername($username2);
        $password = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'PASSWORD', false);
        $password2 = $password['CONFIG'];
        $irideClient->setPassword($password2);
        $timeout = $devLib->getEnv_config('IRIDEWSCONNECTION', 'codice', 'WSIRIDETIMEOUT', false);
        $irideClient->setTimeout($timeout['CONFIG']);
    }

    public function caricaPraticaDaXML($gesnum, $arrayXml) {
        include_once(ITA_LIB_PATH . '/HTML/tag.php');
        include_once(ITA_LIB_PATH . '/HTML/html.php');
        include_once(ITA_LIB_PATH . '/HTML/formHTML.php');

        //
        //Leggo i dati della pratica
        //
        $proges_rec = $this->praLib->GetProges($gesnum);
        $proctipaut_rec = $this->wcoLib->getProctipaut($proges_rec['GESPRO']);
        if (!$proctipaut_rec) {
            Out::msgStop("Attenzione!!!", "Procedimento n. " . $proges_rec['GESPRO'] . " non parametrizzato per importazione automatica nel commercio");
            return false;
        }

        //
        //Assegno campi in base al tipo parametro
        //
        $Anades_tit_rec = $Anades_impresa_rec = $comlic_rec = $praimm_rec = array();

        $titolare = $arrayXml['dati_titolare'][0];
        foreach ($arrayXml['dati_variabili'] as $dato) {
            if ($dato['codice_dato'][0]["@textNode"] == "titolare") {
                foreach ($dato['valore_dato'][0]['parametro'] as $parametro) {
                    if ($parametro['codice'][0]['@textNode'] == "QualitaRichidente") {
                        if ($parametro['valore'][0]['@textNode'] == "LEGALE RAPPRESENTANTE") {
                            $Anades_tit_rec['DESNATLEGALE'] = "R";
                        } else {
                            $Anades_tit_rec['DESNATLEGALE'] = "T";
                        }
                    }
                }
            }
            if ($dato['codice_dato'][0]["@textNode"] == "impresa") {
                $Anades_impresa_rec = array();
                $Anades_impresa_rec['DESNUM'] = $gesnum;
                $Anades_impresa_rec['DESFISGIU'] = "GI";
                $Anades_impresa_rec['DESRUO'] = "0004";
                foreach ($dato['valore_dato'][0]['parametro'] as $parametro) {
                    if ($parametro['codice'][0]['@textNode'] == "PartitaIVA") {
                        $Anades_impresa_rec['DESPIVA'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "Cognome") {
                        $Anades_impresa_rec['DESNOM'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "NaturaGiuridica") {
                        $Anades_impresa_rec['DESNOM'] .= " " . $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "Nazionalita_DESCRI") {
                        $Anades_impresa_rec['DESNAZ'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "CodiceComuneDiResidenza_DESCRI") {
                        $Anades_impresa_rec['DESCIT'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "IndirizzoVia") {
                        $Anades_impresa_rec['DESIND'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "RecapitoImpTelefono") {
                        $Anades_impresa_rec['DESTEL'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "RecapitoImpmail") {
                        $Anades_impresa_rec['DESEMA'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "RecapitoImpPec") {
                        $Anades_impresa_rec['DESPEC'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "NumeroREA") {
                        $Anades_impresa_rec['DESNUMISCRIZIONE'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "CodiceProvinciaREA_DESCRI") {
                        $comuni_rec = $this->basLib->getComuni($parametro['valore'][0]['@textNode']);
                        $Anades_impresa_rec['DESPROVISCRIZIONE'] = $comuni_rec['PROVIN'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "CodiceComuneCCIAA_DESCRI") {
                        $cciaa = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "DataCessazione") {
                        $comlic_rec['LICCES'] = substr($parametro['valore'][0]['@textNode'], 6, 4) . substr($parametro['valore'][0]['@textNode'], 3, 2) . substr($parametro['valore'][0]['@textNode'], 0, 2);
                    }
                }
            }
            if ($dato['codice_dato'][0]["@textNode"] == "attivita") {
                foreach ($dato['valore_dato'][0]['parametro'] as $parametro) {
                    if ($parametro['codice'][0]['@textNode'] == "Attivita") {
                        $comlic_rec['LICATP'] = $parametro['valore'][0]['@textNode']; // Ulteriore attivita
                    }
                    if ($parametro['codice'][0]['@textNode'] == "IndirizzoLuoId_DESCRI") {
                        $comlic_rec['LICEIN'] = $parametro['valore'][0]['@textNode'];
                        $indirizzo = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "IndirizzoNumeroCivico") {
                        $comlic_rec['LICECI'] = $parametro['valore'][0]['@textNode'];
                        $numCivico = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "DataInizioAttivita") {
                        $comlic_rec['LICDIN'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "DataFineAttivita") {
                        $comlic_rec['LICDFI'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "SuperficieCommerciale") {
                        $comlic_rec['LICETM'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "DataInizioStagionale") {
                        $comlic_rec['LICESD'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "DataFineStagionale") {
                        $comlic_rec['LICESA'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "Stato") {
                        if ($parametro['valore'][0]['@textNode'] == "APERTA") {
                            $comlic_rec['LICSTE'] = "Attivo";
                        } else {
                            if ($comlic_rec['LICCES']) {
                                $comlic_rec['LICSTE'] = "Cessato";
                            }
                        }
                    }
                    if ($parametro['codice'][0]['@textNode'] == "CatastoSezione") {
                        $praimm_rec['SEZIONE'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "CatastoFoglio") {
                        $praimm_rec['FOGLIO'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "CatastoNumero") {
                        $praimm_rec['PARTICELLA'] = $parametro['valore'][0]['@textNode'];
                    }
                    if ($parametro['codice'][0]['@textNode'] == "CatastoSub") {
                        $praimm_rec['SUBALTERNO'] = $parametro['valore'][0]['@textNode'];
                    }
                }
                if ($comlic_rec['LICESD'] && $comlic_rec['LICESA']) {
                    $comlic_rec['LICEST'] = 1;
                } else {
                    $comlic_rec['LICEPE'] = 1;
                }
                if ($Anades_tit_rec['DESNATLEGALE'] == "T") {
                    $comlic_rec['LICTIN'] = $indirizzo;
                    $comlic_rec['LICTCI'] = $numCivico;
                    $comlic_rec['LICTPI'] = $Anades_impresa_rec['DESPIVA'];
                    $comlic_rec['LICTCO'] = $Anades_impresa_rec['DESCIT'];
                    $comuni_rec = $this->basLib->getComuni(strtoupper($comlic_rec['LICTCO']));
                    $comlic_rec['LICTPR'] = $comuni_rec['PROVIN'];
                    $comlic_rec['LICTCA'] = $comuni_rec['COAVPO'];
                    $comlic_rec['LICTRI'] = $Anades_impresa_rec['DESNUMISCRIZIONE'];
                    $comlic_rec['LICTCC'] = $cciaa;
                } elseif ($Anades_tit_rec['DESNATLEGALE'] == "R") {
                    $comlic_rec['LICRIN'] = $indirizzo;
                    $comlic_rec['LICRCI'] = $numCivico;
                    $comlic_rec['LICRCF'] = $titolare['tit_codfisc'][0]['@textNode'];
                    $comlic_rec['LICRPI'] = $Anades_impresa_rec['DESPIVA'];
                    $comlic_rec['LICRDE'] = $Anades_impresa_rec['DESNOM'];
                    $comlic_rec['LICRCO'] = $Anades_impresa_rec['DESCIT'];
                    $comuni_rec = $this->basLib->getComuni(strtoupper($comlic_rec['LICRCO']));
                    $comlic_rec['LICRPR'] = $comuni_rec['PROVIN'];
                    $comlic_rec['LICRCA'] = $comuni_rec['COAVPO'];
                    $comlic_rec['LICRRI'] = $Anades_impresa_rec['DESNUMISCRIZIONE'];
                    $comlic_rec['LICRCC'] = $cciaa;
                }
            }
        }

        //
        //Assegno record Titolare da XML
        //
        $Anades_tit_rec['DESNUM'] = $gesnum;
        $Anades_tit_rec['DESFIS'] = $titolare['tit_codfisc'][0]['@textNode'];
        $Anades_tit_rec['DESNOME'] = $titolare['tit_nome'][0]['@textNode'];
        $Anades_tit_rec['DESCOGNOME'] = $titolare['tit_cognome'][0]['@textNode'];
        $Anades_tit_rec['DESNOM'] = $Anades_tit_rec['DESCOGNOME'] . " " . $Anades_tit_rec['DESNOME'];
        $Anades_tit_rec['DESNASCIT'] = $titolare['tit_desluon'][0]['@textNode'];
        $Anades_tit_rec['DESNASPROV'] = $titolare['tit_codpron'][0]['@textNode'];
        $Anades_tit_rec['DESNASNAZ'] = $titolare['tit_staton'][0]['@textNode'];
        $Anades_tit_rec['DESNASDAT'] = substr($titolare['tit_dtnas'][0]['@textNode'], 0, 4) . substr($titolare['tit_dtnas'][0]['@textNode'], 5, 2) . substr($titolare['tit_dtnas'][0]['@textNode'], 8, 2);
        $Anades_tit_rec['DESSESSO'] = $titolare['tit_sesso'][0]['@textNode'];
        $Anades_tit_rec['DESNAZ'] = $titolare['tit_descit'][0]['@textNode'];
        $Anades_tit_rec['DESIND'] = $titolare['tit_desindr'][0]['@textNode'] . " " . $titolare['tit_numciv'][0]['@textNode'];
        $Anades_tit_rec['DESCIV'] = $titolare['tit_numciv'][0]['@textNode'];
        $Anades_tit_rec['DESEMA'] = $titolare['tit_email'][0]['@textNode'];
        $Anades_tit_rec['DESPEC'] = $titolare['tit_pec'][0]['@textNode'];
        $Anades_tit_rec['DESCIT'] = $titolare['tit_descomr'][0]['@textNode'];
        $Anades_tit_rec['DESPRO'] = $titolare['tit_codpror'][0]['@textNode'];
        $Anades_tit_rec['DESRUO'] = "0002";
        $Anades_tit_rec['DESFISGIU'] = $titolare['tit_tiposog'][0]['@textNode'];

        //
        //Assegno record Allegati da XML
        //
        $pramPath = $this->praLib->SetDirectoryPratiche(substr($gesnum, 0, 4), $gesnum, "PROGES");
        foreach ($arrayXml['dati_allegati'] as $allegato) {
            $contentFile = base64_decode($allegato['all_blob'][0]["@textNode"]);
            $ext = $allegato['all_tipo'][0]["@textNode"];
            if ($ext == "p7m") {
                $ext = "pdf.$ext";
            }
            $randName = md5(rand() * time()) . "." . $ext;
            file_put_contents($pramPath . "/" . $randName, $contentFile);
            $pasdoc_rec = array();
            $pasdoc_rec['PASKEY'] = $gesnum;
            $pasdoc_rec['PASFIL'] = $randName;
            $pasdoc_rec['PASLNK'] = "allegato://" . $randName;
            $pasdoc_rec['PASNOT'] = $allegato['all_descri'][0]["@textNode"];
            $pasdoc_rec['PASCLA'] = "GENERALE";
            $pasdoc_rec['PASNAME'] = $allegato['all_nomefile'][0]["@textNode"];
        }

        //
        //Assegno Record Commercio COMLIC
        //
        $progressivo = $this->wcoLib->getProgressivoCompar(2);
        if ($progressivo === false) {
            Out::msgStop("Attenzione!", "Errore nel progressivo.");
            return false;
        }
        $comlic_rec["LICTIP"] = $proctipaut_rec["AUTORIZZAZIONE"];
        $comlic_rec['LICPRO'] = $this->wcoLib->getLicproDaParam($comlic_rec['LICTIP'], $progressivo);
        $comlic_rec["LICSEZ"] = $proctipaut_rec["EVENTO"];
        $comlic_rec["LICAUT"] = "9999999999";
        $comlic_rec["LICDRE"] = date("Ymd");
        $comlic_rec["LICCOG"] = $Anades_tit_rec['DESCOGNOME'];
        $comlic_rec["LICNOM"] = $Anades_tit_rec['DESNOME'];
        $comlic_rec["LICCOF"] = $Anades_tit_rec['DESFIS'];

        $comlic_rec["LICDNA"] = $Anades_tit_rec['DESNASDAT'];
        $comlic_rec["LICCIT"] = $Anades_tit_rec['DESNAZ'];
        $comlic_rec["LICSEX"] = $Anades_tit_rec['DESSESSO'];
        $comlic_rec["LICSTN"] = $Anades_tit_rec['DESNASNAZ'];
        $comlic_rec["LICCON"] = $Anades_tit_rec['DESNASCIT'];
        $comlic_rec["LICPRN"] = $Anades_tit_rec['DESNASPROV'];

        $comlic_rec["LICCOR"] = $Anades_tit_rec['DESCIT'];
        $comlic_rec["LICPRR"] = $Anades_tit_rec['DESPRO'];
        $comlic_rec["LICIND"] = $Anades_tit_rec['DESIND'];
        $comlic_rec["LICCIV"] = $Anades_tit_rec['DESCIV'];
        $comuni_rec = $this->basLib->getComuni(strtoupper($Anades_tit_rec['DESCIT']));
        $comlic_rec["LICCAP"] = $comuni_rec['COAVPO'];

        //
        //Assegno record Catasto PRAIMM
        //
        $praimm_rec['PRONUM'] = $gesnum;
        $praimm_rec['SEQUENZA'] = "10";
        $praimm_rec['TIPO'] = "F";

        //
        // Inserisco i vari record
        //
        if ($Anades_tit_rec) {
            $Anades_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM ANADES WHERE DESNUM='$gesnum' AND DESNOM LIKE '%" . $Anades_tit_rec['DESNOM'] . "%'", false);
            if (!$Anades_rec) {
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANADES", "ROWID", $Anades_tit_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore!!", $exc->getMessage());
                    return false;
                }
            }
        }

        if ($Anades_impresa_rec) {
            $Anades_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM ANADES WHERE DESNUM='$gesnum' AND DESNOM LIKE '%" . $Anades_impresa_rec['DESNOM'] . "%'", false);
            if (!$Anades_rec) {
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "ANADES", "ROWID", $Anades_impresa_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore!!", $exc->getMessage());
                    return false;
                }
            }
        }
        if ($pasdoc_rec) {
            $PasdocOld_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM PASDOC WHERE PASKEY='$gesnum' AND PASNAME LIKE '%" . $pasdoc_rec['PASNAME'] . "%'", false);
            if (!$PasdocOld_rec) {
                try {
                    $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "PASDOC", "ROWID", $pasdoc_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore!!", $exc->getMessage());
                    return false;
                }
            }
        }
        try {
            $nrow = ItaDB::DBInsert($this->wcoLib->getCOMMDB(), "COMLIC", "ROWID", $comlic_rec);
            if ($nrow == -1) {
                return false;
            }
        } catch (Exception $exc) {
            Out::msgStop("Errore!!", $exc->getMessage());
            return false;
        }
        if ($praimm_rec['SEZIONE'] && $praimm_rec['FOGLIO']) {
            try {
                $nrow = ItaDB::DBInsert($this->praLib->getPRAMDB(), "PRAIMM", "ROWID", $praimm_rec);
                if ($nrow == -1) {
                    return false;
                }
            } catch (Exception $exc) {
                Out::msgStop("Errore!!", $exc->getMessage());
                return false;
            }
        }

        //
        //Popolo COMSUA (Aggancio pratiche)
        //
        $this->wcoLib->CollegaPraticaSuap($comlic_rec['LICPRO'], $proges_rec);
        
        //
        //Apro la form del Commercio
        //
        $comlic_newRec = $this->wcoLib->getComlic($comlic_rec['LICPRO']);
        $model = 'wcoComgen';
        itaLib::openForm($model);
        $wcoComgen = new wcoComgen();
        $wcoComgen->CreaCombo();
        $wcoComgen->Dettaglio($comlic_newRec['ROWID']);
        return true;
    }

}

?>